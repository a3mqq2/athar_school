<?php
// app/Http/Controllers/Finance/TeacherSettlementController.php

namespace App\Http\Controllers\Finance;

use App\Models\User;
use App\Models\Treasury;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use App\Models\TransactionType;
use App\Models\UserTransaction;
use App\Models\TeacherSettlement;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class TeacherSettlementController extends Controller
{
    public function index(Request $request)
    {
        $teachers = User::role('teacher')
            ->select('id','name','session_price')
            ->orderBy('name')
            ->get();

        $treasuries = Treasury::orderBy('name')->get(['id','name','current_balance']);

        $teacher = null;
        $logs = collect();
        $summary = [
            'total_lessons' => 0,
            'session_price' => 0,
            'calculated_amount' => 0.00,
        ];

        if ($request->filled('teacher_id')) {
            $teacher = $teachers->firstWhere('id', (int)$request->teacher_id);

            if ($teacher) {
                $logs = AttendanceLog::query()
                    ->where('user_id', $request->teacher_id)
                    ->whereNull('teacher_settlement_id')
                    ->orderBy('date','asc')
                    ->get(['id','date','lessons_count','notes']);

                $totalLessons = (int) $logs->sum('lessons_count');
                $price = (float) ($teacher->session_price ?? 0);

                $summary = [
                    'total_lessons' => $totalLessons,
                    'session_price' => $price,
                    'calculated_amount' => $totalLessons * $price,
                ];
            }
        }

        return view('finance.teacher-settlements.index', compact('teachers','treasuries','teacher','logs','summary'));
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'teacher_id' => ['required','exists:users,id'],
            'settled_amount' => ['required','numeric','min:0'],
            'notes' => ['nullable','string','max:500'],
        ]);
    
        return DB::transaction(function () use ($data) {
            $teacher = User::select('id','name','session_price')->findOrFail($data['teacher_id']);
    
            $logs = AttendanceLog::query()
                ->where('user_id', $teacher->id)
                ->whereNull('teacher_settlement_id')
                ->lockForUpdate()
                ->get(['id','lessons_count']);
    
            $totalLessons = (int) $logs->sum('lessons_count');
            $price = (float) ($teacher->session_price ?? 0);
            $calculated = $totalLessons * $price;
    
            $settlement = TeacherSettlement::create([
                'teacher_id' => $teacher->id,
                'treasury_id' => null, // إزالة ربط الخزينة
                'total_lessons' => $totalLessons,
                'session_price' => $price,
                'calculated_amount' => $calculated,
                'settled_amount' => (float)$data['settled_amount'],
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);
    
            if ($logs->isNotEmpty()) {
                AttendanceLog::whereIn('id', $logs->pluck('id'))
                    ->update(['teacher_settlement_id' => $settlement->id]);
            }
    
            // إنشاء معاملة في حساب المعلم فقط (بدون خزينة)
            UserTransaction::create([
                'user_id' => $teacher->id,
                'amount' => (float)$data['settled_amount'],
                'type' => 'credit',
                'description' => 'تسوية حصص (عدد: '.$totalLessons .')',
                'reference_type' => 'settlement',
                'reference_id' => $settlement->id,
                'transaction_id' => null, // لا توجد معاملة خزينة
                'created_by' => auth()->id(),
            ]);
    
            // تحديث رصيد المعلم فقط
            $teacher->increment('balance', (float)$data['settled_amount']);
    
            return redirect()
                ->route('finance.teacher-settlements.show', $settlement)
                ->with('success', 'تمت التسوية وإضافة المبلغ لرصيد المعلم بنجاح.');
        });
    }

    public function list(Request $request)
    {
        $query = TeacherSettlement::with(['teacher:id,name','creator:id,name','treasury:id,name'])
            ->latest();

        // Apply filters
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        if ($request->filled('treasury_id')) {
            $query->where('treasury_id', $request->treasury_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $settlements = $query->paginate(15);

        return view('finance.teacher-settlements.list', compact('settlements'));
    }

    public function show(TeacherSettlement $teacherSettlement)
    {
        $teacherSettlement->load([
            'teacher:id,name',
            'creator:id,name',
            'treasury:id,name',
            'logs:id,user_id,teacher_settlement_id,lessons_count,date,notes'
        ]);
        
        return view('finance.teacher-settlements.show', [
            'settlement' => $teacherSettlement
        ]);
    }
}