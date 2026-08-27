<?php $__env->startSection('title', 'Atur Permission Role'); ?>
<?php $__env->startSection('body_title', 'Atur Permission Role'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('role.show', $role->id)); ?>" class="btn btn-outline-secondary">Kembali</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <form action="<?php echo e(route('role.update-permissions', $role->id)); ?>" method="POST" class="card">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div class="card-header">
            <div><h3 class="card-title mb-1">Hak Akses untuk <?php echo e($role->role_name ?? $role->name); ?></h3><div class="text-secondary small">Pilih permission yang dapat digunakan oleh role ini.</div></div>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <div class="input-icon flex-fill assignment-search"><span class="input-icon-addon"><i class="ti ti-search"></i></span><input type="search" class="form-control assignment-filter" placeholder="Cari permission..."></div>
                <button type="button" class="btn btn-outline-secondary assignment-select-all">Pilih Semua</button>
                <button type="button" class="btn btn-outline-secondary assignment-clear">Kosongkan</button>
            </div>
            <div class="list-group assignment-list">
                <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="list-group-item d-flex align-items-center gap-3 assignment-item" data-search="<?php echo e(strtolower($permission->name.' '.$permission->permission_name)); ?>">
                            <input class="form-check-input" type="checkbox" name="permission_ids[]" value="<?php echo e($permission->id); ?>" <?php if(in_array($permission->id, $assigned_permission_ids, true)): echo 'checked'; endif; ?>>
                            <span class="col"><span class="fw-semibold d-block"><?php echo e($permission->permission_name ?? $permission->name); ?></span><span class="text-secondary small"><?php echo e($permission->name); ?></span></span>
                    </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?>
                    <div class="list-group-item text-secondary">Belum ada permission yang dapat ditetapkan.</div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <div class="card-footer text-end"><button class="btn btn-primary" type="submit">Simpan</button></div>
    </form>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <style>.assignment-search { min-width: 16rem; }</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        document.querySelectorAll('.assignment-filter').forEach(function (input) {
            input.addEventListener('input', function () { var query = this.value.toLowerCase(); document.querySelectorAll('.assignment-item').forEach(function (item) { item.classList.toggle('d-none', !item.dataset.search.includes(query)); }); });
        });
        document.querySelectorAll('.assignment-select-all').forEach(function (button) { button.addEventListener('click', function () { document.querySelectorAll('.assignment-item:not(.d-none) input').forEach(function (input) { input.checked = true; }); }); });
        document.querySelectorAll('.assignment-clear').forEach(function (button) { button.addEventListener('click', function () { document.querySelectorAll('.assignment-item input').forEach(function (input) { input.checked = false; }); }); });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/role/assign-permissions.blade.php ENDPATH**/ ?>