<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Project\save_job_group_request;
use App\Models\project_job_group;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class project_job_group_controller extends Controller
{
    public function index(): View
    {
        return view('project.job-group.index', [
            'job_groups' => project_job_group::query()->withCount('jobs')->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(save_job_group_request $request): RedirectResponse
    {
        project_job_group::query()->create($request->validated());

        return redirect()->route('job-group.index')->with('success', 'Kelompok pekerjaan berhasil ditambahkan.');
    }

    public function update(save_job_group_request $request, project_job_group $job_group): RedirectResponse
    {
        $job_group->update($request->validated());

        return redirect()->route('job-group.index')->with('success', 'Kelompok pekerjaan berhasil diperbarui.');
    }

    public function destroy(project_job_group $job_group): RedirectResponse
    {
        if ($job_group->jobs()->exists()) {
            return redirect()->route('job-group.index')->with('error', 'Kelompok sudah dipakai pada dokumen pekerjaan. Nonaktifkan kelompok ini sebagai gantinya.');
        }

        $job_group->delete();

        return redirect()->route('job-group.index')->with('success', 'Kelompok pekerjaan berhasil dihapus.');
    }
}
