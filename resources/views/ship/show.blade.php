@extends('layouts.app')

@section('title', 'Detail Kapal')
@section('body_title', 'Detail Kapal')

@section('buttons_beside_title')
    <a href="{{ route('ship.edit', $ship->unique_id) }}" class="btn btn-outline-primary">Ubah</a>
    <a href="{{ route('ship.index') }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
    @include('partials.flash')

    @php($ship_build_year = $ship->build_year ?? $ship->year_built ?? null)

    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><div class="text-secondary">Nama</div><div>{{ $ship->name }}</div></div>
                <div class="col-md-6"><div class="text-secondary">Perusahaan</div><div>{{ $ship->company?->name ?? '-' }}</div></div>
                <div class="col-md-6"><div class="text-secondary">Jenis</div><div>{{ $ship->type?->name ?? '-' }}</div></div>
                <div class="col-md-6"><div class="text-secondary">Kelas</div><div>{{ $ship->classification?->name ?? '-' }}</div></div>
                <div class="col-md-6"><div class="text-secondary">IMO</div><div>{{ $ship->imo_number ?? '-' }}</div></div>
                <div class="col-md-6"><div class="text-secondary">Call Sign</div><div>{{ $ship->call_sign ?? '-' }}</div></div>
                <div class="col-md-6"><div class="text-secondary">MMSI</div><div>{{ $ship->mmsi_number ?? '-' }}</div></div>
                <div class="col-md-6"><div class="text-secondary">Tahun Pembuatan</div><div>{{ $ship_build_year ?? '-' }}</div></div>
                <div class="col-md-3"><div class="text-secondary">LOA</div><div>{{ $ship->length_overall ?? '-' }}</div></div>
                <div class="col-md-3"><div class="text-secondary">Breadth</div><div>{{ $ship->breadth ?? '-' }}</div></div>
                <div class="col-md-3"><div class="text-secondary">Height</div><div>{{ $ship->height ?? '-' }}</div></div>
                <div class="col-md-3"><div class="text-secondary">Gross Tonnage</div><div>{{ $ship->gross_tonnage ?? '-' }}</div></div>
            </div>
        </div>
    </div>
@endsection
