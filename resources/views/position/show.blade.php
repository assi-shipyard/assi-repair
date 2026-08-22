@extends('layouts.app')

@section('title', 'Detail Jabatan')
@section('body_title', 'Detail Jabatan')

@section('buttons_beside_title')
    <a href="{{ route('position.edit', $position->unique_id ?? $position->id) }}" class="btn btn-outline-primary">Ubah</a>
    <a href="{{ route('position.index') }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
    @include('partials.flash')

    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><div class="text-secondary">Nama</div><div>{{ $position->name }}</div></div>
                <div class="col-md-6"><div class="text-secondary">Kategori</div><div>{{ $position->category_label }}</div></div>
                <div class="col-md-6"><div class="text-secondary">Unit</div><div>{{ $position->organizational_unit?->name ?? '-' }}</div></div>
                <div class="col-md-6"><div class="text-secondary">Kode</div><div>{{ $position->code ?? '-' }}</div></div>
            </div>
        </div>
    </div>
@endsection
