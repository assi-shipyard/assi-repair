<?php

namespace App\Http\Requests\Docking;

use Illuminate\Foundation\Http\FormRequest;

class CompleteFloatingRepairRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'floating_completed_at' => 'required|date',
            'notes' => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'floating_completed_at.required' => 'Waktu selesai floating repair wajib diisi.',
            'floating_completed_at.date' => 'Waktu selesai floating repair tidak valid.',
            'notes.string' => 'Catatan penyelesaian harus berupa teks.',
            'notes.max' => 'Catatan penyelesaian maksimal 2000 karakter.',
        ];
    }
}
