<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class OrganizationalUnit extends Model
{
    protected $fillable = [
        'name',
        'code',
        'type',
        'parent_id',
    ];

    protected $casts = [
        'parent_id' => 'integer',
    ];

    // Parent organizational unit
    public function parent(): BelongsTo
    {
        return $this->belongsTo(OrganizationalUnit::class, 'parent_id');
    }

    // Children organizational units
    public function children(): HasMany
    {
        return $this->hasMany(OrganizationalUnit::class, 'parent_id');
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class, 'organizational_unit_id');
    }

    public function employees(): HasManyThrough
    {
        return $this->hasManyThrough(Employee::class, Position::class, 'organizational_unit_id', 'position_id', 'id', 'id');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'directorate' => 'Direktorat',
            'division' => 'Divisi',
            'subdivision' => 'Subdivisi',
            'workshop' => 'Workshop / Bengkel',
            default => ucfirst((string) $this->type),
        };
    }
}
