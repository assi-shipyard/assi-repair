<?php $__env->startSection('title', 'Data Proyek'); ?>
<?php $__env->startSection('body_title', 'Data Proyek'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('project.create')); ?>" class="btn btn-primary">
        <i class="ti ti-plus me-1"></i>Tambah Proyek
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        .project-cover { position: relative; height: 120px; color: #fff; overflow: hidden; border-radius: var(--tblr-card-border-radius) var(--tblr-card-border-radius) 0 0; }
        .project-cover .cover-icon { position: absolute; right: -8px; bottom: -22px; font-size: 7.5rem; opacity: .18; line-height: 1; }
        .project-cover .cover-body { position: relative; padding: .75rem 1rem; height: 100%; display: flex; flex-direction: column; justify-content: space-between; }
        .project-cover .cover-name { font-size: 1.15rem; font-weight: 600; line-height: 1.25; }
        .project-cover .cover-meta { font-size: .75rem; opacity: .9; }
        .status-tab.active { border-color: var(--tblr-primary); }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php
        $status_labels = ['Not Started' => 'Belum Mulai', 'In Progress' => 'Berjalan', 'Completed' => 'Selesai'];
        $status_colors = ['Not Started' => 'secondary', 'In Progress' => 'primary', 'Completed' => 'success'];
        $active_status = $filters['status'] ?? '';
        $total_all = $status_counts->sum();
    ?>

    <div class="row row-cards mb-3">
        <div class="col-6 col-lg-3">
            <a href="<?php echo e(route('project.index')); ?>" class="card card-sm text-decoration-none status-tab <?php echo e($active_status === '' ? 'active' : ''); ?>">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar bg-dark-lt"><i class="ti ti-layout-grid"></i></span>
                    <div><div class="fw-bold"><?php echo e($total_all); ?></div><div class="text-secondary small">Semua Proyek</div></div>
                </div>
            </a>
        </div>
        <?php $__currentLoopData = $status_labels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status_key => $status_label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-6 col-lg-3">
                <a href="<?php echo e(route('project.index', array_merge(request()->except('status', 'page'), ['status' => $status_key]))); ?>"
                   class="card card-sm text-decoration-none status-tab <?php echo e($active_status === $status_key ? 'active' : ''); ?>">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="avatar bg-<?php echo e($status_colors[$status_key]); ?>-lt text-<?php echo e($status_colors[$status_key]); ?>"><i class="ti ti-<?php echo e(['Not Started' => 'clock', 'In Progress' => 'progress', 'Completed' => 'circle-check'][$status_key]); ?>"></i></span>
                        <div><div class="fw-bold"><?php echo e($status_counts[$status_key] ?? 0); ?></div><div class="text-secondary small"><?php echo e($status_label); ?></div></div>
                    </div>
                </a>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <form class="card mb-3" method="GET" action="<?php echo e(route('project.index')); ?>" id="project_filter_form">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-12 col-lg-5">
                    <div class="input-icon">
                        <span class="input-icon-addon"><i class="ti ti-search"></i></span>
                        <input class="form-control" name="search_project" maxlength="100" value="<?php echo e($filters['search_project'] ?? ''); ?>" placeholder="Kode proyek, tipe, nama kapal, IMO, atau call sign">
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <select class="form-select" name="status" aria-label="Status">
                        <option value="">Semua status</option>
                        <?php $__currentLoopData = $status_labels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status_key => $status_label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($status_key); ?>" <?php if($active_status === $status_key): echo 'selected'; endif; ?>><?php echo e($status_label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-6 col-lg-2">
                    <select class="form-select" name="project_type" aria-label="Tipe proyek">
                        <option value="">Semua tipe</option>
                        <?php $__currentLoopData = $project_types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($type); ?>" <?php if(($filters['project_type'] ?? '') === $type): echo 'selected'; endif; ?>><?php echo e($type); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-12 col-lg-3">
                    <select class="form-select" name="sort" aria-label="Urutkan">
                        <?php $__currentLoopData = ['latest' => 'Terbaru', 'oldest' => 'Terlama', 'progress_desc' => 'Progres tertinggi', 'progress_asc' => 'Progres terendah', 'ship_name' => 'Nama kapal (A-Z)']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sort_key => $sort_label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($sort_key); ?>" <?php if(($filters['sort'] ?? 'latest') === $sort_key): echo 'selected'; endif; ?>>Urutkan: <?php echo e($sort_label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>
            <div class="collapse <?php echo e(collect($filters)->only(['ship_id', 'division_id', 'start_from', 'start_to'])->filter()->isNotEmpty() ? 'show' : ''); ?>" id="advanced_filter">
                <div class="row g-2 mt-1">
                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label small mb-1">Kapal</label>
                        <select class="form-select" name="ship_id">
                            <option value="">Semua kapal</option>
                            <?php $__currentLoopData = $ships; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ship): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($ship->id); ?>" <?php if((int) ($filters['ship_id'] ?? 0) === $ship->id): echo 'selected'; endif; ?>><?php echo e($ship->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label small mb-1">Divisi</label>
                        <select class="form-select" name="division_id">
                            <option value="">Semua divisi</option>
                            <?php $__currentLoopData = $divisions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $division): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($division->id); ?>" <?php if((int) ($filters['division_id'] ?? 0) === $division->id): echo 'selected'; endif; ?>><?php echo e($division->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-6 col-lg-3">
                        <label class="form-label small mb-1">Estimasi mulai dari</label>
                        <input type="date" class="form-control" name="start_from" value="<?php echo e($filters['start_from'] ?? ''); ?>">
                    </div>
                    <div class="col-6 col-lg-3">
                        <label class="form-label small mb-1">Estimasi mulai sampai</label>
                        <input type="date" class="form-control" name="start_to" value="<?php echo e($filters['start_to'] ?? ''); ?>">
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <button class="btn btn-primary" type="submit"><i class="ti ti-filter me-1"></i>Terapkan</button>
                <a class="btn btn-outline-secondary" href="<?php echo e(route('project.index')); ?>">Reset</a>
                <a class="btn btn-link ms-auto" data-bs-toggle="collapse" href="#advanced_filter" role="button">Filter lanjutan</a>
            </div>
        </div>
    </form>

    <div class="text-secondary small mb-2">Menampilkan <strong><?php echo e($projects->total()); ?></strong> proyek</div>

    <div id="project_cards">
        <?php echo $__env->make('project.partials.projectcards', ['projects' => $projects], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        $(function () {
            $('#project_filter_form select').not('#advanced_filter select').on('change', function () {
                $('#project_filter_form').trigger('submit');
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/project/index.blade.php ENDPATH**/ ?>