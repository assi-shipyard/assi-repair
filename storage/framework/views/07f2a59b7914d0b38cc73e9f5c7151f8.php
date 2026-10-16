<?php $__env->startSection('title', $stage_title); ?>
<?php $__env->startSection('body_title', 'Tinjau Permohonan Docking'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('docking-approval.index', $stage)); ?>" class="btn btn-outline-secondary">Kembali</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php
        $ship = $docking_request->ship;
        $space = $effective_space;
        $fit_rows = [
            ['Panjang (LOA)', 'length_overall', 'max_length', 'length', 'm'],
            ['Lebar (Breadth)', 'breadth', 'max_breadth', 'breadth', 'm'],
            ['Draft', 'draft', 'max_draft', 'draft', 'm'],
            ['Tonnage', 'gross_tonnage', 'max_tonnage', 'tonnage', 'GT'],
        ];
    ?>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Kapal dan jadwal</h3>
                    <div class="card-actions"><span class="badge bg-blue-lt text-blue"><?php echo e($docking_request->status_label()); ?></span></div>
                </div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item"><div class="datagrid-title">Kapal</div><div class="datagrid-content"><?php echo e($ship?->name ?? '-'); ?></div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Pemilik</div><div class="datagrid-content"><?php echo e($ship?->company?->name ?? '-'); ?></div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Tipe</div><div class="datagrid-content"><?php echo e($ship?->type?->name ?? '-'); ?></div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Jadwal mulai</div><div class="datagrid-content"><?php echo e($docking_request->requested_start_at?->tanggal_waktu_indonesia()); ?></div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Jadwal selesai</div><div class="datagrid-content"><?php echo e($docking_request->requested_end_at?->tanggal_waktu_indonesia() ?? '-'); ?></div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Docking space diminta</div><div class="datagrid-content"><?php echo e($docking_request->requested_docking_space?->name ?? '-'); ?></div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Rekomendasi Engineering</div><div class="datagrid-content"><?php echo e($docking_request->recommended_docking_space?->name ?? '-'); ?></div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Diajukan oleh</div><div class="datagrid-content"><?php echo e($docking_request->requester?->employee_id ?? '-'); ?></div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Diajukan pada</div><div class="datagrid-content"><?php echo e($docking_request->created_at?->tanggal_waktu_indonesia()); ?></div></div>
                    </div>
                    <?php if($docking_request->request_notes): ?>
                        <div class="mt-3">
                            <div class="datagrid-title">Catatan pemohon</div>
                            <div style="white-space: pre-line"><?php echo e($docking_request->request_notes); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Kesesuaian kapal dengan docking space</h3>
                    <?php if($evaluation): ?>
                        <div class="card-actions">
                            <span class="badge <?php echo e($evaluation['is_compatible'] ? 'bg-success-lt text-success' : 'bg-red-lt text-red'); ?>">
                                <?php echo e($evaluation['is_compatible'] ? 'Lolos pemeriksaan sistem' : 'Tidak sesuai'); ?>

                            </span>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if($evaluation): ?>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead><tr><th>Parameter</th><th>Kapal</th><th>Batas docking space</th><th>Sisa margin</th><th>Hasil</th></tr></thead>
                            <tbody>
                                <?php $__currentLoopData = $fit_rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $ship_key, $space_key, $check_key, $unit]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $ship_value = $evaluation['ship_snapshot'][$ship_key] ?? null;
                                        $space_value = $evaluation['docking_space_snapshot'][$space_key] ?? null;
                                        $margin = ($ship_value !== null && $space_value !== null) ? $space_value - $ship_value : null;
                                        $passed = $evaluation['checks'][$check_key] ?? false;
                                    ?>
                                    <tr>
                                        <td><?php echo e($label); ?></td>
                                        <td><?php echo e($ship_value !== null ? rtrim(rtrim(number_format($ship_value, 2, ',', '.'), '0'), ',') . ' ' . $unit : '-'); ?></td>
                                        <td><?php echo e($space_value !== null ? rtrim(rtrim(number_format($space_value, 2, ',', '.'), '0'), ',') . ' ' . $unit : 'Tidak dibatasi'); ?></td>
                                        <td><?php echo e($margin !== null ? rtrim(rtrim(number_format($margin, 2, ',', '.'), '0'), ',') . ' ' . $unit : '-'); ?></td>
                                        <td><span class="badge <?php echo e($passed ? 'bg-success-lt text-success' : 'bg-red-lt text-red'); ?>"><?php echo e($passed ? 'Sesuai' : 'Melebihi'); ?></span></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-body border-top text-secondary small">
                        Pemeriksaan sistem hanya bersifat awal berdasarkan data kapal. Engineering menghitung lebih lanjut dari dokumen terlampir.
                    </div>
                <?php else: ?>
                    <div class="card-body text-secondary">Docking space belum ditentukan.</div>
                <?php endif; ?>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Dokumen terlampir</h3></div>
                <div class="list-group list-group-flush">
                    <?php $__empty_1 = true; $__currentLoopData = $docking_request->documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <a class="list-group-item list-group-item-action d-flex justify-content-between" href="<?php echo e(route('docking-space-request.documents.download', [$docking_request->unique_id, $document->unique_id])); ?>">
                            <span><?php echo e($document->document_name); ?></span>
                            <span class="text-secondary small"><?php echo e($document->created_at?->tanggal_waktu_indonesia()); ?></span>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="list-group-item text-secondary">Tidak ada dokumen.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">Alur persetujuan</h3></div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <div class="fw-medium">1. Pemeriksaan sistem</div>
                            <div class="text-success small">Lolos saat pengajuan</div>
                        </li>
                        <li class="mb-3">
                            <div class="fw-medium">2. Engineering (Biro Litbang)</div>
                            <?php if($docking_request->engineering_approved_at): ?>
                                <div class="text-success small">Disetujui <?php echo e($docking_request->engineering_approver?->employee_id ?? '-'); ?>, <?php echo e($docking_request->engineering_approved_at->tanggal_waktu_indonesia()); ?></div>
                            <?php elseif($docking_request->rejection_stage === 'engineering'): ?>
                                <div class="text-danger small">Ditolak: <?php echo e($docking_request->rejection_reason); ?></div>
                            <?php else: ?>
                                <div class="text-secondary small">Menunggu</div>
                            <?php endif; ?>
                            <?php if($docking_request->engineering_notes && $docking_request->rejection_stage !== 'engineering'): ?>
                                <div class="small">Catatan: <?php echo e($docking_request->engineering_notes); ?></div>
                            <?php endif; ?>
                        </li>
                        <li>
                            <div class="fw-medium">3. Produksi (Divisi Reparasi dan Rekayasa Umum)</div>
                            <?php if($docking_request->production_approved_at): ?>
                                <div class="text-success small">Disetujui <?php echo e($docking_request->production_approver?->employee_id ?? '-'); ?>, <?php echo e($docking_request->production_approved_at->tanggal_waktu_indonesia()); ?></div>
                            <?php elseif($docking_request->rejection_stage === 'production'): ?>
                                <div class="text-danger small">Ditolak: <?php echo e($docking_request->rejection_reason); ?></div>
                            <?php elseif($docking_request->request_status === 'awaiting_marketing'): ?>
                                <div class="text-orange small">
                                    Disarankan <?php echo e($docking_request->production_recommendation === 'postpone' ? 'ditunda' : 'dibatalkan'); ?>

                                    <?php if($docking_request->production_recommendation === 'postpone'): ?>
                                        ke <?php echo e($docking_request->recommended_start_at?->tanggal_waktu_indonesia()); ?> s/d <?php echo e($docking_request->recommended_end_at?->tanggal_waktu_indonesia()); ?>

                                    <?php endif; ?>
                                    - menunggu konfirmasi Marketing
                                </div>
                            <?php else: ?>
                                <div class="text-secondary small">Menunggu</div>
                            <?php endif; ?>
                            <?php if($docking_request->production_notes && $docking_request->rejection_stage !== 'production'): ?>
                                <div class="small">Catatan: <?php echo e($docking_request->production_notes); ?></div>
                            <?php endif; ?>
                            <?php if($docking_request->marketing_response): ?>
                                <div class="small mt-1">Tanggapan Marketing (<?php echo e($docking_request->marketing_responder?->employee_id ?? '-'); ?>, <?php echo e($docking_request->marketing_responded_at?->tanggal_waktu_indonesia()); ?>): <?php echo e($docking_request->marketing_response === 'accept' ? 'customer setuju' : 'customer keberatan'); ?><?php if($docking_request->marketing_notes): ?> - <?php echo e($docking_request->marketing_notes); ?><?php endif; ?></div>
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Keputusan</h3></div>
                <div class="card-body">
                    <?php if($can_decide): ?>
                        <form method="POST" action="<?php echo e(route('docking-space-request.' . $stage . '-review', $docking_request->unique_id)); ?>" id="approval-form">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="return_to_queue" value="1">
                            <?php if($stage === 'engineering'): ?>
                                <div class="mb-3">
                                    <label class="form-label">Docking space rekomendasi <span class="text-danger">*</span></label>
                                    <select class="form-select" name="recommended_docking_space">
                                        <option value="">Pilih docking space (wajib saat menyetujui)</option>
                                        <?php $__currentLoopData = $space_options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php $selectable = $option['is_compatible'] && ! $option['has_conflict']; ?>
                                            <option value="<?php echo e($option['space']->unique_id); ?>" <?php if(! $selectable): echo 'disabled'; endif; ?> <?php if(old('recommended_docking_space', $docking_request->requested_docking_space?->unique_id) === $option['space']->unique_id && $selectable): echo 'selected'; endif; ?>>
                                                <?php echo e($option['space']->name); ?><?php echo e(! $option['is_compatible'] ? ' - tidak sesuai spesifikasi' : ($option['has_conflict'] ? ' - jadwal bentrok' : '')); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <div class="form-hint">Pilih berdasarkan hasil perhitungan dari gambar kapal. Dok yang tidak sesuai atau bentrok tidak dapat dipilih.</div>
                                    <?php $__errorArgs = ['recommended_docking_space'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small mt-1"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <label class="form-label">Catatan</label>
                                <textarea class="form-control" name="notes" rows="4" maxlength="2000" placeholder="Wajib diisi bila menolak"><?php echo e(old('notes')); ?></textarea>
                                <?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small mt-1"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <?php if($stage === 'production'): ?>
                                <div id="postpone-fields" class="row g-2 mb-3 <?php echo e($errors->has('recommended_start_at') || $errors->has('recommended_end_at') ? '' : 'd-none'); ?>">
                                    <div class="col-12 text-secondary small">Usulan jadwal baru yang akan dikonfirmasi Marketing kepada customer.</div>
                                    <div class="col-md-6">
                                        <label class="form-label">Mulai usulan</label>
                                        <input type="datetime-local" class="form-control" name="recommended_start_at" value="<?php echo e(old('recommended_start_at')); ?>">
                                        <?php $__errorArgs = ['recommended_start_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small mt-1"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Selesai usulan</label>
                                        <input type="datetime-local" class="form-control" name="recommended_end_at" value="<?php echo e(old('recommended_end_at')); ?>">
                                        <?php $__errorArgs = ['recommended_end_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small mt-1"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" name="decision" value="approve" class="btn btn-success flex-fill">Setujui</button>
                                <button type="submit" name="decision" value="reject" class="btn btn-outline-danger flex-fill">Tolak</button>
                                <?php if($stage === 'production'): ?>
                                    <button type="submit" name="decision" value="postpone" class="btn btn-outline-warning flex-fill" id="btn-postpone">Sarankan Tunda</button>
                                    <button type="submit" name="decision" value="cancel" class="btn btn-outline-secondary flex-fill">Sarankan Batal</button>
                                <?php endif; ?>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="text-secondary">Permohonan ini tidak sedang menunggu keputusan Anda.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        $(function () {
            const form = $('#approval-form');
            if (!form.length) {
                return;
            }

            form.validate({
                errorClass: 'is-invalid',
                errorElement: 'div',
                errorPlacement: function (error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('div').append(error);
                },
                rules: { notes: { maxlength: 2000 } },
                messages: { notes: { maxlength: 'Catatan maksimal 2000 karakter.' } },
            });

            const space_select = form.find('[name="recommended_docking_space"]');

            form.find('button[value="reject"]').on('click', function (event) {
                if (space_select.length) { space_select.rules('remove', 'required'); }
                form.find('[name="notes"]').rules('add', { required: true, messages: { required: 'Alasan penolakan wajib diisi.' } });
                if (!form.valid()) {
                    event.preventDefault();
                }
            });

            const start_input = form.find('[name="recommended_start_at"]');
            const end_input = form.find('[name="recommended_end_at"]');

            form.find('button[value="reject"], button[value="cancel"], button[value="approve"]').on('click', function () {
                if (start_input.length) {
                    start_input.rules('remove', 'required');
                    end_input.rules('remove', 'required');
                }
            });

            // First click reveals the schedule fields; the second click validates and submits.
            form.find('button[value="postpone"]').on('click', function (event) {
                const fields = $('#postpone-fields');

                if (fields.hasClass('d-none')) {
                    event.preventDefault();
                    fields.removeClass('d-none');
                    return;
                }

                const required = { required: true, messages: { required: 'Wajib diisi.' } };
                start_input.rules('add', required);
                end_input.rules('add', required);
                form.find('[name="notes"]').rules('add', { required: true, messages: { required: 'Alasan penundaan wajib diisi.' } });

                if (!form.valid()) {
                    event.preventDefault();
                }
            });

            form.find('button[value="cancel"]').on('click', function (event) {
                form.find('[name="notes"]').rules('add', { required: true, messages: { required: 'Alasan pembatalan wajib diisi.' } });

                if (!form.valid()) {
                    event.preventDefault();
                }
            });

            form.find('button[value="approve"]').on('click', function () {
                if (space_select.length) {
                    space_select.rules('add', { required: true, messages: { required: 'Docking space rekomendasi wajib dipilih.' } });
                }
                form.find('[name="notes"]').rules('remove', 'required');
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/docking-approval/show.blade.php ENDPATH**/ ?>