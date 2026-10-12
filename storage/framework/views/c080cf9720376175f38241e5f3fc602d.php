<?php $__env->startSection('title', 'Riwayat Proyek'); ?>
<?php $__env->startSection('body_title', 'Riwayat Proyek'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('project.index')); ?>" class="btn btn-outline-primary">
        <i class="ti ti-list me-1"></i>Proyek Aktif
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="row row-cards mb-3">
        <div class="col-6 col-md-4">
            <div class="card card-sm"><div class="card-body">
                <div class="subheader">Proyek Selesai</div>
                <div class="h2 mb-0 text-success"><?php echo e($completed_total); ?></div>
            </div></div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card card-sm"><div class="card-body">
                <div class="subheader">Proyek Berjalan / Belum Mulai</div>
                <div class="h2 mb-0 text-primary"><?php echo e($active_total); ?></div>
            </div></div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card card-sm"><div class="card-body">
                <div class="subheader">Hasil Filter</div>
                <div class="h2 mb-0"><?php echo e($projects->total()); ?></div>
            </div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Proyek Selesai</h3>
        </div>
        <div class="card-body border-bottom">
            <form method="GET" action="<?php echo e(route('project.history')); ?>" class="row g-2 align-items-end" id="form-history-filter">
                <div class="col-12 col-md-5">
                    <label class="form-label" for="search_project">Pencarian</label>
                    <input type="search" id="search_project" name="search_project" class="form-control" maxlength="100" placeholder="Kode proyek atau nama kapal" value="<?php echo e($filters['search_project'] ?? ''); ?>">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label" for="project_type">Tipe Proyek</label>
                    <select id="project_type" name="project_type" class="form-select">
                        <option value="">Semua tipe</option>
                        <?php $__currentLoopData = $project_types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project_type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($project_type); ?>" <?php if(($filters['project_type'] ?? '') === $project_type): echo 'selected'; endif; ?>><?php echo e($project_type); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label" for="year">Tahun Selesai</label>
                    <input type="number" id="year" name="year" class="form-control" min="2000" max="2100" placeholder="<?php echo e(now()->year); ?>" value="<?php echo e($filters['year'] ?? ''); ?>">
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">Terapkan</button>
                    <a href="<?php echo e(route('project.history')); ?>" class="btn btn-outline-secondary" title="Reset filter" aria-label="Reset filter"><i class="ti ti-refresh"></i></a>
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-hover">
                <thead>
                    <tr>
                        <th>Kode Proyek</th>
                        <th>Kapal</th>
                        <th>Tipe</th>
                        <th>Pimpinan Proyek</th>
                        <th>Mulai Aktual</th>
                        <th>Selesai Aktual</th>
                        <th class="text-end">Durasi</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $started_at = $project->start_date_actual ?? $project->start_date_estimation;
                            $finished_at = $project->end_date_actual ?? $project->end_date_estimation;
                            $duration_days = $started_at && $finished_at ? $started_at->diffInDays($finished_at) + 1 : null;
                            $late = $project->end_date_actual && $project->end_date_estimation && $project->end_date_actual->gt($project->end_date_estimation);
                        ?>
                        <tr>
                            <td><span class="badge bg-blue-lt"><?php echo e($project->project_code); ?></span></td>
                            <td>
                                <div class="fw-bold"><?php echo e($project->ship?->name ?? '-'); ?></div>
                                <div class="text-secondary small"><?php echo e($project->ship?->company?->name ?? '-'); ?></div>
                            </td>
                            <td><?php echo e($project->project_type); ?></td>
                            <td><?php echo e($project->leader?->name ?? '-'); ?></td>
                            <td><?php echo e($started_at?->tanggal_indonesia() ?? '-'); ?></td>
                            <td>
                                <?php echo e($finished_at?->tanggal_indonesia() ?? '-'); ?>

                                <?php if($late): ?><span class="badge bg-danger-lt text-danger ms-1">Terlambat</span><?php endif; ?>
                            </td>
                            <td class="text-end"><?php echo e($duration_days !== null ? $duration_days.' hari' : '-'); ?></td>
                            <td><a href="<?php echo e(route('project.show', $project->unique_id)); ?>" class="btn btn-sm btn-outline-primary">Detail</a></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="8" class="text-center text-secondary py-4">Belum ada proyek yang selesai.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($projects->hasPages()): ?>
            <div class="card-footer d-flex align-items-center">
                <p class="m-0 text-secondary">Menampilkan <?php echo e($projects->firstItem()); ?>-<?php echo e($projects->lastItem()); ?> dari <?php echo e($projects->total()); ?> proyek</p>
                <div class="ms-auto"><?php echo e($projects->links('pagination::bootstrap-5')); ?></div>
            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        jQuery('#form-history-filter').validate({
            rules: { search_project: { maxlength: 100 }, year: { digits: true, min: 2000, max: 2100 } },
            messages: {
                search_project: { maxlength: 'Pencarian maksimal 100 karakter.' },
                year: { digits: 'Tahun harus berupa angka.', min: 'Tahun minimal 2000.', max: 'Tahun maksimal 2100.' },
            },
            errorElement: 'div',
            errorClass: 'invalid-feedback',
            highlight: function (element) { jQuery(element).addClass('is-invalid'); },
            unhighlight: function (element) { jQuery(element).removeClass('is-invalid'); },
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/project/history.blade.php ENDPATH**/ ?>