@extends('layouts.app')

@section('title', 'Detail Permohonan Docking - ' . ($docking_request->ship?->name ?? '-'))
@section('body_title', 'Detail Kunjungan Docking')

@section('buttons_beside_title')
    <div class="btn-list">
        <a href="{{ route('docking-space-request.index') }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i>Kembali</a>
    </div>
@endsection

@php
    $ship = $docking_request->ship;
    $stage = $docking_request->request_status === 'approved' ? ($docking_request->visit_stage ?? 'awaiting_arrival') : $docking_request->request_status;
    $step = $docking_request->progress_step();
    $effective_dock = $docking_request->recommended_docking_space ?? $docking_request->requested_docking_space;
    $active_floating = $docking_request->docking_occupancies
        ->flatMap(fn ($occupancy) => $occupancy->floating_repair_histories)
        ->first(fn ($floating): bool => $floating->floating_status === 'active');
    $has_started_docking = $docking_request->docking_occupancies->contains(fn ($occupancy): bool => in_array($occupancy->occupancy_status, ['occupied', 'undocked'], true));
    $has_scheduled_slot = $docking_request->docking_occupancies->contains(fn ($occupancy): bool => $occupancy->occupancy_status === 'scheduled');
    $can_confirm_administration = ($can_operate || $is_owner) && $docking_request->is_in_yard() && $docking_request->administration_completed_at === null;
    $can_cancel = $is_owner && $docking_request->is_cancellable() && ! $has_started_docking;
    $now_value = now()->format('Y-m-d H:i');

    $journey = [
        1 => 'Pengajuan',
        2 => 'Persetujuan',
        3 => 'Menunggu kedatangan',
        4 => 'Tiba di galangan',
        5 => 'Di dalam dock',
        6 => 'Undocking / atas air',
        7 => 'Keluar galangan',
    ];

    $occupancy_labels = [
        'scheduled' => ['Terjadwal', 'bg-cyan-lt'],
        'occupied' => ['Di dalam dock', 'bg-purple-lt'],
        'undocked' => ['Sudah undocking', 'bg-success-lt'],
        'cancelled' => ['Dibatalkan', 'bg-secondary-lt'],
    ];

    $checklist = [
        ['Kapal sudah undocking & pekerjaan atas air selesai', in_array($stage, ['ready_to_depart', 'completed'], true)],
        ['Proyek terhubung' . ($docking_request->project ? ' (' . $docking_request->project->project_code . ')' : ''), $docking_request->project !== null],
        ['Status proyek selesai (Completed)', $docking_request->project?->status === 'Completed'],
        ['Administrasi (BAST, invoice, clearance) dikonfirmasi', $docking_request->administration_completed_at !== null],
    ];
@endphp

