<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class manage_project_access_request extends FormRequest
{
    public function authorize(): bool
    {
        return ! $this->user()?->is_customer() && $this->user()?->hasRole('admin') === true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'uuid', Rule::exists('users', 'unique_id')->where('account_type', 'customer')],
            'project_id' => ['required', 'uuid', Rule::exists('projects', 'unique_id')],
            'is_active' => ['required', 'boolean'],
            'view_prices' => ['required', 'boolean'],
            'approve_initial_boq' => ['required', 'boolean'],
            'approve_satisfaction_notes' => ['required', 'boolean'],
            'approve_final_boq' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            '*.required' => 'Semua pilihan akses wajib diisi.',
            '*.boolean' => 'Pilihan akses tidak valid.',
            '*.uuid' => 'Identitas akun atau proyek tidak valid.',
            '*.exists' => 'Akun pelanggan atau proyek tidak ditemukan.',
        ];
    }
}
