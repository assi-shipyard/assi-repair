<?php

declare(strict_types=1);

namespace App\Http\Requests\Project;

use App\Services\project_progress_service;
use Illuminate\Foundation\Http\FormRequest;

class StoreProgressReportRequest extends FormRequest
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
            'report_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:'.now(project_progress_service::TIMEZONE)->toDateString()],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'report_date.required' => 'Tanggal laporan wajib diisi.',
            'report_date.date_format' => 'Format tanggal laporan tidak valid.',
            'report_date.before_or_equal' => 'Tanggal laporan tidak boleh melebihi hari ini.',
            'notes.max' => 'Catatan maksimal 1000 karakter.',
        ];
    }
}
