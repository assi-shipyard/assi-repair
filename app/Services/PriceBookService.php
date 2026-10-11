<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\PriceBook;
use App\Models\PriceBookItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PriceBookService
{
    public function project_selection(array $data): array
    {
        // Omitted fields preserve selections for older clients; an explicit blank clears one.
        if (! array_key_exists('price_book_uuid', $data)) {
            return [];
        }

        return [
            'price_book_id' => $data['price_book_uuid'] !== null
                ? PriceBook::query()->where('unique_id', $data['price_book_uuid'])->firstOrFail()->id : null,
        ];
    }

    public function list_books(array $filters): array
    {
        $search = trim($filters['search'] ?? '');
        $book_type = $filters['book_type'] ?? '';
        $company_uuid = $filters['company_uuid'] ?? '';
        $query = PriceBook::query()->with('company')->withCount([
            'items as materials_count' => fn (Builder $query) => $query->where('group', 'material'),
            'items as services_count' => fn (Builder $query) => $query->where('group', 'service'),
        ]);

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search): void {
                $query->whereLike('code', '%'.$search.'%')
                    ->orWhereLike('name', '%'.$search.'%')
                    ->orWhereHas('company', fn (Builder $query) => $query->whereLike('name', '%'.$search.'%'));
            });
        }
        if ($book_type === 'reference') {
            $query->where('reference_slot', 1);
        } elseif ($book_type === 'customer') {
            $query->whereNull('reference_slot');
        }
        if ($company_uuid !== '') {
            $query->whereHas('company', fn (Builder $query) => $query->where('unique_id', $company_uuid));
        }

        return [
            'price_books' => $query->orderByDesc('reference_slot')->orderBy('name')->orderBy('id')
                ->paginate(12)->appends($filters),
            'companies' => Company::query()->whereIn('id', PriceBook::query()->select('company_id'))
                ->orderBy('name')->get(['id', 'unique_id', 'name']),
            'filters' => ['search' => $search, 'book_type' => $book_type, 'company_uuid' => $company_uuid],
            'has_filters' => $search !== '' || $book_type !== '' || $company_uuid !== '',
        ];
    }

    public function save_book(array $data, User $user, ?PriceBook $price_book = null): PriceBook
    {
        return DB::transaction(function () use ($data, $user, $price_book): PriceBook {
            $is_new = $price_book === null;
            $price_book ??= new PriceBook;
            $before = $price_book->getAttributes();
            $price_book->fill([
                'code' => $data['code'],
                'name' => $data['name'],
                'reference_slot' => $data['book_type'] === 'reference' ? 1 : null,
                'company_id' => $data['book_type'] === 'customer' && ! empty($data['company_uuid'])
                    ? Company::query()->where('unique_id', $data['company_uuid'])->firstOrFail()->id : null,
                'description' => $data['description'] ?? null,
            ]);
            $price_book->save();
            $this->audit($user, $is_new ? 'price_book.created' : 'price_book.updated', $price_book, $before);

            return $price_book;
        });
    }

    public function save_item(array $data, User $user, PriceBook $price_book, ?PriceBookItem $item = null): void
    {
        DB::transaction(function () use ($data, $user, $price_book, $item): void {
            $is_new = $item === null;
            $item ??= new PriceBookItem;
            $before = $item->getAttributes();
            $item->fill([
                ...$data,
                'has_minimum_charge' => (bool) $data['has_minimum_charge'],
                'minimum_quantity' => $data['has_minimum_charge'] ? $data['minimum_quantity'] : null,
            ]);
            $item->price_book()->associate($price_book);
            $item->save();
            $this->audit($user, $is_new ? 'price_book_item.created' : 'price_book_item.updated', $item, $before);
        });
    }

    public function delete_book(PriceBook $price_book, User $user): void
    {
        DB::transaction(function () use ($price_book, $user): void {
            $price_book = PriceBook::query()->lockForUpdate()->findOrFail($price_book->id);
            if ($price_book->items()->exists()) {
                throw ValidationException::withMessages(['book' => 'Buku yang masih berisi material atau jasa tidak dapat dihapus.']);
            }
            if ($price_book->projects()->exists()) {
                throw ValidationException::withMessages(['book' => 'Buku yang digunakan oleh proyek tidak dapat dihapus.']);
            }
            $before = $price_book->getAttributes();
            $price_book->delete();
            $this->audit($user, 'price_book.deleted', $price_book, $before);
        });
    }

    public function delete_item(PriceBookItem $item, User $user): void
    {
        DB::transaction(function () use ($item, $user): void {
            $before = $item->getAttributes();
            $item->delete();
            $this->audit($user, 'price_book_item.deleted', $item, $before);
        });
    }

    private function audit(User $user, string $event_name, Model $model, array $before): void
    {
        // Keep the mutation and its before/after audit record atomic.
        AuditLog::query()->create([
            'user_id' => $user->id,
            'employee_id' => $user->employee_id,
            'event_name' => $event_name,
            'event_payload' => [
                'resource_uuid' => $model->unique_id,
                'before' => $before,
                'after' => $model->exists ? $model->getAttributes() : null,
            ],
        ]);
    }
}