@section('content')
    @include('partials.flash')

    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-start gap-3">
                <span class="avatar avatar-md bg-blue-lt"><i class="ti ti-ship fs-2"></i></span>
                <div class="flex-fill">
                    <h2 class="mb-1">{{ $ship?->name ?? '-' }}</h2>
                    <div class="text-secondary">
                        {{ $ship?->company?->name ?? '-' }}
                        &middot; {{ $docking_request->project?->project_code ?? 'Belum ada proyek' }}
                        &middot; {{ $effective_dock?->name ?? 'Docking space belum ditentukan' }}
                    </div>
                </div>
                <span class="badge bg-blue-lt fs-5">{{ $docking_request->status_label() }}</span>
            </div>
            @if ($step > 0)
                <div class="steps steps-counter steps-blue mt-4 d-none d-md-flex">
                    @foreach ($journey as $journey_step => $journey_label)
                        <span class="step-item {{ $journey_step === $step ? 'active' : '' }}">{{ $journey_label }}</span>
                    @endforeach
                </div>
                <div class="d-md-none mt-3">
                    <div class="small text-secondary mb-1">Langkah {{ $step }} dari 7: {{ $journey[$step] }}</div>
                    <div class="progress progress-sm"><div class="progress-bar" style="width: {{ round($step / 7 * 100) }}%"></div></div>
                </div>
            @endif
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">Informasi permohonan</h3></div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item"><div class="datagrid-title">Tipe kapal</div><div class="datagrid-content">{{ $ship?->type?->name ?? '-' }}</div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Jadwal mulai</div><div class="datagrid-content">{{ $docking_request->requested_start_at?->tanggal_waktu_indonesia() ?? '-' }}</div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Jadwal selesai</div><div class="datagrid-content">{{ $docking_request->requested_end_at?->tanggal_waktu_indonesia() ?? '-' }}</div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Docking space diminta</div><div class="datagrid-content">{{ $docking_request->requested_docking_space?->name ?? '-' }}</div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Rekomendasi Engineering</div><div class="datagrid-content">{{ $docking_request->recommended_docking_space?->name ?? '-' }}</div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Diajukan oleh</div><div class="datagrid-content">{{ $docking_request->requester?->name ?? '-' }}</div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Diajukan pada</div><div class="datagrid-content">{{ $docking_request->created_at?->tanggal_waktu_indonesia() ?? '-' }}</div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Persetujuan Engineering</div><div class="datagrid-content">{{ $docking_request->engineering_approved_at?->tanggal_waktu_indonesia() ?? '-' }}</div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Persetujuan Produksi</div><div class="datagrid-content">{{ $docking_request->production_approved_at?->tanggal_waktu_indonesia() ?? '-' }}</div></div>
                    </div>
                    @if ($docking_request->request_notes)
                        <div class="mt-3"><div class="datagrid-title">Catatan pemohon</div><div style="white-space: pre-line">{{ $docking_request->request_notes }}</div></div>
                    @endif
                    @if ($docking_request->engineering_notes)
                        <div class="mt-3"><div class="datagrid-title">Catatan Engineering</div><div style="white-space: pre-line">{{ $docking_request->engineering_notes }}</div></div>
                    @endif
                    @if ($docking_request->production_notes)
                        <div class="mt-3"><div class="datagrid-title">Catatan Produksi</div><div style="white-space: pre-line">{{ $docking_request->production_notes }}</div></div>
                    @endif
                    @if ($docking_request->request_status === 'cancelled')
                        <div class="alert alert-secondary mt-3 mb-0">
                            Dibatalkan oleh {{ $docking_request->canceller?->name ?? '-' }} pada {{ $docking_request->cancelled_at?->tanggal_waktu_indonesia() ?? '-' }}.
                            <div class="mt-1" style="white-space: pre-line">{{ $docking_request->cancellation_reason }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">Riwayat kunjungan di galangan</h3></div>
                <div class="card-body">
                    <ul class="timeline">
                        @if ($docking_request->arrived_at)
                            <li class="timeline-event">
                                <div class="timeline-event-icon bg-indigo-lt"><i class="ti ti-ship"></i></div>
                                <div class="card timeline-event-card"><div class="card-body py-2">
                                    <div class="text-secondary float-end small">{{ $docking_request->arrived_at->tanggal_waktu_indonesia() }}</div>
                                    <h4 class="mb-1">Kapal tiba di galangan</h4>
                                    <div class="text-secondary small">Dicatat oleh {{ $docking_request->arrival_recorder?->name ?? '-' }}</div>
                                    @if ($docking_request->arrival_notes)<div class="small mt-1" style="white-space: pre-line">{{ $docking_request->arrival_notes }}</div>@endif
                                </div></div>
                            </li>
                        @endif
                        @foreach ($docking_request->docking_occupancies->where('occupancy_status', '!=', 'cancelled') as $occupancy)
                            @php [$occupancy_label, $occupancy_badge] = $occupancy_labels[$occupancy->occupancy_status] ?? [$occupancy->occupancy_status, 'bg-secondary-lt']; @endphp
                            <li class="timeline-event">
                                <div class="timeline-event-icon bg-purple-lt"><i class="ti ti-anchor"></i></div>
                                <div class="card timeline-event-card"><div class="card-body py-2">
                                    <div class="text-secondary float-end small">{{ $occupancy->docked_at?->tanggal_waktu_indonesia() ?? '-' }}</div>
                                    <h4 class="mb-1">{{ $occupancy->docking_space?->name ?? '-' }} <span class="badge {{ $occupancy_badge }} ms-1">{{ $occupancy_label }}</span></h4>
                                    <div class="text-secondary small">
                                        Estimasi undocking {{ $occupancy->estimated_undock_at?->tanggal_waktu_indonesia() ?? '-' }}
                                        @if ($occupancy->undocked_at) &middot; Undocking {{ $occupancy->undocked_at->tanggal_waktu_indonesia() }} @endif
                                    </div>
                                    @if ($occupancy->remarks)<div class="small mt-1" style="white-space: pre-line">{{ $occupancy->remarks }}</div>@endif
                                    @foreach ($occupancy->floating_repair_histories as $floating)
                                        <div class="border-start border-teal border-2 ps-2 mt-2 small">
                                            <span class="fw-medium">Pekerjaan atas air</span>
                                            <span class="badge {{ $floating->floating_status === 'active' ? 'bg-teal-lt' : 'bg-success-lt' }} ms-1">{{ $floating->floating_status === 'active' ? 'Berjalan' : 'Selesai' }}</span>
                                            <div class="text-secondary">{{ $floating->floating_started_at?->tanggal_waktu_indonesia() ?? '-' }} s/d {{ $floating->floating_completed_at?->tanggal_waktu_indonesia() ?? 'sekarang' }}</div>
                                            @if ($floating->notes)<div style="white-space: pre-line">{{ $floating->notes }}</div>@endif
                                        </div>
                                    @endforeach
                                </div></div>
                            </li>
                        @endforeach
                        @if ($docking_request->administration_completed_at)
                            <li class="timeline-event">
                                <div class="timeline-event-icon bg-lime-lt"><i class="ti ti-file-check"></i></div>
                                <div class="card timeline-event-card"><div class="card-body py-2">
                                    <div class="text-secondary float-end small">{{ $docking_request->administration_completed_at->tanggal_waktu_indonesia() }}</div>
                                    <h4 class="mb-1">Administrasi dikonfirmasi</h4>
                                    <div class="text-secondary small">Oleh {{ $docking_request->administration_confirmer?->name ?? '-' }}</div>
                                    <div class="small mt-1" style="white-space: pre-line">{{ $docking_request->administration_notes }}</div>
                                </div></div>
                            </li>
                        @endif
                        @if ($docking_request->departed_at)
                            <li class="timeline-event">
                                <div class="timeline-event-icon bg-success-lt"><i class="ti ti-door-exit"></i></div>
                                <div class="card timeline-event-card"><div class="card-body py-2">
                                    <div class="text-secondary float-end small">{{ $docking_request->departed_at->tanggal_waktu_indonesia() }}</div>
                                    <h4 class="mb-1">Kapal keluar galangan</h4>
                                    <div class="text-secondary small">Dicatat oleh {{ $docking_request->departure_recorder?->name ?? '-' }}</div>
                                    @if ($docking_request->departure_notes)<div class="small mt-1" style="white-space: pre-line">{{ $docking_request->departure_notes }}</div>@endif
                                </div></div>
                            </li>
                        @endif
                    </ul>
                    @if (! $docking_request->arrived_at && $docking_request->docking_occupancies->where('occupancy_status', '!=', 'cancelled')->isEmpty())
                        <div class="text-secondary">Kapal belum tiba di galangan.</div>
                    @endif
                </div>
            </div>

            <div class="row row-cards">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header"><h3 class="card-title">Dokumen</h3></div>
                        <div class="list-group list-group-flush">
                            @forelse ($docking_request->documents as $document)
                                <a href="{{ route('docking-space-request.documents.download', [$docking_request->unique_id, $document->unique_id]) }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                                    <i class="ti ti-file-download text-secondary"></i>
                                    <span class="flex-fill text-truncate">
                                        {{ $document->document_name }}
                                        <span class="d-block small text-secondary">{{ \App\Models\DockingRequestDocument::TYPE_LABELS[$document->document_type] ?? $document->document_type }}</span>
                                    </span>
                                </a>
                            @empty
                                <div class="list-group-item text-secondary">Tidak ada dokumen terlampir.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header"><h3 class="card-title">Riwayat penundaan</h3></div>
                        <div class="list-group list-group-flush">
                            @forelse ($docking_request->postponements as $postponement)
                                <div class="list-group-item small">
                                    <div class="fw-medium">{{ $postponement->new_start_at?->tanggal_waktu_indonesia() }} s/d {{ $postponement->new_end_at?->tanggal_waktu_indonesia() ?? '-' }}</div>
                                    <div class="text-secondary">Sebelumnya {{ $postponement->previous_start_at?->tanggal_waktu_indonesia() ?? '-' }}</div>
                                    <div style="white-space: pre-line">{{ $postponement->reason }}</div>
                                    <div class="text-secondary">Oleh {{ $postponement->postponer?->name ?? '-' }}, {{ $postponement->created_at?->tanggal_waktu_indonesia() }}</div>
                                </div>
                            @empty
                                <div class="list-group-item text-secondary">Belum pernah ditunda.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">Tindakan</h3></div>
                <div class="card-body d-grid gap-2">
                    @if ($stage === 'submitted' && $can_approve_engineering)
                        <a href="{{ route('docking-approval.show', ['engineering', $docking_request->unique_id]) }}" class="btn btn-primary"><i class="ti ti-ruler-measure me-1"></i>Tinjau Engineering</a>
                    @elseif ($stage === 'engineering_approved' && $can_approve_production)
                        <a href="{{ route('docking-approval.show', ['production', $docking_request->unique_id]) }}" class="btn btn-primary"><i class="ti ti-tool me-1"></i>Tinjau Produksi</a>
                    @elseif ($stage === 'awaiting_marketing' && $is_owner)
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#visit-marketing-modal" data-visit-action="marketing"><i class="ti ti-message-reply me-1"></i>Tanggapi rekomendasi Produksi</button>
                    @endif

                    @if ($can_operate)
                        @if ($stage === 'awaiting_arrival')
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#visit-arrival-modal" data-visit-action="arrival"><i class="ti ti-ship me-1"></i>Catat kedatangan kapal</button>
                        @elseif ($stage === 'arrived')
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#visit-dock-modal" data-visit-action="dock"><i class="ti ti-anchor me-1"></i>Masukkan ke dock</button>
                        @elseif ($stage === 'docked')
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#visit-undock-modal" data-visit-action="undock"><i class="ti ti-arrow-bar-up me-1"></i>Undocking</button>
                        @elseif ($stage === 'floating' && $active_floating)
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#visit-floating-modal" data-visit-action="floating"><i class="ti ti-check me-1"></i>Selesaikan pekerjaan atas air</button>
                        @endif
                    @endif

                    @if ($can_confirm_administration)
                        <button type="button" class="btn {{ $stage === 'ready_to_depart' ? 'btn-primary' : 'btn-outline-primary' }}" data-bs-toggle="modal" data-bs-target="#visit-administration-modal" data-visit-action="administration"><i class="ti ti-file-check me-1"></i>Konfirmasi administrasi</button>
                    @endif

                    @if ($can_operate && $stage === 'ready_to_depart')
                        <button type="button" class="btn {{ $departure_blockers === [] ? 'btn-success' : 'btn-outline-secondary' }}" data-bs-toggle="modal" data-bs-target="#visit-depart-modal" data-visit-action="depart"><i class="ti ti-door-exit me-1"></i>Keluar galangan</button>
                    @endif

                    @if ($is_owner && $docking_request->is_before_docking() && $has_scheduled_slot)
                        <a href="{{ route('docking-space-request.postpone', $docking_request->unique_id) }}" class="btn btn-outline-secondary"><i class="ti ti-calendar-time me-1"></i>Tunda jadwal docking</a>
                    @endif

                    @if ($can_operate && in_array($docking_request->request_status, \App\Models\ProjectDockingRequest::PENDING_STATUSES, true))
                        <form method="POST" action="{{ route('docking-space-request.evaluate', $docking_request->unique_id) }}" class="d-grid">
                            @csrf
                            <button class="btn btn-outline-secondary" type="submit"><i class="ti ti-refresh me-1"></i>Evaluasi ulang kapasitas</button>
                        </form>
                    @endif

                    @if ($can_cancel)
                        @include('docking-space-request.partials.cancel-trigger', ['trigger_class' => 'btn btn-outline-danger', 'trigger_label' => 'Batalkan permohonan'])
                    @endif

                    @if (in_array($stage, ['completed', 'cancelled', 'rejected'], true))
                        <div class="text-secondary small">Permohonan ini sudah ditutup. Tidak ada tindakan lanjutan.</div>
                    @endif
                </div>
            </div>

            @if ($docking_request->is_in_yard() || $stage === 'completed')
                <div class="card mb-3">
                    <div class="card-header"><h3 class="card-title">Syarat keluar galangan</h3></div>
                    <div class="list-group list-group-flush">
                        @foreach ($checklist as [$checklist_label, $checklist_done])
                            <div class="list-group-item d-flex align-items-center gap-2 small">
                                <i class="ti {{ $checklist_done ? 'ti-circle-check text-success' : 'ti-circle-dashed text-secondary' }} fs-3"></i>
                                <span class="{{ $checklist_done ? '' : 'text-secondary' }}">{{ $checklist_label }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-body small text-secondary">
                    <div class="fw-medium text-body mb-1"><i class="ti ti-info-circle me-1"></i>Catatan alur</div>
                    Setelah undocking, kapal dapat melanjutkan <strong>pekerjaan atas air</strong> dan tetap berada di galangan.
                    Kapal baru dapat dicatat keluar galangan bila proyek sudah berstatus selesai dan administrasi (BAST, invoice, clearance) dikonfirmasi.
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modal')
    @php
        $modal_error = fn (string $field) => $errors->has($field) ? '<div class="invalid-feedback d-block">' . e($errors->first($field)) . '</div>' : '';
    @endphp

    @if ($can_operate && $stage === 'awaiting_arrival')
        <div class="modal modal-blur fade" id="visit-arrival-modal" tabindex="-1" aria-labelledby="visit-arrival-title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <form method="POST" action="{{ route('docking-space-request.arrival', $docking_request->unique_id) }}" class="modal-content js-visit-form">
                    @csrf
                    <input type="hidden" name="_visit_action" value="arrival">
                    <div class="modal-header"><h5 class="modal-title" id="visit-arrival-title">Catat kedatangan kapal</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
                    <div class="modal-body">
                        <p class="text-secondary small">Catat waktu kapal {{ $ship?->name }} tiba di area galangan. Kapal belum masuk dock pada tahap ini.</p>
                        <div class="mb-3">
                            <label class="form-label required" for="arrived_at">Waktu kedatangan</label>
                            <input type="datetime-local" class="form-control" id="arrived_at" name="arrived_at" value="{{ old('arrived_at', $now_value) }}" data-date-display="long" required data-msg-required="Waktu kedatangan wajib diisi.">
                            {!! $modal_error('arrived_at') !!}
                        </div>
                        <div>
                            <label class="form-label" for="arrival_notes">Catatan</label>
                            <textarea class="form-control" id="arrival_notes" name="arrival_notes" rows="3" maxlength="2000" data-msg-maxlength="Catatan maksimal 2000 karakter.">{{ old('arrival_notes') }}</textarea>
                            {!! $modal_error('arrival_notes') !!}
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan kedatangan</button></div>
                </form>
            </div>
        </div>
    @endif

    @if ($can_operate && $stage === 'arrived')
        <div class="modal modal-blur fade" id="visit-dock-modal" tabindex="-1" aria-labelledby="visit-dock-title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <form method="POST" action="{{ route('docking-space-request.start-docking', $docking_request->unique_id) }}" class="modal-content js-visit-form">
                    @csrf
                    <input type="hidden" name="_visit_action" value="dock">
                    <div class="modal-header"><h5 class="modal-title" id="visit-dock-title">Masukkan kapal ke dock</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required" for="docking_space_id">Docking space</label>
                            <select class="form-select" id="docking_space_id" name="docking_space_id" required data-msg-required="Docking space wajib dipilih.">
                                @foreach ($dock_options as $option)
                                    <option value="{{ $option['space']->id }}" @selected((int) old('docking_space_id', $effective_dock?->id) === (int) $option['space']->id)>
                                        {{ $option['space']->name }}{{ $option['is_compatible'] ? '' : ' (tidak sesuai spesifikasi)' }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-hint">Default: docking space hasil rekomendasi/permohonan. Sistem tetap memeriksa bentrok jadwal dan kecocokan spesifikasi.</div>
                            {!! $modal_error('docking_space_id') !!}
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label required" for="docked_at">Waktu masuk dock</label>
                                <input type="datetime-local" class="form-control" id="docked_at" name="docked_at" value="{{ old('docked_at', $now_value) }}" data-date-display="long" required data-msg-required="Waktu masuk dock wajib diisi.">
                                {!! $modal_error('docked_at') !!}
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="estimated_undock_at">Estimasi undocking</label>
                                <input type="datetime-local" class="form-control" id="estimated_undock_at" name="estimated_undock_at" value="{{ old('estimated_undock_at', $docking_request->requested_end_at?->format('Y-m-d H:i')) }}" data-date-display="long">
                                {!! $modal_error('estimated_undock_at') !!}
                            </div>
                        </div>
                        <div>
                            <label class="form-label" for="dock_remarks">Catatan</label>
                            <textarea class="form-control" id="dock_remarks" name="remarks" rows="2" maxlength="2000" data-msg-maxlength="Catatan maksimal 2000 karakter.">{{ old('remarks') }}</textarea>
                            {!! $modal_error('remarks') !!}
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Masuk dock</button></div>
                </form>
            </div>
        </div>
    @endif

    @if ($can_operate && $stage === 'docked')
        @php $next_step = old('next_step', 'floating'); @endphp
        <div class="modal modal-blur fade" id="visit-undock-modal" tabindex="-1" aria-labelledby="visit-undock-title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <form method="POST" action="{{ route('docking-space-request.undock', $docking_request->unique_id) }}" class="modal-content js-visit-form">
                    @csrf
                    <input type="hidden" name="_visit_action" value="undock">
                    <div class="modal-header"><h5 class="modal-title" id="visit-undock-title">Undocking kapal</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required" for="undocked_at">Waktu undocking</label>
                            <input type="datetime-local" class="form-control" id="undocked_at" name="undocked_at" value="{{ old('undocked_at', $now_value) }}" data-date-display="long" required data-msg-required="Waktu undocking wajib diisi.">
                            {!! $modal_error('undocked_at') !!}
                        </div>
                        <label class="form-label required">Setelah undocking</label>
                        <div class="form-selectgroup form-selectgroup-boxes d-flex flex-column mb-3">
                            <label class="form-selectgroup-item flex-fill">
                                <input type="radio" name="next_step" value="floating" class="form-selectgroup-input js-next-step" @checked($next_step === 'floating') required data-msg-required="Pilih langkah setelah undocking.">
                                <div class="form-selectgroup-label d-flex align-items-center p-3">
                                    <div class="me-3"><span class="form-selectgroup-check"></span></div>
                                    <div><div class="fw-medium">Lanjut pekerjaan atas air</div><div class="small text-secondary">Kapal keluar dock tetapi masih dikerjakan di galangan.</div></div>
                                </div>
                            </label>
                            <label class="form-selectgroup-item flex-fill">
                                <input type="radio" name="next_step" value="ready_to_depart" class="form-selectgroup-input js-next-step" @checked($next_step === 'ready_to_depart')>
                                <div class="form-selectgroup-label d-flex align-items-center p-3">
                                    <div class="me-3"><span class="form-selectgroup-check"></span></div>
                                    <div><div class="fw-medium">Pekerjaan fisik selesai</div><div class="small text-secondary">Kapal menunggu proyek &amp; administrasi selesai untuk keluar galangan.</div></div>
                                </div>
                            </label>
                        </div>
                        {!! $modal_error('next_step') !!}
                        <div class="mb-3 js-floating-start {{ $next_step === 'floating' ? '' : 'd-none' }}">
                            <label class="form-label required" for="floating_started_at">Mulai pekerjaan atas air</label>
                            <input type="datetime-local" class="form-control" id="floating_started_at" name="floating_started_at" value="{{ old('floating_started_at', $now_value) }}" data-date-display="long" data-msg-required="Waktu mulai pekerjaan atas air wajib diisi.">
                            {!! $modal_error('floating_started_at') !!}
                        </div>
                        <div>
                            <label class="form-label" for="undock_notes">Catatan</label>
                            <textarea class="form-control" id="undock_notes" name="notes" rows="2" maxlength="2000" data-msg-maxlength="Catatan maksimal 2000 karakter.">{{ old('notes') }}</textarea>
                            {!! $modal_error('notes') !!}
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan undocking</button></div>
                </form>
            </div>
        </div>
    @endif

    @if ($can_operate && $stage === 'floating' && $active_floating)
        <div class="modal modal-blur fade" id="visit-floating-modal" tabindex="-1" aria-labelledby="visit-floating-title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <form method="POST" action="{{ route('docking-space-request.complete-floating', $docking_request->unique_id) }}" class="modal-content js-visit-form">
                    @csrf
                    <input type="hidden" name="_visit_action" value="floating">
                    <div class="modal-header"><h5 class="modal-title" id="visit-floating-title">Selesaikan pekerjaan atas air</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
                    <div class="modal-body">
                        <p class="text-secondary small">Pekerjaan atas air dimulai {{ $active_floating->floating_started_at?->tanggal_waktu_indonesia() }}.</p>
                        <div class="mb-3">
                            <label class="form-label required" for="floating_completed_at">Waktu selesai</label>
                            <input type="datetime-local" class="form-control" id="floating_completed_at" name="floating_completed_at" value="{{ old('floating_completed_at', $now_value) }}" data-date-display="long" required data-msg-required="Waktu selesai wajib diisi.">
                            {!! $modal_error('floating_completed_at') !!}
                        </div>
                        <div>
                            <label class="form-label" for="floating_notes">Catatan</label>
                            <textarea class="form-control" id="floating_notes" name="notes" rows="2" maxlength="2000" data-msg-maxlength="Catatan maksimal 2000 karakter.">{{ old('notes') }}</textarea>
                            {!! $modal_error('notes') !!}
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
                </form>
            </div>
        </div>
    @endif

    @if ($can_confirm_administration)
        <div class="modal modal-blur fade" id="visit-administration-modal" tabindex="-1" aria-labelledby="visit-administration-title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <form method="POST" action="{{ route('docking-space-request.administration', $docking_request->unique_id) }}" class="modal-content js-visit-form">
                    @csrf
                    <input type="hidden" name="_visit_action" value="administration">
                    <div class="modal-header"><h5 class="modal-title" id="visit-administration-title">Konfirmasi administrasi</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required" for="administration_notes">Catatan administrasi</label>
                            <textarea class="form-control" id="administration_notes" name="administration_notes" rows="3" required minlength="5" maxlength="2000" placeholder="Contoh: BAST No. ..., invoice lunas, clearance syahbandar terbit" data-msg-required="Catatan administrasi wajib diisi." data-msg-minlength="Catatan administrasi minimal 5 karakter." data-msg-maxlength="Catatan administrasi maksimal 2000 karakter.">{{ old('administration_notes') }}</textarea>
                            {!! $modal_error('administration_notes') !!}
                        </div>
                        <label class="form-check">
                            <input class="form-check-input" type="checkbox" name="administration_confirmed" value="1" @checked(old('administration_confirmed')) required data-msg-required="Centang konfirmasi terlebih dahulu.">
                            <span class="form-check-label">Saya menyatakan BAST/serah terima, invoice, dan clearance kapal sudah selesai.</span>
                        </label>
                        {!! $modal_error('administration_confirmed') !!}
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Konfirmasi</button></div>
                </form>
            </div>
        </div>
    @endif

    @if ($can_operate && $stage === 'ready_to_depart')
        <div class="modal modal-blur fade" id="visit-depart-modal" tabindex="-1" aria-labelledby="visit-depart-title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <form method="POST" action="{{ route('docking-space-request.depart', $docking_request->unique_id) }}" class="modal-content js-visit-form">
                    @csrf
                    <input type="hidden" name="_visit_action" value="depart">
                    <div class="modal-header"><h5 class="modal-title" id="visit-depart-title">Kapal keluar galangan</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
                    <div class="modal-body">
                        @if ($departure_blockers !== [])
                            <div class="alert alert-warning">
                                <div class="fw-medium mb-1">Kapal belum dapat keluar galangan:</div>
                                <ul class="mb-0 ps-3">@foreach ($departure_blockers as $blocker)<li>{{ $blocker }}</li>@endforeach</ul>
                            </div>
                        @else
                            <div class="mb-3">
                                <label class="form-label required" for="departed_at">Waktu keluar galangan</label>
                                <input type="datetime-local" class="form-control" id="departed_at" name="departed_at" value="{{ old('departed_at', $now_value) }}" data-date-display="long" required data-msg-required="Waktu keluar galangan wajib diisi.">
                                {!! $modal_error('departed_at') !!}
                            </div>
                            <div>
                                <label class="form-label" for="departure_notes">Catatan</label>
                                <textarea class="form-control" id="departure_notes" name="departure_notes" rows="2" maxlength="2000" data-msg-maxlength="Catatan maksimal 2000 karakter.">{{ old('departure_notes') }}</textarea>
                                {!! $modal_error('departure_notes') !!}
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Tutup</button>
                        @if ($departure_blockers === [])
                            <button type="submit" class="btn btn-success">Catat keluar galangan</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($stage === 'awaiting_marketing' && $is_owner)
        <div class="modal modal-blur fade" id="visit-marketing-modal" tabindex="-1" aria-labelledby="visit-marketing-title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <form method="POST" action="{{ route('docking-space-request.marketing-response', $docking_request->unique_id) }}" class="modal-content js-visit-form" id="marketing-response-form">
                    @csrf
                    <input type="hidden" name="_visit_action" value="marketing">
                    <div class="modal-header"><h5 class="modal-title" id="visit-marketing-title">Tanggapan rekomendasi Produksi</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            Produksi merekomendasikan <strong>{{ $docking_request->production_recommendation === 'postpone' ? 'penundaan' : 'pembatalan' }}</strong>.
                            @if ($docking_request->production_recommendation === 'postpone' && $docking_request->recommended_start_at)
                                <div class="mt-1">Usulan jadwal: {{ $docking_request->recommended_start_at->tanggal_waktu_indonesia() }} s/d {{ $docking_request->recommended_end_at?->tanggal_waktu_indonesia() ?? '-' }}</div>
                            @endif
                        </div>
                        <p class="small text-secondary">Sampaikan rekomendasi ini kepada customer, lalu catat jawabannya.</p>
                        <div>
                            <label class="form-label" for="marketing_notes">Catatan customer</label>
                            <textarea class="form-control" id="marketing_notes" name="notes" rows="3" maxlength="2000" placeholder="Wajib diisi bila customer keberatan" data-msg-maxlength="Catatan maksimal 2000 karakter.">{{ old('notes') }}</textarea>
                            {!! $modal_error('notes') !!}
                            {!! $modal_error('response') !!}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="response" value="decline" class="btn btn-outline-danger js-marketing-decline">Customer keberatan</button>
                        <button type="submit" name="response" value="accept" class="btn btn-success">Customer setuju</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @include('docking-space-request.partials.cancel-modal')
@endsection

@push('scripts')
    @include('docking-space-request.partials.cancel-modal-script')
    <script>
        $(function () {
            $('.js-visit-form').each(function () {
                $(this).validate({
                    ignore: ':hidden:not(.js-date-value)',
                    errorClass: 'is-invalid',
                    validClass: 'is-valid',
                    errorElement: 'div',
                    errorPlacement: function (error, element) {
                        error.addClass('invalid-feedback d-block');
                        if (element.is(':radio, :checkbox')) {
                            element.closest('.form-selectgroup, .form-check').after(error);
                        } else if (element.hasClass('js-date-value')) {
                            element.siblings('.js-date-display').after(error);
                        } else {
                            element.after(error);
                        }
                    },
                    highlight: function (element) {
                        $(element).addClass('is-invalid').siblings('.js-date-display').addClass('is-invalid');
                    },
                    unhighlight: function (element) {
                        $(element).removeClass('is-invalid').siblings('.js-date-display').removeClass('is-invalid');
                    },
                    submitHandler: function (form) {
                        $(form).find('button[type="submit"]').prop('disabled', true);
                        form.submit();
                    },
                });
            });

            const toggle_floating_start = function () {
                const is_floating = $('.js-next-step:checked').val() === 'floating';
                $('.js-floating-start').toggleClass('d-none', !is_floating)
                    .find('[name="floating_started_at"]').prop('required', is_floating);
            };
            $(document).on('change', '.js-next-step', toggle_floating_start);
            toggle_floating_start();

            // Decline needs a reason; the clicked submit value is not part of the form until submit, so add it manually.
            $('.js-marketing-decline').on('click', function (event) {
                const notes = $('#marketing_notes');
                if (!notes.val().trim()) {
                    event.preventDefault();
                    notes.addClass('is-invalid').next('.invalid-feedback').remove();
                    notes.after('<div class="invalid-feedback d-block">Catatan wajib diisi bila customer keberatan.</div>');
                    return;
                }
                $(this).closest('form').find('input[type="hidden"][name="response"]').remove();
                $(this).closest('form').append('<input type="hidden" name="response" value="decline">');
            });
            $('#marketing-response-form button[value="accept"]').on('click', function () {
                $(this).closest('form').find('input[type="hidden"][name="response"]').remove();
                $(this).closest('form').append('<input type="hidden" name="response" value="accept">');
            });

            const requested_action = @json(old('_visit_action', $open_action));
            if (requested_action === 'cancel') {
                $('.js-cancel-request').first().trigger('click');
            } else if (requested_action) {
                const modal = document.getElementById('visit-' + requested_action + '-modal');
                if (modal) {
                    bootstrap.Modal.getOrCreateInstance(modal).show();
                }
            }
        });
    </script>
@endpush
