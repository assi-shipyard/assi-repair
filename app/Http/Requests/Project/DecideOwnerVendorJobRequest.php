<?php

declare(strict_types=1);

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DecideOwnerVendorJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && ($user->hasRole('admin') || $user->can('approve-owner-vendor-job'));
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in(['approved', 'rejected'])],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'decision.required' => 'Keputusan persetujuan wajib dipilih.',
            'decision.in' => 'Keputusan persetujuan tidak valid.',
            'note.max' => 'Catatan keputusan maksimal 1000 karakter.',
        ];
    }
}
