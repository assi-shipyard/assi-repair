<?php $__env->startSection('title', 'Ubah Perusahaan'); ?>
<?php $__env->startSection('body_title', 'Ubah Perusahaan'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('company.show', $company->unique_id)); ?>" class="btn btn-outline-secondary">Kembali</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('partials.form-shell-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php
        $filled_company_fields = collect([
            old('name', $company->name),
            old('phone_1', $company->phone_1 ?? ''),
            old('phone_2', $company->phone_2 ?? ''),
            old('email', $company->email ?? ''),
            old('tax_id', $company->tax_id ?? ''),
            old('registration_number', $company->registration_number ?? ''),
            old('address', $company->address),
            old('ceo_name', $company->ceo_name ?? ''),
            old('ceo_phone', $company->ceo_phone ?? ''),
            old('ceo_email', $company->ceo_email ?? ''),
            old('pic_name', $company->pic_name ?? ''),
            old('pic_phone', $company->pic_phone ?? ''),
            old('pic_email', $company->pic_email ?? ''),
        ])->filter()->count();
    ?>

    <form action="<?php echo e(route('company.update', $company->unique_id)); ?>" method="POST" class="form-shell" autocomplete="off" id="company-edit-form">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div class="form-layout">
            <div class="form-sidebar">
                <div class="form-info-card" style="--form-info-bg: var(--tblr-teal-lt); --form-info-avatar-bg: var(--tblr-teal);">
                    <div class="form-info-body">
                        <div class="form-info-header">
                            <span class="form-info-avatar"><i class="ti ti-building-bank"></i></span>
                            <div>
                                <h2 class="form-info-title">Ubah perusahaan</h2>
                                <p class="form-info-subtitle">Rapikan identitas, legal, dan kontak operasional agar seluruh modul membaca informasi terbaru.</p>
                            </div>
                        </div>
                        <div class="form-step-list">
                            <div class="form-step-item"><span class="form-step-badge">1</span><span>Periksa nama resmi, NPWP, dan registrasi.</span></div>
                            <div class="form-step-item"><span class="form-step-badge">2</span><span>Pastikan telepon dan email masih aktif.</span></div>
                            <div class="form-step-item"><span class="form-step-badge">3</span><span>Sesuaikan alamat dan kontak PIC bila ada perubahan operasional.</span></div>
                        </div>
                        <div class="form-kpi-grid">
                            <div class="form-kpi"><span class="form-kpi-label">Field Terisi</span><span class="form-kpi-value"><?php echo e($filled_company_fields); ?></span></div>
                            <div class="form-kpi"><span class="form-kpi-label">Legal</span><span class="form-kpi-value">3</span></div>
                            <div class="form-kpi"><span class="form-kpi-label">Kontak Kunci</span><span class="form-kpi-value">6</span></div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header"><h3 class="card-title mb-0">Fokus pembaruan</h3></div>
                    <div class="card-body">
                        <div class="form-note-list">
                            <div class="form-note-item"><div class="form-note-label">Identitas resmi</div><div>Nama perusahaan, NPWP, dan registrasi harus konsisten dengan dokumen legal.</div></div>
                            <div class="form-note-item"><div class="form-note-label">Kontak aktif</div><div>Gunakan nomor dan email aktif untuk mengurangi hambatan koordinasi.</div></div>
                            <div class="form-note-item"><div class="form-note-label">Alamat final</div><div>Gunakan format yang siap dipakai untuk surat, invoice, dan kontrak.</div></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-main-card card shadow-sm">
                <div class="form-main-card-header"><div><h3 class="form-main-card-title">Form Perusahaan</h3><div class="form-main-card-copy">Perbarui data yang muncul pada kartu perusahaan, halaman detail, dan relasi master data lainnya.</div></div></div>
                <div class="form-main-card-body">
                    <div class="form-summary-alert mb-4">
                        <div class="form-summary-top">
                            <div><div class="form-summary-title">Ringkasan profil saat ini</div><div class="form-summary-copy">Pastikan kontak utama dan alamat tetap relevan untuk komunikasi proyek dan administrasi.</div></div>
                            <span class="badge bg-teal-lt text-teal"><?php echo e($company->name); ?></span>
                        </div>
                    </div>

                    <div class="form-surface mb-4">
                        <div class="form-main-card-header"><div><h3 class="form-main-card-title">Informasi Dasar</h3><div class="form-main-card-copy">Jaga agar identitas perusahaan dan kanal utama komunikasi tetap akurat.</div></div></div>
                        <div class="form-main-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label required">Nama Perusahaan</label>
                                    <input class="form-control" name="name" value="<?php echo e(old('name', $company->name)); ?>" placeholder="Nama resmi perusahaan" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label required">Telepon Utama</label>
                                    <input class="form-control" name="phone_1" value="<?php echo e(old('phone_1', $company->phone_1 ?? '')); ?>" placeholder="021-xxxxxxx" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">Telepon Alternatif</label>
                                    <input class="form-control" name="phone_2" value="<?php echo e(old('phone_2', $company->phone_2 ?? '')); ?>" placeholder="08xxxxxxxxxx">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Email</label>
                                    <input class="form-control" type="email" name="email" value="<?php echo e(old('email', $company->email ?? '')); ?>" placeholder="vendor@contoh.com">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">NPWP</label>
                                    <input class="form-control" name="tax_id" value="<?php echo e(old('tax_id', $company->tax_id ?? '')); ?>" placeholder="00.000.000.0-000.000">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">Nomor Registrasi</label>
                                    <input class="form-control" name="registration_number" value="<?php echo e(old('registration_number', $company->registration_number ?? '')); ?>" placeholder="Nomor registrasi legal">
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>

                    <div class="form-surface mb-4">
                        <div class="form-main-card-header"><div><h3 class="form-main-card-title">Kontak Penanggung Jawab</h3><div class="form-main-card-copy">Pastikan jalur komunikasi strategis dan operasional dibedakan dengan jelas.</div></div></div>
                        <div class="form-main-card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Nama CEO</label>
                                    <input class="form-control" name="ceo_name" value="<?php echo e(old('ceo_name', $company->ceo_name ?? '')); ?>" placeholder="Nama CEO">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Telepon CEO</label>
                                    <input class="form-control" name="ceo_phone" value="<?php echo e(old('ceo_phone', $company->ceo_phone ?? '')); ?>" placeholder="08xxxxxxxxxx">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Email CEO</label>
                                    <input class="form-control" type="email" name="ceo_email" value="<?php echo e(old('ceo_email', $company->ceo_email ?? '')); ?>" placeholder="ceo@contoh.com">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Nama PIC</label>
                                    <input class="form-control" name="pic_name" value="<?php echo e(old('pic_name', $company->pic_name ?? '')); ?>" placeholder="Nama PIC operasional">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Telepon PIC</label>
                                    <input class="form-control" name="pic_phone" value="<?php echo e(old('pic_phone', $company->pic_phone ?? '')); ?>" placeholder="08xxxxxxxxxx">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Email PIC</label>
                                    <input class="form-control" type="email" name="pic_email" value="<?php echo e(old('pic_email', $company->pic_email ?? '')); ?>" placeholder="pic@contoh.com">
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>

                    <div class="form-surface">
                        <div class="form-main-card-header"><div><h3 class="form-main-card-title">Alamat Perusahaan</h3><div class="form-main-card-copy">Tulis alamat lengkap dengan format yang siap digunakan untuk surat dan kontrak.</div></div></div>
                        <div class="form-main-card-body">
                        <div class="form-group">
                            <label class="form-label required">Alamat</label>
                            <textarea class="form-control" name="address" rows="4" required><?php echo e(old('address', $company->address)); ?></textarea>
                            <div class="form-help-text">Maksimal 255 karakter.</div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions-card">
            <div class="form-actions-row">
                <div class="form-actions-copy">Simpan perubahan setelah informasi kontak dan alamat sudah konsisten dengan dokumen perusahaan.</div>
                <div class="form-actions-buttons">
                    <a href="<?php echo e(route('company.show', $company->unique_id)); ?>" class="btn btn-outline-secondary">Batal</a>
                    <button class="btn btn-primary" type="submit">Perbarui Perusahaan</button>
                </div>
            </div>
        </div>
    </form>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
	<script>
		$(document).ready(function() {
			$('#company-edit-form').validate({
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
Ed

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views\company\edit.blade.php ENDPATH**/ ?>