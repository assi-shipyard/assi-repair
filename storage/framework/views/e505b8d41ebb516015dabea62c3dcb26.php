<?php $__env->startSection('title', 'Detail Unit Organisasi'); ?>
<?php $__env->startSection('body_title', 'Detail Unit Organisasi'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('organizational-unit.edit', $organizational_unit->id)); ?>" class="btn btn-outline-primary">Ubah</a>
    <a href="<?php echo e(route('organizational-unit.index')); ?>" class="btn btn-outline-secondary">Kembali</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><div class="text-secondary">Nama</div><div><?php echo e($organizational_unit->name); ?></div></div>
                <div class="col-md-6"><div class="text-secondary">Kode</div><div><?php echo e($organizational_unit->code ?? '-'); ?></div></div>
                <div class="col-md-6"><div class="text-secondary">Jenis</div><div><?php echo e($organizational_unit->type_label); ?></div></div>
                <div class="col-md-6"><div class="text-secondary">Induk</div><div><?php echo e($organizational_unit->parent?->name ?? '-'); ?></div></div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views\organizational-unit\show.blade.php ENDPATH**/ ?>