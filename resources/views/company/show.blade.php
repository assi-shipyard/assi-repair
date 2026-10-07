@extends('layouts.app')

@section('title', 'Detail Perusahaan')
@section('body_title', 'Detail Perusahaan')

@section('buttons_beside_title')
    <a href="{{ route('company.edit', $company->unique_id) }}" class="btn btn-outline-primary">
        <span class="ti ti-edit me-1"></span>Ubah
    </a>
    <a href="{{ route('company.index') }}" class="btn btn-outline-secondary">
        <span class="ti ti-arrow-left me-1"></span>Kembali
    </a>
@endsection

@section('content')
    @include('partials.flash')

    @php
        $logo_url = $company->logo_path
            ? Storage::disk('public')->url('company_logos/' . $company->logo_path)
            : asset('assets/img/default_profile.jpg');
        $profile_completion = collect([
            $company->address,
            $company->phone_1,
            $company->email,
            $company->ceo_name,
            $company->pic_name,
            $company->registration_number,
            $company->tax_id,
        ])->filter()->count();
    @endphp

    <style>
        .detail-hero {
            border: 0;
            background: linear-gradient(135deg, #0f172a 0%, #155e75 60%, #22a6c4 100%);
            color: #f8fafc;
        }

        .detail-hero-avatar {
            flex: 0 0 auto;
            width: 4rem;
            height: 4rem;
            border-radius: .85rem;
            background-size: cover;
            background-position: center;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .detail-kpi {
            padding: .4rem .85rem;
            border-radius: .6rem;
            background: rgba(15, 23, 42, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.14);
            text-align: center;
            min-width: 5.5rem;
        }

        .detail-kpi-label {
            display: block;
            font-size: .68rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: rgba(226, 232, 240, 0.82);
        }

        .detail-kpi-value {
            display: block;
            font-size: 1.2rem;
            font-weight: 700;
            line-height: 1.3;
        }

        .detail-list-item {
            display: flex;
            gap: .75rem;
            padding: .6rem 1rem;
            border-bottom: 1px solid var(--tblr-border-color);
        }

        .detail-list-item:last-child {
            border-bottom: 0;
        }

        .detail-list-item .ti {
            flex: 0 0 auto;
            margin-top: .15rem;
            color: var(--tblr-secondary);
        }

        .detail-list-label {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--tblr-secondary);
        }

        .detail-table-scroll {
            max-height: 24rem;
            overflow-y: auto;
        }
    </style>

    <div class="d-flex flex-column gap-3">
        <div class="card detail-hero">
            <div class="card-body py-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3 min-w-0">
                        <div class="detail-hero-avatar" style="background-image: url('{{ $logo_url }}');"></div>
                        <div class="min-w-0">
                            <h2 class="mb-1 text-white">{{ $company->name }}</h2>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge bg-white text-cyan">{{ $company->registration_number ?? 'Registrasi belum diatur' }}</span>
                                <span class="badge" style="background: rgba(255,255,255,.16); color: #f8fafc;">NPWP {{ $company->tax_id ?? '-' }}</span>
                                <span class="badge {{ $profile_completion >= 5 ? 'bg-green' : 'bg-yellow' }}">
                                    {{ $profile_completion >= 5 ? 'Profil lengkap' : 'Perlu dilengkapi' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <div class="detail-kpi">
                            <span class="detail-kpi-label">Kapal</span>
                            <span class="detail-kpi-value">{{ $ships->count() }}</span>
                        </div>
                        <div class="detail-kpi">
                            <span class="detail-kpi-label">Dokumen</span>
                            <span class="detail-kpi-value">{{ $documents->count() }}</span>
                        </div>
                        <div class="detail-kpi">
                            <span class="detail-kpi-label">Profil</span>
                            <span class="detail-kpi-value">{{ $profile_completion }}/7</span>
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
                            <h3 class="card-title"><span class="ti ti-id me-2"></span>Informasi Perusahaan</h3>
                        </div>
                        <div class="p-0">
                            <div class="detail-list-item">
                                <span class="ti ti-map-pin"></span>
                                <div>
                                    <div class="detail-list-label">Alamat</div>
                                    <div class="fw-semibold">{{ $company->address ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="detail-list-item">
                                <span class="ti ti-mail"></span>
                                <div class="min-w-0">
                                    <div class="detail-list-label">Email</div>
                                    <div class="fw-semibold text-break">{{ $company->email ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="detail-list-item">
                                <span class="ti ti-phone"></span>
                                <div>
                                    <div class="detail-list-label">Telepon</div>
                                    <div class="fw-semibold">{{ $company->phone_1 ?? '-' }}</div>
                                    @if ($company->phone_2)
                                        <div class="fw-semibold">{{ $company->phone_2 }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="detail-list-item">
                                <span class="ti ti-user-star"></span>
                                <div>
                                    <div class="detail-list-label">Direktur Utama</div>
                                    <div class="fw-semibold">{{ $company->ceo_name ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="detail-list-item">
                                <span class="ti ti-user"></span>
                                <div>
                                    <div class="detail-list-label">PIC</div>
                                    <div class="fw-semibold">{{ $company->pic_name ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><span class="ti ti-photo me-2"></span>Logo Perusahaan</h3>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('company.upload-logo', $company->unique_id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="input-group">
                                    <input class="form-control" type="file" name="company_logo" accept="image/*" required>
                                    <button class="btn btn-primary" type="submit">Unggah</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="d-flex flex-column gap-3">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><span class="ti ti-ship me-2"></span>Kapal Milik Perusahaan</h3>
                            <div class="card-actions">
                                <span class="badge bg-blue-lt text-blue">{{ $ships->count() }} kapal</span>
                            </div>
                        </div>
                        @if ($ships->isEmpty())
                            <div class="card-body text-center text-secondary py-4">
                                <span class="ti ti-ship-off fs-1"></span>
                                <div class="mt-2">Perusahaan ini belum memiliki kapal.</div>
                            </div>
                        @else
                            <div class="table-responsive detail-table-scroll">
                                <table class="table table-vcenter table-sm card-table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nama Kapal</th>
                                            <th>Jenis</th>
                                            <th>Klasifikasi</th>
                                            <th class="w-1"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($ships as $ship)
                                            <tr>
                                                <td class="fw-semibold">{{ $ship->name }}</td>
                                                <td>{{ $ship->type?->name ?? '-' }}</td>
                                                <td>{{ $ship->classification?->name ?? '-' }}</td>
                                                <td>
                                                    <a href="{{ route('ship.show', $ship->unique_id ?? $ship->id) }}" class="btn btn-sm btn-outline-primary">
                                                        <span class="ti ti-eye me-1"></span>Lihat
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><span class="ti ti-files me-2"></span>Dokumen Perusahaan</h3>
                            <div class="card-actions">
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                                    <span class="ti ti-upload me-1"></span>Unggah Dokumen
                                </button>
                            </div>
                        </div>
                        @if ($documents->isEmpty())
                            <div class="card-body text-center text-secondary py-4">
                                <span class="ti ti-file-off fs-1"></span>
                                <div class="mt-2">Belum ada dokumen. Unggah dokumen untuk melengkapi arsip perusahaan.</div>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-vcenter table-sm card-table">
                                    <thead>
                                        <tr>
                                            <th>Dokumen</th>
                                            <th>Tipe</th>
                                            <th>File</th>
                                            <th class="w-1"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($documents as $document)
                                            <tr>
                                                <td class="fw-semibold">{{ $document->document_name }}</td>
                                                <td><span class="badge bg-cyan-lt text-cyan">{{ $document->document_type }}</span></td>
                                                <td class="text-break">
                                                    <a href="{{ Storage::disk('public')->url($document->document_path) }}" target="_blank" rel="noopener noreferrer">
                                                        {{ basename((string) $document->document_path) }}
                                                    </a>
                                                </td>
                                                <td>
                                                    <form action="{{ route('company.delete-document', [$company->unique_id, $document->unique_id ?? $document->id]) }}" method="POST" onsubmit="return confirm('Hapus dokumen ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-sm btn-outline-danger" type="submit" title="Hapus dokumen">
                                                            <span class="ti ti-trash"></span>
                                                        </button>
                                                    </form>
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
    <div class="modal modal-blur fade" id="uploadDocumentModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ route('company.upload-document', $company->unique_id) }}" method="POST" enctype="multipart/form-data" id="form-upload-document">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Unggah Dokumen Perusahaan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">Nama Dokumen</label>
                            <input type="text" class="form-control" name="document_name" placeholder="Masukkan nama dokumen">
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Tipe Dokumen</label>
                            <input type="text" class="form-control" name="document_type" placeholder="Masukkan tipe dokumen">
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">File Dokumen</label>
                            <input type="file" class="form-control" name="document_file">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary ms-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-upload" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"></path>
                                <polyline points="7 9 12 4 17 9"></polyline>
                                <line x1="12" y1="4" x2="12" y2="16"></line>
                            </svg>
                            Unggah
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#form-upload-document').validate({
                rules: {
                    document_name: {
                        required: true,
                        maxlength: 255
                    },
                    document_type: {
                        required: true,
                        maxlength: 255
                    },
                    document_file: {
                        required: true
                    }
                },
                messages: {
                    document_name: {
                        required: "Nama dokumen wajib diisi.",
                        maxlength: "Nama dokumen maksimal 255 karakter."
                    },
                    document_type: {
                        required: "Tipe dokumen wajib diisi.",
                        maxlength: "Tipe dokumen maksimal 255 karakter."
                    },
                    document_file: {
                        required: "File dokumen wajib diunggah."
                    }
                },
                errorElement: "span",
                errorPlacement: function(error, element) {
                    error.addClass("invalid-feedback");
                    element.closest(".mb-3").append(error);
                },
                highlight: function(element, errorClass, validClass) {
                    $(element).addClass("is-invalid");
                },
                unhighlight: function(element, errorClass, validClass) {
                    $(element).removeClass("is-invalid");
                }
            });
        });
    </script>
@endpush
