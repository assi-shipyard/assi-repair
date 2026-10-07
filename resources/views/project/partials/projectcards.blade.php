@php
    $status_labels = ['Not Started' => 'Belum Mulai', 'In Progress' => 'Berjalan', 'Completed' => 'Selesai'];
    $status_colors = ['Not Started' => 'secondary', 'In Progress' => 'primary', 'Completed' => 'success'];
@endphp

<div class="row row-cards">
    @forelse ($projects as $project)
        @php
            $ship = $project->ship;
            $status = $project->status ?? 'Not Started';
            $progress = max(0, min(100, (float) ($project->progress ?? 0)));
            $hue = crc32((string) ($ship?->name ?? $project->project_code)) % 360;
            $overdue = $project->end_date_estimation && $status !== 'Completed' && $project->end_date_estimation->isPast();
        @endphp
        <div class="col-md-6 col-xl-4">
            <div class="card h-100">
                <div class="project-cover" style="background: linear-gradient(135deg, hsl({{ $hue }}, 55%, 32%), hsl({{ ($hue + 40) % 360 }}, 60%, 45%));">
                    <i class="ti ti-ship cover-icon"></i>
                    <div class="cover-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="badge bg-white text-dark">{{ $project->project_code }}</span>
                            <span class="badge bg-{{ $status_colors[$status] ?? 'secondary' }}">{{ $status_labels[$status] ?? $status }}</span>
                        </div>
                        <div>
                            <div class="cover-name text-truncate">{{ $ship?->name ?? '-' }}</div>
                            <div class="cover-meta text-truncate">
                                {{ $ship?->type?->name ?? 'Tipe kapal -' }}
                                @if ($ship?->imo_number) &middot; IMO {{ $ship->imo_number }} @endif
                                @if ($ship?->flag) &middot; {{ $ship->flag }} @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-secondary">{{ $project->project_type }}</span>
                        <span class="fw-bold">{{ number_format($progress, 0) }}%</span>
                    </div>
                    <div class="progress progress-sm mb-3">
                        <div class="progress-bar bg-{{ $status_colors[$status] ?? 'primary' }}" style="width: {{ $progress }}%"></div>
                    </div>
                    <div class="small text-secondary d-grid gap-1">
                        <div class="text-truncate"><i class="ti ti-building me-1"></i><span class="fw-bold">{{ $ship?->company?->name ?? '-' }}</span></div>
                        <div class="text-truncate"><i class="ti ti-user me-1"></i>Pimpinan: {{ $project->leader?->name ?? '-' }}</div>
                        <div class="{{ $overdue ? 'text-danger' : '' }}">
                            <i class="ti ti-calendar me-1"></i>
                            {{ $project->start_date_estimation?->format('d/m/Y') ?? '-' }} s/d {{ $project->end_date_estimation?->format('d/m/Y') ?? '-' }}
                            @if ($overdue) <span class="badge bg-danger-lt text-danger ms-1">Terlambat</span> @endif
                        </div>
                        @if ($project->divisions->isNotEmpty())
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                @foreach ($project->divisions as $division)
                                    <span class="badge bg-azure-lt">{{ $division->name }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-footer d-flex gap-2 flex-wrap">
                    <a href="{{ route('project.show', $project->unique_id) }}" class="btn btn-sm btn-primary">Detail</a>
                    <a href="{{ route('project.edit', $project->unique_id) }}" class="btn btn-sm btn-outline-secondary">Ubah</a>
                    <a href="{{ route('project.job-document.workflow.index', $project->unique_id) }}" class="btn btn-sm btn-outline-success ms-auto">Dokumen</a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center text-secondary py-5">
                    <i class="ti ti-ship-off fs-1 d-block mb-2"></i>
                    Tidak ada proyek yang sesuai.
                </div>
            </div>
        </div>
    @endforelse
</div>

@if (method_exists($projects, 'links') && $projects->hasPages())
    <div class="mt-3">{{ $projects->links('pagination::bootstrap-5') }}</div>
@endif
