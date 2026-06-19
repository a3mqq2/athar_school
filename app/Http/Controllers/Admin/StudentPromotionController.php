<?php

namespace App\Http\Controllers\Admin;

use App\Models\Grade;
use App\Models\Stage;
use App\Models\Section;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\AcademicYear;
use App\Models\FeeStructure;
use Illuminate\Http\Request;
use App\Models\StudentEnrollment;
use App\Models\StudentInstallment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class StudentPromotionController extends Controller
{
    /**
     * عرض صفحة ترحيل الطلاب
     */
    public function index()
    {
        return view('admin.students.promotion');
    }

    /**
     * API: جلب السنوات الأكاديمية
     */
    public function getAcademicYears()
    {
        $years = AcademicYear::orderBy('is_current', 'desc')
            ->orderBy('start_date', 'desc')
            ->get(['id', 'name', 'is_current']);
            
        return response()->json($years);
    }

    /**
     * API: جلب المراحل حسب القسم والسنة
     */
    public function getStages(Request $request)
    {
        $sectionType = $request->get('section'); // local | international
        $yearId = $request->get('year');

        $query = Stage::with('sectionObj')
            ->orderBy('name');

        if ($sectionType) {
            $query->whereHas('sectionObj', function($q) use ($sectionType) {
                $q->where('type', $sectionType);
            });
        }

        $stages = $query->get(['id', 'name', 'section_id']);
        
        return response()->json($stages);
    }

    /**
     * API: جلب الصفوف حسب المرحلة
     */
    public function getGrades(Request $request)
    {
        $stageId = $request->get('stage');

        $grades = Grade::where('stage_id', $stageId)
            ->orderBy('name')
            ->get(['id', 'name', 'stage_id']);
            
        return response()->json($grades);
    }

    /**
     * API: جلب الفصول حسب الصف
     */
    public function getClassrooms(Request $request)
    {
        $gradeId = $request->get('grade');

        $classrooms = Classroom::where('grade_id', $gradeId)
            ->orderBy('name')
            ->get(['id', 'name', 'grade_id']);
            
        return response()->json($classrooms);
    }

    /**
     * API: جلب الطلاب حسب الصف والسنة
     */
    public function getStudents(Request $request)
    {
        $gradeId = $request->get('grade');
        $yearId = $request->get('year');

        $students = Student::with([
            'currentEnrollment.classroom',
            'currentEnrollment.grade',
            'currentEnrollment.stage'
        ])
        ->whereHas('enrollments', function($q) use ($yearId, $gradeId) {
            $q->where('academic_year_id', $yearId)
              ->where('grade_id', $gradeId);
        })
        ->get()
        ->map(function($student) {
            $enrollment = $student->enrollments
                ->where('academic_year_id', request('year'))
                ->where('grade_id', request('grade'))
                ->first();
                
            return [
                'id' => $student->id,
                'name' => $student->name,
                'code' => $student->code,
                'parent_name' => $student->parent_name,
                'phone' => $student->phone,
                'current_status' => $student->status,
                'current_classroom_id' => $enrollment->classroom_id ?? null,
                'current_classroom_name' => $enrollment->classroom->name ?? 'غير محدد',
                'current_grade_id' => $enrollment->grade_id ?? null,
                'current_stage_id' => $enrollment->stage_id ?? null,
            ];
        });

        return response()->json($students);
    }

    public function promoteStudents(Request $request)
    {
        $validated = $request->validate([
            'source.year' => 'required|exists:academic_years,id',
            'source.stage' => 'required|exists:stages,id',
            'source.grade' => 'required|exists:grades,id',
            'target.year' => 'required|exists:academic_years,id',
            'target.stage' => 'required|exists:stages,id',
            'target.grade' => 'required|exists:grades,id',
            'students' => 'required|array|min:1',
            'students.*.id' => 'required|exists:students,id',
            'students.*.new_status' => 'required|in:active,graduated,transferred,repeating',
            'students.*.target_classroom_id' => 'nullable|exists:classrooms,id',
            'students.*.billing_cycle' => 'nullable|in:year,semester', // إضافة نوع الفوترة للطلاب المرحلين
        ]);
    
        try {
            DB::beginTransaction();
    
            $sourceYear = $validated['source']['year'];
            $targetYear = $validated['target']['year'];
            $targetStageId = $validated['target']['stage'];
            $targetGradeId = $validated['target']['grade'];
    
            $promotedCount = 0;
            $errors = [];
    
            foreach ($validated['students'] as $studentData) {
                try {
                    $student = Student::findOrFail($studentData['id']);
                    
                    // تحديث حالة الطالب
                    $student->update([
                        'status' => $studentData['new_status']
                    ]);
    
                    // إنشاء تسجيل جديد للطلاب النشطين فقط
                    if ($studentData['new_status'] === 'active') {
                        
                        // تحديد الفصل المستهدف
                        $targetClassroomId = $this->determineTargetClassroom(
                            $student, 
                            $studentData['target_classroom_id'] ?? null, 
                            $targetGradeId
                        );
    
                        if ($targetClassroomId) {
                            // التحقق من عدم وجود تسجيل مسبق
                            $existingEnrollment = StudentEnrollment::where('student_id', $student->id)
                                ->where('academic_year_id', $targetYear)
                                ->first();
    
                            if (!$existingEnrollment) {
                                // تحديد نوع الفوترة - إما من الطلب أو من التسجيل السابق
                                $billingCycle =  'year'; // افتراضي سنوي
                                
                                // يمكن أيضاً جلب نوع الفوترة من التسجيل السابق
                                $previousEnrollment = StudentEnrollment::where('student_id', $student->id)
                                    ->where('academic_year_id', $sourceYear)
                                    ->first();
                                
                                if ($previousEnrollment && isset($previousEnrollment->billing_cycle)) {
                                    $billingCycle = $previousEnrollment->billing_cycle;
                                }
    
                                $enrollment = StudentEnrollment::create([
                                    'student_id' => $student->id,
                                    'academic_year_id' => $targetYear,
                                    'stage_id' => $targetStageId,
                                    'grade_id' => $targetGradeId,
                                    'classroom_id' => $targetClassroomId,
                                    'billing_cycle' => $billingCycle,
                                ]);
    
                                // === إنشاء الأقساط التلقائية للطالب المرحل ===
                                $this->createAutomaticInstallments($student, $enrollment, $targetStageId, $targetGradeId, $billingCycle);
    
                                $promotedCount++;
                            } else {
                                $errors[] = "الطالب {$student->name} مسجل مسبقاً في السنة المستهدفة";
                            }
                        } else {
                            $errors[] = "لا يمكن تحديد الفصل المستهدف للطالب {$student->name}";
                        }
                    } else {
                        // للطلاب المتخرجين أو المنقولين، نحدث الحالة فقط
                        $promotedCount++;
                    }
    
                } catch (\Exception $e) {
                    $errors[] = "خطأ في ترحيل الطالب ID {$studentData['id']}: " . $e->getMessage();
                    Log::error('Student promotion error', [
                        'student_id' => $studentData['id'],
                        'error' => $e->getMessage()
                    ]);
                }
            }
    
            // تسجيل العملية في قاعدة البيانات للتتبع
            $this->logPromotionActivity([
                'source_year' => $sourceYear,
                'target_year' => $targetYear,
                'promoted_count' => $promotedCount,
                'total_count' => count($validated['students']),
                'errors_count' => count($errors),
                'performed_by' => auth()->id(),
            ]);
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => "تم ترحيل {$promotedCount} طالب بنجاح مع إنشاء الأقساط التلقائية",
                'promoted_count' => $promotedCount,
                'errors' => $errors,
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Student promotion failed', [
                'error' => $e->getMessage(),
                'data' => $validated
            ]);
    
            return response()->json([
                'success' => false,
                'message' => 'فشل في تنفيذ عملية الترحيل: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * إنشاء الأقساط التلقائية للطالب
     */
    private function createAutomaticInstallments($student, $enrollment, $stageId, $gradeId, $billingCycle)
    {
        // جلب الفصل مع بياناته
        $classroom = Classroom::with('grade.stage.sectionObj')->find($enrollment->classroom_id);
        if (!$classroom) {
            Log::warning("لا يمكن العثور على الفصل لإنشاء الأقساط", [
                'student_id' => $student->id,
                'classroom_id' => $enrollment->classroom_id
            ]);
            return;
        }
    
        // تحديد نوع القسم
        $sectionType = optional($classroom->grade->stage->sectionObj)->type; // local | international
    
        // البحث عن هيكل الرسوم
        $feeStructure = FeeStructure::query()
            ->when($sectionType, fn($q) => $q->where('section_type', $sectionType))
            ->where('stage_id', $stageId)
            ->where('grade_id', $gradeId)
            ->first();
    
        if (!$feeStructure) {
            Log::warning("لا يوجد هيكل رسوم للمرحلة والصف المحدد", [
                'student_id' => $student->id,
                'stage_id' => $stageId,
                'grade_id' => $gradeId,
                'section_type' => $sectionType
            ]);
            return;
        }
    
        $yearAmount = (float) ($feeStructure->year_amount ?? 0);
        if ($yearAmount <= 0) {
            Log::warning("سعر السنة صفر أو غير محدد", [
                'student_id' => $student->id,
                'fee_structure_id' => $feeStructure->id,
                'year_amount' => $yearAmount
            ]);
            return;
        }
    
        try {
            $status = method_exists($this, 'calculateInstallmentStatus')
                ? $this->calculateInstallmentStatus($yearAmount, 0, null)
                : 'due';
    
            StudentInstallment::create([
                'student_id'            => $student->id,
                'student_enrollment_id' => $enrollment->id,
                'amount_due'            => $yearAmount,
                'paid_amount'           => 0,
                'due_date'              => null,
                'status'                => $status,
                'installment_type_id'   => 1, // عدلها لو عندك dynamic types
                'reference'             => 'PROM-YEAR-' . $student->id . '-' . $enrollment->academic_year_id,
                'notes'                 => 'قسط تلقائي - ترحيل طالب',
            ]);
    
            Log::info("تم إنشاء قسط سنوي للطالب المرحل", [
                'student_id' => $student->id,
                'amount_due' => $yearAmount,
                'billing_cycle' => $billingCycle
            ]);
    
        } catch (\Exception $e) {
            Log::error("خطأ في إنشاء الأقساط التلقائية للطالب المرحل", [
                'student_id' => $student->id,
                'enrollment_id' => $enrollment->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    


        /**
     * حساب حالة القسط بناءً على المبلغ المدفوع وتاريخ الاستحقاق
     */
    private function calculateInstallmentStatus($amountDue, $paidAmount, $dueDate)
    {
        $amountDue = (float) $amountDue;
        $paidAmount = (float) $paidAmount;
        
        // إذا تم دفع المبلغ كاملاً أو أكثر
        if ($paidAmount >= $amountDue) {
            return 'paid';
        }
        
        // التحقق من تاريخ الاستحقاق
        $isOverdue = false;
        if ($dueDate) {
            $isOverdue = now()->gt($dueDate);
        }
        
        // إذا لم يتم دفع أي مبلغ
        if ($paidAmount <= 0) {
            return $isOverdue ? 'overdue' : 'due';
        }
        
        // إذا تم دفع جزئي
        return $isOverdue ? 'overdue' : 'partial';
    }
    
    /**
     * تحديد الفصل المستهدف للطالب
     */
    private function determineTargetClassroom($student, $providedClassroomId, $targetGradeId)
    {
        // إذا تم توفير فصل مستهدف، استخدمه
        if ($providedClassroomId) {
            return $providedClassroomId;
        }
    
        // استخدام currentEnrollment() للحصول على التسجيل الحالي
        $currentEnrollment = $student->currentEnrollment();
    
        if ($currentEnrollment && $currentEnrollment->classroom_id) {
            // الحصول على بيانات الفصل الحالي
            $currentClassroom = Classroom::find($currentEnrollment->classroom_id);
            
            if ($currentClassroom) {
                // البحث عن فصل بنفس الاسم في الصف المستهدف
                $matchingClassroom = Classroom::where('grade_id', $targetGradeId)
                    ->where('name', $currentClassroom->name)
                    ->first();
    
                if ($matchingClassroom) {
                    return $matchingClassroom->id;
                }
    
                // البحث عن فصل بنفس الرمز إذا لم يوجد بنفس الاسم
                if ($currentClassroom->code) {
                    $matchingByCode = Classroom::where('grade_id', $targetGradeId)
                        ->where('code', $currentClassroom->code)
                        ->first();
    
                    if ($matchingByCode) {
                        return $matchingByCode->id;
                    }
                }
            }
        }
    
        // كحل أخير، استخدم أول فصل متاح في الصف المستهدف
        $firstAvailableClassroom = Classroom::where('grade_id', $targetGradeId)
            ->first();
    
        return $firstAvailableClassroom ? $firstAvailableClassroom->id : null;
    }

    /**
     * API: معاينة عملية الترحيل قبل التنفيذ
     */
    public function previewPromotion(Request $request)
    {
        $validated = $request->validate([
            'source.year' => 'required|exists:academic_years,id',
            'target.year' => 'required|exists:academic_years,id',
            'students' => 'required|array|min:1',
            'students.*.id' => 'required|exists:students,id',
            'students.*.new_status' => 'required|in:active,graduated,transferred,repeating',
        ]);

        try {
            $sourceYear = AcademicYear::find($validated['source']['year']);
            $targetYear = AcademicYear::find($validated['target']['year']);
            
            $studentIds = array_column($validated['students'], 'id');
            $students = Student::whereIn('id', $studentIds)->get();

            $preview = [
                'source_year' => $sourceYear->name,
                'target_year' => $targetYear->name,
                'total_students' => count($studentIds),
                'status_breakdown' => [
                    'active' => 0,
                    'graduated' => 0,
                    'transferred' => 0,
                    'repeating' => 0,
                ],
                'warnings' => [],
                'students_details' => [],
            ];

            foreach ($validated['students'] as $studentData) {
                $student = $students->find($studentData['id']);
                $preview['status_breakdown'][$studentData['new_status']]++;
                
                $preview['students_details'][] = [
                    'id' => $student->id,
                    'name' => $student->name,
                    'current_status' => $student->status,
                    'new_status' => $studentData['new_status'],
                ];

                // إضافة تحذيرات
                if ($student->status === 'graduated' && $studentData['new_status'] === 'active') {
                    $preview['warnings'][] = "الطالب {$student->name} متخرج حالياً وسيتم تفعيله مرة أخرى";
                }
            }

            return response()->json($preview);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في معاينة الترحيل: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: الحصول على إحصائيات الترحيل
     */
    public function getPromotionStats(Request $request)
    {
        $yearId = $request->get('year');
        $sectionType = $request->get('section');

        $query = Student::with('currentEnrollment');

        if ($yearId) {
            $query->whereHas('enrollments', function($q) use ($yearId) {
                $q->where('academic_year_id', $yearId);
            });
        }

        if ($sectionType) {
            $query->whereHas('currentEnrollment.stage.sectionObj', function($q) use ($sectionType) {
                $q->where('type', $sectionType);
            });
        }

        $students = $query->get();

        $stats = [
            'total_students' => $students->count(),
            'by_status' => [
                'active' => $students->where('status', 'active')->count(),
                'graduated' => $students->where('status', 'graduated')->count(),
                'transferred' => $students->where('status', 'transferred')->count(),
                'repeating' => $students->where('status', 'repeating')->count(),
                'inactive' => $students->where('status', 'inactive')->count(),
            ],
            'by_grade' => $students->groupBy('currentEnrollment.grade_id')
                ->map(function($group) {
                    return [
                        'count' => $group->count(),
                        'grade_name' => $group->first()->currentEnrollment->grade->name ?? 'غير محدد'
                    ];
                }),
        ];

        return response()->json($stats);
    }

    /**
     * تسجيل نشاط الترحيل للتتبع
     */
    private function logPromotionActivity(array $data)
    {
        try {
            DB::table('student_promotion_logs')->insert([
                'source_academic_year_id' => $data['source_year'],
                'target_academic_year_id' => $data['target_year'],
                'promoted_count' => $data['promoted_count'],
                'total_count' => $data['total_count'],
                'errors_count' => $data['errors_count'],
                'performed_by' => $data['performed_by'],
                'performed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to log promotion activity', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
        }
    }

    /**
     * API: جلب تاريخ عمليات الترحيل
     */
    public function getPromotionHistory(Request $request)
    {
        $history = DB::table('student_promotion_logs as spl')
            ->join('academic_years as source_year', 'spl.source_academic_year_id', '=', 'source_year.id')
            ->join('academic_years as target_year', 'spl.target_academic_year_id', '=', 'target_year.id')
            ->join('users', 'spl.performed_by', '=', 'users.id')
            ->select([
                'spl.id',
                'source_year.name as source_year_name',
                'target_year.name as target_year_name',
                'spl.promoted_count',
                'spl.total_count',
                'spl.errors_count',
                'users.name as performed_by_name',
                'spl.performed_at'
            ])
            ->orderBy('spl.performed_at', 'desc')
            ->paginate(20);

        return response()->json($history);
    }
}