@extends('layouts.app')

@section('title', 'Detail Perusahaan')
@section('body_title', 'Detail Perusahaan')

@section('buttons_beside_title')
    <a href="{{ route('company.edit', $company->unique_id) }}" class="btn btn-outline-primary">Ubah</a>
    <a href="{{ route('company.index') }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
    @include('partials.flash')

    <div class="row row-cards">
        <div class="col-lg-4">
			<div class="row mb-3">
				<div class="card">
					<div class="card-body text-center">
						<div class="avatar avatar-xl mb-3" style="background-image: url('{{ $company->logo_path ? Storage::disk('public')->url('company_logos/' . $company->logo_path) : asset('assets/img/default_profile.jpg') }}');"></div>
						<h3 class="mb-1">{{ $company->name }}</h3>
						<div class="text-secondary">{{ $company->email ?? '-' }}</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="card mb-3">
					<div class="card-header">
						<h3 class="card-title mb-0">Unggah Logo</h3>
					</div>
					<div class="card-body">
						<form action="{{ route('company.upload-logo', $company->unique_id) }}" method="POST" enctype="multipart/form-data">
							@csrf
							<div class="row g-3 align-items-end">
								<div class="col-md-8">
									<label class="form-label">Logo Perusahaan</label>
									<input class="form-control" type="file" name="company_logo" accept="image/*" required>
								</div>
								<div class="col-md-4">
									<button class="btn btn-primary w-100" type="submit">Unggah Logo</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
        </div>

        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header">
					<h3 class="card-title mb-0">Informasi Perusahaan</h3>
				</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="fw-bold">Alamat</div>
                            <div>{{ $company->address }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="fw-bold">Telepon</div>
                            <div>{{ $company->phone_1 ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="fw-bold">Telepon Alternatif</div>
                            <div>{{ $company->phone_2 ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="fw-bold">CEO</div>
                            <div>{{ $company->ceo_name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="fw-bold">PIC</div>
                            <div>{{ $company->pic_name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="fw-bold">Registrasi</div>
                            <div>{{ $company->registration_number ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="fw-bold">NPWP</div>
                            <div>{{ $company->tax_id ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
					<h3 class="card-title mb-0">Dokumen Perusahaan</h3>
				</div>
                <div class="card-body">
                    <div class="mb-4">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            Unggah Dokumen
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table card-table table-vcenter text-nowrap datatable" id="company-document-table">
                            <thead>
                                <tr>
                                    <th class="text-center">Nama</th>
                                    <th class="text-center">Tipe</th>
                                    <th class="text-center">File</th>
                                    <th class="text-center w-1">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($documents as $document)
                                    <tr>
                                        <td class="text-center">{{ $document->document_name }}</td>
                                        <td class="text-center">{{ $document->document_type }}</td>
                                        <td class="text-center">
                                            <a href="{{ Storage::disk('public')->url($document->document_path) }}" target="_blank" rel="noopener noreferrer" class="text-reset">
                                                {{ basename((string) $document->document_path) }}
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <form action="{{ route('company.delete-document', [$company->unique_id, $document->id]) }}" method="POST" onsubmit="return confirm('Hapus dokumen ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" type="submit">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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
            $('#company-document-table').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": true,
                "responsive": true,
                "columnDefs": [
                    { "orderable": false, "targets": 3 }
                ],
                "language": {
                    "emptyTable": "Tidak ada data dokumen perusahaan yang tersedia.",
                    "info": "Menampilkan _START_ hingga _END_ dari _TOTAL_ data",
                    "infoEmpty": "Menampilkan 0 hingga 0 dari 0 data",
                    "infoFiltered": "(disaring dari _MAX_ total data)",
                    "lengthMenu": "Tampilkan _MENU_ data",
                    "search": "Cari:",
                    "zeroRecords": "Tidak ada data yang cocok",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Selanjutnya",
                        "previous": "Sebelumnya"
                    }
                },
            });

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