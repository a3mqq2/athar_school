<?php
// app/Http/Controllers/Finance/EmployeeBalanceController.php

namespace App\Http\Controllers\Finance;

use App\Models\User;
use App\Models\Treasury;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\EmployeeAdvance;
use App\Models\TransactionType;
use App\Models\UserTransaction;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;

class EmployeeBalanceController extends Controller
{
    /**
     * عرض صفحة أرصدة الموظفين
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $role = $request->get('role');

        $users = User::query()
            ->when($search, function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            })
            ->when($role, function($q) use ($role) {
                $q->whereHas('roles', function($roleQuery) use ($role) {
                    $roleQuery->where('name', $role);
                });
            })
            ->with('roles')
            ->withSum('advances as total_advances', 'remaining_amount')
            ->orderBy('name')
            ->paginate(20);

        $treasuries = Treasury::where('responsible_user_id', auth()->id())->orderBy('name')->get(['id', 'name', 'current_balance']);
        $roles = \Spatie\Permission\Models\Role::all(['display_name', 'name']);

        return view('finance.employee-balances.index', compact('users', 'treasuries', 'roles'));
    }

    public function updateBalance(Request $request, User $user)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|not_in:0',
            'treasury_id' => 'nullable|exists:treasuries,id',
            'description' => 'required|string|max:255',
            'type' => 'required|in:add,subtract',
        ]);
    
        return DB::transaction(function () use ($validated, $user) {
            $amount = abs((float) $validated['amount']);
            $isAdd = $validated['type'] == 'add';
    
            $transactionType = TransactionType::firstOrCreate(
                ['name' => 'رصيد موظف', 'for_system' => true],
                ['type' => 'withdrawal']
            );
    
            $treasuryTransaction = null;
    
            if ($isAdd) {
                UserTransaction::create([
                    'user_id'        => $user->id,
                    'amount'         => $amount,
                    'type'           => 'credit',
                    'description'    => $validated['description'],
                    'reference_type' => 'adjustment',
                    'transaction_id' => null,
                    'created_by'     => auth()->id(),
                ]);
    
                $user->increment('balance', $amount);
            } else {
                if (!$validated['treasury_id']) {
                    throw new \Exception('يجب تحديد الخزينة عند الخصم');
                }
    
                $treasury = Treasury::lockForUpdate()->findOrFail($validated['treasury_id']);
    
             
                if ($treasury->current_balance < $amount) {
                    throw new \Exception('رصيد الخزينة غير كافي');
                }
    
                $treasuryTransaction = Transaction::create([
                    'payee_name'          => $user->name,
                    'amount'              => $amount,
                    'description'         => $validated['description'],
                    'document_number'     => 'EMP-' . $user->id . '-' . now()->format('YmdHis'),
                    'transaction_type'    => 'withdrawal',
                    'transaction_type_id' => $transactionType->id,
                    'treasury_id'         => $validated['treasury_id'],
                    'user_id'             => auth()->id(),
                    'employee_id'         => $user->id,
                ]);
    
                $user->decrement('balance', $amount);
                $treasury->decrement('current_balance', $amount);
    
                UserTransaction::create([
                    'user_id'        => $user->id,
                    'amount'         => $amount,
                    'type'           => 'debit',
                    'description'    => $validated['description'],
                    'reference_type' => 'adjustment',
                    'transaction_id' => $treasuryTransaction->id,
                    'created_by'     => auth()->id(),
                ]);
            }
    
            return response()->json([
                'success'        => true,
                'message'        => 'تم تحديث رصيد الموظف بنجاح',
                'new_balance'    => number_format($user->fresh()->balance, 2),
                'transaction_id' => $treasuryTransaction?->id
            ]);
        });
    }

    /**
     * 🆕 عرض صفحة إدارة سلف الموظف
     */
    public function advances(User $user)
    {
        $advances = EmployeeAdvance::where('employee_id', $user->id)
            ->with(['treasury', 'creator'])
            ->orderBy('advance_date', 'desc')
            ->paginate(15);

        $treasuries = Treasury::where('responsible_user_id', auth()->id())
            ->orderBy('name')
            ->get(['id', 'name', 'current_balance']);

        $totalActive = EmployeeAdvance::where('employee_id', $user->id)
            ->where('status', 'active')
            ->sum('remaining_amount');

        return view('finance.employee-balances.advances', compact('user', 'advances', 'treasuries', 'totalActive'));
    }

