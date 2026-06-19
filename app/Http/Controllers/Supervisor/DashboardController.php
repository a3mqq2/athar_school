<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AttendanceLog;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceLogsExport;

class DashboardController extends Controller
{
    public function index() {
        return view('supervisor.dashboard');
    }
    
    /**
     * Display attendance logs
     */
    public function logs(Request $request)
    {
        $query = AttendanceLog::with(['user', 'user.roles', 'supervisor']);
        
        // Date filters
        if ($request->date_from) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        
        if ($request->date_to) {
            $query->whereDate('date', '<=', $request->date_to);
        }
        
        // Search filter
        if ($request->search) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        
        // Role filter
        if ($request->role) {
            $query->whereHas('user.roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }
        
        // Get statistics
        $todayCount = AttendanceLog::whereDate('date', Carbon::today())->count();
        $weekCount = AttendanceLog::whereBetween('date', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ])->count();
        $monthCount = AttendanceLog::whereMonth('date', Carbon::now()->month)
                                   ->whereYear('date', Carbon::now()->year)
                                   ->count();
        $totalLessons = AttendanceLog::whereMonth('date', Carbon::now()->month)
                                     ->whereYear('date', Carbon::now()->year)
                                     ->sum('lessons_count');
        
        $logs = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return view('supervisor.logs', compact(
            'logs', 
            'todayCount', 
            'weekCount', 
            'monthCount', 
            'totalLessons'
        ));
    }
    
    /**
     * Get log details for modal
     */
    public function logDetails($id)
    {
        $log = AttendanceLog::with(['user', 'supervisor'])->findOrFail($id);
        
        return response()->json([
            'user' => [
                'name' => $log->user->name,
                'email' => $log->user->email,
                'code' => $log->user->code
            ],
            'check_in_time' => $log->check_in_time->format('h:i A'),
            'date' => $log->date->format('Y-m-d'),
            'lessons_count' => $log->lessons_count,
            'supervisor' => [
                'name' => $log->supervisor->name
            ],
            'notes' => $log->notes
        ]);
    }
    
    /**
     * Delete log (only today's logs can be deleted)
     */
    public function deleteLog($id)
    {
        $log = AttendanceLog::findOrFail($id);
        
        // Check if log is from today
        if (!$log->created_at->isToday()) {
            return response()->json([
                'success' => false,
                'message' => 'يمكن حذف سجلات اليوم فقط'
            ]);
        }
        
        // Check if user is supervisor who created it or has admin role
        if ($log->supervisor_id != auth()->id() && !auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'ليس لديك صلاحية حذف هذا السجل'
            ]);
        }
        
        $log->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'تم حذف السجل بنجاح'
        ]);
    }
    
    /**
     * Export logs to Excel
     */
    public function exportLogs(Request $request)
    {
        return Excel::download(new AttendanceLogsExport($request), 'attendance_logs_' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Check user by QR code
     */
    public function checkUser(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        $user = User::where('code', $request->code)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'المستخدم غير موجود'
            ]);
        }

        // Check if user has attendance today
        $hasAttendanceToday = AttendanceLog::where('user_id', $user->id)
            ->whereDate('created_at', Carbon::today())
            ->exists();

        // Get user roles
        $roles = $user->roles()->pluck('name')->toArray();
        
        // Check if user is a teacher
        $isTeacher = in_array('teacher', $roles);

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $roles,
                'is_teacher' => $isTeacher,
                'has_attendance_today' => $hasAttendanceToday
            ]
        ]);
    }

    /**
     * Record attendance
     */
    public function recordAttendance(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'lessons_count' => 'nullable|integer|min:0'
        ]);

        $user = User::find($request->user_id);

        // Check if attendance already exists for today
        $existingAttendance = AttendanceLog::where('user_id', $user->id)
            ->whereDate('created_at', Carbon::today())
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'success' => false,
                'message' => 'تم تسجيل الحضور مسبقاً لهذا اليوم'
            ]);
        }

        // Create attendance log
        $attendance = AttendanceLog::create([
            'user_id' => $user->id,
            'supervisor_id' => auth()->id(),
            'check_in_time' => Carbon::now(),
            'lessons_count' => $request->lessons_count,
            'date' => Carbon::today()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الحضور بنجاح'
        ]);
    }
}