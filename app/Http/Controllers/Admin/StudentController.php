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
use App\Exports\StudentsExport;
use Illuminate\Validation\Rule;
use App\Models\StudentEnrollment;
use App\Models\StudentInstallment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StudentContactsExport;
use App\Models\InstallmentType;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        // جلب البيانات للفلاتر
        $sections = Section::with(['stages.grades.classrooms'])->get();
        $currentAcademicYear = AcademicYear::where('is_current', true)->first();
        
        // بناء الاستعلام مع الفلاتر
        $students = Student::with(['currentEnrollment.stage', 'currentEnrollment.grade', 'currentEnrollment.classroom'])
            ->when($request->section_id, function($query) use ($request) {
                $query->whereHas('currentEnrollment.stage.sectionObj', function($q) use ($request) {
                    $q->where('id', $request->section_id);
                });
            })
            ->when($request->stage_id, function($query) use ($request) {
                $query->whereHas('currentEnrollment', function($q) use ($request) {
                    $q->where('stage_id', $request->stage_id);
                });
            })
            ->when($request->grade_id, function($query) use ($request) {
                $query->whereHas('currentEnrollment', function($q) use ($request) {
                    $q->where('grade_id', $request->grade_id);
                });
            })
            ->when($request->classroom_id, function($query) use ($request) {
                $query->whereHas('currentEnrollment', function($q) use ($request) {
                    $q->where('classroom_id', $request->classroom_id);
                });
            })->when($request->gender, function($query) use ($request) {
                $query->where('gender', $request->gender);
            })
            ->when($request->status, function($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->search, function($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('parent_name', 'like', '%' . $request->search . '%')
                      ->orWhere('phone', 'like', '%' . $request->search . '%');
            })
            ->orderBy('name')
            ->paginate(20);

        return view('admin.students.index', compact('students', 'sections'));
    }

    public function create()
    {
        $sections = Section::with(['stages.grades.classrooms'])->get();
        $currentAcademicYear = AcademicYear::where('is_current', true)->first();
        $installmentTypes = \App\Models\InstallmentType::where('status', 'active')->get();
        return view('admin.students.create', compact('sections', 'currentAcademicYear','installmentTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // بيانات الطالب
            'name'         => 'required|string|max:255',
            'gender'       => 'required|in:male,female',
            'registration_number' => 'nullable|string|max:50',
            'parent_name'  => 'required|string|max:255',
            'phone'        => 'nullable|string|max:20|regex:/^[0-9+\-\s()]*$/',
            'mother_name'  => "nullable",
            "phone2"       => 'nullable',
            'address'      => 'nullable|string|max:500',
            'notes'        => 'nullable|string|max:1000',
            'national_id'  => 'nullable|string|max:50',
            'nationality'  => 'nullable|string|max:100',
            // التسجيل الأكاديمي
            'stage_id'     => 'required|exists:stages,id',
            'grade_id'     => 'required|exists:grades,id',
            'classroom_id' => 'required|exists:classrooms,id',
    
            // نوع الفوترة
            'billing_cycle' => 'required|in:year,semester',
    
            // أقساط سابقة (اختياري)
            'has_previous_installments'                 => 'nullable|boolean',
            'previous_installments'                     => 'nullable|array',
            'previous_installments.*.amount_due'        => 'required_with:previous_installments|numeric|min:0.01|max:999999.99',
            'previous_installments.*.paid_amount'       => 'nullable|numeric|min:0|max:999999.99',
            'previous_installments.*.due_date'          => 'nullable|date|after_or_equal:2020-01-01',
            'previous_installments.*.installment_type_id' => 'required_with:previous_installments|exists:installment_types,id',
            'previous_installments.*.reference'         => 'nullable|string|max:100',
            'previous_installments.*.notes'             => 'nullable|string|max:500',
        ]);
    
        $currentAcademicYear = AcademicYear::where('is_current', true)->first();
        if (!$currentAcademicYear) {
            return redirect()->back()
                ->with('error', 'لا توجد سنة دراسية حالية! يجب تعيين سنة دراسية أولاً من إعدادات السنوات الدراسية.')
                ->withInput();
        }
    
        $classroom = Classroom::with('grade.stage.sectionObj')->find($validated['classroom_id']);
        if (!$classroom) {
            return redirect()->back()
                ->withErrors(['classroom_id' => 'الفصل المحدد غير موجود'])
                ->withInput();
        }
    
        if ($classroom->grade_id != $validated['grade_id']) {
            return redirect()->back()
                ->withErrors(['classroom_id' => 'الفصل المحدد لا ينتمي للصف المختار'])
                ->withInput();
        }
    
        if ($classroom->grade->stage_id != $validated['stage_id']) {
            return redirect()->back()
                ->withErrors(['grade_id' => 'الصف المحدد لا ينتمي للمرحلة المختارة'])
                ->withInput();
        }
    
        // فحص تكرار الاسم + ولي الأمر
        $existingStudent = Student::where('name', $validated['name'])
            ->where('parent_name', $validated['parent_name'])
            ->first();
    
        if ($existingStudent) {
            return redirect()->back()
                ->with('error', 'تنبيه: يوجد طالب بنفس الاسم واسم ولي الأمر.')
                ->withInput();
        }
    
        try {
            DB::beginTransaction();
    
            // إنشاء الطالب
            $student = Student::create([
                'name'        => trim($validated['name']),
                'gender'      => $validated['gender'],
                'registration_number' => $validated['registration_number'] ? trim($validated['registration_number']) : null,
                'parent_name' => trim($validated['parent_name']),
                'phone'       => $validated['phone'] ? trim($validated['phone']) : null,
                'address'     => $validated['address'] ? trim($validated['address']) : null,
                'notes'       => $validated['notes'] ? trim($validated['notes']) : null,
                'status'      => 'active',
                'mother_name' => $validated['mother_name'],
                'phone2'      => $validated['phone2'],
                'national_id' => $validated['national_id'],
                'nationality' => $validated['nationality'],
            ]);
    
            // إنشاء التسجيل
            $enrollmentData = [
                'student_id'       => $student->id,
                'academic_year_id' => $currentAcademicYear->id,
                'stage_id'         => $validated['stage_id'],
                'grade_id'         => $validated['grade_id'],
                'classroom_id'     => $validated['classroom_id'],
                'billing_cycle'    => $validated['billing_cycle'],
            ];
            $enrollment = StudentEnrollment::create($enrollmentData);
    
            $installmentCount = 0;
            $totalDue = 0.0;
            $totalPaid = 0.0;
    
            // === الإنشاء التلقائي للأقساط ===
            $sectionType = optional($classroom->grade->stage->sectionObj)->type; // local | international
            $feeStructure = FeeStructure::query()
                ->when($sectionType, fn($q) => $q->where('section_type', $sectionType))
                ->where('stage_id', $validated['stage_id'])
                ->where('grade_id', $validated['grade_id'])
                ->first();
    
            if ($feeStructure && $validated['billing_cycle'] === 'year') {
                $yearAmount = (float) ($feeStructure->year_amount);
    
                if ($yearAmount > 0) {
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
                        'installment_type_id'   => InstallmentType::where('name', 'رسوم دراسية')->value('id') ?? null,
                        'reference'             => 'YEAR-' . $student->id . '-' . $currentAcademicYear->id,
                        'notes'                 => null,
                    ]);
    
                    $installmentCount++;
                    $totalDue += $yearAmount;
                }
            }
    
            // أقساط سابقة
            if ($request->has('has_previous_installments') && $request->has_previous_installments == '1') {
                $previousInstallments = $request->previous_installments ?? [];
    
                foreach ($previousInstallments as $index => $installmentData) {
                    if (empty($installmentData['amount_due']) || $installmentData['amount_due'] <= 0) {
                        continue;
                    }
    
                    $amountDue  = (float) $installmentData['amount_due'];
                    $paidAmount = (float) ($installmentData['paid_amount'] ?? 0);
                    if ($paidAmount > $amountDue) {
                        DB::rollBack();
                        return redirect()->back()
                            ->withErrors(["previous_installments.{$index}.paid_amount" => "المبلغ المدفوع لا يمكن أن يزيد عن المبلغ المستحق"])
                            ->withInput();
                    }
    
                    $dueDate = !empty($installmentData['due_date']) ? $installmentData['due_date'] : null;
                    $status  = method_exists($this, 'calculateInstallmentStatus')
                        ? $this->calculateInstallmentStatus($amountDue, $paidAmount, $dueDate)
                        : ($paidAmount >= $amountDue ? 'paid' : 'due');
    
                    StudentInstallment::create([
                        'student_id'            => $student->id,
                        'student_enrollment_id' => $enrollment->id,
                        'amount_due'            => $amountDue,
                        'paid_amount'           => $paidAmount,
                        'due_date'              => $dueDate,
                        'status'                => $status,
                        'installment_type_id'   => $installmentData['installment_type_id'],
                        'reference'             => !empty($installmentData['reference']) ? trim($installmentData['reference']) : null,
                        'notes'                 => !empty($installmentData['notes']) ? trim($installmentData['notes']) : null,
                    ]);
    
                    $installmentCount++;
                    $totalDue  += $amountDue;
                    $totalPaid += $paidAmount;
                }
            }
    
            DB::commit();
    
            $successMessage = 'تم إضافة الطالب بنجاح';
            if ($installmentCount > 0) {
                $totalRemaining = $totalDue - $totalPaid;
                $successMessage .= " مع {$installmentCount} قسط (إجمالي: " . number_format($totalDue, 2) . " د.ل";
                if ($totalPaid > 0) {
                    $successMessage .= "، مدفوع: " . number_format($totalPaid, 2) . " د.ل";
                }
                if ($totalRemaining > 0) {
                    $successMessage .= "، متبقي: " . number_format($totalRemaining, 2) . " د.ل";
                }
                $successMessage .= ")";
            }
    
            return redirect()->route('admin.students.index')->with('success', $successMessage);
    
        } catch (\Exception $e) {
            DB::rollBack();
    
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء إضافة الطالب. يرجى المحاولة مرة أخرى أو التواصل مع المدير التقني.')
                ->withInput();
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

    public function show(Student $student)
    {
        $student->load([
            'enrollments.academicYear', 
            'enrollments.stage.sectionObj', 
            'enrollments.grade', 
            'enrollments.classroom',
            'installments' => function($query) {
                $query->orderBy('due_date', 'asc')->orderBy('created_at', 'desc');
            }
        ]);
        
        return view('admin.students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        $sections = Section::with(['stages.grades.classrooms'])->get();
        $student->load('currentEnrollment.stage.sectionObj');
        
        return view('admin.students.edit', compact('student', 'sections'));
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'required|in:male,female',
            'registration_number' => 'nullable|string|max:50',
            'parent_name' => 'required|string|max:255',
            'mother_name' => "nullable",
            "phone2" => "nullable",
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive,graduated,transferred',
            'national_id'  => 'nullable|string|max:50',
            'nationality'  => 'nullable|string|max:100',
            // بيانات التسجيل
            'stage_id' => 'required|exists:stages,id',
            'grade_id' => 'required|exists:grades,id',
            'classroom_id' => 'required|exists:classrooms,id',
        ]);

        // التحقق من صحة العلاقات الهرمية
        $classroom = Classroom::with('grade.stage')->find($validated['classroom_id']);
        if (!$classroom || 
            $classroom->grade_id != $validated['grade_id'] ||
            $classroom->grade->stage_id != $validated['stage_id']) {
            return redirect()->back()
                ->withErrors(['classroom_id' => 'الفصل المحدد لا يتطابق مع الصف والمرحلة المختارة'])
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // تحديث بيانات الطالب
            $student->update([
                'name' => $validated['name'],
                'gender' => $validated['gender'],
                'registration_number' => $validated['registration_number'],
                'parent_name' => $validated['parent_name'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'notes' => $validated['notes'],
                'status' => $validated['status'],
                "mother_name" => $validated['mother_name'],
                "phone2" => $validated['phone2'],
                'national_id' => $validated['national_id'],
                'nationality' => $validated['nationality'],
            ]);

            // تحديث التسجيل الحالي
            if ($student->currentEnrollment) {
                $student->currentEnrollment->update([
                    'stage_id' => $validated['stage_id'],
                    'grade_id' => $validated['grade_id'],
                    'classroom_id' => $validated['classroom_id'],
                ]);
            }

            DB::commit();

            return redirect()->route('admin.students.show', $student)
                ->with('success', 'تم تحديث بيانات الطالب بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء تحديث بيانات الطالب: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Student $student)
    {
        try {
            // التأكد من عدم وجود أقساط مدفوعة
            $hasPayments = $student->installments()->where('paid_amount', '>', 0)->exists();
            
            if ($hasPayments) {
                return redirect()->back()->with('error', 'لا يمكن حذف الطالب لوجود أقساط مدفوعة');
            }

            // التأكد من عدم وجود بيانات مهمة أخرى
            $hasEnrollments = $student->enrollments()->count() > 0;
            if ($hasEnrollments) {
                // يمكن السماح بالحذف لكن مع تأكيد إضافي
                $enrollmentCount = $student->enrollments()->count();
                session()->flash('warning', "سيتم حذف $enrollmentCount تسجيل أكاديمي مع الطالب");
            }

            DB::beginTransaction();

            // حذف الأقساط أولاً
            $student->installments()->delete();
            
            // حذف التسجيلات الأكاديمية
            $student->enrollments()->delete();
            
            // حذف الطالب
            $student->delete();

            DB::commit();
            
            return redirect()->route('admin.students.index')
                ->with('success', 'تم حذف الطالب وجميع بياناته بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حذف الطالب: ' . $e->getMessage());
        }
    }

    // AJAX Routes
    public function getGradesByStage(Request $request)
    {
        try {
            $grades = Grade::where('stage_id', $request->stage_id)
                ->with('classrooms')
                ->orderBy('name')
                ->get();
                
            return response()->json([
                'success' => true,
                'grades' => $grades
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب الصفوف'
            ], 500);
        }
    }

    public function getClassroomsByGrade(Request $request)
    {
        try {
            $classrooms = Classroom::where('grade_id', $request->grade_id)
                ->orderBy('name')
                ->get();
            
            return response()->json([
                'success' => true,
                'classrooms' => $classrooms
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب الفصول'
            ], 500);
        }
    }

    // Student Status Management
    public function updateStatus(Request $request, Student $student)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,inactive,graduated,transferred',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            $oldStatus = $student->status;
            $student->update([
                'status' => $validated['status'],
                'notes' => $validated['notes'] ? $student->notes . "\n" . now()->format('Y-m-d') . ": " . $validated['notes'] : $student->notes
            ]);

            $statusNames = [
                'active' => 'نشط',
                'inactive' => 'غير نشط',
                'graduated' => 'متخرج',
                'transferred' => 'منقول'
            ];

            return response()->json([
                'success' => true,
                'message' => "تم تغيير حالة الطالب من {$statusNames[$oldStatus]} إلى {$statusNames[$validated['status']]}"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث حالة الطالب'
            ], 500);
        }
    }

    // Student Transfer
    public function transfer(Request $request, Student $student)
    {
        $validated = $request->validate([
            'new_stage_id' => 'required|exists:stages,id',
            'new_grade_id' => 'required|exists:grades,id',
            'new_classroom_id' => 'required|exists:classrooms,id',
            'transfer_notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $currentAcademicYear = AcademicYear::where('is_current', true)->first();
            if (!$currentAcademicYear) {
                throw new \Exception('لا توجد سنة دراسية حالية');
            }

            // إنشاء تسجيل جديد
            StudentEnrollment::create([
                'student_id' => $student->id,
                'academic_year_id' => $currentAcademicYear->id,
                'stage_id' => $validated['new_stage_id'],
                'grade_id' => $validated['new_grade_id'],
                'classroom_id' => $validated['new_classroom_id'],
            ]);

            // تحديث ملاحظات الطالب
            if ($validated['transfer_notes']) {
                $student->update([
                    'notes' => $student->notes . "\n" . now()->format('Y-m-d') . " - نقل: " . $validated['transfer_notes']
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم نقل الطالب بنجاح'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء نقل الطالب: ' . $e->getMessage()
            ], 500);
        }
    }

    // Statistics and Reports
    public function getStatistics(Request $request)
    {
        try {
            $currentAcademicYear = AcademicYear::where('is_current', true)->first();
            
            $stats = [
                'total_students' => Student::count(),
                'active_students' => Student::where('status', 'active')->count(),
                'current_enrollments' => StudentEnrollment::where('academic_year_id', $currentAcademicYear->id ?? 0)->count(),
                'male_students' => Student::where('gender', 'male')->count(),
                'female_students' => Student::where('gender', 'female')->count(),
                'students_by_section' => Student::whereHas('currentEnrollment')
                    ->join('student_enrollments', 'students.id', '=', 'student_enrollments.student_id')
                    ->join('stages', 'student_enrollments.stage_id', '=', 'stages.id')
                    ->join('sections', 'stages.section_id', '=', 'sections.id')
                    ->where('student_enrollments.academic_year_id', $currentAcademicYear->id ?? 0)
                    ->groupBy('sections.type')
                    ->selectRaw('sections.type, count(*) as count')
                    ->get(),
                'unpaid_installments' => StudentInstallment::whereIn('status', ['due', 'partial', 'overdue'])->count(),
                'overdue_installments' => StudentInstallment::where('status', 'overdue')->count(),
            ];

            return response()->json([
                'success' => true,
                'statistics' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب الإحصائيات'
            ], 500);
        }
    }

 

    /**
     * تحديث حالة الأقساط المتأخرة
     */
    public function updateOverdueInstallments()
    {
        try {
            $updatedCount = StudentInstallment::where('status', '!=', 'paid')
                ->where('due_date', '<', now()->toDateString())
                ->whereIn('status', ['due', 'partial'])
                ->update(['status' => 'overdue']);

            return response()->json([
                'success' => true,
                'message' => "تم تحديث $updatedCount قسط متأخر"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث الأقساط المتأخرة'
            ], 500);
        }
    }

    public function exportExcel(Request $request)
    {
        $filename = 'students_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
        return Excel::download(new StudentsExport($request->all()), $filename);
    }
    
    /**
     * Export student contacts only (parent_name, phone, mother_name, phone2)
     */
    public function exportContacts(Request $request)
    {
        $filename = 'student_contacts_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
        return Excel::download(new StudentContactsExport($request->all()), $filename);
    }
    
    /**
     * Get filtered students query (helper method)
     */
    private function getFilteredStudentsQuery($filters)
    {
        $query = Student::with([
            'currentEnrollment.stage.sectionObj',
            'currentEnrollment.grade',
            'currentEnrollment.classroom'
        ]);
    
        // Apply search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('parent_name', 'LIKE', "%{$search}%")
                  ->orWhere('mother_name', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('phone2', 'LIKE', "%{$search}%");
            });
        }
    
        // Apply section filter
        if (!empty($filters['section_id'])) {
            $query->whereHas('currentEnrollment.stage.sectionObj', function($q) use ($filters) {
                $q->where('id', $filters['section_id']);
            });
        }
    
        // Apply stage filter
        if (!empty($filters['stage_id'])) {
            $query->whereHas('currentEnrollment.stage', function($q) use ($filters) {
                $q->where('id', $filters['stage_id']);
            });
        }
    
        // Apply grade filter
        if (!empty($filters['grade_id'])) {
            $query->whereHas('currentEnrollment.grade', function($q) use ($filters) {
                $q->where('id', $filters['grade_id']);
            });
        }
    
        // Apply classroom filter
        if (!empty($filters['classroom_id'])) {
            $query->whereHas('currentEnrollment.classroom', function($q) use ($filters) {
                $q->where('id', $filters['classroom_id']);
            });
        }


        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }


        return $query;
    }

    public function printView(Request $request)
{
    $query = $this->getFilteredStudentsQuery($request->all());
    $students = $query->get();
    $sections = Section::with(['stages.grades.classrooms'])->get();
    
    return view('admin.students.print', compact('students', 'sections'));
}
}