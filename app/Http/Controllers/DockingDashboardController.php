<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\DockingOccupancy;
use App\Models\DockingSpace;
use App\Models\FloatingRepairHistory;
use App\Models\ProjectDockingRequest;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;

class DockingDashboardController extends Controller
{
    private const WINDOW_DAYS = 30;
    private const STALE_REQUEST_DAYS = 3;

    public function index(): View
    {
        $now = CarbonImmutable::now();
        $window_end = $now->addDays(self::WINDOW_DAYS);

        $spaces = DockingSpace::query()->orderBy('name')->get();

        $bookings = DockingOccupancy::with(['ship.company', 'project'])
            ->whereIn('occupancy_status', ['scheduled', 'occupied'])
            ->where('docked_at', '<=', $window_end)
            ->get()
            ->filter(fn (DockingOccupancy $item): bool => ($item->estimated_undock_at ?? $window_end) >= $now || $item->occupancy_status === 'occupied');

        $space_rows = $spaces->map(function (DockingSpace $space) use ($bookings, $now, $window_end): array {
            $space_bookings = $bookings->where('docking_space_id', $space->id);
            $current = $space_bookings->firstWhere('occupancy_status', 'occupied');
            $next = $space_bookings->where('occupancy_status', 'scheduled')->where('docked_at', '>', $now)->sortBy('docked_at')->first();

            return [
                'space' => $space,
                'current' => $current,
                'next' => $next,
                'is_overdue' => $current?->estimated_undock_at !== null && $current->estimated_undock_at < $now,
                'utilization' => $this->utilization_percent($space_bookings, $now, $window_end),
            ];
        });

        $active_rows = $space_rows->filter(fn (array $row): bool => $row['space']->status === 'active');
        $occupied_count = $active_rows->filter(fn (array $row): bool => $row['current'] !== null)->count();
        $active_count = $active_rows->count();

        $pending_counts = ProjectDockingRequest::query()
            ->selectRaw('request_status, count(*) as total')
            ->whereIn('request_status', ProjectDockingRequest::PENDING_STATUSES)
            ->groupBy('request_status')
            ->pluck('total', 'request_status');

        $stale_requests = ProjectDockingRequest::with(['ship', 'requested_docking_space'])
            ->whereIn('request_status', ProjectDockingRequest::PENDING_STATUSES)
            ->where('updated_at', '<=', $now->subDays(self::STALE_REQUEST_DAYS))
            ->orderBy('updated_at')
            ->limit(5)
            ->get();

        $upcoming = $bookings
            ->where('occupancy_status', 'scheduled')
            ->where('docked_at', '>', $now)
            ->sortBy('docked_at')
            ->values();

        $overdue_rows = $space_rows->filter(fn (array $row): bool => $row['is_overdue'])->values();

        $upcoming_undockings = $bookings
            ->where('occupancy_status', 'occupied')
            ->filter(fn (DockingOccupancy $item): bool => $item->estimated_undock_at !== null && $item->estimated_undock_at >= $now && $item->estimated_undock_at <= $now->addDays(7))
            ->sortBy('estimated_undock_at')
            ->values();

        return view('docking-dashboard', [
            'now' => $now,
            'window_days' => self::WINDOW_DAYS,
            'space_rows' => $space_rows,
            'active_count' => $active_count,
            'occupied_count' => $occupied_count,
            'free_count' => $active_count - $occupied_count,
            'occupancy_percent' => $active_count > 0 ? (int) round($occupied_count / $active_count * 100) : 0,
            'average_utilization' => $active_rows->isNotEmpty() ? (int) round($active_rows->avg('utilization')) : 0,
            'inactive_spaces' => $space_rows->filter(fn (array $row): bool => $row['space']->status !== 'active')->values(),
            'pending_counts' => $pending_counts,
            'awaiting_marketing_count' => (int) ($pending_counts['awaiting_marketing'] ?? 0),
            'stale_requests' => $stale_requests,
            'upcoming' => $upcoming->take(8),
            'arrivals_7_days' => $upcoming->filter(fn (DockingOccupancy $item): bool => $item->docked_at <= $now->addDays(7))->count(),
            'overdue_rows' => $overdue_rows,
            'upcoming_undockings' => $upcoming_undockings,
            'floating_active' => FloatingRepairHistory::with(['ship', 'project'])->where('floating_status', 'active')->orderBy('floating_started_at')->get(),
        ]);
    }

    /** Share of the window during which the dock is booked, merging overlapping bookings so days are not counted twice. */
    private function utilization_percent($bookings, CarbonImmutable $now, CarbonImmutable $window_end): int
    {
        $ranges = $bookings
            ->map(fn (DockingOccupancy $item): array => [
                max($item->docked_at->getTimestamp(), $now->getTimestamp()),
                min(($item->estimated_undock_at ?? $window_end)->getTimestamp(), $window_end->getTimestamp()),
            ])
            ->filter(fn (array $range): bool => $range[1] > $range[0])
            ->sortBy(0)
            ->values();

        $booked_seconds = 0;
        $cursor = null;

        foreach ($ranges as [$start, $end]) {
            $start = $cursor !== null ? max($start, $cursor) : $start;

            if ($end > $start) {
                $booked_seconds += $end - $start;
                $cursor = $end;
            }
        }

        return (int) min(100, round($booked_seconds / max(1, $window_end->getTimestamp() - $now->getTimestamp()) * 100));
    }
}
