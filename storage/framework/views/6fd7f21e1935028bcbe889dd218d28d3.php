<div class="card mb-3"><div class="card-body">
    <form class="customer_form row g-2 align-items-end" method="GET" action="<?php echo e(route($directory_route)); ?>">
        <div class="col-md-7"><label for="search" class="form-label">Cari Pengguna</label><input id="search" name="search" class="form-control" maxlength="100" value="<?php echo e($filters['search'] ?? ''); ?>" placeholder="<?php echo e($search_hint); ?>"></div>
        <div class="col-md-3"><label for="filter_status" class="form-label">Status</label><select id="filter_status" name="status" class="form-select"><option value="">Semua Status</option><option value="active" <?php if(($filters['status'] ?? '') === 'active'): echo 'selected'; endif; ?>>Aktif</option><option value="inactive" <?php if(($filters['status'] ?? '') === 'inactive'): echo 'selected'; endif; ?>>Tidak Aktif</option></select></div>
        <div class="col-md-2 d-flex gap-2"><button class="btn btn-primary flex-fill" type="submit">Cari</button><a class="btn btn-outline-secondary" href="<?php echo e(route($directory_route)); ?>" aria-label="Reset pencarian"><i class="ti ti-refresh" aria-hidden="true"></i></a></div>
    </form>
</div></div>
<?php /**PATH /home/snowy/projects/assi-repair/resources/views/accounts/partials/directory-filter.blade.php ENDPATH**/ ?>