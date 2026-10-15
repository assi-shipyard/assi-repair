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
    $uses_reports = $features['progress_reports'] ?? false;
    $can_mark_owner_supplied = in_array($document->document_type, [\App\Models\ProjectJobDocument::TYPE_INITIAL_BOQ, \App\Models\ProjectJobDocument::TYPE_FINAL_BOQ], true);
    $progress_weight_column = $show_progress && ! $show_prices;
    $fmt_pct = fn ($value): string => $value === null ? '-' : number_format((float) $value, 2, ',', '.').'%';
    $column_count = 4 + ($show_prices ? 3 : 0) + ($show_progress ? 3 + ($progress_weight_column ? 1 : 0) : 0) + ($can_edit ? 1 : 0);
    $job_base = [$project->unique_id, $document->unique_id];
    $rule_labels = collect(\App\Services\boq_rule_service::RULES)->map(fn (array $rule): string => $rule['label'])->all();
    $old_minimum = $errors->hasAny(['minimum_quantity', 'reason', 'price_book_item_id']) && old('action') === 'set'
        ? ['item_id' => old('price_book_item_id'), 'minimum' => old('minimum_quantity'), 'reason' => old('reason')]
        : null;
    $billed_differs = fn ($billed, $volume): bool => $billed !== null && $volume !== null && bccomp((string) $billed, (string) $volume, 4) !== 0;
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
                Jumlah dihitung dari volume tagih × harga satuan. Volume tagih mengikuti aturan perhitungan item (pelat, pipa, docking, crane, kapal tunda, luasan) dan batas minimum proyek. Bobot dihitung ulang otomatis setiap ada perubahan.
                <div class="mt-1 small">Kamus Harga proyek: <strong>{{ $price_book?->name ?? 'Belum tersedia' }}</strong>{{ $price_book && $project->price_book_id === null ? ' (kamus acuan)' : '' }}</div>
            @elseif ($show_progress)
                Progres tertimbang memakai bobot dari RAB yang disalin ke tahap ini.
            @endif
            @if ($show_progress)
                <div class="mt-1 small">
                    Progres pekerjaan induk dihitung otomatis dari sub pekerjaannya: <strong>Bobot</strong> = porsi biaya pekerjaan beserta sub pekerjaannya, <strong>Progres</strong> = realisasi aktual, <strong>Tertimbang</strong> = progres × bobot.
                    @if ($uses_reports) Progres hanya berubah melalui laporan <strong>Progres Harian</strong> yang disetujui PIMPRO Reparasi dan PPC. @endif
                </div>
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
                            @if ($progress_weight_column)<th class="text-end">Bobot</th>@endif
                            @if ($show_progress)<th class="text-end" style="min-width: 9rem">Progres</th><th class="text-end">Tertimbang</th>@endif
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
                                @if ($progress_weight_column)<td class="text-end">{{ $fmt_pct($group['weight']) }}</td>@endif
                                @if ($show_progress)
                                    <td class="text-end">
                                        {{ $fmt_pct($group['progress']) }}
                                        <div class="progress progress-sm mt-1"><div class="progress-bar bg-success" style="width: {{ (float) ($group['progress'] ?? 0) }}%"></div></div>
                                    </td>
                                    <td class="text-end">{{ $fmt_pct($group['weighted']) }}</td>
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
                                @if ($show_progress)<td class="text-end">{{ $fmt_pct($sheet['progress']) }}</td><td class="text-end">{{ $fmt_pct($sheet['progress']) }}</td>@endif
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    @endif
@endif

