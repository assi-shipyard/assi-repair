<?php

declare(strict_types=1);

namespace App\Http\Requests\Docking;

use Illuminate\Foundation\Http\FormRequest;

class SaveDockingSpaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,maintenance,inactive'],
            'max_draft' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
            'max_tonnage' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'max_breadth' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
            'max_capacity' => ['required', 'integer', 'min:1', 'max:2147483647'],
            'max_length' => ['nullable', 'numeric', 'gt:0', 'max:99999999.99'],
            'max_width' => ['nullable', 'numeric', 'gt:0', 'max:99999999.99'],
            'max_weight' => ['nullable', 'numeric', 'gt:0', 'max:9999999999.99'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama docking space',
            'location' => 'lokasi',
            'status' => 'status',
            'max_draft' => 'sarat maksimum',
            'max_tonnage' => 'tonase maksimum',
            'max_breadth' => 'lebar kapal maksimum',
            'max_capacity' => 'kapasitas kapal',
            'max_length' => 'panjang maksimum',
            'max_width' => 'lebar maksimum',
            'max_weight' => 'berat maksimum',
            'notes' => 'catatan',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'numeric' => ':attribute harus berupa angka.',
            'integer' => ':attribute harus berupa bilangan bulat.',
            'gt' => ':attribute harus lebih besar dari :value.',
            'min' => ':attribute minimal :min.',
            'max.string' => ':attribute maksimal :max karakter.',
            'max.numeric' => ':attribute maksimal :max.',
            'in' => ':attribute tidak valid.',
        ];
    }
}
