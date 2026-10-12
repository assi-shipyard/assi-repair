<?php $__env->startSection('title', 'Ubah Pengguna'); ?>
<?php $__env->startSection('body_title', 'Ubah Pengguna'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('employee.show', $employee->unique_id)); ?>" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1" aria-hidden="true"></i>Detail Pengguna</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $__env->make('employee.partials.form', ['is_edit' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/employee/edit.blade.php ENDPATH**/ ?>