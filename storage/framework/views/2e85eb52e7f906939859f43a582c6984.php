<?php $__env->startSection('title', $document_data['title']); ?>
<?php $__env->startSection('body_title', $document_data['title'].' - Revisi '.$document_data['revision_no']); ?>
<?php $__env->startSection('buttons_beside_title'); ?>
    <a class="btn btn-outline-secondary" href="<?php echo e(route('customer.project', $project->unique_id)); ?>">Kembali ke Proyek</a>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="card mb-3"><div class="card-body">
        <div class="d-flex flex-wrap justify-content-between gap-2">
            <strong><?php echo e($project->project_code); ?> &middot; <?php echo e($project->ship?->name ?? '-'); ?></strong>
            <span class="badge bg-blue-lt"><?php echo e(\App\Models\customer_document::STATUS_LABELS[$document_data['status']]); ?></span>
        </div>
        <p class="text-secondary mt-2 mb-0">Dokumen ini merupakan salinan revisi yang diterbitkan oleh ASSI. Persetujuan Anda tidak menggantikan persetujuan internal.</p>
    </div></div>
    <?php if($document_data['document_type'] !== 'satisfaction_notes'): ?>
        <div class="card mb-3 d-print-none"><div class="card-body">
            <?php if($document_data['prices_unlocked']): ?>
                <div class="d-flex flex-wrap justify-content-between gap-3">
                    <div><strong>Harga Terbuka Sementara</strong><p class="text-secondary mb-0">Harga dikunci otomatis setelah 5 menit. Jangan bagikan harga kepada pihak yang tidak berwenang.</p></div>
                    <form method="POST" action="<?php echo e(route('customer.lock', $document_data['unique_id'])); ?>"><?php echo csrf_field(); ?><button class="btn btn-outline-secondary" type="submit">Kunci Harga</button></form>
                </div>
                <div class="financial-value mt-3">
                    <span class="text-secondary">Total RAB:</span>
                    <?php $__empty_1 = true; $__currentLoopData = $document_data['prices']['totals']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $currency => $amount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <strong class="me-3"><?php echo e($currency); ?> <?php echo e(number_format($amount, 2, ',', '.')); ?></strong>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><span>-</span><?php endif; ?>
                </div>
            <?php elseif($document_data['can_unlock']): ?>
                <h3 class="card-title">Harga RAB Terkunci</h3>
                <p class="text-secondary">Gunakan kata sandi akun Anda untuk membuka harga dokumen ini selama 5 menit.</p>
                <form class="customer_form row g-2" method="POST" action="<?php echo e(route('customer.unlock', $document_data['unique_id'])); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="col-sm-8"><label for="current_password" class="visually-hidden">Kata Sandi Akun</label><input id="current_password" name="current_password" type="password" class="form-control" required maxlength="1024" placeholder="Kata sandi akun Anda" autocomplete="current-password"></div>
                    <div class="col-sm-4"><button type="submit" class="btn btn-primary w-100">Buka Harga</button></div>
                </form>
            <?php else: ?>
                <strong>Harga RAB Terkunci</strong><p class="text-secondary mb-0">Akun Anda hanya memiliki akses teknis. Hubungi administrator jika memerlukan akses harga.</p>
            <?php endif; ?>
        </div></div>
    <?php endif; ?>
    <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">Daftar Pekerjaan</h3></div>
        <div class="table-responsive"><table class="table table-vcenter card-table">
            <thead><tr><th>Pekerjaan</th><th>Volume</th><th>Progres</th><th>Jadwal</th><?php if($document_data['prices_unlocked']): ?><th class="financial-value">Harga</th><?php endif; ?></tr></thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $document_data['snapshot']['jobs']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><strong><?php echo e($job['name']); ?></strong>
                            <?php $__currentLoopData = $job['materials']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $material): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="small text-secondary mt-1"><?php echo e($material['name']); ?> &middot; <?php echo e($material['volume'] ?? '-'); ?> <?php echo e($material['dimension'] ?? ''); ?>

                                    <?php if($document_data['prices_unlocked']): ?>
                                        <?php ($material_price = $document_data['prices']['jobs'][$job['unique_id']]['materials'][$material['unique_id']]); ?>
                                        <span class="financial-value"> &middot; <?php echo e($material_price['currency']); ?> <?php echo e(number_format((float) $material_price['price'], 2, ',', '.')); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </td>
                        <td><?php echo e($job['volume'] ?? '-'); ?><?php if($job['actual_volume'] !== null): ?><div class="small text-secondary">Aktual: <?php echo e($job['actual_volume']); ?></div><?php endif; ?></td>
                        <td><?php echo e($job['progress'] ?? '0'); ?>%</td>
                        <td class="small"><?php echo e($job['start_date'] ?? '-'); ?><br><?php echo e($job['end_date'] ?? '-'); ?></td>
                        <?php if($document_data['prices_unlocked']): ?>
                            <?php ($job_price = $document_data['prices']['jobs'][$job['unique_id']]); ?>
                            <td class="financial-value text-nowrap"><?php echo e($job_price['currency']); ?> <?php echo e(number_format((float) $job_price['price'], 2, ',', '.')); ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td>Belum ada pekerjaan.</td></tr>
                <?php endif; ?>
            </tbody>
        </table></div>
    </div>
    <?php if($document_data['can_decide']): ?>
        <div class="card d-print-none"><div class="card-header"><h3 class="card-title">Keputusan Anda</h3></div><div class="card-body">
            <form class="customer_form" method="POST" action="<?php echo e(route('customer.decision', $document_data['unique_id'])); ?>">
                <?php echo csrf_field(); ?>
                <div class="mb-3"><label for="decision" class="form-label">Keputusan</label><select id="decision" name="decision" class="form-select" required><option value="">Pilih keputusan</option><option value="approved" <?php if(old('decision') === 'approved'): echo 'selected'; endif; ?>>Setujui</option><option value="changes_requested" <?php if(old('decision') === 'changes_requested'): echo 'selected'; endif; ?>>Minta Perubahan</option><option value="rejected" <?php if(old('decision') === 'rejected'): echo 'selected'; endif; ?>>Tolak</option></select></div>
                <div class="mb-3"><label for="comment" class="form-label">Komentar / Alasan</label><textarea id="comment" name="comment" class="form-control" rows="3" maxlength="2000"><?php echo e(old('comment')); ?></textarea></div>
                <p class="text-secondary">Keputusan dicatat atas nama akun Anda dan tidak dapat diubah. Pastikan Anda telah memeriksa revisi ini.</p>
                <button class="btn btn-primary" type="submit">Kirim Keputusan</button>
            </form>
        </div></div>
    <?php elseif($document_data['decided_at']): ?>
        <div class="card"><div class="card-body"><strong>Keputusan Dicatat: <?php echo e($document_data['decided_at']); ?></strong><p class="mb-0 mt-2 text-break"><?php echo e($document_data['decision_comment']); ?></p></div></div>
    <?php endif; ?>
    <?php echo $__env->make('customer.validation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('styles'); ?>
<style>@media print { body, table { font-family: Tahoma, sans-serif !important; } }</style>
<?php $__env->stopPush(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
    $(function () {
        $('#decision').on('change', function () { $('#comment').prop('required', $(this).val() !== 'approved'); }).trigger('change');
        <?php if($document_data['prices_unlocked']): ?>
            setTimeout(function () {
                $('.financial-value').remove();
                window.location.reload();
            }, <?php echo \Illuminate\Support\Js::from($document_data['unlock_remaining_seconds'] * 1000)->toHtml() ?>);
            window.addEventListener('pageshow', function (event) {
                if (event.persisted) {
                    $('.financial-value').remove();
                    window.location.reload();
                }
            });
        <?php endif; ?>
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/customer/document.blade.php ENDPATH**/ ?>