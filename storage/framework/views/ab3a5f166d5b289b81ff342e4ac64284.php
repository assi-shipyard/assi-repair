<?php $__env->startSection('title', 'Data Permission'); ?>
<?php $__env->startSection('body_title', 'Data Permission'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('permission.create')); ?>" class="btn btn-primary">Tambah Permission</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead><tr><th>Nama Sistem</th><th>Nama Permission</th><th>Role</th><th class="w-1"></th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($permission->name); ?></td>
                            <td><?php echo e($permission->permission_name ?? '-'); ?></td>
                            <td><?php echo e($permission->roles_count ?? 0); ?></td>
                            <td class="text-end">
                                <a href="<?php echo e(route('permission.show', $permission->id)); ?>" class="btn btn-sm btn-outline-primary">Lihat</a>
                                <a href="<?php echo e(route('permission.edit', $permission->id)); ?>" class="btn btn-sm btn-outline-secondary">Ubah</a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="text-center text-secondary py-4">Belum ada data permission.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views\permission\index.blade.php ENDPATH**/ ?>