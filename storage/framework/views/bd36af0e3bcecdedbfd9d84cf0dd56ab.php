<?php
    $status_labels = ['Not Started' => 'Belum Mulai', 'In Progress' => 'Berjalan', 'Completed' => 'Selesai'];
    $status_colors = ['Not Started' => 'secondary', 'In Progress' => 'primary', 'Completed' => 'success'];
?>

<div class="row row-cards">
    <?php $__empty_1 = true; $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
            $ship = $project->ship;
            $status = $project->status ?? 'Not Started';
            $progress = max(0, min(100, (float) ($project->progress ?? 0)));
            $hue = crc32((string) ($ship?->name ?? $project->project_code)) % 360;
            $overdue = $project->end_date_estimation && $status !== 'Completed' && $project->end_date_estimation->isPast();
        ?>
        <div class="col-md-6 col-xl-4">
            <div class="card h-100">
                <div class="project-cover" style="background: linear-gradient(135deg, hsl(<?php echo e($hue); ?>, 55%, 32%), hsl(<?php echo e(($hue + 40) % 360); ?>, 60%, 45%));">
                    <i class="ti ti-ship cover-icon"></i>
                    <div class="cover-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="badge bg-white text-dark"><?php echo e($project->project_code); ?></span>
                            <span class="badge bg-<?php echo e($status_colors[$status] ?? 'secondary'); ?>"><?php echo e($status_labels[$status] ?? $status); ?></span>
                        </div>
                        <div>
                            <div class="cover-name text-truncate"><?php echo e($ship?->name ?? '-'); ?></div>
                            <div class="cover-meta text-truncate">
                                <?php echo e($ship?->type?->name ?? 'Tipe kapal -'); ?>

                                <?php if($ship?->imo_number): ?> &middot; IMO <?php echo e($ship->imo_number); ?> <?php endif; ?>
                                <?php if($ship?->flag): ?> &middot; <?php echo e($ship->flag); ?> <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-secondary"><?php echo e($project->project_type); ?></span>
                        <span class="fw-bold"><?php echo e(number_format($progress, 0)); ?>%</span>
                    </div>
                    <div class="progress progress-sm mb-3">
                        <div class="progress-bar bg-<?php echo e($status_colors[$status] ?? 'primary'); ?>" style="width: <?php echo e($progress); ?>%"></div>
                    </div>
                    <div class="small text-secondary d-grid gap-1">
                        <div class="text-truncate"><i class="ti ti-building me-1"></i><span class="fw-bold"><?php echo e($ship?->company?->name ?? '-'); ?></span></div>
                        <div class="text-truncate"><i class="ti ti-user me-1"></i>Pimpinan: <?php echo e($project->leader?->name ?? '-'); ?></div>
                        <div class="<?php echo e($overdue ? 'text-danger' : ''); ?>">
                            <i class="ti ti-calendar me-1"></i>
                            <?php echo e($project->start_date_estimation?->format('d/m/Y') ?? '-'); ?> s/d <?php echo e($project->end_date_estimation?->format('d/m/Y') ?? '-'); ?>

                            <?php if($overdue): ?> <span class="badge bg-danger-lt text-danger ms-1">Terlambat</span> <?php endif; ?>
                        </div>
                        <?php if($project->divisions->isNotEmpty()): ?>
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                <?php $__currentLoopData = $project->divisions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $division): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="badge bg-azure-lt"><?php echo e($division->name); ?></span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer d-flex gap-2 flex-wrap">
                    <a href="<?php echo e(route('project.show', $project->unique_id)); ?>" class="btn btn-sm btn-primary">Detail</a>
                    <a href="<?php echo e(route('project.edit', $project->unique_id)); ?>" class="btn btn-sm btn-outline-secondary">Ubah</a>
                    <a href="<?php echo e(route('project.job-document.workflow.index', $project->unique_id)); ?>" class="btn btn-sm btn-outline-success ms-auto">Dokumen</a>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center text-secondary py-5">
                    <i class="ti ti-ship-off fs-1 d-block mb-2"></i>
                    Tidak ada proyek yang sesuai.
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if(method_exists($projects, 'links') && $projects->hasPages()): ?>
    <div class="mt-3"><?php echo e($projects->links('pagination::bootstrap-5')); ?></div>
<?php endif; ?>
<?php /**PATH /home/snowy/projects/assi-repair/resources/views/project/partials/projectcards.blade.php ENDPATH**/ ?>