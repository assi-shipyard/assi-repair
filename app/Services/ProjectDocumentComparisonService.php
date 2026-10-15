<?php

namespace App\Services;

use App\Models\ProjectDocumentJob;
use App\Models\ProjectJobDocument;
use Illuminate\Support\Collection;

class ProjectDocumentComparisonService
{
    public const JOB_FIELDS = [
        'job_name' => 'Nama pekerjaan',
        'job_volume_estimated' => 'Volume',
        'unit' => 'Satuan',
        'unit_price' => 'Harga satuan',
        'responsible_kind' => 'Pelaksana',
        'owner_vendor_approval_status' => 'Persetujuan vendor pemilik',
        'responsible_name' => 'Penanggung jawab',
        'est_start_date' => 'Mulai',
        'est_finish_date' => 'Selesai',
        'est_price' => 'Harga',
        'owner_vendor_surcharge' => 'Surcharge vendor pemilik',
        'est_currency' => 'Mata uang',
        'job_weight_percent' => 'Bobot (%)',
    ];

    public const MATERIAL_FIELDS = [
        'volume' => 'Volume',
        'unit' => 'Satuan',
        'unit_price' => 'Harga satuan',
        'price' => 'Harga',
        'currency' => 'Mata uang',
        'dimension' => 'Dimensi',
        'owner_supplied' => 'Disediakan pemilik',
    ];

    public function compare(ProjectJobDocument $base, ProjectJobDocument $target): array
    {
        $base->loadMissing('jobs.materials');
        $target->loadMissing('jobs.materials');

        $base_by_id = $base->jobs->keyBy('id');
        $matched_base_ids = [];
        $linked_ids = $target->jobs->pluck('source_job_id')->filter()->all();
        $rows = [];

        foreach ($target->jobs as $job) {
            $old = $this->find_base_job($job, $base, $base_by_id, $matched_base_ids, $linked_ids);

            if ($old === null) {
                $rows[] = ['type' => 'added', 'name' => $job->job_name, 'old' => null, 'new' => $job, 'changes' => [], 'materials' => $this->diff_materials(collect(), $job->materials)];

                continue;
            }

            $matched_base_ids[] = $old->id;
            $changes = $this->diff_fields($old, $job, self::JOB_FIELDS);
            $materials = $this->diff_materials($old->materials, $job->materials);

            $rows[] = [
                'type' => $changes !== [] || $materials !== [] ? 'changed' : 'unchanged',
                'name' => $job->job_name,
                'old' => $old,
                'new' => $job,
                'changes' => $changes,
                'materials' => $materials,
            ];
        }

        foreach ($base->jobs->whereNotIn('id', $matched_base_ids) as $job) {
            $rows[] = ['type' => 'removed', 'name' => $job->job_name, 'old' => $job, 'new' => null, 'changes' => [], 'materials' => $this->diff_materials($job->materials, collect())];
        }

        $counts = collect($rows)->countBy('type');

        return [
            'rows' => $rows,
            'counts' => [
                'added' => $counts->get('added', 0),
                'removed' => $counts->get('removed', 0),
                'changed' => $counts->get('changed', 0),
                'unchanged' => $counts->get('unchanged', 0),
            ],
            'totals' => $this->price_totals($base, $target),
        ];
    }

    // Sum of estimated job prices plus material prices, grouped per currency.
    public function document_totals(ProjectJobDocument $document): array
    {
        $document->loadMissing('jobs.materials');
        $totals = [];

        foreach ($document->jobs as $job) {
            if ($job->est_price !== null) {
                $currency = $job->est_currency ?: 'IDR';
                $totals[$currency] = ($totals[$currency] ?? 0) + (float) $job->est_price;
            }

            foreach ($job->materials as $material) {
                if ($material->price !== null) {
                    $currency = $material->currency ?: 'IDR';
                    $totals[$currency] = ($totals[$currency] ?? 0) + (float) $material->price;
                }
            }
        }

        return $totals;
    }

    private function price_totals(ProjectJobDocument $base, ProjectJobDocument $target): array
    {
        $old = $this->document_totals($base);
        $new = $this->document_totals($target);
        $result = [];

        foreach (array_unique([...array_keys($old), ...array_keys($new)]) as $currency) {
            $old_value = $old[$currency] ?? 0.0;
            $new_value = $new[$currency] ?? 0.0;
            $result[$currency] = [
                'old' => $old_value,
                'new' => $new_value,
                'delta' => $new_value - $old_value,
                'percent' => $old_value > 0 ? (($new_value - $old_value) / $old_value) * 100 : null,
            ];
        }

        return $result;
    }

    private function find_base_job(ProjectDocumentJob $job, ProjectJobDocument $base, Collection $base_by_id, array $matched_ids, array $linked_ids): ?ProjectDocumentJob
    {
        if ($job->source_job_id !== null && $base_by_id->has($job->source_job_id) && ! in_array($job->source_job_id, $matched_ids, true)) {
            return $base_by_id->get($job->source_job_id);
        }

        // Fallback for jobs added manually: match by name when the base job is not linked to another job.
        return $base->jobs->first(fn ($candidate): bool => ! in_array($candidate->id, $matched_ids, true)
            && mb_strtolower(trim($candidate->job_name)) === mb_strtolower(trim($job->job_name))
            && ! in_array($candidate->id, $linked_ids, true));
    }

    private function diff_materials(Collection $old, Collection $new): array
    {
        $old_by_name = $old->keyBy(fn ($material): string => mb_strtolower(trim($material->material_name)));
        $new_by_name = $new->keyBy(fn ($material): string => mb_strtolower(trim($material->material_name)));
        $diff = [];

        foreach ($new_by_name as $key => $material) {
            $previous = $old_by_name->get($key);

            if ($previous === null) {
                $diff[] = ['type' => 'added', 'name' => $material->material_name, 'changes' => [], 'new' => $material];

                continue;
            }

            $changes = $this->diff_fields($previous, $material, self::MATERIAL_FIELDS);
            if ($changes !== []) {
                $diff[] = ['type' => 'changed', 'name' => $material->material_name, 'changes' => $changes, 'new' => $material];
            }
        }

        foreach ($old_by_name->diffKeys($new_by_name) as $material) {
            $diff[] = ['type' => 'removed', 'name' => $material->material_name, 'changes' => [], 'new' => null];
        }

        return $diff;
    }

    private function diff_fields(object $old, object $new, array $fields): array
    {
        $changes = [];

        foreach ($fields as $field => $label) {
            $old_value = $this->normalize($old->{$field});
            $new_value = $this->normalize($new->{$field});

            if ($old_value !== $new_value) {
                $changes[] = ['field' => $field, 'label' => $label, 'old' => $old_value, 'new' => $new_value];
            }
        }

        return $changes;
    }

    private function normalize(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_numeric($value)) {
            return rtrim(rtrim(number_format((float) $value, 4, '.', ''), '0'), '.');
        }

        return trim((string) $value);
    }
}
