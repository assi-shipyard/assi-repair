<?php $__env->startSection('title', 'Data Perusahaan'); ?>
<?php $__env->startSection('body_title', 'Data Perusahaan'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('company.create')); ?>" class="btn btn-primary">Tambah Perusahaan</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <style>
        .directory-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem;
            border-radius: 1rem;
            background: linear-gradient(135deg, rgba(12, 74, 110, 0.08), rgba(8, 145, 178, 0.14));
            border: 1px solid rgba(8, 145, 178, 0.15);
        }

        .directory-search {
            position: relative;
            flex: 1 1 24rem;
        }

        .directory-search .ti {
            position: absolute;
            top: 50%;
            left: 1rem;
            transform: translateY(-50%);
            color: var(--tblr-secondary);
        }

        .directory-search-input {
            padding-left: 2.75rem;
            border-radius: 999px;
        }

        .directory-summary {
            display: flex;
            gap: .75rem;
            flex-wrap: wrap;
        }

        .directory-pill {
            min-width: 10rem;
            padding: .85rem 1rem;
            border-radius: .9rem;
            background-color: rgba(255, 255, 255, 0.72);
            border: 1px solid rgba(15, 23, 42, 0.06);
        }

        .directory-pill-label {
            display: block;
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--tblr-secondary);
        }

        .directory-pill-value {
            display: block;
            margin-top: .2rem;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--tblr-dark);
        }

        .directory-card {
            height: 100%;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 1rem;
            box-shadow: 0 1rem 2.5rem -1.75rem rgba(15, 23, 42, 0.45);
        }

        .directory-card .card-body {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .directory-card-meta {
            display: grid;
            gap: .75rem;
        }

        .directory-card-meta-item {
            padding: .85rem 1rem;
            border-radius: .85rem;
            background-color: var(--tblr-bg-surface-secondary);
        }

        .directory-card-actions {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
            margin-top: auto;
        }

        .directory-empty {
            display: none;
        }
    </style>

    <div class="d-flex flex-column gap-3">
        <div class="directory-toolbar">
            <div class="directory-search">
                <span class="ti ti-search"></span>
                <input type="search" class="form-control directory-search-input" id="company-search" placeholder="Cari nama perusahaan, telepon, atau email..." autocomplete="off">
            </div>
            <div class="directory-summary">
                <div class="directory-pill">
                    <span class="directory-pill-label">Total Perusahaan</span>
                    <span class="directory-pill-value"><?php echo e($companies->count()); ?></span>
                </div>
                <div class="directory-pill">
                    <span class="directory-pill-label">Hasil Tampil</span>
                    <span class="directory-pill-value" id="company-visible-count"><?php echo e($companies->count()); ?></span>
                </div>
            </div>
        </div>

        <div class="row g-3" id="company-card-list">
            <?php $__empty_1 = true; $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="col-12 col-md-6 col-xl-4 company-card-item" data-search="<?php echo e(strtolower(trim(implode(' ', array_filter([$company->name, $company->phone_1, $company->phone_2, $company->email]))))); ?>">
                    <div class="card directory-card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between gap-3">
                                <div>
                                    <div class="text-secondary text-uppercase small fw-bold">Perusahaan</div>
                                    <h3 class="card-title mb-1"><?php echo e($company->name); ?></h3>
                                </div>
                                <span class="badge bg-cyan-lt text-cyan">Aktif</span>
                            </div>

                            <div class="directory-card-meta">
                                <div class="directory-card-meta-item">
                                    <div class="text-secondary small mb-1">Telepon Utama</div>
                                    <div class="fw-semibold"><?php echo e($company->phone_1 ?? '-'); ?></div>
                                </div>
                                <div class="directory-card-meta-item">
                                    <div class="text-secondary small mb-1">Telepon Sekunder</div>
                                    <div class="fw-semibold"><?php echo e($company->phone_2 ?? '-'); ?></div>
                                </div>
                                <div class="directory-card-meta-item">
                                    <div class="text-secondary small mb-1">Email</div>
                                    <div class="fw-semibold text-break"><?php echo e($company->email ?? '-'); ?></div>
                                </div>
                            </div>

                            <div class="directory-card-actions">
                                <a href="<?php echo e(route('company.show', $company->unique_id)); ?>" class="btn btn-primary btn-sm">Lihat</a>
                                <a href="<?php echo e(route('company.edit', $company->unique_id)); ?>" class="btn btn-outline-primary btn-sm">Ubah</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center text-secondary py-5">Belum ada data perusahaan.</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if($companies->isNotEmpty()): ?>
            <div class="card directory-empty" id="company-empty-state">
                <div class="card-body text-center py-5">
                    <div class="text-secondary">Tidak ada perusahaan yang cocok dengan pencarian.</div>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>


<?php $__env->startPush('scripts'); ?>
    <script>
        $(document).ready(function() {
            const $search_input = $('#company-search');
            const $cards = $('.company-card-item');
            const $empty_state = $('#company-empty-state');
            const $visible_count = $('#company-visible-count');

            $search_input.on('input', function() {
                const query = $(this).val().toString().trim().toLowerCase();
                let visible_total = 0;

                $cards.each(function() {
                    const matches = $(this).data('search').toString().includes(query);
                    $(this).toggle(matches);

                    if (matches) {
                        visible_total += 1;
                    }
                });

                $visible_count.text(visible_total);
                $empty_state.toggle(visible_total === 0);
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views\company\index.blade.php ENDPATH**/ ?>