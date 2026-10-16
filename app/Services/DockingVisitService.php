<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DockingVisitException;
use App\Models\DockingOccupancy;
use App\Models\DockingSpace;
use App\Models\FloatingRepairHistory;
use App\Models\ProjectDockingRequest;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Ship stay in the shipyard after a docking request is approved:
 * awaiting_arrival → arrived → docked → (floating →) ready_to_depart → departed.
 */
class DockingVisitService
{
    public function __construct(
        private readonly DockingRequestCheckService $check_service,
        private readonly DockingCapacityService $capacity_service,
    ) {}

    public function record_arrival(ProjectDockingRequest $docking_request, string $arrived_at, ?string $notes, int $user_id): void
    {
        $this->ensure_stage($docking_request, ['awaiting_arrival'], 'Kedatangan hanya dapat dicatat untuk kapal yang sedang ditunggu.');

        $docking_request->update([
            'visit_stage' => 'arrived',
            'arrived_at' => CarbonImmutable::parse($arrived_at),
            'arrived_recorded_by' => $user_id,
            'arrival_notes' => $notes,
        ]);
    }

    /**
     * @param  array{docking_space_id?: int|string|null, docked_at: string, estimated_undock_at?: string|null, remarks?: string|null}  $data
     */
    public function dock_in(ProjectDockingRequest $docking_request, array $data, int $user_id): void
    {
        $this->ensure_stage($docking_request, ['arrived'], 'Catat kedatangan kapal terlebih dahulu sebelum kapal masuk dock.');

        $docked_at = CarbonImmutable::parse($data['docked_at']);

        if ($docking_request->arrived_at !== null && $docked_at->lt($docking_request->arrived_at)) {
            throw new DockingVisitException('Waktu masuk dock tidak boleh lebih awal dari waktu kedatangan kapal.');
        }

        $docking_space_id = (int) ($data['docking_space_id'] ?? $docking_request->effective_docking_space_id() ?? 0);

        if ($docking_space_id <= 0) {
            throw new DockingVisitException('Docking space untuk pelaksanaan docking belum ditentukan.');
        }

        $estimated_undock_at = $data['estimated_undock_at'] ?? null;

        if ($this->check_service->has_schedule_conflict($docking_space_id, (string) $docked_at, $estimated_undock_at, $docking_request->id)) {
            throw new DockingVisitException('Pelaksanaan docking bentrok dengan jadwal docking lain pada docking space yang dipilih.');
        }

        $docking_space = DockingSpace::findOrFail($docking_space_id);
        $evaluation = $this->capacity_service->evaluate_ship_for_space($docking_request->ship, $docking_space);

        if (! ($evaluation['is_compatible'] ?? false)) {
            throw new DockingVisitException('Docking space tidak kompatibel untuk kapal ini. Periksa hasil evaluasi kapasitas.');
        }

        DB::transaction(function () use ($docking_request, $docking_space_id, $docked_at, $estimated_undock_at, $data, $user_id): void {
            $docking_request->docking_occupancies()
                ->where('occupancy_status', 'scheduled')
                ->update([
                    'occupancy_status' => 'cancelled',
                    'remarks' => 'Jadwal lama digantikan oleh realisasi masuk dock.',
                    'updated_at' => now(),
                ]);

            DockingOccupancy::create([
                'project_id' => $docking_request->project_id,
                'ship_id' => $docking_request->ship_id,
                'docking_space_id' => $docking_space_id,
                'project_docking_request_id' => $docking_request->id,
                'docked_at' => $docked_at,
                'estimated_undock_at' => $estimated_undock_at,
                'undocked_at' => null,
                'occupancy_status' => 'occupied',
                'remarks' => $data['remarks'] ?? null,
                'created_by' => $user_id,
            ]);

            $docking_request->update(['visit_stage' => 'docked']);
        });
    }

    public function undock(ProjectDockingRequest $docking_request, string $undocked_at, bool $continue_floating, ?string $floating_started_at, ?string $notes, int $user_id): void
    {
        $this->ensure_stage($docking_request, ['docked'], 'Kapal tidak sedang berada di dalam dock.');

        $occupancy = $this->current_occupancy($docking_request);

        if (! $occupancy) {
            throw new DockingVisitException('Data okupansi dock aktif untuk kapal ini tidak ditemukan.');
        }

        $this->undock_occupancy($occupancy, $undocked_at, $continue_floating, $floating_started_at, $notes, $user_id);
    }

    public function undock_occupancy(DockingOccupancy $occupancy, string $undocked_at, bool $continue_floating, ?string $floating_started_at, ?string $notes, int $user_id): void
    {
        if ($occupancy->occupancy_status !== 'occupied' || $occupancy->undocked_at !== null) {
            throw new DockingVisitException('Data okupansi sudah tidak aktif untuk proses undock.');
        }

        $undocked = CarbonImmutable::parse($undocked_at);

        if ($undocked->lt($occupancy->docked_at)) {
            throw new DockingVisitException('Waktu undock tidak boleh lebih awal dari waktu masuk dock.');
        }

        DB::transaction(function () use ($occupancy, $undocked, $continue_floating, $floating_started_at, $notes, $user_id): void {
            $occupancy->update([
                'undocked_at' => $undocked,
                'occupancy_status' => 'undocked',
                'remarks' => $notes ?? $occupancy->remarks,
            ]);

            if ($continue_floating) {
                FloatingRepairHistory::create([
                    'project_id' => $occupancy->project_id,
                    'ship_id' => $occupancy->ship_id,
                    'docking_occupancy_id' => $occupancy->id,
                    'floating_started_at' => $floating_started_at !== null ? CarbonImmutable::parse($floating_started_at) : $undocked,
                    'floating_status' => 'active',
                    'notes' => $notes,
                    'created_by' => $user_id,
                ]);
            }

            $occupancy->docking_request?->update(['visit_stage' => $continue_floating ? 'floating' : 'ready_to_depart']);
        });
    }

