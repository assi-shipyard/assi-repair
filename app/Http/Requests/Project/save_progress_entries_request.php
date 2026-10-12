<?php

declare(strict_types=1);

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class save_progress_entries_request extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['notes' => trim((string) $this->input('notes')) ?: null]);
    }

    public function rules(): array
    {
        return [
            'action' => ['nullable', 'in:save,submit'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'entries' => ['nullable', 'array', 'max:2000'],
            'entries.*' => ['array:progress_percent,notes'],
            'entries.*.progress_percent' => ['nullable', 'numeric', 'min:0', 'max:100', 'decimal:0,2'],
            'entries.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function after(): array
    {
        return [function ($validator): void {
            foreach (array_keys((array) $this->input('entries', [])) as $key) {
                if (! is_string($key) || ! preg_match('/^[0-9a-f-]{36}$/i', $key)) {
                    $validator->errors()->add('entries', 'Data pekerjaan tidak valid.');

                    return;
                }
            }
        }];
    }

    public function messages(): array
    {
        return [
            'action.in' => 'Aksi tidak valid.',
            'notes.max' => 'Catatan laporan maksimal 1000 karakter.',
            'entries.*.array' => 'Data pekerjaan tidak valid.',
            'entries.*.progress_percent.numeric' => 'Progres harus berupa angka.',
            'entries.*.progress_percent.min' => 'Progres tidak boleh kurang dari 0%.',
            'entries.*.progress_percent.max' => 'Progres tidak boleh lebih dari 100%.',
            'entries.*.progress_percent.decimal' => 'Progres maksimal 2 angka desimal.',
            'entries.*.notes.max' => 'Catatan pekerjaan maksimal 500 karakter.',
        ];
    }
}
