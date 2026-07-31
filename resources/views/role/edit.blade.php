@extends('layouts.app')

@section('title', 'Ubah Role')
@section('body_title', 'Ubah Role')

@section('buttons_beside_title')
    <a href="{{ route('role.show', $role->id) }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
    @include('partials.flash')

    <form action="{{ route('role.update', $role->id) }}" method="POST" class="card">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Nama Sistem</label><input class="form-control" name="name" value="{{ old('name', $role->name) }}" required></div>
                <div class="col-md-6"><label class="form-label">Nama Role</label><input class="form-control" name="role_name" value="{{ old('role_name', $role->role_name ?? '') }}" required></div>
                <div class="col-12"><label class="form-label">Deskripsi</label><textarea class="form-control" name="role_description" rows="4">{{ old('role_description', $role->role_description ?? '') }}</textarea></div>
            </div>
        </div>
        <div class="card-footer text-end"><button class="btn btn-primary" type="submit">Perbarui</button></div>
    </form>
@endsection
