@php
    $status_labels = ['draft' => 'Draft', 'submitted' => 'Diajukan', 'approved' => 'Disetujui', 'locked' => 'Final', 'superseded' => 'Digantikan'];
    $status_classes = ['draft' => 'bg-secondary-lt text-secondary', 'submitted' => 'bg-warning-lt text-warning', 'approved' => 'bg-success-lt text-success', 'locked' => 'bg-success-lt text-success', 'superseded' => 'bg-secondary-lt text-secondary'];
    $document_ref = [$project->unique_id, $document->unique_id];
    $action_labels = ['created' => 'Dokumen dibuat', 'updated' => 'Dokumen diperbarui', 'copied' => 'Disalin dari revisi sebelumnya', 'revision_created' => 'Revisi dibuat', 'submitted' => 'Diajukan untuk negosiasi', 'returned_to_draft' => 'Dikembalikan ke draft', 'approved' => 'Disetujui', 'superseded' => 'Digantikan revisi baru', 'job_created' => 'Pekerjaan ditambahkan', 'job_updated' => 'Pekerjaan diperbarui', 'job_deleted' => 'Pekerjaan dihapus', 'material_created' => 'Material ditambahkan', 'material_updated' => 'Material diperbarui', 'material_deleted' => 'Material dihapus', 'photo_created' => 'Foto ditambahkan', 'photo_deleted' => 'Foto dihapus', 'minimum_override_set' => 'Minimum hasil negosiasi diatur', 'minimum_override_cleared' => 'Minimum dikembalikan ke Kamus Harga', 'progress_report_created' => 'Laporan progres harian dibuat', 'progress_report_saved' => 'Progres harian disimpan', 'progress_report_submitted' => 'Laporan progres diajukan', 'progress_report_signed' => 'Laporan progres ditandatangani', 'progress_report_approved' => 'Laporan progres disetujui', 'progress_report_returned' => 'Laporan progres dikembalikan', 'progress_report_deleted' => 'Laporan progres dihapus'];
    $has_progress_tab = $document->features()['progress_reports'] ?? false;
    $steps = ['draft' => 'Draft', 'submitted' => 'Negosiasi', 'approved' => 'Disetujui'];
    $step_index = match ($document->status) { 'draft' => 0, 'submitted' => 1, default => 2 };
@endphp

