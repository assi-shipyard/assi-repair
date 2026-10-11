<?php $__env->startSection('title', 'Dashboard Docking - SIREKA ASSI'); ?>
<?php $__env->startSection('body_title', 'Dashboard Docking'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('docking-space-availability')); ?>" class="btn btn-outline-primary">Docking Schedule (Gantt)</a>
    <a href="<?php echo e(route('docking-space-request.create')); ?>" class="btn btn-primary">Buat Docking Request</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php
        $level_class = fn (int $percent): string => $percent >= 85 ? 'bg-danger' : ($percent >= 60 ? 'bg-warning' : 'bg-success');
        $waiting_engineering = (int) ($pending_counts['submitted'] ?? 0);
        $waiting_production = (int) ($pending_counts['engineering_approved'] ?? 0);
        $alert_count = $overdue_rows->count() + $awaiting_marketing_count + $stale_requests->count();
    ?>

    <div class="text-secondary mb-3">Ringkasan per <?php echo e($now->tanggal_waktu_indonesia()); ?></div>

    <div class="row row-cards mb-3">
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="subheader">Dok terisi</div>
                    <div class="h1 mb-1"><?php echo e($occupied_count); ?> <span class="text-secondary fs-3">/ <?php echo e($active_count); ?></span></div>
                    <div class="progress progress-sm"><div class="progress-bar <?php echo e($level_class($occupancy_percent)); ?>" style="width: <?php echo e($occupancy_percent); ?>%"></div></div>
                    <div class="text-secondary small mt-1"><?php echo e($free_count); ?> dok kosong saat ini</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="subheader">Utilisasi <?php echo e($window_days); ?> hari ke depan</div>
                    <div class="h1 mb-1"><?php echo e($average_utilization); ?>%</div>
                    <div class="progress progress-sm"><div class="progress-bar <?php echo e($level_class($average_utilization)); ?>" style="width: <?php echo e($average_utilization); ?>%"></div></div>
                    <div class="text-secondary small mt-1">Rata-rata dok aktif</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="subheader">Permohonan berjalan</div>
                    <div class="h1 mb-1"><?php echo e($waiting_engineering + $waiting_production + $awaiting_marketing_count); ?></div>
                    <div class="text-secondary small">Engineering <?php echo e($waiting_engineering); ?> &middot; Produksi <?php echo e($waiting_production); ?> &middot; Marketing <?php echo e($awaiting_marketing_count); ?></div>
                    <a href="<?php echo e(route('docking-space-request.index')); ?>" class="small">Lihat daftar</a>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="subheader">Kapal datang 7 hari</div>
                    <div class="h1 mb-1"><?php echo e($arrivals_7_days); ?></div>
                    <div class="text-secondary small"><?php echo e($upcoming_undockings->count()); ?> kapal dijadwalkan keluar dok dalam 7 hari</div>
                </div>
            </div>
        </div>
    </div>

    <?php if($alert_count > 0 || $inactive_spaces->isNotEmpty()): ?>
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Perlu perhatian <span class="badge bg-red-lt text-red ms-2"><?php echo e($alert_count + $inactive_spaces->count()); ?></span></h3>
            </div>
            <div class="list-group list-group-flush">
                <?php $__currentLoopData = $overdue_rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="list-group-item">
                        <span class="badge bg-red-lt text-red me-2">Terlambat keluar</span>
                        <strong><?php echo e($row['current']->ship?->name ?? '-'); ?></strong> di <?php echo e($row['space']->name); ?>:
                        estimasi keluar <?php echo e($row['current']->estimated_undock_at->tanggal_indonesia()); ?> (<?php echo e($row['current']->estimated_undock_at->diffInDays($now)); ?> hari lewat).
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php if($awaiting_marketing_count > 0): ?>
                    <div class="list-group-item">
                        <span class="badge bg-orange-lt text-orange me-2">Konfirmasi Marketing</span>
                        <?php echo e($awaiting_marketing_count); ?> permohonan menunggu konfirmasi customer atas saran Produksi.
                    </div>
                <?php endif; ?>
                <?php $__currentLoopData = $stale_requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="list-group-item">
                        <span class="badge bg-yellow-lt text-yellow me-2">Menunggu lama</span>
                        <strong><?php echo e($stale->ship?->name ?? '-'); ?></strong> belum diproses sejak <?php echo e($stale->updated_at->tanggal_indonesia()); ?>

                        (<?php echo e(['submitted' => 'Engineering', 'engineering_approved' => 'Produksi', 'awaiting_marketing' => 'Marketing'][$stale->request_status] ?? '-'); ?>).
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php $__currentLoopData = $inactive_spaces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="list-group-item">
                        <span class="badge bg-secondary-lt text-secondary me-2">Tidak aktif</span>
                        <?php echo e($row['space']->name); ?> berstatus <?php echo e($row['space']->status); ?> dan tidak dihitung dalam kapasitas.
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="row row-cards">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">Status docking space</h3></div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Dok</th>
                                <th>Status</th>
                                <th>Kapal saat ini</th>
                                <th>Berikutnya</th>
                                <th style="min-width: 9rem;">Utilisasi <?php echo e($window_days); ?> hari</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_0 = true; $__currentLoopData = $space_rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?>
                                <tr>
                                    <td class="fw-medium"><?php echo e($row['space']->name); ?><div class="text-secondary small"><?php echo e($row['space']->location ?? ''); ?></div></td>
                                    <td>
                                        <?php if($row['space']->status !== 'active'): ?>
                                            <span class="badge bg-secondary-lt text-secondary">Tidak aktif</span>
                                        <?php elseif($row['is_overdue']): ?>
                                            <span class="badge bg-red-lt text-red">Terisi - terlambat</span>
                                        <?php elseif($row['current']): ?>
                                            <span class="badge bg-blue-lt text-blue">Terisi</span>
                                        <?php else: ?>
                                            <span class="badge bg-green-lt text-green">Kosong</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($row['current']): ?>
                                            <div><?php echo e($row['current']->ship?->name ?? '-'); ?></div>
                                            <div class="text-secondary small">Keluar <?php echo e($row['current']->estimated_undock_at?->tanggal_indonesia() ?? 'belum ditentukan'); ?></div>
                                        <?php else: ?>
                                            <span class="text-secondary">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($row['next']): ?>
                                            <div><?php echo e($row['next']->ship?->name ?? '-'); ?></div>
                                            <div class="text-secondary small"><?php echo e($row['next']->docked_at->tanggal_indonesia()); ?></div>
                                        <?php else: ?>
                                            <span class="text-secondary">Belum ada jadwal</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress progress-sm flex-fill"><div class="progress-bar <?php echo e($level_class($row['utilization'])); ?>" style="width: <?php echo e($row['utilization']); ?>%"></div></div>
                                            <span class="small"><?php echo e($row['utilization']); ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?>
                                <tr><td colspan="5" class="text-center text-secondary py-4">Belum ada data docking space.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Jadwal masuk dok berikutnya</h3>
                    <div class="card-actions"><a href="<?php echo e(route('docking-space-availability')); ?>">Lihat Gantt</a></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead><tr><th>Kapal</th><th>Pemilik</th><th>Dok</th><th>Masuk</th><th>Keluar</th></tr></thead>
                        <tbody>
                            <?php $__empty_0 = true; $__currentLoopData = $upcoming; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?>
                                <tr>
                                    <td class="fw-medium"><?php echo e($item->ship?->name ?? '-'); ?></td>
                                    <td><?php echo e($item->ship?->company?->name ?? '-'); ?></td>
                                    <td><?php echo e($item->docking_space?->name ?? '-'); ?></td>
                                    <td><?php echo e($item->docked_at->tanggal_indonesia()); ?><div class="text-secondary small"><?php echo e($now->diffInDays($item->docked_at)); ?> hari lagi</div></td>
                                    <td><?php echo e($item->estimated_undock_at?->tanggal_indonesia() ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?>
                                <tr><td colspan="5" class="text-center text-secondary py-4">Tidak ada kapal terjadwal masuk dok dalam <?php echo e($window_days); ?> hari ke depan.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">Keluar dok 7 hari ke depan</h3></div>
                <div class="list-group list-group-flush">
                    <?php $__empty_0 = true; $__currentLoopData = $upcoming_undockings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?>
                        <div class="list-group-item">
                            <div class="fw-medium"><?php echo e($item->ship?->name ?? '-'); ?></div>
                            <div class="text-secondary small"><?php echo e($item->docking_space?->name ?? '-'); ?> &middot; <?php echo e($item->estimated_undock_at->tanggal_indonesia()); ?></div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?>
                        <div class="list-group-item text-secondary">Tidak ada kapal yang dijadwalkan keluar.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Reparasi di perairan (floating)</h3>
                    <div class="card-actions"><span class="badge bg-blue-lt text-blue"><?php echo e($floating_active->count()); ?></span></div>
                </div>
                <div class="list-group list-group-flush">
                    <?php $__empty_0 = true; $__currentLoopData = $floating_active; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?>
                        <div class="list-group-item">
                            <div class="fw-medium"><?php echo e($item->ship?->name ?? '-'); ?></div>
                            <div class="text-secondary small">Sejak <?php echo e($item->floating_started_at->tanggal_indonesia()); ?></div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?>
                        <div class="list-group-item text-secondary">Tidak ada kapal dalam reparasi floating.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/docking-dashboard.blade.php ENDPATH**/ ?>