<?php $__env->startSection('title', 'Ubah Permission'); ?>
<?php $__env->startSection('body_title', 'Ubah Permission'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('permission.show', $permission->id)); ?>" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i>Kembali</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card permission-form-summary mb-4"><div class="card-body p-4 d-flex align-items-center gap-3"><span class="avatar avatar-lg bg-azure-lt text-azure"><i class="ti ti-key fs-2"></i></span><div><div class="text-secondary small">Katalog Hak Akses</div><h2 class="mb-1">Ubah <?php echo e($permission->permission_name ?? $permission->name); ?></h2><div class="text-secondary">Perubahan pada permission tidak menghapus penetapan role yang ada.</div></div></div></div>
    <form id="permission-form" action="<?php echo e(route('permission.update', $permission->id)); ?>" method="POST" class="card">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div class="card-header"><div><h3 class="card-title mb-1">Identitas Permission</h3><div class="text-secondary small">Pastikan nama sistem tetap selaras dengan kebijakan akses aplikasi.</div></div></div>
        <div class="card-body"><div class="row g-3"><div class="col-md-6"><label class="form-label required" for="name">Nama Sistem</label><input id="name" class="form-control" name="name" value="<?php echo e(old('name', $permission->name)); ?>" maxlength="255" required></div><div class="col-md-6"><label class="form-label required" for="permission_name">Nama Permission</label><input id="permission_name" class="form-control" name="permission_name" value="<?php echo e(old('permission_name', $permission->permission_name ?? '')); ?>" maxlength="255" required></div><div class="col-12"><label class="form-label" for="permission_description">Deskripsi</label><textarea id="permission_description" class="form-control" name="permission_description" rows="4" maxlength="1000"><?php echo e(old('permission_description', $permission->permission_description ?? '')); ?></textarea></div></div></div>
        <div class="card-footer d-flex justify-content-between"><a href="<?php echo e(route('permission.assign-roles', $permission->id)); ?>" class="btn btn-outline-secondary"><i class="ti ti-shield me-1"></i>Atur Role</a><button class="btn btn-primary" type="submit"><i class="ti ti-device-floppy me-1"></i>Perbarui Permission</button></div>
    </form>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?><style>.permission-form-summary { border-top: 3px solid var(--tblr-azure); }</style><?php $__env->stopPush(); ?>
<?php $__env->startPush('scripts'); ?><script>if (window.jQuery && jQuery.fn.validate) { jQuery('#permission-form').validate({ rules: { name: { required: true, maxlength: 255 }, permission_name: { required: true, maxlength: 255 }, permission_description: { maxlength: 1000 } }, errorElement: 'div', errorClass: 'invalid-feedback', highlight: function (element) { jQuery(element).addClass('is-invalid'); }, unhighlight: function (element) { jQuery(element).removeClass('is-invalid'); } }); }</script><?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/permission/edit.blade.php ENDPATH**/ ?>