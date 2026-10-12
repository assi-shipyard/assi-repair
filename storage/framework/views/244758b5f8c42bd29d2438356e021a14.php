<?php $__env->startSection('title', 'Ubah Akun'); ?>
<?php $__env->startSection('body_title', 'Ubah Akun'); ?>
<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route($user->is_customer() ? 'accounts.customers' : 'accounts.index')); ?>" class="btn btn-outline-secondary">Daftar Akun</a>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="card"><div class="card-header"><h3 class="card-title"><?php echo e($user->is_customer() ? 'Pelanggan' : 'Admin'); ?></h3></div><div class="card-body">
        <form class="customer_form" method="POST" action="<?php echo e(route('accounts.update', $user)); ?>" enctype="multipart/form-data">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div class="row g-3">
                <div class="col-12">
                    <?php if($user->profile_photo_path): ?><img src="<?php echo e(route('accounts.photo', $user)); ?>" class="avatar avatar-lg object-fit-cover mb-2" alt="Foto profil <?php echo e($user->name); ?>"><?php endif; ?>
                    <label for="profile_photo" class="form-label">Foto Profil (Opsional)</label><input id="profile_photo" name="profile_photo" type="file" class="form-control" accept="image/jpeg,image/png,image/webp">
                    <small class="text-secondary">JPG, PNG, atau WEBP; maksimal 2 MB dan 4.000 x 4.000 piksel.</small>
                    <?php if($user->profile_photo_path): ?><label class="form-check mt-2"><input type="checkbox" name="remove_photo" value="1" class="form-check-input" <?php if(old('remove_photo')): echo 'checked'; endif; ?>><span class="form-check-label">Hapus foto profil</span></label><?php endif; ?>
                </div>
                <div class="col-md-6"><label for="name" class="form-label">Nama Lengkap</label><input id="name" name="name" class="form-control" required maxlength="255" value="<?php echo e(old('name', $user->name)); ?>"></div>
                <div class="col-md-6"><label for="email" class="form-label">Email</label><input id="email" name="email" type="email" class="form-control" required maxlength="64" value="<?php echo e(old('email', $user->email)); ?>"></div>
                <?php if($user->is_customer()): ?>
                    <div class="col-md-6"><label for="company_id" class="form-label">Perusahaan Pelanggan / Surveyor</label><select id="company_id" name="company_id" class="form-select" required><option value="">Pilih perusahaan</option><?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($company->unique_id); ?>" <?php if(old('company_id', $user->company?->unique_id) === $company->unique_id): echo 'selected'; endif; ?>><?php echo e($company->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                <?php else: ?>
                    <div class="col-md-6"><label for="username" class="form-label">Username Admin</label><input id="username" name="username" class="form-control" required maxlength="64" value="<?php echo e(old('username', $user->username)); ?>"></div>
                <?php endif; ?>
                <div class="col-md-6"><label for="password" class="form-label">Reset Kata Sandi (Opsional)</label><input id="password" name="password" type="password" class="form-control" minlength="12" maxlength="128" autocomplete="new-password"></div>
                <div class="col-md-6"><label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi</label><input id="password_confirmation" name="password_confirmation" type="password" class="form-control" minlength="12" maxlength="128" autocomplete="new-password"></div>
            </div>
            <p class="text-secondary mt-3">Kosongkan kata sandi untuk mempertahankan kata sandi saat ini. Reset kata sandi pelanggan mewajibkan penggantian saat login berikutnya. Jenis akun tidak dapat diubah.</p>
            <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
        </form>
    </div></div>
    <?php echo $__env->make('customer.validation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/accounts/edit.blade.php ENDPATH**/ ?>