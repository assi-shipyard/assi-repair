<?php $__env->startSection('title', 'Atur Role Permission'); ?>
<?php $__env->startSection('body_title', 'Atur Role Permission'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('permission.show', $permission->id)); ?>" class="btn btn-outline-secondary">Kembali</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <form action="<?php echo e(route('permission.update-roles', $permission->id)); ?>" method="POST" class="card">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div class="card-body">
            <div class="row row-cards">
                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-md-4">
                        <label class="form-check card card-body">
                            <input class="form-check-input" type="checkbox" name="role_ids[]" value="<?php echo e($role->id); ?>" <?php if(in_array($role->id, $assigned_role_ids, true)): echo 'checked'; endif; ?>>
                            <span class="form-check-label"><?php echo e($role->name); ?></span>
                        </label>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <div class="card-footer text-end"><button class="btn btn-primary" type="submit">Simpan</button></div>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views\permission\assign-roles.blade.php ENDPATH**/ ?>