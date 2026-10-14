@extends('layouts.app')

@section('title', 'Laporan Progres Harian')
@section('body_title', 'Laporan Progres Harian')

@php
    $report_status_classes = ['draft' => 'bg-secondary-lt text-secondary', 'submitted' => 'bg-warning-lt text-warning', 'returned' => 'bg-danger-lt text-danger', 'approved' => 'bg-success-lt text-success'];
    $report_ref = [$project->unique_id, $report->unique_id];
    $can_sign = $report->status === 'submitted' && (($approver_slots['leader'] && $report->leader_approved_at === null) || ($approver_slots['ppc'] && $report->ppc_approved_at === null));
    $can_return = $report->status === 'submitted' && in_array(true, $approver_slots, true);
    $fmt_pct = fn ($value): string => $value === null ? '-' : number_format((float) $value, 2, ',', '.').'%';
    $fmt_time = fn ($time): string => $time ? $time->timezone('Asia/Jakarta')->tanggal_waktu_indonesia().' WIB' : '-';
    $grouped_rows = collect($rows)->groupBy('group');
    $listed_lineages = collect($rows)->map(fn (array $row): string => $row['job']->lineage_uuid)->flip();
    $orphan_entries = $entries->reject(fn ($entry, $lineage): bool => $listed_lineages->has($lineage));
@endphp

