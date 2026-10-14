<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUniqueId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ProjectDocumentJob extends Model
{
    use HasPublicUniqueId;

    protected $table = 'project_document_jobs';

    protected $fillable = [
        'unique_id',
        'project_job_document_id',
        'source_job_id',
        'lineage_uuid',
        'project_job_group_id',
        'parent_job_id',
        'price_book_item_id',
        'calculation_rule',
        'job_name',
        'job_volume_estimated',
        'unit',
        'unit_price',
        'minimum_quantity',
        'billed_quantity',
        'calculation',
        'notes',
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

    protected static function booted(): void
    {
        static::creating(function (self $job): void {
            $job->lineage_uuid ??= (string) Str::uuid();
        });
    }

    protected function casts(): array
    {
        return [
            'unique_id' => 'string',
            'project_job_document_id' => 'integer',
            'source_job_id' => 'integer',
            'project_job_group_id' => 'integer',
            'parent_job_id' => 'integer',
            'price_book_item_id' => 'integer',
            'job_volume_estimated' => 'decimal:4',
            'unit_price' => 'decimal:2',
            'minimum_quantity' => 'decimal:4',
            'billed_quantity' => 'decimal:4',
            'calculation' => 'array',
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

    public function job_group(): BelongsTo
    {
        return $this->belongsTo(ProjectJobGroup::class, 'project_job_group_id');
    }

    public function parent_job(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_job_id');
    }

    public function child_jobs(): HasMany
    {
        return $this->hasMany(self::class, 'parent_job_id')->orderBy('sort_order');
    }

    public function price_book_item(): BelongsTo
    {
        return $this->belongsTo(PriceBookItem::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(ProjectDocumentJobMaterial::class, 'project_document_job_id')->orderBy('sort_order')->orderBy('id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ProjectDocumentJobPhoto::class, 'project_document_job_id');
    }
}
