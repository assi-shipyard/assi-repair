<?php $__env->startSection('title', 'Foto Pekerjaan'); ?>
<?php $__env->startSection('body_title', 'Foto Pekerjaan'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('project.job-document.workflow.show', [$project->unique_id, $document->unique_id ?? $document->id])); ?>" class="btn btn-outline-secondary">Kembali</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card mb-4">
        <div class="card-body">
            <div class="text-secondary"><?php echo e($documentTypeLabels[$document->document_type] ?? $document->document_type); ?></div>
            <h3 class="mb-0"><?php echo e($job->job_name); ?></h3>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><h3 class="card-title mb-0">Unggah Foto</h3></div>
        <div class="card-body">
            <form action="<?php echo e(route('project.job-document.workflow.job.photo.store', [$project->unique_id, $document->unique_id ?? $document->id, $job->unique_id ?? $job->id])); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="row g-3">
                    <div class="col-md-4"><input class="form-control" type="file" name="job_photo" required></div>
                    <div class="col-md-3"><input class="form-control" name="photo_category" placeholder="Kategori foto"></div>
                    <div class="col-md-3"><input class="form-control" name="caption" placeholder="Keterangan"></div>
                    <div class="col-md-2"><button class="btn btn-primary w-100" type="submit">Unggah</button></div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead><tr><th>Kategori</th><th>Keterangan</th><th>Tanggal</th><th class="w-1"></th></tr></thead>
                <tbody>
                    <?php $__empty_0 = true; $__currentLoopData = $photos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $photo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?>
                        <tr>
                            <td><?php echo e($photo->photo_category ?? '-'); ?></td>
                            <td><?php echo e($photo->caption ?? '-'); ?></td>
                            <td><?php echo e(optional($photo->taken_at)->format('d/m/Y H:i') ?? '-'); ?></td>
                            <td>
                                <form action="<?php echo e(route('project.job-document.workflow.job.photo.destroy', [$project->unique_id, $document->unique_id ?? $document->id, $job->unique_id ?? $job->id, $photo->unique_id ?? $photo->id])); ?>" method="POST" onsubmit="return confirm('Hapus foto ini?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?>
                        <tr><td colspan="4" class="text-center text-secondary py-4">Belum ada foto.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/project/job-document/photos.blade.php ENDPATH**/ ?>