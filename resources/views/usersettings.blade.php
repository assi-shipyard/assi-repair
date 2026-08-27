@extends('layouts.app')

@section('title', 'Pengaturan Pengguna')
@section('body_title', 'Pengaturan Pengguna')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-10">
        @include('partials.flash')

        <div class="card mb-4 settings-summary">
            <div class="card-body p-4">
                <div class="row align-items-center g-3">
                    <div class="col-auto">
                        @if ($employee?->profile_photo_path)
                            <span class="avatar avatar-xl" style="background-image: url('{{ route('settings.photo') }}')"></span>
                        @else
                            <span class="avatar avatar-xl bg-primary-lt text-primary"><i class="ti ti-user fs-1"></i></span>
                        @endif
                    </div>
                    <div class="col">
                        <div class="text-secondary small">Akun Pengguna</div>
                        <h2 class="mb-1">{{ $employee?->name ?? $user->employee_id ?? 'Administrator' }}</h2>
                        <div class="text-secondary">{{ $employee?->position?->name ?? 'Akun sistem' }} <span class="mx-1">&bull;</span> {{ $user->employee_id ?? '-' }}</div>
                    </div>
                    <div class="col-12 col-md-auto"><span class="badge bg-success-lt text-success"><i class="ti ti-shield-check me-1"></i>Akun aktif</span></div>
                </div>
            </div>
        </div>

        <form id="profile-form" action="{{ route('settings.update-profile') }}" method="POST" enctype="multipart/form-data" class="mb-4">
            @csrf
            @method('PUT')
            <div class="card">
                <div class="card-header">
                    <div><h3 class="card-title mb-1">Profil Pengguna</h3><div class="text-secondary small">Perbarui data kontak dan foto akun Anda.</div></div>
                </div>
                <div class="card-body">
                    <div class="row align-items-center g-3 mb-4">
                        <div class="col-auto">
                            @if ($employee?->profile_photo_path)
                                <span class="avatar avatar-xl" style="background-image: url('{{ route('settings.photo') }}')"></span>
                            @else
                                <span class="avatar avatar-xl bg-secondary-lt text-secondary"><i class="ti ti-user fs-1"></i></span>
                            @endif
                        </div>
                        <div class="col-md-7">
                            <label class="form-label" for="profile_photo">Foto Profil</label>
                            <input id="profile_photo" type="file" class="form-control" name="profile_photo" accept="image/jpeg,image/png,image/webp">
                            <div class="form-hint">JPG, PNG, atau WEBP dengan ukuran maksimal 2 MB.</div>
                        </div>
                        @if ($employee?->profile_photo_path)
                        <div class="col-auto align-self-end">
                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modal-delete-profile-picture">Hapus Foto</button>
                        </div>
                        @endif
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">NIK</label>
                            <input type="text" class="form-control" value="{{ $user->employee_id ?? '-' }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" name="name" value="{{ old('name', $employee?->name ?? '') }}" required maxlength="255" autocomplete="name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email', $user->email ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jabatan</label>
                            <input type="text" class="form-control" value="{{ $employee?->position?->name ?? '-' }}" readonly>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </div>
        </form>

        <form id="password-form" action="{{ route('settings.update-password') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card">
                <div class="card-header">
                    <div><h3 class="card-title mb-1">Keamanan Akun</h3><div class="text-secondary small">Gunakan kata sandi baru minimal 8 karakter.</div></div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Kata Sandi Baru</label>
                            <input id="password" type="password" class="form-control" name="password" autocomplete="new-password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Konfirmasi Kata Sandi</label>
                            <input type="password" class="form-control" name="password_confirmation" autocomplete="new-password">
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-secondary">Perbarui Kata Sandi</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
    <style>
        .settings-summary { border-top: 3px solid var(--tblr-primary); }
    </style>
@endpush

@push('scripts')
    <script>
        if (window.jQuery && jQuery.fn.validate) {
            jQuery('#profile-form').validate({
                rules: { name: { required: true, maxlength: 255 }, email: { email: true, maxlength: 255 } },
                errorElement: 'div',
                errorClass: 'invalid-feedback',
                highlight: function (element) { jQuery(element).addClass('is-invalid'); },
                unhighlight: function (element) { jQuery(element).removeClass('is-invalid'); }
            });
            jQuery('#password-form').validate({
                rules: { password: { required: true, minlength: 8 }, password_confirmation: { required: true, equalTo: '#password' } },
                errorElement: 'div',
                errorClass: 'invalid-feedback',
                highlight: function (element) { jQuery(element).addClass('is-invalid'); },
                unhighlight: function (element) { jQuery(element).removeClass('is-invalid'); }
            });
        }
    </script>
@endpush

{{-- Modal --}}
@section('modal')
    <div class="modal modal-blur fade" id="modal-delete-profile-picture" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-status bg-danger"></div>
                <div class="modal-body text-center py-4">
                    <h3>Apakah Anda yakin?</h3>
                    <div class="text-secondary">Foto profil akan dihapus dari penyimpanan lokal.</div>
                </div>
                <div class="modal-footer">
                    <div class="w-100">
                        <div class="row">
                            <div class="col">
                                <button class="btn btn-3 w-100" data-bs-dismiss="modal">Batal</button>
                            </div>
                            <div class="col">
                                <form action="{{ route('settings.delete-photo') }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-4 w-100" id="btn-confirm-delete">Hapus Foto</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
