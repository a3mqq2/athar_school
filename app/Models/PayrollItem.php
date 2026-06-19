<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollItem extends Model
{
    protected $fillable = [
        'payroll_id',
        'user_id',
        'base_salary',
        'bonus',
        'deduction',
        'net_amount',
        'notes',
        'advance_deduction', // جديد
        'final_amount',      // جديد
    ];

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
