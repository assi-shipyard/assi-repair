<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function login(): View|RedirectResponse
    {
        $authenticated_redirect = $this->redirect_if_authenticated();
        if ($authenticated_redirect instanceof RedirectResponse) {
            return $authenticated_redirect;
        }

        return view('login.index');
    }

    public function admin_login(): View|RedirectResponse
    {
        $authenticated_redirect = $this->redirect_if_authenticated();
        if ($authenticated_redirect instanceof RedirectResponse) {
            return $authenticated_redirect;
        }

        return view('login.admin');
    }

    public function login_process(Request $request): RedirectResponse
    {
        $authenticated_redirect = $this->redirect_if_authenticated();
        if ($authenticated_redirect instanceof RedirectResponse) {
            return $authenticated_redirect;
        }

        $credentials = $request->only('employee_id', 'password');
        $remember = $request->has('remember');
        $validator = Validator::make(
            $credentials,
            [
                'employee_id' => 'required|string|min:9|max:9',
                'password' => 'required|string',
            ],
            [
                'employee_id.required' => 'Nomor Induk Karyawan (NIK) dibutuhkan.',
                'employee_id.min' => 'Nomor Induk Karyawan (NIK) harus 9 karakter.',
                'employee_id.max' => 'Nomor Induk Karyawan (NIK) harus 9 karakter.',
                'password.required' => 'Password dibutuhkan.',
            ],
        );
        if ($validator->fails()) {
            return $this->redirect_back_with_validation_error($validator);
        }

        if (Auth::attempt(['employee_id' => $credentials['employee_id'], 'password' => $credentials['password']], $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();
            if (! $user instanceof User) {
                $this->logout_and_invalidate($request);

                return back()->with('error', 'Terjadi kesalahan autentikasi. Silakan login ulang.');
            }

            if ($user->hasRole('admin')) {
                $this->logout_and_invalidate($request);

                return redirect()->route('admin.login')->with('error', 'Akun admin harus login melalui halaman admin.');
            }

            $this->set_employee_session($request, $user);

            return redirect()->route('dashboard');
        }

        return back()->with('error', 'Login gagal. Pastikan NIK dan password benar.');
    }

    public function admin_login_process(Request $request): RedirectResponse
    {
        $authenticated_redirect = $this->redirect_if_authenticated();
        if ($authenticated_redirect instanceof RedirectResponse) {
            return $authenticated_redirect;
        }

        $credentials = $request->only('username', 'password');
        $remember = $request->has('remember');
        $validator = Validator::make(
            $credentials,
            [
                'username' => 'required|string|max:64',
                'password' => 'required|string',
            ],
            [
                'username.required' => 'Username admin wajib diisi.',
                'password.required' => 'Password wajib diisi.',
            ],
        );
        if ($validator->fails()) {
            return $this->redirect_back_with_validation_error($validator);
        }

        if (Auth::attempt(['employee_id' => $credentials['username'], 'password' => $credentials['password']], $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();
            if (! $user instanceof User) {
                $this->logout_and_invalidate($request);

                return back()->with('error', 'Terjadi kesalahan autentikasi. Silakan login ulang.');
            }

            if (! $user->hasRole('admin')) {
                $this->logout_and_invalidate($request);

                return back()->with('error', 'Akun ini bukan akun admin.');
            }

            $this->set_employee_session($request, $user);

            return redirect()->route('dashboard');
        }

        return back()->with('error', 'Login admin gagal. Pastikan username dan password benar.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $was_admin = Auth::user()?->hasRole('admin') === true;

        $this->logout_and_invalidate($request);

        return redirect()->route($was_admin ? 'admin.login' : 'login');
    }

    private function redirect_if_authenticated(): ?RedirectResponse
    {
        if (! Auth::check()) {
            return null;
        }

        return redirect()->route('dashboard');
    }

    private function redirect_back_with_validation_error(\Illuminate\Contracts\Validation\Validator $validator): RedirectResponse
    {
        return back()->withErrors($validator)->withInput();
    }

    private function logout_and_invalidate(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    private function set_employee_session(Request $request, User $user): void
    {
        $employee = Employee::with('position')
            ->where('employee_id', $user->employee_id)
            ->first();

        $request->session()->put([
            'employee_id' => $employee?->employee_id ?? $user->employee_id,
            'employee_name' => $employee?->name ?? ($user->hasRole('admin') ? config('seeding.admin.name') : null),
            'employee_position' => $employee?->position?->name,
        ]);
    }
}
