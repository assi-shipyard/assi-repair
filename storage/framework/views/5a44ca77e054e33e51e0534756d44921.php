<?php $__env->startSection('title', 'Kamus Harga'); ?>
<?php $__env->startSection('body_title', 'Kamus Harga'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin')): ?>
        <a href="<?php echo e(route('price-book.create')); ?>" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Tambah Buku Harga</a>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="alert alert-info">
        <div><strong>Buku utama</strong> menjadi acuan harga galangan. Setiap proyek memilih buku yang digunakan tanpa dibatasi pelanggan. Kaitan pelanggan pada buku hanya informasi opsional. Setiap buku memiliki daftar material dan jasa yang berdiri sendiri.</div>
    </div>

    <form id="price-book-filter" action="<?php echo e(route('price-book.index')); ?>" method="GET" class="card mb-3" novalidate>
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-lg-5">
                    <label for="search" class="form-label">Cari Buku Harga</label>
                    <div class="input-icon">
                        <span class="input-icon-addon"><i class="ti ti-search"></i></span>
                        <input type="search" id="search" name="search" class="form-control" maxlength="100" placeholder="Kode, nama buku, atau pelanggan..." value="<?php echo e($filters['search']); ?>">
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <label for="book_type" class="form-label">Jenis Buku</label>
                    <select id="book_type" name="book_type" class="form-select">
                        <option value="">Semua jenis</option>
                        <option value="reference" <?php if($filters['book_type'] === 'reference'): echo 'selected'; endif; ?>>Buku Utama</option>
                        <option value="customer" <?php if($filters['book_type'] === 'customer'): echo 'selected'; endif; ?>>Buku Pelanggan</option>
                    </select>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <label for="company_uuid" class="form-label">Pelanggan</label>
                    <select id="company_uuid" name="company_uuid" class="form-select">
                        <option value="">Semua pelanggan</option>
                        <?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($company->unique_id); ?>" <?php if($filters['company_uuid'] === $company->unique_id): echo 'selected'; endif; ?>><?php echo e($company->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-12 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary"><i class="ti ti-filter me-1"></i>Terapkan Filter</button>
                    <?php if($has_filters): ?>
                        <a href="<?php echo e(route('price-book.index')); ?>" class="btn btn-outline-secondary">Reset Filter</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </form>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0">Daftar Buku Harga</h3>
        <span class="text-secondary small" role="status">
            <?php if($price_books->count()): ?>
                Menampilkan <?php echo e($price_books->firstItem()); ?>–<?php echo e($price_books->lastItem()); ?> dari <?php echo e($price_books->total()); ?> buku
            <?php else: ?>
                <?php echo e($price_books->total()); ?> buku ditemukan
            <?php endif; ?>
        </span>
    </div>
    <div class="row row-cards">
        <?php $__empty_1 = true; $__currentLoopData = $price_books; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $price_book): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="col-12 col-md-6 col-xl-4">
                <article class="card h-100">
                    <div class="card-status-top <?php echo e($price_book->reference_slot ? 'bg-blue' : 'bg-green'); ?>"></div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <span class="avatar avatar-sm <?php echo e($price_book->reference_slot ? 'bg-blue-lt' : 'bg-green-lt'); ?>"><i class="ti ti-book-2" aria-hidden="true"></i></span>
                            <span class="badge <?php echo e($price_book->reference_slot ? 'bg-blue-lt' : 'bg-green-lt'); ?>"><?php echo e($price_book->reference_slot ? 'Buku Utama' : 'Buku Pelanggan'); ?></span>
                        </div>
                        <div class="subheader text-break"><?php echo e($price_book->code); ?></div>
                        <h3 class="card-title text-break mt-1 mb-2">
                            <a href="<?php echo e(route('price-book.show', $price_book)); ?>" class="text-reset"><?php echo e($price_book->name); ?></a>
                        </h3>
                        <div class="text-secondary text-break mb-3"><i class="ti ti-building me-1" aria-hidden="true"></i><?php echo e($price_book->company?->name ?? ($price_book->reference_slot ? 'Acuan galangan' : 'Tidak terkait pelanggan tertentu')); ?></div>
                        <?php if($price_book->description): ?>
                            <p class="text-secondary small text-break"><?php echo e(\Illuminate\Support\Str::limit($price_book->description, 160)); ?></p>
                        <?php endif; ?>
                        <div class="row g-2">
                            <div class="col-6"><div class="border rounded p-2"><div class="subheader">Material</div><div class="h2 mb-0"><?php echo e($price_book->materials_count); ?></div></div></div>
                            <div class="col-6"><div class="border rounded p-2"><div class="subheader">Jasa</div><div class="h2 mb-0"><?php echo e($price_book->services_count); ?></div></div></div>
                        </div>
                    </div>
                    <div class="card-footer mt-auto">
                        <div class="text-secondary small mb-2">Diperbarui <?php echo e($price_book->updated_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d F Y H:i')); ?> WIB</div>
                        <a href="<?php echo e(route('price-book.show', $price_book)); ?>" class="btn btn-outline-primary w-100">Buka Buku<span class="visually-hidden"> <?php echo e($price_book->name); ?></span><i class="ti ti-arrow-right ms-2" aria-hidden="true"></i></a>
                    </div>
                </article>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-12">
                <div class="card">
                    <div class="empty">
                        <div class="empty-icon"><i class="ti ti-books fs-1" aria-hidden="true"></i></div>
                        <p class="empty-title"><?php echo e($has_filters ? 'Tidak ada buku yang sesuai' : 'Belum ada buku harga'); ?></p>
                        <p class="empty-subtitle text-secondary"><?php echo e($has_filters ? 'Coba kata kunci atau pilihan filter lain.' : 'Tambahkan buku utama sebagai acuan harga galangan terlebih dahulu.'); ?></p>
                        <?php if($has_filters): ?>
                            <div class="empty-action"><a href="<?php echo e(route('price-book.index')); ?>" class="btn btn-outline-primary">Reset Filter</a></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="mt-3"><?php echo e($price_books->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <?php echo $__env->make('price-book.partials.validation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <script>
        $(function () {
            $('#price-book-filter').validate({
                ...price_form_options,
                rules: { search: { maxlength: 100 } }
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/price-book/index.blade.php ENDPATH**/ ?>