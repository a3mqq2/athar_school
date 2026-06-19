<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'for_system'
    ];

    protected $casts = [
        'for_system' => 'boolean'
    ];

    // العلاقات
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // Scopes
    public function scopeForUsers($query)
    {
        return $query->where('for_system', false);
    }

    public function scopeForSystem($query)
    {
        return $query->where('for_system', true);
    }

    public function scopeDeposits($query)
    {
        return $query->where('type', 'deposit');
    }

    public function scopeWithdrawals($query)
    {
        return $query->where('type', 'withdrawal');
    }

    // Accessors
    public function getTypeNameAttribute()
    {
        return $this->type === 'deposit' ? 'إيداع' : 'سحب';
    }
}