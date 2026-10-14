<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublishDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ! $this->user()?->is_customer()
            && ($this->user()?->hasRole('admin') || $this->user()?->can('manage-customer-projects'));
    }

    public function rules(): array
    {
        return [
            'document_id' => $this->routeIs('accounts.reassign') ? ['prohibited'] : ['required', 'uuid', Rule::exists('project_job_documents', 'unique_id')],
            'approver_id' => ['required', 'uuid', Rule::exists('users', 'unique_id')->where('account_type', 'customer')->where('is_active', true)],
        ];
    }

    public function messages(): array
    {
        return [
            'document_id.required' => 'Dokumen wajib dipilih.',
            'document_id.uuid' => 'Dokumen tidak valid.',
            'document_id.exists' => 'Dokumen tidak ditemukan.',
            'document_id.prohibited' => 'Dokumen tidak dapat diganti.',
            'approver_id.required' => 'Pemberi persetujuan wajib dipilih.',
            'approver_id.uuid' => 'Pemberi persetujuan tidak valid.',
            'approver_id.exists' => 'Akun pelanggan aktif tidak ditemukan.',
        ];
    }
}
