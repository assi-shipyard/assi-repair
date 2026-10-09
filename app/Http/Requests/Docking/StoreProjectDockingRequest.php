<?php

namespace App\Http\Requests\Docking;

use App\Models\DockingRequestDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreProjectDockingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['nullable', 'required_without:ship_id', 'exists:projects,id'],
            'ship_id' => ['nullable', 'required_without:project_id', 'exists:ships,id'],
            'requested_docking_space_id' => 'nullable|exists:docking_spaces,id',
            'requested_start_at' => 'required|date',
            'requested_end_at' => 'nullable|date|after_or_equal:requested_start_at',
            'request_notes' => 'nullable|string|max:2000',
            'documents' => 'required|array|min:1|max:10',
            'documents.*.type' => ['required', 'in:' . implode(',', array_keys(DockingRequestDocument::TYPE_LABELS))],
            'documents.*.file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,dwg,jpg,jpeg,png|max:20480',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $types = collect($this->input('documents', []))->pluck('type');

                if (! $types->contains('ship_particular')) {
                    $validator->errors()->add('documents', 'Dokumen Ship Particular wajib dilampirkan.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'project_id.required_without' => 'Pilih proyek yang sudah ada atau pilih kapal yang akan diajukan.',
            'project_id.exists' => 'Proyek yang dipilih tidak valid.',
            'ship_id.required_without' => 'Pilih proyek yang sudah ada atau pilih kapal yang akan diajukan.',
            'ship_id.exists' => 'Kapal yang dipilih tidak valid.',
            'requested_docking_space_id.exists' => 'Docking space yang dipilih tidak valid.',
            'requested_start_at.required' => 'Jadwal mulai docking wajib diisi.',
            'requested_start_at.date' => 'Jadwal mulai docking harus berupa tanggal dan waktu yang valid.',
            'requested_end_at.date' => 'Jadwal selesai docking harus berupa tanggal dan waktu yang valid.',
            'requested_end_at.after_or_equal' => 'Jadwal selesai docking tidak boleh lebih awal dari jadwal mulai.',
            'documents.required' => 'Lampirkan minimal satu dokumen kapal.',
            'documents.max' => 'Maksimal 10 dokumen per permohonan.',
            'documents.*.type.required' => 'Jenis dokumen wajib dipilih.',
            'documents.*.type.in' => 'Jenis dokumen tidak valid.',
            'documents.*.file.required' => 'File dokumen wajib diunggah.',
            'documents.*.file.file' => 'File dokumen harus berupa file yang valid.',
            'documents.*.file.mimes' => 'Format file harus pdf, doc, docx, xls, xlsx, dwg, jpg, jpeg, atau png.',
            'documents.*.file.max' => 'Ukuran file maksimal 20MB.',
            'request_notes.string' => 'Catatan permohonan harus berupa teks.',
            'request_notes.max' => 'Catatan permohonan maksimal 2000 karakter.',
        ];
    }
}
