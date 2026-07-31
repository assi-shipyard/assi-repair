@extends('layouts.app')

@section('title', 'Data Perusahaan')
@section('body_title', 'Data Perusahaan')

@section('buttons_beside_title')
    <a href="{{ route('company.create') }}" class="btn btn-primary">Tambah Perusahaan</a>
@endsection

@section('content')
    @include('partials.flash')

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
						@foreach ($companies as $company)
							<tr>
								<td>{{ $company->name }}</td>
								<td>{{ $company->phone_1 ?? '-' }}</td>
								<td>{{ $company->phone_2 ?? '-' }}</td>
								<td>{{ $company->email ?? '-' }}</td>
								<td class="text-center">
									<a href="{{ route('company.show', $company->unique_id) }}" class="btn btn-sm btn-primary">Lihat</a>
									<a href="{{ route('company.edit', $company->unique_id) }}" class="btn btn-sm btn-success">Ubah</a>
									<a href="{{ route('company.destroy', $company->unique_id) }}" class="btn btn-sm btn-danger">Hapus</a>
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
@endpush
