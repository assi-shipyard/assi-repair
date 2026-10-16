<?php

declare(strict_types=1);

namespace App\Http\Requests\Docking;

use Illuminate\Foundation\Http\FormRequest;

class RecordShipArrivalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'arrived_at' => 'required|date|before_or_equal:now',
            'arrival_notes' => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'arrived_at.required' => 'Waktu kedatangan kapal wajib diisi.',
            'arrived_at.date' => 'Waktu kedatangan kapal tidak valid.',
            'arrived_at.before_or_equal' => 'Waktu kedatangan tidak boleh melebihi waktu sekarang.',
            'arrival_notes.string' => 'Catatan kedatangan harus berupa teks.',
            'arrival_notes.max' => 'Catatan kedatangan maksimal 2000 karakter.',
        ];
    }
}
