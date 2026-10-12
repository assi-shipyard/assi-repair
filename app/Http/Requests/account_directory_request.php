<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class account_directory_request extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && ! $this->user()->is_customer();
    }

    public function rules(): array
    {
        return ['search' => ['nullable', 'string', 'max:100'], 'status' => ['nullable', 'in:active,inactive']];
    }

    public function messages(): array
    {
        return [
            'search.string' => 'Pencarian harus berupa teks.',
            'search.max' => 'Pencarian maksimal 100 karakter.',
            'status.in' => 'Status akun tidak valid.',
        ];
    }
}
