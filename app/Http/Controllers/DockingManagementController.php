<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Docking\CompleteFloatingRepairRequest;
use App\Http\Requests\Docking\EvaluateDockingRequest;
use App\Http\Requests\Docking\MarketingRecommendationResponseRequest;
use App\Http\Requests\Docking\PostponeDockingRequest;
use App\Http\Requests\Docking\ReviewDockingStageRequest;
use App\Http\Requests\Docking\SaveDockingSpaceRequest;
use App\Http\Requests\Docking\StartDockingOccupancyRequest;
use App\Http\Requests\Docking\StoreProjectDockingRequest;
use App\Http\Requests\Docking\UndockToFloatingRepairRequest;
use App\Models\DockingCapacityEvaluation;
use App\Models\DockingOccupancy;
use App\Models\DockingRequestDocument;
use App\Models\DockingSpace;
use App\Models\FloatingRepairHistory;
use App\Models\Project;
use App\Models\ProjectDockingRequest;
use App\Models\Ship;
use App\Services\DockingCapacityService;
use App\Services\DockingRequestCheckService;
use App\Services\DockingRequestDocumentStorage;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function store_docking_space(SaveDockingSpaceRequest $request): RedirectResponse
    {
        DockingSpace::create($request->validated());

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

    public function update_docking_space(SaveDockingSpaceRequest $request, string $docking_space): RedirectResponse
    {
        $docking_space = $this->find_docking_space($docking_space);
        $docking_space->update($request->validated());

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

        $ships = Ship::query()
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
            'documents',
            'engineering_approver',
            'production_approver',
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

    public function rejected_requests(Request $request): \Illuminate\Contracts\View\View
    {
        $stage = (string) $request->query('stage', '');

        $rejected_requests = ProjectDockingRequest::with(['ship.company', 'requested_docking_space', 'requester', 'reviewer', 'documents'])
            ->where('request_status', 'rejected')
            ->when(in_array($stage, ['engineering', 'production'], true), function ($query) use ($stage): void {
                $query->where('rejection_stage', $stage);
            })
            ->orderByDesc('reviewed_at')
            ->orderByDesc('updated_at')
            ->get();

        return view('docking-space-request.rejected', compact('rejected_requests', 'stage'));
    }

    public function store_request(
        StoreProjectDockingRequest $request,
        DockingCapacityService $docking_capacity_service,
        DockingRequestCheckService $check_service,
        DockingRequestDocumentStorage $document_storage
    ): RedirectResponse {
        $data = $request->validated();

        $project = null;

        if (! empty($data['project_id'])) {
            $project = Project::findOrFail((int) $data['project_id']);
            $ship_id = (int) $project->ship_id;

            if (! $ship_id) {
                return back()->with('error', 'Proyek tidak memiliki data kapal yang valid.')->withInput();
            }
        } else {
            // Permohonan berbasis kapal tidak mensyaratkan proyek; proyek dapat dikaitkan setelah dimulai.
            $ship_id = (int) $data['ship_id'];
        }

        $ship = Ship::findOrFail($ship_id);

        $check = $check_service->check(
            $ship,
            (string) $data['requested_start_at'],
            $data['requested_end_at'] ?? null,
            ! empty($data['requested_docking_space_id']) ? (int) $data['requested_docking_space_id'] : null
        );

        if (! $check['ok']) {
            return back()->with('error', $check['error'])->withInput();
        }

        $user_id = $request->user()?->id;

        DB::transaction(function () use ($data, $project, $ship_id, $user_id, $check, $request, $docking_capacity_service, $document_storage): void {
            $docking_request = ProjectDockingRequest::create([
                'project_id' => $project?->id,
                'ship_id' => $ship_id,
                'requested_by' => $user_id,
                'requested_docking_space_id' => $check['docking_space']->id,
                'requested_start_at' => $data['requested_start_at'],
                'requested_end_at' => $data['requested_end_at'] ?? null,
                'request_notes' => $data['request_notes'] ?? null,
                'request_status' => 'submitted',
            ]);

            foreach ($data['documents'] as $index => $document) {
                $file = $request->file("documents.{$index}.file");

                $docking_request->documents()->create([
                    'document_type' => $document['type'],
                    'document_name' => DockingRequestDocument::TYPE_LABELS[$document['type']],
                    'document_path' => $document_storage->store_pending($docking_request, $file),
                    'uploaded_by' => $user_id,
                ]);
            }

            $this->run_capacity_evaluation_for_request($docking_request, $docking_capacity_service, $user_id);
        });

        return redirect()->route('docking-space-request.index')->with('success', 'Permohonan lolos pemeriksaan sistem dan menunggu persetujuan Engineering.');
    }

    public function engineering_review(
        ReviewDockingStageRequest $request,
        string $docking_request,
        DockingRequestDocumentStorage $document_storage,
        DockingRequestCheckService $check_service,
        DockingCapacityService $docking_capacity_service
    ): RedirectResponse {
        if (! $this->can_approve($request, 'approve-docking-engineering')) {
            return back()->with('error', 'Anda tidak memiliki hak akses persetujuan Engineering.');
        }

        $docking_request = $this->find_docking_request($docking_request, ['ship']);

        if ($docking_request->request_status !== 'submitted') {
            return back()->with('error', 'Permohonan tidak sedang menunggu persetujuan Engineering.');
        }

        $data = $request->validated();
        $user_id = $request->user()?->id;

        if ($data['decision'] === 'reject') {
            DB::transaction(function () use ($docking_request, $data, $user_id, $document_storage): void {
                $docking_request->update([
                    'request_status' => 'rejected',
                    'rejection_stage' => 'engineering',
                    'rejection_reason' => $data['notes'],
                    'engineering_notes' => $data['notes'],
                    'reviewed_by' => $user_id,
                    'reviewed_at' => now(),
                ]);

                $document_storage->move_to_rejected($docking_request->load('documents'));
            });

            return $this->after_review($request, 'engineering')->with('success', 'Permohonan ditolak oleh Engineering.');
        }

        $recommended_space = DockingSpace::where('unique_id', $data['recommended_docking_space'])->where('status', 'active')->first();

        if (! $recommended_space) {
            return back()->withErrors(['recommended_docking_space' => 'Docking space rekomendasi tidak aktif atau tidak ditemukan.'])->withInput();
        }

        $evaluation = $docking_capacity_service->evaluate_ship_for_space($docking_request->ship, $recommended_space, 0);

        if (! $evaluation['is_compatible']) {
            return back()->withErrors(['recommended_docking_space' => 'Docking space tidak sesuai untuk kapal ini: ' . implode(' ', $evaluation['violations'])])->withInput();
        }

        $has_conflict = $check_service->has_schedule_conflict(
            $recommended_space->id,
            (string) $docking_request->requested_start_at,
            $docking_request->requested_end_at?->toDateTimeString(),
            $docking_request->id
        );

        if ($has_conflict) {
            return back()->withErrors(['recommended_docking_space' => 'Docking space tersebut sudah terpakai pada rentang tanggal permohonan.'])->withInput();
        }

        $docking_request->update([
            'request_status' => 'engineering_approved',
            'recommended_docking_space_id' => $recommended_space->id,
            'engineering_approved_by' => $user_id,
            'engineering_approved_at' => now(),
            'engineering_notes' => $data['notes'] ?? null,
            'reviewed_by' => $user_id,
            'reviewed_at' => now(),
        ]);

        return $this->after_review($request, 'engineering')->with('success', 'Persetujuan Engineering dicatat. Permohonan menunggu persetujuan Produksi.');
    }

    public function production_review(
        ReviewDockingStageRequest $request,
        string $docking_request,
        DockingRequestCheckService $check_service,
        DockingCapacityService $docking_capacity_service,
        DockingRequestDocumentStorage $document_storage
    ): RedirectResponse {
        if (! $this->can_approve($request, 'approve-docking-production')) {
            return back()->with('error', 'Anda tidak memiliki hak akses persetujuan Produksi.');
        }

        $docking_request = $this->find_docking_request($docking_request, ['ship', 'requested_docking_space', 'recommended_docking_space']);

        if ($docking_request->request_status !== 'engineering_approved') {
            return back()->with('error', 'Permohonan belum disetujui Engineering atau sudah diproses.');
        }

        $data = $request->validated();
        $user_id = $request->user()?->id;

        if ($data['decision'] === 'reject') {
            DB::transaction(function () use ($docking_request, $data, $user_id, $document_storage): void {
                $docking_request->update([
                    'request_status' => 'rejected',
                    'rejection_stage' => 'production',
                    'rejection_reason' => $data['notes'],
                    'production_notes' => $data['notes'],
                    'reviewed_by' => $user_id,
                    'reviewed_at' => now(),
                ]);

                $document_storage->move_to_rejected($docking_request->load('documents'));
            });

            return $this->after_review($request, 'production')->with('success', 'Permohonan ditolak oleh Produksi.');
        }

        $docking_space = $docking_request->recommended_docking_space ?? $docking_request->requested_docking_space;

        if (in_array($data['decision'], ['postpone', 'cancel'], true)) {
            return $this->recommend_production_action($request, $docking_request, $docking_space, $data, $check_service);
        }

        if (! $docking_space) {
            return back()->with('error', 'Docking space permohonan belum ditentukan.');
        }

        // Recheck right before reserving the slot in case the schedule changed since submission.
        $has_conflict = $check_service->has_schedule_conflict(
            $docking_space->id,
            (string) $docking_request->requested_start_at,
            $docking_request->requested_end_at?->toDateTimeString(),
            $docking_request->id
        );

        if ($has_conflict) {
            return back()->with('error', 'Persetujuan gagal karena jadwal docking kini bentrok dengan jadwal lain.');
        }

        $evaluation = $docking_capacity_service->evaluate_ship_for_space($docking_request->ship, $docking_space, 0);

        if (! $evaluation['is_compatible']) {
            return back()->with('error', 'Docking space tidak lagi sesuai untuk kapal ini: ' . implode(' ', $evaluation['violations']));
        }

        DB::transaction(function () use ($docking_request, $data, $user_id, $docking_space): void {
            $docking_request->update([
                'request_status' => 'approved',
                'production_approved_by' => $user_id,
                'production_approved_at' => now(),
                'production_notes' => $data['notes'] ?? null,
                'approved_by' => $user_id,
                'approved_at' => now(),
                'reviewed_by' => $user_id,
                'reviewed_at' => now(),
            ]);

            DockingOccupancy::firstOrCreate(
                [
                    'project_docking_request_id' => $docking_request->id,
                    'occupancy_status' => 'scheduled',
                ],
                [
                    'project_id' => $docking_request->project_id,
                    'ship_id' => $docking_request->ship_id,
                    'docking_space_id' => $docking_space->id,
                    'docked_at' => $docking_request->requested_start_at,
                    'estimated_undock_at' => $docking_request->requested_end_at,
                    'remarks' => 'Jadwal otomatis dari persetujuan permohonan docking.',
                    'created_by' => $user_id,
                ]
            );
        });

        return $this->after_review($request, 'production')->with('success', 'Permohonan disetujui. Kapal menunggu kedatangan.');
    }

    public function respond_to_recommendation(
        MarketingRecommendationResponseRequest $request,
        string $docking_request,
        DockingRequestCheckService $check_service,
        DockingRequestDocumentStorage $document_storage
    ): RedirectResponse {
        $docking_request = $this->find_docking_request($docking_request, ['documents']);
        $user = $request->user();

        $is_owner = (int) $docking_request->requested_by === (int) $user?->id;

        if (! $is_owner && ! $user?->hasRole('admin')) {
            return back()->with('error', 'Hanya pemohon (Marketing) yang dapat menanggapi rekomendasi Produksi.');
        }

        if ($docking_request->request_status !== 'awaiting_marketing') {
            return back()->with('error', 'Permohonan ini tidak sedang menunggu konfirmasi Marketing.');
        }

        $data = $request->validated();
        $is_postpone = $docking_request->production_recommendation === 'postpone';

        $response_fields = [
            'marketing_responded_by' => $user->id,
            'marketing_responded_at' => now(),
            'marketing_response' => $data['response'],
            'marketing_notes' => $data['notes'] ?? null,
        ];

        // Customer objects: the request goes back to Production with the customer's reason attached.
        if ($data['response'] === 'decline') {
            $docking_request->update($response_fields + ['request_status' => 'engineering_approved']);

            return back()->with('success', 'Keberatan customer dicatat. Permohonan dikembalikan ke Produksi.');
        }

        if (! $is_postpone) {
            DB::transaction(function () use ($docking_request, $response_fields, $document_storage): void {
                $docking_request->update($response_fields + [
                    'request_status' => 'rejected',
                    'rejection_stage' => 'production',
                    'rejection_reason' => $docking_request->production_notes,
                ]);

                $document_storage->move_to_rejected($docking_request);
            });

            return back()->with('success', 'Pembatalan dikonfirmasi. Permohonan dipindahkan ke daftar ditolak.');
        }

        $space_id = $docking_request->effective_docking_space_id();

        if ($space_id !== null && $check_service->has_schedule_conflict(
            $space_id,
            $docking_request->recommended_start_at->toDateTimeString(),
            $docking_request->recommended_end_at?->toDateTimeString(),
            $docking_request->id
        )) {
            return back()->with('error', 'Jadwal usulan Produksi kini bentrok dengan jadwal lain. Hubungi Produksi untuk jadwal baru.');
        }

        $docking_request->update($response_fields + [
            'request_status' => 'engineering_approved',
            'requested_start_at' => $docking_request->recommended_start_at,
            'requested_end_at' => $docking_request->recommended_end_at,
        ]);

        return back()->with('success', 'Penundaan dikonfirmasi. Jadwal diperbarui dan permohonan menunggu persetujuan Produksi.');
    }

    private function recommend_production_action(
        ReviewDockingStageRequest $request,
        ProjectDockingRequest $docking_request,
        ?DockingSpace $docking_space,
        array $data,
        DockingRequestCheckService $check_service
    ): RedirectResponse {
        $is_postpone = $data['decision'] === 'postpone';

        if ($is_postpone && $docking_space && $check_service->has_schedule_conflict(
            $docking_space->id,
            (string) $data['recommended_start_at'],
            $data['recommended_end_at'],
            $docking_request->id
        )) {
            return back()->withErrors(['recommended_start_at' => 'Jadwal usulan bentrok dengan jadwal docking lain pada docking space ini.'])->withInput();
        }

        $docking_request->update([
            'request_status' => 'awaiting_marketing',
            'production_recommendation' => $data['decision'],
            'recommended_start_at' => $is_postpone ? $data['recommended_start_at'] : null,
            'recommended_end_at' => $is_postpone ? $data['recommended_end_at'] : null,
            'production_notes' => $data['notes'],
            'reviewed_by' => $request->user()?->id,
            'reviewed_at' => now(),
            'marketing_response' => null,
            'marketing_notes' => null,
            'marketing_responded_at' => null,
            'marketing_responded_by' => null,
        ]);

        return $this->after_review($request, 'production')->with('success', 'Rekomendasi Produksi dicatat. Marketing akan diminta konfirmasi kepada customer.');
    }

    public function postpone_form(Request $request, string $docking_request, DockingRequestCheckService $check_service): \Illuminate\Contracts\View\View|RedirectResponse
    {
        $docking_request = $this->find_docking_request($docking_request, ['ship', 'requested_docking_space', 'recommended_docking_space', 'postponements.postponer']);

        if ($response = $this->guard_postponement($request, $docking_request)) {
            return $response;
        }

        $space = $docking_request->recommended_docking_space ?? $docking_request->requested_docking_space;
        $duration_minutes = $this->request_duration_minutes($docking_request);

        $earliest = $request->query('from');
        $earliest = is_string($earliest) && strtotime($earliest) !== false
            ? $earliest
            : now()->max($docking_request->requested_start_at)->toDateTimeString();

        $suggestions = $space
            ? $check_service->suggest_slots($space->id, $earliest, $duration_minutes, $docking_request->id)
            : [];

        return view('docking-space-request.postpone', compact('docking_request', 'space', 'suggestions', 'duration_minutes', 'earliest'));
    }

    public function postpone_docking(PostponeDockingRequest $request, string $docking_request, DockingRequestCheckService $check_service): RedirectResponse
    {
        $docking_request = $this->find_docking_request($docking_request, ['ship', 'requested_docking_space', 'recommended_docking_space']);

        if ($response = $this->guard_postponement($request, $docking_request)) {
            return $response;
        }

        $data = $request->validated();
        $space = $docking_request->recommended_docking_space ?? $docking_request->requested_docking_space;
        $new_start = \Carbon\CarbonImmutable::parse($data['new_start_at']);
        $new_end = \Carbon\CarbonImmutable::parse($data['new_end_at']);

        if ($new_start <= $docking_request->requested_start_at) {
            return back()->withErrors(['new_start_at' => 'Tanggal docking baru harus setelah tanggal docking saat ini.'])->withInput();
        }

        if ($space && $check_service->has_schedule_conflict($space->id, $new_start->toDateTimeString(), $new_end->toDateTimeString(), $docking_request->id)) {
            return back()
                ->withErrors(['new_start_at' => 'Jadwal baru bentrok dengan jadwal docking lain pada ' . $space->name . '. Gunakan salah satu saran jadwal di bawah.'])
                ->withInput();
        }

        DB::transaction(function () use ($docking_request, $data, $new_start, $new_end, $space, $request): void {
            $docking_request->postponements()->create([
                'docking_space_id' => $space?->id,
                'previous_start_at' => $docking_request->requested_start_at,
                'previous_end_at' => $docking_request->requested_end_at,
                'new_start_at' => $new_start,
                'new_end_at' => $new_end,
                'reason' => $data['reason'],
                'postponed_by' => $request->user()?->id,
            ]);

            $docking_request->update(['requested_start_at' => $new_start, 'requested_end_at' => $new_end]);

            $docking_request->docking_occupancies()
                ->where('occupancy_status', 'scheduled')
                ->update(['docked_at' => $new_start, 'estimated_undock_at' => $new_end]);
        });

        return redirect()->route('docking-space-request.index')->with('success', 'Jadwal docking berhasil ditunda ke ' . $new_start->tanggal_waktu_indonesia() . '.');
    }

    private function guard_postponement(Request $request, ProjectDockingRequest $docking_request): ?RedirectResponse
    {
        $user = $request->user();
        $is_owner = (int) $docking_request->requested_by === (int) $user?->id;

        if (! $is_owner && ! $user?->hasRole('admin')) {
            return redirect()->route('docking-space-request.index')->with('error', 'Hanya pemohon (Marketing) yang dapat menunda jadwal docking.');
        }

        $has_scheduled = $docking_request->docking_occupancies()->where('occupancy_status', 'scheduled')->exists();

        if ($docking_request->request_status !== 'approved' || ! $has_scheduled) {
            return redirect()->route('docking-space-request.index')->with('error', 'Hanya permohonan disetujui yang kapalnya belum masuk dock yang dapat ditunda.');
        }

        return null;
    }

    private function request_duration_minutes(ProjectDockingRequest $docking_request): int
    {
        if (! $docking_request->requested_end_at) {
            return 30 * 24 * 60;
        }

        return max(60, (int) $docking_request->requested_start_at->diffInMinutes($docking_request->requested_end_at));
    }

    public function cancel_request(Request $request, string $docking_request): RedirectResponse
    {
        $docking_request = $this->find_docking_request($docking_request);
        $user = $request->user();

        $is_owner = $user !== null && (int) $docking_request->requested_by === (int) $user->id;

        if (! $is_owner && ! $user?->hasRole('admin')) {
            return back()->with('error', 'Hanya pemohon yang dapat membatalkan permohonan ini.');
        }

        if (! in_array($docking_request->request_status, ProjectDockingRequest::PENDING_STATUSES, true)) {
            return back()->with('error', 'Permohonan yang sudah diputuskan tidak dapat dibatalkan.');
        }

        $docking_request->update(['request_status' => 'cancelled']);

        return back()->with('success', 'Permohonan docking dibatalkan.');
    }

    public function download_request_document(Request $request, string $docking_request, string $document): StreamedResponse|RedirectResponse
    {
        $docking_request = $this->find_docking_request($docking_request);
        $user = $request->user();

        // Rejected and cancelled requests keep their files for history, so access stays limited to involved roles.
        $can_download = $user !== null && (
            $user->hasRole('admin')
            || (int) $docking_request->requested_by === (int) $user->id
            || $user->can('approve-docking-engineering')
            || $user->can('approve-docking-production')
            || $user->can('docking.manage')
            || $user->can('project.manage')
            || $user->can('project.update')
        );

        abort_unless($can_download, 403);

        $document_model = $docking_request->documents()->where('unique_id', $document)->first();

        if (! $document_model || ! Storage::disk(DockingRequestDocument::DISK)->exists($document_model->document_path)) {
            return back()->with('error', 'Dokumen tidak ditemukan.');
        }

        $extension = pathinfo($document_model->document_path, PATHINFO_EXTENSION);

        return Storage::disk(DockingRequestDocument::DISK)->download(
            $document_model->document_path,
            Str::slug($document_model->document_name) . ($extension !== '' ? '.' . $extension : '')
        );
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
            : (int) ($docking_request->effective_docking_space_id() ?? 0);

        if ($docking_space_id <= 0) {
            return back()->with('error', 'Docking space untuk pelaksanaan docking belum ditentukan.');
        }

        $has_conflict = app(DockingRequestCheckService::class)->has_schedule_conflict(
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

    private function after_review(ReviewDockingStageRequest $request, string $stage): RedirectResponse
    {
        return $request->boolean('return_to_queue')
            ? redirect()->route('docking-approval.index', $stage)
            : back();
    }

    private function can_approve(Request $request, string $permission): bool
    {
        $user = $request->user();

        return $user !== null && ($user->hasRole('admin') || $user->can($permission));
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
