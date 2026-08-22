@extends('layouts.app')

@section('title', 'Workflow Dokumen Proyek')
@section('body_title', 'Workflow Dokumen Proyek')

@section('buttons_beside_title')
    <a href="{{ route('project.show', $project->unique_id) }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
    @include('partials.flash')

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><div class="text-secondary">Proyek</div><div>{{ $project->project_code }} - {{ $project->ship?->name ?? '-' }}</div></div>
                <div class="col-md-6"><div class="text-secondary">Status Proyek</div><div>{{ $project->status ?? '-' }}</div></div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><h3 class="card-title mb-0">Buat Dokumen Baru</h3></div>
        <div class="card-body">
            <form action="{{ route('project.job-document.workflow.store', $project->unique_id) }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Tipe Dokumen</label><select class="form-select" name="document_type" required><option value="">Pilih tipe</option>@foreach ($documentTypeLabels as $documentType => $documentLabel)<option value="{{ $documentType }}" @disabled(! ($sop[$documentType]['ready'] ?? false))>{{ $documentLabel }}</option>@endforeach</select></div>
                    <div class="col-md-4"><label class="form-label">Nomor Dokumen</label><input class="form-control" name="document_number"></div>
                    <div class="col-md-4"><label class="form-label">Sumber Dokumen</label><select class="form-select" name="source_document_id"><option value="">Tanpa sumber</option>@foreach ($documents as $workflow_document)<option value="{{ $workflow_document->id }}">{{ $documentTypeLabels[$workflow_document->document_type] ?? $workflow_document->document_type }} #{{ $workflow_document->revision_no }}</option>@endforeach</select></div>
                    <div class="col-12"><label class="form-label">Catatan</label><textarea class="form-control" name="notes" rows="3"></textarea></div>
                    <div class="col-12 text-end"><button class="btn btn-primary" type="submit">Buat Dokumen</button></div>
                </div>
            </form>
        </div>
    </div>

    <div class="row row-cards">
        @forelse ($documents as $workflow_document)
            <div class="col-md-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="text-secondary small">{{ $documentTypeLabels[$workflow_document->document_type] ?? $workflow_document->document_type }}</div>
                                <h3 class="mb-1">Revisi {{ $workflow_document->revision_no }}</h3>
                            </div>
                            <span class="badge bg-primary-lt">{{ $workflow_document->status }}</span>
                        </div>
                        <div class="text-secondary mb-3">{{ $workflow_document->document_number ?? '-' }}</div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('project.job-document.workflow.show', [$project->unique_id, $workflow_document->unique_id ?? $workflow_document->id]) }}" class="btn btn-sm btn-outline-primary">Buka</a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12"><div class="card"><div class="card-body text-center text-secondary py-5">Belum ada dokumen pekerjaan.</div></div></div>
        @endforelse
    </div>
@endsection
