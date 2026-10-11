<div class="card mb-3">
    <div class="card-header"><h3 class="card-title">{{ $heading }} <span class="badge bg-secondary-lt ms-2">{{ $items->total() }}</span></h3></div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead><tr><th>Kode / Nama</th><th>Satuan</th><th class="text-end">Harga Satuan (IDR)</th><th>Minimum Ditagihkan</th>@role('admin')<th></th>@endrole</tr></thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td><div class="text-secondary small">{{ $item->code }}</div><div class="fw-bold">{{ $item->name }}</div>@if ($item->description)<div class="text-secondary small">{{ $item->description }}</div>@endif</td>
                        <td>{{ $item->unit }}</td>
                        <td class="text-end text-nowrap">{{ number_format((float) $item->unit_price, 2, ',', '.') }}</td>
                        <td class="text-nowrap">
                            @if ($item->has_minimum_charge)
                                <span class="badge bg-orange-lt">{{ number_format((float) $item->minimum_quantity, 3, ',', '.') }} {{ $item->unit }}</span>
                            @else
                                <span class="text-secondary">Tanpa minimum</span>
                            @endif
                        </td>
                        @role('admin')
                            <td class="text-nowrap">
                                <a href="{{ route('price-book.items.edit', [$price_book, $item]) }}" class="btn btn-sm btn-outline-primary">Ubah</a>
                                <form action="{{ route('price-book.items.destroy', [$price_book, $item]) }}" method="POST" class="d-inline price-delete-form">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        @endrole
                    </tr>
                @empty
                    <tr><td colspan="{{ auth()->user()?->hasRole('admin') ? 5 : 4 }}" class="text-center text-secondary py-4">Belum ada {{ strtolower($heading) }} dalam buku ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $items->withQueryString()->links() }}</div>
</div>
