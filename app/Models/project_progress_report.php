<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicUniqueId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class project_progress_report extends Model
{
    use HasPublicUniqueId;

    public const STATUS_LABELS = [
        'draft' => 'Draft',
        'submitted' => 'Menunggu Persetujuan',
        'returned' => 'Dikembalikan',
        'approved' => 'Disetujui',
    ];

    public const EDITABLE_STATUSES = ['draft', 'returned'];

    protected $fillable = [
        'project_id',
        'project_job_document_id',
        'report_date',
        'status',
        'notes',
        'reported_progress',
        'created_by_user_id',
        'submitted_by_user_id',
        'submitted_at',
        'leader_approved_by_employee_id',
        'leader_approved_at',
        'ppc_approved_by_employee_id',
        'ppc_approved_at',
        'approved_at',
        'returned_by_user_id',
        'returned_at',
        'return_reason',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'reported_progress' => 'decimal:2',
            'submitted_at' => 'datetime',
            'leader_approved_at' => 'datetime',
            'ppc_approved_at' => 'datetime',
            'approved_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(ProjectJobDocument::class, 'project_job_document_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(project_progress_entry::class, 'project_progress_report_id');
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function leader_approved_by(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'leader_approved_by_employee_id');
    }

    public function ppc_approved_by(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'ppc_approved_by_employee_id');
    }

    public function is_editable(): bool
    {
        return in_array($this->status, self::EDITABLE_STATUSES, true);
    }
}
