<div class="surveyor-row border rounded p-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-6 col-lg-3">
            <label class="form-label small mb-1">Nama</label>
            <input class="form-control" maxlength="150" name="owner_surveyors[<?php echo e($index); ?>][name]" value="<?php echo e($row['name'] ?? ''); ?>" placeholder="Nama surveyor">
        </div>
        <div class="col-md-6 col-lg-3">
            <label class="form-label small mb-1">Perusahaan</label>
            <input class="form-control" maxlength="150" name="owner_surveyors[<?php echo e($index); ?>][company]" value="<?php echo e($row['company'] ?? ''); ?>" placeholder="Perusahaan">
        </div>
        <div class="col-md-4 col-lg-2">
            <label class="form-label small mb-1">Jabatan</label>
            <input class="form-control" maxlength="150" name="owner_surveyors[<?php echo e($index); ?>][position]" value="<?php echo e($row['position'] ?? ''); ?>" placeholder="Jabatan">
        </div>
        <div class="col-md-4 col-lg-2">
            <label class="form-label small mb-1">Email</label>
            <input class="form-control surveyor-email" type="email" maxlength="150" name="owner_surveyors[<?php echo e($index); ?>][email]" value="<?php echo e($row['email'] ?? ''); ?>" placeholder="Email">
        </div>
        <div class="col-md-4 col-lg-2">
            <label class="form-label small mb-1">Telepon</label>
            <div class="input-group">
                <input class="form-control" maxlength="30" name="owner_surveyors[<?php echo e($index); ?>][phone]" value="<?php echo e($row['phone'] ?? ''); ?>" placeholder="Telepon">
                <button type="button" class="btn btn-icon btn-outline-danger remove-surveyor" aria-label="Hapus surveyor"><i class="ti ti-trash"></i></button>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /home/snowy/projects/assi-repair/resources/views/project/partials/surveyor_row.blade.php ENDPATH**/ ?>