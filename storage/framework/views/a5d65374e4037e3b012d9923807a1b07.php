<?php $__env->startSection('title', 'Tambah Karyawan'); ?>
<?php $__env->startSection('body_title', 'Tambah Karyawan'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('employee.index')); ?>" class="btn btn-outline-secondary">Kembali</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('partials.form-shell-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php
        $filled_employee_fields = collect([
            old('name'),
            old('employee_id'),
            old('email'),
            old('position_id'),
            old('manager_id'),
            old('password'),
        ])->filter()->count();
    ?>

    <form action="<?php echo e(route('employee.store')); ?>" method="POST" enctype="multipart/form-data" id="employee-form" class="form-shell">
        <?php echo csrf_field(); ?>
        <div class="form-layout">
            <div class="form-sidebar">
                <div class="form-info-card" style="--form-info-bg: var(--tblr-green-lt); --form-info-avatar-bg: var(--tblr-green);">
                    <div class="form-info-body">
                        <div class="form-info-header">
                            <span class="form-info-avatar"><i class="ti ti-user-plus"></i></span>
                            <div>
                                <h2 class="form-info-title">Tambah user</h2>
                                <p class="form-info-subtitle">Gabungkan identitas karyawan, struktur organisasi, dan akses akun dalam satu alur yang jelas.</p>
                            </div>
                        </div>
                        <div class="form-step-list">
                            <div class="form-step-item"><span class="form-step-badge">1</span><span>Masukkan identitas karyawan dan NIK 9 digit.</span></div>
                            <div class="form-step-item"><span class="form-step-badge">2</span><span>Pilih jabatan dan manajer langsung bila ada.</span></div>
                            <div class="form-step-item"><span class="form-step-badge">3</span><span>Atur kata sandi awal atau gunakan NIK sebagai default.</span></div>
                        </div>
                        <div class="form-kpi-grid">
                            <div class="form-kpi"><span class="form-kpi-label">Field Terisi</span><span class="form-kpi-value"><?php echo e($filled_employee_fields); ?></span></div>
                            <div class="form-kpi"><span class="form-kpi-label">NIK</span><span class="form-kpi-value">9</span></div>
                            <div class="form-kpi"><span class="form-kpi-label">Bagian</span><span class="form-kpi-value">2</span></div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header"><h3 class="card-title mb-0">Foto dan catatan</h3></div>
                    <div class="card-body">
                        <div class="form-preview-shell mb-4">
                            <span class="form-avatar-placeholder" id="photo-preview-placeholder">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-user text-muted" width="72" height="72" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <circle cx="12" cy="7" r="4" />
                                    <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                                </svg>
                            </span>
                            <img id="photo-preview" src="#" alt="Preview Foto Profil" class="form-avatar-frame d-none">
                            <div>
                                <h4 class="mb-1">Foto Profil</h4>
                                <div class="form-help-text">Maksimal 10MB dengan format JPG, JPEG, PNG, atau WEBP.</div>
                            </div>
                        </div>

                        <div class="w-100 mb-4">
                            <label for="profile_photo" class="btn btn-outline-primary w-100">Pilih Foto</label>
                            <input class="form-control d-none" type="file" id="profile_photo" name="profile_photo" accept="image/*">
                        </div>

                        <div class="form-note-list">
                            <div class="form-note-item"><div class="form-note-label">NIK sebagai identitas</div><div>NIK harus 9 digit dan menjadi kunci penting untuk akun karyawan.</div></div>
                            <div class="form-note-item"><div class="form-note-label">Jabatan dan manajer</div><div>Tentukan struktur organisasi sejak awal agar alur approval dan notifikasi lebih rapi.</div></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-main-card card shadow-sm">
                <div class="form-main-card-header"><div><h3 class="form-main-card-title">Form User Baru</h3><div class="form-main-card-copy">Lengkapi identitas user agar akun siap dipakai oleh admin dan modul organisasi.</div></div></div>
                <div class="form-main-card-body">
                    <div class="form-summary-alert mb-4"><div class="form-summary-top"><div><div class="form-summary-title">Ringkasan input</div><div class="form-summary-copy">Fokus utama ada pada NIK, jabatan, dan akun akses awal.</div></div><span class="badge bg-green-lt text-green">User setup</span></div></div>

                    <div class="form-surface mb-4">
                        <div class="form-main-card-header"><div><h3 class="form-main-card-title">Informasi Dasar</h3><div class="form-main-card-copy">Identitas karyawan dan struktur organisasinya.</div></div></div>
                        <div class="form-main-card-body">
                            <div class="row g-3">
                                <div class="col-md-12"><div class="form-group"><label class="form-label required" for="name">Nama Lengkap</label><input class="form-control" name="name" id="name" value="<?php echo e(old('name')); ?>" placeholder="Contoh: Budi Santoso" autocomplete="off"></div></div>
                                <div class="col-md-6"><div class="form-group"><label class="form-label required" for="employee_id">NIK</label><input class="form-control" name="employee_id" id="employee_id" value="<?php echo e(old('employee_id')); ?>" placeholder="Contoh: 123456789" autocomplete="off" oninput="this.value = this.value.replace(/[^0-9]/g, '');" maxlength="9"><div id="nik-feedback" class="form-text mt-2"></div></div></div>
                                <div class="col-md-6"><div class="form-group"><label class="form-label" for="email">Email (Opsional)</label><input class="form-control" type="email" name="email" id="email" value="<?php echo e(old('email')); ?>" placeholder="Contoh: budi@contoh.com" autocomplete="off"></div></div>
                                <div class="col-md-6"><div class="form-group"><label class="form-label required" for="position_id">Jabatan</label><select class="form-select dropdown-list" name="position_id" id="position_id"><option value="" selected disabled>Pilih jabatan</option><?php $__currentLoopData = $positions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $position): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($position->id); ?>" <?php if(old('position_id') == $position->id): echo 'selected'; endif; ?>><?php echo e($position->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div></div>
                                <div class="col-md-6"><div class="form-group"><label class="form-label" for="manager_id">Manajer Langsung</label><select class="form-select dropdown-list" name="manager_id" id="manager_id"><option value="">Tidak ada</option><?php $__currentLoopData = $managers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $manager): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($manager->id); ?>" <?php if(old('manager_id') == $manager->id): echo 'selected'; endif; ?>><?php echo e($manager->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-surface">
                        <div class="form-main-card-header"><div><h3 class="form-main-card-title">Akun Akses</h3><div class="form-main-card-copy">Atur kata sandi awal atau biarkan sistem menggunakan NIK sebagai default.</div></div></div>
                        <div class="form-main-card-body">
                            <div class="row g-3">
                                <div class="col-md-6"><div class="form-group"><label class="form-label" for="password">Kata Sandi</label><input class="form-control" type="password" name="password" id="password" placeholder="Minimal 8 karakter (opsional)" autocomplete="new-password"><div class="form-text text-muted">Bila dikosongkan, kata sandi bawaan akan menggunakan NIK karyawan.</div></div></div>
                                <div class="col-md-6"><div class="form-group"><label class="form-label" for="password_confirmation">Konfirmasi Kata Sandi</label><input class="form-control" type="password" name="password_confirmation" id="password_confirmation" placeholder="Ulangi kata sandi" autocomplete="new-password"></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions-card">
            <div class="form-actions-row">
                <div class="form-actions-copy">Pastikan NIK, jabatan, dan jalur manajer sudah benar sebelum akun dibuat.</div>
                <div class="form-actions-buttons">
                    <a href="<?php echo e(route('employee.index')); ?>" class="btn btn-outline-secondary">Batal</a>
                    <button class="btn btn-primary px-4" type="submit" id="btn-submit">Simpan Karyawan</button>
                </div>
            </div>
        </div>
    </form>
<?php $__env->stopSection(); ?>
                if (file) {
                    let reader = new FileReader();
                    reader.onload = function(event) {
                        $('#photo-preview-placeholder').addClass('d-none');
                        $('#photo-preview').attr('src', event.target.result).removeClass('d-none');
                    }
                    reader.readAsDataURL(file);
                } else {
                    $('#photo-preview').addClass('d-none').attr('src', '#');
                    $('#photo-preview-placeholder').removeClass('d-none');
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
                        employee_id: nik
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
                ignore: [],
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
                    position_id: {
                        required: true
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
                    position_id: {
                        required: 'Pilih jabatan karyawan.'
                    },
					password: {
						minlength: "Kata sandi minimal 8 karakter."
					},
					password_confirmation: {
						equalTo: "Konfirmasi kata sandi harus sama dengan kata sandi."
					}
				},
				errorElement: 'div',
                errorPlacement: function (error, element) {
                    error.addClass('invalid-feedback');
                    if (element.hasClass('select2-hidden-accessible')) {
                        error.insertAfter(element.next('.select2'));
                        return;
                    }

                    element.closest('.form-group').append(error);
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
		});
	</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views\employee\create.blade.php ENDPATH**/ ?>