@extends('layouts.app')

@section('title', 'Riwayat Docking Kapal')
@section('body_title', 'Riwayat Docking Kapal')

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
                        <th>Docked At</th>
                        <th>Undocked At</th>
                        <th>Floating Repair</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($docking_histories as $history)
                        @php
                            $floating = $history->floating_repair_histories->sortByDesc('floating_started_at')->first();
                        @endphp
                        <tr>
                            <td>{{ $history->project?->project_code ?? '-' }}</td>
                            <td>{{ $history->ship?->name ?? '-' }}</td>
                            <td>{{ $history->docking_space?->name ?? '-' }}</td>
                            <td>{{ optional($history->docked_at)->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>{{ optional($history->undocked_at)->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>
                                @if ($floating)
                                    {{ strtoupper($floating->floating_status) }}
                                    <div class="text-secondary small">
                                        {{ optional($floating->floating_started_at)->format('d/m/Y H:i') ?? '-' }}
                                        s/d
                                        {{ optional($floating->floating_completed_at)->format('d/m/Y H:i') ?? '-' }}
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if ($can_manage_docking && $floating && $floating->floating_status === 'active')
                                    <form method="POST" action="{{ route('ship-docking.complete-floating', $history->id) }}" class="d-flex flex-column gap-2 js-complete-floating-form">
                                        @csrf
                                        <input type="datetime-local" class="form-control form-control-sm" name="floating_completed_at" required>
                                        <input type="text" class="form-control form-control-sm" name="notes" placeholder="Catatan penyelesaian floating repair">
                                        <button class="btn btn-sm btn-success w-100" type="submit">Selesaikan Floating</button>
                                    </form>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-secondary">Belum ada riwayat docking kapal.</td>
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
            $('.js-complete-floating-form').each(function () {
                $(this).validate({
                    errorClass: 'is-invalid',
                    validClass: 'is-valid',
                    errorElement: 'div',
                    errorPlacement: function (error, element) {
                        error.addClass('invalid-feedback');
                        element.closest('div').append(error);
                    },
                    rules: {
                        floating_completed_at: { required: true },
                    },
                    messages: {
                        floating_completed_at: { required: 'Waktu selesai floating repair wajib diisi.' },
                    },
                });
            });
        });
    </script>
@endpush
