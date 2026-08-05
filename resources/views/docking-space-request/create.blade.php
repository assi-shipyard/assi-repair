@extends('layouts.app')

@section('title', 'Form Permohonan Docking Space')
@section('body_title', 'Form Permohonan Docking Space')

@section('content')
    @include('partials.flash')

    <div class="card">
        <div class="card-body">
            <form id="docking-request-form" method="POST" action="{{ route('docking-space-request.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Proyek</label>
                        <select class="form-select" name="project_id" required>
                            <option value="">Pilih Proyek</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}" @selected(old('project_id') == $project->id)>
                                    {{ $project->project_code }} - {{ $project->ship?->name ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Docking Space (Preferensi)</label>
                        <select class="form-select" name="requested_docking_space_id">
                            <option value="">Sistem Akan Evaluasi Otomatis</option>
                            @foreach ($docking_spaces as $docking_space)
                                <option value="{{ $docking_space->id }}" @selected(old('requested_docking_space_id') == $docking_space->id)>
                                    {{ $docking_space->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Jadwal Mulai Docking</label>
                        <input type="datetime-local" class="form-control" name="requested_start_at" value="{{ old('requested_start_at') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Jadwal Selesai Docking (Estimasi)</label>
                        <input type="datetime-local" class="form-control" name="requested_end_at" value="{{ old('requested_end_at') }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Catatan Permohonan</label>
                        <textarea class="form-control" name="request_notes" rows="3">{{ old('request_notes') }}</textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Kirim Permohonan</button>
                    <a class="btn btn-outline-secondary" href="{{ route('docking-space-request.index') }}">Lihat Daftar Permohonan</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            $.validator.addMethod('greaterThanOrEqual', function (value, element, param) {
                if (!value) {
                    return true;
                }

                const comparedValue = $(param).val();
                if (!comparedValue) {
                    return true;
                }

                return new Date(value) >= new Date(comparedValue);
            });

            $('#docking-request-form').validate({
                errorClass: 'is-invalid',
                validClass: 'is-valid',
                errorElement: 'div',
                errorPlacement: function (error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('div').append(error);
                },
                highlight: function (element) {
                    $(element).addClass('is-invalid').removeClass('is-valid');
                },
                unhighlight: function (element) {
                    $(element).removeClass('is-invalid').addClass('is-valid');
                },
                rules: {
                    project_id: { required: true },
                    requested_start_at: { required: true },
                    requested_end_at: {
                        greaterThanOrEqual: '[name="requested_start_at"]',
                    },
                    request_notes: { maxlength: 2000 },
                },
                messages: {
                    project_id: { required: 'Proyek wajib dipilih.' },
                    requested_start_at: { required: 'Jadwal mulai docking wajib diisi.' },
                    requested_end_at: {
                        greaterThanOrEqual: 'Jadwal selesai docking tidak boleh lebih awal dari jadwal mulai.',
                    },
                    request_notes: { maxlength: 'Catatan permohonan maksimal 2000 karakter.' },
                },
            });
        });
    </script>
@endpush
