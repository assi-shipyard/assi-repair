@extends('layouts.app')

@section('title', 'Daftar Pengguna')
@section('body_title', 'Daftar Pengguna')

@section('buttons_beside_title')
    <a href="{{ route('employee.create') }}" class="btn btn-primary"><i class="ti ti-user-plus me-1" aria-hidden="true"></i>Buat Akun Baru</a>
@endsection

@section('content')
    @include('partials.flash')

    <div class="card employee-directory-summary mb-4">
        <div class="card-body p-4 d-flex flex-wrap align-items-center gap-3">
            <span class="avatar avatar-lg bg-primary-lt text-primary"><i class="ti ti-users fs-2" aria-hidden="true"></i></span>
            <div class="me-auto">
                <div class="text-secondary small">Pengguna Karyawan</div>
                <h2 class="mb-1">{{ $employees->count() }} Karyawan Terdaftar</h2>
                <div class="text-secondary">Kelola identitas karyawan, jabatan, dan struktur organisasi. Akun administrator terpisah dari daftar ini.</div>
            </div>
            <span class="badge bg-success-lt text-success">{{ $employees->where('status', 'active')->count() }} aktif</span>
            <span class="badge bg-secondary-lt text-secondary">{{ $employees->where('status', 'inactive')->count() }} tidak aktif</span>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Daftar Karyawan</h3></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table card-table table-vcenter table-hover" id="employee-table">
                    <thead>
                        <tr><th>Nama / NIK</th><th>Jabatan</th><th>Unit Organisasi</th><th>Manajer Langsung</th><th>Status</th><th class="w-1">Aksi</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($employees as $employee)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $employee->name }}</div>
                                    <div class="text-secondary small font-monospace">NIK {{ $employee->employee_id }}</div>
                                </td>
                                <td>{{ $employee->position?->name ?? '-' }}</td>
                                <td>{{ $employee->position?->organizational_unit?->name ?? '-' }}</td>
                                <td>{{ $employee->manager?->name ?? '-' }}</td>
                                <td class="text-nowrap"><span class="badge {{ $employee->status === 'active' ? 'bg-success-lt text-success' : 'bg-secondary-lt text-secondary' }}">{{ $employee->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}</span></td>
                                <td class="text-nowrap">
                                    <a href="{{ route('employee.show', $employee->unique_id) }}" class="btn btn-sm btn-outline-primary" aria-label="Lihat {{ $employee->name }}" title="Lihat detail"><i class="ti ti-eye" aria-hidden="true"></i></a>
                                    <a href="{{ route('employee.edit', $employee->unique_id) }}" class="btn btn-sm btn-outline-secondary" aria-label="Ubah {{ $employee->name }}" title="Ubah pengguna"><i class="ti ti-pencil" aria-hidden="true"></i></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .employee-directory-summary { border-top: 3px solid var(--tblr-primary); }
    </style>
@endpush

@push('scripts')
    <script>
        $(function () {
            $('#employee-table').DataTable({
                autoWidth: false,
                responsive: true,
                columnDefs: [{ orderable: false, targets: -1 }],
                language: {
                    emptyTable: 'Belum ada karyawan terdaftar.',
                    info: 'Menampilkan _START_ hingga _END_ dari _TOTAL_ data',
                    infoEmpty: 'Menampilkan 0 hingga 0 dari 0 data',
                    infoFiltered: '(disaring dari _MAX_ total data)',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    search: 'Cari:',
                    searchPlaceholder: 'Nama, NIK, jabatan, atau unit',
                    zeroRecords: 'Tidak ada pengguna yang cocok dengan pencarian.',
                    aria: {
                        orderable: 'Aktifkan untuk mengurutkan',
                        orderableReverse: 'Aktifkan untuk membalik urutan',
                        orderableRemove: 'Aktifkan untuk menghapus pengurutan',
                        paginate: { first: 'Pertama', last: 'Terakhir', next: 'Selanjutnya', previous: 'Sebelumnya' },
                    },
                    paginate: { first: 'Pertama', last: 'Terakhir', next: 'Selanjutnya', previous: 'Sebelumnya' },
                },
            });
        });
    </script>
@endpush
