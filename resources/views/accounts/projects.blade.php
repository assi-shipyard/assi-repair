@extends('layouts.app')
@section('title', 'Layanan Pelanggan')
@section('body_title', 'Akses & Persetujuan Pelanggan')
@section('buttons_beside_title')
    @role('admin')<a class="btn btn-outline-secondary" href="{{ route('accounts.customers') }}">Akun Pelanggan</a>@endrole
@endsection
@section('content')
    @include('partials.flash')
    <div class="card mb-3"><div class="card-body">
        <h3 class="card-title">Akses Teknis Tidak Sama dengan Akses Harga</h3>
        <p class="text-secondary mb-0">Surveyor eksternal dapat melihat proyek dan menyetujui Catatan Kepuasan tanpa akses harga. Persetujuan RAB memerlukan izin harga dan konfirmasi kata sandi pelanggan. Salinan revisi yang diterbitkan tidak berubah saat dokumen internal diedit.</p>
    </div></div>
    @role('admin')
    <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">Atur Akses Proyek</h3></div>
        <div class="card-body">
            <form id="access_form" class="customer_form" method="POST" action="{{ route('accounts.access') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6"><label for="user_id" class="form-label">Pelanggan</label><select id="user_id" name="user_id" class="form-select" required><option value="">Pilih pelanggan</option>@foreach ($customers as $customer)<option value="{{ $customer->unique_id }}" @selected(old('user_id') === $customer->unique_id)>{{ $customer->name }} - {{ $customer->email }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label for="project_id" class="form-label">Proyek</label><select id="project_id" name="project_id" class="form-select" required><option value="">Pilih proyek</option>@foreach ($projects as $project)<option value="{{ $project->unique_id }}" @selected(old('project_id') === $project->unique_id)>{{ $project->project_code }} - {{ $project->ship?->name }}</option>@endforeach</select></div>
                    @foreach (['is_active' => 'Akses Proyek Aktif', 'view_prices' => 'Izin Membuka Harga', 'approve_initial_boq' => 'Persetujuan RAB Awal', 'approve_satisfaction_notes' => 'Persetujuan Catatan Kepuasan', 'approve_final_boq' => 'Persetujuan RAB Akhir'] as $field => $label)
                        <div class="col-sm-6 col-lg-4"><label for="{{ $field }}" class="form-label">{{ $label }}</label><select id="{{ $field }}" name="{{ $field }}" class="form-select" required><option value="0" @selected((string) old($field, $field === 'is_active' ? '1' : '0') === '0')>Tidak</option><option value="1" @selected((string) old($field, $field === 'is_active' ? '1' : '0') === '1')>Ya</option></select></div>
                    @endforeach
                </div>
                <p class="text-secondary mt-3">Menyimpan akun dan proyek yang sama mengganti izin sebelumnya. Untuk mencabut akses, pilih akses proyek aktif: Tidak.</p>
                <button class="btn btn-primary" type="submit">Simpan Akses</button>
            </form>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">Akses Terdaftar</h3></div>
        <div class="table-responsive"><table class="table table-vcenter card-table">
            <thead><tr><th>Pelanggan</th><th>Proyek</th><th>Akses</th><th>Harga</th><th>Persetujuan</th><th>Tindakan</th></tr></thead>
            <tbody>@forelse ($access_entries as $entry)<tr><td>{{ $entry->user->name }}</td><td>{{ $entry->project->project_code }}</td><td>{{ $entry->is_active ? 'Aktif' : 'Dicabut' }}</td><td>{{ $entry->view_prices ? 'Diizinkan' : 'Tidak Diizinkan' }}</td><td>{{ implode(', ', array_filter([$entry->approve_initial_boq ? 'RAB Awal' : null, $entry->approve_satisfaction_notes ? 'Catatan Kepuasan' : null, $entry->approve_final_boq ? 'RAB Akhir' : null])) ?: '-' }}</td><td>
                <button type="button" class="btn btn-sm btn-outline-primary edit_access" data-access="{{ json_encode(['user_id' => $entry->user->unique_id, 'project_id' => $entry->project->unique_id, 'is_active' => (int) $entry->is_active, 'view_prices' => (int) $entry->view_prices, 'approve_initial_boq' => (int) $entry->approve_initial_boq, 'approve_satisfaction_notes' => (int) $entry->approve_satisfaction_notes, 'approve_final_boq' => (int) $entry->approve_final_boq]) }}">Ubah Akses</button>
            </td></tr>@empty<tr><td>Belum ada akses proyek.</td></tr>@endforelse</tbody>
        </table></div>
        <div class="card-footer">{{ $access_entries->links() }}</div>
    </div>
    @endrole
    <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">Terbitkan untuk Persetujuan Pelanggan</h3></div>
        <div class="card-body">
            <form class="customer_form" method="POST" action="{{ route('accounts.publish') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-7"><label for="document_id" class="form-label">Dokumen yang Disetujui Internal</label><select id="document_id" name="document_id" class="form-select" required><option value="">Pilih revisi terbaru</option>@foreach ($documents as $document)<option value="{{ $document->unique_id }}" @selected(old('document_id') === $document->unique_id)>{{ $document->project->project_code }} - {{ \App\Models\CustomerDocument::TYPE_LABELS[$document->document_type] }} - Revisi {{ $document->revision_no }}</option>@endforeach</select></div>
                    <div class="col-md-5"><label for="approver_id" class="form-label">Pemberi Persetujuan</label><select id="approver_id" name="approver_id" class="form-select" required><option value="">Pilih pelanggan berwenang</option>@foreach ($customers as $customer)<option value="{{ $customer->unique_id }}" @selected(old('approver_id') === $customer->unique_id)>{{ $customer->name }} - {{ $customer->email }}</option>@endforeach</select></div>
                </div>
                <p class="text-secondary mt-3">Pelanggan harus memiliki akses dan wewenang untuk proyek ini. Revisi baru menggantikan permintaan persetujuan lama yang belum diputuskan.</p>
                <button type="submit" class="btn btn-primary">Terbitkan Dokumen</button>
            </form>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">Riwayat Persetujuan Pelanggan</h3></div>
        <div class="table-responsive"><table class="table table-vcenter card-table">
            <thead><tr><th>Proyek / Dokumen</th><th>Pemberi Persetujuan</th><th>Status</th><th>Keputusan</th></tr></thead>
            <tbody>@forelse ($publications as $publication)<tr><td>{{ $publication->project->project_code }}<div class="small text-secondary">{{ \App\Models\CustomerDocument::TYPE_LABELS[$publication->document_type] }} - Revisi {{ $publication->revision_no }}</div></td><td>{{ $publication->approver->name }}
                @if ($publication->status === 'pending')
                    <form class="customer_form mt-2" method="POST" action="{{ route('accounts.reassign', $publication) }}">
                        @csrf @method('PUT')
                        <label for="approver_{{ $publication->unique_id }}" class="visually-hidden">Pemberi Persetujuan Baru</label>
                        <select id="approver_{{ $publication->unique_id }}" name="approver_id" class="form-select form-select-sm" required><option value="">Pilih pengganti</option>@foreach ($customers as $customer)<option value="{{ $customer->unique_id }}">{{ $customer->name }}</option>@endforeach</select>
                        <button class="btn btn-sm btn-outline-secondary mt-1" type="submit">Alihkan</button>
                    </form>
                @endif
            </td><td>{{ \App\Models\CustomerDocument::STATUS_LABELS[$publication->status] }}</td><td>{{ $publication->decided_at?->timezone('Asia/Jakarta')->tanggal_waktu_indonesia() }}@if ($publication->decided_at) WIB @endif<div class="small text-secondary text-break">{{ $publication->decision_comment }}</div></td></tr>@empty<tr><td>Belum ada dokumen yang diterbitkan.</td></tr>@endforelse</tbody>
        </table></div>
        <div class="card-footer">{{ $publications->links() }}</div>
    </div>
    <div class="card">
        <div class="card-header"><h3 class="card-title">Kepuasan Pelanggan &amp; Komplain</h3></div>
        <div class="list-group list-group-flush">
            @forelse ($feedback as $entry)
                <div class="list-group-item">
                    <div class="d-flex flex-wrap justify-content-between gap-2"><strong>{{ $entry->project->project_code }} - {{ $entry->user->name }}</strong><span class="badge bg-blue-lt">{{ ['submitted' => 'Dikirim', 'in_progress' => 'Ditangani', 'resolved' => 'Selesai'][$entry->status] }}</span></div>
                    <div class="text-secondary small mt-1">{{ $entry->kind === 'complaint' ? 'Komplain' : 'Kepuasan Pelanggan ('.$entry->rating.'/5)' }} &middot; {{ $entry->created_at->timezone('Asia/Jakarta')->tanggal_waktu_indonesia() }} WIB</div>
                    <p class="text-break mt-2">{{ $entry->message }}</p>
                    <form class="customer_form" method="POST" action="{{ route('accounts.respond', $entry) }}">
                        @csrf @method('PUT')
                        <div class="row g-2">
                            <div class="col-md-7"><label for="response_{{ $entry->unique_id }}" class="form-label">Tanggapan</label><textarea id="response_{{ $entry->unique_id }}" name="response" class="form-control" rows="2" required maxlength="4000">{{ $entry->response }}</textarea></div>
                            <div class="col-md-3"><label for="status_{{ $entry->unique_id }}" class="form-label">Status Penanganan</label><select id="status_{{ $entry->unique_id }}" name="status" class="form-select" required><option value="in_progress" @selected($entry->status === 'in_progress')>Ditangani</option><option value="resolved" @selected($entry->status === 'resolved')>Selesai</option></select></div>
                            <div class="col-md-2 d-flex align-items-end"><button class="btn btn-outline-primary w-100" type="submit">Simpan</button></div>
                        </div>
                    </form>
                </div>
            @empty<div class="list-group-item text-secondary">Belum ada masukan pelanggan.</div>@endforelse
        </div>
        <div class="card-footer">{{ $feedback->links() }}</div>
    </div>
    @include('customer.validation')
@endsection
@push('scripts')
<script>
    $(function () {
        $('.edit_access').on('click', function () {
            const access_data = $(this).data('access');
            $.each(access_data, function (field, value) { $('#access_form [name="' + field + '"]').val(String(value)).trigger('change'); });
            $('#access_form').get(0).scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    });
</script>
@endpush
