@extends('layouts.app')
@section('title', 'Proyek Saya')
@section('body_title', 'Proyek Saya')
@section('content')
    @include('partials.flash')
    <div class="card mb-3"><div class="card-body">
        <h3 class="card-title">Selamat Datang, {{ auth()->user()->name }}</h3>
        <p class="text-secondary mb-0">Hanya proyek yang ditugaskan kepada Anda yang tampil. Harga RAB terkunci dan memerlukan izin khusus serta konfirmasi kata sandi.</p>
    </div></div>
    @if ($pending_documents->isNotEmpty())
        <div class="card mb-3">
            <div class="card-header"><h3 class="card-title">Menunggu Persetujuan Anda ({{ $pending_documents->total() }})</h3></div>
            <div class="list-group list-group-flush">
                @foreach ($pending_documents as $pending_document)
                    <a class="list-group-item list-group-item-action" href="{{ route('customer.document', $pending_document) }}">{{ $pending_document->project->project_code }} &middot; {{ \App\Models\customer_document::TYPE_LABELS[$pending_document->document_type] }} &middot; Revisi {{ $pending_document->revision_no }}</a>
                @endforeach
            </div>
            <div class="card-footer">{{ $pending_documents->links() }}</div>
        </div>
    @endif
    <div class="mb-3 d-flex flex-wrap gap-2">
        <a class="btn {{ $status === null ? 'btn-primary' : 'btn-outline-secondary' }}" href="{{ route('customer.index') }}">Semua Proyek</a>
        @foreach ($status_labels as $status_code => $status_label)
            <a class="btn {{ $status === $status_code ? 'btn-primary' : 'btn-outline-secondary' }}" href="{{ route('customer.index', ['status' => $status_code]) }}">{{ $status_label }}</a>
        @endforeach
    </div>
    <div class="row g-3">
        @forelse ($projects as $project)
            <div class="col-md-6 col-xl-4"><div class="card h-100">
                <div class="card-body">
                    <div class="text-secondary mb-1">{{ $project->project_code }}</div>
                    <h3 class="card-title">{{ $project->ship?->name ?? '-' }}</h3>
                    <div class="mb-2">{{ $status_labels[$project->status] ?? 'Status Belum Tersedia' }}</div>
                    <div class="d-flex justify-content-between mb-1"><span>Progres</span><span>{{ $project->progress }}%</span></div>
                    <progress class="w-100" max="100" value="{{ $project->progress }}">{{ $project->progress }}%</progress>
                    <div class="text-secondary mt-2">{{ $project->start_date_estimation?->tanggal_indonesia() ?? '-' }} &ndash; {{ $project->end_date_estimation?->tanggal_indonesia() ?? '-' }}</div>
                </div>
                <div class="card-footer"><a class="btn btn-outline-primary w-100" href="{{ route('customer.project', $project->unique_id) }}">Lihat Proyek</a></div>
            </div></div>
        @empty
            <div class="col-12"><div class="card"><div class="card-body text-secondary">Belum ada proyek yang dapat Anda akses.</div></div></div>
        @endforelse
    </div>
    <div class="mt-3">{{ $projects->links() }}</div>
@endsection
