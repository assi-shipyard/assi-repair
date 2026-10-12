<?php
    $status_labels = ['draft' => 'Draft', 'submitted' => 'Diajukan', 'approved' => 'Disetujui', 'locked' => 'Final', 'superseded' => 'Digantikan'];
    $status_classes = ['draft' => 'bg-secondary-lt text-secondary', 'submitted' => 'bg-warning-lt text-warning', 'approved' => 'bg-success-lt text-success', 'locked' => 'bg-success-lt text-success', 'superseded' => 'bg-secondary-lt text-secondary'];
    $document_ref = [$project->unique_id, $document->unique_id];
    $action_labels = ['created' => 'Dokumen dibuat', 'updated' => 'Dokumen diperbarui', 'copied' => 'Disalin dari revisi sebelumnya', 'revision_created' => 'Revisi dibuat', 'submitted' => 'Diajukan untuk negosiasi', 'returned_to_draft' => 'Dikembalikan ke draft', 'approved' => 'Disetujui', 'superseded' => 'Digantikan revisi baru', 'job_created' => 'Pekerjaan ditambahkan', 'job_updated' => 'Pekerjaan diperbarui', 'job_deleted' => 'Pekerjaan dihapus', 'material_created' => 'Material ditambahkan', 'material_updated' => 'Material diperbarui', 'material_deleted' => 'Material dihapus', 'photo_created' => 'Foto ditambahkan', 'photo_deleted' => 'Foto dihapus'];
    $steps = ['draft' => 'Draft', 'submitted' => 'Negosiasi', 'approved' => 'Disetujui'];
    $step_index = match ($document->status) { 'draft' => 0, 'submitted' => 1, default => 2 };
?>

<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <div class="text-secondary"><?php echo e($documentTypeLabels[$document->document_type] ?? $document->document_type); ?></div>
                <h2 class="mb-1">Revisi <?php echo e($document->revision_no); ?> <span class="badge <?php echo e($status_classes[$document->status] ?? 'bg-primary-lt'); ?> ms-1"><?php echo e($status_labels[$document->status] ?? $document->status); ?></span></h2>
                <div class="text-secondary"><?php echo e($project->project_code); ?> &middot; <?php echo e($project->ship?->name ?? '-'); ?> &middot; <?php echo e($document->document_number ?: 'Nomor dokumen belum diisi'); ?></div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <?php if($previous): ?>
                    <a href="<?php echo e(route('project.job-document.workflow.compare', $document_ref)); ?>" class="btn btn-outline-primary"><i class="ti ti-git-compare me-1"></i>Bandingkan dengan Rev. <?php echo e($previous->revision_no); ?></a>
                <?php endif; ?>
                <?php if($document->is_editable()): ?>
                    <form action="<?php echo e(route('project.job-document.workflow.submit', $document_ref)); ?>" method="POST" onsubmit="return confirm('Ajukan revisi ini untuk negosiasi? Revisi akan dikunci dari perubahan.')">
                        <?php echo csrf_field(); ?> <button class="btn btn-primary" type="submit"><i class="ti ti-send me-1"></i>Ajukan untuk Negosiasi</button>
                    </form>
                <?php endif; ?>
                <?php if($document->status === 'submitted' && $is_latest): ?>
                    <form action="<?php echo e(route('project.job-document.workflow.return-to-draft', $document_ref)); ?>" method="POST"><?php echo csrf_field(); ?> <button class="btn btn-outline-secondary" type="submit">Kembalikan ke Draft</button></form>
                    <?php if($can_approve): ?>
                        <form action="<?php echo e(route('project.job-document.workflow.approve', $document_ref)); ?>" method="POST" onsubmit="return confirm('Setujui revisi ini? Revisi sebelumnya akan ditandai Digantikan.')"><?php echo csrf_field(); ?> <button class="btn btn-success" type="submit"><i class="ti ti-check me-1"></i>Setujui</button></form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="row g-3 mt-2">
            <div class="col-md-5">
                <ul class="steps steps-green steps-counter my-0">
                    <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step_label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="step-item <?php echo e($loop->index <= $step_index ? ($loop->index === $step_index ? 'active' : '') : ''); ?>"><?php echo e($step_label); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
            <div class="col-md-7">
                <div class="row g-2">
                    <div class="col-4"><div class="text-secondary small">Pekerjaan</div><div class="fw-bold"><?php echo e($document->jobs->count()); ?></div></div>
                    <div class="col-8"><div class="text-secondary small">Total Harga Estimasi</div>
                        <div class="fw-bold"><?php $__empty_1 = true; $__currentLoopData = $totals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $currency => $amount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php echo e($currency); ?> <?php echo e(number_format($amount, 0, ',', '.')); ?><?php if(! $loop->last): ?> &middot; <?php endif; ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> - <?php endif; ?></div></div>
                    <div class="col-4"><div class="text-secondary small">Disiapkan</div><div><?php echo e($document->prepared_by?->name ?? '-'); ?></div></div>
                    <div class="col-4"><div class="text-secondary small">Diajukan</div><div><?php echo e($document->submitted_at ? $document->submitted_at->timezone('Asia/Jakarta')->tanggal_waktu_indonesia().' WIB' : '-'); ?></div></div>
                    <div class="col-4"><div class="text-secondary small">Disetujui</div><div><?php echo e($document->approved_at ? $document->approved_at->timezone('Asia/Jakarta')->tanggal_waktu_indonesia().' WIB' : '-'); ?></div></div>
                </div>
            </div>
        </div>

        <?php if($document->revision_reason): ?>
            <div class="alert alert-info mt-3 mb-0"><strong>Alasan revisi:</strong> <?php echo e($document->revision_reason); ?></div>
        <?php endif; ?>
        <?php if(! $document->is_editable()): ?>
            <div class="text-secondary small mt-3"><i class="ti ti-lock me-1"></i>Revisi ini tidak dapat diubah. Buat revisi baru dari halaman alur dokumen untuk melakukan perubahan.</div>
        <?php endif; ?>
    </div>
