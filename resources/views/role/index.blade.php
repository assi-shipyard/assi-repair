@extends('layouts.app')

@section('title', 'Data Role')
@section('body_title', 'Data Role')

@section('buttons_beside_title')
    <a href="{{ route('role.create') }}" class="btn btn-primary"><i class="ti ti-shield-plus me-1"></i>Tambah Role</a>
@endsection

@section('content')
    @include('partials.flash')
    <div class="card role-summary mb-4">
        <div class="card-body p-4 d-flex flex-wrap align-items-center gap-3">
            <span class="avatar avatar-lg bg-primary-lt text-primary"><i class="ti ti-shield fs-2"></i></span>
            <div class="me-auto"><div class="text-secondary small">Akses Berbasis Peran</div><h2 class="mb-1">{{ $roles->count() }} Role Terdaftar</h2><div class="text-secondary">Kelola peran dan hak akses yang digunakan pengguna aplikasi.</div></div>
            <a href="{{ route('permission.index') }}" class="btn btn-outline-secondary"><i class="ti ti-key me-1"></i>Kelola Permission</a>
        </div>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead><tr><th>Role</th><th>Nama Sistem</th><th>Hak Akses</th><th class="w-1"></th></tr></thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td><div class="fw-semibold">{{ $role->role_name ?? $role->name }}</div><div class="text-secondary small">{{ $role->role_description ?: 'Deskripsi belum diisi.' }}</div></td>
                            <td><code>{{ $role->name }}</code></td>
                            <td><span class="badge bg-primary-lt text-primary">{{ $role->permissions_count ?? 0 }} permission</span></td>
                            <td class="text-end">
                                <a href="{{ route('role.assign-permissions', $role->id) }}" class="btn btn-sm btn-primary">Atur Akses</a>
                                <a href="{{ route('role.show', $role->id) }}" class="btn btn-sm btn-outline-secondary" aria-label="Lihat {{ $role->name }}"><i class="ti ti-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-secondary py-4">Belum ada data role.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('styles')
    <style>.role-summary { border-top: 3px solid var(--tblr-primary); }</style>
@endpush
