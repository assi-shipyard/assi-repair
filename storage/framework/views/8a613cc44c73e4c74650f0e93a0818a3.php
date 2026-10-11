<?php $__env->startSection('title', 'Data Role'); ?>
<?php $__env->startSection('body_title', 'Data Role'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('role.create')); ?>" class="btn btn-primary"><i class="ti ti-shield-plus me-1"></i>Tambah Role</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="card role-summary mb-4">
        <div class="card-body p-4 d-flex flex-wrap align-items-center gap-3">
            <span class="avatar avatar-lg bg-primary-lt text-primary"><i class="ti ti-shield fs-2"></i></span>
            <div class="me-auto"><div class="text-secondary small">Akses Berbasis Peran</div><h2 class="mb-1"><?php echo e($roles->count()); ?> Role Terdaftar</h2><div class="text-secondary">Kelola peran dan hak akses yang digunakan pengguna aplikasi.</div></div>
            <a href="<?php echo e(route('permission.index')); ?>" class="btn btn-outline-secondary"><i class="ti ti-key me-1"></i>Kelola Permission</a>
        </div>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead><tr><th>Role</th><th>Nama Sistem</th><th>Hak Akses</th><th class="w-1"></th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><div class="fw-semibold"><?php echo e($role->role_name ?? $role->name); ?></div><div class="text-secondary small"><?php echo e($role->role_description ?: 'Deskripsi belum diisi.'); ?></div></td>
                            <td><code><?php echo e($role->name); ?></code></td>
                            <td><span class="badge bg-primary-lt text-primary"><?php echo e($role->permissions_count ?? 0); ?> permission</span></td>
                            <td class="text-end">
                                <a href="<?php echo e(route('role.assign-permissions', $role->id)); ?>" class="btn btn-sm btn-primary">Atur Akses</a>
                                <a href="<?php echo e(route('role.show', $role->id)); ?>" class="btn btn-sm btn-outline-secondary" aria-label="Lihat <?php echo e($role->name); ?>"><i class="ti ti-eye"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="text-center text-secondary py-4">Belum ada data role.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <style>.role-summary { border-top: 3px solid var(--tblr-primary); }</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/role/index.blade.php ENDPATH**/ ?>