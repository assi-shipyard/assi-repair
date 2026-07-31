<?php $__env->startSection('title', 'Tambah Jabatan'); ?>
<?php $__env->startSection('body_title', 'Tambah Jabatan'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('position.index')); ?>" class="btn btn-outline-secondary">Kembali</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <form action="<?php echo e(route('position.store')); ?>" method="POST" class="card" id="position_form">
        <?php echo csrf_field(); ?>
        <div class="card-header">
            <div>
                <h3 class="card-title mb-1">Formulir Jabatan</h3>
                <p class="text-muted mb-0">Kelompokkan identitas jabatan dan pengaturannya agar lebih mudah dipahami.</p>
            </div>
        </div>
        <div class="card-body">
            <div class="card shadow-none border">
                <div class="card-header py-3">
                    <div>
                        <h4 class="card-title mb-1">Data Jabatan</h4>
                        <div class="text-muted">Nama, kode, kategori, dan unit organisasi yang menaungi jabatan.</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
							<label class="form-label">Nama</label>
							<input class="form-control" name="name" value="<?php echo e(old('name')); ?>" required>
						</div>
                        <div class="col-md-6">
							<label class="form-label">Kode</label>
							<input class="form-control" name="code" value="<?php echo e(old('code')); ?>">
						</div>
                        <div class="col-md-6">
                            <label class="form-label">Kategori</label>
                            <select class="form-select dropdown-list" name="category" required>
                                <option value="" selected disabled>Pilih kategori</option>
                                <?php $__currentLoopData = $category_options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($category); ?>" <?php if(old('category') == $category): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Unit Organisasi</label>
                            <select class="form-select dropdown-list" name="organizational_unit_id" required>
                                <option value="" selected disabled>Pilih unit</option>
                                <?php $__currentLoopData = $organizational_units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $organizational_unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($organizational_unit->id); ?>" <?php if(old('organizational_unit_id') == $organizational_unit->id): echo 'selected'; endif; ?>><?php echo e($organizational_unit->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3 shadow-none border">
                <div class="card-header py-3">
                    <div>
                        <h4 class="card-title mb-1">Penanda Khusus</h4>
                        <div class="text-muted">Tandai bila jabatan ini merupakan jabatan kepala.</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_head_position" value="1" id="is_head_position" <?php if(old('is_head_position')): echo 'checked'; endif; ?>>
                        <label class="form-check-label" for="is_head_position">Jabatan kepala</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-end"><button class="btn btn-primary" type="submit">Simpan</button></div>
    </form>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
	<script>
		$(document).ready(function() {
			$('#position_form').validate({
				rules: {
					name: {
						required: true,
						maxlength: 255
					},
					code: {
						maxlength: 50
					},
					category: {
						required: true
					},
					organizational_unit_id: {
						required: true
					}
				},
				messages: {
					name: {
						required: "Nama jabatan wajib diisi.",
						maxlength: "Nama jabatan tidak boleh lebih dari 255 karakter."
					},
					code: {
						maxlength: "Kode jabatan tidak boleh lebih dari 50 karakter."
					},
					category: {
						required: "Kategori jabatan wajib dipilih."
					},
					organizational_unit_id: {
						required: "Unit organisasi wajib dipilih."
					}
				},
				errorElement: 'div',
				errorPlacement: function(error, element) {
					error.addClass('invalid-feedback');
					if (element.prop('type') === 'checkbox') {
						error.insertAfter(element.next('label'));
					} else {
						error.insertAfter(element);
					}
				},
				highlight: function(element, errorClass, validClass) {
					$(element).addClass('is-invalid').removeClass('is-valid');
				},
				unhighlight: function(element, errorClass, validClass) {
					$(element).removeClass('is-invalid').addClass('is-valid');
				}
			});
		});
	</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views/position/create.blade.php ENDPATH**/ ?>