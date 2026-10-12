<?php
    $existing_photo_url = $employee?->profile_photo_path ? route('employee.photo', $employee->unique_id) : null;
?>

    <form action="<?php echo e($is_edit ? route('employee.update', $employee->unique_id) : route('employee.store')); ?>" method="POST" enctype="multipart/form-data" id="employee-form" novalidate>
        <?php echo csrf_field(); ?>
        <?php if($is_edit): ?>
            <?php echo method_field('PUT'); ?>
        <?php endif; ?>
        <div class="row row-cards">
            <div class="col-12 col-lg-8">
                <div class="card mb-3">
                    <div class="card-header"><h3 class="card-title"><i class="ti ti-id me-2" aria-hidden="true"></i>Identitas Karyawan</h3></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 form-group">
                                <label class="form-label required" for="name">Nama Lengkap</label>
                                <input class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="name" id="name" value="<?php echo e(old('name', $employee?->name)); ?>" maxlength="255" autocomplete="name" required>
                                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label required" for="employee_id">NIK</label>
                                <input class="form-control <?php $__errorArgs = ['employee_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="employee_id" id="employee_id" value="<?php echo e(old('employee_id', $employee?->employee_id)); ?>" inputmode="numeric" pattern="[0-9]{9}" maxlength="9" aria-describedby="nik-help nik-feedback" required>
                                <div class="form-text" id="nik-help">Nomor induk karyawan terdiri dari tepat 9 digit, termasuk angka nol di awal.</div>
                                <div id="nik-feedback" class="form-text" aria-live="polite"></div>
                                <?php $__errorArgs = ['employee_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label" for="email">Email <span class="text-secondary">(opsional)</span></label>
                                <input class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" type="email" name="email" id="email" value="<?php echo e(old('email', $employee?->email)); ?>" maxlength="255" autocomplete="email">
                                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label required" for="position_id">Jabatan</label>
                                <select class="form-select dropdown-list <?php $__errorArgs = ['position_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="position_id" id="position_id" required>
                                    <option value="">Pilih jabatan</option>
                                    <?php $__currentLoopData = $positions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $position): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($position->id); ?>" <?php if(old('position_id', $employee?->position_id) == $position->id): echo 'selected'; endif; ?>><?php echo e($position->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['position_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label" for="manager_id">Manajer Langsung <span class="text-secondary">(opsional)</span></label>
                                <select class="form-select dropdown-list <?php $__errorArgs = ['manager_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="manager_id" id="manager_id">
                                    <option value="">Tidak ada</option>
                                    <?php $__currentLoopData = $managers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $manager): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($manager->id); ?>" <?php if(old('manager_id', $employee?->direct_manager_employee_id) == $manager->id): echo 'selected'; endif; ?>><?php echo e($manager->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['manager_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <?php if($is_edit): ?>
                                <div class="col-12 form-group">
                                    <label class="form-label required" for="status">Status Karyawan</label>
                                    <select class="form-select <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="status" id="status" required>
                                        <option value="active" <?php if(old('status', $employee->status) === 'active'): echo 'selected'; endif; ?>>Aktif</option>
                                        <option value="inactive" <?php if(old('status', $employee->status) === 'inactive'): echo 'selected'; endif; ?>>Tidak Aktif</option>
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
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h3 class="card-title"><i class="ti ti-lock me-2" aria-hidden="true"></i>Akses Akun</h3></div>
                    <div class="card-body">
                        <?php if($is_edit): ?>
                            <div class="alert alert-info" role="note">Biarkan kata sandi kosong untuk mempertahankan kata sandi akun yang sudah ada. Isi kata sandi baru dan konfirmasinya hanya jika diperlukan.</div>
                        <?php else: ?>
                            <div class="alert alert-warning" role="note">NIK digunakan untuk masuk ke akun. Jika kata sandi dikosongkan, sistem menggunakan NIK sebagai kata sandi awal. Disarankan mengisi kata sandi khusus dan meminta pengguna menggantinya setelah masuk.</div>
                        <?php endif; ?>
                        <div class="row g-3">
                            <div class="col-md-6 form-group">
                                <label class="form-label" for="password"><?php echo e($is_edit ? 'Kata Sandi Baru' : 'Kata Sandi Awal'); ?></label>
                                <input class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" type="password" name="password" id="password" autocomplete="new-password" aria-describedby="password-help">
                                <div class="form-text" id="password-help">Minimal 8 karakter bila diisi.</div>
                                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label" for="password_confirmation">Konfirmasi Kata Sandi</label>
                                <input class="form-control <?php $__errorArgs = ['password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password">
                                <?php $__errorArgs = ['password_confirmation'];
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

            <div class="col-12 col-lg-4">
                <div class="card mb-3">
                    <div class="card-header"><h3 class="card-title">Foto Profil</h3><span class="ms-auto text-secondary small">Opsional</span></div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <span class="avatar avatar-xl bg-primary-lt text-primary <?php echo e($existing_photo_url && !old('remove_photo') ? 'd-none' : ''); ?>" id="photo-preview-placeholder"><i class="ti ti-user fs-1" aria-hidden="true"></i></span>
                            <img id="photo-preview" <?php if($existing_photo_url): ?> src="<?php echo e($existing_photo_url); ?>" <?php endif; ?> alt="Pratinjau foto profil" class="avatar avatar-xl employee-photo-preview <?php echo e(!$existing_photo_url || old('remove_photo') ? 'd-none' : ''); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="profile_photo">Unggah Foto</label>
                            <input class="form-control <?php $__errorArgs = ['profile_photo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" type="file" id="profile_photo" name="profile_photo" accept="image/jpeg,image/png,image/webp" aria-describedby="photo-help photo-feedback">
                            <div class="form-text" id="photo-help">JPG, JPEG, PNG, atau WEBP, maksimal 10 MB. Foto disimpan secara privat.</div>
                            <div id="photo-feedback" class="text-danger small" role="alert"></div>
                            <?php $__errorArgs = ['profile_photo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <?php if($existing_photo_url): ?>
                            <div class="form-check form-switch mt-3">
                                <input class="form-check-input" type="checkbox" name="remove_photo" value="1" id="remove_photo" <?php if(old('remove_photo')): echo 'checked'; endif; ?>>
                                <label class="form-check-label" for="remove_photo">Hapus foto profil saat menyimpan</label>
                                <?php $__errorArgs = ['remove_photo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">Sebelum Menyimpan</h3>
                        <ul class="text-secondary ps-3 mb-0">
                            <li class="mb-2">Pastikan NIK sesuai data karyawan dan tidak digunakan karyawan lain.</li>
                            <li class="mb-2">Pilih jabatan yang sesuai dengan unit organisasi.</li>
                            <li>Kolom bertanda bintang wajib diisi. Akun administrator tidak dibuat melalui formulir ini.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-body d-flex flex-wrap align-items-center gap-3">
                        <span class="text-secondary me-auto">Periksa identitas dan akses akun sebelum menyimpan.</span>
                        <div class="d-flex gap-2">
                            <a href="<?php echo e($is_edit ? route('employee.show', $employee->unique_id) : route('employee.index')); ?>" class="btn btn-outline-secondary">Batal</a>
                            <button class="btn btn-primary" type="submit" id="btn-submit"><i class="ti ti-device-floppy me-1" aria-hidden="true"></i><?php echo e($is_edit ? 'Simpan Perubahan' : 'Simpan Akun'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

<?php $__env->startPush('styles'); ?>
    <style>
        .employee-photo-preview { object-fit: cover; }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        $(function () {
            const existing_photo_url = <?php echo \Illuminate\Support\Js::from($existing_photo_url)->toHtml() ?>;
            let preview_url = null;
            function reset_photo_preview() {
                if (preview_url) { URL.revokeObjectURL(preview_url); preview_url = null; }
                const show_existing = existing_photo_url && !$('#remove_photo').prop('checked');
                $('#photo-preview').toggleClass('d-none', !show_existing);
                $('#photo-preview-placeholder').toggleClass('d-none', Boolean(show_existing));
                if (show_existing) { $('#photo-preview').attr('src', existing_photo_url); }
                else { $('#photo-preview').removeAttr('src'); }
            }
            $('#profile_photo').on('change', function () {
                reset_photo_preview();
                $('#photo-feedback').text('');
                const file = this.files[0];
                if (!file) { return; }
                if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 10485760) {
                    this.value = '';
                    $('#photo-feedback').text('Pilih foto JPG, JPEG, PNG, atau WEBP dengan ukuran maksimal 10 MB.');
                    return;
                }
                $('#remove_photo').prop('checked', false);
                preview_url = URL.createObjectURL(file);
                $('#photo-preview').attr('src', preview_url).removeClass('d-none');
                $('#photo-preview-placeholder').addClass('d-none');
            });

            $('#remove_photo').on('change', function () {
                $('#profile_photo').val('');
                $('#photo-feedback').text('');
                reset_photo_preview();
            });
            $(window).on('pagehide', function () {
                if (preview_url) { URL.revokeObjectURL(preview_url); }
            });

            const original_nik = <?php echo \Illuminate\Support\Js::from($employee?->employee_id)->toHtml() ?>;
            let nik_timeout = null;
            let nik_request = null;
            $('#employee_id').on('input', function () {
                clearTimeout(nik_timeout);
                if (nik_request) { nik_request.abort(); nik_request = null; }
                const nik = $(this).val().toString();
                const feedback = $('#nik-feedback');
                feedback.removeClass('text-success text-danger').text('');
                if (!/^[0-9]{9}$/.test(nik)) { return; }
                if (nik === original_nik) { feedback.text('NIK karyawan saat ini.'); return; }
                feedback.text('Memeriksa NIK...');
                nik_timeout = setTimeout(function () {
                    nik_request = $.post(<?php echo \Illuminate\Support\Js::from(route('employee.check-nik'))->toHtml() ?>, { employee_id: nik })
                        .done(function (response) {
                            if ($('#employee_id').val() !== nik) { return; }
                            feedback.addClass(response.available ? 'text-success' : 'text-danger')
                                .text(response.available ? 'NIK tersedia.' : 'NIK sudah terdaftar.');
                        })
                        .fail(function (xhr, status) {
                            if (status === 'abort' || $('#employee_id').val() !== nik) { return; }
                            feedback.addClass('text-danger').text('NIK belum dapat diperiksa. Sistem akan memvalidasi ulang saat akun disimpan.');
                        });
                }, 500);
            });

            const validator = $('#employee-form').validate({
                ignore: [],
                rules: {
                    name: { required: true, maxlength: 255 },
                    employee_id: { required: true, digits: true, minlength: 9, maxlength: 9 },
                    email: { email: true, maxlength: 255 },
                    position_id: { required: true },
                    status: { required: true },
                    password: { minlength: 8 },
                    password_confirmation: {
                        required: function () { return $('#password').val().length > 0; },
                        equalTo: '#password',
                    },
                },
                messages: {
                    name: { required: 'Nama lengkap wajib diisi.', maxlength: 'Nama maksimal 255 karakter.' },
                    employee_id: { required: 'NIK wajib diisi.', digits: 'NIK harus berupa angka.', minlength: 'NIK harus tepat 9 digit.', maxlength: 'NIK harus tepat 9 digit.' },
                    email: { email: 'Masukkan alamat email yang valid.', maxlength: 'Email maksimal 255 karakter.' },
                    position_id: { required: 'Pilih jabatan karyawan.' },
                    status: { required: 'Pilih status karyawan.' },
                    password: { minlength: 'Kata sandi minimal 8 karakter.' },
                    password_confirmation: { required: 'Konfirmasi kata sandi wajib diisi.', equalTo: 'Konfirmasi kata sandi harus sama.' },
                },
                errorElement: 'div',
                errorClass: 'invalid-feedback',
                errorPlacement: function (error, element) {
                    if (element.hasClass('select2-hidden-accessible')) {
                        error.addClass('d-block').insertAfter(element.next('.select2'));
                    } else { error.insertAfter(element); }
                },
                highlight: function (element) {
                    $(element).addClass('is-invalid');
                    $(element).next('.select2').find('.select2-selection').addClass('is-invalid');
                },
                unhighlight: function (element) {
                    $(element).removeClass('is-invalid');
                    $(element).next('.select2').find('.select2-selection').removeClass('is-invalid');
                },
            });
            $('#position_id, #manager_id').on('change', function () { validator.element(this); });
        });
    </script>
<?php $__env->stopPush(); ?>
<?php /**PATH /home/snowy/projects/assi-repair/resources/views/employee/partials/form.blade.php ENDPATH**/ ?>