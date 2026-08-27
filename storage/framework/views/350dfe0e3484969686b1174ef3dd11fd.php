<?php $__env->startSection('title', 'Detail Permission'); ?>
<?php $__env->startSection('body_title', 'Detail Permission'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('permission.index')); ?>" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i>Kembali</a>
    <a href="<?php echo e(route('permission.edit', $permission->id)); ?>" class="btn btn-outline-secondary"><i class="ti ti-pencil me-1"></i>Ubah</a>
    <a href="<?php echo e(route('permission.assign-roles', $permission->id)); ?>" class="btn btn-primary"><i class="ti ti-shield me-1"></i>Atur Role</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card permission-detail-summary mb-4"><div class="card-body p-4 d-flex flex-wrap align-items-center gap-3"><span class="avatar avatar-lg bg-azure-lt text-azure"><i class="ti ti-key fs-2"></i></span><div class="me-auto"><div class="text-secondary small">Permission</div><h2 class="mb-1"><?php echo e($permission->permission_name ?? $permission->name); ?></h2><div class="text-secondary"><code><?php echo e($permission->name); ?></code></div></div><div><div class="text-secondary small">Role Terhubung</div><div class="h2 mb-0"><?php echo e($permission->roles->count()); ?></div></div></div></div>
    <div class="row row-cards"><div class="col-lg-5"><div class="card h-100"><div class="card-header"><h3 class="card-title">Deskripsi</h3></div><div class="card-body text-secondary"><?php echo e($permission->permission_description ?: 'Deskripsi permission belum diisi.'); ?></div></div></div><div class="col-lg-7"><div class="card h-100"><div class="card-header"><h3 class="card-title">Role yang Memiliki Akses</h3></div><div class="card-body"><div class="d-flex flex-wrap gap-2">
                <?php $__empty_-1 = true; $__currentLoopData = $permission->roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_-1 = false; ?>
                    <span class="badge bg-azure-lt text-azure"><?php echo e($role->role_name ?? $role->name); ?></span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_-1): ?>
                    <span class="text-secondary">Belum ada role yang menerima permission ini.</span>
                <?php endif; ?>
            </div></div></div></div></div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?><style>.permission-detail-summary { border-top: 3px solid var(--tblr-azure); }</style><?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/permission/show.blade.php ENDPATH**/ ?>