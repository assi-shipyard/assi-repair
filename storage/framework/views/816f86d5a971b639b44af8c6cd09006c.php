<?php $__env->startSection('title', 'Proyek Saya'); ?>
<?php $__env->startSection('body_title', 'Proyek Saya'); ?>
<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="card mb-3"><div class="card-body">
        <h3 class="card-title">Selamat Datang, <?php echo e(auth()->user()->name); ?></h3>
        <p class="text-secondary mb-0">Hanya proyek yang ditugaskan kepada Anda yang tampil. Harga RAB terkunci dan memerlukan izin khusus serta konfirmasi kata sandi.</p>
    </div></div>
    <?php if($pending_documents->isNotEmpty()): ?>
        <div class="card mb-3">
            <div class="card-header"><h3 class="card-title">Menunggu Persetujuan Anda (<?php echo e($pending_documents->total()); ?>)</h3></div>
            <div class="list-group list-group-flush">
                <?php $__currentLoopData = $pending_documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pending_document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a class="list-group-item list-group-item-action" href="<?php echo e(route('customer.document', $pending_document)); ?>"><?php echo e($pending_document->project->project_code); ?> &middot; <?php echo e(\App\Models\customer_document::TYPE_LABELS[$pending_document->document_type]); ?> &middot; Revisi <?php echo e($pending_document->revision_no); ?></a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="card-footer"><?php echo e($pending_documents->links()); ?></div>
        </div>
    <?php endif; ?>
    <div class="mb-3 d-flex flex-wrap gap-2">
        <a class="btn <?php echo e($status === null ? 'btn-primary' : 'btn-outline-secondary'); ?>" href="<?php echo e(route('customer.index')); ?>">Semua Proyek</a>
        <?php $__currentLoopData = $status_labels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status_code => $status_label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a class="btn <?php echo e($status === $status_code ? 'btn-primary' : 'btn-outline-secondary'); ?>" href="<?php echo e(route('customer.index', ['status' => $status_code])); ?>"><?php echo e($status_label); ?></a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <div class="row g-3">
        <?php $__empty_1 = true; $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="col-md-6 col-xl-4"><div class="card h-100">
                <div class="card-body">
                    <div class="text-secondary mb-1"><?php echo e($project->project_code); ?></div>
                    <h3 class="card-title"><?php echo e($project->ship?->name ?? '-'); ?></h3>
                    <div class="mb-2"><?php echo e($status_labels[$project->status] ?? 'Status Belum Tersedia'); ?></div>
                    <div class="d-flex justify-content-between mb-1"><span>Progres</span><span><?php echo e($project->progress); ?>%</span></div>
                    <progress class="w-100" max="100" value="<?php echo e($project->progress); ?>"><?php echo e($project->progress); ?>%</progress>
                    <div class="text-secondary mt-2"><?php echo e($project->start_date_estimation?->tanggal_indonesia() ?? '-'); ?> &ndash; <?php echo e($project->end_date_estimation?->tanggal_indonesia() ?? '-'); ?></div>
                </div>
                <div class="card-footer"><a class="btn btn-outline-primary w-100" href="<?php echo e(route('customer.project', $project->unique_id)); ?>">Lihat Proyek</a></div>
            </div></div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-12"><div class="card"><div class="card-body text-secondary">Belum ada proyek yang dapat Anda akses.</div></div></div>
        <?php endif; ?>
    </div>
    <div class="mt-3"><?php echo e($projects->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/customer/index.blade.php ENDPATH**/ ?>