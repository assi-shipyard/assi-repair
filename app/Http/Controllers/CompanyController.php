<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use RuntimeException;

use App\Models\Company;
use App\Models\CompanyDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * CompanyController handles all company-related operations, including
 * displaying company information, creating new companies, and managing
 * company documents.
 */
class CompanyController extends Controller
{
    private const COMPANY_NOT_FOUND_MESSAGE = 'Perusahaan tidak ditemukan.';

    /**
     * Display a listing of all companies.
     */
    public function index(): View
    {
        // Fetch all companies to display in the index view
        $companies = Company::withCount('ships')->orderBy('name')->get();

        return view('company.index', compact('companies'));
    }

    /**
     * Show the form for creating a new company.
     */
    public function create(): View
    {
        return view('company.create'); // Make sure this view exists
    }

    /**
     * Store a newly created company in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate input with custom rules and messages
        $validation = Validator::make($request->all(), $this->validation_rules(), $this->validation_messages());

        // If validation fails, redirect back with errors and input
        if ($validation->fails()) {
            return back()->withErrors($validation)->withInput();
        }

        // Use only validated fields to prevent accidental mass assignment
        $data = $validation->validated();

        // 'unique_id' is used in URLs and public references
        $data['unique_id'] = Str::uuid()->toString();
        $data['name'] = strtoupper($data['name']);

        // Save the company to the database using validated payload
        $company = new Company;
        $company->forceFill($data);
        $company->save();

        return redirect()->route('company.index')->with('success', 'Perusahaan '.$company->name.' berhasil ditambahkan.');

    }

    // Get company information (for API)
    public function get($id): JsonResponse
    {
        if (! request()->ajax()) {
            return response()->json(['error' => 'Permintaan tidak valid'], 400);
        }

        $company = $this->find_company($id);
        if (! $company) {
            return response()->json(['error' => 'Perusahaan tidak ditemukan'], 404);
        }

        return response()->json($company, 200);
    }

    // Show company information page (for web)
    public function show($id): View
    {
        $company = $this->find_company($id);
        if (! $company) {
            return $this->company_not_found_redirect();
        }

        $documents = $company->documents()->get();
        $ships = $company->ships()->with(['type', 'classification'])->orderBy('name')->get();

        return view('company.show', compact('company', 'documents', 'ships'));
    }

    // Show edit company form
    public function edit($id): View
    {
        $company = $this->find_company($id);
        if (! $company) {
            return $this->company_not_found_redirect();
        }

        return view('company.edit', compact('company'));
    }

    // Process edit company form submission
    public function update(Request $request, $id): RedirectResponse
    {
        $company = $this->find_company($id);
        if (! $company) {
            return $this->company_not_found_redirect();
        }

        // Normalize and validate the form input
        $payload = $request->all();
        $payload['name'] = strtoupper((string) $request->input('name'));

        $validation = Validator::make(
            $payload,
            $this->update_validation_rules($company->id),
            $this->update_validation_messages()
        );

        if ($validation->fails()) {
            return $this->back_with_validation($validation);
        }

        // Update the company using validated payload
        $company->forceFill($validation->validated());
        $company->save();

        return redirect()->route('company.show', $company->unique_id)->with('success', 'Data perusahaan berhasil diperbarui.');
    }

    // Delete a company
    public function destroy($id): RedirectResponse
    {
        $company = $this->find_company($id);
        if (! $company) {
            return $this->company_not_found_redirect();
        }

        // If company still has ships associated, prevent deletion
        if ($company->ships()->count() > 0) {
            return redirect()->route('company.index')->with('error', 'Tidak dapat menghapus perusahaan yang masih memiliki kapal terdaftar di sistem. Harap hapus kapal-kapal tersebut terlebih dahulu.');
        }

        // If there's no further issue, delete the company
        $company->delete();

        return redirect()->route('company.index')->with('success', 'Perusahaan berhasil dihapus.');
    }

    /**
     * Validation rules shared by store and update methods to ensure data integrity for company records.
     *
     * @param  int|null  $ignoreCompanyId  Existing company id for unique check when updating.
     */
    private function validation_rules(?int $ignoreCompanyId = null): array
    {
        $nameRule = 'required|string|max:255|unique:companies,name';

        if ($ignoreCompanyId) {
            $nameRule .= ','.$ignoreCompanyId;
        }

        return [
            'name' 					=> $nameRule,
            'address' 				=> 'required|string|max:255',
            'phone_1' 				=> 'nullable|string|max:20',
            'phone_2' 				=> 'nullable|string|max:20',
            'email' 				=> 'nullable|email|max:255',
            'ceo_name' 				=> 'nullable|string|max:255',
            'ceo_phone' 			=> 'nullable|string|max:20',
            'ceo_email' 			=> 'nullable|email|max:255',
            'pic_name' 				=> 'nullable|string|max:255',
            'pic_phone' 			=> 'nullable|string|max:20',
            'pic_email' 			=> 'nullable|email|max:255',
            'registration_number'	=> 'nullable|string|max:255',
            'tax_id' 				=> 'nullable|string|max:255',
        ];
    }

