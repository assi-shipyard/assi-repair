<?php

declare(strict_types=1);

namespace App\Http\Requests\Docking;

use Illuminate\Foundation\Http\FormRequest;

class UndockShipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'undocked_at' => 'required|date|before_or_equal:now',
            'next_step' => 'required|in:floating,ready_to_depart',
            'floating_started_at' => 'nullable|required_if:next_step,floating|date|after_or_equal:undocked_at',
            'notes' => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'undocked_at.required' => 'Waktu undocking wajib diisi.',
            'undocked_at.date' => 'Waktu undocking tidak valid.',
            'undocked_at.before_or_equal' => 'Waktu undocking tidak boleh melebihi waktu sekarang.',
            'next_step.required' => 'Tahap setelah undocking wajib dipilih.',
            'next_step.in' => 'Tahap setelah undocking tidak valid.',
            'floating_started_at.required_if' => 'Waktu mulai pekerjaan atas air wajib diisi.',
            'floating_started_at.date' => 'Waktu mulai pekerjaan atas air tidak valid.',
            'floating_started_at.after_or_equal' => 'Waktu mulai pekerjaan atas air tidak boleh lebih awal dari waktu undocking.',
            'notes.string' => 'Catatan harus berupa teks.',
            'notes.max' => 'Catatan maksimal 2000 karakter.',
        ];
    }
}