<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <div class="text-secondary">{{ $documentTypeLabels[$document->document_type] ?? $document->document_type }}</div>
                <h2 class="mb-1">Revisi {{ $document->revision_no }} <span class="badge {{ $status_classes[$document->status] ?? 'bg-primary-lt' }} ms-1">{{ $status_labels[$document->status] ?? $document->status }}</span></h2>
                <div class="text-secondary">{{ $project->project_code }} &middot; {{ $project->ship?->name ?? '-' }} &middot; {{ $document->document_number ?: 'Nomor dokumen belum diisi' }}</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if ($previous)
                    <a href="{{ route('project.job-document.workflow.compare', $document_ref) }}" class="btn btn-outline-primary"><i class="ti ti-git-compare me-1"></i>Bandingkan dengan Rev. {{ $previous->revision_no }}</a>
                @endif
                @if ($document->is_editable())
                    <form action="{{ route('project.job-document.workflow.submit', $document_ref) }}" method="POST" onsubmit="return confirm('Ajukan revisi ini untuk negosiasi? Revisi akan dikunci dari perubahan.')">
                        @csrf <button class="btn btn-primary" type="submit"><i class="ti ti-send me-1"></i>Ajukan untuk Negosiasi</button>
                    </form>
                @endif
                @if ($document->status === 'submitted' && $is_latest)
                    <form action="{{ route('project.job-document.workflow.return-to-draft', $document_ref) }}" method="POST">@csrf <button class="btn btn-outline-secondary" type="submit">Kembalikan ke Draft</button></form>
                    @if ($can_approve)
                        <form action="{{ route('project.job-document.workflow.approve', $document_ref) }}" method="POST" onsubmit="return confirm('Setujui revisi ini? Revisi sebelumnya akan ditandai Digantikan.')">@csrf <button class="btn btn-success" type="submit"><i class="ti ti-check me-1"></i>Setujui</button></form>
                    @endif
                @endif
            </div>
        </div>

        <div class="row g-3 mt-2">
            <div class="col-md-5">
                <ul class="steps steps-green steps-counter my-0">
                    @foreach ($steps as $step_label)
                        <li class="step-item {{ $loop->index <= $step_index ? ($loop->index === $step_index ? 'active' : '') : '' }}">{{ $step_label }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="col-md-7">
                <div class="row g-2">
                    <div class="col-4"><div class="text-secondary small">Pekerjaan</div><div class="fw-bold">{{ $document->jobs->count() }}</div></div>
                    <div class="col-8"><div class="text-secondary small">Total Harga Estimasi</div>
                        <div class="fw-bold">@forelse ($totals as $currency => $amount){{ $currency }} {{ number_format($amount, 0, ',', '.') }}@if (! $loop->last) &middot; @endif @empty - @endforelse</div></div>
                    <div class="col-4"><div class="text-secondary small">Disiapkan</div><div>{{ $document->prepared_by?->name ?? '-' }}</div></div>
                    <div class="col-4"><div class="text-secondary small">Diajukan</div><div>{{ $document->submitted_at ? $document->submitted_at->timezone('Asia/Jakarta')->tanggal_waktu_indonesia().' WIB' : '-' }}</div></div>
                    <div class="col-4"><div class="text-secondary small">Disetujui</div><div>{{ $document->approved_at ? $document->approved_at->timezone('Asia/Jakarta')->tanggal_waktu_indonesia().' WIB' : '-' }}</div></div>
                </div>
            </div>
        </div>

        @if ($document->revision_reason)
            <div class="alert alert-info mt-3 mb-0"><strong>Alasan revisi:</strong> {{ $document->revision_reason }}</div>
        @endif
        @if (! $document->is_editable())
            <div class="text-secondary small mt-3"><i class="ti ti-lock me-1"></i>Revisi ini tidak dapat diubah. Buat revisi baru dari halaman alur dokumen untuk melakukan perubahan.</div>
        @endif
    </div>
</div>

<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab_jobs" role="tab">Pekerjaan <span class="badge bg-secondary-lt ms-1">{{ $document->jobs->count() }}</span></a></li>
    @if ($has_progress_tab)
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab_progress" role="tab">Progres Harian @if ($has_open_report)<span class="badge bg-warning-lt text-warning ms-1">1</span>@endif</a></li>
    @endif
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab_changes" role="tab">Perubahan @if ($comparison)<span class="badge bg-warning-lt text-warning ms-1">{{ $comparison['counts']['changed'] + $comparison['counts']['added'] + $comparison['counts']['removed'] }}</span>@endif</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab_revisions" role="tab">Revisi <span class="badge bg-secondary-lt ms-1">{{ $revisions->count() }}</span></a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab_history" role="tab">Riwayat</a></li>
</ul>

<div class="tab-content">
<div class="tab-pane active show" id="tab_jobs" role="tabpanel">
    @include('project.job-document.partials.boq-sheet')
</div>

@if ($has_progress_tab)
<div class="tab-pane" id="tab_progress" role="tabpanel">
    @include('project.job-document.partials.progress-reports')
</div>
@endif

<div class="tab-pane" id="tab_changes" role="tabpanel">
    @if ($comparison)
        <div class="text-secondary mb-3">Perubahan Revisi {{ $document->revision_no }} dibandingkan Revisi {{ $previous->revision_no }}.</div>
        @include('project.job-document.partials.diff-rows')
    @else
        <div class="card"><div class="card-body text-center text-secondary py-5">Ini revisi pertama, belum ada pembanding.</div></div>
    @endif
</div>

<div class="tab-pane" id="tab_revisions" role="tabpanel">
    <div class="card"><div class="list-group list-group-flush">
        @foreach ($revisions as $revision)
            <div class="list-group-item">
                <div class="row align-items-center g-2">
                    <div class="col-auto"><span class="avatar avatar-sm {{ $revision->id === $document->id ? 'bg-primary text-white' : 'bg-primary-lt text-primary' }}">R{{ $revision->revision_no }}</span></div>
                    <div class="col">
                        <div class="fw-semibold">Revisi {{ $revision->revision_no }} <span class="badge {{ $status_classes[$revision->status] ?? 'bg-primary-lt' }}">{{ $status_labels[$revision->status] ?? $revision->status }}</span></div>
                        <div class="small text-secondary">{{ $revision->revision_reason ?: 'Revisi awal' }} &middot; {{ $revision->jobs_count }} pekerjaan</div>
                    </div>
                    <div class="col-auto d-flex gap-2">
                        @if ($revision->id !== $document->id)
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('project.job-document.workflow.show', [$project->unique_id, $revision->unique_id]) }}">Buka</a>
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('project.job-document.workflow.compare', [$project->unique_id, $document->unique_id, 'with' => $revision->unique_id]) }}">Bandingkan</a>
                        @else
                            <span class="text-secondary small">Sedang dibuka</span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div></div>
</div>

<div class="tab-pane" id="tab_history" role="tabpanel">
    <div class="card"><div class="card-body">
        @forelse ($histories as $history)
            <div class="d-flex gap-3 pb-3 mb-3 border-bottom">
                <span class="avatar avatar-sm bg-primary-lt text-primary"><i class="ti ti-history"></i></span>
                <div>
                    <div class="fw-semibold">{{ $action_labels[$history->action] ?? $history->action }}@if (! empty($history->payload['job_name'])): {{ $history->payload['job_name'] }}@endif @if (! empty($history->payload['report_date'])): {{ \Carbon\Carbon::parse($history->payload['report_date'])->tanggal_indonesia() }}@endif</div>
                    @if (! empty($history->payload['reason']))<div class="small">Alasan: {{ $history->payload['reason'] }}</div>@endif
                    <div class="small text-secondary">{{ $history->created_at?->timezone('Asia/Jakarta')->tanggal_waktu_indonesia() }} WIB &middot; {{ $actor_names[$history->actor_id] ?? 'Sistem' }}</div>
                </div>
            </div>
        @empty
            <div class="text-secondary text-center py-3">Belum ada riwayat.</div>
        @endforelse
    </div></div>
</div>
</div>
