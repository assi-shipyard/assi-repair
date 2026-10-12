<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ProjectDocumentJob;
use Illuminate\Support\Collection;

// Parents never carry their own progress: actual % is the weighted average of their sub jobs,
// weight is the share of the whole subtree, and weighted % = actual % × weight.
final class progress_rollup_service
{
    private const SCALE = 6;

    /**
     * @param  Collection<int, ProjectDocumentJob>  $jobs
     * @return array<int, array{weight: string, actual: string, weighted: string, is_leaf: bool, status: string}>
     */
    public function rollup(Collection $jobs): array
    {
        $known = $jobs->keyBy('id');
        $children = $jobs->filter(fn (ProjectDocumentJob $job): bool => $job->parent_job_id !== null && $known->has($job->parent_job_id))
            ->groupBy('parent_job_id');
        $result = [];

        foreach ($this->roots($jobs) as $root) {
            $this->visit($root, $children, $result);
        }

        return $result;
    }

    /**
     * @param  Collection<int, ProjectDocumentJob>  $jobs
     * @return Collection<int, ProjectDocumentJob>
     */
    public function roots(Collection $jobs): Collection
    {
        $known = $jobs->pluck('id')->flip();

        return $jobs->filter(fn (ProjectDocumentJob $job): bool => $job->parent_job_id === null || ! $known->has($job->parent_job_id))->values();
    }

    /**
     * Combined actual % of sibling subtrees; falls back to a plain average when none of them has a weight.
     *
     * @param  array<int, array{weight: string, actual: string}>  $rollup
     * @param  iterable<int>  $job_ids
     */
    public function combine(array $rollup, iterable $job_ids): ?string
    {
        $items = collect($job_ids)->map(fn (int $id): ?array => $rollup[$id] ?? null)->filter()->values();
        if ($items->isEmpty()) {
            return null;
        }

        $weight = $items->reduce(fn (string $carry, array $item): string => bcadd($carry, $item['weight'], self::SCALE), '0');
        if (bccomp($weight, '0', self::SCALE) > 0) {
            $score = $items->reduce(fn (string $carry, array $item): string => bcadd($carry, bcmul($item['weight'], $item['actual'], self::SCALE), self::SCALE), '0');

            return $this->round(bcdiv($score, $weight, self::SCALE), 2);
        }

        $sum = $items->reduce(fn (string $carry, array $item): string => bcadd($carry, $item['actual'], self::SCALE), '0');

        return $this->round(bcdiv($sum, (string) $items->count(), self::SCALE), 2);
    }

    public function status_for(string $percent): string
    {
        return match (true) {
            bccomp($percent, '100', 2) >= 0 => 'completed',
            bccomp($percent, '0', 2) > 0 => 'ongoing',
            default => 'pending',
        };
    }

    public function round(string $value, int $scale): string
    {
        $offset = '0.'.str_repeat('0', $scale).'5';

        return bcadd($value, str_starts_with($value, '-') ? '-'.$offset : $offset, $scale);
    }

    private function visit(ProjectDocumentJob $job, Collection $children, array &$result): void
    {
        $own_weight = $job->job_weight_percent === null ? '0' : (string) $job->job_weight_percent;
        $kids = $children->get($job->id, collect());

        if ($kids->isEmpty()) {
            $actual = $job->progress_percent === null ? '0' : bcadd((string) $job->progress_percent, '0', 2);
            $result[$job->id] = $this->entry($own_weight, $actual, true, (string) ($job->status ?: 'pending'));

            return;
        }

        foreach ($kids as $kid) {
            $this->visit($kid, $children, $result);
        }

        $ids = $kids->pluck('id');
        $statuses = $ids->map(fn (int $id): string => $result[$id]['status'])->unique();
        $weight = $ids->reduce(fn (string $carry, int $id): string => bcadd($carry, $result[$id]['weight'], self::SCALE), $own_weight);
        $actual = $this->combine($result, $ids) ?? '0';

        $status = match (true) {
            $statuses->every(fn (string $status): bool => $status === 'completed') => 'completed',
            $statuses->every(fn (string $status): bool => $status === 'pending') && bccomp($actual, '0', 2) === 0 => 'pending',
            default => 'ongoing',
        };

        $result[$job->id] = $this->entry($weight, $actual, false, $status);
    }

    private function entry(string $weight, string $actual, bool $is_leaf, string $status): array
    {
        return [
            'weight' => bcadd($weight, '0', 4),
            'actual' => $actual,
            'weighted' => $this->round(bcdiv(bcmul($weight, $actual, self::SCALE), '100', self::SCALE), 4),
            'is_leaf' => $is_leaf,
            'status' => $status,
        ];
    }
}
