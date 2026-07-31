<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::withCount('roles')->orderBy('name')->get();

        return view('permission.index', compact('permissions'));
    }

    public function create()
    {
        return view('permission.create');
    }

    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), $this->permission_rules(), $this->permission_messages());

        if ($validation->fails()) {
            return back()->withErrors($validation)->withInput();
        }

        $validated = $validation->validated();

        Permission::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
            'permission_name' => $validated['permission_name'],
            'permission_description' => $validated['permission_description'] ?? null,
        ]);

        return redirect()->route('permission.index')->with('success', 'Permission berhasil ditambahkan.');
    }

    public function show($id)
    {
        $permission = Permission::with('roles')->findOrFail($id);

        return view('permission.show', compact('permission'));
    }

    public function edit($id)
    {
        $permission = Permission::findOrFail($id);

        return view('permission.edit', compact('permission'));
    }

    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $validation = Validator::make($request->all(), $this->permission_rules($permission->id), $this->permission_messages());

        if ($validation->fails()) {
            return back()->withErrors($validation)->withInput();
        }

        $validated = $validation->validated();

        $permission->update([
            'name' => $validated['name'],
            'permission_name' => $validated['permission_name'],
            'permission_description' => $validated['permission_description'] ?? null,
        ]);

        return redirect()->route('permission.index')->with('success', 'Permission berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);

        $has_model_assignment = DB::table('model_has_permissions')
            ->where('permission_id', $permission->id)
            ->exists();

        if ($has_model_assignment) {
            return redirect()->route('permission.index')->with('error', 'Permission tidak dapat dihapus karena masih diassign ke pengguna.');
        }

        $permission->delete();

        return redirect()->route('permission.index')->with('success', 'Permission berhasil dihapus.');
    }

    public function edit_roles($id)
    {
        $permission = Permission::with('roles')->findOrFail($id);
        $roles = Role::orderBy('name')->get();
        $assigned_role_ids = $permission->roles->pluck('id')->toArray();

        return view('permission.assign-roles', compact('permission', 'roles', 'assigned_role_ids'));
    }

    public function update_roles(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $validation = Validator::make($request->all(), [
            'role_ids' => 'nullable|array',
            'role_ids.*' => 'integer|exists:roles,id',
        ]);

        if ($validation->fails()) {
            return back()->withErrors($validation)->withInput();
        }

        $role_ids = $request->input('role_ids', []);
        $permission->roles()->sync($role_ids);

        return redirect()->route('permission.index')->with('success', 'Assignment role untuk permission berhasil diperbarui.');
    }

    private function permission_rules(?int $ignore_id = null): array
    {
        $name_rule = 'required|string|max:255|unique:permissions,name';

        if ($ignore_id !== null) {
            $name_rule .= ','.$ignore_id;
        }

        return [
            'name' => $name_rule,
            'permission_name' => 'required|string|max:255',
            'permission_description' => 'nullable|string|max:1000',
        ];
    }

    private function permission_messages(): array
    {
        return [
            'name.required' => 'Field nama sistem permission wajib diisi.',
            'name.unique' => 'Nama sistem permission sudah digunakan.',
            'permission_name.required' => 'Field nama permission wajib diisi.',
        ];
    }
}
