<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Customer\customer_decision_request;
use App\Http\Requests\Customer\customer_feedback_request;
use App\Http\Requests\Customer\customer_login_request;
use App\Http\Requests\Customer\customer_password_request;
use App\Http\Resources\customer_document_resource;
use App\Models\customer_document;
use App\Services\customer_portal_service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class customer_portal_controller extends Controller
{
    public function __construct(private readonly customer_portal_service $portal) {}

    public function login(): View
    {
        return view('customer.login');
    }

    public function login_process(customer_login_request $request): RedirectResponse
    {
        if (! $this->portal->login($request, $request->validated())) {
            return back()->withInput($request->only('email'))->with('error', 'Login gagal. Periksa email dan kata sandi atau hubungi administrator.');
        }

        return redirect()->route('customer.index');
    }

    public function index(Request $request): View
    {
        return view('customer.index', $this->portal->index_data($request));
    }

    public function project(Request $request, string $project_id): View
    {
        return view('customer.project', $this->portal->project_data($request, $project_id));
    }

    public function document(Request $request, customer_document $document): View
    {
        $document_data = $this->portal->document_data($request, $document);

        return view('customer.document', ['document_data' => $document_data, 'project' => $document->project]);
    }

    public function document_data(Request $request, customer_document $document): customer_document_resource
    {
        return new customer_document_resource($this->portal->document_data($request, $document));
    }

    public function unlock(customer_password_request $request, customer_document $document): RedirectResponse
    {
        $this->portal->unlock_prices($request, $document);

        return redirect()->route('customer.document', $document)->with('success', 'Harga dibuka selama 5 menit untuk dokumen ini.');
    }

    public function lock(Request $request, customer_document $document): RedirectResponse
    {
        $this->portal->lock_prices($request, $document);

        return redirect()->route('customer.document', $document)->with('success', 'Harga telah dikunci.');
    }

    public function decide(customer_decision_request $request, customer_document $document): RedirectResponse
    {
        $this->portal->decide($request, $document, $request->validated());

        return redirect()->route('customer.document', $document)->with('success', 'Keputusan Anda berhasil dicatat.');
    }

    public function feedback(customer_feedback_request $request, string $project_id): RedirectResponse
    {
        $this->portal->submit_feedback($request, $project_id, $request->validated());

        return redirect()->route('customer.project', $project_id)->with('success', 'Masukan Anda berhasil dikirim.');
    }

    public function settings(): View
    {
        return view('customer.settings');
    }

    public function password(customer_password_request $request): RedirectResponse
    {
        $this->portal->update_password($request, $request->validated('password'));

        return redirect()->route('customer.settings')->with('success', 'Kata sandi berhasil diperbarui. Semua harga dikunci kembali.');
    }
}
