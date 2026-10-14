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
    $uses_reports = $features['progress_reports'] ?? false;
    $progress_weight_column = $show_progress && ! $show_prices;
    $fmt_pct = fn ($value): string => $value === null ? '-' : number_format((float) $value, 2, ',', '.').'%';
    $column_count = 4 + ($show_prices ? 3 : 0) + ($show_progress ? 3 + ($progress_weight_column ? 1 : 0) : 0) + ($can_edit ? 1 : 0);
    $job_base = [$project->unique_id, $document->unique_id];
    $rule_labels = collect(\App\Services\boq_rule_service::RULES)->map(fn (array $rule): string => $rule['label'])->all();
    $old_minimum = $errors->hasAny(['minimum_quantity', 'reason', 'price_book_item_id']) && old('action') === 'set'
        ? ['item_id' => old('price_book_item_id'), 'minimum' => old('minimum_quantity'), 'reason' => old('reason')]
        : null;
    $billed_differs = fn ($billed, $volume): bool => $billed !== null && $volume !== null && bccomp((string) $billed, (string) $volume, 4) !== 0;
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
                Jumlah dihitung dari volume tagih × harga satuan. Volume tagih mengikuti aturan perhitungan item (pelat, pipa, docking, crane, kapal tunda, luasan) dan batas minimum proyek. Bobot dihitung ulang otomatis setiap ada perubahan.
                <div class="mt-1 small">Kamus Harga proyek: <strong><?php echo e($price_book?->name ?? 'Belum tersedia'); ?></strong><?php echo e($price_book && $project->price_book_id === null ? ' (kamus acuan)' : ''); ?></div>
            <?php elseif($show_progress): ?>
                Progres tertimbang memakai bobot dari RAB yang disalin ke tahap ini.
            <?php endif; ?>
            <?php if($show_progress): ?>
                <div class="mt-1 small">
                    Progres pekerjaan induk dihitung otomatis dari sub pekerjaannya: <strong>Bobot</strong> = porsi biaya pekerjaan beserta sub pekerjaannya, <strong>Progres</strong> = realisasi aktual, <strong>Tertimbang</strong> = progres × bobot.
                    <?php if($uses_reports): ?> Progres hanya berubah melalui laporan <strong>Progres Harian</strong> yang disetujui PIMPRO Reparasi dan PPC. <?php endif; ?>
                </div>
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
                            <?php if($progress_weight_column): ?><th class="text-end">Bobot</th><?php endif; ?>
                            <?php if($show_progress): ?><th class="text-end" style="min-width: 9rem">Progres</th><th class="text-end">Tertimbang</th><?php endif; ?>
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
                                <?php if($progress_weight_column): ?><td class="text-end"><?php echo e($fmt_pct($group['weight'])); ?></td><?php endif; ?>
                                <?php if($show_progress): ?>
                                    <td class="text-end">
                                        <?php echo e($fmt_pct($group['progress'])); ?>

                                        <div class="progress progress-sm mt-1"><div class="progress-bar bg-success" style="width: <?php echo e((float) ($group['progress'] ?? 0)); ?>%"></div></div>
                                    </td>
                                    <td class="text-end"><?php echo e($fmt_pct($group['weighted'])); ?></td>
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
                                <?php if($show_progress): ?><td class="text-end"><?php echo e($fmt_pct($sheet['progress'])); ?></td><td class="text-end"><?php echo e($fmt_pct($sheet['progress'])); ?></td><?php endif; ?>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php if($show_prices && count($sheet['minimums']) > 0): ?>
    <div class="card mb-3">
        <div class="card-header">
            <div>
                <h3 class="card-title">Batas Minimum &amp; Negosiasi</h3>
                <div class="card-subtitle">Hanya tampil di internal. Dokumen untuk pelanggan tidak menampilkan penerapan minimum.</div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-sm">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Berlaku</th>
                        <th class="text-end">Minimum</th>
                        <th class="text-end">Terpakai</th>
                        <th class="text-end">Ditagih</th>
                        <th>Keterangan</th>
                        <?php if($can_edit): ?><th class="w-1"></th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $sheet['minimums']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $minimum_row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $override = $minimum_row['override']; ?>
                        <tr>
                            <td><div class="small text-secondary"><?php echo e($minimum_row['item']->code); ?></div><div><?php echo e($minimum_row['item']->name); ?></div></td>
                            <td class="text-nowrap"><?php echo e($minimum_row['scope'] === 'piece' ? 'Per keping' : 'Total proyek'); ?> <span class="text-secondary small">(<?php echo e($minimum_row['line_count']); ?> baris)</span></td>
                            <td class="text-end text-nowrap">
                                <?php if($override): ?>
                                    <span class="badge bg-purple-lt"><?php echo e(bccomp((string) $override['minimum'], '0', 4) === 0 ? 'Ditiadakan' : $fmt_qty($override['minimum']).' '.$minimum_row['unit']); ?></span>
                                    <?php if($minimum_row['original'] !== null): ?><div class="small text-secondary text-decoration-line-through"><?php echo e($fmt_qty($minimum_row['original'])); ?> <?php echo e($minimum_row['unit']); ?></div><?php endif; ?>
                                <?php else: ?>
                                    <?php echo e($minimum_row['original'] !== null ? $fmt_qty($minimum_row['original']).' '.$minimum_row['unit'] : '-'); ?>

                                <?php endif; ?>
                            </td>
                            <td class="text-end text-nowrap"><?php echo e($fmt_qty($minimum_row['used'])); ?> <?php echo e($minimum_row['unit']); ?></td>
                            <td class="text-end text-nowrap fw-semibold"><?php echo e($fmt_qty($minimum_row['billed'])); ?> <?php echo e($minimum_row['unit']); ?></td>
                            <td class="small">
                                <?php if(bccomp($minimum_row['shortfall'], '0', 4) > 0): ?>
                                    <span class="text-warning">Kekurangan <?php echo e($fmt_qty($minimum_row['shortfall'])); ?> <?php echo e($minimum_row['unit']); ?> dibebankan ke baris pemakaian terbesar.</span>
                                <?php endif; ?>
                                <?php if($override): ?>
                                    <div class="text-secondary">Negosiasi: <?php echo e($override['reason']); ?> — <?php echo e($override['user_name'] ?? 'Pengguna'); ?>, <?php echo e(\Carbon\CarbonImmutable::parse($override['at'])->timezone(config('app.timezone'))->locale('id')->translatedFormat('j F Y H.i')); ?> WIB</div>
                                <?php endif; ?>
                            </td>
                            <?php if($can_edit): ?>
                                <td class="text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary boq-minimum-edit"
                                        data-item-id="<?php echo e($minimum_row['item']->unique_id); ?>"
                                        data-item-name="<?php echo e($minimum_row['item']->code.' — '.$minimum_row['item']->name); ?>"
                                        data-unit="<?php echo e($minimum_row['unit']); ?>"
                                        data-minimum="<?php echo e($override['minimum'] ?? $minimum_row['original']); ?>"
                                        data-reason="<?php echo e($override['reason'] ?? ''); ?>">Ubah</button>
                                    <?php if($override): ?>
                                        <form method="POST" action="<?php echo e(route('project.job-document.workflow.minimum-override.save', $job_base)); ?>" class="d-inline">
                                            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                                            <input type="hidden" name="price_book_item_id" value="<?php echo e($minimum_row['item']->unique_id); ?>">
                                            <input type="hidden" name="action" value="clear">
                                            <button type="submit" class="btn btn-sm btn-ghost-secondary">Reset</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer small text-secondary">
            <i class="ti ti-info-circle me-1"></i>Perubahan minimum hasil negosiasi tercatat di riwayat dokumen. Total akhir setelah negosiasi disetujui oleh Manajer Pemasaran.
        </div>
    </div>
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
                        <?php if($progress_weight_column): ?><th class="text-end">Bobot</th><?php endif; ?>
                        <th class="text-end">Progres</th>
                        <th class="text-end">Tertimbang</th>
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
                            <?php if($progress_weight_column): ?><td class="text-end fw-bold"><?php echo e($fmt_pct($group['weight'])); ?></td><?php endif; ?>
                            <td class="text-end fw-bold"><?php echo e($fmt_pct($group['progress'])); ?></td>
                            <td class="text-end fw-bold"><?php echo e($fmt_pct($group['weighted'])); ?></td>
                            <td></td>
                        <?php endif; ?>
                        <?php if($can_edit): ?><td></td><?php endif; ?>
                    </tr>
                    <?php $__currentLoopData = $group['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $job = $row['job'];
                            $calculation = $job->calculation;
                            $job_route = [...$job_base, $job->unique_id];
                            $job_photos_payload = $job->photos->map(fn ($p): array => [
                                'id' => $p->unique_id,
                                'category' => $p->photo_category,
                                'category_label' => match ($p->photo_category) {
                                    'quotation' => 'Penawaran',
                                    'progress' => 'Progres',
                                    'result_before' => 'Sebelum Perbaikan',
                                    'result_after' => 'Sesudah Perbaikan',
                                    default => $p->photo_category ? ucfirst($p->photo_category) : 'Umum',
                                },
                                'caption' => $p->caption,
                                'taken_at' => $p->taken_at ? $p->taken_at->timezone('Asia/Jakarta')->tanggal_waktu_indonesia().' WIB' : null,
                                'created_at' => $p->created_at ? $p->created_at->timezone('Asia/Jakarta')->tanggal_waktu_indonesia().' WIB' : null,
                                'url' => route('project.job-document.workflow.job.photo.show', [...$job_route, $p->unique_id]),
                                'delete_url' => route('project.job-document.workflow.job.photo.destroy', [...$job_route, $p->unique_id]),
                            ])->values()->all();
                            $job_payload = [
                                'id' => $job->unique_id,
                                'action' => route('project.job-document.workflow.job.update', $job_route),
                                'is_child' => $job->parent_job_id !== null,
                                'job_name' => $job->job_name,
                                'project_job_group_id' => $group['group']?->unique_id,
                                'price_item' => $job->price_book_item ? ['id' => $job->price_book_item->unique_id, 'text' => $job->price_book_item->code.' — '.$job->price_book_item->name, 'rule' => $job->price_book_item->calculation_rule] : null,
                                'calculation_rule' => $job->calculation_rule,
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
                        <tr class="boq-job-row <?php echo e($row['depth'] === 0 ? 'boq-root' : ''); ?>" data-job-id="<?php echo e($job->unique_id); ?>" data-search="<?php echo e(mb_strtolower($job->job_name.' '.$job->materials->pluck('material_name')->implode(' '))); ?>">
                            <td class="text-secondary text-nowrap"><?php echo e($row['number']); ?></td>
                            <td>
                                <div style="padding-left: <?php echo e($row['depth'] * 1.25); ?>rem">
                                    <div class="<?php echo e($row['depth'] === 0 ? 'fw-bold' : 'fw-semibold'); ?>">
                                        <?php if($row['depth'] > 0): ?><i class="ti ti-corner-down-right text-secondary me-1"></i><?php endif; ?><?php echo e($job->job_name); ?>

                                    </div>
                                    <div class="small text-secondary d-flex flex-wrap column-gap-2">
                                        <?php if($job->price_book_item): ?><span><i class="ti ti-book me-1"></i><?php echo e($job->price_book_item->code); ?></span><?php endif; ?>
                                        <?php if($job->calculation_rule): ?><span class="badge bg-azure-lt"><?php echo e($rule_labels[$job->calculation_rule] ?? $job->calculation_rule); ?></span><?php endif; ?>
                                        <?php if($show_prices && $job->minimum_quantity !== null): ?><span class="text-warning">Min. <?php echo e($fmt_qty($job->minimum_quantity)); ?> <?php echo e($job->unit); ?></span><?php endif; ?>
                                        <?php if($row['calculation_text']): ?><span><i class="ti ti-calculator me-1"></i><?php echo e($row['calculation_text']); ?></span><?php endif; ?>
                                        <?php if($job->responsible_name): ?><span><i class="ti ti-user me-1"></i><?php echo e($job->responsible_name); ?></span><?php endif; ?>
                                        <?php if($job->est_start_date || $job->est_finish_date): ?><span><i class="ti ti-calendar me-1"></i><?php echo e($fmt_date($job->est_start_date) ?? '-'); ?> – <?php echo e($fmt_date($job->est_finish_date) ?? '-'); ?></span><?php endif; ?>
                                        <span class="boq-job-photo-badge-wrapper" data-job-id="<?php echo e($job->unique_id); ?>">
                                            <button type="button" class="btn btn-sm btn-ghost-secondary p-0 border-0 text-secondary boq-job-photos-btn <?php echo e($job->photos->isEmpty() ? 'd-none' : ''); ?>"
                                                data-job-id="<?php echo e($job->unique_id); ?>"
                                                data-job-name="<?php echo e($row['number'].' '.$job->job_name); ?>"
                                                data-upload-url="<?php echo e(route('project.job-document.workflow.job.photo.store', $job_route)); ?>"
                                                data-photos-url="<?php echo e(route('project.job-document.workflow.job.photos', $job_route)); ?>"
                                                data-photos="<?php echo e(json_encode($job_photos_payload)); ?>"
                                                title="Lihat foto pekerjaan">
                                                <i class="ti ti-photo me-1"></i><span class="boq-job-photo-count"><?php echo e($job->photos->count()); ?></span> foto
                                            </button>
                                        </span>
                                    </div>
                                    <?php if($job->notes): ?><div class="small text-secondary fst-italic"><?php echo e($job->notes); ?></div><?php endif; ?>
                                </div>
                            </td>
                            <td class="text-end text-nowrap">
                                <?php echo e($fmt_qty($job->job_volume_estimated)); ?>

                                <?php if($show_prices && $billed_differs($job->billed_quantity, $job->job_volume_estimated)): ?>
                                    <div class="small text-warning" title="Volume yang ditagihkan setelah aturan perhitungan dan minimum">Tagih <?php echo e($fmt_qty($job->billed_quantity)); ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($job->unit ?? '-'); ?></td>
                            <?php if($show_prices): ?>
                                <td class="text-end text-nowrap"><?php echo e($job->unit_price !== null ? $fmt_money($job->unit_price) : '-'); ?></td>
                                <td class="text-end text-nowrap">
                                    <?php echo e($job->est_price !== null ? $fmt_money($job->est_price) : '-'); ?>

                                    <?php if($row['has_children'] || bccomp($row['materials_total'], '0', 2) > 0): ?>
                                        <div class="small text-secondary" title="Termasuk material dan sub pekerjaan">Σ <?php echo e($fmt_money($row['subtotal'])); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end"><?php echo e($fmt_pct($show_progress ? ($row['progress']['weight'] ?? null) : $job->job_weight_percent)); ?></td>
                            <?php endif; ?>
                            <?php if($show_progress): ?>
                                <?php ($row_status = $row['progress']['status'] ?? $job->status); ?>
                                <?php if($progress_weight_column): ?><td class="text-end"><?php echo e($fmt_pct($row['progress']['weight'] ?? null)); ?></td><?php endif; ?>
                                <td class="text-end text-nowrap <?php echo e($row['has_children'] ? 'fst-italic' : ''); ?>" <?php if($row['has_children']): ?> title="Dihitung dari sub pekerjaan" <?php endif; ?>><?php echo e($fmt_pct($row['progress']['actual'] ?? '0')); ?></td>
                                <td class="text-end text-nowrap"><?php echo e($fmt_pct($row['progress']['weighted'] ?? null)); ?></td>
                                <td><span class="badge <?php echo e($job_status_classes[$row_status] ?? 'bg-secondary-lt'); ?>"><?php echo e($job_status_labels[$row_status] ?? $row_status); ?></span></td>
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
                                            <button type="button" class="dropdown-item boq-job-photos-btn"
                                                data-job-id="<?php echo e($job->unique_id); ?>"
                                                data-job-name="<?php echo e($row['number'].' '.$job->job_name); ?>"
                                                data-upload-url="<?php echo e(route('project.job-document.workflow.job.photo.store', $job_route)); ?>"
                                                data-photos-url="<?php echo e(route('project.job-document.workflow.job.photos', $job_route)); ?>"
                                                data-photos="<?php echo e(json_encode($job_photos_payload)); ?>">
                                                <i class="ti ti-photo me-2"></i>Foto
                                            </button>
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
                                        <?php if($show_prices && $material->minimum_quantity !== null): ?><span class="text-warning">· Min. <?php echo e($fmt_qty($material->minimum_quantity)); ?> <?php echo e($material->unit); ?></span><?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-end small text-nowrap">
                                    <?php echo e($fmt_qty($material->volume)); ?>

                                    <?php if($show_prices && $billed_differs($material->billed_quantity, $material->volume)): ?>
                                        <div class="text-warning">Tagih <?php echo e($fmt_qty($material->billed_quantity)); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="small"><?php echo e($material->unit ?? '-'); ?></td>
                                <?php if($show_prices): ?>
                                    <td class="text-end small text-nowrap"><?php echo e($material->unit_price !== null ? $fmt_money($material->unit_price) : '-'); ?></td>
                                    <td class="text-end small text-nowrap"><?php echo e($material->price !== null ? $fmt_money($material->price) : '-'); ?></td>
                                    <td></td>
                                <?php endif; ?>
                                <?php if($show_progress): ?><td colspan="<?php echo e($progress_weight_column ? 4 : 3); ?>"></td><?php endif; ?>
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

<?php echo $__env->make('project.job-document.partials.photo-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php if($can_edit && $show_prices && count($sheet['minimums']) > 0): ?>
    <div class="modal modal-blur fade" id="boq-minimum-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" id="boq-minimum-form" method="POST" action="<?php echo e(route('project.job-document.workflow.minimum-override.save', $job_base)); ?>" novalidate>
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <input type="hidden" name="action" value="set">
                <input type="hidden" name="price_book_item_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title">Minimum Hasil Negosiasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <?php if($errors->has('minimum_quantity') || $errors->has('reason') || $errors->has('price_book_item_id')): ?>
                        <div class="alert alert-danger"><ul class="mb-0 ps-3"><?php $__currentLoopData = ['price_book_item_id', 'minimum_quantity', 'reason']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error_key): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php $__currentLoopData = $errors->get($error_key); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul></div>
                    <?php endif; ?>
                    <div class="mb-3 fw-semibold" id="boq-minimum-item"></div>
                    <div class="mb-3">
                        <label class="form-label required" for="boq-minimum-quantity">Minimum Baru</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="minimum_quantity" id="boq-minimum-quantity" min="0" step="0.0001" required>
                            <span class="input-group-text" id="boq-minimum-unit"></span>
                        </div>
                        <div class="form-hint">Isi 0 untuk meniadakan minimum pada dokumen ini.</div>
                    </div>
                    <div>
                        <label class="form-label required" for="boq-minimum-reason">Alasan Negosiasi</label>
                        <textarea class="form-control" name="reason" id="boq-minimum-reason" rows="3" minlength="5" maxlength="500" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
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

            const $minimum_form = $('#boq-minimum-form');
            if ($minimum_form.length) {
                $minimum_form.validate({
                    errorElement: 'div',
                    errorClass: 'invalid-feedback',
                    highlight: element => $(element).addClass('is-invalid'),
                    unhighlight: element => $(element).removeClass('is-invalid'),
                    errorPlacement: (error, element) => error.insertAfter(element.closest('.input-group').length ? element.closest('.input-group') : element),
                    rules: { minimum_quantity: { required: true, number: true, min: 0, max: 999999999 }, reason: { required: true, minlength: 5, maxlength: 500 } },
                    messages: {
                        minimum_quantity: { required: 'Minimum baru wajib diisi.', number: 'Masukkan angka yang valid.', min: 'Minimum tidak boleh negatif.' },
                        reason: { required: 'Alasan negosiasi wajib diisi.', minlength: 'Alasan minimal 5 karakter.', maxlength: 'Alasan maksimal 500 karakter.' },
                    },
                });
                const open_minimum = function (data) {
                    $minimum_form.find('[name=price_book_item_id]').val(data.item_id);
                    $('#boq-minimum-item').text(data.item_name || '');
                    $('#boq-minimum-unit').text(data.unit || '');
                    $('#boq-minimum-quantity').val(data.minimum || '');
                    $('#boq-minimum-reason').val(data.reason || '');
                    $('#boq-minimum-modal').modal('show');
                };
                $(document).on('click', '.boq-minimum-edit', function () {
                    $minimum_form.find('.alert-danger').remove();
                    open_minimum({ item_id: this.dataset.itemId, item_name: this.dataset.itemName, unit: this.dataset.unit, minimum: this.dataset.minimum, reason: this.dataset.reason });
                });
                const old_minimum = <?php echo json_encode($old_minimum, 15, 512) ?>;
                if (old_minimum) {
                    const $button = $('.boq-minimum-edit[data-item-id="' + old_minimum.item_id + '"]');
                    open_minimum({ ...old_minimum, item_name: $button.data('itemName'), unit: $button.data('unit') });
                }
            }
        })();
    </script>
<?php $__env->stopPush(); ?>
<?php /**PATH /home/snowy/projects/assi-repair/resources/views/project/job-document/partials/boq-sheet.blade.php ENDPATH**/ ?>