<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectDocumentJobMaterial extends Model
{
    protected $table = 'project_document_job_materials';

    protected $fillable = [
        'project_document_job_id',
        'material_name',
        'volume',
        'density',
        'dimension',
        'price',
        'currency',
        'diameter',
        'length',
        'thickness',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'project_document_job_id' => 'integer',
            'volume' => 'decimal:4',
            'density' => 'decimal:6',
            'price' => 'decimal:2',
            'diameter' => 'decimal:4',
            'length' => 'decimal:4',
            'thickness' => 'decimal:4',
        ];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(ProjectDocumentJob::class, 'project_document_job_id');
    }
}
