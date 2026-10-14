<?php

declare(strict_types=1);

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class SaveDocumentJobMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['material_name' => trim((string) $this->input('material_name'))]);
    }

    public function rules(): array
    {
        return [
            'material_name' => ['required', 'string', 'max:191'],
            'price_book_item_id' => ['nullable', 'uuid'],
            'volume' => ['nullable', 'numeric', 'min:0', 'max:99999999999999'],
            'unit' => ['nullable', 'string', 'max:30'],
            'unit_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999999999'],
            'dimension' => ['nullable', 'string', 'max:191'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'material_name.required' => 'Nama material wajib diisi.',
            'material_name.max' => 'Nama material maksimal 191 karakter.',
            'price_book_item_id.uuid' => 'Item kamus harga tidak valid.',
            'volume.numeric' => 'Volume harus berupa angka.',
            'volume.min' => 'Volume tidak boleh negatif.',
            'unit.max' => 'Satuan maksimal 30 karakter.',
            'unit_price.numeric' => 'Harga satuan harus berupa angka.',
            'unit_price.min' => 'Harga satuan tidak boleh negatif.',
            'unit_price.max' => 'Harga satuan terlalu besar.',
            'dimension.max' => 'Dimensi maksimal 191 karakter.',
            'notes.max' => 'Catatan maksimal 2000 karakter.',
        ];
    }
}
