<?php $__env->startSection('title', 'Tambah Proyek'); ?>
<?php $__env->startSection('body_title', 'Tambah Proyek'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('project.index')); ?>" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i>Kembali
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <form action="<?php echo e(route('project.store')); ?>" method="POST" id="project-create-form" novalidate>
        <?php echo csrf_field(); ?>

        <div class="text-secondary mb-3">
            <i class="ti ti-info-circle me-1"></i>Kode proyek dibuat otomatis saat disimpan berdasarkan kapal, tipe, dan tanggal estimasi mulai.
        </div>

        <div class="row row-cards">
            <div class="col-12 col-xxl-7">
                <div class="card project-form-card h-100">
                    <div class="card-header">
                        <h3 class="card-title"><span class="project-step">1</span>Data Proyek</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label required" for="ship_id">Kapal</label>
                                <select class="form-select dropdown-list" id="ship_id" name="ship_id" required>
                                    <option value="">Pilih kapal</option>
                                    <?php $__currentLoopData = $ships; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ship): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($ship->id); ?>" <?php if(old('ship_id') == $ship->id): echo 'selected'; endif; ?>><?php echo e($ship->name); ?> - <?php echo e($ship->company?->name ?? '-'); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['ship_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required" for="project_type">Tipe Proyek</label>
                                <select class="form-select" id="project_type" name="project_type" required>
                                    <option value="">Pilih tipe</option>
                                    <?php $__currentLoopData = $projectTypeOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project_type_option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($project_type_option); ?>" <?php if(old('project_type') == $project_type_option): echo 'selected'; endif; ?>><?php echo e($project_type_option); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['project_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-12">
                                <div class="subheader mb-0">Tim Pelaksana</div>
                                <div class="form-hint">Pilih minimal salah satu dari PIMPRO, PPC, atau Manajer Divisi.</div>
                            </div>
                            <div class="col-md-6 col-lg-3 col-xxl-6">
                                <label class="form-label" for="project_leader_employee_id">PIMPRO</label>
                                <select class="form-select dropdown-list" id="project_leader_employee_id" name="project_leader_employee_id">
                                    <option value="">Tidak ada</option>
                                    <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($employee->id); ?>" <?php if(old('project_leader_employee_id') == $employee->id): echo 'selected'; endif; ?>><?php echo e($employee->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['project_leader_employee_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-6 col-lg-3 col-xxl-6">
                                <label class="form-label" for="project_ppc_employee_id">PPC</label>
                                <select class="form-select dropdown-list" id="project_ppc_employee_id" name="project_ppc_employee_id">
                                    <option value="">Tidak ada</option>
                                    <?php $__currentLoopData = $ppc_employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($employee->id); ?>" <?php if(old('project_ppc_employee_id') == $employee->id): echo 'selected'; endif; ?>><?php echo e($employee->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['project_ppc_employee_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-6 col-lg-3 col-xxl-6">
                                <label class="form-label required" for="division_ids">Divisi Pelaksana</label>
                                <select class="form-select dropdown-list" id="division_ids" name="division_ids[]" multiple required>
                                    <?php $__currentLoopData = $divisions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $division): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($division->id); ?>" <?php if(in_array($division->id, old('division_ids', []))): echo 'selected'; endif; ?>><?php echo e($division->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['division_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-6 col-lg-3 col-xxl-6">
                                <label class="form-label" for="division_manager_employee_id">Manajer Divisi</label>
                                <select class="form-select dropdown-list" id="division_manager_employee_id" name="division_manager_employee_id">
                                    <option value="">Tidak ada</option>
                                    <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($employee->id); ?>" <?php if(old('division_manager_employee_id') == $employee->id): echo 'selected'; endif; ?>><?php echo e($employee->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['division_manager_employee_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xxl-5">
                <div class="card project-form-card h-100">
                    <div class="card-header">
                        <h3 class="card-title"><span class="project-step">2</span>Jadwal dan Status</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6 col-md-4 col-lg-2 col-xxl-6">
                                <label class="form-label" for="start_date_estimation">Estimasi Mulai</label>
                                <input class="form-control" type="date" id="start_date_estimation" name="start_date_estimation" value="<?php echo e(old('start_date_estimation')); ?>">
                                <?php $__errorArgs = ['start_date_estimation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-6 col-md-4 col-lg-2 col-xxl-6">
                                <label class="form-label" for="end_date_estimation">Estimasi Selesai</label>
                                <input class="form-control" type="date" id="end_date_estimation" name="end_date_estimation" value="<?php echo e(old('end_date_estimation')); ?>">
                                <?php $__errorArgs = ['end_date_estimation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-6 col-md-4 col-lg-2 col-xxl-6">
                                <label class="form-label" for="start_date_actual">Aktual Mulai</label>
                                <input class="form-control" type="date" id="start_date_actual" name="start_date_actual" value="<?php echo e(old('start_date_actual')); ?>">
                                <?php $__errorArgs = ['start_date_actual'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-6 col-md-4 col-lg-2 col-xxl-6">
                                <label class="form-label" for="end_date_actual">Aktual Selesai</label>
                                <input class="form-control" type="date" id="end_date_actual" name="end_date_actual" value="<?php echo e(old('end_date_actual')); ?>">
                                <?php $__errorArgs = ['end_date_actual'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-6 col-md-4 col-lg-2 col-xxl-6">
                                <label class="form-label" for="progress">Progres</label>
                                <div class="input-group">
                                    <input class="form-control" type="number" min="0" max="100" step="0.01" id="progress" name="progress" value="<?php echo e(old('progress', 0)); ?>">
                                    <span class="input-group-text">%</span>
                                </div>
                                <?php $__errorArgs = ['progress'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-6 col-md-4 col-lg-2 col-xxl-6">
                                <label class="form-label" for="status">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="Not Started" <?php if(old('status', 'Not Started') === 'Not Started'): echo 'selected'; endif; ?>>Not Started</option>
                                    <option value="In Progress" <?php if(old('status') === 'In Progress'): echo 'selected'; endif; ?>>In Progress</option>
                                    <option value="Completed" <?php if(old('status') === 'Completed'): echo 'selected'; endif; ?>>Completed</option>
                                </select>
                                <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="comment">Komentar</label>
                                <textarea class="form-control" id="comment" name="comment" rows="2" placeholder="Catatan awal proyek (opsional)"><?php echo e(old('comment')); ?></textarea>
                                <?php $__errorArgs = ['comment'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card project-form-card">
                    <div class="card-header">
                        <h3 class="card-title"><span class="project-step">3</span>Surveyor Pemilik Kapal</h3>
                        <div class="card-actions"><span class="badge bg-secondary-lt text-secondary">Opsional</span></div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label" for="owner_surveyor_name">Nama</label>
                                <input class="form-control" id="owner_surveyor_name" name="owner_surveyors[0][name]" value="<?php echo e(old('owner_surveyors.0.name')); ?>" placeholder="Nama surveyor">
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label" for="owner_surveyor_company">Perusahaan</label>
                                <input class="form-control" id="owner_surveyor_company" name="owner_surveyors[0][company]" value="<?php echo e(old('owner_surveyors.0.company')); ?>" placeholder="Perusahaan">
                            </div>
                            <div class="col-md-4 col-lg-2">
                                <label class="form-label" for="owner_surveyor_position">Jabatan</label>
                                <input class="form-control" id="owner_surveyor_position" name="owner_surveyors[0][position]" value="<?php echo e(old('owner_surveyors.0.position')); ?>" placeholder="Jabatan">
                            </div>
                            <div class="col-md-4 col-lg-2">
                                <label class="form-label" for="owner_surveyor_email">Email</label>
                                <input class="form-control" type="email" id="owner_surveyor_email" name="owner_surveyors[0][email]" value="<?php echo e(old('owner_surveyors.0.email')); ?>" placeholder="Email">
                                <?php $__errorArgs = ['owner_surveyors.0.email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-4 col-lg-2">
                                <label class="form-label" for="owner_surveyor_phone">Telepon</label>
                                <input class="form-control" id="owner_surveyor_phone" name="owner_surveyors[0][phone]" value="<?php echo e(old('owner_surveyors.0.phone')); ?>" placeholder="Telepon">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="project-form-actions d-flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="text-secondary small d-none d-md-inline">Periksa kapal, tim, dan jadwal sebelum menyimpan.</span>
            <div class="d-flex gap-2 ms-md-auto">
                <a href="<?php echo e(route('project.index')); ?>" class="btn btn-outline-secondary">Batal</a>
                <button class="btn btn-primary" type="submit"><i class="ti ti-device-floppy me-1"></i>Simpan Proyek</button>
            </div>
        </div>
    </form>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        #project-create-form .invalid-feedback { display: block; }

        .project-form-card .form-label { font-weight: 600; margin-bottom: .25rem; }

        .project-step {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.5rem;
            height: 1.5rem;
            margin-right: .6rem;
            border-radius: 50%;
            font-size: .75rem;
            font-weight: 700;
            color: var(--tblr-primary);
            background: var(--tblr-primary-lt);
        }

        .project-form-actions {
            position: sticky;
            bottom: 0;
            z-index: 5;
            margin-top: 1rem;
            padding: .75rem 1rem;
            background: var(--tblr-bg-surface);
            border: 1px solid var(--tblr-border-color);
            border-radius: var(--tblr-border-radius);
        }

        .select2-container--bootstrap-5 .select2-selection.is-invalid {
            border-color: var(--tblr-danger);
        }

        @media (max-height: 800px) {
            .project-form-card .card-body { padding: 1rem; }
            .project-form-actions { padding: .5rem .75rem; margin-top: .5rem; }
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        $(document).ready(function () {
            $.validator.addMethod('on_or_after', function (value, element, start_selector) {
                const start_value = $(start_selector).val();

                return !value || !start_value || value >= start_value;
            });

            $('#project-create-form').validate({
                ignore: [],
                rules: {
                    ship_id: { required: true },
                    project_type: { required: true },
                    'division_ids[]': { required: true },
                    end_date_estimation: { on_or_after: '#start_date_estimation' },
                    end_date_actual: { on_or_after: '#start_date_actual' },
                    progress: { number: true, min: 0, max: 100 },
                    'owner_surveyors[0][email]': { email: true }
                },
                messages: {
                    ship_id: { required: 'Pilih kapal.' },
                    project_type: { required: 'Pilih tipe proyek.' },
                    'division_ids[]': { required: 'Pilih minimal satu divisi pelaksana.' },
                    end_date_estimation: { on_or_after: 'Tanggal selesai harus sama atau setelah tanggal mulai.' },
                    end_date_actual: { on_or_after: 'Tanggal selesai harus sama atau setelah tanggal mulai.' },
                    progress: {
                        number: 'Progres harus berupa angka.',
                        min: 'Progres minimal 0%.',
                        max: 'Progres maksimal 100%.'
                    },
                    'owner_surveyors[0][email]': { email: 'Masukkan email yang valid.' }
                },
                errorElement: 'div',
                errorPlacement: function (error, element) {
                    error.addClass('invalid-feedback');

                    if (element.hasClass('select2-hidden-accessible')) {
                        error.insertAfter(element.next('.select2'));
                        return;
                    }

                    if (element.parent().hasClass('input-group')) {
                        error.insertAfter(element.parent());
                        return;
                    }

                    error.insertAfter(element);
                },
                highlight: function (element) {
                    const $element = $(element);

                    if ($element.hasClass('select2-hidden-accessible')) {
                        $element.next('.select2').find('.select2-selection').addClass('is-invalid');
                        return;
                    }

                    $element.addClass('is-invalid');
                },
                unhighlight: function (element) {
                    const $element = $(element);

                    if ($element.hasClass('select2-hidden-accessible')) {
                        $element.next('.select2').find('.select2-selection').removeClass('is-invalid');
                        return;
                    }

                    $element.removeClass('is-invalid');
                }
            });

            $('.dropdown-list, #start_date_estimation, #start_date_actual').on('change', function () {
                $(this).valid();
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/project/create.blade.php ENDPATH**/ ?>