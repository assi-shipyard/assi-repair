<?php $__env->startSection('title', 'Tipe Notifikasi - SIREKA'); ?>
<?php $__env->startSection('body_title', 'Data Tipe Notifikasi (Flags)'); ?>

<?php $__env->startSection('content'); ?>
<div class="row row-cards">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Tipe Notifikasi</h3>
                <div class="card-actions">
                    <a href="<?php echo e(route('notification-flag.create')); ?>" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                        Tambah Tipe
                    </a>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Kode Event</th>
                            <th>Nama Notifikasi</th>
                            <th>Deskripsi</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $flags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $flag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><code><?php echo e($flag->code); ?></code></td>
                            <td><?php echo e($flag->name); ?></td>
                            <td class="text-muted"><?php echo e($flag->description ?? '-'); ?></td>
                            <td>
                                <div class="btn-list flex-nowrap">
                                    <a href="<?php echo e(route('notification-flag.edit', $flag->id)); ?>" class="btn btn-white btn-sm">
                                        Edit
                                    </a>
                                    <form action="<?php echo e(route('notification-flag.destroy', $flag->id)); ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tipe notifikasi ini? Pengaturan yang terkait akan ikut terhapus.')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">Belum ada tipe notifikasi.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views\notification-flag\index.blade.php ENDPATH**/ ?>