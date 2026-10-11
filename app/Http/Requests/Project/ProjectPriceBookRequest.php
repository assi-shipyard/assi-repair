<?php

declare(strict_types=1);

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectPriceBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'price_book_uuid' => ['sometimes', 'nullable', 'uuid', Rule::exists('price_books', 'unique_id')],
        ];
    }

    public function messages(): array
    {
        return [
            'price_book_uuid.uuid' => 'Buku Kamus Harga tidak valid.',
            'price_book_uuid.exists' => 'Buku Kamus Harga tidak ditemukan.',
        ];
    }
}
