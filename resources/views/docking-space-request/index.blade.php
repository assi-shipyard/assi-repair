@extends('layouts.app')

@section('title', 'Daftar Permohonan Docking Space')
@section('body_title', 'Daftar Permohonan Docking Space')

@section('buttons_beside_title')
    <a href="{{ route('docking-space-request.create') }}" class="btn btn-primary">Permohonan Baru</a>
@endsection

@section('content')
    @include('partials.flash')

    @php
        $can_manage_docking = auth()->user()?->hasRole('admin')
            || auth()->user()?->can('docking.manage')
            || auth()->user()?->can('project.manage')
            || auth()->user()?->can('project.update');
    @endphp

    <form class="card mb-3" method="GET" action="{{ route('docking-space-request.index') }}">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Filter Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        @foreach (['submitted', 'reviewed', 'approved', 'rejected', 'cancelled'] as $item_status)
                            <option value="{{ $item_status }}" @selected($status === $item_status)>{{ strtoupper($item_status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-primary w-100" type="submit">Terapkan</button>
                </div>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Proyek</th>
                        <th>Kapal</th>
                        <th>Jadwal</th>
                        <th>Preferensi Docking Space</th>
                        <th>Status</th>
                        <th>Kecocokan Terbaik</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($docking_requests as $docking_request)
                        @php
                            $best_evaluation = $docking_request->capacity_evaluations
                                ->sortByDesc('compatibility_score')
                                ->first();
                        @endphp
                        <tr>
                            <td>{{ $docking_request->project?->project_code ?? '-' }}</td>
                            <td>{{ $docking_request->ship?->name ?? '-' }}</td>
                            <td>
                                {{ optional($docking_request->requested_start_at)->format('d/m/Y H:i') ?? '-' }}
                                <div class="text-secondary small">
                                    s/d {{ optional($docking_request->requested_end_at)->format('d/m/Y H:i') ?? '-' }}
                                </div>
                            </td>
                            <td>{{ $docking_request->requested_docking_space?->name ?? '-' }}</td>
                            <td><span class="badge bg-blue-lt">{{ strtoupper($docking_request->request_status) }}</span></td>
                            <td>
                                @if ($best_evaluation)
                                    {{ $best_evaluation->docking_space?->name ?? '-' }}
                                    ({{ $best_evaluation->compatibility_score }}%)
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-2">
                                    @if ($can_manage_docking)
                                        <form method="POST" action="{{ route('docking-space-request.evaluate', $docking_request->id) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-primary w-100" type="submit">Evaluasi Ulang</button>
                                        </form>

                                        @if (in_array($docking_request->request_status, ['submitted', 'reviewed'], true))
                                            <form method="POST" action="{{ route('docking-space-request.review', $docking_request->id) }}" class="d-flex flex-column gap-2 js-review-approve-form">
                                                @csrf
                                                <input type="hidden" name="request_status" value="approved">
                                                <select class="form-select form-select-sm" name="approved_docking_space_id" required>
                                                    <option value="">Pilih Docking Space</option>
                                                    @foreach ($docking_spaces as $docking_space)
                                                        <option value="{{ $docking_space->id }}" @selected((int) $docking_request->requested_docking_space_id === (int) $docking_space->id)>
                                                            {{ $docking_space->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button class="btn btn-sm btn-success w-100" type="submit">Setujui</button>
                                            </form>

                                            <form method="POST" action="{{ route('docking-space-request.review', $docking_request->id) }}">
                                                @csrf
                                                <input type="hidden" name="request_status" value="rejected">
                                                <input type="hidden" name="rejection_reason" value="Ditolak dari halaman daftar permohonan docking space.">
                                                <button class="btn btn-sm btn-outline-danger w-100" type="submit">Tolak</button>
                                            </form>
                                        @endif

                                        @if ($docking_request->request_status === 'approved')
                                            <form method="POST" action="{{ route('docking-space-request.start-docking', $docking_request->id) }}" class="d-flex flex-column gap-2 js-start-docking-form">
                                                @csrf
                                                <input type="hidden" name="docking_space_id" value="{{ $docking_request->requested_docking_space_id }}">
                                                <input type="datetime-local" class="form-control form-control-sm" name="docked_at" value="{{ optional($docking_request->requested_start_at)->format('Y-m-d\TH:i') }}" required>
                                                <input type="datetime-local" class="form-control form-control-sm" name="estimated_undock_at" value="{{ optional($docking_request->requested_end_at)->format('Y-m-d\TH:i') }}">
                                                <button class="btn btn-sm btn-primary w-100" type="submit">Mulai Docking</button>
                                            </form>
                                        @endif
                                    @else
                                        <span class="text-secondary small">Tidak ada akses aksi.</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-secondary">Belum ada data permohonan docking space.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            $.validator.addMethod('greaterThanOrEqual', function (value, element, param) {
                if (!value) {
                    return true;
                }

                const comparedValue = $(element).closest('form').find(param).val();
                if (!comparedValue) {
                    return true;
                }

                return new Date(value) >= new Date(comparedValue);
            });

            $('.js-review-approve-form').each(function () {
                $(this).validate({
                    errorClass: 'is-invalid',
                    validClass: 'is-valid',
                    errorElement: 'div',
                    errorPlacement: function (error, element) {
                        error.addClass('invalid-feedback');
                        element.closest('div').append(error);
                    },
                    rules: {
                        approved_docking_space_id: { required: true },
                    },
                    messages: {
                        approved_docking_space_id: { required: 'Docking space persetujuan wajib dipilih.' },
                    },
                });
            });

            $('.js-start-docking-form').each(function () {
                $(this).validate({
                    errorClass: 'is-invalid',
                    validClass: 'is-valid',
                    errorElement: 'div',
                    errorPlacement: function (error, element) {
                        error.addClass('invalid-feedback');
                        element.closest('div').append(error);
                    },
                    rules: {
                        docked_at: { required: true },
                        estimated_undock_at: {
                            greaterThanOrEqual: '[name="docked_at"]',
                        },
                    },
                    messages: {
                        docked_at: { required: 'Waktu masuk dock wajib diisi.' },
                        estimated_undock_at: {
                            greaterThanOrEqual: 'Estimasi keluar dock tidak boleh lebih awal dari waktu masuk dock.',
                        },
                    },
                });
            });
        });
    </script>
@endpush
