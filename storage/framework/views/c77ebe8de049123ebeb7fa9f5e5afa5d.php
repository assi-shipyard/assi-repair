<?php
    $features = $sheet['features'];
    $show_prices = $features['prices'];
    $show_progress = $features['progress'];
    $max_depth = \App\Services\project_boq_service::MAX_DEPTH;
    $job_status_labels = ['pending' => 'Belum Mulai', 'ongoing' => 'Berjalan', 'completed' => 'Selesai', 'cancelled' => 'Batal'];
    $job_status_classes = ['pending' => 'bg-secondary-lt', 'ongoing' => 'bg-blue-lt', 'completed' => 'bg-success-lt', 'cancelled' => 'bg-danger-lt'];
    $fmt_money = fn ($value): string => number_format((float) $value, 2, ',', '.');
    $fmt_qty = fn ($value): string => $value === null ? '-' : rtrim(rtrim(number_format((float) $value, 4, ',', '.'), '0'), ',');
    $fmt_date = fn ($date): ?string => $date?->locale('id')->translatedFormat('j F Y');
    $column_count = 4 + ($show_prices ? 3 : 0) + ($show_progress ? 2 : 0) + ($can_edit ? 1 : 0);
    $job_base = [$project->unique_id, $document->unique_id];
?>

<div class="row row-cards mb-3">
    <div class="col-6 col-lg-3">
        <div class="card card-sm"><div class="card-body">
            <div class="subheader">Pekerjaan</div>
            <div class="h2 mb-0"><?php echo e(number_format($sheet['job_count'], 0, ',', '.')); ?></div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-sm"><div class="card-body">
            <div class="subheader">Material</div>
            <div class="h2 mb-0"><?php echo e(number_format($sheet['material_count'], 0, ',', '.')); ?></div>
        </div></div>
    </div>
    <?php if($show_prices): ?>
        <div class="col-12 col-lg-3">
            <div class="card card-sm"><div class="card-body">
                <div class="subheader">Total Biaya (IDR)</div>
                <div class="h2 mb-0 text-primary"><?php echo e($fmt_money($sheet['grand_total'])); ?></div>
            </div></div>
        </div>
    <?php endif; ?>
    <?php if($show_progress): ?>
        <div class="col-12 col-lg-3">
            <div class="card card-sm"><div class="card-body">
                <div class="subheader">Progres Tertimbang</div>
                <div class="h2 mb-1"><?php echo e($sheet['progress'] !== null ? number_format((float) $sheet['progress'], 2, ',', '.').'%' : '-'); ?></div>
                <div class="progress progress-sm"><div class="progress-bar bg-success" style="width: <?php echo e((float) ($sheet['progress'] ?? 0)); ?>%"></div></div>
            </div></div>
        </div>
    <?php endif; ?>
</div>

