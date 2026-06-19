<?php

namespace App\Http\Controllers\Finance;

use App\Models\Section;
use App\Models\Student;
use App\Models\Treasury;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\StudentPayment;
use App\Models\TransactionType;
use App\Models\StudentInstallment;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class StudentBillingController extends Controller
{
    public function index(Request $request)
    {
        $order = $request->string('order')->toString() == 'asc' ? 'asc' : 'desc';
        $orderBy = in_array($request->string('order_by')->toString(), ['due_sum','name','id']) 
            ? $request->string('order_by')->toString() 
            : 'due_sum';
    
        $q          = $request->string('q')->toString();
        $status     = $request->string('status')->toString();
        $minDue     = $request->filled('min_due') ? (float)$request->input('min_due') : null;
        $maxDue     = $request->filled('max_due') ? (float)$request->input('max_due') : null;
        $hasOverdue = $request->boolean('has_overdue');
        $dueFilter  = $request->string('due_filter')->toString();
        $perPage    = in_array((int)$request->input('per_page'), [10,20,50,100]) 
            ? (int)$request->input('per_page') 
            : 20;
    
        // New hierarchical filters
        $sectionId   = $request->input('section_id');
        $stageId     = $request->input('stage_id');
        $gradeId     = $request->input('grade_id');
        $classroomId = $request->input('classroom_id');
        $gender      = $request->input('gender');
    
        $dueSub = DB::table('student_installments')
            ->selectRaw('COALESCE(SUM(amount_due - paid_amount),0)')
            ->whereColumn('student_installments.student_id', 'students.id')
            ->whereIn('status', ['due','partial','overdue']);
    
        $overdueSub = DB::table('student_installments')
            ->selectRaw('COALESCE(SUM(amount_due - paid_amount),0)')
            ->whereColumn('student_installments.student_id', 'students.id')
            ->where('status', 'overdue');
    
        $students = Student::query()
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($x) use ($q) {
                    $x->where('name', 'like', "%{$q}%")
                      ->orWhere('parent_name', 'like', "%{$q}%")
                      ->orWhere('phone', 'like', "%{$q}%");
                });
            })
            ->when($status, function ($qq) use ($status) {
                $qq->where('status', $status);
            })
            // Gender filter
            ->when($gender, function($qq) use ($gender) {
                $qq->where('gender', $gender);
            })
            // Section filter - through enrollment relationship
            ->when($sectionId, function($qq) use ($sectionId) {
                $qq->whereHas('currentEnrollment.stage.sectionObj', function($q) use ($sectionId) {
                    $q->where('id', $sectionId);
                });
            })
            // Stage filter
            ->when($stageId, function($qq) use ($stageId) {
                $qq->whereHas('currentEnrollment', function($q) use ($stageId) {
                    $q->where('stage_id', $stageId);
                });
            })
            // Grade filter
            ->when($gradeId, function($qq) use ($gradeId) {
                $qq->whereHas('currentEnrollment', function($q) use ($gradeId) {
                    $q->where('grade_id', $gradeId);
                });
            })
            // Classroom filter
            ->when($classroomId, function($qq) use ($classroomId) {
                $qq->whereHas('currentEnrollment', function($q) use ($classroomId) {
                    $q->where('classroom_id', $classroomId);
                });
            })
            ->select('students.*')
            ->selectSub($dueSub, 'due_sum')
            ->selectSub($overdueSub, 'overdue_sum')
            ->when($hasOverdue, function ($qq) {
                $qq->having('overdue_sum', '>', 0);
            })
            ->when(!is_null($minDue), function ($qq) use ($minDue) {
                $qq->having('due_sum', '>=', $minDue);
            })
            ->when(!is_null($maxDue), function ($qq) use ($maxDue) {
                $qq->having('due_sum', '<=', $maxDue);
            })
            ->when($dueFilter == 'zero', function($qq) {
                $qq->having('due_sum', '=', 0);
            })
            ->when($dueFilter == 'has_dues', function($qq) {
                $qq->having('due_sum', '>', 0);
            })
            ->when($dueFilter == 'only_overdue', function($qq) {
                $qq->having('overdue_sum', '>', 0);
            })
            ->when($dueFilter == 'non_overdue', function($qq) {
                $qq->having('due_sum', '>', 0)->having('overdue_sum', '=', 0);
            })
            // Sorting
            ->when($orderBy == 'due_sum', function ($qq) use ($order) {
                $qq->orderBy('due_sum', $order);
            })
            ->when($orderBy == 'name', function ($qq) use ($order) {
                $qq->orderBy('name', $order);
            })
            ->when($orderBy == 'id', function ($qq) use ($order) {
                $qq->orderBy('id', $order);
            })
            ->paginate($perPage)
            ->withQueryString();
    
        // Load sections with hierarchy for cascading dropdowns
        $sections = Section::with(['stages.grades.classrooms'])->get();
    
        return view('finance.students.index', [
            'students'    => $students,
            'sections'    => $sections,
            'order'       => $order,
            'orderBy'     => $orderBy,
            'q'           => $q,
            'status'      => $status,
            'minDue'      => $minDue,
            'maxDue'      => $maxDue,
            'hasOverdue'  => $hasOverdue,
            'dueFilter'   => $dueFilter,
            'perPage'     => $perPage,
            'sectionId'   => $sectionId,
            'stageId'     => $stageId,
            'gradeId'     => $gradeId,
            'classroomId' => $classroomId,
            'gender'      => $gender,
        ]);
    }
    

    public function show(Student $student)
    {
        $installmentTypes = DB::table('installment_types')
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id','name']);
        $student->load([
            'installments' => fn($q) => $q->orderBy('due_date')->with('installmentType'),
        ]);

        
        $treasuries = Treasury::where('responsible_user_id', auth()->id())->orderBy('name')->get(['id','name','current_balance']);

        $dueSum = (float) $student->installments()
            ->whereIn('status', ['due','partial','overdue'])
            ->selectRaw('COALESCE(SUM(amount_due - paid_amount),0) as s')
            ->value('s');

            $payments = StudentPayment::with([
                'treasury:id,name',
                'installment:id,installment_type_id,semester_number'
            ])->where('student_id',$student->id)
              ->orderByDesc('id')
              ->paginate(10);
            

        return view('finance.students.show', compact('student','treasuries','dueSum','payments','installmentTypes'));
    }

    public function pay(Request $request, Student $student)
    {
        $data = $request->validate([
            'student_installment_id' => ['required', 'exists:student_installments,id'],
            'treasury_id'            => ['required', 'exists:treasuries,id'],
            'amount'                 => ['required', 'numeric', 'min:0.01'],
            'payment_method'         => ['required', 'in:cash,pos,bank,transfer,other'],
            'bank_name'             => ['nullable', 'string', 'max:255'],
            'account_number'        => ['nullable', 'string', 'max:255'],
        ]);

        return DB::transaction(function () use ($data, $student) {
            $installment = StudentInstallment::lockForUpdate()
                ->where('student_id', $student->id)
                ->findOrFail($data['student_installment_id']);

            $remaining = max(0, (float) $installment->amount_due - (float) $installment->paid_amount);
            $amount = min($remaining, (float) $data['amount']);

            if ($amount <= 0) {
                return back()->with('error', 'لا يوجد مبلغ متبقي لهذا القسط');
            }

            $installment->paid_amount = (float) $installment->paid_amount + $amount;
            $installment->status = $installment->paid_amount >= $installment->amount_due ? 'paid' : 'partial';
            $installment->save();

            $payment = StudentPayment::create([
                'student_id'             => $student->id,
                'student_installment_id' => $installment->id,
                'treasury_id'            => $data['treasury_id'],
                'amount'                 => $amount,
                'payment_method'         => $data['payment_method'],
                'created_by'             => auth()->id(),
                'bank_name'              => $data['bank_name'] ?? null,
                'account_number'         => $data['account_number'] ?? null,
            ]);


            $typeId = optional(TransactionType::where('name', 'اقساط')->first())->id;

            $txn = Transaction::create([
                'payee_name'            => $student->name,
                'amount'                => $amount,
                'description'           =>  $installment->installmentType?->name,
                'document_number'       => (string) $payment->id,
                'transaction_type'      => 'deposit',
                'transaction_type_id'   => $typeId,
                'treasury_id'           => $data['treasury_id'],
                'user_id'               => auth()->id(),
                'treasury_transfer_id'  => null,
                'student_payment_id'   => $payment->id,
                'payment_method'       => $data['payment_method'],
            ]);

            $payment->transaction_id = $txn->id;
            $payment->save();

            return redirect()
                ->route('finance.students.show', $student)
                ->with('success', 'تم تسجيل الدفعة وإنشاء حركة في الخزينة');
        });
    }

    public function addInstallment(Request $request, Student $student)
    {
        $data = $request->validate([
            'installment_type_id' => ['required','exists:installment_types,id'],
            'semester_number'     => ['nullable','integer','min:1'],
            'amount_due'          => ['required','numeric','min:0.01'],
            'due_date'            => ['required','date','after_or_equal:today'],
        ]);
        
        StudentInstallment::create([
            'student_id'            => $student->id,
            'student_enrollment_id' => optional($student->currentEnrollment)->id,
            'installment_type_id'   => $data['installment_type_id'],
            'semester_number'       => $data['semester_number'],
            'amount_due'            => $data['amount_due'],
            'due_date'              => $data['due_date'],
            'paid_amount'           => 0,
            'status'                => 'due',
            'created_by'            => auth()->id(),
        ]);
        

        return redirect()
            ->route('finance.students.show', $student)
            ->with('success', 'تم إضافة قسط جديد للطالب');
    }

    public function refund(Request $request, Student $student)
    {
        $data = $request->validate([
            'student_installment_id' => ['required','exists:student_installments,id'],
            'treasury_id'            => ['required','exists:treasuries,id'],
            'amount'                 => ['required','numeric','min:0.01'],
            'payment_method'         => ['required'],
        ]);
    
        return DB::transaction(function () use ($data, $student) {
            $installment = StudentInstallment::lockForUpdate()
                ->where('student_id',$student->id)
                ->findOrFail($data['student_installment_id']);
    
            if ($data['amount'] > $installment->paid_amount) {
                return back()->with('error','المبلغ المسترد أكبر من المبلغ المدفوع');
            }
    
            // تحديث القسط
            $installment->paid_amount -= $data['amount'];
            if ($installment->paid_amount < 0) {
                $installment->paid_amount = 0;
            }
    
            if ($installment->paid_amount == 0) {
                // استرداد كامل
                $installment->status = 'refunded';
            } elseif ($installment->paid_amount >= $installment->amount_due) {
                $installment->status = 'paid';
            } else {
                $installment->status = 'partial';
            }
    
            $installment->save();
    
            $payment = StudentPayment::create([
                'student_id'             => $student->id,
                'student_installment_id' => $installment->id,
                'treasury_id'            => $data['treasury_id'],
                'amount'                 => $data['amount'],
                'payment_method'         => $data['payment_method'],
                'type'                   => 'refund',
                'created_by'             => auth()->id(),
            ]);
    
            $typeId = optional(TransactionType::where('name','استرجاع اقساط')->first())->id;
            $txn = Transaction::create([
                'payee_name'          => $student->name,
                'amount'              => $data['amount'],
                'description'         =>  $installment->installmentType?->name ,
                'document_number'     => (string)$payment->id,
                'transaction_type'    => 'withdrawal',
                'transaction_type_id' => $typeId,
                'treasury_id'         => $data['treasury_id'],
                'user_id'             => auth()->id(),
                'payment_method'      => $data['payment_method'],
            ]);
    
            $payment->transaction_id = $txn->id;
            $payment->save();
    
            return redirect()
                ->route('finance.students.show',$student)
                ->with('success','تم استرجاع المبلغ وإنشاء حركة في الخزينة');
        });
    }
    


