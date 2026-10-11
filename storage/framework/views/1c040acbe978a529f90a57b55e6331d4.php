<?php $__env->startSection('title', 'Data Jenis Reparasi'); ?>
<?php $__env->startSection('body_title', 'Data Jenis Reparasi'); ?>

<?php
    $form_action = \Illuminate\Support\Str::startsWith((string) old('form_action'), url('/price-dictionary'))
        ? old('form_action')
        : route('price-dictionary.store');
    $is_admin = auth()->user()?->hasRole('admin');
    $active_count = $repair_types->where('is_active', true)->count();
?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <?php if($is_admin): ?>
        <button type="button" class="btn btn-primary" id="btn-add-repair-type">
            <i class="ti ti-plus me-1"></i>Tambah Jenis Reparasi
        </button>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="row row-cards mb-3">
        <div class="col-6 col-md-3">
            <div class="card card-sm"><div class="card-body">
                <div class="subheader">Total Jenis</div>
                <div class="h2 mb-0"><?php echo e($repair_types->count()); ?></div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-sm"><div class="card-body">
                <div class="subheader">Aktif</div>
                <div class="h2 mb-0 text-success"><?php echo e($active_count); ?></div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-sm"><div class="card-body">
                <div class="subheader">Nonaktif</div>
                <div class="h2 mb-0 text-secondary"><?php echo e($repair_types->count() - $active_count); ?></div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-sm"><div class="card-body">
                <div class="subheader">Kategori</div>
                <div class="h2 mb-0"><?php echo e($repair_types->pluck('category')->unique()->count()); ?></div>
            </div></div>
        </div>
    </div>

    <div class="alert alert-info">
        <div class="d-flex">
            <i class="ti ti-info-circle fs-2 me-2"></i>
            <div>Harga standar bersifat acuan estimasi dalam Rupiah (IDR) dan dapat disesuaikan pada setiap dokumen pekerjaan proyek.</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Jenis Reparasi</h3>
            <div class="card-actions d-flex gap-2">
                <select id="filter-category" class="form-select form-select-sm">
                    <option value="">Semua kategori</option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category_label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($category_label); ?>"><?php echo e($category_label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <input type="search" id="filter-search" class="form-control form-control-sm" placeholder="Cari kode / nama...">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-hover" id="repair-type-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Jenis Reparasi</th>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        <th class="text-end">Harga Standar (IDR)</th>
                        <th>Status</th>
                        <?php if($is_admin): ?><th class="w-1">Aksi</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_0 = true; $__currentLoopData = $repair_types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $repair_type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?>
                        <tr data-category="<?php echo e($categories[$repair_type->category] ?? $repair_type->category); ?>">
                            <td><span class="badge bg-blue-lt"><?php echo e($repair_type->code); ?></span></td>
                            <td>
                                <div class="fw-bold"><?php echo e($repair_type->name); ?></div>
                                <?php if($repair_type->description): ?>
                                    <div class="text-secondary small text-truncate" style="max-width: 360px"><?php echo e($repair_type->description); ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($categories[$repair_type->category] ?? $repair_type->category); ?></td>
                            <td><?php echo e($repair_type->unit); ?></td>
                            <td class="text-end"><?php echo e($repair_type->standard_price !== null ? number_format((float) $repair_type->standard_price, 2, ',', '.') : '-'); ?></td>
                            <td>
                                <span class="badge <?php echo e($repair_type->is_active ? 'bg-success-lt' : 'bg-secondary-lt'); ?>"><?php echo e($repair_type->is_active ? 'Aktif' : 'Nonaktif'); ?></span>
                            </td>
                            <?php if($is_admin): ?>
                                <td class="text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-repair-type"
                                        data-action="<?php echo e(route('price-dictionary.update', $repair_type)); ?>"
                                        data-code="<?php echo e($repair_type->code); ?>"
                                        data-name="<?php echo e($repair_type->name); ?>"
                                        data-category="<?php echo e($repair_type->category); ?>"
                                        data-unit="<?php echo e($repair_type->unit); ?>"
                                        data-price="<?php echo e($repair_type->standard_price); ?>"
                                        data-description="<?php echo e($repair_type->description); ?>"
                                        data-active="<?php echo e($repair_type->is_active ? 1 : 0); ?>">Ubah</button>
                                    <form action="<?php echo e(route('price-dictionary.destroy', $repair_type)); ?>" method="POST" class="d-inline form-delete-repair-type">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?>
                        <tr><td colspan="<?php echo e($is_admin ? 7 : 6); ?>" class="text-center text-secondary py-4">Belum ada data jenis reparasi.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('modal'); ?>
    <?php if($is_admin): ?>
        <div class="modal modal-blur fade" id="modal-repair-type" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" id="form-repair-type" method="POST" action="<?php echo e($form_action); ?>" novalidate>
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="_method" id="form-method" value="<?php echo e(old('_method') === 'PUT' ? 'PUT' : 'POST'); ?>">
                    <input type="hidden" name="form_action" id="form-action" value="<?php echo e($form_action); ?>">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-repair-type-title">Tambah Jenis Reparasi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label required" for="code">Kode</label>
                                <input type="text" id="code" name="code" class="form-control text-uppercase <?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" maxlength="30" value="<?php echo e(old('code')); ?>">
                                <?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label required" for="name">Nama Jenis Reparasi</label>
                                <input type="text" id="name" name="name" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" maxlength="255" value="<?php echo e(old('name')); ?>">
                                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required" for="category">Kategori</label>
                                <select id="category" name="category" class="form-select <?php $__errorArgs = ['category'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <option value="">Pilih kategori</option>
                                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category_key => $category_label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($category_key); ?>" <?php if(old('category') === $category_key): echo 'selected'; endif; ?>><?php echo e($category_label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['category'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required" for="unit">Satuan</label>
                                <input type="text" id="unit" name="unit" class="form-control <?php $__errorArgs = ['unit'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" maxlength="30" placeholder="m², kg, jam, unit" value="<?php echo e(old('unit')); ?>">
                                <?php $__errorArgs = ['unit'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="standard_price">Harga Standar (IDR)</label>
                                <input type="number" step="0.01" min="0" id="standard_price" name="standard_price" class="form-control <?php $__errorArgs = ['standard_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('standard_price')); ?>">
                                <?php $__errorArgs = ['standard_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="description">Deskripsi</label>
                                <textarea id="description" name="description" rows="3" class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" maxlength="2000"><?php echo e(old('description')); ?></textarea>
                                <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-12">
                                <label class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" <?php if(old('is_active', $errors->any() ? false : true)): echo 'checked'; endif; ?>>
                                    <span class="form-check-label">Aktif</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        (function ($) {
            const rows = $('#repair-type-table tbody tr[data-category]');

            function apply_filter() {
                const keyword = $('#filter-search').val().toLowerCase();
                const category = $('#filter-category').val();

                rows.each(function () {
                    const row = $(this);
                    const matches_text = row.text().toLowerCase().includes(keyword);
                    const matches_category = !category || row.data('category') === category;
                    row.toggle(matches_text && matches_category);
                });
            }

            $('#filter-search').on('input', apply_filter);
            $('#filter-category').on('change', apply_filter);

            <?php if($is_admin): ?>
            const modal_element = document.getElementById('modal-repair-type');
            const modal = new bootstrap.Modal(modal_element);
            const form = $('#form-repair-type');
            const store_url = <?php echo \Illuminate\Support\Js::from(route('price-dictionary.store'))->toHtml() ?>;

            function open_form(action, method, title, values) {
                form.attr('action', action);
                $('#form-action').val(action);
                $('#form-method').val(method);
                $('#modal-repair-type-title').text(title);
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback').remove();
                $('#code').val(values.code);
                $('#name').val(values.name);
                $('#category').val(values.category);
                $('#unit').val(values.unit);
                $('#standard_price').val(values.price);
                $('#description').val(values.description);
                $('#is_active').prop('checked', values.active === 1);
                modal.show();
            }

            $('#btn-add-repair-type').on('click', function () {
                open_form(store_url, 'POST', 'Tambah Jenis Reparasi', { code: '', name: '', category: '', unit: '', price: '', description: '', active: 1 });
            });

            $('.btn-edit-repair-type').on('click', function () {
                const data = $(this).data();
                open_form(data.action, 'PUT', 'Ubah Jenis Reparasi', {
                    code: data.code, name: data.name, category: data.category, unit: data.unit,
                    price: data.price, description: data.description, active: Number(data.active),
                });
            });

            $('.form-delete-repair-type').on('submit', function (event) {
                event.preventDefault();
                const delete_form = this;

                Swal.fire({
                    title: 'Hapus jenis reparasi?',
                    text: 'Data yang dihapus tidak dapat dikembalikan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                }).then(function (result) {
                    if (result.isConfirmed) { delete_form.submit(); }
                });
            });

            form.validate({
                ignore: [],
                rules: {
                    code: { required: true, maxlength: 30, pattern: /^[A-Za-z0-9._-]+$/ },
                    name: { required: true, maxlength: 255 },
                    category: { required: true },
                    unit: { required: true, maxlength: 30 },
                    standard_price: { number: true, min: 0 },
                    description: { maxlength: 2000 },
                },
                messages: {
                    code: { required: 'Kode wajib diisi.', pattern: 'Kode hanya boleh huruf, angka, titik, garis bawah, dan strip.' },
                    name: { required: 'Nama jenis reparasi wajib diisi.' },
                    category: { required: 'Kategori wajib dipilih.' },
                    unit: { required: 'Satuan wajib diisi.' },
                    standard_price: { number: 'Harga standar harus berupa angka.', min: 'Harga standar tidak boleh negatif.' },
                },
                errorElement: 'div',
                errorClass: 'invalid-feedback',
                highlight: function (element) { $(element).addClass('is-invalid'); },
                unhighlight: function (element) { $(element).removeClass('is-invalid'); },
            });

            <?php if($errors->any() && old('form_action')): ?>
                $('#modal-repair-type-title').text(<?php echo \Illuminate\Support\Js::from(old('_method') === 'PUT' ? 'Ubah Jenis Reparasi' : 'Tambah Jenis Reparasi')->toHtml() ?>);
                modal.show();
            <?php endif; ?>
            <?php endif; ?>
        })(jQuery);
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/repair-type/index.blade.php ENDPATH**/ ?>