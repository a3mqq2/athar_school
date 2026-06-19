<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'stage_id',
        'name'
    ];

    protected $casts = [
        'stage_id' => 'integer'
    ];

    // Relationships
    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }

    public function classrooms()
    {
        return $this->hasMany(Classroom::class)->orderBy('name');
    }

    // Accessors
    public function getTotalClassroomsAttribute()
    {
        return $this->classrooms()->count();
    }

    public function getSectionTypeAttribute()
    {
        return $this->stage->section->type ?? null;
    }

    // Scopes
    public function scopeWithStage($query, $stageId)
    {
        return $query->where('stage_id', $stageId);
    }

    public function scopeInSection($query, $sectionType)
    {
        return $query->whereHas('stage.section', function($q) use ($sectionType) {
            $q->where('type', $sectionType);
        });
    }
}
