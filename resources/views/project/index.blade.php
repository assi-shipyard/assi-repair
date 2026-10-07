@extends('layouts.app')

@section('title', 'Data Proyek')
@section('body_title', 'Data Proyek')

@section('buttons_beside_title')
    <a href="{{ route('project.create') }}" class="btn btn-primary">
        <i class="ti ti-plus me-1"></i>Tambah Proyek
    </a>
@endsection

@push('styles')
    <style>
        .project-cover { position: relative; height: 120px; color: #fff; overflow: hidden; border-radius: var(--tblr-card-border-radius) var(--tblr-card-border-radius) 0 0; }
        .project-cover .cover-icon { position: absolute; right: -8px; bottom: -22px; font-size: 7.5rem; opacity: .18; line-height: 1; }
        .project-cover .cover-body { position: relative; padding: .75rem 1rem; height: 100%; display: flex; flex-direction: column; justify-content: space-between; }
        .project-cover .cover-name { font-size: 1.15rem; font-weight: 600; line-height: 1.25; }
        .project-cover .cover-meta { font-size: .75rem; opacity: .9; }
        .status-tab.active { border-color: var(--tblr-primary); }
    </style>
@endpush

@section('content')
    @include('partials.flash')

    @php
        $status_labels = ['Not Started' => 'Belum Mulai', 'In Progress' => 'Berjalan', 'Completed' => 'Selesai'];
        $status_colors = ['Not Started' => 'secondary', 'In Progress' => 'primary', 'Completed' => 'success'];
        $active_status = $filters['status'] ?? '';
        $total_all = $status_counts->sum();
    @endphp

    <div class="row row-cards mb-3">
        <div class="col-6 col-lg-3">
            <a href="{{ route('project.index') }}" class="card card-sm text-decoration-none status-tab {{ $active_status === '' ? 'active' : '' }}">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar bg-dark-lt"><i class="ti ti-layout-grid"></i></span>
                    <div><div class="fw-bold">{{ $total_all }}</div><div class="text-secondary small">Semua Proyek</div></div>
                </div>
            </a>
        </div>
        @foreach ($status_labels as $status_key => $status_label)
            <div class="col-6 col-lg-3">
                <a href="{{ route('project.index', array_merge(request()->except('status', 'page'), ['status' => $status_key])) }}"
                   class="card card-sm text-decoration-none status-tab {{ $active_status === $status_key ? 'active' : '' }}">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="avatar bg-{{ $status_colors[$status_key] }}-lt text-{{ $status_colors[$status_key] }}"><i class="ti ti-{{ ['Not Started' => 'clock', 'In Progress' => 'progress', 'Completed' => 'circle-check'][$status_key] }}"></i></span>
                        <div><div class="fw-bold">{{ $status_counts[$status_key] ?? 0 }}</div><div class="text-secondary small">{{ $status_label }}</div></div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <form class="card mb-3" method="GET" action="{{ route('project.index') }}" id="project_filter_form">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-12 col-lg-5">
                    <div class="input-icon">
                        <span class="input-icon-addon"><i class="ti ti-search"></i></span>
                        <input class="form-control" name="search_project" maxlength="100" value="{{ $filters['search_project'] ?? '' }}" placeholder="Kode proyek, tipe, nama kapal, IMO, atau call sign">
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <select class="form-select" name="status" aria-label="Status">
                        <option value="">Semua status</option>
                        @foreach ($status_labels as $status_key => $status_label)
                            <option value="{{ $status_key }}" @selected($active_status === $status_key)>{{ $status_label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-lg-2">
                    <select class="form-select" name="project_type" aria-label="Tipe proyek">
                        <option value="">Semua tipe</option>
                        @foreach ($project_types as $type)
                            <option value="{{ $type }}" @selected(($filters['project_type'] ?? '') === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-lg-3">
                    <select class="form-select" name="sort" aria-label="Urutkan">
                        @foreach (['latest' => 'Terbaru', 'oldest' => 'Terlama', 'progress_desc' => 'Progres tertinggi', 'progress_asc' => 'Progres terendah', 'ship_name' => 'Nama kapal (A-Z)'] as $sort_key => $sort_label)
                            <option value="{{ $sort_key }}" @selected(($filters['sort'] ?? 'latest') === $sort_key)>Urutkan: {{ $sort_label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="collapse {{ collect($filters)->only(['ship_id', 'division_id', 'start_from', 'start_to'])->filter()->isNotEmpty() ? 'show' : '' }}" id="advanced_filter">
                <div class="row g-2 mt-1">
                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label small mb-1">Kapal</label>
                        <select class="form-select" name="ship_id">
                            <option value="">Semua kapal</option>
                            @foreach ($ships as $ship)
                                <option value="{{ $ship->id }}" @selected((int) ($filters['ship_id'] ?? 0) === $ship->id)>{{ $ship->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label small mb-1">Divisi</label>
                        <select class="form-select" name="division_id">
                            <option value="">Semua divisi</option>
                            @foreach ($divisions as $division)
                                <option value="{{ $division->id }}" @selected((int) ($filters['division_id'] ?? 0) === $division->id)>{{ $division->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-lg-3">
                        <label class="form-label small mb-1">Estimasi mulai dari</label>
                        <input type="date" class="form-control" name="start_from" value="{{ $filters['start_from'] ?? '' }}">
                    </div>
                    <div class="col-6 col-lg-3">
                        <label class="form-label small mb-1">Estimasi mulai sampai</label>
                        <input type="date" class="form-control" name="start_to" value="{{ $filters['start_to'] ?? '' }}">
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <button class="btn btn-primary" type="submit"><i class="ti ti-filter me-1"></i>Terapkan</button>
                <a class="btn btn-outline-secondary" href="{{ route('project.index') }}">Reset</a>
                <a class="btn btn-link ms-auto" data-bs-toggle="collapse" href="#advanced_filter" role="button">Filter lanjutan</a>
            </div>
        </div>
    </form>

    <div class="text-secondary small mb-2">Menampilkan <strong>{{ $projects->total() }}</strong> proyek</div>

    <div id="project_cards">
        @include('project.partials.projectcards', ['projects' => $projects])
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            $('#project_filter_form select').not('#advanced_filter select').on('change', function () {
                $('#project_filter_form').trigger('submit');
            });
        });
    </script>
@endpush
