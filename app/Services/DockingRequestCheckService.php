<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DockingOccupancy;
use App\Models\DockingSpace;
use App\Models\ProjectDockingRequest;
use Carbon\CarbonImmutable;

class DockingRequestCheckService
{
    public function has_schedule_conflict(
        int $docking_space_id,
        string $start_at,
        ?string $end_at,
        ?int $exclude_request_id = null
    ): bool {
        $start = CarbonImmutable::parse($start_at)->toDateTimeString();
        $end = $end_at !== null
            ? CarbonImmutable::parse($end_at)->toDateTimeString()
            : CarbonImmutable::parse($start_at)->addDays(30)->toDateTimeString();

        $occupancy_query = DockingOccupancy::query()
            ->where('docking_space_id', $docking_space_id)
            ->whereIn('occupancy_status', ['scheduled', 'occupied'])
            ->where('docked_at', '<=', $end)
            ->whereRaw("coalesce(estimated_undock_at, undocked_at, '2999-12-31 23:59:59') >= ?", [$start]);

        // Requests still awaiting approval hold their slot so two requests cannot claim the same dates.
        $pending_query = ProjectDockingRequest::query()
            ->whereRaw('coalesce(recommended_docking_space_id, requested_docking_space_id) = ?', [$docking_space_id])
            ->whereIn('request_status', ProjectDockingRequest::PENDING_STATUSES)
            ->where('requested_start_at', '<=', $end)
            ->whereRaw("coalesce(requested_end_at, '2999-12-31 23:59:59') >= ?", [$start]);

        if ($exclude_request_id !== null) {
            $occupancy_query->where(function ($builder) use ($exclude_request_id): void {
                $builder->whereNull('project_docking_request_id')
                    ->orWhere('project_docking_request_id', '!=', $exclude_request_id);
            });
            $pending_query->where('id', '!=', $exclude_request_id);
        }

        return $occupancy_query->exists() || $pending_query->exists();
    }

    /**
     * Checks schedule availability without making a preliminary ship-fit result a submission blocker.
     *
     * @return array{ok: bool, docking_space: ?DockingSpace, error: ?string}
     */
    public function check(string $start_at, ?string $end_at, ?int $preferred_space_id = null): array
    {
        $spaces = DockingSpace::query()
            ->where('status', 'active')
            ->when($preferred_space_id !== null, fn ($query) => $query->where('id', $preferred_space_id))
            ->orderBy('max_length')
            ->orderBy('name')
            ->get();

        if ($spaces->isEmpty()) {
            return $this->failure('Docking space yang dipilih tidak aktif atau tidak tersedia.');
        }

        $free_spaces = $spaces->reject(
            fn (DockingSpace $space): bool => $this->has_schedule_conflict($space->id, $start_at, $end_at)
        );

        if ($free_spaces->isEmpty()) {
            return $this->failure('Jadwal docking bentrok dengan jadwal lain pada ' . ($preferred_space_id !== null ? 'docking space yang dipilih' : 'seluruh docking space aktif') . '. Silakan ubah tanggal.');
        }

        return ['ok' => true, 'docking_space' => $free_spaces->first(), 'error' => null];
    }

    /**
     * Earliest free windows on one dock, starting no earlier than $earliest_start and keeping the requested duration.
     *
     * @return list<array{start: CarbonImmutable, end: CarbonImmutable}>
     */
    public function suggest_slots(
        int $docking_space_id,
        string $earliest_start,
        int $duration_minutes,
        ?int $exclude_request_id = null,
        int $limit = 3
    ): array {
        $candidate = CarbonImmutable::parse($earliest_start);
        $horizon = $candidate->addYear();
        $suggestions = [];

        for ($guard = 0; $guard < 200 && count($suggestions) < $limit && $candidate < $horizon; $guard++) {
            $candidate_end = $candidate->addMinutes($duration_minutes);

            if (! $this->has_schedule_conflict($docking_space_id, $candidate->toDateTimeString(), $candidate_end->toDateTimeString(), $exclude_request_id)) {
                $suggestions[] = ['start' => $candidate, 'end' => $candidate_end];
                // Offer the next option after this window so choices are meaningfully different.
                $candidate = $candidate_end->addDay();

                continue;
            }

            $blocker_end = $this->blocking_end($docking_space_id, $candidate, $candidate_end, $exclude_request_id);

            if ($blocker_end === null) {
                break;
            }

            $candidate = $blocker_end->addMinute()->startOfMinute();
        }

        return $suggestions;
    }

    /** Latest end among bookings overlapping the window; null when a booking has no end date (dock blocked indefinitely). */
    private function blocking_end(int $docking_space_id, CarbonImmutable $start, CarbonImmutable $end, ?int $exclude_request_id): ?CarbonImmutable
    {
        $occupancies = DockingOccupancy::query()
            ->where('docking_space_id', $docking_space_id)
            ->whereIn('occupancy_status', ['scheduled', 'occupied'])
            ->where('docked_at', '<=', $end->toDateTimeString())
            ->when($exclude_request_id !== null, fn ($query) => $query->where(function ($builder) use ($exclude_request_id): void {
                $builder->whereNull('project_docking_request_id')->orWhere('project_docking_request_id', '!=', $exclude_request_id);
            }))
            ->get()
            ->map(fn (DockingOccupancy $item) => $item->estimated_undock_at ?? $item->undocked_at)
            ->all();

        $pending = ProjectDockingRequest::query()
            ->whereRaw('coalesce(recommended_docking_space_id, requested_docking_space_id) = ?', [$docking_space_id])
            ->whereIn('request_status', ProjectDockingRequest::PENDING_STATUSES)
            ->where('requested_start_at', '<=', $end->toDateTimeString())
            ->when($exclude_request_id !== null, fn ($query) => $query->where('id', '!=', $exclude_request_id))
            ->pluck('requested_end_at')
            ->all();

        $latest = null;

        foreach ([...$occupancies, ...$pending] as $booking_end) {
            if ($booking_end === null) {
                return null;
            }

            $booking_end = CarbonImmutable::parse($booking_end);

            if ($booking_end >= $start && ($latest === null || $booking_end > $latest)) {
                $latest = $booking_end;
            }
        }

        return $latest ?? $start->addDay();
    }

    private function failure(string $message): array
    {
        return ['ok' => false, 'docking_space' => null, 'error' => $message];
    }
}
