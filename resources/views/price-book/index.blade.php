@extends('layouts.app')

@section('title', 'Kamus Harga')
@section('body_title', 'Kamus Harga')

@section('buttons_beside_title')
    @role('admin')
        <a href="{{ route('price-book.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Tambah Buku Harga</a>
    @endrole
@endsection

@section('content')
    @include('partials.flash')
    <div class="alert alert-info">
        <div><strong>Buku utama</strong> menjadi acuan harga galangan. Setiap proyek memilih buku yang digunakan tanpa dibatasi pelanggan. Kaitan pelanggan pada buku hanya informasi opsional. Setiap buku memiliki daftar material dan jasa yang berdiri sendiri.</div>
    </div>

    <form id="price-book-filter" action="{{ route('price-book.index') }}" method="GET" class="card mb-3" novalidate>
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-lg-5">
                    <label for="search" class="form-label">Cari Buku Harga</label>
                    <div class="input-icon">
                        <span class="input-icon-addon"><i class="ti ti-search"></i></span>
                        <input type="search" id="search" name="search" class="form-control" maxlength="100" placeholder="Kode, nama buku, atau pelanggan..." value="{{ $filters['search'] }}">
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <label for="book_type" class="form-label">Jenis Buku</label>
                    <select id="book_type" name="book_type" class="form-select">
                        <option value="">Semua jenis</option>
                        <option value="reference" @selected($filters['book_type'] === 'reference')>Buku Utama</option>
                        <option value="customer" @selected($filters['book_type'] === 'customer')>Buku Pelanggan</option>
                    </select>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <label for="company_uuid" class="form-label">Pelanggan</label>
                    <select id="company_uuid" name="company_uuid" class="form-select">
                        <option value="">Semua pelanggan</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->unique_id }}" @selected($filters['company_uuid'] === $company->unique_id)>{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary"><i class="ti ti-filter me-1"></i>Terapkan Filter</button>
                    @if ($has_filters)
                        <a href="{{ route('price-book.index') }}" class="btn btn-outline-secondary">Reset Filter</a>
                    @endif
                </div>
            </div>
        </div>
    </form>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0">Daftar Buku Harga</h3>
        <span class="text-secondary small" role="status">
            @if ($price_books->count())
                Menampilkan {{ $price_books->firstItem() }}–{{ $price_books->lastItem() }} dari {{ $price_books->total() }} buku
            @else
                {{ $price_books->total() }} buku ditemukan
            @endif
        </span>
    </div>
    <div class="row row-cards">
        @forelse ($price_books as $price_book)
            <div class="col-12 col-md-6 col-xl-4">
                <article class="card h-100">
                    <div class="card-status-top {{ $price_book->reference_slot ? 'bg-blue' : 'bg-green' }}"></div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <span class="avatar avatar-sm {{ $price_book->reference_slot ? 'bg-blue-lt' : 'bg-green-lt' }}"><i class="ti ti-book-2" aria-hidden="true"></i></span>
                            <span class="badge {{ $price_book->reference_slot ? 'bg-blue-lt' : 'bg-green-lt' }}">{{ $price_book->reference_slot ? 'Buku Utama' : 'Buku Pelanggan' }}</span>
                        </div>
                        <div class="subheader text-break">{{ $price_book->code }}</div>
                        <h3 class="card-title text-break mt-1 mb-2">
                            <a href="{{ route('price-book.show', $price_book) }}" class="text-reset">{{ $price_book->name }}</a>
                        </h3>
                        <div class="text-secondary text-break mb-3"><i class="ti ti-building me-1" aria-hidden="true"></i>{{ $price_book->company?->name ?? ($price_book->reference_slot ? 'Acuan galangan' : 'Tidak terkait pelanggan tertentu') }}</div>
                        @if ($price_book->description)
                            <p class="text-secondary small text-break">{{ \Illuminate\Support\Str::limit($price_book->description, 160) }}</p>
                        @endif
                        <div class="row g-2">
                            <div class="col-6"><div class="border rounded p-2"><div class="subheader">Material</div><div class="h2 mb-0">{{ $price_book->materials_count }}</div></div></div>
                            <div class="col-6"><div class="border rounded p-2"><div class="subheader">Jasa</div><div class="h2 mb-0">{{ $price_book->services_count }}</div></div></div>
                        </div>
                    </div>
                    <div class="card-footer mt-auto">
                        <div class="text-secondary small mb-2">Diperbarui {{ $price_book->updated_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d F Y H:i') }} WIB</div>
                        <a href="{{ route('price-book.show', $price_book) }}" class="btn btn-outline-primary w-100">Buka Buku<span class="visually-hidden"> {{ $price_book->name }}</span><i class="ti ti-arrow-right ms-2" aria-hidden="true"></i></a>
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="empty">
                        <div class="empty-icon"><i class="ti ti-books fs-1" aria-hidden="true"></i></div>
                        <p class="empty-title">{{ $has_filters ? 'Tidak ada buku yang sesuai' : 'Belum ada buku harga' }}</p>
                        <p class="empty-subtitle text-secondary">{{ $has_filters ? 'Coba kata kunci atau pilihan filter lain.' : 'Tambahkan buku utama sebagai acuan harga galangan terlebih dahulu.' }}</p>
                        @if ($has_filters)
                            <div class="empty-action"><a href="{{ route('price-book.index') }}" class="btn btn-outline-primary">Reset Filter</a></div>
                        @endif
                    </div>
                </div>
            </div>
        @endforelse
    </div>
    <div class="mt-3">{{ $price_books->links() }}</div>
@endsection

@push('scripts')
    @include('price-book.partials.validation')
    <script>
        $(function () {
            $('#price-book-filter').validate({
                ...price_form_options,
                rules: { search: { maxlength: 100 } }
            });
        });
    </script>
@endpush
