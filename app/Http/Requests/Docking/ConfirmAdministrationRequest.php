<?php

declare(strict_types=1);

namespace App\Http\Requests\Docking;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmAdministrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'administration_notes' => 'required|string|min:5|max:2000',
            'administration_confirmed' => 'accepted',
        ];
    }

    public function messages(): array
    {
        return [
            'administration_notes.required' => 'Catatan administrasi wajib diisi (contoh: nomor BAST, status invoice, clearance).',
            'administration_notes.string' => 'Catatan administrasi harus berupa teks.',
            'administration_notes.min' => 'Catatan administrasi minimal 5 karakter.',
            'administration_notes.max' => 'Catatan administrasi maksimal 2000 karakter.',
            'administration_confirmed.accepted' => 'Centang pernyataan bahwa administrasi telah selesai.',
        ];
    }
}