    public function complete_floating(ProjectDockingRequest $docking_request, string $completed_at, ?string $notes): void
    {
        $this->ensure_stage($docking_request, ['floating'], 'Kapal tidak sedang menjalani pekerjaan atas air.');

        $floating = FloatingRepairHistory::query()
            ->whereIn('docking_occupancy_id', $docking_request->docking_occupancies()->select('id'))
            ->where('floating_status', 'active')
            ->latest('floating_started_at')
            ->first();

        if (! $floating) {
            throw new DockingVisitException('Riwayat pekerjaan atas air yang aktif tidak ditemukan.');
        }

        $this->complete_floating_history($floating, $completed_at, $notes);
    }

    public function complete_floating_history(FloatingRepairHistory $floating, string $completed_at, ?string $notes): void
    {
        $completed = CarbonImmutable::parse($completed_at);

        if ($completed->lt($floating->floating_started_at)) {
            throw new DockingVisitException('Waktu selesai pekerjaan atas air tidak boleh lebih awal dari waktu mulainya.');
        }

        DB::transaction(function () use ($floating, $completed, $notes): void {
            $floating->update([
                'floating_completed_at' => $completed,
                'floating_status' => 'completed',
                'notes' => $notes ?? $floating->notes,
            ]);

            $floating->docking_occupancy?->docking_request?->update(['visit_stage' => 'ready_to_depart']);
        });
    }

    public function confirm_administration(ProjectDockingRequest $docking_request, string $notes, int $user_id): void
    {
        $this->ensure_stage($docking_request, ProjectDockingRequest::IN_YARD_STAGES, 'Administrasi hanya dapat dikonfirmasi saat kapal berada di galangan.');

        $docking_request->update([
            'administration_completed_at' => now(),
            'administration_completed_by' => $user_id,
            'administration_notes' => $notes,
        ]);
    }

    public function depart(ProjectDockingRequest $docking_request, string $departed_at, ?string $notes, int $user_id): void
    {
        $blockers = $this->departure_blockers($docking_request);

        if ($blockers !== []) {
            throw new DockingVisitException('Kapal belum dapat keluar galangan: '.implode(' ', $blockers));
        }

        $departed = CarbonImmutable::parse($departed_at);
        $last_event = $this->last_yard_event_at($docking_request);

        if ($last_event !== null && $departed->lt($last_event)) {
            throw new DockingVisitException('Waktu keluar galangan tidak boleh lebih awal dari '.$last_event->tanggal_waktu_indonesia().' WIB.');
        }

        $docking_request->update([
            'request_status' => 'completed',
            'visit_stage' => 'departed',
            'departed_at' => $departed,
            'departed_recorded_by' => $user_id,
            'departure_notes' => $notes,
        ]);
    }

    /**
     * @return list<string>
     */
    public function departure_blockers(ProjectDockingRequest $docking_request): array
    {
        if ($docking_request->request_status !== 'approved' || ! in_array($docking_request->visit_stage, ProjectDockingRequest::IN_YARD_STAGES, true)) {
            return ['Kapal tidak sedang berada di galangan.'];
        }

        $blockers = match ($docking_request->visit_stage) {
            'arrived' => ['Kapal belum menjalani docking.'],
            'docked' => ['Kapal masih berada di dalam dock. Lakukan undocking terlebih dahulu.'],
            'floating' => ['Pekerjaan atas air masih berjalan.'],
            default => [],
        };

        $project = $docking_request->project;

        if ($project === null) {
            $blockers[] = 'Permohonan belum terhubung ke proyek.';
        } elseif ($project->status !== 'Completed') {
            $blockers[] = 'Proyek '.$project->project_code.' belum berstatus selesai (Completed).';
        }

        if ($docking_request->administration_completed_at === null) {
            $blockers[] = 'Administrasi (BAST, invoice, clearance) belum dikonfirmasi selesai.';
        }

        return $blockers;
    }

    public function current_occupancy(ProjectDockingRequest $docking_request): ?DockingOccupancy
    {
        return $docking_request->docking_occupancies()
            ->where('occupancy_status', 'occupied')
            ->whereNull('undocked_at')
            ->latest('docked_at')
            ->first();
    }

    private function last_yard_event_at(ProjectDockingRequest $docking_request): ?CarbonImmutable
    {
        $occupancy_ids = $docking_request->docking_occupancies()->select('id');

        $candidates = array_filter([
            $docking_request->arrived_at,
            $docking_request->docking_occupancies()->max('undocked_at'),
            FloatingRepairHistory::query()->whereIn('docking_occupancy_id', $occupancy_ids)->max('floating_completed_at'),
        ]);

        if ($candidates === []) {
            return null;
        }

        return collect($candidates)
            ->map(fn ($value): CarbonImmutable => CarbonImmutable::parse($value))
            ->max();
    }

    /**
     * @param  list<string>  $stages
     */
    private function ensure_stage(ProjectDockingRequest $docking_request, array $stages, string $message): void
    {
        if ($docking_request->request_status !== 'approved' || ! in_array($docking_request->visit_stage, $stages, true)) {
            throw new DockingVisitException($message);
        }
    }
}
