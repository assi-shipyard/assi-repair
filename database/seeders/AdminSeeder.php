<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\OrganizationalUnit;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use RuntimeException;
use Spatie\Permission\Models\Role;

/**
 * Seeds the single administrator account.
 *
 * Credentials are never hardcoded: they are read from the environment.
 * On re-run the seeder is idempotent and will NOT reset an existing password.
 */
class AdminSeeder extends Seeder
{
    private const DEFAULT_EMPLOYEE_ID = 'assiadmin';

    public function run(): void
    {
        $employee_id = trim((string) config('seeding.admin.employee_id', self::DEFAULT_EMPLOYEE_ID));
        $email = trim((string) config('seeding.admin.email', ''));
        $name = trim((string) config('seeding.admin.name', 'Administrator Sistem'));
        $password = (string) config('seeding.admin.password', '');
        $password_was_generated = false;
        $existing_user = User::query()->where('employee_id', $employee_id)->exists();

        $this->validate_identity($employee_id, $email);

        if ($existing_user) {
            if ($password !== '') {
                $this->warn_if_password_weak($password);
            }
        } else {
            if ($password === '') {
                if (app()->environment('production')) {
                    throw new RuntimeException(
                        'ADMIN_PASSWORD wajib diisi pada environment production sebelum menjalankan AdminSeeder.'
                    );
                }

                $password = $this->generate_password();
                $password_was_generated = true;
            }

            $this->validate_password($password);
        }

        DB::transaction(function () use ($employee_id, $email, $name, $password, $password_was_generated): void {
            $admin_role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

            $directorate = OrganizationalUnit::updateOrCreate(
                ['code' => 'DIR-ADM'],
                [
                    'name' => 'Direktorat Administrasi',
                    'type' => 'directorate',
                    'parent_id' => null,
                ]
            );

            $unit = OrganizationalUnit::updateOrCreate(
                ['code' => 'DIV-ADM'],
                [
                    'name' => 'Divisi Administrasi',
                    'type' => 'division',
                    'parent_id' => $directorate->id,
                ]
            );

            $position = Position::updateOrCreate(
                ['code' => 'ADMIN'],
                [
                    'name' => 'Administrator Sistem',
                    'level' => 1,
                    'category' => 'assistant_manager',
                    'is_head_position' => true,
                    'organizational_unit_id' => $unit->id,
                ]
            );

            $user = User::query()->where('employee_id', $employee_id)->first();

            if ($user === null) {
                $user = new User();
                $user->employee_id = $employee_id;
                $user->password = Hash::make($password);
            }

            // Existing password is intentionally left untouched to avoid silent credential resets.
            $user->email = $email !== '' ? $email : $user->email;
            $user->save();

            $user->syncRoles([$admin_role->name]);

            Employee::updateOrCreate(
                ['employee_id' => $employee_id],
                [
                    'user_id' => $user->id,
                    'email' => $email !== '' ? $email : null,
                    'name' => $name,
                    'status' => 'active',
                    'position_id' => $position->id,
                    'direct_manager_employee_id' => null,
                ]
            );

            $this->report($employee_id, $password, $password_was_generated, $user->wasRecentlyCreated);
        });

        // Best-effort scrub of the plaintext password from memory.
        $password = str_repeat("\0", strlen($password));
        unset($password);
    }

    private function validate_credentials(string $employee_id, string $email, string $password): void
    {
        $this->validate_identity($employee_id, $email);
        $this->validate_password($password);
    }

    private function validate_identity(string $employee_id, string $email): void
    {
        $validator = Validator::make(
            [
                'employee_id' => $employee_id,
                'email' => $email !== '' ? $email : null,
            ],
            [
                'employee_id' => ['required', 'string', 'min:5', 'max:64', 'regex:/^[A-Za-z0-9._-]+$/'],
                'email' => ['nullable', 'email:rfc', 'max:64'],
            ],
            [
                'employee_id.required' => 'ADMIN_EMPLOYEE_ID wajib diisi.',
                'employee_id.regex' => 'ADMIN_EMPLOYEE_ID hanya boleh berisi huruf, angka, titik, garis bawah, dan tanda hubung.',
                'email.email' => 'ADMIN_EMAIL harus berupa alamat email yang valid.',
            ]
        );

        if ($validator->fails()) {
            throw new RuntimeException(
                'Kredensial admin tidak valid: ' . implode(' ', $validator->errors()->all())
            );
        }
    }

    private function validate_password(string $password): void
    {
        $validator = Validator::make(
            ['password' => $password],
            [
                'password' => [
                    'required',
                    'string',
                    'max:255',
                    Password::min(12)->letters()->mixedCase()->numbers()->symbols()->uncompromised(),
                ],
            ],
            [
                'password.required' => 'ADMIN_PASSWORD wajib diisi.',
            ]
        );

        if ($validator->fails()) {
            throw new RuntimeException(
                'Kredensial admin tidak valid: ' . implode(' ', $validator->errors()->all())
            );
        }
    }

    private function warn_if_password_weak(string $password): void
    {
        $validator = Validator::make(
            ['password' => $password],
            [
                'password' => [
                    'required',
                    'string',
                    'max:255',
                    Password::min(12)->letters()->mixedCase()->numbers()->symbols()->uncompromised(),
                ],
            ]
        );

        if ($validator->fails()) {
            $this->command?->warn('ADMIN_PASSWORD diabaikan karena akun admin sudah ada dan format password tidak memenuhi kebijakan.');
        }
    }

    private function generate_password(): string
    {
        // Str::password() uses a CSPRNG and guarantees mixed case, digits, and symbols.
        return Str::password(24);
    }

    private function report(
        string $employee_id,
        string $password,
        bool $password_was_generated,
        bool $was_created
    ): void {
        if (! $was_created) {
            $this->command?->info("Akun admin '{$employee_id}' sudah ada. Kata sandi tidak diubah.");

            return;
        }

        $this->command?->info("Akun admin '{$employee_id}' berhasil dibuat.");

        if ($password_was_generated) {
            $this->command?->warn('Kata sandi acak dibuat otomatis (hanya ditampilkan sekali):');
            $this->command?->warn($password);
            $this->command?->warn('Simpan kata sandi ini di password manager dan segera ubah setelah login pertama.');
        }
    }
}
