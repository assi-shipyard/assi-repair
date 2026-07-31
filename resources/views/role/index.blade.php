@extends('layouts.app')

@section('title', 'Data Role')
@section('body_title', 'Data Role')

@section('buttons_beside_title')
    <a href="{{ route('role.create') }}" class="btn btn-primary">Tambah Role</a>
@endsection

@section('content')
    @include('partials.flash')
    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead><tr><th>Nama Sistem</th><th>Nama Role</th><th>Permission</th><th class="w-1"></th></tr></thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td>{{ $role->name }}</td>
                            <td>{{ $role->role_name ?? '-' }}</td>
                            <td>{{ $role->permissions_count ?? 0 }}</td>
                            <td class="text-end">
                                <a href="{{ route('role.show', $role->id) }}" class="btn btn-sm btn-outline-primary">Lihat</a>
                                <a href="{{ route('role.edit', $role->id) }}" class="btn btn-sm btn-outline-secondary">Ubah</a>
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
