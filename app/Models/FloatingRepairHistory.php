<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FloatingRepairHistory extends Model
{
    protected $table = 'floating_repair_histories';

    protected $fillable = [
        'project_id',
        'ship_id',
        'docking_occupancy_id',
        'floating_started_at',
        'floating_completed_at',
        'floating_status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'floating_started_at' => 'datetime',
        'floating_completed_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function ship(): BelongsTo
    {
        return $this->belongsTo(Ship::class, 'ship_id');
    }

    public function docking_occupancy(): BelongsTo
    {
        return $this->belongsTo(DockingOccupancy::class, 'docking_occupancy_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scope_active(Builder $query): Builder
    {
        return $query->where('floating_status', 'active');
    }
}
