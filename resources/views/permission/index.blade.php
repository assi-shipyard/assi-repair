@extends('layouts.app')

@section('title', 'Data Permission')
@section('body_title', 'Data Permission')

@section('buttons_beside_title')
    <a href="{{ route('permission.create') }}" class="btn btn-primary"><i class="ti ti-key-plus me-1"></i>Tambah Permission</a>
@endsection

@section('content')
    @include('partials.flash')

    <div class="card permission-summary mb-4">
        <div class="card-body p-4 d-flex flex-wrap align-items-center gap-3">
            <span class="avatar avatar-lg bg-azure-lt text-azure"><i class="ti ti-key fs-2"></i></span>
            <div class="me-auto"><div class="text-secondary small">Katalog Hak Akses</div><h2 class="mb-1">{{ $permissions->count() }} Permission Terdaftar</h2><div class="text-secondary">Tetapkan izin ke role untuk menjaga akses setiap fitur tetap terkontrol.</div></div>
            <a href="{{ route('role.index') }}" class="btn btn-outline-secondary"><i class="ti ti-shield me-1"></i>Kelola Role</a>
        </div>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead><tr><th>Permission</th><th>Nama Sistem</th><th>Dipakai Role</th><th class="w-1"></th></tr></thead>
                <tbody>
                    @forelse ($permissions as $permission)
                        <tr>
                            <td><div class="fw-semibold">{{ $permission->permission_name ?? $permission->name }}</div><div class="text-secondary small">{{ $permission->permission_description ?: 'Deskripsi belum diisi.' }}</div></td>
                            <td><code>{{ $permission->name }}</code></td>
                            <td><span class="badge bg-azure-lt text-azure">{{ $permission->roles_count ?? 0 }} role</span></td>
                            <td class="text-end">
                                <a href="{{ route('permission.assign-roles', $permission->id) }}" class="btn btn-sm btn-primary">Atur Role</a>
                                <a href="{{ route('permission.show', $permission->id) }}" class="btn btn-sm btn-outline-secondary" aria-label="Lihat {{ $permission->name }}"><i class="ti ti-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-secondary py-4">Belum ada data permission.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('styles')
    <style>.permission-summary { border-top: 3px solid var(--tblr-azure); }</style>
@endpush
