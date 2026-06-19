<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Treasury;
use App\Models\StudentPayment;
use App\Models\TeacherSettlement;
use App\Models\TransactionType;
use App\Models\User;
use App\Models\UserTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $treasuries = Treasury::orderBy('name')->get(['id','name']);
        
        // Get employees (staff members)
        $employees = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['admin', 'finance', 'staff']);
        })->orderBy('name')->get(['id', 'name']);
        
        // Get teachers
        $teachers = User::whereHas('roles', function($q) {
            $q->where('name', 'teacher');
        })->orderBy('name')->get(['id', 'name']);
        
        $paymentMethods = [
            'cash' => 'نقدي',
            'pos' => 'نقاط بيع',
            'bank' => 'إيداع بنكي',
            'transfer' => 'تحويل',
            'other' => 'أخرى'
        ];
        
        // Today's payments total for stats
        $todayPayments = StudentPayment::whereDate('created_at', Carbon::today())
            ->sum('amount') ?? 0;

        $transaction_types = TransactionType::all();
        return view('finance.reports.index', compact(
            'treasuries', 'employees', 'teachers', 'paymentMethods', 'todayPayments','transaction_types'
        ));
    }

    public function treasury(Request $request)
    {
        $filters = [
            'from' => $request->input('from', Carbon::today()->format('Y-m-d')),
            'to' => $request->input('to', Carbon::today()->format('Y-m-d')),
            'treasury_id' => $request->input('treasury_id'),
            'transaction_type_id' => $request->input('transaction_type_id'),
        ];

        $treasuries = Treasury::orderBy('name')->get(['id','name']);
        $report = $this->treasuryStatement($filters);
        
        $selectedTreasury = $filters['treasury_id'] 
            ? $treasuries->find($filters['treasury_id']) 
            : null;

        $format = $request->input('format', 'view');
        $view = $format === 'print' ? 'finance.reports.treasury.print' : 'finance.reports.treasury.view';

        return view($view, compact('report', 'filters', 'treasuries', 'selectedTreasury'));
    }

    public function employee(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:users,id'
        ]);

        $filters = [
            'from' => $request->input('from', Carbon::today()->format('Y-m-d')),
            'to' => $request->input('to', Carbon::today()->format('Y-m-d')),
            'employee_id' => $request->input('employee_id'),
        ];

        $employee = User::findOrFail($filters['employee_id']);
        $report = $this->employeeStatement($filters);

        $format = $request->input('format', 'view');
        $view = $format === 'print' ? 'finance.reports.employee.print' : 'finance.reports.employee.view';

        return view($view, compact('report', 'filters', 'employee'));
    }

    public function teachers(Request $request)
    {
        $filters = [
            'from' => $request->input('from', Carbon::today()->format('Y-m-d')),
            'to' => $request->input('to', Carbon::today()->format('Y-m-d')),
            'teacher_id' => $request->input('teacher_id'),
            'status' => $request->input('status'),
        ];

        $teachers = User::whereHas('roles', function($q) {
            $q->where('name', 'teacher');
        })->orderBy('name')->get(['id', 'name']);

        $report = $this->teacherSettlements($filters);

        $format = $request->input('format', 'view');
        $view = $format === 'print' ? 'finance.reports.teachers.print' : 'finance.reports.teachers.view';

        return view($view, compact('report', 'filters', 'teachers'));
    }

    public function students(Request $request)
    {
        $filters = [
            'from' => $request->input('from', Carbon::today()->format('Y-m-d')),
            'to' => $request->input('to', Carbon::today()->format('Y-m-d')),
            'treasury_id' => $request->input('treasury_id'),
            'payment_method' => $request->input('payment_method'),
            'student_search' => $request->input('student_search'),
        ];

        $treasuries = Treasury::orderBy('name')->get(['id','name']);
        $paymentMethods = [
            'cash' => 'نقدي',
            'pos' => 'نقاط بيع',
            'bank' => 'إيداع بنكي',
            'transfer' => 'تحويل',
            'other' => 'أخرى'
        ];

        $report = $this->studentPayments($filters);

        $format = $request->input('format', 'view');
        $view = $format === 'print' ? 'finance.reports.students.print' : 'finance.reports.students.view';

        return view($view, compact('report', 'filters', 'treasuries', 'paymentMethods'));
    }

    private function treasuryStatement(array $filters)
    {
        $from = Carbon::parse($filters['from'])->startOfDay();
        $to = Carbon::parse($filters['to'])->endOfDay();


        $base = Transaction::query()
            ->with(['treasury:id,name'])
            ->when($filters['treasury_id'], fn($q)=>$q->where('treasury_id',$filters['treasury_id']))
            ->when($filters['transaction_type_id'], fn($q)=>$q->where('transaction_type_id',$filters['transaction_type_id']))
            ->whereBetween('created_at', [$from,$to])
            ->select('id','created_at','payee_name','amount','description','transaction_type','treasury_id');

        $rows = $base->orderBy('created_at', 'desc')->orderBy('id', 'desc')->get();

        $totals = [
            'deposits' => (clone $base)->where('transaction_type','deposit')->sum('amount'),
            'withdrawals' => (clone $base)->where('transaction_type','withdrawal')->sum('amount'),
        ];
        $totals['net'] = (float)$totals['deposits'] - (float)$totals['withdrawals'];

        return compact('rows','totals');
    }

    private function employeeStatement(array $filters)
    {
        $from = Carbon::parse($filters['from'])->startOfDay();
        $to = Carbon::parse($filters['to'])->endOfDay();

        // Get employee transactions (salary payments, deductions, etc.)
        $rows = UserTransaction::query()
            ->where('user_id', $filters['employee_id'])
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at', 'desc')
            ->get();

        $totals = [
            'total_received' => $rows->where('transaction_type', 'withdrawal')->sum('amount'),
            'total_deductions' => $rows->where('transaction_type', 'deposit')->sum('amount'),
            'net_salary' => $rows->where('transaction_type', 'withdrawal')->sum('amount') - 
                           $rows->where('transaction_type', 'deposit')->sum('amount'),
        ];

        return compact('rows', 'totals');
    }

    private function teacherSettlements(array $filters)
    {
        $from = Carbon::parse($filters['from'])->startOfDay();
        $to = Carbon::parse($filters['to'])->endOfDay();

        $base = TeacherSettlement::query()
            ->with(['teacher:id,name','treasury:id,name'])
            ->whereBetween('created_at', [$from,$to])
            ->when($filters['teacher_id'], fn($q)=>$q->where('teacher_id',$filters['teacher_id']));

        // Apply status filter
        if ($filters['status'] === 'pending') {
            $base->havingRaw('calculated_amount > settled_amount');
        } elseif ($filters['status'] === 'settled') {
            $base->havingRaw('calculated_amount <= settled_amount');
        }

        $items = $base->select(
                'teacher_id',
                DB::raw('SUM(total_lessons) as total_lessons'),
                DB::raw('SUM(calculated_amount) as calculated_amount'),
                DB::raw('SUM(settled_amount) as settled_amount')
            )
            ->groupBy('teacher_id')
            ->get();

        $rows = $items->map(function($r){
            $pending = max(0, (float)$r->calculated_amount - (float)$r->settled_amount);
            return [
                'teacher_id'        => $r->teacher_id,
                'name'              => optional($r->teacher)->name ?? ('#'.$r->teacher_id),
                'total_lessons'     => (int)$r->total_lessons,
                'calculated_amount' => (float)$r->calculated_amount,
                'settled_amount'    => (float)$r->settled_amount,
                'pending_amount'    => $pending,
            ];
        });

        // Apply status filter after mapping
        if ($filters['status'] === 'pending') {
            $rows = $rows->filter(fn($r) => $r['pending_amount'] > 0);
        } elseif ($filters['status'] === 'settled') {
            $rows = $rows->filter(fn($r) => $r['pending_amount'] == 0);
        }

        $totals = [
            'total_lessons'     => (int)$rows->sum('total_lessons'),
            'calculated_amount' => (float)$rows->sum('calculated_amount'),
            'settled_amount'    => (float)$rows->sum('settled_amount'),
            'pending_amount'    => (float)$rows->sum('pending_amount'),
        ];

        return compact('rows','totals');
    }

    private function studentPayments(array $filters)
    {
        $from = Carbon::parse($filters['from'])->startOfDay();
        $to = Carbon::parse($filters['to'])->endOfDay();

        $base = StudentPayment::query()
            ->with(['student:id,name','treasury:id,name','installment:id,installment_type_id,semester_number'])
            ->whereBetween('created_at', [$from,$to])
            ->when($filters['treasury_id'], fn($q)=>$q->where('treasury_id',$filters['treasury_id']))
            ->when($filters['payment_method'], fn($q)=>$q->where('payment_method',$filters['payment_method']))
            ->when($filters['student_search'], function($q) use ($filters){
                $q->whereHas('student', fn($qq)=>$qq->where('name','like','%'.$filters['student_search'].'%'));
            })
            ->orderBy('created_at','desc')
            ->orderBy('id','desc');

        $rows = $base->get(['id','student_id','student_installment_id','treasury_id','amount','payment_method','transaction_id','created_at']);

        $totals = [
            'count' => $rows->count(),
            'amount' => (float)$rows->sum('amount'),
            'by_method' => $rows->groupBy('payment_method')->map->sum('amount')->toArray(),
            'by_treasury' => $rows->groupBy('treasury_id')->map->sum('amount')->toArray(),
        ];

        return compact('rows','totals');
    }
}