<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'section_id',
        'section'
    ];

    protected $casts = [
        'section_id' => 'integer'
    ];

    public function sectionObj()
    {
        return $this->belongsTo(Section::class,'section_id');
    }

    public function grades()
    {
        return $this->hasMany(Grade::class)->orderBy('name');
    }

    public function getTotalGradesAttribute()
    {
        return $this->grades()->count();
    }

    public function getTotalClassroomsAttribute()
    {
        return $this->grades()->withCount('classrooms')->get()->sum('classrooms_count');
    }

    // Scopes
    public function scopeLocal($query)
    {
        return $query->whereHas('sectionObj', function($q) {
            $q->where('type', 'local');
        });
    }

    public function scopeInternational($query)
    {
        return $query->whereHas('sectionObj', function($q) {
            $q->where('type', 'international');
        });
    }

    public function scopeWithSection($query, $sectionType)
    {
        return $query->whereHas('sectionObj', function($q) use ($sectionType) {
            $q->where('type', $sectionType);
        });
    }
}