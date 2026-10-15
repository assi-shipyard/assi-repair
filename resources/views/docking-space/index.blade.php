@extends('layouts.app')

@section('title', 'Data Dock')
@section('body_title', 'Data Dock')

@section('buttons_beside_title')
    <a href="#docking-space-form" class="btn btn-primary">
        <i class="ti ti-{{ isset($editingSpace) ? 'pencil' : 'plus' }} me-1" aria-hidden="true"></i>
        {{ isset($editingSpace) ? 'Lanjutkan Pengubahan' : 'Tambah Dock' }}
    </a>
@endsection

@section('content')
    @include('partials.flash')

    @php
        $editing_space = $editingSpace ?? null;
        $statuses = [
            'active' => ['label' => 'Aktif', 'color' => 'green', 'icon' => 'circle-check'],
            'maintenance' => ['label' => 'Pemeliharaan', 'color' => 'yellow', 'icon' => 'tool'],
            'inactive' => ['label' => 'Nonaktif', 'color' => 'secondary', 'icon' => 'circle-pause'],
        ];
        $capacity_fields = [
            'max_draft' => ['label' => 'Sarat Maksimum (m)', 'max' => '99999999.99'],
            'max_breadth' => ['label' => 'Lebar Kapal Maksimum (m)', 'max' => '99999999.99'],
            'max_tonnage' => ['label' => 'Tonase Maksimum (GT)', 'max' => '9999999999.99'],
            'max_capacity' => ['label' => 'Kapasitas Kapal', 'max' => '2147483647'],
        ];
        $optional_fields = [
            'max_length' => ['label' => 'Panjang Maksimum (m)', 'max' => '99999999.99'],
            'max_width' => ['label' => 'Lebar Maksimum (m)', 'max' => '99999999.99'],
            'max_weight' => ['label' => 'Berat Maksimum', 'max' => '9999999999.99'],
        ];
    @endphp

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="card card-sm h-100 shadow-none">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar bg-blue-lt"><i class="ti ti-anchor fs-2" aria-hidden="true"></i></span>
                    <div>
                        <div class="text-secondary small">Total Dock</div>
                        <div class="h2 mb-0">{{ $dockingSpaces->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
        @foreach ($statuses as $status_key => $status_detail)
            <div class="col-6 col-md-3">
                <div class="card card-sm h-100 shadow-none">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="avatar bg-{{ $status_detail['color'] }}-lt"><i class="ti ti-{{ $status_detail['icon'] }} fs-2" aria-hidden="true"></i></span>
                        <div>
                            <div class="text-secondary small">{{ $status_detail['label'] }}</div>
                            <div class="h2 mb-0">{{ $dockingSpaces->where('status', $status_key)->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3 align-items-start">
        <div class="col-xl-8">
            <div class="card shadow-none">
                <div class="card-header">
                    <div>
                        <h3 class="card-title mb-1">Daftar Dock</h3>
                        <div class="text-secondary small">Lokasi, status operasional, dan batas kapasitas setiap dock.</div>
                    </div>
                </div>
                <div class="card-body border-bottom" id="dock-filters" hidden>
                    <div class="row g-2 align-items-end">
                        <div class="col-sm">
                            <label for="dock-search" class="form-label">Cari dock</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-search" aria-hidden="true"></i></span>
                                <input id="dock-search" type="search" class="form-control" placeholder="Nama atau lokasi dock" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <label for="dock-status" class="form-label">Status operasional</label>
                            <select id="dock-status" class="form-select">
                                <option value="">Semua status</option>
                                @foreach ($statuses as $status_key => $status_detail)
                                    <option value="{{ $status_key }}">{{ $status_detail['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-auto">
                            <button type="button" id="dock-filter-reset" class="btn w-100">Reset</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if ($dockingSpaces->isEmpty())
                        <div class="empty py-5">
                            <div class="empty-icon"><i class="ti ti-anchor fs-1" aria-hidden="true"></i></div>
                            <p class="empty-title">Belum ada data dock</p>
                            <p class="empty-subtitle text-secondary">Tambahkan dock beserta batas kapasitasnya melalui formulir.</p>
                            <div class="empty-action"><a href="#docking-space-form" class="btn btn-primary">Tambah Dock Pertama</a></div>
                        </div>
                    @else
                        <div class="text-secondary small mb-3" id="dock-result-count" role="status" aria-live="polite">{{ $dockingSpaces->count() }} dock ditampilkan</div>
                        <div class="row g-3">
                            @foreach ($dockingSpaces as $space)
                                @php
                                    $status_detail = $statuses[$space->status] ?? ['label' => 'Tidak diketahui', 'color' => 'secondary', 'icon' => 'help'];
                                @endphp
                                <div class="col-md-6" data-dock-card data-name="{{ $space->name }}" data-location="{{ $space->location }}" data-status="{{ $space->status }}">
                                    <div class="card h-100 shadow-none {{ $editing_space?->id === $space->id ? 'border-primary' : '' }}">
                                        <div class="card-body">
                                            <div class="d-flex align-items-start gap-2 mb-3">
                                                <span class="avatar bg-blue-lt flex-shrink-0"><i class="ti ti-anchor fs-2" aria-hidden="true"></i></span>
                                                <div class="flex-fill text-break">
                                                    <h4 class="mb-1">{{ $space->name }}</h4>
                                                    <div class="text-secondary small"><i class="ti ti-map-pin me-1" aria-hidden="true"></i>{{ $space->location ?: 'Lokasi belum ditentukan' }}</div>
                                                </div>
                                            </div>
                                            <span class="badge bg-{{ $status_detail['color'] }}-lt mb-3">{{ $status_detail['label'] }}</span>
                                            @if ($editing_space?->id === $space->id)
                                                <span class="badge bg-blue-lt mb-3">Sedang diubah</span>
                                            @endif
                                            <div class="row g-2 mb-3">
                                                <div class="col-6">
                                                    <div class="bg-body-tertiary rounded p-2 h-100">
                                                        <div class="text-secondary small">Tonase Maksimum (GT)</div>
                                                        <div class="h3 mb-0 text-break">{{ $space->max_tonnage ?? '-' }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="bg-body-tertiary rounded p-2 h-100">
                                                        <div class="text-secondary small">Kapasitas Kapal</div>
                                                        <div class="h3 mb-0">{{ $space->max_capacity ?? '-' }} <span class="text-secondary small fw-normal">kapal</span></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <dl class="row g-2 small mb-0">
                                                @foreach (['max_draft' => 'Sarat maksimum', 'max_breadth' => 'Lebar kapal maksimum', 'max_length' => 'Panjang maksimum', 'max_width' => 'Lebar maksimum', 'max_weight' => 'Berat maksimum'] as $field_name => $field_label)
                                                    <dt class="col-7 text-secondary fw-normal">{{ $field_label }}</dt>
                                                    <dd class="col-5 text-end text-break mb-0">
                                                        {{ $space->{$field_name} ?? '-' }}{{ $space->{$field_name} !== null && $field_name !== 'max_weight' ? ' m' : '' }}
                                                    </dd>
                                                @endforeach
                                            </dl>
                                            @if (!empty($space->notes))
                                                <div class="border-top mt-3 pt-3 small text-break">{{ $space->notes }}</div>
                                            @endif
                                        </div>
                                        <div class="card-footer bg-transparent d-flex align-items-center justify-content-between gap-2">
                                            <a href="{{ route('docking-space.edit', $space->unique_id) }}" class="btn btn-sm btn-outline-primary" aria-label="Ubah {{ $space->name }}">
                                                <i class="ti ti-pencil me-1" aria-hidden="true"></i>Ubah
                                            </a>
                                            <form method="POST" action="{{ route('docking-space.destroy', $space->unique_id) }}" onsubmit="return confirm('Hapus dock ini? Data yang dihapus tidak dapat dikembalikan.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-ghost-danger" aria-label="Hapus {{ $space->name }}"><i class="ti ti-trash me-1" aria-hidden="true"></i>Hapus</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div id="dock-no-results" class="empty py-4" hidden>
                            <p class="empty-title">Dock tidak ditemukan</p>
                            <p class="empty-subtitle text-secondary">Coba nama lain atau ubah filter status.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <form id="docking-space-form" method="POST" action="{{ $editing_space ? route('docking-space.update', $editing_space->unique_id) : route('docking-space.store') }}" class="card shadow-none" aria-labelledby="dock-form-title">
                @csrf
                @if ($editing_space)
                    @method('PUT')
                @endif
                <div class="card-header">
                    <div>
                        <h3 class="card-title mb-1" id="dock-form-title">{{ $editing_space ? 'Ubah Data Dock' : 'Tambah Dock' }}</h3>
                        <div class="text-secondary small">{{ $editing_space ? $editing_space->name : 'Lengkapi identitas dan kapasitas dock.' }}</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="text-secondary small mb-3">Kolom bertanda <span class="text-danger">*</span> wajib diisi.</div>
                    <fieldset class="mb-4">
                        <legend class="h4 mb-3">Identitas Dock</legend>
                        <div class="mb-3">
                            <label for="dock-name" class="form-label required">Nama Dock</label>
                            <input id="dock-name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $editing_space?->name) }}" placeholder="Contoh: Floating Dock" maxlength="255" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="dock-location" class="form-label">Lokasi</label>
                            <input id="dock-location" type="text" name="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location', $editing_space?->location) }}" placeholder="Contoh: Area Timur" maxlength="255">
                            @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="dock-form-status" class="form-label required">Status Operasional</label>
                            <select id="dock-form-status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                                @foreach ($statuses as $status_key => $status_detail)
                                    <option value="{{ $status_key }}" @selected(old('status', $editing_space?->status ?? 'active') === $status_key)>{{ $status_detail['label'] }}</option>
                                @endforeach
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </fieldset>

                    <fieldset class="mb-4">
                        <legend class="h4 mb-1">Batas Kapasitas</legend>
                        <p class="text-secondary small mb-3">Gunakan batas operasional yang telah ditetapkan untuk dock ini.</p>
                        <div class="row g-3">
                            @foreach ($capacity_fields as $field_name => $field_detail)
                                <div class="col-sm-6">
                                    <label for="dock-{{ $field_name }}" class="form-label required">{{ $field_detail['label'] }}</label>
                                    <input id="dock-{{ $field_name }}" type="number" name="{{ $field_name }}" class="form-control @error($field_name) is-invalid @enderror" value="{{ old($field_name, $editing_space?->{$field_name}) }}" step="{{ $field_name === 'max_capacity' ? '1' : '0.01' }}" min="{{ $field_name === 'max_capacity' ? '1' : '0.01' }}" max="{{ $field_detail['max'] }}" required>
                                    @error($field_name)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            @endforeach
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend class="h4 mb-1">Spesifikasi Tambahan</legend>
                        <p class="text-secondary small mb-3">Opsional. Kosongkan jika belum ditetapkan.</p>
                        <div class="row g-3">
                            @foreach ($optional_fields as $field_name => $field_detail)
                                <div class="col-sm-6">
                                    <label for="dock-{{ $field_name }}" class="form-label">{{ $field_detail['label'] }}</label>
                                    <input id="dock-{{ $field_name }}" type="number" name="{{ $field_name }}" class="form-control @error($field_name) is-invalid @enderror" value="{{ old($field_name, $editing_space?->{$field_name}) }}" step="0.01" min="0.01" max="{{ $field_detail['max'] }}">
                                    @error($field_name)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            <label for="dock-notes" class="form-label">Catatan</label>
                            <textarea id="dock-notes" name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" placeholder="Informasi khusus mengenai dock ini">{{ old('notes', $editing_space?->notes) }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </fieldset>
                </div>
                <div class="card-footer d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary flex-fill"><i class="ti ti-device-floppy me-1" aria-hidden="true"></i>{{ $editing_space ? 'Simpan Perubahan' : 'Simpan Dock' }}</button>
                    @if ($editing_space)
                        <a href="{{ route('docking-space.index') }}" class="btn">Batal</a>
                    @endif
                </div>
            </form>
            <div class="card bg-blue-lt mt-3 shadow-none">
                <div class="card-body small">
                    <h4 class="mb-2"><i class="ti ti-info-circle me-1" aria-hidden="true"></i>Panduan Pengisian</h4>
                    <p class="mb-2">Kapasitas kapal adalah jumlah maksimum kapal yang dapat ditampung, bukan jumlah kapal yang sedang berada di dock.</p>
                    <p class="mb-0">Status aktif menunjukkan dock beroperasi, bukan jaminan ketersediaan jadwal. Gunakan status pemeliharaan atau nonaktif sesuai kondisi dock.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ secure_asset('assets/dist/js/jquery-validation/localization/messages_id.min.js') }}"></script>
    <script>
        $(function () {
            const dock_cards = $('[data-dock-card]');
            const dock_search = $('#dock-search');
            const dock_status = $('#dock-status');

            function filter_docks() {
                const query = dock_search.val().trim().toLocaleLowerCase('id-ID');
                const status = dock_status.val();
                let visible_count = 0;

                dock_cards.each(function () {
                    const card = $(this);
                    const search_text = (card.attr('data-name') + ' ' + card.attr('data-location')).toLocaleLowerCase('id-ID');
                    const matches = search_text.includes(query) && (!status || card.attr('data-status') === status);
                    card.prop('hidden', !matches);
                    visible_count += matches ? 1 : 0;
                });

                $('#dock-result-count').text(visible_count + ' dari ' + dock_cards.length + ' dock ditampilkan');
                $('#dock-no-results').prop('hidden', visible_count !== 0);
            }

            $('#dock-filters').prop('hidden', false);
            dock_search.on('input', filter_docks);
            dock_status.on('change', filter_docks);
            $('#dock-filter-reset').on('click', function () {
                dock_search.val('');
                dock_status.val('');
                filter_docks();
            });

            $('#docking-space-form').validate({
                rules: {
                    status: { required: true },
                    max_capacity: { required: true, digits: true }
                },
                messages: {
                    name: { required: 'Nama dock wajib diisi.' },
                    max_draft: { required: 'Sarat maksimum wajib diisi.' },
                    max_breadth: { required: 'Lebar kapal maksimum wajib diisi.' },
                    max_tonnage: { required: 'Tonase maksimum wajib diisi.' },
                    max_capacity: {
                        required: 'Kapasitas kapal wajib diisi.',
                        digits: 'Kapasitas kapal harus berupa bilangan bulat.'
                    }
                },
                errorElement: 'div',
                errorClass: 'invalid-feedback',
                highlight: function (element) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function (element) {
                    $(element).removeClass('is-invalid');
                }
            });
        });
    </script>
@endpush
