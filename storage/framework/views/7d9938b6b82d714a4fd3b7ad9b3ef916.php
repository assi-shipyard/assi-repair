<?php $__env->startSection('title', 'Ubah Unit Organisasi'); ?>
<?php $__env->startSection('body_title', 'Ubah Unit Organisasi'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('organizational-unit.show', $organizational_unit->unique_id ?? $organizational_unit->id)); ?>" class="btn btn-outline-secondary">Kembali</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <form action="<?php echo e(route('organizational-unit.update', $organizational_unit->unique_id ?? $organizational_unit->id)); ?>" method="POST" class="card">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Nama</label><input class="form-control" name="name" value="<?php echo e(old('name', $organizational_unit->name)); ?>" required></div>
                <div class="col-md-6"><label class="form-label">Kode</label><input class="form-control" name="code" value="<?php echo e(old('code', $organizational_unit->code ?? '')); ?>"></div>
                <div class="col-md-6"><label class="form-label">Jenis</label><select class="form-select" name="type" required><?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($type); ?>" <?php if(old('type', $organizational_unit->type) === $type): echo 'selected'; endif; ?>><?php echo e($label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                <div class="col-md-6"><label class="form-label">Unit Induk</label><select class="form-select" name="parent_id"><option value="">Tanpa induk</option><?php $__currentLoopData = $organizationalUnits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $organizationalUnitOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($organizationalUnitOption->id); ?>" <?php if(old('parent_id', $organizational_unit->parent_id) == $organizationalUnitOption->id): echo 'selected'; endif; ?>><?php echo e($organizationalUnitOption->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
            </div>
        </div>
        <div class="card-footer text-end"><button class="btn btn-primary" type="submit">Perbarui</button></div>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views\organizational-unit\edit.blade.php ENDPATH**/ ?>