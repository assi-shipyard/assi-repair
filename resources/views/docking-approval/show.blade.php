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
        $space = $effective_space;
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
                    <div class="card-actions"><span class="badge bg-blue-lt text-blue">{{ $docking_request->status_label() }}</span></div>
                </div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item"><div class="datagrid-title">Kapal</div><div class="datagrid-content">{{ $ship?->name ?? '-' }}</div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Pemilik</div><div class="datagrid-content">{{ $ship?->company?->name ?? '-' }}</div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Tipe</div><div class="datagrid-content">{{ $ship?->type?->name ?? '-' }}</div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Jadwal mulai</div><div class="datagrid-content">{{ $docking_request->requested_start_at?->tanggal_waktu_indonesia() }}</div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Jadwal selesai</div><div class="datagrid-content">{{ $docking_request->requested_end_at?->tanggal_waktu_indonesia() ?? '-' }}</div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Docking space diminta</div><div class="datagrid-content">{{ $docking_request->requested_docking_space?->name ?? '-' }}</div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Rekomendasi Engineering</div><div class="datagrid-content">{{ $docking_request->recommended_docking_space?->name ?? '-' }}</div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Diajukan oleh</div><div class="datagrid-content">{{ $docking_request->requester?->employee_id ?? '-' }}</div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Diajukan pada</div><div class="datagrid-content">{{ $docking_request->created_at?->tanggal_waktu_indonesia() }}</div></div>
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
                            <span class="text-secondary small">{{ $document->created_at?->tanggal_waktu_indonesia() }}</span>
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
                                <div class="text-success small">Disetujui {{ $docking_request->engineering_approver?->employee_id ?? '-' }}, {{ $docking_request->engineering_approved_at->tanggal_waktu_indonesia() }}</div>
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
                                <div class="text-success small">Disetujui {{ $docking_request->production_approver?->employee_id ?? '-' }}, {{ $docking_request->production_approved_at->tanggal_waktu_indonesia() }}</div>
                            @elseif ($docking_request->rejection_stage === 'production')
                                <div class="text-danger small">Ditolak: {{ $docking_request->rejection_reason }}</div>
                            @elseif ($docking_request->request_status === 'awaiting_marketing')
                                <div class="text-orange small">
                                    Disarankan {{ $docking_request->production_recommendation === 'postpone' ? 'ditunda' : 'dibatalkan' }}
                                    @if ($docking_request->production_recommendation === 'postpone')
                                        ke {{ $docking_request->recommended_start_at?->tanggal_waktu_indonesia() }} s/d {{ $docking_request->recommended_end_at?->tanggal_waktu_indonesia() }}
                                    @endif
                                    - menunggu konfirmasi Marketing
                                </div>
                            @else
                                <div class="text-secondary small">Menunggu</div>
                            @endif
                            @if ($docking_request->production_notes && $docking_request->rejection_stage !== 'production')
                                <div class="small">Catatan: {{ $docking_request->production_notes }}</div>
                            @endif
                            @if ($docking_request->marketing_response)
                                <div class="small mt-1">Tanggapan Marketing ({{ $docking_request->marketing_responder?->employee_id ?? '-' }}, {{ $docking_request->marketing_responded_at?->tanggal_waktu_indonesia() }}): {{ $docking_request->marketing_response === 'accept' ? 'customer setuju' : 'customer keberatan' }}@if ($docking_request->marketing_notes) - {{ $docking_request->marketing_notes }}@endif</div>
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
                            @if ($stage === 'engineering')
                                <div class="mb-3">
                                    <label class="form-label">Docking space rekomendasi <span class="text-danger">*</span></label>
                                    <select class="form-select" name="recommended_docking_space">
                                        <option value="">Pilih docking space (wajib saat menyetujui)</option>
                                        @foreach ($space_options as $option)
                                            @php $selectable = $option['is_compatible'] && ! $option['has_conflict']; @endphp
                                            <option value="{{ $option['space']->unique_id }}" @disabled(! $selectable) @selected(old('recommended_docking_space', $docking_request->requested_docking_space?->unique_id) === $option['space']->unique_id && $selectable)>
                                                {{ $option['space']->name }}{{ ! $option['is_compatible'] ? ' - tidak sesuai spesifikasi' : ($option['has_conflict'] ? ' - jadwal bentrok' : '') }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-hint">Pilih berdasarkan hasil perhitungan dari gambar kapal. Dok yang tidak sesuai atau bentrok tidak dapat dipilih.</div>
                                    @error('recommended_docking_space')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                            @endif
                            <div class="mb-3">
                                <label class="form-label">Catatan</label>
                                <textarea class="form-control" name="notes" rows="4" maxlength="2000" placeholder="Wajib diisi bila menolak">{{ old('notes') }}</textarea>
                                @error('notes')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                            @if ($stage === 'production')
                                <div id="postpone-fields" class="row g-2 mb-3 {{ $errors->has('recommended_start_at') || $errors->has('recommended_end_at') ? '' : 'd-none' }}">
                                    <div class="col-12 text-secondary small">Usulan jadwal baru yang akan dikonfirmasi Marketing kepada customer.</div>
                                    <div class="col-md-6">
                                        <label class="form-label">Mulai usulan</label>
                                        <input type="datetime-local" class="form-control" name="recommended_start_at" value="{{ old('recommended_start_at') }}">
                                        @error('recommended_start_at')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Selesai usulan</label>
                                        <input type="datetime-local" class="form-control" name="recommended_end_at" value="{{ old('recommended_end_at') }}">
                                        @error('recommended_end_at')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            @endif
                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" name="decision" value="approve" class="btn btn-success flex-fill">Setujui</button>
                                <button type="submit" name="decision" value="reject" class="btn btn-outline-danger flex-fill">Tolak</button>
                                @if ($stage === 'production')
                                    <button type="submit" name="decision" value="postpone" class="btn btn-outline-warning flex-fill" id="btn-postpone">Sarankan Tunda</button>
                                    <button type="submit" name="decision" value="cancel" class="btn btn-outline-secondary flex-fill">Sarankan Batal</button>
                                @endif
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

            const space_select = form.find('[name="recommended_docking_space"]');

            form.find('button[value="reject"]').on('click', function (event) {
                if (space_select.length) { space_select.rules('remove', 'required'); }
                form.find('[name="notes"]').rules('add', { required: true, messages: { required: 'Alasan penolakan wajib diisi.' } });
                if (!form.valid()) {
                    event.preventDefault();
                }
            });

            const start_input = form.find('[name="recommended_start_at"]');
            const end_input = form.find('[name="recommended_end_at"]');

            form.find('button[value="reject"], button[value="cancel"], button[value="approve"]').on('click', function () {
                if (start_input.length) {
                    start_input.rules('remove', 'required');
                    end_input.rules('remove', 'required');
                }
            });

            // First click reveals the schedule fields; the second click validates and submits.
            form.find('button[value="postpone"]').on('click', function (event) {
                const fields = $('#postpone-fields');

                if (fields.hasClass('d-none')) {
                    event.preventDefault();
                    fields.removeClass('d-none');
                    return;
                }

                const required = { required: true, messages: { required: 'Wajib diisi.' } };
                start_input.rules('add', required);
                end_input.rules('add', required);
                form.find('[name="notes"]').rules('add', { required: true, messages: { required: 'Alasan penundaan wajib diisi.' } });

                if (!form.valid()) {
                    event.preventDefault();
                }
            });

            form.find('button[value="cancel"]').on('click', function (event) {
                form.find('[name="notes"]').rules('add', { required: true, messages: { required: 'Alasan pembatalan wajib diisi.' } });

                if (!form.valid()) {
                    event.preventDefault();
                }
            });

            form.find('button[value="approve"]').on('click', function () {
                if (space_select.length) {
                    space_select.rules('add', { required: true, messages: { required: 'Docking space rekomendasi wajib dipilih.' } });
                }
                form.find('[name="notes"]').rules('remove', 'required');
            });
        });
    </script>
@endpush
