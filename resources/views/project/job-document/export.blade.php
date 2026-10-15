@php
    $features = $sheet['features'];
    $show_prices = $features['export_prices'];
    $show_progress = $features['progress'];
    $progress_weight_column = $show_progress && ! $show_prices;
    $money = fn ($value): string => $value === null ? '-' : number_format((float) $value, 2, ',', '.');
    $qty = fn ($value): string => $value === null ? '-' : rtrim(rtrim(number_format((float) $value, 4, ',', '.'), '0'), ',');
    $percent = fn ($value): string => $value === null ? '-' : number_format((float) $value, 2, ',', '.').'%';
    $status_labels = ['pending' => 'Belum Mulai', 'ongoing' => 'Berjalan', 'completed' => 'Selesai', 'cancelled' => 'Batal'];
    $column_count = 4 + ($show_prices ? 3 : 0) + ($show_progress ? 3 + ($progress_weight_column ? 1 : 0) : 0);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $document_label }} - {{ $project->project_code }}</title>
    <style>
        @font-face { font-family: 'Tahoma'; font-weight: normal; src: url('{{ public_path('assets/fonts/tahoma.ttf') }}') format('truetype'); }
        @font-face { font-family: 'Tahoma'; font-weight: bold; src: url('{{ public_path('assets/fonts/tahomabd.ttf') }}') format('truetype'); }
        @page { margin: 18mm 12mm 16mm 12mm; }
        body { font-family: 'Tahoma', sans-serif; font-size: 8.5pt; color: #1d273b; }
        h1 { font-size: 13pt; margin: 0 0 2px; }
        .muted { color: #667382; }
        .meta { width: 100%; margin: 8px 0 10px; border-collapse: collapse; }
        .meta td { padding: 1px 4px 1px 0; vertical-align: top; }
        .sheet { width: 100%; border-collapse: collapse; }
        .sheet th, .sheet td { border: 0.6pt solid #9aa4b2; padding: 3px 4px; vertical-align: top; }
        .sheet thead th { background: #e9ecef; font-weight: bold; text-align: center; }
        .sheet thead { display: table-header-group; }
        .sheet tr { page-break-inside: avoid; }
        .group td { background: #f1f3f5; font-weight: bold; }
        .material td { color: #49566a; font-size: 8pt; }
        .total td { font-weight: bold; background: #e9ecef; }
        .right { text-align: right; }
        .center { text-align: center; }
        .nowrap { white-space: nowrap; }
        .small { font-size: 7.5pt; }
        .summary { width: 55%; border-collapse: collapse; margin-top: 12px; }
        .summary th, .summary td { border: 0.6pt solid #9aa4b2; padding: 3px 4px; }
        .summary th { background: #e9ecef; }
        .signatures { width: 100%; margin-top: 24px; }
        .signatures td { width: 50%; text-align: center; vertical-align: top; }
        .footer { position: fixed; bottom: -10mm; left: 0; right: 0; font-size: 7pt; color: #667382; }
    </style>
</head>
<body>
    <div class="footer">Dicetak {{ $printed_at->timezone('Asia/Jakarta')->tanggal_waktu_indonesia() }} WIB · {{ $document_label }} Revisi {{ $document->revision_no }} · {{ $project->project_code }}</div>

    <h1>{{ strtoupper($document_label) }}</h1>
    <div class="muted">PT Adiluhung Saranasegara Indonesia</div>

    <table class="meta">
        <tr>
            <td style="width: 14%">Kode Proyek</td><td style="width: 36%">: {{ $project->project_code }}</td>
            <td style="width: 14%">No. Dokumen</td><td>: {{ $document->document_number ?: '-' }}</td>
        </tr>
        <tr>
            <td>Kapal</td><td>: {{ $project->ship?->name ?? '-' }}</td>
            <td>Revisi</td><td>: {{ $document->revision_no }}</td>
        </tr>
        <tr>
            <td>Disiapkan</td><td>: {{ $document->prepared_by?->name ?? '-' }}</td>
            <td>Disetujui</td><td>: {{ $document->approved_at ? $document->approved_at->timezone('Asia/Jakarta')->tanggal_waktu_indonesia().' WIB' : '-' }}</td>
        </tr>
    </table>

    <table class="sheet">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th>Uraian Pekerjaan</th>
                <th style="width: 8%">Volume</th>
                <th style="width: 6%">Sat.</th>
                @if ($show_prices)
                    <th style="width: 12%">Harga Satuan (IDR)</th>
                    <th style="width: 13%">Jumlah (IDR)</th>
                    <th style="width: 6%">Bobot</th>
                @endif
                @if ($show_progress)
                    @if ($progress_weight_column)<th style="width: 7%">Bobot</th>@endif
                    <th style="width: 7%">Progres</th>
                    <th style="width: 8%">Tertimbang</th>
                    <th style="width: 8%">Status</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($sheet['groups'] as $group)
                <tr class="group">
                    <td class="center">{{ $group['roman'] }}</td>
                    <td colspan="{{ $show_prices ? 4 : 3 }}">{{ strtoupper($group['label']) }}</td>
                    @if ($show_prices)
                        <td class="right nowrap">{{ $money($group['total']) }}</td>
                        <td class="right">{{ $percent($group['weight']) }}</td>
                    @endif
                    @if ($show_progress)
                        @if ($progress_weight_column)<td class="right">{{ $percent($group['weight']) }}</td>@endif
                        <td class="right">{{ $percent($group['progress']) }}</td>
                        <td class="right">{{ $percent($group['weighted']) }}</td>
                        <td></td>
                    @endif
                </tr>
                @foreach ($group['rows'] as $row)
                    @php($job = $row['job'])
                    <tr>
                        <td class="nowrap">{{ $row['number'] }}</td>
                        <td style="padding-left: {{ 4 + $row['depth'] * 12 }}px">
                            {{ $job->job_name }}
                            @if ($job->responsible_kind === 'owner')
                                <div class="small muted">Vendor Pemilik · {{ $job->owner_vendor_approval_status === 'approved' ? 'Disetujui Direksi' : 'Belum disetujui Direksi' }}</div>
                                @if ($job->owner_vendor_surcharge > 0)<div class="small muted">Surcharge vendor pemilik {{ \App\Services\project_boq_service::OWNER_VENDOR_SURCHARGE_PERCENT }}%: {{ $money($job->owner_vendor_surcharge) }}</div>@endif
                            @endif
                            @if ($row['calculation_text_customer'])<div class="small muted">{{ $row['calculation_text_customer'] }}</div>@endif
                            @if ($job->notes)<div class="small muted">{{ $job->notes }}</div>@endif
                        </td>
                        <td class="right">{{ $qty($show_prices ? ($job->billed_quantity ?? $job->job_volume_estimated) : $job->job_volume_estimated) }}</td>
                        <td class="center">{{ $job->unit ?? '-' }}</td>
                        @if ($show_prices)
                            <td class="right nowrap">{{ $money($job->unit_price) }}</td>
                            <td class="right nowrap">{{ $money($job->est_price) }}</td>
                            <td class="right">{{ $percent($show_progress ? ($row['progress']['weight'] ?? null) : $job->job_weight_percent) }}</td>
                        @endif
                        @if ($show_progress)
                            @if ($progress_weight_column)<td class="right">{{ $percent($row['progress']['weight'] ?? null) }}</td>@endif
                            <td class="right">{{ $percent($row['progress']['actual'] ?? '0') }}</td>
                            <td class="right">{{ $percent($row['progress']['weighted'] ?? null) }}</td>
                            <td class="center">{{ $status_labels[$row['progress']['status'] ?? $job->status] ?? $job->status }}</td>
                        @endif
                    </tr>
                    @foreach ($row['materials'] as $material_row)
                        @php($material = $material_row['material'])
                        <tr class="material">
                            <td></td>
                            <td style="padding-left: {{ 16 + $row['depth'] * 12 }}px">
                                {{ $material_row['letter'] }}. {{ $material->material_name }}@if ($material->dimension) · {{ $material->dimension }}@endif
                                @if ($material->owner_supplied)<div class="small muted">Disediakan Pemilik — tidak ditagihkan</div>@endif
                            </td>
                            <td class="right">{{ $qty($show_prices && ! $material->owner_supplied ? ($material->billed_quantity ?? $material->volume) : $material->volume) }}</td>
                            <td class="center">{{ $material->unit ?? '-' }}</td>
                            @if ($show_prices)
                                <td class="right nowrap">{{ $material->owner_supplied ? '-' : $money($material->unit_price) }}</td>
                                <td class="right nowrap">{{ $money($material->price) }}</td>
                                <td></td>
                            @endif
                            @if ($show_progress)<td colspan="{{ $progress_weight_column ? 4 : 3 }}"></td>@endif
                        </tr>
                    @endforeach
                @endforeach
            @empty
                <tr><td colspan="{{ $column_count }}" class="center muted">Belum ada pekerjaan.</td></tr>
            @endforelse
            @if ($show_prices && count($sheet['groups']) > 0)
                <tr class="total">
                    <td colspan="5" class="right">TOTAL</td>
                    <td class="right nowrap">{{ $money($sheet['grand_total']) }}</td>
                    <td colspan="{{ $column_count - 6 }}"></td>
                </tr>
            @endif
        </tbody>
    </table>

    @if (($show_prices || $show_progress) && count($sheet['groups']) > 0)
        <table class="summary">
            <thead>
                <tr>
                    <th style="width: 8%">No</th>
                    <th>Rekapitulasi Kelompok Pekerjaan</th>
                    @if ($show_prices)<th>Jumlah (IDR)</th>@endif
                    @if ($show_prices || $show_progress)<th>Bobot</th>@endif
                    @if ($show_progress)<th>Progres</th><th>Tertimbang</th>@endif
                </tr>
            </thead>
            <tbody>
                @foreach ($sheet['groups'] as $group)
                    <tr>
                        <td class="center">{{ $group['roman'] }}</td>
                        <td>{{ $group['label'] }}</td>
                        @if ($show_prices)<td class="right nowrap">{{ $money($group['total']) }}</td>@endif
                        <td class="right">{{ $percent($group['weight']) }}</td>
                        @if ($show_progress)<td class="right">{{ $percent($group['progress']) }}</td><td class="right">{{ $percent($group['weighted']) }}</td>@endif
                    </tr>
                @endforeach
                <tr class="total">
                    <td colspan="2" class="right">TOTAL</td>
                    @if ($show_prices)<td class="right nowrap">{{ $money($sheet['grand_total']) }}</td>@endif
                    <td class="right">{{ count($sheet['groups']) > 0 && collect($sheet['groups'])->contains(fn ($group) => $group['weight'] !== null) ? '100,00%' : '-' }}</td>
                    @if ($show_progress)<td class="right">{{ $percent($sheet['progress']) }}</td><td class="right">{{ $percent($sheet['progress']) }}</td>@endif
                </tr>
            </tbody>
        </table>
    @endif

    <table class="signatures">
        <tr>
            <td>Disiapkan oleh,<br><br><br><br><strong>{{ $document->prepared_by?->name ?? '(....................)' }}</strong></td>
            <td>Disetujui oleh,<br><br><br><br><strong>{{ $document->approved_by?->name ?? '(....................)' }}</strong></td>
        </tr>
    </table>
</body>
</html>
