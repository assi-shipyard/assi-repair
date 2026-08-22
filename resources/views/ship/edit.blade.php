@extends('layouts.app')

@section('title', 'Ubah Kapal')
@section('body_title', 'Ubah Kapal')

@section('buttons_beside_title')
    <a href="{{ route('ship.show', $ship->unique_id) }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
    @include('partials.flash')
    @include('partials.form-shell-styles')

    @php
        $filled_ship_fields = collect([
            old('name', $ship->name),
            old('company_id', $ship->company_id),
            old('ship_type_id', $ship->ship_type_id),
            old('ship_class_id', $ship->ship_class_id),
            old('imo_number', $ship->imo_number),
            old('mmsi_number', $ship->mmsi_number),
            old('call_sign', $ship->call_sign),
            old('flag', $ship->flag),
            old('length_overall', $ship->length_overall),
            old('breadth', $ship->breadth),
            old('height', $ship->height),
            old('gross_tonnage', $ship->gross_tonnage),
            old('empty_draft', $ship->empty_draft),
            old('loaded_draft', $ship->loaded_draft),
            old('net_tonnage', $ship->net_tonnage),
            old('build_year', $ship->build_year ?? null),
            old('engine_brand', $ship->engine_brand),
            old('engine_model', $ship->engine_model),
            old('engine_power', $ship->engine_power),
            old('engine_type', $ship->engine_type),
            old('engine_rpm', $ship->engine_rpm),
            old('engine_fuel_type', $ship->engine_fuel_type),
            old('engine_fuel_capacity', $ship->engine_fuel_capacity),
            old('engine_fuel_consumption', $ship->engine_fuel_consumption),
        ])->filter()->count();
    @endphp

    <form action="{{ route('ship.update', $ship->unique_id) }}" method="POST" class="form-shell" id="ship-edit-form">
        @csrf
        @method('PUT')
        <div class="form-layout">
            <div class="form-sidebar">
                <div class="form-info-card" style="--form-info-bg: var(--tblr-blue-lt); --form-info-avatar-bg: var(--tblr-blue);">
                    <div class="form-info-body">
                        <div class="form-info-header"><span class="form-info-avatar"><i class="ti ti-ship"></i></span><div><h2 class="form-info-title">Ubah kapal</h2><p class="form-info-subtitle">Rapikan data teknis kapal agar evaluasi docking dan dokumen proyek tetap sinkron.</p></div></div>
                        <div class="form-step-list">
                            <div class="form-step-item"><span class="form-step-badge">1</span><span>Periksa identitas dasar kapal terhadap master data.</span></div>
                            <div class="form-step-item"><span class="form-step-badge">2</span><span>Sesuaikan dimensi bila ada pembaruan dari data teknis.</span></div>
                            <div class="form-step-item"><span class="form-step-badge">3</span><span>Lengkapi mesin dan bahan bakar jika ada perubahan operasional.</span></div>
                        </div>
                        <div class="form-kpi-grid">
                            <div class="form-kpi"><span class="form-kpi-label">Field Terisi</span><span class="form-kpi-value">{{ $filled_ship_fields }}</span></div>
                            <div class="form-kpi"><span class="form-kpi-label">Registrasi</span><span class="form-kpi-value">4</span></div>
                            <div class="form-kpi"><span class="form-kpi-label">Dimensi Inti</span><span class="form-kpi-value">5</span></div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header"><h3 class="card-title mb-0">Checklist teknis</h3></div>
                    <div class="card-body">
                        <div class="form-note-list">
                            <div class="form-note-item"><div class="form-note-label">Identitas</div><div>Nama kapal, perusahaan, jenis, dan kelas harus konsisten dengan data master.</div></div>
                            <div class="form-note-item"><div class="form-note-label">Dimensi</div><div>Perubahan kecil dapat memengaruhi evaluasi docking dan kapasitas.</div></div>
                            <div class="form-note-item"><div class="form-note-label">Mesin</div><div>Simpan model, daya, dan konsumsi terbaru untuk analisis teknis.</div></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-main-card card shadow-sm">
                <div class="form-main-card-header"><div><h3 class="form-main-card-title">Form Kapal</h3><div class="form-main-card-copy">Perbarui identitas kapal dan informasi teknis yang muncul pada proyek dan docking.</div></div></div>
                <div class="form-main-card-body">
                    <div class="form-summary-alert mb-4"><div class="form-summary-top"><div><div class="form-summary-title">Ringkasan profil saat ini</div><div class="form-summary-copy">Fokus utama ada pada identitas kapal, dimensi pokok, dan mesin utama.</div></div><span class="badge bg-blue-lt text-blue">{{ $ship->name }}</span></div></div>

                    <div class="form-surface mb-4">
                        <div class="form-main-card-header"><div><h3 class="form-main-card-title">Identitas Kapal</h3><div class="form-main-card-copy">Perbarui identitas dasar dan relasi kapal terhadap master data lain.</div></div></div>
                        <div class="form-main-card-body">
                        <div class="row g-3">
                            <div class="col-md-6"><div class="form-group"><label class="form-label required">Nama Kapal</label><input class="form-control" name="name" value="{{ old('name', $ship->name) }}" placeholder="Nama kapal" required></div></div>
                            <div class="col-md-6"><div class="form-group"><label class="form-label required">Perusahaan</label><select class="form-select dropdown-list" name="company_id" required><option value="" disabled>Pilih perusahaan</option>@foreach ($companies as $company)<option value="{{ $company->id }}" @selected(old('company_id', $ship->company_id) == $company->id)>{{ $company->name }}</option>@endforeach</select></div></div>
                            <div class="col-md-6"><div class="form-group"><label class="form-label required">Jenis Kapal</label><select class="form-select dropdown-list" name="ship_type_id" required><option value="" disabled>Pilih jenis</option>@foreach ($ship_types as $ship_type)<option value="{{ $ship_type->id }}" @selected(old('ship_type_id', $ship->ship_type_id) == $ship_type->id)>{{ $ship_type->name }}</option>@endforeach</select></div></div>
                            <div class="col-md-6"><div class="form-group"><label class="form-label required">Kelas Kapal</label><select class="form-select dropdown-list" name="ship_class_id" required><option value="" disabled>Pilih kelas</option>@foreach ($ship_classes as $ship_class)<option value="{{ $ship_class->id }}" @selected(old('ship_class_id', $ship->ship_class_id) == $ship_class->id)>{{ $ship_class->name }}{{ $ship_class->abbreviation ? ' ('.$ship_class->abbreviation.')' : '' }}</option>@endforeach</select></div></div>
                            <div class="col-md-6"><div class="form-group"><label class="form-label">IMO</label><input class="form-control" name="imo_number" value="{{ old('imo_number', $ship->imo_number) }}" placeholder="IMO number"></div></div>
                            <div class="col-md-6"><div class="form-group"><label class="form-label">MMSI</label><input class="form-control" name="mmsi_number" value="{{ old('mmsi_number', $ship->mmsi_number) }}" placeholder="MMSI number"></div></div>
                            <div class="col-md-6"><div class="form-group"><label class="form-label">Call Sign</label><input class="form-control" name="call_sign" value="{{ old('call_sign', $ship->call_sign) }}" placeholder="Call sign"></div></div>
                            <div class="col-md-6"><div class="form-group"><label class="form-label">Bendera</label><input class="form-control" name="flag" value="{{ old('flag', $ship->flag) }}" placeholder="Negara bendera"></div></div>
                        </div>
                        </div>
                    </div>

                    <div class="form-surface mb-4">
                        <div class="form-main-card-header"><div><h3 class="form-main-card-title">Dimensi dan Tonase</h3><div class="form-main-card-copy">Pastikan ukuran utama siap dipakai oleh fitur evaluasi kapasitas docking.</div></div></div>
                        <div class="form-main-card-body">
                        <div class="row g-3">
                            <div class="col-md-3"><div class="form-group"><label class="form-label required">LOA</label><input class="form-control" name="length_overall" value="{{ old('length_overall', $ship->length_overall) }}" placeholder="Meter" required></div></div>
                            <div class="col-md-3"><div class="form-group"><label class="form-label required">Breadth</label><input class="form-control" name="breadth" value="{{ old('breadth', $ship->breadth) }}" placeholder="Meter" required></div></div>
                            <div class="col-md-3"><div class="form-group"><label class="form-label required">Height</label><input class="form-control" name="height" value="{{ old('height', $ship->height) }}" placeholder="Meter" required></div></div>
                            <div class="col-md-3"><div class="form-group"><label class="form-label required">Gross Tonnage</label><input class="form-control" name="gross_tonnage" value="{{ old('gross_tonnage', $ship->gross_tonnage) }}" placeholder="GT" required></div></div>
                            <div class="col-md-3"><div class="form-group"><label class="form-label">Draft Kosong</label><input class="form-control" name="empty_draft" value="{{ old('empty_draft', $ship->empty_draft) }}" placeholder="Meter"></div></div>
                            <div class="col-md-3"><div class="form-group"><label class="form-label">Draft Muat</label><input class="form-control" name="loaded_draft" value="{{ old('loaded_draft', $ship->loaded_draft) }}" placeholder="Meter"></div></div>
                            <div class="col-md-3"><div class="form-group"><label class="form-label">Net Tonnage</label><input class="form-control" name="net_tonnage" value="{{ old('net_tonnage', $ship->net_tonnage) }}" placeholder="NT"></div></div>
                            <div class="col-md-3"><div class="form-group"><label class="form-label">Tahun Pembuatan</label><input class="form-control" type="date" name="build_year" value="{{ old('build_year', $ship->build_year ?? null) }}"></div></div>
                        </div>
                        </div>
                    </div>

                    <div class="form-surface">
                        <div class="form-main-card-header"><div><h3 class="form-main-card-title">Spesifikasi Mesin</h3><div class="form-main-card-copy">Perbarui detail mesin dan bahan bakar sebagai profil teknis kapal.</div></div></div>
                        <div class="form-main-card-body">
                        <div class="row g-3">
                            <div class="col-md-6"><div class="form-group"><label class="form-label">Merek Mesin</label><input class="form-control" name="engine_brand" value="{{ old('engine_brand', $ship->engine_brand) }}" placeholder="Merek mesin"></div></div>
                            <div class="col-md-6"><div class="form-group"><label class="form-label">Model Mesin</label><input class="form-control" name="engine_model" value="{{ old('engine_model', $ship->engine_model) }}" placeholder="Model mesin"></div></div>
                            <div class="col-md-4"><div class="form-group"><label class="form-label">Daya Mesin</label><input class="form-control" name="engine_power" value="{{ old('engine_power', $ship->engine_power) }}" placeholder="HP / kW"></div></div>
                            <div class="col-md-4"><div class="form-group"><label class="form-label">Tipe Mesin</label><input class="form-control" name="engine_type" value="{{ old('engine_type', $ship->engine_type) }}" placeholder="Tipe mesin"></div></div>
                            <div class="col-md-4"><div class="form-group"><label class="form-label">RPM Mesin</label><input class="form-control" name="engine_rpm" value="{{ old('engine_rpm', $ship->engine_rpm) }}" placeholder="RPM"></div></div>
                            <div class="col-md-6"><div class="form-group"><label class="form-label">Tipe BBM</label><input class="form-control" name="engine_fuel_type" value="{{ old('engine_fuel_type', $ship->engine_fuel_type) }}" placeholder="Jenis bahan bakar"></div></div>
                            <div class="col-md-3"><div class="form-group"><label class="form-label">Kapasitas BBM</label><input class="form-control" name="engine_fuel_capacity" value="{{ old('engine_fuel_capacity', $ship->engine_fuel_capacity) }}" placeholder="Liter"></div></div>
                            <div class="col-md-3"><div class="form-group"><label class="form-label">Konsumsi BBM</label><input class="form-control" name="engine_fuel_consumption" value="{{ old('engine_fuel_consumption', $ship->engine_fuel_consumption) }}" placeholder="Liter/jam"></div></div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions-card">
            <div class="form-actions-row">
                <div class="form-actions-copy">Simpan setelah identitas kapal, dimensi utama, dan spesifikasi mesin sudah diperbarui.</div>
                <div class="form-actions-buttons">
                    <a href="{{ route('ship.show', $ship->unique_id) }}" class="btn btn-outline-secondary">Batal</a>
                    <button class="btn btn-primary" type="submit">Perbarui Kapal</button>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
	<script>
		$(document).ready(function() {
			$('#ship-edit-form').validate({
				ignore: [],
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
					name: {
						required: 'Nama kapal wajib diisi.',
						minlength: 'Nama kapal minimal 3 karakter.',
						maxlength: 'Nama kapal maksimal 255 karakter.'
					},
					company_id: {
						required: 'Perusahaan pemilik wajib dipilih.'
					},
					ship_type_id: {
						required: 'Jenis kapal wajib dipilih.'
					},
					ship_class_id: {
						required: 'Kelas kapal wajib dipilih.'
					}
				},
				errorElement: 'span',
				errorPlacement: function (error, element) {
					error.addClass('invalid-feedback');
					if (element.hasClass('select2-hidden-accessible')) {
						error.insertAfter(element.next('.select2'));
						return;
					}

					element.closest('.form-group').append(error);
				},
				highlight: function (element) {
					const $element = $(element);
					if ($element.hasClass('select2-hidden-accessible')) {
						$element.next('.select2').find('.select2-selection').addClass('is-invalid');
						return;
					}

					$element.addClass('is-invalid');
				},
				unhighlight: function (element) {
					const $element = $(element);
					if ($element.hasClass('select2-hidden-accessible')) {
						$element.next('.select2').find('.select2-selection').removeClass('is-invalid');
						return;
					}

					$element.removeClass('is-invalid');
				}
			});
		});
	</script>
@endpush
