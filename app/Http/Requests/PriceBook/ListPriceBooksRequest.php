<?php

declare(strict_types=1);

namespace App\Http\Requests\PriceBook;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListPriceBooksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'book_type' => ['nullable', Rule::in(['reference', 'customer'])],
            'company_uuid' => ['nullable', 'uuid', Rule::exists('companies', 'unique_id')],
            'page' => ['nullable', 'integer', 'min:1', 'max:1000000'],
        ];
    }

    public function messages(): array
    {
        return [
            'search.string' => 'Pencarian harus berupa teks.',
            'search.max' => 'Pencarian maksimal 100 karakter.',
            'book_type.in' => 'Jenis buku tidak valid.',
            'company_uuid.uuid' => 'Pelanggan tidak valid.',
            'company_uuid.exists' => 'Pelanggan tidak ditemukan.',
            'page.integer' => 'Halaman harus berupa angka bulat.',
            'page.min' => 'Halaman minimal 1.',
            'page.max' => 'Halaman melebihi batas yang diizinkan.',
        ];
    }
}
