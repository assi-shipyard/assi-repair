<?php

namespace App\Http\Controllers;

use App\Models\OrganizationalUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrganizationalUnitController extends Controller
{
    // Display a listing of the organizational units.
    public function index()
    {
        // Fetch organizational units with their parent, children, and positions, ordered by type and name
        $organizational_units = OrganizationalUnit::with(['parent', 'children', 'positions'])
            ->orderByRaw("FIELD(type, 'directorate', 'division', 'subdivision', 'workshop')")
            ->orderBy('name')
            ->get();

        return view('organizational-unit.index', compact('organizational_units'));
    }

    // Show the form for creating a new organizational unit.
    public function create()
    {
        // Fetch types and existing organizational units for the form
        $types = $this->types();
        $organizational_units = OrganizationalUnit::orderBy('name')->get();

        return view('organizational-unit.create', compact('types', 'organizational_units'));
    }

    // Store a newly created organizational unit in storage.
    public function store(Request $request)
    {
        // Validate the request data against the defined rules and messages
        $validation = Validator::make($request->all(), $this->rules(), $this->messages());

        // If validation fails, redirect back with errors and input data
        if ($validation->fails()) {
            return back()->withErrors($validation)->withInput();
        }

        // Retrieve the validated data and check for parent type errors
        $data = $validation->validated();
        $parent_type_error = $this->parent_type_error($data['type'], $data['parent_id'] ?? null);

        // If there is a parent type error, redirect back with the error message and input data
        if ($parent_type_error) {
            return back()->withErrors(['parent_id' => $parent_type_error])->withInput();
        }

        // Create a new organizational unit with the validated data
        OrganizationalUnit::create([
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'type' => $data['type'],
            'parent_id' => $data['parent_id'] ?? null,
        ]);

        return redirect()->route('organizational-unit.index')->with('success', 'Unit organisasi berhasil ditambahkan.');
    }

    // Display the specified organizational unit.
    public function show(string $organizational_unit)
    {
        $organizational_unit = $this->find_organizational_unit($organizational_unit, ['parent', 'children.positions', 'positions.employees']);

        return view('organizational-unit.show', compact('organizational_unit'));
    }

    // Show the form for editing the specified organizational unit.
    public function edit(string $organizational_unit)
    {
        $organizational_unit = $this->find_organizational_unit($organizational_unit);
        $types = $this->types();
        $organizational_units = OrganizationalUnit::where('id', '!=', $organizational_unit->id)
            ->orderBy('name')
            ->get();

        return view('organizational-unit.edit', compact('organizational_unit', 'types', 'organizational_units'));
    }

    // Update the specified organizational unit in storage.
    public function update(Request $request, string $organizational_unit)
    {
        $organizational_unit = $this->find_organizational_unit($organizational_unit);

        $validation = Validator::make($request->all(), $this->rules($organizational_unit->id), $this->messages());

        // If validation fails, redirect back with errors and input data
        if ($validation->fails()) {
            return back()->withErrors($validation)->withInput();
        }

        // Retrieve the validated data and check for parent type errors, passing the current organizational unit ID to avoid self-referencing
        $data = $validation->validated();
        $parent_type_error = $this->parent_type_error($data['type'], $data['parent_id'] ?? null, $organizational_unit->id);

        // If there is a parent type error, redirect back with the error message and input data
        if ($parent_type_error) {
            return back()->withErrors(['parent_id' => $parent_type_error])->withInput();
        }

        // Update the organizational unit with the validated data
        $organizational_unit->update([
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'type' => $data['type'],
            'parent_id' => $data['parent_id'] ?? null,
        ]);

        return redirect()->route('organizational-unit.index')->with('success', 'Unit organisasi berhasil diperbarui.');
    }

    // Remove the specified organizational unit from storage.
    public function destroy(string $organizational_unit)
    {
        $organizational_unit = $this->find_organizational_unit($organizational_unit);

        // Check if the organizational unit has any children or positions before deletion
        if ($organizational_unit->children()->exists()) {
            return back()->with('error', 'Unit organisasi tidak dapat dihapus karena masih memiliki sub unit.');
        }

        // Check if the organizational unit has any positions before deletion
        if ($organizational_unit->positions()->exists()) {
            return back()->with('error', 'Unit organisasi tidak dapat dihapus karena masih memiliki jabatan.');
        }

        // Delete the organizational unit if it has no children or positions and no conflicts with other organizational units
        $organizational_unit->delete();

        return redirect()->route('organizational-unit.index')->with('success', 'Unit organisasi berhasil dihapus.');
    }

    // Private helper methods for types, validation rules, messages, and parent type error checking
    private function types(): array
    {
        return [
            'directorate' => 'Direktorat',
            'division' => 'Divisi',
            'subdivision' => 'Subdivisi',
            'workshop' => 'Workshop / Bengkel',
        ];
    }

    // Validation rules for creating or updating an organizational unit, with optional unique code validation for updates
    private function rules(?int $organizationalUnitId = null): array
    {
        // Define the validation rule for the 'code' field, allowing it to be nullable, a string, a maximum of 50 characters, and unique in the organizational_units table
        $codeRule = 'nullable|string|max:50|unique:organizational_units,code';

        // If an organizational unit ID is provided (for updates), append it to the unique validation rule to exclude the current record from the uniqueness check
        if ($organizationalUnitId !== null) {
            $codeRule .= ','.$organizationalUnitId;
        }

        return [
            'name' => 'required|string|max:255',
            'code' => $codeRule,
            'type' => 'required|in:directorate,division,subdivision,workshop',
            'parent_id' => 'nullable|exists:organizational_units,id',
        ];
    }

    // Validation messages for the organizational unit form fields, providing user-friendly error messages for required fields, unique constraints, and valid parent unit selection
    private function messages(): array
    {
        return [
            'name.required' => 'Nama unit organisasi wajib diisi.',
            'code.unique' => 'Kode unit organisasi sudah digunakan.',
            'type.required' => 'Jenis unit organisasi wajib dipilih.',
            'type.in' => 'Jenis unit organisasi tidak valid.',
            'parent_id.exists' => 'Unit induk yang dipilih tidak valid.',
        ];
    }

    // Private helper method to check for parent type errors based on the organizational unit's type and its parent's type, ensuring that the parent-child relationship is valid according to the defined hierarchy
    private function parent_type_error(string $type, mixed $parentId, ?int $currentId = null): ?string
    {
        // If the organizational unit is a directorate, it should not have a parent unit because it is the highest level. If it does, return an error message.
        if ($type === 'directorate') {
            if ($parentId !== null && $parentId !== '') {
                return 'Direktorat tidak boleh memiliki unit induk.';
            }

            return null;
        }

        // If the organizational unit is not a directorate, it must have a parent unit. If it doesn't, return an error message.
        if (! $parentId) {
            return 'Unit ini memerlukan unit induk.';
        }

        // Fetch the parent organizational unit by its ID to check its type and validate the parent-child relationship according to the defined hierarchy.
        $parent = OrganizationalUnit::findOrFail($parentId);

        // Define the allowed parent types for each organizational unit type, ensuring that the parent-child relationship is valid according to the defined hierarchy.
        $allowedParentTypes = [
            'division' => ['directorate'],
            'subdivision' => ['division'],
            'workshop' => ['division'],
        ];

        // Check if the parent unit is the same as the current unit to prevent self-referencing, which would create a circular relationship. If it is, return an error message.
        if ($currentId !== null && (int) $parent->id === $currentId) {
            return 'Unit induk tidak boleh sama dengan unit itu sendiri.';
        }

        // Check if the parent unit's type is allowed for the current unit's type based on the defined hierarchy. If it is not, return an error message indicating that the parent unit type is not valid for the selected unit type.
        if (! in_array($parent->type, $allowedParentTypes[$type] ?? [], true)) {
            return 'Jenis unit induk tidak sesuai dengan unit yang dipilih.';
        }

        return null;
    }

    private function find_organizational_unit(string $organizational_unit, array $relations = []): OrganizationalUnit
    {
        $query = OrganizationalUnit::query()->with($relations);

        if (preg_match('/^[0-9a-fA-F-]{36}$/', $organizational_unit) === 1) {
            return $query->where('unique_id', $organizational_unit)->firstOrFail();
        }

        return $query->findOrFail((int) $organizational_unit);
    }
}
