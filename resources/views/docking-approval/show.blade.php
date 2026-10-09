@extends('layouts.app')

@section('title', $stage_title)
@section('body_title', 'Tinjau Permohonan Docking')

@section('buttons_beside_title')
    <a href="{{ route('docking-approval.index', $stage) }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
    @include('partials.flash')

    @php
        $ship = $docking_request->ship;
        $space = $docking_request->requested_docking_space;
        $status_labels = [
            'submitted' => 'Menunggu Engineering',
            'engineering_approved' => 'Menunggu Produksi',
            'approved' => 'Disetujui / menunggu kedatangan',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
        ];
        $fit_rows = [
            ['Panjang (LOA)', 'length_overall', 'max_length', 'length', 'm'],
            ['Lebar (Breadth)', 'breadth', 'max_breadth', 'breadth', 'm'],
            ['Draft', 'draft', 'max_draft', 'draft', 'm'],
            ['Tonnage', 'gross_tonnage', 'max_tonnage', 'tonnage', 'GT'],
        ];
    @endphp

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Kapal dan jadwal</h3>
                    <div class="card-actions"><span class="badge bg-blue-lt text-blue">{{ $status_labels[$docking_request->request_status] ?? $docking_request->request_status }}</span></div>
                </div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item"><div class="datagrid-title">Kapal</div><div class="datagrid-content">{{ $ship?->name ?? '-' }}</div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Pemilik</div><div class="datagrid-content">{{ $ship?->company?->name ?? '-' }}</div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Tipe</div><div class="datagrid-content">{{ $ship?->type?->name ?? '-' }}</div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Jadwal mulai</div><div class="datagrid-content">{{ $docking_request->requested_start_at?->format('d/m/Y H:i') }}</div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Jadwal selesai</div><div class="datagrid-content">{{ $docking_request->requested_end_at?->format('d/m/Y H:i') ?? '-' }}</div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Docking space</div><div class="datagrid-content">{{ $space?->name ?? '-' }}</div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Diajukan oleh</div><div class="datagrid-content">{{ $docking_request->requester?->employee_id ?? '-' }}</div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Diajukan pada</div><div class="datagrid-content">{{ $docking_request->created_at?->format('d/m/Y H:i') }}</div></div>
                    </div>
                    @if ($docking_request->request_notes)
                        <div class="mt-3">
                            <div class="datagrid-title">Catatan pemohon</div>
                            <div style="white-space: pre-line">{{ $docking_request->request_notes }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Kesesuaian kapal dengan docking space</h3>
                    @if ($evaluation)
                        <div class="card-actions">
                            <span class="badge {{ $evaluation['is_compatible'] ? 'bg-success-lt text-success' : 'bg-red-lt text-red' }}">
                                {{ $evaluation['is_compatible'] ? 'Lolos pemeriksaan sistem' : 'Tidak sesuai' }}
                            </span>
                        </div>
                    @endif
                </div>
                @if ($evaluation)
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead><tr><th>Parameter</th><th>Kapal</th><th>Batas docking space</th><th>Sisa margin</th><th>Hasil</th></tr></thead>
                            <tbody>
                                @foreach ($fit_rows as [$label, $ship_key, $space_key, $check_key, $unit])
                                    @php
                                        $ship_value = $evaluation['ship_snapshot'][$ship_key] ?? null;
                                        $space_value = $evaluation['docking_space_snapshot'][$space_key] ?? null;
                                        $margin = ($ship_value !== null && $space_value !== null) ? $space_value - $ship_value : null;
                                        $passed = $evaluation['checks'][$check_key] ?? false;
                                    @endphp
                                    <tr>
                                        <td>{{ $label }}</td>
                                        <td>{{ $ship_value !== null ? rtrim(rtrim(number_format($ship_value, 2, ',', '.'), '0'), ',') . ' ' . $unit : '-' }}</td>
                                        <td>{{ $space_value !== null ? rtrim(rtrim(number_format($space_value, 2, ',', '.'), '0'), ',') . ' ' . $unit : 'Tidak dibatasi' }}</td>
                                        <td>{{ $margin !== null ? rtrim(rtrim(number_format($margin, 2, ',', '.'), '0'), ',') . ' ' . $unit : '-' }}</td>
                                        <td><span class="badge {{ $passed ? 'bg-success-lt text-success' : 'bg-red-lt text-red' }}">{{ $passed ? 'Sesuai' : 'Melebihi' }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-body border-top text-secondary small">
                        Pemeriksaan sistem hanya bersifat awal berdasarkan data kapal. Engineering menghitung lebih lanjut dari dokumen terlampir.
                    </div>
                @else
                    <div class="card-body text-secondary">Docking space belum ditentukan.</div>
                @endif
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Dokumen terlampir</h3></div>
                <div class="list-group list-group-flush">
                    @forelse ($docking_request->documents as $document)
                        <a class="list-group-item list-group-item-action d-flex justify-content-between" href="{{ route('docking-space-request.documents.download', [$docking_request->unique_id, $document->unique_id]) }}">
                            <span>{{ $document->document_name }}</span>
                            <span class="text-secondary small">{{ $document->created_at?->format('d/m/Y H:i') }}</span>
                        </a>
                    @empty
                        <div class="list-group-item text-secondary">Tidak ada dokumen.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">Alur persetujuan</h3></div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <div class="fw-medium">1. Pemeriksaan sistem</div>
                            <div class="text-success small">Lolos saat pengajuan</div>
                        </li>
                        <li class="mb-3">
                            <div class="fw-medium">2. Engineering (Biro Litbang)</div>
                            @if ($docking_request->engineering_approved_at)
                                <div class="text-success small">Disetujui {{ $docking_request->engineering_approver?->employee_id ?? '-' }}, {{ $docking_request->engineering_approved_at->format('d/m/Y H:i') }}</div>
                            @elseif ($docking_request->rejection_stage === 'engineering')
                                <div class="text-danger small">Ditolak: {{ $docking_request->rejection_reason }}</div>
                            @else
                                <div class="text-secondary small">Menunggu</div>
                            @endif
                            @if ($docking_request->engineering_notes && $docking_request->rejection_stage !== 'engineering')
                                <div class="small">Catatan: {{ $docking_request->engineering_notes }}</div>
                            @endif
                        </li>
                        <li>
                            <div class="fw-medium">3. Produksi (Divisi Reparasi dan Rekayasa Umum)</div>
                            @if ($docking_request->production_approved_at)
                                <div class="text-success small">Disetujui {{ $docking_request->production_approver?->employee_id ?? '-' }}, {{ $docking_request->production_approved_at->format('d/m/Y H:i') }}</div>
                            @elseif ($docking_request->rejection_stage === 'production')
                                <div class="text-danger small">Ditolak: {{ $docking_request->rejection_reason }}</div>
                            @else
                                <div class="text-secondary small">Menunggu</div>
                            @endif
                            @if ($docking_request->production_notes && $docking_request->rejection_stage !== 'production')
                                <div class="small">Catatan: {{ $docking_request->production_notes }}</div>
                            @endif
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Keputusan</h3></div>
                <div class="card-body">
                    @if ($can_decide)
                        <form method="POST" action="{{ route('docking-space-request.' . $stage . '-review', $docking_request->unique_id) }}" id="approval-form">
                            @csrf
                            <input type="hidden" name="return_to_queue" value="1">
                            <div class="mb-3">
                                <label class="form-label">Catatan</label>
                                <textarea class="form-control" name="notes" rows="4" maxlength="2000" placeholder="Wajib diisi bila menolak">{{ old('notes') }}</textarea>
                                @error('notes')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" name="decision" value="approve" class="btn btn-success flex-fill">Setujui</button>
                                <button type="submit" name="decision" value="reject" class="btn btn-outline-danger flex-fill">Tolak</button>
                            </div>
                        </form>
                    @else
                        <div class="text-secondary">Permohonan ini tidak sedang menunggu keputusan Anda.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            const form = $('#approval-form');
            if (!form.length) {
                return;
            }

            form.validate({
                errorClass: 'is-invalid',
                errorElement: 'div',
                errorPlacement: function (error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('div').append(error);
                },
                rules: { notes: { maxlength: 2000 } },
                messages: { notes: { maxlength: 'Catatan maksimal 2000 karakter.' } },
            });

            form.find('button[value="reject"]').on('click', function (event) {
                form.find('[name="notes"]').rules('add', { required: true, messages: { required: 'Alasan penolakan wajib diisi.' } });
                if (!form.valid()) {
                    event.preventDefault();
                }
            });

            form.find('button[value="approve"]').on('click', function () {
                form.find('[name="notes"]').rules('remove', 'required');
            });
        });
    </script>
@endpush
