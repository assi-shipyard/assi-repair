<?php $__env->startSection('title', 'Data Perusahaan'); ?>
<?php $__env->startSection('body_title', 'Data Perusahaan'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('company.create')); ?>" class="btn btn-primary">
        <span class="ti ti-plus me-1"></span>Tambah Perusahaan
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <style>
        .directory-stat {
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .directory-avatar {
            flex: 0 0 auto;
            width: 2.75rem;
            height: 2.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: .75rem;
            font-weight: 700;
        }

        .directory-card {
            height: 100%;
            transition: border-color .15s ease;
        }

        .directory-card:hover {
            border-color: var(--tblr-primary);
        }

        .directory-contact {
            display: flex;
            flex-direction: column;
            gap: .4rem;
            padding: .6rem .75rem;
            border-radius: .5rem;
            background-color: var(--tblr-bg-surface-secondary);
        }
    </style>

    <div class="d-flex flex-column gap-3">
        <div class="row g-3">
            <div class="col-6">
                <div class="card card-sm">
                    <div class="card-body directory-stat">
                        <span class="directory-avatar bg-cyan-lt text-cyan"><span class="ti ti-building fs-2"></span></span>
                        <div>
                            <div class="text-secondary small">Total Perusahaan</div>
                            <div class="h2 mb-0"><?php echo e($companies->count()); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card card-sm">
                    <div class="card-body directory-stat">
                        <span class="directory-avatar bg-green-lt text-green"><span class="ti ti-filter fs-2"></span></span>
                        <div>
                            <div class="text-secondary small">Hasil Tampil</div>
                            <div class="h2 mb-0" id="company-visible-count"><?php echo e($companies->count()); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-7 col-lg-8">
                        <label class="form-label" for="company-search">Pencarian</label>
                        <div class="input-icon">
                            <span class="input-icon-addon"><span class="ti ti-search"></span></span>
                            <input type="search" class="form-control" id="company-search" placeholder="Cari nama perusahaan, telepon, atau email..." autocomplete="off">
                        </div>
                    </div>
                    <div class="col-12 col-md-5 col-lg-4">
                        <div class="d-flex gap-2">
                            <select class="form-select" id="company-sort" aria-label="Urutkan">
                                <option value="asc">Nama A-Z</option>
                                <option value="desc">Nama Z-A</option>
                                <option value="ships">Kapal terbanyak</option>
                            </select>
                            <button type="button" class="btn btn-outline-secondary" id="company-reset" title="Atur ulang pencarian">
                                <span class="ti ti-filter-off"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3" id="company-card-list">
            <?php $__empty_1 = true; $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="col-12 col-md-6 col-xl-4 company-card-item"
                    data-name="<?php echo e(strtolower($company->name)); ?>"
                    data-ships="<?php echo e($company->ships_count); ?>"
                    data-search="<?php echo e(strtolower(trim(implode(' ', array_filter([$company->name, $company->phone_1, $company->phone_2, $company->email]))))); ?>">
                    <div class="card directory-card">
                        <div class="card-body d-flex flex-column gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <span class="directory-avatar bg-cyan-lt text-cyan"><?php echo e(strtoupper(mb_substr($company->name, 0, 1))); ?></span>
                                <div class="min-w-0 flex-fill">
                                    <h3 class="card-title mb-0 text-truncate"><?php echo e($company->name); ?></h3>
                                    <div class="text-secondary small">
                                        <span class="ti ti-ship me-1"></span><?php echo e($company->ships_count); ?> kapal
                                    </div>
                                </div>
                            </div>

                            <div class="directory-contact">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="ti ti-phone text-secondary"></span>
                                    <span><?php echo e($company->phone_1 ?? '-'); ?></span>
                                </div>
                                <?php if($company->phone_2): ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="ti ti-phone-plus text-secondary"></span>
                                        <span><?php echo e($company->phone_2); ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="ti ti-mail text-secondary"></span>
                                    <span class="text-break"><?php echo e($company->email ?? '-'); ?></span>
                                </div>
                            </div>

                            <div class="btn-list mt-auto">
                                <a href="<?php echo e(route('company.show', $company->unique_id)); ?>" class="btn btn-primary btn-sm">
                                    <span class="ti ti-eye me-1"></span>Lihat
                                </a>
                                <a href="<?php echo e(route('company.edit', $company->unique_id)); ?>" class="btn btn-outline-primary btn-sm">
                                    <span class="ti ti-edit me-1"></span>Ubah
                                </a>
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
            <div class="card d-none" id="company-empty-state">
                <div class="card-body text-center py-5">
                    <span class="ti ti-search-off fs-1 text-secondary"></span>
                    <div class="text-secondary mt-2">Tidak ada perusahaan yang cocok dengan pencarian.</div>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        $(document).ready(function() {
            const $search_input = $('#company-search');
            const $sort = $('#company-sort');
            const $list = $('#company-card-list');
            const $empty_state = $('#company-empty-state');
            const $visible_count = $('#company-visible-count');

            function apply_filters() {
                const query = $search_input.val().toString().trim().toLowerCase();
                let visible_total = 0;

                $('.company-card-item').each(function() {
                    const matches = $(this).attr('data-search').includes(query);
                    $(this).toggle(matches);
                    visible_total += matches ? 1 : 0;
                });

                $visible_count.text(visible_total);
                $empty_state.toggleClass('d-none', visible_total !== 0);
            }

            function apply_sort() {
                const mode = $sort.val();
                const sorted = $('.company-card-item').get().sort(function(a, b) {
                    if (mode === 'ships') {
                        return Number($(b).attr('data-ships')) - Number($(a).attr('data-ships'));
                    }

                    const result = $(a).attr('data-name').localeCompare($(b).attr('data-name'), 'id');
                    return mode === 'desc' ? -result : result;
                });
                $list.append(sorted);
            }

            $search_input.on('input', apply_filters);
            $sort.on('change', apply_sort);

            $('#company-reset').on('click', function() {
                $search_input.val('');
                $sort.val('asc');
                apply_sort();
                apply_filters();
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/company/index.blade.php ENDPATH**/ ?>