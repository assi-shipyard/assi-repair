<?php $__env->startSection('title', 'Workflow Dokumen Proyek'); ?>
<?php $__env->startSection('body_title', 'Workflow Dokumen Proyek'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('project.show', $project->unique_id)); ?>" class="btn btn-outline-secondary">Kembali</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><div class="text-secondary">Proyek</div><div><?php echo e($project->project_code); ?> - <?php echo e($project->ship?->name ?? '-'); ?></div></div>
                <div class="col-md-6"><div class="text-secondary">Status Proyek</div><div><?php echo e($project->status ?? '-'); ?></div></div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><h3 class="card-title mb-0">Buat Dokumen Baru</h3></div>
        <div class="card-body">
            <form action="<?php echo e(route('project.job-document.workflow.store', $project->unique_id)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Tipe Dokumen</label><select class="form-select" name="document_type" required><option value="">Pilih tipe</option><?php $__currentLoopData = $documentTypeLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $documentType => $documentLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($documentType); ?>" <?php if(! ($sop[$documentType]['ready'] ?? false)): echo 'disabled'; endif; ?>><?php echo e($documentLabel); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                    <div class="col-md-4"><label class="form-label">Nomor Dokumen</label><input class="form-control" name="document_number"></div>
                    <div class="col-md-4"><label class="form-label">Sumber Dokumen</label><select class="form-select" name="source_document_id"><option value="">Tanpa sumber</option><?php $__currentLoopData = $documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $workflow_document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($workflow_document->id); ?>"><?php echo e($documentTypeLabels[$workflow_document->document_type] ?? $workflow_document->document_type); ?> #<?php echo e($workflow_document->revision_no); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                    <div class="col-12"><label class="form-label">Catatan</label><textarea class="form-control" name="notes" rows="3"></textarea></div>
                    <div class="col-12 text-end"><button class="btn btn-primary" type="submit">Buat Dokumen</button></div>
                </div>
            </form>
        </div>
    </div>

    <div class="row row-cards">
        <?php $__empty_1 = true; $__currentLoopData = $documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $workflow_document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="col-md-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="text-secondary small"><?php echo e($documentTypeLabels[$workflow_document->document_type] ?? $workflow_document->document_type); ?></div>
                                <h3 class="mb-1">Revisi <?php echo e($workflow_document->revision_no); ?></h3>
                            </div>
                            <span class="badge bg-primary-lt"><?php echo e($workflow_document->status); ?></span>
                        </div>
                        <div class="text-secondary mb-3"><?php echo e($workflow_document->document_number ?? '-'); ?></div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="<?php echo e(route('project.job-document.workflow.show', [$project->unique_id, $workflow_document->unique_id ?? $workflow_document->id])); ?>" class="btn btn-sm btn-outline-primary">Buka</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-12"><div class="card"><div class="card-body text-center text-secondary py-5">Belum ada dokumen pekerjaan.</div></div></div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views\project\job-document\workflow.blade.php ENDPATH**/ ?>