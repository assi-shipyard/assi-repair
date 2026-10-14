<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Subcontractor\SaveSubcontractorRequest;
use App\Models\Subcontractor;
use App\Services\subcontractor_service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SubcontractorController extends Controller
{
    public function __construct(private readonly subcontractor_service $subcontractor_service) {}

    public function index(): View
    {
        return view('subcontractor.index', [
            'subcontractors' => Subcontractor::query()->orderBy('name')->orderBy('id')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('subcontractor.form', ['subcontractor' => new Subcontractor(['is_active' => true])]);
    }

    public function edit(Subcontractor $subcontractor): View
    {
        return view('subcontractor.form', compact('subcontractor'));
    }

    public function store(SaveSubcontractorRequest $request): RedirectResponse
    {
        $this->subcontractor_service->save_vendor($request->validated(), $request->user());

        return redirect()->route('subcontractor.index')->with('success', 'Data subkontraktor berhasil ditambahkan.');
    }

    public function update(SaveSubcontractorRequest $request, Subcontractor $subcontractor): RedirectResponse
    {
        $this->subcontractor_service->save_vendor($request->validated(), $request->user(), $subcontractor);

        return redirect()->route('subcontractor.index')->with('success', 'Data subkontraktor berhasil diperbarui.');
    }
}
