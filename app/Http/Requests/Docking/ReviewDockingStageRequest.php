<?php

namespace App\Http\Requests\Docking;

use Illuminate\Foundation\Http\FormRequest;

class ReviewDockingStageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'decision' => 'required|in:approve,reject',
            'return_to_queue' => 'nullable|boolean',
            'notes' => 'required_if:decision,reject|nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'decision.required' => 'Keputusan wajib dipilih.',
            'decision.in' => 'Keputusan tidak valid.',
            'notes.required_if' => 'Alasan penolakan wajib diisi.',
            'notes.string' => 'Catatan harus berupa teks.',
            'notes.max' => 'Catatan maksimal 2000 karakter.',
        ];
    }
}
