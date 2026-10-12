@extends('layouts.app')
@section('title', 'Pengaturan Akun Pelanggan')
@section('body_title', 'Pengaturan Akun Pelanggan')
@section('content')
    @include('partials.flash')
    <div class="row g-3">
        <div class="col-lg-5"><div class="card"><div class="card-body"><h3 class="card-title">Identitas Akun</h3><p>{{ auth()->user()->name }}<br><span class="text-secondary">{{ auth()->user()->email }}</span></p><p class="text-secondary mb-0">Hubungi administrator untuk perubahan identitas, perusahaan, atau akses proyek.</p></div></div></div>
        <div class="col-lg-7"><div class="card"><div class="card-header"><h3 class="card-title">Ubah Kata Sandi</h3></div><div class="card-body">
            <form class="customer_form" method="POST" action="{{ route('customer.password') }}">
                @csrf @method('PUT')
                <div class="mb-3"><label for="current_password" class="form-label">Kata Sandi Saat Ini</label><input id="current_password" name="current_password" type="password" class="form-control" required maxlength="1024" autocomplete="current-password"></div>
                <div class="mb-3"><label for="password" class="form-label">Kata Sandi Baru</label><input id="password" name="password" type="password" class="form-control" required minlength="12" maxlength="128" autocomplete="new-password"><small class="text-secondary">Minimal 12 karakter dengan huruf besar, huruf kecil, dan angka.</small></div>
                <div class="mb-3"><label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi Baru</label><input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required minlength="12" maxlength="128" autocomplete="new-password"></div>
                <button class="btn btn-primary" type="submit">Simpan Kata Sandi</button>
            </form>
        </div></div></div>
    </div>
    @include('customer.validation')
@endsection
