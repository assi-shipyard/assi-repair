@extends('layouts.app')

@section('title', 'Buat Akun Baru')
@section('body_title', 'Buat Akun Baru')

@section('buttons_beside_title')
    <a href="{{ route('employee.index') }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1" aria-hidden="true"></i>Daftar Pengguna</a>
@endsection

@section('content')
    @include('partials.flash')

    @role('admin')
        <div class="card mb-3"><div class="card-body">
            <h3 class="card-title">Jenis Akun: Karyawan Internal</h3>
            <p class="text-secondary mb-2">Formulir ini khusus karyawan dengan NIK 9 digit. Admin dan pelanggan memiliki identitas serta hak akses terpisah.</p>
            <a class="btn btn-outline-primary" href="{{ route('accounts.customers') }}">Buat Akun Pelanggan</a>
            <a class="btn btn-outline-secondary" href="{{ route('accounts.index') }}">Buat Akun Admin</a>
        </div></div>
    @endrole

    @include('employee.partials.form', ['is_edit' => false, 'employee' => null])
@endsection
