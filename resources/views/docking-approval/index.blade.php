@extends('layouts.app')

@section('title', $stage_title)
@section('body_title', $stage_title)

@section('buttons_beside_title')
    <a href="{{ route('docking-space-request.index') }}" class="btn btn-outline-secondary">Semua Permohonan</a>
@endsection

@section('content')
    @include('partials.flash')

    @if ($waiting_upstream_count > 0)
        <div class="alert alert-info">
            Terdapat {{ $waiting_upstream_count }} permohonan yang masih menunggu persetujuan Engineering. Permohonan tersebut akan muncul di sini setelah disetujui.
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Menunggu persetujuan <span class="badge bg-blue-lt text-blue ms-2">{{ $pending_requests->count() }}</span></h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Kapal</th>
                        <th>Pemilik</th>
                        <th>Jadwal</th>
                        <th>Docking Space</th>
                        <th>Dokumen</th>
                        <th>Diajukan</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pending_requests as $item)
                        <tr>
                            <td class="fw-medium">{{ $item->ship?->name ?? '-' }}</td>
                            <td>{{ $item->ship?->company?->name ?? '-' }}</td>
                            <td>
                                <div>{{ $item->requested_start_at?->format('d/m/Y H:i') }}</div>
                                <div class="text-secondary small">s/d {{ $item->requested_end_at?->format('d/m/Y H:i') ?? '-' }}</div>
                            </td>
                            <td>{{ $item->requested_docking_space?->name ?? '-' }}</td>
                            <td>{{ $item->documents_count }} berkas</td>
                            <td>
                                <div>{{ $item->created_at?->format('d/m/Y H:i') }}</div>
                                <div class="text-secondary small">{{ $item->requester?->employee_id ?? '-' }}</div>
                            </td>
                            <td><a href="{{ route('docking-approval.show', [$stage, $item->unique_id]) }}" class="btn btn-sm btn-primary">Tinjau</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary py-4">Tidak ada permohonan yang menunggu persetujuan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Riwayat keputusan (50 terakhir)</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Kapal</th>
                        <th>Jadwal</th>
                        <th>Keputusan</th>
                        <th>Status saat ini</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($decided_requests as $item)
                        @php
                            $approved_at = $stage === 'engineering' ? $item->engineering_approved_at : $item->production_approved_at;
                            $approver = $stage === 'engineering' ? $item->engineering_approver : $item->production_approver;
                            $rejected_here = $item->rejection_stage === $stage && $item->request_status === 'rejected';
                        @endphp
                        <tr>
                            <td class="fw-medium">{{ $item->ship?->name ?? '-' }}</td>
                            <td>{{ $item->requested_start_at?->format('d/m/Y') }} - {{ $item->requested_end_at?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                @if ($rejected_here)
                                    <span class="badge bg-red-lt text-red">Ditolak</span>
                                    <div class="text-secondary small">{{ $item->rejection_reason }}</div>
                                @elseif ($approved_at)
                                    <span class="badge bg-success-lt text-success">Disetujui</span>
                                    <div class="text-secondary small">{{ $approver?->employee_id ?? '-' }}, {{ $approved_at->format('d/m/Y H:i') }}</div>
                                @endif
                            </td>
                            <td>{{ ['submitted' => 'Menunggu Engineering', 'engineering_approved' => 'Menunggu Produksi', 'approved' => 'Disetujui / menunggu kedatangan', 'rejected' => 'Ditolak', 'cancelled' => 'Dibatalkan'][$item->request_status] ?? $item->request_status }}</td>
                            <td><a href="{{ route('docking-approval.show', [$stage, $item->unique_id]) }}" class="btn btn-sm btn-outline-secondary">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-secondary py-4">Belum ada riwayat keputusan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
