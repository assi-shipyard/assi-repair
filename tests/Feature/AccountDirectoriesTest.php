<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

// PHPUnit requires this class name to match its *Test.php discovery convention.
class AccountDirectoriesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::create(['account_type' => 'admin', 'name' => 'Admin Direktori', 'username' => 'admin-directory', 'password' => 'SecretPassword123']);
        $admin->assignRole(Role::findOrCreate('admin', 'web'));

        return $admin;
    }

    private function customer(): User
    {
        return User::create(['account_type' => 'customer', 'name' => 'Pelanggan Direktori', 'email' => 'customer@example.test', 'password' => 'SecretPassword123']);
    }

    public function test_admin_and_customer_directories_are_separate_card_pages(): void
    {
        $admin = $this->admin();
        $customer = $this->customer();
        User::create(['account_type' => 'internal', 'name' => 'Internal Tidak Ditampilkan', 'employee_id' => '199600001', 'password' => 'SecretPassword123']);
        $this->actingAs($admin)->get(route('accounts.index'))->assertOk()->assertSee('Admin Direktori')
            ->assertDontSee('Pelanggan Direktori')->assertDontSee('Internal Tidak Ditampilkan')->assertDontSee('<table', false);
        $this->get(route('accounts.customers'))->assertOk()->assertSee('Pelanggan Direktori')
            ->assertDontSee('<h3 class="card-title mb-1 text-break">Admin Direktori</h3>', false)->assertDontSee('Internal Tidak Ditampilkan')
            ->assertSee('0 proyek aktif')->assertSee('Foto profil belum tersedia')->assertDontSee('<table', false);
        $customer->update(['is_active' => false]);
        $this->get(route('accounts.customers', ['status' => 'active']))->assertDontSee('Pelanggan Direktori');
        $this->get(route('accounts.customers', ['status' => 'inactive', 'search' => 'customer@example.test']))->assertSee('Pelanggan Direktori');
        $this->get(route('accounts.customers', ['status' => 'invalid']))->assertSessionHasErrors('status');
    }

    public function test_customer_cards_are_paginated_and_searchable_by_company(): void
    {
        $admin = $this->admin();
        $company = Company::create(['unique_id' => (string) Str::uuid(), 'name' => 'Perusahaan Surveyor', 'address' => 'Alamat']);
        for ($index = 1; $index <= 13; $index++) {
            User::create(['account_type' => 'customer', 'name' => sprintf('Pelanggan %02d', $index), 'company_id' => $company->id, 'password' => 'SecretPassword123']);
        }
        $this->actingAs($admin)->get(route('accounts.customers'))->assertSee('Pelanggan 01')->assertDontSee('Pelanggan 13')
            ->assertSee('Menampilkan 1 hingga 12 dari 13 data')->assertSee('Navigasi halaman')
            ->assertDontSee('Showing')->assertDontSee('sm:hidden', false);
        $this->get(route('accounts.customers', ['search' => 'Surveyor', 'page' => 2]))->assertSee('Pelanggan 13')->assertDontSee('Pelanggan 01')->assertSee('search=Surveyor', false);
    }

    public function test_private_account_photos_can_be_uploaded_replaced_and_removed(): void
    {
        Storage::fake('ship');
        $admin = $this->admin();
        $customer = $this->customer();
        $company = Company::create(['unique_id' => (string) Str::uuid(), 'name' => 'Surveyor', 'address' => 'Alamat']);
        $payload = ['name' => $customer->name, 'email' => $customer->email, 'company_id' => $company->unique_id, 'password' => null];
        $this->actingAs($admin)->put(route('accounts.update', $customer), [
            ...$payload, 'profile_photo' => UploadedFile::fake()->image('photo.png'),
        ])->assertSessionHasNoErrors()->assertRedirect(route('accounts.customers'));
        $path = $customer->fresh()->profile_photo_path;
        Storage::disk('ship')->assertExists($path);
        $this->get(route('accounts.customers'))->assertSee(route('accounts.photo', $customer), false)->assertDontSee($path);
        $this->get(route('accounts.photo', $customer))->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->get('/accounts/'.$customer->id.'/photo')->assertNotFound();
        $this->put(route('accounts.update', $customer), [
            ...$payload, 'profile_photo' => UploadedFile::fake()->image('replacement.jpg'),
        ])->assertSessionHasNoErrors();
        $replacement_path = $customer->fresh()->profile_photo_path;
        $this->assertNotSame($path, $replacement_path);
        Storage::disk('ship')->assertMissing($path);
        $this->put(route('accounts.update', $customer), [...$payload, 'remove_photo' => true])->assertSessionHasNoErrors();
        $this->assertNull($customer->fresh()->profile_photo_path);
        Storage::disk('ship')->assertMissing($replacement_path);
        $this->get(route('accounts.photo', $customer))->assertNotFound();
    }

    public function test_invalid_photo_and_unprivileged_directory_access_are_rejected(): void
    {
        Storage::fake('ship');
        $admin = $this->admin();
        $customer = $this->customer();
        $company = Company::create(['unique_id' => (string) Str::uuid(), 'name' => 'Surveyor', 'address' => 'Alamat']);
        $payload = ['name' => $customer->name, 'email' => $customer->email, 'company_id' => $company->unique_id];
        $this->actingAs($admin)->put(route('accounts.update', $customer), [
            ...$payload, 'profile_photo' => UploadedFile::fake()->create('document.pdf', 100),
        ])->assertSessionHasErrors('profile_photo');
        $this->put(route('accounts.update', $customer), [
            ...$payload, 'profile_photo' => UploadedFile::fake()->image('large.png')->size(2049),
        ])->assertSessionHasErrors('profile_photo');
        $this->assertNull($customer->fresh()->profile_photo_path);
        $internal = User::create(['employee_id' => '199600001', 'password' => 'SecretPassword123']);
        foreach ([$customer, $internal] as $user) {
            $this->actingAs($user)->get(route('accounts.customers'))->assertForbidden();
            $this->get(route('accounts.index'))->assertForbidden();
            $this->get(route('accounts.photo', $customer))->assertForbidden();
        }
        $this->actingAs($customer)->get(route('employee.index'))->assertForbidden();
    }

    public function test_customer_creation_supports_private_photo_and_returns_to_customer_directory(): void
    {
        Storage::fake('ship');
        $admin = $this->admin();
        $company = Company::create(['unique_id' => (string) Str::uuid(), 'name' => 'Perusahaan Uji', 'address' => 'Alamat']);
        $this->actingAs($admin)->post(route('accounts.store'), [
            'account_type' => 'customer', 'name' => 'Pelanggan Baru', 'email' => 'new@example.test',
            'company_id' => $company->unique_id, 'password' => 'SecretPassword123', 'password_confirmation' => 'SecretPassword123',
            'profile_photo' => UploadedFile::fake()->image('customer.jpg'),
        ])->assertSessionHasNoErrors()->assertRedirect(route('accounts.customers'));
        $customer = User::where('email', 'new@example.test')->firstOrFail();
        Storage::disk('ship')->assertExists($customer->profile_photo_path);
        $this->get(route('accounts.customers'))->assertSee('Pelanggan Baru')->assertSee(route('accounts.photo', $customer), false);
        $this->assertArrayNotHasKey('profile_photo_path', $customer->toArray());
    }
}
