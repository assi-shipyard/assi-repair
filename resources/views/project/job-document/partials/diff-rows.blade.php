@php
    $type_meta = [
        'added' => ['label' => 'Ditambahkan', 'class' => 'bg-success-lt text-success', 'icon' => 'ti-plus'],
        'removed' => ['label' => 'Dihapus', 'class' => 'bg-danger-lt text-danger', 'icon' => 'ti-minus'],
        'changed' => ['label' => 'Diubah', 'class' => 'bg-warning-lt text-warning', 'icon' => 'ti-pencil'],
        'unchanged' => ['label' => 'Tetap', 'class' => 'bg-secondary-lt text-secondary', 'icon' => 'ti-equal'],
    ];
    $money_fields = ['est_price', 'price'];
    $value_labels = [
        'responsible_kind' => ['internal_unit' => 'Unit Internal', 'subcontractor' => 'Subkontraktor', 'owner' => 'Vendor Pemilik'],
        'owner_vendor_approval_status' => ['not_required' => 'Tidak diperlukan', 'pending' => 'Menunggu persetujuan', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'],
    ];
    $show_unchanged = $show_unchanged ?? false;
    $format_value = function ($field, $value) use ($money_fields, $value_labels) {
        if ($value === null) {
            return '—';
        }
        if (isset($value_labels[$field][$value])) {
            return $value_labels[$field][$value];
        }
        return in_array($field, $money_fields, true) && is_numeric($value) ? number_format((float) $value, 0, ',', '.') : $value;
    };
@endphp

<div class="row g-2 mb-3">
    @foreach (['changed', 'added', 'removed', 'unchanged'] as $key)
        <div class="col-6 col-md-3">
            <div class="card card-sm"><div class="card-body d-flex align-items-center gap-2">
                <span class="avatar avatar-sm {{ $type_meta[$key]['class'] }}"><i class="ti {{ $type_meta[$key]['icon'] }}"></i></span>
                <div><div class="fw-bold">{{ $comparison['counts'][$key] }}</div><div class="text-secondary small">{{ $type_meta[$key]['label'] }}</div></div>
            </div></div>
        </div>
    @endforeach
</div>

@if (count($comparison['totals']) > 0)
    <div class="row g-2 mb-3">
        @foreach ($comparison['totals'] as $currency => $total)
            <div class="col-md-6 col-xl-4">
                <div class="card card-sm"><div class="card-body">
                    <div class="text-secondary small mb-1">Total Harga Estimasi ({{ $currency }})</div>
                    <div class="d-flex flex-wrap align-items-baseline gap-2">
                        <span class="text-secondary text-decoration-line-through">{{ number_format($total['old'], 0, ',', '.') }}</span>
                        <i class="ti ti-arrow-right text-secondary"></i>
                        <span class="h3 mb-0">{{ number_format($total['new'], 0, ',', '.') }}</span>
                    </div>
                    <div class="mt-1 {{ $total['delta'] > 0 ? 'text-danger' : ($total['delta'] < 0 ? 'text-success' : 'text-secondary') }}">
                        <i class="ti {{ $total['delta'] > 0 ? 'ti-trending-up' : ($total['delta'] < 0 ? 'ti-trending-down' : 'ti-minus') }}"></i>
                        {{ $total['delta'] > 0 ? '+' : '' }}{{ number_format($total['delta'], 0, ',', '.') }}
                        @if ($total['percent'] !== null) ({{ $total['delta'] > 0 ? '+' : '' }}{{ number_format($total['percent'], 1, ',', '.') }}%) @endif
                    </div>
                </div></div>
            </div>
        @endforeach
    </div>
@endif

<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead><tr><th class="w-1">Perubahan</th><th>Pekerjaan</th><th>Detail Perubahan (sebelum → sesudah)</th></tr></thead>
            <tbody>
                @php($visible_rows = collect($comparison['rows'])->filter(fn ($row) => $show_unchanged || $row['type'] !== 'unchanged'))
                @forelse ($visible_rows as $row)
                    <tr>
                        <td><span class="badge {{ $type_meta[$row['type']]['class'] }}">{{ $type_meta[$row['type']]['label'] }}</span></td>
                        <td class="fw-semibold">{{ $row['name'] }}</td>
                        <td>
                            @if ($row['type'] === 'added')
                                <span class="text-success">Pekerjaan baru &middot; harga {{ $format_value('est_price', $row['new']->est_price) }} {{ $row['new']->est_currency }}</span>
                            @elseif ($row['type'] === 'removed')
                                <span class="text-danger">Dihapus &middot; harga sebelumnya {{ $format_value('est_price', $row['old']->est_price) }} {{ $row['old']->est_currency }}</span>
                            @endif
                            @foreach ($row['changes'] as $change)
                                <div class="small"><span class="text-secondary">{{ $change['label'] }}:</span>
                                    <span class="text-danger text-decoration-line-through">{{ $format_value($change['field'], $change['old']) }}</span>
                                    <i class="ti ti-arrow-right text-secondary"></i>
                                    <span class="text-success fw-semibold">{{ $format_value($change['field'], $change['new']) }}</span>
                                </div>
                            @endforeach
                            @foreach ($row['materials'] as $material)
                                <div class="small mt-1">
                                    <span class="badge {{ $type_meta[$material['type']]['class'] }}">Material {{ strtolower($type_meta[$material['type']]['label']) }}</span>
                                    {{ $material['name'] }}
                                    @foreach ($material['changes'] as $change)
                                        &middot; {{ $change['label'] }}: <span class="text-danger text-decoration-line-through">{{ $format_value($change['field'], $change['old']) }}</span> → <span class="text-success">{{ $format_value($change['field'], $change['new']) }}</span>
                                    @endforeach
                                </div>
                            @endforeach
                            @if ($row['type'] === 'unchanged')<span class="text-secondary small">Tidak ada perubahan.</span>@endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-secondary py-4">Tidak ada perbedaan antara kedua revisi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
