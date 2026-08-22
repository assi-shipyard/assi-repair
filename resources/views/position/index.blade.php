@extends('layouts.app')

@section('title', 'Data Jabatan')
@section('body_title', 'Data Jabatan')

@section('buttons_beside_title')
    <a href="{{ route('position.create') }}" class="btn btn-primary">Tambah Jabatan</a>
@endsection

@section('content')
	<div class="card">
		<div class="card-body">
			@include('partials.flash')

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
						@forelse ($positions as $position)
							<tr>
								<td>{{ $position->name }}</td>
								<td>{{ $position->category_label }}</td>
								<td>{{ $position->organizational_unit?->name ?? '-' }}</td>
								<td>{{ $position->code ?? '-' }}</td>
								<td class="text-end">
									<a href="{{ route('position.show', $position->unique_id ?? $position->id) }}" class="btn btn-sm btn-outline-primary">Lihat</a>
									<a href="{{ route('position.edit', $position->unique_id ?? $position->id) }}" class="btn btn-sm btn-outline-secondary">Ubah</a>
								</td>
							</tr>
						@empty
							<tr><td colspan="5" class="text-center text-secondary py-4">Belum ada data jabatan.</td></tr>
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
@endpush
