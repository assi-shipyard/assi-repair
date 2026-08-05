<?php $__env->startSection('title', 'Detail Kapal'); ?>
<?php $__env->startSection('body_title', 'Detail Kapal'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('ship.edit', $ship->unique_id ?? $ship->id)); ?>" class="btn btn-outline-primary">Ubah</a>
    <a href="<?php echo e(route('ship.index')); ?>" class="btn btn-outline-secondary">Kembali</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php
        $ship_build_year = $ship->build_year ? \Illuminate\Support\Carbon::parse($ship->build_year)->format('Y') : null;
        $gross_tonnage = is_numeric($ship->gross_tonnage) ? number_format((float) $ship->gross_tonnage, 0, ',', '.') : ($ship->gross_tonnage ?? null);
        $net_tonnage = is_numeric($ship->net_tonnage) ? number_format((float) $ship->net_tonnage, 0, ',', '.') : ($ship->net_tonnage ?? null);
        $ship_identifier = $ship->unique_id ?? $ship->id;
    ?>

    <div class="row row-cards">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="mb-3">
                        <div class="text-secondary text-uppercase fw-bold" style="font-size: 11px; letter-spacing: .05em;">Ringkasan Kapal</div>
                        <h2 class="mb-1"><?php echo e($ship->name); ?></h2>
                        <div class="text-secondary"><?php echo e($ship->company?->name ?? '-'); ?></div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="card card-sm mb-0">
                                <div class="card-body p-2">
                                    <div class="text-secondary">Jenis</div>
                                    <div class="fw-semibold text-truncate"><?php echo e($ship->type?->name ?? '-'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card card-sm mb-0">
                                <div class="card-body p-2">
                                    <div class="text-secondary">Kelas</div>
                                    <div class="fw-semibold text-truncate"><?php echo e($ship->classification?->abbreviation ?? $ship->classification?->name ?? '-'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="<?php echo e(route('ship.edit', $ship_identifier)); ?>" class="btn btn-primary">Ubah Data Kapal</a>
                        <a href="<?php echo e(route('ship.index')); ?>" class="btn btn-outline-secondary">Kembali ke Daftar</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title mb-0">Identitas & Registrasi</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <tbody>
                            <tr>
                                <td class="w-25 text-secondary">ID Kapal</td>
                                <td><?php echo e($ship_identifier); ?></td>
                                <td class="w-25 text-secondary">Tahun Pembuatan</td>
                                <td><?php echo e($ship_build_year ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <td class="text-secondary">IMO Number</td>
                                <td><?php echo e($ship->imo_number ?? '-'); ?></td>
                                <td class="text-secondary">MMSI Number</td>
                                <td><?php echo e($ship->mmsi_number ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <td class="text-secondary">Call Sign</td>
                                <td><?php echo e($ship->call_sign ?? '-'); ?></td>
                                <td class="text-secondary">Bendera</td>
                                <td><?php echo e($ship->flag ?? '-'); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title mb-0">Dimensi & Kapasitas</h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6 col-md-3">
                            <div class="text-secondary small">LOA</div>
                            <div class="h3 mb-0"><?php echo e($ship->length_overall ?? '-'); ?></div>
                            <div class="text-secondary small">meter</div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="text-secondary small">Breadth</div>
                            <div class="h3 mb-0"><?php echo e($ship->breadth ?? '-'); ?></div>
                            <div class="text-secondary small">meter</div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="text-secondary small">Height</div>
                            <div class="h3 mb-0"><?php echo e($ship->height ?? '-'); ?></div>
                            <div class="text-secondary small">meter</div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="text-secondary small">Draft Kosong</div>
                            <div class="h3 mb-0"><?php echo e($ship->empty_draft ?? '-'); ?></div>
                            <div class="text-secondary small">meter</div>
                        </div>
                    </div>
                    <hr class="my-3">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="text-secondary small">Gross Tonnage</div>
                            <div class="fw-semibold"><?php echo e($gross_tonnage ?? '-'); ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-secondary small">Net Tonnage</div>
                            <div class="fw-semibold"><?php echo e($net_tonnage ?? '-'); ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-secondary small">Draft Muat</div>
                            <div class="fw-semibold"><?php echo e($ship->loaded_draft ?? '-'); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">Mesin Utama</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <tbody>
                            <tr>
                                <td class="w-25 text-secondary">Merek</td>
                                <td><?php echo e($ship->engine_brand ?? '-'); ?></td>
                                <td class="w-25 text-secondary">Model</td>
                                <td><?php echo e($ship->engine_model ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <td class="text-secondary">Daya</td>
                                <td><?php echo e($ship->engine_power ?? '-'); ?></td>
                                <td class="text-secondary">RPM</td>
                                <td><?php echo e($ship->engine_rpm ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <td class="text-secondary">Tipe Mesin</td>
                                <td><?php echo e($ship->engine_type ?? '-'); ?></td>
                                <td class="text-secondary">Tipe BBM</td>
                                <td><?php echo e($ship->engine_fuel_type ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <td class="text-secondary">Kapasitas BBM</td>
                                <td><?php echo e($ship->engine_fuel_capacity ?? '-'); ?></td>
                                <td class="text-secondary">Konsumsi BBM</td>
                                <td><?php echo e($ship->engine_fuel_consumption ?? '-'); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views/ship/show.blade.php ENDPATH**/ ?>