    /**
     * 🆕 إضافة سلفة جديدة
     */
    public function storeAdvance(Request $request, User $user)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'monthly_deduction' => 'required|numeric|min:0.01|lte:amount',
            'treasury_id' => 'required|exists:treasuries,id',
            'description' => 'required|string|max:255',
        ], [
            'amount.required' => 'المبلغ مطلوب',
            'amount.min' => 'المبلغ يجب أن يكون أكبر من صفر',
            'monthly_deduction.required' => 'الاستقطاع الشهري مطلوب',
            'monthly_deduction.lte' => 'الاستقطاع الشهري لا يمكن أن يكون أكبر من قيمة السلفة',
            'treasury_id.required' => 'الخزينة مطلوبة',
            'description.required' => 'الوصف مطلوب',
        ]);

        try {
            return DB::transaction(function () use ($validated, $user) {
                $amount = (float) $validated['amount'];
                $monthlyDeduction = (float) $validated['monthly_deduction'];

                $treasury = Treasury::lockForUpdate()->findOrFail($validated['treasury_id']);

                if ($treasury->current_balance < $amount) {
                    throw new \Exception('رصيد الخزينة غير كافي');
                }

                $advance = EmployeeAdvance::create([
                    'employee_id' => $user->id,
                    'treasury_id' => $validated['treasury_id'],
                    'amount' => $amount,
                    'advance_date' => now()->toDate(),
                    'monthly_deduction' => $monthlyDeduction,
                    'description' => $validated['description'],
                    'remaining_amount' => $amount,
                    'paid_amount' => 0,
                    'status' => 'active',
                    'created_by' => auth()->id(),
                ]);

                $transactionType = TransactionType::firstOrCreate(
                    ['name' => 'سلفة موظف', 'for_system' => true],
                    ['type' => 'withdrawal']
                );

                $treasuryTransaction = Transaction::create([
                    'payee_name' => $user->name,
                    'amount' => $amount,
                    'description' => 'سلفة: ' . $validated['description'],
                    'document_number' => 'ADV-' . $advance->id . '-' . now()->format('YmdHis'),
                    'transaction_type' => 'withdrawal',
                    'transaction_type_id' => $transactionType->id,
                    'treasury_id' => $validated['treasury_id'],
                    'user_id' => auth()->id(),
                    'employee_id' => $user->id,
                    'advance_id' => $advance->id,
                ]);

                $treasury->decrement('current_balance', $amount);

                UserTransaction::create([
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'type' => 'debit',
                    'description' => 'سلفة: ' . $validated['description'],
                    'reference_type' => 'advance',
                    'reference_id' => $advance->id,
                    'transaction_id' => $treasuryTransaction->id,
                    'created_by' => auth()->id(),
                ]);

                $user->decrement('balance', $amount);

                return response()->json([
                    'success' => true,
                    'message' => 'تم إضافة السلفة بنجاح',
                    'new_balance' => number_format($user->fresh()->balance, 2),
                    'total_advances' => number_format($user->advances()->where('status', 'active')->sum('remaining_amount'), 2),
                    'transaction_id' => $treasuryTransaction->id,
                    'advance_id' => $advance->id
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * 🔥 تعديل سلفة - فقط المتبقي يتم التعامل معه
     */
    public function updateAdvance(Request $request, EmployeeAdvance $advance)
    {
        $validated = $request->validate([
            'adjustment_amount' => 'required|numeric|not_in:0',
            'adjustment_type' => 'required|in:increase,decrease',
            'treasury_id' => 'required|exists:treasuries,id',
            'description' => 'required|string|max:255',
            'update_monthly_deduction' => 'nullable|boolean',
            'new_monthly_deduction' => 'nullable|numeric|min:0.01',
        ]);

        try {
            return DB::transaction(function () use ($validated, $advance) {
                $adjustmentAmount = abs((float) $validated['adjustment_amount']);
                $isIncrease = $validated['adjustment_type'] === 'increase';
                $treasury = Treasury::lockForUpdate()->findOrFail($validated['treasury_id']);
                $user = $advance->employee;

                if ($isIncrease) {
                    // ✅ زيادة السلفة - يضاف للمتبقي فقط
                    if ($treasury->current_balance < $adjustmentAmount) {
                        throw new \Exception('رصيد الخزينة غير كافي');
                    }

                    // زيادة المبلغ الإجمالي والمتبقي فقط
                    $advance->increment('amount', $adjustmentAmount);
                    $advance->increment('remaining_amount', $adjustmentAmount);

                    $treasury->decrement('current_balance', $adjustmentAmount);
                    $user->decrement('balance', $adjustmentAmount);

                    $transactionType = TransactionType::firstOrCreate(
                        ['name' => 'تعديل سلفة موظف', 'for_system' => true],
                        ['type' => 'withdrawal']
                    );

                    $treasuryTransaction = Transaction::create([
                        'payee_name' => $user->name,
                        'amount' => $adjustmentAmount,
                        'description' => 'زيادة سلفة: ' . $validated['description'],
                        'document_number' => 'ADV-INC-' . $advance->id . '-' . now()->format('YmdHis'),
                        'transaction_type' => 'withdrawal',
                        'transaction_type_id' => $transactionType->id,
                        'treasury_id' => $validated['treasury_id'],
                        'user_id' => auth()->id(),
                        'employee_id' => $user->id,
                        'advance_id' => $advance->id,
                    ]);

                    UserTransaction::create([
                        'user_id' => $user->id,
                        'amount' => $adjustmentAmount,
                        'type' => 'debit',
                        'description' => 'زيادة سلفة: ' . $validated['description'],
                        'reference_type' => 'advance_adjustment',
                        'reference_id' => $advance->id,
                        'transaction_id' => $treasuryTransaction->id,
                        'created_by' => auth()->id(),
                    ]);

                    $message = 'تم زيادة السلفة بنجاح';

                } else {
                    // ✅ تقليل السلفة - فقط من المتبقي
                    if ($advance->remaining_amount < $adjustmentAmount) {
                        throw new \Exception('المبلغ أكبر من المتبقي من السلفة (' . number_format($advance->remaining_amount, 2) . ' د.ل)');
                    }

                    // تقليل المبلغ الإجمالي والمتبقي فقط - المدفوع لا يتأثر
                    $advance->decrement('amount', $adjustmentAmount);
                    $advance->decrement('remaining_amount', $adjustmentAmount);

                    $treasury->increment('current_balance', $adjustmentAmount);
                    $user->increment('balance', $adjustmentAmount);

                    $transactionType = TransactionType::firstOrCreate(
                        ['name' => 'تعديل سلفة موظف', 'for_system' => true],
                        ['type' => 'deposit']
                    );

                    $treasuryTransaction = Transaction::create([
                        'payee_name' => $user->name,
                        'amount' => $adjustmentAmount,
                        'description' => 'تقليل سلفة: ' . $validated['description'],
                        'document_number' => 'ADV-DEC-' . $advance->id . '-' . now()->format('YmdHis'),
                        'transaction_type' => 'deposit',
                        'transaction_type_id' => $transactionType->id,
                        'treasury_id' => $validated['treasury_id'],
                        'user_id' => auth()->id(),
                        'employee_id' => $user->id,
                        'advance_id' => $advance->id,
                    ]);

                    UserTransaction::create([
                        'user_id' => $user->id,
                        'amount' => $adjustmentAmount,
                        'type' => 'credit',
                        'description' => 'تقليل سلفة: ' . $validated['description'],
                        'reference_type' => 'advance_adjustment',
                        'reference_id' => $advance->id,
                        'transaction_id' => $treasuryTransaction->id,
                        'created_by' => auth()->id(),
                    ]);

                    $message = 'تم تقليل السلفة بنجاح';

                    if ($advance->remaining_amount <= 0) {
                        $advance->update(['status' => 'completed']);
                    }
                }

                // تحديث الاستقطاع الشهري
                if ($validated['update_monthly_deduction'] && isset($validated['new_monthly_deduction'])) {
                    $newMonthly = (float) $validated['new_monthly_deduction'];
                    if ($newMonthly > $advance->remaining_amount) {
                        throw new \Exception('الاستقطاع الشهري لا يمكن أن يكون أكبر من المتبقي');
                    }
                    $advance->update(['monthly_deduction' => $newMonthly]);
                }

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'new_balance' => number_format($user->fresh()->balance, 2),
                    'advance' => [
                        'amount' => number_format($advance->amount, 2),
                        'remaining_amount' => number_format($advance->remaining_amount, 2),
                        'paid_amount' => number_format($advance->paid_amount, 2),
                        'monthly_deduction' => number_format($advance->monthly_deduction, 2),
                        'status' => $advance->status,
                    ],
                    'transaction_id' => $treasuryTransaction->id
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * 🔥 حذف سلفة - يمكن الحذف في أي وقت، فقط المتبقي يتم استرداده
     */
    public function destroyAdvance(EmployeeAdvance $advance)
    {
        try {
            return DB::transaction(function () use ($advance) {
                $remainingAmount = $advance->remaining_amount;
                $paidAmount = $advance->paid_amount;

                // استرجاع المتبقي فقط (إن وجد)
                if ($remainingAmount > 0) {
                    $transactionType = TransactionType::firstOrCreate(
                        ['name' => 'إلغاء سلفة موظف', 'for_system' => true],
                        ['type' => 'deposit']
                    );

                    $treasuryTransaction = Transaction::create([
                        'payee_name' => $advance->employee->name,
                        'amount' => $remainingAmount,
                        'description' => 'إلغاء سلفة: ' . $advance->description . ($paidAmount > 0 ? ' (استرجاع المتبقي فقط)' : ''),
                        'document_number' => 'ADV-CANCEL-' . $advance->id . '-' . now()->format('YmdHis'),
                        'transaction_type' => 'deposit',
                        'transaction_type_id' => $transactionType->id,
                        'treasury_id' => $advance->treasury_id,
                        'user_id' => auth()->id(),
                        'employee_id' => $advance->employee_id,
                        'advance_id' => $advance->id,
                    ]);

                    // استرجاع المبلغ المتبقي فقط
                    $advance->employee->increment('balance', $remainingAmount);
                    $advance->treasury->increment('current_balance', $remainingAmount);

                    // معاملة موظف
                    UserTransaction::create([
                        'user_id' => $advance->employee_id,
                        'amount' => $remainingAmount,
                        'type' => 'credit',
                        'description' => 'إلغاء سلفة: ' . $advance->description . ($paidAmount > 0 ? ' (استرجاع المتبقي)' : ''),
                        'reference_type' => 'advance_cancellation',
                        'reference_id' => $advance->id,
                        'transaction_id' => $treasuryTransaction->id,
                        'created_by' => auth()->id(),
                    ]);
                }

                $advance->delete();

                // رسالة توضيحية
                $message = 'تم حذف السلفة بنجاح.';
                if ($paidAmount > 0) {
                    $message .= ' تم استرجاع المتبقي فقط: ' . number_format($remainingAmount, 2) . ' د.ل';
                    $message .= ' (المبلغ المدفوع ' . number_format($paidAmount, 2) . ' د.ل لم يتم استرجاعه)';
                } else {
                    $message .= ' تم استرجاع كامل المبلغ: ' . number_format($remainingAmount, 2) . ' د.ل';
                }

                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * عرض كشف حساب موظف
     */
    public function statement(User $user, Request $request)
    {
        $from = $request->get('from');
        $to = $request->get('to');

        $userTransactions = UserTransaction::where('user_id', $user->id)
            ->with(['transaction.treasury', 'creator'])
            ->when($from, function($q) use ($from) {
                $q->whereDate('created_at', '>=', $from);
            })
            ->when($to, function($q) use ($to) {
                $q->whereDate('created_at', '<=', $to);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $totalCredits = UserTransaction::where('user_id', $user->id)
            ->where('type', 'credit')->sum('amount');
        $totalDebits = UserTransaction::where('user_id', $user->id)
            ->where('type', 'debit')->sum('amount');
        $totalAdvances = $user->advances()->where('status', 'active')->sum('remaining_amount');

        $treasuries = Treasury::where('responsible_user_id', auth()->id())->orderBy('name')->get(['id', 'name', 'current_balance']);

        return view('finance.employee-balances.statement', compact(
            'user', 'userTransactions', 'totalCredits', 'totalDebits', 'totalAdvances','treasuries'
        ));
    }

    public function print(Request $request)
    {
        $search = $request->get('search');
        $role = $request->get('role');
    
        $users = User::query()
            ->when($search, function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            })
            ->when($role, function($q) use ($role) {
                $q->whereHas('roles', function($roleQuery) use ($role) {
                    $roleQuery->where('name', $role);
                });
            })
            ->with('roles')
            ->withSum('advances as total_advances', 'remaining_amount')
            ->orderBy('name')
            ->paginate(100);
    
        $roles = \Spatie\Permission\Models\Role::all(['display_name', 'name']);
    
        return view('finance.employee-balances.print', compact('users', 'roles'));
    }
}