@if ($show_prices && count($sheet['minimums']) > 0)
    <div class="card mb-3">
        <div class="card-header">
            <div>
                <h3 class="card-title">Batas Minimum &amp; Negosiasi</h3>
                <div class="card-subtitle">Hanya tampil di internal. Dokumen untuk pelanggan tidak menampilkan penerapan minimum.</div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-sm">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Berlaku</th>
                        <th class="text-end">Minimum</th>
                        <th class="text-end">Terpakai</th>
                        <th class="text-end">Ditagih</th>
                        <th>Keterangan</th>
                        @if ($can_edit)<th class="w-1"></th>@endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sheet['minimums'] as $minimum_row)
                        @php $override = $minimum_row['override']; @endphp
                        <tr>
                            <td><div class="small text-secondary">{{ $minimum_row['item']->code }}</div><div>{{ $minimum_row['item']->name }}</div></td>
                            <td class="text-nowrap">{{ $minimum_row['scope'] === 'piece' ? 'Per keping' : 'Total proyek' }} <span class="text-secondary small">({{ $minimum_row['line_count'] }} baris)</span></td>
                            <td class="text-end text-nowrap">
                                @if ($override)
                                    <span class="badge bg-purple-lt">{{ bccomp((string) $override['minimum'], '0', 4) === 0 ? 'Ditiadakan' : $fmt_qty($override['minimum']).' '.$minimum_row['unit'] }}</span>
                                    @if ($minimum_row['original'] !== null)<div class="small text-secondary text-decoration-line-through">{{ $fmt_qty($minimum_row['original']) }} {{ $minimum_row['unit'] }}</div>@endif
                                @else
                                    {{ $minimum_row['original'] !== null ? $fmt_qty($minimum_row['original']).' '.$minimum_row['unit'] : '-' }}
                                @endif
                            </td>
                            <td class="text-end text-nowrap">{{ $fmt_qty($minimum_row['used']) }} {{ $minimum_row['unit'] }}</td>
                            <td class="text-end text-nowrap fw-semibold">{{ $fmt_qty($minimum_row['billed']) }} {{ $minimum_row['unit'] }}</td>
                            <td class="small">
                                @if (bccomp($minimum_row['shortfall'], '0', 4) > 0)
                                    <span class="text-warning">Kekurangan {{ $fmt_qty($minimum_row['shortfall']) }} {{ $minimum_row['unit'] }} dibebankan ke baris pemakaian terbesar.</span>
                                @endif
                                @if ($override)
                                    <div class="text-secondary">Negosiasi: {{ $override['reason'] }} — {{ $override['user_name'] ?? 'Pengguna' }}, {{ \Carbon\CarbonImmutable::parse($override['at'])->timezone(config('app.timezone'))->locale('id')->translatedFormat('j F Y H.i') }} WIB</div>
                                @endif
                            </td>
                            @if ($can_edit)
                                <td class="text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary boq-minimum-edit"
                                        data-item-id="{{ $minimum_row['item']->unique_id }}"
                                        data-item-name="{{ $minimum_row['item']->code.' — '.$minimum_row['item']->name }}"
                                        data-unit="{{ $minimum_row['unit'] }}"
                                        data-minimum="{{ $override['minimum'] ?? $minimum_row['original'] }}"
                                        data-reason="{{ $override['reason'] ?? '' }}">Ubah</button>
                                    @if ($override)
                                        <form method="POST" action="{{ route('project.job-document.workflow.minimum-override.save', $job_base) }}" class="d-inline">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="price_book_item_id" value="{{ $minimum_row['item']->unique_id }}">
                                            <input type="hidden" name="action" value="clear">
                                            <button type="submit" class="btn btn-sm btn-ghost-secondary">Reset</button>
                                        </form>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer small text-secondary">
            <i class="ti ti-info-circle me-1"></i>Perubahan minimum hasil negosiasi tercatat di riwayat dokumen. Total akhir setelah negosiasi disetujui oleh Manajer Pemasaran.
        </div>
    </div>
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
                        @if ($progress_weight_column)<th class="text-end">Bobot</th>@endif
                        <th class="text-end">Progres</th>
                        <th class="text-end">Tertimbang</th>
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
                            @if ($progress_weight_column)<td class="text-end fw-bold">{{ $fmt_pct($group['weight']) }}</td>@endif
                            <td class="text-end fw-bold">{{ $fmt_pct($group['progress']) }}</td>
                            <td class="text-end fw-bold">{{ $fmt_pct($group['weighted']) }}</td>
                            <td></td>
                        @endif
                        @if ($can_edit)<td></td>@endif
                    </tr>
                    @foreach ($group['rows'] as $row)
                        @php
                            $job = $row['job'];
                            $calculation = $job->calculation;
                            $job_route = [...$job_base, $job->unique_id];
                            $job_photos_payload = $job->photos->map(fn ($p): array => [
                                'id' => $p->unique_id,
                                'category' => $p->photo_category,
                                'category_label' => match ($p->photo_category) {
                                    'quotation' => 'Penawaran',
                                    'progress' => 'Progres',
                                    'result_before' => 'Sebelum Perbaikan',
                                    'result_after' => 'Sesudah Perbaikan',
                                    default => $p->photo_category ? ucfirst($p->photo_category) : 'Umum',
                                },
                                'caption' => $p->caption,
                                'taken_at' => $p->taken_at ? $p->taken_at->timezone('Asia/Jakarta')->tanggal_waktu_indonesia().' WIB' : null,
                                'created_at' => $p->created_at ? $p->created_at->timezone('Asia/Jakarta')->tanggal_waktu_indonesia().' WIB' : null,
                                'url' => route('project.job-document.workflow.job.photo.show', [...$job_route, $p->unique_id]),
                                'delete_url' => route('project.job-document.workflow.job.photo.destroy', [...$job_route, $p->unique_id]),
                            ])->values()->all();
                            $job_payload = [
                                'id' => $job->unique_id,
                                'action' => route('project.job-document.workflow.job.update', $job_route),
                                'is_child' => $job->parent_job_id !== null,
                                'job_name' => $job->job_name,
                                'project_job_group_id' => $group['group']?->unique_id,
                                'price_item' => $job->price_book_item ? ['id' => $job->price_book_item->unique_id, 'text' => $job->price_book_item->code.' — '.$job->price_book_item->name, 'rule' => $job->price_book_item->calculation_rule] : null,
                                'calculation_rule' => $job->calculation_rule,
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
                        <tr class="boq-job-row {{ $row['depth'] === 0 ? 'boq-root' : '' }}" data-job-id="{{ $job->unique_id }}" data-search="{{ mb_strtolower($job->job_name.' '.$job->materials->pluck('material_name')->implode(' ')) }}">
                            <td class="text-secondary text-nowrap">{{ $row['number'] }}</td>
                            <td>
                                <div style="padding-left: {{ $row['depth'] * 1.25 }}rem">
                                    <div class="{{ $row['depth'] === 0 ? 'fw-bold' : 'fw-semibold' }}">
                                        @if ($row['depth'] > 0)<i class="ti ti-corner-down-right text-secondary me-1"></i>@endif{{ $job->job_name }}
                                    </div>
                                    <div class="small text-secondary d-flex flex-wrap column-gap-2">
                                        @if ($job->price_book_item)<span><i class="ti ti-book me-1"></i>{{ $job->price_book_item->code }}</span>@endif
                                        @if ($job->calculation_rule)<span class="badge bg-azure-lt">{{ $rule_labels[$job->calculation_rule] ?? $job->calculation_rule }}</span>@endif
                                        @if ($job->responsible_kind === 'owner')
                                            <span class="badge bg-purple-lt">Vendor Pemilik</span>
                                            @php($owner_approval_labels = ['pending' => 'Menunggu Persetujuan Direksi', 'approved' => 'Disetujui Direksi', 'rejected' => 'Ditolak Direksi'])
                                            <span class="badge {{ $job->owner_vendor_approval_status === 'approved' ? 'bg-success-lt' : ($job->owner_vendor_approval_status === 'rejected' ? 'bg-danger-lt' : 'bg-warning-lt') }}">{{ $owner_approval_labels[$job->owner_vendor_approval_status] ?? $job->owner_vendor_approval_status }}</span>
                                            @if ($document->document_type === \App\Models\ProjectJobDocument::TYPE_FINAL_BOQ && $job->owner_vendor_surcharge > 0)
                                                <span class="badge bg-orange-lt">Surcharge {{ \App\Services\project_boq_service::OWNER_VENDOR_SURCHARGE_PERCENT }}%: {{ $fmt_money($job->owner_vendor_surcharge) }}</span>
                                            @endif
                                            @if ($job->owner_vendor_decision_note)<span class="text-secondary">Catatan keputusan: {{ $job->owner_vendor_decision_note }}</span>@endif
                                            @if ($document->document_type === \App\Models\ProjectJobDocument::TYPE_FINAL_BOQ && $can_approve_owner_vendor_job && $document->is_editable())
                                                <span class="d-inline-flex flex-wrap gap-1">
                                                    <form method="POST" action="{{ route('project.job-document.workflow.job.owner-vendor-decision', $job_route) }}" onsubmit="return confirm('Setujui penggunaan vendor pemilik untuk pekerjaan ini?')">
                                                        @csrf<input type="hidden" name="decision" value="approved">
                                                        <button type="submit" class="btn btn-sm btn-success">Setujui vendor pemilik</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('project.job-document.workflow.job.owner-vendor-decision', $job_route) }}" onsubmit="return confirm('Tolak penggunaan vendor pemilik untuk pekerjaan ini?')">
                                                        @csrf<input type="hidden" name="decision" value="rejected">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Tolak</button>
                                                    </form>
                                                </span>
                                            @endif
                                        @endif
                                        @if ($show_prices && $job->minimum_quantity !== null)<span class="text-warning">Min. {{ $fmt_qty($job->minimum_quantity) }} {{ $job->unit }}</span>@endif
                                        @if ($row['calculation_text'])<span><i class="ti ti-calculator me-1"></i>{{ $row['calculation_text'] }}</span>@endif
                                        @if ($job->responsible_name)<span><i class="ti ti-user me-1"></i>{{ $job->responsible_name }}</span>@endif
                                        @if ($job->est_start_date || $job->est_finish_date)<span><i class="ti ti-calendar me-1"></i>{{ $fmt_date($job->est_start_date) ?? '-' }} – {{ $fmt_date($job->est_finish_date) ?? '-' }}</span>@endif
                                        <span class="boq-job-photo-badge-wrapper" data-job-id="{{ $job->unique_id }}">
                                            <button type="button" class="btn btn-sm btn-ghost-secondary p-0 border-0 text-secondary boq-job-photos-btn {{ $job->photos->isEmpty() ? 'd-none' : '' }}"
                                                data-job-id="{{ $job->unique_id }}"
                                                data-job-name="{{ $row['number'].' '.$job->job_name }}"
                                                data-upload-url="{{ route('project.job-document.workflow.job.photo.store', $job_route) }}"
                                                data-photos-url="{{ route('project.job-document.workflow.job.photos', $job_route) }}"
                                                data-photos="{{ json_encode($job_photos_payload) }}"
                                                title="Lihat foto pekerjaan">
                                                <i class="ti ti-photo me-1"></i><span class="boq-job-photo-count">{{ $job->photos->count() }}</span> foto
                                            </button>
                                        </span>
                                    </div>
                                    @if ($job->notes)<div class="small text-secondary fst-italic">{{ $job->notes }}</div>@endif
                                </div>
                            </td>
                            <td class="text-end text-nowrap">
                                {{ $fmt_qty($job->job_volume_estimated) }}
                                @if ($show_prices && $billed_differs($job->billed_quantity, $job->job_volume_estimated))
                                    <div class="small text-warning" title="Volume yang ditagihkan setelah aturan perhitungan dan minimum">Tagih {{ $fmt_qty($job->billed_quantity) }}</div>
                                @endif
                            </td>
                            <td>{{ $job->unit ?? '-' }}</td>
                            @if ($show_prices)
                                <td class="text-end text-nowrap">{{ $job->unit_price !== null ? $fmt_money($job->unit_price) : '-' }}</td>
                                <td class="text-end text-nowrap">
                                    {{ $job->est_price !== null ? $fmt_money($job->est_price) : '-' }}
                                    @if ($row['has_children'] || bccomp($row['materials_total'], '0', 2) > 0)
                                        <div class="small text-secondary" title="Termasuk material dan sub pekerjaan">Σ {{ $fmt_money($row['subtotal']) }}</div>
                                    @endif
                                </td>
                                <td class="text-end">{{ $fmt_pct($show_progress ? ($row['progress']['weight'] ?? null) : $job->job_weight_percent) }}</td>
                            @endif
                            @if ($show_progress)
                                @php($row_status = $row['progress']['status'] ?? $job->status)
                                @if ($progress_weight_column)<td class="text-end">{{ $fmt_pct($row['progress']['weight'] ?? null) }}</td>@endif
                                <td class="text-end text-nowrap {{ $row['has_children'] ? 'fst-italic' : '' }}" @if ($row['has_children']) title="Dihitung dari sub pekerjaan" @endif>{{ $fmt_pct($row['progress']['actual'] ?? '0') }}</td>
                                <td class="text-end text-nowrap">{{ $fmt_pct($row['progress']['weighted'] ?? null) }}</td>
                                <td><span class="badge {{ $job_status_classes[$row_status] ?? 'bg-secondary-lt' }}">{{ $job_status_labels[$row_status] ?? $row_status }}</span></td>
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
                                            <button type="button" class="dropdown-item boq-job-photos-btn"
                                                data-job-id="{{ $job->unique_id }}"
                                                data-job-name="{{ $row['number'].' '.$job->job_name }}"
                                                data-upload-url="{{ route('project.job-document.workflow.job.photo.store', $job_route) }}"
                                                data-photos-url="{{ route('project.job-document.workflow.job.photos', $job_route) }}"
                                                data-photos="{{ json_encode($job_photos_payload) }}">
                                                <i class="ti ti-photo me-2"></i>Foto
                                            </button>
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
                                        @if ($material->owner_supplied)<span class="badge bg-azure-lt ms-1">Disediakan Pemilik · tidak ditagihkan</span>@endif
                                        @if ($show_prices && ! $material->owner_supplied && $material->minimum_quantity !== null)<span class="text-warning">· Min. {{ $fmt_qty($material->minimum_quantity) }} {{ $material->unit }}</span>@endif
                                    </div>
                                </td>
                                <td class="text-end small text-nowrap">
                                    {{ $fmt_qty($material->volume) }}
                                    @if ($show_prices && ! $material->owner_supplied && $billed_differs($material->billed_quantity, $material->volume))
                                        <div class="text-warning">Tagih {{ $fmt_qty($material->billed_quantity) }}</div>
                                    @endif
                                </td>
                                <td class="small">{{ $material->unit ?? '-' }}</td>
                                @if ($show_prices)
                                    <td class="text-end small text-nowrap">{{ ! $material->owner_supplied && $material->unit_price !== null ? $fmt_money($material->unit_price) : '-' }}</td>
                                    <td class="text-end small text-nowrap">{{ $material->price !== null ? $fmt_money($material->price) : '-' }}</td>
                                    <td></td>
                                @endif
                                @if ($show_progress)<td colspan="{{ $progress_weight_column ? 4 : 3 }}"></td>@endif
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
                                                'owner_supplied' => $material->owner_supplied,
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

