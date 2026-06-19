<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'grade_id',
        'name'
    ];

    protected $casts = [
        'grade_id' => 'integer'
    ];

    // Relationships
    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    // Accessors
    public function getStageNameAttribute()
    {
        return $this->grade->stage->name ?? null;
    }

    public function getSectionTypeAttribute()
    {
        return $this->grade->stage->section->type ?? null;
    }

    public function getFullNameAttribute()
    {
        return "{$this->stage_name} - {$this->grade->name} - {$this->name}";
    }

    // Scopes
    public function scopeWithGrade($query, $gradeId)
    {
        return $query->where('grade_id', $gradeId);
    }

    public function scopeInStage($query, $stageId)
    {
        return $query->whereHas('grade', function($q) use ($stageId) {
            $q->where('stage_id', $stageId);
        });
    }

    public function scopeInSection($query, $sectionType)
    {
        return $query->whereHas('grade.stage.sectionObj', function($q) use ($sectionType) {
            $q->where('type', $sectionType);
        });
    }
}
