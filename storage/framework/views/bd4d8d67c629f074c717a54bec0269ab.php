<?php $__env->startSection('title', 'Perbandingan Revisi'); ?>
<?php $__env->startSection('body_title', 'Perbandingan Revisi'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('project.job-document.workflow.show', [$project->unique_id, $document->unique_id])); ?>" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i>Kembali ke Dokumen
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card mb-3">
        <div class="card-body">
            <div class="text-secondary small"><?php echo e($documentTypeLabels[$document->document_type] ?? $document->document_type); ?> &middot; <?php echo e($project->project_code); ?> &middot; <?php echo e($project->ship?->name ?? '-'); ?></div>
            <form method="GET" class="row g-2 align-items-end mt-1">
                <div class="col-md-4">
                    <label class="form-label">Revisi Pembanding</label>
                    <select class="form-select" name="with" onchange="this.form.submit()">
                        <?php $__currentLoopData = $revisions->where('id', '!=', $document->id); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $revision): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($revision->unique_id); ?>" <?php if($revision->id === ($base->id === $document->id ? $target->id : $base->id)): echo 'selected'; endif; ?>>Revisi <?php echo e($revision->revision_no); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-auto">
                    <div class="fw-semibold fs-3">Rev. <?php echo e($base->revision_no); ?> <i class="ti ti-arrow-right text-secondary"></i> Rev. <?php echo e($target->revision_no); ?></div>
                </div>
            </form>
            <?php if($target->revision_reason): ?>
                <div class="alert alert-info mt-3 mb-0"><strong>Alasan Revisi <?php echo e($target->revision_no); ?>:</strong> <?php echo e($target->revision_reason); ?></div>
            <?php endif; ?>
        </div>
    </div>

    <?php echo $__env->make('project.job-document.partials.diff-rows', ['show_unchanged' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/project/job-document/compare.blade.php ENDPATH**/ ?>