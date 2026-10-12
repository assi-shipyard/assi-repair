@php
    $document_url = route('project.job-document.workflow.show', $job_base);
    $old_action = (string) old('form_action');
    $old_form = $errors->any() && in_array(old('form_kind'), ['job', 'material'], true) && \Illuminate\Support\Str::startsWith($old_action, $document_url.'/')
        ? [
            'kind' => old('form_kind'),
            'action' => $old_action,
            'method' => old('_method') === 'PUT' ? 'PUT' : 'POST',
            'title' => (string) old('form_title'),
            'input' => \Illuminate\Support\Arr::except(session()->getOldInput(), ['_token', '_method', 'form_action', 'form_kind', 'form_title']),
        ]
        : null;
    $responsible_kinds = ['internal_unit' => 'Unit Internal', 'subcontractor' => 'Subkontraktor'];
@endphp

<div class="modal modal-blur fade" id="boq-job-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form class="modal-content" id="boq-job-form" method="POST" action="{{ route('project.job-document.workflow.job.store', $job_base) }}" novalidate>
            @csrf
            <input type="hidden" name="_method" value="POST">
            <input type="hidden" name="form_kind" value="job">
            <input type="hidden" name="form_action" value="">
            <input type="hidden" name="form_title" value="">
            <input type="hidden" name="parent_job_id" value="">
            <input type="hidden" name="form_is_child" value="0">
            <input type="hidden" name="price_item_text" value="">
            <div class="modal-header">
                <h5 class="modal-title" id="boq-job-title">Tambah Pekerjaan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                @if ($errors->any() && $old_form && $old_form['kind'] === 'job')
                    <div class="alert alert-danger"><ul class="mb-0 ps-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                <div class="alert alert-secondary py-2 d-none" id="boq-parent-info"><i class="ti ti-subtask me-1"></i>Sub pekerjaan dari <strong></strong></div>
                <div class="row g-3">
                    <div class="col-md-6" id="boq-group-field">
                        <label class="form-label required" for="boq-job-group">Kelompok Pekerjaan</label>
                        <select class="form-select" name="project_job_group_id" id="boq-job-group">
                            <option value="">Pilih kelompok</option>
                            @foreach ($job_groups as $job_group)
                                <option value="{{ $job_group->unique_id }}">{{ $job_group->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-hint">Sub pekerjaan otomatis mengikuti kelompok induknya.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="boq-job-price-item">Item Kamus Harga</label>
                        <select class="form-select" name="price_book_item_id" id="boq-job-price-item" data-group="service" @disabled($price_book === null)></select>
                        <div class="form-hint" id="boq-job-price-hint">{{ $price_book ? 'Opsional. Mengisi satuan, harga, dan minimum charge.' : 'Kamus harga belum tersedia.' }}</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label required" for="boq-job-name">Uraian Pekerjaan</label>
                        <input type="text" class="form-control" name="job_name" id="boq-job-name" maxlength="191" required>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label" for="boq-job-volume">Volume</label>
                        <input type="number" class="form-control" name="job_volume_estimated" id="boq-job-volume" min="0" step="0.0001">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label" for="boq-job-unit">Satuan</label>
                        <input type="text" class="form-control" name="unit" id="boq-job-unit" maxlength="30" placeholder="m², kg, ls">
                    </div>
                    @if ($show_prices)
                        <div class="col-md-6">
                            <label class="form-label" for="boq-job-unit-price">Harga Satuan (IDR)</label>
                            <input type="number" class="form-control" name="unit_price" id="boq-job-unit-price" min="0" step="0.01">
                            <div class="form-hint text-warning d-none" id="boq-job-minimum"></div>
                        </div>
                    @endif

                    <div class="col-12">
                        <div class="border rounded p-3">
                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <div class="fw-semibold"><i class="ti ti-calculator me-1"></i>Kalkulator Pelat / Pipa</div>
                                <select class="form-select form-select-sm w-auto" name="calculation[type]" id="boq-calc-type">
                                    <option value="">Tidak dipakai</option>
                                    @foreach (\App\Services\project_boq_service::CALCULATION_TYPES as $calc_key => $calc_label)
                                        <option value="{{ $calc_key }}">{{ $calc_label }}</option>
                                    @endforeach
                                </select>
                                <select class="form-select form-select-sm w-auto d-none" name="calculation[basis]" id="boq-calc-basis"></select>
                            </div>
                            <div class="row g-2 mt-1 d-none" id="boq-calc-fields">
                                <div class="col-6 col-md-3 calc-plate"><label class="form-label small">Panjang (mm)</label><input type="number" class="form-control form-control-sm" name="calculation[length]" min="0" step="0.01"></div>
                                <div class="col-6 col-md-3 calc-plate"><label class="form-label small">Lebar (mm)</label><input type="number" class="form-control form-control-sm" name="calculation[width]" min="0" step="0.01"></div>
                                <div class="col-6 col-md-3 calc-plate"><label class="form-label small">Tebal (mm)</label><input type="number" class="form-control form-control-sm" name="calculation[thickness]" min="0" step="0.01"></div>
                                <div class="col-6 col-md-3 calc-pipe"><label class="form-label small">Diameter luar (mm)</label><input type="number" class="form-control form-control-sm" name="calculation[diameter]" min="0" step="0.01"></div>
                                <div class="col-6 col-md-3 calc-pipe"><label class="form-label small">Tebal dinding (mm)</label><input type="number" class="form-control form-control-sm" name="calculation[wall_thickness]" min="0" step="0.01"></div>
                                <div class="col-6 col-md-3 calc-pipe"><label class="form-label small">Panjang (mm)</label><input type="number" class="form-control form-control-sm" name="calculation[pipe_length]" min="0" step="0.01"></div>
                                <div class="col-4 col-md-2"><label class="form-label small">Jumlah</label><input type="number" class="form-control form-control-sm" name="calculation[quantity]" min="1" step="1" value="1"></div>
                                <div class="col-4 col-md-2"><label class="form-label small">Massa jenis</label><input type="number" class="form-control form-control-sm" name="calculation[density]" min="1" step="0.01" value="7850"></div>
                                <div class="col-4 col-md-2"><label class="form-label small">Faktor aman</label><input type="number" class="form-control form-control-sm" name="calculation[safety_factor]" min="1" max="5" step="0.01" value="1.1"></div>
                                <div class="col-12"><div class="small text-secondary" id="boq-calc-preview"></div></div>
                            </div>
                            <div class="form-hint mt-1">Bila dipakai, volume dan satuan diisi otomatis dari hasil kalkulator. Pipa di bawah 500 mm ditagih 500 mm, di bawah 1.000 mm ditagih 1.000 mm per batang.</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="boq-job-responsible-kind">Pelaksana</label>
                        <select class="form-select" name="responsible_kind" id="boq-job-responsible-kind">
                            <option value="">-</option>
                            @foreach ($responsible_kinds as $kind_key => $kind_label)
                                <option value="{{ $kind_key }}">{{ $kind_label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label" for="boq-job-responsible-name">Nama Pelaksana</label>
                        <input type="text" class="form-control" name="responsible_name" id="boq-job-responsible-name" maxlength="191">
                    </div>
                    <div class="col-6">
                        <label class="form-label" for="boq-job-est-start">Rencana Mulai</label>
                        <input type="date" class="form-control" name="est_start_date" id="boq-job-est-start">
                    </div>
                    <div class="col-6">
                        <label class="form-label" for="boq-job-est-finish">Rencana Selesai</label>
                        <input type="date" class="form-control" name="est_finish_date" id="boq-job-est-finish">
                    </div>

                    @if ($show_progress)
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="boq-job-progress">Progres (%)</label>
                            <input type="number" class="form-control" name="progress_percent" id="boq-job-progress" min="0" max="100" step="0.01">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="boq-job-status">Status</label>
                            <select class="form-select" name="status" id="boq-job-status">
                                @foreach ($job_status_labels as $status_key => $status_label)
                                    <option value="{{ $status_key }}">{{ $status_label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="boq-job-actual-volume">Volume Realisasi</label>
                            <input type="number" class="form-control" name="actual_volume" id="boq-job-actual-volume" min="0" step="0.0001">
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="boq-job-actual-start">Realisasi Mulai</label>
                            <input type="date" class="form-control" name="actual_start_date" id="boq-job-actual-start">
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="boq-job-actual-finish">Realisasi Selesai</label>
                            <input type="date" class="form-control" name="actual_finish_date" id="boq-job-actual-finish">
                        </div>
                    @endif

                    <div class="col-12">
                        <label class="form-label" for="boq-job-notes">Catatan</label>
                        <textarea class="form-control" name="notes" id="boq-job-notes" rows="2" maxlength="2000"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal modal-blur fade" id="boq-material-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <form class="modal-content" id="boq-material-form" method="POST" action="" novalidate>
            @csrf
            <input type="hidden" name="_method" value="POST">
            <input type="hidden" name="form_kind" value="material">
            <input type="hidden" name="form_action" value="">
            <input type="hidden" name="form_title" value="">
            <input type="hidden" name="price_item_text" value="">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">Material</h5>
                    <div class="small text-secondary" id="boq-material-title"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                @if ($errors->any() && $old_form && $old_form['kind'] === 'material')
                    <div class="alert alert-danger"><ul class="mb-0 ps-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label" for="boq-material-price-item">Item Kamus Harga</label>
                        <select class="form-select" name="price_book_item_id" id="boq-material-price-item" data-group="material" @disabled($price_book === null)></select>
                    </div>
                    <div class="col-12">
                        <label class="form-label required" for="boq-material-name">Nama Material</label>
                        <input type="text" class="form-control" name="material_name" id="boq-material-name" maxlength="191" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label" for="boq-material-volume">Volume</label>
                        <input type="number" class="form-control" name="volume" id="boq-material-volume" min="0" step="0.0001">
                    </div>
                    <div class="col-6">
                        <label class="form-label" for="boq-material-unit">Satuan</label>
                        <input type="text" class="form-control" name="unit" id="boq-material-unit" maxlength="30">
                    </div>
                    @if ($show_prices)
                        <div class="col-12">
                            <label class="form-label" for="boq-material-unit-price">Harga Satuan (IDR)</label>
                            <input type="number" class="form-control" name="unit_price" id="boq-material-unit-price" min="0" step="0.01">
                            <div class="form-hint text-warning d-none" id="boq-material-minimum"></div>
                        </div>
                    @endif
                    <div class="col-12">
                        <label class="form-label" for="boq-material-dimension">Dimensi / Spesifikasi</label>
                        <input type="text" class="form-control" name="dimension" id="boq-material-dimension" maxlength="191" placeholder="Contoh: Pelat 10 mm, Pipa 2 inci SCH 40">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="boq-material-notes">Catatan</label>
                        <textarea class="form-control" name="notes" id="boq-material-notes" rows="2" maxlength="2000"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Simpan</button>
            </div>
        </form>
    </div>
</div>

<form id="boq-delete-form" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>

@push('styles')
    <style>
        .boq-table .boq-group-row td { background: var(--tblr-bg-surface-secondary); }
        .boq-table .boq-material-row td { border-top-style: dashed; }
        .boq-table.hide-materials .boq-material-row { display: none; }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            const job_store_action = @json(route('project.job-document.workflow.job.store', $job_base));
            const price_items_url = @json(route('project.job-document.workflow.price-items', $project->unique_id));
            const calc_bases = @json(\App\Services\project_boq_service::CALCULATION_BASES);
            const old_form = @json($old_form);
            const $job_modal = $('#boq-job-modal');
            const $job_form = $('#boq-job-form');
            const $material_modal = $('#boq-material-modal');
            const $material_form = $('#boq-material-form');
            const number_id = (value, digits) => Number(value).toLocaleString('id-ID', { maximumFractionDigits: digits });

            const validation_options = {
                errorElement: 'div',
                errorClass: 'invalid-feedback',
                ignore: ':hidden:not(select)',
                highlight: element => $(element).addClass('is-invalid'),
                unhighlight: element => $(element).removeClass('is-invalid'),
                errorPlacement: function (error, element) {
                    error.insertAfter(element.hasClass('select2-hidden-accessible') ? element.next('.select2') : element);
                },
            };
            $.validator.addMethod('date_not_before', function (value, element, start_selector) {
                const start = $(start_selector).val();
                return this.optional(element) || !start || value >= start;
            });
            $.extend($.validator.messages, {
                required: 'Kolom ini wajib diisi.',
                maxlength: $.validator.format('Maksimal {0} karakter.'),
                number: 'Masukkan angka yang valid.',
                min: $.validator.format('Nilai minimal {0}.'),
                max: $.validator.format('Nilai maksimal {0}.'),
                step: 'Jumlah angka desimal tidak valid.',
            });

            const job_validator = $job_form.validate($.extend({}, validation_options, {
                rules: {
                    project_job_group_id: { required: () => $job_form.find('[name=parent_job_id]').val() === '' },
                    est_finish_date: { date_not_before: '#boq-job-est-start' },
                    actual_finish_date: { date_not_before: '#boq-job-actual-start' },
                    'calculation[length]': { required: () => $('#boq-calc-type').val() === 'plate', min: 0.01 },
                    'calculation[width]': { required: () => $('#boq-calc-type').val() === 'plate', min: 0.01 },
                    'calculation[thickness]': { required: () => $('#boq-calc-type').val() === 'plate', min: 0.01 },
                    'calculation[diameter]': { required: () => $('#boq-calc-type').val() === 'pipe', min: 0.01 },
                    'calculation[wall_thickness]': { required: () => $('#boq-calc-type').val() === 'pipe', min: 0.01 },
                    'calculation[pipe_length]': { required: () => $('#boq-calc-type').val() === 'pipe', min: 0.01 },
                },
                messages: {
                    project_job_group_id: { required: 'Kelompok pekerjaan wajib dipilih.' },
                    job_name: { required: 'Uraian pekerjaan wajib diisi.' },
                    est_finish_date: { date_not_before: 'Rencana selesai tidak boleh sebelum rencana mulai.' },
                    actual_finish_date: { date_not_before: 'Realisasi selesai tidak boleh sebelum realisasi mulai.' },
                },
            }));
            const material_validator = $material_form.validate($.extend({}, validation_options, {
                messages: { material_name: { required: 'Nama material wajib diisi.' } },
            }));

            function init_price_select($select, $modal, on_pick) {
                $select.select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    allowClear: true,
                    placeholder: 'Cari kode / nama item',
                    dropdownParent: $modal,
                    minimumInputLength: 0,
                    language: { noResults: () => 'Item tidak ditemukan', searching: () => 'Mencari...', errorLoading: () => 'Gagal memuat item' },
                    ajax: {
                        url: price_items_url,
                        delay: 250,
                        data: params => ({ q: params.term || '', group: $select.data('group') }),
                        processResults: response => ({ results: response.data }),
                    },
                });
                $select.on('select2:select', function (event) {
                    $select.closest('form').find('[name=price_item_text]').val(event.params.data.text);
                    on_pick(event.params.data);
                });
                $select.on('select2:clear', function () {
                    $select.closest('form').find('[name=price_item_text]').val('');
                    on_pick(null);
                });
            }

            function set_price_option($select, item) {
                $select.empty();
                if (item && item.id) {
                    $select.append(new Option(item.text, item.id, true, true));
                }
                $select.trigger('change.select2');
            }

            function show_minimum($hint, minimum, unit) {
                const has_minimum = minimum !== null && minimum !== undefined && minimum !== '';
                $hint.toggleClass('d-none', !has_minimum).text(has_minimum ? 'Minimum charge: ' + number_id(minimum, 4) + ' ' + (unit || '') + ' (volume tagih tidak kurang dari nilai ini).' : '');
            }

            function fill_form($form, values) {
                Object.entries(values).forEach(function ([name, value]) {
                    if (value !== null && typeof value === 'object') {
                        fill_form($form, Object.fromEntries(Object.entries(value).map(([key, inner]) => [name + '[' + key + ']', inner])));
                        return;
                    }
                    const $field = $form.find('[name="' + name + '"]');
                    if ($field.length && !$field.is('[type=hidden]')) {
                        $field.val(value ?? '');
                    }
                });
            }

            function reset_form($form, validator) {
                $form[0].reset();
                validator.resetForm();
                $form.find('.is-invalid').removeClass('is-invalid');
                $form.find('.alert-danger').remove();
                $form.find('[name=price_item_text]').val('');
            }

            init_price_select($('#boq-job-price-item'), $job_modal, function (item) {
                if (item) {
                    if ($('#boq-job-name').val() === '') { $('#boq-job-name').val(item.name); }
                    if ($('#boq-calc-type').val() === '') { $('#boq-job-unit').val(item.unit); }
                    $('#boq-job-unit-price').val(item.unit_price);
                }
                show_minimum($('#boq-job-minimum'), item ? item.minimum_quantity : null, item ? item.unit : '');
            });
            init_price_select($('#boq-material-price-item'), $material_modal, function (item) {
                if (item) {
                    if ($('#boq-material-name').val() === '') { $('#boq-material-name').val(item.name); }
                    $('#boq-material-unit').val(item.unit);
                    $('#boq-material-unit-price').val(item.unit_price);
                }
                show_minimum($('#boq-material-minimum'), item ? item.minimum_quantity : null, item ? item.unit : '');
            });

            // Mirrors project_boq_service::calculate() so users see the result before saving.
            function refresh_calculator() {
                const type = $('#boq-calc-type').val();
                const $basis = $('#boq-calc-basis');
                const current_basis = $basis.val();
                $('#boq-calc-fields, #boq-calc-basis').toggleClass('d-none', type === '');
                $('#boq-job-volume, #boq-job-unit').prop('readonly', type !== '');
                if (type === '') { $('#boq-calc-preview').text(''); return; }

                if ($basis.data('type') !== type) {
                    $basis.empty().data('type', type);
                    Object.entries(calc_bases[type]).forEach(([key, label]) => $basis.append(new Option(label, key)));
                    if (current_basis && calc_bases[type][current_basis]) { $basis.val(current_basis); }
                }
                $('.calc-plate').toggleClass('d-none', type !== 'plate');
                $('.calc-pipe').toggleClass('d-none', type !== 'pipe');

                const field = name => parseFloat($job_form.find('[name="calculation[' + name + ']"]').val()) || 0;
                const quantity = Math.max(1, Math.trunc(field('quantity')) || 1);
                const density = field('density') > 0 ? field('density') : 7850;
                const safety = field('safety_factor') >= 1 ? field('safety_factor') : 1.1;
                const basis = $basis.val();
                let weight = 0, volume = 0, unit = '', detail = '';

                if (type === 'plate') {
                    weight = field('length') * field('width') * field('thickness') * 1e-9 * density * quantity * safety;
                    const area = field('length') * field('width') * 1e-6 * quantity;
                    volume = basis === 'm2' ? area : weight;
                    unit = basis === 'm2' ? 'm²' : 'kg';
                    detail = 'Luas ' + number_id(area, 4) + ' m²';
                } else {
                    const length = field('pipe_length');
                    const billed = length < 500 ? 500 : (length < 1000 ? 1000 : length);
                    const inner = field('diameter') - 2 * field('wall_thickness');
                    const section = Math.PI / 4 * (field('diameter') ** 2 - inner ** 2) * 1e-6;
                    const length_m = billed / 1000 * quantity;
                    weight = section * billed / 1000 * quantity * density * safety;
                    volume = basis === 'm' ? length_m : weight;
                    unit = basis === 'm' ? 'm' : 'kg';
                    detail = 'Panjang tagih ' + number_id(billed, 0) + ' mm/batang, total ' + number_id(length_m, 4) + ' m';
                }
                $('#boq-job-volume').val(volume > 0 ? volume.toFixed(4) : '');
                $('#boq-job-unit').val(unit);
                $('#boq-calc-preview').text('Berat ' + number_id(weight, 4) + ' kg · ' + detail + ' → volume ' + number_id(volume, 4) + ' ' + unit);
            }
            $('#boq-calc-type, #boq-calc-basis').on('change', refresh_calculator);
            $('#boq-calc-fields').on('input', 'input', refresh_calculator);

            function open_job_form(options) {
                reset_form($job_form, job_validator);
                const is_child = options.is_child;
                $job_form.attr('action', options.action).find('[name=_method]').val(options.method);
                $job_form.find('[name=form_action]').val(options.action);
                $job_form.find('[name=form_title]').val(options.title);
                $job_form.find('[name=parent_job_id]').val(options.parent_id || '');
                $job_form.find('[name=form_is_child]').val(is_child ? '1' : '0');
                $('#boq-job-title').text(options.title);
                $('#boq-group-field').toggleClass('d-none', is_child);
                $('#boq-parent-info').toggleClass('d-none', !options.parent_name).find('strong').text(options.parent_name || '');
                $('#boq-calc-basis').removeData('type');
                fill_form($job_form, options.values || {});
                set_price_option($('#boq-job-price-item'), options.price_item);
                $job_form.find('[name=price_item_text]').val(options.price_item ? options.price_item.text : '');
                show_minimum($('#boq-job-minimum'), options.minimum, $('#boq-job-unit').val());
                refresh_calculator();
                if (options.values && options.values.calculation) {
                    $('#boq-calc-basis').val(options.values.calculation.basis);
                    refresh_calculator();
                }
                $job_modal.modal('show');
            }

            function open_material_form(options) {
                reset_form($material_form, material_validator);
                $material_form.attr('action', options.action).find('[name=_method]').val(options.method);
                $material_form.find('[name=form_action]').val(options.action);
                $material_form.find('[name=form_title]').val(options.title);
                $('#boq-material-title').text(options.title);
                fill_form($material_form, options.values || {});
                set_price_option($('#boq-material-price-item'), options.price_item);
                $material_form.find('[name=price_item_text]').val(options.price_item ? options.price_item.text : '');
                show_minimum($('#boq-material-minimum'), options.minimum, $('#boq-material-unit').val());
                $material_modal.modal('show');
            }

            $('#boq-add-job').on('click', () => open_job_form({ action: job_store_action, method: 'POST', title: 'Tambah Pekerjaan', is_child: false }));

            $(document).on('click', '.boq-add-child', function () {
                open_job_form({ action: job_store_action, method: 'POST', title: 'Tambah Sub Pekerjaan', is_child: true, parent_id: this.dataset.parentId, parent_name: this.dataset.parentName });
            });

            $(document).on('click', '.boq-edit-job', function () {
                const job = JSON.parse(this.dataset.job);
                const values = Object.assign({}, job);
                ['id', 'action', 'is_child', 'price_item', 'minimum_quantity'].forEach(key => delete values[key]);
                open_job_form({ action: job.action, method: 'PUT', title: 'Ubah Pekerjaan', is_child: job.is_child, values: values, price_item: job.price_item, minimum: job.minimum_quantity });
            });

            $(document).on('click', '.boq-add-material', function () {
                open_material_form({ action: this.dataset.action, method: 'POST', title: this.dataset.jobName });
            });

            $(document).on('click', '.boq-edit-material', function () {
                const material = JSON.parse(this.dataset.material);
                const values = Object.assign({}, material);
                ['action', 'job_name', 'price_item', 'minimum_quantity'].forEach(key => delete values[key]);
                open_material_form({ action: material.action, method: 'PUT', title: material.job_name, values: values, price_item: material.price_item, minimum: material.minimum_quantity });
            });

            $(document).on('click', '.boq-delete', function () {
                const action = this.dataset.action;
                Swal.fire({
                    title: this.dataset.title,
                    text: this.dataset.text,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#d63939',
                }).then(result => {
                    if (result.isConfirmed) {
                        $('#boq-delete-form').attr('action', action).trigger('submit');
                    }
                });
            });

            if (old_form) {
                const input = old_form.input || {};
                const price_item = input.price_book_item_id ? { id: input.price_book_item_id, text: input.price_item_text || input.price_book_item_id } : null;
                const values = Object.assign({}, input);
                ['price_book_item_id', 'price_item_text', 'parent_job_id'].forEach(key => delete values[key]);
                const $alert = $((old_form.kind === 'job' ? '#boq-job-form' : '#boq-material-form') + ' .alert-danger').detach();
                if (old_form.kind === 'job') {
                    open_job_form({ action: old_form.action, method: old_form.method, title: old_form.title || 'Pekerjaan', is_child: input.form_is_child === '1', parent_id: input.parent_job_id, values: values, price_item: price_item });
                    $job_form.find('.modal-body').prepend($alert);
                } else {
                    open_material_form({ action: old_form.action, method: old_form.method, title: old_form.title, values: values, price_item: price_item });
                    $material_form.find('.modal-body').prepend($alert);
                }
            }
        })();
    </script>
@endpush
