<?php $__env->startSection('title', 'Data Proyek'); ?>
<?php $__env->startSection('body_title', 'Data Proyek'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('project.create')); ?>" class="btn btn-primary">Tambah Proyek</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <form class="card mb-4" method="GET" action="<?php echo e(route('project.index')); ?>">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-10">
                    <label class="form-label">Cari Proyek</label>
                    <input class="form-control" name="search_project" value="<?php echo e(request('search_project')); ?>" placeholder="Kode proyek, tipe proyek, atau nama kapal">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-primary w-100" type="submit">Cari</button>
                </div>
            </div>
        </div>
    </form>

    <div id="project_cards">
        <?php echo $__env->make('project.partials.projectcards', ['projects' => $projects], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views\project\index.blade.php ENDPATH**/ ?>