@extends('layouts.app')

@section('title', 'Docking Kapal Sekarang')
@section('body_title', 'Docking Kapal Sekarang')

@section('content')
    @include('partials.flash')

    @php
        $can_manage_docking = auth()->user()?->hasRole('admin')
            || auth()->user()?->can('docking.manage')
            || auth()->user()?->can('project.manage')
            || auth()->user()?->can('project.update');
    @endphp

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Proyek</th>
                        <th>Kapal</th>
                        <th>Docking Space</th>
                        <th>Waktu Masuk Dock</th>
                        <th>Estimasi Undock</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($current_occupancies as $occupancy)
                        <tr>
                            <td>{{ $occupancy->project?->project_code ?? '-' }}</td>
                            <td>{{ $occupancy->ship?->name ?? '-' }}</td>
                            <td>{{ $occupancy->docking_space?->name ?? '-' }}</td>
                            <td>{{ optional($occupancy->docked_at)->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>{{ optional($occupancy->estimated_undock_at)->format('d/m/Y H:i') ?? '-' }}</td>
                            <td><span class="badge bg-azure-lt">{{ strtoupper($occupancy->occupancy_status) }}</span></td>
                            <td>
                                @if ($can_manage_docking)
                                    <form method="POST" action="{{ route('ship-docking.undock-to-floating', $occupancy->unique_id ?? $occupancy->id) }}" class="d-flex flex-column gap-2 js-undock-floating-form">
                                        @csrf
                                        <input type="datetime-local" class="form-control form-control-sm" name="undocked_at" required>
                                        <input type="datetime-local" class="form-control form-control-sm" name="floating_started_at">
                                        <input type="text" class="form-control form-control-sm" name="notes" placeholder="Catatan undock/floating repair">
                                        <button class="btn btn-sm btn-outline-primary w-100" type="submit">Undock ke Floating</button>
                                    </form>
                                @else
                                    <span class="text-secondary small">Tidak ada akses aksi.</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-secondary">Tidak ada kapal yang sedang docking saat ini.</td>
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

            $('.js-undock-floating-form').each(function () {
                $(this).validate({
                    errorClass: 'is-invalid',
                    validClass: 'is-valid',
                    errorElement: 'div',
                    errorPlacement: function (error, element) {
                        error.addClass('invalid-feedback');
                        element.closest('div').append(error);
                    },
                    rules: {
                        undocked_at: { required: true },
                        floating_started_at: {
                            greaterThanOrEqual: '[name="undocked_at"]',
                        },
                    },
                    messages: {
                        undocked_at: { required: 'Waktu undock wajib diisi.' },
                        floating_started_at: {
                            greaterThanOrEqual: 'Waktu mulai floating repair tidak boleh lebih awal dari waktu undock.',
                        },
                    },
                });
            });
        });
    </script>
@endpush
