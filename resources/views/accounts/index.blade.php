@extends('layouts.app')
@php
    $is_customer_directory = $account_type === \App\Models\User::TYPE_CUSTOMER;
    $directory_title = $is_customer_directory ? 'Akun Pelanggan' : 'Akun Administrator';
    $directory_route = $is_customer_directory ? 'accounts.customers' : 'accounts.index';
@endphp
@section('title', $directory_title)
@section('body_title', $directory_title)
@section('buttons_beside_title')
    <a class="btn btn-outline-secondary" href="{{ route('employee.index') }}">Karyawan Internal</a>
    <a class="btn btn-outline-secondary" href="{{ route($is_customer_directory ? 'accounts.index' : 'accounts.customers') }}">{{ $is_customer_directory ? 'Akun Admin' : 'Akun Pelanggan' }}</a>
    @if ($is_customer_directory)<a class="btn btn-outline-primary" href="{{ route('accounts.projects') }}">Akses Proyek</a>@endif
    <a class="btn btn-primary" href="#create-account">Buat Akun {{ $is_customer_directory ? 'Pelanggan' : 'Admin' }}</a>
@endsection
@section('content')
    @include('partials.flash')
    <div class="card mb-3"><div class="card-body d-flex flex-wrap align-items-center gap-3">
        <div class="me-auto"><h3 class="mb-1">{{ $total }} {{ $is_customer_directory ? 'Pelanggan' : 'Admin' }} Terdaftar</h3><p class="text-secondary mb-0">{{ $is_customer_directory ? 'Pelanggan menggunakan email. Perusahaan dapat berupa pemilik kapal atau surveyor; akses proyek diberikan secara terpisah.' : 'Akun administrator menggunakan username dan tidak terkait dengan identitas karyawan.' }}</p></div>
        <span class="badge bg-green-lt">{{ $active_total }} aktif</span><span class="badge bg-secondary-lt">{{ $total - $active_total }} tidak aktif</span>
    </div></div>
    @include('accounts.partials.directory-filter', ['search_hint' => $is_customer_directory ? 'Nama, email, atau perusahaan' : 'Nama, email, atau username'])
    <div class="row g-3">
        @forelse ($users as $user)
            <div class="col-12 col-md-6 col-xl-4"><article class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        @if ($user->profile_photo_path)
                            <img src="{{ route('accounts.photo', $user) }}" class="avatar avatar-lg object-fit-cover flex-shrink-0" alt="Foto profil {{ $user->name ?? $user->username }}" loading="lazy">
                        @else
                            <span class="avatar avatar-lg bg-primary-lt flex-shrink-0" role="img" aria-label="Foto profil belum tersedia"><i class="ti ti-user fs-2" aria-hidden="true"></i></span>
                        @endif
                        <div class="min-w-0"><h3 class="card-title mb-1 text-break">{{ $user->name ?? $user->username ?? $user->employee_id }}</h3><span class="badge {{ $user->is_active ? 'bg-green-lt' : 'bg-red-lt' }}">{{ $user->is_active ? 'Aktif' : 'Tidak Aktif' }}</span></div>
                    </div>
                    <dl class="row small mb-0">
                        <dt class="col-4 text-secondary fw-normal">Email</dt><dd class="col-8 text-break">{{ $user->email ?? '-' }}</dd>
                        @if ($is_customer_directory)
                            <dt class="col-4 text-secondary fw-normal">Perusahaan</dt><dd class="col-8 text-break">{{ $user->company?->name ?? '-' }}</dd>
                            <dt class="col-4 text-secondary fw-normal">Akses Proyek</dt><dd class="col-8">{{ $user->customer_access_count }} proyek aktif</dd>
                        @else
                            <dt class="col-4 text-secondary fw-normal">Username</dt><dd class="col-8 text-break">{{ $user->username ?? $user->employee_id }}</dd>
                        @endif
                        <dt class="col-4 text-secondary fw-normal">Terdaftar</dt><dd class="col-8 mb-0">{{ $user->created_at?->tanggal_indonesia() ?? '-' }}</dd>
                    </dl>
                    @if ($user->must_change_password)<div class="text-secondary small mt-3">Wajib mengganti kata sandi awal.</div>@endif
                </div>
                <div class="card-footer d-flex flex-wrap gap-2">
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('accounts.edit', $user) }}">Ubah</a>
                    @if ($user->id !== auth()->id())
                        <form method="POST" action="{{ route('accounts.status', $user) }}">@csrf @method('PUT')<input type="hidden" name="is_active" value="{{ $user->is_active ? '0' : '1' }}"><button class="btn btn-sm btn-outline-secondary" type="submit">{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
                    @else<span class="text-secondary small align-self-center">Akun Anda</span>@endif
                </div>
            </article></div>
        @empty
            <div class="col-12"><div class="card"><div class="card-body text-center text-secondary py-5">{{ $total === 0 ? 'Belum ada akun terdaftar.' : 'Tidak ada akun yang cocok dengan pencarian.' }}</div></div></div>
        @endforelse
    </div>
    <div class="my-3">{{ $users->onEachSide(1)->links('accounts.partials.pagination') }}</div>
    <div class="card" id="create-account"><div class="card-header"><h3 class="card-title">Buat Akun {{ $is_customer_directory ? 'Pelanggan' : 'Admin' }}</h3></div><div class="card-body">
        <form class="customer_form" action="{{ route('accounts.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="account_type" value="{{ $account_type }}">
            <div class="row g-3">
                <div class="col-md-6"><label for="name" class="form-label">Nama Lengkap</label><input id="name" name="name" class="form-control" required maxlength="255" value="{{ old('name') }}"></div>
                <div class="col-md-6"><label for="email" class="form-label">Email</label><input id="email" name="email" type="email" class="form-control" required maxlength="64" value="{{ old('email') }}" autocomplete="off"></div>
                @if ($is_customer_directory)
                    <div class="col-md-6"><label for="company_id" class="form-label">Perusahaan Pelanggan / Surveyor</label><select id="company_id" name="company_id" class="form-select" required><option value="">Pilih perusahaan</option>@foreach ($companies as $company)<option value="{{ $company->unique_id }}" @selected(old('company_id') === $company->unique_id)>{{ $company->name }}</option>@endforeach</select></div>
                @else
                    <div class="col-md-6"><label for="username" class="form-label">Username Admin</label><input id="username" name="username" class="form-control" required maxlength="64" value="{{ old('username') }}"></div>
                @endif
                <div class="col-md-6"><label for="profile_photo" class="form-label">Foto Profil (Opsional)</label><input id="profile_photo" name="profile_photo" type="file" class="form-control" accept="image/jpeg,image/png,image/webp"><small class="text-secondary">JPG, PNG, atau WEBP; maksimal 2 MB dan 4.000 x 4.000 piksel.</small></div>
                <div class="col-md-6"><label for="password" class="form-label">Kata Sandi Awal</label><input id="password" name="password" type="password" class="form-control" required minlength="12" maxlength="128" autocomplete="new-password"></div>
                <div class="col-md-6"><label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi</label><input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required minlength="12" maxlength="128" autocomplete="new-password"></div>
            </div>
            <p class="text-secondary mt-3">Minimal 12 karakter dengan huruf besar, huruf kecil, dan angka. Sampaikan kata sandi awal melalui saluran aman.</p>
            <button class="btn btn-primary" type="submit">Simpan Akun</button>
        </form>
    </div></div>
    @include('customer.validation')
@endsection
