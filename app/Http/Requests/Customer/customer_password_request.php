<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class customer_password_request extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_customer() === true;
    }

    public function rules(): array
    {
        $rules = ['current_password' => ['required', 'string', 'max:1024', 'current_password:web']];
        if ($this->routeIs('customer.password')) {
            $rules['password'] = ['required', 'confirmed', 'different:current_password', 'max:128', Password::min(12)->letters()->numbers()->mixedCase()];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'current_password.current_password' => 'Kata sandi tidak sesuai.',
            'current_password.max' => 'Kata sandi terlalu panjang.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sesuai.',
            '*.string' => 'Kata sandi harus berupa teks.',
            'password.different' => 'Kata sandi baru harus berbeda dari kata sandi saat ini.',
            'password.min' => 'Kata sandi minimal 12 karakter.',
            'password.max' => 'Kata sandi maksimal 128 karakter.',
            'password.letters' => 'Kata sandi harus mengandung huruf.',
            'password.numbers' => 'Kata sandi harus mengandung angka.',
            'password.mixed' => 'Kata sandi harus mengandung huruf besar dan kecil.',
            'password.password.letters' => 'Kata sandi harus mengandung huruf.',
            'password.password.numbers' => 'Kata sandi harus mengandung angka.',
            'password.password.mixed' => 'Kata sandi harus mengandung huruf besar dan kecil.',
        ];
    }
}
