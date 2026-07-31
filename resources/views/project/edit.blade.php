@extends('layouts.app')

@section('title', 'Ubah Proyek')
@section('body_title', 'Ubah Proyek')

@section('buttons_beside_title')
    <a href="{{ route('project.show', $project->unique_id) }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
    @include('partials.flash')

    <form action="{{ route('project.update', $project->unique_id) }}" method="POST" class="card">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Kapal</label><select class="form-select" name="ship_id" required>@foreach ($ships as $ship)<option value="{{ $ship->id }}" @selected(old('ship_id', $project->ship_id) == $ship->id)>{{ $ship->name }} - {{ $ship->company?->name ?? '-' }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Tipe Proyek</label><select class="form-select" name="project_type" required>@foreach ($projectTypeOptions as $projectTypeOption)<option value="{{ $projectTypeOption }}" @selected(old('project_type', $project->project_type) === $projectTypeOption)>{{ $projectTypeOption }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">PIMPRO</label><select class="form-select" name="project_leader_employee_id"><option value="">Tidak ada</option>@foreach ($employees as $employee)<option value="{{ $employee->id }}" @selected(old('project_leader_employee_id', $project->project_leader_employee_id) == $employee->id)>{{ $employee->name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">PPC</label><select class="form-select" name="project_ppc_employee_id"><option value="">Tidak ada</option>@foreach ($employees as $employee)<option value="{{ $employee->id }}" @selected(old('project_ppc_employee_id', $project->project_ppc_employee_id) == $employee->id)>{{ $employee->name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Divisi Pelaksana</label><select class="form-select" name="division_ids[]" multiple required>@foreach ($divisions as $division)<option value="{{ $division->id }}" @selected(in_array($division->id, $project->divisions->pluck('id')->all(), true))>{{ $division->name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Manajer Divisi</label><select class="form-select" name="division_manager_employee_id"><option value="">Tidak ada</option>@foreach ($employees as $employee)<option value="{{ $employee->id }}">{{ $employee->name }}</option>@endforeach</select></div>
                <div class="col-md-3"><label class="form-label">Estimasi Mulai</label><input class="form-control" type="date" name="start_date_estimation" value="{{ old('start_date_estimation', optional($project->start_date_estimation)->format('Y-m-d')) }}"></div>
                <div class="col-md-3"><label class="form-label">Estimasi Selesai</label><input class="form-control" type="date" name="end_date_estimation" value="{{ old('end_date_estimation', optional($project->end_date_estimation)->format('Y-m-d')) }}"></div>
                <div class="col-md-3"><label class="form-label">Aktual Mulai</label><input class="form-control" type="date" name="start_date_actual" value="{{ old('start_date_actual', optional($project->start_date_actual)->format('Y-m-d')) }}"></div>
                <div class="col-md-3"><label class="form-label">Aktual Selesai</label><input class="form-control" type="date" name="end_date_actual" value="{{ old('end_date_actual', optional($project->end_date_actual)->format('Y-m-d')) }}"></div>
                <div class="col-md-4"><label class="form-label">Progress</label><input class="form-control" name="progress" value="{{ old('progress', $project->progress) }}"></div>
                <div class="col-md-4"><label class="form-label">Status</label><select class="form-select" name="status"><option value="Not Started" @selected(old('status', $project->status) === 'Not Started')>Not Started</option><option value="In Progress" @selected(old('status', $project->status) === 'In Progress')>In Progress</option><option value="Completed" @selected(old('status', $project->status) === 'Completed')>Completed</option></select></div>
                <div class="col-md-4"><label class="form-label">Komentar</label><input class="form-control" name="comment" value="{{ old('comment', $project->comment ?? '') }}"></div>
            </div>
        </div>
        <div class="card-footer text-end"><button class="btn btn-primary" type="submit">Perbarui</button></div>
    </form>
@endsection
