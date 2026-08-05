<?php $__env->startSection('title', 'Ubah Kapal'); ?>
<?php $__env->startSection('body_title', 'Ubah Kapal'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('ship.show', $ship->unique_id)); ?>" class="btn btn-outline-secondary">Kembali</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <form action="<?php echo e(route('ship.update', $ship->unique_id)); ?>" method="POST" class="card">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Nama Kapal</label><input class="form-control" name="name" value="<?php echo e(old('name', $ship->name)); ?>" required></div>
                <div class="col-md-6"><label class="form-label">Perusahaan</label><select class="form-select" name="company_id" required><?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($company->id); ?>" <?php if(old('company_id', $ship->company_id) == $company->id): echo 'selected'; endif; ?>><?php echo e($company->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                <div class="col-md-6"><label class="form-label">Jenis Kapal</label><select class="form-select" name="ship_type_id" required><?php $__currentLoopData = $ship_types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ship_type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($ship_type->id); ?>" <?php if(old('ship_type_id', $ship->ship_type_id) == $ship_type->id): echo 'selected'; endif; ?>><?php echo e($ship_type->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                <div class="col-md-6"><label class="form-label">Kelas Kapal</label><select class="form-select" name="ship_class_id" required><?php $__currentLoopData = $ship_classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ship_class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($ship_class->id); ?>" <?php if(old('ship_class_id', $ship->ship_class_id) == $ship_class->id): echo 'selected'; endif; ?>><?php echo e($ship_class->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                <div class="col-md-3"><label class="form-label">LOA</label><input class="form-control" name="length_overall" value="<?php echo e(old('length_overall', $ship->length_overall)); ?>" required></div>
                <div class="col-md-3"><label class="form-label">Breadth</label><input class="form-control" name="breadth" value="<?php echo e(old('breadth', $ship->breadth)); ?>" required></div>
                <div class="col-md-3"><label class="form-label">Height</label><input class="form-control" name="height" value="<?php echo e(old('height', $ship->height)); ?>" required></div>
                <div class="col-md-3"><label class="form-label">Gross Tonnage</label><input class="form-control" name="gross_tonnage" value="<?php echo e(old('gross_tonnage', $ship->gross_tonnage)); ?>" required></div>
                <div class="col-md-3"><label class="form-label">Draft Kosong</label><input class="form-control" name="empty_draft" value="<?php echo e(old('empty_draft', $ship->empty_draft)); ?>"></div>
                <div class="col-md-3"><label class="form-label">Draft Muat</label><input class="form-control" name="loaded_draft" value="<?php echo e(old('loaded_draft', $ship->loaded_draft)); ?>"></div>
                <div class="col-md-3"><label class="form-label">Net Tonnage</label><input class="form-control" name="net_tonnage" value="<?php echo e(old('net_tonnage', $ship->net_tonnage)); ?>"></div>
                <div class="col-md-3"><label class="form-label">Tahun Pembuatan</label><input class="form-control" type="date" name="build_year" value="<?php echo e(old('build_year', $ship->build_year ?? null)); ?>"></div>
                <div class="col-md-6"><label class="form-label">IMO</label><input class="form-control" name="imo_number" value="<?php echo e(old('imo_number', $ship->imo_number)); ?>"></div>
                <div class="col-md-6"><label class="form-label">MMSI</label><input class="form-control" name="mmsi_number" value="<?php echo e(old('mmsi_number', $ship->mmsi_number)); ?>"></div>
                <div class="col-md-6"><label class="form-label">Call Sign</label><input class="form-control" name="call_sign" value="<?php echo e(old('call_sign', $ship->call_sign)); ?>"></div>
                <div class="col-md-6"><label class="form-label">Bendera</label><input class="form-control" name="flag" value="<?php echo e(old('flag', $ship->flag)); ?>"></div>
                <div class="col-md-6"><label class="form-label">Merek Mesin</label><input class="form-control" name="engine_brand" value="<?php echo e(old('engine_brand', $ship->engine_brand)); ?>"></div>
                <div class="col-md-6"><label class="form-label">Model Mesin</label><input class="form-control" name="engine_model" value="<?php echo e(old('engine_model', $ship->engine_model)); ?>"></div>
                <div class="col-md-4"><label class="form-label">Daya Mesin</label><input class="form-control" name="engine_power" value="<?php echo e(old('engine_power', $ship->engine_power)); ?>"></div>
                <div class="col-md-4"><label class="form-label">Tipe Mesin</label><input class="form-control" name="engine_type" value="<?php echo e(old('engine_type', $ship->engine_type)); ?>"></div>
                <div class="col-md-4"><label class="form-label">RPM Mesin</label><input class="form-control" name="engine_rpm" value="<?php echo e(old('engine_rpm', $ship->engine_rpm)); ?>"></div>
                <div class="col-md-6"><label class="form-label">Tipe BBM</label><input class="form-control" name="engine_fuel_type" value="<?php echo e(old('engine_fuel_type', $ship->engine_fuel_type)); ?>"></div>
                <div class="col-md-3"><label class="form-label">Kapasitas BBM</label><input class="form-control" name="engine_fuel_capacity" value="<?php echo e(old('engine_fuel_capacity', $ship->engine_fuel_capacity)); ?>"></div>
                <div class="col-md-3"><label class="form-label">Konsumsi BBM</label><input class="form-control" name="engine_fuel_consumption" value="<?php echo e(old('engine_fuel_consumption', $ship->engine_fuel_consumption)); ?>"></div>
            </div>
        </div>
        <div class="card-footer text-end"><button class="btn btn-primary" type="submit">Perbarui</button></div>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views\ship\edit.blade.php ENDPATH**/ ?>