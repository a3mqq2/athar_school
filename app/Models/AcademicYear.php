<?php

// app/Models/AcademicYear.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $fillable = ['name','start_date','end_date','is_current'];

    protected $casts = [
        'is_current' => 'boolean',
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function scopeCurrent($q) { return $q->where('is_current', true); }



}
