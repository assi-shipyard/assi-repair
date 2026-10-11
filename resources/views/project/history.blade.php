@extends('layouts.app')

@section('title', 'Riwayat Proyek')
@section('body_title', 'Riwayat Proyek')

@section('buttons_beside_title')
    <a href="{{ route('project.index') }}" class="btn btn-outline-primary">
        <i class="ti ti-list me-1"></i>Proyek Aktif
    </a>
@endsection

@section('content')
    @include('partials.flash')

    <div class="row row-cards mb-3">
        <div class="col-6 col-md-4">
            <div class="card card-sm"><div class="card-body">
                <div class="subheader">Proyek Selesai</div>
                <div class="h2 mb-0 text-success">{{ $completed_total }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card card-sm"><div class="card-body">
                <div class="subheader">Proyek Berjalan / Belum Mulai</div>
                <div class="h2 mb-0 text-primary">{{ $active_total }}</div>
            </div></div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card card-sm"><div class="card-body">
                <div class="subheader">Hasil Filter</div>
                <div class="h2 mb-0">{{ $projects->total() }}</div>
            </div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Proyek Selesai</h3>
        </div>
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('project.history') }}" class="row g-2 align-items-end" id="form-history-filter">
                <div class="col-12 col-md-5">
                    <label class="form-label" for="search_project">Pencarian</label>
                    <input type="search" id="search_project" name="search_project" class="form-control" maxlength="100" placeholder="Kode proyek atau nama kapal" value="{{ $filters['search_project'] ?? '' }}">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label" for="project_type">Tipe Proyek</label>
                    <select id="project_type" name="project_type" class="form-select">
                        <option value="">Semua tipe</option>
                        @foreach ($project_types as $project_type)
                            <option value="{{ $project_type }}" @selected(($filters['project_type'] ?? '') === $project_type)>{{ $project_type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label" for="year">Tahun Selesai</label>
                    <input type="number" id="year" name="year" class="form-control" min="2000" max="2100" placeholder="{{ now()->year }}" value="{{ $filters['year'] ?? '' }}">
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">Terapkan</button>
                    <a href="{{ route('project.history') }}" class="btn btn-outline-secondary" title="Reset filter" aria-label="Reset filter"><i class="ti ti-refresh"></i></a>
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-hover">
                <thead>
                    <tr>
                        <th>Kode Proyek</th>
                        <th>Kapal</th>
                        <th>Tipe</th>
                        <th>Pimpinan Proyek</th>
                        <th>Mulai Aktual</th>
                        <th>Selesai Aktual</th>
                        <th class="text-end">Durasi</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($projects as $project)
                        @php
                            $started_at = $project->start_date_actual ?? $project->start_date_estimation;
                            $finished_at = $project->end_date_actual ?? $project->end_date_estimation;
                            $duration_days = $started_at && $finished_at ? $started_at->diffInDays($finished_at) + 1 : null;
                            $late = $project->end_date_actual && $project->end_date_estimation && $project->end_date_actual->gt($project->end_date_estimation);
                        @endphp
                        <tr>
                            <td><span class="badge bg-blue-lt">{{ $project->project_code }}</span></td>
                            <td>
                                <div class="fw-bold">{{ $project->ship?->name ?? '-' }}</div>
                                <div class="text-secondary small">{{ $project->ship?->company?->name ?? '-' }}</div>
                            </td>
                            <td>{{ $project->project_type }}</td>
                            <td>{{ $project->leader?->name ?? '-' }}</td>
                            <td>{{ $started_at?->tanggal_indonesia() ?? '-' }}</td>
                            <td>
                                {{ $finished_at?->tanggal_indonesia() ?? '-' }}
                                @if ($late)<span class="badge bg-danger-lt text-danger ms-1">Terlambat</span>@endif
                            </td>
                            <td class="text-end">{{ $duration_days !== null ? $duration_days.' hari' : '-' }}</td>
                            <td><a href="{{ route('project.show', $project->unique_id) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-secondary py-4">Belum ada proyek yang selesai.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($projects->hasPages())
            <div class="card-footer d-flex align-items-center">
                <p class="m-0 text-secondary">Menampilkan {{ $projects->firstItem() }}-{{ $projects->lastItem() }} dari {{ $projects->total() }} proyek</p>
                <div class="ms-auto">{{ $projects->links('pagination::bootstrap-5') }}</div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        jQuery('#form-history-filter').validate({
            rules: { search_project: { maxlength: 100 }, year: { digits: true, min: 2000, max: 2100 } },
            messages: {
                search_project: { maxlength: 'Pencarian maksimal 100 karakter.' },
                year: { digits: 'Tahun harus berupa angka.', min: 'Tahun minimal 2000.', max: 'Tahun maksimal 2100.' },
            },
            errorElement: 'div',
            errorClass: 'invalid-feedback',
            highlight: function (element) { jQuery(element).addClass('is-invalid'); },
            unhighlight: function (element) { jQuery(element).removeClass('is-invalid'); },
        });
    </script>
@endpush
