<form method="POST" action="<?php echo e(route($route_name, $docking_request->unique_id)); ?>" class="d-flex flex-column gap-2 js-review-form">
    <?php echo csrf_field(); ?>
    <textarea class="form-control form-control-sm" name="notes" rows="2" maxlength="2000" placeholder="Catatan (wajib bila menolak)"></textarea>
    <button class="btn btn-sm btn-success w-100" type="submit" name="decision" value="approve"><?php echo e($approve_label); ?></button>
    <button class="btn btn-sm btn-outline-danger w-100" type="submit" name="decision" value="reject">Tolak</button>
</form>
<?php /**PATH /home/snowy/projects/assi-repair/resources/views/docking-space-request/partials/review-form.blade.php ENDPATH**/ ?>