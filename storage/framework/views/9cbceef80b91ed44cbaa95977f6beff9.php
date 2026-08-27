<?php $__env->startSection('title', 'Data Permission'); ?>
<?php $__env->startSection('body_title', 'Data Permission'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('permission.create')); ?>" class="btn btn-primary"><i class="ti ti-key-plus me-1"></i>Tambah Permission</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card permission-summary mb-4">
        <div class="card-body p-4 d-flex flex-wrap align-items-center gap-3">
            <span class="avatar avatar-lg bg-azure-lt text-azure"><i class="ti ti-key fs-2"></i></span>
            <div class="me-auto"><div class="text-secondary small">Katalog Hak Akses</div><h2 class="mb-1"><?php echo e($permissions->count()); ?> Permission Terdaftar</h2><div class="text-secondary">Tetapkan izin ke role untuk menjaga akses setiap fitur tetap terkontrol.</div></div>
            <a href="<?php echo e(route('role.index')); ?>" class="btn btn-outline-secondary"><i class="ti ti-shield me-1"></i>Kelola Role</a>
        </div>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead><tr><th>Permission</th><th>Nama Sistem</th><th>Dipakai Role</th><th class="w-1"></th></tr></thead>
                <tbody>
                    <?php $__empty_-1 = true; $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_-1 = false; ?>
                        <tr>
                            <td><div class="fw-semibold"><?php echo e($permission->permission_name ?? $permission->name); ?></div><div class="text-secondary small"><?php echo e($permission->permission_description ?: 'Deskripsi belum diisi.'); ?></div></td>
                            <td><code><?php echo e($permission->name); ?></code></td>
                            <td><span class="badge bg-azure-lt text-azure"><?php echo e($permission->roles_count ?? 0); ?> role</span></td>
                            <td class="text-end">
                                <a href="<?php echo e(route('permission.assign-roles', $permission->id)); ?>" class="btn btn-sm btn-primary">Atur Role</a>
                                <a href="<?php echo e(route('permission.show', $permission->id)); ?>" class="btn btn-sm btn-outline-secondary" aria-label="Lihat <?php echo e($permission->name); ?>"><i class="ti ti-eye"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_-1): ?>
                        <tr><td colspan="4" class="text-center text-secondary py-4">Belum ada data permission.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <style>.permission-summary { border-top: 3px solid var(--tblr-azure); }</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/permission/index.blade.php ENDPATH**/ ?>