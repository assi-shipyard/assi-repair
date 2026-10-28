<?php

declare(strict_types=1);

namespace App\Http\Requests\Project;

use App\Models\project_contract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class save_project_contract_request extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['legal', 'admin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'contract_number' => ['required', 'string', 'max:100'],
            'status' => ['required', 'string', Rule::in(array_keys(project_contract::STATUS_LABELS))],
            'signing_due_date' => ['nullable', 'date_format:Y-m-d'],
            'document' => ['nullable', File::types(['pdf', 'doc', 'docx'])->max(20 * 1024), 'extensions:pdf,doc,docx'],
        ];
    }

    public function messages(): array
    {
        return [
            'contract_number.required' => 'Nomor kontrak wajib diisi.',
            'contract_number.string' => 'Nomor kontrak harus berupa teks.',
            'contract_number.max' => 'Nomor kontrak maksimal 100 karakter.',
            'status.required' => 'Status kontrak wajib dipilih.',
            'status.string' => 'Status kontrak tidak valid.',
            'status.in' => 'Status kontrak tidak valid.',
            'signing_due_date.date_format' => 'Target penandatanganan harus berupa tanggal yang valid.',
            'document.file' => 'Dokumen kontrak harus berupa berkas.',
            'document.mimes' => 'Dokumen kontrak harus berformat PDF, DOC, atau DOCX.',
            'document.extensions' => 'Ekstensi dokumen kontrak harus PDF, DOC, atau DOCX.',
            'document.max' => 'Ukuran dokumen kontrak maksimal 20 MB.',
            'document.uploaded' => 'Dokumen kontrak gagal diunggah. Periksa ukuran berkas dan coba lagi.',
        ];
    }
}
