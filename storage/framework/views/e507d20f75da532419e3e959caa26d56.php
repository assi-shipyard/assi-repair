<?php $__env->startSection('title', 'Data Dock'); ?>
<?php $__env->startSection('body_title', 'Data Dock'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="#docking-space-form" class="btn btn-primary">
        <i class="ti ti-<?php echo e(isset($editingSpace) ? 'pencil' : 'plus'); ?> me-1" aria-hidden="true"></i>
        <?php echo e(isset($editingSpace) ? 'Lanjutkan Pengubahan' : 'Tambah Dock'); ?>

    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php
        $editing_space = $editingSpace ?? null;
        $statuses = [
            'active' => ['label' => 'Aktif', 'color' => 'green', 'icon' => 'circle-check'],
            'maintenance' => ['label' => 'Pemeliharaan', 'color' => 'yellow', 'icon' => 'tool'],
            'inactive' => ['label' => 'Nonaktif', 'color' => 'secondary', 'icon' => 'circle-pause'],
        ];
        $capacity_fields = [
            'max_draft' => ['label' => 'Sarat Maksimum (m)', 'max' => '99999999.99'],
            'max_breadth' => ['label' => 'Lebar Kapal Maksimum (m)', 'max' => '99999999.99'],
            'max_tonnage' => ['label' => 'Tonase Maksimum (GT)', 'max' => '9999999999.99'],
            'max_capacity' => ['label' => 'Kapasitas Kapal', 'max' => '2147483647'],
        ];
        $optional_fields = [
            'max_length' => ['label' => 'Panjang Maksimum (m)', 'max' => '99999999.99'],
            'max_width' => ['label' => 'Lebar Maksimum (m)', 'max' => '99999999.99'],
            'max_weight' => ['label' => 'Berat Maksimum', 'max' => '9999999999.99'],
        ];
    ?>

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="card card-sm h-100 shadow-none">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar bg-blue-lt"><i class="ti ti-anchor fs-2" aria-hidden="true"></i></span>
                    <div>
                        <div class="text-secondary small">Total Dock</div>
                        <div class="h2 mb-0"><?php echo e($dockingSpaces->count()); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status_key => $status_detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-6 col-md-3">
                <div class="card card-sm h-100 shadow-none">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="avatar bg-<?php echo e($status_detail['color']); ?>-lt"><i class="ti ti-<?php echo e($status_detail['icon']); ?> fs-2" aria-hidden="true"></i></span>
                        <div>
                            <div class="text-secondary small"><?php echo e($status_detail['label']); ?></div>
                            <div class="h2 mb-0"><?php echo e($dockingSpaces->where('status', $status_key)->count()); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="row g-3 align-items-start">
        <div class="col-xl-8">
            <div class="card shadow-none">
                <div class="card-header">
                    <div>
                        <h3 class="card-title mb-1">Daftar Dock</h3>
                        <div class="text-secondary small">Lokasi, status operasional, dan batas kapasitas setiap dock.</div>
                    </div>
                </div>
                <div class="card-body border-bottom" id="dock-filters" hidden>
                    <div class="row g-2 align-items-end">
                        <div class="col-sm">
                            <label for="dock-search" class="form-label">Cari dock</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-search" aria-hidden="true"></i></span>
                                <input id="dock-search" type="search" class="form-control" placeholder="Nama atau lokasi dock" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <label for="dock-status" class="form-label">Status operasional</label>
                            <select id="dock-status" class="form-select">
                                <option value="">Semua status</option>
                                <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status_key => $status_detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($status_key); ?>"><?php echo e($status_detail['label']); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-sm-auto">
                            <button type="button" id="dock-filter-reset" class="btn w-100">Reset</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if($dockingSpaces->isEmpty()): ?>
                        <div class="empty py-5">
                            <div class="empty-icon"><i class="ti ti-anchor fs-1" aria-hidden="true"></i></div>
                            <p class="empty-title">Belum ada data dock</p>
                            <p class="empty-subtitle text-secondary">Tambahkan dock beserta batas kapasitasnya melalui formulir.</p>
                            <div class="empty-action"><a href="#docking-space-form" class="btn btn-primary">Tambah Dock Pertama</a></div>
                        </div>
                    <?php else: ?>
                        <div class="text-secondary small mb-3" id="dock-result-count" role="status" aria-live="polite"><?php echo e($dockingSpaces->count()); ?> dock ditampilkan</div>
                        <div class="row g-3">
                            <?php $__currentLoopData = $dockingSpaces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $space): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $status_detail = $statuses[$space->status] ?? ['label' => 'Tidak diketahui', 'color' => 'secondary', 'icon' => 'help'];
                                ?>
                                <div class="col-md-6" data-dock-card data-name="<?php echo e($space->name); ?>" data-location="<?php echo e($space->location); ?>" data-status="<?php echo e($space->status); ?>">
                                    <div class="card h-100 shadow-none <?php echo e($editing_space?->id === $space->id ? 'border-primary' : ''); ?>">
                                        <div class="card-body">
                                            <div class="d-flex align-items-start gap-2 mb-3">
                                                <span class="avatar bg-blue-lt flex-shrink-0"><i class="ti ti-anchor fs-2" aria-hidden="true"></i></span>
                                                <div class="flex-fill text-break">
                                                    <h4 class="mb-1"><?php echo e($space->name); ?></h4>
                                                    <div class="text-secondary small"><i class="ti ti-map-pin me-1" aria-hidden="true"></i><?php echo e($space->location ?: 'Lokasi belum ditentukan'); ?></div>
                                                </div>
                                            </div>
                                            <span class="badge bg-<?php echo e($status_detail['color']); ?>-lt mb-3"><?php echo e($status_detail['label']); ?></span>
                                            <?php if($editing_space?->id === $space->id): ?>
                                                <span class="badge bg-blue-lt mb-3">Sedang diubah</span>
                                            <?php endif; ?>
                                            <div class="row g-2 mb-3">
                                                <div class="col-6">
                                                    <div class="bg-body-tertiary rounded p-2 h-100">
                                                        <div class="text-secondary small">Tonase Maksimum (GT)</div>
                                                        <div class="h3 mb-0 text-break"><?php echo e($space->max_tonnage ?? '-'); ?></div>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="bg-body-tertiary rounded p-2 h-100">
                                                        <div class="text-secondary small">Kapasitas Kapal</div>
                                                        <div class="h3 mb-0"><?php echo e($space->max_capacity ?? '-'); ?> <span class="text-secondary small fw-normal">kapal</span></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <dl class="row g-2 small mb-0">
                                                <?php $__currentLoopData = ['max_draft' => 'Sarat maksimum', 'max_breadth' => 'Lebar kapal maksimum', 'max_length' => 'Panjang maksimum', 'max_width' => 'Lebar maksimum', 'max_weight' => 'Berat maksimum']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field_name => $field_label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <dt class="col-7 text-secondary fw-normal"><?php echo e($field_label); ?></dt>
                                                    <dd class="col-5 text-end text-break mb-0">
                                                        <?php echo e($space->{$field_name} ?? '-'); ?><?php echo e($space->{$field_name} !== null && $field_name !== 'max_weight' ? ' m' : ''); ?>

                                                    </dd>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </dl>
                                            <?php if(!empty($space->notes)): ?>
                                                <div class="border-top mt-3 pt-3 small text-break"><?php echo e($space->notes); ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-footer bg-transparent d-flex align-items-center justify-content-between gap-2">
                                            <a href="<?php echo e(route('docking-space.edit', $space->unique_id)); ?>" class="btn btn-sm btn-outline-primary" aria-label="Ubah <?php echo e($space->name); ?>">
                                                <i class="ti ti-pencil me-1" aria-hidden="true"></i>Ubah
                                            </a>
                                            <form method="POST" action="<?php echo e(route('docking-space.destroy', $space->unique_id)); ?>" onsubmit="return confirm('Hapus dock ini? Data yang dihapus tidak dapat dikembalikan.');">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="btn btn-sm btn-ghost-danger" aria-label="Hapus <?php echo e($space->name); ?>"><i class="ti ti-trash me-1" aria-hidden="true"></i>Hapus</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <div id="dock-no-results" class="empty py-4" hidden>
                            <p class="empty-title">Dock tidak ditemukan</p>
                            <p class="empty-subtitle text-secondary">Coba nama lain atau ubah filter status.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <form id="docking-space-form" method="POST" action="<?php echo e($editing_space ? route('docking-space.update', $editing_space->unique_id) : route('docking-space.store')); ?>" class="card shadow-none" aria-labelledby="dock-form-title">
                <?php echo csrf_field(); ?>
                <?php if($editing_space): ?>
                    <?php echo method_field('PUT'); ?>
                <?php endif; ?>
                <div class="card-header">
                    <div>
                        <h3 class="card-title mb-1" id="dock-form-title"><?php echo e($editing_space ? 'Ubah Data Dock' : 'Tambah Dock'); ?></h3>
                        <div class="text-secondary small"><?php echo e($editing_space ? $editing_space->name : 'Lengkapi identitas dan kapasitas dock.'); ?></div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="text-secondary small mb-3">Kolom bertanda <span class="text-danger">*</span> wajib diisi.</div>
                    <fieldset class="mb-4">
                        <legend class="h4 mb-3">Identitas Dock</legend>
                        <div class="mb-3">
                            <label for="dock-name" class="form-label required">Nama Dock</label>
                            <input id="dock-name" type="text" name="name" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('name', $editing_space?->name)); ?>" placeholder="Contoh: Floating Dock" maxlength="255" required>
                            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="mb-3">
                            <label for="dock-location" class="form-label">Lokasi</label>
                            <input id="dock-location" type="text" name="location" class="form-control <?php $__errorArgs = ['location'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('location', $editing_space?->location)); ?>" placeholder="Contoh: Area Timur" maxlength="255">
                            <?php $__errorArgs = ['location'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div>
                            <label for="dock-form-status" class="form-label required">Status Operasional</label>
                            <select id="dock-form-status" name="status" class="form-select <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status_key => $status_detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($status_key); ?>" <?php if(old('status', $editing_space?->status ?? 'active') === $status_key): echo 'selected'; endif; ?>><?php echo e($status_detail['label']); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </fieldset>

                    <fieldset class="mb-4">
                        <legend class="h4 mb-1">Batas Kapasitas</legend>
                        <p class="text-secondary small mb-3">Gunakan batas operasional yang telah ditetapkan untuk dock ini.</p>
                        <div class="row g-3">
                            <?php $__currentLoopData = $capacity_fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field_name => $field_detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-sm-6">
                                    <label for="dock-<?php echo e($field_name); ?>" class="form-label required"><?php echo e($field_detail['label']); ?></label>
                                    <input id="dock-<?php echo e($field_name); ?>" type="number" name="<?php echo e($field_name); ?>" class="form-control <?php $__errorArgs = [$field_name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old($field_name, $editing_space?->{$field_name})); ?>" step="<?php echo e($field_name === 'max_capacity' ? '1' : '0.01'); ?>" min="<?php echo e($field_name === 'max_capacity' ? '1' : '0.01'); ?>" max="<?php echo e($field_detail['max']); ?>" required>
                                    <?php $__errorArgs = [$field_name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend class="h4 mb-1">Spesifikasi Tambahan</legend>
                        <p class="text-secondary small mb-3">Opsional. Kosongkan jika belum ditetapkan.</p>
                        <div class="row g-3">
                            <?php $__currentLoopData = $optional_fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field_name => $field_detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-sm-6">
                                    <label for="dock-<?php echo e($field_name); ?>" class="form-label"><?php echo e($field_detail['label']); ?></label>
                                    <input id="dock-<?php echo e($field_name); ?>" type="number" name="<?php echo e($field_name); ?>" class="form-control <?php $__errorArgs = [$field_name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old($field_name, $editing_space?->{$field_name})); ?>" step="0.01" min="0.01" max="<?php echo e($field_detail['max']); ?>">
                                    <?php $__errorArgs = [$field_name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <div class="mt-3">
                            <label for="dock-notes" class="form-label">Catatan</label>
                            <textarea id="dock-notes" name="notes" class="form-control <?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" rows="3" placeholder="Informasi khusus mengenai dock ini"><?php echo e(old('notes', $editing_space?->notes)); ?></textarea>
                            <?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </fieldset>
                </div>
                <div class="card-footer d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary flex-fill"><i class="ti ti-device-floppy me-1" aria-hidden="true"></i><?php echo e($editing_space ? 'Simpan Perubahan' : 'Simpan Dock'); ?></button>
                    <?php if($editing_space): ?>
                        <a href="<?php echo e(route('docking-space.index')); ?>" class="btn">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
            <div class="card bg-blue-lt mt-3 shadow-none">
                <div class="card-body small">
                    <h4 class="mb-2"><i class="ti ti-info-circle me-1" aria-hidden="true"></i>Panduan Pengisian</h4>
                    <p class="mb-2">Kapasitas kapal adalah jumlah maksimum kapal yang dapat ditampung, bukan jumlah kapal yang sedang berada di dock.</p>
                    <p class="mb-0">Status aktif menunjukkan dock beroperasi, bukan jaminan ketersediaan jadwal. Gunakan status pemeliharaan atau nonaktif sesuai kondisi dock.</p>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script src="<?php echo e(secure_asset('assets/dist/js/jquery-validation/localization/messages_id.min.js')); ?>"></script>
    <script>
        $(function () {
            const dock_cards = $('[data-dock-card]');
            const dock_search = $('#dock-search');
            const dock_status = $('#dock-status');

            function filter_docks() {
                const query = dock_search.val().trim().toLocaleLowerCase('id-ID');
                const status = dock_status.val();
                let visible_count = 0;

                dock_cards.each(function () {
                    const card = $(this);
                    const search_text = (card.attr('data-name') + ' ' + card.attr('data-location')).toLocaleLowerCase('id-ID');
                    const matches = search_text.includes(query) && (!status || card.attr('data-status') === status);
                    card.prop('hidden', !matches);
                    visible_count += matches ? 1 : 0;
                });

                $('#dock-result-count').text(visible_count + ' dari ' + dock_cards.length + ' dock ditampilkan');
                $('#dock-no-results').prop('hidden', visible_count !== 0);
            }

            $('#dock-filters').prop('hidden', false);
            dock_search.on('input', filter_docks);
            dock_status.on('change', filter_docks);
            $('#dock-filter-reset').on('click', function () {
                dock_search.val('');
                dock_status.val('');
                filter_docks();
            });

            $('#docking-space-form').validate({
                rules: {
                    status: { required: true },
                    max_capacity: { required: true, digits: true }
                },
                messages: {
                    name: { required: 'Nama dock wajib diisi.' },
                    max_draft: { required: 'Sarat maksimum wajib diisi.' },
                    max_breadth: { required: 'Lebar kapal maksimum wajib diisi.' },
                    max_tonnage: { required: 'Tonase maksimum wajib diisi.' },
                    max_capacity: {
                        required: 'Kapasitas kapal wajib diisi.',
                        digits: 'Kapasitas kapal harus berupa bilangan bulat.'
                    }
                },
                errorElement: 'div',
                errorClass: 'invalid-feedback',
                highlight: function (element) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function (element) {
                    $(element).removeClass('is-invalid');
                }
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/docking-space/index.blade.php ENDPATH**/ ?>