</div>

<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab_jobs" role="tab">Pekerjaan <span class="badge bg-secondary-lt ms-1"><?php echo e($document->jobs->count()); ?></span></a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab_changes" role="tab">Perubahan <?php if($comparison): ?><span class="badge bg-warning-lt text-warning ms-1"><?php echo e($comparison['counts']['changed'] + $comparison['counts']['added'] + $comparison['counts']['removed']); ?></span><?php endif; ?></a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab_revisions" role="tab">Revisi <span class="badge bg-secondary-lt ms-1"><?php echo e($revisions->count()); ?></span></a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab_history" role="tab">Riwayat</a></li>
</ul>

<div class="tab-content">
<div class="tab-pane active show" id="tab_jobs" role="tabpanel">
    <?php echo $__env->make('project.job-document.partials.boq-sheet', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>

<div class="tab-pane" id="tab_changes" role="tabpanel">
    <?php if($comparison): ?>
        <div class="text-secondary mb-3">Perubahan Revisi <?php echo e($document->revision_no); ?> dibandingkan Revisi <?php echo e($previous->revision_no); ?>.</div>
        <?php echo $__env->make('project.job-document.partials.diff-rows', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php else: ?>
        <div class="card"><div class="card-body text-center text-secondary py-5">Ini revisi pertama, belum ada pembanding.</div></div>
    <?php endif; ?>
</div>

<div class="tab-pane" id="tab_revisions" role="tabpanel">
    <div class="card"><div class="list-group list-group-flush">
        <?php $__currentLoopData = $revisions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $revision): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="list-group-item">
                <div class="row align-items-center g-2">
                    <div class="col-auto"><span class="avatar avatar-sm <?php echo e($revision->id === $document->id ? 'bg-primary text-white' : 'bg-primary-lt text-primary'); ?>">R<?php echo e($revision->revision_no); ?></span></div>
                    <div class="col">
                        <div class="fw-semibold">Revisi <?php echo e($revision->revision_no); ?> <span class="badge <?php echo e($status_classes[$revision->status] ?? 'bg-primary-lt'); ?>"><?php echo e($status_labels[$revision->status] ?? $revision->status); ?></span></div>
                        <div class="small text-secondary"><?php echo e($revision->revision_reason ?: 'Revisi awal'); ?> &middot; <?php echo e($revision->jobs_count); ?> pekerjaan</div>
                    </div>
                    <div class="col-auto d-flex gap-2">
                        <?php if($revision->id !== $document->id): ?>
                            <a class="btn btn-sm btn-outline-primary" href="<?php echo e(route('project.job-document.workflow.show', [$project->unique_id, $revision->unique_id])); ?>">Buka</a>
                            <a class="btn btn-sm btn-outline-secondary" href="<?php echo e(route('project.job-document.workflow.compare', [$project->unique_id, $document->unique_id, 'with' => $revision->unique_id])); ?>">Bandingkan</a>
                        <?php else: ?>
                            <span class="text-secondary small">Sedang dibuka</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div></div>
</div>

<div class="tab-pane" id="tab_history" role="tabpanel">
    <div class="card"><div class="card-body">
        <?php $__empty_1 = true; $__currentLoopData = $histories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $history): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="d-flex gap-3 pb-3 mb-3 border-bottom">
                <span class="avatar avatar-sm bg-primary-lt text-primary"><i class="ti ti-history"></i></span>
                <div>
                    <div class="fw-semibold"><?php echo e($action_labels[$history->action] ?? $history->action); ?><?php if(! empty($history->payload['job_name'])): ?>: <?php echo e($history->payload['job_name']); ?><?php endif; ?></div>
                    <?php if(! empty($history->payload['reason'])): ?><div class="small">Alasan: <?php echo e($history->payload['reason']); ?></div><?php endif; ?>
                    <div class="small text-secondary"><?php echo e($history->created_at?->timezone('Asia/Jakarta')->tanggal_waktu_indonesia()); ?> WIB &middot; <?php echo e($actor_names[$history->actor_id] ?? 'Sistem'); ?></div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="text-secondary text-center py-3">Belum ada riwayat.</div>
        <?php endif; ?>
    </div></div>
</div>
</div>
<?php /**PATH /home/snowy/projects/assi-repair/resources/views/project/job-document/partials/document-page.blade.php ENDPATH**/ ?>