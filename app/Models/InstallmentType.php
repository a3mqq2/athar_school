<?php
// app/Models/InstallmentType.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InstallmentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
    ];

    protected $casts = [
        'name'   => 'string',
        'status' => 'string',
    ];

    // Status constants
    public const STATUS_ACTIVE   = 'active';
    public const STATUS_INACTIVE = 'inactive';

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeFilter($query, array $filters = [])
    {
        return $query
            ->when(isset($filters['search']) && $filters['search'] != '', function ($q) use ($filters) {
                $term = trim($filters['search']);
                $q->where(function ($qq) use ($term) {
                    $qq->where('name', 'like', "%{$term}%")
                      ->orWhere('id', $term);
                });
            })
            ->when(isset($filters['status']) && in_array($filters['status'], [self::STATUS_ACTIVE, self::STATUS_INACTIVE]), function ($q) use ($filters) {
                $q->where('status', $filters['status']);
            });
    }

    // Helpers
    public function getIsActiveAttribute(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function toggleStatus(): self
    {
        $this->status = $this->is_active ? self::STATUS_INACTIVE : self::STATUS_ACTIVE;
        $this->save();

        return $this;
    }
}
