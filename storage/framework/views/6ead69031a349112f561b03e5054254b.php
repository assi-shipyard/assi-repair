<?php $__env->startSection('title', 'Data Unit Organisasi'); ?>
<?php $__env->startSection('body_title', 'Data Unit Organisasi'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('organizational-unit.create')); ?>" class="btn btn-primary"><i class="ti ti-building-plus me-1"></i>Tambah Unit</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card unit-directory-summary mb-4"><div class="card-body p-4 d-flex flex-wrap align-items-center gap-3"><span class="avatar avatar-lg bg-primary-lt text-primary"><i class="ti ti-sitemap fs-2"></i></span><div class="me-auto"><div class="text-secondary small">Struktur Organisasi</div><h2 class="mb-1"><?php echo e($organizational_units->count()); ?> Unit Terdaftar</h2><div class="text-secondary">Divisi dan biro ditampilkan sejajar di bawah direktorat masing-masing.</div></div><span class="badge bg-secondary-lt text-secondary"><?php echo e($organizational_units->where('type', 'division')->count()); ?> divisi</span><span class="badge bg-secondary-lt text-secondary"><?php echo e($organizational_units->where('type', 'bureau')->count()); ?> biro</span></div></div>
    <div class="card">
        <div class="table-responsive">
            <table class="table card-table table-vcenter datatable" id="organizational-unit-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Jenis Unit</th>
                        <th>Nama</th>
                        <th>Unit Induk</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $organizational_units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $organizational_unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="font-monospace"><?php echo e($organizational_unit->code ?? '-'); ?></td>
                            <td><?php echo e($organizational_unit->type_label); ?></td>
                            <td class="fw-semibold"><?php echo e($organizational_unit->name); ?></td>
                            <td><?php echo e($organizational_unit->parent?->name ?? '-'); ?></td>
                            <td class="text-nowrap">
                                <a href="<?php echo e(route('organizational-unit.show', $organizational_unit->unique_id ?? $organizational_unit->id)); ?>" class="btn btn-sm btn-outline-primary" aria-label="Lihat <?php echo e($organizational_unit->name); ?>"><i class="ti ti-eye"></i></a>
                                <a href="<?php echo e(route('organizational-unit.edit', $organizational_unit->unique_id ?? $organizational_unit->id)); ?>" class="btn btn-sm btn-outline-secondary" aria-label="Ubah <?php echo e($organizational_unit->name); ?>"><i class="ti ti-pencil"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?><style>.unit-directory-summary { border-top: 3px solid var(--tblr-primary); }</style><?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        $(document).ready(function() {
            $('#organizational-unit-table').DataTable({
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

        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/organizational-unit/index.blade.php ENDPATH**/ ?>