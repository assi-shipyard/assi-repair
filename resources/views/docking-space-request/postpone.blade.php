@extends('layouts.app')

@section('title', 'Tunda Jadwal Docking')
@section('body_title', 'Tunda Jadwal Docking')

@section('buttons_beside_title')
    <a href="{{ route('docking-space-request.index') }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
    @include('partials.flash')

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">Jadwal saat ini</h3></div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item"><div class="datagrid-title">Kapal</div><div class="datagrid-content">{{ $docking_request->ship?->name ?? '-' }}</div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Docking space</div><div class="datagrid-content">{{ $space?->name ?? '-' }}</div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Durasi</div><div class="datagrid-content">{{ round($duration_minutes / 1440, 1) }} hari</div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Docking</div><div class="datagrid-content">{{ $docking_request->requested_start_at?->tanggal_waktu_indonesia() }}</div></div>
                        <div class="datagrid-item"><div class="datagrid-title">Undocking</div><div class="datagrid-content">{{ $docking_request->requested_end_at?->tanggal_waktu_indonesia() ?? '-' }}</div></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Jadwal baru</h3></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('docking-space-request.postpone.store', $docking_request->unique_id) }}" id="postpone-form">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label required">Tanggal docking baru</label>
                                <input type="datetime-local" class="form-control @error('new_start_at') is-invalid @enderror" name="new_start_at" id="new_start_at" value="{{ old('new_start_at') }}">
                                @error('new_start_at')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Tanggal undocking baru</label>
                                <input type="datetime-local" class="form-control @error('new_end_at') is-invalid @enderror" name="new_end_at" id="new_end_at" value="{{ old('new_end_at') }}">
                                @error('new_end_at')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label required">Alasan penundaan</label>
                                <textarea class="form-control @error('reason') is-invalid @enderror" name="reason" rows="3" maxlength="2000" placeholder="Contoh: kapal sebelumnya masih memerlukan pekerjaan di dock.">{{ old('reason') }}</textarea>
                                @error('reason')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="mt-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Simpan Penundaan</button>
                            <a href="{{ route('docking-space-request.index') }}" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">Saran jadwal kosong</h3></div>
                <div class="card-body">
                    <p class="text-secondary small mb-3">Slot terdekat pada <strong>{{ $space?->name ?? 'docking space' }}</strong> dengan durasi sama ({{ round($duration_minutes / 1440, 1) }} hari), dihitung sejak {{ \Carbon\Carbon::parse($earliest)->tanggal_waktu_indonesia() }}.</p>
                    <div class="list-group list-group-flush border rounded mb-3">
                        @forelse ($suggestions as $suggestion)
                            <div class="list-group-item">
                                <div class="row align-items-center g-2">
                                    <div class="col">
                                        <div class="text-secondary small">Docking</div>
                                        <div class="fw-medium">{{ $suggestion['start']->tanggal_waktu_indonesia() }}</div>
                                        <div class="text-secondary small mt-1">Undocking</div>
                                        <div class="fw-medium">{{ $suggestion['end']->tanggal_waktu_indonesia() }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <button type="button" class="btn btn-sm btn-primary js-use-suggestion"
                                            data-start="{{ $suggestion['start']->format('Y-m-d\TH:i') }}" data-end="{{ $suggestion['end']->format('Y-m-d\TH:i') }}">Pakai</button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="list-group-item text-secondary">Tidak ditemukan slot kosong dalam 1 tahun ke depan pada docking space ini.</div>
                        @endforelse
                    </div>

                    <form method="GET" action="{{ route('docking-space-request.postpone', $docking_request->unique_id) }}" class="mt-3">
                        <label class="form-label">Cari slot mulai dari</label>
                        <div class="input-group">
                            <input type="datetime-local" class="form-control" name="from" value="{{ \Carbon\Carbon::parse($earliest)->format('Y-m-d\TH:i') }}">
                            <button class="btn btn-outline-secondary" type="submit">Cari</button>
                        </div>
                    </form>
                </div>
            </div>

            @if ($docking_request->postponements->isNotEmpty())
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Riwayat penundaan</h3></div>
                    <div class="list-group list-group-flush">
                        @foreach ($docking_request->postponements as $item)
                            <div class="list-group-item">
                                <div class="small">{{ $item->previous_start_at->tanggal_waktu_indonesia() }} &rarr; {{ $item->new_start_at->tanggal_waktu_indonesia() }}</div>
                                <div class="text-secondary small">{{ $item->reason }}</div>
                                <div class="text-secondary small">{{ $item->postponer?->employee_id ?? '-' }}, {{ $item->created_at->tanggal_waktu_indonesia() }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            $('.js-use-suggestion').on('click', function () {
                $('#new_start_at').val($(this).data('start')).valid();
                $('#new_end_at').val($(this).data('end')).valid();
                document.getElementById('postpone-form').scrollIntoView({ behavior: 'smooth' });
            });

            $('#postpone-form').validate({
                errorClass: 'is-invalid',
                errorElement: 'div',
                errorPlacement: function (error, element) {
                    error.addClass('invalid-feedback d-block');
                    element.after(error);
                },
                rules: {
                    new_start_at: { required: true },
                    new_end_at: { required: true },
                    reason: { required: true, maxlength: 2000 },
                },
                messages: {
                    new_start_at: { required: 'Tanggal docking baru wajib diisi.' },
                    new_end_at: { required: 'Tanggal undocking baru wajib diisi.' },
                    reason: { required: 'Alasan penundaan wajib diisi.', maxlength: 'Alasan maksimal 2000 karakter.' },
                },
            });
        });
    </script>
@endpush
