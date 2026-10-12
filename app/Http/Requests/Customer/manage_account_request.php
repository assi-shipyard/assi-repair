<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class manage_account_request extends FormRequest
{
    // Laravel's FormRequest hook requires this framework-defined method name.
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => strtolower(trim($this->input('email')))]);
        }
    }

    public function authorize(): bool
    {
        return ! $this->user()?->is_customer() && $this->user()?->hasRole('admin') === true;
    }

    public function rules(): array
    {
        if ($this->routeIs('accounts.status')) {
            return ['is_active' => ['required', 'boolean']];
        }

        $is_update = $this->routeIs('accounts.update');
        $user = $is_update ? $this->route('user') : null;
        $account_type = $user?->account_type ?? $this->input('account_type');

        return [
            'account_type' => $is_update ? ['prohibited'] : ['required', 'in:admin,customer'],
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                Rule::requiredIf($account_type === 'admin'), Rule::excludeIf($account_type !== 'admin'), 'string', 'max:64',
                Rule::unique('users', 'username')->ignore($user?->id), Rule::unique('users', 'employee_id')->ignore($user?->id),
            ],
            'email' => ['required', 'email:rfc', 'max:64', Rule::unique('users', 'email')->ignore($user?->id)],
            'company_id' => [Rule::requiredIf($account_type === 'customer'), Rule::excludeIf($account_type !== 'customer'), 'uuid', Rule::exists('companies', 'unique_id')],
            'password' => [$is_update ? 'nullable' : 'required', 'confirmed', 'max:128', Password::min(12)->letters()->numbers()->mixedCase()],
        ];
    }

    public function messages(): array
    {
        return [
            'account_type.required' => 'Jenis akun wajib dipilih.',
            'account_type.in' => 'Jenis akun tidak valid.',
            'account_type.prohibited' => 'Jenis akun tidak dapat diubah.',
            '*.string' => 'Kolom ini harus berupa teks.',
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'username.required_if' => 'Username admin wajib diisi.',
            'username.required' => 'Username admin wajib diisi.',
            'username.unique' => 'Username sudah digunakan.',
            'username.max' => 'Username maksimal 64 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 64 karakter.',
            'email.unique' => 'Email sudah digunakan.',
            'company_id.required_if' => 'Perusahaan pelanggan wajib dipilih.',
            'company_id.required' => 'Perusahaan pelanggan wajib dipilih.',
            'company_id.uuid' => 'Perusahaan tidak valid.',
            'company_id.exists' => 'Perusahaan tidak ditemukan.',
            'password.required' => 'Kata sandi awal wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sesuai.',
            'password.min' => 'Kata sandi minimal 12 karakter.',
            'password.max' => 'Kata sandi maksimal 128 karakter.',
            'password.letters' => 'Kata sandi harus mengandung huruf.',
            'password.numbers' => 'Kata sandi harus mengandung angka.',
            'password.mixed' => 'Kata sandi harus mengandung huruf besar dan kecil.',
            'password.password.letters' => 'Kata sandi harus mengandung huruf.',
            'password.password.numbers' => 'Kata sandi harus mengandung angka.',
            'password.password.mixed' => 'Kata sandi harus mengandung huruf besar dan kecil.',
            'is_active.required' => 'Status akun wajib dipilih.',
            'is_active.boolean' => 'Status akun tidak valid.',
        ];
    }
}
