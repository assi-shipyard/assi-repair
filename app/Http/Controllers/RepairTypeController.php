<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\RepairType\SaveRepairTypeRequest;
use App\Models\RepairType;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class RepairTypeController extends Controller
{
    public function index(): View
    {
        return view('repair-type.index', [
            'repair_types' => RepairType::query()->orderBy('category')->orderBy('code')->get(),
            'categories' => RepairType::CATEGORIES,
        ]);
    }

    public function store(SaveRepairTypeRequest $request): RedirectResponse
    {
        RepairType::create($this->payload($request));

        return redirect()->route('price-dictionary.index')->with('success', 'Jenis reparasi berhasil ditambahkan.');
    }

    public function update(SaveRepairTypeRequest $request, RepairType $repair_type): RedirectResponse
    {
        $repair_type->update($this->payload($request));

        return redirect()->route('price-dictionary.index')->with('success', 'Jenis reparasi berhasil diperbarui.');
    }

    public function destroy(RepairType $repair_type): RedirectResponse
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        $repair_type->delete();

        return redirect()->route('price-dictionary.index')->with('success', 'Jenis reparasi berhasil dihapus.');
    }

    private function payload(SaveRepairTypeRequest $request): array
    {
        return [
            ...$request->safe()->except('is_active'),
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
