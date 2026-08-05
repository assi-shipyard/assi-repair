<?php

namespace App\Http\Requests\Docking;

use Illuminate\Foundation\Http\FormRequest;

class ReviewProjectDockingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'request_status' => 'required|in:reviewed,approved,rejected,cancelled',
            'approved_docking_space_id' => 'nullable|exists:docking_spaces,id',
            'rejection_reason' => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'request_status.required' => 'Status review permohonan wajib diisi.',
            'request_status.in' => 'Status review permohonan tidak valid.',
            'approved_docking_space_id.exists' => 'Docking space persetujuan tidak valid.',
            'rejection_reason.string' => 'Alasan penolakan harus berupa teks.',
            'rejection_reason.max' => 'Alasan penolakan maksimal 2000 karakter.',
        ];
    }
}
