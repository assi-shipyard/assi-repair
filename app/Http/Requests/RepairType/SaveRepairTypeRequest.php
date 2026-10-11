<?php

declare(strict_types=1);

namespace App\Http\Requests\RepairType;

use App\Models\RepairType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveRepairTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper(trim((string) $this->input('code'))),
            'standard_price' => str_replace(',', '', (string) $this->input('standard_price')) ?: null,
        ]);
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required', 'string', 'max:30', 'regex:/^[A-Z0-9._-]+$/',
                Rule::unique('repair_types', 'code')->ignore($this->route('repair_type')?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(array_keys(RepairType::CATEGORIES))],
            'unit' => ['required', 'string', 'max:30'],
            'standard_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999999999'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode wajib diisi.',
            'code.regex' => 'Kode hanya boleh berisi huruf kapital, angka, titik, garis bawah, dan strip.',
            'code.unique' => 'Kode sudah digunakan.',
            'code.max' => 'Kode maksimal 30 karakter.',
            'name.required' => 'Nama jenis reparasi wajib diisi.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'category.required' => 'Kategori wajib dipilih.',
            'category.in' => 'Kategori tidak valid.',
            'unit.required' => 'Satuan wajib diisi.',
            'unit.max' => 'Satuan maksimal 30 karakter.',
            'standard_price.numeric' => 'Harga standar harus berupa angka.',
            'standard_price.min' => 'Harga standar tidak boleh negatif.',
            'description.max' => 'Deskripsi maksimal 2000 karakter.',
        ];
    }
}
