<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\OrganizationalUnit;
use App\Models\Position;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PositionController extends Controller
{
    public function index(): View
    {
        $positions = Position::with(['organizational_unit', 'employees'])
            ->orderBy('name')
            ->get();

        return view('position.index', compact('positions'));
    }

    public function create(): View
    {
        $organizational_units = OrganizationalUnit::orderByRaw("FIELD(type, 'directorate', 'division', 'subdivision', 'workshop')")
            ->orderBy('name')
            ->get();
        $category_options = Position::category_options();

        return view('position.create', compact('organizational_units', 'category_options'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validation = Validator::make($request->all(), $this->rules(), $this->messages());

        if ($validation->fails()) {
            return back()->withErrors($validation)->withInput();
        }

        Position::create($this->prepare_payload($validation->validated()));

        return redirect()->route('position.index')->with('success', 'Jabatan berhasil ditambahkan.');
    }

    public function show(int $id): View
    {
        $position = Position::with(['organizational_unit.parent', 'employees'])->findOrFail($id);

        return view('position.show', compact('position'));
    }

    public function edit(int $id): View
    {
        $position = Position::findOrFail($id);

        $organizational_units = OrganizationalUnit::orderByRaw("FIELD(type, 'directorate', 'division', 'subdivision', 'workshop')")
            ->orderBy('name')
            ->get();
        $category_options = Position::category_options();

        return view('position.edit', compact('position', 'organizational_units', 'category_options'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $position = Position::findOrFail($id);

        $validation = Validator::make($request->all(), $this->rules($id), $this->messages());

        if ($validation->fails()) {
            return back()->withErrors($validation)->withInput();
        }

        $position->update($this->prepare_payload($validation->validated()));

        return redirect()->route('position.index')->with('success', 'Jabatan berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $position = Position::withCount('employees')->findOrFail($id);

        if ($position->employees_count > 0) {
            return back()->with('error', 'Jabatan tidak dapat dihapus karena masih dipakai oleh karyawan.');
        }

        $position->delete();

        return redirect()->route('position.index')->with('success', 'Jabatan berhasil dihapus.');
    }

    private function rules(?int $position_id = null): array
    {
        $name_rule = 'required|string|max:255|unique:positions,name';
        $code_rule = 'nullable|string|max:50|unique:positions,code';

        if ($position_id !== null) {
            $name_rule .= ','.$position_id;
            $code_rule .= ','.$position_id;
        }

        return [
            'name' => $name_rule,
            'category' => 'required|in:'.implode(',', array_keys(Position::category_options())),
            'is_head_position' => 'nullable|boolean',
            'code' => $code_rule,
            'organizational_unit_id' => 'required|exists:organizational_units,id',
        ];
    }

    private function messages(): array
    {
        return [
            'name.required' => 'Nama jabatan wajib diisi.',
            'name.unique' => 'Nama jabatan sudah digunakan.',
            'category.required' => 'Kategori jabatan wajib dipilih.',
            'category.in' => 'Kategori jabatan tidak valid.',
            'code.unique' => 'Kode jabatan sudah digunakan.',
            'organizational_unit_id.required' => 'Unit organisasi wajib dipilih.',
            'organizational_unit_id.exists' => 'Unit organisasi yang dipilih tidak valid.',
        ];
    }

    private function prepare_payload(array $validated): array
    {
        $validated['level'] = Position::level_for_category($validated['category']);

        return $validated;
    }
}
