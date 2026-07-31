@extends('layouts.app')

@section('title', 'Satisfaction Notes')
@section('body_title', 'Satisfaction Notes')

@section('buttons_beside_title')
    <a href="{{ route('project.job-document.workflow.index', $project->unique_id) }}" class="btn btn-outline-secondary">Kembali</a>
    <form action="{{ route('project.job-document.workflow.finalize-satisfaction-notes', [$project->unique_id, $document->id]) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-success">Finalisasi</button>
    </form>
@endsection

@section('content')
    @include('partials.flash')
    @include('project.job-document.partials.document-page')
@endsection
