<?php $__env->startSection('title', $stage_title); ?>
<?php $__env->startSection('body_title', $stage_title); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('docking-space-request.index')); ?>" class="btn btn-outline-secondary">Semua Permohonan</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if($waiting_upstream_count > 0): ?>
        <div class="alert alert-info">
            Terdapat <?php echo e($waiting_upstream_count); ?> permohonan yang masih menunggu persetujuan Engineering. Permohonan tersebut akan muncul di sini setelah disetujui.
        </div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Menunggu persetujuan <span class="badge bg-blue-lt text-blue ms-2"><?php echo e($pending_requests->count()); ?></span></h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Kapal</th>
                        <th>Pemilik</th>
                        <th>Jadwal</th>
                        <th>Docking Space</th>
                        <th>Dokumen</th>
                        <th>Diajukan</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $pending_requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="fw-medium"><?php echo e($item->ship?->name ?? '-'); ?></td>
                            <td><?php echo e($item->ship?->company?->name ?? '-'); ?></td>
                            <td>
                                <div><?php echo e($item->requested_start_at?->format('d/m/Y H:i')); ?></div>
                                <div class="text-secondary small">s/d <?php echo e($item->requested_end_at?->format('d/m/Y H:i') ?? '-'); ?></div>
                            </td>
                            <td><?php echo e($item->requested_docking_space?->name ?? '-'); ?></td>
                            <td><?php echo e($item->documents_count); ?> berkas</td>
                            <td>
                                <div><?php echo e($item->created_at?->format('d/m/Y H:i')); ?></div>
                                <div class="text-secondary small"><?php echo e($item->requester?->employee_id ?? '-'); ?></div>
                            </td>
                            <td><a href="<?php echo e(route('docking-approval.show', [$stage, $item->unique_id])); ?>" class="btn btn-sm btn-primary">Tinjau</a></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="7" class="text-center text-secondary py-4">Tidak ada permohonan yang menunggu persetujuan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Riwayat keputusan (50 terakhir)</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Kapal</th>
                        <th>Jadwal</th>
                        <th>Keputusan</th>
                        <th>Status saat ini</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $decided_requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $approved_at = $stage === 'engineering' ? $item->engineering_approved_at : $item->production_approved_at;
                            $approver = $stage === 'engineering' ? $item->engineering_approver : $item->production_approver;
                            $rejected_here = $item->rejection_stage === $stage && $item->request_status === 'rejected';
                        ?>
                        <tr>
                            <td class="fw-medium"><?php echo e($item->ship?->name ?? '-'); ?></td>
                            <td><?php echo e($item->requested_start_at?->format('d/m/Y')); ?> - <?php echo e($item->requested_end_at?->format('d/m/Y') ?? '-'); ?></td>
                            <td>
                                <?php if($rejected_here): ?>
                                    <span class="badge bg-red-lt text-red">Ditolak</span>
                                    <div class="text-secondary small"><?php echo e($item->rejection_reason); ?></div>
                                <?php elseif($approved_at): ?>
                                    <span class="badge bg-success-lt text-success">Disetujui</span>
                                    <div class="text-secondary small"><?php echo e($approver?->employee_id ?? '-'); ?>, <?php echo e($approved_at->format('d/m/Y H:i')); ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e(['submitted' => 'Menunggu Engineering', 'engineering_approved' => 'Menunggu Produksi', 'approved' => 'Disetujui / menunggu kedatangan', 'rejected' => 'Ditolak', 'cancelled' => 'Dibatalkan'][$item->request_status] ?? $item->request_status); ?></td>
                            <td><a href="<?php echo e(route('docking-approval.show', [$stage, $item->unique_id])); ?>" class="btn btn-sm btn-outline-secondary">Detail</a></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5" class="text-center text-secondary py-4">Belum ada riwayat keputusan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/docking-approval/index.blade.php ENDPATH**/ ?>