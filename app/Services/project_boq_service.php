<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PriceBook;
use App\Models\PriceBookItem;
use App\Models\Project;
use App\Models\project_job_group;
use App\Models\ProjectDocumentJob;
use App\Models\ProjectDocumentJobMaterial;
use App\Models\ProjectJobDocument;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class project_boq_service
{
    public const MAX_DEPTH = 3;

    public const CALCULATION_TYPES = ['plate' => 'Pelat', 'pipe' => 'Pipa'];

    public const CALCULATION_BASES = [
        'plate' => ['kg' => 'Berat (kg)', 'm2' => 'Luas (m²)'],
        'pipe' => ['kg' => 'Berat (kg)', 'm' => 'Panjang (m)'],
    ];

    private const BASIS_UNITS = ['kg' => 'kg', 'm2' => 'm²', 'm' => 'm'];

    private const DEFAULT_DENSITY = 7850.0;

    private const DEFAULT_SAFETY_FACTOR = 1.1;

    private const MONEY_SCALE = 2;

    private const PROGRESS_FIELDS = ['progress_percent', 'status', 'actual_start_date', 'actual_finish_date', 'actual_volume'];

    private const JOB_FIELDS = ['job_name', 'responsible_kind', 'responsible_name', 'est_start_date', 'est_finish_date', 'notes'];

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
        return project_job_group::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
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
                $attributes['project_job_group_id'] = project_job_group::query()->where('unique_id', $data['project_job_group_id'] ?? null)->value('id')
                    ?? throw ValidationException::withMessages(['project_job_group_id' => 'Kelompok pekerjaan wajib dipilih.']);
            }

            $item = $this->resolve_price_item($project, $data['price_book_item_id'] ?? null, 'price_book_item_id');
            $attributes['price_book_item_id'] = $item?->id;
            $attributes['minimum_quantity'] = $this->minimum_for($item, $job->price_book_item_id, $job->minimum_quantity);

            $calculation = $this->calculate($data['calculation'] ?? []);
            $attributes['calculation'] = $calculation['calculation'];
            $attributes['job_volume_estimated'] = $calculation['volume'] ?? $this->decimal_or_null($data['job_volume_estimated'] ?? null);
            $attributes['unit'] = $calculation['unit'] ?? ($data['unit'] ?? $item?->unit);

            if ($features['prices']) {
                $attributes['unit_price'] = $this->decimal_or_null($data['unit_price'] ?? null) ?? $item?->unit_price;
            }

            if ($features['progress']) {
                $attributes = [...$attributes, ...array_intersect_key($data, array_flip(self::PROGRESS_FIELDS))];
                $attributes['status'] ??= 'pending';
            }

            $job->fill($attributes);
            $this->apply_line_total($job, 'job_volume_estimated', 'est_price', 'est_currency', $features['prices']);

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

            $this->recalculate_weights($document);

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

            $this->recalculate_weights($document);
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
                'volume' => $this->decimal_or_null($data['volume'] ?? null),
                'unit' => $data['unit'] ?? $item?->unit,
                'dimension' => $data['dimension'] ?? null,
                'notes' => $data['notes'] ?? null,
                'minimum_quantity' => $this->minimum_for($item, $material->price_book_item_id, $material->minimum_quantity),
            ];

            if ($prices) {
                $attributes['unit_price'] = $this->decimal_or_null($data['unit_price'] ?? null) ?? $item?->unit_price;
            }

            $material->fill($attributes);
            $this->apply_line_total($material, 'volume', 'price', 'currency', $prices);

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

            $this->recalculate_weights($document);

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

            $this->recalculate_weights($document);
        });
    }

    // Weight = (job line + its materials) / document total. Phases without prices keep the weights
    // inherited from the priced phase they were copied from, so progress stays comparable.
    public function recalculate_weights(ProjectJobDocument $document): void
    {
        if (! $document->features()['prices']) {
            return;
        }

        $jobs = ProjectDocumentJob::query()
            ->where('project_job_document_id', $document->id)
            ->withSum('materials as materials_total', 'price')
            ->get(['id', 'est_price', 'job_weight_percent']);

        $lines = $jobs->mapWithKeys(fn (ProjectDocumentJob $job): array => [
            $job->id => bcadd($this->money($job->est_price), $this->money($job->materials_total), self::MONEY_SCALE),
        ]);
        $total = $lines->reduce(fn (string $carry, string $line): string => bcadd($carry, $line, self::MONEY_SCALE), '0');
        $has_total = bccomp($total, '0', self::MONEY_SCALE) > 0;

        foreach ($jobs as $job) {
            $weight = $has_total ? $this->round(bcdiv(bcmul($lines[$job->id], '100', 8), $total, 8), 2) : null;
            $current = $job->job_weight_percent === null ? null : $this->round((string) $job->job_weight_percent, 2);

            if ($weight !== $current) {
                ProjectDocumentJob::query()->whereKey($job->id)->update(['job_weight_percent' => $weight]);
            }
        }
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

        $this->recalculate_weights($document);

        return $document;
    }

    // Builds the grouped, numbered job tree with totals so the views never run queries or arithmetic.
    public function build_sheet(ProjectJobDocument $document): array
    {
        $document->loadMissing(['jobs.materials.price_book_item', 'jobs.price_book_item']);
        $jobs = $document->jobs;
        $children = $jobs->whereNotNull('parent_job_id')->groupBy('parent_job_id');
        $known_ids = $jobs->pluck('id')->flip();
        $roots = $jobs->filter(fn (ProjectDocumentJob $job): bool => $job->parent_job_id === null || ! $known_ids->has($job->parent_job_id));

        $group_ids = $jobs->pluck('project_job_group_id')->filter()->unique();
        $groups = project_job_group::query()
            ->where(fn ($query) => $query->where('is_active', true)->orWhereIn('id', $group_ids))
            ->orderBy('sort_order')->orderBy('name')
            ->get();

        $buckets = $groups->map(fn (project_job_group $group): array => ['group' => $group, 'label' => $group->name, 'roots' => $roots->where('project_job_group_id', $group->id)])
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
                $total = bcadd($total, $this->flatten($root, (string) ++$number, 0, $children, $rows), self::MONEY_SCALE);
            }

            $grand_total = bcadd($grand_total, $total, self::MONEY_SCALE);
            $sheet_groups[] = [
                'group' => $bucket['group'],
                'label' => $bucket['label'],
                'roman' => $this->roman($index + 1),
                'rows' => $rows,
                'total' => $total,
                'job_count' => count($rows),
                'progress' => $this->weighted_progress(collect($rows)->pluck('job')),
            ];
        }

        foreach ($sheet_groups as &$group) {
            $group['weight'] = bccomp($grand_total, '0', self::MONEY_SCALE) > 0
                ? $this->round(bcdiv(bcmul($group['total'], '100', 8), $grand_total, 8), 2)
                : null;
        }
        unset($group);

        return [
            'features' => $document->features(),
            'groups' => $sheet_groups,
            'grand_total' => $grand_total,
            'job_count' => $jobs->count(),
            'material_count' => $jobs->sum(fn (ProjectDocumentJob $job): int => $job->materials->count()),
            'progress' => $this->weighted_progress($jobs),
        ];
    }

    // Plate/pipe geometry in millimetres; density in kg/m³. Pipe pieces are billed at a minimum
    // of 0.5 m and then 1 m, the per-piece rule carried over from the previous system.
    public function calculate(array $input): array
    {
        $type = $input['type'] ?? null;
        if (! array_key_exists((string) $type, self::CALCULATION_TYPES)) {
            return ['calculation' => null, 'volume' => null, 'unit' => null];
        }

        $basis = $input['basis'] ?? array_key_first(self::CALCULATION_BASES[$type]);
        $quantity = max(1, (int) ($input['quantity'] ?? 1));
        $density = (float) ($input['density'] ?? 0) > 0 ? (float) $input['density'] : self::DEFAULT_DENSITY;
        $safety_factor = (float) ($input['safety_factor'] ?? 0) >= 1 ? (float) $input['safety_factor'] : self::DEFAULT_SAFETY_FACTOR;
        $result = ['type' => $type, 'basis' => $basis, 'quantity' => $quantity, 'density' => $density, 'safety_factor' => $safety_factor];

        if ($type === 'plate') {
            $length = (float) $input['length'];
            $width = (float) $input['width'];
            $thickness = (float) $input['thickness'];
            $result += [
                'length' => $length,
                'width' => $width,
                'thickness' => $thickness,
                'weight_kg' => round($length * $width * $thickness * 1e-9 * $density * $quantity * $safety_factor, 4),
                'area_m2' => round($length * $width * 1e-6 * $quantity, 4),
            ];
            $volume = $basis === 'm2' ? $result['area_m2'] : $result['weight_kg'];
        } else {
            $diameter = (float) $input['diameter'];
            $wall_thickness = (float) $input['wall_thickness'];
            $pipe_length = (float) $input['pipe_length'];
            $billed_length = match (true) {
                $pipe_length < 500 => 500.0,
                $pipe_length < 1000 => 1000.0,
                default => $pipe_length,
            };
            $inner = $diameter - 2 * $wall_thickness;
            $section_m2 = M_PI / 4 * ($diameter ** 2 - $inner ** 2) * 1e-6;
            $result += [
                'diameter' => $diameter,
                'wall_thickness' => $wall_thickness,
                'pipe_length' => $pipe_length,
                'billed_length_mm' => $billed_length,
                'length_m' => round($billed_length / 1000 * $quantity, 4),
                'weight_kg' => round($section_m2 * $billed_length / 1000 * $quantity * $density * $safety_factor, 4),
            ];
            $volume = $basis === 'm' ? $result['length_m'] : $result['weight_kg'];
        }

        return [
            'calculation' => $result,
            'volume' => number_format($volume, 4, '.', ''),
            'unit' => self::BASIS_UNITS[$basis],
        ];
    }

    private function flatten(ProjectDocumentJob $job, string $number, int $depth, Collection $children, array &$rows): string
    {
        $materials_total = $job->materials->reduce(fn (string $carry, ProjectDocumentJobMaterial $material): string => bcadd($carry, $this->money($material->price), self::MONEY_SCALE), '0');
        $index = count($rows);
        $rows[] = [
            'job' => $job,
            'number' => $number,
            'depth' => $depth,
            'line_total' => $this->money($job->est_price),
            'materials_total' => $materials_total,
            'materials' => $job->materials->values()->map(fn (ProjectDocumentJobMaterial $material, int $position): array => [
                'material' => $material,
                'letter' => $this->letter($position),
            ])->all(),
            'has_children' => false,
        ];

        $subtotal = bcadd($rows[$index]['line_total'], $materials_total, self::MONEY_SCALE);
        $position = 0;

        foreach ($children->get($job->id, collect())->sortBy('sort_order') as $child) {
            $rows[$index]['has_children'] = true;
            $subtotal = bcadd($subtotal, $this->flatten($child, $number.'.'.++$position, $depth + 1, $children, $rows), self::MONEY_SCALE);
        }

        $rows[$index]['subtotal'] = $subtotal;

        return $subtotal;
    }

    private function weighted_progress(Collection $jobs): ?string
    {
        $weighted = $jobs->filter(fn (ProjectDocumentJob $job): bool => (float) $job->job_weight_percent > 0);
        if ($weighted->isEmpty()) {
            return null;
        }

        $weight_sum = '0';
        $score = '0';
        foreach ($weighted as $job) {
            $weight_sum = bcadd($weight_sum, (string) $job->job_weight_percent, 4);
            $score = bcadd($score, bcmul((string) $job->job_weight_percent, $this->money($job->progress_percent), 6), 6);
        }

        return $this->round(bcdiv($score, $weight_sum, 6), 2);
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

    private function apply_line_total(ProjectDocumentJob|ProjectDocumentJobMaterial $line, string $volume_field, string $total_field, string $currency_field, bool $prices): void
    {
        if ($line->unit_price === null) {
            if ($prices) {
                $line->{$total_field} = null;
            }

            return;
        }

        $volume = $this->money($line->{$volume_field}, 4);
        $billable = bccomp($volume, '0', 4) > 0 && $line->minimum_quantity !== null && bccomp($volume, (string) $line->minimum_quantity, 4) < 0
            ? (string) $line->minimum_quantity
            : $volume;

        $line->{$total_field} = $this->round(bcmul($billable, (string) $line->unit_price, 6), self::MONEY_SCALE);
        $line->{$currency_field} = 'IDR';
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
