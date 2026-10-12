<?php $__env->startSection('title', 'Detail Pengguna'); ?>
<?php $__env->startSection('body_title', 'Detail Pengguna'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('employee.edit', $employee->unique_id)); ?>" class="btn btn-primary"><i class="ti ti-pencil me-1" aria-hidden="true"></i>Ubah Pengguna</a>
    <a href="<?php echo e(route('employee.index')); ?>" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1" aria-hidden="true"></i>Daftar Pengguna</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card employee-profile-summary mb-4">
        <div class="card-body p-4 d-flex flex-wrap align-items-center gap-3">
            <?php if($employee->profile_photo_path): ?>
                <img src="<?php echo e(route('employee.photo', $employee->unique_id)); ?>" alt="Foto <?php echo e($employee->name); ?>" class="avatar avatar-xl employee-profile-photo">
            <?php else: ?>
                <span class="avatar avatar-xl bg-primary-lt text-primary"><?php echo e(\Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($employee->name, 0, 2))); ?></span>
            <?php endif; ?>
            <div class="me-auto text-break">
                <div class="text-secondary small">Pengguna Karyawan</div>
                <h2 class="mb-1"><?php echo e($employee->name); ?></h2>
                <div class="text-secondary font-monospace">NIK <?php echo e($employee->employee_id); ?></div>
            </div>
            <span class="badge <?php echo e($employee->status === 'active' ? 'bg-success-lt text-success' : 'bg-secondary-lt text-secondary'); ?>"><?php echo e($employee->status === 'active' ? 'Aktif' : 'Tidak Aktif'); ?></span>
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-12 col-lg-8">
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-id me-2" aria-hidden="true"></i>Identitas Karyawan</h3></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-secondary fw-normal">Nama Lengkap</dt><dd class="col-sm-8 text-break"><?php echo e($employee->name); ?></dd>
                        <dt class="col-sm-4 text-secondary fw-normal">NIK</dt><dd class="col-sm-8 font-monospace"><?php echo e($employee->employee_id); ?></dd>
                        <dt class="col-sm-4 text-secondary fw-normal">Email</dt><dd class="col-sm-8 text-break"><?php echo e($employee->email ?? 'Belum diisi'); ?></dd>
                        <dt class="col-sm-4 text-secondary fw-normal">Status Karyawan</dt><dd class="col-sm-8 mb-0"><?php echo e($employee->status === 'active' ? 'Aktif' : 'Tidak Aktif'); ?></dd>
                    </dl>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-sitemap me-2" aria-hidden="true"></i>Struktur Organisasi</h3></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-secondary fw-normal">Jabatan</dt>
                        <dd class="col-sm-8"><?php echo e($employee->position?->name ?? 'Belum diatur'); ?></dd>
                        <dt class="col-sm-4 text-secondary fw-normal">Kategori Jabatan</dt><dd class="col-sm-8"><?php echo e($employee->position?->category_label ?? '-'); ?></dd>
                        <dt class="col-sm-4 text-secondary fw-normal">Unit Organisasi</dt><dd class="col-sm-8"><?php echo e($employee->position?->organizational_unit?->name ?? 'Belum diatur'); ?></dd>
                        <dt class="col-sm-4 text-secondary fw-normal">Manajer Langsung</dt>
                        <dd class="col-sm-8 mb-0">
                            <?php if($employee->manager): ?>
                                <a href="<?php echo e(route('employee.show', $employee->manager->unique_id)); ?>"><?php echo e($employee->manager->name); ?></a>
                            <?php else: ?>
                                Tidak ada
                            <?php endif; ?>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">Informasi Pencatatan</h3></div>
                <div class="card-body">
                    <dl class="mb-0">
                        <dt class="text-secondary fw-normal">Terdaftar Pada</dt>
                        <dd><?php echo e($employee->created_at?->timezone('Asia/Jakarta')->tanggal_waktu_indonesia()); ?> WIB</dd>
                        <dt class="text-secondary fw-normal">Terakhir Diperbarui</dt>
                        <dd class="mb-0"><?php echo e($employee->updated_at?->timezone('Asia/Jakarta')->tanggal_waktu_indonesia()); ?> WIB</dd>
                    </dl>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Pengelolaan Pengguna</h3>
                    <p class="text-secondary mb-0">Gunakan tombol Ubah Pengguna untuk memperbarui identitas, status karyawan, jabatan, manajer, foto profil, atau kata sandi. Kata sandi tidak ditampilkan pada halaman ini.</p>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        .employee-profile-summary { border-top: 3px solid var(--tblr-primary); }
        .employee-profile-photo { object-fit: cover; }
    </style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/employee/show.blade.php ENDPATH**/ ?>