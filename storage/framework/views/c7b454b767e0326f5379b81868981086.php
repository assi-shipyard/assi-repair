<?php $__env->startSection('title', 'Data Kelas Kapal'); ?>
<?php $__env->startSection('body_title', 'Data Kelas Kapal'); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead><tr><th>Nama</th><th>Singkatan</th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $ship_classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ship_class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr><td><?php echo e($ship_class->name); ?></td><td><?php echo e($ship_class->abbreviation ?? '-'); ?></td></tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="2" class="text-center text-secondary py-4">Belum ada kelas kapal.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views\ship\class\index.blade.php ENDPATH**/ ?>