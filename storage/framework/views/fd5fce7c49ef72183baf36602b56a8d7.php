<?php $__env->startSection('title', 'Ubah Permission'); ?>
<?php $__env->startSection('body_title', 'Ubah Permission'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('permission.show', $permission->id)); ?>" class="btn btn-outline-secondary">Kembali</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <form action="<?php echo e(route('permission.update', $permission->id)); ?>" method="POST" class="card">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Nama Sistem</label><input class="form-control" name="name" value="<?php echo e(old('name', $permission->name)); ?>" required></div>
                <div class="col-md-6"><label class="form-label">Nama Permission</label><input class="form-control" name="permission_name" value="<?php echo e(old('permission_name', $permission->permission_name ?? '')); ?>" required></div>
                <div class="col-12"><label class="form-label">Deskripsi</label><textarea class="form-control" name="permission_description" rows="4"><?php echo e(old('permission_description', $permission->permission_description ?? '')); ?></textarea></div>
            </div>
        </div>
        <div class="card-footer text-end"><button class="btn btn-primary" type="submit">Perbarui</button></div>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views\permission\edit.blade.php ENDPATH**/ ?>