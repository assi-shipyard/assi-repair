@extends('layouts.app')
@section('title', 'Ubah Akun')
@section('body_title', 'Ubah Akun')
@section('buttons_beside_title')
    <a href="{{ route('accounts.index') }}" class="btn btn-outline-secondary">Daftar Akun</a>
@endsection
@section('content')
    @include('partials.flash')
    <div class="card"><div class="card-header"><h3 class="card-title">{{ $user->is_customer() ? 'Pelanggan' : 'Admin' }}</h3></div><div class="card-body">
        <form class="customer_form" method="POST" action="{{ route('accounts.update', $user) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6"><label for="name" class="form-label">Nama Lengkap</label><input id="name" name="name" class="form-control" required maxlength="255" value="{{ old('name', $user->name) }}"></div>
                <div class="col-md-6"><label for="email" class="form-label">Email</label><input id="email" name="email" type="email" class="form-control" required maxlength="64" value="{{ old('email', $user->email) }}"></div>
                @if ($user->is_customer())
                    <div class="col-md-6"><label for="company_id" class="form-label">Perusahaan Pelanggan / Surveyor</label><select id="company_id" name="company_id" class="form-select" required><option value="">Pilih perusahaan</option>@foreach ($companies as $company)<option value="{{ $company->unique_id }}" @selected(old('company_id', $user->company?->unique_id) === $company->unique_id)>{{ $company->name }}</option>@endforeach</select></div>
                @else
                    <div class="col-md-6"><label for="username" class="form-label">Username Admin</label><input id="username" name="username" class="form-control" required maxlength="64" value="{{ old('username', $user->username) }}"></div>
                @endif
                <div class="col-md-6"><label for="password" class="form-label">Reset Kata Sandi (Opsional)</label><input id="password" name="password" type="password" class="form-control" minlength="12" maxlength="128" autocomplete="new-password"></div>
                <div class="col-md-6"><label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi</label><input id="password_confirmation" name="password_confirmation" type="password" class="form-control" minlength="12" maxlength="128" autocomplete="new-password"></div>
            </div>
            <p class="text-secondary mt-3">Kosongkan kata sandi untuk mempertahankan kata sandi saat ini. Reset kata sandi pelanggan mewajibkan penggantian saat login berikutnya. Jenis akun tidak dapat diubah.</p>
            <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
        </form>
    </div></div>
    @include('customer.validation')
@endsection
