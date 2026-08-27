@extends('layouts.app')

@section('title', 'Dokumen Proyek')
@section('body_title', 'Dokumen Proyek')

@section('buttons_beside_title')
    <a href="{{ route('project.show', $project->unique_id) }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i>Kembali ke Proyek
    </a>
@endsection

@section('content')
    @include('partials.flash')

    @php
        $stage_types = array_keys($documentTypeLabels);
        $document_status_classes = [
            'approved' => 'bg-success-lt text-success',
            'locked' => 'bg-success-lt text-success',
            'draft' => 'bg-secondary-lt text-secondary',
        ];
    @endphp

    <div class="card workflow-project-summary mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center g-3">
                <div class="col-auto d-none d-sm-block"><span class="avatar avatar-lg bg-primary-lt text-primary"><i class="ti ti-ship fs-2"></i></span></div>
                <div class="col">
                    <div class="text-secondary small mb-1">Dokumen Proyek</div>
                    <h2 class="mb-1">{{ $project->ship?->name ?? 'Kapal belum ditentukan' }}</h2>
                    <div class="text-secondary"><span class="font-monospace">{{ $project->project_code }}</span> &bull; {{ $project->project_type ?? '-' }}</div>
                </div>
                <div class="col-12 col-sm-auto d-flex gap-4">
                    <div><div class="text-secondary small">Total Dokumen</div><div class="h2 mb-0">{{ $documents->count() }}</div></div>
                    <div><div class="text-secondary small">Status Proyek</div><div class="fw-semibold">{{ $project->status ?? '-' }}</div></div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
        <div>
            <h2 class="mb-1">Tahapan Dokumen</h2>
            <div class="text-secondary">Buka revisi terakhir atau buat revisi baru dari versi aktif di setiap tahap.</div>
        </div>
        <span class="text-secondary small"><i class="ti ti-git-branch me-1"></i>Revisi baru menyalin pekerjaan dan material versi sumber.</span>
    </div>

    <div class="row row-cards mb-4">
        @foreach ($stage_types as $document_type)
            @php
                $stage_documents = $documentsByType->get($document_type, collect());
                $latest_document = $stage_documents->first();
                $stage_is_ready = (bool) ($sop[$document_type]['ready'] ?? false);
                $stage_is_available = $latest_document !== null || $stage_is_ready;
                $status_class = $latest_document ? ($document_status_classes[$latest_document->status] ?? 'bg-primary-lt text-primary') : 'bg-secondary-lt text-secondary';
            @endphp
            <div class="col-md-6 col-xl">
                <div class="card h-100 workflow-stage {{ $stage_is_available ? '' : 'workflow-stage-locked' }}">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                            <div><div class="text-secondary small">Tahap {{ $loop->iteration }}</div><h3 class="card-title mb-1">{{ $documentTypeLabels[$document_type] }}</h3></div>
                            <span class="avatar avatar-sm {{ $stage_is_available ? 'bg-primary-lt text-primary' : 'bg-secondary-lt text-secondary' }}"><i class="ti {{ $stage_is_available ? 'ti-file-description' : 'ti-lock' }}"></i></span>
                        </div>
                        <p class="text-secondary small mb-3">{{ $sop[$document_type]['description'] ?? '-' }}</p>

                        @if ($latest_document)
                            <div class="stage-document-meta mb-3">
                                <div class="d-flex justify-content-between gap-2"><span class="badge {{ $status_class }}">{{ ucfirst($latest_document->status) }}</span><span class="text-secondary small">{{ $stage_documents->count() }} revisi</span></div>
                                <div class="fw-semibold mt-2">Revisi {{ $latest_document->revision_no }}</div>
                                <div class="small text-secondary text-truncate">{{ $latest_document->document_number ?: 'Nomor dokumen belum diisi' }}</div>
                            </div>
                            <a href="{{ route('project.job-document.workflow.show', [$project->unique_id, $latest_document->unique_id ?? $latest_document->id]) }}" class="btn btn-outline-primary w-100 mb-2">Buka Revisi Terakhir<i class="ti ti-arrow-right ms-1"></i></a>
                            <button type="button" class="btn btn-primary w-100 document-action" data-bs-toggle="modal" data-bs-target="#document-modal" data-document-type="{{ $document_type }}" data-document-label="{{ $documentTypeLabels[$document_type] }}" data-source-id="{{ $latest_document->id }}" data-source-revision="{{ $latest_document->revision_no }}"><i class="ti ti-copy-plus me-1"></i>Buat Revisi</button>
                        @elseif ($stage_is_ready)
                            <div class="stage-empty text-secondary small mb-3">Belum ada dokumen untuk tahap ini.</div>
                            <button type="button" class="btn btn-primary w-100 mt-auto document-action" data-bs-toggle="modal" data-bs-target="#document-modal" data-document-type="{{ $document_type }}" data-document-label="{{ $documentTypeLabels[$document_type] }}"><i class="ti ti-file-plus me-1"></i>Buat Dokumen</button>
                        @else
                            <div class="stage-empty text-secondary small mb-3">Tahap ini terbuka setelah tahap sebelumnya tersedia.</div>
                            <button type="button" class="btn btn-outline-secondary w-100 mt-auto" disabled><i class="ti ti-lock me-1"></i>Belum Tersedia</button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-header"><div><h3 class="card-title mb-1">Riwayat Revisi per Tahap</h3><div class="text-secondary small">Tinjau versi terdahulu dari setiap tahap tanpa kehilangan konteks proses proyek.</div></div></div>
        <div class="card-body p-0">
            <div class="accordion accordion-flush" id="revision-history">
                @foreach ($stage_types as $document_type)
                    @php($stage_documents = $documentsByType->get($document_type, collect()))
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="revision-heading-{{ $document_type }}"><button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#revision-collapse-{{ $document_type }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}"><span class="fw-semibold">{{ $documentTypeLabels[$document_type] }}</span><span class="badge bg-secondary-lt text-secondary ms-2">{{ $stage_documents->count() }} revisi</span></button></h2>
                        <div id="revision-collapse-{{ $document_type }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#revision-history">
                            <div class="accordion-body p-0">
                                @forelse ($stage_documents as $workflow_document)
                                    @php($status_class = $document_status_classes[$workflow_document->status] ?? 'bg-primary-lt text-primary')
                                    <a href="{{ route('project.job-document.workflow.show', [$project->unique_id, $workflow_document->unique_id ?? $workflow_document->id]) }}" class="revision-row list-group-item list-group-item-action">
                                        <div class="row align-items-center g-2">
                                            <div class="col-auto"><span class="avatar avatar-sm bg-primary-lt text-primary">R{{ $workflow_document->revision_no }}</span></div>
                                            <div class="col"><div class="fw-semibold">Revisi {{ $workflow_document->revision_no }}</div><div class="small text-secondary">{{ $workflow_document->document_number ?: 'Nomor dokumen belum diisi' }}</div></div>
                                            <div class="col-auto text-secondary small d-none d-md-block">{{ $workflow_document->jobs_count }} pekerjaan</div>
                                            <div class="col-auto"><span class="badge {{ $status_class }}">{{ ucfirst($workflow_document->status) }}</span></div>
                                            <div class="col-auto"><i class="ti ti-chevron-right text-secondary"></i></div>
                                        </div>
                                    </a>
                                @empty
                                    <div class="text-secondary small px-3 py-4">Belum ada revisi untuk tahap ini.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="modal modal-blur fade" id="document-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="document-form" class="modal-content" action="{{ route('project.job-document.workflow.store', $project->unique_id) }}" method="POST">
                @csrf
                <input type="hidden" name="document_type" id="document-type-input">
                <input type="hidden" name="source_document_id" id="source-document-input">
                <div class="modal-header"><div><h3 class="modal-title" id="document-modal-title">Buat Dokumen</h3><div class="text-secondary small" id="document-modal-description"></div></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label" for="document-number-input">Nomor Dokumen</label><input id="document-number-input" class="form-control" name="document_number" maxlength="100" value="{{ old('document_number') }}" placeholder="Contoh: RL/001/VIII/2026"></div>
                    <div><label class="form-label" for="document-notes-input">Catatan</label><textarea id="document-notes-input" class="form-control" name="notes" rows="3" placeholder="Catatan untuk revisi atau dokumen ini">{{ old('notes') }}</textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary" id="document-submit-button">Buat Dokumen</button></div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .workflow-project-summary { border-top: 3px solid var(--tblr-primary); }
        .workflow-stage { min-height: 280px; }
        .workflow-stage-locked { background-color: var(--tblr-bg-surface-secondary); }
        .stage-document-meta, .stage-empty { min-height: 76px; }
        .revision-row { border-left: 0; border-right: 0; }
    </style>
@endpush

@push('scripts')
    <script>
        document.querySelectorAll('.document-action').forEach(function (button) {
            button.addEventListener('click', function () {
                var source_id = this.dataset.sourceId || '';
                var is_revision = source_id !== '';
                document.getElementById('document-type-input').value = this.dataset.documentType;
                document.getElementById('source-document-input').value = source_id;
                document.getElementById('document-modal-title').textContent = (is_revision ? 'Buat Revisi' : 'Buat Dokumen') + ' - ' + this.dataset.documentLabel;
                document.getElementById('document-modal-description').textContent = is_revision ? 'Revisi baru akan menyalin data dari Revisi ' + this.dataset.sourceRevision + '.' : 'Dokumen baru akan dibuat untuk tahap ini.';
                document.getElementById('document-submit-button').textContent = is_revision ? 'Buat Revisi' : 'Buat Dokumen';
            });
        });

        if (window.jQuery && jQuery.fn.validate) {
            jQuery('#document-form').validate({ rules: { document_number: { maxlength: 100 }, notes: { maxlength: 65535 } } });
        }
    </script>
@endpush
