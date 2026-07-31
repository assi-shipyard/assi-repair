<?php $__env->startSection('title', 'Tambah Proyek'); ?>
<?php $__env->startSection('body_title', 'Tambah Proyek'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('project.index')); ?>" class="btn btn-outline-secondary">Kembali</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <form action="<?php echo e(route('project.store')); ?>" method="POST" class="card">
        <?php echo csrf_field(); ?>
        <div class="card-header">
            <div>
                <h3 class="card-title mb-1">Formulir Proyek</h3>
                <p class="text-muted mb-0">Pisahkan data kapal, jadwal, tim proyek, dan pihak owner surveyor.</p>
            </div>
        </div>
        <div class="card-body">
            <div class="card mb-3 shadow-none border">
                <div class="card-header py-3">
                    <div>
                        <h4 class="card-title mb-1">Data Proyek</h4>
                        <div class="text-muted">Pilih kapal, tipe proyek, dan penanggung jawab utama.</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Kapal</label>
                            <select class="form-select" name="ship_id" required>
                                <option value="">Pilih kapal</option>
                                <?php $__currentLoopData = $ships; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ship): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($ship->id); ?>" <?php if(old('ship_id') == $ship->id): echo 'selected'; endif; ?>><?php echo e($ship->name); ?> - <?php echo e($ship->company?->name ?? '-'); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipe Proyek</label>
                            <select class="form-select" name="project_type" required>
                                <option value="">Pilih tipe</option>
                                <?php $__currentLoopData = $projectTypeOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $projectTypeOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($projectTypeOption); ?>" <?php if(old('project_type') == $projectTypeOption): echo 'selected'; endif; ?>><?php echo e($projectTypeOption); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">PIMPRO</label>
                            <select class="form-select" name="project_leader_employee_id">
                                <option value="">Tidak ada</option>
                                <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($employee->id); ?>" <?php if(old('project_leader_employee_id') == $employee->id): echo 'selected'; endif; ?>><?php echo e($employee->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">PPC</label>
                            <select class="form-select" name="project_ppc_employee_id">
                                <option value="">Tidak ada</option>
                                <?php $__currentLoopData = $ppc_employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($employee->id); ?>" <?php if(old('project_ppc_employee_id') == $employee->id): echo 'selected'; endif; ?>><?php echo e($employee->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Divisi Pelaksana</label>
                            <select class="form-select" name="division_ids[]" multiple required>
                                <?php $__currentLoopData = $divisions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $division): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($division->id); ?>" <?php if(in_array($division->id, old('division_ids', []))): echo 'selected'; endif; ?>><?php echo e($division->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Manajer Divisi</label>
                            <select class="form-select" name="division_manager_employee_id">
                                <option value="">Tidak ada</option>
                                <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($employee->id); ?>" <?php if(old('division_manager_employee_id') == $employee->id): echo 'selected'; endif; ?>><?php echo e($employee->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3 shadow-none border">
                <div class="card-header py-3">
                    <div>
                        <h4 class="card-title mb-1">Jadwal dan Status</h4>
                        <div class="text-muted">Atur estimasi, realisasi, progres, dan status pekerjaan.</div>
                    </div>
                </div>
                <div class="card-body">
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

            <div class="card shadow-none border">
                <div class="card-header py-3">
                    <div>
                        <h4 class="card-title mb-1">Owner Surveyor</h4>
                        <div class="text-muted">Isi data kontak surveyor pemilik bila diperlukan.</div>
                    </div>
                </div>
                <div class="card-body">
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
        <div class="card-footer text-end"><button class="btn btn-primary" type="submit">Simpan</button></div>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views/project/create.blade.php ENDPATH**/ ?>