<?php $__env->startSection('title', 'Data Perusahaan'); ?>
<?php $__env->startSection('body_title', 'Data Perusahaan'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('company.create')); ?>" class="btn btn-primary">Tambah Perusahaan</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card">
		<div class="card-body">
			<div class="table-responsive">
				<table class="table card-table table-vcenter text-nowrap datatable" id="company-table">
					<thead>
						<tr>
							<th class="text-center">Nama</th>
							<th class="text-center">Telepon Utama</th>
							<th class="text-center">Telepon Sekunder</th>
							<th class="text-center">Email</th>
							<th class="text-center w-1">Aksi</th>
						</tr>
					</thead>
					<tbody>
						<?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
							<tr>
								<td><?php echo e($company->name); ?></td>
								<td><?php echo e($company->phone_1 ?? '-'); ?></td>
								<td><?php echo e($company->phone_2 ?? '-'); ?></td>
								<td><?php echo e($company->email ?? '-'); ?></td>
								<td class="text-center">
									<a href="<?php echo e(route('company.show', $company->unique_id)); ?>" class="btn btn-sm btn-primary">Lihat</a>
									<a href="<?php echo e(route('company.edit', $company->unique_id)); ?>" class="btn btn-sm btn-success">Ubah</a>
									<a href="<?php echo e(route('company.destroy', $company->unique_id)); ?>" class="btn btn-sm btn-danger">Hapus</a>
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
            $('#company-table').DataTable({
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
                    "emptyTable": "Tidak ada data perusahaan yang tersedia.",
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views\company\index.blade.php ENDPATH**/ ?>