<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Company;
use App\Models\Project;
use App\Models\Ship;

class DashboardController extends Controller
{
    public function index(): View
    {
        $total_companies = Company::count();
        $total_ships = Ship::count();
        $active_projects = Project::query()
            ->whereIn('status', ['Not Started', 'In Progress'])
            ->count();

        $recent_projects = Project::query()
            ->with(['ship'])
            ->latest()
            ->limit(8)
            ->get();

        $project_summary = Project::query()
            ->selectRaw('COUNT(*) as total_projects')
            ->selectRaw("SUM(CASE WHEN status = 'Not Started' THEN 1 ELSE 0 END) as pending_projects")
            ->selectRaw("SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as ongoing_projects")
            ->selectRaw("SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_projects")
            ->selectRaw("SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled_projects")
            ->first();

        $needs_password_change = false;
        if (Auth::check() && Auth::user()->employee_id) {
            $needs_password_change = Hash::check(Auth::user()->employee_id, Auth::user()->password);
        }

        return view('dashboard', [
            'total_companies' => $total_companies,
            'total_ships' => $total_ships,
            'active_projects' => $active_projects,
            'recent_projects' => $recent_projects,
            'project_summary' => $project_summary,
            'needs_password_change' => $needs_password_change,
        ]);
    }
}
