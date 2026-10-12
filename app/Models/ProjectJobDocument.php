<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUniqueId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class ProjectJobDocument extends Model
{
    use HasPublicUniqueId;

    public const TYPE_REPAIR_LIST = 'repair_list';

    public const TYPE_INITIAL_BOQ = 'initial_boq';

    public const TYPE_SATISFACTION_NOTES = 'satisfaction_notes';

    public const TYPE_FINAL_BOQ = 'final_boq';

    public const TYPE_DOCKING_REPORT = 'docking_report';

    public const TYPE_LABELS = [
        self::TYPE_REPAIR_LIST => 'Repair List',
        self::TYPE_INITIAL_BOQ => 'RAB Awal',
        self::TYPE_SATISFACTION_NOTES => 'Satisfaction Notes',
        self::TYPE_DOCKING_REPORT => 'Docking Report',
        self::TYPE_FINAL_BOQ => 'RAB Akhir',
    ];

    // Which job-list columns each phase works with; every phase shares the same tables.
    // Satisfaction Notes keeps internal prices only to weight jobs added during repair; its export hides them,
    // and its progress comes from approved daily reports instead of manual edits.
    public const PHASE_FEATURES = [
        self::TYPE_REPAIR_LIST => ['prices' => false, 'export_prices' => false, 'progress' => false, 'progress_reports' => false],
        self::TYPE_INITIAL_BOQ => ['prices' => true, 'export_prices' => true, 'progress' => false, 'progress_reports' => false],
        self::TYPE_SATISFACTION_NOTES => ['prices' => true, 'export_prices' => false, 'progress' => true, 'progress_reports' => true],
        self::TYPE_DOCKING_REPORT => ['prices' => false, 'export_prices' => false, 'progress' => true, 'progress_reports' => false],
        self::TYPE_FINAL_BOQ => ['prices' => true, 'export_prices' => true, 'progress' => true, 'progress_reports' => false],
    ];

    // The phase whose latest job list seeds a new document of the key phase.
    public const PREVIOUS_PHASE = [
        self::TYPE_INITIAL_BOQ => self::TYPE_REPAIR_LIST,
        self::TYPE_SATISFACTION_NOTES => self::TYPE_INITIAL_BOQ,
        self::TYPE_DOCKING_REPORT => self::TYPE_INITIAL_BOQ,
        self::TYPE_FINAL_BOQ => self::TYPE_SATISFACTION_NOTES,
    ];

    protected $table = 'project_job_documents';

    protected $fillable = [
        'unique_id',
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
        'revision_reason',
        'submitted_at',
        'submitted_by_user_id',
        'superseded_at',
    ];

    protected function casts(): array
    {
        return [
            'unique_id' => 'string',
            'project_id' => 'integer',
            'revision_no' => 'integer',
            'source_document_id' => 'integer',
            'source_revision_no' => 'integer',
            'prepared_by_employee_id' => 'integer',
            'approved_by_employee_id' => 'integer',
            'approved_at' => 'datetime',
            'locked_at' => 'datetime',
            'submitted_at' => 'datetime',
            'superseded_at' => 'datetime',
            'minimum_overrides' => 'array',
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

    public function is_editable(): bool
    {
        return $this->status === 'draft';
    }

    public function features(): array
    {
        return self::PHASE_FEATURES[$this->document_type] ?? self::PHASE_FEATURES[self::TYPE_REPAIR_LIST];
    }

    public function can_be_edited_by(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        $permission = match ($this->document_type) {
            self::TYPE_INITIAL_BOQ => 'edit-project-quotation',
            self::TYPE_FINAL_BOQ => 'edit-project-realization',
            self::TYPE_SATISFACTION_NOTES => 'edit-project-satisfaction-note',
            default => 'edit-project',
        };

        return $user->hasRole('admin') || $user->can($permission) || $user->can('edit-project');
    }

    public static function cloneWithChildren(self $source, array $attributes = []): self
    {
        return DB::transaction(function () use ($source, $attributes): self {
            $actor_id = $attributes['actor_id'] ?? null;
            $with_photos = $attributes['with_photos'] ?? true;
            unset($attributes['actor_id'], $attributes['with_photos']);

            $target = $source->replicate([
                'unique_id',
                'source_document_id',
                'source_revision_no',
                'approved_by_employee_id',
                'approved_at',
                'locked_at',
                'submitted_at',
                'submitted_by_user_id',
                'superseded_at',
                'revision_reason',
            ]);
            $target->status = 'draft';

            $target->source_document_id = $source->id;
            $target->source_revision_no = $source->revision_no;
            $target->revision_no = $attributes['revision_no'] ?? ($source->revision_no + 1);

            foreach ($attributes as $key => $value) {
                $target->{$key} = $value;
            }

            $target->save();

            $source->loadMissing($with_photos ? ['jobs.materials', 'jobs.photos'] : ['jobs.materials']);
            $id_map = [];

            foreach ($source->jobs as $job) {
                $newJob = $job->replicate(['unique_id', 'project_job_document_id', 'source_job_id', 'parent_job_id']);
                $newJob->project_job_document_id = $target->id;
                $newJob->source_job_id = $job->id;
                $newJob->save();
                $id_map[$job->id] = $newJob->id;

                foreach ($job->materials as $material) {
                    $newMaterial = $material->replicate(['unique_id', 'project_document_job_id']);
                    $newMaterial->project_document_job_id = $newJob->id;
                    $newMaterial->save();
                }

                foreach ($with_photos ? $job->photos : [] as $photo) {
                    $newPhoto = $photo->replicate(['unique_id', 'project_document_job_id']);
                    $newPhoto->project_document_job_id = $newJob->id;
                    $newPhoto->save();
                }
            }

            // Parents may be sorted after their children, so the tree is relinked after all rows exist.
            foreach ($source->jobs->whereNotNull('parent_job_id') as $job) {
                if (isset($id_map[$job->parent_job_id])) {
                    ProjectDocumentJob::query()->whereKey($id_map[$job->id])->update(['parent_job_id' => $id_map[$job->parent_job_id]]);
                }
            }

            $target->histories()->create([
                'action' => 'copied',
                'actor_id' => $actor_id,
                'payload' => [
                    'source_document_id' => $source->id,
                    'source_revision_no' => $source->revision_no,
                ],
            ]);

            return $target;
        });
    }
}
