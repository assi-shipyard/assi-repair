@extends('layouts.app')

@section('title', 'Data Permission')
@section('body_title', 'Data Permission')

@section('buttons_beside_title')
    <a href="{{ route('permission.create') }}" class="btn btn-primary">Tambah Permission</a>
@endsection

@section('content')
    @include('partials.flash')

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead><tr><th>Nama Sistem</th><th>Nama Permission</th><th>Role</th><th class="w-1"></th></tr></thead>
                <tbody>
                    @forelse ($permissions as $permission)
                        <tr>
                            <td>{{ $permission->name }}</td>
                            <td>{{ $permission->permission_name ?? '-' }}</td>
                            <td>{{ $permission->roles_count ?? 0 }}</td>
                            <td class="text-end">
                                <a href="{{ route('permission.show', $permission->id) }}" class="btn btn-sm btn-outline-primary">Lihat</a>
                                <a href="{{ route('permission.edit', $permission->id) }}" class="btn btn-sm btn-outline-secondary">Ubah</a>
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
