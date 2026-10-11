@extends('layouts.app')

@section('title', 'Data Jenis Reparasi')
@section('body_title', 'Data Jenis Reparasi')

@php
    $form_action = \Illuminate\Support\Str::startsWith((string) old('form_action'), url('/price-dictionary'))
        ? old('form_action')
        : route('price-dictionary.store');
    $is_admin = auth()->user()?->hasRole('admin');
    $active_count = $repair_types->where('is_active', true)->count();
@endphp

@section('buttons_beside_title')
    @if ($is_admin)
        <button type="button" class="btn btn-primary" id="btn-add-repair-type">
            <i class="ti ti-plus me-1"></i>Tambah Jenis Reparasi
        </button>
    @endif
@endsection

@section('content')
    @include('partials.flash')

    <div class="row row-cards mb-3">
        <div class="col-6 col-md-3">
            <div class="card card-sm"><div class="card-body">
                <div class="subheader">Total Jenis</div>
                <div class="h2 mb-0">{{ $repair_types->count() }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-sm"><div class="card-body">
                <div class="subheader">Aktif</div>
                <div class="h2 mb-0 text-success">{{ $active_count }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-sm"><div class="card-body">
                <div class="subheader">Nonaktif</div>
                <div class="h2 mb-0 text-secondary">{{ $repair_types->count() - $active_count }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-sm"><div class="card-body">
                <div class="subheader">Kategori</div>
                <div class="h2 mb-0">{{ $repair_types->pluck('category')->unique()->count() }}</div>
            </div></div>
        </div>
    </div>

    <div class="alert alert-info">
        <div class="d-flex">
            <i class="ti ti-info-circle fs-2 me-2"></i>
            <div>Harga standar bersifat acuan estimasi dalam Rupiah (IDR) dan dapat disesuaikan pada setiap dokumen pekerjaan proyek.</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Jenis Reparasi</h3>
            <div class="card-actions d-flex gap-2">
                <select id="filter-category" class="form-select form-select-sm">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $category_label)
                        <option value="{{ $category_label }}">{{ $category_label }}</option>
                    @endforeach
                </select>
                <input type="search" id="filter-search" class="form-control form-control-sm" placeholder="Cari kode / nama...">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-hover" id="repair-type-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Jenis Reparasi</th>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        <th class="text-end">Harga Standar (IDR)</th>
                        <th>Status</th>
                        @if ($is_admin)<th class="w-1">Aksi</th>@endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($repair_types as $repair_type)
                        <tr data-category="{{ $categories[$repair_type->category] ?? $repair_type->category }}">
                            <td><span class="badge bg-blue-lt">{{ $repair_type->code }}</span></td>
                            <td>
                                <div class="fw-bold">{{ $repair_type->name }}</div>
                                @if ($repair_type->description)
                                    <div class="text-secondary small text-truncate" style="max-width: 360px">{{ $repair_type->description }}</div>
                                @endif
                            </td>
                            <td>{{ $categories[$repair_type->category] ?? $repair_type->category }}</td>
                            <td>{{ $repair_type->unit }}</td>
                            <td class="text-end">{{ $repair_type->standard_price !== null ? number_format((float) $repair_type->standard_price, 2, ',', '.') : '-' }}</td>
                            <td>
                                <span class="badge {{ $repair_type->is_active ? 'bg-success-lt' : 'bg-secondary-lt' }}">{{ $repair_type->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </td>
                            @if ($is_admin)
                                <td class="text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-repair-type"
                                        data-action="{{ route('price-dictionary.update', $repair_type) }}"
                                        data-code="{{ $repair_type->code }}"
                                        data-name="{{ $repair_type->name }}"
                                        data-category="{{ $repair_type->category }}"
                                        data-unit="{{ $repair_type->unit }}"
                                        data-price="{{ $repair_type->standard_price }}"
                                        data-description="{{ $repair_type->description }}"
                                        data-active="{{ $repair_type->is_active ? 1 : 0 }}">Ubah</button>
                                    <form action="{{ route('price-dictionary.destroy', $repair_type) }}" method="POST" class="d-inline form-delete-repair-type">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="{{ $is_admin ? 7 : 6 }}" class="text-center text-secondary py-4">Belum ada data jenis reparasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('modal')
    @if ($is_admin)
        <div class="modal modal-blur fade" id="modal-repair-type" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" id="form-repair-type" method="POST" action="{{ $form_action }}" novalidate>
                    @csrf
                    <input type="hidden" name="_method" id="form-method" value="{{ old('_method') === 'PUT' ? 'PUT' : 'POST' }}">
                    <input type="hidden" name="form_action" id="form-action" value="{{ $form_action }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-repair-type-title">Tambah Jenis Reparasi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label required" for="code">Kode</label>
                                <input type="text" id="code" name="code" class="form-control text-uppercase @error('code') is-invalid @enderror" maxlength="30" value="{{ old('code') }}">
                                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-8">
                                <label class="form-label required" for="name">Nama Jenis Reparasi</label>
                                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" maxlength="255" value="{{ old('name') }}">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required" for="category">Kategori</label>
                                <select id="category" name="category" class="form-select @error('category') is-invalid @enderror">
                                    <option value="">Pilih kategori</option>
                                    @foreach ($categories as $category_key => $category_label)
                                        <option value="{{ $category_key }}" @selected(old('category') === $category_key)>{{ $category_label }}</option>
                                    @endforeach
                                </select>
                                @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required" for="unit">Satuan</label>
                                <input type="text" id="unit" name="unit" class="form-control @error('unit') is-invalid @enderror" maxlength="30" placeholder="m², kg, jam, unit" value="{{ old('unit') }}">
                                @error('unit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="standard_price">Harga Standar (IDR)</label>
                                <input type="number" step="0.01" min="0" id="standard_price" name="standard_price" class="form-control @error('standard_price') is-invalid @enderror" value="{{ old('standard_price') }}">
                                @error('standard_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="description">Deskripsi</label>
                                <textarea id="description" name="description" rows="3" class="form-control @error('description') is-invalid @enderror" maxlength="2000">{{ old('description') }}</textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" @checked(old('is_active', $errors->any() ? false : true))>
                                    <span class="form-check-label">Aktif</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        (function ($) {
            const rows = $('#repair-type-table tbody tr[data-category]');

            function apply_filter() {
                const keyword = $('#filter-search').val().toLowerCase();
                const category = $('#filter-category').val();

                rows.each(function () {
                    const row = $(this);
                    const matches_text = row.text().toLowerCase().includes(keyword);
                    const matches_category = !category || row.data('category') === category;
                    row.toggle(matches_text && matches_category);
                });
            }

            $('#filter-search').on('input', apply_filter);
            $('#filter-category').on('change', apply_filter);

            @if ($is_admin)
            const modal_element = document.getElementById('modal-repair-type');
            const modal = new bootstrap.Modal(modal_element);
            const form = $('#form-repair-type');
            const store_url = @js(route('price-dictionary.store'));

            function open_form(action, method, title, values) {
                form.attr('action', action);
                $('#form-action').val(action);
                $('#form-method').val(method);
                $('#modal-repair-type-title').text(title);
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback').remove();
                $('#code').val(values.code);
                $('#name').val(values.name);
                $('#category').val(values.category);
                $('#unit').val(values.unit);
                $('#standard_price').val(values.price);
                $('#description').val(values.description);
                $('#is_active').prop('checked', values.active === 1);
                modal.show();
            }

            $('#btn-add-repair-type').on('click', function () {
                open_form(store_url, 'POST', 'Tambah Jenis Reparasi', { code: '', name: '', category: '', unit: '', price: '', description: '', active: 1 });
            });

            $('.btn-edit-repair-type').on('click', function () {
                const data = $(this).data();
                open_form(data.action, 'PUT', 'Ubah Jenis Reparasi', {
                    code: data.code, name: data.name, category: data.category, unit: data.unit,
                    price: data.price, description: data.description, active: Number(data.active),
                });
            });

            $('.form-delete-repair-type').on('submit', function (event) {
                event.preventDefault();
                const delete_form = this;

                Swal.fire({
                    title: 'Hapus jenis reparasi?',
                    text: 'Data yang dihapus tidak dapat dikembalikan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                }).then(function (result) {
                    if (result.isConfirmed) { delete_form.submit(); }
                });
            });

            form.validate({
                ignore: [],
                rules: {
                    code: { required: true, maxlength: 30, pattern: /^[A-Za-z0-9._-]+$/ },
                    name: { required: true, maxlength: 255 },
                    category: { required: true },
                    unit: { required: true, maxlength: 30 },
                    standard_price: { number: true, min: 0 },
                    description: { maxlength: 2000 },
                },
                messages: {
                    code: { required: 'Kode wajib diisi.', pattern: 'Kode hanya boleh huruf, angka, titik, garis bawah, dan strip.' },
                    name: { required: 'Nama jenis reparasi wajib diisi.' },
                    category: { required: 'Kategori wajib dipilih.' },
                    unit: { required: 'Satuan wajib diisi.' },
                    standard_price: { number: 'Harga standar harus berupa angka.', min: 'Harga standar tidak boleh negatif.' },
                },
                errorElement: 'div',
                errorClass: 'invalid-feedback',
                highlight: function (element) { $(element).addClass('is-invalid'); },
                unhighlight: function (element) { $(element).removeClass('is-invalid'); },
            });

            @if ($errors->any() && old('form_action'))
                $('#modal-repair-type-title').text(@js(old('_method') === 'PUT' ? 'Ubah Jenis Reparasi' : 'Tambah Jenis Reparasi'));
                modal.show();
            @endif
            @endif
        })(jQuery);
    </script>
@endpush
