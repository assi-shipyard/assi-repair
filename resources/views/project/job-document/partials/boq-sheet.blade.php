@php
    $features = $sheet['features'];
    $show_prices = $features['prices'];
    $show_progress = $features['progress'];
    $max_depth = \App\Services\project_boq_service::MAX_DEPTH;
    $job_status_labels = ['pending' => 'Belum Mulai', 'ongoing' => 'Berjalan', 'completed' => 'Selesai', 'cancelled' => 'Batal'];
    $job_status_classes = ['pending' => 'bg-secondary-lt', 'ongoing' => 'bg-blue-lt', 'completed' => 'bg-success-lt', 'cancelled' => 'bg-danger-lt'];
    $fmt_money = fn ($value): string => number_format((float) $value, 2, ',', '.');
    $fmt_qty = fn ($value): string => $value === null ? '-' : rtrim(rtrim(number_format((float) $value, 4, ',', '.'), '0'), ',');
    $fmt_date = fn ($date): ?string => $date?->locale('id')->translatedFormat('j F Y');
    $column_count = 4 + ($show_prices ? 3 : 0) + ($show_progress ? 2 : 0) + ($can_edit ? 1 : 0);
    $job_base = [$project->unique_id, $document->unique_id];
@endphp

<div class="row row-cards mb-3">
    <div class="col-6 col-lg-3">
        <div class="card card-sm"><div class="card-body">
            <div class="subheader">Pekerjaan</div>
            <div class="h2 mb-0">{{ number_format($sheet['job_count'], 0, ',', '.') }}</div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-sm"><div class="card-body">
            <div class="subheader">Material</div>
            <div class="h2 mb-0">{{ number_format($sheet['material_count'], 0, ',', '.') }}</div>
        </div></div>
    </div>
    @if ($show_prices)
        <div class="col-12 col-lg-3">
            <div class="card card-sm"><div class="card-body">
                <div class="subheader">Total Biaya (IDR)</div>
                <div class="h2 mb-0 text-primary">{{ $fmt_money($sheet['grand_total']) }}</div>
            </div></div>
        </div>
    @endif
    @if ($show_progress)
        <div class="col-12 col-lg-3">
            <div class="card card-sm"><div class="card-body">
                <div class="subheader">Progres Tertimbang</div>
                <div class="h2 mb-1">{{ $sheet['progress'] !== null ? number_format((float) $sheet['progress'], 2, ',', '.').'%' : '-' }}</div>
                <div class="progress progress-sm"><div class="progress-bar bg-success" style="width: {{ (float) ($sheet['progress'] ?? 0) }}%"></div></div>
            </div></div>
        </div>
    @endif
</div>

<div class="alert alert-info">
    <div class="d-flex">
        <i class="ti ti-info-circle fs-2 me-2"></i>
        <div>
            Pekerjaan dikelompokkan per kelompok pekerjaan (I, II, ...) dan dapat memiliki sub pekerjaan (1.1, 1.1.1) maksimal {{ $max_depth }} tingkat.
            @if ($show_prices)
                Jumlah dihitung dari volume × harga satuan; bila item Kamus Harga memiliki minimum charge, volume tagih memakai nilai minimum tersebut. Bobot dihitung ulang otomatis setiap ada perubahan.
                <div class="mt-1 small">Kamus Harga proyek: <strong>{{ $price_book?->name ?? 'Belum tersedia' }}</strong>{{ $price_book && $project->price_book_id === null ? ' (kamus acuan)' : '' }}</div>
            @elseif ($show_progress)
                Progres tertimbang memakai bobot dari RAB yang disalin ke tahap ini.
            @endif
        </div>
    </div>
</div>

