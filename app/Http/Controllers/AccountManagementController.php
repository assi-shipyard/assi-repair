<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\AccountDirectoryRequest;
use App\Http\Requests\Customer\ManageAccountRequest;
use App\Http\Requests\Customer\ManageProjectAccessRequest;
use App\Http\Requests\Customer\PublishDocumentRequest;
use App\Http\Requests\Customer\RespondFeedbackRequest;
use App\Models\CustomerDocument;
use App\Models\CustomerFeedback;
use App\Models\User;
use App\Services\customer_management_service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccountManagementController extends Controller
{
    public function __construct(private readonly customer_management_service $management) {}

    public function index(AccountDirectoryRequest $request): View
    {
        return view('accounts.index', $this->management->account_data(User::TYPE_ADMIN, $request->validated()));
    }

    public function customers(AccountDirectoryRequest $request): View
    {
        return view('accounts.index', $this->management->account_data(User::TYPE_CUSTOMER, $request->validated()));
    }

    public function photo(User $user): StreamedResponse
    {
        return $this->management->photo($user);
    }

    public function store(ManageAccountRequest $request): RedirectResponse
    {
        $this->management->create_account($request->user(), $request->validated());

        return redirect()->route($request->validated('account_type') === User::TYPE_CUSTOMER ? 'accounts.customers' : 'accounts.index')->with('success', 'Akun berhasil dibuat. Sampaikan kata sandi awal melalui saluran aman.');
    }

    public function status(ManageAccountRequest $request, User $user): RedirectResponse
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

    public function update(ManageAccountRequest $request, User $user): RedirectResponse
    {
        $this->management->update_account($request->user(), $user, $request->validated());

        return redirect()->route($user->is_customer() ? 'accounts.customers' : 'accounts.index')->with('success', 'Akun berhasil diperbarui.');
    }

    public function access(ManageProjectAccessRequest $request): RedirectResponse
    {
        $this->management->save_access($request->user(), $request->validated());

        return redirect()->route('accounts.projects')->with('success', 'Akses proyek berhasil diperbarui. Pelanggan perlu login kembali.');
    }

    public function publish(PublishDocumentRequest $request): RedirectResponse
    {
        $this->management->publish($request->user(), $request->validated());

        return redirect()->route('accounts.projects')->with('success', 'Salinan dokumen diterbitkan untuk persetujuan pelanggan.');
    }

    public function respond(RespondFeedbackRequest $request, CustomerFeedback $feedback): RedirectResponse
    {
        $this->management->respond($request->user(), $feedback, $request->validated());

        return redirect()->route('accounts.projects')->with('success', 'Tanggapan berhasil dicatat.');
    }

    public function reassign(PublishDocumentRequest $request, CustomerDocument $document): RedirectResponse
    {
        $this->management->reassign($request->user(), $document, $request->validated('approver_id'));

        return redirect()->route('accounts.projects')->with('success', 'Pemberi persetujuan berhasil dialihkan.');
    }
}
