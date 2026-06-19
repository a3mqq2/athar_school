<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentInstallment extends Model
{
    protected $fillable = [
        'student_id',
        'student_enrollment_id',
        'semester_number',
        'amount_due',
        'paid_amount',
        'due_date',
        'status',
        'installment_type_id',
        'reference',
        'notes',
        'semester_number',
    ];

    protected $casts = [
        'amount_due'   => 'decimal:2',
        'paid_amount'  => 'decimal:2',
        'due_date'     => 'date',
        'semester_number' => 'integer',
    ];

    // العلاقات
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollment::class, 'student_enrollment_id');
    }

    // Scopes
    public function scopeUnpaid($q)  
    { 
        return $q->whereIn('status', ['due','partial','overdue']); 
    }

    public function scopeOverdue($q) 
    { 
        return $q->where('status','overdue'); 
    }

    // Helpers
    public function getRemainingAttribute(): float
    {
        return max(0, (float)$this->amount_due - (float)$this->paid_amount);
    }

    public function markPaid(): void
    {
        $this->status = 'paid';
        $this->paid_amount = $this->amount_due;
        $this->save();
    }

 
    public function getStatusNameAttribute(): string
    {
        $statuses = [
            'due'     => 'مستحق',
            'partial' => 'مدفوع جزئياً',
            'paid'    => 'مدفوع',
            'overdue' => 'متأخر'
        ];

        return $statuses[$this->status] ?? 'غير محدد';
    }

    public function getSemesterNameAttribute(): string
    {
        return $this->semester_number 
            ? "الفصل {$this->semester_number}" 
            : "سنة كاملة";
    }

    public function installmentType(): BelongsTo
    {
        return $this->belongsTo(InstallmentType::class, 'installment_type_id');
    }
}
