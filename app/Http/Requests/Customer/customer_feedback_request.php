<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class customer_feedback_request extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_customer() === true;
    }

    public function rules(): array
    {
        return [
            'kind' => ['required', 'in:satisfaction,complaint'],
            'rating' => ['required_if:kind,satisfaction', 'exclude_unless:kind,satisfaction', 'integer', 'between:1,5'],
            'message' => ['required', 'string', 'max:4000'],
        ];
    }

    public function messages(): array
    {
        return [
            'kind.required' => 'Jenis masukan wajib dipilih.',
            'kind.in' => 'Jenis masukan tidak valid.',
            'rating.required_if' => 'Nilai kepuasan wajib dipilih.',
            'rating.integer' => 'Nilai kepuasan harus berupa angka.',
            'rating.between' => 'Nilai kepuasan harus antara 1 dan 5.',
            'message.required' => 'Pesan wajib diisi.',
            'message.string' => 'Pesan harus berupa teks.',
            'message.max' => 'Pesan maksimal 4.000 karakter.',
        ];
    }
}
