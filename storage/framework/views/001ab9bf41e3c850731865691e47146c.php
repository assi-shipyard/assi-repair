<?php $__env->startSection('title', 'Satisfaction Notes'); ?>
<?php $__env->startSection('body_title', 'Satisfaction Notes'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('project.job-document.workflow.index', $project->unique_id)); ?>" class="btn btn-outline-secondary">Kembali</a>
    <form action="<?php echo e(route('project.job-document.workflow.finalize-satisfaction-notes', [$project->unique_id, $document->unique_id ?? $document->id])); ?>" method="POST" class="d-inline">
        <?php echo csrf_field(); ?>
        <button type="submit" class="btn btn-success">Finalisasi</button>
    </form>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('project.job-document.partials.document-page', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views\satisfaction-notes\index.blade.php ENDPATH**/ ?>