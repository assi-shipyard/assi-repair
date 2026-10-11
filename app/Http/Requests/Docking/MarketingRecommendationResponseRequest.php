<?php

namespace App\Http\Requests\Docking;

use Illuminate\Foundation\Http\FormRequest;

class MarketingRecommendationResponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'response' => 'required|in:accept,decline',
            'notes' => 'required_if:response,decline|nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'response.required' => 'Tanggapan wajib dipilih.',
            'response.in' => 'Tanggapan tidak valid.',
            'notes.required_if' => 'Alasan keberatan customer wajib diisi.',
            'notes.string' => 'Catatan harus berupa teks.',
            'notes.max' => 'Catatan maksimal 2000 karakter.',
        ];
    }
}
