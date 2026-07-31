<?php $__env->startSection('title', 'Detail Role'); ?>
<?php $__env->startSection('body_title', 'Detail Role'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('role.assign-permissions', $role->id)); ?>" class="btn btn-outline-primary">Atur Permission</a>
    <a href="<?php echo e(route('role.edit', $role->id)); ?>" class="btn btn-outline-secondary">Ubah</a>
    <a href="<?php echo e(route('role.index')); ?>" class="btn btn-outline-secondary">Kembali</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><div class="text-secondary">Nama Sistem</div><div><?php echo e($role->name); ?></div></div>
                <div class="col-md-6"><div class="text-secondary">Nama Role</div><div><?php echo e($role->role_name ?? '-'); ?></div></div>
                <div class="col-12"><div class="text-secondary">Deskripsi</div><div><?php echo e($role->role_description ?? '-'); ?></div></div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header"><h3 class="card-title mb-0">Permission</h3></div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                <?php $__empty_1 = true; $__currentLoopData = $role->permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <span class="badge bg-primary-lt"><?php echo e($permission->name); ?></span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <span class="text-secondary">Belum ada permission.</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views/role/show.blade.php ENDPATH**/ ?>