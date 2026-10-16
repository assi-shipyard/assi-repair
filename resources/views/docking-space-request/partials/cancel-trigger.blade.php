{{-- Opens #cancel-request-modal; expects $docking_request and optional $trigger_class / $trigger_label. --}}
<button type="button" class="{{ $trigger_class ?? 'btn btn-outline-danger' }} js-cancel-request"
    data-bs-toggle="modal" data-bs-target="#cancel-request-modal"
    data-action="{{ route('docking-space-request.cancel', $docking_request->unique_id) }}"
    data-request-id="{{ $docking_request->unique_id }}"
    data-ship="{{ $docking_request->ship?->name ?? '-' }}"
    data-project="{{ $docking_request->project?->project_code ?? 'Tanpa proyek' }}"
    data-schedule="{{ $docking_request->requested_start_at?->tanggal_waktu_indonesia() ?? '-' }} s/d {{ $docking_request->requested_end_at?->tanggal_waktu_indonesia() ?? '-' }}"
    data-dock="{{ $docking_request->recommended_docking_space?->name ?? $docking_request->requested_docking_space?->name ?? '-' }}"
    data-status="{{ $docking_request->status_label() }}"
    data-approved="{{ $docking_request->request_status === 'approved' ? '1' : '0' }}">
    <i class="ti ti-x me-1"></i>{{ $trigger_label ?? 'Batalkan permohonan' }}
</button>
