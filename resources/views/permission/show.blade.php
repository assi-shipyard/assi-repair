@extends('layouts.app')

@section('title', 'Detail Permission')
@section('body_title', 'Detail Permission')

@section('buttons_beside_title')
    <a href="{{ route('permission.index') }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i>Kembali</a>
    <a href="{{ route('permission.edit', $permission->id) }}" class="btn btn-outline-secondary"><i class="ti ti-pencil me-1"></i>Ubah</a>
    <a href="{{ route('permission.assign-roles', $permission->id) }}" class="btn btn-primary"><i class="ti ti-shield me-1"></i>Atur Role</a>
@endsection

@section('content')
    @include('partials.flash')

    <div class="card permission-detail-summary mb-4"><div class="card-body p-4 d-flex flex-wrap align-items-center gap-3"><span class="avatar avatar-lg bg-azure-lt text-azure"><i class="ti ti-key fs-2"></i></span><div class="me-auto"><div class="text-secondary small">Permission</div><h2 class="mb-1">{{ $permission->permission_name ?? $permission->name }}</h2><div class="text-secondary"><code>{{ $permission->name }}</code></div></div><div><div class="text-secondary small">Role Terhubung</div><div class="h2 mb-0">{{ $permission->roles->count() }}</div></div></div></div>
    <div class="row row-cards"><div class="col-lg-5"><div class="card h-100"><div class="card-header"><h3 class="card-title">Deskripsi</h3></div><div class="card-body text-secondary">{{ $permission->permission_description ?: 'Deskripsi permission belum diisi.' }}</div></div></div><div class="col-lg-7"><div class="card h-100"><div class="card-header"><h3 class="card-title">Role yang Memiliki Akses</h3></div><div class="card-body"><div class="d-flex flex-wrap gap-2">
                @forelse ($permission->roles as $role)
                    <span class="badge bg-azure-lt text-azure">{{ $role->role_name ?? $role->name }}</span>
                @empty
                    <span class="text-secondary">Belum ada role yang menerima permission ini.</span>
                @endforelse
            </div></div></div></div></div>
@endsection

@push('styles')<style>.permission-detail-summary { border-top: 3px solid var(--tblr-azure); }</style>@endpush
