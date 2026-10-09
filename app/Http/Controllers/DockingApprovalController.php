<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ProjectDockingRequest;
use App\Services\DockingCapacityService;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class DockingApprovalController extends Controller
{
    private const STAGES = [
        'engineering' => [
            'permission' => 'approve-docking-engineering',
            'title' => 'Persetujuan Engineering (Biro Litbang)',
            'pending_status' => 'submitted',
            'approved_column' => 'engineering_approved_at',
            'approver_relation' => 'engineering_approver',
        ],
        'production' => [
            'permission' => 'approve-docking-production',
            'title' => 'Persetujuan Produksi (Divisi Reparasi dan Rekayasa Umum)',
            'pending_status' => 'engineering_approved',
            'approved_column' => 'production_approved_at',
            'approver_relation' => 'production_approver',
        ],
    ];

    public function index(Request $request, string $stage): View
    {
        $config = $this->authorize_stage($request, $stage);

        $base_relations = ['ship.company', 'requested_docking_space', 'requester', $config['approver_relation']];

        $pending_requests = ProjectDockingRequest::with($base_relations)
            ->withCount('documents')
            ->where('request_status', $config['pending_status'])
            ->orderBy('requested_start_at')
            ->get();

        // A rejection at this stage is recorded in rejection_stage; approvals in the stage timestamp.
        $decided_requests = ProjectDockingRequest::with($base_relations)
            ->where(function ($query) use ($config, $stage): void {
                $query->whereNotNull($config['approved_column'])
                    ->orWhere('rejection_stage', $stage);
            })
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get();

        $waiting_upstream_count = $stage === 'production'
            ? ProjectDockingRequest::where('request_status', 'submitted')->count()
            : 0;

        return view('docking-approval.index', [
            'stage' => $stage,
            'stage_title' => $config['title'],
            'pending_requests' => $pending_requests,
            'decided_requests' => $decided_requests,
            'waiting_upstream_count' => $waiting_upstream_count,
        ]);
    }

    public function show(Request $request, DockingCapacityService $capacity_service, string $stage, string $docking_request): View
    {
        $config = $this->authorize_stage($request, $stage);

        $docking_request = ProjectDockingRequest::with([
            'ship.company', 'ship.type', 'requested_docking_space', 'requester', 'documents',
            'engineering_approver', 'production_approver',
        ])->where('unique_id', $docking_request)->firstOrFail();

        $evaluation = $docking_request->requested_docking_space
            ? $capacity_service->evaluate_ship_for_space($docking_request->ship, $docking_request->requested_docking_space, 0)
            : null;

        return view('docking-approval.show', [
            'stage' => $stage,
            'stage_title' => $config['title'],
            'docking_request' => $docking_request,
            'evaluation' => $evaluation,
            'can_decide' => $docking_request->request_status === $config['pending_status'],
        ]);
    }

    private function authorize_stage(Request $request, string $stage): array
    {
        $config = self::STAGES[$stage] ?? abort(404);
        $user = $request->user();

        abort_unless($user !== null && ($user->hasRole('admin') || $user->can($config['permission'])), 403);

        return $config;
    }
}
