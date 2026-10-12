<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\account_directory_request;
use App\Http\Requests\Customer\manage_account_request;
use App\Http\Requests\Customer\manage_project_access_request;
use App\Http\Requests\Customer\publish_document_request;
use App\Http\Requests\Customer\respond_feedback_request;
use App\Models\customer_document;
use App\Models\customer_feedback;
use App\Models\User;
use App\Services\customer_management_service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class account_management_controller extends Controller
{
    public function __construct(private readonly customer_management_service $management) {}

    public function index(account_directory_request $request): View
    {
        return view('accounts.index', $this->management->account_data(User::TYPE_ADMIN, $request->validated()));
    }

    public function customers(account_directory_request $request): View
    {
        return view('accounts.index', $this->management->account_data(User::TYPE_CUSTOMER, $request->validated()));
    }

    public function photo(User $user): StreamedResponse
    {
        return $this->management->photo($user);
    }

    public function store(manage_account_request $request): RedirectResponse
    {
        $this->management->create_account($request->user(), $request->validated());

        return redirect()->route($request->validated('account_type') === User::TYPE_CUSTOMER ? 'accounts.customers' : 'accounts.index')->with('success', 'Akun berhasil dibuat. Sampaikan kata sandi awal melalui saluran aman.');
    }

    public function status(manage_account_request $request, User $user): RedirectResponse
    {
        $this->management->set_account_status($request->user(), $user, $request->boolean('is_active'));

        return redirect()->route($user->is_customer() ? 'accounts.customers' : ($user->account_type === User::TYPE_INTERNAL ? 'employee.index' : 'accounts.index'))->with('success', 'Status akun berhasil diperbarui.');
    }

    public function projects(Request $request): View
    {
        abort_unless($request->user()->hasRole('admin') || $request->user()->can('manage-customer-projects'), 403, 'Akses tidak diizinkan.');

        return view('accounts.projects', $this->management->management_data());
    }

    public function edit(User $user): View
    {
        return view('accounts.edit', $this->management->edit_data($user));
    }

    public function update(manage_account_request $request, User $user): RedirectResponse
    {
        $this->management->update_account($request->user(), $user, $request->validated());

        return redirect()->route($user->is_customer() ? 'accounts.customers' : 'accounts.index')->with('success', 'Akun berhasil diperbarui.');
    }

    public function access(manage_project_access_request $request): RedirectResponse
    {
        $this->management->save_access($request->user(), $request->validated());

        return redirect()->route('accounts.projects')->with('success', 'Akses proyek berhasil diperbarui. Pelanggan perlu login kembali.');
    }

    public function publish(publish_document_request $request): RedirectResponse
    {
        $this->management->publish($request->user(), $request->validated());

        return redirect()->route('accounts.projects')->with('success', 'Salinan dokumen diterbitkan untuk persetujuan pelanggan.');
    }

    public function respond(respond_feedback_request $request, customer_feedback $feedback): RedirectResponse
    {
        $this->management->respond($request->user(), $feedback, $request->validated());

        return redirect()->route('accounts.projects')->with('success', 'Tanggapan berhasil dicatat.');
    }

    public function reassign(publish_document_request $request, customer_document $document): RedirectResponse
    {
        $this->management->reassign($request->user(), $document, $request->validated('approver_id'));

        return redirect()->route('accounts.projects')->with('success', 'Pemberi persetujuan berhasil dialihkan.');
    }
}
