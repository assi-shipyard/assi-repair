<?php $__env->startSection('title', 'Data Jabatan'); ?>
<?php $__env->startSection('body_title', 'Data Jabatan'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('position.create')); ?>" class="btn btn-primary">Tambah Jabatan</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
	<div class="card">
		<div class="card-body">
			<?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

			<div class="table-responsive">
				<table class="table card-table table-vcenter text-nowrap datatable" id="position-table">
					<thead>
						<tr>
							<th class="text-center">Nama</th>
							<th class="text-center">Kategori</th>
							<th class="text-center">Unit</th>
							<th class="text-center">Kode</th>
							<th class="text-center w-1"></th>
						</tr>
					</thead>
					<tbody>
						<?php $__empty_1 = true; $__currentLoopData = $positions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $position): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
							<tr>
								<td><?php echo e($position->name); ?></td>
								<td><?php echo e($position->category_label); ?></td>
								<td><?php echo e($position->organizational_unit?->name ?? '-'); ?></td>
								<td><?php echo e($position->code ?? '-'); ?></td>
								<td class="text-end">
									<a href="<?php echo e(route('position.show', $position->unique_id ?? $position->id)); ?>" class="btn btn-sm btn-outline-primary">Lihat</a>
									<a href="<?php echo e(route('position.edit', $position->unique_id ?? $position->id)); ?>" class="btn btn-sm btn-outline-secondary">Ubah</a>
								</td>
							</tr>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
							<tr><td colspan="5" class="text-center text-secondary py-4">Belum ada data jabatan.</td></tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        $(document).ready(function() {
            // Datatable initialization
            $('#position-table').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": true,
                "responsive": true,
                "columnDefs": [
                    { "orderable": false, "targets": 4 }
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