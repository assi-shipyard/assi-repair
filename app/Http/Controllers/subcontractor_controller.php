<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Subcontractor\save_subcontractor_request;
use App\Models\subcontractor;
use App\Services\subcontractor_service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class subcontractor_controller extends Controller
{
    public function __construct(private readonly subcontractor_service $subcontractor_service) {}

    public function index(): View
    {
        return view('subcontractor.index', [
            'subcontractors' => subcontractor::query()->orderBy('name')->orderBy('id')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('subcontractor.form', ['subcontractor' => new subcontractor(['is_active' => true])]);
    }

    public function edit(subcontractor $subcontractor): View
    {
        return view('subcontractor.form', compact('subcontractor'));
    }

    public function store(save_subcontractor_request $request): RedirectResponse
    {
        $this->subcontractor_service->save_vendor($request->validated(), $request->user());

        return redirect()->route('subcontractor.index')->with('success', 'Data subkontraktor berhasil ditambahkan.');
    }

    public function update(save_subcontractor_request $request, subcontractor $subcontractor): RedirectResponse
    {
        $this->subcontractor_service->save_vendor($request->validated(), $request->user(), $subcontractor);

        return redirect()->route('subcontractor.index')->with('success', 'Data subkontraktor berhasil diperbarui.');
    }
}
