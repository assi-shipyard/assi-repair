@extends('layouts.app')
@section('title', $document_data['title'])
@section('body_title', $document_data['title'].' - Revisi '.$document_data['revision_no'])
@section('buttons_beside_title')
    <a class="btn btn-outline-secondary" href="{{ route('customer.project', $project->unique_id) }}">Kembali ke Proyek</a>
@endsection
@section('content')
    @include('partials.flash')
    <div class="card mb-3"><div class="card-body">
        <div class="d-flex flex-wrap justify-content-between gap-2">
            <strong>{{ $project->project_code }} &middot; {{ $project->ship?->name ?? '-' }}</strong>
            <span class="badge bg-blue-lt">{{ \App\Models\customer_document::STATUS_LABELS[$document_data['status']] }}</span>
        </div>
        <p class="text-secondary mt-2 mb-0">Dokumen ini merupakan salinan revisi yang diterbitkan oleh ASSI. Persetujuan Anda tidak menggantikan persetujuan internal.</p>
    </div></div>
    @if ($document_data['document_type'] !== 'satisfaction_notes')
        <div class="card mb-3 d-print-none"><div class="card-body">
            @if ($document_data['prices_unlocked'])
                <div class="d-flex flex-wrap justify-content-between gap-3">
                    <div><strong>Harga Terbuka Sementara</strong><p class="text-secondary mb-0">Harga dikunci otomatis setelah 5 menit. Jangan bagikan harga kepada pihak yang tidak berwenang.</p></div>
                    <form method="POST" action="{{ route('customer.lock', $document_data['unique_id']) }}">@csrf<button class="btn btn-outline-secondary" type="submit">Kunci Harga</button></form>
                </div>
                <div class="financial-value mt-3">
                    <span class="text-secondary">Total RAB:</span>
                    @forelse ($document_data['prices']['totals'] as $currency => $amount)
                        <strong class="me-3">{{ $currency }} {{ number_format($amount, 2, ',', '.') }}</strong>
                    @empty<span>-</span>@endforelse
                </div>
            @elseif ($document_data['can_unlock'])
                <h3 class="card-title">Harga RAB Terkunci</h3>
                <p class="text-secondary">Gunakan kata sandi akun Anda untuk membuka harga dokumen ini selama 5 menit.</p>
                <form class="customer_form row g-2" method="POST" action="{{ route('customer.unlock', $document_data['unique_id']) }}">
                    @csrf
                    <div class="col-sm-8"><label for="current_password" class="visually-hidden">Kata Sandi Akun</label><input id="current_password" name="current_password" type="password" class="form-control" required maxlength="1024" placeholder="Kata sandi akun Anda" autocomplete="current-password"></div>
                    <div class="col-sm-4"><button type="submit" class="btn btn-primary w-100">Buka Harga</button></div>
                </form>
            @else
                <strong>Harga RAB Terkunci</strong><p class="text-secondary mb-0">Akun Anda hanya memiliki akses teknis. Hubungi administrator jika memerlukan akses harga.</p>
            @endif
        </div></div>
    @endif
    <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">Daftar Pekerjaan</h3></div>
        <div class="table-responsive"><table class="table table-vcenter card-table">
            <thead><tr><th>Pekerjaan</th><th>Volume</th><th>Progres</th><th>Jadwal</th>@if ($document_data['prices_unlocked'])<th class="financial-value">Harga</th>@endif</tr></thead>
            <tbody>
                @forelse ($document_data['snapshot']['jobs'] as $job)
                    <tr>
                        <td><strong>{{ $job['name'] }}</strong>
                            @foreach ($job['materials'] as $material)
                                <div class="small text-secondary mt-1">{{ $material['name'] }} &middot; {{ $material['volume'] ?? '-' }} {{ $material['dimension'] ?? '' }}
                                    @if ($document_data['prices_unlocked'])
                                        @php($material_price = $document_data['prices']['jobs'][$job['unique_id']]['materials'][$material['unique_id']])
                                        <span class="financial-value"> &middot; {{ $material_price['currency'] }} {{ number_format((float) $material_price['price'], 2, ',', '.') }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </td>
                        <td>{{ $job['volume'] ?? '-' }}@if ($job['actual_volume'] !== null)<div class="small text-secondary">Aktual: {{ $job['actual_volume'] }}</div>@endif</td>
                        <td>{{ $job['progress'] ?? '0' }}%</td>
                        <td class="small">{{ $job['start_date'] ?? '-' }}<br>{{ $job['end_date'] ?? '-' }}</td>
                        @if ($document_data['prices_unlocked'])
                            @php($job_price = $document_data['prices']['jobs'][$job['unique_id']])
                            <td class="financial-value text-nowrap">{{ $job_price['currency'] }} {{ number_format((float) $job_price['price'], 2, ',', '.') }}</td>
                        @endif
                    </tr>
                @empty
                    <tr><td>Belum ada pekerjaan.</td></tr>
                @endforelse
            </tbody>
        </table></div>
    </div>
    @if ($document_data['can_decide'])
        <div class="card d-print-none"><div class="card-header"><h3 class="card-title">Keputusan Anda</h3></div><div class="card-body">
            <form class="customer_form" method="POST" action="{{ route('customer.decision', $document_data['unique_id']) }}">
                @csrf
                <div class="mb-3"><label for="decision" class="form-label">Keputusan</label><select id="decision" name="decision" class="form-select" required><option value="">Pilih keputusan</option><option value="approved" @selected(old('decision') === 'approved')>Setujui</option><option value="changes_requested" @selected(old('decision') === 'changes_requested')>Minta Perubahan</option><option value="rejected" @selected(old('decision') === 'rejected')>Tolak</option></select></div>
                <div class="mb-3"><label for="comment" class="form-label">Komentar / Alasan</label><textarea id="comment" name="comment" class="form-control" rows="3" maxlength="2000">{{ old('comment') }}</textarea></div>
                <p class="text-secondary">Keputusan dicatat atas nama akun Anda dan tidak dapat diubah. Pastikan Anda telah memeriksa revisi ini.</p>
                <button class="btn btn-primary" type="submit">Kirim Keputusan</button>
            </form>
        </div></div>
    @elseif ($document_data['decided_at'])
        <div class="card"><div class="card-body"><strong>Keputusan Dicatat: {{ $document_data['decided_at'] }}</strong><p class="mb-0 mt-2 text-break">{{ $document_data['decision_comment'] }}</p></div></div>
    @endif
    @include('customer.validation')
@endsection
@push('styles')
<style>@media print { body, table { font-family: Tahoma, sans-serif !important; } }</style>
@endpush
@push('scripts')
<script>
    $(function () {
        $('#decision').on('change', function () { $('#comment').prop('required', $(this).val() !== 'approved'); }).trigger('change');
        @if ($document_data['prices_unlocked'])
            setTimeout(function () {
                $('.financial-value').remove();
                window.location.reload();
            }, @js($document_data['unlock_remaining_seconds'] * 1000));
            window.addEventListener('pageshow', function (event) {
                if (event.persisted) {
                    $('.financial-value').remove();
                    window.location.reload();
                }
            });
        @endif
    });
</script>
@endpush
