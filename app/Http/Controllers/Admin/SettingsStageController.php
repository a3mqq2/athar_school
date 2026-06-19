<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Stage;
use App\Models\Grade;
use App\Models\Classroom;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SettingsStageController extends Controller
{
    public function index()
    {
        try {
            $localStages = Stage::whereHas('sectionObj', fn($q) => $q->where('type', 'local'))
                ->with(['grades.classrooms'])
                ->orderBy('name')
                ->get();

            $internationalStages = Stage::whereHas('sectionObj', fn($q) => $q->where('type', 'international'))
                ->with(['grades.classrooms'])
                ->orderBy('name')
                ->get();

            $totalStages     = Stage::count();
            $totalGrades     = Grade::count();
            $totalClassrooms = Classroom::count();

            return view('admin.settings.stages', compact(
                'localStages',
                'internationalStages',
                'totalStages',
                'totalGrades',
                'totalClassrooms'
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء تحميل البيانات: ' . $e->getMessage());
        }
    }

    public function update(Request $request)
    {
        // 🛠️ تطبيع القيم الناقصة قبل التحقق: لو كان الصف/الفصل قديم والـ FK مفقود، نجيبه من قاعدة البيانات
        $payload = $request->all();

        if (!empty($payload['grades']) && is_array($payload['grades'])) {
            foreach ($payload['grades'] as $gid => $gdata) {
                $hasStageId = isset($gdata['stage_id']) && $gdata['stage_id'] != '' && $gdata['stage_id'] != null;
                if (!$hasStageId && is_numeric($gid) && (int)$gid < 1000) {
                    if ($model = Grade::find($gid)) {
                        $payload['grades'][$gid]['stage_id'] = $model->stage_id;
                    }
                }
            }
        }

        if (!empty($payload['classrooms']) && is_array($payload['classrooms'])) {
            foreach ($payload['classrooms'] as $cid => $cdata) {
                $hasGradeId = isset($cdata['grade_id']) && $cdata['grade_id'] != '' && $cdata['grade_id'] != null;
                if (!$hasGradeId && is_numeric($cid) && (int)$cid < 1000) {
                    if ($model = Classroom::find($cid)) {
                        $payload['classrooms'][$cid]['grade_id'] = $model->grade_id;
                    }
                }
            }
        }

        // دمج التطبيع في الطلب قبل الـ validation
        $request->replace($payload);

        $validator = Validator::make($request->all(), [
            'stages.*.name'         => 'required|string|max:255',
            'stages.*.section_type' => 'required|in:local,international',

            'grades.*.name'         => 'required|string|max:255',
            'grades.*.stage_id'     => 'required|integer',

            'classrooms.*.name'     => 'required|string|max:255',
            'classrooms.*.grade_id' => 'required|integer',
        ], [
            'stages.*.name.required'     => 'اسم المرحلة مطلوب',
            'stages.*.name.max'          => 'اسم المرحلة يجب أن يكون أقل من 255 حرف',
            'stages.*.section_type.*'    => 'نوع القسم يجب أن يكون محلي أو دولي',

            'grades.*.name.required'     => 'اسم الصف مطلوب',
            'grades.*.name.max'          => 'اسم الصف يجب أن يكون أقل من 255 حرف',
            'grades.*.stage_id.required' => 'يجب تحديد المرحلة للصف',
            'grades.*.stage_id.integer'  => 'معرّف المرحلة غير صالح',

            'classrooms.*.name.required' => 'اسم الفصل مطلوب',
            'classrooms.*.name.max'      => 'اسم الفصل يجب أن يكون أقل من 255 حرف',
            'classrooms.*.grade_id.required' => 'يجب تحديد الصف للفصل',
            'classrooms.*.grade_id.integer'  => 'معرّف الصف غير صالح',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'يرجى مراجعة البيانات المدخلة');
        }

        DB::beginTransaction();

        try {
            $existingStageIds     = Stage::pluck('id')->toArray();
            $existingGradeIds     = Grade::pluck('id')->toArray();
            $existingClassroomIds = Classroom::pluck('id')->toArray();

            $processedStageIds     = [];
            $processedGradeIds     = [];
            $processedClassroomIds = [];

            $localSection         = Section::firstOrCreate(['type' => 'local']);
            $internationalSection = Section::firstOrCreate(['type' => 'international']);

            // stages
            if ($request->has('stages')) {
                foreach ($request->input('stages') as $stageTempId => $stageData) {
                    $sectionId = $stageData['section_type'] === 'local' ? $localSection->id : $internationalSection->id;

                    if ((int)$stageTempId < 1000) {
                        $stage = Stage::find($stageTempId);
                        if ($stage) {
                            $stage->update([
                                'name'       => $stageData['name'],
                                'section_id' => $sectionId,
                                'section'   => $stageData['section_type'],
                            ]);
                            $processedStageIds[] = $stage->id;
                        }
                    } else {
                        $stage = Stage::firstOrCreate(
                            ['section_id' => $sectionId, 'name' => $stageData['name'], 'section' => $stageData['section_type']],
                            ['section_id' => $sectionId, 'name' => $stageData['name'], 'section' => $stageData['section_type']],
                        );

                        $this->updateStageMapping($stageTempId, $stage->id, $request);
                        $processedStageIds[] = $stage->id;
                    }
                }
            }

            // grades
            if ($request->has('grades')) {
                foreach ($request->input('grades') as $gradeTempId => $gradeData) {
                    $stageId = (int)$gradeData['stage_id'];

                    if ($stageId >= 1000) {
                        $actualStage = Stage::where('name', $request->input("stages.{$stageId}.name"))
                            ->whereHas('sectionObj', function ($q) use ($request, $stageId) {
                                $q->where('type', $request->input("stages.{$stageId}.section_type"));
                            })
                            ->first();
                        if ($actualStage) {
                            $stageId = $actualStage->id;
                        }
                    }

                    if ((int)$gradeTempId < 1000) {
                        $grade = Grade::find($gradeTempId);
                        if ($grade) {
                            $grade->update([
                                'name'     => $gradeData['name'],
                                'stage_id' => $stageId,
                            ]);
                            $processedGradeIds[] = $grade->id;
                        }
                    } else {
                        $grade = Grade::firstOrCreate(
                            ['stage_id' => $stageId, 'name' => $gradeData['name']],
                            ['stage_id' => $stageId, 'name' => $gradeData['name']]
                        );

                        $this->updateGradeMapping($gradeTempId, $grade->id, $request);
                        $processedGradeIds[] = $grade->id;
                    }
                }
            }

            // classrooms
            if ($request->has('classrooms')) {
                foreach ($request->input('classrooms') as $classroomTempId => $classroomData) {
                    $gradeId = (int)$classroomData['grade_id'];

                    if ($gradeId >= 1000) {
                        $actualGrade = Grade::where('name', $request->input("grades.{$gradeId}.name"))->first();
                        if ($actualGrade) {
                            $gradeId = $actualGrade->id;
                        }
                    }

                    if ((int)$classroomTempId < 1000) {
                        $classroom = Classroom::find($classroomTempId);
                        if ($classroom) {
                            $duplicate = Classroom::where('grade_id', $gradeId)
                                ->where('name', $classroomData['name'])
                                ->where('id', '!=', $classroom->id)
                                ->first();

                            if (!$duplicate) {
                                $classroom->update([
                                    'name'     => $classroomData['name'],
                                    'grade_id' => $gradeId,
                                ]);
                                $processedClassroomIds[] = $classroom->id;
                            } else {
                                $processedClassroomIds[] = $duplicate->id;
                            }
                        }
                    } else {
                        $classroom = Classroom::firstOrCreate(
                            ['grade_id' => $gradeId, 'name' => $classroomData['name']],
                            ['grade_id' => $gradeId, 'name' => $classroomData['name']]
                        );

                        $processedClassroomIds[] = $classroom->id;
                    }
                }
            }

            // حذف العناصر غير المُعالجة
            $stagesToDelete = array_diff($existingStageIds, $processedStageIds);
            $gradesToDelete = array_diff($existingGradeIds, $processedGradeIds);
            $classroomsToDelete = array_diff($existingClassroomIds, $processedClassroomIds);

            if (!empty($classroomsToDelete)) {
                Classroom::whereIn('id', $classroomsToDelete)->delete();
            }
            if (!empty($gradesToDelete)) {
                Grade::whereIn('id', $gradesToDelete)->delete();
            }
            if (!empty($stagesToDelete)) {
                Stage::whereIn('id', $stagesToDelete)->delete();
            }

            DB::commit();

            return redirect()->route('admin.stages.index')->with('success', 'تم حفظ جميع التغييرات بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء حفظ البيانات: ' . $e->getMessage());
        }
    }

    private function updateStageMapping($oldId, $newId, Request $request)
    {
        if ($request->has('grades')) {
            $grades = $request->input('grades');
            foreach ($grades as $gradeId => $gradeData) {
                if ((int)($gradeData['stage_id'] ?? 0) === (int)$oldId) {
                    $grades[$gradeId]['stage_id'] = $newId;
                }
            }
            $request->merge(['grades' => $grades]);
        }
    }

    private function updateGradeMapping($oldId, $newId, Request $request)
    {
        if ($request->has('classrooms')) {
            $classrooms = $request->input('classrooms');
            foreach ($classrooms as $classroomId => $classroomData) {
                if ((int)($classroomData['grade_id'] ?? 0) === (int)$oldId) {
                    $classrooms[$classroomId]['grade_id'] = $newId;
                }
            }
            $request->merge(['classrooms' => $classrooms]);
        }
    }

    public function getStageData($id)
    {
        try {
            $stage = Stage::with(['grades.classrooms', 'sectionObj'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data'    => $stage,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'المرحلة غير موجودة',
            ], 404);
        }
    }

    public function deleteStage($id)
    {
        try {
            $stage = Stage::findOrFail($id);
            $stage->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف المرحلة بنجاح',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف المرحلة',
            ], 500);
        }
    }

    public function deleteGrade($id)
    {
        try {
            $grade = Grade::findOrFail($id);
            $grade->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف الصف بنجاح',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف الصف',
            ], 500);
        }
    }

    public function deleteClassroom($id)
    {
        try {
            $classroom = Classroom::findOrFail($id);
            $classroom->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف الفصل بنجاح',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف الفصل',
            ], 500);
        }
    }

    public function getStatistics()
    {
        try {
            $stats = [
                'totalStages'         => Stage::count(),
                'totalGrades'         => Grade::count(),
                'totalClassrooms'     => Classroom::count(),
                'localStages'         => Stage::whereHas('sectionObj', fn($q) => $q->where('type', 'local'))->count(),
                'internationalStages' => Stage::whereHas('sectionObj', fn($q) => $q->where('type', 'international'))->count(),
            ];

            return response()->json([
                'success' => true,
                'data'    => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحميل الإحصائيات',
            ], 500);
        }
    }

    public function validateStageName(Request $request)
    {
        $name        = $request->input('name');
        $sectionType = $request->input('section_type');
        $stageId     = $request->input('stage_id');

        $query = Stage::whereHas('sectionObj', fn($q) => $q->where('type', $sectionType))
            ->where('name', $name);

        if ($stageId) {
            $query->where('id', '!=', $stageId);
        }

        $exists = $query->exists();

        return response()->json([
            'available' => !$exists,
            'message'   => $exists ? 'اسم المرحلة موجود بالفعل في هذا القسم' : 'اسم المرحلة متاح',
        ]);
    }

    public function validateGradeName(Request $request)
    {
        $name    = $request->input('name');
        $stageId = $request->input('stage_id');
        $gradeId = $request->input('grade_id');

        $query = Grade::where('stage_id', $stageId)->where('name', $name);

        if ($gradeId) {
            $query->where('id', '!=', $gradeId);
        }

        $exists = $query->exists();

        return response()->json([
            'available' => !$exists,
            'message'   => $exists ? 'اسم الصف موجود بالفعل في هذه المرحلة' : 'اسم الصف متاح',
        ]);
    }

    public function validateClassroomName(Request $request)
    {
        $name        = $request->input('name');
        $gradeId     = $request->input('grade_id');
        $classroomId = $request->input('classroom_id');

        $query = Classroom::where('grade_id', $gradeId)->where('name', $name);

        if ($classroomId) {
            $query->where('id', '!=', $classroomId);
        }

        $exists = $query->exists();

        return response()->json([
            'available' => !$exists,
            'message'   => $exists ? 'اسم الفصل موجود بالفعل في هذا الصف' : 'اسم الفصل متاح',
        ]);
    }
}
