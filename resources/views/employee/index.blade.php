@extends('layouts.app')

@section('title', 'Data Karyawan')
@section('body_title', 'Data Karyawan')

@section('buttons_beside_title')
    <a href="{{ route('employee.create') }}" class="btn btn-primary">Tambah Karyawan</a>
@endsection

@section('content')
    @include('partials.flash')

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
						@forelse ($employees as $employee)
							<tr>
								<td>{{ $employee->employee_id }}</td>
								<td>{{ $employee->name }}</td>
								<td>{{ $employee->position?->name ?? '-' }}</td>
								<td>{{ $employee->position?->organizational_unit?->name ?? '-' }}</td>
								<td class="text-center">
									@if ($employee->status == "active")
										<span class="badge bg-green text-green-fg">Aktif</span>
									@else
										<span class="badge bg-red text-red-fg">Tidak Aktif</span>
									@endif
								</td>
								<td class="text-center">
									<a href="{{ route('employee.show', $employee->id) }}" class="btn btn-sm btn-outline-primary">Lihat</a>
									<a href="{{ route('employee.edit', $employee->id) }}" class="btn btn-sm btn-outline-secondary">Ubah</a>
								</td>
							</tr>
						@empty
							<tr><td colspan="6" class="text-center text-secondary py-4">Belum ada data karyawan.</td></tr>
						@endforelse
					</tbody>
				</table>
			</div>
		</div>
    </div>
@endsection

@push('scripts')
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
@endpush
