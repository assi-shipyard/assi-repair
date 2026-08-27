<?php $__env->startSection('title', 'Ubah Role'); ?>
<?php $__env->startSection('body_title', 'Ubah Role'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('role.show', $role->id)); ?>" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i>Kembali</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card access-form-summary mb-4"><div class="card-body p-4 d-flex align-items-center gap-3"><span class="avatar avatar-lg bg-primary-lt text-primary"><i class="ti ti-shield-check fs-2"></i></span><div><div class="text-secondary small">Kontrol Akses</div><h2 class="mb-1">Ubah <?php echo e($role->role_name ?? $role->name); ?></h2><div class="text-secondary">Perubahan pada identitas role tidak mengubah permission yang sudah ditetapkan.</div></div></div></div>
    <form id="role-form" action="<?php echo e(route('role.update', $role->id)); ?>" method="POST" class="card">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div class="card-header"><div><h3 class="card-title mb-1">Identitas Role</h3><div class="text-secondary small">Gunakan nama sistem yang stabil untuk integrasi aplikasi.</div></div></div>
        <div class="card-body"><div class="row g-3"><div class="col-md-6"><label class="form-label required" for="name">Nama Sistem</label><input id="name" class="form-control" name="name" value="<?php echo e(old('name', $role->name)); ?>" maxlength="255" required></div><div class="col-md-6"><label class="form-label required" for="role_name">Nama Role</label><input id="role_name" class="form-control" name="role_name" value="<?php echo e(old('role_name', $role->role_name ?? '')); ?>" maxlength="255" required></div><div class="col-12"><label class="form-label" for="role_description">Deskripsi</label><textarea id="role_description" class="form-control" name="role_description" rows="4" maxlength="1000"><?php echo e(old('role_description', $role->role_description ?? '')); ?></textarea></div></div></div>
        <div class="card-footer d-flex justify-content-between"><a href="<?php echo e(route('role.assign-permissions', $role->id)); ?>" class="btn btn-outline-secondary"><i class="ti ti-key me-1"></i>Atur Permission</a><button class="btn btn-primary" type="submit"><i class="ti ti-device-floppy me-1"></i>Perbarui Role</button></div>
    </form>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?><style>.access-form-summary { border-top: 3px solid var(--tblr-primary); }</style><?php $__env->stopPush(); ?>
<?php $__env->startPush('scripts'); ?><script>if (window.jQuery && jQuery.fn.validate) { jQuery('#role-form').validate({ rules: { name: { required: true, maxlength: 255 }, role_name: { required: true, maxlength: 255 }, role_description: { maxlength: 1000 } }, errorElement: 'div', errorClass: 'invalid-feedback', highlight: function (element) { jQuery(element).addClass('is-invalid'); }, unhighlight: function (element) { jQuery(element).removeClass('is-invalid'); } }); }</script><?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/role/edit.blade.php ENDPATH**/ ?>