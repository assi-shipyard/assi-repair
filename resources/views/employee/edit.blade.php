@extends('layouts.app')

@section('title', 'Ubah Pengguna')
@section('body_title', 'Ubah Pengguna')

@section('buttons_beside_title')
    <a href="{{ route('employee.show', $employee->unique_id) }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1" aria-hidden="true"></i>Detail Pengguna</a>
@endsection

@section('content')
    @include('partials.flash')

    @include('employee.partials.form', ['is_edit' => true])
@endsection
