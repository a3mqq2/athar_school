<?php
// app/Models/EmployeeAdvance.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAdvance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'treasury_id',
        'amount',
        'advance_date',
        'monthly_deduction',
        'remaining_amount',
        'status',
        'description',
        'created_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'monthly_deduction' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'advance_date' => 'date'
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function treasury()
    {
        return $this->belongsTo(Treasury::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'advance_id');
    }

    // حساب المبلغ المدفوع
    public function getPaidAmountAttribute()
    {
        return $this->amount - $this->remaining_amount;
    }

    // حساب عدد الأشهر المطلوبة
    public function getMonthsRequiredAttribute()
    {
        if ($this->monthly_deduction <= 0) return 0;
        return ceil($this->remaining_amount / $this->monthly_deduction);
    }


    public function getMonthsToCompleteAttribute()
    {
        if ($this->monthly_deduction <= 0) return 0;
        return ceil($this->amount / $this->monthly_deduction);
    }

    // حساب التاريخ المتوقع لإنهاء السلفة
    public function getExpectedCompletionDateAttribute()
    {
        if ($this->monthly_deduction <= 0) return null;
        
        $monthsNeeded = $this->months_to_complete;
        return $this->advance_date->copy()->addMonths($monthsNeeded);
    }

}