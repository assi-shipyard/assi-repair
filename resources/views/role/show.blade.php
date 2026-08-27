@extends('layouts.app')

@section('title', 'Detail Role')
@section('body_title', 'Detail Role')

@section('buttons_beside_title')
    <a href="{{ route('role.index') }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i>Kembali</a>
    <a href="{{ route('role.edit', $role->id) }}" class="btn btn-outline-secondary"><i class="ti ti-pencil me-1"></i>Ubah</a>
    <a href="{{ route('role.assign-permissions', $role->id) }}" class="btn btn-primary"><i class="ti ti-key me-1"></i>Atur Permission</a>
@endsection

@section('content')
    @include('partials.flash')

    <div class="card role-detail-summary mb-4"><div class="card-body p-4 d-flex flex-wrap align-items-center gap-3"><span class="avatar avatar-lg bg-primary-lt text-primary"><i class="ti ti-shield fs-2"></i></span><div class="me-auto"><div class="text-secondary small">Role</div><h2 class="mb-1">{{ $role->role_name ?? $role->name }}</h2><div class="text-secondary"><code>{{ $role->name }}</code></div></div><div><div class="text-secondary small">Permission Aktif</div><div class="h2 mb-0">{{ $role->permissions->count() }}</div></div></div></div>
    <div class="row row-cards"><div class="col-lg-5"><div class="card h-100"><div class="card-header"><h3 class="card-title">Deskripsi</h3></div><div class="card-body text-secondary">{{ $role->role_description ?: 'Deskripsi role belum diisi.' }}</div></div></div><div class="col-lg-7"><div class="card h-100"><div class="card-header"><h3 class="card-title">Permission yang Ditetapkan</h3></div><div class="card-body"><div class="d-flex flex-wrap gap-2">
                @forelse ($role->permissions as $permission)
                    <span class="badge bg-primary-lt text-primary">{{ $permission->permission_name ?? $permission->name }}</span>
                @empty
                    <span class="text-secondary">Belum ada permission yang ditetapkan.</span>
                @endforelse
            </div></div></div></div></div>
@endsection

@push('styles')<style>.role-detail-summary { border-top: 3px solid var(--tblr-primary); }</style>@endpush
