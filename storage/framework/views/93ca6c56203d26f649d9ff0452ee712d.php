<?php $__env->startSection('title', 'Detail Karyawan'); ?>
<?php $__env->startSection('body_title', 'Detail Karyawan'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('employee.edit', $employee->id)); ?>" class="btn btn-outline-primary">Ubah</a>
    <a href="<?php echo e(route('employee.index')); ?>" class="btn btn-outline-secondary">Kembali</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="row row-cards">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="avatar avatar-xl mb-3" style="background-image: url('<?php echo e(route('employee.photo', $employee->id)); ?>');"></div>
                    <h3 class="mb-1"><?php echo e($employee->name); ?></h3>
                    <div class="text-secondary"><?php echo e($employee->employee_id); ?></div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><div class="text-secondary">Email</div><div><?php echo e($employee->email ?? '-'); ?></div></div>
                        <div class="col-md-6"><div class="text-secondary">Status</div><div><?php echo e($employee->status); ?></div></div>
                        <div class="col-md-6"><div class="text-secondary">Jabatan</div><div><?php echo e($employee->position?->name ?? '-'); ?></div></div>
                        <div class="col-md-6"><div class="text-secondary">Unit Organisasi</div><div><?php echo e($employee->position?->organizational_unit?->name ?? '-'); ?></div></div>
                        <div class="col-md-6"><div class="text-secondary">Manajer</div><div><?php echo e($employee->manager?->name ?? '-'); ?></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views/employee/show.blade.php ENDPATH**/ ?>