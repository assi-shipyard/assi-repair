@extends('layouts.app')

@section('title', 'Kamus Harga - '.$price_book->name)
@section('body_title', $price_book->name)

@section('buttons_beside_title')
    <a href="{{ route('price-book.index') }}" class="btn">Daftar Buku</a>
    @role('admin')
        <a href="{{ route('price-book.edit', $price_book) }}" class="btn btn-outline-primary">Ubah Buku</a>
        <a href="{{ route('price-book.items.create', $price_book) }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Tambah Material / Jasa</a>
    @endrole
@endsection

@section('content')
    @include('partials.flash')
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><div class="subheader">Kode / Jenis Buku</div><div>{{ $price_book->code }} <span class="badge bg-blue-lt ms-2">{{ $price_book->reference_slot ? 'Buku Utama' : 'Buku Pelanggan' }}</span></div></div>
                <div class="col-md-4"><div class="subheader">Pelanggan (Informasi Opsional)</div><div>{{ $price_book->company?->name ?? ($price_book->reference_slot ? 'Acuan galangan' : 'Tidak terkait pelanggan tertentu') }}</div></div>
                <div class="col-md-4"><div class="subheader">Jumlah Item</div><div>{{ $materials->total() }} material / {{ $services->total() }} jasa</div></div>
                @if ($price_book->description)<div class="col-12 text-secondary">{{ $price_book->description }}</div>@endif
            </div>
        </div>
    </div>
    <div class="alert alert-info">
        <div><strong>Batas minimum menggunakan satuan item.</strong> Contoh: pelat baja minimum 15 kg atau jasa crane minimum 8 jam. Kuantitas yang ditagihkan paling sedikit sebesar batas minimum. Setiap kombinasi kode dan satuan dapat memiliki harga berbeda; isi buku ini tidak otomatis mengikuti buku utama.</div>
    </div>
    @include('price-book.partials.items', ['items' => $materials, 'heading' => 'Material'])
    @include('price-book.partials.items', ['items' => $services, 'heading' => 'Jasa'])
    @role('admin')
        @if ($materials->total() + $services->total() === 0)
            <form action="{{ route('price-book.destroy', $price_book) }}" method="POST" class="price-delete-form">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">Hapus Buku Kosong</button>
            </form>
        @endif
    @endrole
@endsection

@push('scripts')
    <script>
        $(function () {
            $('.price-delete-form').on('submit', function (event) {
                if (!confirm('Hapus data ini? Tindakan ini tidak dapat dibatalkan.')) event.preventDefault();
            });
        });
    </script>
@endpush
