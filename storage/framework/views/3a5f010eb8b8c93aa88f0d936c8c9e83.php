<?php $__env->startSection('title', 'Layanan Pelanggan'); ?>
<?php $__env->startSection('body_title', 'Akses & Persetujuan Pelanggan'); ?>
<?php $__env->startSection('buttons_beside_title'); ?>
    <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin')): ?><a class="btn btn-outline-secondary" href="<?php echo e(route('accounts.index')); ?>">Kelola Semua Akun</a><?php endif; ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="card mb-3"><div class="card-body">
        <h3 class="card-title">Akses Teknis Tidak Sama dengan Akses Harga</h3>
        <p class="text-secondary mb-0">Surveyor eksternal dapat melihat proyek dan menyetujui Catatan Kepuasan tanpa akses harga. Persetujuan RAB memerlukan izin harga dan konfirmasi kata sandi pelanggan. Salinan revisi yang diterbitkan tidak berubah saat dokumen internal diedit.</p>
    </div></div>
    <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin')): ?>
    <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">Atur Akses Proyek</h3></div>
        <div class="card-body">
            <form id="access_form" class="customer_form" method="POST" action="<?php echo e(route('accounts.access')); ?>">
                <?php echo csrf_field(); ?>
                <div class="row g-3">
                    <div class="col-md-6"><label for="user_id" class="form-label">Pelanggan</label><select id="user_id" name="user_id" class="form-select" required><option value="">Pilih pelanggan</option><?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($customer->unique_id); ?>" <?php if(old('user_id') === $customer->unique_id): echo 'selected'; endif; ?>><?php echo e($customer->name); ?> - <?php echo e($customer->email); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                    <div class="col-md-6"><label for="project_id" class="form-label">Proyek</label><select id="project_id" name="project_id" class="form-select" required><option value="">Pilih proyek</option><?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($project->unique_id); ?>" <?php if(old('project_id') === $project->unique_id): echo 'selected'; endif; ?>><?php echo e($project->project_code); ?> - <?php echo e($project->ship?->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                    <?php $__currentLoopData = ['is_active' => 'Akses Proyek Aktif', 'view_prices' => 'Izin Membuka Harga', 'approve_initial_boq' => 'Persetujuan RAB Awal', 'approve_satisfaction_notes' => 'Persetujuan Catatan Kepuasan', 'approve_final_boq' => 'Persetujuan RAB Akhir']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-sm-6 col-lg-4"><label for="<?php echo e($field); ?>" class="form-label"><?php echo e($label); ?></label><select id="<?php echo e($field); ?>" name="<?php echo e($field); ?>" class="form-select" required><option value="0" <?php if((string) old($field, $field === 'is_active' ? '1' : '0') === '0'): echo 'selected'; endif; ?>>Tidak</option><option value="1" <?php if((string) old($field, $field === 'is_active' ? '1' : '0') === '1'): echo 'selected'; endif; ?>>Ya</option></select></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <p class="text-secondary mt-3">Menyimpan akun dan proyek yang sama mengganti izin sebelumnya. Untuk mencabut akses, pilih akses proyek aktif: Tidak.</p>
                <button class="btn btn-primary" type="submit">Simpan Akses</button>
            </form>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">Akses Terdaftar</h3></div>
        <div class="table-responsive"><table class="table table-vcenter card-table">
            <thead><tr><th>Pelanggan</th><th>Proyek</th><th>Akses</th><th>Harga</th><th>Persetujuan</th><th>Tindakan</th></tr></thead>
            <tbody><?php $__empty_1 = true; $__currentLoopData = $access_entries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td><?php echo e($entry->user->name); ?></td><td><?php echo e($entry->project->project_code); ?></td><td><?php echo e($entry->is_active ? 'Aktif' : 'Dicabut'); ?></td><td><?php echo e($entry->view_prices ? 'Diizinkan' : 'Tidak Diizinkan'); ?></td><td><?php echo e(implode(', ', array_filter([$entry->approve_initial_boq ? 'RAB Awal' : null, $entry->approve_satisfaction_notes ? 'Catatan Kepuasan' : null, $entry->approve_final_boq ? 'RAB Akhir' : null])) ?: '-'); ?></td><td>
                <button type="button" class="btn btn-sm btn-outline-primary edit_access" data-access="<?php echo e(json_encode(['user_id' => $entry->user->unique_id, 'project_id' => $entry->project->unique_id, 'is_active' => (int) $entry->is_active, 'view_prices' => (int) $entry->view_prices, 'approve_initial_boq' => (int) $entry->approve_initial_boq, 'approve_satisfaction_notes' => (int) $entry->approve_satisfaction_notes, 'approve_final_boq' => (int) $entry->approve_final_boq])); ?>">Ubah Akses</button>
            </td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td>Belum ada akses proyek.</td></tr><?php endif; ?></tbody>
        </table></div>
        <div class="card-footer"><?php echo e($access_entries->links()); ?></div>
    </div>
    <?php endif; ?>
    <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">Terbitkan untuk Persetujuan Pelanggan</h3></div>
        <div class="card-body">
            <form class="customer_form" method="POST" action="<?php echo e(route('accounts.publish')); ?>">
                <?php echo csrf_field(); ?>
                <div class="row g-3">
                    <div class="col-md-7"><label for="document_id" class="form-label">Dokumen yang Disetujui Internal</label><select id="document_id" name="document_id" class="form-select" required><option value="">Pilih revisi terbaru</option><?php $__currentLoopData = $documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($document->unique_id); ?>" <?php if(old('document_id') === $document->unique_id): echo 'selected'; endif; ?>><?php echo e($document->project->project_code); ?> - <?php echo e(\App\Models\customer_document::TYPE_LABELS[$document->document_type]); ?> - Revisi <?php echo e($document->revision_no); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                    <div class="col-md-5"><label for="approver_id" class="form-label">Pemberi Persetujuan</label><select id="approver_id" name="approver_id" class="form-select" required><option value="">Pilih pelanggan berwenang</option><?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($customer->unique_id); ?>" <?php if(old('approver_id') === $customer->unique_id): echo 'selected'; endif; ?>><?php echo e($customer->name); ?> - <?php echo e($customer->email); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                </div>
                <p class="text-secondary mt-3">Pelanggan harus memiliki akses dan wewenang untuk proyek ini. Revisi baru menggantikan permintaan persetujuan lama yang belum diputuskan.</p>
                <button type="submit" class="btn btn-primary">Terbitkan Dokumen</button>
            </form>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">Riwayat Persetujuan Pelanggan</h3></div>
        <div class="table-responsive"><table class="table table-vcenter card-table">
            <thead><tr><th>Proyek / Dokumen</th><th>Pemberi Persetujuan</th><th>Status</th><th>Keputusan</th></tr></thead>
            <tbody><?php $__empty_1 = true; $__currentLoopData = $publications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $publication): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td><?php echo e($publication->project->project_code); ?><div class="small text-secondary"><?php echo e(\App\Models\customer_document::TYPE_LABELS[$publication->document_type]); ?> - Revisi <?php echo e($publication->revision_no); ?></div></td><td><?php echo e($publication->approver->name); ?>

                <?php if($publication->status === 'pending'): ?>
                    <form class="customer_form mt-2" method="POST" action="<?php echo e(route('accounts.reassign', $publication)); ?>">
                        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                        <label for="approver_<?php echo e($publication->unique_id); ?>" class="visually-hidden">Pemberi Persetujuan Baru</label>
                        <select id="approver_<?php echo e($publication->unique_id); ?>" name="approver_id" class="form-select form-select-sm" required><option value="">Pilih pengganti</option><?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($customer->unique_id); ?>"><?php echo e($customer->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
                        <button class="btn btn-sm btn-outline-secondary mt-1" type="submit">Alihkan</button>
                    </form>
                <?php endif; ?>
            </td><td><?php echo e(\App\Models\customer_document::STATUS_LABELS[$publication->status]); ?></td><td><?php echo e($publication->decided_at?->timezone('Asia/Jakarta')->tanggal_waktu_indonesia()); ?><?php if($publication->decided_at): ?> WIB <?php endif; ?><div class="small text-secondary text-break"><?php echo e($publication->decision_comment); ?></div></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td>Belum ada dokumen yang diterbitkan.</td></tr><?php endif; ?></tbody>
        </table></div>
        <div class="card-footer"><?php echo e($publications->links()); ?></div>
    </div>
    <div class="card">
        <div class="card-header"><h3 class="card-title">Kepuasan Pelanggan &amp; Komplain</h3></div>
        <div class="list-group list-group-flush">
            <?php $__empty_1 = true; $__currentLoopData = $feedback; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="list-group-item">
                    <div class="d-flex flex-wrap justify-content-between gap-2"><strong><?php echo e($entry->project->project_code); ?> - <?php echo e($entry->user->name); ?></strong><span class="badge bg-blue-lt"><?php echo e(['submitted' => 'Dikirim', 'in_progress' => 'Ditangani', 'resolved' => 'Selesai'][$entry->status]); ?></span></div>
                    <div class="text-secondary small mt-1"><?php echo e($entry->kind === 'complaint' ? 'Komplain' : 'Kepuasan Pelanggan ('.$entry->rating.'/5)'); ?> &middot; <?php echo e($entry->created_at->timezone('Asia/Jakarta')->tanggal_waktu_indonesia()); ?> WIB</div>
                    <p class="text-break mt-2"><?php echo e($entry->message); ?></p>
                    <form class="customer_form" method="POST" action="<?php echo e(route('accounts.respond', $entry)); ?>">
                        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                        <div class="row g-2">
                            <div class="col-md-7"><label for="response_<?php echo e($entry->unique_id); ?>" class="form-label">Tanggapan</label><textarea id="response_<?php echo e($entry->unique_id); ?>" name="response" class="form-control" rows="2" required maxlength="4000"><?php echo e($entry->response); ?></textarea></div>
                            <div class="col-md-3"><label for="status_<?php echo e($entry->unique_id); ?>" class="form-label">Status Penanganan</label><select id="status_<?php echo e($entry->unique_id); ?>" name="status" class="form-select" required><option value="in_progress" <?php if($entry->status === 'in_progress'): echo 'selected'; endif; ?>>Ditangani</option><option value="resolved" <?php if($entry->status === 'resolved'): echo 'selected'; endif; ?>>Selesai</option></select></div>
                            <div class="col-md-2 d-flex align-items-end"><button class="btn btn-outline-primary w-100" type="submit">Simpan</button></div>
                        </div>
                    </form>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><div class="list-group-item text-secondary">Belum ada masukan pelanggan.</div><?php endif; ?>
        </div>
        <div class="card-footer"><?php echo e($feedback->links()); ?></div>
    </div>
    <?php echo $__env->make('customer.validation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
    $(function () {
        $('.edit_access').on('click', function () {
            const access_data = $(this).data('access');
            $.each(access_data, function (field, value) { $('#access_form [name="' + field + '"]').val(String(value)).trigger('change'); });
            $('#access_form').get(0).scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/accounts/projects.blade.php ENDPATH**/ ?>