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
        'engineering_approved_by',
        'engineering_approved_at',
        'engineering_notes',
        'production_approved_by',
        'production_approved_at',
        'production_notes',
        'rejection_stage',
        'recommended_docking_space_id',
        'production_recommendation',
        'recommended_start_at',
        'recommended_end_at',
        'marketing_responded_by',
        'marketing_responded_at',
        'marketing_response',
        'marketing_notes',
    ];

    protected $casts = [
        'unique_id' => 'string',
        'requested_start_at' => 'datetime',
        'requested_end_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'engineering_approved_at' => 'datetime',
        'production_approved_at' => 'datetime',
        'recommended_start_at' => 'datetime',
        'recommended_end_at' => 'datetime',
        'marketing_responded_at' => 'datetime',
    ];

    public const PENDING_STATUSES = ['submitted', 'engineering_approved', 'awaiting_marketing'];

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

    public function recommended_docking_space(): BelongsTo
    {
        return $this->belongsTo(DockingSpace::class, 'recommended_docking_space_id');
    }

    // Engineering's recommendation overrides the dock originally requested.
    public function effective_docking_space_id(): ?int
    {
        return $this->recommended_docking_space_id ?? $this->requested_docking_space_id;
    }

    public function marketing_responder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marketing_responded_by');
    }

    public function postponements(): HasMany
    {
        return $this->hasMany(DockingRequestPostponement::class, 'project_docking_request_id')->latest();
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

    public function engineering_approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'engineering_approved_by');
    }

    public function production_approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'production_approved_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(DockingRequestDocument::class, 'project_docking_request_id');
    }
}
