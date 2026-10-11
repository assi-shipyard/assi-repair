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

    <div class="card mb-3">
        <div class="card-body">
            <div class="subheader">Buku Kamus Harga Proyek</div>
            @if ($project->price_book)
                <a class="fw-semibold" href="{{ route('price-book.show', $project->price_book) }}">{{ $project->price_book->code }} - {{ $project->price_book->name }}</a>
            @else
                <span class="text-secondary">Belum dipilih</span>
            @endif
            <div class="text-secondary small mt-1">Pilihan buku ditetapkan per proyek, bukan berdasarkan pelanggan. Harga BoQ yang sudah tersimpan tidak berubah saat buku diperbarui atau pilihan buku diganti.</div>
        </div>
    </div>

    @php
        $ship = $project->ship;
        $progress = max(0, min(100, (float) ($project->progress ?? 0)));
        $status = $project->status ?? 'Not Started';
        $status_labels = ['Not Started' => 'Belum Mulai', 'In Progress' => 'Berjalan', 'Completed' => 'Selesai'];
        $status_color = ['Not Started' => 'secondary', 'In Progress' => 'primary', 'Completed' => 'success'][$status] ?? 'secondary';
        $hue = crc32((string) ($ship?->name ?? $project->project_code)) % 360;
        $overdue = $project->end_date_estimation && $status !== 'Completed' && $project->end_date_estimation->isPast();
        $fmt = fn ($d) => $d?->tanggal_indonesia() ?? '-';
        $doc_labels = [
            'repair_list' => 'Daftar Perbaikan', 'initial_boq' => 'BOQ Awal', 'satisfaction_notes' => 'Catatan Kepuasan',
            'final_boq' => 'BOQ Akhir', 'docking_report' => 'Laporan Docking',
        ];
        $days_left = $project->end_date_estimation && $status !== 'Completed' ? (int) now()->startOfDay()->diffInDays($project->end_date_estimation, false) : null;
        $current_occupancy = $occupancies->firstWhere('occupancy_status', 'occupied') ?? $occupancies->firstWhere('occupancy_status', 'scheduled');
        $occupancy_labels = ['scheduled' => 'Terjadwal', 'occupied' => 'Di dock', 'undocked' => 'Keluar dock'];
        $request_labels = ['draft' => 'Draf', 'submitted' => 'Diajukan', 'engineering_approved' => 'Disetujui Engineering', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'cancelled' => 'Dibatalkan'];
        $docs_final = $project->job_documents->whereIn('status', ['approved', 'locked', 'final'])->count();
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
        <div class="card-body border-top">
            <div class="row g-3">
                <div class="col-6 col-lg-3">
                    <div class="d-flex align-items-center gap-3">
                        <span class="avatar bg-{{ $overdue ? 'danger' : 'blue' }}-lt"><i class="ti ti-hourglass"></i></span>
                        <div>
                            <div class="text-secondary small">Sisa waktu</div>
                            <div class="fw-bold {{ $overdue ? 'text-danger' : '' }}">
                                @if ($status === 'Completed') Selesai
                                @elseif ($days_left === null) Jadwal belum diatur
                                @elseif ($days_left < 0) Terlambat {{ abs($days_left) }} hari
                                @else {{ $days_left }} hari lagi @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="d-flex align-items-center gap-3">
                        <span class="avatar bg-azure-lt"><i class="ti ti-anchor"></i></span>
                        <div>
                            <div class="text-secondary small">Docking</div>
                            <div class="fw-bold">
                                @if ($current_occupancy) {{ $occupancy_labels[$current_occupancy->occupancy_status] ?? '-' }} &middot; {{ $current_occupancy->docking_space?->name ?? '-' }}
                                @else Belum terjadwal @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="d-flex align-items-center gap-3">
                        <span class="avatar bg-green-lt"><i class="ti ti-files"></i></span>
                        <div>
                            <div class="text-secondary small">Dokumen pekerjaan</div>
                            <div class="fw-bold">{{ $project->job_documents->count() }} dokumen &middot; {{ $docs_final }} final</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="d-flex align-items-center gap-3">
                        <span class="avatar bg-yellow-lt"><i class="ti ti-history"></i></span>
                        <div>
                            <div class="text-secondary small">Pembaruan terakhir</div>
                            <div class="fw-bold">{{ $timeline->first()['at']?->diffForHumans() ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-header border-top">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab_ringkasan" role="tab">Ringkasan</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab_docking" role="tab">Docking <span class="badge bg-secondary-lt ms-1">{{ $occupancies->count() }}</span></a></li>
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

        <div class="tab-pane" id="tab_docking" role="tabpanel">
            <div class="row row-cards">
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header"><h3 class="card-title">Jadwal &amp; Realisasi Dock</h3></div>
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead><tr><th>Docking space</th><th>Masuk</th><th>Est. keluar</th><th>Keluar</th><th>Status</th></tr></thead>
                                <tbody>
                                    @forelse ($occupancies as $occupancy)
                                        <tr>
                                            <td>{{ $occupancy->docking_space?->name ?? '-' }}</td>
                                            <td>{{ $occupancy->docked_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                            <td>{{ $occupancy->estimated_undock_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                            <td>{{ $occupancy->undocked_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                            <td><span class="badge bg-azure-lt">{{ $occupancy_labels[$occupancy->occupancy_status] ?? $occupancy->occupancy_status }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-secondary py-4">Belum ada jadwal docking.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header"><h3 class="card-title">Permohonan Docking Terkait</h3></div>
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead><tr><th>Docking space</th><th>Rencana</th><th>Status</th></tr></thead>
                                <tbody>
                                    @forelse ($docking_requests as $docking_request)
                                        <tr>
                                            <td>{{ $docking_request->requested_docking_space?->name ?? '-' }}</td>
                                            <td>{{ $docking_request->requested_start_at?->format('d/m/Y') }} &ndash; {{ $docking_request->requested_end_at?->format('d/m/Y') ?? '-' }}</td>
                                            <td><span class="badge bg-secondary-lt">{{ $request_labels[$docking_request->request_status] ?? $docking_request->request_status }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center text-secondary py-4">Tidak ada permohonan docking terkait.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
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

    @php
        $event_style = [
            'created' => ['plus', 'green'], 'docking_linked' => ['anchor', 'azure'], 'status_changed' => ['flag', 'purple'],
            'progress_changed' => ['progress', 'blue'], 'divisions_changed' => ['users-group', 'orange'],
            'surveyors_changed' => ['user-search', 'yellow'], 'document' => ['file-description', 'teal'], 'updated' => ['pencil', 'secondary'],
        ];
    @endphp
    <div class="card mt-3">
        <div class="card-header">
            <h3 class="card-title"><i class="ti ti-history me-2 text-primary"></i>Linimasa Pembaruan Proyek</h3>
            <div class="card-actions text-secondary small">{{ $timeline->count() }} aktivitas</div>
        </div>
        <div class="card-body">
            @if ($timeline->isEmpty())
                <div class="text-center text-secondary py-3">Belum ada riwayat pembaruan.</div>
            @else
                <ul class="timeline">
                    @foreach ($timeline as $entry)
                        @php [$icon, $color] = $event_style[$entry['event']] ?? ['point', 'secondary']; @endphp
                        <li class="timeline-event">
                            <div class="timeline-event-icon bg-{{ $color }}-lt text-{{ $color }}"><i class="ti ti-{{ $icon }}"></i></div>
                            <div class="card timeline-event-card">
                                <div class="card-body py-2">
                                    <div class="d-flex flex-wrap justify-content-between gap-1">
                                        <div class="fw-semibold">{{ $entry['description'] }}</div>
                                        <div class="text-secondary small" title="{{ $entry['at']?->format('d/m/Y H:i:s') }}">{{ $entry['at']?->format('d/m/Y H:i') }}</div>
                                    </div>
                                    @if (! empty($entry['changes']))
                                        <ul class="list-unstyled small mb-1 mt-1">
                                            @foreach ($entry['changes'] as $change)
                                                <li>
                                                    <span class="text-secondary">{{ $change['field'] }}:</span>
                                                    <span class="text-decoration-line-through text-secondary">{{ $change['old'] ?? 'kosong' }}</span>
                                                    <i class="ti ti-arrow-right mx-1"></i>
                                                    <strong>{{ $change['new'] ?? 'kosong' }}</strong>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    <div class="text-secondary small"><i class="ti ti-user me-1"></i>{{ $entry['actor'] ?? 'Sistem' }}</div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection
