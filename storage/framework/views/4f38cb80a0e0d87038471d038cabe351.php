<?php $__env->startSection('title', 'Ubah Perusahaan'); ?>
<?php $__env->startSection('body_title', 'Ubah Perusahaan'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('company.show', $company->unique_id)); ?>" class="btn btn-outline-secondary">Kembali</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <form action="<?php echo e(route('company.update', $company->unique_id)); ?>" method="POST" class="card" autocomplete="off" id="company-edit-form">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div class="card-header">
            <div>
                <h3 class="card-title mb-1">Formulir Perusahaan</h3>
            </div>
        </div>
        <div class="card-body">
            <div class="card mb-3 shadow-none border">
                <div class="card-header py-3">
                    <div>
                        <h4 class="card-title mb-1">Informasi Dasar</h4>
                        <div class="text-muted">Identitas utama dan nomor kontak perusahaan.</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label required">Nama Perusahaan</label>
                            <input class="form-control" name="name" value="<?php echo e(old('name', $company->name)); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Telepon Utama</label>
                            <input class="form-control" name="phone_1" value="<?php echo e(old('phone_1', $company->phone_1 ?? '')); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Telepon Alternatif</label>
                            <input class="form-control" name="phone_2" value="<?php echo e(old('phone_2', $company->phone_2 ?? '')); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input class="form-control" type="email" name="email" value="<?php echo e(old('email', $company->email ?? '')); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NPWP</label>
                            <input class="form-control" name="tax_id" value="<?php echo e(old('tax_id', $company->tax_id ?? '')); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nomor Registrasi</label>
                            <input class="form-control" name="registration_number" value="<?php echo e(old('registration_number', $company->registration_number ?? '')); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3 shadow-none border">
                <div class="card-header py-3">
                    <div>
                        <h4 class="card-title mb-1">Kontak Penanggung Jawab</h4>
                        <div class="text-muted">Data CEO dan PIC yang akan dihubungi.</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Nama CEO</label>
                            <input class="form-control" name="ceo_name" value="<?php echo e(old('ceo_name', $company->ceo_name ?? '')); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Telepon CEO</label>
                            <input class="form-control" name="ceo_phone" value="<?php echo e(old('ceo_phone', $company->ceo_phone ?? '')); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email CEO</label>
                            <input class="form-control" type="email" name="ceo_email" value="<?php echo e(old('ceo_email', $company->ceo_email ?? '')); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nama PIC</label>
                            <input class="form-control" name="pic_name" value="<?php echo e(old('pic_name', $company->pic_name ?? '')); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Telepon PIC</label>
                            <input class="form-control" name="pic_phone" value="<?php echo e(old('pic_phone', $company->pic_phone ?? '')); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email PIC</label>
                            <input class="form-control" type="email" name="pic_email" value="<?php echo e(old('pic_email', $company->pic_email ?? '')); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-none border">
                <div class="card-header py-3">
                    <div>
                        <h4 class="card-title mb-1">Alamat Perusahaan</h4>
                        <div class="text-muted">Tuliskan alamat lengkap perusahaan untuk kebutuhan administrasi.</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label required">Alamat</label>
                            <textarea class="form-control" name="address" rows="3" required><?php echo e(old('address', $company->address)); ?></textarea>
                            <small class="text-muted">Maksimal 255 karakter.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <button class="btn btn-primary" type="submit">Perbarui</button>
        </div>
    </form>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
	<script>
		$(document).ready(function() {
			$('#company-edit-form').validate({
				rules: {
					name: {
						required: true,
						maxlength: 255
					},
					address: {
						required: true,
						maxlength: 255
					},
					email: {
						email: true,
						maxlength: 255
					},
					ceo_email: {
						email: true,
						maxlength: 255
					},
					pic_email: {
						email: true,
						maxlength: 255
					}
				},
				messages: {
					name: {
						required: "Nama perusahaan wajib diisi.",
						maxlength: "Nama perusahaan tidak boleh lebih dari 255 karakter."
					},
					address: {
						required: "Alamat perusahaan wajib diisi.",
						maxlength: "Alamat perusahaan tidak boleh lebih dari 255 karakter."
					},
					email: {
						email: "Format email tidak valid.",
						maxlength: "Email tidak boleh lebih dari 255 karakter."
					},
					ceo_email: {
						email: "Format email CEO tidak valid.",
						maxlength: "Email CEO tidak boleh lebih dari 255 karakter."
					},
					pic_email: {
						email: "Format email PIC tidak valid.",
						maxlength: "Email PIC tidak boleh lebih dari 255 karakter."
					}
				},
				errorElement: 'span',
                errorPlacement: function (error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('.form-group').append(error);
                },
                highlight: function (element) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function (element) {
                    $(element).removeClass('is-invalid');
                },
			});
		});
	</script>
<?php $__env->stopPush(); ?>
Ed

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views/company/edit.blade.php ENDPATH**/ ?>