@include('project.job-document.partials.photo-modal')

@if ($can_edit && $show_prices && count($sheet['minimums']) > 0)
    <div class="modal modal-blur fade" id="boq-minimum-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" id="boq-minimum-form" method="POST" action="{{ route('project.job-document.workflow.minimum-override.save', $job_base) }}" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="action" value="set">
                <input type="hidden" name="price_book_item_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title">Minimum Hasil Negosiasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    @if ($errors->has('minimum_quantity') || $errors->has('reason') || $errors->has('price_book_item_id'))
                        <div class="alert alert-danger"><ul class="mb-0 ps-3">@foreach (['price_book_item_id', 'minimum_quantity', 'reason'] as $error_key)@foreach ($errors->get($error_key) as $error)<li>{{ $error }}</li>@endforeach @endforeach</ul></div>
                    @endif
                    <div class="mb-3 fw-semibold" id="boq-minimum-item"></div>
                    <div class="mb-3">
                        <label class="form-label required" for="boq-minimum-quantity">Minimum Baru</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="minimum_quantity" id="boq-minimum-quantity" min="0" step="0.0001" required>
                            <span class="input-group-text" id="boq-minimum-unit"></span>
                        </div>
                        <div class="form-hint">Isi 0 untuk meniadakan minimum pada dokumen ini.</div>
                    </div>
                    <div>
                        <label class="form-label required" for="boq-minimum-reason">Alasan Negosiasi</label>
                        <textarea class="form-control" name="reason" id="boq-minimum-reason" rows="3" minlength="5" maxlength="500" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
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

            const $minimum_form = $('#boq-minimum-form');
            if ($minimum_form.length) {
                $minimum_form.validate({
                    errorElement: 'div',
                    errorClass: 'invalid-feedback',
                    highlight: element => $(element).addClass('is-invalid'),
                    unhighlight: element => $(element).removeClass('is-invalid'),
                    errorPlacement: (error, element) => error.insertAfter(element.closest('.input-group').length ? element.closest('.input-group') : element),
                    rules: { minimum_quantity: { required: true, number: true, min: 0, max: 999999999 }, reason: { required: true, minlength: 5, maxlength: 500 } },
                    messages: {
                        minimum_quantity: { required: 'Minimum baru wajib diisi.', number: 'Masukkan angka yang valid.', min: 'Minimum tidak boleh negatif.' },
                        reason: { required: 'Alasan negosiasi wajib diisi.', minlength: 'Alasan minimal 5 karakter.', maxlength: 'Alasan maksimal 500 karakter.' },
                    },
                });
                const open_minimum = function (data) {
                    $minimum_form.find('[name=price_book_item_id]').val(data.item_id);
                    $('#boq-minimum-item').text(data.item_name || '');
                    $('#boq-minimum-unit').text(data.unit || '');
                    $('#boq-minimum-quantity').val(data.minimum || '');
                    $('#boq-minimum-reason').val(data.reason || '');
                    $('#boq-minimum-modal').modal('show');
                };
                $(document).on('click', '.boq-minimum-edit', function () {
                    $minimum_form.find('.alert-danger').remove();
                    open_minimum({ item_id: this.dataset.itemId, item_name: this.dataset.itemName, unit: this.dataset.unit, minimum: this.dataset.minimum, reason: this.dataset.reason });
                });
                const old_minimum = @json($old_minimum);
                if (old_minimum) {
                    const $button = $('.boq-minimum-edit[data-item-id="' + old_minimum.item_id + '"]');
                    open_minimum({ ...old_minimum, item_name: $button.data('itemName'), unit: $button.data('unit') });
                }
            }
        })();
    </script>
@endpush
