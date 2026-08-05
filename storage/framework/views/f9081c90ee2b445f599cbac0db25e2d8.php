<?php $__env->startSection('title', 'Ubah Jabatan'); ?>
<?php $__env->startSection('body_title', 'Ubah Jabatan'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('position.show', $position->id)); ?>" class="btn btn-outline-secondary">Kembali</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <form action="<?php echo e(route('position.update', $position->id)); ?>" method="POST" class="card">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Nama</label><input class="form-control" name="name" value="<?php echo e(old('name', $position->name)); ?>" required></div>
                <div class="col-md-6"><label class="form-label">Kode</label><input class="form-control" name="code" value="<?php echo e(old('code', $position->code ?? '')); ?>"></div>
                <div class="col-md-6"><label class="form-label">Kategori</label><select class="form-select" name="category" required><?php $__currentLoopData = $category_options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($category); ?>" <?php if(old('category', $position->category) === $category): echo 'selected'; endif; ?>><?php echo e($label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                <div class="col-md-6"><label class="form-label">Unit Organisasi</label><select class="form-select" name="organizational_unit_id" required><?php $__currentLoopData = $organizational_units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $organizational_unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($organizational_unit->id); ?>" <?php if(old('organizational_unit_id', $position->organizational_unit_id) == $organizational_unit->id): echo 'selected'; endif; ?>><?php echo e($organizational_unit->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                <div class="col-md-6 form-check mt-4"><input class="form-check-input" type="checkbox" name="is_head_position" value="1" id="is_head_position" <?php if(old('is_head_position', $position->is_head_position)): echo 'checked'; endif; ?>><label class="form-check-label" for="is_head_position">Jabatan kepala</label></div>
            </div>
        </div>
        <div class="card-footer text-end"><button class="btn btn-primary" type="submit">Perbarui</button></div>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views\position\edit.blade.php ENDPATH**/ ?>