public function updateInstallment(Request $request, Student $student, StudentInstallment $installment)
{
    // التأكد أن القسط يخص هذا الطالب
    if ($installment->student_id != $student->id) {
        return back()->with('error', 'القسط غير موجود');
    }

    $data = $request->validate([
        'installment_type_id' => ['required', 'exists:installment_types,id'],
        'semester_number'     => ['nullable', 'integer', 'min:1'],
        'amount_due'          => ['required', 'numeric', 'min:0.01'],
        'due_date'            => ['required', 'date'],
        'notes'               => ['nullable', 'string', 'max:1000'],
    ]);

    return DB::transaction(function () use ($data, $installment) {
        $oldAmount = $installment->amount_due;
        $newAmount = $data['amount_due'];

        // تحديث بيانات القسط
        $installment->update([
            'installment_type_id' => $data['installment_type_id'],
            'semester_number'     => $data['semester_number'],
            'amount_due'          => $newAmount,
            'due_date'            => $data['due_date'],
            'notes'               => $data['notes'] ?? null,
            'updated_by'          => auth()->id(),
        ]);

        // إعادة حساب حالة القسط بناءً على المبلغ الجديد
        if ($installment->paid_amount >= $newAmount) {
            $installment->status = 'paid';
        } elseif ($installment->paid_amount > 0) {
            $installment->status = 'partial';
        } else {
        }

        $installment->save();

        return redirect()
            ->route('finance.students.show', $installment->student)
            ->with('success', 'تم تحديث القسط بنجاح');
    });
}




    public function destroyInstallment(Student $student, StudentInstallment $installment)
    {
        if ($installment->student_id != $student->id) {
            return back()->with('error', 'القسط غير موجود');
        }

        if ($installment->paid_amount > 0) {
            return back()->with('error', 'لا يمكن حذف قسط تم دفع جزء منه. يجب استرداد المدفوعات أولاً.');
        }



        return DB::transaction(function () use ($installment, $student) {
            $installment->delete();

            return redirect()
                ->route('finance.students.show', $student)
                ->with('success', 'تم حذف القسط بنجاح');
        });
    }


    public function print(Request $request)
{
    $order = $request->string('order')->toString() == 'asc' ? 'asc' : 'desc';
    $orderBy = in_array($request->string('order_by')->toString(), ['due_sum','name','id']) 
        ? $request->string('order_by')->toString() 
        : 'due_sum';

    $q          = $request->string('q')->toString();
    $status     = $request->string('status')->toString();
    $minDue     = $request->filled('min_due') ? (float)$request->input('min_due') : null;
    $maxDue     = $request->filled('max_due') ? (float)$request->input('max_due') : null;
    $hasOverdue = $request->boolean('has_overdue');
    $dueFilter  = $request->string('due_filter')->toString();

    // Hierarchical filters
    $sectionId   = $request->input('section_id');
    $stageId     = $request->input('stage_id');
    $gradeId     = $request->input('grade_id');
    $classroomId = $request->input('classroom_id');
    $gender      = $request->input('gender');

    $dueSub = DB::table('student_installments')
        ->selectRaw('COALESCE(SUM(amount_due - paid_amount),0)')
        ->whereColumn('student_installments.student_id', 'students.id')
        ->whereIn('status', ['due','partial','overdue']);

    $overdueSub = DB::table('student_installments')
        ->selectRaw('COALESCE(SUM(amount_due - paid_amount),0)')
        ->whereColumn('student_installments.student_id', 'students.id')
        ->where('status', 'overdue');

    // نفس الـ query لكن بدون pagination
    $students = Student::query()
        ->when($q, function ($qq) use ($q) {
            $qq->where(function ($x) use ($q) {
                $x->where('name', 'like', "%{$q}%")
                  ->orWhere('parent_name', 'like', "%{$q}%")
                  ->orWhere('phone', 'like', "%{$q}%");
            });
        })
        ->when($status, function ($qq) use ($status) {
            $qq->where('status', $status);
        })
        ->when($gender, function($qq) use ($gender) {
            $qq->where('gender', $gender);
        })
        ->when($sectionId, function($qq) use ($sectionId) {
            $qq->whereHas('currentEnrollment.stage.sectionObj', function($q) use ($sectionId) {
                $q->where('id', $sectionId);
            });
        })
        ->when($stageId, function($qq) use ($stageId) {
            $qq->whereHas('currentEnrollment', function($q) use ($stageId) {
                $q->where('stage_id', $stageId);
            });
        })
        ->when($gradeId, function($qq) use ($gradeId) {
            $qq->whereHas('currentEnrollment', function($q) use ($gradeId) {
                $q->where('grade_id', $gradeId);
            });
        })
        ->when($classroomId, function($qq) use ($classroomId) {
            $qq->whereHas('currentEnrollment', function($q) use ($classroomId) {
                $q->where('classroom_id', $classroomId);
            });
        })
        ->select('students.*')
        ->selectSub($dueSub, 'due_sum')
        ->selectSub($overdueSub, 'overdue_sum')
        ->when($hasOverdue, function ($qq) {
            $qq->having('overdue_sum', '>', 0);
        })
        ->when(!is_null($minDue), function ($qq) use ($minDue) {
            $qq->having('due_sum', '>=', $minDue);
        })
        ->when(!is_null($maxDue), function ($qq) use ($maxDue) {
            $qq->having('due_sum', '<=', $maxDue);
        })
        ->when($dueFilter == 'zero', function($qq) {
            $qq->having('due_sum', '=', 0);
        })
        ->when($dueFilter == 'has_dues', function($qq) {
            $qq->having('due_sum', '>', 0);
        })
        ->when($dueFilter == 'only_overdue', function($qq) {
            $qq->having('overdue_sum', '>', 0);
        })
        ->when($dueFilter == 'non_overdue', function($qq) {
            $qq->having('due_sum', '>', 0)->having('overdue_sum', '=', 0);
        })
        ->when($orderBy == 'due_sum', function ($qq) use ($order) {
            $qq->orderBy('due_sum', $order);
        })
        ->when($orderBy == 'name', function ($qq) use ($order) {
            $qq->orderBy('name', $order);
        })
        ->when($orderBy == 'id', function ($qq) use ($order) {
            $qq->orderBy('id', $order);
        })
        ->with(['currentEnrollment.stage.sectionObj', 'currentEnrollment.grade', 'currentEnrollment.classroom'])
        ->get(); // بدون pagination للطباعة

    // معلومات الفلاتر المطبقة
    $sections = Section::with(['stages.grades.classrooms'])->get();
    $appliedFilters = $this->getAppliedFiltersText($request, $sections);

    return view('finance.students.print', [
        'students'        => $students,
        'appliedFilters'  => $appliedFilters,
        'totalDue'        => $students->sum('due_sum'),
        'totalOverdue'    => $students->sum('overdue_sum'),
    ]);
}

