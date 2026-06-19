<?php

namespace App\Http\Controllers\Finance;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Payroll;
use App\Models\Treasury;
use App\Models\PayrollItem;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use App\Models\EmployeeAdvance;
use App\Models\TransactionType;
use App\Models\UserTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class PayrollController extends Controller
{
    public function index()
    {
        return view('finance.payrolls.index');
    }

    public function formData(Request $request)
    {
        $month = $request->string('month')->toString();

        $users = User::query()
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['admin','finance','supervisor']);
            })
            ->select('id','name','email','salary')
            ->orderBy('name')
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name ?: $u->email,
                    'base_salary' => $u->salary,
                    'bonus' => 0,
                    'deduction' => 0,
                    'notes' => '',
                ];
            });

        $treasuries = Treasury::query()
            ->with('responsible:id,name')
            ->get(['id','name','current_balance','responsible_user_id'])
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'name' => $t->name,
                    'current_balance' => (float)$t->current_balance,
                    'responsible' => $t->responsible->name ?? '',
                ];
            });

        return response()->json([
            'month' => $month,
            'users' => $users,
            'treasuries' => $treasuries,
        ]);
    }

    public function attendance(User $user, Request $request)
    {
        $month = $request->string('month')->toString();
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = (clone $start)->endOfMonth();
    
        $logs = AttendanceLog::query()
            ->where('user_id', $user->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->get(['id','date','check_in_time','lessons_count','notes'])
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'date' => $log->date->format('Y-m-d'),
                    'status' => $log->lessons_count > 0 ? 'present' : 'absent',
                    'check_in' => $log->check_in_time ? $log->check_in_time->format('H:i') : null,
                    'lessons_count' => $log->lessons_count,
                    'notes' => $log->notes,
                ];
            });
    
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name ?: $user->email,
            ],
            'logs' => $logs,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'month' => ['required','date_format:Y-m'],
            'items' => ['required','array','min:1'],
            'items.*.user_id' => ['required','exists:users,id'],
            'items.*.base_salary' => ['nullable','numeric','min:0'],
            'items.*.bonus' => ['nullable','numeric','min:0'],
            'items.*.deduction' => ['nullable','numeric','min:0'],
            'items.*.notes' => ['nullable','string','max:255'],
        ]);
    
        return DB::transaction(function () use ($data) {
            // حساب الإجمالي
            $total = 0;
            foreach ($data['items'] as &$row) {
                $row['base_salary'] = (float)($row['base_salary'] ?? 0);
                $row['bonus'] = (float)($row['bonus'] ?? 0);
                $row['deduction'] = (float)($row['deduction'] ?? 0);
                $row['net_amount'] = max(0, $row['base_salary'] + $row['bonus'] - $row['deduction']);
                $total += $row['net_amount'];
            }
    
            // إنشاء Payroll
            $payroll = Payroll::create([
                'month' => $data['month'],
                'total_amount' => $total,
                'created_by' => auth()->id(),
            ]);
    
            // عناصر الرواتب
            foreach ($data['items'] as $row) {
                PayrollItem::create([
                    'payroll_id' => $payroll->id,
                    'user_id' => $row['user_id'],
                    'base_salary' => $row['base_salary'],
                    'bonus' => $row['bonus'],
                    'deduction' => $row['deduction'],
                    'net_amount' => $row['net_amount'],
                    'notes' => $row['notes'] ?? null,
                ]);
            }
    
            // إنشاء transaction خاص بالرواتب
            $transactionType = TransactionType::firstOrCreate(
                ['name' => 'رواتب'],
                ['type' => 'withdrawal','for_system' => true]
            );
    
            Transaction::create([
                'payee_name' => 'صرف رواتب شهر '.$data['month'],
                'amount' => $total,
                'description' => 'صرف رواتب الموظفين للشهر '.$data['month'],
                'document_number' => 'PAY-'.$payroll->id,
                'transaction_type' => 'withdrawal',
                'transaction_type_id' => $transactionType->id,
                'user_id' => auth()->id(),
            ]);
    
            return response()->json([
                'id' => $payroll->id,
                'total' => $total,
                'message' => 'تم صرف الرواتب بنجاح.'
            ], 201);
        });
    }

    public function list(Request $request)
    {
        $treasuries = Treasury::all();
    
        $query = Payroll::with(['treasury:id,name', 'creator:id,name'])
            ->orderByDesc('month');
    
        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        $payrolls = $query->paginate(15);
    
        return view('finance.payrolls.list', compact('payrolls','treasuries'));
    }
    
    public function show(Payroll $payroll)
    {
        $payroll->load(['treasury:id,name','creator:id,name','items.user:id,name']);
        return view('finance.payrolls.show', compact('payroll'));
    }

    /**
     * 🆕 جلب الموظفين مع بيانات السلف النشطة
     */
    public function getEmployees(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        
        $employees = User::role(['admin','supervisor','finance'])
            ->with(['roles'])
            ->whereHas('roles')
            ->get()
            ->map(function($user) {
                // 🔥 جلب جميع السلف النشطة للموظف
                $activeAdvances = EmployeeAdvance::where('employee_id', $user->id)
                    ->where('status', 'active')
                    ->where('remaining_amount', '>', 0)
                    ->get();
                
                // حساب إجمالي الاستقطاع الشهري المقترح
                $totalMonthlyDeduction = 0;
                $totalRemaining = 0;
                $advancesData = [];
                
                foreach ($activeAdvances as $advance) {
                    $suggestedDeduction = min(
                        $advance->monthly_deduction,
                        $advance->remaining_amount
                    );
                    
                    $totalMonthlyDeduction += $suggestedDeduction;
                    $totalRemaining += $advance->remaining_amount;
                    
                    $advancesData[] = [
                        'id' => $advance->id,
                        'amount' => $advance->amount,
                        'remaining_amount' => $advance->remaining_amount,
                        'monthly_deduction' => $advance->monthly_deduction,
                        'suggested_deduction' => $suggestedDeduction,
                        'description' => $advance->description,
                    ];
                }
                
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->roles->first()?->display_name ?? 'موظف',
                    'balance' => $user->balance,
                    'base_salary' => $user->salary ?? 0,
                    
                    // 🔥 بيانات السلف المحدثة
                    'advance' => $activeAdvances->isNotEmpty() ? [
                        'total_remaining' => $totalRemaining,
                        'total_monthly_deduction' => $totalMonthlyDeduction,
                        'advances_count' => $activeAdvances->count(),
                        'advances' => $advancesData,
                    ] : null,
                    
                    'suggested_deduction' => $totalMonthlyDeduction
                ];
            });
        
        return response()->json(['employees' => $employees]);
    }

    /**
     * 🆕 معالجة صرف الرواتب مع استقطاع السلف
     */
    public function processPayroll(Request $request)
    {
        $data = $request->validate([
            'month' => 'required|string',
            'employees' => 'required|array',
            'employees.*.user_id' => 'required|exists:users,id',
            'employees.*.base_salary' => 'required|numeric|min:0',
            'employees.*.bonus' => 'nullable|numeric|min:0',
            'employees.*.deduction' => 'nullable|numeric|min:0',
            'employees.*.advance_deduction' => 'nullable|numeric|min:0',
            'employees.*.notes' => 'nullable|string|max:500',
        ]);

        try {
            return DB::transaction(function () use ($data) {
                $totalPaid = 0;
                $totalDeductions = 0;
                $totalAmount = 0;

                // إنشاء سجل الراتب الرئيسي
                $payroll = Payroll::create([
                    'month' => $data['month'],
                    'total_amount' => 0, // سنحدثه لاحقاً
                    'created_by' => auth()->id(),
                ]);

                foreach ($data['employees'] as $empData) {
                    $user = User::lockForUpdate()->findOrFail($empData['user_id']);
                    
                    // حساب الصافي
                    $baseSalary = (float) $empData['base_salary'];
                    $bonus = (float) ($empData['bonus'] ?? 0);
                    $deduction = (float) ($empData['deduction'] ?? 0);
                    $advanceDeduction = (float) ($empData['advance_deduction'] ?? 0);
                    
                    $netSalary = $baseSalary + $bonus - $deduction;
                    $finalAmount = $netSalary - $advanceDeduction;
                
                    // إنشاء سجل راتب الموظف
                    $payrollItem = PayrollItem::create([
                        'payroll_id' => $payroll->id,
                        'user_id' => $user->id,
                        'base_salary' => $baseSalary,
                        'bonus' => $bonus,
                        'deduction' => $deduction,
                        'net_amount' => $netSalary,
                        'advance_deduction' => $advanceDeduction,
                        'final_amount' => $finalAmount,
                        'notes' => $empData['notes'] ?? '',
                    ]);

                    // 1️⃣ إضافة حركة الراتب (credit)
                    if($netSalary > 0)
                    {
                        UserTransaction::create([
                            'user_id' => $user->id,
                            'amount' => $netSalary,
                            'type' => 'credit',
                            'description' => "راتب شهر {$data['month']}",
                            'reference_type' => 'salary',
                            'reference_id' => $payrollItem->id,
                            'transaction_id' => null,
                            'created_by' => auth()->id(),
                        ]);
                    }
                   

                    // 2️⃣ معالجة استقطاع السلف من جميع السلف النشطة
                    if ($advanceDeduction > 0) {
                        $remainingToDeduct = $advanceDeduction;
                        
                        // جلب جميع السلف النشطة مرتبة حسب تاريخ الإنشاء (الأقدم أولاً)
                        $activeAdvances = EmployeeAdvance::where('employee_id', $user->id)
                            ->where('status', 'active')
                            ->where('remaining_amount', '>', 0)
                            ->orderBy('advance_date', 'asc')
                            ->lockForUpdate()
                            ->get();

                        foreach ($activeAdvances as $advance) {
                            if ($remainingToDeduct <= 0) break;

                            // حساب المبلغ المستقطع من هذه السلفة
                            $deductFromThisAdvance = min(
                                $remainingToDeduct,
                                $advance->remaining_amount,
                                $advance->monthly_deduction
                            );

                            // تسجيل حركة الاستقطاع
                            UserTransaction::create([
                                'user_id' => $user->id,
                                'amount' => $deductFromThisAdvance,
                                'type' => 'debit',
                                'description' => "استقطاع سلفة #{$advance->id} - راتب شهر {$data['month']}",
                                'reference_type' => 'advance_deduction',
                                'reference_id' => $advance->id,
                                'transaction_id' => null,
                                'created_by' => auth()->id(),
                            ]);

                            // 🔥 تحديث المبلغ المتبقي من السلفة
                            $advance->remaining_amount -= $deductFromThisAdvance;
                            
                            // 🔥 إذا أصبح المتبقي صفر أو أقل، تحديث الحالة إلى مكتملة
                            if ($advance->remaining_amount <= 0) {
                                $advance->status = 'completed';
                                $advance->remaining_amount = 0; // للتأكد من عدم وجود قيم سالبة
                            }
                            
                            $advance->save();

                            $remainingToDeduct -= $deductFromThisAdvance;
                        }

                        // تحذير إذا لم يتم استقطاع كامل المبلغ
                        if ($remainingToDeduct > 0.01) {
                            \Log::warning("لم يتم استقطاع كامل المبلغ للموظف {$user->id}. المتبقي: {$remainingToDeduct}");
                        }
                    }

                    // 3️⃣ تحديث رصيد الموظف (الصافي بعد كل شيء)
                    $user->increment('balance', $finalAmount);

                    $totalPaid += $finalAmount;
                    $totalDeductions += $advanceDeduction;
                    $totalAmount += $netSalary;
                }

                // تحديث إجمالي الراتب
                $payroll->update(['total_amount' => $totalAmount]);

                return response()->json([
                    'success' => true,
                    'message' => 'تم صرف الرواتب بنجاح',
                    'payroll_id' => $payroll->id,
                    'total' => $totalPaid,
                    'deductions' => $totalDeductions,
                    'gross_total' => $totalAmount
                ]);
            });
        } catch (\Exception $e) {
            \Log::error('خطأ في صرف الرواتب: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء صرف الرواتب: ' . $e->getMessage()
            ], 500);
        }
    }
}