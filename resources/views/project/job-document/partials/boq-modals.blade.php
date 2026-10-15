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
    $responsible_kinds = ['internal_unit' => 'Unit Internal', 'subcontractor' => 'Subkontraktor', 'owner' => 'Vendor Pemilik'];
    $calc_rules = \App\Services\boq_rule_service::RULES;
    $plate_materials = \App\Services\boq_rule_service::PLATE_MATERIALS;
    $default_safety_factor = $sheet['last_safety_factor'] ?? 1.1;
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
                        <div class="form-hint" id="boq-job-price-hint">{{ $price_book ? 'Opsional. Mengisi satuan, harga, aturan, dan minimum.' : 'Kamus harga belum tersedia.' }}</div>
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
                            <input type="hidden" name="price_item_rule" value="">
                            <input type="hidden" name="calculation[type]" id="boq-calc-type" value="">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-6">
                                    <label class="form-label" for="boq-calc-rule"><i class="ti ti-calculator me-1"></i>Aturan Perhitungan</label>
                                    <select class="form-select" name="calculation_rule" id="boq-calc-rule">
                                        <option value="">Umum (volume diisi manual)</option>
                                        @foreach ($calc_rules as $rule_key => $rule)
                                            <option value="{{ $rule_key }}">{{ $rule['label'] }} ({{ $rule['unit'] }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6"><div class="form-hint mb-md-2" id="boq-calc-rule-hint">Aturan otomatis mengikuti item Kamus Harga bila item tersebut memiliki aturan.</div></div>
                            </div>

                            <div class="row g-2 mt-1 d-none calc-panel" data-panel="plate">
                                <div class="col-md-4">
                                    <label class="form-label small required">Bahan Pelat</label>
                                    <select class="form-select form-select-sm" name="calculation[material]">
                                        @foreach ($plate_materials as $material_key => $plate_material)
                                            <option value="{{ $material_key }}">{{ $plate_material['label'] }} ({{ number_format($plate_material['density'], 0, ',', '.') }} kg/m³)</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-4 col-md-2"><label class="form-label small required">Panjang (mm)</label><input type="number" class="form-control form-control-sm" name="calculation[length]" min="0" step="0.01"></div>
                                <div class="col-4 col-md-2"><label class="form-label small required">Lebar (mm)</label><input type="number" class="form-control form-control-sm" name="calculation[width]" min="0" step="0.01"></div>
                                <div class="col-4 col-md-2"><label class="form-label small required">Tebal (mm)</label><input type="number" class="form-control form-control-sm" name="calculation[thickness]" min="0" step="0.01"></div>
                                <div class="col-6 col-md-1"><label class="form-label small required">Keping</label><input type="number" class="form-control form-control-sm" name="calculation[quantity]" min="1" step="1" value="1"></div>
                                <div class="col-6 col-md-1"><label class="form-label small required" title="Faktor keamanan sesuai tipe kapal">Faktor</label><input type="number" class="form-control form-control-sm" name="calculation[safety_factor]" min="1" max="3" step="0.01" value="{{ $default_safety_factor }}"></div>
                            </div>

                            <div class="row g-2 mt-1 d-none calc-panel" data-panel="pipe">
                                <div class="col-md-6"><label class="form-label small">Ukuran Pipa</label><input type="text" class="form-control form-control-sm" name="calculation[size]" maxlength="50" placeholder="Contoh: 2 inci SCH 40"></div>
                                <div class="col-6 col-md-3"><label class="form-label small required">Panjang / batang (m)</label><input type="number" class="form-control form-control-sm" name="calculation[length_m]" min="0" max="1000" step="0.001"></div>
                                <div class="col-6 col-md-3"><label class="form-label small required">Jumlah batang</label><input type="number" class="form-control form-control-sm" name="calculation[quantity]" min="1" step="1" value="1"></div>
                            </div>

                            <div class="mt-2 d-none calc-panel" data-panel="tugboat">
                                <div class="table-responsive">
                                    <table class="table table-sm table-vcenter mb-2">
                                        <thead><tr><th>Mulai</th><th>Selesai</th><th class="text-center">Libur</th><th class="w-1"></th></tr></thead>
                                        <tbody id="boq-tug-rows"></tbody>
                                    </table>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="boq-tug-add"><i class="ti ti-plus me-1"></i>Tambah Pemakaian</button>
                                <div class="small text-secondary mt-2">
                                    Tarif: Senin–Jumat 07.30–16.30 100%, 16.30–22.30 150%, 22.30–07.30 200%; Sabtu, Minggu &amp; hari libur 200% / 250% / 300%. Durasi tiap pemakaian dibulatkan ke atas per jam.
                                </div>
                            </div>

                            <div class="small mt-2 d-none" id="boq-calc-preview"></div>
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
                        @if ($document->document_type === \App\Models\ProjectJobDocument::TYPE_FINAL_BOQ)
                            <div class="form-hint">Vendor pemilik memerlukan persetujuan Direksi. Setelah disetujui, RAB Akhir menambahkan surcharge 20% pada biaya pekerjaan.</div>
                        @endif
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

                    @if ($show_progress && $uses_reports)
                        <div class="col-md-6">
                            <label class="form-label" for="boq-job-actual-volume">Volume Realisasi</label>
                            <input type="number" class="form-control" name="actual_volume" id="boq-job-actual-volume" min="0" step="0.0001">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="small text-secondary"><i class="ti ti-info-circle me-1"></i>Progres, status, dan tanggal realisasi diisi melalui laporan Progres Harian.</div>
                        </div>
                    @elseif ($show_progress)
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
                    @if ($can_mark_owner_supplied)
                        <div class="col-12">
                            <label class="form-check">
                                <input class="form-check-input" type="checkbox" name="owner_supplied" value="1">
                                <span class="form-check-label">Material disediakan pemilik kapal</span>
                            </label>
                            <div class="form-hint">Material ini tetap tercantum di RAB, tetapi biayanya tidak ditagihkan.</div>
                        </div>
                    @endif
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
            const calc_rules = @json($calc_rules);
            const plate_materials = @json($plate_materials);
            const tug_rates = @json(\App\Services\boq_rule_service::TUGBOAT_RATES);
            const tug_max_entries = @json(\App\Services\boq_rule_service::MAX_TUGBOAT_ENTRIES);
            const tug_max_hours = @json(\App\Services\boq_rule_service::MAX_TUGBOAT_HOURS);
            const default_safety_factor = @json((string) $default_safety_factor);
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
                    'calculation[material]': { required: true },
                    'calculation[length]': { required: true, min: 0.01, max: 1000000 },
                    'calculation[width]': { required: true, min: 0.01, max: 1000000 },
                    'calculation[thickness]': { required: true, min: 0.01, max: 1000 },
                    'calculation[quantity]': { required: true, digits: true, min: 1, max: 100000 },
                    'calculation[safety_factor]': { required: true, number: true, min: 1, max: 3 },
                    'calculation[size]': { maxlength: 50 },
                    'calculation[length_m]': { required: true, min: 0.001, max: 1000 },
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
                        if ($field.is(':checkbox')) {
                            $field.prop('checked', value === true || value === 1 || value === '1');
                        } else {
                            $field.val(value ?? '');
                        }
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
                    $('#boq-job-unit').val(item.unit);
                    $('#boq-job-unit-price').val(item.unit_price);
                }
                $job_form.data('minimum', item ? item.minimum_quantity : null);
                set_item_rule(item ? item.calculation_rule : null);
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

            const $rule = $('#boq-calc-rule');
            const $tug_rows = $('#boq-tug-rows');
            let tug_index = 0;

            // An item with a rule dictates the calculation; other options stay locked until the item changes.
            function set_item_rule(rule) {
                $job_form.find('[name=price_item_rule]').val(rule || '');
                if (rule && calc_rules[rule]) { $rule.val(rule); }
                $rule.find('option').prop('disabled', false);
                if (rule && calc_rules[rule]) { $rule.find('option').not('[value="' + rule + '"]').prop('disabled', true); }
                refresh_calculator();
            }

            function add_tug_row(entry) {
                if ($tug_rows.children().length >= tug_max_entries) { return; }
                const index = tug_index++;
                const name = field => 'calculation[entries][' + index + '][' + field + ']';
                const $row = $('<tr>').append(
                    $('<td>').append($('<input type="datetime-local" class="form-control form-control-sm" required>').attr('name', name('start_at')).val(entry?.start_at || '')),
                    $('<td>').append($('<input type="datetime-local" class="form-control form-control-sm" required>').attr('name', name('end_at')).val(entry?.end_at || '')),
                    $('<td class="text-center">').append(
                        $('<input type="hidden" value="0">').attr('name', name('is_holiday')),
                        $('<input type="checkbox" class="form-check-input" value="1" title="Hari libur nasional (tarif akhir pekan)">').attr('name', name('is_holiday'))
                            .prop('checked', [true, 1, '1', 'true'].includes(entry?.is_holiday))
                    ),
                    $('<td>').append('<button type="button" class="btn btn-sm btn-ghost-danger btn-icon boq-tug-remove" aria-label="Hapus baris"><i class="ti ti-x"></i></button>')
                );
                $tug_rows.append($row);
                $row.find('[type=datetime-local]').each(function () {
                    $(this).rules('add', { required: true, messages: { required: 'Waktu wajib diisi.' } });
                });
                $row.find('[name$="[end_at]"]').rules('add', { tug_after_start: true, tug_max_hours: true });
            }

            $.validator.addMethod('tug_after_start', function (value, element) {
                const start = $(element).closest('tr').find('[name$="[start_at]"]').val();
                return this.optional(element) || !start || value > start;
            }, 'Selesai harus setelah mulai.');
            $.validator.addMethod('tug_max_hours', function (value, element) {
                const start = parse_local($(element).closest('tr').find('[name$="[start_at]"]').val());
                const end = parse_local(value);
                return this.optional(element) || start === null || end === null || (end - start) / 3600000 <= tug_max_hours;
            }, 'Satu pemakaian maksimal ' + tug_max_hours + ' jam.');

            $('#boq-tug-add').on('click', () => { add_tug_row(null); refresh_calculator(); });
            $tug_rows.on('click', '.boq-tug-remove', function () {
                $(this).closest('tr').remove();
                if ($tug_rows.children().length === 0) { add_tug_row(null); }
                refresh_calculator();
            });

            // Wall-clock arithmetic in UTC so the browser time zone cannot shift the WIB tariff bands.
            function parse_local(value) {
                const match = /^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})/.exec(value || '');
                return match ? Date.UTC(+match[1], +match[2] - 1, +match[3], +match[4], +match[5]) : null;
            }

            function tug_entry_summary(start, end, is_holiday) {
                const hours = Math.ceil((end - start) / 3600000);
                const billed_end = start + hours * 3600000;
                let cursor = start, equivalent = 0;
                while (cursor < billed_end) {
                    const date = new Date(cursor);
                    const minute = date.getUTCHours() * 60 + date.getUTCMinutes();
                    const [band, band_end] = minute >= 450 && minute < 990 ? ['day', 990] : (minute >= 990 && minute < 1350 ? ['evening', 1350] : (minute < 450 ? ['night', 450] : ['night', 1440]));
                    const day_start = Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate());
                    const segment_end = Math.min(day_start + band_end * 60000, billed_end);
                    const day_type = is_holiday || [0, 6].includes(date.getUTCDay()) ? 'weekend' : 'weekday';
                    equivalent += (segment_end - cursor) / 60000 * tug_rates[day_type][band];
                    cursor = segment_end;
                }
                return { hours: hours, equivalent: equivalent / 6000 };
            }

            // Mirrors boq_rule_service so users see the result before saving; the server recalculates on save.
            function refresh_calculator() {
                const rule = $rule.val();
                const config = calc_rules[rule] || null;
                const calculator = config && config.calculator ? rule : '';
                const minimum = $job_form.data('minimum');
                $('#boq-calc-type').val(calculator);
                $('#boq-calc-rule-hint').text(config ? config.hint : 'Aturan otomatis mengikuti item Kamus Harga bila item tersebut memiliki aturan.');
                $('.calc-panel').each(function () {
                    const active = this.dataset.panel === calculator;
                    $(this).toggleClass('d-none', !active).find('input, select').prop('disabled', !active);
                });
                $('#boq-job-volume').prop('readonly', calculator !== '');
                $('#boq-job-unit').prop('readonly', config !== null);
                if (config) { $('#boq-job-unit').val(config.unit); }
                if (calculator === 'tugboat' && $tug_rows.children().length === 0) { add_tug_row(null); }

                const field = name => parseFloat($job_form.find('[data-panel="' + calculator + '"] [name="calculation[' + name + ']"]').val()) || 0;
                const quantity = Math.max(1, Math.trunc(field('quantity')) || 1);
                let volume = null, text = '';

                if (calculator === 'plate') {
                    const material = plate_materials[$job_form.find('[name="calculation[material]"]').val()] || plate_materials.steel;
                    const piece = field('length') * field('width') * field('thickness') * 1e-9 * material.density * field('safety_factor');
                    volume = piece * quantity;
                    const billed_piece = minimum && piece > 0 && piece < minimum ? Number(minimum) : piece;
                    text = 'Berat ' + number_id(piece, 4) + ' kg/keping × ' + quantity + ' = <strong>' + number_id(volume, 4) + ' kg</strong>'
                        + (billed_piece !== piece ? ' · ditagih ' + number_id(billed_piece * quantity, 4) + ' kg (minimum ' + number_id(minimum, 4) + ' kg/keping)' : '');
                } else if (calculator === 'pipe') {
                    const length = field('length_m');
                    const billed = length <= 0.5 ? 0.5 : (length < 1 ? 1 : length);
                    volume = length * quantity;
                    text = 'Panjang ' + number_id(volume, 4) + ' m · <strong>ditagih ' + number_id(billed * quantity, 4) + ' m</strong>' + (billed !== length ? ' (' + number_id(billed, 4) + ' m/batang)' : '');
                } else if (calculator === 'tugboat') {
                    let hours = 0, equivalent = 0;
                    $tug_rows.children().each(function () {
                        const start = parse_local($(this).find('[name$="[start_at]"]').val());
                        const end = parse_local($(this).find('[name$="[end_at]"]').val());
                        if (start === null || end === null || end <= start) { return; }
                        const summary = tug_entry_summary(start, end, $(this).find('[type=checkbox]').is(':checked'));
                        hours += summary.hours;
                        equivalent += summary.equivalent;
                    });
                    volume = hours;
                    text = 'Pemakaian ' + number_id(hours, 0) + ' jam · <strong>ditagih ' + number_id(equivalent, 4) + ' jam setara tarif dasar</strong>';
                } else if (config && config.round_up) {
                    text = 'Volume dibulatkan ke atas saat ditagih.';
                }

                if (calculator !== '') { $('#boq-job-volume').val(volume > 0 ? volume.toFixed(4) : ''); }
                $('#boq-calc-preview').toggleClass('d-none', text === '').html(text);
            }
            $rule.on('change', refresh_calculator);
            $('.calc-panel').on('input change', 'input, select', refresh_calculator);

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
                const values = $.extend(true, {}, options.values || {});
                const entries = values.calculation?.entries;
                if (values.calculation) { delete values.calculation.entries; }
                $tug_rows.empty();
                $job_form.find('.calc-panel [name="calculation[safety_factor]"]').val(default_safety_factor);
                $job_form.data('minimum', options.minimum ?? null);
                $rule.find('option').prop('disabled', false);
                $job_form.find('.calc-panel input, .calc-panel select').prop('disabled', false);
                fill_form($job_form, values);
                Object.values(entries || {}).forEach(entry => add_tug_row(entry));
                set_price_option($('#boq-job-price-item'), options.price_item);
                $job_form.find('[name=price_item_text]').val(options.price_item ? options.price_item.text : '');
                set_item_rule(options.price_item_rule || null);
                show_minimum($('#boq-job-minimum'), options.minimum, $('#boq-job-unit').val());
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
                open_job_form({ action: job.action, method: 'PUT', title: 'Ubah Pekerjaan', is_child: job.is_child, values: values, price_item: job.price_item, price_item_rule: job.price_item?.rule, minimum: job.minimum_quantity });
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
                ['price_book_item_id', 'price_item_text', 'parent_job_id', 'price_item_rule'].forEach(key => delete values[key]);
                const $alert = $((old_form.kind === 'job' ? '#boq-job-form' : '#boq-material-form') + ' .alert-danger').detach();
                if (old_form.kind === 'job') {
                    open_job_form({ action: old_form.action, method: old_form.method, title: old_form.title || 'Pekerjaan', is_child: input.form_is_child === '1', parent_id: input.parent_job_id, values: values, price_item: price_item, price_item_rule: input.price_item_rule });
                    $job_form.find('.modal-body').prepend($alert);
                } else {
                    open_material_form({ action: old_form.action, method: old_form.method, title: old_form.title, values: values, price_item: price_item });
                    $material_form.find('.modal-body').prepend($alert);
                }
            }
        })();
    </script>
@endpush
