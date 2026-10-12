@extends('layouts.app')

@section('title', 'Kelompok Pekerjaan')
@section('body_title', 'Kelompok Pekerjaan')

@php
    $form_action = \Illuminate\Support\Str::startsWith((string) old('form_action'), url('/job-group'))
        ? old('form_action')
        : route('job-group.store');
    $active_count = $job_groups->where('is_active', true)->count();
@endphp

@section('buttons_beside_title')
    <button type="button" class="btn btn-primary" id="btn-add-job-group">
        <i class="ti ti-plus me-1"></i>Tambah Kelompok
    </button>
@endsection

@section('content')
    @include('partials.flash')

    <div class="row row-cards mb-3">
        <div class="col-6 col-md-3">
            <div class="card card-sm"><div class="card-body">
                <div class="subheader">Total Kelompok</div>
                <div class="h2 mb-0">{{ $job_groups->count() }}</div>
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
                <div class="h2 mb-0 text-secondary">{{ $job_groups->count() - $active_count }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-sm"><div class="card-body">
                <div class="subheader">Total Pekerjaan</div>
                <div class="h2 mb-0">{{ number_format($job_groups->sum('jobs_count'), 0, ',', '.') }}</div>
            </div></div>
        </div>
    </div>

    <div class="alert alert-info">
        <div class="d-flex">
            <i class="ti ti-info-circle fs-2 me-2"></i>
            <div>Kelompok pekerjaan dipakai bersama oleh semua proyek dan semua tahap dokumen (Repair List, RAB, Satisfaction Notes, Docking Report). Kelompok yang sudah dipakai tidak dapat dihapus; nonaktifkan agar tidak muncul pada pilihan baru.</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Kelompok Pekerjaan</h3>
            <div class="card-actions">
                <input type="search" id="filter-search" class="form-control form-control-sm" placeholder="Cari kelompok...">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-hover" id="job-group-table">
                <thead>
                    <tr>
                        <th class="w-1">Urutan</th>
                        <th>Nama Kelompok</th>
                        <th class="text-end">Dipakai</th>
                        <th>Status</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($job_groups as $job_group)
                        <tr data-row>
                            <td class="text-secondary">{{ $job_group->sort_order }}</td>
                            <td class="fw-bold">{{ $job_group->name }}</td>
                            <td class="text-end">{{ number_format($job_group->jobs_count, 0, ',', '.') }} pekerjaan</td>
                            <td><span class="badge {{ $job_group->is_active ? 'bg-success-lt' : 'bg-secondary-lt' }}">{{ $job_group->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td class="text-nowrap">
                                <button type="button" class="btn btn-sm btn-outline-primary btn-edit-job-group"
                                    data-action="{{ route('job-group.update', $job_group) }}"
                                    data-name="{{ $job_group->name }}"
                                    data-sort="{{ $job_group->sort_order }}"
                                    data-active="{{ $job_group->is_active ? 1 : 0 }}">Ubah</button>
                                @if ($job_group->jobs_count === 0)
                                    <form action="{{ route('job-group.destroy', $job_group) }}" method="POST" class="d-inline form-delete-job-group">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-secondary py-4">Belum ada kelompok pekerjaan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('modal')
    <div class="modal modal-blur fade" id="modal-job-group" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" id="form-job-group" method="POST" action="{{ $form_action }}" novalidate>
                @csrf
                <input type="hidden" name="_method" id="form-method" value="{{ old('_method') === 'PUT' ? 'PUT' : 'POST' }}">
                <input type="hidden" name="form_action" id="form-action" value="{{ $form_action }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-job-group-title">Tambah Kelompok</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label required" for="name">Nama Kelompok</label>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" maxlength="100" value="{{ old('name') }}">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required" for="sort_order">Urutan</label>
                            <input type="number" id="sort_order" name="sort_order" min="0" max="9999" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', ($job_groups->max('sort_order') ?? 0) + 1) }}">
                            @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
@endsection

@push('scripts')
    <script>
        (function ($) {
            const modal = new bootstrap.Modal(document.getElementById('modal-job-group'));
            const form = $('#form-job-group');
            const store_url = @js(route('job-group.store'));
            const next_sort = @js(($job_groups->max('sort_order') ?? 0) + 1);

            $('#filter-search').on('input', function () {
                const keyword = $(this).val().toLowerCase();
                $('#job-group-table tbody tr[data-row]').each(function () {
                    $(this).toggle($(this).text().toLowerCase().includes(keyword));
                });
            });

            function open_form(action, method, title, values) {
                form.attr('action', action);
                $('#form-action').val(action);
                $('#form-method').val(method);
                $('#modal-job-group-title').text(title);
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback').remove();
                $('#name').val(values.name);
                $('#sort_order').val(values.sort);
                $('#is_active').prop('checked', values.active === 1);
                modal.show();
            }

            $('#btn-add-job-group').on('click', function () {
                open_form(store_url, 'POST', 'Tambah Kelompok', { name: '', sort: next_sort, active: 1 });
            });

            $('.btn-edit-job-group').on('click', function () {
                const data = $(this).data();
                open_form(data.action, 'PUT', 'Ubah Kelompok', { name: data.name, sort: data.sort, active: Number(data.active) });
            });

            $('.form-delete-job-group').on('submit', function (event) {
                event.preventDefault();
                const delete_form = this;
                Swal.fire({
                    title: 'Hapus kelompok?',
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
                rules: {
                    name: { required: true, maxlength: 100 },
                    sort_order: { required: true, digits: true, max: 9999 },
                },
                messages: {
                    name: { required: 'Nama kelompok wajib diisi.', maxlength: 'Nama kelompok maksimal 100 karakter.' },
                    sort_order: { required: 'Urutan wajib diisi.', digits: 'Urutan harus berupa bilangan bulat.', max: 'Urutan maksimal 9999.' },
                },
                errorElement: 'div',
                errorClass: 'invalid-feedback',
                highlight: function (element) { $(element).addClass('is-invalid'); },
                unhighlight: function (element) { $(element).removeClass('is-invalid'); },
            });

            @if ($errors->any() && old('form_action'))
                $('#modal-job-group-title').text(@js(old('_method') === 'PUT' ? 'Ubah Kelompok' : 'Tambah Kelompok'));
                modal.show();
            @endif
        })(jQuery);
    </script>
@endpush
