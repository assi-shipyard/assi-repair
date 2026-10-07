<?php $__env->startSection('title', 'Data Kapal'); ?>
<?php $__env->startSection('body_title', 'Data Kapal'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('ship.create')); ?>" class="btn btn-primary">
        <span class="ti ti-plus me-1"></span>Tambah Kapal
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

        .directory-meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .5rem;
        }

        .directory-meta-item {
            padding: .5rem .75rem;
            border-radius: .5rem;
            background-color: var(--tblr-bg-surface-secondary);
            min-width: 0;
        }

        .directory-meta-item .fw-semibold {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>

    <div class="d-flex flex-column gap-3">
        <div class="row g-3">
            <div class="col-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body directory-stat">
                        <span class="directory-avatar bg-blue-lt text-blue"><span class="ti ti-ship fs-2"></span></span>
                        <div>
                            <div class="text-secondary small">Total Kapal</div>
                            <div class="h2 mb-0"><?php echo e($ships->count()); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body directory-stat">
                        <span class="directory-avatar bg-green-lt text-green"><span class="ti ti-filter fs-2"></span></span>
                        <div>
                            <div class="text-secondary small">Hasil Tampil</div>
                            <div class="h2 mb-0" id="ship-visible-count"><?php echo e($ships->count()); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body directory-stat">
                        <span class="directory-avatar bg-cyan-lt text-cyan"><span class="ti ti-building fs-2"></span></span>
                        <div>
                            <div class="text-secondary small">Pemilik</div>
                            <div class="h2 mb-0"><?php echo e($companies->count()); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body directory-stat">
                        <span class="directory-avatar bg-orange-lt text-orange"><span class="ti ti-category fs-2"></span></span>
                        <div>
                            <div class="text-secondary small">Jenis Kapal</div>
                            <div class="h2 mb-0"><?php echo e($ship_types->count()); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-lg-4">
                        <label class="form-label" for="ship-search">Pencarian</label>
                        <div class="input-icon">
                            <span class="input-icon-addon"><span class="ti ti-search"></span></span>
                            <input type="search" class="form-control" id="ship-search" placeholder="Cari nama kapal, IMO, atau tanda panggilan..." autocomplete="off">
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label" for="filter-company">Pemilik</label>
                        <select class="form-select ship-filter" id="filter-company" data-key="company">
                            <option value="">Semua pemilik</option>
                            <?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($company->id); ?>"><?php echo e($company->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-6 col-lg-2">
                        <label class="form-label" for="filter-type">Jenis Kapal</label>
                        <select class="form-select ship-filter" id="filter-type" data-key="type">
                            <option value="">Semua jenis</option>
                            <?php $__currentLoopData = $ship_types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ship_type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($ship_type->id); ?>"><?php echo e($ship_type->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-6 col-lg-2">
                        <label class="form-label" for="filter-class">Klasifikasi</label>
                        <select class="form-select ship-filter" id="filter-class" data-key="class">
                            <option value="">Semua klasifikasi</option>
                            <?php $__currentLoopData = $ship_classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ship_class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($ship_class->id); ?>"><?php echo e($ship_class->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-12 col-lg-2">
                        <div class="d-flex gap-2">
                            <select class="form-select" id="ship-sort" aria-label="Urutkan">
                                <option value="asc">Nama A-Z</option>
                                <option value="desc">Nama Z-A</option>
                            </select>
                            <button type="button" class="btn btn-outline-secondary" id="ship-reset" title="Atur ulang filter">
                                <span class="ti ti-filter-off"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3" id="ship-card-list">
            <?php $__empty_1 = true; $__currentLoopData = $ships; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ship): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php ($ship_identifier = $ship->unique_id ?? $ship->id); ?>
                <div class="col-12 col-md-6 col-xl-4 ship-card-item"
                    data-name="<?php echo e(strtolower($ship->name)); ?>"
                    data-search="<?php echo e(strtolower(trim(implode(' ', array_filter([$ship->name, $ship->imo_number, $ship->call_sign]))))); ?>"
                    data-company="<?php echo e($ship->company_id); ?>"
                    data-type="<?php echo e($ship->ship_type_id); ?>"
                    data-class="<?php echo e($ship->ship_class_id); ?>">
                    <div class="card directory-card">
                        <div class="card-body d-flex flex-column gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <span class="directory-avatar bg-blue-lt text-blue"><span class="ti ti-ship fs-2"></span></span>
                                <div class="min-w-0 flex-fill">
                                    <h3 class="card-title mb-0 text-truncate"><?php echo e($ship->name); ?></h3>
                                    <div class="text-secondary small text-truncate">
                                        <span class="ti ti-building me-1"></span><?php echo e($ship->company?->name ?? 'Pemilik belum diatur'); ?>

                                    </div>
                                </div>
                            </div>

                            <div class="directory-meta">
                                <div class="directory-meta-item">
                                    <div class="text-secondary small">Jenis Kapal</div>
                                    <div class="fw-semibold"><?php echo e($ship->type?->name ?? '-'); ?></div>
                                </div>
                                <div class="directory-meta-item">
                                    <div class="text-secondary small">Klasifikasi</div>
                                    <div class="fw-semibold"><?php echo e($ship->classification?->name ?? '-'); ?></div>
                                </div>
                            </div>

                            <div class="btn-list mt-auto">
                                <a href="<?php echo e(route('ship.show', $ship_identifier)); ?>" class="btn btn-primary btn-sm">
                                    <span class="ti ti-eye me-1"></span>Lihat
                                </a>
                                <a href="<?php echo e(route('ship.edit', $ship_identifier)); ?>" class="btn btn-outline-primary btn-sm">
                                    <span class="ti ti-edit me-1"></span>Ubah
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center text-secondary py-5">Belum ada data kapal.</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if($ships->isNotEmpty()): ?>
            <div class="card d-none" id="ship-empty-state">
                <div class="card-body text-center py-5">
                    <span class="ti ti-search-off fs-1 text-secondary"></span>
                    <div class="text-secondary mt-2">Tidak ada kapal yang cocok dengan pencarian atau filter.</div>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        $(document).ready(function() {
            const $search_input = $('#ship-search');
            const $filters = $('.ship-filter');
            const $sort = $('#ship-sort');
            const $list = $('#ship-card-list');
            const $empty_state = $('#ship-empty-state');
            const $visible_count = $('#ship-visible-count');

            function apply_filters() {
                const query = $search_input.val().toString().trim().toLowerCase();
                const selected = {};
                $filters.each(function() {
                    selected[$(this).data('key')] = $(this).val().toString();
                });

                let visible_total = 0;

                $('.ship-card-item').each(function() {
                    const $card = $(this);
                    const matches = $card.attr('data-search').includes(query)
                        && Object.keys(selected).every(function(key) {
                            return selected[key] === '' || $card.attr('data-' + key) === selected[key];
                        });

                    $card.toggle(matches);
                    visible_total += matches ? 1 : 0;
                });

                $visible_count.text(visible_total);
                $empty_state.toggleClass('d-none', visible_total !== 0);
            }

            function apply_sort() {
                const direction = $sort.val() === 'desc' ? -1 : 1;
                const sorted = $('.ship-card-item').get().sort(function(a, b) {
                    return direction * $(a).attr('data-name').localeCompare($(b).attr('data-name'), 'id');
                });
                $list.append(sorted);
            }

            $search_input.on('input', apply_filters);
            $filters.on('change', apply_filters);
            $sort.on('change', apply_sort);

            $('#ship-reset').on('click', function() {
                $search_input.val('');
                $filters.val('');
                $sort.val('asc');
                apply_sort();
                apply_filters();
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/ship/index.blade.php ENDPATH**/ ?>