<?php

declare(strict_types=1);

namespace App\Http\Requests\Docking;

use Illuminate\Foundation\Http\FormRequest;

class CancelDockingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'cancellation_reason' => 'required|string|min:5|max:2000',
            'cancel_request_id' => 'nullable|uuid',
        ];
    }

    public function messages(): array
    {
        return [
            'cancellation_reason.required' => 'Alasan pembatalan wajib diisi.',
            'cancellation_reason.string' => 'Alasan pembatalan harus berupa teks.',
            'cancellation_reason.min' => 'Alasan pembatalan minimal 5 karakter.',
            'cancellation_reason.max' => 'Alasan pembatalan maksimal 2000 karakter.',
        ];
    }
}
