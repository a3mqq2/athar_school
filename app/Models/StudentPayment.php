<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPayment extends Model
{
    protected $fillable = [
        'student_id',
        'student_installment_id',
        'treasury_id',
        'amount',
        'payment_method',
        'transaction_id',
        'created_by',
        'bank_name',
        'account_number',
        'type',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function installment(): BelongsTo
    {
        return $this->belongsTo(StudentInstallment::class, 'student_installment_id');
    }

    public function treasury(): BelongsTo
    {
        return $this->belongsTo(Treasury::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
