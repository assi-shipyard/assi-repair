@extends('layouts.app')

@section('title', 'Tambah Unit Organisasi')
@section('body_title', 'Tambah Unit Organisasi')

@section('buttons_beside_title')
    <a href="{{ route('organizational-unit.index') }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
    @include('partials.flash')

    <form action="{{ route('organizational-unit.store') }}" method="POST" class="card">
        @csrf
        <div class="card-header">
            <div>
                <h3 class="card-title mb-1">Formulir Unit Organisasi</h3>
                <p class="text-muted mb-0">Pisahkan identitas unit dan hubungan induknya untuk navigasi yang lebih jelas.</p>
            </div>
        </div>
        <div class="card-body">
            <div class="card shadow-none border">
                <div class="card-header py-3">
                    <div>
                        <h4 class="card-title mb-1">Data Unit</h4>
                        <div class="text-muted">Nama, kode, dan jenis unit organisasi yang akan dibuat.</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Nama</label><input class="form-control" name="name" value="{{ old('name') }}" required></div>
                        <div class="col-md-6"><label class="form-label">Kode</label><input class="form-control" name="code" value="{{ old('code') }}"></div>
                        <div class="col-md-6">
                            <label class="form-label">Jenis</label>
                            <select class="form-select" name="type" required>
                                <option value="">Pilih jenis</option>
                                @foreach ($types as $type => $label)
                                    <option value="{{ $type }}" @selected(old('type') == $type)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Unit Induk</label>
                            <select class="form-select" name="parent_id">
                                <option value="">Tanpa induk</option>
                                @foreach ($organizationalUnits as $organizationalUnit)
                                    <option value="{{ $organizationalUnit->id }}" @selected(old('parent_id') == $organizationalUnit->id)>{{ $organizationalUnit->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-end"><button class="btn btn-primary" type="submit">Simpan</button></div>
    </form>
@endsection
