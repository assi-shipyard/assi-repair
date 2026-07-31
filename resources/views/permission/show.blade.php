@extends('layouts.app')

@section('title', 'Detail Permission')
@section('body_title', 'Detail Permission')

@section('buttons_beside_title')
    <a href="{{ route('permission.assign-roles', $permission->id) }}" class="btn btn-outline-primary">Atur Role</a>
    <a href="{{ route('permission.edit', $permission->id) }}" class="btn btn-outline-secondary">Ubah</a>
    <a href="{{ route('permission.index') }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
    @include('partials.flash')

    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><div class="text-secondary">Nama Sistem</div><div>{{ $permission->name }}</div></div>
                <div class="col-md-6"><div class="text-secondary">Nama Permission</div><div>{{ $permission->permission_name ?? '-' }}</div></div>
                <div class="col-12"><div class="text-secondary">Deskripsi</div><div>{{ $permission->permission_description ?? '-' }}</div></div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header"><h3 class="card-title mb-0">Role</h3></div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                @forelse ($permission->roles as $role)
                    <span class="badge bg-primary-lt">{{ $role->name }}</span>
                @empty
                    <span class="text-secondary">Belum ada role.</span>
                @endforelse
            </div>
        </div>
    </div>
@endsection
