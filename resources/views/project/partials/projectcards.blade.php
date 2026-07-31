<div class="row row-cards">
    @forelse ($projects as $project)
        <div class="col-md-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="text-secondary small">{{ $project->project_code }}</div>
                            <h3 class="mb-1">{{ $project->ship?->name ?? '-' }}</h3>
                        </div>
                        <span class="badge bg-primary-lt">{{ $project->status ?? 'Not Started' }}</span>
                    </div>
                    <div class="text-secondary mb-3">{{ $project->project_type }}</div>
                    <div class="small text-secondary">Progres</div>
                    <div class="progress mb-3"><div class="progress-bar" style="width: {{ (float) ($project->progress ?? 0) }}%"></div></div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('project.show', $project->unique_id) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                        <a href="{{ route('project.edit', $project->unique_id) }}" class="btn btn-sm btn-outline-secondary">Ubah</a>
                        <a href="{{ route('project.job-document.workflow.index', $project->unique_id) }}" class="btn btn-sm btn-outline-success">Dokumen</a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center text-secondary py-5">Belum ada data proyek.</div>
            </div>
        </div>
    @endforelse
</div>
