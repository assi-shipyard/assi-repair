@extends('layouts.app')

@section('title', 'Detail Proyek')
@section('body_title', 'Detail Proyek')

@section('buttons_beside_title')
    <a href="{{ route('project.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i>Kembali
    </a>
    <a href="{{ route('project.edit', $project->unique_id) }}" class="btn btn-outline-primary">
        <i class="ti ti-pencil me-1"></i>Ubah
    </a>
    <a href="{{ route('project.job-document.workflow.index', $project->unique_id) }}" class="btn btn-primary">
        <i class="ti ti-files me-1"></i>Dokumen
    </a>
@endsection

@push('styles')
    <style>
        .project-cover { position: relative; color: #fff; overflow: hidden; border-radius: var(--tblr-card-border-radius) var(--tblr-card-border-radius) 0 0; }
        .project-cover .cover-icon { position: absolute; right: 1rem; bottom: -2.5rem; font-size: 11rem; opacity: .15; line-height: 1; }
        .project-cover .cover-body { position: relative; padding: 1.25rem 1.5rem; }
        .project-note { white-space: pre-line; }
        .kv dt { font-weight: 400; color: var(--tblr-secondary); font-size: .8rem; }
        .kv dd { font-weight: 600; margin-bottom: .75rem; }
    </style>
@endpush

@section('content')
    @include('partials.flash')

    @php
        $ship = $project->ship;
        $progress = max(0, min(100, (float) ($project->progress ?? 0)));
        $status = $project->status ?? 'Not Started';
        $status_labels = ['Not Started' => 'Belum Mulai', 'In Progress' => 'Berjalan', 'Completed' => 'Selesai'];
        $status_color = ['Not Started' => 'secondary', 'In Progress' => 'primary', 'Completed' => 'success'][$status] ?? 'secondary';
        $hue = crc32((string) ($ship?->name ?? $project->project_code)) % 360;
        $overdue = $project->end_date_estimation && $status !== 'Completed' && $project->end_date_estimation->isPast();
        $fmt = fn ($d) => $d?->translatedFormat('d M Y') ?? '-';
        $doc_labels = [
            'repair_list' => 'Daftar Perbaikan', 'initial_boq' => 'BOQ Awal', 'satisfaction_notes' => 'Catatan Kepuasan',
            'final_boq' => 'BOQ Akhir', 'docking_report' => 'Laporan Docking',
        ];
        $spec = fn ($v, $unit = '') => filled($v) ? $v.($unit ? ' '.$unit : '') : '-';
    @endphp

    <div class="card mb-3">
        <div class="project-cover" style="background: linear-gradient(135deg, hsl({{ $hue }}, 55%, 30%), hsl({{ ($hue + 40) % 360 }}, 60%, 45%));">
            <i class="ti ti-ship cover-icon"></i>
            <div class="cover-body">
                <div class="d-flex flex-wrap gap-2 mb-2">
                    <span class="badge bg-white text-dark font-monospace">{{ $project->project_code }}</span>
                    <span class="badge bg-{{ $status_color }}">{{ $status_labels[$status] ?? $status }}</span>
                    <span class="badge bg-white-lt">{{ $project->project_type ?: 'Tipe proyek belum ditentukan' }}</span>
                    @if ($overdue)<span class="badge bg-danger">Terlambat</span>@endif
                </div>
                <h2 class="mb-1 text-white">{{ $ship?->name ?? 'Kapal belum ditentukan' }}</h2>
                <div class="opacity-75">
                    {{ $ship?->type?->name ?? 'Tipe kapal -' }}
                    @if ($ship?->imo_number) &middot; IMO {{ $ship->imo_number }} @endif
                    @if ($ship?->call_sign) &middot; {{ $ship->call_sign }} @endif
                    @if ($ship?->flag) &middot; {{ $ship->flag }} @endif
                    <br>{{ $ship?->company?->name ?? 'Perusahaan belum ditentukan' }}
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-12 col-lg-5">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-secondary small">Kemajuan proyek</span>
                        <span class="fw-bold">{{ number_format($progress, 0) }}%</span>
                    </div>
                    <div class="progress progress-sm">
                        <div class="progress-bar bg-{{ $status_color }}" role="progressbar" style="width: {{ $progress }}%" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="text-secondary small">Estimasi</div>
                    <div class="fw-semibold {{ $overdue ? 'text-danger' : '' }}">{{ $fmt($project->start_date_estimation) }} &ndash; {{ $fmt($project->end_date_estimation) }}</div>
                </div>
                <div class="col-6 col-lg-4">
                    <div class="text-secondary small">Realisasi</div>
                    <div class="fw-semibold">{{ $fmt($project->start_date_actual) }} &ndash; {{ $fmt($project->end_date_actual) }}</div>
                </div>
            </div>
        </div>
        <div class="card-header border-top">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab_ringkasan" role="tab">Ringkasan</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab_kapal" role="tab">Data Kapal</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab_surveyor" role="tab">Surveyor <span class="badge bg-secondary-lt ms-1">{{ $project->owner_surveyors->count() }}</span></a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab_dokumen" role="tab">Dokumen <span class="badge bg-secondary-lt ms-1">{{ $project->job_documents->count() }}</span></a></li>
            </ul>
        </div>
    </div>

    <div class="tab-content">
        <div class="tab-pane active show" id="tab_ringkasan" role="tabpanel">
            <div class="row row-cards">
                <div class="col-lg-8">
                    <div class="card mb-3">
                        <div class="card-header"><h3 class="card-title">Catatan Proyek</h3></div>
                        <div class="card-body project-note {{ blank($project->comment) ? 'text-secondary' : '' }}">{{ $project->comment ?: 'Belum ada catatan proyek.' }}</div>
                    </div>
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Divisi Pelaksana</h3></div>
                        <div class="card-body d-flex flex-wrap gap-2">
                            @forelse ($project->divisions as $division)
                                <span class="badge bg-azure-lt">{{ $division->name }}</span>
                            @empty
                                <span class="text-secondary">Belum ada divisi pelaksana.</span>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Tim Pelaksana</h3></div>
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex align-items-center">
                                <span class="avatar bg-blue-lt text-blue me-3"><i class="ti ti-user-star"></i></span>
                                <div><div class="text-secondary small">Pimpinan Proyek</div><div class="fw-semibold">{{ $project->leader?->name ?? 'Belum ditetapkan' }}</div></div>
                            </div>
                            <div class="list-group-item d-flex align-items-center">
                                <span class="avatar bg-azure-lt text-azure me-3"><i class="ti ti-user-cog"></i></span>
                                <div><div class="text-secondary small">PPC Proyek</div><div class="fw-semibold">{{ $project->ppc?->name ?? 'Belum ditetapkan' }}</div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane" id="tab_kapal" role="tabpanel">
            <div class="row row-cards">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header"><h3 class="card-title">Identitas &amp; Dimensi</h3></div>
                        <div class="card-body">
                            <dl class="row kv mb-0">
                                <div class="col-6"><dt>Nama</dt><dd>{{ $spec($ship?->name) }}</dd></div>
                                <div class="col-6"><dt>Tipe</dt><dd>{{ $spec($ship?->type?->name) }}</dd></div>
                                <div class="col-6"><dt>Klasifikasi</dt><dd>{{ $spec($ship?->classification?->name) }}</dd></div>
                                <div class="col-6"><dt>Tahun Bangun</dt><dd>{{ $spec($ship?->build_year) }}</dd></div>
                                <div class="col-6"><dt>Panjang (LOA)</dt><dd>{{ $spec($ship?->length_overall, 'm') }}</dd></div>
                                <div class="col-6"><dt>Lebar</dt><dd>{{ $spec($ship?->breadth, 'm') }}</dd></div>
                                <div class="col-6"><dt>Tinggi</dt><dd>{{ $spec($ship?->height, 'm') }}</dd></div>
                                <div class="col-6"><dt>Sarat Kosong / Muat</dt><dd>{{ $spec($ship?->empty_draft) }} / {{ $spec($ship?->loaded_draft, 'm') }}</dd></div>
                                <div class="col-6"><dt>GT</dt><dd>{{ $spec($ship?->gross_tonnage) }}</dd></div>
                                <div class="col-6"><dt>NT</dt><dd>{{ $spec($ship?->net_tonnage) }}</dd></div>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header"><h3 class="card-title">Mesin</h3></div>
                        <div class="card-body">
                            <dl class="row kv mb-0">
                                <div class="col-6"><dt>Merek</dt><dd>{{ $spec($ship?->engine_brand) }}</dd></div>
                                <div class="col-6"><dt>Model</dt><dd>{{ $spec($ship?->engine_model) }}</dd></div>
                                <div class="col-6"><dt>Tipe</dt><dd>{{ $spec($ship?->engine_type) }}</dd></div>
                                <div class="col-6"><dt>Daya</dt><dd>{{ $spec($ship?->engine_power) }}</dd></div>
                                <div class="col-6"><dt>RPM</dt><dd>{{ $spec($ship?->engine_rpm) }}</dd></div>
                                <div class="col-6"><dt>Bahan Bakar</dt><dd>{{ $spec($ship?->engine_fuel_type) }}</dd></div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane" id="tab_surveyor" role="tabpanel">
            <div class="row row-cards">
                @forelse ($project->owner_surveyors as $surveyor)
                    <div class="col-md-6 col-xl-4">
                        <div class="card h-100">
                            <div class="card-body d-flex gap-3">
                                <span class="avatar bg-yellow-lt text-yellow">{{ strtoupper(mb_substr($surveyor->name, 0, 1)) }}</span>
                                <div class="min-w-0">
                                    <div class="fw-semibold">{{ $surveyor->name }}</div>
                                    <div class="small text-secondary">{{ $surveyor->position ?: 'Jabatan belum dicantumkan' }}</div>
                                    <div class="small text-secondary mt-2"><i class="ti ti-building me-1"></i>{{ $surveyor->company ?: '-' }}</div>
                                    @if ($surveyor->email)<div class="small text-secondary text-break"><i class="ti ti-mail me-1"></i>{{ $surveyor->email }}</div>@endif
                                    @if ($surveyor->phone)<div class="small text-secondary"><i class="ti ti-phone me-1"></i>{{ $surveyor->phone }}</div>@endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12"><div class="card"><div class="card-body text-center text-secondary py-4">Belum ada surveyor pemilik kapal yang ditetapkan.</div></div></div>
                @endforelse
            </div>
        </div>

        <div class="tab-pane" id="tab_dokumen" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Dokumen Pekerjaan</h3>
                    <div class="card-actions">
                        <a href="{{ route('project.job-document.workflow.index', $project->unique_id) }}" class="btn btn-primary btn-sm">Kelola Dokumen</a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead><tr><th>Jenis</th><th>Nomor</th><th>Revisi</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse ($project->job_documents->sortBy(['document_type', 'revision_no']) as $document)
                                <tr>
                                    <td>{{ $doc_labels[$document->document_type] ?? $document->document_type }}</td>
                                    <td class="font-monospace">{{ $document->document_number ?: '-' }}</td>
                                    <td>Rev. {{ $document->revision_no }}</td>
                                    <td><span class="badge bg-secondary-lt text-uppercase">{{ $document->status }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-secondary py-4">Belum ada dokumen pekerjaan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
