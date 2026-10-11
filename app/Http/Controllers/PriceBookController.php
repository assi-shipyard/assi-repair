<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\PriceBook\ListPriceBooksRequest;
use App\Http\Requests\PriceBook\SavePriceBookItemRequest;
use App\Http\Requests\PriceBook\SavePriceBookRequest;
use App\Models\Company;
use App\Models\PriceBook;
use App\Models\PriceBookItem;
use App\Services\PriceBookService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PriceBookController extends Controller
{
    public function __construct(private readonly PriceBookService $price_book_service) {}

    public function index(ListPriceBooksRequest $request): View
    {
        return view('price-book.index', $this->price_book_service->list_books($request->validated()));
    }

    public function create(): View
    {
        return $this->book_form(new PriceBook);
    }

    public function edit(PriceBook $price_book): View
    {
        return $this->book_form($price_book);
    }

    public function store(SavePriceBookRequest $request): RedirectResponse
    {
        $price_book = $this->price_book_service->save_book($request->validated(), $request->user());

        return redirect()->route('price-book.show', $price_book)->with('success', 'Buku harga berhasil ditambahkan.');
    }

    public function update(SavePriceBookRequest $request, PriceBook $price_book): RedirectResponse
    {
        $this->price_book_service->save_book($request->validated(), $request->user(), $price_book);

        return redirect()->route('price-book.show', $price_book)->with('success', 'Buku harga berhasil diperbarui.');
    }

    public function destroy(Request $request, PriceBook $price_book): RedirectResponse
    {
        $this->price_book_service->delete_book($price_book, $request->user());

        return redirect()->route('price-book.index')->with('success', 'Buku harga berhasil dihapus.');
    }

    public function show(PriceBook $price_book): View
    {
        $price_book->load('company');

        return view('price-book.show', [
            'price_book' => $price_book,
            'materials' => $price_book->items()->where('group', 'material')->orderBy('code')->orderBy('unit')->paginate(20, ['*'], 'materials_page'),
            'services' => $price_book->items()->where('group', 'service')->orderBy('code')->orderBy('unit')->paginate(20, ['*'], 'services_page'),
        ]);
    }

    public function create_item(PriceBook $price_book): View
    {
        return view('price-book.item-form', ['price_book' => $price_book, 'item' => new PriceBookItem]);
    }

    public function edit_item(PriceBook $price_book, PriceBookItem $item): View
    {
        return view('price-book.item-form', compact('price_book', 'item'));
    }

    public function store_item(SavePriceBookItemRequest $request, PriceBook $price_book): RedirectResponse
    {
        $this->price_book_service->save_item($request->validated(), $request->user(), $price_book);

        return redirect()->route('price-book.show', $price_book)->with('success', 'Item harga berhasil ditambahkan.');
    }

    public function update_item(SavePriceBookItemRequest $request, PriceBook $price_book, PriceBookItem $item): RedirectResponse
    {
        $this->price_book_service->save_item($request->validated(), $request->user(), $price_book, $item);

        return redirect()->route('price-book.show', $price_book)->with('success', 'Item harga berhasil diperbarui.');
    }

    public function destroy_item(Request $request, PriceBook $price_book, PriceBookItem $item): RedirectResponse
    {
        $this->price_book_service->delete_item($item, $request->user());

        return redirect()->route('price-book.show', $price_book)->with('success', 'Item harga berhasil dihapus.');
    }

    private function book_form(PriceBook $price_book): View
    {
        $price_book->load('company');

        return view('price-book.form', [
            'price_book' => $price_book,
            'companies' => Company::query()->orderBy('name')->get(['id', 'unique_id', 'name']),
            'has_reference' => PriceBook::query()->where('reference_slot', 1)->exists(),
        ]);
    }
}
