@extends('layouts.app')

@section('title', 'Tambah Proyek')
@section('body_title', 'Tambah Proyek')

@section('buttons_beside_title')
    <a href="{{ route('project.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i>Kembali
    </a>
@endsection

@section('content')
    @include('partials.flash')
    @include('project.partials.form')
@endsection
