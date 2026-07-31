@extends('layouts.app')

@section('title', 'Detail Karyawan')
@section('body_title', 'Detail Karyawan')

@section('buttons_beside_title')
    <a href="{{ route('employee.edit', $employee->id) }}" class="btn btn-outline-primary">Ubah</a>
    <a href="{{ route('employee.index') }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
    @include('partials.flash')

    <div class="row row-cards">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="avatar avatar-xl mb-3" style="background-image: url('{{ route('employee.photo', $employee->id) }}');"></div>
                    <h3 class="mb-1">{{ $employee->name }}</h3>
                    <div class="text-secondary">{{ $employee->employee_id }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-secondary">Email</div>
                            <div>{{ $employee->email ?? '-' }}</div></div>
                        <div class="col-md-6">
                            <div class="text-secondary">Status</div>
                            <div>{{ $employee->status }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-secondary">Jabatan</div>
                            <div>{{ $employee->position?->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-secondary">Unit Organisasi</div>
                            <div>{{ $employee->position?->organizational_unit?->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-secondary">Manajer</div>
                            <div>{{ $employee->manager?->name ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
