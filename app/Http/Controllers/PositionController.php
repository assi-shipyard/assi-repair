<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\OrganizationalUnit;
use App\Models\Position;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
        $organizational_units = OrganizationalUnit::orderByRaw("FIELD(type, 'ceo', 'chrgao', 'cfo', 'cpo', 'directorate', 'division', 'bureau', 'subdivision', 'workshop')")
            ->orderBy('name')
            ->get();
        $category_options = Position::category_options();

        return view('position.create', compact('organizational_units', 'category_options'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validation = Validator::make($request->all(), $this->rules($request), $this->messages());

        if ($validation->fails()) {
            return back()->withErrors($validation)->withInput();
        }

        Position::create($this->prepare_payload($validation->validated()));

        return redirect()->route('position.index')->with('success', 'Jabatan berhasil ditambahkan.');
    }

    public function show(string $position): View
    {
        $position = $this->find_position($position, ['organizational_unit.parent', 'employees']);

        return view('position.show', compact('position'));
    }

    public function edit(string $position): View
    {
        $position = $this->find_position($position);

        $organizational_units = OrganizationalUnit::orderByRaw("FIELD(type, 'ceo', 'chrgao', 'cfo', 'cpo', 'directorate', 'division', 'bureau', 'subdivision', 'workshop')")
            ->orderBy('name')
            ->get();
        $category_options = Position::category_options();

        return view('position.edit', compact('position', 'organizational_units', 'category_options'));
    }

    public function update(Request $request, string $position): RedirectResponse
    {
        $position = $this->find_position($position);

        $validation = Validator::make($request->all(), $this->rules($request, $position->id), $this->messages());

        if ($validation->fails()) {
            return back()->withErrors($validation)->withInput();
        }

        $position->update($this->prepare_payload($validation->validated()));

        return redirect()->route('position.index')->with('success', 'Jabatan berhasil diperbarui.');
    }

    public function destroy(string $position): RedirectResponse
    {
        $position = $this->find_position($position, [], true);

        if ($position->employees_count > 0) {
            return back()->with('error', 'Jabatan tidak dapat dihapus karena masih dipakai oleh karyawan.');
        }

        $position->delete();

        return redirect()->route('position.index')->with('success', 'Jabatan berhasil dihapus.');
    }

    private function rules(Request $request, ?int $position_id = null): array
    {
        $code_rule = 'nullable|string|max:50|unique:positions,code';

        if ($position_id !== null) {
            $name_rule .= ','.$position_id;
            $code_rule .= ','.$position_id;
        }

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('positions', 'name')
                    ->where('organizational_unit_id', $request->input('organizational_unit_id'))
                    ->ignore($position_id),
            ],
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

    private function find_position(string $position, array $relations = [], bool $with_employee_count = false): Position
    {
        $query = Position::query()->with($relations);

        if ($with_employee_count) {
            $query->withCount('employees');
        }

        if (preg_match('/^[0-9a-fA-F-]{36}$/', $position) === 1) {
            return $query->where('unique_id', $position)->firstOrFail();
        }

        return $query->findOrFail((int) $position);
    }
}
