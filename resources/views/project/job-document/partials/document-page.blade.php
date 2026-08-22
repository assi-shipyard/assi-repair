<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <div class="text-secondary">{{ $documentTypeLabels[$document->document_type] ?? $document->document_type }}</div>
                <h2 class="mb-1">Revisi {{ $document->revision_no }}</h2>
                <div class="text-secondary">{{ $project->project_code }} - {{ $project->ship?->name ?? '-' }}</div>
            </div>
            <div class="text-end">
                <div class="badge bg-primary-lt mb-2">{{ $document->status }}</div>
                <div class="small text-secondary">{{ $document->document_number ?? '-' }}</div>
            </div>
        </div>
    </div>
</div>

@if ($document->document_type === 'satisfaction_notes' && in_array($document->status, ['draft', 'approved', 'locked'], true) === false)
    <div class="alert alert-info">Dokumen ini dapat difinalisasi setelah semua pekerjaan selesai.</div>
@endif

<div class="card mb-4">
    <div class="card-header"><h3 class="card-title mb-0">Tambah Pekerjaan</h3></div>
    <div class="card-body">
        <form action="{{ route('project.job-document.workflow.job.store', [$project->unique_id, $document->unique_id ?? $document->id]) }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-4"><input class="form-control" name="job_name" placeholder="Nama pekerjaan" required></div>
                <div class="col-md-3"><input class="form-control" name="job_volume_estimated" placeholder="Volume"></div>
                <div class="col-md-3"><input class="form-control" name="responsible_name" placeholder="Penanggung jawab"></div>
                <div class="col-md-2"><button class="btn btn-primary w-100" type="submit">Tambah</button></div>
                <div class="col-md-4"><input class="form-control" name="progress_percent" placeholder="Progress"></div>
                <div class="col-md-4"><input class="form-control" name="est_price" placeholder="Harga estimasi"></div>
                <div class="col-md-4"><select class="form-select" name="status"><option value="pending">Pending</option><option value="ongoing">Ongoing</option><option value="completed">Completed</option><option value="cancelled">Cancelled</option></select></div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead><tr><th>Pekerjaan</th><th>Penanggung Jawab</th><th>Progress</th><th>Status</th><th class="w-1"></th></tr></thead>
            <tbody>
                @forelse ($document->jobs as $job)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $job->job_name }}</div>
                            <div class="small text-secondary">{{ $job->job_volume_estimated ?? '-' }}</div>
                            <div class="small text-secondary">Material: {{ $job->materials->count() }}</div>
                        </td>
                        <td>{{ $job->responsible_name ?? '-' }}</td>
                        <td>{{ $job->progress_percent ?? 0 }}%</td>
                        <td>{{ $job->status ?? '-' }}</td>
                        <td>
                            <a href="{{ route('project.job-document.workflow.job.photos', [$project->unique_id, $document->unique_id ?? $document->id, $job->unique_id ?? $job->id]) }}" class="btn btn-sm btn-outline-primary">Foto</a>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5" class="bg-light">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-4"><input class="form-control form-control-sm" form="material-form-{{ $job->id }}" name="material_name" placeholder="Material baru"></div>
                                <div class="col-md-2"><input class="form-control form-control-sm" form="material-form-{{ $job->id }}" name="volume" placeholder="Volume"></div>
                                <div class="col-md-2"><input class="form-control form-control-sm" form="material-form-{{ $job->id }}" name="price" placeholder="Harga"></div>
                                <div class="col-md-2"><input class="form-control form-control-sm" form="material-form-{{ $job->id }}" name="currency" placeholder="IDR"></div>
                                <div class="col-md-2">
                                    <form id="material-form-{{ $job->id }}" action="{{ route('project.job-document.workflow.material.store', [$project->unique_id, $document->unique_id ?? $document->id, $job->unique_id ?? $job->id]) }}" method="POST">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-primary w-100" type="submit">Tambah Material</button>
                                    </form>
                                </div>
                            </div>
                            <div class="mt-3 table-responsive">
                                <table class="table table-sm table-vcenter mb-0">
                                    <thead><tr><th>Material</th><th>Volume</th><th>Harga</th></tr></thead>
                                    <tbody>
                                        @forelse ($job->materials as $material)
                                            <tr><td>{{ $material->material_name }}</td><td>{{ $material->volume ?? '-' }}</td><td>{{ $material->price ?? '-' }}</td></tr>
                                        @empty
                                            <tr><td colspan="3" class="text-secondary">Belum ada material.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-secondary py-4">Belum ada pekerjaan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
