<?php

namespace App\Http\Requests\Docking;

use Illuminate\Foundation\Http\FormRequest;

class PostponeDockingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'new_start_at' => 'required|date',
            'new_end_at' => 'required|date|after:new_start_at',
            'reason' => 'required|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'new_start_at.required' => 'Tanggal docking baru wajib diisi.',
            'new_start_at.date' => 'Tanggal docking baru tidak valid.',
            'new_end_at.required' => 'Tanggal undocking baru wajib diisi.',
            'new_end_at.date' => 'Tanggal undocking baru tidak valid.',
            'new_end_at.after' => 'Tanggal undocking harus setelah tanggal docking.',
            'reason.required' => 'Alasan penundaan wajib diisi.',
            'reason.string' => 'Alasan harus berupa teks.',
            'reason.max' => 'Alasan maksimal 2000 karakter.',
        ];
    }
}
