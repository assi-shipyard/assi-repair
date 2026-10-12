<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\OrganizationalUnit;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class EmployeePagesTest extends TestCase
{
    use RefreshDatabase;

    private function sign_in(): void
    {
        $this->actingAs(User::create([
            'employee_id' => '900000001',
            'email' => 'operator@example.test',
            'password' => 'secret-pass',
        ]));
    }

    private function position(): Position
    {
        $unit = OrganizationalUnit::create(['name' => 'Divisi Pengujian', 'type' => 'division']);

        return Position::create([
            'name' => 'Staf Pengujian',
            'category' => 'supervisor_staff',
            'level' => 4,
            'organizational_unit_id' => $unit->id,
        ]);
    }

    public function test_account_pages_require_authentication(): void
    {
        $this->get(route('employee.index'))->assertRedirect(route('login'));
        $this->get(route('employee.create'))->assertRedirect(route('login'));
    }

    public function test_empty_directory_uses_datatables_empty_state(): void
    {
        $this->sign_in();

        $this->get(route('employee.index'))->assertOk()
            ->assertSee('Daftar Pengguna')
            ->assertSee('0 Karyawan Terdaftar')
            ->assertSee('Belum ada karyawan terdaftar.')
            ->assertSee('employee-table')
            ->assertDontSee('colspan=', false)
            ->assertDontSee('box-shadow');
    }

    public function test_directory_displays_organization_manager_status_and_uuid_actions(): void
    {
        $this->sign_in();
        $position = $this->position();
        $manager = Employee::create(['name' => 'Manajer Uji', 'employee_id' => '100000001', 'position_id' => $position->id]);
        $employee = Employee::create([
            'name' => 'Karyawan Uji',
            'employee_id' => '100000002',
            'position_id' => $position->id,
            'direct_manager_employee_id' => $manager->id,
            'status' => 'inactive',
        ]);

        $this->get(route('employee.index'))->assertOk()
            ->assertSee('2 Karyawan Terdaftar')
            ->assertSee('1 aktif')
            ->assertSee('1 tidak aktif')
            ->assertSee('Karyawan Uji')
            ->assertSee('NIK 100000002')
            ->assertSee('Staf Pengujian')
            ->assertSee('Divisi Pengujian')
            ->assertSee('Manajer Uji')
            ->assertSee('Tidak Aktif')
            ->assertSee(route('employee.show', $employee->unique_id), false)
            ->assertSee(route('employee.edit', $employee->unique_id), false);
    }

    public function test_creation_form_displays_options_upload_and_password_guidance(): void
    {
        $this->sign_in();
        $this->position();

        $this->get(route('employee.create'))->assertOk()
            ->assertSee('Buat Akun Baru')
            ->assertSee('Identitas Karyawan')
            ->assertSee('Staf Pengujian')
            ->assertSee('Akses Akun')
            ->assertSee('Foto Profil')
            ->assertSee('maksimal 10 MB')
            ->assertSee('sistem menggunakan NIK sebagai kata sandi awal')
            ->assertSee('Simpan Akun')
            ->assertSee('multipart/form-data', false)
            ->assertDontSee('shadow-sm');
    }

    public function test_creation_form_preserves_input_and_shows_field_errors_without_passwords(): void
    {
        $this->sign_in();
        $errors = (new ViewErrorBag)->put('default', new MessageBag(['employee_id' => 'NIK sudah terdaftar.']));

        $this->withSession([
            'errors' => $errors,
            '_old_input' => ['name' => 'Nama Tersimpan', 'employee_id' => '001234567'],
        ])->get(route('employee.create'))->assertOk()
            ->assertSee('value="Nama Tersimpan"', false)
            ->assertSee('value="001234567"', false)
            ->assertSee('<div class="invalid-feedback">NIK sudah terdaftar.</div>', false);
    }

    public function test_creation_preserves_employee_id_and_optional_password_behavior(): void
    {
        $this->sign_in();
        $position = $this->position();

        $this->post(route('employee.store'), [
            'name' => 'Pengguna Baru',
            'employee_id' => '001234567',
            'email' => null,
            'position_id' => $position->id,
        ])->assertSessionHasNoErrors()->assertRedirect(route('employee.index'));

        $employee = Employee::where('employee_id', '001234567')->firstOrFail();
        $this->assertNotNull($employee->user);
        $this->assertTrue(Hash::check('001234567', $employee->user->password));

        $this->post(route('employee.store'), [
            'name' => 'Pengguna Duplikat',
            'employee_id' => '001234567',
            'position_id' => $position->id,
        ])->assertSessionHasErrors('employee_id');
    }

    private function employee(): Employee
    {
        $position = $this->position();
        $user = User::create(['employee_id' => '001234567', 'password' => 'existing-password']);

        return Employee::create([
            'name' => 'Pengguna Pengujian',
            'employee_id' => '001234567',
            'position_id' => $position->id,
            'user_id' => $user->id,
            'status' => 'inactive',
        ]);
    }

    public function test_edit_and_detail_pages_require_authentication(): void
    {
        $employee = $this->employee();

        $this->get(route('employee.edit', $employee->unique_id))->assertRedirect(route('login'));
        $this->get(route('employee.show', $employee->unique_id))->assertRedirect(route('login'));
    }

    public function test_edit_form_prefills_values_without_exposing_passwords(): void
    {
        $this->sign_in();
        $employee = $this->employee();
        $manager = Employee::create(['name' => 'Manajer Contoh', 'employee_id' => '100000001']);
        $employee->update(['direct_manager_employee_id' => $manager->id, 'profile_photo_path' => 'employees/example.jpg']);

        $this->get(route('employee.edit', $employee->unique_id))->assertOk()
            ->assertSee('Ubah Pengguna')
            ->assertSee('value="Pengguna Pengujian"', false)
            ->assertSee('value="001234567"', false)
            ->assertSee('value="inactive" selected', false)
            ->assertSee('value="'.$manager->id.'" selected', false)
            ->assertSee('value="'.$employee->position_id.'" selected', false)
            ->assertSee('name="_method" value="PUT"', false)
            ->assertSee('Kata Sandi Baru')
            ->assertSee('mempertahankan kata sandi akun yang sudah ada')
            ->assertSee('Hapus foto profil saat menyimpan')
            ->assertSee(route('employee.photo', $employee->unique_id), false)
            ->assertSee('Simpan Perubahan')
            ->assertDontSee('existing-password')
            ->assertDontSee($employee->user->password)
            ->assertDontSee('shadow-sm');
    }

    public function test_detail_shows_organization_manager_and_wib_record_dates(): void
    {
        $this->sign_in();
        $this->travelTo(now()->setTimezone('UTC')->setDate(2026, 10, 11)->setTime(23, 30));
        $employee = $this->employee();
        $manager = Employee::create(['name' => 'Manajer Contoh', 'employee_id' => '100000001']);
        $employee->update(['direct_manager_employee_id' => $manager->id]);

        $this->get(route('employee.show', $employee->unique_id))->assertOk()
            ->assertSee('Detail Pengguna')
            ->assertSee('Pengguna Pengujian')
            ->assertSee('NIK 001234567')
            ->assertSee('Tidak Aktif')
            ->assertSee('Belum diisi')
            ->assertSee('Staf Pengujian')
            ->assertSee('Supervisor/Staf')
            ->assertSee('Divisi Pengujian')
            ->assertSee('Manajer Contoh')
            ->assertSee(route('employee.show', $manager->unique_id), false)
            ->assertSee('12 Oktober 2026, 06:30 WIB')
            ->assertSee(route('employee.edit', $employee->unique_id), false)
            ->assertDontSee($employee->user->password)
            ->assertDontSee('linear-gradient')
            ->assertDontSee('box-shadow');
    }

    public function test_detail_handles_missing_organization_manager_and_photo(): void
    {
        $this->sign_in();
        $employee = Employee::create(['name' => 'Tanpa Penugasan', 'employee_id' => '100000001']);

        $this->get(route('employee.show', $employee->unique_id))->assertOk()
            ->assertSee('Belum diatur')
            ->assertSee('Tidak ada')
            ->assertDontSee(route('employee.photo', $employee->unique_id), false);
    }

    public function test_edit_preserves_submitted_status_and_photo_removal_after_validation_failure(): void
    {
        $this->sign_in();
        $employee = $this->employee();
        $employee->update(['profile_photo_path' => 'employees/example.jpg']);
        $errors = (new ViewErrorBag)->put('default', new MessageBag(['employee_id' => 'NIK tidak valid.']));

        $response = $this->withSession([
            'errors' => $errors,
            '_old_input' => ['name' => 'Nama Koreksi', 'employee_id' => '123', 'status' => 'active', 'remove_photo' => '1'],
        ])->get(route('employee.edit', $employee->unique_id))->assertOk()
            ->assertSee('value="Nama Koreksi"', false)
            ->assertSee('value="123"', false)
            ->assertSee('value="active" selected', false)
            ->assertSee('id="remove_photo" checked', false)
            ->assertSee('<div class="invalid-feedback">NIK tidak valid.</div>', false);
        $this->assertMatchesRegularExpression('/id="photo-preview"[^>]+class="[^"]+d-none"/', $response->getContent());
    }

    public function test_update_keeps_password_when_blank_and_removes_private_photo(): void
    {
        $this->sign_in();
        $employee = $this->employee();
        Storage::fake('ship');
        Storage::fake('local');
        Storage::disk('ship')->put('employees/example.jpg', 'example-photo');
        $employee->update(['profile_photo_path' => 'employees/example.jpg']);
        $original_password = $employee->user->password;

        $this->put(route('employee.update', $employee->unique_id), [
            'name' => 'Nama Diperbarui',
            'employee_id' => '001234567',
            'email' => null,
            'position_id' => $employee->position_id,
            'status' => 'active',
            'password' => '',
            'password_confirmation' => '',
            'remove_photo' => '1',
        ])->assertSessionHasNoErrors()->assertRedirect(route('employee.index'));

        $employee->refresh();
        $this->assertSame('active', $employee->status);
        $this->assertSame('Nama Diperbarui', $employee->name);
        $this->assertSame($original_password, $employee->user->password);
        $this->assertNull($employee->profile_photo_path);
        Storage::disk('ship')->assertMissing('employees/example.jpg');
    }

    public function test_update_requires_matching_confirmation_and_changes_password(): void
    {
        $this->sign_in();
        $employee = $this->employee();
        $payload = [
            'name' => $employee->name,
            'employee_id' => $employee->employee_id,
            'email' => null,
            'position_id' => $employee->position_id,
            'status' => 'active',
            'password' => 'updated-password',
            'password_confirmation' => 'incorrect-password',
        ];

        $this->put(route('employee.update', $employee->unique_id), $payload)->assertSessionHasErrors('password');
        $this->assertTrue(Hash::check('existing-password', $employee->user->password));

        $payload['password_confirmation'] = 'updated-password';
        $this->put(route('employee.update', $employee->unique_id), $payload)
            ->assertSessionHasNoErrors()->assertRedirect(route('employee.index'));
        $this->assertTrue(Hash::check('updated-password', $employee->fresh()->user->password));
    }
}
