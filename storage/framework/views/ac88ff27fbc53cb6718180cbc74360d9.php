<?php
    $features = $sheet['features'];
    $show_prices = $features['export_prices'];
    $show_progress = $features['progress'];
    $progress_weight_column = $show_progress && ! $show_prices;
    $money = fn ($value): string => $value === null ? '-' : number_format((float) $value, 2, ',', '.');
    $qty = fn ($value): string => $value === null ? '-' : rtrim(rtrim(number_format((float) $value, 4, ',', '.'), '0'), ',');
    $percent = fn ($value): string => $value === null ? '-' : number_format((float) $value, 2, ',', '.').'%';
    $status_labels = ['pending' => 'Belum Mulai', 'ongoing' => 'Berjalan', 'completed' => 'Selesai', 'cancelled' => 'Batal'];
    $column_count = 4 + ($show_prices ? 3 : 0) + ($show_progress ? 3 + ($progress_weight_column ? 1 : 0) : 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title><?php echo e($document_label); ?> - <?php echo e($project->project_code); ?></title>
    <style>
        @font-face { font-family: 'Tahoma'; font-weight: normal; src: url('<?php echo e(public_path('assets/fonts/tahoma.ttf')); ?>') format('truetype'); }
        @font-face { font-family: 'Tahoma'; font-weight: bold; src: url('<?php echo e(public_path('assets/fonts/tahomabd.ttf')); ?>') format('truetype'); }
        @page { margin: 18mm 12mm 16mm 12mm; }
        body { font-family: 'Tahoma', sans-serif; font-size: 8.5pt; color: #1d273b; }
        h1 { font-size: 13pt; margin: 0 0 2px; }
        .muted { color: #667382; }
        .meta { width: 100%; margin: 8px 0 10px; border-collapse: collapse; }
        .meta td { padding: 1px 4px 1px 0; vertical-align: top; }
        .sheet { width: 100%; border-collapse: collapse; }
        .sheet th, .sheet td { border: 0.6pt solid #9aa4b2; padding: 3px 4px; vertical-align: top; }
        .sheet thead th { background: #e9ecef; font-weight: bold; text-align: center; }
        .sheet thead { display: table-header-group; }
        .sheet tr { page-break-inside: avoid; }
        .group td { background: #f1f3f5; font-weight: bold; }
        .material td { color: #49566a; font-size: 8pt; }
        .total td { font-weight: bold; background: #e9ecef; }
        .right { text-align: right; }
        .center { text-align: center; }
        .nowrap { white-space: nowrap; }
        .small { font-size: 7.5pt; }
        .summary { width: 55%; border-collapse: collapse; margin-top: 12px; }
        .summary th, .summary td { border: 0.6pt solid #9aa4b2; padding: 3px 4px; }
        .summary th { background: #e9ecef; }
        .signatures { width: 100%; margin-top: 24px; }
        .signatures td { width: 50%; text-align: center; vertical-align: top; }
        .footer { position: fixed; bottom: -10mm; left: 0; right: 0; font-size: 7pt; color: #667382; }
    </style>
</head>
<body>
    <div class="footer">Dicetak <?php echo e($printed_at->timezone('Asia/Jakarta')->tanggal_waktu_indonesia()); ?> WIB · <?php echo e($document_label); ?> Revisi <?php echo e($document->revision_no); ?> · <?php echo e($project->project_code); ?></div>

    <h1><?php echo e(strtoupper($document_label)); ?></h1>
    <div class="muted">PT Adiluhung Saranasegara Indonesia</div>

    <table class="meta">
        <tr>
            <td style="width: 14%">Kode Proyek</td><td style="width: 36%">: <?php echo e($project->project_code); ?></td>
            <td style="width: 14%">No. Dokumen</td><td>: <?php echo e($document->document_number ?: '-'); ?></td>
        </tr>
        <tr>
            <td>Kapal</td><td>: <?php echo e($project->ship?->name ?? '-'); ?></td>
            <td>Revisi</td><td>: <?php echo e($document->revision_no); ?></td>
        </tr>
        <tr>
            <td>Disiapkan</td><td>: <?php echo e($document->prepared_by?->name ?? '-'); ?></td>
            <td>Disetujui</td><td>: <?php echo e($document->approved_at ? $document->approved_at->timezone('Asia/Jakarta')->tanggal_waktu_indonesia().' WIB' : '-'); ?></td>
        </tr>
    </table>

    <table class="sheet">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th>Uraian Pekerjaan</th>
                <th style="width: 8%">Volume</th>
                <th style="width: 6%">Sat.</th>
                <?php if($show_prices): ?>
                    <th style="width: 12%">Harga Satuan (IDR)</th>
                    <th style="width: 13%">Jumlah (IDR)</th>
                    <th style="width: 6%">Bobot</th>
                <?php endif; ?>
                <?php if($show_progress): ?>
                    <?php if($progress_weight_column): ?><th style="width: 7%">Bobot</th><?php endif; ?>
                    <th style="width: 7%">Progres</th>
                    <th style="width: 8%">Tertimbang</th>
                    <th style="width: 8%">Status</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $sheet['groups']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="group">
                    <td class="center"><?php echo e($group['roman']); ?></td>
                    <td colspan="<?php echo e($show_prices ? 4 : 3); ?>"><?php echo e(strtoupper($group['label'])); ?></td>
                    <?php if($show_prices): ?>
                        <td class="right nowrap"><?php echo e($money($group['total'])); ?></td>
                        <td class="right"><?php echo e($percent($group['weight'])); ?></td>
                    <?php endif; ?>
                    <?php if($show_progress): ?>
                        <?php if($progress_weight_column): ?><td class="right"><?php echo e($percent($group['weight'])); ?></td><?php endif; ?>
                        <td class="right"><?php echo e($percent($group['progress'])); ?></td>
                        <td class="right"><?php echo e($percent($group['weighted'])); ?></td>
                        <td></td>
                    <?php endif; ?>
                </tr>
                <?php $__currentLoopData = $group['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php ($job = $row['job']); ?>
                    <tr>
                        <td class="nowrap"><?php echo e($row['number']); ?></td>
                        <td style="padding-left: <?php echo e(4 + $row['depth'] * 12); ?>px">
                            <?php echo e($job->job_name); ?>

                            <?php if($row['calculation_text_customer']): ?><div class="small muted"><?php echo e($row['calculation_text_customer']); ?></div><?php endif; ?>
                            <?php if($job->notes): ?><div class="small muted"><?php echo e($job->notes); ?></div><?php endif; ?>
                        </td>
                        <td class="right"><?php echo e($qty($show_prices ? ($job->billed_quantity ?? $job->job_volume_estimated) : $job->job_volume_estimated)); ?></td>
                        <td class="center"><?php echo e($job->unit ?? '-'); ?></td>
                        <?php if($show_prices): ?>
                            <td class="right nowrap"><?php echo e($money($job->unit_price)); ?></td>
                            <td class="right nowrap"><?php echo e($money($job->est_price)); ?></td>
                            <td class="right"><?php echo e($percent($show_progress ? ($row['progress']['weight'] ?? null) : $job->job_weight_percent)); ?></td>
                        <?php endif; ?>
                        <?php if($show_progress): ?>
                            <?php if($progress_weight_column): ?><td class="right"><?php echo e($percent($row['progress']['weight'] ?? null)); ?></td><?php endif; ?>
                            <td class="right"><?php echo e($percent($row['progress']['actual'] ?? '0')); ?></td>
                            <td class="right"><?php echo e($percent($row['progress']['weighted'] ?? null)); ?></td>
                            <td class="center"><?php echo e($status_labels[$row['progress']['status'] ?? $job->status] ?? $job->status); ?></td>
                        <?php endif; ?>
                    </tr>
                    <?php $__currentLoopData = $row['materials']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $material_row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php ($material = $material_row['material']); ?>
                        <tr class="material">
                            <td></td>
                            <td style="padding-left: <?php echo e(16 + $row['depth'] * 12); ?>px">
                                <?php echo e($material_row['letter']); ?>. <?php echo e($material->material_name); ?><?php if($material->dimension): ?> · <?php echo e($material->dimension); ?><?php endif; ?>
                            </td>
                            <td class="right"><?php echo e($qty($show_prices ? ($material->billed_quantity ?? $material->volume) : $material->volume)); ?></td>
                            <td class="center"><?php echo e($material->unit ?? '-'); ?></td>
                            <?php if($show_prices): ?>
                                <td class="right nowrap"><?php echo e($money($material->unit_price)); ?></td>
                                <td class="right nowrap"><?php echo e($money($material->price)); ?></td>
                                <td></td>
                            <?php endif; ?>
                            <?php if($show_progress): ?><td colspan="<?php echo e($progress_weight_column ? 4 : 3); ?>"></td><?php endif; ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="<?php echo e($column_count); ?>" class="center muted">Belum ada pekerjaan.</td></tr>
            <?php endif; ?>
            <?php if($show_prices && count($sheet['groups']) > 0): ?>
                <tr class="total">
                    <td colspan="5" class="right">TOTAL</td>
                    <td class="right nowrap"><?php echo e($money($sheet['grand_total'])); ?></td>
                    <td colspan="<?php echo e($column_count - 6); ?>"></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if(($show_prices || $show_progress) && count($sheet['groups']) > 0): ?>
        <table class="summary">
            <thead>
                <tr>
                    <th style="width: 8%">No</th>
                    <th>Rekapitulasi Kelompok Pekerjaan</th>
                    <?php if($show_prices): ?><th>Jumlah (IDR)</th><?php endif; ?>
                    <?php if($show_prices || $show_progress): ?><th>Bobot</th><?php endif; ?>
                    <?php if($show_progress): ?><th>Progres</th><th>Tertimbang</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $sheet['groups']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td class="center"><?php echo e($group['roman']); ?></td>
                        <td><?php echo e($group['label']); ?></td>
                        <?php if($show_prices): ?><td class="right nowrap"><?php echo e($money($group['total'])); ?></td><?php endif; ?>
                        <td class="right"><?php echo e($percent($group['weight'])); ?></td>
                        <?php if($show_progress): ?><td class="right"><?php echo e($percent($group['progress'])); ?></td><td class="right"><?php echo e($percent($group['weighted'])); ?></td><?php endif; ?>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <tr class="total">
                    <td colspan="2" class="right">TOTAL</td>
                    <?php if($show_prices): ?><td class="right nowrap"><?php echo e($money($sheet['grand_total'])); ?></td><?php endif; ?>
                    <td class="right"><?php echo e(count($sheet['groups']) > 0 && collect($sheet['groups'])->contains(fn ($group) => $group['weight'] !== null) ? '100,00%' : '-'); ?></td>
                    <?php if($show_progress): ?><td class="right"><?php echo e($percent($sheet['progress'])); ?></td><td class="right"><?php echo e($percent($sheet['progress'])); ?></td><?php endif; ?>
                </tr>
            </tbody>
        </table>
    <?php endif; ?>

    <table class="signatures">
        <tr>
            <td>Disiapkan oleh,<br><br><br><br><strong><?php echo e($document->prepared_by?->name ?? '(....................)'); ?></strong></td>
            <td>Disetujui oleh,<br><br><br><br><strong><?php echo e($document->approved_by?->name ?? '(....................)'); ?></strong></td>
        </tr>
    </table>
</body>
</html>
<?php /**PATH /home/snowy/projects/assi-repair/resources/views/project/job-document/export.blade.php ENDPATH**/ ?>