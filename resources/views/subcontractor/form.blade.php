@extends('layouts.app')

@section('title', $subcontractor->exists ? 'Ubah Subkontraktor' : 'Tambah Subkontraktor')
@section('body_title', $subcontractor->exists ? 'Ubah Subkontraktor' : 'Tambah Subkontraktor')

@section('content')
    @include('partials.flash')

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <form id="subcontractor_form" action="{{ $subcontractor->exists ? route('subcontractor.update', $subcontractor) : route('subcontractor.store') }}" method="POST" class="card" novalidate>
                @csrf
                @if ($subcontractor->exists) @method('PUT') @endif
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="code" class="form-label required">Kode Subkontraktor</label>
                            <input id="code" name="code" class="form-control" maxlength="50" value="{{ old('code', $subcontractor->code) }}" required>
                        </div>
                        <div class="col-md-8">
                            <label for="name" class="form-label required">Nama Perusahaan</label>
                            <input id="name" name="name" class="form-control" maxlength="191" value="{{ old('name', $subcontractor->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="contact_name" class="form-label required">Nama Kontak</label>
                            <input id="contact_name" name="contact_name" class="form-control" maxlength="191" value="{{ old('contact_name', $subcontractor->contact_name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="contact_phone" class="form-label">Telepon</label>
                            <input id="contact_phone" name="contact_phone" type="tel" class="form-control" maxlength="30" value="{{ old('contact_phone', $subcontractor->contact_phone) }}">
                        </div>
                        <div class="col-md-8">
                            <label for="contact_email" class="form-label">Email</label>
                            <input id="contact_email" name="contact_email" type="email" class="form-control" maxlength="254" value="{{ old('contact_email', $subcontractor->contact_email) }}">
                        </div>
                        <div class="col-md-4">
                            <label for="is_active" class="form-label required">Status</label>
                            <select id="is_active" name="is_active" class="form-select" required>
                                <option value="1" @selected((string) old('is_active', (int) $subcontractor->is_active) === '1')>Aktif</option>
                                <option value="0" @selected((string) old('is_active', (int) $subcontractor->is_active) === '0')>Nonaktif</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="address" class="form-label">Alamat</label>
                            <textarea id="address" name="address" class="form-control" maxlength="2000" rows="3">{{ old('address', $subcontractor->address) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label for="notes" class="form-label">Catatan Internal</label>
                            <textarea id="notes" name="notes" class="form-control" maxlength="2000" rows="3">{{ old('notes', $subcontractor->notes) }}</textarea>
                        </div>
                    </div>
                    <div class="text-secondary small mt-3">PIC internal dan penugasan proyek tidak diatur pada halaman ini. Gunakan status Nonaktif untuk perusahaan yang tidak digunakan.</div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="{{ route('subcontractor.index') }}" class="btn">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Subkontraktor</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            $.validator.addMethod('subcontractor_code', function (value, element) {
                return this.optional(element) || /^[A-Za-z0-9._-]+$/.test(value);
            }, 'Kode hanya boleh berisi huruf, angka, titik, garis bawah, dan strip.');
            $.validator.addMethod('subcontractor_phone', function (value, element) {
                return this.optional(element) || (/^[0-9+().\s-]+$/.test(value) && /[0-9]/.test(value));
            }, 'Nomor telepon tidak valid.');
            $('#subcontractor_form').validate({
                errorElement: 'div',
                errorClass: 'invalid-feedback',
                highlight: function (element) { $(element).addClass('is-invalid'); },
                unhighlight: function (element) { $(element).removeClass('is-invalid'); },
                rules: {
                    code: { required: true, maxlength: 50, subcontractor_code: true },
                    name: { required: true, maxlength: 191 },
                    contact_name: { required: true, maxlength: 191 },
                    contact_phone: { maxlength: 30, subcontractor_phone: true },
                    contact_email: { email: true, maxlength: 254 },
                    address: { maxlength: 2000 },
                    notes: { maxlength: 2000 },
                    is_active: { required: true }
                },
                messages: {
                    code: { required: 'Kode subkontraktor wajib diisi.', maxlength: 'Maksimal 50 karakter.' },
                    name: { required: 'Nama perusahaan wajib diisi.', maxlength: 'Maksimal 191 karakter.' },
                    contact_name: { required: 'Nama kontak wajib diisi.', maxlength: 'Maksimal 191 karakter.' },
                    contact_phone: { maxlength: 'Maksimal 30 karakter.' },
                    contact_email: { email: 'Masukkan alamat email yang valid.', maxlength: 'Maksimal 254 karakter.' },
                    address: { maxlength: 'Maksimal 2000 karakter.' },
                    notes: { maxlength: 'Maksimal 2000 karakter.' },
                    is_active: { required: 'Status wajib diisi.' }
                }
            });
        });
    </script>
@endpush
