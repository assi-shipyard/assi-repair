<?php $__env->startSection('title', 'Akun Karyawan Internal'); ?>
<?php $__env->startSection('body_title', 'Akun Karyawan Internal'); ?>
<?php $__env->startSection('buttons_beside_title'); ?>
    <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin')): ?>
        <a href="<?php echo e(route('accounts.customers')); ?>" class="btn btn-outline-secondary">Akun Pelanggan</a>
    <?php endif; ?>
    <a href="<?php echo e(route('employee.create')); ?>" class="btn btn-primary"><i class="ti ti-user-plus me-1" aria-hidden="true"></i>Buat Akun Karyawan</a>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="card mb-3">
        <div class="card-body d-flex flex-wrap align-items-center gap-3">
            <span class="avatar avatar-lg bg-primary-lt"><i class="ti ti-users fs-2" aria-hidden="true"></i></span>
            <div class="me-auto"><h3 class="mb-1"><?php echo e($total); ?> Karyawan Terdaftar</h3><p class="text-secondary mb-0">Identitas, jabatan, dan struktur organisasi karyawan internal. Akun pelanggan dan admin berada di halaman terpisah.</p></div>
            <span class="badge bg-green-lt"><?php echo e($active_total); ?> aktif</span>
            <span class="badge bg-secondary-lt"><?php echo e($inactive_total); ?> tidak aktif</span>
        </div>
    </div>
    <?php echo $__env->make('accounts.partials.directory-filter', ['directory_route' => 'employee.index', 'search_hint' => 'Nama, NIK, jabatan, unit, atau manajer'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="row g-3" id="employee-cards">
        <?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="col-12 col-md-6 col-xl-4">
                <article class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <?php if($employee->profile_photo_path): ?>
                                <img src="<?php echo e(route('employee.photo', $employee->unique_id)); ?>" class="avatar avatar-lg object-fit-cover flex-shrink-0" alt="Foto profil <?php echo e($employee->name); ?>" loading="lazy">
                            <?php else: ?>
                                <span class="avatar avatar-lg bg-primary-lt flex-shrink-0" role="img" aria-label="Foto profil belum tersedia"><i class="ti ti-user fs-2" aria-hidden="true"></i></span>
                            <?php endif; ?>
                            <div class="min-w-0"><h3 class="card-title mb-1 text-break"><?php echo e($employee->name); ?></h3><div class="text-secondary small font-monospace">NIK <?php echo e($employee->employee_id); ?></div></div>
                        </div>
                        <span class="badge <?php echo e($employee->status === 'active' ? 'bg-green-lt' : 'bg-secondary-lt'); ?>"><?php echo e($employee->status === 'active' ? 'Aktif' : 'Tidak Aktif'); ?></span>
                        <?php if($employee->user && ! $employee->user->is_active): ?><span class="badge bg-red-lt">Akun Dinonaktifkan</span><?php endif; ?>
                        <dl class="row mb-0 mt-3 small">
                            <dt class="col-4 text-secondary fw-normal">Jabatan</dt><dd class="col-8 text-break"><?php echo e($employee->position?->name ?? '-'); ?></dd>
                            <dt class="col-4 text-secondary fw-normal">Unit</dt><dd class="col-8 text-break"><?php echo e($employee->position?->organizational_unit?->name ?? '-'); ?></dd>
                            <dt class="col-4 text-secondary fw-normal">Manajer</dt><dd class="col-8 text-break"><?php echo e($employee->manager?->name ?? '-'); ?></dd>
                            <dt class="col-4 text-secondary fw-normal">Email</dt><dd class="col-8 text-break mb-0"><?php echo e($employee->email ?? $employee->user?->email ?? '-'); ?></dd>
                        </dl>
                    </div>
                    <div class="card-footer d-flex flex-wrap gap-2">
                        <a href="<?php echo e(route('employee.show', $employee->unique_id)); ?>" class="btn btn-outline-primary btn-sm">Lihat Detail</a>
                        <a href="<?php echo e(route('employee.edit', $employee->unique_id)); ?>" class="btn btn-outline-secondary btn-sm">Ubah</a>
                    </div>
                </article>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-12"><div class="card"><div class="card-body text-center text-secondary py-5"><?php echo e($total === 0 ? 'Belum ada karyawan terdaftar.' : 'Tidak ada karyawan yang cocok dengan pencarian.'); ?></div></div></div>
        <?php endif; ?>
    </div>
    <div class="mt-3"><?php echo e($employees->onEachSide(1)->links('accounts.partials.pagination')); ?></div>
    <?php echo $__env->make('customer.validation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/employee/index.blade.php ENDPATH**/ ?>