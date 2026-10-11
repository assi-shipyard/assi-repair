<?php
    $type_meta = [
        'added' => ['label' => 'Ditambahkan', 'class' => 'bg-success-lt text-success', 'icon' => 'ti-plus'],
        'removed' => ['label' => 'Dihapus', 'class' => 'bg-danger-lt text-danger', 'icon' => 'ti-minus'],
        'changed' => ['label' => 'Diubah', 'class' => 'bg-warning-lt text-warning', 'icon' => 'ti-pencil'],
        'unchanged' => ['label' => 'Tetap', 'class' => 'bg-secondary-lt text-secondary', 'icon' => 'ti-equal'],
    ];
    $money_fields = ['est_price', 'price'];
    $show_unchanged = $show_unchanged ?? false;
    $format_value = function ($field, $value) use ($money_fields) {
        if ($value === null) {
            return '—';
        }
        return in_array($field, $money_fields, true) && is_numeric($value) ? number_format((float) $value, 0, ',', '.') : $value;
    };
?>

<div class="row g-2 mb-3">
    <?php $__currentLoopData = ['changed', 'added', 'removed', 'unchanged']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-6 col-md-3">
            <div class="card card-sm"><div class="card-body d-flex align-items-center gap-2">
                <span class="avatar avatar-sm <?php echo e($type_meta[$key]['class']); ?>"><i class="ti <?php echo e($type_meta[$key]['icon']); ?>"></i></span>
                <div><div class="fw-bold"><?php echo e($comparison['counts'][$key]); ?></div><div class="text-secondary small"><?php echo e($type_meta[$key]['label']); ?></div></div>
            </div></div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<?php if(count($comparison['totals']) > 0): ?>
    <div class="row g-2 mb-3">
        <?php $__currentLoopData = $comparison['totals']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $currency => $total): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-md-6 col-xl-4">
                <div class="card card-sm"><div class="card-body">
                    <div class="text-secondary small mb-1">Total Harga Estimasi (<?php echo e($currency); ?>)</div>
                    <div class="d-flex flex-wrap align-items-baseline gap-2">
                        <span class="text-secondary text-decoration-line-through"><?php echo e(number_format($total['old'], 0, ',', '.')); ?></span>
                        <i class="ti ti-arrow-right text-secondary"></i>
                        <span class="h3 mb-0"><?php echo e(number_format($total['new'], 0, ',', '.')); ?></span>
                    </div>
                    <div class="mt-1 <?php echo e($total['delta'] > 0 ? 'text-danger' : ($total['delta'] < 0 ? 'text-success' : 'text-secondary')); ?>">
                        <i class="ti <?php echo e($total['delta'] > 0 ? 'ti-trending-up' : ($total['delta'] < 0 ? 'ti-trending-down' : 'ti-minus')); ?>"></i>
                        <?php echo e($total['delta'] > 0 ? '+' : ''); ?><?php echo e(number_format($total['delta'], 0, ',', '.')); ?>

                        <?php if($total['percent'] !== null): ?> (<?php echo e($total['delta'] > 0 ? '+' : ''); ?><?php echo e(number_format($total['percent'], 1, ',', '.')); ?>%) <?php endif; ?>
                    </div>
                </div></div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead><tr><th class="w-1">Perubahan</th><th>Pekerjaan</th><th>Detail Perubahan (sebelum → sesudah)</th></tr></thead>
            <tbody>
                <?php ($visible_rows = collect($comparison['rows'])->filter(fn ($row) => $show_unchanged || $row['type'] !== 'unchanged')); ?>
                <?php $__empty_1 = true; $__currentLoopData = $visible_rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><span class="badge <?php echo e($type_meta[$row['type']]['class']); ?>"><?php echo e($type_meta[$row['type']]['label']); ?></span></td>
                        <td class="fw-semibold"><?php echo e($row['name']); ?></td>
                        <td>
                            <?php if($row['type'] === 'added'): ?>
                                <span class="text-success">Pekerjaan baru &middot; harga <?php echo e($format_value('est_price', $row['new']->est_price)); ?> <?php echo e($row['new']->est_currency); ?></span>
                            <?php elseif($row['type'] === 'removed'): ?>
                                <span class="text-danger">Dihapus &middot; harga sebelumnya <?php echo e($format_value('est_price', $row['old']->est_price)); ?> <?php echo e($row['old']->est_currency); ?></span>
                            <?php endif; ?>
                            <?php $__currentLoopData = $row['changes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $change): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="small"><span class="text-secondary"><?php echo e($change['label']); ?>:</span>
                                    <span class="text-danger text-decoration-line-through"><?php echo e($format_value($change['field'], $change['old'])); ?></span>
                                    <i class="ti ti-arrow-right text-secondary"></i>
                                    <span class="text-success fw-semibold"><?php echo e($format_value($change['field'], $change['new'])); ?></span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php $__currentLoopData = $row['materials']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $material): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="small mt-1">
                                    <span class="badge <?php echo e($type_meta[$material['type']]['class']); ?>">Material <?php echo e(strtolower($type_meta[$material['type']]['label'])); ?></span>
                                    <?php echo e($material['name']); ?>

                                    <?php $__currentLoopData = $material['changes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $change): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        &middot; <?php echo e($change['label']); ?>: <span class="text-danger text-decoration-line-through"><?php echo e($format_value($change['field'], $change['old'])); ?></span> → <span class="text-success"><?php echo e($format_value($change['field'], $change['new'])); ?></span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php if($row['type'] === 'unchanged'): ?><span class="text-secondary small">Tidak ada perubahan.</span><?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="3" class="text-center text-secondary py-4">Tidak ada perbedaan antara kedua revisi.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php /**PATH /home/snowy/projects/assi-repair/resources/views/project/job-document/partials/diff-rows.blade.php ENDPATH**/ ?>