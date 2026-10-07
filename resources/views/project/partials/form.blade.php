@php
    $is_edit = isset($project);
    $form_id = 'project-form';
    $val = fn (string $field, $default = null) => old($field, $is_edit ? ($project->{$field} ?? $default) : $default);
    $date_val = fn (string $field) => old($field, $is_edit ? $project->{$field}?->format('Y-m-d') : null);
    $selected_division = old('division_ids.0', $is_edit ? $project->divisions->first()?->id : null);
    $surveyor_rows = old('owner_surveyors', $is_edit
        ? $project->owner_surveyors->map(fn ($s) => $s->only(['name', 'company', 'position', 'email', 'phone']))->all()
        : []);
    if (empty($surveyor_rows)) {
        $surveyor_rows = [[]];
    }
    $status_labels = ['Not Started' => 'Belum Mulai', 'In Progress' => 'Berjalan', 'Completed' => 'Selesai'];
    $employee_option = fn ($employee) => 'value="'.$employee->id.'" data-division="'.($employee->position?->organizational_unit?->id ?? '').'"';
@endphp

<form action="{{ $is_edit ? route('project.update', $project->unique_id) : route('project.store') }}" method="POST" id="{{ $form_id }}" novalidate>
    @csrf
    @if ($is_edit) @method('PUT') @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <div class="fw-bold mb-1">Data belum dapat disimpan:</div>
            <ul class="mb-0 ps-3">@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="row row-cards">
        <div class="col-12 col-xl-8">
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-ship me-2 text-primary"></i>Data Proyek</h3></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required" for="ship_id">Kapal</label>
                            <select class="form-select dropdown-list" id="ship_id" name="ship_id" required>
                                <option value="">Pilih kapal</option>
                                @foreach ($ships as $ship)
                                    <option value="{{ $ship->id }}"
                                        data-name="{{ $ship->name }}"
                                        data-meta="{{ collect([$ship->type?->name, $ship->imo_number ? 'IMO '.$ship->imo_number : null, $ship->flag, $ship->company?->name])->filter()->implode(' · ') }}"
                                        @selected(old('ship_id', $is_edit ? $project->ship_id : null) == $ship->id)>{{ $ship->name }} - {{ $ship->company?->name ?? '-' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required" for="project_type">Tipe Proyek</label>
                            <select class="form-select dropdown-list" id="project_type" name="project_type" required>
                                <option value="">Pilih tipe</option>
                                @foreach ($project_type_options as $option)
                                    <option value="{{ $option }}" @selected($val('project_type') === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="comment">Catatan</label>
                            <textarea class="form-control" id="comment" name="comment" rows="3" maxlength="2000" placeholder="Catatan proyek (opsional)">{{ $val('comment') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-users-group me-2 text-primary"></i>Tim Pelaksana</h3></div>
                <div class="card-body">
                    <div class="form-hint mb-3">Pilih minimal salah satu dari PIMPRO, PPC, atau Manajer Divisi. Ketiganya harus berasal dari divisi yang sama; divisi terisi otomatis.</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="project_leader_employee_id">PIMPRO</label>
                            <select class="form-select dropdown-list team-select" id="project_leader_employee_id" name="project_leader_employee_id">
                                <option value="">Tidak ada</option>
                                @foreach ($employees as $employee)
                                    <option {!! $employee_option($employee) !!} @selected($val('project_leader_employee_id') == $employee->id)>{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="project_ppc_employee_id">PPC</label>
                            <select class="form-select dropdown-list team-select" id="project_ppc_employee_id" name="project_ppc_employee_id">
                                <option value="">Tidak ada</option>
                                @foreach ($ppc_employees as $employee)
                                    <option {!! $employee_option($employee) !!} @selected($val('project_ppc_employee_id') == $employee->id)>{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="division_manager_employee_id">Manajer Divisi</label>
                            <select class="form-select dropdown-list team-select" id="division_manager_employee_id" name="division_manager_employee_id">
                                <option value="">Tidak ada</option>
                                @foreach ($employees as $employee)
                                    <option {!! $employee_option($employee) !!} @selected(old('division_manager_employee_id') == $employee->id)>{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required" for="division_id">Divisi Pelaksana</label>
                            <select class="form-select dropdown-list" id="division_id" name="division_ids[]" required>
                                <option value="">Pilih divisi</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}" @selected((int) $selected_division === $division->id)>{{ $division->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card mb-3 overflow-hidden" id="ship_preview" data-code-url="{{ route('project.generate-code-preview') }}">
                <div class="ship-cover" id="ship_cover">
                    <i class="ti ti-ship ship-cover-icon"></i>
                    <div class="ship-cover-body">
                        <span class="badge bg-white text-dark font-monospace" id="preview_code">{{ $is_edit ? $project->project_code : 'Kode otomatis' }}</span>
                        <div>
                            <div class="ship-cover-name" id="preview_name">Pilih kapal</div>
                            <div class="ship-cover-meta" id="preview_meta">Ringkasan kapal tampil di sini</div>
                        </div>
                    </div>
                </div>
                <div class="card-body small text-secondary">
                    <i class="ti ti-info-circle me-1"></i>
                    @if ($is_edit)
                        Kode dibuat ulang otomatis jika kapal, tipe, atau tanggal estimasi mulai berubah.
                    @else
                        Kode proyek dibuat otomatis berdasarkan kapal, tipe, dan tanggal estimasi mulai.
                    @endif
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-calendar-event me-2 text-primary"></i>{{ $is_edit ? 'Jadwal & Status' : 'Jadwal' }}</h3></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label" for="start_date_estimation">Estimasi Mulai</label>
                            <input class="form-control" type="date" id="start_date_estimation" name="start_date_estimation" value="{{ $date_val('start_date_estimation') }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="end_date_estimation">Estimasi Selesai</label>
                            <input class="form-control" type="date" id="end_date_estimation" name="end_date_estimation" value="{{ $date_val('end_date_estimation') }}">
                        </div>
                        @if ($is_edit)
                        <div class="col-6">
                            <label class="form-label" for="start_date_actual">Aktual Mulai</label>
                            <input class="form-control" type="date" id="start_date_actual" name="start_date_actual" value="{{ $date_val('start_date_actual') }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="end_date_actual">Aktual Selesai</label>
                            <input class="form-control" type="date" id="end_date_actual" name="end_date_actual" value="{{ $date_val('end_date_actual') }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="status">Status</label>
                            <select class="form-select" id="status" name="status">
                                @foreach ($status_labels as $status_key => $status_label)
                                    <option value="{{ $status_key }}" @selected($val('status', 'Not Started') === $status_key)>{{ $status_label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="progress">Progres</label>
                            <div class="input-group">
                                <input class="form-control" type="number" min="0" max="100" step="0.01" id="progress" name="progress" value="{{ $val('progress', 0) }}">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-user-search me-2 text-primary"></i>Surveyor Pemilik Kapal</h3>
                    <div class="card-actions">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add_surveyor"><i class="ti ti-plus me-1"></i>Tambah Surveyor</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="form-hint mb-3">Opsional. Baris tanpa nama diabaikan.</div>
                    <div id="surveyor_rows" class="d-grid gap-3">
                        @foreach ($surveyor_rows as $index => $row)
                            @include('project.partials.surveyor_row', ['index' => $index, 'row' => $row])
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="project-form-actions d-flex flex-wrap justify-content-end gap-2">
        <a href="{{ $is_edit ? route('project.show', $project->unique_id) : route('project.index') }}" class="btn btn-outline-secondary">Batal</a>
        <button class="btn btn-primary" type="submit"><i class="ti ti-device-floppy me-1"></i>{{ $is_edit ? 'Perbarui Proyek' : 'Simpan Proyek' }}</button>
    </div>
</form>

<template id="surveyor_template">
    @include('project.partials.surveyor_row', ['index' => '__INDEX__', 'row' => []])
</template>

@push('styles')
    <style>
        #project-form label.invalid-feedback, #project-form .invalid-feedback { display: block; }
        .project-form-actions { position: sticky; bottom: 0; z-index: 5; margin-top: 1rem; padding: .75rem 1rem; background: var(--tblr-bg-surface); border: 1px solid var(--tblr-border-color); border-radius: var(--tblr-border-radius); }
        .ship-cover { position: relative; height: 150px; color: #fff; overflow: hidden; background: linear-gradient(135deg, #3b4a5a, #6b7c8d); transition: background .3s; }
        .ship-cover-icon { position: absolute; right: -6px; bottom: -26px; font-size: 8.5rem; opacity: .18; line-height: 1; }
        .ship-cover-body { position: relative; height: 100%; padding: .85rem 1rem; display: flex; flex-direction: column; justify-content: space-between; align-items: flex-start; }
        .ship-cover-name { font-size: 1.25rem; font-weight: 600; line-height: 1.25; }
        .ship-cover-meta { font-size: .78rem; opacity: .9; }
        @media (min-width: 1200px) { #ship_preview { position: sticky; top: 1rem; z-index: 1; } }
        .select2-container--bootstrap-5 .select2-selection.is-invalid { border-color: var(--tblr-danger); }
    </style>
@endpush

@push('scripts')
    <script>
        $(function () {
            let surveyor_index = {{ count($surveyor_rows) }};

            $.validator.addMethod('on_or_after', function (value, element, start_selector) {
                const start_value = $(start_selector).val();
                return !value || !start_value || value >= start_value;
            });

            $('#project-form').validate({
                ignore: [],
                rules: {
                    ship_id: { required: true },
                    project_type: { required: true },
                    'division_ids[]': { required: true },
                    end_date_estimation: { on_or_after: '#start_date_estimation' },
                    end_date_actual: { on_or_after: '#start_date_actual' },
                    progress: { number: true, min: 0, max: 100 }
                },
                messages: {
                    ship_id: { required: 'Pilih kapal.' },
                    project_type: { required: 'Pilih tipe proyek.' },
                    'division_ids[]': { required: 'Pilih divisi pelaksana.' },
                    end_date_estimation: { on_or_after: 'Tanggal selesai harus sama atau setelah tanggal mulai.' },
                    end_date_actual: { on_or_after: 'Tanggal selesai harus sama atau setelah tanggal mulai.' },
                    progress: { number: 'Progres harus berupa angka.', min: 'Progres minimal 0%.', max: 'Progres maksimal 100%.' }
                },
                errorElement: 'div',
                errorPlacement: function (error, element) {
                    error.addClass('invalid-feedback');
                    if (element.hasClass('select2-hidden-accessible')) { error.insertAfter(element.next('.select2')); return; }
                    if (element.parent().hasClass('input-group')) { error.insertAfter(element.parent()); return; }
                    error.insertAfter(element);
                },
                highlight: function (element) {
                    const $el = $(element);
                    ($el.hasClass('select2-hidden-accessible') ? $el.next('.select2').find('.select2-selection') : $el).addClass('is-invalid');
                },
                unhighlight: function (element) {
                    const $el = $(element);
                    ($el.hasClass('select2-hidden-accessible') ? $el.next('.select2').find('.select2-selection') : $el).removeClass('is-invalid');
                }
            });

            const apply_surveyor_rules = function ($scope) {
                $scope.find('.surveyor-email').each(function () {
                    $(this).rules('add', { email: true, messages: { email: 'Masukkan email yang valid.' } });
                });
            };
            apply_surveyor_rules($('#surveyor_rows'));

            $('#add_surveyor').on('click', function () {
                const html = $('#surveyor_template').html().replace(/__INDEX__/g, surveyor_index++);
                const $row = $(html).appendTo('#surveyor_rows');
                apply_surveyor_rules($row);
            });

            $('#surveyor_rows').on('click', '.remove-surveyor', function () {
                $(this).closest('.surveyor-row').remove();
            });

            // Division follows the selected team members.
            $('.team-select').on('change', function () {
                const division_id = $(this).find('option:selected').data('division');
                if (division_id) {
                    $('#division_id').val(String(division_id)).trigger('change');
                }
            });

            const hue_of = function (text) {
                let h = 0;
                for (let i = 0; i < text.length; i++) { h = (h * 31 + text.charCodeAt(i)) >>> 0; }
                return h % 360;
            };

            const refresh_ship_preview = function () {
                const $opt = $('#ship_id option:selected');
                const name = $opt.data('name');
                $('#preview_name').text(name || 'Pilih kapal');
                $('#preview_meta').text(name ? ($opt.data('meta') || '-') : 'Ringkasan kapal tampil di sini');
                const hue = name ? hue_of(String(name)) : null;
                $('#ship_cover').css('background', hue === null
                    ? 'linear-gradient(135deg, #3b4a5a, #6b7c8d)'
                    : 'linear-gradient(135deg, hsl(' + hue + ',55%,30%), hsl(' + ((hue + 40) % 360) + ',60%,45%))');
            };

            let code_request = null;
            const refresh_code_preview = function () {
                const ship_id = $('#ship_id').val();
                const project_type = $('#project_type').val();
                if (!ship_id || !project_type) { return; }
                if (code_request) { code_request.abort(); }
                code_request = $.get($('#ship_preview').data('code-url'), {
                    ship_id: ship_id,
                    project_type: project_type,
                    start_date_estimation: $('#start_date_estimation').val() || null
                }).done(function (response) {
                    $('#preview_code').text(response.project_code);
                });
            };

            $('#ship_id').on('change', refresh_ship_preview);
            $('#ship_id, #project_type, #start_date_estimation').on('change', refresh_code_preview);
            refresh_ship_preview();

            $('.dropdown-list, #start_date_estimation, #start_date_actual').on('change', function () {
                if ($(this).closest('form').data('validator')) { $(this).valid(); }
            });
        });
    </script>
@endpush
