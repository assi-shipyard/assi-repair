@extends('layouts.app')

@section('title', 'Repair List')
@section('body_title', 'Repair List')

@section('buttons_beside_title')
    <a href="{{ route('project.job-document.workflow.index', $project->unique_id) }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
    @include('partials.flash')
    @include('project.job-document.partials.document-page')
@endsection
