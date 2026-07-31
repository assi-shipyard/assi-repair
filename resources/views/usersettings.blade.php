@extends('layouts.app')

@section('title', 'Pengaturan Pengguna')
@section('body_title', 'Pengaturan Pengguna')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-8">
        @include('partials.flash')

        <form action="{{ route('settings.update-profile') }}" method="POST" enctype="multipart/form-data" class="mb-4">
            @csrf
            @method('PUT')
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">Data Pribadi</h3>
                </div>
                <div class="card-body">
                    <div class="row align-items-center g-3 mb-4">
                        <div class="col-auto">
                            <div class="avatar avatar-xl rounded" style="background-image: url('{{ route('settings.photo') }}'); background-size: cover; background-position: center;"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Foto Profil</label>
                            <input type="file" class="form-control" name="profile_photo" accept="image/*">
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modal-delete-profile-picture">Hapus Foto</button>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">NIK</label>
                            <input type="text" class="form-control" value="{{ $user->employee_id ?? '-' }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" name="name" value="{{ old('name', $employee->name ?? '') }}" required>
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

        <form action="{{ route('settings.update-password') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">Ubah Kata Sandi</h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Kata Sandi Baru</label>
                            <input type="password" class="form-control" name="password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Konfirmasi Kata Sandi</label>
                            <input type="password" class="form-control" name="password_confirmation">
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
