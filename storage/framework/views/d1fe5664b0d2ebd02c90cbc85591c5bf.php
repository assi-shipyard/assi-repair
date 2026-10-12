<?php
    $is_customer_directory = $account_type === \App\Models\User::TYPE_CUSTOMER;
    $directory_title = $is_customer_directory ? 'Akun Pelanggan' : 'Akun Administrator';
    $directory_route = $is_customer_directory ? 'accounts.customers' : 'accounts.index';
?>
<?php $__env->startSection('title', $directory_title); ?>
<?php $__env->startSection('body_title', $directory_title); ?>
<?php $__env->startSection('buttons_beside_title'); ?>
    <a class="btn btn-outline-secondary" href="<?php echo e(route('employee.index')); ?>">Karyawan Internal</a>
    <a class="btn btn-outline-secondary" href="<?php echo e(route($is_customer_directory ? 'accounts.index' : 'accounts.customers')); ?>"><?php echo e($is_customer_directory ? 'Akun Admin' : 'Akun Pelanggan'); ?></a>
    <?php if($is_customer_directory): ?><a class="btn btn-outline-primary" href="<?php echo e(route('accounts.projects')); ?>">Akses Proyek</a><?php endif; ?>
    <a class="btn btn-primary" href="#create-account">Buat Akun <?php echo e($is_customer_directory ? 'Pelanggan' : 'Admin'); ?></a>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="card mb-3"><div class="card-body d-flex flex-wrap align-items-center gap-3">
        <div class="me-auto"><h3 class="mb-1"><?php echo e($total); ?> <?php echo e($is_customer_directory ? 'Pelanggan' : 'Admin'); ?> Terdaftar</h3><p class="text-secondary mb-0"><?php echo e($is_customer_directory ? 'Pelanggan menggunakan email. Perusahaan dapat berupa pemilik kapal atau surveyor; akses proyek diberikan secara terpisah.' : 'Akun administrator menggunakan username dan tidak terkait dengan identitas karyawan.'); ?></p></div>
        <span class="badge bg-green-lt"><?php echo e($active_total); ?> aktif</span><span class="badge bg-secondary-lt"><?php echo e($total - $active_total); ?> tidak aktif</span>
    </div></div>
    <?php echo $__env->make('accounts.partials.directory-filter', ['search_hint' => $is_customer_directory ? 'Nama, email, atau perusahaan' : 'Nama, email, atau username'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="row g-3">
        <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="col-12 col-md-6 col-xl-4"><article class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <?php if($user->profile_photo_path): ?>
                            <img src="<?php echo e(route('accounts.photo', $user)); ?>" class="avatar avatar-lg object-fit-cover flex-shrink-0" alt="Foto profil <?php echo e($user->name ?? $user->username); ?>" loading="lazy">
                        <?php else: ?>
                            <span class="avatar avatar-lg bg-primary-lt flex-shrink-0" role="img" aria-label="Foto profil belum tersedia"><i class="ti ti-user fs-2" aria-hidden="true"></i></span>
                        <?php endif; ?>
                        <div class="min-w-0"><h3 class="card-title mb-1 text-break"><?php echo e($user->name ?? $user->username ?? $user->employee_id); ?></h3><span class="badge <?php echo e($user->is_active ? 'bg-green-lt' : 'bg-red-lt'); ?>"><?php echo e($user->is_active ? 'Aktif' : 'Tidak Aktif'); ?></span></div>
                    </div>
                    <dl class="row small mb-0">
                        <dt class="col-4 text-secondary fw-normal">Email</dt><dd class="col-8 text-break"><?php echo e($user->email ?? '-'); ?></dd>
                        <?php if($is_customer_directory): ?>
                            <dt class="col-4 text-secondary fw-normal">Perusahaan</dt><dd class="col-8 text-break"><?php echo e($user->company?->name ?? '-'); ?></dd>
                            <dt class="col-4 text-secondary fw-normal">Akses Proyek</dt><dd class="col-8"><?php echo e($user->customer_access_count); ?> proyek aktif</dd>
                        <?php else: ?>
                            <dt class="col-4 text-secondary fw-normal">Username</dt><dd class="col-8 text-break"><?php echo e($user->username ?? $user->employee_id); ?></dd>
                        <?php endif; ?>
                        <dt class="col-4 text-secondary fw-normal">Terdaftar</dt><dd class="col-8 mb-0"><?php echo e($user->created_at?->tanggal_indonesia() ?? '-'); ?></dd>
                    </dl>
                    <?php if($user->must_change_password): ?><div class="text-secondary small mt-3">Wajib mengganti kata sandi awal.</div><?php endif; ?>
                </div>
                <div class="card-footer d-flex flex-wrap gap-2">
                    <a class="btn btn-sm btn-outline-primary" href="<?php echo e(route('accounts.edit', $user)); ?>">Ubah</a>
                    <?php if($user->id !== auth()->id()): ?>
                        <form method="POST" action="<?php echo e(route('accounts.status', $user)); ?>"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?><input type="hidden" name="is_active" value="<?php echo e($user->is_active ? '0' : '1'); ?>"><button class="btn btn-sm btn-outline-secondary" type="submit"><?php echo e($user->is_active ? 'Nonaktifkan' : 'Aktifkan'); ?></button></form>
                    <?php else: ?><span class="text-secondary small align-self-center">Akun Anda</span><?php endif; ?>
                </div>
            </article></div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-12"><div class="card"><div class="card-body text-center text-secondary py-5"><?php echo e($total === 0 ? 'Belum ada akun terdaftar.' : 'Tidak ada akun yang cocok dengan pencarian.'); ?></div></div></div>
        <?php endif; ?>
    </div>
    <div class="my-3"><?php echo e($users->onEachSide(1)->links('accounts.partials.pagination')); ?></div>
    <div class="card" id="create-account"><div class="card-header"><h3 class="card-title">Buat Akun <?php echo e($is_customer_directory ? 'Pelanggan' : 'Admin'); ?></h3></div><div class="card-body">
        <form class="customer_form" action="<?php echo e(route('accounts.store')); ?>" method="POST" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="account_type" value="<?php echo e($account_type); ?>">
            <div class="row g-3">
                <div class="col-md-6"><label for="name" class="form-label">Nama Lengkap</label><input id="name" name="name" class="form-control" required maxlength="255" value="<?php echo e(old('name')); ?>"></div>
                <div class="col-md-6"><label for="email" class="form-label">Email</label><input id="email" name="email" type="email" class="form-control" required maxlength="64" value="<?php echo e(old('email')); ?>" autocomplete="off"></div>
                <?php if($is_customer_directory): ?>
                    <div class="col-md-6"><label for="company_id" class="form-label">Perusahaan Pelanggan / Surveyor</label><select id="company_id" name="company_id" class="form-select" required><option value="">Pilih perusahaan</option><?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($company->unique_id); ?>" <?php if(old('company_id') === $company->unique_id): echo 'selected'; endif; ?>><?php echo e($company->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                <?php else: ?>
                    <div class="col-md-6"><label for="username" class="form-label">Username Admin</label><input id="username" name="username" class="form-control" required maxlength="64" value="<?php echo e(old('username')); ?>"></div>
                <?php endif; ?>
                <div class="col-md-6"><label for="profile_photo" class="form-label">Foto Profil (Opsional)</label><input id="profile_photo" name="profile_photo" type="file" class="form-control" accept="image/jpeg,image/png,image/webp"><small class="text-secondary">JPG, PNG, atau WEBP; maksimal 2 MB dan 4.000 x 4.000 piksel.</small></div>
                <div class="col-md-6"><label for="password" class="form-label">Kata Sandi Awal</label><input id="password" name="password" type="password" class="form-control" required minlength="12" maxlength="128" autocomplete="new-password"></div>
                <div class="col-md-6"><label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi</label><input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required minlength="12" maxlength="128" autocomplete="new-password"></div>
            </div>
            <p class="text-secondary mt-3">Minimal 12 karakter dengan huruf besar, huruf kecil, dan angka. Sampaikan kata sandi awal melalui saluran aman.</p>
            <button class="btn btn-primary" type="submit">Simpan Akun</button>
        </form>
    </div></div>
    <?php echo $__env->make('customer.validation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/accounts/index.blade.php ENDPATH**/ ?>