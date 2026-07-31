<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;

class PositionRolePermissionController extends Controller
{
    // ================================================
    // I. ROLE MANAGEMENT
    // ================================================
    public function role_index()
    {
        $roles = Role::all();

        return view('admin.roledata', compact('roles'));
    }

    public function role_create(Request $request)
    {
        // Validate input
        $validation = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name',
            'role_name' => 'required|string|max:255|unique:roles,role_name',
            'role_description' => 'nullable|string|max:255',
        ], [
            'name.required' => 'Field nama role wajib diisi.',
            'name.unique' => 'Nama role sudah digunakan.',
            'role_name.required' => 'Field nama role (descriptive) wajib diisi.',
            'role_name.unique' => 'Nama role (descriptive) sudah digunakan.',
        ]);

        // Handle validation failure
        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        }

        // Create new role
        $role = new Role;
        $role->name = $request->name;
        $role->role_name = $request->role_name;
        $role->role_description = $request->role_description ?? null;
        $role->guard_name = 'web';
        $role->save();

        return redirect()->route('role.index')->with('success', 'Role \''.$role->role_name.'\' berhasil ditambahkan.');
    }

    public function role_show($id)
    {
        // AJAX-only access
        if (! request()->ajax()) {
            abort(403, 'Unauthorized');
        }

        // Fetch and return role data
        $role = Role::findOrFail($id);

        return response()->json($role);
    }

    public function role_update(Request $request, $id)
    {
        // Fetch role
        $role = Role::findOrFail($id);

        // Validate input
        $validation = Validator::make($request->all(), [
            'edit_name' => 'required|string|max:255|unique:roles,name,'.$role->id,
            'edit_role_name' => 'required|string|max:255|unique:roles,role_name,'.$role->id,
            'edit_role_description' => 'nullable|string|max:255',
        ]);

        // Handle validation failure
        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        }

        // Update role
        $role->name = $request->edit_name;
        $role->role_name = $request->edit_role_name;
        $role->role_description = $request->edit_role_description ?? null;
        $role->save();

        return redirect()->route('role.index')->with('success', 'Role berhasil diperbarui.');
    }

    public function role_delete($id)
    {
        // Delete role
        $role = Role::findOrFail($id);

        // Check if role is assigned to any positions
        if ($role->positions()->count() > 0) {
            return redirect()->route('role.index')->with('error', 'Role tidak dapat dihapus karena masih digunakan oleh jabatan.');
        }

        // Proceed to delete role if not assigned
        $role->delete();

        return redirect()->route('role.index')->with('success', 'Role berhasil dihapus.');
    }

    // ================================================
    // II. POSITION ASSIGNMENT TO ROLE
    // ================================================
    public function assign_role_index($id)
    {
        // Fetch role and related data
        $role = Role::with('positions')->findOrFail($id);
        $positions = Position::all();
        $assignedPositionIds = $role->positions->pluck('id')->toArray();

        return view('admin.assignrolepositions', compact('role', 'positions', 'assignedPositionIds'));
    }

    public function assign_role_edit($id)
    {
        // Fetch role and related data
        $role = Role::with('positions')->findOrFail($id);
        $positions = Position::all();
        $assignedPositionIds = $role->positions->pluck('id')->toArray();

        return view('admin.assignrolepositionsedit', compact('role', 'positions', 'assignedPositionIds'));
    }

    public function assign_role_update(Request $request, $id)
    {
        // Fetch role
        $role = Role::findOrFail($id);

        // Sync assigned positions with selected positions
        $positionIds = $request->input('position_ids', []);

        // Update role's positions
        $role->positions()->sync($positionIds);

        // Redirect with success message with the role name and number of assigned positions
        return redirect()->route('role.index')->with('success', 'Role \''.$role->name.'\' berhasil diperbarui dengan '.count($positionIds).' jabatan yang diassign.');
    }

    // ================================================
    // III. PERMISSION MANAGEMENT
    // ================================================
    public function permission_index()
    {
        $permissions = Permission::all();

        return view('admin.permissiondata', compact('permissions'));
    }

    public function permission_create(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:permissions,name',
            'permission_name' => 'required|string|max:255',
            'permission_description' => 'nullable|string',
        ], [
            'name.required' => 'Field nama permission (Spatie) wajib diisi.',
            'name.unique' => 'Nama permission (Spatie) sudah digunakan.',
            'permission_name.required' => 'Field nama permission wajib diisi.',
            'permission_name.unique' => 'Nama permission sudah digunakan.',
        ]);

        // Handle validation failure
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Create new permission
        $permission = new Permission;
        $permission->name = $request->name;
        $permission->guard_name = 'web';
        $permission->permission_name = $request->permission_name;
        $permission->permission_description = $request->permission_description ?? null;
        $permission->save();

        return redirect()->route('permission.index')->with('success', 'Permission berhasil ditambahkan.');
    }

    public function permission_show($id)
    {
        // AJAX-only access
        if (! request()->ajax()) {
            abort(403, 'Unauthorized');
        }

        // Fetch and return permission data
        $permission = Permission::findOrFail($id);

        return response()->json($permission);
    }

    public function permission_update(Request $request, $id)
    {
        // Fetch permission
        $permission = Permission::findOrFail($id);

        // Validate input
        $validator = Validator::make($request->all(), [
            'edit_name' => 'required|string|max:255|unique:permissions,name,'.$permission->name,
            'edit_permission_name' => 'required|string|max:255',
            'edit_permission_description' => 'nullable|string',
        ]);

        // Handle validation failure
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Update permission
        $permission->name = $request->edit_name;
        $permission->permission_name = $request->edit_permission_name;
        $permission->permission_description = $request->edit_permission_description ?? null;
        $permission->save();

        return redirect()->route('permission.index')->with('success', 'Permission berhasil diperbarui.');
    }

    public function permission_delete($id)
    {
        // Delete permission
        $permission = Permission::findOrFail($id);

        // Check if permission is assigned to any roles
        if ($permission->roles()->count() > 0) {
            return redirect()->route('permission.index')->with('error', 'Permission tidak dapat dihapus karena masih digunakan oleh role.');
        }

        // Proceed to delete permission if not assigned
        $permission->delete();

        return redirect()->route('permission.index')->with('success', 'Permission berhasil dihapus.');
    }

    // ================================================
    // IV. POSITION ROLE & PERMISSION ASSIGNMENT
    // ================================================
    // Show assignment table (assignrolepermission.blade.php)
    public function index()
    {
        $positions = Position::with(['roles', 'permissions'])->get();

        return view('admin.assignrolepermission', compact('positions'));
    }

    // Show form to assign roles/permissions to a position
    public function edit($positionId)
    {
        $position = Position::with(['roles', 'permissions'])->findOrFail($positionId);
        $allRoles = Role::all(); // uses roles seeded by RolePermissionSeeder
        $allPermissions = Permission::all(); // uses permissions seeded by RolePermissionSeeder
        $assignedRoleIds = $position->roles->pluck('id')->toArray();
        $assignedPermissionIds = $position->permissions->pluck('id')->toArray();

        return view('admin.assignrolepermissionedit', compact(
            'position', 'allRoles', 'allPermissions', 'assignedRoleIds', 'assignedPermissionIds'
        ));
    }

    // Update roles/permissions for a position
    public function update(Request $request, $positionId)
    {
        // Fetch position
        $position = Position::findOrFail($positionId);

        // Assign roles from seeded roles
        $roleIds = $request->input('roles', []);
        $position->roles()->sync($roleIds);

        // Assign permissions from seeded permissions
        $permissionIds = $request->input('permissions', []);
        $position->syncPermissions($permissionIds);

        return redirect()->route('assign-permission.index')->with('success', 'Roles and permissions updated for position.');
    }

    // ================================================
    // V. ASSIGN ROLES TO SPECIFIC POSITION
    // ================================================
    public function assign_roles_form($positionId)
    {
        // Show form to assign roles to a position
        $position = Position::with('roles')->findOrFail($positionId);
        $roles = Role::all();
        $assignedRoleIds = $position->roles->pluck('id')->toArray();

        return view('admin.assignroletoposition', compact('position', 'roles', 'assignedRoleIds'));
    }

    public function assign_roles_update(Request $request, $positionId)
    {
        // Update assigned roles for the position
        $position = Position::findOrFail($positionId);
        $roleIds = $request->input('role_ids', []);
        $position->roles()->sync($roleIds);

        return redirect()->route('position.index')->with('success', 'Role berhasil diassign ke jabatan.');
    }

    // Assign permission to roles form
    public function assign_permission_roles_form($permissionId)
    {
        $permission = Permission::with('roles')->findOrFail($permissionId);
        $roles = Role::all();
        $assignedRoleIds = $permission->roles->pluck('id')->toArray();

        return view('admin.assignpermissiontorole', compact('permission', 'roles', 'assignedRoleIds'));
    }

    public function assign_permission_roles_update(Request $request, $permissionId)
    {
        $permission = Permission::findOrFail($permissionId);
        $roleIds = $request->input('role_ids', []);
        $permission->roles()->sync($roleIds);

        return redirect()->route('permission.index')->with('success', 'Permission berhasil diassign ke role.');
    }
}
