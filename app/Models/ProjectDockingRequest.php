<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUniqueId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectDockingRequest extends Model
{
    use HasPublicUniqueId;

    protected $table = 'project_docking_requests';

    protected $fillable = [
        'unique_id',
        'project_id',
        'ship_id',
        'requested_by',
        'requested_docking_space_id',
        'requested_start_at',
        'requested_end_at',
        'request_notes',
        'request_status',
        'reviewed_by',
        'reviewed_at',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'unique_id' => 'string',
        'requested_start_at' => 'datetime',
        'requested_end_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function ship(): BelongsTo
    {
        return $this->belongsTo(Ship::class, 'ship_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function requested_docking_space(): BelongsTo
    {
        return $this->belongsTo(DockingSpace::class, 'requested_docking_space_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function capacity_evaluations(): HasMany
    {
        return $this->hasMany(DockingCapacityEvaluation::class, 'project_docking_request_id');
    }

    public function docking_occupancies(): HasMany
    {
        return $this->hasMany(DockingOccupancy::class, 'project_docking_request_id');
    }
}
