<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TreasuryTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_treasury_id',
        'to_treasury_id',
        'amount',
        'description',
        'reference_number',
        'user_id'
    ];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    // العلاقات
    public function fromTreasury()
    {
        return $this->belongsTo(Treasury::class, 'from_treasury_id');
    }

    public function toTreasury()
    {
        return $this->belongsTo(Treasury::class, 'to_treasury_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // معاملات التحويل المرتبطة
    public function withdrawalTransaction()
    {
        return $this->hasOne(Transaction::class, 'treasury_transfer_id')
                   ->where('transaction_type', 'withdrawal');
    }

    public function depositTransaction()
    {
        return $this->hasOne(Transaction::class, 'treasury_transfer_id')
                   ->where('transaction_type', 'deposit');
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2);
    }

    // Boot method للتعامل مع المعاملات
    protected static function boot()
    {
        parent::boot();

        static::created(function ($transfer) {
            $transfer->createTransactions();
        });

        static::deleted(function ($transfer) {
            // حذف المعاملات المرتبطة
            Transaction::where('treasury_transfer_id', $transfer->id)->delete();

            // إعادة حساب أرصدة الخزائن
            $transfer->fromTreasury?->recalculateBalance();
            $transfer->toTreasury?->recalculateBalance();
        });
    }

    // إنشاء معاملات التحويل
    public function createTransactions()
    {
        DB::transaction(function () {
            // تحديد تصنيفات النظام للتحويل
            $withdrawalType = TransactionType::firstOrCreate(
                ['name' => 'تحويل - سحب', 'type' => 'withdrawal', 'for_system' => true]
            );

            $depositType = TransactionType::firstOrCreate(
                ['name' => 'تحويل - إيداع', 'type' => 'deposit', 'for_system' => true]
            );

            // إنشاء معاملة السحب من الخزنة المرسلة
            Transaction::create([
                'payee_name' => 'تحويل إلى: ' . $this->toTreasury->name,
                'amount' => $this->amount,
                'description' => $this->description ?? 'تحويل بين الخزائن',
                'document_number' => $this->reference_number,
                'transaction_type' => 'withdrawal',
                'transaction_type_id' => $withdrawalType->id,
                'treasury_id' => $this->from_treasury_id,
                'user_id' => $this->user_id,
                'treasury_transfer_id' => $this->id
            ]);

            // إنشاء معاملة الإيداع في الخزنة المستقبلة
            Transaction::create([
                'payee_name' => 'تحويل من: ' . $this->fromTreasury->name,
                'amount' => $this->amount,
                'description' => $this->description ?? 'تحويل بين الخزائن',
                'document_number' => $this->reference_number,
                'transaction_type' => 'deposit',
                'transaction_type_id' => $depositType->id,
                'treasury_id' => $this->to_treasury_id,
                'user_id' => $this->user_id,
                'treasury_transfer_id' => $this->id
            ]);

            // تحديث أرصدة الخزائن مباشرة
            $this->fromTreasury->recalculateBalance();
            $this->toTreasury->recalculateBalance();
        });
    }

    // Scopes
    public function scopeFromTreasury($query, $treasuryId)
    {
        return $query->where('from_treasury_id', $treasuryId);
    }

    public function scopeToTreasury($query, $treasuryId)
    {
        return $query->where('to_treasury_id', $treasuryId);
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }
}
