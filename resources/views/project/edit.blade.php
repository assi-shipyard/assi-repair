@extends('layouts.app')

@section('title', 'Ubah Proyek')
@section('body_title', 'Ubah Proyek')

@section('buttons_beside_title')
    <a href="{{ route('project.show', $project->unique_id) }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i>Kembali
    </a>
@endsection

@section('content')
    @include('partials.flash')
    @include('project.partials.form')
@endsection
