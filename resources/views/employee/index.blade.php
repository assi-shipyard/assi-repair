@extends('layouts.app')
@section('title', 'Akun Karyawan Internal')
@section('body_title', 'Akun Karyawan Internal')
@section('buttons_beside_title')
    @role('admin')
        <a href="{{ route('accounts.customers') }}" class="btn btn-outline-secondary">Akun Pelanggan</a>
    @endrole
    <a href="{{ route('employee.create') }}" class="btn btn-primary"><i class="ti ti-user-plus me-1" aria-hidden="true"></i>Buat Akun Karyawan</a>
@endsection
@section('content')
    @include('partials.flash')
    <div class="card mb-3">
        <div class="card-body d-flex flex-wrap align-items-center gap-3">
            <span class="avatar avatar-lg bg-primary-lt"><i class="ti ti-users fs-2" aria-hidden="true"></i></span>
            <div class="me-auto"><h3 class="mb-1">{{ $total }} Karyawan Terdaftar</h3><p class="text-secondary mb-0">Identitas, jabatan, dan struktur organisasi karyawan internal. Akun pelanggan dan admin berada di halaman terpisah.</p></div>
            <span class="badge bg-green-lt">{{ $active_total }} aktif</span>
            <span class="badge bg-secondary-lt">{{ $inactive_total }} tidak aktif</span>
        </div>
    </div>
    @include('accounts.partials.directory-filter', ['directory_route' => 'employee.index', 'search_hint' => 'Nama, NIK, jabatan, unit, atau manajer'])
    <div class="row g-3" id="employee-cards">
        @forelse ($employees as $employee)
            <div class="col-12 col-md-6 col-xl-4">
                <article class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            @if ($employee->profile_photo_path)
                                <img src="{{ route('employee.photo', $employee->unique_id) }}" class="avatar avatar-lg object-fit-cover flex-shrink-0" alt="Foto profil {{ $employee->name }}" loading="lazy">
                            @else
                                <span class="avatar avatar-lg bg-primary-lt flex-shrink-0" role="img" aria-label="Foto profil belum tersedia"><i class="ti ti-user fs-2" aria-hidden="true"></i></span>
                            @endif
                            <div class="min-w-0"><h3 class="card-title mb-1 text-break">{{ $employee->name }}</h3><div class="text-secondary small font-monospace">NIK {{ $employee->employee_id }}</div></div>
                        </div>
                        <span class="badge {{ $employee->status === 'active' ? 'bg-green-lt' : 'bg-secondary-lt' }}">{{ $employee->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}</span>
                        @if ($employee->user && ! $employee->user->is_active)<span class="badge bg-red-lt">Akun Dinonaktifkan</span>@endif
                        <dl class="row mb-0 mt-3 small">
                            <dt class="col-4 text-secondary fw-normal">Jabatan</dt><dd class="col-8 text-break">{{ $employee->position?->name ?? '-' }}</dd>
                            <dt class="col-4 text-secondary fw-normal">Unit</dt><dd class="col-8 text-break">{{ $employee->position?->organizational_unit?->name ?? '-' }}</dd>
                            <dt class="col-4 text-secondary fw-normal">Manajer</dt><dd class="col-8 text-break">{{ $employee->manager?->name ?? '-' }}</dd>
                            <dt class="col-4 text-secondary fw-normal">Email</dt><dd class="col-8 text-break mb-0">{{ $employee->email ?? $employee->user?->email ?? '-' }}</dd>
                        </dl>
                    </div>
                    <div class="card-footer d-flex flex-wrap gap-2">
                        <a href="{{ route('employee.show', $employee->unique_id) }}" class="btn btn-outline-primary btn-sm">Lihat Detail</a>
                        <a href="{{ route('employee.edit', $employee->unique_id) }}" class="btn btn-outline-secondary btn-sm">Ubah</a>
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12"><div class="card"><div class="card-body text-center text-secondary py-5">{{ $total === 0 ? 'Belum ada karyawan terdaftar.' : 'Tidak ada karyawan yang cocok dengan pencarian.' }}</div></div></div>
        @endforelse
    </div>
    <div class="mt-3">{{ $employees->onEachSide(1)->links('accounts.partials.pagination') }}</div>
    @include('customer.validation')
@endsection
