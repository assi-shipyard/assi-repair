@extends('layouts.app')

@section('title', 'Detail Kapal')
@section('body_title', 'Detail Kapal')

@section('buttons_beside_title')
    <a href="{{ route('ship.edit', $ship->unique_id ?? $ship->id) }}" class="btn btn-outline-primary">Ubah</a>
    <a href="{{ route('ship.index') }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
    @include('partials.flash')

    @php
        $ship_build_year = $ship->build_year ? \Illuminate\Support\Carbon::parse($ship->build_year)->format('Y') : null;
        $gross_tonnage = is_numeric($ship->gross_tonnage) ? number_format((float) $ship->gross_tonnage, 0, ',', '.') : ($ship->gross_tonnage ?? null);
        $net_tonnage = is_numeric($ship->net_tonnage) ? number_format((float) $ship->net_tonnage, 0, ',', '.') : ($ship->net_tonnage ?? null);
        $ship_identifier = $ship->unique_id ?? $ship->id;
        $dimension_completion = collect([
            $ship->length_overall,
            $ship->breadth,
            $ship->height,
            $ship->empty_draft,
            $ship->loaded_draft,
        ])->filter()->count();
        $registration_completion = collect([
            $ship->imo_number,
            $ship->mmsi_number,
            $ship->call_sign,
            $ship->flag,
            $ship_build_year,
        ])->filter()->count();
    @endphp

    <style>
        .ship-hero {
            border: 0;
            border-radius: 1.25rem;
            background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 55%, #38bdf8 100%);
            color: #f8fafc;
            box-shadow: 0 2rem 4rem -2.75rem rgba(15, 23, 42, 0.82);
        }

        .ship-hero-body {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            align-items: center;
            justify-content: space-between;
            padding: 1.75rem;
        }

        .ship-hero-badge {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .55rem .85rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            color: rgba(248, 250, 252, 0.95);
            font-size: .82rem;
        }

        .ship-kpi-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .75rem;
            min-width: min(100%, 25rem);
        }

        .ship-kpi {
            padding: .9rem 1rem;
            border-radius: 1rem;
            background: rgba(15, 23, 42, 0.22);
            border: 1px solid rgba(255, 255, 255, 0.14);
        }

        .ship-kpi-label {
            display: block;
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: rgba(226, 232, 240, 0.82);
        }

        .ship-kpi-value {
            display: block;
            margin-top: .3rem;
            font-size: 1.45rem;
            font-weight: 700;
        }

        .ship-panel {
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 1.15rem;
            box-shadow: 0 1.5rem 3rem -2.5rem rgba(15, 23, 42, 0.45);
        }

        .ship-stat-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .ship-stat-card {
            padding: 1rem;
            border-radius: 1rem;
            background: var(--tblr-bg-surface-secondary);
            border: 1px solid rgba(15, 23, 42, 0.05);
        }

        .ship-stat-label {
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--tblr-secondary);
            margin-bottom: .35rem;
        }

        .ship-dimension-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
        }

        .ship-dimension-card {
            padding: 1rem;
            border-radius: 1rem;
            background: linear-gradient(180deg, rgba(239, 246, 255, 0.96), rgba(224, 242, 254, 0.88));
            border: 1px solid rgba(56, 189, 248, 0.14);
        }

        .ship-dimension-value {
            font-size: 1.65rem;
            font-weight: 700;
            line-height: 1.1;
        }

        @media (max-width: 991.98px) {
            .ship-kpi-grid,
            .ship-stat-grid,
            .ship-dimension-grid {
                grid-template-columns: 1fr;
            }

            .ship-hero-body {
                padding: 1.25rem;
            }
        }
    </style>

    <div class="d-flex flex-column gap-3">
        <div class="card ship-hero">
            <div class="ship-hero-body">
                <div>
                    <div class="text-uppercase small fw-bold mb-2" style="letter-spacing: .1em; color: rgba(226, 232, 240, 0.78);">Profil Kapal</div>
                    <h1 class="mb-1 text-white">{{ $ship->name }}</h1>
                    <div class="mb-3" style="color: rgba(226, 232, 240, 0.84);">{{ $ship->company?->name ?? 'Perusahaan belum diatur' }}</div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="ship-hero-badge">{{ $ship->type?->name ?? 'Jenis belum diatur' }}</span>
                        <span class="ship-hero-badge">{{ $ship->classification?->abbreviation ?? $ship->classification?->name ?? 'Kelas belum diatur' }}</span>
                        <span class="ship-hero-badge">ID {{ $ship_identifier }}</span>
                    </div>
                </div>

                <div class="ship-kpi-grid">
                    <div class="ship-kpi">
                        <span class="ship-kpi-label">Registrasi Terisi</span>
                        <span class="ship-kpi-value">{{ $registration_completion }}/5</span>
                    </div>
                    <div class="ship-kpi">
                        <span class="ship-kpi-label">Dimensi Terisi</span>
                        <span class="ship-kpi-value">{{ $dimension_completion }}/5</span>
                    </div>
                    <div class="ship-kpi">
                        <span class="ship-kpi-label">Build Year</span>
                        <span class="ship-kpi-value">{{ $ship_build_year ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card ship-panel h-100">
                    <div class="card-header border-0 pb-0">
                        <h3 class="card-title mb-0">Aksi Cepat</h3>
                    </div>
                    <div class="card-body pt-3 d-flex flex-column gap-3">
                        <div class="ship-stat-card">
                            <div class="ship-stat-label">Perusahaan Pemilik</div>
                            <div class="fw-semibold">{{ $ship->company?->name ?? '-' }}</div>
                        </div>
                        <div class="ship-stat-card">
                            <div class="ship-stat-label">Gross Tonnage</div>
                            <div class="fw-semibold">{{ $gross_tonnage ?? '-' }}</div>
                        </div>
                        <div class="ship-stat-card">
                            <div class="ship-stat-label">Net Tonnage</div>
                            <div class="fw-semibold">{{ $net_tonnage ?? '-' }}</div>
                        </div>
                        <div class="d-grid gap-2 mt-auto">
                            <a href="{{ route('ship.edit', $ship_identifier) }}" class="btn btn-primary">Ubah Data Kapal</a>
                            <a href="{{ route('ship.index') }}" class="btn btn-outline-secondary">Kembali ke Daftar</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card ship-panel mb-3">
                    <div class="card-header border-0 pb-0">
                        <h3 class="card-title mb-0">Identitas & Registrasi</h3>
                    </div>
                    <div class="card-body pt-3">
                        <div class="ship-stat-grid">
                            <div class="ship-stat-card">
                                <div class="ship-stat-label">IMO Number</div>
                                <div class="fw-semibold">{{ $ship->imo_number ?? '-' }}</div>
                            </div>
                            <div class="ship-stat-card">
                                <div class="ship-stat-label">MMSI Number</div>
                                <div class="fw-semibold">{{ $ship->mmsi_number ?? '-' }}</div>
                            </div>
                            <div class="ship-stat-card">
                                <div class="ship-stat-label">Call Sign</div>
                                <div class="fw-semibold">{{ $ship->call_sign ?? '-' }}</div>
                            </div>
                            <div class="ship-stat-card">
                                <div class="ship-stat-label">Bendera</div>
                                <div class="fw-semibold">{{ $ship->flag ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card ship-panel mb-3">
                    <div class="card-header border-0 pb-0">
                        <h3 class="card-title mb-0">Dimensi & Kapasitas</h3>
                    </div>
                    <div class="card-body pt-3">
                        <div class="ship-dimension-grid mb-3">
                            <div class="ship-dimension-card">
                                <div class="ship-stat-label">LOA</div>
                                <div class="ship-dimension-value">{{ $ship->length_overall ?? '-' }}</div>
                                <div class="text-secondary small">meter</div>
                            </div>
                            <div class="ship-dimension-card">
                                <div class="ship-stat-label">Breadth</div>
                                <div class="ship-dimension-value">{{ $ship->breadth ?? '-' }}</div>
                                <div class="text-secondary small">meter</div>
                            </div>
                            <div class="ship-dimension-card">
                                <div class="ship-stat-label">Height</div>
                                <div class="ship-dimension-value">{{ $ship->height ?? '-' }}</div>
                                <div class="text-secondary small">meter</div>
                            </div>
                            <div class="ship-dimension-card">
                                <div class="ship-stat-label">Draft Kosong</div>
                                <div class="ship-dimension-value">{{ $ship->empty_draft ?? '-' }}</div>
                                <div class="text-secondary small">meter</div>
                            </div>
                        </div>
                        <div class="ship-stat-grid">
                            <div class="ship-stat-card">
                                <div class="ship-stat-label">Gross Tonnage</div>
                                <div class="fw-semibold">{{ $gross_tonnage ?? '-' }}</div>
                            </div>
                            <div class="ship-stat-card">
                                <div class="ship-stat-label">Net Tonnage</div>
                                <div class="fw-semibold">{{ $net_tonnage ?? '-' }}</div>
                            </div>
                            <div class="ship-stat-card">
                                <div class="ship-stat-label">Draft Muat</div>
                                <div class="fw-semibold">{{ $ship->loaded_draft ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card ship-panel">
                    <div class="card-header border-0 pb-0">
                        <h3 class="card-title mb-0">Mesin Utama</h3>
                    </div>
                    <div class="card-body pt-3">
                        <div class="ship-stat-grid">
                            <div class="ship-stat-card">
                                <div class="ship-stat-label">Merek</div>
                                <div class="fw-semibold">{{ $ship->engine_brand ?? '-' }}</div>
                            </div>
                            <div class="ship-stat-card">
                                <div class="ship-stat-label">Model</div>
                                <div class="fw-semibold">{{ $ship->engine_model ?? '-' }}</div>
                            </div>
                            <div class="ship-stat-card">
                                <div class="ship-stat-label">Daya</div>
                                <div class="fw-semibold">{{ $ship->engine_power ?? '-' }}</div>
                            </div>
                            <div class="ship-stat-card">
                                <div class="ship-stat-label">RPM</div>
                                <div class="fw-semibold">{{ $ship->engine_rpm ?? '-' }}</div>
                            </div>
                            <div class="ship-stat-card">
                                <div class="ship-stat-label">Tipe Mesin</div>
                                <div class="fw-semibold">{{ $ship->engine_type ?? '-' }}</div>
                            </div>
                            <div class="ship-stat-card">
                                <div class="ship-stat-label">Tipe BBM</div>
                                <div class="fw-semibold">{{ $ship->engine_fuel_type ?? '-' }}</div>
                            </div>
                            <div class="ship-stat-card">
                                <div class="ship-stat-label">Kapasitas BBM</div>
                                <div class="fw-semibold">{{ $ship->engine_fuel_capacity ?? '-' }}</div>
                            </div>
                            <div class="ship-stat-card">
                                <div class="ship-stat-label">Konsumsi BBM</div>
                                <div class="fw-semibold">{{ $ship->engine_fuel_consumption ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
