<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class CustomerDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_customer() === true;
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', 'in:approved,rejected,changes_requested'],
            'comment' => ['required_unless:decision,approved', 'nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'decision.required' => 'Keputusan wajib dipilih.',
            'decision.in' => 'Keputusan tidak valid.',
            'comment.required_unless' => 'Alasan penolakan atau permintaan perubahan wajib diisi.',
            'comment.max' => 'Komentar maksimal 2.000 karakter.',
            'comment.string' => 'Komentar harus berupa teks.',
        ];
    }
}
