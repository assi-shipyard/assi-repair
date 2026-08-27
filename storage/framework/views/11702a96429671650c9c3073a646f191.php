<?php $__env->startSection('title', 'Tambah Proyek'); ?>
<?php $__env->startSection('body_title', 'Tambah Proyek'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('project.index')); ?>" class="btn btn-outline-secondary">Kembali</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('partials.form-shell-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php
        $filled_project_fields = collect([
            old('ship_id'),
            old('project_type'),
            old('project_leader_employee_id'),
            old('project_ppc_employee_id'),
            old('division_manager_employee_id'),
            old('start_date_estimation'),
            old('end_date_estimation'),
            old('start_date_actual'),
            old('end_date_actual'),
            old('progress', 0),
            old('status', 'Not Started'),
            old('comment'),
            old('owner_surveyors.0.name'),
            old('owner_surveyors.0.company'),
        ])->filter(function ($value) {
            return $value !== null && $value !== '';
        })->count();
    ?>

    <form action="<?php echo e(route('project.store')); ?>" method="POST" class="form-shell" id="project-create-form">
        <?php echo csrf_field(); ?>
        <div class="form-layout">
            <div class="form-sidebar">
                <div class="form-info-card" style="--form-info-bg: var(--tblr-primary-lt); --form-info-avatar-bg: var(--tblr-primary);">
                    <div class="form-info-body">
                        <div class="form-info-header"><span class="form-info-avatar"><i class="ti ti-file-pencil"></i></span><div><h2 class="form-info-title">Buat proyek baru</h2><p class="form-info-subtitle">Gabungkan kapal, tim, jadwal, dan owner surveyor dengan pola yang mirip permohonan docking.</p></div></div>
                        <div class="form-step-list">
                            <div class="form-step-item"><span class="form-step-badge">1</span><span>Pilih kapal, tipe proyek, dan tim inti.</span></div>
                            <div class="form-step-item"><span class="form-step-badge">2</span><span>Atur jadwal dan status awal pekerjaan.</span></div>
                            <div class="form-step-item"><span class="form-step-badge">3</span><span>Tambahkan owner surveyor bila dibutuhkan oleh proyek.</span></div>
                        </div>
                        <div class="form-kpi-grid">
                            <div class="form-kpi"><span class="form-kpi-label">Field Terisi</span><span class="form-kpi-value"><?php echo e($filled_project_fields); ?></span></div>
                            <div class="form-kpi"><span class="form-kpi-label">Tim Inti</span><span class="form-kpi-value">4</span></div>
                            <div class="form-kpi"><span class="form-kpi-label">Bagian</span><span class="form-kpi-value">3</span></div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header"><h3 class="card-title mb-0">Informasi penting</h3></div>
                    <div class="card-body">
                        <div class="form-note-list">
                            <div class="form-note-item"><div class="form-note-label">Kapal dan divisi</div><div>Pilih kapal dan divisi pelaksana yang benar agar workflow dokumen dan docking tidak salah relasi.</div></div>
                            <div class="form-note-item"><div class="form-note-label">Jadwal</div><div>Isi estimasi minimal untuk membantu dashboard dan monitoring progres.</div></div>
                            <div class="form-note-item"><div class="form-note-label">Surveyor owner</div><div>Tambahkan kontak owner bila proyek membutuhkan koordinasi inspeksi eksternal.</div></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-main-card card shadow-sm">
                <div class="form-main-card-header"><div><h3 class="form-main-card-title">Form Proyek Baru</h3><div class="form-main-card-copy">Lengkapi data proyek agar tim dapat mulai bekerja dengan konteks kapal, jadwal, dan penanggung jawab yang jelas.</div></div></div>
                <div class="form-main-card-body">
                    <div class="form-summary-alert mb-4"><div class="form-summary-top"><div><div class="form-summary-title">Ringkasan form</div><div class="form-summary-copy">Fokus utama ada pada kapal, tipe proyek, tim inti, dan status awal pengerjaan.</div></div><span class="badge bg-primary-lt text-primary">Project setup</span></div></div>

                    <div class="form-surface mb-4">
                        <div class="form-main-card-header"><div><h3 class="form-main-card-title">Data Proyek</h3><div class="form-main-card-copy">Pilih kapal, tipe proyek, dan penanggung jawab utama.</div></div></div>
                        <div class="form-main-card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><div class="form-group"><label class="form-label">Kapal</label><select class="form-select dropdown-list" name="ship_id" required>
                                <option value="">Pilih kapal</option>
                                <?php $__currentLoopData = $ships; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ship): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($ship->id); ?>" <?php if(old('ship_id') == $ship->id): echo 'selected'; endif; ?>><?php echo e($ship->name); ?> - <?php echo e($ship->company?->name ?? '-'); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select></div></div>
                        <div class="col-md-6"><div class="form-group"><label class="form-label">Tipe Proyek</label><select class="form-select" name="project_type" required>
                                <option value="">Pilih tipe</option>
                                <?php $__currentLoopData = $projectTypeOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $projectTypeOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($projectTypeOption); ?>" <?php if(old('project_type') == $projectTypeOption): echo 'selected'; endif; ?>><?php echo e($projectTypeOption); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select></div></div>
                        <div class="col-md-6"><div class="form-group"><label class="form-label">PIMPRO</label><select class="form-select dropdown-list" name="project_leader_employee_id">
                                <option value="">Tidak ada</option>
                                <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($employee->id); ?>" <?php if(old('project_leader_employee_id') == $employee->id): echo 'selected'; endif; ?>><?php echo e($employee->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select></div></div>
                        <div class="col-md-6"><div class="form-group"><label class="form-label">PPC</label><select class="form-select dropdown-list" name="project_ppc_employee_id">
                                <option value="">Tidak ada</option>
                                <?php $__currentLoopData = $ppc_employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($employee->id); ?>" <?php if(old('project_ppc_employee_id') == $employee->id): echo 'selected'; endif; ?>><?php echo e($employee->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select></div></div>
                        <div class="col-md-6"><div class="form-group"><label class="form-label">Divisi Pelaksana</label><select class="form-select dropdown-list" name="division_ids[]" multiple required>
                                <?php $__currentLoopData = $divisions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $division): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($division->id); ?>" <?php if(in_array($division->id, old('division_ids', []))): echo 'selected'; endif; ?>><?php echo e($division->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select></div></div>
                        <div class="col-md-6"><div class="form-group"><label class="form-label">Manajer Divisi</label><select class="form-select dropdown-list" name="division_manager_employee_id">
                                <option value="">Tidak ada</option>
                                <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($employee->id); ?>" <?php if(old('division_manager_employee_id') == $employee->id): echo 'selected'; endif; ?>><?php echo e($employee->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select></div></div>
                    </div>
                        </div>
                    </div>

                    <div class="form-surface mb-4">
                        <div class="form-main-card-header"><div><h3 class="form-main-card-title">Jadwal dan Status</h3><div class="form-main-card-copy">Atur estimasi, realisasi, progres, dan status pekerjaan.</div></div></div>
                        <div class="form-main-card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Estimasi Mulai</label>
                            <input class="form-control" type="date" name="start_date_estimation" value="<?php echo e(old('start_date_estimation')); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Estimasi Selesai</label>
                            <input class="form-control" type="date" name="end_date_estimation" value="<?php echo e(old('end_date_estimation')); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Aktual Mulai</label>
                            <input class="form-control" type="date" name="start_date_actual" value="<?php echo e(old('start_date_actual')); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Aktual Selesai</label>
                            <input class="form-control" type="date" name="end_date_actual" value="<?php echo e(old('end_date_actual')); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Progress</label>
                            <input class="form-control" name="progress" value="<?php echo e(old('progress', 0)); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="Not Started" <?php if(old('status', 'Not Started') === 'Not Started'): echo 'selected'; endif; ?>>Not Started</option>
                                <option value="In Progress" <?php if(old('status') === 'In Progress'): echo 'selected'; endif; ?>>In Progress</option>
                                <option value="Completed" <?php if(old('status') === 'Completed'): echo 'selected'; endif; ?>>Completed</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Komentar</label>
                            <input class="form-control" name="comment" value="<?php echo e(old('comment')); ?>">
                        </div>
                    </div>
                        </div>
                    </div>

                    <div class="form-surface">
                        <div class="form-main-card-header"><div><h3 class="form-main-card-title">Owner Surveyor</h3><div class="form-main-card-copy">Isi data kontak surveyor pemilik bila diperlukan.</div></div></div>
                        <div class="form-main-card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Nama</label>
                            <input class="form-control" name="owner_surveyors[0][name]" value="<?php echo e(old('owner_surveyors.0.name')); ?>" placeholder="Nama">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Perusahaan</label>
                            <input class="form-control" name="owner_surveyors[0][company]" value="<?php echo e(old('owner_surveyors.0.company')); ?>" placeholder="Perusahaan">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Jabatan</label>
                            <input class="form-control" name="owner_surveyors[0][position]" value="<?php echo e(old('owner_surveyors.0.position')); ?>" placeholder="Jabatan">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Email</label>
                            <input class="form-control" name="owner_surveyors[0][email]" value="<?php echo e(old('owner_surveyors.0.email')); ?>" placeholder="Email">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Telepon</label>
                            <input class="form-control" name="owner_surveyors[0][phone]" value="<?php echo e(old('owner_surveyors.0.phone')); ?>" placeholder="Telepon">
                        </div>
                    </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions-card">
            <div class="form-actions-row">
                <div class="form-actions-copy">Simpan setelah kapal, tim inti, jadwal, dan status awal proyek sudah sesuai.</div>
                <div class="form-actions-buttons">
                    <a href="<?php echo e(route('project.index')); ?>" class="btn btn-outline-secondary">Batal</a>
                    <button class="btn btn-primary" type="submit">Simpan Proyek</button>
                </div>
            </div>
        </div>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/project/create.blade.php ENDPATH**/ ?>