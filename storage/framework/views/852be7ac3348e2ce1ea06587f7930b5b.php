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

        $status_labels = [
            'submitted' => 'Diajukan',
            'reviewed' => 'Ditinjau',
            'approved' => 'Disetujui / menunggu kedatangan',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
        ];

        $status_badges = [
            'submitted' => 'bg-blue-lt text-blue',
            'reviewed' => 'bg-amber-lt text-amber',
            'approved' => 'bg-success-lt text-success',
            'rejected' => 'bg-red-lt text-red',
            'cancelled' => 'bg-secondary-lt text-secondary',
        ];

        $status_counts = [
            'submitted' => 0,
            'reviewed' => 0,
            'approved' => 0,
            'rejected' => 0,
            'cancelled' => 0,
        ];

        foreach ($docking_requests as $docking_request) {
            if (isset($status_counts[$docking_request->request_status])) {
                $status_counts[$docking_request->request_status]++;
            }
        }
    ?>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card h-100 border-0 bg-primary-lt shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small">Diajukan</div>
                            <div class="fw-bold fs-2 mb-0"><?php echo e($status_counts['submitted']); ?></div>
                        </div>
                        <span class="avatar bg-primary text-white"><i class="ti ti-send"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-0 bg-amber-lt shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small">Ditinjau</div>
                            <div class="fw-bold fs-2 mb-0"><?php echo e($status_counts['reviewed']); ?></div>
                        </div>
                        <span class="avatar bg-amber text-white"><i class="ti ti-search"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-0 bg-success-lt shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small">Disetujui</div>
                            <div class="fw-bold fs-2 mb-0"><?php echo e($status_counts['approved']); ?></div>
                        </div>
                        <span class="avatar bg-success text-white"><i class="ti ti-check"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-0 bg-secondary-lt shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small">Selesai / ditolak</div>
                            <div class="fw-bold fs-2 mb-0"><?php echo e($status_counts['rejected'] + $status_counts['cancelled']); ?></div>
                        </div>
                        <span class="avatar bg-secondary text-white"><i class="ti ti-flag"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form class="card mb-3 shadow-sm" method="GET" action="<?php echo e(route('docking-space-request.index')); ?>">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Filter Status SOP</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Tahap</option>
                        <?php $__currentLoopData = ['submitted', 'reviewed', 'approved', 'rejected', 'cancelled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item_status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($item_status); ?>" <?php if($status === $item_status): echo 'selected'; endif; ?>><?php echo e($status_labels[$item_status] ?? strtoupper($item_status)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-primary w-100" type="submit">Terapkan</button>
                </div>
            </div>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-header border-0">
            <div>
                <h3 class="card-title mb-1">Timeline permohonan docking</h3>
                <div class="text-secondary">1) Permohonan docking, 2) menunggu kedatangan kapal, 3) masuk dock &amp; proyek dimulai, 4) review BOQ dan notes kepuasan</div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Proyek / Ship</th>
                        <th>Jadwal</th>
                        <th>Docking Space</th>
                        <th>Status SOP</th>
                        <th>Kecocokan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_0 = true; $__currentLoopData = $docking_requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $docking_request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?>
                        <?php
                            $best_evaluation = $docking_request->capacity_evaluations
                                ->sortByDesc('compatibility_score')
                                ->first();

                            $status_key = $docking_request->request_status;
                            $status_label = $status_labels[$status_key] ?? strtoupper($status_key);
                            $status_badge = $status_badges[$status_key] ?? 'bg-secondary-lt text-secondary';
                        ?>
                        <tr>
                            <td>
                                <div class="fw-medium"><?php echo e($docking_request->project?->project_code ?? '-'); ?></div>
                                <div class="text-secondary small mt-1"><?php echo e($docking_request->ship?->name ?? '-'); ?></div>
                            </td>
                            <td>
                                <div><?php echo e(optional($docking_request->requested_start_at)->format('d/m/Y H:i') ?? '-'); ?></div>
                                <div class="text-secondary small">s/d <?php echo e(optional($docking_request->requested_end_at)->format('d/m/Y H:i') ?? '-'); ?></div>
                            </td>
                            <td>
                                <div class="fw-medium"><?php echo e($docking_request->requested_docking_space?->name ?? '-'); ?></div>
                                <div class="text-secondary small">Preferensi request</div>
                            </td>
                            <td>
                                <span class="badge <?php echo e($status_badge); ?>"><?php echo e($status_label); ?></span>
                            </td>
                            <td>
                                <?php if($best_evaluation): ?>
                                    <div class="fw-medium"><?php echo e($best_evaluation->docking_space?->name ?? '-'); ?></div>
                                    <div class="text-secondary small"><?php echo e($best_evaluation->compatibility_score); ?>% cocok</div>
                                <?php else: ?>
                                    <span class="text-secondary">Belum ada evaluasi</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-2">
                                    <?php if($can_manage_docking): ?>
                                        <form method="POST" action="<?php echo e(route('docking-space-request.evaluate', $docking_request->unique_id ?? $docking_request->id)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <button class="btn btn-sm btn-outline-primary w-100" type="submit">Evaluasi ulang</button>
                                        </form>

                                        <?php if(in_array($docking_request->request_status, ['submitted', 'reviewed'], true)): ?>
                                            <form method="POST" action="<?php echo e(route('docking-space-request.review', $docking_request->unique_id ?? $docking_request->id)); ?>" class="d-flex flex-column gap-2 js-review-approve-form">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="request_status" value="approved">
                                                <select class="form-select form-select-sm" name="approved_docking_space_id" required>
                                                    <option value="">Pilih docking space</option>
                                                    <?php $__currentLoopData = $docking_spaces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $docking_space): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($docking_space->id); ?>" <?php if((int) $docking_request->requested_docking_space_id === (int) $docking_space->id): echo 'selected'; endif; ?>>
                                                            <?php echo e($docking_space->name); ?>

                                                        </option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                                <button class="btn btn-sm btn-success w-100" type="submit">Setujui</button>
                                            </form>

                                            <form method="POST" action="<?php echo e(route('docking-space-request.review', $docking_request->unique_id ?? $docking_request->id)); ?>">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="request_status" value="rejected">
                                                <input type="hidden" name="rejection_reason" value="Ditolak dari halaman daftar permohonan docking space.">
                                                <button class="btn btn-sm btn-outline-danger w-100" type="submit">Tolak</button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if($docking_request->request_status === 'approved'): ?>
                                            <form method="POST" action="<?php echo e(route('docking-space-request.start-docking', $docking_request->unique_id ?? $docking_request->id)); ?>" class="d-flex flex-column gap-2 js-start-docking-form">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="docking_space_id" value="<?php echo e($docking_request->requested_docking_space_id); ?>">
                                                <input type="datetime-local" class="form-control form-control-sm" name="docked_at" value="<?php echo e(optional($docking_request->requested_start_at)->format('Y-m-d\TH:i')); ?>" required>
                                                <input type="datetime-local" class="form-control form-control-sm" name="estimated_undock_at" value="<?php echo e(optional($docking_request->requested_end_at)->format('Y-m-d\TH:i')); ?>">
                                                <button class="btn btn-sm btn-primary w-100" type="submit">Masuk dock</button>
                                            </form>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-secondary small">Tidak ada akses aksi.</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?>
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-4">Belum ada data permohonan docking space.</td>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/docking-space-request/index.blade.php ENDPATH**/ ?>