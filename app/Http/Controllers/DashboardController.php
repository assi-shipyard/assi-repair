<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Project;
use App\Models\Ship;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    public function index(): View
    {
        $total_companies = Company::count();
        $total_ships = Ship::count();

        $active_projects = Project::query()
            ->whereIn('status', ['Not Started', 'In Progress'])
            ->count();

        $ships_per_company = $total_companies > 0
            ? round($total_ships / $total_companies, 1)
            : 0;

        $pending_projects = Project::query()
            ->with(['ship.company'])
            ->where('status', 'Not Started')
            ->orderByRaw('COALESCE(start_date_estimation, created_at) asc')
            ->orderBy('project_code')
            ->get();

        $ongoing_projects = Project::query()
            ->with(['ship.company'])
            ->where('status', 'In Progress')
            ->orderByDesc('progress')
            ->orderByRaw('COALESCE(start_date_actual, start_date_estimation, created_at) asc')
            ->get();

        $completed_projects = Project::query()
            ->with(['ship.company'])
            ->where('status', 'Completed')
            ->orderByRaw('COALESCE(end_date_actual, end_date_estimation, updated_at, created_at) desc')
            ->get();

        $project_summary = Project::query()
            ->selectRaw('COUNT(*) as total_projects')
            ->selectRaw("SUM(CASE WHEN status = 'Not Started' THEN 1 ELSE 0 END) as pending_projects")
            ->selectRaw("SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as ongoing_projects")
            ->selectRaw("SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_projects")
            ->selectRaw("SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled_projects")
            ->first();

        $company_fleet_summary = Company::query()
            ->with([
                'ships' => fn ($query) => $query
                    ->select(['id', 'company_id', 'name'])
                    ->orderBy('name'),
            ])
            ->withCount('ships')
            ->orderByDesc('ships_count')
            ->orderBy('name')
            ->get();

        $completed_project_series = Project::query()
            ->selectRaw("DATE(COALESCE(end_date_actual, end_date_estimation, updated_at, created_at)) as completion_date")
            ->selectRaw('COUNT(*) as total_projects')
            ->where('status', 'Completed')
            ->groupBy(DB::raw("DATE(COALESCE(end_date_actual, end_date_estimation, updated_at, created_at))"))
            ->orderBy('completion_date')
            ->get();

        $completion_chart_series = $completed_project_series
            ->map(fn ($item): array => [
                'x' => $item->completion_date,
                'y' => (int) $item->total_projects,
            ])
            ->values()
            ->all();

        $completion_chart_total = $completed_project_series->sum('total_projects');

        $project_status_sections = collect([
            [
                'title' => 'Proyek Pending',
                'subtitle' => 'Menunggu pelaksanaan atau jadwal mulai.',
                'empty_state' => 'Belum ada proyek pending.',
                'badge_class' => 'bg-azure-lt text-azure',
                'progress_class' => 'bg-azure',
                'projects' => $pending_projects,
            ],
            [
                'title' => 'Proyek Berjalan',
                'subtitle' => 'Sedang dikerjakan dan perlu dipantau progresnya.',
                'empty_state' => 'Belum ada proyek berjalan.',
                'badge_class' => 'bg-yellow-lt text-yellow',
                'progress_class' => 'bg-yellow',
                'projects' => $ongoing_projects,
            ],
            [
                'title' => 'Proyek Selesai',
                'subtitle' => 'Sudah ditutup dan siap untuk peninjauan histori.',
                'empty_state' => 'Belum ada proyek selesai.',
                'badge_class' => 'bg-green-lt text-green',
                'progress_class' => 'bg-green',
                'projects' => $completed_projects,
            ],
        ]);

        $needs_password_change = false;

        if (Auth::check() && Auth::user()->employee_id) {
            $needs_password_change = Hash::check(Auth::user()->employee_id, Auth::user()->password);
        }

        return view('dashboard', [
            'total_companies' => $total_companies,
            'total_ships' => $total_ships,
            'active_projects' => $active_projects,
            'ships_per_company' => $ships_per_company,
            'project_summary' => $project_summary,
            'company_fleet_summary' => $company_fleet_summary,
            'completion_chart_series' => $completion_chart_series,
            'completion_chart_total' => $completion_chart_total,
            'project_status_sections' => $project_status_sections,
            'needs_password_change' => $needs_password_change,
        ]);
    }
}
