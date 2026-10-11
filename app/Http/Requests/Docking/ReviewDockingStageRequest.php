<?php

namespace App\Http\Requests\Docking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewDockingStageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in($this->routeIs('docking-space-request.production-review') ? ['approve', 'reject', 'postpone', 'cancel'] : ['approve', 'reject'])],
            'recommended_start_at' => ['required_if:decision,postpone', 'nullable', 'date'],
            'recommended_end_at' => ['required_if:decision,postpone', 'nullable', 'date', 'after:recommended_start_at'],
            'return_to_queue' => 'nullable|boolean',
            'recommended_docking_space' => [
                Rule::requiredIf(fn (): bool => $this->routeIs('docking-space-request.engineering-review') && $this->input('decision') === 'approve'),
                'nullable',
                'uuid',
                Rule::exists('docking_spaces', 'unique_id'),
            ],
            'notes' => 'required_if:decision,reject,postpone,cancel|nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'decision.required' => 'Keputusan wajib dipilih.',
            'decision.in' => 'Keputusan tidak valid.',
            'recommended_docking_space.required' => 'Docking space rekomendasi wajib dipilih saat menyetujui.',
            'recommended_docking_space.uuid' => 'Docking space rekomendasi tidak valid.',
            'recommended_docking_space.exists' => 'Docking space rekomendasi tidak ditemukan.',
            'recommended_start_at.required_if' => 'Tanggal mulai usulan wajib diisi.',
            'recommended_start_at.date' => 'Tanggal mulai usulan tidak valid.',
            'recommended_end_at.required_if' => 'Tanggal selesai usulan wajib diisi.',
            'recommended_end_at.date' => 'Tanggal selesai usulan tidak valid.',
            'recommended_end_at.after' => 'Tanggal selesai usulan harus setelah tanggal mulai.',
            'notes.required_if' => 'Alasan wajib diisi untuk penolakan, penundaan, atau pembatalan.',
            'notes.string' => 'Catatan harus berupa teks.',
            'notes.max' => 'Catatan maksimal 2000 karakter.',
        ];
    }
}
