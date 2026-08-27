<?php $__env->startSection('title', 'Docking Kapal Sekarang'); ?>
<?php $__env->startSection('body_title', 'Docking Kapal Sekarang'); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php
        $can_manage_docking = auth()->user()?->hasRole('admin')
            || auth()->user()?->can('docking.manage')
            || auth()->user()?->can('project.manage')
            || auth()->user()?->can('project.update');
    ?>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Proyek</th>
                        <th>Kapal</th>
                        <th>Docking Space</th>
                        <th>Waktu Masuk Dock</th>
                        <th>Estimasi Undock</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_0 = true; $__currentLoopData = $current_occupancies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $occupancy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?>
                        <tr>
                            <td><?php echo e($occupancy->project?->project_code ?? '-'); ?></td>
                            <td><?php echo e($occupancy->ship?->name ?? '-'); ?></td>
                            <td><?php echo e($occupancy->docking_space?->name ?? '-'); ?></td>
                            <td><?php echo e(optional($occupancy->docked_at)->format('d/m/Y H:i') ?? '-'); ?></td>
                            <td><?php echo e(optional($occupancy->estimated_undock_at)->format('d/m/Y H:i') ?? '-'); ?></td>
                            <td><span class="badge bg-azure-lt"><?php echo e(strtoupper($occupancy->occupancy_status)); ?></span></td>
                            <td>
                                <?php if($can_manage_docking): ?>
                                    <form method="POST" action="<?php echo e(route('ship-docking.undock-to-floating', $occupancy->unique_id ?? $occupancy->id)); ?>" class="d-flex flex-column gap-2 js-undock-floating-form">
                                        <?php echo csrf_field(); ?>
                                        <input type="datetime-local" class="form-control form-control-sm" name="undocked_at" required>
                                        <input type="datetime-local" class="form-control form-control-sm" name="floating_started_at">
                                        <input type="text" class="form-control form-control-sm" name="notes" placeholder="Catatan undock/floating repair">
                                        <button class="btn btn-sm btn-outline-primary w-100" type="submit">Undock ke Floating</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-secondary small">Tidak ada akses aksi.</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?>
                        <tr>
                            <td colspan="7" class="text-center text-secondary">Tidak ada kapal yang sedang docking saat ini.</td>
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

            $('.js-undock-floating-form').each(function () {
                $(this).validate({
                    errorClass: 'is-invalid',
                    validClass: 'is-valid',
                    errorElement: 'div',
                    errorPlacement: function (error, element) {
                        error.addClass('invalid-feedback');
                        element.closest('div').append(error);
                    },
                    rules: {
                        undocked_at: { required: true },
                        floating_started_at: {
                            greaterThanOrEqual: '[name="undocked_at"]',
                        },
                    },
                    messages: {
                        undocked_at: { required: 'Waktu undock wajib diisi.' },
                        floating_started_at: {
                            greaterThanOrEqual: 'Waktu mulai floating repair tidak boleh lebih awal dari waktu undock.',
                        },
                    },
                });
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/ship-docking/current.blade.php ENDPATH**/ ?>