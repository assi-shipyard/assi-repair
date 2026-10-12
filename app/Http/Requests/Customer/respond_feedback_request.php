<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class respond_feedback_request extends FormRequest
{
    public function authorize(): bool
    {
        return ! $this->user()?->is_customer()
            && ($this->user()?->hasRole('admin') || $this->user()?->can('manage-customer-projects'));
    }

    public function rules(): array
    {
        return [
            'response' => ['required', 'string', 'max:4000'],
            'status' => ['required', 'in:in_progress,resolved'],
        ];
    }

    public function messages(): array
    {
        return [
            'response.required' => 'Tanggapan wajib diisi.',
            'response.string' => 'Tanggapan harus berupa teks.',
            'response.max' => 'Tanggapan maksimal 4.000 karakter.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status tidak valid.',
        ];
    }
}
