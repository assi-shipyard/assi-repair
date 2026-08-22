<?php $__env->startSection('title', 'Tambah Kapal'); ?>
<?php $__env->startSection('body_title', 'Tambah Kapal'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('ship.index')); ?>" class="btn btn-outline-secondary">Kembali</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
	<?php echo $__env->make('partials.form-shell-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

	<?php
		$filled_ship_fields = collect([
			old('name'),
			old('company_id'),
			old('ship_type_id'),
			old('ship_class_id'),
			old('imo_number'),
			old('mmsi_number'),
			old('call_sign'),
			old('flag'),
			old('length_overall'),
			old('breadth'),
			old('height'),
			old('gross_tonnage'),
			old('empty_draft'),
			old('loaded_draft'),
			old('net_tonnage'),
			old('build_year'),
			old('engine_brand'),
			old('engine_model'),
			old('engine_power'),
			old('engine_type'),
			old('engine_rpm'),
			old('engine_fuel_type'),
			old('engine_fuel_capacity'),
			old('engine_fuel_consumption'),
		])->filter()->count();
	?>

	<form action="<?php echo e(route('ship.store')); ?>" method="POST" class="form-shell" id="ship-form">
        <?php echo csrf_field(); ?>
		<div class="form-layout">
			<div class="form-sidebar">
				<div class="form-info-card" style="--form-info-bg: var(--tblr-blue-lt); --form-info-avatar-bg: var(--tblr-blue);">
					<div class="form-info-body">
						<div class="form-info-header">
							<span class="form-info-avatar"><i class="ti ti-ship"></i></span>
							<div><h2 class="form-info-title">Tambah kapal</h2><p class="form-info-subtitle">Susun identitas, dimensi, dan mesin kapal dengan pola yang mudah dipindai.</p></div>
						</div>
						<div class="form-step-list">
							<div class="form-step-item"><span class="form-step-badge">1</span><span>Pilih perusahaan, jenis, dan kelas kapal.</span></div>
							<div class="form-step-item"><span class="form-step-badge">2</span><span>Masukkan dimensi pokok dan tonase untuk kebutuhan docking.</span></div>
							<div class="form-step-item"><span class="form-step-badge">3</span><span>Lengkapi spesifikasi mesin agar profil teknis lebih kaya.</span></div>
						</div>
						<div class="form-kpi-grid">
							<div class="form-kpi"><span class="form-kpi-label">Field Terisi</span><span class="form-kpi-value"><?php echo e($filled_ship_fields); ?></span></div>
							<div class="form-kpi"><span class="form-kpi-label">Bagian Utama</span><span class="form-kpi-value">3</span></div>
							<div class="form-kpi"><span class="form-kpi-label">Atribut Teknis</span><span class="form-kpi-value">16+</span></div>
						</div>
					</div>
				</div>

				<div class="card shadow-sm">
					<div class="card-header"><h3 class="card-title mb-0">Informasi penting</h3></div>
					<div class="card-body">
						<div class="form-note-list">
							<div class="form-note-item"><div class="form-note-label">Identitas Kapal</div><div>Hubungkan kapal ke perusahaan, jenis, dan kelas agar filter data bekerja baik.</div></div>
							<div class="form-note-item"><div class="form-note-label">Dimensi</div><div>Ukuran utama dipakai untuk evaluasi kompatibilitas docking dan perencanaan teknis.</div></div>
							<div class="form-note-item"><div class="form-note-label">Mesin</div><div>Spesifikasi mesin membantu tim survey dan dokumen pekerjaan membaca konteks kapal.</div></div>
						</div>
					</div>
				</div>
			</div>

			<div class="form-main-card card shadow-sm">
				<div class="form-main-card-header"><div><h3 class="form-main-card-title">Form Kapal Baru</h3><div class="form-main-card-copy">Isi identitas kapal dan informasi teknis agar siap dipakai pada proyek dan docking.</div></div></div>
				<div class="form-main-card-body">
					<div class="form-summary-alert mb-4"><div class="form-summary-top"><div><div class="form-summary-title">Ringkasan input</div><div class="form-summary-copy">Fokus utama ada pada identitas kapal, dimensi pokok, dan mesin utama.</div></div><span class="badge bg-blue-lt text-blue">Ship profile</span></div></div>

					<div class="form-surface mb-4">
						<div class="form-main-card-header"><div><h3 class="form-main-card-title">Identitas Kapal</h3><div class="form-main-card-copy">Data dasar kapal dan kaitannya dengan entitas lain di sistem.</div></div></div>
						<div class="form-main-card-body">
						<div class="row g-3">
							<div class="col-md-6">
								<div class="form-group">
									<label class="form-label required">Nama Kapal</label>
									<input class="form-control" name="name" id="name" value="<?php echo e(old('name')); ?>" placeholder="Contoh: KMP Nusantara 01" required>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="form-label required">Perusahaan</label>
									<select class="form-select dropdown-list" name="company_id" id="company_id" required>
										<option value="" selected disabled>Pilih perusahaan</option>
										<?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
											<option value="<?php echo e($company->id); ?>" <?php if(old('company_id') == $company->id): echo 'selected'; endif; ?>><?php echo e($company->name); ?></option>
										<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
									</select>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="form-label required">Jenis Kapal</label>
									<select class="form-select dropdown-list" name="ship_type_id" id="ship_type_id" required>
										<option value="" selected disabled>Pilih jenis</option>
										<?php $__currentLoopData = $ship_types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ship_type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
											<option value="<?php echo e($ship_type->id); ?>" <?php if(old('ship_type_id') == $ship_type->id): echo 'selected'; endif; ?>><?php echo e($ship_type->name); ?></option>
										<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
									</select>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="form-label required">Kelas Kapal</label>
									<select class="form-select dropdown-list" name="ship_class_id" id="ship_class_id" required>
										<option value="" selected disabled>Pilih kelas</option>
										<?php $__currentLoopData = $ship_classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ship_class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
											<option value="<?php echo e($ship_class->id); ?>" <?php if(old('ship_class_id') == $ship_class->id): echo 'selected'; endif; ?>><?php echo e($ship_class->name); ?> (<?php echo e($ship_class->abbreviation); ?>)</option>
										<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
									</select>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="form-label">IMO</label>
									<input class="form-control" name="imo_number" id="imo_number" value="<?php echo e(old('imo_number')); ?>" placeholder="IMO number">
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="form-label">MMSI</label>
									<input class="form-control" name="mmsi_number" id="mmsi_number" value="<?php echo e(old('mmsi_number')); ?>" placeholder="MMSI number">
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="form-label">Call Sign</label>
									<input class="form-control" name="call_sign" id="call_sign" value="<?php echo e(old('call_sign')); ?>" placeholder="Call sign kapal">
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="form-label">Bendera</label>
									<input class="form-control" name="flag" id="flag" value="<?php echo e(old('flag')); ?>" placeholder="Negara bendera">
								</div>
							</div>
						</div>
						</div>
					</div>

					<div class="form-surface mb-4">
						<div class="form-main-card-header"><div><h3 class="form-main-card-title">Dimensi dan Tonase</h3><div class="form-main-card-copy">Masukkan ukuran pokok dan tonase agar data teknis siap dipakai pada evaluasi kapasitas.</div></div></div>
						<div class="form-main-card-body">
						<div class="row g-3">
							<div class="col-md-3"><div class="form-group"><label class="form-label required">LOA</label><input class="form-control" name="length_overall" id="length_overall" value="<?php echo e(old('length_overall')); ?>" placeholder="Meter" required></div></div>
							<div class="col-md-3"><div class="form-group"><label class="form-label required">Breadth</label><input class="form-control" name="breadth" id="breadth" value="<?php echo e(old('breadth')); ?>" placeholder="Meter" required></div></div>
							<div class="col-md-3"><div class="form-group"><label class="form-label required">Height</label><input class="form-control" name="height" id="height" value="<?php echo e(old('height')); ?>" placeholder="Meter" required></div></div>
							<div class="col-md-3"><div class="form-group"><label class="form-label required">Gross Tonnage</label><input class="form-control" name="gross_tonnage" id="gross_tonnage" value="<?php echo e(old('gross_tonnage')); ?>" placeholder="GT" required></div></div>
							<div class="col-md-3"><div class="form-group"><label class="form-label">Draft Kosong</label><input class="form-control" name="empty_draft" id="empty_draft" value="<?php echo e(old('empty_draft')); ?>" placeholder="Meter"></div></div>
							<div class="col-md-3"><div class="form-group"><label class="form-label">Draft Muat</label><input class="form-control" name="loaded_draft" id="loaded_draft" value="<?php echo e(old('loaded_draft')); ?>" placeholder="Meter"></div></div>
							<div class="col-md-3"><div class="form-group"><label class="form-label">Net Tonnage</label><input class="form-control" name="net_tonnage" id="net_tonnage" value="<?php echo e(old('net_tonnage')); ?>" placeholder="NT"></div></div>
							<div class="col-md-3"><div class="form-group"><label class="form-label">Tahun Pembuatan</label><input class="form-control" type="date" name="build_year" id="build_year" value="<?php echo e(old('build_year')); ?>"></div></div>
						</div>
						</div>
					</div>

					<div class="form-surface">
						<div class="form-main-card-header"><div><h3 class="form-main-card-title">Spesifikasi Mesin</h3><div class="form-main-card-copy">Isi data mesin utama dan bahan bakar untuk memperkaya profil teknis kapal.</div></div></div>
						<div class="form-main-card-body">
						<div class="row g-3">
							<div class="col-md-6"><div class="form-group"><label class="form-label">Merek Mesin</label><input class="form-control" name="engine_brand" id="engine_brand" value="<?php echo e(old('engine_brand')); ?>" placeholder="Contoh: Caterpillar"></div></div>
							<div class="col-md-6"><div class="form-group"><label class="form-label">Model Mesin</label><input class="form-control" name="engine_model" id="engine_model" value="<?php echo e(old('engine_model')); ?>" placeholder="Model mesin"></div></div>
							<div class="col-md-4"><div class="form-group"><label class="form-label">Daya Mesin</label><input class="form-control" name="engine_power" id="engine_power" value="<?php echo e(old('engine_power')); ?>" placeholder="HP / kW"></div></div>
							<div class="col-md-4"><div class="form-group"><label class="form-label">Tipe Mesin</label><input class="form-control" name="engine_type" id="engine_type" value="<?php echo e(old('engine_type')); ?>" placeholder="Tipe mesin"></div></div>
							<div class="col-md-4"><div class="form-group"><label class="form-label">RPM Mesin</label><input class="form-control" name="engine_rpm" id="engine_rpm" value="<?php echo e(old('engine_rpm')); ?>" placeholder="RPM"></div></div>
							<div class="col-md-6"><div class="form-group"><label class="form-label">Tipe BBM</label><input class="form-control" name="engine_fuel_type" id="engine_fuel_type" value="<?php echo e(old('engine_fuel_type')); ?>" placeholder="Jenis bahan bakar"></div></div>
							<div class="col-md-3"><div class="form-group"><label class="form-label">Kapasitas BBM</label><input class="form-control" name="engine_fuel_capacity" id="engine_fuel_capacity" value="<?php echo e(old('engine_fuel_capacity')); ?>" placeholder="Liter"></div></div>
							<div class="col-md-3"><div class="form-group"><label class="form-label">Konsumsi BBM</label><input class="form-control" name="engine_fuel_consumption" id="engine_fuel_consumption" value="<?php echo e(old('engine_fuel_consumption')); ?>" placeholder="Liter/jam"></div></div>
						</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="form-actions-card">
			<div class="form-actions-row">
				<div class="form-actions-copy">Simpan setelah identitas kapal dan dimensi pokok terisi dengan format yang konsisten.</div>
				<div class="form-actions-buttons">
					<a href="<?php echo e(route('ship.index')); ?>" class="btn btn-outline-secondary">Batal</a>
					<button class="btn btn-primary" type="submit">Simpan Kapal</button>
				</div>
			</div>
		</div>
    </form>
<?php $__env->stopSection(); ?>


<?php $__env->startPush('scripts'); ?>
	<script>
		$(document).ready(function() {
			$('#ship-form').validate({
				ignore: [],
				rules: {
					name: {
						required: true,
						minlength: 3,
						maxlength: 255
					},
					company_id: {
						required: true,
					},
					ship_type_id: {
						required: true,
					},
					ship_class_id: {
						required: true,
					}
				},
				messages: {
					name: {
						required: 'Nama kapal wajib diisi.',
						minlength: 'Nama kapal minimal 3 karakter.',
						maxlength: 'Nama kapal maksimal 255 karakter.'
					},
					company_id: {
						required: 'Perusahaan pemilik wajib dipilih.'
					},
					ship_type_id: {
						required: 'Jenis kapal wajib dipilih.'
					},
					ship_class_id: {
						required: 'Kelas kapal wajib dipilih.'
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views\ship\create.blade.php ENDPATH**/ ?>