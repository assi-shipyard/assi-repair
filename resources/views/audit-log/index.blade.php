@extends('layouts.app')

@section('title', 'Audit Log - SIREKA')
@section('body_title', 'Audit Log Sistem')

@section('content')
<div class="row row-cards mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Filter Audit Log</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('audit-log.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Event</label>
                        <input type="text" name="event_name" class="form-control" value="{{ $filters['event_name'] ?? '' }}" placeholder="contoh: http.post">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Employee ID</label>
                        <input type="text" name="employee_id" class="form-control" value="{{ $filters['employee_id'] ?? '' }}" placeholder="contoh: 123456789">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nama Route</label>
                        <input type="text" name="route_name" class="form-control" value="{{ $filters['route_name'] ?? '' }}" placeholder="contoh: project.store">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Dari</label>
                        <input type="date" name="from_date" class="form-control" value="{{ $filters['from_date'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Sampai</label>
                        <input type="date" name="to_date" class="form-control" value="{{ $filters['to_date'] ?? '' }}">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                        <a href="{{ route('audit-log.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row row-cards">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Riwayat Aktivitas</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Event</th>
                            <th>Pengguna</th>
                            <th>Route</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>IP</th>
                            <th>Payload</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($audit_logs as $audit_log)
                            <tr>
                                <td class="text-muted">{{ $audit_log->created_at?->format('d/m/Y H:i:s') }}</td>
                                <td><code>{{ $audit_log->event_name }}</code></td>
                                <td>
                                    <div>{{ $audit_log->employee_id ?? '-' }}</div>
                                    <small class="text-muted">{{ $audit_log->user?->email ?? '-' }}</small>
                                </td>
                                <td class="text-muted">{{ $audit_log->route_name ?? '-' }}</td>
                                <td>{{ $audit_log->http_method ?? '-' }}</td>
                                <td>{{ $audit_log->response_status ?? '-' }}</td>
                                <td>{{ $audit_log->ip_address ?? '-' }}</td>
                                <td>
                                    @php
                                        $payload = [
                                            'request' => $audit_log->request_payload,
                                            'event' => $audit_log->event_payload,
                                        ];
                                    @endphp
                                    <details>
                                        <summary>Lihat</summary>
                                        <pre class="small mb-0">{{ json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </details>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">Belum ada data audit log.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $audit_logs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
