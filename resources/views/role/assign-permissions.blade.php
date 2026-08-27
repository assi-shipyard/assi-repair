@extends('layouts.app')

@section('title', 'Atur Permission Role')
@section('body_title', 'Atur Permission Role')

@section('buttons_beside_title')
    <a href="{{ route('role.show', $role->id) }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
    @include('partials.flash')

    <form action="{{ route('role.update-permissions', $role->id) }}" method="POST" class="card">
        @csrf
        @method('PUT')
        <div class="card-header">
            <div><h3 class="card-title mb-1">Hak Akses untuk {{ $role->role_name ?? $role->name }}</h3><div class="text-secondary small">Pilih permission yang dapat digunakan oleh role ini.</div></div>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <div class="input-icon flex-fill assignment-search"><span class="input-icon-addon"><i class="ti ti-search"></i></span><input type="search" class="form-control assignment-filter" placeholder="Cari permission..."></div>
                <button type="button" class="btn btn-outline-secondary assignment-select-all">Pilih Semua</button>
                <button type="button" class="btn btn-outline-secondary assignment-clear">Kosongkan</button>
            </div>
            <div class="list-group assignment-list">
                @foreach ($permissions as $permission)
                    <label class="list-group-item d-flex align-items-center gap-3 assignment-item" data-search="{{ strtolower($permission->name.' '.$permission->permission_name) }}">
                            <input class="form-check-input" type="checkbox" name="permission_ids[]" value="{{ $permission->id }}" @checked(in_array($permission->id, $assigned_permission_ids, true))>
                            <span class="col"><span class="fw-semibold d-block">{{ $permission->permission_name ?? $permission->name }}</span><span class="text-secondary small">{{ $permission->name }}</span></span>
                    </label>
                @empty
                    <div class="list-group-item text-secondary">Belum ada permission yang dapat ditetapkan.</div>
                @endforeach
            </div>
        </div>
        <div class="card-footer text-end"><button class="btn btn-primary" type="submit">Simpan</button></div>
    </form>
@endsection

@push('styles')
    <style>.assignment-search { min-width: 16rem; }</style>
@endpush

@push('scripts')
    <script>
        document.querySelectorAll('.assignment-filter').forEach(function (input) {
            input.addEventListener('input', function () { var query = this.value.toLowerCase(); document.querySelectorAll('.assignment-item').forEach(function (item) { item.classList.toggle('d-none', !item.dataset.search.includes(query)); }); });
        });
        document.querySelectorAll('.assignment-select-all').forEach(function (button) { button.addEventListener('click', function () { document.querySelectorAll('.assignment-item:not(.d-none) input').forEach(function (input) { input.checked = true; }); }); });
        document.querySelectorAll('.assignment-clear').forEach(function (button) { button.addEventListener('click', function () { document.querySelectorAll('.assignment-item input').forEach(function (input) { input.checked = false; }); }); });
    </script>
@endpush
