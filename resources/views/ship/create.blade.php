@extends('layouts.app')

@section('title', 'Tambah Kapal')
@section('body_title', 'Tambah Kapal')

@section('buttons_beside_title')
    <a href="{{ route('ship.index') }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
    @include('partials.flash')

    <form action="{{ route('ship.store') }}" method="POST" class="card" id="ship-form">
        @csrf
        <div class="card-header">
            <div>
                <h3 class="card-title mb-1">Formulir Kapal</h3>
                <p class="text-muted mb-0">Bagi data identitas kapal, dimensi, dan spesifikasi mesin agar mudah dipindai.</p>
            </div>
        </div>
        <div class="card-body">
            <div class="card mb-3 shadow-none border">
                <div class="card-header py-3">
                    <div>
                        <h4 class="card-title mb-1">Identitas Kapal</h4>
                        <div class="text-muted">Data utama kapal beserta hubungan ke perusahaan dan klasifikasinya.</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
							<div class="form-group">
								<label class="form-label required">Nama Kapal</label>
								<input class="form-control" name="name" id="name" value="{{ old('name') }}" required>
							</div>

						</div>
                        <div class="col-md-6">
							<div class="form-group">
								<label class="form-label required">Perusahaan</label>
								<select class="form-select dropdown-list" name="company_id" id="company_id" required>
									<option value="" selected disabled>Pilih perusahaan</option>
									@foreach ($companies as $company)
										<option value="{{ $company->id }}" @selected(old('company_id') == $company->id)>{{ $company->name }}</option>
									@endforeach
								</select>
							</div>
                        </div>
                        <div class="col-md-6">
							<div class="form-group">
								<label class="form-label required">Jenis Kapal</label>
								<select class="form-select dropdown-list" name="ship_type_id" id="ship_type_id" required>
									<option value="" selected disabled>Pilih jenis</option>
									@foreach ($ship_types as $ship_type)
										<option value="{{ $ship_type->id }}" @selected(old('ship_type_id') == $ship_type->id)>{{ $ship_type->name }}</option>
									@endforeach
								</select>
							</div>
                        </div>
                        <div class="col-md-6">
							<div class="form-group">
								<label class="form-label required">Kelas Kapal</label>
								<select class="form-select dropdown-list" name="ship_class_id" id="ship_class_id" required>
									<option value="" selected disabled>Pilih kelas</option>
									@foreach ($ship_classes as $ship_class)
										<option value="{{ $ship_class->id }}" @selected(old('ship_class_id') == $ship_class->id)>{{ $ship_class->name }} ({{ $ship_class->abbreviation }})</option>
									@endforeach
								</select>
							</div>
                        </div>
                        <div class="col-md-6">
							<label class="form-label">IMO</label>
							<input class="form-control" name="imo_number" id="imo_number" value="{{ old('imo_number') }}">
						</div>
                        <div class="col-md-6">
							<label class="form-label">MMSI</label>
							<input class="form-control" name="mmsi_number" id="mmsi_number" value="{{ old('mmsi_number') }}">
						</div>
                        <div class="col-md-6">
							<label class="form-label">Call Sign</label>
							<input class="form-control" name="call_sign" id="call_sign" value="{{ old('call_sign') }}">
						</div>
                        <div class="col-md-6">
							<label class="form-label">Bendera</label>
							<input class="form-control" name="flag" id="flag" value="{{ old('flag') }}">
						</div>
                    </div>
                </div>
            </div>

            <div class="card mb-3 shadow-none border">
                <div class="card-header py-3">
                    <div>
                        <h4 class="card-title mb-1">Dimensi dan Tonase</h4>
                        <div class="text-muted">Ukuran utama kapal untuk kebutuhan teknis dan dokumen proyek.</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
							<label class="form-label">LOA</label>
							<input class="form-control" name="length_overall" id="length_overall" value="{{ old('length_overall') }}" required>
						</div>
                        <div class="col-md-3">
							<label class="form-label">Breadth</label>
							<input class="form-control" name="breadth" id="breadth" value="{{ old('breadth') }}" required>
						</div>
                        <div class="col-md-3">
							<label class="form-label">Height</label>
							<input class="form-control" name="height" id="height" value="{{ old('height') }}" required>
						</div>
                        <div class="col-md-3">
							<label class="form-label">Gross Tonnage</label>
							<input class="form-control" name="gross_tonnage" id="gross_tonnage" value="{{ old('gross_tonnage') }}" required>
						</div>
                        <div class="col-md-3">
							<label class="form-label">Draft Kosong</label>
							<input class="form-control" name="empty_draft" id="empty_draft" value="{{ old('empty_draft') }}">
						</div>
                        <div class="col-md-3">
							<label class="form-label">Draft Muat</label>
							<input class="form-control" name="loaded_draft" id="loaded_draft" value="{{ old('loaded_draft') }}">
						</div>
                        <div class="col-md-3">
							<label class="form-label">Net Tonnage</label>
							<input class="form-control" name="net_tonnage" id="net_tonnage" value="{{ old('net_tonnage') }}">
						</div>
                        <div class="col-md-3">
							<label class="form-label">Tahun Pembuatan</label>
							<input class="form-control" type="date" name="build_year" id="build_year" value="{{ old('build_year') }}">
						</div>
                    </div>
                </div>
            </div>

            <div class="card shadow-none border">
                <div class="card-header py-3">
                    <div>
                        <h4 class="card-title mb-1">Spesifikasi Mesin</h4>
                        <div class="text-muted">Isi data mesin dan kebutuhan bahan bakar kapal.</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
							<label class="form-label">Merek Mesin</label>
							<input class="form-control" name="engine_brand" id="engine_brand" value="{{ old('engine_brand') }}">
						</div>
                        <div class="col-md-6">
							<label class="form-label">Model Mesin</label>
							<input class="form-control" name="engine_model" id="engine_model" value="{{ old('engine_model') }}">
						</div>
                        <div class="col-md-4">
							<label class="form-label">Daya Mesin</label>
							<input class="form-control" name="engine_power" id="engine_power" value="{{ old('engine_power') }}">
						</div>
                        <div class="col-md-4">
							<label class="form-label">Tipe Mesin</label>
							<input class="form-control" name="engine_type" id="engine_type" value="{{ old('engine_type') }}">
						</div>
                        <div class="col-md-4">
							<label class="form-label">RPM Mesin</label>
							<input class="form-control" name="engine_rpm" id="engine_rpm" value="{{ old('engine_rpm') }}">
						</div>
                        <div class="col-md-6">
							<label class="form-label">Tipe BBM</label>
							<input class="form-control" name="engine_fuel_type" id="engine_fuel_type" value="{{ old('engine_fuel_type') }}">
						</div>
                        <div class="col-md-3">
							<label class="form-label">Kapasitas BBM</label>
							<input class="form-control" name="engine_fuel_capacity" id="engine_fuel_capacity" value="{{ old('engine_fuel_capacity') }}">
						</div>
                        <div class="col-md-3">
							<label class="form-label">Konsumsi BBM</label>
							<input class="form-control" name="engine_fuel_consumption" id="engine_fuel_consumption" value="{{ old('engine_fuel_consumption') }}">
						</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
			<button class="btn btn-primary" type="submit">Simpan</button>
		</div>
    </form>
@endsection


@push('scripts')
	<script>
		$(document).ready(function() {
			$('#ship-form').validate({
				rules: {
					name: {
						required: true,
						minlength: 3,
						maxlength: 255
					},
					company_id: {
						required: true,
					},
					ship_type_id: {
						required: true,
					},
					ship_class_id: {
						required: true,
					}
				},
				messages: {

				},
				errorElement: 'span',
                errorPlacement: function (error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('.form-group').append(error);
                },
                highlight: function (element) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function (element) {
                    $(element).removeClass('is-invalid');
                },
			});
		});
	</script>
@endpush
