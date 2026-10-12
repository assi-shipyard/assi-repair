<?php

declare(strict_types=1);

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class save_minimum_override_request extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['reason' => trim((string) $this->input('reason'))]);
    }

    public function rules(): array
    {
        return [
            'price_book_item_id' => ['required', 'uuid'],
            'action' => ['required', Rule::in(['set', 'clear'])],
            'minimum_quantity' => ['exclude_unless:action,set', 'required', 'numeric', 'min:0', 'max:999999999'],
            'reason' => ['exclude_unless:action,set', 'required', 'string', 'min:5', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'price_book_item_id.required' => 'Item kamus harga wajib dipilih.',
            'price_book_item_id.uuid' => 'Item kamus harga tidak valid.',
            'action.*' => 'Aksi tidak valid.',
            'minimum_quantity.required' => 'Minimum hasil negosiasi wajib diisi.',
            'minimum_quantity.numeric' => 'Minimum harus berupa angka.',
            'minimum_quantity.min' => 'Minimum tidak boleh negatif. Isi 0 untuk meniadakan minimum.',
            'minimum_quantity.max' => 'Minimum terlalu besar.',
            'reason.required' => 'Alasan negosiasi wajib diisi.',
            'reason.min' => 'Alasan negosiasi minimal 5 karakter.',
            'reason.max' => 'Alasan negosiasi maksimal 500 karakter.',
        ];
    }
}
