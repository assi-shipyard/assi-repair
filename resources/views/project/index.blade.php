@extends('layouts.app')

@section('title', 'Data Proyek')
@section('body_title', 'Data Proyek')

@section('buttons_beside_title')
    <a href="{{ route('project.create') }}" class="btn btn-primary">Tambah Proyek</a>
@endsection

@section('content')
    @include('partials.flash')

    <form class="card mb-4" method="GET" action="{{ route('project.index') }}">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-10">
                    <label class="form-label">Cari Proyek</label>
                    <input class="form-control" name="search_project" value="{{ request('search_project') }}" placeholder="Kode proyek, tipe proyek, atau nama kapal">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-primary w-100" type="submit">Cari</button>
                </div>
            </div>
        </div>
    </form>

    <div id="project_cards">
        @include('project.partials.projectcards', ['projects' => $projects])
    </div>
@endsection
