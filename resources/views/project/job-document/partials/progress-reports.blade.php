@php
    $report_status_classes = ['draft' => 'bg-secondary-lt text-secondary', 'submitted' => 'bg-warning-lt text-warning', 'returned' => 'bg-danger-lt text-danger', 'approved' => 'bg-success-lt text-success'];
    $approved_reports = $progress_reports->where('status', 'approved');
    $last_approved = $approved_reports->first();
    $today_wib = now('Asia/Jakarta')->toDateString();
    $open_progress_modal = $errors->hasAny(['report_date', 'notes']) && old('form_kind') === 'progress_report';
@endphp

<div class="row row-cards mb-3">
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm"><div class="card-body">
            <div class="subheader">Progres Disetujui</div>
            <div class="h2 mb-1">{{ $sheet['progress'] !== null ? number_format((float) $sheet['progress'], 2, ',', '.').'%' : '-' }}</div>
            <div class="progress progress-sm"><div class="progress-bar bg-success" style="width: {{ (float) ($sheet['progress'] ?? 0) }}%"></div></div>
        </div></div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm"><div class="card-body">
            <div class="subheader">Laporan Terakhir Disetujui</div>
            <div class="h3 mb-0">{{ $last_approved?->report_date?->tanggal_indonesia() ?? '-' }}</div>
        </div></div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm"><div class="card-body">
            <div class="subheader">Jumlah Laporan Disetujui</div>
            <div class="h2 mb-0">{{ $approved_reports->count() }}</div>
        </div></div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm"><div class="card-body">
            <div class="subheader">PIMPRO / PPC</div>
            <div class="small">{{ $project->leader?->name ?? 'PIMPRO belum ditetapkan' }}</div>
            <div class="small">{{ $project->ppc?->name ?? 'PPC belum ditetapkan' }}</div>
        </div></div>
    </div>
</div>

<div class="alert alert-info">
    <div class="d-flex">
        <i class="ti ti-info-circle fs-2 me-2"></i>
        <div>
            Laporan progres dibuat harian. Isi progres kumulatif (0–100%) untuk pekerjaan paling bawah; pekerjaan induk dan total proyek dihitung otomatis.
            Laporan baru berlaku setelah disetujui <strong>PIMPRO Reparasi</strong> dan <strong>PPC</strong>. Hanya satu laporan yang boleh terbuka dalam satu waktu, dan tanggalnya harus setelah laporan sebelumnya.
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Laporan Progres Harian</h3>
        @if ($can_report_progress && ! $has_open_report)
            <div class="card-actions">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#progress-report-modal"><i class="ti ti-plus me-1"></i>Buat Laporan</button>
            </div>
        @endif
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th class="text-end">Pekerjaan Diperbarui</th>
                    <th class="text-end">Progres Proyek</th>
                    <th>Persetujuan</th>
                    <th class="w-1"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($progress_reports as $report)
                    <tr>
                        <td class="text-nowrap">{{ $report->report_date->tanggal_indonesia() }}</td>
                        <td><span class="badge {{ $report_status_classes[$report->status] ?? 'bg-secondary-lt' }}">{{ \App\Models\ProjectProgressReport::STATUS_LABELS[$report->status] ?? $report->status }}</span></td>
                        <td class="text-end">{{ $report->entries_count }}</td>
                        <td class="text-end">{{ $report->reported_progress !== null ? number_format((float) $report->reported_progress, 2, ',', '.').'%' : '-' }}</td>
                        <td class="small">
                            <div><i class="ti {{ $report->leader_approved_at ? 'ti-circle-check text-success' : 'ti-circle-dashed text-secondary' }} me-1"></i>PIMPRO{{ $report->leader_approved_by ? ': '.$report->leader_approved_by->name : '' }}</div>
                            <div><i class="ti {{ $report->ppc_approved_at ? 'ti-circle-check text-success' : 'ti-circle-dashed text-secondary' }} me-1"></i>PPC{{ $report->ppc_approved_by ? ': '.$report->ppc_approved_by->name : '' }}</div>
                        </td>
                        <td><a href="{{ route('project.progress-report.show', [$project->unique_id, $report->unique_id]) }}" class="btn btn-sm btn-outline-primary">Buka</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-secondary py-4">Belum ada laporan progres harian.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($can_report_progress && ! $has_open_report)
    @section('modal')
        @parent
        <div class="modal modal-blur fade" id="progress-report-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <form class="modal-content" id="progress-report-form" method="POST" action="{{ route('project.progress-report.store', $project->unique_id) }}" novalidate>
                    @csrf
                    <input type="hidden" name="form_kind" value="progress_report">
                    <div class="modal-header">
                        <h5 class="modal-title">Buat Laporan Progres Harian</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required" for="progress-report-date">Tanggal Laporan</label>
                            <input type="date" class="form-control @error('report_date') is-invalid @enderror" id="progress-report-date" name="report_date"
                                value="{{ old('report_date', $today_wib) }}" max="{{ $today_wib }}" min="{{ $last_approved ? $last_approved->report_date->copy()->addDay()->toDateString() : '' }}" required>
                            @error('report_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="progress-report-notes">Catatan Umum</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="progress-report-notes" name="notes" rows="3" maxlength="1000" placeholder="Cuaca, kendala, atau informasi lapangan">{{ old('notes') }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Buat Laporan</button>
                    </div>
                </form>
            </div>
        </div>
    @endsection
@endif

@push('scripts')
    <script>
        $(function () {
            const params = new URLSearchParams(window.location.search);
            if (params.get('tab') === 'progress') {
                bootstrap.Tab.getOrCreateInstance(document.querySelector('a[href="#tab_progress"]')).show();
            }

            const $form = $('#progress-report-form');
            if ($form.length) {
                $form.validate({
                    errorClass: 'is-invalid',
                    validClass: 'is-valid',
                    errorElement: 'div',
                    errorPlacement: (error, element) => error.addClass('invalid-feedback').insertAfter(element),
                    rules: { report_date: { required: true }, notes: { maxlength: 1000 } },
                    messages: {
                        report_date: { required: 'Tanggal laporan wajib diisi.' },
                        notes: { maxlength: 'Catatan maksimal 1000 karakter.' },
                    },
                });
            }

            @if ($open_progress_modal)
                bootstrap.Tab.getOrCreateInstance(document.querySelector('a[href="#tab_progress"]')).show();
                bootstrap.Modal.getOrCreateInstance(document.getElementById('progress-report-modal')).show();
            @endif
        });
    </script>
@endpush
