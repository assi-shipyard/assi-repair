<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUniqueId;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DockingOccupancy extends Model
{
    use HasPublicUniqueId;

    protected $table = 'docking_occupancies';

    protected $fillable = [
        'unique_id',
        'project_id',
        'ship_id',
        'docking_space_id',
        'project_docking_request_id',
        'docked_at',
        'estimated_undock_at',
        'undocked_at',
        'occupancy_status',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'unique_id' => 'string',
        'docked_at' => 'datetime',
        'estimated_undock_at' => 'datetime',
        'undocked_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function ship(): BelongsTo
    {
        return $this->belongsTo(Ship::class, 'ship_id');
    }

    public function docking_space(): BelongsTo
    {
        return $this->belongsTo(DockingSpace::class, 'docking_space_id');
    }

    public function docking_request(): BelongsTo
    {
        return $this->belongsTo(ProjectDockingRequest::class, 'project_docking_request_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function floating_repair_histories(): HasMany
    {
        return $this->hasMany(FloatingRepairHistory::class, 'docking_occupancy_id');
    }

    public function scope_current(Builder $query): Builder
    {
        return $query
            ->where('occupancy_status', 'occupied')
            ->whereNull('undocked_at');
    }
}
