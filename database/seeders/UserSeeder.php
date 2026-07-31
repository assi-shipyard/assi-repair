<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\OrganizationalUnit;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $adminRole = Role::firstOrCreate([
                'name' => 'admin',
                'guard_name' => 'web',
            ]);

            $adminDirectorate = OrganizationalUnit::updateOrCreate(
                ['code' => 'DIR-ADM'],
                [
                    'name' => 'Direktorat Administrasi',
                    'type' => 'directorate',
                    'parent_id' => null,
                ]
            );

            $adminUnit = OrganizationalUnit::updateOrCreate(
                ['code' => 'DIV-ADM'],
                [
                    'name' => 'Divisi Administrasi',
                    'type' => 'division',
                    'parent_id' => $adminDirectorate->id,
                ]
            );

            $adminPosition = Position::updateOrCreate(
                ['code' => 'ADMIN'],
                [
                    'name' => 'Administrator Sistem',
                    'level' => 1,
                    'category' => 'assistant_manager',
                    'is_head_position' => true,
                    'organizational_unit_id' => $adminUnit->id,
                ]
            );

            $user = User::updateOrCreate(
                ['employee_id' => 'assiadmin'],
                [
                    'email' => 'admin@assishipyard.com',
                    'password' => bcrypt('admin@assishipyard!'),
                ]
            );

            $user->syncRoles([$adminRole->name]);

            Employee::updateOrCreate(
                ['employee_id' => 'assiadmin'],
                [
                    'user_id' => $user->id,
                    'email' => 'admin@assishipyard.com',
                    'name' => 'Administrator Sistem',
                    'status' => 'active',
                    'position_id' => $adminPosition->id,
                    'direct_manager_employee_id' => null,
                ]
            );
        });
    }
}