<div class="alert alert-info">
    <div class="d-flex">
        <i class="ti ti-info-circle fs-2 me-2"></i>
        <div>
            Pekerjaan dikelompokkan per kelompok pekerjaan (I, II, ...) dan dapat memiliki sub pekerjaan (1.1, 1.1.1) maksimal <?php echo e($max_depth); ?> tingkat.
            <?php if($show_prices): ?>
                Jumlah dihitung dari volume × harga satuan; bila item Kamus Harga memiliki minimum charge, volume tagih memakai nilai minimum tersebut. Bobot dihitung ulang otomatis setiap ada perubahan.
                <div class="mt-1 small">Kamus Harga proyek: <strong><?php echo e($price_book?->name ?? 'Belum tersedia'); ?></strong><?php echo e($price_book && $project->price_book_id === null ? ' (kamus acuan)' : ''); ?></div>
            <?php elseif($show_progress): ?>
                Progres tertimbang memakai bobot dari RAB yang disalin ke tahap ini.
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if($show_prices || $show_progress): ?>
    <?php if(count($sheet['groups']) > 0): ?>
        <div class="card mb-3">
            <div class="card-header"><h3 class="card-title">Rekapitulasi per Kelompok</h3></div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-sm">
                    <thead>
                        <tr>
                            <th class="w-1">No</th>
                            <th>Kelompok Pekerjaan</th>
                            <th class="text-end">Pekerjaan</th>
                            <?php if($show_prices): ?><th class="text-end">Jumlah (IDR)</th><th class="text-end">Bobot</th><?php endif; ?>
                            <?php if($show_progress): ?><th class="text-end" style="min-width: 9rem">Progres</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $sheet['groups']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="fw-bold"><?php echo e($group['roman']); ?></td>
                                <td><?php echo e($group['label']); ?></td>
                                <td class="text-end"><?php echo e($group['job_count']); ?></td>
                                <?php if($show_prices): ?>
                                    <td class="text-end"><?php echo e($fmt_money($group['total'])); ?></td>
                                    <td class="text-end"><?php echo e($group['weight'] !== null ? number_format((float) $group['weight'], 2, ',', '.').'%' : '-'); ?></td>
                                <?php endif; ?>
                                <?php if($show_progress): ?>
                                    <td class="text-end"><?php echo e($group['progress'] !== null ? number_format((float) $group['progress'], 2, ',', '.').'%' : '-'); ?></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                    <?php if($show_prices): ?>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="3" class="text-end">Total</td>
                                <td class="text-end"><?php echo e($fmt_money($sheet['grand_total'])); ?></td>
                                <td class="text-end"><?php echo e(bccomp($sheet['grand_total'], '0', 2) > 0 ? '100,00%' : '-'); ?></td>
                                <?php if($show_progress): ?><td class="text-end"><?php echo e($sheet['progress'] !== null ? number_format((float) $sheet['progress'], 2, ',', '.').'%' : '-'); ?></td><?php endif; ?>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<div class="card">
    <div class="card-header flex-wrap gap-2">
        <h3 class="card-title">Daftar Pekerjaan</h3>
        <div class="card-actions d-flex flex-wrap gap-2">
            <input type="search" id="boq-search" class="form-control form-control-sm" style="max-width: 14rem" placeholder="Cari pekerjaan / material...">
            <label class="form-check form-switch mb-0 align-self-center">
                <input class="form-check-input" type="checkbox" id="boq-toggle-materials" checked>
                <span class="form-check-label small">Material</span>
            </label>
            <a href="<?php echo e(route('project.job-document.workflow.export', $job_base)); ?>" class="btn btn-sm btn-outline-secondary"><i class="ti ti-file-type-pdf me-1"></i>Ekspor PDF</a>
            <?php if($can_edit): ?>
                <button type="button" class="btn btn-sm btn-primary" id="boq-add-job"><i class="ti ti-plus me-1"></i>Tambah Pekerjaan</button>
            <?php endif; ?>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table table-sm boq-table" id="boq-table">
            <thead>
                <tr>
                    <th class="w-1">No</th>
                    <th style="min-width: 16rem">Uraian Pekerjaan</th>
                    <th class="text-end">Volume</th>
                    <th>Sat.</th>
                    <?php if($show_prices): ?>
                        <th class="text-end">Harga Satuan</th>
                        <th class="text-end">Jumlah (IDR)</th>
                        <th class="text-end">Bobot</th>
                    <?php endif; ?>
                    <?php if($show_progress): ?>
                        <th class="text-end">Progres</th>
                        <th>Status</th>
                    <?php endif; ?>
                    <?php if($can_edit): ?><th class="w-1"></th><?php endif; ?>
                </tr>
            </thead>
            <?php $__empty_1 = true; $__currentLoopData = $sheet['groups']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tbody class="boq-group">
                    <tr class="boq-group-row">
                        <td class="fw-bold"><?php echo e($group['roman']); ?></td>
                        <td colspan="<?php echo e($show_prices ? 4 : 3); ?>" class="fw-bold text-uppercase"><?php echo e($group['label']); ?></td>
                        <?php if($show_prices): ?>
                            <td class="text-end fw-bold"><?php echo e($fmt_money($group['total'])); ?></td>
                            <td class="text-end fw-bold"><?php echo e($group['weight'] !== null ? number_format((float) $group['weight'], 2, ',', '.').'%' : '-'); ?></td>
                        <?php endif; ?>
                        <?php if($show_progress): ?>
                            <td class="text-end fw-bold"><?php echo e($group['progress'] !== null ? number_format((float) $group['progress'], 2, ',', '.').'%' : '-'); ?></td>
                            <td></td>
                        <?php endif; ?>
                        <?php if($can_edit): ?><td></td><?php endif; ?>
                    </tr>
                    <?php $__currentLoopData = $group['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $job = $row['job'];
                            $calculation = $job->calculation;
                            $job_route = [...$job_base, $job->unique_id];
                            $job_payload = [
                                'id' => $job->unique_id,
                                'action' => route('project.job-document.workflow.job.update', $job_route),
                                'is_child' => $job->parent_job_id !== null,
                                'job_name' => $job->job_name,
                                'project_job_group_id' => $group['group']?->unique_id,
                                'price_item' => $job->price_book_item ? ['id' => $job->price_book_item->unique_id, 'text' => $job->price_book_item->code.' — '.$job->price_book_item->name] : null,
                                'minimum_quantity' => $job->minimum_quantity,
                                'job_volume_estimated' => $job->job_volume_estimated,
                                'unit' => $job->unit,
                                'unit_price' => $job->unit_price,
                                'calculation' => $calculation,
                                'notes' => $job->notes,
                                'responsible_kind' => $job->responsible_kind,
                                'responsible_name' => $job->responsible_name,
                                'est_start_date' => $job->est_start_date?->format('Y-m-d'),
                                'est_finish_date' => $job->est_finish_date?->format('Y-m-d'),
                                'progress_percent' => $job->progress_percent,
                                'status' => $job->status,
                                'actual_start_date' => $job->actual_start_date?->format('Y-m-d'),
                                'actual_finish_date' => $job->actual_finish_date?->format('Y-m-d'),
                                'actual_volume' => $job->actual_volume,
                            ];
                        ?>
                        <tr class="boq-job-row <?php echo e($row['depth'] === 0 ? 'boq-root' : ''); ?>" data-search="<?php echo e(mb_strtolower($job->job_name.' '.$job->materials->pluck('material_name')->implode(' '))); ?>">
                            <td class="text-secondary text-nowrap"><?php echo e($row['number']); ?></td>
                            <td>
                                <div style="padding-left: <?php echo e($row['depth'] * 1.25); ?>rem">
                                    <div class="<?php echo e($row['depth'] === 0 ? 'fw-bold' : 'fw-semibold'); ?>">
                                        <?php if($row['depth'] > 0): ?><i class="ti ti-corner-down-right text-secondary me-1"></i><?php endif; ?><?php echo e($job->job_name); ?>

                                    </div>
                                    <div class="small text-secondary d-flex flex-wrap column-gap-2">
                                        <?php if($job->price_book_item): ?><span><i class="ti ti-book me-1"></i><?php echo e($job->price_book_item->code); ?></span><?php endif; ?>
                                        <?php if($job->minimum_quantity !== null): ?><span class="text-warning">Min. <?php echo e($fmt_qty($job->minimum_quantity)); ?> <?php echo e($job->unit); ?></span><?php endif; ?>
                                        <?php if($calculation): ?>
                                            <span><i class="ti ti-calculator me-1"></i>
                                                <?php if($calculation['type'] === 'plate'): ?>
                                                    Pelat <?php echo e($fmt_qty($calculation['length'])); ?>×<?php echo e($fmt_qty($calculation['width'])); ?>×<?php echo e($fmt_qty($calculation['thickness'])); ?> mm × <?php echo e($calculation['quantity']); ?>

                                                <?php else: ?>
                                                    Pipa Ø<?php echo e($fmt_qty($calculation['diameter'])); ?>×<?php echo e($fmt_qty($calculation['wall_thickness'])); ?> mm, <?php echo e($fmt_qty($calculation['pipe_length'])); ?> mm × <?php echo e($calculation['quantity']); ?>

                                                <?php endif; ?>
                                                = <?php echo e($fmt_qty($calculation['weight_kg'])); ?> kg
                                            </span>
                                        <?php endif; ?>
                                        <?php if($job->responsible_name): ?><span><i class="ti ti-user me-1"></i><?php echo e($job->responsible_name); ?></span><?php endif; ?>
                                        <?php if($job->est_start_date || $job->est_finish_date): ?><span><i class="ti ti-calendar me-1"></i><?php echo e($fmt_date($job->est_start_date) ?? '-'); ?> – <?php echo e($fmt_date($job->est_finish_date) ?? '-'); ?></span><?php endif; ?>
                                        <?php if($job->photos->isNotEmpty()): ?><span><i class="ti ti-photo me-1"></i><?php echo e($job->photos->count()); ?> foto</span><?php endif; ?>
                                    </div>
                                    <?php if($job->notes): ?><div class="small text-secondary fst-italic"><?php echo e($job->notes); ?></div><?php endif; ?>
                                </div>
                            </td>
                            <td class="text-end text-nowrap"><?php echo e($fmt_qty($job->job_volume_estimated)); ?></td>
                            <td><?php echo e($job->unit ?? '-'); ?></td>
                            <?php if($show_prices): ?>
                                <td class="text-end text-nowrap"><?php echo e($job->unit_price !== null ? $fmt_money($job->unit_price) : '-'); ?></td>
                                <td class="text-end text-nowrap">
                                    <?php echo e($job->est_price !== null ? $fmt_money($job->est_price) : '-'); ?>

                                    <?php if($row['has_children'] || bccomp($row['materials_total'], '0', 2) > 0): ?>
                                        <div class="small text-secondary" title="Termasuk material dan sub pekerjaan">Σ <?php echo e($fmt_money($row['subtotal'])); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end"><?php echo e($job->job_weight_percent !== null ? number_format((float) $job->job_weight_percent, 2, ',', '.').'%' : '-'); ?></td>
                            <?php endif; ?>
                            <?php if($show_progress): ?>
                                <td class="text-end text-nowrap"><?php echo e(number_format((float) ($job->progress_percent ?? 0), 2, ',', '.')); ?>%</td>
                                <td><span class="badge <?php echo e($job_status_classes[$job->status] ?? 'bg-secondary-lt'); ?>"><?php echo e($job_status_labels[$job->status] ?? $job->status); ?></span></td>
                            <?php endif; ?>
                            <?php if($can_edit): ?>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-ghost-secondary btn-icon" type="button" data-bs-toggle="dropdown" aria-label="Aksi"><i class="ti ti-dots-vertical"></i></button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <button type="button" class="dropdown-item boq-edit-job" data-job="<?php echo e(json_encode($job_payload)); ?>"><i class="ti ti-edit me-2"></i>Ubah</button>
                                            <?php if($row['depth'] < $max_depth - 1): ?>
                                                <button type="button" class="dropdown-item boq-add-child" data-parent-id="<?php echo e($job->unique_id); ?>" data-parent-name="<?php echo e($row['number'].' '.$job->job_name); ?>"><i class="ti ti-subtask me-2"></i>Tambah Sub Pekerjaan</button>
                                            <?php endif; ?>
                                            <button type="button" class="dropdown-item boq-add-material" data-action="<?php echo e(route('project.job-document.workflow.material.store', $job_route)); ?>" data-job-name="<?php echo e($row['number'].' '.$job->job_name); ?>"><i class="ti ti-package me-2"></i>Tambah Material</button>
                                            <a class="dropdown-item" href="<?php echo e(route('project.job-document.workflow.job.photos', $job_route)); ?>"><i class="ti ti-photo me-2"></i>Foto</a>
                                            <div class="dropdown-divider"></div>
                                            <button type="button" class="dropdown-item text-danger boq-delete" data-action="<?php echo e(route('project.job-document.workflow.job.destroy', $job_route)); ?>" data-title="Hapus pekerjaan?" data-text="<?php echo e($row['has_children'] ? 'Sub pekerjaan dan material di bawahnya ikut terhapus.' : 'Material di bawahnya ikut terhapus.'); ?>"><i class="ti ti-trash me-2"></i>Hapus</button>
                                        </div>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                        <?php $__currentLoopData = $row['materials']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $material_row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php ($material = $material_row['material']); ?>
                            <tr class="boq-material-row" data-search="<?php echo e(mb_strtolower($job->job_name.' '.$material->material_name)); ?>">
                                <td></td>
                                <td>
                                    <div class="small" style="padding-left: <?php echo e($row['depth'] * 1.25 + 1.25); ?>rem">
                                        <span class="text-secondary"><?php echo e($material_row['letter']); ?>.</span> <?php echo e($material->material_name); ?>

                                        <?php if($material->dimension): ?><span class="text-secondary">· <?php echo e($material->dimension); ?></span><?php endif; ?>
                                        <?php if($material->minimum_quantity !== null): ?><span class="text-warning">· Min. <?php echo e($fmt_qty($material->minimum_quantity)); ?> <?php echo e($material->unit); ?></span><?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-end small text-nowrap"><?php echo e($fmt_qty($material->volume)); ?></td>
                                <td class="small"><?php echo e($material->unit ?? '-'); ?></td>
                                <?php if($show_prices): ?>
                                    <td class="text-end small text-nowrap"><?php echo e($material->unit_price !== null ? $fmt_money($material->unit_price) : '-'); ?></td>
                                    <td class="text-end small text-nowrap"><?php echo e($material->price !== null ? $fmt_money($material->price) : '-'); ?></td>
                                    <td></td>
                                <?php endif; ?>
                                <?php if($show_progress): ?><td></td><td></td><?php endif; ?>
                                <?php if($can_edit): ?>
                                    <td class="text-end text-nowrap">
                                        <button type="button" class="btn btn-sm btn-ghost-primary btn-icon boq-edit-material" aria-label="Ubah material"
                                            data-material="<?php echo e(json_encode([
                                                'action' => route('project.job-document.workflow.material.update', [...$job_route, $material->unique_id]),
                                                'job_name' => $row['number'].' '.$job->job_name,
                                                'material_name' => $material->material_name,
                                                'price_item' => $material->price_book_item_id && $material->price_book_item ? ['id' => $material->price_book_item->unique_id, 'text' => $material->price_book_item->code.' — '.$material->price_book_item->name] : null,
                                                'minimum_quantity' => $material->minimum_quantity,
                                                'volume' => $material->volume,
                                                'unit' => $material->unit,
                                                'unit_price' => $material->unit_price,
                                                'dimension' => $material->dimension,
                                                'notes' => $material->notes,
                                            ])); ?>"><i class="ti ti-edit"></i></button>
                                        <button type="button" class="btn btn-sm btn-ghost-danger btn-icon boq-delete" aria-label="Hapus material" data-action="<?php echo e(route('project.job-document.workflow.material.destroy', [...$job_route, $material->unique_id])); ?>" data-title="Hapus material?" data-text="<?php echo e($material->material_name); ?>"><i class="ti ti-trash"></i></button>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tbody>
                    <tr>
                        <td colspan="<?php echo e($column_count); ?>" class="text-center text-secondary py-5">
                            <div class="mb-2"><i class="ti ti-list-details fs-1"></i></div>
                            Belum ada pekerjaan.
                            <?php if($can_edit): ?> Klik <strong>Tambah Pekerjaan</strong> untuk mulai menyusun daftar. <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            <?php endif; ?>
            <?php if($show_prices && count($sheet['groups']) > 0): ?>
                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="5" class="text-end">Total</td>
                        <td class="text-end text-nowrap"><?php echo e($fmt_money($sheet['grand_total'])); ?></td>
                        <td colspan="<?php echo e($column_count - 6); ?>"></td>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>
    <div class="card-footer text-secondary small d-none" id="boq-search-empty">Tidak ada pekerjaan yang cocok dengan pencarian.</div>
</div>

<?php if($can_edit): ?>
    <?php echo $__env->make('project.job-document.partials.boq-modals', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        (function () {
            const $table = $('#boq-table');
            $('#boq-toggle-materials').on('change', function () {
                $table.toggleClass('hide-materials', !this.checked);
            });
            $('#boq-search').on('input', function () {
                const term = this.value.trim().toLowerCase();
                let visible = 0;
                $table.find('tbody.boq-group').each(function () {
                    let group_visible = 0;
                    $(this).find('tr[data-search]').each(function () {
                        const match = term === '' || this.dataset.search.includes(term);
                        $(this).toggleClass('d-none', !match);
                        if (match) { group_visible++; }
                    });
                    $(this).toggleClass('d-none', group_visible === 0);
                    visible += group_visible;
                });
                $('#boq-search-empty').toggleClass('d-none', visible > 0 || term === '');
            });
        })();
    </script>
<?php $__env->stopPush(); ?>
<?php /**PATH /home/snowy/projects/assi-repair/resources/views/project/job-document/partials/boq-sheet.blade.php ENDPATH**/ ?>