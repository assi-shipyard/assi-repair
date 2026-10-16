@extends('layouts.app')

@section('title', 'Form Permohonan Docking Space')
@section('body_title', 'Permohonan Docking Space')

@section('buttons_beside_title')
    <a href="{{ route('docking-space-request.index') }}" class="btn btn-outline-secondary">Lihat Daftar</a>
@endsection

@section('content')
    @include('partials.flash')

    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm bg-primary-lt">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span class="avatar avatar-md bg-primary text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M12 9v12m-8 -8a8 8 0 0 0 16 0m1 0h-2m-14 0h-2"/>
                                <path d="M12 6m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/>
                            </svg>
                        </span>
                        <div class="ms-3">
                            <div class="fw-bold fs-3">Ajukan Docking</div>
                            <div class="text-secondary small">Permohonan untuk kebutuhan dock kapal dan evaluasi kapasitas otomatis</div>
                        </div>
                    </div>

                    <div class="list-group list-group-flush rounded-3 overflow-hidden">
                        <div class="list-group-item bg-transparent px-0 py-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary-subtle text-primary">1</span>
                                <span class="fw-medium">Pilih kapal atau proyek yang sudah ada</span>
                            </div>
                        </div>
                        <div class="list-group-item bg-transparent px-0 py-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary-subtle text-primary">2</span>
                                <span class="fw-medium">Tentukan preferensi docking space</span>
                            </div>
                        </div>
                        <div class="list-group-item bg-transparent px-0 py-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary-subtle text-primary">3</span>
                                <span class="fw-medium">Isi jadwal dan catatan kebutuhan</span>
                            </div>
                        </div>
                        <div class="list-group-item bg-transparent px-0 py-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary-subtle text-primary">4</span>
                                <span class="fw-medium">Lampirkan dokumen kapal</span>
                            </div>
                        </div>
                        <div class="list-group-item bg-transparent px-0 py-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary-subtle text-primary">5</span>
                                <span class="fw-medium">Pemeriksaan sistem, persetujuan Engineering, lalu Produksi</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4 shadow-sm">
                <div class="card-header">
                    <h3 class="card-title mb-0">Informasi penting</h3>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex gap-2 mb-3">
                            <span class="text-primary"><i class="ti ti-info-circle"></i></span>
                            <span>Preferensi docking space bersifat opsional. Jika kosong, sistem akan mengevaluasi otomatis dari semua ruang docking yang tersedia.</span>
                        </li>
                        <li class="d-flex gap-2 mb-3">
                            <span class="text-primary"><i class="ti ti-calendar-time"></i></span>
                            <span>Jadwal mulai wajib diisi agar evaluasi kapasitas dapat memproses kebutuhan dock dengan akurat.</span>
                        </li>
                        <li class="d-flex gap-2 mb-0">
                            <span class="text-primary"><i class="ti ti-alert-circle"></i></span>
                            <span>Sistem memeriksa bentrok jadwal dan kesesuaian dimensi kapal. Jika lolos, permohonan diteruskan ke persetujuan Engineering, lalu Produksi.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card shadow-sm">
                <div class="card-header border-0">
                    <div>
                        <h3 class="card-title mb-1">Form Permohonan Docking</h3>
                        <div class="text-secondary">Silakan lengkapi informasi kebutuhan dock kapal agar tim dapat mengevaluasi jadwal dan kapasitas dengan cepat.</div>
                    </div>
                </div>
                <div class="card-body">
                    <form id="docking-request-form" method="POST" action="{{ route('docking-space-request.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="row g-3">
                            <div class="col-12">
                                <div class="alert alert-light border rounded-3 mb-0">
                                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                        <div>
                                            <div class="fw-semibold text-dark">Ringkasan permohonan</div>
                                            <div id="project-summary" class="text-secondary mt-1">Belum ada kapal dipilih.</div>
                                        </div>
                                        <span class="badge bg-secondary-lt text-secondary">Ringkasan otomatis</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Permohonan untuk</label>
                                <div class="btn-group w-100" role="group" aria-label="Pilihan jenis permohonan">
                                    <input type="radio" class="btn-check" name="request_scope" id="request_scope_ship" value="ship" @checked(old('request_scope', old('project_id') ? 'project' : 'ship') === 'ship')>
                                    <label class="btn btn-outline-primary" for="request_scope_ship">Kapal (Vessel)</label>

                                    <input type="radio" class="btn-check" name="request_scope" id="request_scope_project" value="project" @checked(old('request_scope', old('project_id') ? 'project' : 'ship') === 'project')>
                                    <label class="btn btn-outline-primary" for="request_scope_project">Proyek yang sudah ada</label>
                                </div>
                            </div>

                            <div class="col-md-6 js-project-field">
                                <label class="form-label">Proyek</label>
                                <select class="form-select" name="project_id">
                                    <option value="">Pilih proyek</option>
                                    @foreach ($projects as $project)
                                        <option value="{{ $project->id }}" data-ship-id="{{ $project->ship_id }}" @selected(old('project_id') == $project->id)>
                                            {{ $project->project_code }} - {{ $project->ship?->name ?? '-' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 d-none js-ship-field">
                                <label class="form-label">Kapal</label>
                                <select class="form-select" name="ship_id">
                                    <option value="">Pilih kapal</option>
                                    @foreach ($ships as $ship)
                                        <option value="{{ $ship->id }}" @selected(old('ship_id') == $ship->id)>
                                            {{ $ship->name }} - {{ $ship->company?->name ?? 'Perusahaan belum ditentukan' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Docking space preferensi</label>
                                <select class="form-select" name="requested_docking_space_id">
                                    <option value="">Sistem akan mengevaluasi otomatis</option>
                                    @foreach ($docking_spaces as $docking_space)
                                        <option value="{{ $docking_space->id }}" data-label="{{ $docking_space->name }}" @selected(old('requested_docking_space_id') == $docking_space->id)>
                                            {{ $docking_space->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-hint mt-1">Kosongkan bila Anda ingin sistem memilih ruang docking terbaik berdasarkan ketersediaan.</div>
                                <div id="dock-compatibility-hint" class="form-hint mt-1" role="status"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="requested_start_at">Jadwal mulai docking</label>
                                <input type="datetime-local" class="form-control" id="requested_start_at" name="requested_start_at" value="{{ old('requested_start_at') }}" data-date-display="long" placeholder="Pilih tanggal dan waktu" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="requested_end_at">Jadwal selesai docking (estimasi)</label>
                                <input type="datetime-local" class="form-control" id="requested_end_at" name="requested_end_at" value="{{ old('requested_end_at') }}" data-date-display="long" placeholder="Pilih tanggal dan waktu">
                                <div class="form-hint">Waktu Indonesia Barat (WIB).</div>
                            </div>

                            <div class="col-12">
                                <div class="card border shadow-none bg-light-subtle">
                                    <div class="card-body py-3">
                                        <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                                            <div>
                                                <div class="fw-semibold">Preview jadwal</div>
                                                <div id="schedule-preview" class="text-secondary small mt-1">Masukkan jadwal mulai dan selesai untuk melihat estimasi durasi.</div>
                                            </div>
                                            <span id="space-preference-summary" class="badge bg-azure-lt text-azure">Evaluasi otomatis</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label required">Dokumen kapal</label>
                                <div id="document-rows" class="d-flex flex-column gap-2"></div>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="add-document-row">Tambah dokumen</button>
                                <div class="form-hint mt-1">Wajib melampirkan Ship Particular. Format: pdf, doc, docx, xls, xlsx, dwg, jpg, png (maks. 20MB per file, 10 dokumen). Dokumen akan ditinjau Engineering.</div>
                                @error('documents')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                @foreach ($errors->get('documents.*') as $messages)
                                    @foreach ($messages as $message)<div class="text-danger small mt-1">{{ $message }}</div>@endforeach
                                @endforeach
                            </div>

                            <div class="col-12">
                                <label class="form-label">Catatan permohonan</label>
                                <textarea class="form-control" name="request_notes" rows="4" placeholder="Contoh: kapal membutuhkan penanganan cepat sebelum tahap uji coba, atau docking diprioritaskan seiring dengan jadwal inspeksi teknis.">{{ old('request_notes') }}</textarea>
                                <div class="form-hint mt-1">Tambahkan detail penting seperti prioritas pekerjaan, kebutuhan khusus, atau catatan teknis untuk mempercepat proses evaluasi.</div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2 flex-wrap">
                            <button class="btn btn-primary" type="submit">Kirim permohonan</button>
                            <button class="btn btn-outline-secondary" type="reset">Reset formulir</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            const dock_options_by_ship = @json($dock_options_by_ship);

            function update_dock_options() {
                const is_project = $('input[name="request_scope"]:checked').val() === 'project';
                const ship_id = is_project
                    ? $('select[name="project_id"] option:selected').data('ship-id')
                    : $('select[name="ship_id"]').val();
                const options = dock_options_by_ship[ship_id] || {};
                const select = $('select[name="requested_docking_space_id"]');
                let compatible_count = 0;
                let cleared = false;

                select.find('option[value!=""]').each(function () {
                    const result = options[this.value];
                    const compatible = !!result && result.is_compatible;
                    this.disabled = !compatible;
                    this.textContent = $(this).data('label') + (result && !compatible ? ' - ' + result.reason : '');
                    if (compatible) {
                        compatible_count++;
                    }
                    if (this.selected && !compatible) {
                        cleared = true;
                    }
                });

                if (cleared) {
                    select.val('');
                }

                $('#dock-compatibility-hint').text(!ship_id
                    ? 'Pilih kapal terlebih dahulu untuk melihat dok yang sesuai.'
                    : (cleared ? 'Preferensi sebelumnya tidak sesuai untuk kapal ini dan telah dikosongkan. ' : '')
                        + compatible_count + ' dok sesuai spesifikasi. Ketersediaan jadwal diperiksa kembali saat permohonan dikirim.');
            }

            function formatDuration(startDate, endDate) {
                if (!startDate || !endDate) {
                    return null;
                }

                const start = new Date(startDate);
                const end = new Date(endDate);

                if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime()) || end < start) {
                    return null;
                }

                const diffMs = end.getTime() - start.getTime();
                const diffHours = diffMs / (1000 * 60 * 60);

                if (diffHours < 24) {
                    return Math.round(diffHours) + ' jam';
                }

                const diffDays = diffHours / 24;
                const roundedDays = Math.round(diffDays * 10) / 10;
                return roundedDays + ' hari';
            }

            function toggleRequestScope() {
                const requestScope = $('input[name="request_scope"]:checked').val();
                const isProjectMode = requestScope === 'project';

                $('.js-project-field').toggleClass('d-none', !isProjectMode);
                $('.js-ship-field').toggleClass('d-none', isProjectMode);

                if (isProjectMode) {
                    $('select[name="ship_id"]').val('');
                } else {
                    $('select[name="project_id"]').val('');
                }
            }

            function updateSummary() {
                const currentScope = $('input[name="request_scope"]:checked').val();
                const projectValue = $('select[name="project_id"]').val();
                const shipValue = $('select[name="ship_id"]').val();

                const projectLabel = $('select[name="project_id"] option:selected').text().trim();
                const shipLabel = $('select[name="ship_id"] option:selected').text().trim();
                const selectedProject = currentScope === 'project' ? (projectValue ? projectLabel : 'Belum ada proyek dipilih.') : 'Mode kapal / ship';
                const selectedShip = currentScope === 'ship' ? (shipValue ? shipLabel : 'Belum ada kapal dipilih.') : 'Mode proyek';
                $('#project-summary').text(currentScope === 'project' ? selectedProject : selectedShip);

                const dockingValue = $('select[name="requested_docking_space_id"]').val();
                const dockingLabel = $('select[name="requested_docking_space_id"] option:selected').text().trim();
                const selectedDocking = dockingValue ? dockingLabel : 'Evaluasi otomatis';
                $('#space-preference-summary').text(selectedDocking);

                const startValue = $('input[name="requested_start_at"]').val();
                const endValue = $('input[name="requested_end_at"]').val();

                if (startValue && endValue) {
                    const durationText = formatDuration(startValue, endValue);
                    if (durationText) {
                        $('#schedule-preview').text('Estimasi durasi pengerjaan: ' + durationText + '.');
                        return;
                    }
                }

                if (startValue && !endValue) {
                    $('#schedule-preview').text('Jadwal mulai telah ditentukan. Selesai docking dapat diisi untuk menyesuaikan estimasi durasi.');
                    return;
                }

                if (!startValue && endValue) {
                    $('#schedule-preview').text('Jadwal selesai belum dapat dihitung karena jadwal mulai belum diisi.');
                    return;
                }

                $('#schedule-preview').text('Masukkan jadwal mulai dan selesai untuk melihat estimasi durasi.');
            }

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

            $('input[name="request_scope"]').on('change', function () {
                toggleRequestScope();
                updateSummary();
            });

            $('select[name="project_id"], select[name="ship_id"]').on('change', function () {
                update_dock_options();
                updateSummary();
            });
            $('input[name="request_scope"]').on('change', function () {
                update_dock_options();
                updateSummary();
            });
            $('select[name="requested_docking_space_id"]').on('change', function () {
                updateSummary();
            });
            // Delegated: Vanilla Calendar Pro swaps these inputs after this script runs.
            $('#docking-request-form').on('change', 'input[name="requested_start_at"], input[name="requested_end_at"]', function () {
                updateSummary();
                docking_request_validator.element(this);
            });
            toggleRequestScope();
            update_dock_options();
            updateSummary();

            $('#docking-request-form').on('reset', function () {
                setTimeout(function () {
                    toggleRequestScope();
                    update_dock_options();
                    updateSummary();
                }, 0);
            });

            const docking_request_validator = $('#docking-request-form').validate({
                ignore: ':hidden:not(.js-date-value)',
                errorClass: 'is-invalid',
                validClass: 'is-valid',
                errorElement: 'div',
                errorPlacement: function (error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('div').append(error);
                },
                highlight: function (element) {
                    $(element).addClass('is-invalid').removeClass('is-valid');
                    $(element).siblings('.js-date-display').addClass('is-invalid');
                },
                unhighlight: function (element) {
                    $(element).removeClass('is-invalid').addClass('is-valid');
                    $(element).siblings('.js-date-display').removeClass('is-invalid');
                },
                rules: {
                    project_id: { required: true },
                    ship_id: { required: true },
                    requested_start_at: { required: true },
                    requested_end_at: {
                        greaterThanOrEqual: '[name="requested_start_at"]',
                    },
                    request_notes: { maxlength: 2000 },
                },
                messages: {
                    project_id: { required: 'Proyek wajib dipilih.' },
                    ship_id: { required: 'Kapal wajib dipilih.' },
                    requested_start_at: { required: 'Jadwal mulai docking wajib diisi.' },
                    requested_end_at: {
                        greaterThanOrEqual: 'Jadwal selesai docking tidak boleh lebih awal dari jadwal mulai.',
                    },
                    request_notes: { maxlength: 'Catatan permohonan maksimal 2000 karakter.' },
                },
            });

            const documentTypes = @json(\App\Models\DockingRequestDocument::TYPE_LABELS);
            let documentIndex = 0;

            function addDocumentRow(defaultType) {
                const index = documentIndex++;
                const options = Object.entries(documentTypes).map(function (entry) {
                    return '<option value="' + entry[0] + '"' + (entry[0] === defaultType ? ' selected' : '') + '>' + entry[1] + '</option>';
                }).join('');
                const row = $(
                    '<div class="row g-2 js-document-row">' +
                    '<div class="col-md-4"><select class="form-select" name="documents[' + index + '][type]">' + options + '</select></div>' +
                    '<div class="col"><input type="file" class="form-control" name="documents[' + index + '][file]" accept=".pdf,.doc,.docx,.xls,.xlsx,.dwg,.jpg,.jpeg,.png"></div>' +
                    '<div class="col-auto"><button type="button" class="btn btn-outline-danger js-remove-document">Hapus</button></div>' +
                    '</div>'
                );
                $('#document-rows').append(row);
                row.find('input[type="file"]').rules('add', { required: true, messages: { required: 'File dokumen wajib diunggah.' } });
            }

            $('#add-document-row').on('click', function () { addDocumentRow('other'); });
            $('#document-rows').on('click', '.js-remove-document', function () {
                if ($('.js-document-row').length > 1) {
                    $(this).closest('.js-document-row').remove();
                }
            });
            addDocumentRow('ship_particular');
        });
    </script>
@endpush
