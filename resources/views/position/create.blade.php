@extends('layouts.app')

@section('title', 'Tambah Jabatan')
@section('body_title', 'Tambah Jabatan')

@section('buttons_beside_title')
    <a href="{{ route('position.index') }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
    @include('partials.flash')

    <form action="{{ route('position.store') }}" method="POST" class="card" id="position_form">
        @csrf
        <div class="card-header">
            <div>
                <h3 class="card-title mb-1">Formulir Jabatan</h3>
                <p class="text-muted mb-0">Kelompokkan identitas jabatan dan pengaturannya agar lebih mudah dipahami.</p>
            </div>
        </div>
        <div class="card-body">
            <div class="card shadow-none border">
                <div class="card-header py-3">
                    <div>
                        <h4 class="card-title mb-1">Data Jabatan</h4>
                        <div class="text-muted">Nama, kode, kategori, dan unit organisasi yang menaungi jabatan.</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
							<label class="form-label">Nama</label>
							<input class="form-control" name="name" value="{{ old('name') }}" required>
						</div>
                        <div class="col-md-6">
							<label class="form-label">Kode</label>
							<input class="form-control" name="code" value="{{ old('code') }}">
						</div>
                        <div class="col-md-6">
                            <label class="form-label">Kategori</label>
                            <select class="form-select dropdown-list" name="category" required>
                                <option value="" selected disabled>Pilih kategori</option>
                                @foreach ($category_options as $category => $label)
                                    <option value="{{ $category }}" @selected(old('category') == $category)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Unit Organisasi</label>
                            <select class="form-select dropdown-list" name="organizational_unit_id" required>
                                <option value="" selected disabled>Pilih unit</option>
                                @foreach ($organizational_units as $organizational_unit)
                                    <option value="{{ $organizational_unit->id }}" @selected(old('organizational_unit_id') == $organizational_unit->id)>{{ $organizational_unit->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3 shadow-none border">
                <div class="card-header py-3">
                    <div>
                        <h4 class="card-title mb-1">Penanda Khusus</h4>
                        <div class="text-muted">Tandai bila jabatan ini merupakan jabatan kepala.</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_head_position" value="1" id="is_head_position" @checked(old('is_head_position'))>
                        <label class="form-check-label" for="is_head_position">Jabatan kepala</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-end"><button class="btn btn-primary" type="submit">Simpan</button></div>
    </form>
@endsection

@push('scripts')
	<script>
		$(document).ready(function() {
			$('#position_form').validate({
				rules: {
					name: {
						required: true,
						maxlength: 255
					},
					code: {
						maxlength: 50
					},
					category: {
						required: true
					},
					organizational_unit_id: {
						required: true
					}
				},
				messages: {
					name: {
						required: "Nama jabatan wajib diisi.",
						maxlength: "Nama jabatan tidak boleh lebih dari 255 karakter."
					},
					code: {
						maxlength: "Kode jabatan tidak boleh lebih dari 50 karakter."
					},
					category: {
						required: "Kategori jabatan wajib dipilih."
					},
					organizational_unit_id: {
						required: "Unit organisasi wajib dipilih."
					}
				},
				errorElement: 'div',
				errorPlacement: function(error, element) {
					error.addClass('invalid-feedback');
					if (element.prop('type') === 'checkbox') {
						error.insertAfter(element.next('label'));
					} else {
						error.insertAfter(element);
					}
				},
				highlight: function(element, errorClass, validClass) {
					$(element).addClass('is-invalid').removeClass('is-valid');
				},
				unhighlight: function(element, errorClass, validClass) {
					$(element).removeClass('is-invalid').addClass('is-valid');
				}
			});
		});
	</script>
@endpush
