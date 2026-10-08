@extends('layouts.app')

@section('title', 'Dashboard - SIREKA ASSI')
@section('body_title', 'Dashboard')

@section('content')
    <style>
        .dashboard-shell {
            display: grid;
            gap: 1.5rem;
        }

        .dashboard-hero {
            position: relative;
            overflow: hidden;
            border: 0;
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.24), transparent 32%),
                linear-gradient(135deg, #0f766e 0%, #155e75 55%, #1d4ed8 100%);
            color: #f8fafc;
        }

        .dashboard-hero::after {
            content: '';
            position: absolute;
            right: -4rem;
            bottom: -5rem;
            width: 16rem;
            height: 16rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
        }

        .dashboard-hero .text-secondary {
            color: rgba(248, 250, 252, 0.76) !important;
        }

        .dashboard-mini-stat {
            border-radius: 1rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(8px);
        }

        .dashboard-kpi-card,
        .dashboard-panel {
            border: 0;
            box-shadow: 0 1rem 2rem rgba(15, 23, 42, 0.08);
        }

        .dashboard-kpi-icon {
            width: 3rem;
            height: 3rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 1rem;
            font-size: 1.4rem;
        }

        .dashboard-scroll {
            max-height: 28rem;
            overflow-y: auto;
        }

        .dashboard-project-item {
            border: 1px solid rgba(148, 163, 184, 0.18);
            border-radius: 1rem;
            padding: 1rem;
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.94), #ffffff);
        }

        .dashboard-company-ships {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
        }
    </style>

    @include('partials.flash')

    @if (isset($needs_password_change) && $needs_password_change)
        <div class="alert alert-warning alert-important alert-dismissible" role="alert">
            <div class="d-flex">
                <div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v2m0 4v.01" /><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75" /></svg>
                </div>
                <div>
                    <strong>Peringatan Keamanan!</strong> Anda masih menggunakan kata sandi bawaan (NIK). Demi keamanan akun Anda, harap <a href="{{ route('settings.index') }}" class="alert-link text-decoration-underline">segera ganti kata sandi Anda di sini</a>.
                </div>
            </div>
            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
        </div>
    @endif

    <div class="dashboard-shell">
        <div class="card dashboard-hero">
            <div class="card-body p-4 p-xl-5">
                <div class="row align-items-center g-4 position-relative">
                    <div class="col-xl-7">
                        <div class="text-uppercase small fw-semibold text-secondary mb-2">Pusat Kendali Operasional</div>
                        <h1 class="mb-3 text-white">Pantau seluruh proyek dan armada pelanggan dari satu dashboard.</h1>
                        <p class="text-secondary mb-4">Tampilan ini merangkum proyek pending, proyek berjalan, proyek selesai, daftar pelanggan, armada kapal, dan tren penyelesaian proyek per tanggal.</p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('project.index') }}" class="btn btn-light">Lihat daftar proyek</a>
                            <a href="{{ route('company.index') }}" class="btn btn-outline-light">Lihat data pelanggan</a>
                        </div>
                    </div>
                    <div class="col-xl-5">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="dashboard-mini-stat">
                                    <div class="text-uppercase small text-secondary">Pengguna Aktif</div>
                                    <div class="fs-2 fw-bold">{{ session('employee_name') ?: 'Pengguna' }}</div>
                                    <div class="text-secondary">{{ session('employee_position') ?: '-' }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="dashboard-mini-stat">
                                    <div class="text-uppercase small text-secondary">Proyek Aktif</div>
                                    <div class="fs-1 fw-bold">{{ number_format($active_projects) }}</div>
                                    <div class="text-secondary">Pending dan berjalan</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="dashboard-mini-stat">
                                    <div class="text-uppercase small text-secondary">Rasio Armada</div>
                                    <div class="fs-1 fw-bold">{{ number_format($ships_per_company, 1) }}</div>
                                    <div class="text-secondary">Kapal per pelanggan</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="dashboard-mini-stat">
                                    <div class="text-uppercase small text-secondary">NIK</div>
                                    <div class="fs-2 fw-bold">{{ session('employee_id') ?: '-' }}</div>
                                    <div class="text-secondary">Sesi saat ini</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row row-cards">
            <div class="col-sm-6 col-xl-3">
                <div class="card dashboard-kpi-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <div class="text-secondary text-uppercase small fw-semibold">Pending</div>
                                <div class="display-6 fw-bold mb-1">{{ number_format($project_summary->pending_projects ?? 0) }}</div>
                                <div class="text-secondary">Belum dimulai</div>
                            </div>
                            <span class="dashboard-kpi-icon bg-azure-lt text-azure"><i class="ti ti-hourglass-empty"></i></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card dashboard-kpi-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <div class="text-secondary text-uppercase small fw-semibold">Berjalan</div>
                                <div class="display-6 fw-bold mb-1">{{ number_format($project_summary->ongoing_projects ?? 0) }}</div>
                                <div class="text-secondary">Sedang dikerjakan</div>
                            </div>
                            <span class="dashboard-kpi-icon bg-yellow-lt text-yellow"><i class="ti ti-rocket"></i></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card dashboard-kpi-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <div class="text-secondary text-uppercase small fw-semibold">Selesai</div>
                                <div class="display-6 fw-bold mb-1">{{ number_format($project_summary->completed_projects ?? 0) }}</div>
                                <div class="text-secondary">Siap ditinjau</div>
                            </div>
                            <span class="dashboard-kpi-icon bg-green-lt text-green"><i class="ti ti-circle-check"></i></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card dashboard-kpi-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <div class="text-secondary text-uppercase small fw-semibold">Total Proyek</div>
                                <div class="display-6 fw-bold mb-1">{{ number_format($project_summary->total_projects ?? 0) }}</div>
                                <div class="text-secondary">Semua status</div>
                            </div>
                            <span class="dashboard-kpi-icon bg-indigo-lt text-indigo"><i class="ti ti-folders"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row row-cards">
            <div class="col-md-6 col-xl-3">
                <div class="card dashboard-panel">
                    <div class="card-body">
                        <div class="text-secondary text-uppercase small fw-semibold mb-2">Pelanggan Terdaftar</div>
                        <div class="display-6 fw-bold mb-1">{{ number_format($total_companies) }}</div>
                        <div class="text-secondary">Perusahaan pemilik kapal</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card dashboard-panel">
                    <div class="card-body">
                        <div class="text-secondary text-uppercase small fw-semibold mb-2">Kapal Terdaftar</div>
                        <div class="display-6 fw-bold mb-1">{{ number_format($total_ships) }}</div>
                        <div class="text-secondary">Total armada pelanggan</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card dashboard-panel">
                    <div class="card-body">
                        <div class="text-secondary text-uppercase small fw-semibold mb-2">Rata-Rata Armada</div>
                        <div class="display-6 fw-bold mb-1">{{ number_format($ships_per_company, 1) }}</div>
                        <div class="text-secondary">Kapal tiap pelanggan</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card dashboard-panel">
                    <div class="card-body">
                        <div class="text-secondary text-uppercase small fw-semibold mb-2">Penyelesaian Tercatat</div>
                        <div class="display-6 fw-bold mb-1">{{ number_format($completion_chart_total) }}</div>
                        <div class="text-secondary">Masuk ke grafik harian</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row row-cards">
            <div class="col-xl-8">
                <div class="card dashboard-panel h-100">
                    <div class="card-header border-0 pb-0">
                        <div>
                            <h3 class="card-title mb-1">Tren Penyelesaian Proyek</h3>
                            <div class="text-secondary">Grafik menggunakan titik tanggal penyelesaian agar evaluasi bulanan tetap bisa ditelusuri sampai hari yang spesifik.</div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="project-completion-chart" style="min-height: 320px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card dashboard-panel h-100">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title mb-1">Pelanggan dan Kapalnya</h3>
                            <div class="text-secondary">Ringkasan seluruh customer beserta armada terdaftar.</div>
                        </div>
                    </div>
                    <div class="card-body dashboard-scroll">
                        @forelse ($company_fleet_summary as $company)
                            <div class="dashboard-project-item mb-3">
                                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                    <div>
                                        <div class="fw-bold">{{ $company->name }}</div>
                                        <div class="text-secondary small">{{ $company->email ?: 'Email belum tersedia' }}</div>
                                    </div>
                                    <span class="badge bg-blue-lt text-blue">{{ number_format($company->ships_count) }} kapal</span>
                                </div>
                                <div class="dashboard-company-ships">
                                    @forelse ($company->ships as $ship)
                                        <span class="badge bg-secondary-lt text-secondary">{{ $ship->name }}</span>
                                    @empty
                                        <span class="text-secondary small">Belum ada kapal terdaftar.</span>
                                    @endforelse
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-secondary py-5">Belum ada data perusahaan.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="row row-cards">
            @foreach ($project_status_sections as $section)
                <div class="col-12 col-xl-4">
                    <div class="card dashboard-panel h-100">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title mb-1">{{ $section['title'] }}</h3>
                                <div class="text-secondary">{{ $section['subtitle'] }}</div>
                            </div>
                        </div>
                        <div class="card-body dashboard-scroll">
                            @forelse ($section['projects'] as $project)
                                <div class="dashboard-project-item mb-3">
                                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                        <div>
                                            <div class="fw-bold">{{ $project->project_code }}</div>
                                            <div class="text-secondary small">{{ $project->ship?->name ?? '-' }} · {{ $project->ship?->company?->name ?? 'Pelanggan belum diatur' }}</div>
                                        </div>
                                        <span class="badge {{ $section['badge_class'] }}">{{ $project->status }}</span>
                                    </div>
                                    <div class="row g-2 text-secondary small mb-3">
                                        <div class="col-6">
                                            Mulai estimasi<br>
                                            <span class="text-body fw-semibold">{{ $project->start_date_estimation?->format('d M Y') ?? '-' }}</span>
                                        </div>
                                        <div class="col-6">
                                            Selesai aktual<br>
                                            <span class="text-body fw-semibold">{{ $project->end_date_actual?->format('d M Y') ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-secondary small">Progress</span>
                                        <span class="fw-semibold">{{ number_format((float) ($project->progress ?? 0), 0) }}%</span>
                                    </div>
                                    <div class="progress progress-sm mb-3">
                                        <div class="progress-bar {{ $section['progress_class'] }}" style="width: {{ min(100, max(0, (float) ($project->progress ?? 0))) }}%"></div>
                                    </div>
                                    <a href="{{ route('project.show', $project->unique_id) }}" class="btn btn-outline-secondary btn-sm">Lihat detail</a>
                                </div>
                            @empty
                                <div class="text-center text-secondary py-5">{{ $section['empty_state'] }}</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chartElement = document.getElementById('project-completion-chart');
            const chartSeries = @json($completion_chart_series);

            if (!chartElement || typeof ApexCharts === 'undefined') {
                return;
            }

            const chartOptions = {
                chart: {
                    type: 'area',
                    height: 320,
                    toolbar: {
                        show: true,
                    },
                    zoom: {
                        enabled: true,
                    },
                },
                series: [{
                    name: 'Proyek selesai',
                    data: chartSeries,
                }],
                colors: ['#0f766e'],
                dataLabels: {
                    enabled: false,
                },
                stroke: {
                    curve: 'smooth',
                    width: 3,
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.35,
                        opacityTo: 0.05,
                        stops: [0, 95, 100],
                    },
                },
                xaxis: {
                    type: 'datetime',
                    labels: {
                        datetimeUTC: false,
                        format: 'dd MMM yyyy',
                    },
                },
                yaxis: {
                    min: 0,
                    forceNiceScale: true,
                    title: {
                        text: 'Jumlah proyek',
                    },
                },
                tooltip: {
                    x: {
                        format: 'dd MMM yyyy',
                    },
                },
                noData: {
                    text: 'Belum ada proyek selesai untuk ditampilkan.',
                },
                grid: {
                    strokeDashArray: 4,
                },
            };

            new ApexCharts(chartElement, chartOptions).render();
        });
    </script>
@endpush
