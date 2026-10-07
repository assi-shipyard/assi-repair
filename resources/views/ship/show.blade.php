@extends('layouts.app')

@section('title', 'Detail Kapal')
@section('body_title', 'Detail Kapal')

@section('buttons_beside_title')
    <a href="{{ route('ship.edit', $ship->unique_id ?? $ship->id) }}" class="btn btn-outline-primary">
        <span class="ti ti-edit me-1"></span>Ubah
    </a>
    <a href="{{ route('ship.index') }}" class="btn btn-outline-secondary">
        <span class="ti ti-arrow-left me-1"></span>Kembali
    </a>
@endsection

@section('content')
    @include('partials.flash')

    @php
        $ship_identifier = $ship->unique_id ?? $ship->id;
        $ship_build_year = $ship->build_year ? \Illuminate\Support\Carbon::parse($ship->build_year)->format('Y') : null;
        $gross_tonnage = is_numeric($ship->gross_tonnage) ? number_format((float) $ship->gross_tonnage, 0, ',', '.') : ($ship->gross_tonnage ?? null);
        $net_tonnage = is_numeric($ship->net_tonnage) ? number_format((float) $ship->net_tonnage, 0, ',', '.') : ($ship->net_tonnage ?? null);

        $status_map = [
            'In Progress' => ['label' => 'Sedang Berjalan', 'group' => 'ongoing', 'color' => 'bg-blue', 'order' => 0],
            'Not Started' => ['label' => 'Akan Datang', 'group' => 'upcoming', 'color' => 'bg-yellow', 'order' => 1],
            'Completed' => ['label' => 'Selesai', 'group' => 'history', 'color' => 'bg-green', 'order' => 2],
        ];
        $sorted_projects = $projects->sortBy(fn ($project) => $status_map[$project->status]['order'] ?? 3)->values();
        $ongoing_total = $projects->where('status', 'In Progress')->count();
        $upcoming_total = $projects->where('status', 'Not Started')->count();
        $history_total = $projects->where('status', 'Completed')->count();

        $dimensions = [
            ['LOA', $ship->length_overall, 'm'],
            ['Breadth', $ship->breadth, 'm'],
            ['Height', $ship->height, 'm'],
            ['Draft Kosong', $ship->empty_draft, 'm'],
            ['Draft Muat', $ship->loaded_draft, 'm'],
            ['Gross Tonnage', $gross_tonnage, 'GT'],
            ['Net Tonnage', $net_tonnage, 'NT'],
        ];
        $engine_rows = [
            ['Merek', $ship->engine_brand],
            ['Model', $ship->engine_model],
            ['Tipe Mesin', $ship->engine_type],
            ['Daya', $ship->engine_power],
            ['RPM', $ship->engine_rpm],
            ['Tipe BBM', $ship->engine_fuel_type],
            ['Kapasitas BBM', $ship->engine_fuel_capacity],
            ['Konsumsi BBM', $ship->engine_fuel_consumption],
        ];
    @endphp

    <style>
        .ship-hero {
            border: 0;
            background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 60%, #38bdf8 100%);
            color: #f8fafc;
        }

        .ship-hero-icon {
            flex: 0 0 auto;
            width: 4rem;
            height: 4rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: .85rem;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.25);
        }

        .ship-kpi {
            padding: .4rem .85rem;
            border-radius: .6rem;
            background: rgba(15, 23, 42, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.14);
            text-align: center;
            min-width: 5.5rem;
        }

        .ship-kpi-label {
            display: block;
            font-size: .68rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: rgba(226, 232, 240, 0.82);
        }

        .ship-kpi-value {
            display: block;
            font-size: 1.2rem;
            font-weight: 700;
            line-height: 1.3;
        }

        .ship-list-item {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: .5rem 1rem;
            border-bottom: 1px solid var(--tblr-border-color);
        }

        .ship-list-item:last-child {
            border-bottom: 0;
        }

        .ship-list-label {
            color: var(--tblr-secondary);
            white-space: nowrap;
        }

        .ship-list-value {
            font-weight: 600;
            text-align: right;
            word-break: break-word;
        }

        .ship-dimension-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(8rem, 1fr));
            gap: .5rem;
        }

        .ship-dimension {
            padding: .5rem .75rem;
            border-radius: .5rem;
            background: var(--tblr-bg-surface-secondary);
        }

        .ship-dimension-label {
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--tblr-secondary);
        }

        .ship-dimension-value {
            font-size: 1.15rem;
            font-weight: 700;
            line-height: 1.3;
        }

        .ship-table-scroll {
            max-height: 26rem;
            overflow-y: auto;
        }
    </style>

    <div class="d-flex flex-column gap-3">
        <div class="card ship-hero">
            <div class="card-body py-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3 min-w-0">
                        <div class="ship-hero-icon"><span class="ti ti-ship fs-1"></span></div>
                        <div class="min-w-0">
                            <h2 class="mb-1 text-white">{{ $ship->name }}</h2>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge bg-white text-blue">{{ $ship->type?->name ?? 'Jenis belum diatur' }}</span>
                                <span class="badge" style="background: rgba(255,255,255,.16); color: #f8fafc;">{{ $ship->classification?->abbreviation ?? $ship->classification?->name ?? 'Kelas belum diatur' }}</span>
                                <span class="badge" style="background: rgba(255,255,255,.16); color: #f8fafc;">{{ $ship->company?->name ?? 'Pemilik belum diatur' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <div class="ship-kpi">
                            <span class="ship-kpi-label">Berjalan</span>
                            <span class="ship-kpi-value">{{ $ongoing_total }}</span>
                        </div>
                        <div class="ship-kpi">
                            <span class="ship-kpi-label">Akan Datang</span>
                            <span class="ship-kpi-value">{{ $upcoming_total }}</span>
                        </div>
                        <div class="ship-kpi">
                            <span class="ship-kpi-label">Selesai</span>
                            <span class="ship-kpi-value">{{ $history_total }}</span>
                        </div>
                        <div class="ship-kpi">
                            <span class="ship-kpi-label">Tahun Bangun</span>
                            <span class="ship-kpi-value">{{ $ship_build_year ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-4">
                <div class="d-flex flex-column gap-3">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><span class="ti ti-id me-2"></span>Identitas & Registrasi</h3>
                        </div>
                        <div>
                            <div class="ship-list-item">
                                <span class="ship-list-label">Pemilik</span>
                                <span class="ship-list-value">
                                    @if ($ship->company)
                                        <a href="{{ route('company.show', $ship->company->unique_id) }}">{{ $ship->company->name }}</a>
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                            <div class="ship-list-item">
                                <span class="ship-list-label">IMO Number</span>
                                <span class="ship-list-value">{{ $ship->imo_number ?? '-' }}</span>
                            </div>
                            <div class="ship-list-item">
                                <span class="ship-list-label">MMSI Number</span>
                                <span class="ship-list-value">{{ $ship->mmsi_number ?? '-' }}</span>
                            </div>
                            <div class="ship-list-item">
                                <span class="ship-list-label">Call Sign</span>
                                <span class="ship-list-value">{{ $ship->call_sign ?? '-' }}</span>
                            </div>
                            <div class="ship-list-item">
                                <span class="ship-list-label">Bendera</span>
                                <span class="ship-list-value">{{ $ship->flag ?? '-' }}</span>
                            </div>
                            <div class="ship-list-item">
                                <span class="ship-list-label">Tahun Bangun</span>
                                <span class="ship-list-value">{{ $ship_build_year ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><span class="ti ti-engine me-2"></span>Mesin Utama</h3>
                        </div>
                        <div>
                            @foreach ($engine_rows as [$engine_label, $engine_value])
                                <div class="ship-list-item">
                                    <span class="ship-list-label">{{ $engine_label }}</span>
                                    <span class="ship-list-value">{{ $engine_value ?? '-' }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="d-flex flex-column gap-3">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><span class="ti ti-ruler-measure me-2"></span>Dimensi & Kapasitas</h3>
                        </div>
                        <div class="card-body">
                            <div class="ship-dimension-grid">
                                @foreach ($dimensions as [$dimension_label, $dimension_value, $dimension_unit])
                                    <div class="ship-dimension">
                                        <div class="ship-dimension-label">{{ $dimension_label }}</div>
                                        <div class="ship-dimension-value">
                                            {{ $dimension_value ?? '-' }}
                                            @if ($dimension_value !== null)
                                                <span class="text-secondary small fw-normal">{{ $dimension_unit }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><span class="ti ti-history me-2"></span>Riwayat Proyek</h3>
                            <div class="card-actions">
                                <ul class="nav nav-pills nav-pills-sm" id="project-filter">
                                    <li class="nav-item"><a href="#" class="nav-link active py-1 px-2" data-group="all">Semua ({{ $projects->count() }})</a></li>
                                    <li class="nav-item"><a href="#" class="nav-link py-1 px-2" data-group="ongoing">Berjalan ({{ $ongoing_total }})</a></li>
                                    <li class="nav-item"><a href="#" class="nav-link py-1 px-2" data-group="upcoming">Akan Datang ({{ $upcoming_total }})</a></li>
                                    <li class="nav-item"><a href="#" class="nav-link py-1 px-2" data-group="history">Selesai ({{ $history_total }})</a></li>
                                </ul>
                            </div>
                        </div>
                        @if ($projects->isEmpty())
                            <div class="card-body text-center text-secondary py-4">
                                <span class="ti ti-clipboard-off fs-1"></span>
                                <div class="mt-2">Kapal ini belum memiliki proyek perbaikan.</div>
                            </div>
                        @else
                            <div class="table-responsive ship-table-scroll">
                                <table class="table table-vcenter table-sm card-table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Kode</th>
                                            <th>Tipe</th>
                                            <th>Periode</th>
                                            <th>Progres</th>
                                            <th>Status</th>
                                            <th class="w-1"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($sorted_projects as $project)
                                            @php
                                                $status = $status_map[$project->status] ?? ['label' => $project->status, 'group' => 'history', 'color' => 'bg-secondary'];
                                                $period_start = $project->start_date_actual ?? $project->start_date_estimation;
                                                $period_end = $project->end_date_actual ?? $project->end_date_estimation;
                                                $progress_value = (float) $project->progress;
                                            @endphp
                                            <tr class="project-row" data-group="{{ $status['group'] }}">
                                                <td class="fw-semibold">{{ $project->project_code }}</td>
                                                <td>{{ $project->project_type }}</td>
                                                <td class="text-nowrap">
                                                    {{ $period_start?->format('d/m/Y') ?? '-' }} - {{ $period_end?->format('d/m/Y') ?? '-' }}
                                                    @unless ($project->start_date_actual)
                                                        <span class="text-secondary small">(estimasi)</span>
                                                    @endunless
                                                </td>
                                                <td style="min-width: 7rem;">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="progress flex-fill" style="height: .4rem;">
                                                            <div class="progress-bar" style="width: {{ min(100, max(0, $progress_value)) }}%"></div>
                                                        </div>
                                                        <span class="small text-secondary">{{ rtrim(rtrim(number_format($progress_value, 2, ',', '.'), '0'), ',') }}%</span>
                                                    </div>
                                                </td>
                                                <td><span class="badge {{ $status['color'] }} text-white">{{ $status['label'] }}</span></td>
                                                <td>
                                                    <a href="{{ route('project.show', $project->unique_id) }}" class="btn btn-sm btn-outline-primary">
                                                        <span class="ti ti-eye me-1"></span>Lihat
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr id="project-empty-row" class="d-none">
                                            <td colspan="6" class="text-center text-secondary py-3">Tidak ada proyek pada kategori ini.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><span class="ti ti-files me-2"></span>Dokumen Kapal</h3>
                            <div class="card-actions">
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadShipDocumentModal">
                                    <span class="ti ti-upload me-1"></span>Unggah Dokumen
                                </button>
                            </div>
                        </div>
                        @if ($documents->isEmpty())
                            <div class="card-body text-center text-secondary py-4">
                                <span class="ti ti-file-off fs-1"></span>
                                <div class="mt-2">Belum ada dokumen. Unggah sertifikat atau dokumen pendukung kapal.</div>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-vcenter table-sm card-table">
                                    <thead>
                                        <tr>
                                            <th>Dokumen</th>
                                            <th>Tipe</th>
                                            <th>Diunggah</th>
                                            <th class="w-1"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($documents as $document)
                                            <tr>
                                                <td class="fw-semibold">{{ $document->document_name }}</td>
                                                <td><span class="badge bg-blue-lt text-blue">{{ $document->document_type }}</span></td>
                                                <td class="text-nowrap text-secondary">{{ $document->created_at?->format('d/m/Y H:i') }}</td>
                                                <td>
                                                    <div class="btn-list flex-nowrap">
                                                        <a href="{{ route('ship.documents.download', [$ship_identifier, $document->unique_id]) }}" class="btn btn-sm btn-outline-primary" title="Unduh dokumen">
                                                            <span class="ti ti-download"></span>
                                                        </a>
                                                        <form action="{{ route('ship.documents.destroy', [$ship_identifier, $document->unique_id]) }}" method="POST" onsubmit="return confirm('Hapus dokumen ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn btn-sm btn-outline-danger" type="submit" title="Hapus dokumen">
                                                                <span class="ti ti-trash"></span>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modal')
    <div class="modal modal-blur fade" id="uploadShipDocumentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ route('ship.documents.store', $ship_identifier) }}" method="POST" enctype="multipart/form-data" id="form-upload-ship-document">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Unggah Dokumen Kapal</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">Nama Dokumen</label>
                            <input type="text" class="form-control" name="document_name" maxlength="255" placeholder="Contoh: Sertifikat Kelas" value="{{ old('document_name') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Tipe Dokumen</label>
                            <input type="text" class="form-control" name="document_type" maxlength="255" placeholder="Contoh: Sertifikat" value="{{ old('document_type') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">File Dokumen</label>
                            <input type="file" class="form-control" name="document_file" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                            <div class="form-hint">Format: PDF, Word, Excel, JPG, PNG. Maksimal 10MB.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary ms-auto"><span class="ti ti-upload me-1"></span>Unggah</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#project-filter .nav-link').on('click', function(event) {
                event.preventDefault();

                const group = $(this).data('group');
                $('#project-filter .nav-link').removeClass('active');
                $(this).addClass('active');

                const $rows = $('.project-row');
                $rows.each(function() {
                    $(this).toggle(group === 'all' || $(this).data('group') === group);
                });

                $('#project-empty-row').toggleClass('d-none', $rows.filter(':visible').length > 0);
            });

            $('#form-upload-ship-document').validate({
                rules: {
                    document_name: { required: true, maxlength: 255 },
                    document_type: { required: true, maxlength: 255 },
                    document_file: { required: true, extension: 'pdf|doc|docx|xls|xlsx|jpg|jpeg|png' }
                },
                messages: {
                    document_name: {
                        required: 'Nama dokumen wajib diisi.',
                        maxlength: 'Nama dokumen maksimal 255 karakter.'
                    },
                    document_type: {
                        required: 'Tipe dokumen wajib diisi.',
                        maxlength: 'Tipe dokumen maksimal 255 karakter.'
                    },
                    document_file: {
                        required: 'File dokumen wajib diunggah.',
                        extension: 'Format file harus pdf, doc, docx, xls, xlsx, jpg, jpeg, atau png.'
                    }
                },
                errorElement: 'span',
                errorPlacement: function(error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('.mb-3').append(error);
                },
                highlight: function(element) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function(element) {
                    $(element).removeClass('is-invalid');
                }
            });
        });
    </script>
@endpush
