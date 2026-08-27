@extends('layouts.app')

@section('title', 'Data Unit Organisasi')
@section('body_title', 'Data Unit Organisasi')

@section('buttons_beside_title')
    <a href="{{ route('organizational-unit.create') }}" class="btn btn-primary"><i class="ti ti-building-plus me-1"></i>Tambah Unit</a>
@endsection

@section('content')
    @include('partials.flash')

    <div class="card unit-directory-summary mb-4"><div class="card-body p-4 d-flex flex-wrap align-items-center gap-3"><span class="avatar avatar-lg bg-primary-lt text-primary"><i class="ti ti-sitemap fs-2"></i></span><div class="me-auto"><div class="text-secondary small">Struktur Organisasi</div><h2 class="mb-1">{{ $organizational_units->count() }} Unit Terdaftar</h2><div class="text-secondary">Telusuri struktur organisasi berdasarkan tingkatan unit.</div></div><span class="badge bg-secondary-lt text-secondary">{{ $organizational_units->where('type', 'directorate')->count() }} direktorat</span></div></div>
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs" role="tablist">
                @php
                    $types = [
                        'directorate' => 'Direktorat',
                        'division' => 'Divisi',
                        'workshop' => 'Bengkel',
                        'subdivision' => 'Subdivisi',
                    ];
                @endphp
                @foreach($types as $type => $label)
                    <li class="nav-item" role="presentation">
                        <a href="#tabs-{{ $type }}" class="nav-link {{ $loop->first ? 'active' : '' }}" data-bs-toggle="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}" role="tab" {!! !$loop->first ? 'tabindex="-1"' : '' !!}>{{ $label }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="card-body p-0">
            <div class="tab-content">
                @foreach($types as $type => $label)
                    <div class="tab-pane {{ $loop->first ? 'active show' : '' }}" id="tabs-{{ $type }}" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table card-table table-vcenter text-nowrap datatable" id="organizational-unit-table-{{ $type }}">
                                <thead>
                                    <tr>
                                        <th class="text-center">Kode</th>
                                        <th class="text-center">Nama</th>
                                        <th class="text-center">Induk</th>
                                        <th class="text-center w-1">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($organizational_units->where('type', $type) as $organizational_unit)
                                        <tr>
                                            <td class="text-center">{{ $organizational_unit->code ?? '-' }}</td>
                                            <td class="text-center">{{ $organizational_unit->name }}</td>
                                            <td class="text-center">{{ $organizational_unit->parent?->name ?? '-' }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('organizational-unit.show', $organizational_unit->unique_id ?? $organizational_unit->id) }}" class="btn btn-sm btn-outline-primary" aria-label="Lihat {{ $organizational_unit->name }}"><i class="ti ti-eye"></i></a>
                                                <a href="{{ route('organizational-unit.edit', $organizational_unit->unique_id ?? $organizational_unit->id) }}" class="btn btn-sm btn-outline-secondary" aria-label="Ubah {{ $organizational_unit->name }}"><i class="ti ti-pencil"></i></a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@push('styles')<style>.unit-directory-summary { border-top: 3px solid var(--tblr-primary); }</style>@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.datatable').DataTable({
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
                    "emptyTable": "Tidak ada data unit organisasi yang tersedia.",
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

            // Recalculate DataTables when a tab is shown to prevent hidden columns
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().responsive.recalc();
            });
        });
    </script>
@endpush
