<?php $__env->startSection('title', 'Jadwal Docking'); ?>
<?php $__env->startSection('body_title', 'Jadwal Docking'); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <style>
        .gantt-mini-table {
            font-size: 0.78rem;
        }

        .gantt-mini-table th,
        .gantt-mini-table td {
            white-space: nowrap;
            text-align: center;
            vertical-align: middle;
            padding: 0.35rem 0.4rem;
        }

        .gantt-mini-table .ship-col {
            min-width: 260px;
            text-align: left;
            position: sticky;
            left: 0;
            z-index: 2;
            background: #ffffff;
        }

        .gantt-mini-table .day-header {
            min-width: 36px;
        }

        .gantt-mini-table .day-cell {
            min-width: 36px;
            height: 28px;
        }

        .gantt-mini-table .day-cell-active-scheduled {
            background-color: #206bc4;
        }

        .gantt-mini-table .day-cell-active-occupied {
            background-color: #2fb344;
        }

        .gantt-mini-table .day-cell-active-undocked {
            background-color: #f59f00;
        }

        .gantt-mini-table .day-cell-empty {
            background-color: #f1f5f9;
        }

        .gantt-mini-table .total-days-col {
            min-width: 52px;
            font-weight: 600;
            background: #f8fafc;
        }

        .schedule-table-wrap {
            overflow-x: auto;
        }
    </style>

    <form class="card mb-3" method="GET" action="<?php echo e(route('docking-space-availability')); ?>">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4 col-lg-3">
                    <label class="form-label">Pilih Bulan</label>
                    <select name="month" class="form-select" onchange="this.form.submit()">
                        <?php $__currentLoopData = $month_options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $month_number => $month_name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($month_number); ?>" <?php if((int) $selected_month === (int) $month_number): echo 'selected'; endif; ?>>
                                <?php echo e($month_name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-md-4 col-lg-2">
                    <label class="form-label">Pilih Tahun</label>
                    <select name="year" class="form-select" onchange="this.form.submit()">
                        <?php $__currentLoopData = $year_options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year_option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($year_option); ?>" <?php if((int) $selected_year === (int) $year_option): echo 'selected'; endif; ?>>
                                <?php echo e($year_option); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-md-4 col-lg-2">
                    <button type="submit" class="btn btn-primary w-100">Tampilkan</button>
                </div>

                <div class="col-md-12 col-lg-5">
                    <div class="d-flex flex-wrap gap-3 justify-content-md-end">
                        <div><span class="badge" style="background: #206bc4">&nbsp;</span> <small class="text-secondary">Scheduled</small></div>
                        <div><span class="badge" style="background: #2fb344">&nbsp;</span> <small class="text-secondary">Occupied</small></div>
                        <div><span class="badge" style="background: #f59f00">&nbsp;</span> <small class="text-secondary">Undocked</small></div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="row row-cards">
        <?php $__currentLoopData = $schedule_cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title mb-0"><?php echo e($card['docking_space_name']); ?></h3>
                            <div class="text-secondary small"><?php echo e($card['location'] ?? '-'); ?></div>
                        </div>
                        <div class="d-flex gap-3">
                            <div class="text-center">
                                <div class="h3 mb-0"><?php echo e($card['current_occupied_count']); ?></div>
                                <small class="text-secondary">Aktif</small>
                            </div>
                            <div class="text-center">
                                <div class="h3 mb-0"><?php echo e($card['scheduled_count']); ?></div>
                                <small class="text-secondary">Terjadwal</small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="schedule-table-wrap">
                            <table class="table table-bordered table-sm mb-0 gantt-mini-table">
                                <thead>
                                    <tr>
                                        <th class="ship-col">Kapal Terjadwal</th>
                                        <?php $__currentLoopData = $day_numbers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day_number): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <th class="day-header"><?php echo e($day_number); ?></th>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <th class="total-days-col">Hari</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $card['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <?php
                                            $cell_class = match ((string) $row['occupancy_status']) {
                                                'scheduled' => 'day-cell-active-scheduled',
                                                'occupied' => 'day-cell-active-occupied',
                                                'undocked' => 'day-cell-active-undocked',
                                                default => 'day-cell-empty',
                                            };
                                        ?>
                                        <tr>
                                            <td class="ship-col">
                                                <div class="fw-semibold"><?php echo e($row['ship_name']); ?></div>
                                                <div class="text-secondary small"><?php echo e($row['project_code']); ?> | <?php echo e(strtoupper((string) $row['occupancy_status'])); ?></div>
                                            </td>

                                            <?php $__currentLoopData = $day_numbers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day_number): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php if(!empty($row['day_cells'][$day_number])): ?>
                                                    <td class="day-cell <?php echo e($cell_class); ?>"></td>
                                                <?php else: ?>
                                                    <td class="day-cell day-cell-empty"></td>
                                                <?php endif; ?>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                            <td class="total-days-col"><?php echo e($row['active_days_count']); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="<?php echo e(2 + $total_days_in_month); ?>" class="text-center text-secondary py-4">
                                                Tidak ada jadwal docking pada bulan dan tahun terpilih.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views\docking-space-availability\index.blade.php ENDPATH**/ ?>