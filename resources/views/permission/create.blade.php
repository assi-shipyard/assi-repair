@extends('layouts.app')

@section('title', 'Tambah Permission')
@section('body_title', 'Tambah Permission')

@section('buttons_beside_title')
    <a href="{{ route('permission.index') }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i>Kembali</a>
@endsection

@section('content')
    @include('partials.flash')

    <div class="card permission-form-summary mb-4"><div class="card-body p-4 d-flex align-items-center gap-3"><span class="avatar avatar-lg bg-azure-lt text-azure"><i class="ti ti-key-plus fs-2"></i></span><div><div class="text-secondary small">Katalog Hak Akses</div><h2 class="mb-1">Buat Permission Baru</h2><div class="text-secondary">Tambahkan izin spesifik, lalu tetapkan ke role yang sesuai.</div></div></div></div>
    <form id="permission-form" action="{{ route('permission.store') }}" method="POST" class="card">
        @csrf
        <div class="card-header"><div><h3 class="card-title mb-1">Identitas Permission</h3><div class="text-secondary small">Nama sistem digunakan untuk pemeriksaan hak akses di aplikasi.</div></div></div>
        <div class="card-body">
            <div class="row g-3"><div class="col-md-6"><label class="form-label required" for="name">Nama Sistem</label><input id="name" class="form-control" name="name" value="{{ old('name') }}" maxlength="255" placeholder="Contoh: project.update" required><div class="form-hint">Gunakan pola modul.aksi agar konsisten.</div></div><div class="col-md-6"><label class="form-label required" for="permission_name">Nama Permission</label><input id="permission_name" class="form-control" name="permission_name" value="{{ old('permission_name') }}" maxlength="255" placeholder="Contoh: Ubah Proyek" required></div><div class="col-12"><label class="form-label" for="permission_description">Deskripsi</label><textarea id="permission_description" class="form-control" name="permission_description" rows="4" maxlength="1000" placeholder="Jelaskan tindakan yang diizinkan.">{{ old('permission_description') }}</textarea></div></div>
        </div>
        <div class="card-footer d-flex justify-content-between"><span class="text-secondary small">Role dapat dipilih setelah permission dibuat.</span><button class="btn btn-primary" type="submit"><i class="ti ti-device-floppy me-1"></i>Simpan Permission</button></div>
    </form>
@endsection

@push('styles')<style>.permission-form-summary { border-top: 3px solid var(--tblr-azure); }</style>@endpush
@push('scripts')<script>if (window.jQuery && jQuery.fn.validate) { jQuery('#permission-form').validate({ rules: { name: { required: true, maxlength: 255 }, permission_name: { required: true, maxlength: 255 }, permission_description: { maxlength: 1000 } }, errorElement: 'div', errorClass: 'invalid-feedback', highlight: function (element) { jQuery(element).addClass('is-invalid'); }, unhighlight: function (element) { jQuery(element).removeClass('is-invalid'); } }); }</script>@endpush
