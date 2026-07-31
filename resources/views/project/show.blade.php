@extends('layouts.app')

@section('title', 'Detail Proyek')
@section('body_title', 'Detail Proyek')

@section('buttons_beside_title')
    <a href="{{ route('project.job-document.workflow.index', $project->unique_id) }}" class="btn btn-outline-success">Dokumen</a>
    <a href="{{ route('project.edit', $project->unique_id) }}" class="btn btn-outline-primary">Ubah</a>
    <a href="{{ route('project.index') }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
    @include('partials.flash')

    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><div class="text-secondary">Kode Proyek</div><div>{{ $project->project_code }}</div></div>
                <div class="col-md-6"><div class="text-secondary">Kapal</div><div>{{ $project->ship?->name ?? '-' }}</div></div>
                <div class="col-md-6"><div class="text-secondary">Tipe</div><div>{{ $project->project_type ?? '-' }}</div></div>
                <div class="col-md-6"><div class="text-secondary">Status</div><div>{{ $project->status ?? '-' }}</div></div>
                <div class="col-md-6"><div class="text-secondary">PIMPRO</div><div>{{ $project->leader?->name ?? '-' }}</div></div>
                <div class="col-md-6"><div class="text-secondary">PPC</div><div>{{ $project->ppc?->name ?? '-' }}</div></div>
                <div class="col-md-12"><div class="text-secondary">Divisi Pelaksana</div><div>{{ $project->divisions->pluck('name')->join(', ') ?: '-' }}</div></div>
                <div class="col-md-12"><div class="text-secondary">Komentar</div><div>{{ $project->comment ?? '-' }}</div></div>
            </div>
        </div>
    </div>
@endsection
