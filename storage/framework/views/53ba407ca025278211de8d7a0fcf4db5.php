<?php $__env->startSection('title', 'Daftar Permohonan Docking Space'); ?>
<?php $__env->startSection('body_title', 'Daftar Permohonan Docking Space'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('docking-space-request.create')); ?>" class="btn btn-primary">Permohonan Baru</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php
        $can_manage_docking = auth()->user()?->hasRole('admin')
            || auth()->user()?->can('docking.manage')
            || auth()->user()?->can('project.manage')
            || auth()->user()?->can('project.update');
    ?>

    <form class="card mb-3" method="GET" action="<?php echo e(route('docking-space-request.index')); ?>">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Filter Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <?php $__currentLoopData = ['submitted', 'reviewed', 'approved', 'rejected', 'cancelled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item_status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($item_status); ?>" <?php if($status === $item_status): echo 'selected'; endif; ?>><?php echo e(strtoupper($item_status)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-primary w-100" type="submit">Terapkan</button>
                </div>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Proyek</th>
                        <th>Kapal</th>
                        <th>Jadwal</th>
                        <th>Preferensi Docking Space</th>
                        <th>Status</th>
                        <th>Kecocokan Terbaik</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $docking_requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $docking_request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $best_evaluation = $docking_request->capacity_evaluations
                                ->sortByDesc('compatibility_score')
                                ->first();
                        ?>
                        <tr>
                            <td><?php echo e($docking_request->project?->project_code ?? '-'); ?></td>
                            <td><?php echo e($docking_request->ship?->name ?? '-'); ?></td>
                            <td>
                                <?php echo e(optional($docking_request->requested_start_at)->format('d/m/Y H:i') ?? '-'); ?>

                                <div class="text-secondary small">
                                    s/d <?php echo e(optional($docking_request->requested_end_at)->format('d/m/Y H:i') ?? '-'); ?>

                                </div>
                            </td>
                            <td><?php echo e($docking_request->requested_docking_space?->name ?? '-'); ?></td>
                            <td><span class="badge bg-blue-lt"><?php echo e(strtoupper($docking_request->request_status)); ?></span></td>
                            <td>
                                <?php if($best_evaluation): ?>
                                    <?php echo e($best_evaluation->docking_space?->name ?? '-'); ?>

                                    (<?php echo e($best_evaluation->compatibility_score); ?>%)
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-2">
                                    <?php if($can_manage_docking): ?>
                                        <form method="POST" action="<?php echo e(route('docking-space-request.evaluate', $docking_request->id)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <button class="btn btn-sm btn-outline-primary w-100" type="submit">Evaluasi Ulang</button>
                                        </form>

                                        <?php if(in_array($docking_request->request_status, ['submitted', 'reviewed'], true)): ?>
                                            <form method="POST" action="<?php echo e(route('docking-space-request.review', $docking_request->id)); ?>" class="d-flex flex-column gap-2 js-review-approve-form">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="request_status" value="approved">
                                                <select class="form-select form-select-sm" name="approved_docking_space_id" required>
                                                    <option value="">Pilih Docking Space</option>
                                                    <?php $__currentLoopData = $docking_spaces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $docking_space): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($docking_space->id); ?>" <?php if((int) $docking_request->requested_docking_space_id === (int) $docking_space->id): echo 'selected'; endif; ?>>
                                                            <?php echo e($docking_space->name); ?>

                                                        </option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                                <button class="btn btn-sm btn-success w-100" type="submit">Setujui</button>
                                            </form>

                                            <form method="POST" action="<?php echo e(route('docking-space-request.review', $docking_request->id)); ?>">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="request_status" value="rejected">
                                                <input type="hidden" name="rejection_reason" value="Ditolak dari halaman daftar permohonan docking space.">
                                                <button class="btn btn-sm btn-outline-danger w-100" type="submit">Tolak</button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if($docking_request->request_status === 'approved'): ?>
                                            <form method="POST" action="<?php echo e(route('docking-space-request.start-docking', $docking_request->id)); ?>" class="d-flex flex-column gap-2 js-start-docking-form">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="docking_space_id" value="<?php echo e($docking_request->requested_docking_space_id); ?>">
                                                <input type="datetime-local" class="form-control form-control-sm" name="docked_at" value="<?php echo e(optional($docking_request->requested_start_at)->format('Y-m-d\TH:i')); ?>" required>
                                                <input type="datetime-local" class="form-control form-control-sm" name="estimated_undock_at" value="<?php echo e(optional($docking_request->requested_end_at)->format('Y-m-d\TH:i')); ?>">
                                                <button class="btn btn-sm btn-primary w-100" type="submit">Mulai Docking</button>
                                            </form>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-secondary small">Tidak ada akses aksi.</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center text-secondary">Belum ada data permohonan docking space.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        $(function () {
            $.validator.addMethod('greaterThanOrEqual', function (value, element, param) {
                if (!value) {
                    return true;
                }

                const comparedValue = $(element).closest('form').find(param).val();
                if (!comparedValue) {
                    return true;
                }

                return new Date(value) >= new Date(comparedValue);
            });

            $('.js-review-approve-form').each(function () {
                $(this).validate({
                    errorClass: 'is-invalid',
                    validClass: 'is-valid',
                    errorElement: 'div',
                    errorPlacement: function (error, element) {
                        error.addClass('invalid-feedback');
                        element.closest('div').append(error);
                    },
                    rules: {
                        approved_docking_space_id: { required: true },
                    },
                    messages: {
                        approved_docking_space_id: { required: 'Docking space persetujuan wajib dipilih.' },
                    },
                });
            });

            $('.js-start-docking-form').each(function () {
                $(this).validate({
                    errorClass: 'is-invalid',
                    validClass: 'is-valid',
                    errorElement: 'div',
                    errorPlacement: function (error, element) {
                        error.addClass('invalid-feedback');
                        element.closest('div').append(error);
                    },
                    rules: {
                        docked_at: { required: true },
                        estimated_undock_at: {
                            greaterThanOrEqual: '[name="docked_at"]',
                        },
                    },
                    messages: {
                        docked_at: { required: 'Waktu masuk dock wajib diisi.' },
                        estimated_undock_at: {
                            greaterThanOrEqual: 'Estimasi keluar dock tidak boleh lebih awal dari waktu masuk dock.',
                        },
                    },
                });
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views/docking-space-request/index.blade.php ENDPATH**/ ?>