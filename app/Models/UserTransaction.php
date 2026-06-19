<?php
// app/Models/UserTransaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'type',
        'description',
        'reference_type',
        'reference_id',
        'transaction_id',
        'created_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // للحصول على المرجع حسب النوع
    public function reference()
    {
        switch ($this->reference_type) {
            case 'advance':
                return $this->belongsTo(EmployeeAdvance::class, 'reference_id');
            case 'settlement':
                return $this->belongsTo(TeacherSettlement::class, 'reference_id');
            default:
                return null;
        }
    }

    // حساب تأثير المعاملة على الرصيد
    public function getBalanceEffectAttribute()
    {
        return $this->type === 'credit' ? $this->amount : -$this->amount;
    }
}