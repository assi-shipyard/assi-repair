<?php

namespace App\Http\Controllers;

use App\Http\Requests\Docking\CompleteFloatingRepairRequest;
use App\Http\Requests\Docking\EvaluateDockingRequest;
use App\Http\Requests\Docking\ReviewProjectDockingRequest;
use App\Http\Requests\Docking\StartDockingOccupancyRequest;
use App\Http\Requests\Docking\StoreProjectDockingRequest;
use App\Http\Requests\Docking\UndockToFloatingRepairRequest;
use App\Models\DockingCapacityEvaluation;
use App\Models\DockingOccupancy;
use App\Models\DockingSpace;
use App\Models\FloatingRepairHistory;
use App\Models\Project;
use App\Models\ProjectDockingRequest;
use App\Services\DockingCapacityService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DockingManagementController extends Controller
{
    public function index_docking_space(): \Illuminate\Contracts\View\View
    {
        $docking_spaces = DockingSpace::query()
            ->orderBy('name')
            ->get();

        return view('docking-space.index', [
            'dockingSpaces' => $docking_spaces,
            'editingSpace' => null,
        ]);
    }

    public function store_docking_space(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:active,maintenance,inactive'],
            'max_draft' => ['nullable', 'numeric'],
            'max_length' => ['nullable', 'numeric'],
            'max_breadth' => ['nullable', 'numeric'],
            'max_width' => ['nullable', 'numeric'],
            'max_weight' => ['nullable', 'numeric'],
            'max_capacity' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string'],
        ]);

        DockingSpace::create($data);

        return redirect()->route('docking-space.index')->with('success', 'Docking space berhasil ditambahkan.');
    }

    public function edit_docking_space(string $docking_space): \Illuminate\Contracts\View\View
    {
        $docking_spaces = DockingSpace::query()
            ->orderBy('name')
            ->get();

        $editing_space = $this->find_docking_space($docking_space);

        return view('docking-space.index', [
            'dockingSpaces' => $docking_spaces,
            'editingSpace' => $editing_space,
        ]);
    }

    public function update_docking_space(Request $request, string $docking_space): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:active,maintenance,inactive'],
            'max_draft' => ['nullable', 'numeric'],
            'max_length' => ['nullable', 'numeric'],
            'max_breadth' => ['nullable', 'numeric'],
            'max_width' => ['nullable', 'numeric'],
            'max_weight' => ['nullable', 'numeric'],
            'max_capacity' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string'],
        ]);

        $docking_space = $this->find_docking_space($docking_space);
        $docking_space->update($data);

        return redirect()->route('docking-space.index')->with('success', 'Docking space berhasil diperbarui.');
    }

    public function destroy_docking_space(string $docking_space): RedirectResponse
    {
        $docking_space = $this->find_docking_space($docking_space);
        $docking_space->delete();

        return redirect()->route('docking-space.index')->with('success', 'Docking space berhasil dihapus.');
    }

    public function create_request(): \Illuminate\Contracts\View\View
    {
        $projects = Project::with('ship')
            ->orderByDesc('created_at')
            ->get();

        $ships = \App\Models\Ship::query()
            ->with('company')
            ->orderBy('name')
            ->get();

        $docking_spaces = DockingSpace::query()
            ->orderBy('name')
            ->get();

        return view('docking-space-request.create', compact('projects', 'ships', 'docking_spaces'));
    }

    public function index_request(Request $request): \Illuminate\Contracts\View\View
    {
        $status = trim((string) $request->query('status', ''));

        $docking_requests = ProjectDockingRequest::with([
            'project',
            'ship',
            'requested_docking_space',
            'capacity_evaluations.docking_space',
        ])
            ->when($status !== '', function ($query) use ($status) {
                $query->where('request_status', $status);
            })
            ->orderByDesc('requested_start_at')
            ->orderByDesc('created_at')
            ->get();

        $docking_spaces = DockingSpace::query()
            ->orderBy('name')
            ->get();

        return view('docking-space-request.index', compact('docking_requests', 'status', 'docking_spaces'));
    }

    public function store_request(StoreProjectDockingRequest $request, DockingCapacityService $docking_capacity_service): RedirectResponse
    {
        $data = $request->validated();

        $project = null;
        $ship_id = null;

        if (! empty($data['project_id'])) {
            $project = Project::with('ship')->findOrFail((int) $data['project_id']);
            $ship_id = (int) ($project->ship_id ?? 0);
        } elseif (! empty($data['ship_id'])) {
            $ship_id = (int) $data['ship_id'];
            $project = Project::query()
                ->with('ship')
                ->where('ship_id', $ship_id)
                ->orderByDesc('created_at')
                ->first();

            if (! $project) {
                return back()->with('error', 'Kapal yang dipilih belum memiliki proyek aktif. Silakan pilih proyek yang sudah ada terlebih dahulu.')->withInput();
            }
        }

        if (! $project || ! $ship_id) {
            return back()->with('error', 'Pilih proyek yang sudah ada atau pilih kapal yang akan diajukan terlebih dahulu.')->withInput();
        }

        if (! $project->ship_id) {
            return back()->with('error', 'Proyek tidak memiliki data kapal yang valid.')->withInput();
        }

        if (! empty($data['requested_docking_space_id'])) {
            $has_conflict = $this->has_space_schedule_conflict(
                (int) $data['requested_docking_space_id'],
                (string) $data['requested_start_at'],
                $data['requested_end_at'] ?? null
            );

            if ($has_conflict) {
                return back()->with('error', 'Jadwal docking bentrok dengan jadwal docking lain pada docking space yang dipilih.')->withInput();
            }
        }

        $user_id = $request->user()?->id;

        DB::transaction(function () use ($data, $project, $ship_id, $user_id, $docking_capacity_service): void {
            $docking_request = ProjectDockingRequest::create([
                'project_id' => $project->id,
                'ship_id' => $ship_id,
                'requested_by' => $user_id,
                'requested_docking_space_id' => $data['requested_docking_space_id'] ?? null,
                'requested_start_at' => $data['requested_start_at'],
                'requested_end_at' => $data['requested_end_at'] ?? null,
                'request_notes' => $data['request_notes'] ?? null,
                'request_status' => 'submitted',
            ]);

            $this->run_capacity_evaluation_for_request($docking_request, $docking_capacity_service, $user_id);
        });

        return redirect()->route('docking-space-request.index')->with('success', 'Permohonan docking space berhasil dibuat dan evaluasi kapasitas telah dihitung.');
    }

    public function evaluate_request(EvaluateDockingRequest $request, string $docking_request, DockingCapacityService $docking_capacity_service): RedirectResponse
    {
        if ($response = $this->ensure_docking_operator($request)) {
            return $response;
        }

        $docking_request = $this->find_docking_request($docking_request, ['ship']);

        $space_ids = $request->validated('docking_space_ids') ?? [];
        $only_active_spaces = (bool) ($request->validated('only_active_spaces') ?? true);
        $user_id = $request->user()?->id;

        $this->run_capacity_evaluation_for_request(
            $docking_request,
            $docking_capacity_service,
            $user_id,
            $space_ids,
            $only_active_spaces
        );

        return back()->with('success', 'Evaluasi kapasitas docking space berhasil diperbarui.');
    }

    public function review_request(ReviewProjectDockingRequest $request, string $docking_request): RedirectResponse
    {
        if ($response = $this->ensure_docking_operator($request)) {
            return $response;
        }

        $docking_request = $this->find_docking_request($docking_request);
        $data = $request->validated();
        $status = (string) $data['request_status'];
        $user_id = $request->user()?->id;

        if ($status === 'approved' && empty($data['approved_docking_space_id'])) {
            return back()->with('error', 'Docking space persetujuan wajib dipilih saat status disetujui.');
        }

        if ($status === 'approved') {
            $has_conflict = $this->has_space_schedule_conflict(
                (int) $data['approved_docking_space_id'],
                (string) $docking_request->requested_start_at,
                $docking_request->requested_end_at,
                $docking_request->id
            );

            if ($has_conflict) {
                return back()->with('error', 'Persetujuan gagal karena jadwal docking bentrok dengan jadwal lain di docking space tersebut.');
            }
        }

        DB::transaction(function () use ($docking_request, $data, $status, $user_id): void {
            $payload = [
                'request_status' => $status,
                'reviewed_by' => $user_id,
                'reviewed_at' => now(),
                'rejection_reason' => null,
            ];

            if ($status === 'approved') {
                $payload['approved_by'] = $user_id;
                $payload['approved_at'] = now();
                $payload['requested_docking_space_id'] = $data['approved_docking_space_id'];
            }

            if ($status === 'rejected') {
                $payload['rejection_reason'] = $data['rejection_reason'] ?? 'Permohonan docking space ditolak.';
            }

            $docking_request->update($payload);

            if ($status === 'approved') {
                DockingOccupancy::firstOrCreate(
                    [
                        'project_docking_request_id' => $docking_request->id,
                        'occupancy_status' => 'scheduled',
                    ],
                    [
                        'project_id' => $docking_request->project_id,
                        'ship_id' => $docking_request->ship_id,
                        'docking_space_id' => $docking_request->requested_docking_space_id,
                        'docked_at' => $docking_request->requested_start_at,
                        'estimated_undock_at' => $docking_request->requested_end_at,
                        'remarks' => 'Jadwal otomatis dari persetujuan permohonan docking.',
                        'created_by' => $user_id,
                    ]
                );
            }
        });

        return back()->with('success', 'Status permohonan docking space berhasil diperbarui.');
    }

    public function start_docking(StartDockingOccupancyRequest $request, string $docking_request, DockingCapacityService $docking_capacity_service): RedirectResponse
    {
        if ($response = $this->ensure_docking_operator($request)) {
            return $response;
        }

        $docking_request = $this->find_docking_request($docking_request, ['ship']);

        if ($docking_request->request_status !== 'approved') {
            return back()->with('error', 'Permohonan harus berstatus disetujui sebelum kapal dapat masuk dock.');
        }

        $data = $request->validated();
        $docking_space_id = isset($data['docking_space_id'])
            ? (int) $data['docking_space_id']
            : (int) ($docking_request->requested_docking_space_id ?? 0);

        if ($docking_space_id <= 0) {
            return back()->with('error', 'Docking space untuk pelaksanaan docking belum ditentukan.');
        }

        $has_conflict = $this->has_space_schedule_conflict(
            $docking_space_id,
            (string) $data['docked_at'],
            $data['estimated_undock_at'] ?? null,
            $docking_request->id
        );

        if ($has_conflict) {
            return back()->with('error', 'Pelaksanaan docking bentrok dengan jadwal docking lain pada docking space yang dipilih.');
        }

        $docking_space = DockingSpace::findOrFail($docking_space_id);
        $evaluation = $docking_capacity_service->evaluate_ship_for_space($docking_request->ship, $docking_space);

        if (! ($evaluation['is_compatible'] ?? false)) {
            return back()->with('error', 'Docking space tidak kompatibel untuk kapal ini. Periksa hasil evaluasi kapasitas.');
        }

        $user_id = $request->user()?->id;

        DB::transaction(function () use ($docking_request, $docking_space_id, $data, $user_id): void {
            DockingOccupancy::query()
                ->where('project_docking_request_id', $docking_request->id)
                ->where('occupancy_status', 'scheduled')
                ->update([
                    'occupancy_status' => 'cancelled',
                    'remarks' => 'Jadwal lama dibatalkan karena realisasi docking terbaru.',
                    'updated_at' => now(),
                ]);

            DockingOccupancy::create([
                'project_id' => $docking_request->project_id,
                'ship_id' => $docking_request->ship_id,
                'docking_space_id' => $docking_space_id,
                'project_docking_request_id' => $docking_request->id,
                'docked_at' => $data['docked_at'],
                'estimated_undock_at' => $data['estimated_undock_at'] ?? null,
                'undocked_at' => null,
                'occupancy_status' => 'occupied',
                'remarks' => $data['remarks'] ?? null,
                'created_by' => $user_id,
            ]);
        });

        return back()->with('success', 'Status okupansi kapal berhasil diubah menjadi sedang docking.');
    }

    public function undock_to_floating(UndockToFloatingRepairRequest $request, string $occupancy): RedirectResponse
    {
        if ($response = $this->ensure_docking_operator($request)) {
            return $response;
        }

        $occupancy = $this->find_docking_occupancy($occupancy, ['project', 'ship']);

        if ($occupancy->occupancy_status !== 'occupied' || $occupancy->undocked_at !== null) {
            return back()->with('error', 'Data okupansi sudah tidak aktif untuk proses undock.');
        }

        $data = $request->validated();
        $floating_started_at = $data['floating_started_at'] ?? $data['undocked_at'];
        $user_id = $request->user()?->id;

        DB::transaction(function () use ($occupancy, $data, $floating_started_at, $user_id): void {
            $occupancy->update([
                'undocked_at' => $data['undocked_at'],
                'occupancy_status' => 'undocked',
                'remarks' => $data['notes'] ?? $occupancy->remarks,
            ]);

            FloatingRepairHistory::create([
                'project_id' => $occupancy->project_id,
                'ship_id' => $occupancy->ship_id,
                'docking_occupancy_id' => $occupancy->id,
                'floating_started_at' => $floating_started_at,
                'floating_status' => 'active',
                'notes' => $data['notes'] ?? null,
                'created_by' => $user_id,
            ]);
        });

        return back()->with('success', 'Kapal berhasil diundock dan riwayat floating repair aktif telah dibuat.');
    }

    public function complete_floating(CompleteFloatingRepairRequest $request, string $occupancy): RedirectResponse
    {
        if ($response = $this->ensure_docking_operator($request)) {
            return $response;
        }

        $occupancy = $this->find_docking_occupancy($occupancy);

        $floating_history = FloatingRepairHistory::query()
            ->where('docking_occupancy_id', $occupancy->id)
            ->where('floating_status', 'active')
            ->latest('floating_started_at')
            ->first();

        if (! $floating_history) {
            return back()->with('error', 'Riwayat floating repair aktif tidak ditemukan untuk okupansi ini.');
        }

        $data = $request->validated();

        $floating_history->update([
            'floating_completed_at' => $data['floating_completed_at'],
            'floating_status' => 'completed',
            'notes' => $data['notes'] ?? $floating_history->notes,
        ]);

        return back()->with('success', 'Riwayat floating repair berhasil diselesaikan.');
    }

    public function docking_space_availability(Request $request): \Illuminate\Contracts\View\View
    {
        $selected_month = (int) $request->query('month', now()->month);
        if ($selected_month < 1 || $selected_month > 12) {
            $selected_month = now()->month;
        }

        $selected_year = (int) $request->query('year', now()->year);
        if ($selected_year < 2000 || $selected_year > 2100) {
            $selected_year = now()->year;
        }

        $month_start = CarbonImmutable::create($selected_year, $selected_month, 1, 0, 0, 0);
        $month_end = $month_start->endOfMonth();
        $total_days_in_month = $month_end->day;
        $day_numbers = range(1, $total_days_in_month);

        $docking_spaces = DockingSpace::with([
            'docking_occupancies' => function ($query) use ($month_start, $month_end) {
                $query
                    ->with(['project', 'ship'])
                    ->where('occupancy_status', '!=', 'cancelled')
                    ->where('docked_at', '<=', $month_end->toDateTimeString())
                    ->whereRaw(
                        "coalesce(estimated_undock_at, undocked_at, docked_at) >= ?",
                        [$month_start->toDateTimeString()]
                    )
                    ->orderBy('docked_at');
            },
        ])
            ->withCount([
                'docking_occupancies as current_occupied_count' => function ($query) {
                    $query->where('occupancy_status', 'occupied')->whereNull('undocked_at');
                },
                'docking_occupancies as scheduled_count' => function ($query) {
                    $query->where('occupancy_status', 'scheduled');
                },
            ])
            ->orderBy('name')
            ->get();

        $schedule_cards = [];

        foreach ($docking_spaces as $docking_space) {
            $rows = [];

            foreach ($docking_space->docking_occupancies as $occupancy) {
                $start_at = CarbonImmutable::parse($occupancy->docked_at);
                $raw_end = $occupancy->estimated_undock_at
                    ?? $occupancy->undocked_at
                    ?? $occupancy->docked_at;
                $end_at = CarbonImmutable::parse((string) $raw_end);

                $clamped_start = $start_at->lessThan($month_start) ? $month_start : $start_at;
                $clamped_end = $end_at->greaterThan($month_end) ? $month_end : $end_at;

                if ($clamped_end->lessThan($clamped_start)) {
                    continue;
                }

                $day_cells = [];

                foreach ($day_numbers as $day_number) {
                    $day_date = $month_start->setDay($day_number);

                    $is_active_day = $day_date->betweenIncluded(
                        $clamped_start->startOfDay(),
                        $clamped_end->endOfDay()
                    );

                    $day_cells[$day_number] = $is_active_day;
                }

                $active_days_count = count(array_filter($day_cells));

                $rows[] = [
                    'occupancy_status' => (string) $occupancy->occupancy_status,
                    'project_code' => $occupancy->project?->project_code ?? '-',
                    'ship_name' => $occupancy->ship?->name ?? '-',
                    'start_at' => $start_at,
                    'end_at' => $end_at,
                    'active_days_count' => $active_days_count,
                    'day_cells' => $day_cells,
                ];
            }

            usort($rows, function (array $first, array $second): int {
                return $first['start_at']->timestamp <=> $second['start_at']->timestamp;
            });

            $schedule_cards[] = [
                'docking_space_id' => $docking_space->id,
                'docking_space_name' => $docking_space->name,
                'location' => $docking_space->location,
                'status' => $docking_space->status,
                'current_occupied_count' => $docking_space->current_occupied_count,
                'scheduled_count' => $docking_space->scheduled_count,
                'rows' => $rows,
            ];
        }

        $docked_at_min = DockingOccupancy::query()->min('docked_at');
        $docked_at_max = DockingOccupancy::query()
            ->selectRaw("max(coalesce(estimated_undock_at, undocked_at, docked_at)) as end_at")
            ->value('end_at');

        $min_year = $docked_at_min ? CarbonImmutable::parse((string) $docked_at_min)->year : now()->year - 2;
        $max_year = $docked_at_max ? CarbonImmutable::parse((string) $docked_at_max)->year : now()->year + 2;

        $range_start = min($min_year, now()->year - 2);
        $range_end = max($max_year, now()->year + 2);

        $year_options = range($range_start, $range_end);
        $month_options = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return view('docking-space-availability.index', [
            'selected_month' => $selected_month,
            'selected_year' => $selected_year,
            'month_options' => $month_options,
            'year_options' => $year_options,
            'day_numbers' => $day_numbers,
            'total_days_in_month' => $total_days_in_month,
            'schedule_cards' => $schedule_cards,
        ]);
    }

    public function current_docking(): \Illuminate\Contracts\View\View
    {
        $current_occupancies = DockingOccupancy::with(['project', 'ship', 'docking_space'])
            ->current()
            ->orderByDesc('docked_at')
            ->get();

        return view('ship-docking.current', compact('current_occupancies'));
    }

    public function docking_history(): \Illuminate\Contracts\View\View
    {
        $docking_histories = DockingOccupancy::with([
            'project',
            'ship',
            'docking_space',
            'floating_repair_histories',
        ])
            ->orderByDesc('docked_at')
            ->get();

        return view('ship-docking.history', compact('docking_histories'));
    }

    private function run_capacity_evaluation_for_request(
        ProjectDockingRequest $docking_request,
        DockingCapacityService $docking_capacity_service,
        ?int $user_id,
        array $space_ids = [],
        bool $only_active_spaces = true
    ): void {
        $docking_request->loadMissing('ship');

        $spaces_query = DockingSpace::query();

        if (! empty($space_ids)) {
            $spaces_query->whereIn('id', $space_ids);
        }

        if ($only_active_spaces) {
            $spaces_query->where('status', 'active');
        }

        $docking_spaces = $spaces_query->get();

        foreach ($docking_spaces as $docking_space) {
            $evaluation = $docking_capacity_service->evaluate_ship_for_space($docking_request->ship, $docking_space);

            DockingCapacityEvaluation::updateOrCreate(
                [
                    'project_docking_request_id' => $docking_request->id,
                    'docking_space_id' => $docking_space->id,
                ],
                [
                    'ship_id' => $docking_request->ship_id,
                    'is_compatible' => (bool) ($evaluation['is_compatible'] ?? false),
                    'compatibility_score' => (int) ($evaluation['compatibility_score'] ?? 0),
                    'compatibility_detail' => [
                        'checks' => $evaluation['checks'] ?? [],
                        'violations' => $evaluation['violations'] ?? [],
                    ],
                    'ship_snapshot' => $evaluation['ship_snapshot'] ?? [],
                    'docking_space_snapshot' => $evaluation['docking_space_snapshot'] ?? [],
                    'evaluated_by' => $user_id,
                    'evaluated_at' => now(),
                ]
            );
        }
    }

    private function has_space_schedule_conflict(
        int $docking_space_id,
        string $start_at,
        ?string $end_at,
        ?int $exclude_request_id = null
    ): bool {
        $normalized_start = CarbonImmutable::parse($start_at)->toDateTimeString();
        $normalized_end = $end_at !== null
            ? CarbonImmutable::parse($end_at)->toDateTimeString()
            : CarbonImmutable::parse($start_at)->addDays(30)->toDateTimeString();

        $query = DockingOccupancy::query()
            ->where('docking_space_id', $docking_space_id)
            ->whereIn('occupancy_status', ['scheduled', 'occupied'])
            ->where(function ($builder) use ($normalized_start, $normalized_end) {
                $builder
                    ->where('docked_at', '<=', $normalized_end)
                    ->whereRaw(
                        "coalesce(estimated_undock_at, undocked_at, '2999-12-31 23:59:59') >= ?",
                        [$normalized_start]
                    );
            });

        if ($exclude_request_id !== null) {
            $query->where(function ($builder) use ($exclude_request_id) {
                $builder
                    ->whereNull('project_docking_request_id')
                    ->orWhere('project_docking_request_id', '!=', $exclude_request_id);
            });
        }

        return $query->exists();
    }

    private function ensure_docking_operator(Request $request): ?RedirectResponse
    {
        $user = $request->user();

        $can_manage_docking = $user?->hasRole('admin')
            || $user?->can('docking.manage')
            || $user?->can('project.manage')
            || $user?->can('project.update');

        if (! $can_manage_docking) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk melakukan aksi docking ini.');
        }

        return null;
    }

    private function find_docking_space(string $docking_space): DockingSpace
    {
        if (preg_match('/^[0-9a-fA-F-]{36}$/', $docking_space) === 1) {
            return DockingSpace::query()->where('unique_id', $docking_space)->firstOrFail();
        }

        return DockingSpace::query()->findOrFail((int) $docking_space);
    }

    private function find_docking_request(string $docking_request, array $relations = []): ProjectDockingRequest
    {
        $query = ProjectDockingRequest::query()->with($relations);

        if (preg_match('/^[0-9a-fA-F-]{36}$/', $docking_request) === 1) {
            return $query->where('unique_id', $docking_request)->firstOrFail();
        }

        return $query->findOrFail((int) $docking_request);
    }

    private function find_docking_occupancy(string $occupancy, array $relations = []): DockingOccupancy
    {
        $query = DockingOccupancy::query()->with($relations);

        if (preg_match('/^[0-9a-fA-F-]{36}$/', $occupancy) === 1) {
            return $query->where('unique_id', $occupancy)->firstOrFail();
        }

        return $query->findOrFail((int) $occupancy);
    }
}
