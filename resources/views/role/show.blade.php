@extends('layouts.app')

@section('title', 'Detail Role')
@section('body_title', 'Detail Role')

@section('buttons_beside_title')
    <a href="{{ route('role.assign-permissions', $role->id) }}" class="btn btn-outline-primary">Atur Permission</a>
    <a href="{{ route('role.edit', $role->id) }}" class="btn btn-outline-secondary">Ubah</a>
    <a href="{{ route('role.index') }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
    @include('partials.flash')

    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><div class="text-secondary">Nama Sistem</div><div>{{ $role->name }}</div></div>
                <div class="col-md-6"><div class="text-secondary">Nama Role</div><div>{{ $role->role_name ?? '-' }}</div></div>
                <div class="col-12"><div class="text-secondary">Deskripsi</div><div>{{ $role->role_description ?? '-' }}</div></div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header"><h3 class="card-title mb-0">Permission</h3></div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                @forelse ($role->permissions as $permission)
                    <span class="badge bg-primary-lt">{{ $permission->name }}</span>
                @empty
                    <span class="text-secondary">Belum ada permission.</span>
                @endforelse
            </div>
        </div>
    </div>
@endsection
