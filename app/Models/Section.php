<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'type'
    ];

    protected $casts = [
        'type' => 'string'
    ];

    // Relationships
    public function stages()
    {
        return $this->hasMany(Stage::class);
    }

    // Scopes
    public function scopeLocal($query)
    {
        return $query->where('type', 'local');
    }

    public function scopeInternational($query)
    {
        return $query->where('type', 'international');
    }

    // Accessors
    public function getTypeNameAttribute()
    {
        return $this->type === 'local' ? 'محلي' : 'دولي';
    }
}