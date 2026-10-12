<?php $__env->startSection('title', 'Detail Proyek'); ?>
<?php $__env->startSection('body_title', $project->project_code); ?>
<?php $__env->startSection('buttons_beside_title'); ?>
    <a class="btn btn-outline-secondary" href="<?php echo e(route('customer.index')); ?>">Proyek Saya</a>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="card mb-3"><div class="card-body">
        <h3 class="card-title"><?php echo e($project->ship?->name ?? '-'); ?></h3>
        <div class="row g-3">
            <div class="col-sm-4"><div class="text-secondary">Progres</div><strong><?php echo e($project->progress); ?>%</strong></div>
            <div class="col-sm-4"><div class="text-secondary">Estimasi Mulai</div><?php echo e($project->start_date_estimation?->tanggal_indonesia() ?? '-'); ?></div>
            <div class="col-sm-4"><div class="text-secondary">Estimasi Selesai</div><?php echo e($project->end_date_estimation?->tanggal_indonesia() ?? '-'); ?></div>
        </div>
    </div></div>
    <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">Dokumen &amp; Persetujuan</h3></div>
        <div class="list-group list-group-flush">
            <?php $__empty_1 = true; $__currentLoopData = $documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a class="list-group-item list-group-item-action" href="<?php echo e(route('customer.document', $document)); ?>">
                    <div class="d-flex flex-wrap justify-content-between gap-2">
                        <span><?php echo e(\App\Models\customer_document::TYPE_LABELS[$document->document_type]); ?> &middot; Revisi <?php echo e($document->revision_no); ?></span>
                        <span class="badge bg-blue-lt"><?php echo e(\App\Models\customer_document::STATUS_LABELS[$document->status]); ?></span>
                    </div>
                    <?php if($document->approver_id === auth()->id() && $document->status === 'pending'): ?>
                        <div class="text-secondary small mt-1">Ditugaskan kepada Anda untuk memberikan keputusan.</div>
                    <?php endif; ?>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="list-group-item text-secondary">Belum ada dokumen yang diterbitkan untuk pelanggan.</div>
            <?php endif; ?>
        </div>
        <div class="card-footer"><?php echo e($documents->links()); ?></div>
    </div>
    <div class="row g-3">
        <div class="col-lg-5"><div class="card">
            <div class="card-header"><h3 class="card-title">Kepuasan Pelanggan &amp; Komplain</h3></div>
            <div class="card-body">
                <p class="text-secondary">Komplain dapat dikirim selama proyek berlangsung maupun setelah selesai. Penilaian kepuasan tersedia setelah proyek selesai, satu kali per akun.</p>
                <form class="customer_form" method="POST" action="<?php echo e(route('customer.feedback', $project->unique_id)); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3"><label for="kind" class="form-label">Jenis Masukan</label><select id="kind" name="kind" class="form-select" required>
                        <option value="complaint" <?php if(old('kind') === 'complaint'): echo 'selected'; endif; ?>>Komplain</option>
                        <?php if($project->status === 'Completed'): ?><option value="satisfaction" <?php if(old('kind') === 'satisfaction'): echo 'selected'; endif; ?>>Kepuasan Pelanggan</option><?php endif; ?>
                    </select></div>
                    <div class="mb-3" id="rating_group"><label for="rating" class="form-label">Nilai Kepuasan</label><select id="rating" name="rating" class="form-select"><option value="">Pilih nilai</option>
                        <?php $__currentLoopData = [1 => 'Sangat Tidak Puas', 2 => 'Tidak Puas', 3 => 'Cukup', 4 => 'Puas', 5 => 'Sangat Puas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rating => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($rating); ?>" <?php if((string) old('rating') === (string) $rating): echo 'selected'; endif; ?>><?php echo e($rating); ?> - <?php echo e($label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select></div>
                    <div class="mb-3"><label for="message" class="form-label">Pesan</label><textarea id="message" name="message" class="form-control" rows="4" required maxlength="4000"><?php echo e(old('message')); ?></textarea></div>
                    <button class="btn btn-primary" type="submit">Kirim Masukan</button>
                </form>
            </div>
        </div></div>
        <div class="col-lg-7"><div class="card">
            <div class="card-header"><h3 class="card-title">Masukan Saya</h3></div>
            <div class="list-group list-group-flush">
                <?php $__empty_1 = true; $__currentLoopData = $feedback; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between gap-2"><strong><?php echo e($entry->kind === 'complaint' ? 'Komplain' : 'Kepuasan Pelanggan'); ?> <?php echo e($entry->rating ? '('.$entry->rating.'/5)' : ''); ?></strong><span class="badge bg-blue-lt"><?php echo e(['submitted' => 'Dikirim', 'in_progress' => 'Ditangani', 'resolved' => 'Selesai'][$entry->status]); ?></span></div>
                        <div class="text-secondary small mt-1"><?php echo e($entry->created_at->timezone('Asia/Jakarta')->tanggal_waktu_indonesia()); ?> WIB</div>
                        <p class="mt-2 mb-0 text-break"><?php echo e($entry->message); ?></p>
                        <?php if($entry->response): ?><div class="border-start ps-3 mt-3"><strong>Tanggapan ASSI</strong><p class="mb-1 text-break"><?php echo e($entry->response); ?></p><small class="text-secondary"><?php echo e($entry->responded_at->timezone('Asia/Jakarta')->tanggal_waktu_indonesia()); ?> WIB</small></div><?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="list-group-item text-secondary">Belum ada masukan.</div>
                <?php endif; ?>
            </div>
            <div class="card-footer"><?php echo e($feedback->links()); ?></div>
        </div></div>
    </div>
    <?php echo $__env->make('customer.validation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
    $(function () {
        function toggle_rating() {
            const is_satisfaction = $('#kind').val() === 'satisfaction';
            $('#rating_group').toggle(is_satisfaction);
            $('#rating').prop('required', is_satisfaction).prop('disabled', !is_satisfaction);
        }
        $('#kind').on('change', toggle_rating);
        toggle_rating();
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/customer/project.blade.php ENDPATH**/ ?>