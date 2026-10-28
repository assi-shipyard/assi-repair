<?php
    $contract = $project->contract;
    $contract_status_labels = \App\Models\project_contract::STATUS_LABELS;
    $contract_days_left = $contract?->remaining_days();
?>

<div class="card mb-3" id="project-contract">
    <div class="card-header">
        <h3 class="card-title"><i class="ti ti-file-certificate me-2"></i>Kontrak Proyek</h3>
        <?php if($contract): ?>
            <div class="card-actions">
                <span class="badge <?php echo e($contract->status === 'signed_by_customer' ? 'bg-success-lt' : 'bg-blue-lt'); ?>">Status Penandatanganan</span>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if($contract_days_left !== null): ?>
            <div class="alert <?php echo e($contract_days_left < 0 ? 'alert-danger' : 'alert-warning'); ?>" role="alert">
                <h4 class="alert-title">Pengingat Penandatanganan Kontrak</h4>
                <div>
                    <?php if($contract_days_left < 0): ?>
                        Terlambat <?php echo e(abs($contract_days_left)); ?> hari.
                    <?php elseif($contract_days_left === 0): ?>
                        Batas penandatanganan hari ini.
                    <?php else: ?>
                        Sisa waktu penandatanganan <?php echo e($contract_days_left); ?> hari.
                    <?php endif; ?>
                    Target: <?php echo e($contract->signing_due_date->locale('id')->translatedFormat('j F Y')); ?> (WIB).
                    Tim legal perlu menindaklanjuti pihak yang belum menandatangani.
                </div>
            </div>
        <?php endif; ?>

        <?php if($contract): ?>
            <div class="row g-3 mb-3">
                <div class="col-12 col-md-4">
                    <div class="text-secondary small">Nomor Kontrak</div>
                    <div class="fw-semibold text-break"><?php echo e($contract->contract_number); ?></div>
                </div>
                <div class="col-12 col-md-5">
                    <div class="text-secondary small">Status Saat Ini</div>
                    <div class="fw-semibold"><?php echo e($contract_status_labels[$contract->status]); ?></div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="text-secondary small">Target Penandatanganan</div>
                    <div class="fw-semibold"><?php echo e($contract->signing_due_date?->locale('id')->translatedFormat('j F Y') ?? 'Pengingat tidak diaktifkan'); ?></div>
                    <?php if($contract->status === 'signed_by_customer'): ?>
                        <div class="text-success small">Penandatanganan selesai. Pengingat dihentikan.</div>
                    <?php endif; ?>
                </div>
            </div>
            <ol class="list-group list-group-numbered mb-3">
                <?php $__currentLoopData = $contract_status_labels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contract_status => $contract_label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="list-group-item <?php echo e($contract->status === $contract_status ? 'active' : ''); ?>" <?php if($contract->status === $contract_status): ?> aria-current="step" <?php endif; ?>>
                        <?php echo e($contract_label); ?>

                        <?php if($contract->status === $contract_status): ?><span class="ms-1 small">(saat ini)</span><?php endif; ?>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ol>
        <?php else: ?>
            <p class="text-secondary">Kontrak belum dicatat. Tim legal dapat mencatat nomor, memperbarui proses penandatanganan, dan mengunggah dokumen kontrak.</p>
        <?php endif; ?>

        <?php if (\Illuminate\Support\Facades\Blade::check('hasanyrole', 'legal|admin')): ?>
            <form id="project-contract-form" action="<?php echo e(route('project.contract.update', $project->unique_id)); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label for="contract_number" class="form-label required">Nomor Kontrak</label>
                        <input type="text" id="contract_number" name="contract_number" class="form-control <?php $__errorArgs = ['contract_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('contract_number', $contract?->contract_number)); ?>" required maxlength="100">
                        <?php $__errorArgs = ['contract_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="col-12 col-md-5">
                        <label for="contract_status" class="form-label required">Status Penandatanganan</label>
                        <select id="contract_status" name="status" class="form-select <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                            <?php $__currentLoopData = $contract_status_labels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contract_status => $contract_label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($contract_status); ?>" <?php if(old('status', $contract?->status ?? 'drafting') === $contract_status): echo 'selected'; endif; ?>><?php echo e($contract_label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="col-12 col-md-3">
                        <label for="signing_due_date" class="form-label">Target Penandatanganan (Opsional)</label>
                        <input type="date" id="signing_due_date" name="signing_due_date" class="form-control <?php $__errorArgs = ['signing_due_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('signing_due_date', $contract?->signing_due_date?->format('Y-m-d'))); ?>">
                        <div class="form-hint">Kosongkan untuk menonaktifkan pengingat. Perhitungan hari menggunakan WIB.</div>
                        <?php $__errorArgs = ['signing_due_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="col-12">
                        <label for="contract_document" class="form-label">Unggah Dokumen Kontrak (Opsional)</label>
                        <input type="file" id="contract_document" name="document" class="form-control <?php $__errorArgs = ['document'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" accept=".pdf,.doc,.docx">
                        <div class="form-hint">PDF, DOC, atau DOCX, maksimal 20 MB. Unggah kembali dokumen setelah ditandatangani. Versi sebelumnya tetap tersimpan.</div>
                        <?php $__errorArgs = ['document'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="col-12">
                        <div class="text-secondary small mb-2">Pembaruan dicatat dalam linimasa proyek. Pengingat hanya ditampilkan di halaman ini, bukan dikirim melalui email atau notifikasi.</div>
                        <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Simpan Kontrak</button>
                    </div>
                </div>
            </form>
        <?php else: ?>
            <div class="text-secondary small">Hanya pengguna dengan peran legal atau admin yang dapat mengubah kontrak dan mengunggah dokumen.</div>
        <?php endif; ?>
    </div>
    <?php if($contract && $contract->documents->isNotEmpty()): ?>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead><tr><th>Versi Dokumen</th><th>Status Saat Diunggah</th><th>Waktu Unggah</th><th>Unduh</th></tr></thead>
                <tbody>
                    <?php $__currentLoopData = $contract->documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contract_document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="text-break">
                                <?php echo e($contract_document->document_name); ?>

                                <?php if($loop->first): ?><span class="badge bg-blue-lt ms-1">Terbaru</span><?php endif; ?>
                            </td>
                            <td><?php echo e($contract_status_labels[$contract_document->status]); ?></td>
                            <td class="text-nowrap"><?php echo e($contract_document->created_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j F Y H:i')); ?> WIB</td>
                            <td><a class="btn btn-outline-primary btn-sm" href="<?php echo e(route('project.contract.download', [$project->unique_id, $contract_document->unique_id])); ?>"><i class="ti ti-download me-1"></i>Unduh</a></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    <?php elseif($contract): ?>
        <div class="card-footer text-secondary">Belum ada dokumen kontrak yang diunggah.</div>
    <?php endif; ?>
</div>

<?php if (\Illuminate\Support\Facades\Blade::check('hasanyrole', 'legal|admin')): ?>
    <?php $__env->startPush('scripts'); ?>
        <script>
            $(function () {
                $.validator.addMethod('contract_document', function (value, element) {
                    if (this.optional(element)) { return true; }
                    return /\.(pdf|doc|docx)$/i.test(value) && element.files[0].size <= 20 * 1024 * 1024;
                }, 'Dokumen harus berformat PDF, DOC, atau DOCX dengan ukuran maksimal 20 MB.');

                $('#project-contract-form').validate({
                    rules: {
                        contract_number: { required: true, maxlength: 100 },
                        status: { required: true },
                        signing_due_date: { dateISO: true },
                        document: { contract_document: true }
                    },
                    messages: {
                        contract_number: { required: 'Nomor kontrak wajib diisi.', maxlength: 'Nomor kontrak maksimal 100 karakter.' },
                        status: { required: 'Status kontrak wajib dipilih.' },
                        signing_due_date: { dateISO: 'Masukkan target penandatanganan yang valid.' }
                    },
                    errorElement: 'div',
                    errorClass: 'invalid-feedback',
                    highlight: function (element) { $(element).addClass('is-invalid'); },
                    unhighlight: function (element) { $(element).removeClass('is-invalid'); },
                    errorPlacement: function (error, element) { error.insertAfter(element); }
                });
            });
        </script>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH /home/snowy/projects/assi-repair/resources/views/project/partials/contract.blade.php ENDPATH**/ ?>