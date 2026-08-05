<?php

namespace App\Http\Requests\Docking;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectDockingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'requested_docking_space_id' => 'nullable|exists:docking_spaces,id',
            'requested_start_at' => 'required|date',
            'requested_end_at' => 'nullable|date|after_or_equal:requested_start_at',
            'request_notes' => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'project_id.required' => 'Proyek wajib dipilih.',
            'project_id.exists' => 'Proyek yang dipilih tidak valid.',
            'requested_docking_space_id.exists' => 'Docking space yang dipilih tidak valid.',
            'requested_start_at.required' => 'Jadwal mulai docking wajib diisi.',
            'requested_start_at.date' => 'Jadwal mulai docking harus berupa tanggal dan waktu yang valid.',
            'requested_end_at.date' => 'Jadwal selesai docking harus berupa tanggal dan waktu yang valid.',
            'requested_end_at.after_or_equal' => 'Jadwal selesai docking tidak boleh lebih awal dari jadwal mulai.',
            'request_notes.string' => 'Catatan permohonan harus berupa teks.',
            'request_notes.max' => 'Catatan permohonan maksimal 2000 karakter.',
        ];
    }
}
