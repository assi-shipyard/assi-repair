<?php $__env->startSection('title', 'Pengaturan Akun Pelanggan'); ?>
<?php $__env->startSection('body_title', 'Pengaturan Akun Pelanggan'); ?>
<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="row g-3">
        <div class="col-lg-5"><div class="card"><div class="card-body"><h3 class="card-title">Identitas Akun</h3><p><?php echo e(auth()->user()->name); ?><br><span class="text-secondary"><?php echo e(auth()->user()->email); ?></span></p><p class="text-secondary mb-0">Hubungi administrator untuk perubahan identitas, perusahaan, atau akses proyek.</p></div></div></div>
        <div class="col-lg-7"><div class="card"><div class="card-header"><h3 class="card-title">Ubah Kata Sandi</h3></div><div class="card-body">
            <form class="customer_form" method="POST" action="<?php echo e(route('customer.password')); ?>">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <div class="mb-3"><label for="current_password" class="form-label">Kata Sandi Saat Ini</label><input id="current_password" name="current_password" type="password" class="form-control" required maxlength="1024" autocomplete="current-password"></div>
                <div class="mb-3"><label for="password" class="form-label">Kata Sandi Baru</label><input id="password" name="password" type="password" class="form-control" required minlength="12" maxlength="128" autocomplete="new-password"><small class="text-secondary">Minimal 12 karakter dengan huruf besar, huruf kecil, dan angka.</small></div>
                <div class="mb-3"><label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi Baru</label><input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required minlength="12" maxlength="128" autocomplete="new-password"></div>
                <button class="btn btn-primary" type="submit">Simpan Kata Sandi</button>
            </form>
        </div></div></div>
    </div>
    <?php echo $__env->make('customer.validation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/customer/settings.blade.php ENDPATH**/ ?>