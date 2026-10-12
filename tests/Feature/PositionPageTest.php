<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\OrganizationalUnit;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PositionPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_position_page_requires_authentication(): void
    {
        $this->get(route('position.index'))->assertRedirect(route('login'));
    }

    public function test_empty_position_page_renders_a_valid_datatable(): void
    {
        $user = User::create([
            'employee_id' => '100000001',
            'email' => 'position@example.test',
            'password' => 'secret-pass',
        ]);

        $this->actingAs($user)->get(route('position.index'))
            ->assertOk()
            ->assertSee('Data Jabatan')
            ->assertSee('0 Jabatan Terdaftar')
            ->assertSee('0 unit organisasi')
            ->assertSee('0 jabatan kepala')
            ->assertSee('Tidak ada data jabatan yang tersedia.')
            ->assertDontSee('colspan="5"', false);
    }

    public function test_position_page_displays_position_details_and_uuid_links(): void
    {
        $user = User::create([
            'employee_id' => '100000002',
            'email' => 'position-list@example.test',
            'password' => 'secret-pass',
        ]);
        $organizational_unit = OrganizationalUnit::create([
            'name' => 'Divisi Pengujian',
            'type' => 'division',
        ]);
        $position = Position::create([
            'name' => 'Manajer Pengujian',
            'category' => 'manager',
            'level' => 2,
            'is_head_position' => true,
            'code' => 'MGR-UJI',
            'organizational_unit_id' => $organizational_unit->id,
        ]);

        $this->actingAs($user)->get(route('position.index'))
            ->assertOk()
            ->assertSee('Manajer Pengujian')
            ->assertSee('1 Jabatan Terdaftar')
            ->assertSee('1 unit organisasi')
            ->assertSee('1 jabatan kepala')
            ->assertSee('aria-label="Lihat Manajer Pengujian"', false)
            ->assertSee('aria-label="Ubah Manajer Pengujian"', false)
            ->assertSee('Manajer Divisi')
            ->assertSee('Divisi Pengujian')
            ->assertSee('MGR-UJI')
            ->assertSee(route('position.show', $position->unique_id), false)
            ->assertSee(route('position.edit', $position->unique_id), false);
    }
}
