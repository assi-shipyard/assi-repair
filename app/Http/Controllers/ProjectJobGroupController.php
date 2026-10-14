<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Project\SaveJobGroupRequest;
use App\Models\ProjectJobGroup;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ProjectJobGroupController extends Controller
{
    public function index(): View
    {
        return view('project.job-group.index', [
            'job_groups' => ProjectJobGroup::query()->withCount('jobs')->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(SaveJobGroupRequest $request): RedirectResponse
    {
        ProjectJobGroup::query()->create($request->validated());

        return redirect()->route('job-group.index')->with('success', 'Kelompok pekerjaan berhasil ditambahkan.');
    }

    public function update(SaveJobGroupRequest $request, ProjectJobGroup $job_group): RedirectResponse
    {
        $job_group->update($request->validated());

        return redirect()->route('job-group.index')->with('success', 'Kelompok pekerjaan berhasil diperbarui.');
    }

    public function destroy(ProjectJobGroup $job_group): RedirectResponse
    {
        if ($job_group->jobs()->exists()) {
            return redirect()->route('job-group.index')->with('error', 'Kelompok sudah dipakai pada dokumen pekerjaan. Nonaktifkan kelompok ini sebagai gantinya.');
        }

        $job_group->delete();

        return redirect()->route('job-group.index')->with('success', 'Kelompok pekerjaan berhasil dihapus.');
    }
}
