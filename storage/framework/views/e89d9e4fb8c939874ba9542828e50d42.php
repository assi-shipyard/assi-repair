<?php $__env->startSection('title', 'Data Jenis Kapal'); ?>
<?php $__env->startSection('body_title', 'Data Jenis Kapal'); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead><tr><th>Nama</th></tr></thead>
                    <tbody>
                        <?php $__empty_0 = true; $__currentLoopData = $ship_types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ship_type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?>
                            <tr><td><?php echo e($ship_type->name); ?></td></tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?>
                            <tr><td class="text-center text-secondary py-4">Belum ada jenis kapal.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/ship/type/index.blade.php ENDPATH**/ ?>