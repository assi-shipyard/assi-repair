<?php $__env->startSection('title', 'Data Subkontraktor'); ?>
<?php $__env->startSection('body_title', 'Data Subkontraktor'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin')): ?>
        <a href="<?php echo e(route('subcontractor.create')); ?>" class="btn btn-primary">
            <i class="ti ti-plus me-1" aria-hidden="true"></i>Tambah Subkontraktor
        </a>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="alert alert-info">
        <div class="d-flex">
            <i class="ti ti-info-circle fs-2 me-2" aria-hidden="true"></i>
            <div>
                Data ini merupakan daftar perusahaan subkontraktor dan kontaknya, bukan penugasan pekerjaan.
                PIC internal (bengkel/unit kerja) tetap terpisah dari subkontraktor pelaksana.
                Nonaktifkan perusahaan yang tidak digunakan tanpa menghapus datanya.
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Subkontraktor</h3>
            <div class="card-actions text-secondary"><?php echo e($subcontractors->total()); ?> subkontraktor</div>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Perusahaan</th>
                        <th>Nama Kontak</th>
                        <th>Telepon</th>
                        <th>Email</th>
                        <th>Status</th>
                        <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin')): ?><th class="w-1">Aksi</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $subcontractors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subcontractor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><span class="badge bg-blue-lt"><?php echo e($subcontractor->code); ?></span></td>
                            <td class="fw-semibold"><?php echo e($subcontractor->name); ?></td>
                            <td><?php echo e($subcontractor->contact_name); ?></td>
                            <td><?php echo e($subcontractor->contact_phone ?? '-'); ?></td>
                            <td><?php echo e($subcontractor->contact_email ?? '-'); ?></td>
                            <td>
                                <span class="badge <?php echo e($subcontractor->is_active ? 'bg-green-lt' : 'bg-secondary-lt'); ?>">
                                    <?php echo e($subcontractor->is_active ? 'Aktif' : 'Nonaktif'); ?>

                                </span>
                            </td>
                            <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin')): ?>
                                <td><a href="<?php echo e(route('subcontractor.edit', $subcontractor)); ?>" class="btn btn-sm">Ubah</a></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="<?php echo e(auth()->user()->hasRole('admin') ? 7 : 6); ?>" class="text-center text-secondary py-4">Belum ada data subkontraktor.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($subcontractors->hasPages()): ?>
            <div class="card-footer">
                <?php echo e($subcontractors->links()); ?>

            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/subcontractor/index.blade.php ENDPATH**/ ?>