<?php $__env->startSection('title', 'Ubah Karyawan'); ?>
<?php $__env->startSection('body_title', 'Ubah Karyawan'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('employee.show', $employee->id)); ?>" class="btn btn-outline-secondary">Kembali</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <form action="<?php echo e(route('employee.update', $employee->id)); ?>" method="POST" enctype="multipart/form-data" id="employee-form">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div class="row g-4">
            <!-- Left Column: Profile Picture -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-center align-items-center py-5">
                        <div class="mb-4">
                            <?php if($employee->profile_photo_path): ?>
                                <img id="photo-preview" src="<?php echo e(route('employee.photo', $employee->id)); ?>" alt="Foto Profil" class="avatar avatar-xl rounded-circle shadow-sm" style="width: 150px; height: 150px; object-fit: cover;">
                                <span class="avatar avatar-xl rounded-circle shadow-sm d-none" id="photo-preview-placeholder" style="width: 150px; height: 150px; background-color: #f8f9fa;">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-user text-muted" width="72" height="72" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <circle cx="12" cy="7" r="4" />
                                        <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                                    </svg>
                                </span>
                            <?php else: ?>
                                <img id="photo-preview" src="#" alt="Preview Foto Profil" class="avatar avatar-xl rounded-circle shadow-sm d-none" style="width: 150px; height: 150px; object-fit: cover;">
                                <span class="avatar avatar-xl rounded-circle shadow-sm" id="photo-preview-placeholder" style="width: 150px; height: 150px; background-color: #f8f9fa;">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-user text-muted" width="72" height="72" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <circle cx="12" cy="7" r="4" />
                                        <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                                    </svg>
                                </span>
                            <?php endif; ?>
                        </div>
                        <h4 class="mb-1">Foto Profil</h4>
                        <p class="text-muted small mb-4">Maksimal 10MB (JPG, JPEG, PNG, WEBP)</p>
                        
                        <div class="w-100 mb-2">
                            <label for="profile_photo" class="btn btn-outline-primary w-100">
                                Ubah Foto
                            </label>
                            <input class="form-control d-none" type="file" id="profile_photo" name="profile_photo" accept="image/*">
                        </div>
                        
                        <?php if($employee->profile_photo_path): ?>
                        <div class="w-100">
                            <label class="form-check form-switch cursor-pointer justify-content-center">
                                <input class="form-check-input" type="checkbox" name="remove_photo" value="1" id="remove_photo">
                                <span class="form-check-label text-danger">Hapus foto ini</span>
                            </label>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column: Details -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0">
                        <h4 class="card-title mb-0">Informasi Dasar</h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Lengkap</label>
                                <input class="form-control" name="name" value="<?php echo e(old('name', $employee->name)); ?>" placeholder="Contoh: Budi Santoso" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">NIK</label>
                                <input class="form-control" name="employee_id" id="employee_id" value="<?php echo e(old('employee_id', $employee->employee_id)); ?>" placeholder="Contoh: 123456789" required oninput="this.value = this.value.replace(/[^0-9]/g, '');" maxlength="9">
                                <div id="nik-feedback" class="form-text mt-2"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input class="form-control" type="email" name="email" value="<?php echo e(old('email', $employee->email)); ?>" placeholder="Contoh: budi@contoh.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" required>
                                    <option value="active" <?php if(old('status', $employee->status) === 'active'): echo 'selected'; endif; ?>>Aktif</option>
                                    <option value="inactive" <?php if(old('status', $employee->status) === 'inactive'): echo 'selected'; endif; ?>>Tidak Aktif</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jabatan</label>
                                <select class="form-select" name="position_id" required>
                                    <option value="">Pilih jabatan</option>
                                    <?php $__currentLoopData = $positions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $position): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($position->id); ?>" <?php if(old('position_id', $employee->position_id) == $position->id): echo 'selected'; endif; ?>><?php echo e($position->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Manajer Langsung</label>
                                <select class="form-select" name="manager_id">
                                    <option value="">Tidak ada</option>
                                    <?php $__currentLoopData = $managers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $manager): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($manager->id); ?>" <?php if(old('manager_id', $employee->direct_manager_employee_id) == $manager->id): echo 'selected'; endif; ?>><?php echo e($manager->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0">
                        <h4 class="card-title mb-0">Akun Akses</h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Kata Sandi Baru</label>
                                <input class="form-control" type="password" name="password" placeholder="Minimal 8 karakter (opsional)">
                                <div class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah kata sandi.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Konfirmasi Kata Sandi</label>
                                <input class="form-control" type="password" name="password_confirmation" placeholder="Ulangi kata sandi baru">
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent text-end border-top-0 pb-4">
                        <button class="btn btn-primary px-4" type="submit" id="btn-submit">Perbarui Karyawan</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
	<script>
		$(document).ready(function() {
            // Photo Preview Logic
            $('#profile_photo').change(function() {
                const file = this.files[0];
                if (file) {
                    let reader = new FileReader();
                    reader.onload = function(event) {
                        $('#photo-preview-placeholder').addClass('d-none');
                        $('#photo-preview').attr('src', event.target.result).removeClass('d-none');
                        // Uncheck remove photo if a new one is selected
                        $('#remove_photo').prop('checked', false);
                    }
                    reader.readAsDataURL(file);
                }
            });

            $('#remove_photo').change(function() {
                if ($(this).is(':checked')) {
                    $('#profile_photo').val('');
                    $('#photo-preview').addClass('d-none');
                    $('#photo-preview-placeholder').removeClass('d-none');
                } else {
                    <?php if($employee->profile_photo_path): ?>
                        $('#photo-preview').attr('src', '<?php echo e(route('employee.photo', $employee->id)); ?>').removeClass('d-none');
                        $('#photo-preview-placeholder').addClass('d-none');
                    <?php endif; ?>
                }
            });

            // NIK Validation Logic
            let nikTimeout = null;
            $('#employee_id').on('input', function() {
                clearTimeout(nikTimeout);
                const nik = $(this).val();
                const feedback = $('#nik-feedback');
                
                if (nik.length < 5) {
                    feedback.html('');
                    $(this).removeClass('is-invalid is-valid');
                    return;
                }

                feedback.html('<span class="spinner-border spinner-border-sm text-secondary me-2" role="status"></span> Memeriksa NIK...');
                
                nikTimeout = setTimeout(() => {
                    $.post('<?php echo e(route('employee.check-nik')); ?>', {
                        _token: '<?php echo e(csrf_token()); ?>',
                        employee_id: nik,
                        exclude_id: <?php echo e($employee->id); ?>

                    }).done(function(response) {
                        if (response.available) {
                            $('#employee_id').removeClass('is-invalid').addClass('is-valid');
                            feedback.html('<span class="text-success"><svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-check" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg> NIK tersedia</span>');
                        } else {
                            $('#employee_id').removeClass('is-valid').addClass('is-invalid');
                            feedback.html('<span class="text-danger"><svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-alert-circle" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9" /><line x1="12" y1="8" x2="12" y2="12" /><line x1="12" y1="16" x2="12.01" y2="16" /></svg> NIK sudah terdaftar</span>');
                        }
                    }).fail(function() {
                        feedback.html('<span class="text-muted">Gagal memeriksa NIK.</span>');
                    });
                }, 500);
            });

			$('#employee-form').validate({
				rules: {
					employee_id: {
						required: true,
						digits: true,
						minlength: 9,
						maxlength: 9
					},
					name: {
						required: true,
						minlength: 3,
						maxlength: 255
					},
					email: {
						email: true,
						maxlength: 255
					},
					password: {
						minlength: 8
					},
					password_confirmation: {
						equalTo: '[name="password"]'
					}
				},
				messages: {
                    employee_id: {
                        required: "Masukkan NIK karyawan.",
                        digits: "NIK harus berupa angka.",
                        minlength: "NIK harus 9 digit.",
                        maxlength: "NIK harus 9 digit."
                    },
                    name: {
                        required: "Masukkan nama karyawan.",
                        minlength: "Nama karyawan minimal 3 karakter.",
                        maxlength: "Nama karyawan maksimal 255 karakter."
                    },
                    email: {
                        email: "Masukkan email yang valid.",
                        maxlength: "Email maksimal 255 karakter."   
                    },
					password: {
						minlength: "Kata sandi minimal 8 karakter."
					},
					password_confirmation: {
						equalTo: "Konfirmasi kata sandi harus sama dengan kata sandi."
					}
				}
			});
		});
	</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views\employee\edit.blade.php ENDPATH**/ ?>