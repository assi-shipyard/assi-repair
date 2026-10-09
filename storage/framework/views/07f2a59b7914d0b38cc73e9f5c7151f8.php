<?php $__env->startSection('title', $stage_title); ?>
<?php $__env->startSection('body_title', 'Tinjau Permohonan Docking'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('docking-approval.index', $stage)); ?>" class="btn btn-outline-secondary">Kembali</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php
        $ship = $docking_request->ship;
        $space = $docking_request->requested_docking_space;
        $status_labels = [
            'submitted' => 'Menunggu Engineering',
            'engineering_approved' => 'Menunggu Produksi',
            'approved' => 'Disetujui / menunggu kedatangan',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
        ];
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
                    <div class="card-actions"><span class="badge bg-blue-lt text-blue"><?php echo e($status_labels[$docking_request->request_status] ?? $docking_request->request_status); ?></span></div>
                </div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item"><div class="datagrid-title">Kapal</div><div class="datagrid-content"><?php echo e($ship?->name ?? '-'); ?></div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Pemilik</div><div class="datagrid-content"><?php echo e($ship?->company?->name ?? '-'); ?></div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Tipe</div><div class="datagrid-content"><?php echo e($ship?->type?->name ?? '-'); ?></div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Jadwal mulai</div><div class="datagrid-content"><?php echo e($docking_request->requested_start_at?->format('d/m/Y H:i')); ?></div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Jadwal selesai</div><div class="datagrid-content"><?php echo e($docking_request->requested_end_at?->format('d/m/Y H:i') ?? '-'); ?></div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Docking space</div><div class="datagrid-content"><?php echo e($space?->name ?? '-'); ?></div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Diajukan oleh</div><div class="datagrid-content"><?php echo e($docking_request->requester?->employee_id ?? '-'); ?></div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Diajukan pada</div><div class="datagrid-content"><?php echo e($docking_request->created_at?->format('d/m/Y H:i')); ?></div></div>
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
                            <span class="text-secondary small"><?php echo e($document->created_at?->format('d/m/Y H:i')); ?></span>
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
                                <div class="text-success small">Disetujui <?php echo e($docking_request->engineering_approver?->employee_id ?? '-'); ?>, <?php echo e($docking_request->engineering_approved_at->format('d/m/Y H:i')); ?></div>
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
                                <div class="text-success small">Disetujui <?php echo e($docking_request->production_approver?->employee_id ?? '-'); ?>, <?php echo e($docking_request->production_approved_at->format('d/m/Y H:i')); ?></div>
                            <?php elseif($docking_request->rejection_stage === 'production'): ?>
                                <div class="text-danger small">Ditolak: <?php echo e($docking_request->rejection_reason); ?></div>
                            <?php else: ?>
                                <div class="text-secondary small">Menunggu</div>
                            <?php endif; ?>
                            <?php if($docking_request->production_notes && $docking_request->rejection_stage !== 'production'): ?>
                                <div class="small">Catatan: <?php echo e($docking_request->production_notes); ?></div>
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
                            <div class="d-flex gap-2">
                                <button type="submit" name="decision" value="approve" class="btn btn-success flex-fill">Setujui</button>
                                <button type="submit" name="decision" value="reject" class="btn btn-outline-danger flex-fill">Tolak</button>
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

            form.find('button[value="reject"]').on('click', function (event) {
                form.find('[name="notes"]').rules('add', { required: true, messages: { required: 'Alasan penolakan wajib diisi.' } });
                if (!form.valid()) {
                    event.preventDefault();
                }
            });

            form.find('button[value="approve"]').on('click', function () {
                form.find('[name="notes"]').rules('remove', 'required');
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/docking-approval/show.blade.php ENDPATH**/ ?>