<?php
// app/Models/FeeStructure.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeStructure extends Model
{
    protected $fillable = [
        'section_type', // local | international
        'stage_id',
        'grade_id',
        'amount',
        'year_amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'year_amount' => 'decimal:2',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }
}
