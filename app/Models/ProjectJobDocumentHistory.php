<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectJobDocumentHistory extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'project_job_document_histories';

    protected $fillable = [
        'project_job_document_id',
        'action',
        'actor_id',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'project_job_document_id' => 'integer',
            'actor_id' => 'integer',
            'payload' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(ProjectJobDocument::class, 'project_job_document_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
