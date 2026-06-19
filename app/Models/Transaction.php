<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'payee_name',
        'amount',
        'description',
        'document_number',
        'transaction_type',
        'transaction_type_id',
        'treasury_id',
        'user_id',
        'employee_id',
        'student_payment_id',
        'treasury_transfer_id',
        'payment_method',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_method' => 'string',
    ];

    public function treasury()
    {
        return $this->belongsTo(Treasury::class);
    }

    public function transactionType()
    {
        return $this->belongsTo(TransactionType::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function treasuryTransfer()
    {
        return $this->belongsTo(TreasuryTransfer::class);
    }

    public function scopeDeposits($query)
    {
        return $query->where('transaction_type', 'deposit');
    }

    public function scopeWithdrawals($query)
    {
        return $query->where('transaction_type', 'withdrawal');
    }

    public function scopeByTreasury($query, $treasuryId)
    {
        return $query->where('treasury_id', $treasuryId);
    }

    public function scopeByType($query, $typeId)
    {
        return $query->where('transaction_type_id', $typeId);
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    public function scopeTransfers($query)
    {
        return $query->whereNotNull('treasury_transfer_id');
    }

    public function scopeRegular($query)
    {
        return $query->whereNull('treasury_transfer_id');
    }

    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    public function getTransactionTypeNameAttribute()
    {
        return $this->transaction_type === 'deposit' ? 'إيداع' : 'سحب';
    }

    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2);
    }

    public function getIsTransferAttribute()
    {
        return !is_null($this->treasury_transfer_id);
    }

    public function getPaymentMethodNameAttribute()
    {
        return match ($this->payment_method) {
            'cash' => 'نقدي',
            'credit_card' => 'بطاقة ائتمان',
            'bank' => 'تحويل بنكي',
            'check' => 'شيك',
            'mobile_payment' => 'دفع عبر الهاتف',
            default => 'غير محدد',
        };
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($transaction) {
            if (!$transaction->treasury_transfer_id) {
                $transaction->updateTreasuryBalance();
            }
        });

        static::updated(function ($transaction) {
            $transaction->treasury->recalculateBalance();
        });

        static::deleted(function ($transaction) {
            $transaction->treasury->recalculateBalance();
        });
    }

    private function updateTreasuryBalance()
    {
        $treasury = $this->treasury;
        
        if ($this->transaction_type === 'deposit') {
            $treasury->increment('current_balance', $this->amount);
        } else {
            $treasury->decrement('current_balance', $this->amount);
        }
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function advance()
    {
        return $this->belongsTo(EmployeeAdvance::class, 'employee_id', 'employee_id');
    }

    public function studentPayment()
    {
        return $this->belongsTo(StudentPayment::class);
    }
}
