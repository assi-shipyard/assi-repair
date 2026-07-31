<?php $__env->startSection('title', 'Dashboard - SIREKA ASSI'); ?>
<?php $__env->startSection('body_title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if(isset($needs_password_change) && $needs_password_change): ?>
        <div class="alert alert-warning alert-important alert-dismissible" role="alert">
            <div class="d-flex">
                <div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v2m0 4v.01" /><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75" /></svg>
                </div>
                <div>
                    <strong>Peringatan Keamanan!</strong> Anda masih menggunakan kata sandi bawaan (NIK). Demi keamanan akun Anda, harap <a href="<?php echo e(route('settings.index')); ?>" class="alert-link text-decoration-underline">segera ganti kata sandi Anda di sini</a>.
                </div>
            </div>
            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center g-3">
                <div class="col-lg-8">
                    <div class="text-secondary mb-2">Selamat datang kembali</div>
                    <h2 class="mb-2"><?php echo e(session('employee_name') ?: 'Pengguna'); ?></h2>
                    <p class="text-secondary mb-0">Ringkasan operasional SIREKA ASSI tersedia di bawah ini.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="text-secondary small">Jabatan</div>
                    <div class="fw-bold"><?php echo e(session('employee_position') ?: '-'); ?></div>
                    <div class="text-secondary small">NIK</div>
                    <div class="fw-bold"><?php echo e(session('employee_id') ?: '-'); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards mb-4">
        <div class="col-sm-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary">Total Perusahaan</div>
                    <div class="h1 mb-0"><?php echo e($total_companies); ?></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary">Total Kapal</div>
                    <div class="h1 mb-0"><?php echo e($total_ships); ?></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary">Proyek Aktif</div>
                    <div class="h1 mb-0"><?php echo e($active_projects); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title mb-0">Status Proyek</h3>
        </div>
        <div class="card-body">
            <div class="row row-cards">
                <div class="col-md-3">
                    <div class="border rounded p-3">
                        <div class="text-secondary">Total Proyek</div>
                        <div class="h2 mb-0"><?php echo e($project_summary->total_projects ?? 0); ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded p-3">
                        <div class="text-secondary">Belum Dimulai</div>
                        <div class="h2 mb-0"><?php echo e($project_summary->pending_projects ?? 0); ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded p-3">
                        <div class="text-secondary">Berjalan</div>
                        <div class="h2 mb-0"><?php echo e($project_summary->ongoing_projects ?? 0); ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded p-3">
                        <div class="text-secondary">Selesai</div>
                        <div class="h2 mb-0"><?php echo e($project_summary->completed_projects ?? 0); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Proyek Terbaru</h3>
            <a href="<?php echo e(route('project.index')); ?>" class="btn btn-outline-primary btn-sm">Lihat Semua</a>
        </div>
        <div class="table-responsive">
            <table class="table card-table table-vcenter">
                <thead>
                    <tr>
                        <th>Kode Proyek</th>
                        <th>Kapal</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $recent_projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($project->project_code); ?></td>
                            <td><?php echo e($project->ship?->name ?? '-'); ?></td>
                            <td><?php echo e($project->status ?? '-'); ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span><?php echo e((float) ($project->progress ?? 0)); ?>%</span>
                                    <div class="progress progress-xs flex-grow-1">
                                        <div class="progress-bar" style="width: <?php echo e((float) ($project->progress ?? 0)); ?>%"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="<?php echo e(route('project.show', $project->unique_id)); ?>" class="btn btn-sm btn-outline-secondary">Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-4">Belum ada data proyek.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views/dashboard.blade.php ENDPATH**/ ?>