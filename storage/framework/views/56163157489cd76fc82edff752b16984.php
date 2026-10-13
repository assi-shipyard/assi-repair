<?php $__env->startSection('title', 'Tunda Jadwal Docking'); ?>
<?php $__env->startSection('body_title', 'Tunda Jadwal Docking'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('docking-space-request.index')); ?>" class="btn btn-outline-secondary">Kembali</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">Jadwal saat ini</h3></div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item"><div class="datagrid-title">Kapal</div><div class="datagrid-content"><?php echo e($docking_request->ship?->name ?? '-'); ?></div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Docking space</div><div class="datagrid-content"><?php echo e($space?->name ?? '-'); ?></div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Durasi</div><div class="datagrid-content"><?php echo e(round($duration_minutes / 1440, 1)); ?> hari</div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Docking</div><div class="datagrid-content"><?php echo e($docking_request->requested_start_at?->tanggal_waktu_indonesia()); ?></div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Undocking</div><div class="datagrid-content"><?php echo e($docking_request->requested_end_at?->tanggal_waktu_indonesia() ?? '-'); ?></div></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Jadwal baru</h3></div>
                <div class="card-body">
                    <form method="POST" action="<?php echo e(route('docking-space-request.postpone.store', $docking_request->unique_id)); ?>" id="postpone-form">
                        <?php echo csrf_field(); ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label required">Tanggal docking baru</label>
                                <input type="datetime-local" class="form-control <?php $__errorArgs = ['new_start_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="new_start_at" id="new_start_at" value="<?php echo e(old('new_start_at')); ?>">
                                <?php $__errorArgs = ['new_start_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Tanggal undocking baru</label>
                                <input type="datetime-local" class="form-control <?php $__errorArgs = ['new_end_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="new_end_at" id="new_end_at" value="<?php echo e(old('new_end_at')); ?>">
                                <?php $__errorArgs = ['new_end_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-12">
                                <label class="form-label required">Alasan penundaan</label>
                                <textarea class="form-control <?php $__errorArgs = ['reason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="reason" rows="3" maxlength="2000" placeholder="Contoh: kapal sebelumnya masih memerlukan pekerjaan di dock."><?php echo e(old('reason')); ?></textarea>
                                <?php $__errorArgs = ['reason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                        <div class="mt-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Simpan Penundaan</button>
                            <a href="<?php echo e(route('docking-space-request.index')); ?>" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">Saran jadwal kosong</h3></div>
                <div class="card-body">
                    <p class="text-secondary small mb-3">Slot terdekat pada <strong><?php echo e($space?->name ?? 'docking space'); ?></strong> dengan durasi sama (<?php echo e(round($duration_minutes / 1440, 1)); ?> hari), dihitung sejak <?php echo e(\Carbon\Carbon::parse($earliest)->tanggal_waktu_indonesia()); ?>.</p>
                    <div class="list-group list-group-flush border rounded mb-3">
                        <?php $__empty_1 = true; $__currentLoopData = $suggestions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $suggestion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="list-group-item">
                                <div class="row align-items-center g-2">
                                    <div class="col">
                                        <div class="text-secondary small">Docking</div>
                                        <div class="fw-medium"><?php echo e($suggestion['start']->tanggal_waktu_indonesia()); ?></div>
                                        <div class="text-secondary small mt-1">Undocking</div>
                                        <div class="fw-medium"><?php echo e($suggestion['end']->tanggal_waktu_indonesia()); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <button type="button" class="btn btn-sm btn-primary js-use-suggestion"
                                            data-start="<?php echo e($suggestion['start']->format('Y-m-d\TH:i')); ?>" data-end="<?php echo e($suggestion['end']->format('Y-m-d\TH:i')); ?>">Pakai</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="list-group-item text-secondary">Tidak ditemukan slot kosong dalam 1 tahun ke depan pada docking space ini.</div>
                        <?php endif; ?>
                    </div>

                    <form method="GET" action="<?php echo e(route('docking-space-request.postpone', $docking_request->unique_id)); ?>" class="mt-3">
                        <label class="form-label">Cari slot mulai dari</label>
                        <div class="input-group">
                            <input type="datetime-local" class="form-control" name="from" value="<?php echo e(\Carbon\Carbon::parse($earliest)->format('Y-m-d\TH:i')); ?>">
                            <button class="btn btn-outline-secondary" type="submit">Cari</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if($docking_request->postponements->isNotEmpty()): ?>
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Riwayat penundaan</h3></div>
                    <div class="list-group list-group-flush">
                        <?php $__currentLoopData = $docking_request->postponements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="list-group-item">
                                <div class="small"><?php echo e($item->previous_start_at->tanggal_waktu_indonesia()); ?> &rarr; <?php echo e($item->new_start_at->tanggal_waktu_indonesia()); ?></div>
                                <div class="text-secondary small"><?php echo e($item->reason); ?></div>
                                <div class="text-secondary small"><?php echo e($item->postponer?->employee_id ?? '-'); ?>, <?php echo e($item->created_at->tanggal_waktu_indonesia()); ?></div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        $(function () {
            $('.js-use-suggestion').on('click', function () {
                $('#new_start_at').val($(this).data('start')).valid();
                $('#new_end_at').val($(this).data('end')).valid();
                document.getElementById('postpone-form').scrollIntoView({ behavior: 'smooth' });
            });

            $('#postpone-form').validate({
                errorClass: 'is-invalid',
                errorElement: 'div',
                errorPlacement: function (error, element) {
                    error.addClass('invalid-feedback d-block');
                    element.after(error);
                },
                rules: {
                    new_start_at: { required: true },
                    new_end_at: { required: true },
                    reason: { required: true, maxlength: 2000 },
                },
                messages: {
                    new_start_at: { required: 'Tanggal docking baru wajib diisi.' },
                    new_end_at: { required: 'Tanggal undocking baru wajib diisi.' },
                    reason: { required: 'Alasan penundaan wajib diisi.', maxlength: 'Alasan maksimal 2000 karakter.' },
                },
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/docking-space-request/postpone.blade.php ENDPATH**/ ?>