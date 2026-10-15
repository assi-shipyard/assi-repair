<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PriceBook;
use App\Models\PriceBookItem;
use App\Models\Project;
use App\Models\ProjectDocumentJob;
use App\Models\ProjectDocumentJobMaterial;
use App\Models\ProjectJobDocument;
use App\Models\ProjectJobGroup;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class project_boq_service
{
    public const MAX_DEPTH = 3;

    public const OWNER_VENDOR_SURCHARGE_PERCENT = 20;

    private const MONEY_SCALE = 2;

    private const PROGRESS_FIELDS = ['progress_percent', 'status', 'actual_start_date', 'actual_finish_date', 'actual_volume'];

    private const JOB_FIELDS = ['job_name', 'responsible_kind', 'responsible_name', 'est_start_date', 'est_finish_date', 'notes'];

    public function __construct(private readonly boq_rule_service $rules, private readonly progress_rollup_service $rollup) {}

    public function price_book_for(Project $project): ?PriceBook
    {
        if ($project->price_book_id !== null) {
            return PriceBook::query()->find($project->price_book_id);
        }

        return PriceBook::query()->where('reference_slot', 1)->first();
    }

    public function search_price_items(Project $project, ?string $term, ?string $group, int $limit = 30): EloquentCollection
    {
        $book = $this->price_book_for($project);
        if ($book === null) {
            return new EloquentCollection;
        }

        $term = trim((string) $term);

        return PriceBookItem::query()
            ->where('price_book_id', $book->id)
            ->when(in_array($group, array_keys(PriceBookItem::GROUPS), true), fn ($query) => $query->where('group', $group))
            ->when($term !== '', function ($query) use ($term): void {
                $like = '%'.addcslashes($term, '%_\\').'%';
                $query->where(fn ($inner) => $inner->where('name', 'like', $like)->orWhere('code', 'like', $like));
            })
            ->orderBy('code')
            ->limit($limit)
            ->get();
    }

    public function active_groups(): EloquentCollection
    {
        return ProjectJobGroup::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
    }

    public function save_job(ProjectJobDocument $document, ?ProjectDocumentJob $job, array $data, ?int $actor_id): ProjectDocumentJob
    {
        return DB::transaction(function () use ($document, $job, $data, $actor_id): ProjectDocumentJob {
            ProjectJobDocument::query()->whereKey($document->id)->lockForUpdate()->first();
            $project = Project::query()->findOrFail($document->project_id);
            $features = $document->features();
            $is_new = $job === null;
            $job ??= new ProjectDocumentJob(['project_job_document_id' => $document->id, 'status' => 'pending']);
            $original_group_id = $job->project_job_group_id;

            $attributes = array_intersect_key($data, array_flip(self::JOB_FIELDS));
            $attributes['est_duration_days'] = $this->duration_days($data['est_start_date'] ?? null, $data['est_finish_date'] ?? null);

            if ($is_new && ! empty($data['parent_job_id'])) {
                $parent = $this->find_parent($document, (string) $data['parent_job_id']);
                $attributes['parent_job_id'] = $parent->id;
                $attributes['project_job_group_id'] = $parent->project_job_group_id;
            } elseif ($job->parent_job_id === null) {
                $attributes['project_job_group_id'] = ProjectJobGroup::query()->where('unique_id', $data['project_job_group_id'] ?? null)->value('id')
                    ?? throw ValidationException::withMessages(['project_job_group_id' => 'Kelompok pekerjaan wajib dipilih.']);
            }

            $item = $this->resolve_price_item($project, $data['price_book_item_id'] ?? null, 'price_book_item_id');
            $rule = $item?->calculation_rule ?? ($data['calculation_rule'] ?? null);
            $attributes['price_book_item_id'] = $item?->id;
            $attributes['calculation_rule'] = $rule;
            $attributes['minimum_quantity'] = $this->minimum_for($item, $job->price_book_item_id, $job->minimum_quantity);
            $attributes = [...$attributes, ...$this->calculated_volume($rule, $data)];
            $attributes['unit'] = $this->rules->unit_for($rule) ?? ($data['unit'] ?? $item?->unit);

            if ($features['prices']) {
                $attributes['unit_price'] = $this->decimal_or_null($data['unit_price'] ?? null) ?? $item?->unit_price;
            }

            if ($features['progress']) {
                // Progress-report phases only accept realised volume here; progress itself comes from approved daily reports.
                $fields = $features['progress_reports'] ? ['actual_volume'] : self::PROGRESS_FIELDS;
                $attributes = [...$attributes, ...array_intersect_key($data, array_flip($fields))];
                $attributes['status'] ??= 'pending';
            }

            $job->fill($attributes);

            if ($job->responsible_kind === 'owner') {
                if ($is_new || $job->getOriginal('responsible_kind') !== 'owner') {
                    $job->owner_vendor_approval_status = 'pending';
                    $job->owner_vendor_decided_by_user_id = null;
                    $job->owner_vendor_decided_at = null;
                    $job->owner_vendor_decision_note = null;
                } elseif ($job->owner_vendor_approval_status !== 'pending' && $job->isDirty([
                    'job_name',
                    'responsible_name',
                    'price_book_item_id',
                    'calculation_rule',
                    'job_volume_estimated',
                    'unit',
                    'unit_price',
                    'minimum_quantity',
                    'calculation',
                ])) {
                    $job->owner_vendor_approval_status = 'pending';
                    $job->owner_vendor_decided_by_user_id = null;
                    $job->owner_vendor_decided_at = null;
                    $job->owner_vendor_decision_note = null;
                }
            } else {
                $job->owner_vendor_approval_status = 'not_required';
                $job->owner_vendor_decided_by_user_id = null;
                $job->owner_vendor_decided_at = null;
                $job->owner_vendor_decision_note = null;
            }

            if ($is_new) {
                $job->sort_order = $this->next_job_sort_order($document, $job->project_job_group_id, $job->parent_job_id);
            }

            $job->save();

            if (! $is_new && $original_group_id !== $job->project_job_group_id) {
                $this->move_descendants_to_group($job);
            }

            $this->record_history($document, $is_new ? 'job_created' : 'job_updated', [
                'job_id' => $job->unique_id,
                'job_name' => $job->job_name,
                'changes' => $is_new ? null : $job->getChanges(),
            ], $actor_id);

            $this->recalculate_document($document);

            return $job;
        });
    }

    public function delete_job(ProjectJobDocument $document, ProjectDocumentJob $job, ?int $actor_id): void
    {
        DB::transaction(function () use ($document, $job, $actor_id): void {
            ProjectJobDocument::query()->whereKey($document->id)->lockForUpdate()->first();
            $ids = $this->descendant_ids($job);

            ProjectDocumentJob::query()->whereIn('id', [...$ids, $job->id])->delete();

            $this->record_history($document, 'job_deleted', [
                'job_id' => $job->unique_id,
                'job_name' => $job->job_name,
                'sub_jobs_deleted' => count($ids),
            ], $actor_id);

            $this->recalculate_document($document);
        });
    }

    public function decide_owner_vendor_job(
        ProjectJobDocument $document,
        ProjectDocumentJob $job,
        string $decision,
        ?string $note,
        ?int $actor_id,
    ): void {
        DB::transaction(function () use ($document, $job, $decision, $note, $actor_id): void {
            ProjectJobDocument::query()->whereKey($document->id)->lockForUpdate()->first();
            $job = ProjectDocumentJob::query()->whereKey($job->id)->lockForUpdate()->firstOrFail();

            if ($document->document_type !== ProjectJobDocument::TYPE_FINAL_BOQ
                || ! $document->is_editable()
                || $job->project_job_document_id !== $document->id
                || $job->responsible_kind !== 'owner') {
                throw ValidationException::withMessages([
                    'decision' => 'Keputusan hanya dapat diberikan untuk pekerjaan vendor pemilik pada RAB Akhir berstatus Draft.',
                ]);
            }

            $previous_status = $job->owner_vendor_approval_status;
            $job->update([
                'owner_vendor_approval_status' => $decision,
                'owner_vendor_decided_by_user_id' => $actor_id,
                'owner_vendor_decided_at' => now(),
                'owner_vendor_decision_note' => $note,
            ]);

            $this->record_history($document, 'owner_vendor_job_'.$decision, [
                'job_id' => $job->unique_id,
                'job_name' => $job->job_name,
                'previous_status' => $previous_status,
                'decision' => $decision,
                'note' => $note,
            ], $actor_id);

            $this->recalculate_document($document);
        });
    }

    public function save_material(ProjectJobDocument $document, ProjectDocumentJob $job, ?ProjectDocumentJobMaterial $material, array $data, ?int $actor_id): ProjectDocumentJobMaterial
    {
        return DB::transaction(function () use ($document, $job, $material, $data, $actor_id): ProjectDocumentJobMaterial {
            ProjectJobDocument::query()->whereKey($document->id)->lockForUpdate()->first();
            $project = Project::query()->findOrFail($document->project_id);
            $prices = $document->features()['prices'];
            $is_new = $material === null;
            $material ??= new ProjectDocumentJobMaterial(['project_document_job_id' => $job->id]);

            $item = $this->resolve_price_item($project, $data['price_book_item_id'] ?? null, 'price_book_item_id');
            $attributes = [
                'material_name' => $data['material_name'],
                'price_book_item_id' => $item?->id,
                'calculation_rule' => $item?->calculation_rule,
                'volume' => $this->decimal_or_null($data['volume'] ?? null),
                'unit' => $this->rules->unit_for($item?->calculation_rule) ?? ($data['unit'] ?? $item?->unit),
                'dimension' => $data['dimension'] ?? null,
                'notes' => $data['notes'] ?? null,
                'minimum_quantity' => $this->minimum_for($item, $material->price_book_item_id, $material->minimum_quantity),
            ];

            if (in_array($document->document_type, [ProjectJobDocument::TYPE_INITIAL_BOQ, ProjectJobDocument::TYPE_FINAL_BOQ], true)) {
                $attributes['owner_supplied'] = (bool) ($data['owner_supplied'] ?? false);
            }

            if ($prices) {
                $attributes['unit_price'] = $this->decimal_or_null($data['unit_price'] ?? null) ?? $item?->unit_price;
            }

            $material->fill($attributes);

            if ($is_new) {
                $material->sort_order = ((int) ProjectDocumentJobMaterial::query()->where('project_document_job_id', $job->id)->max('sort_order')) + 1;
            }

            $material->save();

            $this->record_history($document, $is_new ? 'material_created' : 'material_updated', [
                'job_id' => $job->unique_id,
                'material_id' => $material->unique_id,
                'material_name' => $material->material_name,
                'changes' => $is_new ? null : $material->getChanges(),
            ], $actor_id);

            $this->recalculate_document($document);

            return $material;
        });
    }

    public function delete_material(ProjectJobDocument $document, ProjectDocumentJobMaterial $material, ?int $actor_id): void
    {
        DB::transaction(function () use ($document, $material, $actor_id): void {
            ProjectJobDocument::query()->whereKey($document->id)->lockForUpdate()->first();
            $material->delete();

            $this->record_history($document, 'material_deleted', [
                'material_id' => $material->unique_id,
                'material_name' => $material->material_name,
            ], $actor_id);

            $this->recalculate_document($document);
        });
    }

    // Recomputes billed quantities, line totals and weights for the whole document in one pass,
    // because project-level minimums make every line of the same price item interdependent.
    // Phases without prices keep the weights inherited from the priced phase they were copied from.
    // Weights only use job lines (unit price × billed volume); materials never carry weight.
    public function recalculate_document(ProjectJobDocument $document): void
    {
        $document = ProjectJobDocument::query()->findOrFail($document->id);
        $jobs = ProjectDocumentJob::query()->where('project_job_document_id', $document->id)->with('materials')->get();
        $lines = $this->price_lines($document, $jobs)['lines'];
        $job_totals = [];

        foreach ($lines as $line) {
            $model = $line['model'];
            [$total_field, $currency_field] = $model instanceof ProjectDocumentJob ? ['est_price', 'est_currency'] : ['price', 'currency'];
            $owner_supplied = $model instanceof ProjectDocumentJobMaterial && $model->owner_supplied;
            $total = $owner_supplied
                ? '0.00'
                : ($line['billed'] === null || $model->unit_price === null
                    ? null
                    : $this->round(bcmul($line['billed'], (string) $model->unit_price, 6), self::MONEY_SCALE));

            if ($model instanceof ProjectDocumentJob) {
                $has_surcharge = $document->document_type === ProjectJobDocument::TYPE_FINAL_BOQ
                    && $model->responsible_kind === 'owner'
                    && $model->owner_vendor_approval_status === 'approved'
                    && $total !== null;
                $surcharge = $has_surcharge
                    ? $this->round(bcdiv(bcmul($total, (string) self::OWNER_VENDOR_SURCHARGE_PERCENT, 6), '100', 6), self::MONEY_SCALE)
                    : '0.00';
                $model->owner_vendor_surcharge = $surcharge;
                if ($total !== null) {
                    $total = bcadd($total, $surcharge, self::MONEY_SCALE);
                }
            }

            $model->billed_quantity = $line['billed'];
            $model->{$total_field} = $total;
            if ($total !== null) {
                $model->{$currency_field} = 'IDR';
            }

            if ($model->isDirty()) {
                $model::query()->whereKey($model->id)->update($model->getDirty());
            }

            if ($model instanceof ProjectDocumentJob) {
                $job_totals[$model->id] = $this->money($model->est_price);
            }
        }

        if ($document->features()['prices']) {
            $this->apply_weights($jobs, $job_totals);
        }

        if ($document->features()['progress']) {
            $this->sync_rollup($document);
        }
    }

    // Parent rows cache the rolled-up progress and status so lists, finalisation checks and the customer portal can read them directly.
    public function sync_rollup(ProjectJobDocument $document): void
    {
        $jobs = ProjectDocumentJob::query()->where('project_job_document_id', $document->id)
            ->get(['id', 'parent_job_id', 'job_weight_percent', 'progress_percent', 'status']);

        foreach ($this->rollup->rollup($jobs) as $job_id => $values) {
            if ($values['is_leaf']) {
                continue;
            }

            $job = $jobs->firstWhere('id', $job_id);
            $current = $job->progress_percent === null ? null : bcadd((string) $job->progress_percent, '0', 2);

            if ($current !== $values['actual'] || $job->status !== $values['status']) {
                ProjectDocumentJob::query()->whereKey($job_id)->update(['progress_percent' => $values['actual'], 'status' => $values['status']]);
            }
        }
    }

    public function document_progress(ProjectJobDocument $document): ?string
    {
        $jobs = ProjectDocumentJob::query()->where('project_job_document_id', $document->id)
            ->get(['id', 'parent_job_id', 'job_weight_percent', 'progress_percent', 'status']);

        return $this->rollup->combine($this->rollup->rollup($jobs), $this->rollup->roots($jobs)->pluck('id'));
    }

    // Negotiated minimums replace the snapshotted price-book minimum for one item in this document.
    public function set_minimum_override(ProjectJobDocument $document, string $item_id, ?string $minimum, ?string $reason, ?int $actor_id): void
    {
        DB::transaction(function () use ($document, $item_id, $minimum, $reason, $actor_id): void {
            $document = ProjectJobDocument::query()->lockForUpdate()->findOrFail($document->id);
            $item = PriceBookItem::query()->where('unique_id', $item_id)->first();
            $is_used = $item !== null && (
                ProjectDocumentJob::query()->where('project_job_document_id', $document->id)->where('price_book_item_id', $item->id)->exists()
                || ProjectDocumentJobMaterial::query()->where('price_book_item_id', $item->id)
                    ->whereHas('job', fn ($query) => $query->where('project_job_document_id', $document->id))->exists()
            );

            if (! $is_used) {
                throw ValidationException::withMessages(['price_book_item_id' => 'Item tidak digunakan pada dokumen ini.']);
            }

            $overrides = $document->minimum_overrides ?? [];
            $previous = $overrides[$item_id]['minimum'] ?? null;

            if ($minimum === null) {
                unset($overrides[$item_id]);
            } else {
                $overrides[$item_id] = [
                    'minimum' => bcadd($minimum, '0', 4),
                    'reason' => $reason,
                    'user_id' => $actor_id,
                    'at' => now()->toIso8601String(),
                ];
            }

            $document->minimum_overrides = $overrides === [] ? null : $overrides;
            $document->save();

            $this->record_history($document, $minimum === null ? 'minimum_override_cleared' : 'minimum_override_set', [
                'item_id' => $item_id,
                'item_code' => $item->code,
                'item_name' => $item->name,
                'previous_minimum' => $previous,
                'minimum' => $minimum === null ? null : bcadd($minimum, '0', 4),
                'reason' => $reason,
            ], $actor_id);

            $this->recalculate_document($document);
        });
    }

    public function create_document_from_previous_phase(Project $project, string $document_type, array $attributes, ?int $actor_id): ?ProjectJobDocument
    {
        $previous_type = ProjectJobDocument::PREVIOUS_PHASE[$document_type] ?? null;
        if ($previous_type === null) {
            return null;
        }

        $source = ProjectJobDocument::query()
            ->where('project_id', $project->id)
            ->where('document_type', $previous_type)
            ->orderByDesc('revision_no')
            ->first();

        if ($source === null) {
            return null;
        }

        $document = ProjectJobDocument::cloneWithChildren($source, [
            ...$attributes,
            'document_type' => $document_type,
            'revision_no' => 1,
            'status' => 'draft',
            'actor_id' => $actor_id,
            'with_photos' => false,
        ]);

        $this->record_history($document, 'created', [
            'document_type' => $document_type,
            'revision_no' => 1,
            'copied_from_type' => $previous_type,
            'copied_from_revision_no' => $source->revision_no,
        ], $actor_id);

        $this->recalculate_document($document);

        return $document;
    }

    // Builds the grouped, numbered job tree with totals so the views never run queries or arithmetic.
    public function build_sheet(ProjectJobDocument $document): array
    {
        $document->loadMissing(['jobs.materials.price_book_item', 'jobs.price_book_item', 'jobs.photos']);
        $jobs = $document->jobs;
        $children = $jobs->whereNotNull('parent_job_id')->groupBy('parent_job_id');
        $roots = $this->rollup->roots($jobs);
        $rollup = $this->rollup->rollup($jobs);

        $group_ids = $jobs->pluck('project_job_group_id')->filter()->unique();
        $groups = ProjectJobGroup::query()
            ->where(fn ($query) => $query->where('is_active', true)->orWhereIn('id', $group_ids))
            ->orderBy('sort_order')->orderBy('name')
            ->get();

        $buckets = $groups->map(fn (ProjectJobGroup $group): array => ['group' => $group, 'label' => $group->name, 'roots' => $roots->where('project_job_group_id', $group->id)])
            ->push(['group' => null, 'label' => 'Tanpa Kelompok', 'roots' => $roots->filter(fn ($job): bool => ! $groups->contains('id', $job->project_job_group_id))])
            ->filter(fn (array $bucket): bool => $bucket['roots']->isNotEmpty())
            ->values();

        $sheet_groups = [];
        $grand_total = '0';

        foreach ($buckets as $index => $bucket) {
            $rows = [];
            $total = '0';
            $number = 0;

            foreach ($bucket['roots'] as $root) {
                $total = bcadd($total, $this->flatten($root, (string) ++$number, 0, $children, $rollup, $rows), self::MONEY_SCALE);
            }

            $root_ids = $bucket['roots']->pluck('id');
            $weight = $root_ids->reduce(fn (string $carry, int $id): string => bcadd($carry, $rollup[$id]['weight'], 4), '0');
            $grand_total = bcadd($grand_total, $total, self::MONEY_SCALE);
            $sheet_groups[] = [
                'group' => $bucket['group'],
                'label' => $bucket['label'],
                'roman' => $this->roman($index + 1),
                'rows' => $rows,
                'total' => $total,
                'job_count' => count($rows),
                'weight' => bccomp($weight, '0', 4) > 0 ? $this->round($weight, 2) : null,
                'progress' => $this->rollup->combine($rollup, $root_ids),
                'weighted' => $root_ids->reduce(fn (string $carry, int $id): string => bcadd($carry, $rollup[$id]['weighted'], 4), '0'),
            ];
        }

        return [
            'features' => $document->features(),
            'groups' => $sheet_groups,
            'grand_total' => $grand_total,
            'job_count' => $jobs->count(),
            'material_count' => $jobs->sum(fn (ProjectDocumentJob $job): int => $job->materials->count()),
            'progress' => $this->rollup->combine($rollup, $roots->pluck('id')),
            'minimums' => $document->features()['prices'] ? $this->minimum_summary($document, $jobs) : [],
            'last_safety_factor' => $jobs->filter(fn (ProjectDocumentJob $job): bool => ($job->calculation['type'] ?? null) === 'plate')
                ->sortByDesc('id')->first()?->calculation['safety_factor'],
        ];
    }

    // Rules with a calculator derive the volume from the calculator; the calculator type must match
    // the rule so a later price-book rule change cannot be bypassed by a stale form.
    private function calculated_volume(?string $rule, array $data): array
    {
        if (! $this->rules->has_calculator($rule)) {
            return [
                'calculation' => null,
                'job_volume_estimated' => $this->decimal_or_null($data['job_volume_estimated'] ?? null),
            ];
        }

        $input = $data['calculation'] ?? null;
        if (! is_array($input) || ($input['type'] ?? null) !== $rule) {
            throw ValidationException::withMessages([
                'calculation' => 'Kalkulator '.boq_rule_service::RULES[$rule]['label'].' wajib diisi untuk item ini.',
            ]);
        }

        $result = $this->rules->calculate($rule, $input);

        return ['calculation' => $result['calculation'], 'job_volume_estimated' => $result['volume']];
    }

    /**
     * @return array{lines: list<array>, minimums: array<int, array>}
     */
    private function price_lines(ProjectJobDocument $document, EloquentCollection $jobs): array
    {
        $overrides = $this->override_minimums($document);
        $lines = [];

        foreach ($jobs as $job) {
            $lines[] = $this->price_line($job, $job->calculation, $job->job_volume_estimated, $overrides);
            foreach ($job->materials as $material) {
                $lines[] = $this->price_line($material, null, $material->volume, $overrides);
            }
        }

        $minimums = [];
        foreach ($lines as $index => $line) {
            if ($line['item_id'] === null || $line['minimum'] === null) {
                continue;
            }

            $minimums[$line['item_id']] ??= ['scope' => $line['scope'], 'minimum' => $line['minimum'], 'used' => '0', 'indexes' => []];
            $minimums[$line['item_id']]['used'] = bcadd($minimums[$line['item_id']]['used'], $line['base'] ?? '0', 4);
            $minimums[$line['item_id']]['indexes'][] = $index;
        }

        // The shortfall lands on the line with the largest usage so the project total meets the minimum.
        foreach ($minimums as &$minimum) {
            $minimum['shortfall'] = '0';
            if ($minimum['scope'] !== 'project' || bccomp($minimum['used'], '0', 4) <= 0 || bccomp($minimum['used'], $minimum['minimum'], 4) >= 0) {
                continue;
            }

            $target = collect($minimum['indexes'])->sortByDesc(fn (int $index): float => (float) $lines[$index]['base'])->first();
            $minimum['shortfall'] = bcsub($minimum['minimum'], $minimum['used'], 4);
            $lines[$target]['billed'] = bcadd($lines[$target]['billed'], $minimum['shortfall'], 4);
            $lines[$target]['shortfall'] = $minimum['shortfall'];
        }
        unset($minimum);

        return ['lines' => $lines, 'minimums' => $minimums];
    }

    private function price_line(ProjectDocumentJob|ProjectDocumentJobMaterial $model, ?array $calculation, mixed $volume, array $overrides): array
    {
        if ($model instanceof ProjectDocumentJobMaterial && $model->owner_supplied) {
            return [
                'model' => $model,
                'item_id' => null,
                'minimum' => null,
                'scope' => null,
                'base' => '0',
                'billed' => '0',
                'shortfall' => null,
            ];
        }

        $item_id = $model->price_book_item_id;
        $minimum = $item_id !== null && array_key_exists($item_id, $overrides)
            ? $overrides[$item_id]
            : ($model->minimum_quantity === null ? null : (string) $model->minimum_quantity);
        $minimum = $minimum !== null && bccomp($minimum, '0', 4) > 0 ? $minimum : null;
        $scope = $this->rules->minimum_scope($model->calculation_rule, $calculation);
        $base = $this->rules->billed_base($model->calculation_rule, $calculation, $volume, $scope === 'piece' ? $minimum : null);

        return [
            'model' => $model,
            'item_id' => $item_id,
            'minimum' => $scope === null ? null : $minimum,
            'scope' => $scope,
            'base' => $base,
            'billed' => $base,
            'shortfall' => null,
        ];
    }

    /** @return array<int, string> price item id => negotiated minimum (0 waives it) */
    private function override_minimums(ProjectJobDocument $document): array
    {
        $overrides = $document->minimum_overrides ?? [];
        if ($overrides === []) {
            return [];
        }

        return PriceBookItem::query()->whereIn('unique_id', array_keys($overrides))->pluck('id', 'unique_id')
            ->mapWithKeys(fn (int $id, string $unique_id): array => [$id => (string) $overrides[$unique_id]['minimum']])
            ->all();
    }

    // Weight = job line total / sum of all job line totals.
    private function apply_weights(EloquentCollection $jobs, array $job_totals): void
    {
        $total = array_reduce($job_totals, fn (string $carry, string $line): string => bcadd($carry, $line, self::MONEY_SCALE), '0');
        $has_total = bccomp($total, '0', self::MONEY_SCALE) > 0;

        foreach ($jobs as $job) {
            $weight = $has_total ? $this->round(bcdiv(bcmul($job_totals[$job->id] ?? '0', '100', 8), $total, 8), 2) : null;
            $current = $job->job_weight_percent === null ? null : $this->round((string) $job->job_weight_percent, 2);

            if ($weight !== $current) {
                ProjectDocumentJob::query()->whereKey($job->id)->update(['job_weight_percent' => $weight]);
            }
        }
    }

    // Internal summary of every minimum in play, including negotiated overrides.
    private function minimum_summary(ProjectJobDocument $document, EloquentCollection $jobs): array
    {
        $overrides = $document->minimum_overrides ?? [];
        $items = $jobs->pluck('price_book_item')->merge($jobs->flatMap->materials->pluck('price_book_item'))->filter()->keyBy('id');
        $computed = $this->price_lines($document, $jobs);
        $used_item_ids = collect($computed['lines'])->pluck('item_id')->filter()->unique();
        $users = User::query()->whereIn('id', collect($overrides)->pluck('user_id')->filter()->unique())->pluck('name', 'id');
        $summary = [];

        foreach ($used_item_ids as $item_id) {
            $item = $items->get($item_id);
            $minimum = $computed['minimums'][$item_id] ?? null;
            $override = $item === null ? null : ($overrides[$item->unique_id] ?? null);
            $lines = collect($computed['lines'])->where('item_id', $item_id);
            $original = $lines->map(fn (array $line): ?string => $line['model']->minimum_quantity === null ? null : (string) $line['model']->minimum_quantity)->filter()->first();

            if ($item === null || $lines->first()['scope'] === null || ($minimum === null && $override === null && $original === null)) {
                continue;
            }

            $summary[] = [
                'item' => $item,
                'unit' => $lines->first()['model']->unit ?? $item->unit,
                'scope' => $minimum['scope'] ?? $lines->first()['scope'],
                'original' => $original,
                'minimum' => $minimum['minimum'] ?? null,
                'used' => $minimum['used'] ?? $lines->reduce(fn (string $carry, array $line): string => bcadd($carry, $line['base'] ?? '0', 4), '0'),
                'billed' => $lines->reduce(fn (string $carry, array $line): string => bcadd($carry, $line['billed'] ?? '0', 4), '0'),
                'shortfall' => $minimum['shortfall'] ?? '0',
                'line_count' => $lines->count(),
                'override' => $override === null ? null : [...$override, 'user_name' => $users[$override['user_id'] ?? 0] ?? null],
            ];
        }

        return $summary;
    }

    private function flatten(ProjectDocumentJob $job, string $number, int $depth, Collection $children, array $rollup, array &$rows): string
    {
        $materials_total = $job->materials->reduce(fn (string $carry, ProjectDocumentJobMaterial $material): string => bcadd($carry, $this->money($material->price), self::MONEY_SCALE), '0');
        $index = count($rows);
        $rows[] = [
            'job' => $job,
            'number' => $number,
            'depth' => $depth,
            'line_total' => $this->money($job->est_price),
            'calculation_text' => $this->rules->describe($job->calculation, true),
            'calculation_text_customer' => $this->rules->describe($job->calculation, false),
            'materials_total' => $materials_total,
            'materials' => $job->materials->values()->map(fn (ProjectDocumentJobMaterial $material, int $position): array => [
                'material' => $material,
                'letter' => $this->letter($position),
            ])->all(),
            'has_children' => false,
            'progress' => $rollup[$job->id] ?? null,
        ];

        $subtotal = bcadd($rows[$index]['line_total'], $materials_total, self::MONEY_SCALE);
        $position = 0;

        foreach ($children->get($job->id, collect())->sortBy('sort_order') as $child) {
            $rows[$index]['has_children'] = true;
            $subtotal = bcadd($subtotal, $this->flatten($child, $number.'.'.++$position, $depth + 1, $children, $rollup, $rows), self::MONEY_SCALE);
        }

        $rows[$index]['subtotal'] = $subtotal;

        return $subtotal;
    }

    private function find_parent(ProjectJobDocument $document, string $parent_id): ProjectDocumentJob
    {
        $parent = ProjectDocumentJob::query()->where('project_job_document_id', $document->id)->where('unique_id', $parent_id)->first();
        if ($parent === null) {
            throw ValidationException::withMessages(['parent_job_id' => 'Pekerjaan induk tidak ditemukan pada dokumen ini.']);
        }

        $depth = 1;
        $cursor = $parent;
        while ($cursor->parent_job_id !== null) {
            $depth++;
            $cursor = ProjectDocumentJob::query()->find($cursor->parent_job_id, ['id', 'parent_job_id']);
            if ($cursor === null) {
                break;
            }
        }

        if ($depth >= self::MAX_DEPTH) {
            throw ValidationException::withMessages(['parent_job_id' => 'Sub pekerjaan maksimal '.self::MAX_DEPTH.' tingkat.']);
        }

        return $parent;
    }

    private function resolve_price_item(Project $project, ?string $item_id, string $field): ?PriceBookItem
    {
        if ($item_id === null || $item_id === '') {
            return null;
        }

        $book = $this->price_book_for($project);
        $item = $book === null ? null : PriceBookItem::query()->where('price_book_id', $book->id)->where('unique_id', $item_id)->first();

        if ($item === null) {
            throw ValidationException::withMessages([$field => 'Item kamus harga tidak tersedia untuk proyek ini.']);
        }

        return $item;
    }

    // The minimum charge is a snapshot: it is only refreshed when a different price item is picked.
    private function minimum_for(?PriceBookItem $item, ?int $current_item_id, mixed $current_minimum): ?string
    {
        if ($item === null) {
            return null;
        }

        if ($item->id === $current_item_id) {
            return $current_minimum === null ? null : (string) $current_minimum;
        }

        return $item->has_minimum_charge && $item->minimum_quantity !== null ? (string) $item->minimum_quantity : null;
    }

    private function next_job_sort_order(ProjectJobDocument $document, ?int $group_id, ?int $parent_id): int
    {
        return ((int) ProjectDocumentJob::query()
            ->where('project_job_document_id', $document->id)
            ->where('project_job_group_id', $group_id)
            ->where('parent_job_id', $parent_id)
            ->max('sort_order')) + 1;
    }

    private function move_descendants_to_group(ProjectDocumentJob $job): void
    {
        $ids = $this->descendant_ids($job);
        if ($ids !== []) {
            ProjectDocumentJob::query()->whereIn('id', $ids)->update(['project_job_group_id' => $job->project_job_group_id]);
        }
    }

    private function descendant_ids(ProjectDocumentJob $job): array
    {
        $ids = [];
        $frontier = [$job->id];

        while ($frontier !== []) {
            $frontier = ProjectDocumentJob::query()->whereIn('parent_job_id', $frontier)->pluck('id')->all();
            $ids = [...$ids, ...$frontier];
        }

        return $ids;
    }

    private function duration_days(?string $start, ?string $finish): ?int
    {
        if ($start === null || $finish === null) {
            return null;
        }

        return (int) CarbonImmutable::parse($start)->diffInDays(CarbonImmutable::parse($finish)) + 1;
    }

    private function record_history(ProjectJobDocument $document, string $action, array $payload, ?int $actor_id): void
    {
        $document->histories()->create(['action' => $action, 'actor_id' => $actor_id, 'payload' => $payload]);
    }

    private function decimal_or_null(mixed $value): ?string
    {
        return $value === null || $value === '' ? null : (string) $value;
    }

    private function money(mixed $value, int $scale = self::MONEY_SCALE): string
    {
        return $value === null || $value === '' ? '0' : bcadd((string) $value, '0', $scale);
    }

    // Half-up rounding for non-negative bcmath values (bcround needs PHP 8.4, the floor is 8.3).
    private function round(string $value, int $scale): string
    {
        return bcadd($value, '0.'.str_repeat('0', $scale).'5', $scale);
    }

    private function roman(int $number): string
    {
        $map = ['M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1];
        $result = '';
        foreach ($map as $symbol => $value) {
            $result .= str_repeat($symbol, intdiv($number, $value));
            $number %= $value;
        }

        return $result;
    }

    private function letter(int $index): string
    {
        $letter = '';
        for ($index++; $index > 0; $index = intdiv($index - 1, 26)) {
            $letter = chr(97 + ($index - 1) % 26).$letter;
        }

        return $letter;
    }
}
