<?php

namespace App\Http\Requests\Docking;

use Illuminate\Foundation\Http\FormRequest;

class EvaluateDockingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'docking_space_ids' => 'nullable|array',
            'docking_space_ids.*' => 'integer|exists:docking_spaces,id',
            'only_active_spaces' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'docking_space_ids.array' => 'Daftar docking space tidak valid.',
            'docking_space_ids.*.exists' => 'Ada docking space yang tidak ditemukan.',
            'only_active_spaces.boolean' => 'Parameter filter status docking space tidak valid.',
        ];
    }
}
