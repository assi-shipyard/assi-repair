<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\DockingVisitException;
use App\Http\Requests\Docking\CompleteFloatingRepairRequest;
use App\Http\Requests\Docking\ConfirmAdministrationRequest;
use App\Http\Requests\Docking\DepartShipyardRequest;
use App\Http\Requests\Docking\RecordShipArrivalRequest;
use App\Http\Requests\Docking\StartDockingOccupancyRequest;
use App\Http\Requests\Docking\UndockShipRequest;
use App\Models\DockingSpace;
use App\Models\ProjectDockingRequest;
use App\Models\User;
use App\Services\DockingCapacityService;
use App\Services\DockingVisitService;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DockingVisitController extends Controller
{
    public function __construct(private readonly DockingVisitService $visit_service) {}

    public function show(Request $request, string $docking_request, DockingCapacityService $capacity_service): View
    {
        $docking_request = ProjectDockingRequest::query()
            ->with([
                'ship.company',
                'ship.type',
                'project',
                'requested_docking_space',
                'recommended_docking_space',
                'requester',
                'engineering_approver',
                'production_approver',
                'canceller',
                'arrival_recorder',
                'administration_confirmer',
                'departure_recorder',
                'documents',
                'postponements.postponer',
                'docking_occupancies' => fn ($query) => $query->with(['docking_space', 'floating_repair_histories'])->orderBy('docked_at'),
            ])
            ->where('unique_id', $docking_request)
            ->firstOrFail();

        $user = $request->user();
        $can_operate = $this->can_operate($user);
        $is_owner = $docking_request->is_managed_by($user);

        $dock_options = collect();

        if ($docking_request->visit_stage === 'arrived' && $can_operate) {
            $dock_options = DockingSpace::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get()
                ->map(fn (DockingSpace $space): array => [
                    'space' => $space,
                    'is_compatible' => (bool) ($capacity_service->evaluate_ship_for_space($docking_request->ship, $space, 0)['is_compatible'] ?? false),
                ]);
        }

        return view('docking-space-request.show', [
            'docking_request' => $docking_request,
            'can_operate' => $can_operate,
            'is_owner' => $is_owner,
            'can_approve_engineering' => $user->hasRole('admin') || $user->can('approve-docking-engineering'),
            'can_approve_production' => $user->hasRole('admin') || $user->can('approve-docking-production'),
            'departure_blockers' => $this->visit_service->departure_blockers($docking_request),
            'current_occupancy' => $docking_request->docking_occupancies->first(fn ($item): bool => $item->occupancy_status === 'occupied' && $item->undocked_at === null),
            'dock_options' => $dock_options,
            'open_action' => in_array($request->query('action'), ['arrival', 'dock', 'undock', 'floating', 'administration', 'depart', 'cancel', 'marketing'], true)
                ? $request->query('action')
                : null,
        ]);
    }

    public function record_arrival(RecordShipArrivalRequest $request, string $docking_request): RedirectResponse
    {
        return $this->operate($request, $docking_request, function (ProjectDockingRequest $item, int $user_id) use ($request): string {
            $this->visit_service->record_arrival($item, $request->validated('arrived_at'), $request->validated('arrival_notes'), $user_id);

            return 'Kedatangan kapal '.($item->ship?->name ?? '-').' berhasil dicatat. Kapal siap dijadwalkan masuk dock.';
        });
    }

    public function dock_in(StartDockingOccupancyRequest $request, string $docking_request): RedirectResponse
    {
        return $this->operate($request, $docking_request, function (ProjectDockingRequest $item, int $user_id) use ($request): string {
            $this->visit_service->dock_in($item, $request->validated(), $user_id);

            return 'Kapal '.($item->ship?->name ?? '-').' tercatat masuk dock.';
        });
    }

    public function undock(UndockShipRequest $request, string $docking_request): RedirectResponse
    {
        return $this->operate($request, $docking_request, function (ProjectDockingRequest $item, int $user_id) use ($request): string {
            $continue_floating = $request->validated('next_step') === 'floating';

            $this->visit_service->undock(
                $item,
                $request->validated('undocked_at'),
                $continue_floating,
                $request->validated('floating_started_at'),
                $request->validated('notes'),
                $user_id
            );

            return $continue_floating
                ? 'Kapal berhasil undocking dan melanjutkan pekerjaan atas air.'
                : 'Kapal berhasil undocking dan siap keluar galangan setelah proyek dan administrasi selesai.';
        });
    }

    public function complete_floating(CompleteFloatingRepairRequest $request, string $docking_request): RedirectResponse
    {
        return $this->operate($request, $docking_request, function (ProjectDockingRequest $item) use ($request): string {
            $this->visit_service->complete_floating($item, $request->validated('floating_completed_at'), $request->validated('notes'));

            return 'Pekerjaan atas air selesai. Kapal siap keluar galangan setelah proyek dan administrasi selesai.';
        });
    }

    public function confirm_administration(ConfirmAdministrationRequest $request, string $docking_request): RedirectResponse
    {
        $item = $this->find($docking_request);
        $user = $request->user();

        if (! $this->can_operate($user) && ! $item->is_managed_by($user)) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk mengonfirmasi administrasi kapal ini.');
        }

        try {
            $this->visit_service->confirm_administration($item, $request->validated('administration_notes'), (int) $user->id);
        } catch (DockingVisitException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('docking-space-request.show', $item->unique_id)->with('success', 'Administrasi kapal dikonfirmasi selesai.');
    }

    public function depart(DepartShipyardRequest $request, string $docking_request): RedirectResponse
    {
        return $this->operate($request, $docking_request, function (ProjectDockingRequest $item, int $user_id) use ($request): string {
            $this->visit_service->depart($item, $request->validated('departed_at'), $request->validated('departure_notes'), $user_id);

            return 'Kapal '.($item->ship?->name ?? '-').' tercatat keluar galangan. Kunjungan docking selesai.';
        });
    }

    /**
     * @param  Closure(ProjectDockingRequest, int): string  $action
     */
    private function operate(Request $request, string $docking_request, Closure $action): RedirectResponse
    {
        $user = $request->user();

        if (! $this->can_operate($user)) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk melakukan aksi docking ini.');
        }

        $item = $this->find($docking_request);

        try {
            $message = $action($item, (int) $user->id);
        } catch (DockingVisitException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('docking-space-request.show', $item->unique_id)->with('success', $message);
    }

    private function find(string $docking_request): ProjectDockingRequest
    {
        return ProjectDockingRequest::query()
            ->with(['ship', 'project'])
            ->where('unique_id', $docking_request)
            ->firstOrFail();
    }

    private function can_operate(User $user): bool
    {
        return $user->hasRole('admin')
            || $user->can('docking.manage')
            || $user->can('project.manage')
            || $user->can('project.update');
    }
}
