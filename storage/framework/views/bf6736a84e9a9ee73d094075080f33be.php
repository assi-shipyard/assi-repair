<?php $__env->startSection('title', 'Riwayat Docking Kapal'); ?>
<?php $__env->startSection('body_title', 'Riwayat Docking Kapal'); ?>

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
                        <th>Docked At</th>
                        <th>Undocked At</th>
                        <th>Floating Repair</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $docking_histories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $history): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $floating = $history->floating_repair_histories->sortByDesc('floating_started_at')->first();
                        ?>
                        <tr>
                            <td><?php echo e($history->project?->project_code ?? '-'); ?></td>
                            <td><?php echo e($history->ship?->name ?? '-'); ?></td>
                            <td><?php echo e($history->docking_space?->name ?? '-'); ?></td>
                            <td><?php echo e(optional($history->docked_at)->format('d/m/Y H:i') ?? '-'); ?></td>
                            <td><?php echo e(optional($history->undocked_at)->format('d/m/Y H:i') ?? '-'); ?></td>
                            <td>
                                <?php if($floating): ?>
                                    <?php echo e(strtoupper($floating->floating_status)); ?>

                                    <div class="text-secondary small">
                                        <?php echo e(optional($floating->floating_started_at)->format('d/m/Y H:i') ?? '-'); ?>

                                        s/d
                                        <?php echo e(optional($floating->floating_completed_at)->format('d/m/Y H:i') ?? '-'); ?>

                                    </div>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($can_manage_docking && $floating && $floating->floating_status === 'active'): ?>
                                    <form method="POST" action="<?php echo e(route('ship-docking.complete-floating', $history->unique_id ?? $history->id)); ?>" class="d-flex flex-column gap-2 js-complete-floating-form">
                                        <?php echo csrf_field(); ?>
                                        <input type="datetime-local" class="form-control form-control-sm" name="floating_completed_at" required>
                                        <input type="text" class="form-control form-control-sm" name="notes" placeholder="Catatan penyelesaian floating repair">
                                        <button class="btn btn-sm btn-success w-100" type="submit">Selesaikan Floating</button>
                                    </form>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center text-secondary">Belum ada riwayat docking kapal.</td>
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
            $('.js-complete-floating-form').each(function () {
                $(this).validate({
                    errorClass: 'is-invalid',
                    validClass: 'is-valid',
                    errorElement: 'div',
                    errorPlacement: function (error, element) {
                        error.addClass('invalid-feedback');
                        element.closest('div').append(error);
                    },
                    rules: {
                        floating_completed_at: { required: true },
                    },
                    messages: {
                        floating_completed_at: { required: 'Waktu selesai floating repair wajib diisi.' },
                    },
                });
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views\ship-docking\history.blade.php ENDPATH**/ ?>