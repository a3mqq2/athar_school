<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentInstallment;
use App\Models\StudentPayment;
use App\Models\Transaction;
use App\Models\TransactionType;
use App\Models\Treasury;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd   = Carbon::today()->endOfDay();

        $totalBalance = (float) Treasury::sum('current_balance');

        $todayDepositsQ   = Transaction::regular()->where('transaction_type', 'deposit')->whereBetween('created_at', [$todayStart, $todayEnd]);
        $todayWithdrawalsQ= Transaction::regular()->where('transaction_type', 'withdrawal')->whereBetween('created_at', [$todayStart, $todayEnd]);

        $todayDeposits        = (float) $todayDepositsQ->sum('amount');
        $todayDepositsCount   = (int) $todayDepositsQ->count();
        $todayWithdrawals     = (float) $todayWithdrawalsQ->sum('amount');
        $todayWithdrawalsCount= (int) $todayWithdrawalsQ->count();


        $totalWithdrawal = Transaction::whereHas('transactionType', function($q) {
            $q->where('for_system', false);
        })->where('transaction_type', 'withdrawal')->sum('amount');

        $studentsDue = (float) StudentInstallment::whereIn('status', ['due','partial','overdue'])
            ->selectRaw('COALESCE(SUM(amount_due - paid_amount),0) as s')->value('s');
        $studentsPaid = (float) StudentPayment::selectRaw('COALESCE(SUM(amount),0) as s')->value('s');

        // سلسلة شهرية لحركة الخزينة
        $labels = []; $depositsSeries = []; $withdrawalsSeries = []; $netSeries = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = Carbon::now()->subMonths($i)->startOfMonth();
            $label = $m->format('Y-m');
            $labels[] = $label;

            $mStart = $m->copy()->startOfMonth();
            $mEnd   = $m->copy()->endOfMonth();

            $dep  = (float) Transaction::regular()->where('transaction_type', 'deposit')->whereBetween('created_at', [$mStart, $mEnd])->sum('amount');
            $with = (float) Transaction::regular()->where('transaction_type', 'withdrawal')->whereBetween('created_at', [$mStart, $mEnd])->sum('amount');

            $depositsSeries[]   = $dep;
            $withdrawalsSeries[]= $with;
            $netSeries[]        = $dep - $with;
        }
        $seriesMonthlyTreasury = [
            'labels'       => $labels,
            'deposits'     => $depositsSeries,
            'withdrawals'  => $withdrawalsSeries,
            'net'          => $netSeries,
        ];

        // مدفوعات الطلاب حسب الطريقة
        $byMethodRaw = StudentPayment::select('payment_method', DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')->pluck('total','payment_method');
        $seriesPaymentsByMethod = [
            'labels' => $byMethodRaw->keys()->values(),
            'values' => $byMethodRaw->values(),
        ];

        // مستحق مقابل مدفوع (طلاب)
        $seriesDuesVsPaid = [
            'labels' => ['المستحق','المدفوع'],
            'values' => [$studentsDue, $studentsPaid],
        ];

        // تشارت حسب transaction_type_id
        $typeSums = Transaction::regular()
            ->select('transaction_type_id', DB::raw('SUM(amount) as total'))
            ->groupBy('transaction_type_id')
            ->get();
        $typeNames = TransactionType::whereIn('id', $typeSums->pluck('transaction_type_id'))->pluck('name','id');
        $seriesByTransactionType = [
            'labels' => $typeSums->map(fn($r) => $typeNames[$r->transaction_type_id] ?? ('نوع #'.$r->transaction_type_id))->values(),
            'values' => $typeSums->pluck('total')->map(fn($v)=>(float)$v)->values(),
        ];

        // تشارت الإيداع مقابل السحب (آخر 30 يوم)
        $from30 = Carbon::now()->subDays(29)->startOfDay();
        $days   = collect(range(0,29))->map(fn($d)=>$from30->copy()->addDays($d)->format('Y-m-d'));
        $dailyDeposits = Transaction::regular()
            ->where('transaction_type','deposit')
            ->where('created_at','>=',$from30)
            ->selectRaw("DATE(created_at) as d, SUM(amount) as s")
            ->groupBy('d')->pluck('s','d');
        $dailyWithdraws = Transaction::regular()
            ->where('transaction_type','withdrawal')
            ->where('created_at','>=',$from30)
            ->selectRaw("DATE(created_at) as d, SUM(amount) as s")
            ->groupBy('d')->pluck('s','d');
        $seriesDepVsWithDaily = [
            'labels'      => $days,
            'deposits'    => $days->map(fn($d)=>(float)($dailyDeposits[$d] ?? 0)),
            'withdrawals' => $days->map(fn($d)=>(float)($dailyWithdraws[$d] ?? 0)),
        ];

        // أعلى طلاب مديونية
        $dueSub = DB::table('student_installments')
            ->selectRaw('COALESCE(SUM(amount_due - paid_amount),0)')
            ->whereColumn('student_installments.student_id','students.id')
            ->whereIn('status',['due','partial','overdue']);
        $topDebtors = Student::query()
            ->select('students.id','students.name')
            ->selectSub($dueSub, 'due_sum')
            ->orderByDesc('due_sum')
            ->limit(10)
            ->get();

        // آخر الحركات
        $recentTransactions = Transaction::with('treasury:id,name')
            ->regular()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $depTotal  = (float) Transaction::regular()->where('transaction_type','deposit')->sum('amount');
        $withTotal = (float) Transaction::regular()->where('transaction_type','withdrawal')->sum('amount');

        $dueAmount = StudentInstallment::selectRaw('COALESCE(SUM(amount_due - paid_amount),0) as due_amount')
        ->value('due_amount');

        
        $metrics = [
            'total_balance'             => $totalBalance,
            'today_deposits'            => $todayDeposits,
            'today_withdrawals'         => $todayWithdrawals,
            'today_deposits_count'      => $todayDepositsCount,
            'today_withdrawals_count'   => $todayWithdrawalsCount,
            'students_due'              => $studentsDue,
            'students_paid'             => $studentsPaid,
            'total_deposits_all'        => $depTotal,
            'total_withdrawals_all'     => $withTotal,
            'due_amount'                => $dueAmount,
        ];

        return view('finance.dashboard', [
            'metrics'                  => $metrics,
            'seriesMonthlyTreasury'    => $seriesMonthlyTreasury,
            'seriesPaymentsByMethod'   => $seriesPaymentsByMethod,
            'seriesDuesVsPaid'         => $seriesDuesVsPaid,
            'seriesByTransactionType'  => $seriesByTransactionType,
            'seriesDepVsWithDaily'     => $seriesDepVsWithDaily,
            'topDebtors'               => $topDebtors,
            'recentTransactions'       => $recentTransactions,
            'totalWithdrawal'          => $totalWithdrawal,
        ]);
    }
}