    /**
     * Human-friendly Indonesian validation messages for company-related operations to provide clear feedback to users.
     */
    private function validation_messages(): array
    {
        return [
            'name.required' => 'Nama perusahaan dibutuhkan.',
            'name.unique' => 'Nama perusahaan sudah terdaftar.',
            'name.max' => 'Nama perusahaan tidak boleh lebih dari 255 karakter.',
            'address.required' => 'Alamat perusahaan dibutuhkan.',
            'address.max' => 'Alamat perusahaan tidak boleh lebih dari 255 karakter.',
            //'phone_1.required' => 'Nomor telepon perusahaan dibutuhkan.',
            'phone_1.max' => 'Nomor telepon perusahaan tidak boleh lebih dari 20 karakter.',
            'phone_2.max' => 'Nomor telepon alternatif tidak boleh lebih dari 20 karakter.',
            //'email.required' => 'Email perusahaan dibutuhkan.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email perusahaan tidak boleh lebih dari 255 karakter.',
            //'ceo_name.required' => 'Nama CEO dibutuhkan.',
            'ceo_name.max' => 'Nama CEO tidak boleh lebih dari 255 karakter.',
            //'ceo_phone.required' => 'Nomor telepon CEO dibutuhkan.',
            'ceo_phone.max' => 'Nomor telepon CEO tidak boleh lebih dari 20 karakter.',
            //'ceo_email.required' => 'Email CEO dibutuhkan.',
            'ceo_email.email' => 'Format email CEO tidak valid.',
            'ceo_email.max' => 'Email CEO tidak boleh lebih dari 255 karakter.',
            //'pic_name.required' => 'Nama PIC dibutuhkan.',
            'pic_name.max' => 'Nama PIC tidak boleh lebih dari 255 karakter.',
            //'pic_phone.required' => 'Nomor telepon PIC dibutuhkan.',
            'pic_phone.max' => 'Nomor telepon PIC tidak boleh lebih dari 20 karakter.',
            //'pic_email.required' => 'Email PIC dibutuhkan.',
            'pic_email.email' => 'Format email PIC tidak valid.',
            'pic_email.max' => 'Email PIC tidak boleh lebih dari 255 karakter.',
            //'registration_number.required' => 'Nomor registrasi perusahaan dibutuhkan.',
            'registration_number.max' => 'Nomor registrasi perusahaan tidak boleh lebih dari 255 karakter.',
            //'tax_id.required' => 'NPWP perusahaan dibutuhkan.',
            'tax_id.max' => 'NPWP perusahaan tidak boleh lebih dari 255 karakter.',
        ];
    }

    /**
     * Validation rules for update operation while keeping optional business fields.
     */
    private function update_validation_rules(int $ignoreCompanyId): array
    {
        return [
            'name' => 'required|string|max:255|unique:companies,name,'.$ignoreCompanyId,
            'address' => 'required|string|max:255',
            'phone_1' => 'nullable|string|max:20',
            'phone_2' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'ceo_name' => 'nullable|string|max:255',
            'ceo_phone' => 'nullable|string|max:20',
            'ceo_email' => 'nullable|email|max:255',
            'pic_name' => 'nullable|string|max:255',
            'pic_phone' => 'nullable|string|max:20',
            'pic_email' => 'nullable|email|max:255',
            'registration_number' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:255',
        ];
    }

    /**
     * Validation messages for update operation.
     */
    private function update_validation_messages(): array
    {
        return [
            'name.required' => 'Nama perusahaan dibutuhkan.',
            'name.unique' => 'Nama perusahaan sudah terdaftar.',
            'name.max' => 'Nama perusahaan tidak boleh lebih dari 255 karakter.',
            'address.required' => 'Alamat perusahaan dibutuhkan.',
            'address.max' => 'Alamat perusahaan tidak boleh lebih dari 255 karakter.',
            'phone_1.max' => 'Nomor telepon perusahaan tidak boleh lebih dari 20 karakter.',
            'phone_2.max' => 'Nomor telepon alternatif tidak boleh lebih dari 20 karakter.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email perusahaan tidak boleh lebih dari 255 karakter.',
            'ceo_name.max' => 'Nama CEO tidak boleh lebih dari 255 karakter.',
            'ceo_phone.max' => 'Nomor telepon CEO tidak boleh lebih dari 20 karakter.',
            'ceo_email.max' => 'Email CEO tidak boleh lebih dari 255 karakter.',
            'pic_name.max' => 'Nama PIC tidak boleh lebih dari 255 karakter.',
            'pic_phone.max' => 'Nomor telepon PIC tidak boleh lebih dari 20 karakter.',
            'pic_email.max' => 'Email PIC tidak boleh lebih dari 255 karakter.',
            'registration_number.max' => 'Nomor registrasi tidak boleh lebih dari 255 karakter.',
            'tax_id.max' => 'NPWP tidak boleh lebih dari 255 karakter.',
        ];
    }

