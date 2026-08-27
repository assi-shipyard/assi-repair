<?php $__env->startSection('title', 'Detail Role'); ?>
<?php $__env->startSection('body_title', 'Detail Role'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('role.index')); ?>" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i>Kembali</a>
    <a href="<?php echo e(route('role.edit', $role->id)); ?>" class="btn btn-outline-secondary"><i class="ti ti-pencil me-1"></i>Ubah</a>
    <a href="<?php echo e(route('role.assign-permissions', $role->id)); ?>" class="btn btn-primary"><i class="ti ti-key me-1"></i>Atur Permission</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card role-detail-summary mb-4"><div class="card-body p-4 d-flex flex-wrap align-items-center gap-3"><span class="avatar avatar-lg bg-primary-lt text-primary"><i class="ti ti-shield fs-2"></i></span><div class="me-auto"><div class="text-secondary small">Role</div><h2 class="mb-1"><?php echo e($role->role_name ?? $role->name); ?></h2><div class="text-secondary"><code><?php echo e($role->name); ?></code></div></div><div><div class="text-secondary small">Permission Aktif</div><div class="h2 mb-0"><?php echo e($role->permissions->count()); ?></div></div></div></div>
    <div class="row row-cards"><div class="col-lg-5"><div class="card h-100"><div class="card-header"><h3 class="card-title">Deskripsi</h3></div><div class="card-body text-secondary"><?php echo e($role->role_description ?: 'Deskripsi role belum diisi.'); ?></div></div></div><div class="col-lg-7"><div class="card h-100"><div class="card-header"><h3 class="card-title">Permission yang Ditetapkan</h3></div><div class="card-body"><div class="d-flex flex-wrap gap-2">
                <?php $__empty_0 = true; $__currentLoopData = $role->permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?>
                    <span class="badge bg-primary-lt text-primary"><?php echo e($permission->permission_name ?? $permission->name); ?></span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?>
                    <span class="text-secondary">Belum ada permission yang ditetapkan.</span>
                <?php endif; ?>
            </div></div></div></div></div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?><style>.role-detail-summary { border-top: 3px solid var(--tblr-primary); }</style><?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/role/show.blade.php ENDPATH**/ ?>