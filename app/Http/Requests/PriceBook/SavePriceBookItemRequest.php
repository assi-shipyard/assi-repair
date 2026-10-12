<?php

declare(strict_types=1);

namespace App\Http\Requests\PriceBook;

use App\Models\PriceBookItem;
use App\Services\boq_rule_service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavePriceBookItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    // A rule fixes the billing unit, and pipe steps replace a quantity minimum.
    protected function prepareForValidation(): void
    {
        $rule = $this->input('calculation_rule') ?: null;
        $this->merge(['calculation_rule' => $rule]);

        if ($rule !== null && isset(boq_rule_service::RULES[$rule])) {
            $this->merge(['unit' => boq_rule_service::RULES[$rule]['unit']]);
        }

        if ($rule === 'pipe') {
            $this->merge(['has_minimum_charge' => '0']);
        }
    }

    public function rules(): array
    {
        return [
            'group' => ['required', Rule::in(array_keys(PriceBookItem::GROUPS))],
            'code' => [
                'required', 'string', 'max:30', 'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique('price_book_items')->where('price_book_id', $this->route('price_book')->id)
                    ->where('group', $this->input('group'))->where('unit', $this->input('unit'))
                    ->ignore($this->route('item')),
            ],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:30'],
            'calculation_rule' => ['nullable', Rule::in(array_keys(boq_rule_service::RULES))],
            'unit_price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:999999999999.99'],
            'has_minimum_charge' => ['required', 'boolean'],
            'minimum_quantity' => ['exclude_unless:has_minimum_charge,1', 'required', 'numeric', 'decimal:0,3', 'gt:0', 'max:999999999.999'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'max' => ':attribute melebihi batas :max.',
            'code.regex' => 'Kode hanya boleh berisi huruf, angka, titik, garis bawah, dan strip.',
            'code.unique' => 'Kode dengan satuan tersebut sudah digunakan dalam kelompok dan buku ini.',
            'in' => ':attribute tidak valid.',
            'numeric' => ':attribute harus berupa angka.',
            'decimal' => ':attribute memiliki terlalu banyak angka desimal.',
            'min' => ':attribute tidak boleh negatif.',
            'gt' => ':attribute harus lebih besar dari nol.',
            'boolean' => ':attribute tidak valid.',
        ];
    }

    public function attributes(): array
    {
        return [
            'group' => 'Kelompok', 'code' => 'Kode', 'name' => 'Nama', 'unit' => 'Satuan',
            'unit_price' => 'Harga satuan', 'has_minimum_charge' => 'Batas minimum',
            'minimum_quantity' => 'Kuantitas minimum', 'description' => 'Keterangan',
            'calculation_rule' => 'Aturan perhitungan',
        ];
    }
}