    /**
     * Standard redirect response for non-existent company records.
     */
    private function company_not_found_redirect(): RedirectResponse
    {
        return redirect()->route('company.index')->with('error', self::COMPANY_NOT_FOUND_MESSAGE);
    }

    // ===> II. COMPANY DOCUMENT MANAGEMENT <===
    public function upload_logo(Request $request, $id): RedirectResponse
    {
        $company = $this->find_company($id);
        if (! $company) {
            return $this->company_not_found_redirect();
        }

        // Validate the uploaded logo with custom rules and messages
        $validation = Validator::make($request->all(), [
            'company_logo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'company_logo.required' => 'Logo perusahaan dibutuhkan.',
            'company_logo.image' => 'File logo harus berupa gambar yang valid.',
            'company_logo.mimes' => 'Format logo harus jpg, jpeg, atau png.',
            'company_logo.max' => 'Ukuran logo tidak boleh lebih dari 2MB.',
        ]);

        if ($validation->fails()) {
            return $this->back_with_validation($validation);
        }

        // Handle the logo file upload and update the company's logo path
        $logoFile = $request->file('company_logo');
        $logoFilename = time().'_'.Str::slug($company->name).'.'.$logoFile->getClientOriginalExtension();

        // Delete old logo file if it exists before saving the new one
        if ($company->logo_path) {
            Storage::disk('public')->delete('company_logos/'.$company->logo_path);
        }

        // Store the new logo file and update the company's logo path in the database
        $logoFile->storeAs('company_logos', $logoFilename, 'public');
        $company->logo_path = $logoFilename;
        $company->save();

        return redirect()->route('company.show', $company->unique_id)->with('success', 'Logo perusahaan berhasil diunggah.');
    }

    // Add new document for a company
    public function upload_document(Request $request, $id): RedirectResponse
    {
        $company = $this->find_company($id);
        if (! $company) {
            return $this->company_not_found_redirect();
        }

        // Validate the uploaded document
        $validation = Validator::make($request->all(), [
            'document_name' => 'required|string|max:255',
            'document_type' => 'required|string|max:255',
            'document_file' => 'required|file|max:10240', // Max file size 10MB
        ], [
            'document_name.required' => 'Nama dokumen dibutuhkan.',
            'document_name.max' => 'Nama dokumen tidak boleh lebih dari 255 karakter.',
            'document_type.required' => 'Tipe dokumen dibutuhkan.',
            'document_type.max' => 'Tipe dokumen tidak boleh lebih dari 255 karakter.',
            'document_file.required' => 'File dokumen dibutuhkan.',
            'document_file.file' => 'File dokumen harus berupa file yang valid.',
            'document_file.max' => 'File dokumen tidak boleh lebih dari 10MB.',
        ]);

        if ($validation->fails()) {
            return $this->back_with_validation($validation);
        }

        $data = $validation->validated();

        // Handle the file upload
        $file = $data['document_file'];
        $filename = time().'_'.$file->getClientOriginalName();
        $filepath = $file->storeAs('company_documents', $filename, 'public');

        // Create a new company document record
        $document = new CompanyDocument;
        $document->company_id = $company->id;
        $document->document_name = $data['document_name'];
        $document->document_type = $data['document_type'];
        $document->document_path = $filepath;
        $document->save();

        return redirect()->route('company.documents', $company->unique_id)->with('success', 'Dokumen berhasil diunggah.');
    }

    public function show_documents($id): View
    {
        $company = $this->find_company($id);

        if (! $company) {
            return $this->company_not_found_redirect();
        }

        $documents = $company->documents()->get();
        $ships = $company->ships()->with(['type', 'classification'])->orderBy('name')->get();

        return view('company.show', compact('company', 'documents', 'ships'));
    }

    public function delete_document($companyId, $documentId): RedirectResponse
    {
        $company = $this->find_company($companyId);

        if (! $company) {
            return $this->company_not_found_redirect();
        }

        $document = $this->find_company_document($company->id, (string) $documentId);

        if (! $document) {
            return redirect()->route('company.show', $company->unique_id)->with('error', 'Dokumen tidak ditemukan.');
        }

        Storage::disk('public')->delete($document->document_path);
        $document->delete();

        return redirect()->route('company.show', $company->unique_id)->with('success', 'Dokumen berhasil dihapus.');
    }

    private function find_company(string $unique_id): ?Company
    {
        return Company::query()->firstWhere('unique_id', $unique_id);
    }

    private function find_company_document(int $company_id, string $document_id): ?CompanyDocument
    {
        $query = CompanyDocument::query()->where('company_id', $company_id);

        if (preg_match('/^[0-9a-fA-F-]{36}$/', $document_id) === 1) {
            return $query->where('unique_id', $document_id)->first();
        }

        return $query->whereKey((int) $document_id)->first();
    }

    private function back_with_validation(\Illuminate\Contracts\Validation\Validator $validation)
    {
        return back()->withErrors($validation)->withInput();
    }
}
