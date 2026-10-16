<?php

declare(strict_types=1);

namespace App\Http\Requests\Docking;

use Illuminate\Foundation\Http\FormRequest;

class DepartShipyardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'departed_at' => 'required|date|before_or_equal:now',
            'departure_notes' => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'departed_at.required' => 'Waktu keluar galangan wajib diisi.',
            'departed_at.date' => 'Waktu keluar galangan tidak valid.',
            'departed_at.before_or_equal' => 'Waktu keluar galangan tidak boleh melebihi waktu sekarang.',
            'departure_notes.string' => 'Catatan keberangkatan harus berupa teks.',
            'departure_notes.max' => 'Catatan keberangkatan maksimal 2000 karakter.',
        ];
    }
}
