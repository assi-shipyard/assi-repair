@extends('layouts.app')

@section('title', 'Foto Pekerjaan')
@section('body_title', 'Foto Pekerjaan')

@section('buttons_beside_title')
    <a href="{{ route('project.job-document.workflow.show', [$project->unique_id, $document->id]) }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
    @include('partials.flash')

    <div class="card mb-4">
        <div class="card-body">
            <div class="text-secondary">{{ $documentTypeLabels[$document->document_type] ?? $document->document_type }}</div>
            <h3 class="mb-0">{{ $job->job_name }}</h3>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><h3 class="card-title mb-0">Unggah Foto</h3></div>
        <div class="card-body">
            <form action="{{ route('project.job-document.workflow.job.photo.store', [$project->unique_id, $document->id, $job->id]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4"><input class="form-control" type="file" name="job_photo" required></div>
                    <div class="col-md-3"><input class="form-control" name="photo_category" placeholder="Kategori foto"></div>
                    <div class="col-md-3"><input class="form-control" name="caption" placeholder="Keterangan"></div>
                    <div class="col-md-2"><button class="btn btn-primary w-100" type="submit">Unggah</button></div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead><tr><th>Kategori</th><th>Keterangan</th><th>Tanggal</th><th class="w-1"></th></tr></thead>
                <tbody>
                    @forelse ($photos as $photo)
                        <tr>
                            <td>{{ $photo->photo_category ?? '-' }}</td>
                            <td>{{ $photo->caption ?? '-' }}</td>
                            <td>{{ optional($photo->taken_at)->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>
                                <form action="{{ route('project.job-document.workflow.job.photo.destroy', [$project->unique_id, $document->id, $job->id, $photo->id]) }}" method="POST" onsubmit="return confirm('Hapus foto ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-secondary py-4">Belum ada foto.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
