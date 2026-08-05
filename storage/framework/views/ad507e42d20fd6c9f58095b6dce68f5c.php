<?php $__env->startSection('title', 'Data Role'); ?>
<?php $__env->startSection('body_title', 'Data Role'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('role.create')); ?>" class="btn btn-primary">Tambah Role</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead><tr><th>Nama Sistem</th><th>Nama Role</th><th>Permission</th><th class="w-1"></th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($role->name); ?></td>
                            <td><?php echo e($role->role_name ?? '-'); ?></td>
                            <td><?php echo e($role->permissions_count ?? 0); ?></td>
                            <td class="text-end">
                                <a href="<?php echo e(route('role.show', $role->id)); ?>" class="btn btn-sm btn-outline-primary">Lihat</a>
                                <a href="<?php echo e(route('role.edit', $role->id)); ?>" class="btn btn-sm btn-outline-secondary">Ubah</a>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views\role\index.blade.php ENDPATH**/ ?>