<?php $__env->startSection('title', 'Detail Karyawan'); ?>
<?php $__env->startSection('body_title', 'Detail Karyawan'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('employee.edit', $employee->unique_id ?? $employee->id)); ?>" class="btn btn-outline-primary">Ubah</a>
    <a href="<?php echo e(route('employee.index')); ?>" class="btn btn-outline-secondary">Kembali</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php
        $employee_initials = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($employee->name ?? '-', 0, 2));
        $employee_status_label = $employee->status === 'active' ? 'Aktif' : 'Tidak Aktif';
        $employee_status_class = $employee->status === 'active' ? 'bg-green-lt text-green' : 'bg-red-lt text-red';
        $employee_profile_completion = collect([
            $employee->email,
            $employee->position?->name,
            $employee->position?->organizational_unit?->name,
            $employee->manager?->name,
            $employee->employee_id,
        ])->filter()->count();
    ?>

    <style>
        .employee-hero {
            border: 0;
            border-radius: 1.25rem;
            background: linear-gradient(135deg, #14532d 0%, #15803d 50%, #86efac 100%);
            color: #f0fdf4;
            box-shadow: 0 2rem 4rem -2.75rem rgba(20, 83, 45, 0.78);
        }

        .employee-hero-body {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            padding: 1.75rem;
        }

        .employee-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .employee-avatar-shell {
            position: relative;
            width: 6rem;
            height: 6rem;
        }

        .employee-avatar-image,
        .employee-avatar-fallback {
            width: 100%;
            height: 100%;
            border-radius: 1.5rem;
            border: 3px solid rgba(255, 255, 255, 0.22);
            box-shadow: 0 1.25rem 2.5rem -1.75rem rgba(20, 83, 45, 0.88);
        }

        .employee-avatar-image {
            object-fit: cover;
        }

        .employee-avatar-fallback {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.14);
        }

        .employee-kpi-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .75rem;
            min-width: min(100%, 24rem);
        }

        .employee-kpi {
            padding: .9rem 1rem;
            border-radius: 1rem;
            background: rgba(20, 83, 45, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.14);
        }

        .employee-kpi-label {
            display: block;
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: rgba(220, 252, 231, 0.82);
        }

        .employee-kpi-value {
            display: block;
            margin-top: .3rem;
            font-size: 1.45rem;
            font-weight: 700;
        }

        .employee-panel {
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 1.15rem;
            box-shadow: 0 1.5rem 3rem -2.5rem rgba(15, 23, 42, 0.45);
        }

        .employee-info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .employee-info-card {
            padding: 1rem;
            border-radius: 1rem;
            background: var(--tblr-bg-surface-secondary);
            border: 1px solid rgba(15, 23, 42, 0.05);
        }

        .employee-info-label {
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--tblr-secondary);
            margin-bottom: .35rem;
        }

        @media (max-width: 991.98px) {
            .employee-kpi-grid,
            .employee-info-grid {
                grid-template-columns: 1fr;
            }

            .employee-hero-body {
                padding: 1.25rem;
            }
        }
    </style>

    <div class="d-flex flex-column gap-3">
        <div class="card employee-hero">
            <div class="employee-hero-body">
                <div class="employee-profile">
                    <div class="employee-avatar-shell">
                        <?php if($employee->profile_photo_path): ?>
                            <img src="<?php echo e(route('employee.photo', $employee->unique_id ?? $employee->id)); ?>" alt="Foto <?php echo e($employee->name); ?>" class="employee-avatar-image" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
                            <span class="employee-avatar-fallback" style="display:none;"><?php echo e($employee_initials); ?></span>
                        <?php else: ?>
                            <span class="employee-avatar-fallback"><?php echo e($employee_initials); ?></span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="text-uppercase small fw-bold mb-2" style="letter-spacing: .1em; color: rgba(220, 252, 231, 0.82);">Profil User</div>
                        <h1 class="mb-1 text-white"><?php echo e($employee->name); ?></h1>
                        <div class="mb-3" style="color: rgba(220, 252, 231, 0.9);">NIK <?php echo e($employee->employee_id); ?></div>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge <?php echo e($employee_status_class); ?>"><?php echo e($employee_status_label); ?></span>
                            <span class="badge" style="background: rgba(255,255,255,.16); color: #f0fdf4;"><?php echo e($employee->position?->name ?? 'Jabatan belum diatur'); ?></span>
                        </div>
                    </div>
                </div>

                <div class="employee-kpi-grid">
                    <div class="employee-kpi">
                        <span class="employee-kpi-label">Profil Terisi</span>
                        <span class="employee-kpi-value"><?php echo e($employee_profile_completion); ?>/5</span>
                    </div>
                    <div class="employee-kpi">
                        <span class="employee-kpi-label">Unit</span>
                        <span class="employee-kpi-value"><?php echo e($employee->position?->organizational_unit?->name ? 1 : 0); ?></span>
                    </div>
                    <div class="employee-kpi">
                        <span class="employee-kpi-label">Manajer</span>
                        <span class="employee-kpi-value"><?php echo e($employee->manager?->name ? 1 : 0); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card employee-panel h-100">
                    <div class="card-header border-0 pb-0">
                        <h3 class="card-title mb-0">Ringkasan</h3>
                    </div>
                    <div class="card-body pt-3 d-flex flex-column gap-3">
                        <div class="employee-info-card">
                            <div class="employee-info-label">Status Akun</div>
                            <div class="fw-semibold"><?php echo e($employee_status_label); ?></div>
                        </div>
                        <div class="employee-info-card">
                            <div class="employee-info-label">Email</div>
                            <div class="fw-semibold text-break"><?php echo e($employee->email ?? '-'); ?></div>
                        </div>
                        <div class="employee-info-card">
                            <div class="employee-info-label">Jabatan Saat Ini</div>
                            <div class="fw-semibold"><?php echo e($employee->position?->name ?? '-'); ?></div>
                        </div>
                        <div class="d-grid gap-2 mt-auto">
                            <a href="<?php echo e(route('employee.edit', $employee->unique_id ?? $employee->id)); ?>" class="btn btn-primary">Ubah Data User</a>
                            <a href="<?php echo e(route('employee.index')); ?>" class="btn btn-outline-secondary">Kembali ke Daftar</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card employee-panel">
                    <div class="card-header border-0 pb-0">
                        <h3 class="card-title mb-0">Informasi Karyawan</h3>
                    </div>
                    <div class="card-body pt-3">
                        <div class="employee-info-grid">
                            <div class="employee-info-card">
                                <div class="employee-info-label">Email</div>
                                <div class="fw-semibold text-break"><?php echo e($employee->email ?? '-'); ?></div>
                            </div>
                            <div class="employee-info-card">
                                <div class="employee-info-label">Status</div>
                                <div class="fw-semibold"><?php echo e($employee_status_label); ?></div>
                            </div>
                            <div class="employee-info-card">
                                <div class="employee-info-label">Jabatan</div>
                                <div class="fw-semibold"><?php echo e($employee->position?->name ?? '-'); ?></div>
                            </div>
                            <div class="employee-info-card">
                                <div class="employee-info-label">Unit Organisasi</div>
                                <div class="fw-semibold"><?php echo e($employee->position?->organizational_unit?->name ?? '-'); ?></div>
                            </div>
                            <div class="employee-info-card">
                                <div class="employee-info-label">Manajer</div>
                                <div class="fw-semibold"><?php echo e($employee->manager?->name ?? '-'); ?></div>
                            </div>
                            <div class="employee-info-card">
                                <div class="employee-info-label">NIK</div>
                                <div class="fw-semibold"><?php echo e($employee->employee_id); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/employee/show.blade.php ENDPATH**/ ?>