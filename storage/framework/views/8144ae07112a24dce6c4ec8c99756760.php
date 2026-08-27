<?php $__env->startSection('title', 'Detail Proyek'); ?>
<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('project.index')); ?>" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i>Kembali
    </a>
    <a href="<?php echo e(route('project.edit', $project->unique_id)); ?>" class="btn btn-outline-primary">
        <i class="ti ti-pencil me-1"></i>Ubah
    </a>
    <a href="<?php echo e(route('project.job-document.workflow.index', $project->unique_id)); ?>" class="btn btn-primary">
        <i class="ti ti-files me-1"></i>Dokumen
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php
        $progress = max(0, min(100, (float) ($project->progress ?? 0)));
        $status = $project->status ?? 'Not Started';
        $status_class = match ($status) {
            'Completed' => 'bg-success-lt text-success',
            'In Progress' => 'bg-primary-lt text-primary',
            default => 'bg-secondary-lt text-secondary',
        };
    ?>

    <div class="card project-overview mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center g-4">
                <div class="col-auto d-none d-sm-block">
                    <span class="avatar avatar-xl bg-primary-lt text-primary">
                        <i class="ti ti-ship fs-1"></i>
                    </span>
                </div>
                <div class="col">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <span class="badge <?php echo e($status_class); ?>"><?php echo e($status); ?></span>
                        <span class="text-secondary small"><?php echo e($project->project_type ?: 'Tipe proyek belum ditentukan'); ?></span>
                    </div>
                    <h2 class="mb-1"><?php echo e($project->ship?->name ?? 'Kapal belum ditentukan'); ?></h2>
                    <div class="text-secondary">
                        <span class="font-monospace"><?php echo e($project->project_code); ?></span>
                        <span class="mx-1">&bull;</span>
                        <?php echo e($project->ship?->company?->name ?? 'Perusahaan belum ditentukan'); ?>

                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary">Kemajuan proyek</span>
                        <span class="fw-bold"><?php echo e(number_format($progress, 0)); ?>%</span>
                    </div>
                    <div class="progress progress-sm">
                        <div class="progress-bar" role="progressbar" style="width: <?php echo e($progress); ?>%" aria-valuenow="<?php echo e($progress); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-clipboard-data me-2 text-primary"></i>Informasi Proyek</h3>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-sm-6">
                            <div class="text-secondary small mb-1">Kode Proyek</div>
                            <div class="fw-semibold font-monospace"><?php echo e($project->project_code); ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-secondary small mb-1">Tipe Proyek</div>
                            <div class="fw-semibold"><?php echo e($project->project_type ?? '-'); ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-secondary small mb-1">Kapal</div>
                            <div class="fw-semibold"><?php echo e($project->ship?->name ?? '-'); ?></div>
                            <div class="small text-secondary"><?php echo e($project->ship?->company?->name ?? '-'); ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-secondary small mb-1">Status</div>
                            <span class="badge <?php echo e($status_class); ?>"><?php echo e($status); ?></span>
                        </div>
                        <div class="col-12">
                            <div class="text-secondary small mb-1">Catatan Proyek</div>
                            <div class="project-note <?php echo e(blank($project->comment) ? 'text-secondary' : ''); ?>">
                                <?php echo e($project->comment ?: 'Belum ada catatan proyek.'); ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-calendar-event me-2 text-primary"></i>Jadwal</h3>
                </div>
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <div class="text-secondary small">Estimasi Pelaksanaan</div>
                        <div class="fw-semibold mt-1">
                            <?php echo e($project->start_date_estimation?->translatedFormat('d M Y') ?? '-'); ?>

                            <span class="text-secondary mx-1">s.d.</span>
                            <?php echo e($project->end_date_estimation?->translatedFormat('d M Y') ?? '-'); ?>

                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="text-secondary small">Realisasi Pelaksanaan</div>
                        <div class="fw-semibold mt-1">
                            <?php echo e($project->start_date_actual?->translatedFormat('d M Y') ?? '-'); ?>

                            <span class="text-secondary mx-1">s.d.</span>
                            <?php echo e($project->end_date_actual?->translatedFormat('d M Y') ?? '-'); ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-users-group me-2 text-primary"></i>Tim Pelaksana</h3>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <span class="avatar bg-blue-lt text-blue me-3"><i class="ti ti-user-star"></i></span>
                                <div>
                                    <div class="text-secondary small">Pimpinan Proyek</div>
                                    <div class="fw-semibold"><?php echo e($project->leader?->name ?? 'Belum ditetapkan'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <span class="avatar bg-azure-lt text-azure me-3"><i class="ti ti-user-cog"></i></span>
                                <div>
                                    <div class="text-secondary small">PPC Proyek</div>
                                    <div class="fw-semibold"><?php echo e($project->ppc?->name ?? 'Belum ditetapkan'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="text-secondary small mb-2">Divisi Pelaksana</div>
                            <div class="d-flex flex-wrap gap-2">
                                <?php $__empty_0 = true; $__currentLoopData = $project->divisions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $division): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?>
                                    <span class="badge bg-secondary-lt text-secondary"><?php echo e($division->name); ?></span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?>
                                    <span class="text-secondary">Belum ada divisi pelaksana.</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <div class="text-secondary small">Dokumen Pekerjaan</div>
                            <div class="h1 mb-0"><?php echo e($project->job_documents->count()); ?></div>
                        </div>
                        <span class="avatar bg-green-lt text-green"><i class="ti ti-file-description"></i></span>
                    </div>
                    <p class="text-secondary mb-4">Kelola dokumen kerja dan tahapan workflow proyek.</p>
                    <a href="<?php echo e(route('project.job-document.workflow.index', $project->unique_id)); ?>" class="btn btn-outline-primary mt-auto">
                        Buka Dokumen<i class="ti ti-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-user-search me-2 text-primary"></i>Surveyor Pemilik Kapal</h3>
                </div>
                <div class="list-group list-group-flush">
                    <?php $__empty_0 = true; $__currentLoopData = $project->owner_surveyors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $owner_surveyor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?>
                        <div class="list-group-item py-3">
                            <div class="row align-items-center g-2">
                                <div class="col-auto">
                                    <span class="avatar bg-yellow-lt text-yellow"><?php echo e(strtoupper(mb_substr($owner_surveyor->name, 0, 1))); ?></span>
                                </div>
                                <div class="col-md-4">
                                    <div class="fw-semibold"><?php echo e($owner_surveyor->name); ?></div>
                                    <div class="small text-secondary"><?php echo e($owner_surveyor->position ?: 'Jabatan belum dicantumkan'); ?></div>
                                </div>
                                <div class="col-md-3 text-secondary">
                                    <i class="ti ti-building me-1"></i><?php echo e($owner_surveyor->company ?: '-'); ?>

                                </div>
                                <div class="col-md-3 text-secondary">
                                    <?php if($owner_surveyor->email): ?>
                                        <div><i class="ti ti-mail me-1"></i><?php echo e($owner_surveyor->email); ?></div>
                                    <?php endif; ?>
                                    <?php if($owner_surveyor->phone): ?>
                                        <div><i class="ti ti-phone me-1"></i><?php echo e($owner_surveyor->phone); ?></div>
                                    <?php endif; ?>
                                    <?php if(! $owner_surveyor->email && ! $owner_surveyor->phone): ?>
                                        <span>-</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?>
                        <div class="list-group-item text-secondary py-4">Belum ada surveyor pemilik kapal yang ditetapkan.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        .project-overview {
            border-top: 3px solid var(--tblr-primary);
        }

        .project-note {
            white-space: pre-line;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/project/show.blade.php ENDPATH**/ ?>