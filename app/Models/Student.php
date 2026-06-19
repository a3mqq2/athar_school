<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [
        'name',
        'gender',
        'registration_number',
        'national_id',
        'nationality',
        'parent_name',
        'phone',
        'phone2',
        'mother_name',
        'address',
        'notes',
        'status',
    ];

    protected $casts = [
        'gender' => 'string',
        'status' => 'string',
    ];

    /**
     * سجلّات تسجيل الطالب عبر السنوات.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    /**
     * التسجيل الحالي (يربط بالسنة الدراسية الحالية).
     */
    public function currentEnrollment()
    {
        return $this->hasOne(StudentEnrollment::class)
            ->whereHas('academicYear', fn($q) => $q->where('is_current', true));
    }

    /** 
     * أقساط الطالب.
     */
    public function installments(): HasMany
    {
        return $this->hasMany(StudentInstallment::class);
    }

    public function getStatuesAttribute(): array
    {
        return [
            'active' => 'نشط',
            'inactive' => 'غير نشط',
            'graduated' => 'متخرج',
            'transferred' => 'منقول',
        ];
    }
}
