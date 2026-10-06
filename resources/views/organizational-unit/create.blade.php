@extends('layouts.app')

@section('title', 'Tambah Unit Organisasi')
@section('body_title', 'Tambah Unit Organisasi')

@section('buttons_beside_title')
    <a href="{{ route('organizational-unit.index') }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i>Kembali</a>
@endsection

@section('content')
    @include('partials.flash')

    <div class="card unit-form-summary mb-4"><div class="card-body p-4 d-flex align-items-center gap-3"><span class="avatar avatar-lg bg-primary-lt text-primary"><i class="ti ti-building-community fs-2"></i></span><div><div class="text-secondary small">Struktur Organisasi</div><h2 class="mb-1">Tambah Unit Organisasi</h2><div class="text-secondary">Bangun struktur C-Suite, direktorat, divisi, biro, subdivisi, dan bengkel.</div></div></div></div>
    <form id="organizational-unit-form" action="{{ route('organizational-unit.store') }}" method="POST" class="card">
        @csrf
        <div class="card-header"><div><h3 class="card-title mb-1">Identitas dan Hierarki Unit</h3><div class="text-secondary small">Divisi dan biro sejajar di bawah direktorat; subdivisi berada di bawah divisi, sedangkan bengkel mandiri di bawah CPO.</div></div></div>
        <div class="card-body">
            <div class="row g-3">
                        <div class="col-md-6"><label class="form-label required" for="name">Nama Unit</label><input id="name" class="form-control" name="name" value="{{ old('name') }}" maxlength="255" placeholder="Contoh: Divisi Reparasi" required></div>
                        <div class="col-md-6"><label class="form-label" for="code">Kode Unit</label><input id="code" class="form-control" name="code" value="{{ old('code') }}" maxlength="50" placeholder="Contoh: DRR"></div>
                        <div class="col-md-6">
                            <label class="form-label required" for="type">Jenis Unit</label>
                            <select id="type" class="form-select" name="type" required>
                                <option value="">Pilih jenis</option>
                                @foreach ($types as $type => $label)
                                    <option value="{{ $type }}" @selected(old('type') == $type)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="parent_id">Unit Induk</label>
                            <select id="parent_id" class="form-select" name="parent_id">
                                <option value="">Tanpa induk</option>
                                @foreach ($organizational_units as $organizational_unit_option)
                                    <option value="{{ $organizational_unit_option->id }}" data-type="{{ $organizational_unit_option->type }}" @selected(old('parent_id') == $organizational_unit_option->id)>{{ $organizational_unit_option->name }} ({{ $organizational_unit_option->type_label }})</option>
                                @endforeach
                            </select>
                        </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between"><span class="text-secondary small">CEO tidak memiliki induk; jenis unit menentukan induk yang dapat dipilih.</span><button class="btn btn-primary" type="submit"><i class="ti ti-device-floppy me-1"></i>Simpan Unit</button></div>
    </form>
@endsection

@push('styles')<style>.unit-form-summary { border-top: 3px solid var(--tblr-primary); }</style>@endpush
@push('scripts')<script>
    const allowed_parent_types = @json(\App\Models\OrganizationalUnit::ALLOWED_PARENT_TYPES);
    const unit_type = jQuery('#type');
    const parent_unit = jQuery('#parent_id');
    function update_parent_options() {
        const allowed_types = allowed_parent_types[unit_type.val()] || [];
        parent_unit.find('option').each(function () {
            const option_type = jQuery(this).data('type');
            const allowed = !option_type || allowed_types.includes(option_type);
            jQuery(this).prop('disabled', !allowed).prop('hidden', !allowed);
            if (!allowed && jQuery(this).is(':selected')) parent_unit.val('');
        });
    }
    unit_type.on('change', update_parent_options);
    update_parent_options();
    if (window.jQuery && jQuery.fn.validate) { jQuery('#organizational-unit-form').validate({ rules: { name: { required: true, maxlength: 255 }, code: { maxlength: 50 }, type: { required: true }, parent_id: { required: function () { return (allowed_parent_types[unit_type.val()] || []).length > 0; } } }, errorElement: 'div', errorClass: 'invalid-feedback', highlight: function (element) { jQuery(element).addClass('is-invalid'); }, unhighlight: function (element) { jQuery(element).removeClass('is-invalid'); } }); }
</script>@endpush
