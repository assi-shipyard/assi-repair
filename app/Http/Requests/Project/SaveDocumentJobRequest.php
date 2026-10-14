<?php

declare(strict_types=1);

namespace App\Http\Requests\Project;

use App\Services\boq_rule_service;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveDocumentJobRequest extends FormRequest
{
    private const MAX_DIMENSION = 1000000;

    private const CALCULATOR_TYPES = ['plate', 'pipe', 'tugboat'];

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
            'calculation_rule' => ['nullable', Rule::in(array_keys(boq_rule_service::RULES))],
            'calculation' => ['nullable', 'array'],
            'calculation.type' => ['nullable', Rule::in(self::CALCULATOR_TYPES)],
            'calculation.quantity' => ['exclude_if:calculation.type,tugboat', 'required_with:calculation.type', 'integer', 'min:1', 'max:100000'],
            'calculation.material' => ['exclude_unless:calculation.type,plate', 'required', Rule::in(array_keys(boq_rule_service::PLATE_MATERIALS))],
            'calculation.safety_factor' => ['exclude_unless:calculation.type,plate', 'required', 'numeric', 'min:1', 'max:3'],
            'calculation.length' => ['exclude_unless:calculation.type,plate', 'required', 'numeric', 'gt:0', 'max:'.self::MAX_DIMENSION],
            'calculation.width' => ['exclude_unless:calculation.type,plate', 'required', 'numeric', 'gt:0', 'max:'.self::MAX_DIMENSION],
            'calculation.thickness' => ['exclude_unless:calculation.type,plate', 'required', 'numeric', 'gt:0', 'max:1000'],
            'calculation.size' => ['exclude_unless:calculation.type,pipe', 'nullable', 'string', 'max:50'],
            'calculation.length_m' => ['exclude_unless:calculation.type,pipe', 'required', 'numeric', 'gt:0', 'max:1000'],
            'calculation.entries' => ['exclude_unless:calculation.type,tugboat', 'required', 'array', 'min:1', 'max:'.boq_rule_service::MAX_TUGBOAT_ENTRIES],
            'calculation.entries.*.start_at' => ['exclude_unless:calculation.type,tugboat', 'required', 'date_format:Y-m-d\\TH:i'],
            'calculation.entries.*.end_at' => ['exclude_unless:calculation.type,tugboat', 'required', 'date_format:Y-m-d\\TH:i'],
            'calculation.entries.*.is_holiday' => ['exclude_unless:calculation.type,tugboat', 'nullable', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->input('calculation.type') !== 'tugboat' || $validator->errors()->isNotEmpty()) {
                    return;
                }

                foreach ((array) $this->input('calculation.entries') as $index => $entry) {
                    $start = CarbonImmutable::createFromFormat('Y-m-d\\TH:i', (string) $entry['start_at']);
                    $end = CarbonImmutable::createFromFormat('Y-m-d\\TH:i', (string) $entry['end_at']);

                    if ($end <= $start) {
                        $validator->errors()->add("calculation.entries.$index.end_at", 'Waktu selesai pemakaian kapal tunda harus setelah waktu mulai.');
                    } elseif ($start->diffInHours($end) > boq_rule_service::MAX_TUGBOAT_HOURS) {
                        $validator->errors()->add("calculation.entries.$index.end_at", 'Satu pemakaian kapal tunda maksimal '.boq_rule_service::MAX_TUGBOAT_HOURS.' jam.');
                    }
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
            'calculation_rule.in' => 'Aturan perhitungan tidak valid.',
            'calculation.type.in' => 'Jenis kalkulator tidak valid.',
            'calculation.material.required' => 'Jenis bahan pelat wajib dipilih.',
            'calculation.material.in' => 'Jenis bahan pelat tidak valid.',
            'calculation.safety_factor.required' => 'Faktor keamanan wajib diisi.',
            'calculation.entries.required' => 'Isi minimal satu pemakaian kapal tunda.',
            'calculation.entries.min' => 'Isi minimal satu pemakaian kapal tunda.',
            'calculation.entries.max' => 'Pemakaian kapal tunda maksimal '.boq_rule_service::MAX_TUGBOAT_ENTRIES.' baris.',
            'calculation.entries.*.start_at.required' => 'Waktu mulai pemakaian wajib diisi.',
            'calculation.entries.*.end_at.required' => 'Waktu selesai pemakaian wajib diisi.',
            'calculation.entries.*.start_at.date_format' => 'Format waktu mulai tidak valid.',
            'calculation.entries.*.end_at.date_format' => 'Format waktu selesai tidak valid.',
            'calculation.size.max' => 'Ukuran pipa maksimal 50 karakter.',
            'calculation.quantity.required_with' => 'Jumlah wajib diisi.',
            'calculation.quantity.integer' => 'Jumlah harus berupa bilangan bulat.',
            'calculation.quantity.min' => 'Jumlah minimal 1.',
            'calculation.safety_factor.min' => 'Faktor keamanan minimal 1.',
            'calculation.safety_factor.max' => 'Faktor keamanan maksimal 3.',
            'calculation.*.required' => 'Ukuran kalkulator wajib diisi.',
            'calculation.*.numeric' => 'Ukuran kalkulator harus berupa angka.',
            'calculation.*.gt' => 'Ukuran kalkulator harus lebih dari 0.',
            'calculation.*.max' => 'Ukuran kalkulator terlalu besar.',
        ];
    }
}
