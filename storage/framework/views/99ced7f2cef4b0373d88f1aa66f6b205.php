
<button type="button" class="<?php echo e($trigger_class ?? 'btn btn-outline-danger'); ?> js-cancel-request"
    data-bs-toggle="modal" data-bs-target="#cancel-request-modal"
    data-action="<?php echo e(route('docking-space-request.cancel', $docking_request->unique_id)); ?>"
    data-request-id="<?php echo e($docking_request->unique_id); ?>"
    data-ship="<?php echo e($docking_request->ship?->name ?? '-'); ?>"
    data-project="<?php echo e($docking_request->project?->project_code ?? 'Tanpa proyek'); ?>"
    data-schedule="<?php echo e($docking_request->requested_start_at?->tanggal_waktu_indonesia() ?? '-'); ?> s/d <?php echo e($docking_request->requested_end_at?->tanggal_waktu_indonesia() ?? '-'); ?>"
    data-dock="<?php echo e($docking_request->recommended_docking_space?->name ?? $docking_request->requested_docking_space?->name ?? '-'); ?>"
    data-status="<?php echo e($docking_request->status_label()); ?>"
    data-approved="<?php echo e($docking_request->request_status === 'approved' ? '1' : '0'); ?>">
    <i class="ti ti-x me-1"></i><?php echo e($trigger_label ?? 'Batalkan permohonan'); ?>

</button>
<?php /**PATH /home/snowy/projects/assi-repair/resources/views/docking-space-request/partials/cancel-trigger.blade.php ENDPATH**/ ?>