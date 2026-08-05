@extends('layouts.app')

@section('title', 'Jadwal Docking')
@section('body_title', 'Jadwal Docking')

@section('content')
    @include('partials.flash')

    <style>
        .gantt-mini-table {
            font-size: 0.78rem;
        }

        .gantt-mini-table th,
        .gantt-mini-table td {
            white-space: nowrap;
            text-align: center;
            vertical-align: middle;
            padding: 0.35rem 0.4rem;
        }

        .gantt-mini-table .ship-col {
            min-width: 260px;
            text-align: left;
            position: sticky;
            left: 0;
            z-index: 2;
            background: #ffffff;
        }

        .gantt-mini-table .day-header {
            min-width: 36px;
        }

        .gantt-mini-table .day-cell {
            min-width: 36px;
            height: 28px;
        }

        .gantt-mini-table .day-cell-active-scheduled {
            background-color: #206bc4;
        }

        .gantt-mini-table .day-cell-active-occupied {
            background-color: #2fb344;
        }

        .gantt-mini-table .day-cell-active-undocked {
            background-color: #f59f00;
        }

        .gantt-mini-table .day-cell-empty {
            background-color: #f1f5f9;
        }

        .gantt-mini-table .total-days-col {
            min-width: 52px;
            font-weight: 600;
            background: #f8fafc;
        }

        .schedule-table-wrap {
            overflow-x: auto;
        }
    </style>

    <form class="card mb-3" method="GET" action="{{ route('docking-space-availability') }}">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4 col-lg-3">
                    <label class="form-label">Pilih Bulan</label>
                    <select name="month" class="form-select" onchange="this.form.submit()">
                        @foreach ($month_options as $month_number => $month_name)
                            <option value="{{ $month_number }}" @selected((int) $selected_month === (int) $month_number)>
                                {{ $month_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 col-lg-2">
                    <label class="form-label">Pilih Tahun</label>
                    <select name="year" class="form-select" onchange="this.form.submit()">
                        @foreach ($year_options as $year_option)
                            <option value="{{ $year_option }}" @selected((int) $selected_year === (int) $year_option)>
                                {{ $year_option }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 col-lg-2">
                    <button type="submit" class="btn btn-primary w-100">Tampilkan</button>
                </div>

                <div class="col-md-12 col-lg-5">
                    <div class="d-flex flex-wrap gap-3 justify-content-md-end">
                        <div><span class="badge" style="background: #206bc4">&nbsp;</span> <small class="text-secondary">Scheduled</small></div>
                        <div><span class="badge" style="background: #2fb344">&nbsp;</span> <small class="text-secondary">Occupied</small></div>
                        <div><span class="badge" style="background: #f59f00">&nbsp;</span> <small class="text-secondary">Undocked</small></div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="row row-cards">
        @foreach ($schedule_cards as $card)
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title mb-0">{{ $card['docking_space_name'] }}</h3>
                            <div class="text-secondary small">{{ $card['location'] ?? '-' }}</div>
                        </div>
                        <div class="d-flex gap-3">
                            <div class="text-center">
                                <div class="h3 mb-0">{{ $card['current_occupied_count'] }}</div>
                                <small class="text-secondary">Aktif</small>
                            </div>
                            <div class="text-center">
                                <div class="h3 mb-0">{{ $card['scheduled_count'] }}</div>
                                <small class="text-secondary">Terjadwal</small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="schedule-table-wrap">
                            <table class="table table-bordered table-sm mb-0 gantt-mini-table">
                                <thead>
                                    <tr>
                                        <th class="ship-col">Kapal Terjadwal</th>
                                        @foreach ($day_numbers as $day_number)
                                            <th class="day-header">{{ $day_number }}</th>
                                        @endforeach
                                        <th class="total-days-col">Hari</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($card['rows'] as $row)
                                        @php
                                            $cell_class = match ((string) $row['occupancy_status']) {
                                                'scheduled' => 'day-cell-active-scheduled',
                                                'occupied' => 'day-cell-active-occupied',
                                                'undocked' => 'day-cell-active-undocked',
                                                default => 'day-cell-empty',
                                            };
                                        @endphp
                                        <tr>
                                            <td class="ship-col">
                                                <div class="fw-semibold">{{ $row['ship_name'] }}</div>
                                                <div class="text-secondary small">{{ $row['project_code'] }} | {{ strtoupper((string) $row['occupancy_status']) }}</div>
                                            </td>

                                            @foreach ($day_numbers as $day_number)
                                                @if (!empty($row['day_cells'][$day_number]))
                                                    <td class="day-cell {{ $cell_class }}"></td>
                                                @else
                                                    <td class="day-cell day-cell-empty"></td>
                                                @endif
                                            @endforeach

                                            <td class="total-days-col">{{ $row['active_days_count'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ 2 + $total_days_in_month }}" class="text-center text-secondary py-4">
                                                Tidak ada jadwal docking pada bulan dan tahun terpilih.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
