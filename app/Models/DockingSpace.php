<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUniqueId;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DockingSpace extends Model
{
    use HasPublicUniqueId;

    protected $table = 'docking_spaces';

    protected $fillable = [
        'unique_id',
        'name',
        'max_draft',
        'max_tonnage',
        'max_breadth',
        'max_capacity',
        'location',
        'status',
        'max_length',
        'max_width',
        'max_weight',
    ];

    protected $casts = [
        'unique_id' => 'string',
        'max_length' => 'decimal:2',
        'max_width' => 'decimal:2',
        'max_weight' => 'decimal:2',
    ];

    public function docking_requests(): HasMany
    {
        return $this->hasMany(ProjectDockingRequest::class, 'requested_docking_space_id');
    }

    public function capacity_evaluations(): HasMany
    {
        return $this->hasMany(DockingCapacityEvaluation::class, 'docking_space_id');
    }

    public function docking_occupancies(): HasMany
    {
        return $this->hasMany(DockingOccupancy::class, 'docking_space_id');
    }

    public function current_docking_occupancies(): HasMany
    {
        return $this->docking_occupancies()
            ->where('occupancy_status', 'occupied')
            ->whereNull('undocked_at');
    }

    public function scope_active(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
