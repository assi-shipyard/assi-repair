@extends('layouts.app')

@section('title', 'Detail Pengguna')
@section('body_title', 'Detail Pengguna')

@section('buttons_beside_title')
    <a href="{{ route('employee.edit', $employee->unique_id) }}" class="btn btn-primary"><i class="ti ti-pencil me-1" aria-hidden="true"></i>Ubah Pengguna</a>
    <a href="{{ route('employee.index') }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1" aria-hidden="true"></i>Daftar Pengguna</a>
@endsection

@section('content')
    @include('partials.flash')

    <div class="card employee-profile-summary mb-4">
        <div class="card-body p-4 d-flex flex-wrap align-items-center gap-3">
            @if ($employee->profile_photo_path)
                <img src="{{ route('employee.photo', $employee->unique_id) }}" alt="Foto {{ $employee->name }}" class="avatar avatar-xl employee-profile-photo">
            @else
                <span class="avatar avatar-xl bg-primary-lt text-primary">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($employee->name, 0, 2)) }}</span>
            @endif
            <div class="me-auto text-break">
                <div class="text-secondary small">Pengguna Karyawan</div>
                <h2 class="mb-1">{{ $employee->name }}</h2>
                <div class="text-secondary font-monospace">NIK {{ $employee->employee_id }}</div>
            </div>
            <span class="badge {{ $employee->status === 'active' ? 'bg-success-lt text-success' : 'bg-secondary-lt text-secondary' }}">{{ $employee->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}</span>
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-12 col-lg-8">
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-id me-2" aria-hidden="true"></i>Identitas Karyawan</h3></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-secondary fw-normal">Nama Lengkap</dt><dd class="col-sm-8 text-break">{{ $employee->name }}</dd>
                        <dt class="col-sm-4 text-secondary fw-normal">NIK</dt><dd class="col-sm-8 font-monospace">{{ $employee->employee_id }}</dd>
                        <dt class="col-sm-4 text-secondary fw-normal">Email</dt><dd class="col-sm-8 text-break">{{ $employee->email ?? 'Belum diisi' }}</dd>
                        <dt class="col-sm-4 text-secondary fw-normal">Status Karyawan</dt><dd class="col-sm-8 mb-0">{{ $employee->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}</dd>
                    </dl>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-sitemap me-2" aria-hidden="true"></i>Struktur Organisasi</h3></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-secondary fw-normal">Jabatan</dt>
                        <dd class="col-sm-8">{{ $employee->position?->name ?? 'Belum diatur' }}</dd>
                        <dt class="col-sm-4 text-secondary fw-normal">Kategori Jabatan</dt><dd class="col-sm-8">{{ $employee->position?->category_label ?? '-' }}</dd>
                        <dt class="col-sm-4 text-secondary fw-normal">Unit Organisasi</dt><dd class="col-sm-8">{{ $employee->position?->organizational_unit?->name ?? 'Belum diatur' }}</dd>
                        <dt class="col-sm-4 text-secondary fw-normal">Manajer Langsung</dt>
                        <dd class="col-sm-8 mb-0">
                            @if ($employee->manager)
                                <a href="{{ route('employee.show', $employee->manager->unique_id) }}">{{ $employee->manager->name }}</a>
                            @else
                                Tidak ada
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">Informasi Pencatatan</h3></div>
                <div class="card-body">
                    <dl class="mb-0">
                        <dt class="text-secondary fw-normal">Terdaftar Pada</dt>
                        <dd>{{ $employee->created_at?->timezone('Asia/Jakarta')->tanggal_waktu_indonesia() }} WIB</dd>
                        <dt class="text-secondary fw-normal">Terakhir Diperbarui</dt>
                        <dd class="mb-0">{{ $employee->updated_at?->timezone('Asia/Jakarta')->tanggal_waktu_indonesia() }} WIB</dd>
                    </dl>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Pengelolaan Pengguna</h3>
                    <p class="text-secondary mb-0">Gunakan tombol Ubah Pengguna untuk memperbarui identitas, status karyawan, jabatan, manajer, foto profil, atau kata sandi. Kata sandi tidak ditampilkan pada halaman ini.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .employee-profile-summary { border-top: 3px solid var(--tblr-primary); }
        .employee-profile-photo { object-fit: cover; }
    </style>
@endpush
