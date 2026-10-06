<?php $__env->startSection('title', 'Data Klasifikasi'); ?>
<?php $__env->startSection('body_title', 'Data Klasifikasi'); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="row row-cards">
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Tambah Klasifikasi</h3></div>
                <div class="card-body">
                    <form id="ship-class-form" action="<?php echo e(route('ship-classification.store')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <label for="name" class="form-label required">Nama Klasifikasi</label>
                            <input id="name" type="text" name="name" value="<?php echo e(old('name')); ?>" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" maxlength="255" required>
                            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="mb-3">
                            <label for="abbreviation" class="form-label">Singkatan</label>
                            <input id="abbreviation" type="text" name="abbreviation" value="<?php echo e(old('abbreviation')); ?>" class="form-control <?php $__errorArgs = ['abbreviation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" maxlength="255">
                            <?php $__errorArgs = ['abbreviation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Daftar Klasifikasi</h3><div class="card-actions text-secondary"><?php echo e($ship_classes->count()); ?> data</div></div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead><tr><th>Nama</th><th>Singkatan</th><th class="w-1">Aksi</th></tr></thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $ship_classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ship_class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($ship_class->name); ?></td>
                                    <td><?php echo e($ship_class->abbreviation ?? '-'); ?></td>
                                    <td>
                                        <form action="<?php echo e(route('ship-classification.destroy', $ship_class->id)); ?>" method="POST" onsubmit="return confirm('Hapus klasifikasi ini? Referensi klasifikasi pada data kapal terkait akan dikosongkan.')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="3" class="text-center text-secondary py-4">Belum ada klasifikasi kapal.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        if (window.jQuery && jQuery.fn.validate) {
            jQuery('#ship-class-form').validate({
                rules: { name: { required: true, maxlength: 255 }, abbreviation: { maxlength: 255 } },
                errorElement: 'div',
                errorClass: 'invalid-feedback',
                highlight: function (element) { jQuery(element).addClass('is-invalid'); },
                unhighlight: function (element) { jQuery(element).removeClass('is-invalid'); }
            });
        }
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/ship/class/index.blade.php ENDPATH**/ ?>