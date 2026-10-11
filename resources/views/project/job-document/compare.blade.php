@extends('layouts.app')

@section('title', 'Perbandingan Revisi')
@section('body_title', 'Perbandingan Revisi')

@section('buttons_beside_title')
    <a href="{{ route('project.job-document.workflow.show', [$project->unique_id, $document->unique_id]) }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i>Kembali ke Dokumen
    </a>
@endsection

@section('content')
    @include('partials.flash')

    <div class="card mb-3">
        <div class="card-body">
            <div class="text-secondary small">{{ $documentTypeLabels[$document->document_type] ?? $document->document_type }} &middot; {{ $project->project_code }} &middot; {{ $project->ship?->name ?? '-' }}</div>
            <form method="GET" class="row g-2 align-items-end mt-1">
                <div class="col-md-4">
                    <label class="form-label">Revisi Pembanding</label>
                    <select class="form-select" name="with" onchange="this.form.submit()">
                        @foreach ($revisions->where('id', '!=', $document->id) as $revision)
                            <option value="{{ $revision->unique_id }}" @selected($revision->id === ($base->id === $document->id ? $target->id : $base->id))>Revisi {{ $revision->revision_no }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <div class="fw-semibold fs-3">Rev. {{ $base->revision_no }} <i class="ti ti-arrow-right text-secondary"></i> Rev. {{ $target->revision_no }}</div>
                </div>
            </form>
            @if ($target->revision_reason)
                <div class="alert alert-info mt-3 mb-0"><strong>Alasan Revisi {{ $target->revision_no }}:</strong> {{ $target->revision_reason }}</div>
            @endif
        </div>
    </div>

    @include('project.job-document.partials.diff-rows', ['show_unchanged' => true])
@endsection
