<?php $__env->startSection('title', 'Tambah Kapal'); ?>
<?php $__env->startSection('body_title', 'Tambah Kapal'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('ship.index')); ?>" class="btn btn-outline-secondary">Kembali</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <form action="<?php echo e(route('ship.store')); ?>" method="POST" class="card" id="ship-form">
        <?php echo csrf_field(); ?>
        <div class="card-header">
            <div>
                <h3 class="card-title mb-1">Formulir Kapal</h3>
                <p class="text-muted mb-0">Bagi data identitas kapal, dimensi, dan spesifikasi mesin agar mudah dipindai.</p>
            </div>
        </div>
        <div class="card-body">
            <div class="card mb-3 shadow-none border">
                <div class="card-header py-3">
                    <div>
                        <h4 class="card-title mb-1">Identitas Kapal</h4>
                        <div class="text-muted">Data utama kapal beserta hubungan ke perusahaan dan klasifikasinya.</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
							<div class="form-group">
								<label class="form-label required">Nama Kapal</label>
								<input class="form-control" name="name" id="name" value="<?php echo e(old('name')); ?>" required>
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
							<label class="form-label">IMO</label>
							<input class="form-control" name="imo_number" id="imo_number" value="<?php echo e(old('imo_number')); ?>">
						</div>
                        <div class="col-md-6">
							<label class="form-label">MMSI</label>
							<input class="form-control" name="mmsi_number" id="mmsi_number" value="<?php echo e(old('mmsi_number')); ?>">
						</div>
                        <div class="col-md-6">
							<label class="form-label">Call Sign</label>
							<input class="form-control" name="call_sign" id="call_sign" value="<?php echo e(old('call_sign')); ?>">
						</div>
                        <div class="col-md-6">
							<label class="form-label">Bendera</label>
							<input class="form-control" name="flag" id="flag" value="<?php echo e(old('flag')); ?>">
						</div>
                    </div>
                </div>
            </div>

            <div class="card mb-3 shadow-none border">
                <div class="card-header py-3">
                    <div>
                        <h4 class="card-title mb-1">Dimensi dan Tonase</h4>
                        <div class="text-muted">Ukuran utama kapal untuk kebutuhan teknis dan dokumen proyek.</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
							<label class="form-label">LOA</label>
							<input class="form-control" name="length_overall" id="length_overall" value="<?php echo e(old('length_overall')); ?>" required>
						</div>
                        <div class="col-md-3">
							<label class="form-label">Breadth</label>
							<input class="form-control" name="breadth" id="breadth" value="<?php echo e(old('breadth')); ?>" required>
						</div>
                        <div class="col-md-3">
							<label class="form-label">Height</label>
							<input class="form-control" name="height" id="height" value="<?php echo e(old('height')); ?>" required>
						</div>
                        <div class="col-md-3">
							<label class="form-label">Gross Tonnage</label>
							<input class="form-control" name="gross_tonnage" id="gross_tonnage" value="<?php echo e(old('gross_tonnage')); ?>" required>
						</div>
                        <div class="col-md-3">
							<label class="form-label">Draft Kosong</label>
							<input class="form-control" name="empty_draft" id="empty_draft" value="<?php echo e(old('empty_draft')); ?>">
						</div>
                        <div class="col-md-3">
							<label class="form-label">Draft Muat</label>
							<input class="form-control" name="loaded_draft" id="loaded_draft" value="<?php echo e(old('loaded_draft')); ?>">
						</div>
                        <div class="col-md-3">
							<label class="form-label">Net Tonnage</label>
							<input class="form-control" name="net_tonnage" id="net_tonnage" value="<?php echo e(old('net_tonnage')); ?>">
						</div>
                        <div class="col-md-3">
							<label class="form-label">Tahun Pembuatan</label>
							<input class="form-control" type="date" name="build_year" id="build_year" value="<?php echo e(old('build_year')); ?>">
						</div>
                    </div>
                </div>
            </div>

            <div class="card shadow-none border">
                <div class="card-header py-3">
                    <div>
                        <h4 class="card-title mb-1">Spesifikasi Mesin</h4>
                        <div class="text-muted">Isi data mesin dan kebutuhan bahan bakar kapal.</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
							<label class="form-label">Merek Mesin</label>
							<input class="form-control" name="engine_brand" id="engine_brand" value="<?php echo e(old('engine_brand')); ?>">
						</div>
                        <div class="col-md-6">
							<label class="form-label">Model Mesin</label>
							<input class="form-control" name="engine_model" id="engine_model" value="<?php echo e(old('engine_model')); ?>">
						</div>
                        <div class="col-md-4">
							<label class="form-label">Daya Mesin</label>
							<input class="form-control" name="engine_power" id="engine_power" value="<?php echo e(old('engine_power')); ?>">
						</div>
                        <div class="col-md-4">
							<label class="form-label">Tipe Mesin</label>
							<input class="form-control" name="engine_type" id="engine_type" value="<?php echo e(old('engine_type')); ?>">
						</div>
                        <div class="col-md-4">
							<label class="form-label">RPM Mesin</label>
							<input class="form-control" name="engine_rpm" id="engine_rpm" value="<?php echo e(old('engine_rpm')); ?>">
						</div>
                        <div class="col-md-6">
							<label class="form-label">Tipe BBM</label>
							<input class="form-control" name="engine_fuel_type" id="engine_fuel_type" value="<?php echo e(old('engine_fuel_type')); ?>">
						</div>
                        <div class="col-md-3">
							<label class="form-label">Kapasitas BBM</label>
							<input class="form-control" name="engine_fuel_capacity" id="engine_fuel_capacity" value="<?php echo e(old('engine_fuel_capacity')); ?>">
						</div>
                        <div class="col-md-3">
							<label class="form-label">Konsumsi BBM</label>
							<input class="form-control" name="engine_fuel_consumption" id="engine_fuel_consumption" value="<?php echo e(old('engine_fuel_consumption')); ?>">
						</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
			<button class="btn btn-primary" type="submit">Simpan</button>
		</div>
    </form>
<?php $__env->stopSection(); ?>


<?php $__env->startPush('scripts'); ?>
	<script>
		$(document).ready(function() {
			$('#ship-form').validate({
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views\ship\create.blade.php ENDPATH**/ ?>