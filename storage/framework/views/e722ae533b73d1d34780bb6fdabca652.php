<div class="row row-cards">
    <?php $__empty_1 = true; $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="col-md-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="text-secondary small"><?php echo e($project->project_code); ?></div>
                            <h3 class="mb-1"><?php echo e($project->ship?->name ?? '-'); ?></h3>
                        </div>
                        <span class="badge bg-primary-lt"><?php echo e($project->status ?? 'Not Started'); ?></span>
                    </div>
                    <div class="text-secondary mb-3"><?php echo e($project->project_type); ?></div>
                    <div class="small text-secondary">Progres</div>
                    <div class="progress mb-3"><div class="progress-bar" style="width: <?php echo e((float) ($project->progress ?? 0)); ?>%"></div></div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="<?php echo e(route('project.show', $project->unique_id)); ?>" class="btn btn-sm btn-outline-primary">Detail</a>
                        <a href="<?php echo e(route('project.edit', $project->unique_id)); ?>" class="btn btn-sm btn-outline-secondary">Ubah</a>
                        <a href="<?php echo e(route('project.job-document.workflow.index', $project->unique_id)); ?>" class="btn btn-sm btn-outline-success">Dokumen</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center text-secondary py-5">Belum ada data proyek.</div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php /**PATH D:\wamp64\www\assi-repair\resources\views\project\partials\projectcards.blade.php ENDPATH**/ ?>