@section('buttons_beside_title')
    <a href="{{ route('project.job-document.workflow.show', [$project->unique_id, $document->unique_id, 'tab' => 'progress']) }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
    @include('partials.flash')

    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <div class="text-secondary">{{ $project->project_code }} &middot; {{ $project->ship?->name ?? '-' }}</div>
                    <h2 class="mb-1">{{ $report->report_date->tanggal_indonesia() }}
                        <span class="badge {{ $report_status_classes[$report->status] ?? 'bg-secondary-lt' }} ms-1">{{ \App\Models\ProjectProgressReport::STATUS_LABELS[$report->status] ?? $report->status }}</span>
                    </h2>
                    <div class="text-secondary small">Dibuat oleh {{ $report->created_by?->name ?? '-' }} &middot; Satisfaction Notes Rev. {{ $document->revision_no }}</div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @if ($can_sign)
                        <form method="POST" action="{{ route('project.progress-report.approve', $report_ref) }}" onsubmit="return confirm('Setujui laporan progres ini?')">
                            @csrf <button type="submit" class="btn btn-success"><i class="ti ti-check me-1"></i>Setujui</button>
                        </form>
                    @endif
                    @if ($can_return)
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#return-report-modal"><i class="ti ti-arrow-back-up me-1"></i>Kembalikan</button>
                    @endif
                    @if ($can_edit)
                        <form method="POST" action="{{ route('project.progress-report.destroy', $report_ref) }}" onsubmit="return confirm('Hapus laporan progres ini?')">
                            @csrf @method('DELETE') <button type="submit" class="btn btn-outline-danger"><i class="ti ti-trash me-1"></i>Hapus</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-sm-6 col-lg-3">
                    <div class="text-secondary small">{{ $report->status === 'approved' ? 'Progres Proyek Saat Disetujui' : 'Progres Proyek Saat Ini' }}</div>
                    <div class="fw-bold">{{ $fmt_pct($report->status === 'approved' ? $report->reported_progress : $project->progress) }}</div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="text-secondary small">Diajukan</div>
                    <div>{{ $fmt_time($report->submitted_at) }}</div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="text-secondary small">PIMPRO Reparasi ({{ $project->leader?->name ?? 'belum ditetapkan' }})</div>
                    <div>@if ($report->leader_approved_at)<i class="ti ti-circle-check text-success me-1"></i>{{ $fmt_time($report->leader_approved_at) }}@else<span class="text-secondary">Belum menyetujui</span>@endif</div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="text-secondary small">PPC ({{ $project->ppc?->name ?? 'belum ditetapkan' }})</div>
                    <div>@if ($report->ppc_approved_at)<i class="ti ti-circle-check text-success me-1"></i>{{ $fmt_time($report->ppc_approved_at) }}@else<span class="text-secondary">Belum menyetujui</span>@endif</div>
                </div>
            </div>

            @if ($report->status === 'returned' && $report->return_reason)
                <div class="alert alert-danger mt-3 mb-0"><strong>Dikembalikan:</strong> {{ $report->return_reason }} <span class="text-secondary small">({{ $fmt_time($report->returned_at) }})</span></div>
            @endif
        </div>
    </div>

    @if ($can_edit)
        <div class="alert alert-info">
            <div class="d-flex">
                <i class="ti ti-info-circle fs-2 me-2"></i>
                <div>
                    Isi <strong>progres kumulatif</strong> per pekerjaan (bukan penambahan harian). Kosongkan atau biarkan sama jika tidak ada perubahan.
                    Jika progres turun, catatan wajib diisi. Klik <strong>Simpan</strong> untuk menyimpan sementara atau <strong>Ajukan</strong> untuk meminta persetujuan PIMPRO Reparasi dan PPC.
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('project.progress-report.update', $report_ref) }}" id="progress-entries-form" novalidate>
        @csrf @method('PUT')
        <div class="card">
            <div class="card-header flex-wrap gap-2">
                <h3 class="card-title">Progres Pekerjaan <span class="badge bg-primary-lt ms-1">{{ $entries->count() }} diperbarui</span></h3>
                <div class="card-actions ms-auto">
                    <input type="search" class="form-control form-control-sm" id="progress-search" placeholder="Cari pekerjaan..." aria-label="Cari pekerjaan">
                </div>
            </div>
            @error('entries')<div class="alert alert-danger m-3 mb-0">{{ $message }}</div>@enderror
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-sm">
                    <thead>
                        <tr>
                            <th class="w-1">No</th>
                            <th style="min-width: 16rem">Pekerjaan</th>
                            <th class="text-end">Bobot</th>
                            <th class="text-end">Sebelumnya</th>
                            <th class="text-end" style="min-width: 8rem">Progres (%)</th>
                            <th style="min-width: 14rem">Catatan</th>
                        </tr>
                    </thead>
                    @forelse ($grouped_rows as $group_label => $group_rows)
                        <tbody>
                            <tr class="bg-light"><td colspan="6" class="fw-bold text-uppercase small">{{ $group_label }}</td></tr>
                            @foreach ($group_rows as $row)
                                @php
                                    $lineage = $row['job']->lineage_uuid;
                                    $entry = $entries->get($lineage);
                                    $previous = $entry?->previous_percent ?? $row['previous'];
                                    $value = old('entries.'.$lineage.'.progress_percent', $entry ? (string) (float) $entry->progress_percent : '');
                                    $note = old('entries.'.$lineage.'.notes', $entry?->notes ?? '');
                                    $field_error = $errors->first('entries.'.$lineage.'.progress_percent') ?: $errors->first('entries.'.$lineage.'.notes');
                                @endphp
                                <tr class="progress-row {{ $entry ? 'table-active' : '' }}" data-search="{{ mb_strtolower($row['parents'].' '.$row['job']->job_name) }}">
                                    <td class="text-secondary text-nowrap small">{{ $row['number'] }}</td>
                                    <td>
                                        @if ($row['parents'] !== '')<div class="small text-secondary">{{ $row['parents'] }}</div>@endif
                                        <div class="fw-semibold">{{ $row['job']->job_name }}</div>
                                        @if ($row['job']->est_start_date || $row['job']->est_finish_date)
                                            <div class="small text-secondary"><i class="ti ti-calendar me-1"></i>{{ $row['job']->est_start_date?->tanggal_indonesia() ?? '-' }} – {{ $row['job']->est_finish_date?->tanggal_indonesia() ?? '-' }}</div>
                                        @endif
                                    </td>
                                    <td class="text-end text-nowrap">{{ $fmt_pct($entry?->weight_percent ?? $row['weight']) }}</td>
                                    <td class="text-end text-nowrap">{{ $fmt_pct($previous) }}</td>
                                    <td class="text-end">
                                        @if ($can_edit)
                                            <input type="number" class="form-control form-control-sm text-end progress-input @if ($field_error) is-invalid @endif" name="entries[{{ $lineage }}][progress_percent]"
                                                value="{{ $value }}" min="0" max="100" step="0.01" placeholder="{{ (float) $previous }}" data-previous="{{ (float) $previous }}" aria-label="Progres {{ $row['job']->job_name }}">
                                        @else
                                            <span class="fw-semibold">{{ $entry ? $fmt_pct($entry->progress_percent) : '—' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($can_edit)
                                            <input type="text" class="form-control form-control-sm progress-note @if ($field_error) is-invalid @endif" name="entries[{{ $lineage }}][notes]" value="{{ $note }}" maxlength="500" placeholder="Opsional" aria-label="Catatan {{ $row['job']->job_name }}">
                                            @if ($field_error)<div class="invalid-feedback d-block">{{ $field_error }}</div>@endif
                                        @else
                                            <span class="small">{{ $entry?->notes ?? '' }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    @empty
                        <tbody><tr><td colspan="6" class="text-center text-secondary py-4">Satisfaction Notes belum memiliki pekerjaan.</td></tr></tbody>
                    @endforelse
                    @if ($orphan_entries->isNotEmpty())
                        <tbody>
                            <tr class="bg-light"><td colspan="6" class="fw-bold text-uppercase small">Pekerjaan yang sudah diubah atau dihapus</td></tr>
                            @foreach ($orphan_entries as $entry)
                                <tr>
                                    <td></td>
                                    <td class="text-secondary">{{ $entry->job_name }}</td>
                                    <td class="text-end">{{ $fmt_pct($entry->weight_percent) }}</td>
                                    <td class="text-end">{{ $fmt_pct($entry->previous_percent) }}</td>
                                    <td class="text-end fw-semibold">{{ $fmt_pct($entry->progress_percent) }}</td>
                                    <td class="small">{{ $entry->notes }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    @endif
                </table>
            </div>
            <div class="card-body border-top">
                <label class="form-label" for="progress-report-notes">Catatan Umum</label>
                @if ($can_edit)
                    <textarea class="form-control @error('notes') is-invalid @enderror" id="progress-report-notes" name="notes" rows="2" maxlength="1000">{{ old('notes', $report->notes) }}</textarea>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                @else
                    <div>{{ $report->notes ?: '-' }}</div>
                @endif
            </div>
            @if ($can_edit)
                <div class="card-footer d-flex justify-content-end flex-wrap gap-2">
                    <button type="submit" name="action" value="save" class="btn btn-outline-primary"><i class="ti ti-device-floppy me-1"></i>Simpan</button>
                    <button type="submit" name="action" value="submit" class="btn btn-primary" onclick="return confirm('Ajukan laporan ini untuk disetujui PIMPRO Reparasi dan PPC?')"><i class="ti ti-send me-1"></i>Ajukan</button>
                </div>
            @endif
        </div>
    </form>
@endsection

@section('modal')
    @if ($can_return)
        <div class="modal modal-blur fade" id="return-report-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <form class="modal-content" id="return-report-form" method="POST" action="{{ route('project.progress-report.return', $report_ref) }}" novalidate>
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Kembalikan Laporan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label required" for="return-reason">Alasan Pengembalian</label>
                        <textarea class="form-control @error('reason') is-invalid @enderror" id="return-reason" name="reason" rows="3" maxlength="1000" required>{{ old('reason') }}</textarea>
                        @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Kembalikan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        $(function () {
            const validation_options = {
                errorClass: 'is-invalid',
                validClass: 'is-valid',
                errorElement: 'div',
                errorPlacement: (error, element) => error.addClass('invalid-feedback d-block').insertAfter(element),
            };

            $('#progress-search').on('input', function () {
                const term = this.value.trim().toLowerCase();
                $('.progress-row').each(function () {
                    $(this).toggleClass('d-none', term !== '' && ! String($(this).data('search')).includes(term));
                });
            });

            const $entries = $('#progress-entries-form');
            if ($entries.find('.progress-input').length) {
                $.validator.addMethod('note_on_decrease', function (value, element) {
                    const $input = $(element).closest('tr').find('.progress-input');
                    const progress = $input.val();
                    return progress === '' || parseFloat(progress) >= parseFloat($input.data('previous')) || value.trim() !== '';
                }, 'Catatan wajib diisi jika progres turun.');

                $entries.validate(validation_options);
                $entries.find('.progress-input').each(function () {
                    $(this).rules('add', {
                        number: true, min: 0, max: 100,
                        messages: { number: 'Progres harus berupa angka.', min: 'Progres tidak boleh kurang dari 0%.', max: 'Progres tidak boleh lebih dari 100%.' },
                    });
                });
                $entries.find('.progress-note').each(function () {
                    $(this).rules('add', { maxlength: 500, note_on_decrease: true, messages: { maxlength: 'Catatan maksimal 500 karakter.' } });
                });
                $entries.on('input', '.progress-input', function () {
                    $(this).closest('tr').toggleClass('table-active', this.value !== '');
                    $(this).closest('tr').find('.progress-note').valid();
                });
            }

            const $return_form = $('#return-report-form');
            if ($return_form.length) {
                $return_form.validate($.extend({}, validation_options, {
                    rules: { reason: { required: true, minlength: 5, maxlength: 1000 } },
                    messages: { reason: { required: 'Alasan pengembalian wajib diisi.', minlength: 'Alasan minimal 5 karakter.', maxlength: 'Alasan maksimal 1000 karakter.' } },
                }));
                @if ($errors->has('reason'))
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('return-report-modal')).show();
                @endif
            }
        });
    </script>
@endpush
