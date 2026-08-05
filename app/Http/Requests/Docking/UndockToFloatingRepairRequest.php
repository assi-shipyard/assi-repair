<?php

namespace App\Http\Requests\Docking;

use Illuminate\Foundation\Http\FormRequest;

class UndockToFloatingRepairRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'undocked_at' => 'required|date',
            'floating_started_at' => 'nullable|date|after_or_equal:undocked_at',
            'notes' => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'undocked_at.required' => 'Waktu undock wajib diisi.',
            'undocked_at.date' => 'Waktu undock tidak valid.',
            'floating_started_at.date' => 'Waktu mulai floating repair tidak valid.',
            'floating_started_at.after_or_equal' => 'Waktu mulai floating repair tidak boleh lebih awal dari waktu undock.',
            'notes.string' => 'Catatan floating repair harus berupa teks.',
            'notes.max' => 'Catatan floating repair maksimal 2000 karakter.',
        ];
    }
}
