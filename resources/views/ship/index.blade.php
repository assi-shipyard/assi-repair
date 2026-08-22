@extends('layouts.app')

@section('title', 'Data Kapal')
@section('body_title', 'Data Kapal')

@section('buttons_beside_title')
    <a href="{{ route('ship.create') }}" class="btn btn-primary">Tambah Kapal</a>
@endsection

@section('content')
    @include('partials.flash')

    <style>
        .directory-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem;
            border-radius: 1rem;
            background: linear-gradient(135deg, rgba(30, 64, 175, 0.08), rgba(14, 165, 233, 0.14));
            border: 1px solid rgba(14, 165, 233, 0.15);
        }

        .directory-search {
            position: relative;
            flex: 1 1 24rem;
        }

        .directory-search .ti {
            position: absolute;
            top: 50%;
            left: 1rem;
            transform: translateY(-50%);
            color: var(--tblr-secondary);
        }

        .directory-search-input {
            padding-left: 2.75rem;
            border-radius: 999px;
        }

        .directory-summary {
            display: flex;
            gap: .75rem;
            flex-wrap: wrap;
        }

        .directory-pill {
            min-width: 10rem;
            padding: .85rem 1rem;
            border-radius: .9rem;
            background-color: rgba(255, 255, 255, 0.72);
            border: 1px solid rgba(15, 23, 42, 0.06);
        }

        .directory-pill-label {
            display: block;
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--tblr-secondary);
        }

        .directory-pill-value {
            display: block;
            margin-top: .2rem;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--tblr-dark);
        }

        .directory-card {
            height: 100%;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 1rem;
            box-shadow: 0 1rem 2.5rem -1.75rem rgba(15, 23, 42, 0.45);
        }

        .directory-card .card-body {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .directory-card-meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .75rem;
        }

        .directory-card-meta-item {
            padding: .85rem 1rem;
            border-radius: .85rem;
            background-color: var(--tblr-bg-surface-secondary);
        }

        .directory-card-actions {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
            margin-top: auto;
        }

        .directory-empty {
            display: none;
        }

        @media (max-width: 575.98px) {
            .directory-card-meta {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="d-flex flex-column gap-3">
        <div class="directory-toolbar">
            <div class="directory-search">
                <span class="ti ti-search"></span>
                <input type="search" class="form-control directory-search-input" id="ship-search" placeholder="Cari nama kapal, perusahaan, jenis, atau kelas..." autocomplete="off">
            </div>
            <div class="directory-summary">
                <div class="directory-pill">
                    <span class="directory-pill-label">Total Kapal</span>
                    <span class="directory-pill-value">{{ $ships->count() }}</span>
                </div>
                <div class="directory-pill">
                    <span class="directory-pill-label">Hasil Tampil</span>
                    <span class="directory-pill-value" id="ship-visible-count">{{ $ships->count() }}</span>
                </div>
            </div>
        </div>

        <div class="row g-3" id="ship-card-list">
            @forelse ($ships as $ship)
                @php($ship_identifier = $ship->unique_id ?? $ship->id)
                <div class="col-12 col-md-6 col-xl-4 ship-card-item" data-search="{{ strtolower(trim(implode(' ', array_filter([$ship->name, $ship->company?->name, $ship->type?->name, $ship->classification?->name])))) }}">
                    <div class="card directory-card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between gap-3">
                                <div>
                                    <div class="text-secondary text-uppercase small fw-bold">Kapal</div>
                                    <h3 class="card-title mb-1">{{ $ship->name }}</h3>
                                    <div class="text-secondary">{{ $ship->company?->name ?? 'Perusahaan belum diatur' }}</div>
                                </div>
                                <span class="badge bg-blue-lt text-blue">{{ $ship->type?->name ?? 'Tanpa Jenis' }}</span>
                            </div>

                            <div class="directory-card-meta">
                                <div class="directory-card-meta-item">
                                    <div class="text-secondary small mb-1">Jenis Kapal</div>
                                    <div class="fw-semibold">{{ $ship->type?->name ?? '-' }}</div>
                                </div>
                                <div class="directory-card-meta-item">
                                    <div class="text-secondary small mb-1">Klasifikasi</div>
                                    <div class="fw-semibold">{{ $ship->classification?->name ?? '-' }}</div>
                                </div>
                            </div>

                            <div class="directory-card-actions">
                                <a href="{{ route('ship.show', $ship_identifier) }}" class="btn btn-primary btn-sm">Lihat</a>
                                <a href="{{ route('ship.edit', $ship_identifier) }}" class="btn btn-outline-primary btn-sm">Ubah</a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center text-secondary py-5">Belum ada data kapal.</div>
                    </div>
                </div>
            @endforelse
        </div>

        @if ($ships->isNotEmpty())
            <div class="card directory-empty" id="ship-empty-state">
                <div class="card-body text-center py-5">
                    <div class="text-secondary">Tidak ada kapal yang cocok dengan pencarian.</div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const $search_input = $('#ship-search');
            const $cards = $('.ship-card-item');
            const $empty_state = $('#ship-empty-state');
            const $visible_count = $('#ship-visible-count');

            $search_input.on('input', function() {
                const query = $(this).val().toString().trim().toLowerCase();
                let visible_total = 0;

                $cards.each(function() {
                    const matches = $(this).data('search').toString().includes(query);
                    $(this).toggle(matches);

                    if (matches) {
                        visible_total += 1;
                    }
                });

                $visible_count.text(visible_total);
                $empty_state.toggle(visible_total === 0);
            });
        });
    </script>
@endpush
