@extends('layouts.app')

@section('title', $price_book->exists ? 'Ubah Buku Harga' : 'Tambah Buku Harga')
@section('body_title', $price_book->exists ? 'Ubah Buku Harga' : 'Tambah Buku Harga')

@section('content')
    @include('partials.flash')
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <form id="price-book-form" action="{{ $price_book->exists ? route('price-book.update', $price_book) : route('price-book.store') }}" method="POST" class="card" novalidate>
                @csrf
                @if ($price_book->exists) @method('PUT') @endif
                <input type="hidden" name="reference_slot" value="1">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="code" class="form-label required">Kode Buku</label>
                            <input id="code" name="code" class="form-control" maxlength="30" value="{{ old('code', $price_book->code) }}" required>
                        </div>
                        <div class="col-md-8">
                            <label for="name" class="form-label required">Nama Buku</label>
                            <input id="name" name="name" class="form-control" maxlength="255" value="{{ old('name', $price_book->name) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="book_type" class="form-label required">Jenis Buku</label>
                            @php($book_type = old('book_type', $price_book->exists ? ($price_book->reference_slot ? 'reference' : 'customer') : ($has_reference ? 'customer' : 'reference')))
                            <select id="book_type" name="book_type" class="form-select" required>
                                <option value="reference" @selected($book_type === 'reference')>Buku Utama</option>
                                <option value="customer" @selected($book_type === 'customer')>Buku Pelanggan</option>
                            </select>
                        </div>
                        <div class="col-md-8" id="customer-field">
                            <label for="company_uuid" class="form-label">Pelanggan (Opsional)</label>
                            <select id="company_uuid" name="company_uuid" class="form-select">
                                <option value="">Tidak terkait pelanggan tertentu</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->unique_id }}" @selected(old('company_uuid', $price_book->company?->unique_id) === $company->unique_id)>{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label">Keterangan / Referensi Kesepakatan</label>
                            <textarea id="description" name="description" class="form-control" maxlength="2000" rows="3">{{ old('description', $price_book->description) }}</textarea>
                        </div>
                    </div>
                    <div class="text-secondary small mt-3">Hanya satu buku utama yang diperbolehkan. Pelanggan bersifat informasi opsional; setiap proyek dapat memilih buku mana pun. Harga disimpan dalam Rupiah (IDR).</div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <a class="btn" href="{{ $price_book->exists ? route('price-book.show', $price_book) : route('price-book.index') }}">Batal</a>
                    <button class="btn btn-primary" type="submit">Simpan Buku</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    @include('price-book.partials.validation')
    <script>
        $(function () {
            function toggle_customer() {
                const is_customer = $('#book_type').val() === 'customer';
                $('#customer-field').toggle(is_customer);
                $('#company_uuid').prop('disabled', !is_customer);
            }
            $('#book_type').on('change', toggle_customer);
            toggle_customer();
            $('#price-book-form').validate({
                ...price_form_options,
                rules: {
                    code: { required: true, maxlength: 30, price_code: true },
                    name: { required: true, maxlength: 255 },
                    book_type: { required: true },
                    description: { maxlength: 2000 }
                }
            });
        });
    </script>
@endpush
