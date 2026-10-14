<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicUniqueId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectProgressEntry extends Model
{
    use HasPublicUniqueId;

    protected $fillable = [
        'project_progress_report_id',
        'project_document_job_id',
        'job_lineage_uuid',
        'job_name',
        'previous_percent',
        'progress_percent',
        'weight_percent',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'previous_percent' => 'decimal:2',
            'progress_percent' => 'decimal:2',
            'weight_percent' => 'decimal:4',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(ProjectProgressReport::class, 'project_progress_report_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(ProjectDocumentJob::class, 'project_document_job_id');
    }
}
