<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicUniqueId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class customer_document extends Model
{
    use HasPublicUniqueId;

    public const TYPE_LABELS = [
        'initial_boq' => 'RAB Awal',
        'satisfaction_notes' => 'Catatan Kepuasan',
        'final_boq' => 'RAB Akhir',
    ];

    public const STATUS_LABELS = [
        'pending' => 'Menunggu Persetujuan',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        'changes_requested' => 'Perubahan Diminta',
        'superseded' => 'Digantikan Revisi Baru',
    ];

    protected $fillable = [
        'project_id', 'project_job_document_id', 'approver_id', 'published_by',
        'document_type', 'revision_no', 'public_snapshot', 'financial_snapshot',
        'status', 'decision_comment', 'decided_at', 'decided_by',
    ];

    protected $hidden = ['financial_snapshot'];

    protected function casts(): array
    {
        return [
            'public_snapshot' => 'array',
            'financial_snapshot' => 'encrypted:array',
            'decided_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function is_financial(): bool
    {
        return in_array($this->document_type, ['initial_boq', 'final_boq'], true);
    }
}
