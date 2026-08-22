<?php $__env->startSection('title', 'Tambah Perusahaan'); ?>
<?php $__env->startSection('body_title', 'Tambah Perusahaan'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('company.index')); ?>" class="btn btn-outline-secondary">Kembali</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
	<?php echo $__env->make('partials.form-shell-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

	<?php
		$filled_company_fields = collect([
			old('name'),
			old('phone_1'),
			old('phone_2'),
			old('email'),
			old('tax_id'),
			old('address'),
			old('ceo_name'),
			old('ceo_phone'),
			old('ceo_email'),
			old('pic_name'),
			old('pic_phone'),
			old('pic_email'),
		])->filter()->count();
	?>

	<form action="<?php echo e(route('company.store')); ?>" method="POST" class="form-shell" autocomplete="off" id="company-create-form">
        <?php echo csrf_field(); ?>
		<div class="form-layout">
			<div class="form-sidebar">
				<div class="form-info-card" style="--form-info-bg: var(--tblr-teal-lt); --form-info-avatar-bg: var(--tblr-teal);">
					<div class="form-info-body">
						<div class="form-info-header">
							<span class="form-info-avatar"><i class="ti ti-building"></i></span>
							<div>
								<h2 class="form-info-title">Tambah perusahaan</h2>
								<p class="form-info-subtitle">Bangun profil vendor yang mudah dipakai di kapal, proyek, dan dokumen.</p>
							</div>
						</div>
						<div class="form-step-list">
							<div class="form-step-item"><span class="form-step-badge">1</span><span>Isi identitas perusahaan dan kanal komunikasi utama.</span></div>
							<div class="form-step-item"><span class="form-step-badge">2</span><span>Tuliskan alamat lengkap untuk kebutuhan administrasi.</span></div>
							<div class="form-step-item"><span class="form-step-badge">3</span><span>Lengkapi data CEO dan PIC operasional bila tersedia.</span></div>
						</div>
						<div class="form-kpi-grid">
							<div class="form-kpi"><span class="form-kpi-label">Field Terisi</span><span class="form-kpi-value"><?php echo e($filled_company_fields); ?></span></div>
							<div class="form-kpi"><span class="form-kpi-label">Wajib Utama</span><span class="form-kpi-value">2</span></div>
							<div class="form-kpi"><span class="form-kpi-label">Kontak Opsional</span><span class="form-kpi-value">6</span></div>
						</div>
					</div>
				</div>

				<div class="card shadow-sm">
					<div class="card-header"><h3 class="card-title mb-0">Informasi penting</h3></div>
					<div class="card-body">
						<div class="form-note-list">
							<div class="form-note-item"><div class="form-note-label">Nama resmi</div><div>Gunakan nama yang sama dengan dokumen legal, invoice, dan kontrak.</div></div>
							<div class="form-note-item"><div class="form-note-label">Alamat kerja</div><div>Pilih alamat yang dipakai untuk korespondensi dan operasional proyek.</div></div>
							<div class="form-note-item"><div class="form-note-label">Kontak berlapis</div><div>Pisahkan komunikasi strategis dan operasional agar eskalasi lebih cepat.</div></div>
						</div>
					</div>
				</div>
			</div>

			<div class="form-main-card card shadow-sm">
				<div class="form-main-card-header">
					<div>
						<h3 class="form-main-card-title">Form Perusahaan Baru</h3>
						<div class="form-main-card-copy">Lengkapi profil vendor agar tim dapat langsung memakai data ini pada modul lain.</div>
					</div>
				</div>
				<div class="form-main-card-body">
					<div class="form-summary-alert mb-4">
						<div class="form-summary-top">
							<div>
								<div class="form-summary-title">Ringkasan entri</div>
								<div class="form-summary-copy">Nama perusahaan dan alamat adalah fondasi profil. Kontak tambahan bisa dilengkapi bertahap.</div>
							</div>
							<span class="badge bg-teal-lt text-teal">Vendor profile</span>
						</div>
					</div>

					<div class="form-surface mb-4">
						<div class="form-main-card-header"><div><h3 class="form-main-card-title">Informasi Dasar</h3><div class="form-main-card-copy">Identitas utama perusahaan dan kanal komunikasi awal.</div></div></div>
						<div class="form-main-card-body">
						<div class="row g-3">
							<div class="col-md-6">
								<div class="form-group">
									<label class="form-label required">Nama Perusahaan</label>
									<input class="form-control" name="name" value="<?php echo e(old('name')); ?>" placeholder="Contoh: PT Adiluhung Saranasegara Indonesia" required>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label class="form-label">Telepon Utama</label>
									<input class="form-control" name="phone_1" value="<?php echo e(old('phone_1')); ?>" placeholder="021-xxxxxxx">
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label class="form-label">Telepon Alternatif</label>
									<input class="form-control" name="phone_2" value="<?php echo e(old('phone_2')); ?>" placeholder="0812xxxxxxx">
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="form-label">Email</label>
									<input class="form-control" type="email" name="email" value="<?php echo e(old('email')); ?>" placeholder="vendor@contoh.com">
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="form-label">NPWP</label>
									<input class="form-control" name="tax_id" value="<?php echo e(old('tax_id')); ?>" placeholder="00.000.000.0-000.000">
								</div>
							</div>
						</div>
						</div>
					</div>

					<div class="form-surface mb-4">
						<div class="form-main-card-header"><div><h3 class="form-main-card-title">Alamat Perusahaan</h3><div class="form-main-card-copy">Tulis alamat lengkap yang akan dipakai pada administrasi dan korespondensi.</div></div></div>
						<div class="form-main-card-body">
						<div class="form-group">
							<label class="form-label required">Alamat</label>
							<textarea class="form-control" name="address" rows="4" placeholder="Jalan, kelurahan, kecamatan, kota, dan kode pos" required><?php echo e(old('address')); ?></textarea>
							<div class="form-help-text">Maksimal 255 karakter.</div>
						</div>
						</div>
					</div>

					<div class="form-surface">
						<div class="form-main-card-header"><div><h3 class="form-main-card-title">Kontak Penanggung Jawab</h3><div class="form-main-card-copy">Masukkan data pemangku keputusan dan PIC operasional agar alur komunikasi jelas.</div></div></div>
						<div class="form-main-card-body">
						<div class="row g-3">
							<div class="col-md-4">
								<div class="form-group">
									<label class="form-label">Nama CEO</label>
									<input class="form-control" name="ceo_name" value="<?php echo e(old('ceo_name')); ?>" placeholder="Nama CEO">
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label class="form-label">Telepon CEO</label>
									<input class="form-control" name="ceo_phone" value="<?php echo e(old('ceo_phone')); ?>" placeholder="08xxxxxxxxxx">
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label class="form-label">Email CEO</label>
									<input class="form-control" type="email" name="ceo_email" value="<?php echo e(old('ceo_email')); ?>" placeholder="ceo@contoh.com">
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label class="form-label">Nama PIC</label>
									<input class="form-control" name="pic_name" value="<?php echo e(old('pic_name')); ?>" placeholder="Nama PIC operasional">
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label class="form-label">Telepon PIC</label>
									<input class="form-control" name="pic_phone" value="<?php echo e(old('pic_phone')); ?>" placeholder="08xxxxxxxxxx">
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label class="form-label">Email PIC</label>
									<input class="form-control" type="email" name="pic_email" value="<?php echo e(old('pic_email')); ?>" placeholder="pic@contoh.com">
								</div>
							</div>
						</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="form-actions-card">
			<div class="form-actions-row">
				<div class="form-actions-copy">Periksa nama, alamat, dan kontak utama sebelum menyimpan data perusahaan.</div>
				<div class="form-actions-buttons">
					<a href="<?php echo e(route('company.index')); ?>" class="btn btn-outline-secondary">Batal</a>
					<button class="btn btn-primary" type="submit">Simpan Perusahaan</button>
				</div>
			</div>
        </div>
    </form>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
	<script>
		$(document).ready(function() {
			$('#company-create-form').validate({
				ignore: [],
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
                },
			});
		});
	</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views\company\create.blade.php ENDPATH**/ ?>