<?php $__env->startSection('title', 'Tambah Unit Organisasi'); ?>
<?php $__env->startSection('body_title', 'Tambah Unit Organisasi'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('organizational-unit.index')); ?>" class="btn btn-outline-secondary">Kembali</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <form action="<?php echo e(route('organizational-unit.store')); ?>" method="POST" class="card">
        <?php echo csrf_field(); ?>
        <div class="card-header">
            <div>
                <h3 class="card-title mb-1">Formulir Unit Organisasi</h3>
                <p class="text-muted mb-0">Pisahkan identitas unit dan hubungan induknya untuk navigasi yang lebih jelas.</p>
            </div>
        </div>
        <div class="card-body">
            <div class="card shadow-none border">
                <div class="card-header py-3">
                    <div>
                        <h4 class="card-title mb-1">Data Unit</h4>
                        <div class="text-muted">Nama, kode, dan jenis unit organisasi yang akan dibuat.</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Nama</label><input class="form-control" name="name" value="<?php echo e(old('name')); ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Kode</label><input class="form-control" name="code" value="<?php echo e(old('code')); ?>"></div>
                        <div class="col-md-6">
                            <label class="form-label">Jenis</label>
                            <select class="form-select" name="type" required>
                                <option value="">Pilih jenis</option>
                                <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($type); ?>" <?php if(old('type') == $type): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Unit Induk</label>
                            <select class="form-select" name="parent_id">
                                <option value="">Tanpa induk</option>
                                <?php $__currentLoopData = $organizationalUnits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $organizationalUnit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($organizationalUnit->id); ?>" <?php if(old('parent_id') == $organizationalUnit->id): echo 'selected'; endif; ?>><?php echo e($organizationalUnit->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-end"><button class="btn btn-primary" type="submit">Simpan</button></div>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views/organizational-unit/create.blade.php ENDPATH**/ ?>