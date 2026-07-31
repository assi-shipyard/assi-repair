@extends('layouts.app')

@section('title', 'Ubah Permission')
@section('body_title', 'Ubah Permission')

@section('buttons_beside_title')
    <a href="{{ route('permission.show', $permission->id) }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
    @include('partials.flash')

    <form action="{{ route('permission.update', $permission->id) }}" method="POST" class="card">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Nama Sistem</label><input class="form-control" name="name" value="{{ old('name', $permission->name) }}" required></div>
                <div class="col-md-6"><label class="form-label">Nama Permission</label><input class="form-control" name="permission_name" value="{{ old('permission_name', $permission->permission_name ?? '') }}" required></div>
                <div class="col-12"><label class="form-label">Deskripsi</label><textarea class="form-control" name="permission_description" rows="4">{{ old('permission_description', $permission->permission_description ?? '') }}</textarea></div>
            </div>
        </div>
        <div class="card-footer text-end"><button class="btn btn-primary" type="submit">Perbarui</button></div>
    </form>
@endsection
