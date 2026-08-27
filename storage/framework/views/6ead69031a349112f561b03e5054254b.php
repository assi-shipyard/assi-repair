<?php $__env->startSection('title', 'Data Unit Organisasi'); ?>
<?php $__env->startSection('body_title', 'Data Unit Organisasi'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('organizational-unit.create')); ?>" class="btn btn-primary"><i class="ti ti-building-plus me-1"></i>Tambah Unit</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card unit-directory-summary mb-4"><div class="card-body p-4 d-flex flex-wrap align-items-center gap-3"><span class="avatar avatar-lg bg-primary-lt text-primary"><i class="ti ti-sitemap fs-2"></i></span><div class="me-auto"><div class="text-secondary small">Struktur Organisasi</div><h2 class="mb-1"><?php echo e($organizational_units->count()); ?> Unit Terdaftar</h2><div class="text-secondary">Telusuri struktur organisasi berdasarkan tingkatan unit.</div></div><span class="badge bg-secondary-lt text-secondary"><?php echo e($organizational_units->where('type', 'directorate')->count()); ?> direktorat</span></div></div>
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs" role="tablist">
                <?php
                    $types = [
                        'directorate' => 'Direktorat',
                        'division' => 'Divisi',
                        'workshop' => 'Bengkel',
                        'subdivision' => 'Subdivisi',
                    ];
                ?>
                <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="nav-item" role="presentation">
                        <a href="#tabs-<?php echo e($type); ?>" class="nav-link <?php echo e($loop->first ? 'active' : ''); ?>" data-bs-toggle="tab" aria-selected="<?php echo e($loop->first ? 'true' : 'false'); ?>" role="tab" <?php echo !$loop->first ? 'tabindex="-1"' : ''; ?>><?php echo e($label); ?></a>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
        <div class="card-body p-0">
            <div class="tab-content">
                <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="tab-pane <?php echo e($loop->first ? 'active show' : ''); ?>" id="tabs-<?php echo e($type); ?>" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table card-table table-vcenter text-nowrap datatable" id="organizational-unit-table-<?php echo e($type); ?>">
                                <thead>
                                    <tr>
                                        <th class="text-center">Kode</th>
                                        <th class="text-center">Nama</th>
                                        <th class="text-center">Induk</th>
                                        <th class="text-center w-1">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $organizational_units->where('type', $type); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $organizational_unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td class="text-center"><?php echo e($organizational_unit->code ?? '-'); ?></td>
                                            <td class="text-center"><?php echo e($organizational_unit->name); ?></td>
                                            <td class="text-center"><?php echo e($organizational_unit->parent?->name ?? '-'); ?></td>
                                            <td class="text-center">
                                                <a href="<?php echo e(route('organizational-unit.show', $organizational_unit->unique_id ?? $organizational_unit->id)); ?>" class="btn btn-sm btn-outline-primary" aria-label="Lihat <?php echo e($organizational_unit->name); ?>"><i class="ti ti-eye"></i></a>
                                                <a href="<?php echo e(route('organizational-unit.edit', $organizational_unit->unique_id ?? $organizational_unit->id)); ?>" class="btn btn-sm btn-outline-secondary" aria-label="Ubah <?php echo e($organizational_unit->name); ?>"><i class="ti ti-pencil"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?><style>.unit-directory-summary { border-top: 3px solid var(--tblr-primary); }</style><?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        $(document).ready(function() {
            $('.datatable').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
                "columnDefs": [
                    { "orderable": false, "targets": -1 }
                ],
                "language": {
                    "emptyTable": "Tidak ada data unit organisasi yang tersedia.",
                    "info": "Menampilkan _START_ hingga _END_ dari _TOTAL_ data",
                    "infoEmpty": "Menampilkan 0 hingga 0 dari 0 data",
                    "infoFiltered": "(disaring dari _MAX_ total data)",
                    "lengthMenu": "Tampilkan _MENU_ data",
                    "search": "Cari:",
                    "zeroRecords": "Tidak ada data yang cocok",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Selanjutnya",
                        "previous": "Sebelumnya"
                    }
                },
            });

            // Recalculate DataTables when a tab is shown to prevent hidden columns
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().responsive.recalc();
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/organizational-unit/index.blade.php ENDPATH**/ ?>