@extends('layouts.app')

@section('title', 'Data Kapal')
@section('body_title', 'Data Kapal')

@section('buttons_beside_title')
    <a href="{{ route('ship.create') }}" class="btn btn-primary">Tambah Kapal</a>
@endsection

@section('content')
    @include('partials.flash')

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
						@foreach ($ships as $ship)
                            @php($ship_identifier = $ship->unique_id ?? $ship->id)
							<tr>
								<td class="text-center fw-semibold">{{ $ship->name }}</td>
								<td class="text-center">{{ $ship->company?->name ?? '-' }}</td>
								<td class="text-center">{{ $ship->type?->name ?? '-' }}</td>
								<td class="text-center">{{ $ship->classification?->name ?? '-' }}</td>
								<td class="text-center">
                                    <a href="{{ route('ship.show', $ship_identifier) }}" class="btn btn-sm btn-outline-primary">Lihat</a>
                                    <a href="{{ route('ship.edit', $ship_identifier) }}" class="btn btn-sm btn-outline-secondary">Ubah</a>
								</td>
							</tr>
						@endforeach
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
@endpush
