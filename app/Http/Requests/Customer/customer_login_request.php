<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class customer_login_request extends FormRequest
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
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc', 'max:64'],
            'password' => ['required', 'string', 'max:1024'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 64 karakter.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.max' => 'Kata sandi terlalu panjang.',
            'password.string' => 'Kata sandi harus berupa teks.',
        ];
    }
}
