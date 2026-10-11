<?php $__env->startSection('title', 'Kamus Harga - '.$price_book->name); ?>
<?php $__env->startSection('body_title', $price_book->name); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('price-book.index')); ?>" class="btn">Daftar Buku</a>
    <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin')): ?>
        <a href="<?php echo e(route('price-book.edit', $price_book)); ?>" class="btn btn-outline-primary">Ubah Buku</a>
        <a href="<?php echo e(route('price-book.items.create', $price_book)); ?>" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Tambah Material / Jasa</a>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><div class="subheader">Kode / Jenis Buku</div><div><?php echo e($price_book->code); ?> <span class="badge bg-blue-lt ms-2"><?php echo e($price_book->reference_slot ? 'Buku Utama' : 'Buku Pelanggan'); ?></span></div></div>
                <div class="col-md-4"><div class="subheader">Pelanggan (Informasi Opsional)</div><div><?php echo e($price_book->company?->name ?? ($price_book->reference_slot ? 'Acuan galangan' : 'Tidak terkait pelanggan tertentu')); ?></div></div>
                <div class="col-md-4"><div class="subheader">Jumlah Item</div><div><?php echo e($materials->total()); ?> material / <?php echo e($services->total()); ?> jasa</div></div>
                <?php if($price_book->description): ?><div class="col-12 text-secondary"><?php echo e($price_book->description); ?></div><?php endif; ?>
            </div>
        </div>
    </div>
    <div class="alert alert-info">
        <div><strong>Batas minimum menggunakan satuan item.</strong> Contoh: pelat baja minimum 15 kg atau jasa crane minimum 8 jam. Kuantitas yang ditagihkan paling sedikit sebesar batas minimum. Setiap kombinasi kode dan satuan dapat memiliki harga berbeda; isi buku ini tidak otomatis mengikuti buku utama.</div>
    </div>
    <?php echo $__env->make('price-book.partials.items', ['items' => $materials, 'heading' => 'Material'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('price-book.partials.items', ['items' => $services, 'heading' => 'Jasa'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin')): ?>
        <?php if($materials->total() + $services->total() === 0): ?>
            <form action="<?php echo e(route('price-book.destroy', $price_book)); ?>" method="POST" class="price-delete-form">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button type="submit" class="btn btn-outline-danger">Hapus Buku Kosong</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        $(function () {
            $('.price-delete-form').on('submit', function (event) {
                if (!confirm('Hapus data ini? Tindakan ini tidak dapat dibatalkan.')) event.preventDefault();
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/price-book/show.blade.php ENDPATH**/ ?>