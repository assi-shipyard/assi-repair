<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DockingOccupancy;
use App\Models\DockingSpace;
use App\Models\ProjectDockingRequest;
use App\Models\Ship;
use Carbon\CarbonImmutable;

class DockingRequestCheckService
{
    public function __construct(private readonly DockingCapacityService $docking_capacity_service)
    {
    }

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
            ->where('requested_docking_space_id', $docking_space_id)
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
     * System check: ship specs complete, schedule free, then ship fits the space.
     *
     * @return array{ok: bool, docking_space: ?DockingSpace, error: ?string}
     */
    public function check(Ship $ship, string $start_at, ?string $end_at, ?int $preferred_space_id = null): array
    {
        $missing_specs = $this->missing_ship_specs($ship);

        if ($missing_specs !== []) {
            return $this->failure('Spesifikasi kapal belum lengkap (' . implode(', ', $missing_specs) . '). Lengkapi data kapal terlebih dahulu.');
        }

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

        $violations = [];

        foreach ($free_spaces as $space) {
            $evaluation = $this->docking_capacity_service->evaluate_ship_for_space($ship, $space, 0);

            if ($evaluation['is_compatible']) {
                return ['ok' => true, 'docking_space' => $space, 'error' => null];
            }

            $violations[$space->name] = $evaluation['violations'];
        }

        $detail = collect($violations)
            ->map(fn (array $messages, string $name): string => $name . ': ' . implode(' ', $messages))
            ->implode(' | ');

        return $this->failure('Tidak ada docking space yang mampu menampung kapal ini. ' . $detail);
    }

    private function missing_ship_specs(Ship $ship): array
    {
        $missing = [];

        if (blank($ship->loaded_draft ?? $ship->empty_draft)) {
            $missing[] = 'draft';
        }

        if (blank($ship->breadth)) {
            $missing[] = 'lebar';
        }

        if (blank($ship->length_overall)) {
            $missing[] = 'panjang';
        }

        if (blank($ship->gross_tonnage ?? $ship->net_tonnage)) {
            $missing[] = 'tonnage';
        }

        return $missing;
    }

    private function failure(string $message): array
    {
        return ['ok' => false, 'docking_space' => null, 'error' => $message];
    }
}
