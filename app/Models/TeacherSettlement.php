<?php
// app/Models/TeacherSettlement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeacherSettlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'treasury_id',
        'total_lessons',
        'session_price',
        'calculated_amount',
        'settled_amount',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'total_lessons' => 'integer',
        'session_price' => 'decimal:2',
        'calculated_amount' => 'decimal:2',
        'settled_amount' => 'decimal:2',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class,'teacher_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function logs()
    {
        return $this->hasMany(AttendanceLog::class,'teacher_settlement_id');
    }

    public function treasury()
    {
        return $this->belongsTo(Treasury::class);
    }
}
