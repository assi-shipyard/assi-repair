<?php $__env->startSection('title', 'Form Permohonan Docking Space'); ?>
<?php $__env->startSection('body_title', 'Form Permohonan Docking Space'); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card">
        <div class="card-body">
            <form id="docking-request-form" method="POST" action="<?php echo e(route('docking-space-request.store')); ?>">
                <?php echo csrf_field(); ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Proyek</label>
                        <select class="form-select" name="project_id" required>
                            <option value="">Pilih Proyek</option>
                            <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($project->id); ?>" <?php if(old('project_id') == $project->id): echo 'selected'; endif; ?>>
                                    <?php echo e($project->project_code); ?> - <?php echo e($project->ship?->name ?? '-'); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Docking Space (Preferensi)</label>
                        <select class="form-select" name="requested_docking_space_id">
                            <option value="">Sistem Akan Evaluasi Otomatis</option>
                            <?php $__currentLoopData = $docking_spaces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $docking_space): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($docking_space->id); ?>" <?php if(old('requested_docking_space_id') == $docking_space->id): echo 'selected'; endif; ?>>
                                    <?php echo e($docking_space->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Jadwal Mulai Docking</label>
                        <input type="datetime-local" class="form-control" name="requested_start_at" value="<?php echo e(old('requested_start_at')); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Jadwal Selesai Docking (Estimasi)</label>
                        <input type="datetime-local" class="form-control" name="requested_end_at" value="<?php echo e(old('requested_end_at')); ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Catatan Permohonan</label>
                        <textarea class="form-control" name="request_notes" rows="3"><?php echo e(old('request_notes')); ?></textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Kirim Permohonan</button>
                    <a class="btn btn-outline-secondary" href="<?php echo e(route('docking-space-request.index')); ?>">Lihat Daftar Permohonan</a>
                </div>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        $(function () {
            $.validator.addMethod('greaterThanOrEqual', function (value, element, param) {
                if (!value) {
                    return true;
                }

                const comparedValue = $(param).val();
                if (!comparedValue) {
                    return true;
                }

                return new Date(value) >= new Date(comparedValue);
            });

            $('#docking-request-form').validate({
                errorClass: 'is-invalid',
                validClass: 'is-valid',
                errorElement: 'div',
                errorPlacement: function (error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('div').append(error);
                },
                highlight: function (element) {
                    $(element).addClass('is-invalid').removeClass('is-valid');
                },
                unhighlight: function (element) {
                    $(element).removeClass('is-invalid').addClass('is-valid');
                },
                rules: {
                    project_id: { required: true },
                    requested_start_at: { required: true },
                    requested_end_at: {
                        greaterThanOrEqual: '[name="requested_start_at"]',
                    },
                    request_notes: { maxlength: 2000 },
                },
                messages: {
                    project_id: { required: 'Proyek wajib dipilih.' },
                    requested_start_at: { required: 'Jadwal mulai docking wajib diisi.' },
                    requested_end_at: {
                        greaterThanOrEqual: 'Jadwal selesai docking tidak boleh lebih awal dari jadwal mulai.',
                    },
                    request_notes: { maxlength: 'Catatan permohonan maksimal 2000 karakter.' },
                },
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views/docking-space-request/create.blade.php ENDPATH**/ ?>