<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUniqueId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class OrganizationalUnit extends Model
{
    use HasPublicUniqueId;

    public const TYPE_LABELS = [
        'ceo' => 'CEO',
        'chrgao' => 'CHRGAO',
        'cfo' => 'CFO',
        'cpo' => 'CPO',
        'directorate' => 'Direktorat',
        'division' => 'Divisi',
        'bureau' => 'Biro',
        'subdivision' => 'Subdivisi',
        'workshop' => 'Workshop / Bengkel',
    ];

    public const ALLOWED_PARENT_TYPES = [
        'ceo' => [],
        'chrgao' => ['ceo'],
        'cfo' => ['ceo'],
        'cpo' => ['ceo'],
        'directorate' => ['chrgao', 'cfo', 'cpo'],
        'division' => ['directorate'],
        'bureau' => ['directorate'],
        'subdivision' => ['division'],
        'workshop' => ['division', 'cpo'],
    ];

    protected $fillable = [
        'unique_id',
        'name',
        'code',
        'type',
        'parent_id',
    ];

    protected $casts = [
        'unique_id' => 'string',
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
        return self::TYPE_LABELS[$this->type] ?? ucfirst((string) $this->type);
    }
}
