<?php

declare(strict_types=1);

namespace App\Http\Requests\PriceBook;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavePriceBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        $price_book = $this->route('price_book');

        return [
            'code' => ['required', 'string', 'max:30', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('price_books')->ignore($price_book)],
            'name' => ['required', 'string', 'max:255'],
            'book_type' => ['required', Rule::in(['reference', 'customer'])],
            'reference_slot' => [
                'exclude_unless:book_type,reference', 'required', 'integer', Rule::in([1]),
                Rule::unique('price_books')->ignore($price_book),
            ],
            'company_uuid' => ['exclude_unless:book_type,customer', 'nullable', 'uuid', Rule::exists('companies', 'unique_id')],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'max' => ':attribute maksimal :max karakter.',
            'code.regex' => 'Kode hanya boleh berisi huruf, angka, titik, garis bawah, dan strip.',
            'code.unique' => 'Kode buku sudah digunakan.',
            'reference_slot.unique' => 'Buku utama sudah tersedia. Ubah buku tersebut menjadi buku pelanggan terlebih dahulu jika ingin menggantinya.',
            'integer' => ':attribute tidak valid.',
            'in' => ':attribute tidak valid.',
            'uuid' => ':attribute tidak valid.',
            'exists' => ':attribute tidak ditemukan.',
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => 'Kode buku', 'name' => 'Nama buku', 'book_type' => 'Jenis buku',
            'reference_slot' => 'Buku utama', 'company_uuid' => 'Pelanggan', 'description' => 'Keterangan',
        ];
    }
}
