<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'supervisor_id',
        'check_in_time',
        'lessons_count',
        'date',
        'notes',
        'teacher_settlement_id',
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
        'date' => 'date',
        'lessons_count' => 'integer'
    ];

    /**
     * Get the user that owns the attendance log.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the supervisor who recorded the attendance.
     */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }
}