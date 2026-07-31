@extends('layouts.app')

@section('title', 'Tambah Proyek')
@section('body_title', 'Tambah Proyek')

@section('buttons_beside_title')
    <a href="{{ route('project.index') }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
    @include('partials.flash')

    <form action="{{ route('project.store') }}" method="POST" class="card">
        @csrf
        <div class="card-header">
            <div>
                <h3 class="card-title mb-1">Formulir Proyek</h3>
                <p class="text-muted mb-0">Pisahkan data kapal, jadwal, tim proyek, dan pihak owner surveyor.</p>
            </div>
        </div>
        <div class="card-body">
            <div class="card mb-3 shadow-none border">
                <div class="card-header py-3">
                    <div>
                        <h4 class="card-title mb-1">Data Proyek</h4>
                        <div class="text-muted">Pilih kapal, tipe proyek, dan penanggung jawab utama.</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Kapal</label>
                            <select class="form-select" name="ship_id" required>
                                <option value="">Pilih kapal</option>
                                @foreach ($ships as $ship)
                                    <option value="{{ $ship->id }}" @selected(old('ship_id') == $ship->id)>{{ $ship->name }} - {{ $ship->company?->name ?? '-' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipe Proyek</label>
                            <select class="form-select" name="project_type" required>
                                <option value="">Pilih tipe</option>
                                @foreach ($projectTypeOptions as $projectTypeOption)
                                    <option value="{{ $projectTypeOption }}" @selected(old('project_type') == $projectTypeOption)>{{ $projectTypeOption }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">PIMPRO</label>
                            <select class="form-select" name="project_leader_employee_id">
                                <option value="">Tidak ada</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}" @selected(old('project_leader_employee_id') == $employee->id)>{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">PPC</label>
                            <select class="form-select" name="project_ppc_employee_id">
                                <option value="">Tidak ada</option>
                                @foreach ($ppc_employees as $employee)
                                    <option value="{{ $employee->id }}" @selected(old('project_ppc_employee_id') == $employee->id)>{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Divisi Pelaksana</label>
                            <select class="form-select" name="division_ids[]" multiple required>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}" @selected(in_array($division->id, old('division_ids', [])))>{{ $division->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Manajer Divisi</label>
                            <select class="form-select" name="division_manager_employee_id">
                                <option value="">Tidak ada</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}" @selected(old('division_manager_employee_id') == $employee->id)>{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3 shadow-none border">
                <div class="card-header py-3">
                    <div>
                        <h4 class="card-title mb-1">Jadwal dan Status</h4>
                        <div class="text-muted">Atur estimasi, realisasi, progres, dan status pekerjaan.</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Estimasi Mulai</label>
                            <input class="form-control" type="date" name="start_date_estimation" value="{{ old('start_date_estimation') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Estimasi Selesai</label>
                            <input class="form-control" type="date" name="end_date_estimation" value="{{ old('end_date_estimation') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Aktual Mulai</label>
                            <input class="form-control" type="date" name="start_date_actual" value="{{ old('start_date_actual') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Aktual Selesai</label>
                            <input class="form-control" type="date" name="end_date_actual" value="{{ old('end_date_actual') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Progress</label>
                            <input class="form-control" name="progress" value="{{ old('progress', 0) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="Not Started" @selected(old('status', 'Not Started') === 'Not Started')>Not Started</option>
                                <option value="In Progress" @selected(old('status') === 'In Progress')>In Progress</option>
                                <option value="Completed" @selected(old('status') === 'Completed')>Completed</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Komentar</label>
                            <input class="form-control" name="comment" value="{{ old('comment') }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-none border">
                <div class="card-header py-3">
                    <div>
                        <h4 class="card-title mb-1">Owner Surveyor</h4>
                        <div class="text-muted">Isi data kontak surveyor pemilik bila diperlukan.</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Nama</label>
                            <input class="form-control" name="owner_surveyors[0][name]" value="{{ old('owner_surveyors.0.name') }}" placeholder="Nama">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Perusahaan</label>
                            <input class="form-control" name="owner_surveyors[0][company]" value="{{ old('owner_surveyors.0.company') }}" placeholder="Perusahaan">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Jabatan</label>
                            <input class="form-control" name="owner_surveyors[0][position]" value="{{ old('owner_surveyors.0.position') }}" placeholder="Jabatan">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Email</label>
                            <input class="form-control" name="owner_surveyors[0][email]" value="{{ old('owner_surveyors.0.email') }}" placeholder="Email">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Telepon</label>
                            <input class="form-control" name="owner_surveyors[0][phone]" value="{{ old('owner_surveyors.0.phone') }}" placeholder="Telepon">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-end"><button class="btn btn-primary" type="submit">Simpan</button></div>
    </form>
@endsection
