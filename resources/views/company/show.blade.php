@extends('layouts.app')

@section('title', 'Detail Perusahaan')
@section('body_title', 'Detail Perusahaan')

@section('buttons_beside_title')
    <a href="{{ route('company.edit', $company->unique_id) }}" class="btn btn-outline-primary">Ubah</a>
    <a href="{{ route('company.index') }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
    @include('partials.flash')

    @php
        $logo_url = $company->logo_path
            ? Storage::disk('public')->url('company_logos/' . $company->logo_path)
            : asset('assets/img/default_profile.jpg');
        $document_total = $documents->count();
        $contact_total = collect([$company->phone_1, $company->phone_2, $company->email])->filter()->count();
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
            position: relative;
            overflow: hidden;
            border: 0;
            border-radius: 1.25rem;
            background: linear-gradient(135deg, #0f172a 0%, #155e75 55%, #67e8f9 100%);
            color: #f8fafc;
            box-shadow: 0 2rem 4rem -2.75rem rgba(15, 23, 42, 0.85);
        }

        .detail-hero::after {
            content: '';
            position: absolute;
            inset: auto -4rem -5rem auto;
            width: 14rem;
            height: 14rem;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
        }

        .detail-hero-body {
            position: relative;
            z-index: 1;
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            align-items: center;
            justify-content: space-between;
            padding: 1.75rem;
        }

        .detail-hero-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .detail-hero-avatar {
            width: 5.5rem;
            height: 5.5rem;
            border-radius: 1.35rem;
            background-size: cover;
            background-position: center;
            border: 3px solid rgba(255, 255, 255, 0.25);
            box-shadow: 0 1.25rem 2.5rem -1.75rem rgba(15, 23, 42, 0.9);
        }

        .detail-hero-kpis {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .75rem;
            min-width: min(100%, 24rem);
        }

        .detail-kpi {
            padding: .9rem 1rem;
            border-radius: 1rem;
            background: rgba(15, 23, 42, 0.22);
            border: 1px solid rgba(255, 255, 255, 0.14);
            backdrop-filter: blur(10px);
        }

        .detail-kpi-label {
            display: block;
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: rgba(226, 232, 240, 0.82);
        }

        .detail-kpi-value {
            display: block;
            margin-top: .35rem;
            font-size: 1.45rem;
            font-weight: 700;
        }

        .detail-surface {
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 1.15rem;
            box-shadow: 0 1.5rem 3rem -2.5rem rgba(15, 23, 42, 0.45);
        }

        .detail-info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .detail-info-item {
            padding: 1rem;
            border-radius: 1rem;
            background: var(--tblr-bg-surface-secondary);
            border: 1px solid rgba(15, 23, 42, 0.05);
        }

        .detail-info-label {
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--tblr-secondary);
            margin-bottom: .35rem;
        }

        .detail-side-stack {
            display: grid;
            gap: 1rem;
        }

        .detail-side-card {
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 1.15rem;
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.96), rgba(241, 245, 249, 0.92));
        }

        .document-card {
            height: 100%;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 1rem;
            box-shadow: 0 1rem 2.5rem -2rem rgba(15, 23, 42, 0.6);
        }

        .document-card .card-body {
            display: flex;
            flex-direction: column;
            gap: .9rem;
        }

        .document-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
            justify-content: space-between;
        }

        @media (max-width: 767.98px) {
            .detail-hero-kpis,
            .detail-info-grid {
                grid-template-columns: 1fr;
            }

            .detail-hero-body {
                padding: 1.25rem;
            }
        }
    </style>

    <div class="d-flex flex-column gap-3">
        <div class="card detail-hero">
            <div class="detail-hero-body">
                <div class="detail-hero-profile">
                    <div class="detail-hero-avatar" style="background-image: url('{{ $logo_url }}');"></div>
                    <div>
                        <div class="text-uppercase small fw-bold" style="letter-spacing: .1em; color: rgba(226, 232, 240, 0.78);">Profil Perusahaan</div>
                        <h1 class="mb-1 text-white">{{ $company->name }}</h1>
                        <div style="color: rgba(226, 232, 240, 0.82);">{{ $company->email ?? 'Email belum diatur' }}</div>
                        <div class="mt-2 d-flex flex-wrap gap-2">
                            <span class="badge bg-white text-cyan">{{ $company->registration_number ?? 'Registrasi belum diatur' }}</span>
                            <span class="badge" style="background: rgba(255,255,255,.16); color: #f8fafc;">NPWP {{ $company->tax_id ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <div class="detail-hero-kpis">
                    <div class="detail-kpi">
                        <span class="detail-kpi-label">Dokumen</span>
                        <span class="detail-kpi-value">{{ $document_total }}</span>
                    </div>
                    <div class="detail-kpi">
                        <span class="detail-kpi-label">Kontak Tersedia</span>
                        <span class="detail-kpi-value">{{ $contact_total }}</span>
                    </div>
                    <div class="detail-kpi">
                        <span class="detail-kpi-label">Profil Terisi</span>
                        <span class="detail-kpi-value">{{ $profile_completion }}/7</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-4">
                <div class="detail-side-stack">
                    <div class="card detail-side-card">
                        <div class="card-header border-0 pb-0">
                            <h3 class="card-title mb-0">Kontak Utama</h3>
                        </div>
                        <div class="card-body pt-3">
                            <div class="detail-info-item mb-3">
                                <div class="detail-info-label">Email</div>
                                <div class="fw-semibold text-break">{{ $company->email ?? '-' }}</div>
                            </div>
                            <div class="detail-info-item mb-3">
                                <div class="detail-info-label">Telepon Utama</div>
                                <div class="fw-semibold">{{ $company->phone_1 ?? '-' }}</div>
                            </div>
                            <div class="detail-info-item">
                                <div class="detail-info-label">Telepon Alternatif</div>
                                <div class="fw-semibold">{{ $company->phone_2 ?? '-' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="card detail-side-card">
                        <div class="card-header border-0 pb-0">
                            <h3 class="card-title mb-0">Unggah Logo</h3>
                        </div>
                        <div class="card-body pt-3">
                            <form action="{{ route('company.upload-logo', $company->unique_id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Logo Perusahaan</label>
                                    <input class="form-control" type="file" name="company_logo" accept="image/*" required>
                                </div>
                                <button class="btn btn-primary w-100" type="submit">Unggah Logo Baru</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card detail-surface mb-3">
                    <div class="card-header border-0 pb-0">
                        <h3 class="card-title mb-0">Informasi Perusahaan</h3>
                    </div>
                    <div class="card-body pt-3">
                        <div class="detail-info-grid">
                            <div class="detail-info-item">
                                <div class="detail-info-label">Alamat</div>
                                <div class="fw-semibold">{{ $company->address ?? '-' }}</div>
                            </div>
                            <div class="detail-info-item">
                                <div class="detail-info-label">CEO</div>
                                <div class="fw-semibold">{{ $company->ceo_name ?? '-' }}</div>
                            </div>
                            <div class="detail-info-item">
                                <div class="detail-info-label">PIC</div>
                                <div class="fw-semibold">{{ $company->pic_name ?? '-' }}</div>
                            </div>
                            <div class="detail-info-item">
                                <div class="detail-info-label">Nomor Registrasi</div>
                                <div class="fw-semibold">{{ $company->registration_number ?? '-' }}</div>
                            </div>
                            <div class="detail-info-item">
                                <div class="detail-info-label">NPWP</div>
                                <div class="fw-semibold">{{ $company->tax_id ?? '-' }}</div>
                            </div>
                            <div class="detail-info-item">
                                <div class="detail-info-label">Status Profil</div>
                                <div class="fw-semibold">{{ $profile_completion >= 5 ? 'Siap digunakan' : 'Perlu dilengkapi' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card detail-surface">
                    <div class="card-body">
                        <div class="document-toolbar mb-3">
                            <div>
                                <h3 class="card-title mb-1">Dokumen Perusahaan</h3>
                                <div class="text-secondary">Kelola dokumen legal dan pendukung perusahaan.</div>
                            </div>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">Unggah Dokumen</button>
                        </div>

                        <div class="row g-3">
                            @forelse ($documents as $document)
                                <div class="col-12 col-md-6">
                                    <div class="card document-card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-start justify-content-between gap-3">
                                                <div>
                                                    <div class="text-secondary text-uppercase small fw-bold">{{ $document->document_type }}</div>
                                                    <h4 class="mb-1">{{ $document->document_name }}</h4>
                                                </div>
                                                <span class="badge bg-cyan-lt text-cyan">Dokumen</span>
                                            </div>

                                            <div class="detail-info-item mb-0">
                                                <div class="detail-info-label">Nama File</div>
                                                <a href="{{ Storage::disk('public')->url($document->document_path) }}" target="_blank" rel="noopener noreferrer" class="fw-semibold text-decoration-none text-break">
                                                    {{ basename((string) $document->document_path) }}
                                                </a>
                                            </div>

                                            <div class="d-flex gap-2 flex-wrap mt-auto">
                                                <a href="{{ Storage::disk('public')->url($document->document_path) }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm">Lihat File</a>
                                                <form action="{{ route('company.delete-document', [$company->unique_id, $document->unique_id ?? $document->id]) }}" method="POST" onsubmit="return confirm('Hapus dokumen ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-outline-danger btn-sm" type="submit">Hapus</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="detail-info-item text-center py-5">
                                        <div class="fw-semibold mb-1">Belum ada dokumen perusahaan</div>
                                        <div class="text-secondary">Unggah dokumen untuk melengkapi arsip perusahaan.</div>
                                    </div>
                                </div>
                            @endforelse
                        </div>
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
