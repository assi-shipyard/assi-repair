<?php $__env->startSection('title', 'Data Kapal'); ?>
<?php $__env->startSection('body_title', 'Data Kapal'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('ship.create')); ?>" class="btn btn-primary">Tambah Kapal</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card">
		<div class="card-body">
			<div class="table-responsive">
				<table class="table card-table table-vcenter text-nowrap datatable" id="ship-table">
					<thead>
						<tr>
							<th class="text-center">Nama</th>
							<th class="text-center">Perusahaan</th>
							<th class="text-center">Jenis</th>
							<th class="text-center">Kelas</th>
							<th class="text-center w-1">Aksi</th>
						</tr>
					</thead>
					<tbody>
						<?php $__currentLoopData = $ships; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ship): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
							<tr>
								<td class="text-center fw-semibold"><?php echo e($ship->name); ?></td>
								<td class="text-center"><?php echo e($ship->company?->name ?? '-'); ?></td>
								<td class="text-center"><?php echo e($ship->type?->name ?? '-'); ?></td>
								<td class="text-center"><?php echo e($ship->classification?->name ?? '-'); ?></td>
								<td class="text-center">
									<a href="<?php echo e(route('ship.show', $ship->unique_id)); ?>" class="btn btn-sm btn-outline-primary">Lihat</a>
									<a href="<?php echo e(route('ship.edit', $ship->unique_id)); ?>" class="btn btn-sm btn-outline-secondary">Ubah</a>
								</td>
							</tr>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
            $('#ship-table').DataTable({
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
                    "emptyTable": "Tidak ada data kapal yang tersedia.",
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views/ship/index.blade.php ENDPATH**/ ?>