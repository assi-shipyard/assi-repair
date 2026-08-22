<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUniqueId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectDocumentJobPhoto extends Model
{
    use HasPublicUniqueId;

    protected $table = 'project_document_job_photos';

    protected $fillable = [
        'unique_id',
        'project_document_job_id',
        'photo_category',
        'photo_path',
        'caption',
        'taken_at',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'unique_id' => 'string',
            'project_document_job_id' => 'integer',
            'taken_at' => 'datetime',
            'uploaded_by' => 'integer',
        ];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(ProjectDocumentJob::class, 'project_document_job_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
