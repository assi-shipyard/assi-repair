@extends('layouts.app')

@section('title', 'Data Subkontraktor')
@section('body_title', 'Data Subkontraktor')

@section('buttons_beside_title')
    @role('admin')
        <a href="{{ route('subcontractor.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1" aria-hidden="true"></i>Tambah Subkontraktor
        </a>
    @endrole
@endsection

@section('content')
    @include('partials.flash')

    <div class="alert alert-info">
        <div class="d-flex">
            <i class="ti ti-info-circle fs-2 me-2" aria-hidden="true"></i>
            <div>
                Data ini merupakan daftar perusahaan subkontraktor dan kontaknya, bukan penugasan pekerjaan.
                PIC internal (bengkel/unit kerja) tetap terpisah dari subkontraktor pelaksana.
                Nonaktifkan perusahaan yang tidak digunakan tanpa menghapus datanya.
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Subkontraktor</h3>
            <div class="card-actions text-secondary">{{ $subcontractors->total() }} subkontraktor</div>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Perusahaan</th>
                        <th>Nama Kontak</th>
                        <th>Telepon</th>
                        <th>Email</th>
                        <th>Status</th>
                        @role('admin')<th class="w-1">Aksi</th>@endrole
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subcontractors as $subcontractor)
                        <tr>
                            <td><span class="badge bg-blue-lt">{{ $subcontractor->code }}</span></td>
                            <td class="fw-semibold">{{ $subcontractor->name }}</td>
                            <td>{{ $subcontractor->contact_name }}</td>
                            <td>{{ $subcontractor->contact_phone ?? '-' }}</td>
                            <td>{{ $subcontractor->contact_email ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $subcontractor->is_active ? 'bg-green-lt' : 'bg-secondary-lt' }}">
                                    {{ $subcontractor->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            @role('admin')
                                <td><a href="{{ route('subcontractor.edit', $subcontractor) }}" class="btn btn-sm">Ubah</a></td>
                            @endrole
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->hasRole('admin') ? 7 : 6 }}" class="text-center text-secondary py-4">Belum ada data subkontraktor.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($subcontractors->hasPages())
            <div class="card-footer">
                {{ $subcontractors->links() }}
            </div>
        @endif
    </div>
@endsection
