<?php $__env->startSection('title', 'Pengaturan Notifikasi - SIREKA'); ?>
<?php $__env->startSection('body_title', 'Pengaturan Notifikasi'); ?>

<?php $__env->startSection('content'); ?>
<div class="row row-cards">
    <div class="col-12">
        <form action="<?php echo e(route('notification-settings.update')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            
            <div class="row g-3">
                <?php $__currentLoopData = $flags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $flag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="card-title"><?php echo e($flag->name); ?></h3>
                            <div class="card-actions">
                                <span class="badge bg-blue-lt"><?php echo e($flag->code); ?></span>
                            </div>
                        </div>
                        <div class="card-body overflow-auto" style="max-height: 400px;">
                            <div class="form-selectgroup form-selectgroup-boxes d-flex flex-column">
                                <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $isChecked = false;
                                        if (isset($settings[$flag->id])) {
                                            $isChecked = $settings[$flag->id]->contains('user_id', $employee->user_id);
                                        }
                                    ?>
                                    <label class="form-selectgroup-item flex-fill mb-2">
                                        <input type="checkbox" name="settings[<?php echo e($flag->id); ?>][]" value="<?php echo e($employee->user_id); ?>" class="form-selectgroup-input" <?php echo e($isChecked ? 'checked' : ''); ?>>
                                        <div class="form-selectgroup-label d-flex align-items-center p-3">
                                            <div class="me-3">
                                                <span class="form-selectgroup-check"></span>
                                            </div>
                                            <div>
                                                <div class="font-weight-medium"><?php echo e($employee->name); ?></div>
                                                <div class="text-muted small"><?php echo e($employee->position?->name ?? 'Tanpa Jabatan'); ?> - <?php echo e($employee->position?->organizational_unit?->name ?? 'Tanpa Unit'); ?></div>
                                            </div>
                                        </div>
                                    </label>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M14 4l0 4l-6 0l0 -4" /></svg>
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views\notification-setting\index.blade.php ENDPATH**/ ?>