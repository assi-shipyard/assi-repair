@extends('layouts.app')

@section('title', 'Tambah Permission')
@section('body_title', 'Tambah Permission')

@section('buttons_beside_title')
    <a href="{{ route('permission.index') }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
    @include('partials.flash')

    <form action="{{ route('permission.store') }}" method="POST" class="card">
        @csrf
        <div class="card-header">
            <div>
                <h3 class="card-title mb-1">Formulir Permission</h3>
                <p class="text-muted mb-0">Pisahkan nama sistem dan permission agar daftar hak akses lebih rapi.</p>
            </div>
        </div>
        <div class="card-body">
            <div class="card shadow-none border">
                <div class="card-header py-3">
                    <div>
                        <h4 class="card-title mb-1">Informasi Permission</h4>
                        <div class="text-muted">Nama sistem, label permission, dan keterangan singkatnya.</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Nama Sistem</label><input class="form-control" name="name" value="{{ old('name') }}" required></div>
                        <div class="col-md-6"><label class="form-label">Nama Permission</label><input class="form-control" name="permission_name" value="{{ old('permission_name') }}" required></div>
                        <div class="col-12"><label class="form-label">Deskripsi</label><textarea class="form-control" name="permission_description" rows="4">{{ old('permission_description') }}</textarea></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-end"><button class="btn btn-primary" type="submit">Simpan</button></div>
    </form>
@endsection
