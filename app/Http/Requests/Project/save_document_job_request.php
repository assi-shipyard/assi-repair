<?php

declare(strict_types=1);

namespace App\Http\Requests\Project;

use App\Services\project_boq_service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class save_document_job_request extends FormRequest
{
    private const MAX_DIMENSION = 1000000;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $calculation = $this->input('calculation');

        $this->merge([
            'calculation' => is_array($calculation) && ! empty($calculation['type']) ? $calculation : null,
            'job_name' => trim((string) $this->input('job_name')),
        ]);
    }

    public function rules(): array
    {
        $type = $this->input('calculation.type');

        return [
            'job_name' => ['required', 'string', 'max:191'],
            'project_job_group_id' => ['nullable', 'uuid', Rule::exists('project_job_groups', 'unique_id')],
            'parent_job_id' => ['nullable', 'uuid'],
            'price_book_item_id' => ['nullable', 'uuid'],
            'job_volume_estimated' => ['nullable', 'numeric', 'min:0', 'max:99999999999999'],
            'unit' => ['nullable', 'string', 'max:30'],
            'unit_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999999999'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'responsible_kind' => ['nullable', Rule::in(['internal_unit', 'subcontractor'])],
            'responsible_name' => ['nullable', 'string', 'max:191'],
            'est_start_date' => ['nullable', 'date'],
            'est_finish_date' => ['nullable', 'date', 'after_or_equal:est_start_date'],
            'progress_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['nullable', Rule::in(['pending', 'ongoing', 'completed', 'cancelled'])],
            'actual_start_date' => ['nullable', 'date'],
            'actual_finish_date' => ['nullable', 'date', 'after_or_equal:actual_start_date'],
            'actual_volume' => ['nullable', 'numeric', 'min:0', 'max:99999999999999'],
            'calculation' => ['nullable', 'array'],
            'calculation.type' => ['nullable', Rule::in(array_keys(project_boq_service::CALCULATION_TYPES))],
            'calculation.basis' => ['required_with:calculation.type', Rule::in(array_keys(project_boq_service::CALCULATION_BASES[$type] ?? []))],
            'calculation.quantity' => ['required_with:calculation.type', 'integer', 'min:1', 'max:100000'],
            'calculation.density' => ['nullable', 'numeric', 'gt:0', 'max:100000'],
            'calculation.safety_factor' => ['nullable', 'numeric', 'min:1', 'max:5'],
            'calculation.length' => ['exclude_unless:calculation.type,plate', 'required', 'numeric', 'gt:0', 'max:'.self::MAX_DIMENSION],
            'calculation.width' => ['exclude_unless:calculation.type,plate', 'required', 'numeric', 'gt:0', 'max:'.self::MAX_DIMENSION],
            'calculation.thickness' => ['exclude_unless:calculation.type,plate', 'required', 'numeric', 'gt:0', 'max:1000'],
            'calculation.diameter' => ['exclude_unless:calculation.type,pipe', 'required', 'numeric', 'gt:0', 'max:10000'],
            'calculation.wall_thickness' => ['exclude_unless:calculation.type,pipe', 'required', 'numeric', 'gt:0', 'max:1000'],
            'calculation.pipe_length' => ['exclude_unless:calculation.type,pipe', 'required', 'numeric', 'gt:0', 'max:'.self::MAX_DIMENSION],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $diameter = (float) $this->input('calculation.diameter');
                $wall = (float) $this->input('calculation.wall_thickness');

                if ($this->input('calculation.type') === 'pipe' && $diameter > 0 && $wall * 2 >= $diameter) {
                    $validator->errors()->add('calculation.wall_thickness', 'Tebal dinding harus kurang dari setengah diameter pipa.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'job_name.required' => 'Nama pekerjaan wajib diisi.',
            'job_name.max' => 'Nama pekerjaan maksimal 191 karakter.',
            'project_job_group_id.exists' => 'Kelompok pekerjaan tidak valid.',
            '*.uuid' => 'Data yang dipilih tidak valid.',
            'job_volume_estimated.numeric' => 'Volume harus berupa angka.',
            'job_volume_estimated.min' => 'Volume tidak boleh negatif.',
            'unit.max' => 'Satuan maksimal 30 karakter.',
            'unit_price.numeric' => 'Harga satuan harus berupa angka.',
            'unit_price.min' => 'Harga satuan tidak boleh negatif.',
            'unit_price.max' => 'Harga satuan terlalu besar.',
            'notes.max' => 'Catatan maksimal 2000 karakter.',
            'responsible_kind.in' => 'Jenis penanggung jawab tidak valid.',
            'responsible_name.max' => 'Nama penanggung jawab maksimal 191 karakter.',
            'est_start_date.date' => 'Tanggal mulai tidak valid.',
            'est_finish_date.date' => 'Tanggal selesai tidak valid.',
            'est_finish_date.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'progress_percent.numeric' => 'Progres harus berupa angka.',
            'progress_percent.min' => 'Progres minimal 0%.',
            'progress_percent.max' => 'Progres maksimal 100%.',
            'status.in' => 'Status pekerjaan tidak valid.',
            'actual_finish_date.after_or_equal' => 'Tanggal aktual selesai harus sama atau setelah tanggal aktual mulai.',
            'actual_volume.numeric' => 'Volume aktual harus berupa angka.',
            'calculation.type.in' => 'Jenis kalkulator tidak valid.',
            'calculation.basis.required_with' => 'Dasar perhitungan wajib dipilih.',
            'calculation.basis.in' => 'Dasar perhitungan tidak sesuai jenis kalkulator.',
            'calculation.quantity.required_with' => 'Jumlah wajib diisi.',
            'calculation.quantity.integer' => 'Jumlah harus berupa bilangan bulat.',
            'calculation.quantity.min' => 'Jumlah minimal 1.',
            'calculation.density.gt' => 'Massa jenis harus lebih dari 0.',
            'calculation.safety_factor.min' => 'Faktor keamanan minimal 1.',
            'calculation.safety_factor.max' => 'Faktor keamanan maksimal 5.',
            'calculation.*.required' => 'Ukuran kalkulator wajib diisi.',
            'calculation.*.numeric' => 'Ukuran kalkulator harus berupa angka.',
            'calculation.*.gt' => 'Ukuran kalkulator harus lebih dari 0.',
            'calculation.*.max' => 'Ukuran kalkulator terlalu besar.',
        ];
    }
}
