<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectDocumentJob extends Model
{
    protected $table = 'project_document_jobs';

    protected $fillable = [
        'project_job_document_id',
        'source_job_id',
        'job_name',
        'job_volume_estimated',
        'responsible_kind',
        'responsible_name',
        'est_start_date',
        'est_finish_date',
        'est_duration_days',
        'progress_percent',
        'est_price',
        'est_currency',
        'job_weight_percent',
        'actual_start_date',
        'actual_finish_date',
        'actual_duration_days',
        'actual_cost',
        'actual_currency',
        'actual_volume',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'project_job_document_id' => 'integer',
            'source_job_id' => 'integer',
            'job_volume_estimated' => 'decimal:4',
            'est_start_date' => 'date',
            'est_finish_date' => 'date',
            'est_duration_days' => 'integer',
            'progress_percent' => 'decimal:2',
            'est_price' => 'decimal:2',
            'job_weight_percent' => 'decimal:2',
            'actual_start_date' => 'date',
            'actual_finish_date' => 'date',
            'actual_duration_days' => 'integer',
            'actual_cost' => 'decimal:2',
            'actual_volume' => 'decimal:4',
            'sort_order' => 'integer',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(ProjectJobDocument::class, 'project_job_document_id');
    }

    public function source_job(): BelongsTo
    {
        return $this->belongsTo(self::class, 'source_job_id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(ProjectDocumentJobMaterial::class, 'project_document_job_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ProjectDocumentJobPhoto::class, 'project_document_job_id');
    }
}
