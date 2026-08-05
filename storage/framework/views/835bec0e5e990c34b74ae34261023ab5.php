<?php $__env->startSection('title', $page_title ?? 'Halaman'); ?>
<?php $__env->startSection('body_title', $body_title ?? 'Halaman'); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card">
        <div class="card-body text-center py-5">
            <h3 class="mb-2"><?php echo e($body_title ?? $page_title ?? 'Halaman'); ?></h3>
            <p class="text-secondary mb-0"><?php echo e($message ?? 'Halaman ini belum diaktifkan.'); ?></p>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views\examples\placeholder.blade.php ENDPATH**/ ?>