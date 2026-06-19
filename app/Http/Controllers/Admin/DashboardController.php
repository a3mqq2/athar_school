<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Section;
use App\Models\Classroom;
use App\Models\AttendanceLog;
use App\Models\StudentInstallment;
use App\Models\TeacherSettlement;
use App\Models\Transaction;
use App\Models\TransactionType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $usersCount        = (int) User::count();
        $studentsCount     = (int) Student::count();
        $activeStudents    = (int) Student::where('status','active')->count();
        $classesCount      = class_exists(Classroom::class) ? (int) Classroom::count() : 0;
        $sectionsCount     = class_exists(Section::class) ? (int) Section::count() : 0;

        $today = Carbon::today();
        $todayAttendance = (int) AttendanceLog::whereDate('date', $today)->count();

        $overdueDues = (float) StudentInstallment::where('status','overdue')
            ->selectRaw('COALESCE(SUM(amount_due - paid_amount),0) as s')->value('s');

        $pendingSettlements = class_exists(TeacherSettlement::class)
            ? (int) TeacherSettlement::whereColumn('settled_amount','<','calculated_amount')->count()
            : 0;

        $uLabels = [];
        $uValues = [];
        $sLabels = [];
        $sValues = [];
        for ($i=11; $i>=0; $i--) {
            $mStart = Carbon::now()->subMonths($i)->startOfMonth();
            $mEnd   = (clone $mStart)->endOfMonth();
            $label  = $mStart->format('Y-m');

            $uLabels[] = $label;
            $uValues[] = (int) User::whereBetween('created_at', [$mStart,$mEnd])->count();

            $sLabels[] = $label;
            $sValues[] = (int) Student::whereBetween('created_at', [$mStart,$mEnd])->count();
        }
        $seriesUsersMonthly = ['labels'=>$uLabels,'values'=>$uValues];
        $seriesStudentsMonthly = ['labels'=>$sLabels,'values'=>$sValues];

        $from30 = Carbon::now()->subDays(29)->startOfDay();
        $days = collect(range(0,29))->map(fn($d)=>$from30->copy()->addDays($d)->toDateString());
        $attPresent = AttendanceLog::where('date','>=',$from30->toDateString())
            ->selectRaw('date, COUNT(*) c')->groupBy('date')->pluck('c','date');
        $attAbsent = AttendanceLog::where('date','>=',$from30->toDateString())
            ->selectRaw('date, COUNT(*) c')->groupBy('date')->pluck('c','date');
        $seriesAttendanceDaily = [
            'labels' => $days,
            'present'=> $days->map(fn($d)=>(int)($attPresent[$d] ?? 0)),
            'absent' => $days->map(fn($d)=>(int)($attAbsent[$d] ?? 0)),
        ];

        $typeSums = Transaction::regular()
            ->select('transaction_type_id', DB::raw('SUM(amount) as total'))
            ->groupBy('transaction_type_id')->get();
        $typeNames = TransactionType::whereIn('id',$typeSums->pluck('transaction_type_id'))->pluck('name','id');
        $seriesTxTypes = [
            'labels' => $typeSums->map(fn($r)=>$typeNames[$r->transaction_type_id] ?? ('Type #'.$r->transaction_type_id))->values(),
            'values' => $typeSums->pluck('total')->map(fn($v)=>(float)$v)->values(),
        ];

        $recentUsers = User::with('roles:id,name')
            ->orderByDesc('created_at')->limit(10)->get(['id','name','email','created_at']);

        $recentLogsRaw = AttendanceLog::with('user:id,name')
            ->orderByDesc('created_at')->limit(10)->get(['id','user_id','created_at','notes']);
        $recentLogs = $recentLogsRaw->map(function($l){
            $o = new \stdClass();
            $o->created_at = $l->created_at;
            $o->user = $l->user;
            $o->description = trim(($l->notes ? ' — '.$l->notes : ''));
            return $o;
        });

        $metrics = [
            'users_count'          => $usersCount,
            'students_count'       => $studentsCount,
            'active_students'      => $activeStudents,
            'today_attendance'     => $todayAttendance,
            'classes_count'        => $classesCount,
            'sections_count'       => $sectionsCount,
            'overdue_dues'         => $overdueDues,
            'pending_settlements'  => $pendingSettlements,
        ];

        return view('admin.dashboard', [
            'metrics'               => $metrics,
            'seriesUsersMonthly'    => $seriesUsersMonthly,
            'seriesStudentsMonthly' => $seriesStudentsMonthly,
            'seriesAttendanceDaily' => $seriesAttendanceDaily,
            'seriesTxTypes'         => $seriesTxTypes,
            'recentUsers'           => $recentUsers,
            'recentLogs'            => $recentLogs,
        ]);
    }
}
