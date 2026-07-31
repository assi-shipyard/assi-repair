<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class ProjectJobDocument extends Model
{
    public const TYPE_REPAIR_LIST = 'repair_list';
    public const TYPE_INITIAL_BOQ = 'initial_boq';
    public const TYPE_SATISFACTION_NOTES = 'satisfaction_notes';
    public const TYPE_FINAL_BOQ = 'final_boq';
    public const TYPE_DOCKING_REPORT = 'docking_report';

    protected $table = 'project_job_documents';

    protected $fillable = [
        'project_id',
        'document_type',
        'document_number',
        'revision_no',
        'status',
        'source_document_id',
        'source_revision_no',
        'prepared_by_employee_id',
        'approved_by_employee_id',
        'approved_at',
        'locked_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'project_id' => 'integer',
            'revision_no' => 'integer',
            'source_document_id' => 'integer',
            'source_revision_no' => 'integer',
            'prepared_by_employee_id' => 'integer',
            'approved_by_employee_id' => 'integer',
            'approved_at' => 'datetime',
            'locked_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function source_document(): BelongsTo
    {
        return $this->belongsTo(self::class, 'source_document_id');
    }

    public function prepared_by(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'prepared_by_employee_id');
    }

    public function approved_by(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by_employee_id');
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(ProjectDocumentJob::class, 'project_job_document_id')->orderBy('sort_order');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ProjectJobDocumentHistory::class, 'project_job_document_id');
    }

    public static function cloneWithChildren(self $source, array $attributes = []): self
    {
        return DB::transaction(function () use ($source, $attributes): self {
            $target = $source->replicate([
                'source_document_id',
                'source_revision_no',
            ]);

            $target->source_document_id = $source->id;
            $target->source_revision_no = $source->revision_no;
            $target->revision_no = $attributes['revision_no'] ?? ($source->revision_no + 1);

            foreach ($attributes as $key => $value) {
                $target->{$key} = $value;
            }

            $target->save();

            $source->loadMissing('jobs.materials', 'jobs.photos');

            foreach ($source->jobs as $job) {
                $newJob = $job->replicate(['project_job_document_id', 'source_job_id']);
                $newJob->project_job_document_id = $target->id;
                $newJob->source_job_id = $job->id;
                $newJob->save();

                foreach ($job->materials as $material) {
                    $newMaterial = $material->replicate(['project_document_job_id']);
                    $newMaterial->project_document_job_id = $newJob->id;
                    $newMaterial->save();
                }

                foreach ($job->photos as $photo) {
                    $newPhoto = $photo->replicate(['project_document_job_id']);
                    $newPhoto->project_document_job_id = $newJob->id;
                    $newPhoto->save();
                }
            }

            $target->histories()->create([
                'action' => 'copied',
                'actor_id' => $attributes['actor_id'] ?? null,
                'payload' => [
                    'source_document_id' => $source->id,
                    'source_revision_no' => $source->revision_no,
                ],
            ]);

            return $target;
        });
    }
}
