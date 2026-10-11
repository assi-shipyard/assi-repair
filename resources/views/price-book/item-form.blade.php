@extends('layouts.app')

@section('title', $item->exists ? 'Ubah Item Harga' : 'Tambah Item Harga')
@section('body_title', $item->exists ? 'Ubah Item Harga' : 'Tambah Item Harga')

@section('content')
    @include('partials.flash')
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="alert alert-info">Buku: <strong>{{ $price_book->name }}</strong>. Minimum ditagihkan dalam satuan yang sama dengan harga satuan, bukan berupa minimum nominal Rupiah.</div>
            <form id="price-item-form" action="{{ $item->exists ? route('price-book.items.update', [$price_book, $item]) : route('price-book.items.store', $price_book) }}" method="POST" class="card" novalidate>
                @csrf
                @if ($item->exists) @method('PUT') @endif
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="group" class="form-label required">Kelompok</label>
                            <select id="group" name="group" class="form-select" required>
                                @foreach (\App\Models\PriceBookItem::GROUPS as $value => $label)
                                    <option value="{{ $value }}" @selected(old('group', $item->group) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label for="code" class="form-label required">Kode Item</label>
                            <input id="code" name="code" class="form-control" maxlength="30" value="{{ old('code', $item->code) }}" required>
                        </div>
                        <div class="col-12">
                            <label for="name" class="form-label required">Nama Material / Jasa</label>
                            <input id="name" name="name" class="form-control" maxlength="255" value="{{ old('name', $item->name) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="unit" class="form-label required">Satuan</label>
                            <input id="unit" name="unit" class="form-control" maxlength="30" placeholder="kg, lembar, jam, ..." value="{{ old('unit', $item->unit) }}" required>
                        </div>
                        <div class="col-md-8">
                            <label for="unit_price" class="form-label required">Harga Satuan (IDR)</label>
                            <input type="number" id="unit_price" name="unit_price" class="form-control" min="0" max="999999999999.99" step="0.01" value="{{ old('unit_price', $item->unit_price) }}" required>
                            <div class="form-hint">Gunakan titik untuk desimal, tanpa pemisah ribuan. Contoh: 15000.50.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-check mt-md-4">
                                <input type="hidden" name="has_minimum_charge" value="0">
                                <input type="checkbox" id="has_minimum_charge" name="has_minimum_charge" class="form-check-input" value="1" @checked(old('has_minimum_charge', $item->has_minimum_charge))>
                                <span class="form-check-label">Gunakan batas minimum</span>
                            </label>
                        </div>
                        <div class="col-md-8" id="minimum-field">
                            <label for="minimum_quantity" class="form-label required">Kuantitas Minimum</label>
                            <input type="number" id="minimum_quantity" name="minimum_quantity" class="form-control" min="0.001" max="999999999.999" step="0.001" value="{{ old('minimum_quantity', $item->minimum_quantity) }}">
                            <div class="form-hint">Contoh: 15 untuk 15 kg pelat baja, atau 8 untuk 8 jam jasa crane. Maksimal 3 angka desimal.</div>
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label">Keterangan</label>
                            <textarea id="description" name="description" class="form-control" maxlength="2000" rows="3">{{ old('description', $item->description) }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="{{ route('price-book.show', $price_book) }}" class="btn">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Item</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    @include('price-book.partials.validation')
    <script>
        $(function () {
            function toggle_minimum() {
                const has_minimum = $('#has_minimum_charge').is(':checked');
                $('#minimum-field').toggle(has_minimum);
                $('#minimum_quantity').prop('disabled', !has_minimum);
            }
            $('#has_minimum_charge').on('change', toggle_minimum);
            toggle_minimum();
            $('#price-item-form').validate({
                ...price_form_options,
                rules: {
                    group: { required: true },
                    code: { required: true, maxlength: 30, price_code: true },
                    name: { required: true, maxlength: 255 },
                    unit: { required: true, maxlength: 30 },
                    unit_price: { required: true, number: true, min: 0, max: 999999999999.99, price_decimal: 2 },
                    minimum_quantity: { required: function () { return $('#has_minimum_charge').is(':checked'); }, number: true, min: 0.001, max: 999999999.999, price_decimal: 3 },
                    description: { maxlength: 2000 }
                }
            });
        });
    </script>
@endpush
