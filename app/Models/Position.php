<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUniqueId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    use HasPublicUniqueId;

    public const CATEGORY_LEVELS = [
        'c_suite' => 1,
        'manager' => 2,
        'head_of_bureau' => 2,
        'assistant_manager' => 3,
        'supervisor_staff' => 4,
        'pelaksana' => 5,
    ];

    public const CATEGORY_OPTIONS = [
        'c_suite' => 'Eksekutif C-Suite (CEO/CHRGAO/CFO/CPO)',
        'manager' => 'Manajer Divisi',
        'head_of_bureau' => 'Kepala Biro',
        'assistant_manager' => 'Asisten Manajer / Kepala Bengkel',
        'supervisor_staff' => 'Supervisor/Staf',
        'pelaksana' => 'Pelaksana',
    ];

    protected $fillable = [
        'unique_id',
        'name',
        'level',
        'category',
        'is_head_position',
        'code',
        'organizational_unit_id',
    ];

    protected $casts = [
        'unique_id' => 'string',
    ];

    public function organizational_unit(): BelongsTo
    {
        return $this->belongsTo(OrganizationalUnit::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public static function category_options(): array
    {
        return self::CATEGORY_OPTIONS;
    }

    public static function level_for_category(string $category): int
    {
        return self::CATEGORY_LEVELS[$category] ?? 5;
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORY_OPTIONS[$this->category] ?? $this->category;
    }
}
