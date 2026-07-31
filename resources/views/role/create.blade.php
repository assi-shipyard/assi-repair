@extends('layouts.app')

@section('title', 'Tambah Role')
@section('body_title', 'Tambah Role')

@section('buttons_beside_title')
    <a href="{{ route('role.index') }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
    @include('partials.flash')

    <form action="{{ route('role.store') }}" method="POST" class="card">
        @csrf
        <div class="card-header">
            <div>
                <h3 class="card-title mb-1">Formulir Role</h3>
                <p class="text-muted mb-0">Pisahkan nama sistem dan label role agar lebih mudah dibaca.</p>
            </div>
        </div>
        <div class="card-body">
            <div class="card shadow-none border">
                <div class="card-header py-3">
                    <div>
                        <h4 class="card-title mb-1">Informasi Role</h4>
                        <div class="text-muted">Nama sistem yang dipakai aplikasi dan nama role yang ditampilkan ke pengguna.</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Nama Sistem</label><input class="form-control" name="name" value="{{ old('name') }}" required></div>
                        <div class="col-md-6"><label class="form-label">Nama Role</label><input class="form-control" name="role_name" value="{{ old('role_name') }}" required></div>
                        <div class="col-12"><label class="form-label">Deskripsi</label><textarea class="form-control" name="role_description" rows="4">{{ old('role_description') }}</textarea></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-end"><button class="btn btn-primary" type="submit">Simpan</button></div>
    </form>
@endsection
