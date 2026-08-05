<?php $__env->startSection('title', 'Detail Proyek'); ?>
<?php $__env->startSection('body_title', 'Detail Proyek'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('project.job-document.workflow.index', $project->unique_id)); ?>" class="btn btn-outline-success">Dokumen</a>
    <a href="<?php echo e(route('project.edit', $project->unique_id)); ?>" class="btn btn-outline-primary">Ubah</a>
    <a href="<?php echo e(route('project.index')); ?>" class="btn btn-outline-secondary">Kembali</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><div class="text-secondary">Kode Proyek</div><div><?php echo e($project->project_code); ?></div></div>
                <div class="col-md-6"><div class="text-secondary">Kapal</div><div><?php echo e($project->ship?->name ?? '-'); ?></div></div>
                <div class="col-md-6"><div class="text-secondary">Tipe</div><div><?php echo e($project->project_type ?? '-'); ?></div></div>
                <div class="col-md-6"><div class="text-secondary">Status</div><div><?php echo e($project->status ?? '-'); ?></div></div>
                <div class="col-md-6"><div class="text-secondary">PIMPRO</div><div><?php echo e($project->leader?->name ?? '-'); ?></div></div>
                <div class="col-md-6"><div class="text-secondary">PPC</div><div><?php echo e($project->ppc?->name ?? '-'); ?></div></div>
                <div class="col-md-12"><div class="text-secondary">Divisi Pelaksana</div><div><?php echo e($project->divisions->pluck('name')->join(', ') ?: '-'); ?></div></div>
                <div class="col-md-12"><div class="text-secondary">Komentar</div><div><?php echo e($project->comment ?? '-'); ?></div></div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views\project\show.blade.php ENDPATH**/ ?>