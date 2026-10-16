<?php $__env->startSection('title', 'Permohonan Docking Ditolak'); ?>
<?php $__env->startSection('body_title', 'Permohonan Docking Ditolak'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('docking-space-request.index')); ?>" class="btn btn-outline-secondary">Semua Permohonan</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="alert alert-info">
        Halaman ini menyimpan riwayat permohonan yang ditolak beserta alasan penolakan. Dokumen permohonan tetap tersimpan dan dapat diunduh.
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Riwayat penolakan <span class="badge bg-red-lt text-red ms-2"><?php echo e($rejected_requests->count()); ?></span></h3>
            <div class="card-actions">
                <form method="GET" action="<?php echo e(route('docking-space-request.rejected')); ?>" class="d-flex gap-2">
                    <select name="stage" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua tahap</option>
                        <option value="engineering" <?php if($stage === 'engineering'): echo 'selected'; endif; ?>>Ditolak Engineering</option>
                        <option value="production" <?php if($stage === 'production'): echo 'selected'; endif; ?>>Ditolak Produksi</option>
                    </select>
                </form>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Kapal</th>
                        <th>Pemilik</th>
                        <th>Jadwal Diminta</th>
                        <th>Ditolak Oleh</th>
                        <th>Alasan</th>
                        <th>Dokumen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $rejected_requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="fw-medium"><?php echo e($item->ship?->name ?? '-'); ?></td>
                            <td><?php echo e($item->ship?->company?->name ?? '-'); ?></td>
                            <td>
                                <div><?php echo e($item->requested_start_at?->tanggal_waktu_indonesia()); ?></div>
                                <div class="text-secondary small">s/d <?php echo e($item->requested_end_at?->tanggal_waktu_indonesia() ?? '-'); ?></div>
                                <div class="text-secondary small"><?php echo e($item->requested_docking_space?->name ?? '-'); ?></div>
                            </td>
                            <td>
                                <span class="badge bg-red-lt text-red"><?php echo e($item->rejection_stage === 'production' ? 'Produksi' : 'Engineering'); ?></span>
                                <div class="small mt-1"><?php echo e($item->reviewer?->employee_id ?? '-'); ?></div>
                                <div class="text-secondary small"><?php echo e($item->reviewed_at?->tanggal_waktu_indonesia() ?? '-'); ?></div>
                            </td>
                            <td class="text-wrap" style="min-width: 14rem;"><?php echo e($item->rejection_reason ?? '-'); ?></td>
                            <td>
                                <?php $__empty_2 = true; $__currentLoopData = $item->documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                    <div class="small">
                                        <a href="<?php echo e(route('docking-space-request.documents.download', [$item->unique_id, $document->unique_id])); ?>"><?php echo e($document->document_name); ?></a>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                    <span class="text-secondary">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="6" class="text-center text-secondary py-4">Belum ada permohonan yang ditolak.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/docking-space-request/rejected.blade.php ENDPATH**/ ?>