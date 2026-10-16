<?php $__env->startSection('title', 'Daftar Permohonan Docking Space'); ?>
<?php $__env->startSection('body_title', 'Permohonan & Kunjungan Docking'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <div class="btn-list">
        <a href="<?php echo e(route('docking-space-request.rejected')); ?>" class="btn btn-outline-secondary"><i class="ti ti-ban me-1"></i>Request ditolak</a>
        <a href="<?php echo e(route('docking-space-request.create')); ?>" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Permohonan baru</a>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php
        $current_user = auth()->user();
        $can_operate = $current_user->hasRole('admin')
            || $current_user->can('docking.manage')
            || $current_user->can('project.manage')
            || $current_user->can('project.update');
        $can_approve_engineering = $current_user->hasRole('admin') || $current_user->can('approve-docking-engineering');
        $can_approve_production = $current_user->hasRole('admin') || $current_user->can('approve-docking-production');

        $lane_tabs = [
            'pengajuan' => ['label' => 'Pengajuan', 'icon' => 'ti-file-description', 'hint' => 'Menunggu pemeriksaan Engineering, Produksi, atau konfirmasi Marketing.'],
            'kedatangan' => ['label' => 'Menunggu kedatangan', 'icon' => 'ti-calendar-event', 'hint' => 'Disetujui dan slot dock sudah dipesan. Catat kedatangan saat kapal tiba.'],
            'galangan' => ['label' => 'Di galangan', 'icon' => 'ti-anchor', 'hint' => 'Kapal sudah tiba: menunggu dock, di dalam dock, pekerjaan atas air, atau menunggu keluar galangan.'],
            'selesai' => ['label' => 'Selesai & batal', 'icon' => 'ti-flag-check', 'hint' => 'Kapal sudah keluar galangan atau permohonan dibatalkan.'],
            'semua' => ['label' => 'Semua', 'icon' => 'ti-list', 'hint' => 'Seluruh permohonan kecuali yang ditolak.'],
        ];

        $stage_badges = [
            'submitted' => 'bg-blue-lt',
            'engineering_approved' => 'bg-azure-lt',
            'awaiting_marketing' => 'bg-orange-lt',
            'awaiting_arrival' => 'bg-cyan-lt',
            'arrived' => 'bg-indigo-lt',
            'docked' => 'bg-purple-lt',
            'floating' => 'bg-teal-lt',
            'ready_to_depart' => 'bg-lime-lt',
            'completed' => 'bg-success-lt',
            'cancelled' => 'bg-secondary-lt',
        ];
    ?>

    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="d-flex flex-wrap align-items-center gap-2 small text-secondary">
                <span class="fw-medium text-body me-1"><i class="ti ti-route me-1"></i>Alur kapal:</span>
                <span>1. Pengajuan</span><i class="ti ti-chevron-right"></i>
                <span>2. Persetujuan Engineering &amp; Produksi</span><i class="ti ti-chevron-right"></i>
                <span>3. Tiba di galangan</span><i class="ti ti-chevron-right"></i>
                <span>4. Masuk dock</span><i class="ti ti-chevron-right"></i>
                <span>5. Undocking / pekerjaan atas air</span><i class="ti ti-chevron-right"></i>
                <span>6. Keluar galangan <span class="text-muted">(proyek selesai + administrasi tuntas)</span></span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs flex-nowrap overflow-auto" role="tablist">
                <?php $__currentLoopData = $lane_tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lane_key => $lane_tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('docking-space-request.index', array_filter(['tab' => $lane_key, 'q' => $search]))); ?>"
                            class="nav-link text-nowrap <?php echo e($lane === $lane_key ? 'active' : ''); ?>" <?php if($lane === $lane_key): ?> aria-current="page" <?php endif; ?>>
                            <i class="ti <?php echo e($lane_tab['icon']); ?> me-1"></i><?php echo e($lane_tab['label']); ?>

                            <span class="badge <?php echo e($lane === $lane_key ? 'bg-primary text-primary-fg' : 'bg-secondary-lt'); ?> ms-2"><?php echo e($lane_counts[$lane_key]); ?></span>
                        </a>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
        <div class="card-body border-bottom py-3">
            <div class="row g-2 align-items-center">
                <div class="col-lg">
                    <div class="text-secondary small"><i class="ti ti-info-circle me-1"></i><?php echo e($lane_tabs[$lane]['hint']); ?></div>
                </div>
                <div class="col-lg-auto">
                    <form method="GET" action="<?php echo e(route('docking-space-request.index')); ?>" class="d-flex gap-2" role="search">
                        <input type="hidden" name="tab" value="<?php echo e($lane); ?>">
                        <div class="input-icon">
                            <span class="input-icon-addon"><i class="ti ti-search"></i></span>
                            <input type="search" name="q" value="<?php echo e($search); ?>" maxlength="100" class="form-control" placeholder="Cari kapal / kode proyek" aria-label="Cari kapal atau kode proyek">
                        </div>
                        <button class="btn btn-outline-primary" type="submit">Cari</button>
                        <?php if($search !== ''): ?>
                            <a href="<?php echo e(route('docking-space-request.index', ['tab' => $lane])); ?>" class="btn btn-link link-secondary px-2">Reset</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Kapal &amp; proyek</th>
                        <th>Jadwal docking</th>
                        <th>Docking space</th>
                        <th class="w-25">Tahap</th>
                        <th class="w-1 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $docking_requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $docking_request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $stage_key = $docking_request->request_status === 'approved' ? ($docking_request->visit_stage ?? 'awaiting_arrival') : $docking_request->request_status;
                            $step = $docking_request->progress_step();
                            $is_owner = $docking_request->is_managed_by($current_user);
                            $blockers = $departure_blockers[$docking_request->id] ?? [];
                            $detail_url = route('docking-space-request.show', $docking_request->unique_id);
                            $effective_dock = $docking_request->recommended_docking_space ?? $docking_request->requested_docking_space;

                            $next_action = match (true) {
                                $stage_key === 'submitted' && $can_approve_engineering => ['label' => 'Tinjau Engineering', 'icon' => 'ti-ruler-measure', 'url' => route('docking-approval.show', ['engineering', $docking_request->unique_id])],
                                $stage_key === 'engineering_approved' && $can_approve_production => ['label' => 'Tinjau Produksi', 'icon' => 'ti-tool', 'url' => route('docking-approval.show', ['production', $docking_request->unique_id])],
                                $stage_key === 'awaiting_marketing' && $is_owner => ['label' => 'Tanggapi rekomendasi', 'icon' => 'ti-message-reply', 'url' => $detail_url . '?action=marketing'],
                                $stage_key === 'awaiting_arrival' && $can_operate => ['label' => 'Catat kedatangan', 'icon' => 'ti-ship', 'url' => $detail_url . '?action=arrival'],
                                $stage_key === 'arrived' && $can_operate => ['label' => 'Masuk dock', 'icon' => 'ti-anchor', 'url' => $detail_url . '?action=dock'],
                                $stage_key === 'docked' && $can_operate => ['label' => 'Undocking', 'icon' => 'ti-arrow-bar-up', 'url' => $detail_url . '?action=undock'],
                                $stage_key === 'floating' && $can_operate => ['label' => 'Selesaikan atas air', 'icon' => 'ti-check', 'url' => $detail_url . '?action=floating'],
                                $stage_key === 'ready_to_depart' && $blockers === [] && $can_operate => ['label' => 'Keluar galangan', 'icon' => 'ti-door-exit', 'url' => $detail_url . '?action=depart'],
                                $docking_request->is_in_yard() && $docking_request->administration_completed_at === null && ($can_operate || $is_owner) && $stage_key === 'ready_to_depart' => ['label' => 'Konfirmasi administrasi', 'icon' => 'ti-file-check', 'url' => $detail_url . '?action=administration'],
                                default => null,
                            };
                        ?>
                        <tr>
                            <td>
                                <a href="<?php echo e($detail_url); ?>" class="fw-medium text-reset"><?php echo e($docking_request->ship?->name ?? '-'); ?></a>
                                <div class="text-secondary small"><?php echo e($docking_request->ship?->company?->name ?? '-'); ?></div>
                                <div class="mt-1">
                                    <?php if($docking_request->project): ?>
                                        <span class="badge bg-blue-lt"><?php echo e($docking_request->project->project_code); ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-lt">Belum ada proyek</span>
                                    <?php endif; ?>
                                    <?php if($docking_request->documents_count > 0): ?>
                                        <span class="badge bg-secondary-lt" title="Dokumen terlampir"><i class="ti ti-paperclip"></i> <?php echo e($docking_request->documents_count); ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div><?php echo e($docking_request->requested_start_at?->tanggal_waktu_indonesia() ?? '-'); ?></div>
                                <div class="text-secondary small">s/d <?php echo e($docking_request->requested_end_at?->tanggal_waktu_indonesia() ?? '-'); ?></div>
                                <?php if($docking_request->arrived_at): ?>
                                    <div class="small text-indigo mt-1"><i class="ti ti-ship me-1"></i>Tiba <?php echo e($docking_request->arrived_at->tanggal_waktu_indonesia()); ?></div>
                                <?php endif; ?>
                                <?php if($docking_request->departed_at): ?>
                                    <div class="small text-success"><i class="ti ti-door-exit me-1"></i>Keluar <?php echo e($docking_request->departed_at->tanggal_waktu_indonesia()); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-medium"><?php echo e($effective_dock?->name ?? 'Belum ditentukan'); ?></div>
                                <?php if($docking_request->recommended_docking_space_id && $docking_request->recommended_docking_space_id !== $docking_request->requested_docking_space_id): ?>
                                    <div class="text-secondary small">Rekomendasi Engineering</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?php echo e($stage_badges[$stage_key] ?? 'bg-secondary-lt'); ?>"><?php echo e($docking_request->status_label()); ?></span>
                                <?php if($step > 0): ?>
                                    <div class="d-flex align-items-center gap-2 mt-2">
                                        <div class="progress progress-sm flex-fill" role="progressbar" aria-label="Progres kunjungan" aria-valuenow="<?php echo e($step); ?>" aria-valuemin="0" aria-valuemax="7">
                                            <div class="progress-bar <?php echo e($step === 7 ? 'bg-success' : ''); ?>" style="width: <?php echo e(round($step / 7 * 100)); ?>%"></div>
                                        </div>
                                        <span class="text-secondary small text-nowrap"><?php echo e($step); ?>/7</span>
                                    </div>
                                <?php endif; ?>
                                <?php if($stage_key === 'awaiting_marketing'): ?>
                                    <div class="small text-orange mt-1">Produksi menyarankan <?php echo e($docking_request->production_recommendation === 'postpone' ? 'penundaan' : 'pembatalan'); ?></div>
                                <?php elseif($stage_key === 'ready_to_depart' && $blockers !== []): ?>
                                    <div class="small text-secondary mt-1"><i class="ti ti-lock me-1"></i><?php echo e($blockers[0]); ?><?php if(count($blockers) > 1): ?> (+<?php echo e(count($blockers) - 1); ?>)<?php endif; ?></div>
                                <?php elseif($docking_request->is_in_yard() && $docking_request->administration_completed_at): ?>
                                    <div class="small text-success mt-1"><i class="ti ti-file-check me-1"></i>Administrasi selesai</div>
                                <?php elseif($stage_key === 'cancelled' && $docking_request->cancellation_reason): ?>
                                    <div class="small text-secondary mt-1 text-truncate" style="max-width: 16rem" title="<?php echo e($docking_request->cancellation_reason); ?>">Alasan: <?php echo e($docking_request->cancellation_reason); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-list flex-nowrap justify-content-end">
                                    <?php if($next_action): ?>
                                        <a href="<?php echo e($next_action['url']); ?>" class="btn btn-sm btn-primary text-nowrap"><i class="ti <?php echo e($next_action['icon']); ?> me-1"></i><?php echo e($next_action['label']); ?></a>
                                    <?php else: ?>
                                        <a href="<?php echo e($detail_url); ?>" class="btn btn-sm btn-outline-secondary text-nowrap">Detail</a>
                                    <?php endif; ?>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-icon btn-ghost-secondary" type="button" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false" aria-label="Aksi lainnya">
                                            <i class="ti ti-dots-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item" href="<?php echo e($detail_url); ?>"><i class="ti ti-eye me-2"></i>Lihat detail</a>
                                            <?php if($docking_request->is_before_docking() && $docking_request->has_scheduled_slot && $is_owner): ?>
                                                <a class="dropdown-item" href="<?php echo e(route('docking-space-request.postpone', $docking_request->unique_id)); ?>"><i class="ti ti-calendar-time me-2"></i>Tunda jadwal docking</a>
                                            <?php endif; ?>
                                            <?php if($docking_request->is_cancellable() && ! $docking_request->has_started_docking && $is_owner): ?>
                                                <div class="dropdown-divider"></div>
                                                <?php echo $__env->make('docking-space-request.partials.cancel-trigger', ['trigger_class' => 'dropdown-item text-danger', 'trigger_label' => 'Batalkan permohonan'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty py-4">
                                    <div class="empty-icon"><i class="ti <?php echo e($lane_tabs[$lane]['icon']); ?> fs-1"></i></div>
                                    <p class="empty-title"><?php echo e($search !== '' ? 'Tidak ada data yang cocok' : 'Belum ada data'); ?></p>
                                    <p class="empty-subtitle text-secondary"><?php echo e($search !== '' ? 'Coba kata kunci lain atau reset pencarian.' : 'Belum ada permohonan pada tahap ini.'); ?></p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($docking_requests->hasPages()): ?>
            <div class="card-footer d-flex align-items-center">
                <p class="m-0 text-secondary small">Menampilkan <?php echo e($docking_requests->firstItem()); ?>–<?php echo e($docking_requests->lastItem()); ?> dari <?php echo e($docking_requests->total()); ?> data</p>
                <div class="ms-auto"><?php echo e($docking_requests->links()); ?></div>
            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('modal'); ?>
    <?php echo $__env->make('docking-space-request.partials.cancel-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <?php echo $__env->make('docking-space-request.partials.cancel-modal-script', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/docking-space-request/index.blade.php ENDPATH**/ ?>