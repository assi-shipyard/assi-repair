<?php $__env->startSection('title', 'Detail Kapal'); ?>
<?php $__env->startSection('body_title', 'Detail Kapal'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('ship.edit', $ship->unique_id)); ?>" class="btn btn-outline-primary">Ubah</a>
    <a href="<?php echo e(route('ship.index')); ?>" class="btn btn-outline-secondary">Kembali</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php ($ship_build_year = $ship->build_year ?? $ship->year_built ?? null); ?>

    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><div class="text-secondary">Nama</div><div><?php echo e($ship->name); ?></div></div>
                <div class="col-md-6"><div class="text-secondary">Perusahaan</div><div><?php echo e($ship->company?->name ?? '-'); ?></div></div>
                <div class="col-md-6"><div class="text-secondary">Jenis</div><div><?php echo e($ship->type?->name ?? '-'); ?></div></div>
                <div class="col-md-6"><div class="text-secondary">Kelas</div><div><?php echo e($ship->classification?->name ?? '-'); ?></div></div>
                <div class="col-md-6"><div class="text-secondary">IMO</div><div><?php echo e($ship->imo_number ?? '-'); ?></div></div>
                <div class="col-md-6"><div class="text-secondary">Call Sign</div><div><?php echo e($ship->call_sign ?? '-'); ?></div></div>
                <div class="col-md-6"><div class="text-secondary">MMSI</div><div><?php echo e($ship->mmsi_number ?? '-'); ?></div></div>
                <div class="col-md-6"><div class="text-secondary">Tahun Pembuatan</div><div><?php echo e($ship_build_year ?? '-'); ?></div></div>
                <div class="col-md-3"><div class="text-secondary">LOA</div><div><?php echo e($ship->length_overall ?? '-'); ?></div></div>
                <div class="col-md-3"><div class="text-secondary">Breadth</div><div><?php echo e($ship->breadth ?? '-'); ?></div></div>
                <div class="col-md-3"><div class="text-secondary">Height</div><div><?php echo e($ship->height ?? '-'); ?></div></div>
                <div class="col-md-3"><div class="text-secondary">Gross Tonnage</div><div><?php echo e($ship->gross_tonnage ?? '-'); ?></div></div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views\ship\show.blade.php ENDPATH**/ ?>