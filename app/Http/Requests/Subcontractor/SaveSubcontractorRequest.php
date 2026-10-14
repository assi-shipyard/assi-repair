<?php

declare(strict_types=1);

namespace App\Http\Requests\Subcontractor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveSubcontractorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('subcontractors', 'code')->ignore($this->route('subcontractor'))],
            'name' => ['required', 'string', 'max:191'],
            'contact_name' => ['required', 'string', 'max:191'],
            'contact_phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+().\s-]+$/', 'regex:/[0-9]/'],
            'contact_email' => ['nullable', 'string', 'email', 'max:254'],
            'address' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'max' => ':attribute maksimal :max karakter.',
            'boolean' => ':attribute tidak valid.',
            'email' => ':attribute harus berupa alamat email yang valid.',
            'code.regex' => 'Kode hanya boleh berisi huruf, angka, titik, garis bawah, dan strip.',
            'code.unique' => 'Kode subkontraktor sudah digunakan.',
            'contact_phone.regex' => 'Nomor telepon tidak valid.',
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => 'Kode subkontraktor',
            'name' => 'Nama perusahaan',
            'contact_name' => 'Nama kontak',
            'contact_phone' => 'Telepon',
            'contact_email' => 'Email',
            'address' => 'Alamat',
            'notes' => 'Catatan internal',
            'is_active' => 'Status',
        ];
    }
}
