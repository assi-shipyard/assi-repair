@extends('layouts.app')

@section('title', 'Permohonan Docking Ditolak')
@section('body_title', 'Permohonan Docking Ditolak')

@section('buttons_beside_title')
    <a href="{{ route('docking-space-request.index') }}" class="btn btn-outline-secondary">Semua Permohonan</a>
@endsection

@section('content')
    @include('partials.flash')

    <div class="alert alert-info">
        Halaman ini menyimpan riwayat permohonan yang ditolak beserta alasan penolakan. Dokumen permohonan tetap tersimpan dan dapat diunduh.
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Riwayat penolakan <span class="badge bg-red-lt text-red ms-2">{{ $rejected_requests->count() }}</span></h3>
            <div class="card-actions">
                <form method="GET" action="{{ route('docking-space-request.rejected') }}" class="d-flex gap-2">
                    <select name="stage" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua tahap</option>
                        <option value="engineering" @selected($stage === 'engineering')>Ditolak Engineering</option>
                        <option value="production" @selected($stage === 'production')>Ditolak Produksi</option>
                    </select>
                </form>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Kapal</th>
                        <th>Pemilik</th>
                        <th>Jadwal Diminta</th>
                        <th>Ditolak Oleh</th>
                        <th>Alasan</th>
                        <th>Dokumen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rejected_requests as $item)
                        <tr>
                            <td class="fw-medium">{{ $item->ship?->name ?? '-' }}</td>
                            <td>{{ $item->ship?->company?->name ?? '-' }}</td>
                            <td>
                                <div>{{ $item->requested_start_at?->tanggal_waktu_indonesia() }}</div>
                                <div class="text-secondary small">s/d {{ $item->requested_end_at?->tanggal_waktu_indonesia() ?? '-' }}</div>
                                <div class="text-secondary small">{{ $item->requested_docking_space?->name ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-red-lt text-red">{{ $item->rejection_stage === 'production' ? 'Produksi' : 'Engineering' }}</span>
                                <div class="small mt-1">{{ $item->reviewer?->employee_id ?? '-' }}</div>
                                <div class="text-secondary small">{{ $item->reviewed_at?->tanggal_waktu_indonesia() ?? '-' }}</div>
                            </td>
                            <td class="text-wrap" style="min-width: 14rem;">{{ $item->rejection_reason ?? '-' }}</td>
                            <td>
                                @forelse ($item->documents as $document)
                                    <div class="small">
                                        <a href="{{ route('docking-space-request.documents.download', [$item->unique_id, $document->unique_id]) }}">{{ $document->document_name }}</a>
                                    </div>
                                @empty
                                    <span class="text-secondary">-</span>
                                @endforelse
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary py-4">Belum ada permohonan yang ditolak.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
