<?php $__env->startSection('title', 'Kelola Semua Akun'); ?>
<?php $__env->startSection('body_title', 'Kelola Semua Akun'); ?>
<?php $__env->startSection('buttons_beside_title'); ?>
    <a class="btn btn-outline-primary" href="<?php echo e(route('accounts.projects')); ?>">Akses &amp; Persetujuan Pelanggan</a>
    <a class="btn btn-primary" href="<?php echo e(route('employee.create')); ?>">Buat Akun Karyawan</a>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="card mb-3"><div class="card-body">
        <h3 class="card-title">Tiga Jenis Akun, Hak Akses Terpisah</h3>
        <p class="text-secondary mb-0">Admin menggunakan username. Karyawan menggunakan NIK 9 digit. Pelanggan menggunakan email dan wajib mengganti kata sandi awal. Perusahaan pelanggan dapat berupa pemilik kapal atau perusahaan surveyor; akses proyek tidak diberikan otomatis.</p>
    </div></div>
    <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">Daftar Akun</h3></div>
        <div class="table-responsive"><table class="table table-vcenter card-table">
            <thead><tr><th>Nama / Identitas</th><th>Jenis Akun</th><th>Perusahaan</th><th>Status</th><th>Tindakan</th></tr></thead>
            <tbody><?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($user->name ?? $user->employee?->name ?? $user->username ?? $user->employee_id); ?><div class="text-secondary small"><?php echo e($user->is_customer() ? $user->email : ($user->username ?? $user->employee_id)); ?></div></td>
                    <td><?php echo e(['admin' => 'Admin', 'internal' => 'Karyawan Internal', 'customer' => 'Pelanggan'][$user->account_type]); ?></td>
                    <td><?php echo e($user->company?->name ?? '-'); ?></td>
                    <td><span class="badge <?php echo e($user->is_active ? 'bg-green-lt' : 'bg-red-lt'); ?>"><?php echo e($user->is_active ? 'Aktif' : 'Tidak Aktif'); ?></span></td>
                    <td>
                        <?php if($user->account_type !== 'internal'): ?>
                            <a class="btn btn-sm btn-outline-primary mb-1" href="<?php echo e(route('accounts.edit', $user)); ?>">Ubah</a>
                        <?php elseif($user->employee): ?>
                            <a class="btn btn-sm btn-outline-primary mb-1" href="<?php echo e(route('employee.edit', $user->employee)); ?>">Ubah Karyawan</a>
                        <?php endif; ?>
                        <?php if($user->id !== auth()->id()): ?>
                        <form method="POST" action="<?php echo e(route('accounts.status', $user)); ?>"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?><input type="hidden" name="is_active" value="<?php echo e($user->is_active ? '0' : '1'); ?>"><button class="btn btn-sm btn-outline-secondary" type="submit"><?php echo e($user->is_active ? 'Nonaktifkan' : 'Aktifkan'); ?></button></form>
                    <?php else: ?><span class="text-secondary small">Akun Anda</span><?php endif; ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td>Belum ada akun.</td></tr><?php endif; ?></tbody>
        </table></div>
        <div class="card-footer"><?php echo e($users->links()); ?></div>
    </div>
    <div class="card">
        <div class="card-header"><h3 class="card-title">Buat Akun Admin / Pelanggan</h3></div>
        <div class="card-body">
            <form class="customer_form" action="<?php echo e(route('accounts.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="row g-3">
                    <div class="col-md-6"><label for="account_type" class="form-label">Jenis Akun</label><select class="form-select" id="account_type" name="account_type" required><option value="customer" <?php if(old('account_type', 'customer') === 'customer'): echo 'selected'; endif; ?>>Pelanggan</option><option value="admin" <?php if(old('account_type') === 'admin'): echo 'selected'; endif; ?>>Admin</option></select></div>
                    <div class="col-md-6"><label for="name" class="form-label">Nama Lengkap</label><input id="name" name="name" class="form-control" required maxlength="255" value="<?php echo e(old('name')); ?>"></div>
                    <div class="col-md-6" id="username_group"><label for="username" class="form-label">Username Admin</label><input id="username" name="username" class="form-control" maxlength="64" value="<?php echo e(old('username')); ?>"></div>
                    <div class="col-md-6" id="company_group"><label for="company_id" class="form-label">Perusahaan Pelanggan / Surveyor</label><select id="company_id" name="company_id" class="form-select"><option value="">Pilih perusahaan</option><?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($company->unique_id); ?>" <?php if(old('company_id') === $company->unique_id): echo 'selected'; endif; ?>><?php echo e($company->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                    <div class="col-md-6"><label for="email" class="form-label">Email</label><input id="email" name="email" type="email" class="form-control" required maxlength="64" value="<?php echo e(old('email')); ?>" autocomplete="off"></div>
                    <div class="col-md-6"><label for="password" class="form-label">Kata Sandi Awal</label><input id="password" name="password" type="password" class="form-control" required minlength="12" maxlength="128" autocomplete="new-password"></div>
                    <div class="col-md-6"><label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi</label><input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required minlength="12" maxlength="128" autocomplete="new-password"></div>
                </div>
                <p class="text-secondary mt-3">Minimal 12 karakter dengan huruf besar, huruf kecil, dan angka. Sampaikan kata sandi awal melalui saluran aman, bukan melalui halaman publik.</p>
                <button class="btn btn-primary" type="submit">Simpan Akun</button>
            </form>
        </div>
    </div>
    <?php echo $__env->make('customer.validation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
    $(function () {
        function toggle_account_fields() {
            const is_customer = $('#account_type').val() === 'customer';
            $('#company_group').toggle(is_customer);
            $('#company_id').prop('required', is_customer).prop('disabled', !is_customer);
            $('#username_group').toggle(!is_customer);
            $('#username').prop('required', !is_customer).prop('disabled', is_customer);
        }
        $('#account_type').on('change', toggle_account_fields);
        toggle_account_fields();
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/accounts/index.blade.php ENDPATH**/ ?>