<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $employee = $this->current_employee(true);

        return view('usersettings', compact('employee', 'user'));
    }

    public function update_profile(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validation = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_photo' => 'nullable|boolean',
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'profile_photo.image' => 'Foto profil harus berupa gambar.',
            'profile_photo.mimes' => 'Foto profil harus berformat JPG, JPEG, PNG, atau WEBP.',
            'profile_photo.max' => 'Ukuran foto profil maksimal 2 MB.',
        ]);

        if ($validation->fails()) {
            return back()->withErrors($validation)->withInput();
        }

        $employee = $this->current_employee();

        $validated = $validation->validated();

        $user->email = $validated['email'] ?? $user->email;
        $user->save();

        $employee->name = $validated['name'];
        $employee->email = $validated['email'] ?? $employee->email;

        if ($request->boolean('remove_photo') && $employee->profile_photo_path !== null) {
            Storage::disk('local')->delete($employee->profile_photo_path);
            $employee->profile_photo_path = null;
        }

        if ($request->hasFile('profile_photo')) {
            if ($employee->profile_photo_path !== null) {
                Storage::disk('local')->delete($employee->profile_photo_path);
            }

            $employee->profile_photo_path = $request->file('profile_photo')->store('employee-photos', 'local');
        }

        $employee->save();

        return redirect()->route('settings.index')->with('success', 'Pengaturan profil berhasil diperbarui.');
    }

    public function update_password(Request $request): RedirectResponse
    {
        $validation = Validator::make($request->all(), [
            'password' => 'nullable|string|min:8|confirmed',
        ], [
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        if ($validation->fails()) {
            return back()->withErrors($validation)->withInput();
        }

        $validated = $validation->validated();

        if (empty($validated['password'] ?? null)) {
            return back()->with('error', 'Kata sandi baru tidak boleh kosong.');
        }

        $user = Auth::user();
        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->route('settings.index')->with('success', 'Kata sandi berhasil diperbarui.');
    }

    public function delete_photo(): RedirectResponse
    {
        $employee = $this->current_employee();

        if ($employee->profile_photo_path !== null) {
            Storage::disk('local')->delete($employee->profile_photo_path);
            $employee->profile_photo_path = null;
            $employee->save();
        }

        return redirect()->route('settings.index')->with('success', 'Foto profil berhasil dihapus.');
    }

    public function photo(): RedirectResponse|StreamedResponse
    {
        $employee = $this->current_employee();

        if ($employee->profile_photo_path === null || ! Storage::disk('local')->exists($employee->profile_photo_path)) {
            abort(404);
        }

        return Storage::disk('local')->response($employee->profile_photo_path);
    }

    private function current_employee(bool $with_position = false): ?Employee
    {
        $employee_id = Auth::user()?->employee_id;
        $query = Employee::query()->where('employee_id', $employee_id);

        if ($with_position) {
            return $query->with('position')->first();
        }

        return $query->firstOrFail();
    }
}
