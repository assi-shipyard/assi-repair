<?php $__env->startSection('title', 'Data Karyawan'); ?>
<?php $__env->startSection('body_title', 'Data Karyawan'); ?>

<?php $__env->startSection('buttons_beside_title'); ?>
    <a href="<?php echo e(route('employee.create')); ?>" class="btn btn-primary">Tambah Karyawan</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card">
		<div class="card-body">
			<div class="table-responsive">
				<table class="table card-table table-vcenter text-nowrap datatable" id="employee-table">
					<thead>
						<tr>
							<th class="text-center">Nama</th>
							<th class="text-center">NIK</th>
							<th class="text-center">Jabatan</th>
							<th class="text-center">Unit</th>
							<th class="text-center">Status</th>
							<th class="text-center w-1">Aksi</th>
						</tr>
					</thead>
					<tbody>
						<?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
							<tr>
								<td><?php echo e($employee->employee_id); ?></td>
								<td><?php echo e($employee->name); ?></td>
								<td><?php echo e($employee->position?->name ?? '-'); ?></td>
								<td><?php echo e($employee->position?->organizational_unit?->name ?? '-'); ?></td>
								<td class="text-center">
									<?php if($employee->status == "active"): ?>
										<span class="badge bg-green text-green-fg">Aktif</span>
									<?php else: ?>
										<span class="badge bg-red text-red-fg">Tidak Aktif</span>
									<?php endif; ?>
								</td>
								<td class="text-center">
									<a href="<?php echo e(route('employee.show', $employee->id)); ?>" class="btn btn-sm btn-outline-primary">Lihat</a>
									<a href="<?php echo e(route('employee.edit', $employee->id)); ?>" class="btn btn-sm btn-outline-secondary">Ubah</a>
								</td>
							</tr>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
							<tr><td colspan="6" class="text-center text-secondary py-4">Belum ada data karyawan.</td></tr>
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
            $('#employee-table').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": true,
                "responsive": true,
                "columnDefs": [
                    { "orderable": false, "targets": 5 }
                ],
                "language": {
                    "emptyTable": "Tidak ada data karyawan yang tersedia.",
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\wamp64\www\assi-repair\resources\views/employee/index.blade.php ENDPATH**/ ?>