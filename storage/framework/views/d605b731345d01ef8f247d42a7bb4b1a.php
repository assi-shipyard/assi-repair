<?php $__env->startSection('title', 'Data Jabatan'); ?>
<?php $__env->startSection('body_title', 'Data Jabatan'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('position.create')); ?>" class="btn btn-primary"><i class="ti ti-plus me-1" aria-hidden="true"></i>Tambah Jabatan</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card position-directory-summary mb-4">
        <div class="card-body p-4 d-flex flex-wrap align-items-center gap-3">
            <span class="avatar avatar-lg bg-primary-lt text-primary"><i class="ti ti-briefcase fs-2" aria-hidden="true"></i></span>
            <div class="me-auto">
                <div class="text-secondary small">Struktur Jabatan</div>
                <h2 class="mb-1"><?php echo e($positions->count()); ?> Jabatan Terdaftar</h2>
                <div class="text-secondary">Kelola jabatan, kategori, dan unit organisasi yang menaunginya.</div>
            </div>
            <span class="badge bg-secondary-lt text-secondary"><?php echo e($positions->pluck('organizational_unit_id')->filter()->unique()->count()); ?> unit organisasi</span>
            <span class="badge bg-primary-lt text-primary"><?php echo e($positions->where('is_head_position', true)->count()); ?> jabatan kepala</span>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Jabatan</h3>
        </div>
        <div class="card-body">

			<div class="table-responsive">
				<table class="table card-table table-vcenter table-hover datatable" id="position-table">
					<thead>
						<tr>
							<th>Nama Jabatan</th>
							<th>Kategori</th>
							<th>Unit Organisasi</th>
							<th>Kode</th>
							<th class="w-1">Aksi</th>
						</tr>
					</thead>
					<tbody>
						<?php $__currentLoopData = $positions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $position): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
							<tr>
								<td>
                                    <div class="fw-semibold"><?php echo e($position->name); ?></div>
                                    <?php if($position->is_head_position): ?>
                                        <span class="badge bg-primary-lt text-primary mt-1">Jabatan kepala</span>
                                    <?php endif; ?>
                                </td>
								<td><?php echo e($position->category_label); ?></td>
								<td><?php echo e($position->organizational_unit?->name ?? '-'); ?></td>
								<td class="font-monospace"><?php echo e($position->code ?? '-'); ?></td>
								<td class="text-nowrap">
									<a href="<?php echo e(route('position.show', $position->unique_id ?? $position->id)); ?>" class="btn btn-sm btn-outline-primary" aria-label="Lihat <?php echo e($position->name); ?>" title="Lihat detail"><i class="ti ti-eye" aria-hidden="true"></i></a>
									<a href="<?php echo e(route('position.edit', $position->unique_id ?? $position->id)); ?>" class="btn btn-sm btn-outline-secondary" aria-label="Ubah <?php echo e($position->name); ?>" title="Ubah jabatan"><i class="ti ti-pencil" aria-hidden="true"></i></a>
								</td>
							</tr>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					</tbody>
				</table>
			</div>
		</div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        .position-directory-summary { border-top: 3px solid var(--tblr-primary); }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        $(document).ready(function() {
            $('#position-table').DataTable({
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
                    "emptyTable": "Tidak ada data jabatan yang tersedia.",
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/snowy/projects/assi-repair/resources/views/position/index.blade.php ENDPATH**/ ?>