<?php if($paginator->hasPages()): ?>
    <nav class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2" aria-label="Navigasi halaman">
        <p class="text-secondary small mb-0">
            Menampilkan <?php echo e($paginator->firstItem()); ?> hingga <?php echo e($paginator->lastItem()); ?> dari <?php echo e($paginator->total()); ?> data
        </p>
        <ul class="pagination mb-0">
            <?php if($paginator->onFirstPage()): ?>
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link">Sebelumnya</span>
                </li>
            <?php else: ?>
                <li class="page-item">
                    <a class="page-link" href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev">Sebelumnya</a>
                </li>
            <?php endif; ?>

            <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(is_string($element)): ?>
                    <li class="page-item disabled d-none d-sm-block" aria-disabled="true">
                        <span class="page-link"><?php echo e($element); ?></span>
                    </li>
                <?php endif; ?>

                <?php if(is_array($element)): ?>
                    <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($page == $paginator->currentPage()): ?>
                            <li class="page-item active d-none d-sm-block" aria-current="page">
                                <span class="page-link"><?php echo e($page); ?></span>
                            </li>
                        <?php else: ?>
                            <li class="page-item d-none d-sm-block">
                                <a class="page-link" href="<?php echo e($url); ?>" aria-label="Ke halaman <?php echo e($page); ?>"><?php echo e($page); ?></a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php if($paginator->hasMorePages()): ?>
                <li class="page-item">
                    <a class="page-link" href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next">Berikutnya</a>
                </li>
            <?php else: ?>
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link">Berikutnya</span>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?>
<?php /**PATH /home/snowy/projects/assi-repair/resources/views/accounts/partials/pagination.blade.php ENDPATH**/ ?>