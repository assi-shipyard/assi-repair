@extends('layouts.app')

@section('title', 'Ubah Kapal')
@section('body_title', 'Ubah Kapal')

@section('buttons_beside_title')
    <a href="{{ route('ship.show', $ship->unique_id) }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
    @include('partials.flash')

    <form action="{{ route('ship.update', $ship->unique_id) }}" method="POST" class="card">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Nama Kapal</label><input class="form-control" name="name" value="{{ old('name', $ship->name) }}" required></div>
                <div class="col-md-6"><label class="form-label">Perusahaan</label><select class="form-select" name="company_id" required>@foreach ($companies as $company)<option value="{{ $company->id }}" @selected(old('company_id', $ship->company_id) == $company->id)>{{ $company->name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Jenis Kapal</label><select class="form-select" name="ship_type_id" required>@foreach ($ship_types as $ship_type)<option value="{{ $ship_type->id }}" @selected(old('ship_type_id', $ship->ship_type_id) == $ship_type->id)>{{ $ship_type->name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Kelas Kapal</label><select class="form-select" name="ship_class_id" required>@foreach ($ship_classes as $ship_class)<option value="{{ $ship_class->id }}" @selected(old('ship_class_id', $ship->ship_class_id) == $ship_class->id)>{{ $ship_class->name }}</option>@endforeach</select></div>
                <div class="col-md-3"><label class="form-label">LOA</label><input class="form-control" name="length_overall" value="{{ old('length_overall', $ship->length_overall) }}" required></div>
                <div class="col-md-3"><label class="form-label">Breadth</label><input class="form-control" name="breadth" value="{{ old('breadth', $ship->breadth) }}" required></div>
                <div class="col-md-3"><label class="form-label">Height</label><input class="form-control" name="height" value="{{ old('height', $ship->height) }}" required></div>
                <div class="col-md-3"><label class="form-label">Gross Tonnage</label><input class="form-control" name="gross_tonnage" value="{{ old('gross_tonnage', $ship->gross_tonnage) }}" required></div>
                <div class="col-md-3"><label class="form-label">Draft Kosong</label><input class="form-control" name="empty_draft" value="{{ old('empty_draft', $ship->empty_draft) }}"></div>
                <div class="col-md-3"><label class="form-label">Draft Muat</label><input class="form-control" name="loaded_draft" value="{{ old('loaded_draft', $ship->loaded_draft) }}"></div>
                <div class="col-md-3"><label class="form-label">Net Tonnage</label><input class="form-control" name="net_tonnage" value="{{ old('net_tonnage', $ship->net_tonnage) }}"></div>
                <div class="col-md-3"><label class="form-label">Tahun Pembuatan</label><input class="form-control" type="date" name="build_year" value="{{ old('build_year', $ship->build_year ?? null) }}"></div>
                <div class="col-md-6"><label class="form-label">IMO</label><input class="form-control" name="imo_number" value="{{ old('imo_number', $ship->imo_number) }}"></div>
                <div class="col-md-6"><label class="form-label">MMSI</label><input class="form-control" name="mmsi_number" value="{{ old('mmsi_number', $ship->mmsi_number) }}"></div>
                <div class="col-md-6"><label class="form-label">Call Sign</label><input class="form-control" name="call_sign" value="{{ old('call_sign', $ship->call_sign) }}"></div>
                <div class="col-md-6"><label class="form-label">Bendera</label><input class="form-control" name="flag" value="{{ old('flag', $ship->flag) }}"></div>
                <div class="col-md-6"><label class="form-label">Merek Mesin</label><input class="form-control" name="engine_brand" value="{{ old('engine_brand', $ship->engine_brand) }}"></div>
                <div class="col-md-6"><label class="form-label">Model Mesin</label><input class="form-control" name="engine_model" value="{{ old('engine_model', $ship->engine_model) }}"></div>
                <div class="col-md-4"><label class="form-label">Daya Mesin</label><input class="form-control" name="engine_power" value="{{ old('engine_power', $ship->engine_power) }}"></div>
                <div class="col-md-4"><label class="form-label">Tipe Mesin</label><input class="form-control" name="engine_type" value="{{ old('engine_type', $ship->engine_type) }}"></div>
                <div class="col-md-4"><label class="form-label">RPM Mesin</label><input class="form-control" name="engine_rpm" value="{{ old('engine_rpm', $ship->engine_rpm) }}"></div>
                <div class="col-md-6"><label class="form-label">Tipe BBM</label><input class="form-control" name="engine_fuel_type" value="{{ old('engine_fuel_type', $ship->engine_fuel_type) }}"></div>
                <div class="col-md-3"><label class="form-label">Kapasitas BBM</label><input class="form-control" name="engine_fuel_capacity" value="{{ old('engine_fuel_capacity', $ship->engine_fuel_capacity) }}"></div>
                <div class="col-md-3"><label class="form-label">Konsumsi BBM</label><input class="form-control" name="engine_fuel_consumption" value="{{ old('engine_fuel_consumption', $ship->engine_fuel_consumption) }}"></div>
            </div>
        </div>
        <div class="card-footer text-end"><button class="btn btn-primary" type="submit">Perbarui</button></div>
    </form>
@endsection
