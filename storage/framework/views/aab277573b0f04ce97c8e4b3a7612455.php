<div class="card mb-3">
    <div class="card-header"><h3 class="card-title"><?php echo e($heading); ?> <span class="badge bg-secondary-lt ms-2"><?php echo e($items->total()); ?></span></h3></div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead><tr><th>Kode / Nama</th><th>Satuan</th><th class="text-end">Harga Satuan (IDR)</th><th>Minimum Ditagihkan</th><?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin')): ?><th></th><?php endif; ?></tr></thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><div class="text-secondary small"><?php echo e($item->code); ?></div><div class="fw-bold"><?php echo e($item->name); ?></div><?php if($item->description): ?><div class="text-secondary small"><?php echo e($item->description); ?></div><?php endif; ?></td>
                        <td><?php echo e($item->unit); ?></td>
                        <td class="text-end text-nowrap"><?php echo e(number_format((float) $item->unit_price, 2, ',', '.')); ?></td>
                        <td class="text-nowrap">
                            <?php if($item->has_minimum_charge): ?>
                                <span class="badge bg-orange-lt"><?php echo e(number_format((float) $item->minimum_quantity, 3, ',', '.')); ?> <?php echo e($item->unit); ?></span>
                            <?php else: ?>
                                <span class="text-secondary">Tanpa minimum</span>
                            <?php endif; ?>
                        </td>
                        <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin')): ?>
                            <td class="text-nowrap">
                                <a href="<?php echo e(route('price-book.items.edit', [$price_book, $item])); ?>" class="btn btn-sm btn-outline-primary">Ubah</a>
                                <form action="<?php echo e(route('price-book.items.destroy', [$price_book, $item])); ?>" method="POST" class="d-inline price-delete-form">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="<?php echo e(auth()->user()?->hasRole('admin') ? 5 : 4); ?>" class="text-center text-secondary py-4">Belum ada <?php echo e(strtolower($heading)); ?> dalam buku ini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer"><?php echo e($items->withQueryString()->links()); ?></div>
</div>
<?php /**PATH /home/snowy/projects/assi-repair/resources/views/price-book/partials/items.blade.php ENDPATH**/ ?>