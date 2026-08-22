@extends('layouts.app')

@section('title', 'Detail Unit Organisasi')
@section('body_title', 'Detail Unit Organisasi')

@section('buttons_beside_title')
    <a href="{{ route('organizational-unit.edit', $organizational_unit->unique_id ?? $organizational_unit->id) }}" class="btn btn-outline-primary">Ubah</a>
    <a href="{{ route('organizational-unit.index') }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
    @include('partials.flash')

    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><div class="text-secondary">Nama</div><div>{{ $organizational_unit->name }}</div></div>
                <div class="col-md-6"><div class="text-secondary">Kode</div><div>{{ $organizational_unit->code ?? '-' }}</div></div>
                <div class="col-md-6"><div class="text-secondary">Jenis</div><div>{{ $organizational_unit->type_label }}</div></div>
                <div class="col-md-6"><div class="text-secondary">Induk</div><div>{{ $organizational_unit->parent?->name ?? '-' }}</div></div>
            </div>
        </div>
    </div>
@endsection
