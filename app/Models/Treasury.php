<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Treasury extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'opening_balance',
        'current_balance',
        'responsible_user_id'
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2'
    ];

    // العلاقات الموجودة
    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    // العلاقات الجديدة
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function deposits()
    {
        return $this->hasMany(Transaction::class)->deposits();
    }

    public function withdrawals()
    {
        return $this->hasMany(Transaction::class)->withdrawals();
    }

    // إعادة حساب الرصيد
    public function recalculateBalance()
    {
        $deposits = $this->transactions()->deposits()->sum('amount');
        $withdrawals = $this->transactions()->withdrawals()->sum('amount');
        
        $this->update([
            'current_balance' => $this->opening_balance + $deposits - $withdrawals
        ]);
    }

    // الحصول على إجمالي الإيداعات
    public function getTotalDepositsAttribute()
    {
        return $this->transactions()->deposits()->sum('amount');
    }

    // الحصول على إجمالي السحوبات
    public function getTotalWithdrawalsAttribute()
    {
        return $this->transactions()->withdrawals()->sum('amount');
    }

    // الحصول على عدد المعاملات
    public function getTransactionsCountAttribute()
    {
        return $this->transactions()->count();
    }
}