<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('permissions')->orderBy('name')->get();

        return view('role.index', compact('roles'));
    }

    public function create()
    {
        return view('role.create');
    }

    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), $this->role_rules(), $this->role_messages());

        if ($validation->fails()) {
            return back()->withErrors($validation)->withInput();
        }

        $validated = $validation->validated();

        Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
            'role_name' => $validated['role_name'],
            'role_description' => $validated['role_description'] ?? null,
        ]);

        return redirect()->route('role.index')->with('success', 'Role berhasil ditambahkan.');
    }

    public function show($id)
    {
        $role = Role::with('permissions')->findOrFail($id);

        return view('role.show', compact('role'));
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);

        return view('role.edit', compact('role'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validation = Validator::make($request->all(), $this->role_rules($role->id), $this->role_messages());

        if ($validation->fails()) {
            return back()->withErrors($validation)->withInput();
        }

        $validated = $validation->validated();

        $role->update([
            'name' => $validated['name'],
            'role_name' => $validated['role_name'],
            'role_description' => $validated['role_description'] ?? null,
        ]);

        return redirect()->route('role.index')->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        $has_model_assignment = DB::table('model_has_roles')
            ->where('role_id', $role->id)
            ->exists();

        if ($has_model_assignment) {
            return redirect()->route('role.index')->with('error', 'Role tidak dapat dihapus karena masih diassign ke pengguna.');
        }

        $role->delete();

        return redirect()->route('role.index')->with('success', 'Role berhasil dihapus.');
    }

    public function edit_permissions($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::orderBy('name')->get();
        $assigned_permission_ids = $role->permissions->pluck('id')->toArray();

        return view('role.assign-permissions', compact('role', 'permissions', 'assigned_permission_ids'));
    }

    public function update_permissions(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validation = Validator::make($request->all(), [
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'integer|exists:permissions,id',
        ]);

        if ($validation->fails()) {
            return back()->withErrors($validation)->withInput();
        }

        $permission_ids = $request->input('permission_ids', []);
        $role->syncPermissions($permission_ids);

        return redirect()->route('role.index')->with('success', 'Assignment permission untuk role berhasil diperbarui.');
    }

    private function role_rules(?int $ignore_id = null): array
    {
        $name_rule = 'required|string|max:255|unique:roles,name';
        $role_name_rule = 'required|string|max:255|unique:roles,role_name';

        if ($ignore_id !== null) {
            $name_rule .= ','.$ignore_id;
            $role_name_rule .= ','.$ignore_id;
        }

        return [
            'name' => $name_rule,
            'role_name' => $role_name_rule,
            'role_description' => 'nullable|string|max:1000',
        ];
    }

    private function role_messages(): array
    {
        return [
            'name.required' => 'Field nama sistem role wajib diisi.',
            'name.unique' => 'Nama sistem role sudah digunakan.',
            'role_name.required' => 'Field nama role wajib diisi.',
            'role_name.unique' => 'Nama role sudah digunakan.',
        ];
    }
}
