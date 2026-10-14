@extends('layouts.app')
@section('title', 'Detail Proyek')
@section('body_title', $project->project_code)
@section('buttons_beside_title')
    <a class="btn btn-outline-secondary" href="{{ route('customer.index') }}">Proyek Saya</a>
@endsection
@section('content')
    @include('partials.flash')
    <div class="card mb-3"><div class="card-body">
        <h3 class="card-title">{{ $project->ship?->name ?? '-' }}</h3>
        <div class="row g-3">
            <div class="col-sm-4"><div class="text-secondary">Progres</div><strong>{{ $project->progress }}%</strong></div>
            <div class="col-sm-4"><div class="text-secondary">Estimasi Mulai</div>{{ $project->start_date_estimation?->tanggal_indonesia() ?? '-' }}</div>
            <div class="col-sm-4"><div class="text-secondary">Estimasi Selesai</div>{{ $project->end_date_estimation?->tanggal_indonesia() ?? '-' }}</div>
        </div>
    </div></div>
    <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">Dokumen &amp; Persetujuan</h3></div>
        <div class="list-group list-group-flush">
            @forelse ($documents as $document)
                <a class="list-group-item list-group-item-action" href="{{ route('customer.document', $document) }}">
                    <div class="d-flex flex-wrap justify-content-between gap-2">
                        <span>{{ \App\Models\CustomerDocument::TYPE_LABELS[$document->document_type] }} &middot; Revisi {{ $document->revision_no }}</span>
                        <span class="badge bg-blue-lt">{{ \App\Models\CustomerDocument::STATUS_LABELS[$document->status] }}</span>
                    </div>
                    @if ($document->approver_id === auth()->id() && $document->status === 'pending')
                        <div class="text-secondary small mt-1">Ditugaskan kepada Anda untuk memberikan keputusan.</div>
                    @endif
                </a>
            @empty
                <div class="list-group-item text-secondary">Belum ada dokumen yang diterbitkan untuk pelanggan.</div>
            @endforelse
        </div>
        <div class="card-footer">{{ $documents->links() }}</div>
    </div>
    <div class="row g-3">
        <div class="col-lg-5"><div class="card">
            <div class="card-header"><h3 class="card-title">Kepuasan Pelanggan &amp; Komplain</h3></div>
            <div class="card-body">
                <p class="text-secondary">Komplain dapat dikirim selama proyek berlangsung maupun setelah selesai. Penilaian kepuasan tersedia setelah proyek selesai, satu kali per akun.</p>
                <form class="customer_form" method="POST" action="{{ route('customer.feedback', $project->unique_id) }}">
                    @csrf
                    <div class="mb-3"><label for="kind" class="form-label">Jenis Masukan</label><select id="kind" name="kind" class="form-select" required>
                        <option value="complaint" @selected(old('kind') === 'complaint')>Komplain</option>
                        @if ($project->status === 'Completed')<option value="satisfaction" @selected(old('kind') === 'satisfaction')>Kepuasan Pelanggan</option>@endif
                    </select></div>
                    <div class="mb-3" id="rating_group"><label for="rating" class="form-label">Nilai Kepuasan</label><select id="rating" name="rating" class="form-select"><option value="">Pilih nilai</option>
                        @foreach ([1 => 'Sangat Tidak Puas', 2 => 'Tidak Puas', 3 => 'Cukup', 4 => 'Puas', 5 => 'Sangat Puas'] as $rating => $label)
                            <option value="{{ $rating }}" @selected((string) old('rating') === (string) $rating)>{{ $rating }} - {{ $label }}</option>
                        @endforeach
                    </select></div>
                    <div class="mb-3"><label for="message" class="form-label">Pesan</label><textarea id="message" name="message" class="form-control" rows="4" required maxlength="4000">{{ old('message') }}</textarea></div>
                    <button class="btn btn-primary" type="submit">Kirim Masukan</button>
                </form>
            </div>
        </div></div>
        <div class="col-lg-7"><div class="card">
            <div class="card-header"><h3 class="card-title">Masukan Saya</h3></div>
            <div class="list-group list-group-flush">
                @forelse ($feedback as $entry)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between gap-2"><strong>{{ $entry->kind === 'complaint' ? 'Komplain' : 'Kepuasan Pelanggan' }} {{ $entry->rating ? '('.$entry->rating.'/5)' : '' }}</strong><span class="badge bg-blue-lt">{{ ['submitted' => 'Dikirim', 'in_progress' => 'Ditangani', 'resolved' => 'Selesai'][$entry->status] }}</span></div>
                        <div class="text-secondary small mt-1">{{ $entry->created_at->timezone('Asia/Jakarta')->tanggal_waktu_indonesia() }} WIB</div>
                        <p class="mt-2 mb-0 text-break">{{ $entry->message }}</p>
                        @if ($entry->response)<div class="border-start ps-3 mt-3"><strong>Tanggapan ASSI</strong><p class="mb-1 text-break">{{ $entry->response }}</p><small class="text-secondary">{{ $entry->responded_at->timezone('Asia/Jakarta')->tanggal_waktu_indonesia() }} WIB</small></div>@endif
                    </div>
                @empty
                    <div class="list-group-item text-secondary">Belum ada masukan.</div>
                @endforelse
            </div>
            <div class="card-footer">{{ $feedback->links() }}</div>
        </div></div>
    </div>
    @include('customer.validation')
@endsection
@push('scripts')
<script>
    $(function () {
        function toggle_rating() {
            const is_satisfaction = $('#kind').val() === 'satisfaction';
            $('#rating_group').toggle(is_satisfaction);
            $('#rating').prop('required', is_satisfaction).prop('disabled', !is_satisfaction);
        }
        $('#kind').on('change', toggle_rating);
        toggle_rating();
    });
</script>
@endpush
