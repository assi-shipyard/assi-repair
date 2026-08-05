<?php

namespace App\Http\Requests\Docking;

use Illuminate\Foundation\Http\FormRequest;

class StartDockingOccupancyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'docking_space_id' => 'nullable|exists:docking_spaces,id',
            'docked_at' => 'required|date',
            'estimated_undock_at' => 'nullable|date|after_or_equal:docked_at',
            'remarks' => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'docking_space_id.exists' => 'Docking space tidak valid.',
            'docked_at.required' => 'Waktu masuk dock wajib diisi.',
            'docked_at.date' => 'Waktu masuk dock tidak valid.',
            'estimated_undock_at.date' => 'Estimasi keluar dock tidak valid.',
            'estimated_undock_at.after_or_equal' => 'Estimasi keluar dock tidak boleh lebih awal dari waktu masuk dock.',
            'remarks.string' => 'Catatan okupansi harus berupa teks.',
            'remarks.max' => 'Catatan okupansi maksimal 2000 karakter.',
        ];
    }
}
