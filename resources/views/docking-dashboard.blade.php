@extends('layouts.app')

@section('title', 'Dashboard Docking - SIREKA ASSI')
@section('body_title', 'Dashboard Docking')

@section('buttons_beside_title')
    <a href="{{ route('docking-space-availability') }}" class="btn btn-outline-primary">Docking Schedule (Gantt)</a>
    <a href="{{ route('docking-space-request.create') }}" class="btn btn-primary">Buat Docking Request</a>
@endsection

@section('content')
    @include('partials.flash')

    @php
        $level_class = fn (int $percent): string => $percent >= 85 ? 'bg-danger' : ($percent >= 60 ? 'bg-warning' : 'bg-success');
        $waiting_engineering = (int) ($pending_counts['submitted'] ?? 0);
        $waiting_production = (int) ($pending_counts['engineering_approved'] ?? 0);
        $alert_count = $overdue_rows->count() + $awaiting_marketing_count + $stale_requests->count();
    @endphp

    <div class="text-secondary mb-3">Ringkasan per {{ $now->tanggal_waktu_indonesia() }}</div>

    <div class="row row-cards mb-3">
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="subheader">Dok terisi</div>
                    <div class="h1 mb-1">{{ $occupied_count }} <span class="text-secondary fs-3">/ {{ $active_count }}</span></div>
                    <div class="progress progress-sm"><div class="progress-bar {{ $level_class($occupancy_percent) }}" style="width: {{ $occupancy_percent }}%"></div></div>
                    <div class="text-secondary small mt-1">{{ $free_count }} dok kosong saat ini</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="subheader">Utilisasi {{ $window_days }} hari ke depan</div>
                    <div class="h1 mb-1">{{ $average_utilization }}%</div>
                    <div class="progress progress-sm"><div class="progress-bar {{ $level_class($average_utilization) }}" style="width: {{ $average_utilization }}%"></div></div>
                    <div class="text-secondary small mt-1">Rata-rata dok aktif</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="subheader">Permohonan berjalan</div>
                    <div class="h1 mb-1">{{ $waiting_engineering + $waiting_production + $awaiting_marketing_count }}</div>
                    <div class="text-secondary small">Engineering {{ $waiting_engineering }} &middot; Produksi {{ $waiting_production }} &middot; Marketing {{ $awaiting_marketing_count }}</div>
                    <a href="{{ route('docking-space-request.index') }}" class="small">Lihat daftar</a>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="subheader">Kapal datang 7 hari</div>
                    <div class="h1 mb-1">{{ $arrivals_7_days }}</div>
                    <div class="text-secondary small">{{ $upcoming_undockings->count() }} kapal dijadwalkan keluar dok dalam 7 hari</div>
                </div>
            </div>
        </div>
    </div>

    @if ($alert_count > 0 || $inactive_spaces->isNotEmpty())
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Perlu perhatian <span class="badge bg-red-lt text-red ms-2">{{ $alert_count + $inactive_spaces->count() }}</span></h3>
            </div>
            <div class="list-group list-group-flush">
                @foreach ($overdue_rows as $row)
                    <div class="list-group-item">
                        <span class="badge bg-red-lt text-red me-2">Terlambat keluar</span>
                        <strong>{{ $row['current']->ship?->name ?? '-' }}</strong> di {{ $row['space']->name }}:
                        estimasi keluar {{ $row['current']->estimated_undock_at->tanggal_indonesia() }} ({{ $row['current']->estimated_undock_at->diffInDays($now) }} hari lewat).
                    </div>
                @endforeach
                @if ($awaiting_marketing_count > 0)
                    <div class="list-group-item">
                        <span class="badge bg-orange-lt text-orange me-2">Konfirmasi Marketing</span>
                        {{ $awaiting_marketing_count }} permohonan menunggu konfirmasi customer atas saran Produksi.
                    </div>
                @endif
                @foreach ($stale_requests as $stale)
                    <div class="list-group-item">
                        <span class="badge bg-yellow-lt text-yellow me-2">Menunggu lama</span>
                        <strong>{{ $stale->ship?->name ?? '-' }}</strong> belum diproses sejak {{ $stale->updated_at->tanggal_indonesia() }}
                        ({{ ['submitted' => 'Engineering', 'engineering_approved' => 'Produksi', 'awaiting_marketing' => 'Marketing'][$stale->request_status] ?? '-' }}).
                    </div>
                @endforeach
                @foreach ($inactive_spaces as $row)
                    <div class="list-group-item">
                        <span class="badge bg-secondary-lt text-secondary me-2">Tidak aktif</span>
                        {{ $row['space']->name }} berstatus {{ $row['space']->status }} dan tidak dihitung dalam kapasitas.
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="row row-cards">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">Status docking space</h3></div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Dok</th>
                                <th>Status</th>
                                <th>Kapal saat ini</th>
                                <th>Berikutnya</th>
                                <th style="min-width: 9rem;">Utilisasi {{ $window_days }} hari</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($space_rows as $row)
                                <tr>
                                    <td class="fw-medium">{{ $row['space']->name }}<div class="text-secondary small">{{ $row['space']->location ?? '' }}</div></td>
                                    <td>
                                        @if ($row['space']->status !== 'active')
                                            <span class="badge bg-secondary-lt text-secondary">Tidak aktif</span>
                                        @elseif ($row['is_overdue'])
                                            <span class="badge bg-red-lt text-red">Terisi - terlambat</span>
                                        @elseif ($row['current'])
                                            <span class="badge bg-blue-lt text-blue">Terisi</span>
                                        @else
                                            <span class="badge bg-green-lt text-green">Kosong</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($row['current'])
                                            <div>{{ $row['current']->ship?->name ?? '-' }}</div>
                                            <div class="text-secondary small">Keluar {{ $row['current']->estimated_undock_at?->tanggal_indonesia() ?? 'belum ditentukan' }}</div>
                                        @else
                                            <span class="text-secondary">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($row['next'])
                                            <div>{{ $row['next']->ship?->name ?? '-' }}</div>
                                            <div class="text-secondary small">{{ $row['next']->docked_at->tanggal_indonesia() }}</div>
                                        @else
                                            <span class="text-secondary">Belum ada jadwal</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress progress-sm flex-fill"><div class="progress-bar {{ $level_class($row['utilization']) }}" style="width: {{ $row['utilization'] }}%"></div></div>
                                            <span class="small">{{ $row['utilization'] }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-secondary py-4">Belum ada data docking space.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Jadwal masuk dok berikutnya</h3>
                    <div class="card-actions"><a href="{{ route('docking-space-availability') }}">Lihat Gantt</a></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead><tr><th>Kapal</th><th>Pemilik</th><th>Dok</th><th>Masuk</th><th>Keluar</th></tr></thead>
                        <tbody>
                            @forelse ($upcoming as $item)
                                <tr>
                                    <td class="fw-medium">{{ $item->ship?->name ?? '-' }}</td>
                                    <td>{{ $item->ship?->company?->name ?? '-' }}</td>
                                    <td>{{ $item->docking_space?->name ?? '-' }}</td>
                                    <td>{{ $item->docked_at->tanggal_indonesia() }}<div class="text-secondary small">{{ $now->diffInDays($item->docked_at) }} hari lagi</div></td>
                                    <td>{{ $item->estimated_undock_at?->tanggal_indonesia() ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-secondary py-4">Tidak ada kapal terjadwal masuk dok dalam {{ $window_days }} hari ke depan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">Keluar dok 7 hari ke depan</h3></div>
                <div class="list-group list-group-flush">
                    @forelse ($upcoming_undockings as $item)
                        <div class="list-group-item">
                            <div class="fw-medium">{{ $item->ship?->name ?? '-' }}</div>
                            <div class="text-secondary small">{{ $item->docking_space?->name ?? '-' }} &middot; {{ $item->estimated_undock_at->tanggal_indonesia() }}</div>
                        </div>
                    @empty
                        <div class="list-group-item text-secondary">Tidak ada kapal yang dijadwalkan keluar.</div>
                    @endforelse
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Reparasi di perairan (floating)</h3>
                    <div class="card-actions"><span class="badge bg-blue-lt text-blue">{{ $floating_active->count() }}</span></div>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($floating_active as $item)
                        <div class="list-group-item">
                            <div class="fw-medium">{{ $item->ship?->name ?? '-' }}</div>
                            <div class="text-secondary small">Sejak {{ $item->floating_started_at->tanggal_indonesia() }}</div>
                        </div>
                    @empty
                        <div class="list-group-item text-secondary">Tidak ada kapal dalam reparasi floating.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
