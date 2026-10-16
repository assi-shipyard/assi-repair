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
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
        'visit_stage',
        'arrived_at',
        'arrived_recorded_by',
        'arrival_notes',
        'administration_completed_at',
        'administration_completed_by',
        'administration_notes',
        'departed_at',
        'departed_recorded_by',
        'departure_notes',
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
        'cancelled_at' => 'datetime',
        'arrived_at' => 'datetime',
        'administration_completed_at' => 'datetime',
        'departed_at' => 'datetime',
    ];

    public const PENDING_STATUSES = ['submitted', 'engineering_approved', 'awaiting_marketing'];

    public const STATUS_LABELS = [
        'submitted' => 'Menunggu Engineering',
        'engineering_approved' => 'Menunggu Produksi',
        'awaiting_marketing' => 'Menunggu Konfirmasi Marketing',
        'approved' => 'Disetujui',
        'completed' => 'Selesai',
        'rejected' => 'Ditolak',
        'cancelled' => 'Dibatalkan',
    ];

    public const VISIT_STAGE_LABELS = [
        'awaiting_arrival' => 'Menunggu kedatangan',
        'arrived' => 'Tiba di galangan',
        'docked' => 'Di dalam dock',
        'floating' => 'Pekerjaan atas air',
        'ready_to_depart' => 'Siap keluar galangan',
        'departed' => 'Keluar galangan',
    ];

    public const IN_YARD_STAGES = ['arrived', 'docked', 'floating', 'ready_to_depart'];

    public function status_label(): string
    {
        if ($this->request_status === 'approved' && $this->visit_stage !== null) {
            return self::VISIT_STAGE_LABELS[$this->visit_stage] ?? self::STATUS_LABELS['approved'];
        }

        return self::STATUS_LABELS[$this->request_status] ?? $this->request_status;
    }

    public function is_in_yard(): bool
    {
        return $this->request_status === 'approved' && in_array($this->visit_stage, self::IN_YARD_STAGES, true);
    }

    // Before the ship enters the dock, the request can still be cancelled or rescheduled.
    public function is_before_docking(): bool
    {
        return $this->request_status === 'approved' && in_array($this->visit_stage, [null, 'awaiting_arrival', 'arrived'], true);
    }

    public function is_cancellable(): bool
    {
        return in_array($this->request_status, self::PENDING_STATUSES, true) || $this->is_before_docking();
    }

    public function is_managed_by(User $user): bool
    {
        return (int) $this->requested_by === (int) $user->id || $user->hasRole('admin');
    }

    /**
     * Position in the 7-step journey: request, engineering, production, arrival, dock, undock/floating, departure.
     */
    public function progress_step(): int
    {
        return match (true) {
            $this->request_status === 'completed' => 7,
            $this->request_status === 'submitted' => 1,
            in_array($this->request_status, ['engineering_approved', 'awaiting_marketing'], true) => 2,
            $this->request_status !== 'approved' => 0,
            $this->visit_stage === 'arrived' => 4,
            $this->visit_stage === 'docked' => 5,
            in_array($this->visit_stage, ['floating', 'ready_to_depart'], true) => 6,
            default => 3,
        };
    }

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

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function arrival_recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'arrived_recorded_by');
    }

    public function administration_confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'administration_completed_by');
    }

    public function departure_recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'departed_recorded_by');
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
