<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\PriceBook;
use App\Models\PriceBookItem;
use App\Models\User;
use App\Services\PriceBookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PriceBookTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::create(['employee_id' => '100000001', 'email' => 'price-admin@example.test', 'password' => 'secret-pass']);
        $user->assignRole(Role::findOrCreate('admin'));

        return $user;
    }

    private function book(string $code = 'MAIN'): PriceBook
    {
        return PriceBook::create(['code' => $code, 'name' => 'Buku '.$code, 'reference_slot' => $code === 'MAIN' ? 1 : null]);
    }

    private function item_payload(string $group = 'material'): array
    {
        return [
            'group' => $group, 'code' => 'STEEL-01', 'name' => 'Pelat Baja', 'unit' => 'kg',
            'unit_price' => '15000.50', 'has_minimum_charge' => '1', 'minimum_quantity' => '15',
        ];
    }

    public function test_books_and_items_can_be_managed_and_audited(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('price-book.store'), [
            'code' => 'MAIN', 'name' => 'Buku Acuan', 'book_type' => 'reference', 'reference_slot' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect();
        $book = PriceBook::firstOrFail();
        $this->assertTrue(Str::isUuid($book->getRouteKey()));

        $this->get(route('price-book.index'))->assertOk()->assertSee('Kamus Harga')->assertSee('Buku Acuan');
        $this->get(route('price-book.edit', $book))->assertOk();
        $this->get(route('price-book.items.create', $book))->assertOk();
        $this->put(route('price-book.update', $book), [
            'code' => 'MAIN', 'name' => 'Acuan Galangan', 'book_type' => 'reference', 'reference_slot' => 1,
        ])->assertSessionHasNoErrors();

        $this->post(route('price-book.items.store', $book), $this->item_payload())->assertSessionHasNoErrors();
        $item = PriceBookItem::firstOrFail();
        $this->assertSame('15000.50', $item->unit_price);
        $this->assertSame('15.000', $item->minimum_quantity);
        $this->assertTrue($item->has_minimum_charge);
        $this->get(route('price-book.show', $book))->assertOk()->assertSee('Pelat Baja')->assertSee('15,000 kg');
        $this->get(route('price-book.items.edit', [$book, $item]))->assertOk();

        $this->put(route('price-book.items.update', [$book, $item]), [
            ...$this->item_payload(), 'has_minimum_charge' => '0', 'minimum_quantity' => 'not-a-number',
        ])->assertSessionHasNoErrors();
        $this->assertNull($item->fresh()->minimum_quantity);
        $this->assertFalse($item->fresh()->has_minimum_charge);

        $this->delete(route('price-book.destroy', $book))->assertSessionHasErrors('book');
        $this->delete(route('price-book.items.destroy', [$book, $item]))->assertRedirect();
        $this->delete(route('price-book.destroy', $book))->assertRedirect(route('price-book.index'));
        $this->assertDatabaseCount('price_books', 0);
        $this->assertDatabaseCount('price_book_items', 0);

        $audit = AuditLog::query()->where('event_name', 'price_book_item.updated')->firstOrFail();
        $this->assertSame($admin->id, $audit->user_id);
        $this->assertSame($item->unique_id, $audit->event_payload['resource_uuid']);
        $this->assertEquals(15, $audit->event_payload['before']['minimum_quantity']);
        $this->assertNull($audit->event_payload['after']['minimum_quantity']);
        $this->assertSame(6, AuditLog::query()->where('event_name', 'like', 'price_book%')->count());
    }

    public function test_only_one_main_book_and_valid_customer_books_are_allowed(): void
    {
        $this->actingAs($this->admin());
        $main = $this->book();
        $this->post(route('price-book.store'), [
            'code' => 'MAIN-2', 'name' => 'Utama Kedua', 'book_type' => 'reference', 'reference_slot' => 1,
        ])->assertSessionHasErrors('reference_slot');
        $this->post(route('price-book.store'), [
            'code' => 'CUST', 'name' => 'Pelanggan', 'book_type' => 'customer',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('price_books', ['code' => 'CUST', 'company_id' => null, 'reference_slot' => null]);
        $this->post(route('price-book.store'), [
            'code' => 'CUST', 'name' => 'Pelanggan', 'book_type' => 'customer', 'company_uuid' => (string) Str::uuid(),
        ])->assertSessionHasErrors('company_uuid');

        $company = Company::create(['unique_id' => (string) Str::uuid(), 'name' => 'Perusahaan Uji', 'address' => 'Alamat Uji']);
        foreach (['CUST-1', 'CUST-2'] as $code) {
            $this->post(route('price-book.store'), [
                'code' => $code, 'name' => 'Buku '.$code, 'book_type' => 'customer', 'company_uuid' => $company->unique_id,
                'reference_slot' => 1,
            ])->assertSessionHasNoErrors();
        }
        $this->assertSame(2, PriceBook::query()->where('company_id', $company->id)->whereNull('reference_slot')->count());
        $this->get(route('price-book.show', $main))->assertDontSee('Perusahaan Uji');
        $customer_book = PriceBook::query()->where('code', 'CUST-1')->firstOrFail();
        $this->put(route('price-book.update', $customer_book), [
            'code' => 'CUST-1', 'name' => 'Buku Tanpa Pelanggan', 'book_type' => 'customer', 'company_uuid' => '',
        ])->assertSessionHasNoErrors();
        $this->assertNull($customer_book->fresh()->company_id);
        $this->get(route('price-book.show', $customer_book))->assertOk()->assertSee('Tidak terkait pelanggan tertentu');
    }

    public function test_books_groups_and_units_have_independent_prices(): void
    {
        $this->actingAs($this->admin());
        $main = $this->book();
        $other = $this->book('CUSTOMER');
        $payload = $this->item_payload();
        $this->post(route('price-book.items.store', $main), $payload)->assertSessionHasNoErrors();
        $this->post(route('price-book.items.store', $main), $payload)->assertSessionHasErrors('code');
        $this->post(route('price-book.items.store', $main), [...$payload, 'unit' => 'lembar', 'minimum_quantity' => 1])->assertSessionHasNoErrors();
        $this->post(route('price-book.items.store', $other), [...$payload, 'unit_price' => '9000'])->assertSessionHasNoErrors();
        $this->post(route('price-book.items.store', $main), [
            ...$payload, 'group' => 'service', 'name' => 'Crane', 'unit' => 'jam', 'minimum_quantity' => '8',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('price_book_items', 4);
        $this->assertSame('9000.00', $other->items()->firstOrFail()->unit_price);
        $this->get(route('price-book.show', $main))->assertSee('8,000 jam')->assertSee('Crane');
        $this->get(route('price-book.show', $other))->assertDontSee('Crane');
    }

    public function test_item_validation_rejects_invalid_precision_and_minimums(): void
    {
        $this->actingAs($this->admin());
        $book = $this->book();
        foreach (['0', '-1', '0.0001', '1000000000', '', 'text'] as $minimum) {
            $this->post(route('price-book.items.store', $book), [
                ...$this->item_payload(), 'minimum_quantity' => $minimum,
            ])->assertSessionHasErrors('minimum_quantity');
        }
        foreach (['-1', '1.001', '1000000000000', 'text'] as $price) {
            $this->post(route('price-book.items.store', $book), [
                ...$this->item_payload(), 'unit_price' => $price,
            ])->assertSessionHasErrors('unit_price');
        }
        $this->post(route('price-book.items.store', $book), [
            ...$this->item_payload(), 'group' => 'invalid', 'unit' => '', 'has_minimum_charge' => 'invalid',
        ])->assertSessionHasErrors(['group', 'unit', 'has_minimum_charge']);
        $this->post(route('price-book.items.store', $book), [
            ...$this->item_payload('service'), 'unit_price' => '999999999999.99', 'minimum_quantity' => '999999999.999',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('price_book_items', 1);
    }

    public function test_uuid_and_nested_ownership_are_enforced(): void
    {
        $this->actingAs($this->admin());
        $main = $this->book();
        $other = $this->book('OTHER');
        $this->post(route('price-book.items.store', $main), $this->item_payload());
        $item = PriceBookItem::firstOrFail();
        $this->get('/price-book/'.$main->id)->assertNotFound();
        $this->get('/price-book/'.$main->unique_id.'/items/'.$item->id.'/edit')->assertNotFound();
        $this->get(route('price-book.items.edit', [$other, $item]))->assertNotFound();
        $this->put(route('price-book.items.update', [$other, $item]), $this->item_payload())->assertNotFound();
        $this->delete(route('price-book.items.destroy', [$other, $item]))->assertNotFound();
        $this->assertDatabaseCount('price_book_items', 1);
    }

    public function test_readers_cannot_mutate_books_or_items(): void
    {
        $admin = $this->admin();
        $book = $this->book();
        $this->actingAs($admin)->post(route('price-book.items.store', $book), $this->item_payload());
        $item = PriceBookItem::firstOrFail();
        $reader = User::create(['employee_id' => '100000002', 'email' => 'price-reader@example.test', 'password' => 'secret-pass']);
        $this->actingAs($reader);
        $this->get(route('price-book.index'))->assertOk()->assertDontSee('Tambah Buku Harga');
        $this->get(route('price-book.show', $book))->assertOk()->assertDontSee('Simpan Item')->assertDontSee('Tambah Material / Jasa');
        $this->get(route('price-book.create'))->assertForbidden();
        $this->get(route('price-book.edit', $book))->assertForbidden();
        $this->post(route('price-book.store'), [])->assertForbidden();
        $this->put(route('price-book.update', $book), [])->assertForbidden();
        $this->delete(route('price-book.destroy', $book))->assertForbidden();
        $this->get(route('price-book.items.create', $book))->assertForbidden();
        $this->get(route('price-book.items.edit', [$book, $item]))->assertForbidden();
        $this->post(route('price-book.items.store', $book), [])->assertForbidden();
        $this->put(route('price-book.items.update', [$book, $item]), [])->assertForbidden();
        $this->delete(route('price-book.items.destroy', [$book, $item]))->assertForbidden();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('price-book.index'))->assertRedirect(route('login'));
        $this->post(route('price-book.store'), [])->assertRedirect(route('login'));
    }

    public function test_each_group_is_paginated_independently(): void
    {
        $this->actingAs($this->admin());
        $book = $this->book();
        for ($index = 1; $index <= 21; $index++) {
            $book->items()->create([
                'group' => 'material', 'code' => sprintf('M-%02d', $index), 'name' => 'Material '.$index,
                'unit' => 'kg', 'unit_price' => '1',
            ]);
        }
        $book->items()->create(['group' => 'service', 'code' => 'S-01', 'name' => 'Jasa Crane', 'unit' => 'jam', 'unit_price' => '1']);
        $this->get(route('price-book.show', $book).'?materials_page=2')->assertOk()
            ->assertSee('Material 21')->assertDontSee('Material 1</div>', false)->assertSee('Jasa Crane');
    }

    public function test_mutation_rolls_back_if_its_audit_record_cannot_be_saved(): void
    {
        $admin = $this->admin();
        AuditLog::creating(function (AuditLog $audit_log): void {
            if ($audit_log->event_name === 'price_book.created') {
                throw new RuntimeException('Audit unavailable');
            }
        });

        try {
            $this->app->make(PriceBookService::class)->save_book([
                'code' => 'MAIN', 'name' => 'Buku Acuan', 'book_type' => 'reference',
            ], $admin);
            $this->fail('The mutation must fail when its audit record cannot be saved.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Audit unavailable', $exception->getMessage());
        }

        $this->assertDatabaseCount('price_books', 0);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_dashboard_search_and_filters_are_combined_and_counts_are_correct(): void
    {
        $this->actingAs($this->admin());
        $main = $this->book();
        $company = Company::create(['unique_id' => (string) Str::uuid(), 'name' => 'Samudra Uji', 'address' => 'Alamat Uji']);
        $customer = PriceBook::create(['code' => 'DEAL-26', 'name' => 'Kesepakatan Tahunan', 'company_id' => $company->id]);
        $customer->items()->create([...$this->item_payload(), 'has_minimum_charge' => true]);
        $customer->items()->create([...$this->item_payload('service'), 'unit' => 'jam']);

        foreach (['DEAL-26', 'Tahunan', 'Samudra', 'samudra'] as $search) {
            $this->get(route('price-book.index', ['search' => $search]))->assertOk()
                ->assertViewHas('price_books', fn ($books): bool => $books->total() === 1 && $books->first()->id === $customer->id);
        }
        $this->get(route('price-book.index', ['book_type' => 'reference']))->assertOk()
            ->assertViewHas('price_books', fn ($books): bool => $books->total() === 1 && $books->first()->id === $main->id);
        $this->get(route('price-book.index', [
            'search' => 'Tahunan', 'book_type' => 'customer', 'company_uuid' => $company->unique_id,
        ]))->assertOk()->assertSee('Reset Filter')
            ->assertViewHas('price_books', fn ($books): bool => $books->total() === 1
                && $books->first()->materials_count === 1 && $books->first()->services_count === 1);
        $this->get(route('price-book.index', [
            'book_type' => 'reference', 'company_uuid' => $company->unique_id,
        ]))->assertOk()->assertSee('Tidak ada buku yang sesuai')
            ->assertViewHas('price_books', fn ($books): bool => $books->total() === 0);
        $this->get(route('price-book.index', ['search' => 'Nothing Matches']))->assertOk()
            ->assertSee('Tidak ada buku yang sesuai');
    }

    public function test_dashboard_pagination_retains_filters_and_searches_all_books(): void
    {
        $this->actingAs($this->admin());
        $this->book();
        for ($index = 1; $index <= 13; $index++) {
            PriceBook::create(['code' => sprintf('C-%02d', $index), 'name' => sprintf('Kontrak %02d', $index)]);
        }
        $response = $this->get(route('price-book.index', ['search' => 'Kontrak', 'book_type' => 'customer']));
        $response->assertOk()->assertViewHas('price_books', fn ($books): bool => $books->total() === 13 && $books->count() === 12);
        $next_page = $response->viewData('price_books')->nextPageUrl();
        $this->assertStringContainsString('search=Kontrak', $next_page);
        $this->assertStringContainsString('book_type=customer', $next_page);
        $this->get($next_page)->assertOk()->assertSee('Kontrak 13')
            ->assertViewHas('filters', fn ($filters): bool => $filters['search'] === 'Kontrak' && $filters['book_type'] === 'customer');
        $this->get(route('price-book.index', ['search' => 'C-13']))->assertOk()
            ->assertViewHas('price_books', fn ($books): bool => $books->total() === 1);
    }

    public function test_dashboard_validates_filters_and_displays_empty_catalog(): void
    {
        $this->actingAs($this->admin());
        $this->get(route('price-book.index'))->assertOk()->assertSee('Belum ada buku harga');
        foreach ([
            ['search' => ['invalid']],
            ['search' => str_repeat('a', 101)],
            ['book_type' => 'invalid'],
            ['company_uuid' => '1'],
            ['company_uuid' => (string) Str::uuid()],
            ['page' => '0'],
            ['page' => 'invalid'],
        ] as $filters) {
            $this->from(route('price-book.index'))->get(route('price-book.index', $filters))
                ->assertRedirect(route('price-book.index'))->assertSessionHasErrors(array_key_first($filters));
        }
    }
}
