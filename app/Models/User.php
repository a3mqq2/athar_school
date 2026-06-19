<?php

// app/Models/User.php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'hire_date',
        'salary',
        'subject',
        'session_price',
        'balance',
        'job_title',
        'code',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'salary' => 'decimal:2',
        'session_price' => 'decimal:2',
    ];

    public function employeeTransactions()
    {
        return $this->hasMany(Transaction::class, 'employee_id');
    }

    public function getTotalDepositsAttribute()
    {
        return $this->employeeTransactions()
            ->where('transaction_type', 'deposit')
            ->sum('amount');
    }

    public function getTotalWithdrawalsAttribute()
    {
        return $this->employeeTransactions()
            ->where('transaction_type', 'withdrawal')
            ->sum('amount');
    }

    public function advances()
    {
        return $this->hasMany(EmployeeAdvance::class, 'employee_id');
    }

    // الحصول على إجمالي السلف
    public function getTotalAdvancesAttribute()
    {
        return $this->advances()->sum('remaining_amount');
    }


    public function userTransactions()
    {
        return $this->hasMany(UserTransaction::class);
    }

    // حساب الرصيد من المعاملات
    public function calculateBalanceFromTransactions()
    {
        $credits = $this->userTransactions()->where('type', 'credit')->sum('amount');
        $debits = $this->userTransactions()->where('type', 'debit')->sum('amount');
        return $credits - $debits;
    }


    public function hasPermissionNamed(string $permission): bool
    {
        return $this->permissions()->where('name', $permission)->exists();
    }
}
