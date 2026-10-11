<?php $__env->startSection('title', $price_book->exists ? 'Ubah Buku Harga' : 'Tambah Buku Harga'); ?>
<?php $__env->startSection('body_title', $price_book->exists ? 'Ubah Buku Harga' : 'Tambah Buku Harga'); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <form id="price-book-form" action="<?php echo e($price_book->exists ? route('price-book.update', $price_book) : route('price-book.store')); ?>" method="POST" class="card" novalidate>
                <?php echo csrf_field(); ?>
                <?php if($price_book->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
                <input type="hidden" name="reference_slot" value="1">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="code" class="form-label required">Kode Buku</label>
                            <input id="code" name="code" class="form-control" maxlength="30" value="<?php echo e(old('code', $price_book->code)); ?>" required>
                        </div>
                        <div class="col-md-8">
                            <label for="name" class="form-label required">Nama Buku</label>
                            <input id="name" name="name" class="form-control" maxlength="255" value="<?php echo e(old('name', $price_book->name)); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="book_type" class="form-label required">Jenis Buku</label>
                            <?php ($book_type = old('book_type', $price_book->exists ? ($price_book->reference_slot ? 'reference' : 'customer') : ($has_reference ? 'customer' : 'reference'))); ?>
                            <select id="book_type" name="book_type" class="form-select" required>
                                <option value="reference" <?php if($book_type === 'reference'): echo 'selected'; endif; ?>>Buku Utama</option>
                                <option value="customer" <?php if($book_type === 'customer'): echo 'selected'; endif; ?>>Buku Pelanggan</option>
                            </select>
                        </div>
                        <div class="col-md-8" id="customer-field">
                            <label for="company_uuid" class="form-label">Pelanggan (Opsional)</label>
                            <select id="company_uuid" name="company_uuid" class="form-select">
                                <option value="">Tidak terkait pelanggan tertentu</option>
                                <?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($company->unique_id); ?>" <?php if(old('company_uuid', $price_book->company?->unique_id) === $company->unique_id): echo 'selected'; endif; ?>><?php echo e($company->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label">Keterangan / Referensi Kesepakatan</label>
                            <textarea id="description" name="description" class="form-control" maxlength="2000" rows="3"><?php echo e(old('description', $price_book->description)); ?></textarea>
                        </div>
                    </div>
                    <div class="text-secondary small mt-3">Hanya satu buku utama yang diperbolehkan. Pelanggan bersifat informasi opsional; setiap proyek dapat memilih buku mana pun. Harga disimpan dalam Rupiah (IDR).</div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <a class="btn" href="<?php echo e($price_book->exists ? route('price-book.show', $price_book) : route('price-book.index')); ?>">Batal</a>
                    <button class="btn btn-primary" type="submit">Simpan Buku</button>
                </div>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <?php echo $__env->make('price-book.partials.validation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <script>
        $(function () {
            function toggle_customer() {
                const is_customer = $('#book_type').val() === 'customer';
                $('#customer-field').toggle(is_customer);
                $('#company_uuid').prop('disabled', !is_customer);
            }
            $('#book_type').on('change', toggle_customer);
            toggle_customer();
            $('#price-book-form').validate({
                ...price_form_options,
                rules: {
                    code: { required: true, maxlength: 30, price_code: true },
                    name: { required: true, maxlength: 255 },
                    book_type: { required: true },
                    description: { maxlength: 2000 }
                }
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/price-book/form.blade.php ENDPATH**/ ?>