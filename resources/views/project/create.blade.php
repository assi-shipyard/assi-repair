@extends('layouts.app')

@section('title', 'Tambah Proyek')
@section('body_title', 'Tambah Proyek')

@section('buttons_beside_title')
    <a href="{{ route('project.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i>Kembali
    </a>
@endsection

@section('content')
    @include('partials.flash')

    <form action="{{ route('project.store') }}" method="POST" id="project-create-form" novalidate>
        @csrf

        <div class="text-secondary mb-3">
            <i class="ti ti-info-circle me-1"></i>Kode proyek dibuat otomatis saat disimpan berdasarkan kapal, tipe, dan tanggal estimasi mulai.
        </div>

        <div class="row row-cards">
            <div class="col-12 col-xxl-7">
                <div class="card project-form-card h-100">
                    <div class="card-header">
                        <h3 class="card-title"><span class="project-step">1</span>Data Proyek</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label required" for="ship_id">Kapal</label>
                                <select class="form-select dropdown-list" id="ship_id" name="ship_id" required>
                                    <option value="">Pilih kapal</option>
                                    @foreach ($ships as $ship)
                                        <option value="{{ $ship->id }}" @selected(old('ship_id') == $ship->id)>{{ $ship->name }} - {{ $ship->company?->name ?? '-' }}</option>
                                    @endforeach
                                </select>
                                @error('ship_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required" for="project_type">Tipe Proyek</label>
                                <select class="form-select" id="project_type" name="project_type" required>
                                    <option value="">Pilih tipe</option>
                                    @foreach ($projectTypeOptions as $project_type_option)
                                        <option value="{{ $project_type_option }}" @selected(old('project_type') == $project_type_option)>{{ $project_type_option }}</option>
                                    @endforeach
                                </select>
                                @error('project_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <div class="subheader mb-0">Tim Pelaksana</div>
                                <div class="form-hint">Pilih minimal salah satu dari PIMPRO, PPC, atau Manajer Divisi.</div>
                            </div>
                            <div class="col-md-6 col-lg-3 col-xxl-6">
                                <label class="form-label" for="project_leader_employee_id">PIMPRO</label>
                                <select class="form-select dropdown-list" id="project_leader_employee_id" name="project_leader_employee_id">
                                    <option value="">Tidak ada</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}" @selected(old('project_leader_employee_id') == $employee->id)>{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                                @error('project_leader_employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 col-lg-3 col-xxl-6">
                                <label class="form-label" for="project_ppc_employee_id">PPC</label>
                                <select class="form-select dropdown-list" id="project_ppc_employee_id" name="project_ppc_employee_id">
                                    <option value="">Tidak ada</option>
                                    @foreach ($ppc_employees as $employee)
                                        <option value="{{ $employee->id }}" @selected(old('project_ppc_employee_id') == $employee->id)>{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                                @error('project_ppc_employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 col-lg-3 col-xxl-6">
                                <label class="form-label required" for="division_ids">Divisi Pelaksana</label>
                                <select class="form-select dropdown-list" id="division_ids" name="division_ids[]" multiple required>
                                    @foreach ($divisions as $division)
                                        <option value="{{ $division->id }}" @selected(in_array($division->id, old('division_ids', [])))>{{ $division->name }}</option>
                                    @endforeach
                                </select>
                                @error('division_ids')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 col-lg-3 col-xxl-6">
                                <label class="form-label" for="division_manager_employee_id">Manajer Divisi</label>
                                <select class="form-select dropdown-list" id="division_manager_employee_id" name="division_manager_employee_id">
                                    <option value="">Tidak ada</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}" @selected(old('division_manager_employee_id') == $employee->id)>{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                                @error('division_manager_employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xxl-5">
                <div class="card project-form-card h-100">
                    <div class="card-header">
                        <h3 class="card-title"><span class="project-step">2</span>Jadwal dan Status</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6 col-md-4 col-lg-2 col-xxl-6">
                                <label class="form-label" for="start_date_estimation">Estimasi Mulai</label>
                                <input class="form-control" type="date" id="start_date_estimation" name="start_date_estimation" value="{{ old('start_date_estimation') }}">
                                @error('start_date_estimation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-6 col-md-4 col-lg-2 col-xxl-6">
                                <label class="form-label" for="end_date_estimation">Estimasi Selesai</label>
                                <input class="form-control" type="date" id="end_date_estimation" name="end_date_estimation" value="{{ old('end_date_estimation') }}">
                                @error('end_date_estimation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-6 col-md-4 col-lg-2 col-xxl-6">
                                <label class="form-label" for="start_date_actual">Aktual Mulai</label>
                                <input class="form-control" type="date" id="start_date_actual" name="start_date_actual" value="{{ old('start_date_actual') }}">
                                @error('start_date_actual')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-6 col-md-4 col-lg-2 col-xxl-6">
                                <label class="form-label" for="end_date_actual">Aktual Selesai</label>
                                <input class="form-control" type="date" id="end_date_actual" name="end_date_actual" value="{{ old('end_date_actual') }}">
                                @error('end_date_actual')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-6 col-md-4 col-lg-2 col-xxl-6">
                                <label class="form-label" for="progress">Progres</label>
                                <div class="input-group">
                                    <input class="form-control" type="number" min="0" max="100" step="0.01" id="progress" name="progress" value="{{ old('progress', 0) }}">
                                    <span class="input-group-text">%</span>
                                </div>
                                @error('progress')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-6 col-md-4 col-lg-2 col-xxl-6">
                                <label class="form-label" for="status">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="Not Started" @selected(old('status', 'Not Started') === 'Not Started')>Not Started</option>
                                    <option value="In Progress" @selected(old('status') === 'In Progress')>In Progress</option>
                                    <option value="Completed" @selected(old('status') === 'Completed')>Completed</option>
                                </select>
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="comment">Komentar</label>
                                <textarea class="form-control" id="comment" name="comment" rows="2" placeholder="Catatan awal proyek (opsional)">{{ old('comment') }}</textarea>
                                @error('comment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card project-form-card">
                    <div class="card-header">
                        <h3 class="card-title"><span class="project-step">3</span>Surveyor Pemilik Kapal</h3>
                        <div class="card-actions"><span class="badge bg-secondary-lt text-secondary">Opsional</span></div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label" for="owner_surveyor_name">Nama</label>
                                <input class="form-control" id="owner_surveyor_name" name="owner_surveyors[0][name]" value="{{ old('owner_surveyors.0.name') }}" placeholder="Nama surveyor">
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label" for="owner_surveyor_company">Perusahaan</label>
                                <input class="form-control" id="owner_surveyor_company" name="owner_surveyors[0][company]" value="{{ old('owner_surveyors.0.company') }}" placeholder="Perusahaan">
                            </div>
                            <div class="col-md-4 col-lg-2">
                                <label class="form-label" for="owner_surveyor_position">Jabatan</label>
                                <input class="form-control" id="owner_surveyor_position" name="owner_surveyors[0][position]" value="{{ old('owner_surveyors.0.position') }}" placeholder="Jabatan">
                            </div>
                            <div class="col-md-4 col-lg-2">
                                <label class="form-label" for="owner_surveyor_email">Email</label>
                                <input class="form-control" type="email" id="owner_surveyor_email" name="owner_surveyors[0][email]" value="{{ old('owner_surveyors.0.email') }}" placeholder="Email">
                                @error('owner_surveyors.0.email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4 col-lg-2">
                                <label class="form-label" for="owner_surveyor_phone">Telepon</label>
                                <input class="form-control" id="owner_surveyor_phone" name="owner_surveyors[0][phone]" value="{{ old('owner_surveyors.0.phone') }}" placeholder="Telepon">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="project-form-actions d-flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="text-secondary small d-none d-md-inline">Periksa kapal, tim, dan jadwal sebelum menyimpan.</span>
            <div class="d-flex gap-2 ms-md-auto">
                <a href="{{ route('project.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button class="btn btn-primary" type="submit"><i class="ti ti-device-floppy me-1"></i>Simpan Proyek</button>
            </div>
        </div>
    </form>
@endsection

@push('styles')
    <style>
        #project-create-form .invalid-feedback { display: block; }

        .project-form-card .form-label { font-weight: 600; margin-bottom: .25rem; }

        .project-step {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.5rem;
            height: 1.5rem;
            margin-right: .6rem;
            border-radius: 50%;
            font-size: .75rem;
            font-weight: 700;
            color: var(--tblr-primary);
            background: var(--tblr-primary-lt);
        }

        .project-form-actions {
            position: sticky;
            bottom: 0;
            z-index: 5;
            margin-top: 1rem;
            padding: .75rem 1rem;
            background: var(--tblr-bg-surface);
            border: 1px solid var(--tblr-border-color);
            border-radius: var(--tblr-border-radius);
        }

        .select2-container--bootstrap-5 .select2-selection.is-invalid {
            border-color: var(--tblr-danger);
        }

        @media (max-height: 800px) {
            .project-form-card .card-body { padding: 1rem; }
            .project-form-actions { padding: .5rem .75rem; margin-top: .5rem; }
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function () {
            $.validator.addMethod('on_or_after', function (value, element, start_selector) {
                const start_value = $(start_selector).val();

                return !value || !start_value || value >= start_value;
            });

            $('#project-create-form').validate({
                ignore: [],
                rules: {
                    ship_id: { required: true },
                    project_type: { required: true },
                    'division_ids[]': { required: true },
                    end_date_estimation: { on_or_after: '#start_date_estimation' },
                    end_date_actual: { on_or_after: '#start_date_actual' },
                    progress: { number: true, min: 0, max: 100 },
                    'owner_surveyors[0][email]': { email: true }
                },
                messages: {
                    ship_id: { required: 'Pilih kapal.' },
                    project_type: { required: 'Pilih tipe proyek.' },
                    'division_ids[]': { required: 'Pilih minimal satu divisi pelaksana.' },
                    end_date_estimation: { on_or_after: 'Tanggal selesai harus sama atau setelah tanggal mulai.' },
                    end_date_actual: { on_or_after: 'Tanggal selesai harus sama atau setelah tanggal mulai.' },
                    progress: {
                        number: 'Progres harus berupa angka.',
                        min: 'Progres minimal 0%.',
                        max: 'Progres maksimal 100%.'
                    },
                    'owner_surveyors[0][email]': { email: 'Masukkan email yang valid.' }
                },
                errorElement: 'div',
                errorPlacement: function (error, element) {
                    error.addClass('invalid-feedback');

                    if (element.hasClass('select2-hidden-accessible')) {
                        error.insertAfter(element.next('.select2'));
                        return;
                    }

                    if (element.parent().hasClass('input-group')) {
                        error.insertAfter(element.parent());
                        return;
                    }

                    error.insertAfter(element);
                },
                highlight: function (element) {
                    const $element = $(element);

                    if ($element.hasClass('select2-hidden-accessible')) {
                        $element.next('.select2').find('.select2-selection').addClass('is-invalid');
                        return;
                    }

                    $element.addClass('is-invalid');
                },
                unhighlight: function (element) {
                    const $element = $(element);

                    if ($element.hasClass('select2-hidden-accessible')) {
                        $element.next('.select2').find('.select2-selection').removeClass('is-invalid');
                        return;
                    }

                    $element.removeClass('is-invalid');
                }
            });

            $('.dropdown-list, #start_date_estimation, #start_date_actual').on('change', function () {
                $(this).valid();
            });
        });
    </script>
@endpush
