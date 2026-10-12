<?php $__env->startSection('title', 'Buat Akun Baru'); ?>
<?php $__env->startSection('body_title', 'Buat Akun Baru'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('employee.index')); ?>" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1" aria-hidden="true"></i>Daftar Pengguna</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin')): ?>
        <div class="card mb-3"><div class="card-body">
            <h3 class="card-title">Jenis Akun: Karyawan Internal</h3>
            <p class="text-secondary mb-2">Formulir ini khusus karyawan dengan NIK 9 digit. Admin dan pelanggan memiliki identitas serta hak akses terpisah.</p>
            <a class="btn btn-outline-primary" href="<?php echo e(route('accounts.index')); ?>">Buat Akun Admin / Pelanggan</a>
        </div></div>
    <?php endif; ?>

    <?php echo $__env->make('employee.partials.form', ['is_edit' => false, 'employee' => null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/employee/create.blade.php ENDPATH**/ ?>