private function getAppliedFiltersText($request, $sections)
{
    $filters = [];
    
    if ($request->filled('q')) {
        $filters[] = 'بحث: ' . $request->q;
    }
    
    if ($request->filled('section_id')) {
        $section = $sections->find($request->section_id);
        if ($section) {
            $filters[] = 'القسم: ' . $section->type_name;
        }
    }
    
    if ($request->filled('stage_id')) {
        foreach ($sections as $sec) {
            $stage = $sec->stages->find($request->stage_id);
            if ($stage) {
                $filters[] = 'المرحلة: ' . $stage->name;
                break;
            }
        }
    }
    
    if ($request->filled('grade_id')) {
        foreach ($sections as $sec) {
            foreach ($sec->stages as $st) {
                $grade = $st->grades->find($request->grade_id);
                if ($grade) {
                    $filters[] = 'الصف: ' . $grade->name;
                    break 2;
                }
            }
        }
    }
    
    if ($request->filled('classroom_id')) {
        foreach ($sections as $sec) {
            foreach ($sec->stages as $st) {
                foreach ($st->grades as $gr) {
                    $classroom = $gr->classrooms->find($request->classroom_id);
                    if ($classroom) {
                        $filters[] = 'الفصل: ' . $classroom->name;
                        break 3;
                    }
                }
            }
        }
    }
    
    if ($request->filled('gender')) {
        $filters[] = 'الجنس: ' . ($request->gender == 'male' ? 'ذكر' : 'أنثى');
    }
    
    if ($request->filled('due_filter')) {
        $dueFilterLabels = [
            'zero' => 'مدفوعين كاملا',
            'has_dues' => 'عليهم أقساط',
            'only_overdue' => 'متأخرات فقط',
            'non_overdue' => 'أقساط بدون متأخرات',
        ];
        $filters[] = $dueFilterLabels[$request->due_filter] ?? '';
    }
    
    if ($request->filled('min_due')) {
        $filters[] = 'الحد الأدنى: ' . number_format($request->min_due, 2);
    }
    
    if ($request->filled('max_due')) {
        $filters[] = 'الحد الأقصى: ' . number_format($request->max_due, 2);
    }
    
    if ($request->boolean('has_overdue')) {
        $filters[] = 'لديه متأخرات';
    }
    
    return $filters;
}

}