@if ($show_prices || $show_progress)
    @if (count($sheet['groups']) > 0)
        <div class="card mb-3">
            <div class="card-header"><h3 class="card-title">Rekapitulasi per Kelompok</h3></div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-sm">
                    <thead>
                        <tr>
                            <th class="w-1">No</th>
                            <th>Kelompok Pekerjaan</th>
                            <th class="text-end">Pekerjaan</th>
                            @if ($show_prices)<th class="text-end">Jumlah (IDR)</th><th class="text-end">Bobot</th>@endif
                            @if ($show_progress)<th class="text-end" style="min-width: 9rem">Progres</th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sheet['groups'] as $group)
                            <tr>
                                <td class="fw-bold">{{ $group['roman'] }}</td>
                                <td>{{ $group['label'] }}</td>
                                <td class="text-end">{{ $group['job_count'] }}</td>
                                @if ($show_prices)
                                    <td class="text-end">{{ $fmt_money($group['total']) }}</td>
                                    <td class="text-end">{{ $group['weight'] !== null ? number_format((float) $group['weight'], 2, ',', '.').'%' : '-' }}</td>
                                @endif
                                @if ($show_progress)
                                    <td class="text-end">{{ $group['progress'] !== null ? number_format((float) $group['progress'], 2, ',', '.').'%' : '-' }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                    @if ($show_prices)
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="3" class="text-end">Total</td>
                                <td class="text-end">{{ $fmt_money($sheet['grand_total']) }}</td>
                                <td class="text-end">{{ bccomp($sheet['grand_total'], '0', 2) > 0 ? '100,00%' : '-' }}</td>
                                @if ($show_progress)<td class="text-end">{{ $sheet['progress'] !== null ? number_format((float) $sheet['progress'], 2, ',', '.').'%' : '-' }}</td>@endif
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    @endif
@endif

<div class="card">
    <div class="card-header flex-wrap gap-2">
        <h3 class="card-title">Daftar Pekerjaan</h3>
        <div class="card-actions d-flex flex-wrap gap-2">
            <input type="search" id="boq-search" class="form-control form-control-sm" style="max-width: 14rem" placeholder="Cari pekerjaan / material...">
            <label class="form-check form-switch mb-0 align-self-center">
                <input class="form-check-input" type="checkbox" id="boq-toggle-materials" checked>
                <span class="form-check-label small">Material</span>
            </label>
            <a href="{{ route('project.job-document.workflow.export', $job_base) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-file-type-pdf me-1"></i>Ekspor PDF</a>
            @if ($can_edit)
                <button type="button" class="btn btn-sm btn-primary" id="boq-add-job"><i class="ti ti-plus me-1"></i>Tambah Pekerjaan</button>
            @endif
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table table-sm boq-table" id="boq-table">
            <thead>
                <tr>
                    <th class="w-1">No</th>
                    <th style="min-width: 16rem">Uraian Pekerjaan</th>
                    <th class="text-end">Volume</th>
                    <th>Sat.</th>
                    @if ($show_prices)
                        <th class="text-end">Harga Satuan</th>
                        <th class="text-end">Jumlah (IDR)</th>
                        <th class="text-end">Bobot</th>
                    @endif
                    @if ($show_progress)
                        <th class="text-end">Progres</th>
                        <th>Status</th>
                    @endif
                    @if ($can_edit)<th class="w-1"></th>@endif
                </tr>
            </thead>
            @forelse ($sheet['groups'] as $group)
                <tbody class="boq-group">
                    <tr class="boq-group-row">
                        <td class="fw-bold">{{ $group['roman'] }}</td>
                        <td colspan="{{ $show_prices ? 4 : 3 }}" class="fw-bold text-uppercase">{{ $group['label'] }}</td>
                        @if ($show_prices)
                            <td class="text-end fw-bold">{{ $fmt_money($group['total']) }}</td>
                            <td class="text-end fw-bold">{{ $group['weight'] !== null ? number_format((float) $group['weight'], 2, ',', '.').'%' : '-' }}</td>
                        @endif
                        @if ($show_progress)
                            <td class="text-end fw-bold">{{ $group['progress'] !== null ? number_format((float) $group['progress'], 2, ',', '.').'%' : '-' }}</td>
                            <td></td>
                        @endif
                        @if ($can_edit)<td></td>@endif
                    </tr>
                    @foreach ($group['rows'] as $row)
                        @php
                            $job = $row['job'];
                            $calculation = $job->calculation;
                            $job_route = [...$job_base, $job->unique_id];
                            $job_payload = [
                                'id' => $job->unique_id,
                                'action' => route('project.job-document.workflow.job.update', $job_route),
                                'is_child' => $job->parent_job_id !== null,
                                'job_name' => $job->job_name,
                                'project_job_group_id' => $group['group']?->unique_id,
                                'price_item' => $job->price_book_item ? ['id' => $job->price_book_item->unique_id, 'text' => $job->price_book_item->code.' — '.$job->price_book_item->name] : null,
                                'minimum_quantity' => $job->minimum_quantity,
                                'job_volume_estimated' => $job->job_volume_estimated,
                                'unit' => $job->unit,
                                'unit_price' => $job->unit_price,
                                'calculation' => $calculation,
                                'notes' => $job->notes,
                                'responsible_kind' => $job->responsible_kind,
                                'responsible_name' => $job->responsible_name,
                                'est_start_date' => $job->est_start_date?->format('Y-m-d'),
                                'est_finish_date' => $job->est_finish_date?->format('Y-m-d'),
                                'progress_percent' => $job->progress_percent,
                                'status' => $job->status,
                                'actual_start_date' => $job->actual_start_date?->format('Y-m-d'),
                                'actual_finish_date' => $job->actual_finish_date?->format('Y-m-d'),
                                'actual_volume' => $job->actual_volume,
                            ];
                        @endphp
                        <tr class="boq-job-row {{ $row['depth'] === 0 ? 'boq-root' : '' }}" data-search="{{ mb_strtolower($job->job_name.' '.$job->materials->pluck('material_name')->implode(' ')) }}">
                            <td class="text-secondary text-nowrap">{{ $row['number'] }}</td>
                            <td>
                                <div style="padding-left: {{ $row['depth'] * 1.25 }}rem">
                                    <div class="{{ $row['depth'] === 0 ? 'fw-bold' : 'fw-semibold' }}">
                                        @if ($row['depth'] > 0)<i class="ti ti-corner-down-right text-secondary me-1"></i>@endif{{ $job->job_name }}
                                    </div>
                                    <div class="small text-secondary d-flex flex-wrap column-gap-2">
                                        @if ($job->price_book_item)<span><i class="ti ti-book me-1"></i>{{ $job->price_book_item->code }}</span>@endif
                                        @if ($job->minimum_quantity !== null)<span class="text-warning">Min. {{ $fmt_qty($job->minimum_quantity) }} {{ $job->unit }}</span>@endif
                                        @if ($calculation)
                                            <span><i class="ti ti-calculator me-1"></i>
                                                @if ($calculation['type'] === 'plate')
                                                    Pelat {{ $fmt_qty($calculation['length']) }}×{{ $fmt_qty($calculation['width']) }}×{{ $fmt_qty($calculation['thickness']) }} mm × {{ $calculation['quantity'] }}
                                                @else
                                                    Pipa Ø{{ $fmt_qty($calculation['diameter']) }}×{{ $fmt_qty($calculation['wall_thickness']) }} mm, {{ $fmt_qty($calculation['pipe_length']) }} mm × {{ $calculation['quantity'] }}
                                                @endif
                                                = {{ $fmt_qty($calculation['weight_kg']) }} kg
                                            </span>
                                        @endif
                                        @if ($job->responsible_name)<span><i class="ti ti-user me-1"></i>{{ $job->responsible_name }}</span>@endif
                                        @if ($job->est_start_date || $job->est_finish_date)<span><i class="ti ti-calendar me-1"></i>{{ $fmt_date($job->est_start_date) ?? '-' }} – {{ $fmt_date($job->est_finish_date) ?? '-' }}</span>@endif
                                        @if ($job->photos->isNotEmpty())<span><i class="ti ti-photo me-1"></i>{{ $job->photos->count() }} foto</span>@endif
                                    </div>
                                    @if ($job->notes)<div class="small text-secondary fst-italic">{{ $job->notes }}</div>@endif
                                </div>
                            </td>
                            <td class="text-end text-nowrap">{{ $fmt_qty($job->job_volume_estimated) }}</td>
                            <td>{{ $job->unit ?? '-' }}</td>
                            @if ($show_prices)
                                <td class="text-end text-nowrap">{{ $job->unit_price !== null ? $fmt_money($job->unit_price) : '-' }}</td>
                                <td class="text-end text-nowrap">
                                    {{ $job->est_price !== null ? $fmt_money($job->est_price) : '-' }}
                                    @if ($row['has_children'] || bccomp($row['materials_total'], '0', 2) > 0)
                                        <div class="small text-secondary" title="Termasuk material dan sub pekerjaan">Σ {{ $fmt_money($row['subtotal']) }}</div>
                                    @endif
                                </td>
                                <td class="text-end">{{ $job->job_weight_percent !== null ? number_format((float) $job->job_weight_percent, 2, ',', '.').'%' : '-' }}</td>
                            @endif
                            @if ($show_progress)
                                <td class="text-end text-nowrap">{{ number_format((float) ($job->progress_percent ?? 0), 2, ',', '.') }}%</td>
                                <td><span class="badge {{ $job_status_classes[$job->status] ?? 'bg-secondary-lt' }}">{{ $job_status_labels[$job->status] ?? $job->status }}</span></td>
                            @endif
                            @if ($can_edit)
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-ghost-secondary btn-icon" type="button" data-bs-toggle="dropdown" aria-label="Aksi"><i class="ti ti-dots-vertical"></i></button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <button type="button" class="dropdown-item boq-edit-job" data-job="{{ json_encode($job_payload) }}"><i class="ti ti-edit me-2"></i>Ubah</button>
                                            @if ($row['depth'] < $max_depth - 1)
                                                <button type="button" class="dropdown-item boq-add-child" data-parent-id="{{ $job->unique_id }}" data-parent-name="{{ $row['number'].' '.$job->job_name }}"><i class="ti ti-subtask me-2"></i>Tambah Sub Pekerjaan</button>
                                            @endif
                                            <button type="button" class="dropdown-item boq-add-material" data-action="{{ route('project.job-document.workflow.material.store', $job_route) }}" data-job-name="{{ $row['number'].' '.$job->job_name }}"><i class="ti ti-package me-2"></i>Tambah Material</button>
                                            <a class="dropdown-item" href="{{ route('project.job-document.workflow.job.photos', $job_route) }}"><i class="ti ti-photo me-2"></i>Foto</a>
                                            <div class="dropdown-divider"></div>
                                            <button type="button" class="dropdown-item text-danger boq-delete" data-action="{{ route('project.job-document.workflow.job.destroy', $job_route) }}" data-title="Hapus pekerjaan?" data-text="{{ $row['has_children'] ? 'Sub pekerjaan dan material di bawahnya ikut terhapus.' : 'Material di bawahnya ikut terhapus.' }}"><i class="ti ti-trash me-2"></i>Hapus</button>
                                        </div>
                                    </div>
                                </td>
                            @endif
                        </tr>
                        @foreach ($row['materials'] as $material_row)
                            @php($material = $material_row['material'])
                            <tr class="boq-material-row" data-search="{{ mb_strtolower($job->job_name.' '.$material->material_name) }}">
                                <td></td>
                                <td>
                                    <div class="small" style="padding-left: {{ $row['depth'] * 1.25 + 1.25 }}rem">
                                        <span class="text-secondary">{{ $material_row['letter'] }}.</span> {{ $material->material_name }}
                                        @if ($material->dimension)<span class="text-secondary">· {{ $material->dimension }}</span>@endif
                                        @if ($material->minimum_quantity !== null)<span class="text-warning">· Min. {{ $fmt_qty($material->minimum_quantity) }} {{ $material->unit }}</span>@endif
                                    </div>
                                </td>
                                <td class="text-end small text-nowrap">{{ $fmt_qty($material->volume) }}</td>
                                <td class="small">{{ $material->unit ?? '-' }}</td>
                                @if ($show_prices)
                                    <td class="text-end small text-nowrap">{{ $material->unit_price !== null ? $fmt_money($material->unit_price) : '-' }}</td>
                                    <td class="text-end small text-nowrap">{{ $material->price !== null ? $fmt_money($material->price) : '-' }}</td>
                                    <td></td>
                                @endif
                                @if ($show_progress)<td></td><td></td>@endif
                                @if ($can_edit)
                                    <td class="text-end text-nowrap">
                                        <button type="button" class="btn btn-sm btn-ghost-primary btn-icon boq-edit-material" aria-label="Ubah material"
                                            data-material="{{ json_encode([
                                                'action' => route('project.job-document.workflow.material.update', [...$job_route, $material->unique_id]),
                                                'job_name' => $row['number'].' '.$job->job_name,
                                                'material_name' => $material->material_name,
                                                'price_item' => $material->price_book_item_id && $material->price_book_item ? ['id' => $material->price_book_item->unique_id, 'text' => $material->price_book_item->code.' — '.$material->price_book_item->name] : null,
                                                'minimum_quantity' => $material->minimum_quantity,
                                                'volume' => $material->volume,
                                                'unit' => $material->unit,
                                                'unit_price' => $material->unit_price,
                                                'dimension' => $material->dimension,
                                                'notes' => $material->notes,
                                            ]) }}"><i class="ti ti-edit"></i></button>
                                        <button type="button" class="btn btn-sm btn-ghost-danger btn-icon boq-delete" aria-label="Hapus material" data-action="{{ route('project.job-document.workflow.material.destroy', [...$job_route, $material->unique_id]) }}" data-title="Hapus material?" data-text="{{ $material->material_name }}"><i class="ti ti-trash"></i></button>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            @empty
                <tbody>
                    <tr>
                        <td colspan="{{ $column_count }}" class="text-center text-secondary py-5">
                            <div class="mb-2"><i class="ti ti-list-details fs-1"></i></div>
                            Belum ada pekerjaan.
                            @if ($can_edit) Klik <strong>Tambah Pekerjaan</strong> untuk mulai menyusun daftar. @endif
                        </td>
                    </tr>
                </tbody>
            @endforelse
            @if ($show_prices && count($sheet['groups']) > 0)
                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="5" class="text-end">Total</td>
                        <td class="text-end text-nowrap">{{ $fmt_money($sheet['grand_total']) }}</td>
                        <td colspan="{{ $column_count - 6 }}"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
    <div class="card-footer text-secondary small d-none" id="boq-search-empty">Tidak ada pekerjaan yang cocok dengan pencarian.</div>
</div>

@if ($can_edit)
    @include('project.job-document.partials.boq-modals')
@endif

@push('scripts')
    <script>
        (function () {
            const $table = $('#boq-table');
            $('#boq-toggle-materials').on('change', function () {
                $table.toggleClass('hide-materials', !this.checked);
            });
            $('#boq-search').on('input', function () {
                const term = this.value.trim().toLowerCase();
                let visible = 0;
                $table.find('tbody.boq-group').each(function () {
                    let group_visible = 0;
                    $(this).find('tr[data-search]').each(function () {
                        const match = term === '' || this.dataset.search.includes(term);
                        $(this).toggleClass('d-none', !match);
                        if (match) { group_visible++; }
                    });
                    $(this).toggleClass('d-none', group_visible === 0);
                    visible += group_visible;
                });
                $('#boq-search-empty').toggleClass('d-none', visible > 0 || term === '');
            });
        })();
    </script>
@endpush
