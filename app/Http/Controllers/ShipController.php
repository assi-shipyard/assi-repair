<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Ship;
use App\Models\ShipClass;
use App\Models\ShipImage;
use App\Models\ShipType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller for ship CRUD flow and related ship type/class management.
 *
 * Important note for maintainers:
 * - Public route parameter uses `unique_id` (UUID string).
 * - Database primary key remains numeric `id`.
 */
class ShipController extends Controller
{
    // ===> I. SHIP MANAGEMENT <===

    /**
     * Display all ships for the index page.
     */
    public function index()
    {
        $ships = Ship::with(['company', 'type', 'classification'])->orderBy('name')->get();
        $companies = Company::orderBy('name')->get(['id', 'name']);
        $ship_types = ShipType::orderBy('name')->get(['id', 'name']);
        $ship_classes = ShipClass::orderBy('name')->get(['id', 'name']);

        return view('ship.index', compact('ships', 'companies', 'ship_types', 'ship_classes'));
    }

    /**
     * Show create form and preload supporting dropdown data.
     */
    public function create()
    {
        // Fetch necessary data for the create form (e.g., companies) and pass to the view
        $companies = Company::all();
        $ship_types = ShipType::all();
        $ship_classes = ShipClass::all();

        return view('ship.create', compact('companies', 'ship_types', 'ship_classes'));
    }

    /**
     * Validate and store a new ship record.
     */
    public function store(Request $request)
    {
        // Validate input with custom rules and messages
        $validation = Validator::make($request->all(), $this->validation_rules(), $this->validation_messages());

        // If validation fails, redirect back with errors and input
        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        }

        // Use only validated fields to prevent accidental mass assignment
        $data = $validation->validated();

        // 'unique_id' is used in URLs and public references
        $data['unique_id'] = Str::uuid()->toString();

        // Save the ship to the database
        $ship = Ship::create($data);
        $shipName = $ship->name;
        $companyName = $ship->company->name;

        return redirect()->route('ship.index')->with('success', 'Kapal '.$shipName.' milik '.$companyName.' berhasil ditambahkan.');
    }

    /**
     * Display details of a specific ship.
     */
    public function show($id)
    {
        $ship = $this->find_ship_by_unique_id($id, ['company', 'type', 'classification', 'images']);
        $projects = $ship->projects()->with('leader')->orderByDesc('start_date_estimation')->get();
        $documents = $ship->documents()->latest()->get();

        return view('ship.show', compact('ship', 'projects', 'documents'));
    }

    public function store_photo(Request $request, string $ship): \Illuminate\Http\RedirectResponse
    {
        $ship_model = Ship::query()->firstWhere('unique_id', $ship);

        if (! $ship_model) {
            return redirect()->route('ship.index')->with('error', 'Kapal tidak ditemukan.');
        }

        $validation = Validator::make($request->all(), [
            'ship_photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
        ], [
            'ship_photo.required' => 'Foto kapal wajib diunggah.',
            'ship_photo.image' => 'File harus berupa gambar.',
            'ship_photo.mimes' => 'Foto kapal harus berformat JPG, JPEG, PNG, atau WEBP.',
            'ship_photo.max' => 'Ukuran foto kapal maksimal 10 MB.',
        ]);

        if ($validation->fails()) {
            return back()->withErrors($validation)->withInput();
        }

        $image_path = $request->file('ship_photo')->store('ships/'.$ship_model->unique_id.'/photos', 'ship');
        $ship_model->images()->create(['image_path' => $image_path]);

        return redirect()->route('ship.show', $ship_model->unique_id)->with('success', 'Foto kapal berhasil diunggah.');
    }

    public function photo(string $ship, string $photo): StreamedResponse
    {
        $ship_model = Ship::query()->firstWhere('unique_id', $ship);
        $image = $ship_model?->images()->firstWhere('unique_id', $photo);

        abort_if(! $image || ! Storage::disk('ship')->exists($image->image_path), 404);

        return Storage::disk('ship')->response($image->image_path);
    }

    public function destroy_photo(string $ship, string $photo): \Illuminate\Http\RedirectResponse
    {
        $ship_model = Ship::query()->firstWhere('unique_id', $ship);
        $image = $ship_model?->images()->firstWhere('unique_id', $photo);

        if (! $image) {
            return redirect()->route('ship.show', $ship)->with('error', 'Foto kapal tidak ditemukan.');
        }

        Storage::disk('ship')->delete($image->image_path);
        $image->delete();

        return redirect()->route('ship.show', $ship)->with('success', 'Foto kapal berhasil dihapus.');
    }

    /**
     * Show edit form for a specific ship.
     */
    public function edit($id)
    {
        $ship = $this->find_ship_by_unique_id($id, ['company', 'type', 'classification']);

        // Get the necessary data for the edit form (e.g., companies, ship types, ship classes)
        $companies = Company::all();
        $ship_types = ShipType::all();
        $ship_classes = ShipClass::all();

        return view('ship.edit', compact('ship', 'companies', 'ship_types', 'ship_classes'));
    }

    /**
     * Validate and update an existing ship record.
     */
    public function update(Request $request, $id)
    {
        $ship = $this->find_ship_by_unique_id($id, ['company', 'type', 'classification']);

        // Validate input with custom rules and messages. Pass the existing ship id to ignore it in the unique check.
        $validation = Validator::make($request->all(), $this->validation_rules($ship->id), $this->validation_messages());

        // If validation fails, redirect back with errors and old input.
        if ($validation->fails()) {
            return back()->withErrors($validation)->withInput();
        }

        // Use only validated fields to prevent accidental mass assignment.
        $ship->update($validation->validated());

        return redirect()->route('ship.index')->with('success', 'Kapal '.$ship->name.' milik '.$ship->company->name.' berhasil diperbarui.');
    }

    /**
     * Delete a ship record.
     */
    public function destroy($id)
    {
        $ship = $this->find_ship_by_unique_id($id, ['company']);

        if ($ship->projects()->where(function ($query): void {
            $query->has('customer_access')->orHas('customer_documents')->orHas('customer_feedback');
        })->exists()) {
            return redirect()->route('ship.index')->with('error', 'Kapal tidak dapat dihapus karena proyeknya memiliki akses atau riwayat pelanggan.');
        }

        // To prevent accidental deletion of ships that are still referenced by projects, we can check if there are any projects associated with this ship before allowing deletion.
        // This feature will be implemented later after we have project management features in place. For now, we will allow deletion without this guard.

        // Store ship name and company name for user-friendly message after deletion.
        $shipName = $ship->name;
        $companyName = $ship->company->name;

        $ship->delete();

        return redirect()->route('ship.index')->with('success', 'Kapal '.$shipName.' milik '.$companyName.' berhasil dihapus.');
    }

    /**
     * Validation rules shared by store and update.
     *
     * @param  int|null  $ignoreShipId  Existing ship id for unique check when updating.
     */
    private function validation_rules(?int $ignoreShipId = null): array
    {
        $imoNumberRule = 'nullable|string|max:255|unique:ships,imo_number';
        $mmsiNumberRule = 'nullable|string|max:255|unique:ships,mmsi_number';
        $callSignRule = 'nullable|string|max:255|unique:ships,call_sign';

        if ($ignoreShipId !== null) {
            $imoNumberRule .= ','.$ignoreShipId;
            $mmsiNumberRule .= ','.$ignoreShipId;
            $callSignRule .= ','.$ignoreShipId;
        }

        return [
            'name' => 'required|string|max:255',
            'company_id' => 'required|exists:companies,id',
            'ship_type_id' => 'required|exists:ship_types,id',
            'ship_class_id' => 'required|exists:ship_classes,id',
            'length_overall' => 'required|numeric',
            'breadth' => 'required|numeric',
            'height' => 'required|numeric',
            'empty_draft' => 'nullable|numeric',
            'loaded_draft' => 'nullable|numeric',
            'gross_tonnage' => 'required|numeric',
            'net_tonnage' => 'nullable|numeric',
            'engine_brand' => 'nullable|string|max:255',
            'engine_model' => 'nullable|string|max:255',
            'engine_power' => 'nullable|numeric',
            'engine_type' => 'nullable|string|max:255',
            'engine_rpm' => 'nullable|numeric',
            'engine_fuel_type' => 'nullable|string|max:255',
            'engine_fuel_capacity' => 'nullable|numeric',
            'engine_fuel_consumption' => 'nullable|numeric',
            'imo_number' => $imoNumberRule,
            'mmsi_number' => $mmsiNumberRule,
            'call_sign' => $callSignRule,
            'flag' => 'nullable|string|max:255',
            'build_year' => 'nullable|date',
        ];
    }

    /**
     * Human-friendly Indonesian validation messages for ship-related fields.
     */
    private function validation_messages(): array
    {
        return [
            'name.required' => 'Nama kapal harus diisi.',
            'name.string' => 'Nama kapal harus berupa teks.',
            'name.max' => 'Nama kapal tidak boleh lebih dari 255 karakter.',
            'company_id.required' => 'Perusahaan harus dipilih.',
            'company_id.exists' => 'Perusahaan yang dipilih tidak valid.',
            'ship_type_id.required' => 'Jenis kapal harus dipilih.',
            'ship_type_id.exists' => 'Jenis kapal yang dipilih tidak valid.',
            'ship_class_id.required' => 'Kelas kapal harus dipilih.',
            'ship_class_id.exists' => 'Kelas kapal yang dipilih tidak valid.',
            'length_overall.required' => 'Panjang keseluruhan kapal harus diisi.',
            'length_overall.numeric' => 'Panjang keseluruhan kapal harus berupa angka.',
            'breadth.required' => 'Lebar kapal harus diisi.',
            'breadth.numeric' => 'Lebar kapal harus berupa angka.',
            'height.required' => 'Tinggi kapal harus diisi.',
            'height.numeric' => 'Tinggi kapal harus berupa angka.',
            'empty_draft.numeric' => 'Draft kosong harus berupa angka.',
            'loaded_draft.numeric' => 'Draft muat harus berupa angka.',
            'gross_tonnage.required' => 'Gross tonnage harus diisi.',
            'gross_tonnage.numeric' => 'Gross tonnage harus berupa angka.',
            'net_tonnage.numeric' => 'Net tonnage harus berupa angka.',
            'net_tonnage.required' => 'Net tonnage harus diisi.',
            'engine_brand.string' => 'Merek mesin harus berupa teks.',
            'engine_brand.max' => 'Merek mesin tidak boleh lebih dari 255 karakter.',
            'engine_model.string' => 'Model mesin harus berupa teks.',
            'engine_model.max' => 'Model mesin tidak boleh lebih dari 255 karakter.',
            'engine_power.numeric' => 'Daya mesin harus berupa angka.',
            'engine_type.string' => 'Tipe mesin harus berupa teks.',
            'engine_type.max' => 'Tipe mesin tidak boleh lebih dari 255 karakter.',
            'engine_rpm.numeric' => 'RPM mesin harus berupa angka.',
            'engine_fuel_type.string' => 'Tipe bahan bakar mesin harus berupa teks.',
            'engine_fuel_type.max' => 'Tipe bahan bakar mesin tidak boleh lebih dari 255 karakter.',
            'engine_fuel_capacity.numeric' => 'Kapasitas bahan bakar mesin harus berupa angka.',
            'engine_fuel_consumption.numeric' => 'Konsumsi bahan bakar mesin harus berupa angka.',
            'imo_number.unique' => 'IMO number sudah digunakan oleh kapal lain.',
            'mmsi_number.unique' => 'MMSI number sudah digunakan oleh kapal lain.',
            'call_sign.unique' => 'Call sign sudah digunakan oleh kapal lain.',
            'flag.string' => 'Bendera harus berupa teks.',
            'flag.max' => 'Bendera tidak boleh lebih dari 255 karakter.',
            'build_year.date' => 'Tahun pembuatan harus berupa tanggal yang valid.',
        ];
    }

    private function find_ship_by_unique_id(string $unique_id, array $relations = []): Ship
    {
        $query = Ship::with($relations);

        if (Schema::hasColumn('ships', 'unique_id')) {
            return $query->where('unique_id', $unique_id)->firstOrFail();
        }

        return $query->whereKey((int) $unique_id)->firstOrFail();
    }

    /**
     * Display all ship types for the ship type management page.
     */
    public function type_index()
    {
        $ship_types = ShipType::all();

        return view('ship.type.index', compact('ship_types'));
    }

    /**
     * Validate and store a new ship type record.
     */
    public function type_store(Request $request)
    {
        // Validate the request
        $validation = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ], [
            'name.required' => 'Nama jenis kapal harus diisi.',
            'name.string' => 'Nama jenis kapal harus berupa teks.',
            'name.max' => 'Nama jenis kapal tidak boleh lebih dari 255 karakter.',
        ]);

        // If validation fails, redirect back with errors and input
        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        }

        // Create the new ship type
        $ship_type = new ShipType;
        $ship_type->name = $validation->validated()['name'];
        $ship_type->save();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'message' => 'Jenis kapal berhasil ditambahkan.',
                'data' => [
                    'id' => $ship_type->id,
                    'name' => $ship_type->name,
                ],
            ], 201);
        }

        return redirect()->route('ship-type.index')->with('success', 'Jenis kapal berhasil ditambahkan.');
    }

    /**
     * Delete a ship type record.
     */
    public function type_destroy($id)
    {
        // Delete the specified ship type
        $ship_type = ShipType::findOrFail($id);
        $ship_type->delete();

        return redirect()->route('ship-type.index')->with('success', 'Jenis kapal berhasil dihapus.');
    }

    /**
     * Display all ship classes for the ship class management page.
     */
    public function class_index()
    {
        $ship_classes = ShipClass::all();

        return view('ship.class.index', compact('ship_classes'));
    }

    /**
     * Validate and store a new ship class record.
     */
    public function class_store(Request $request)
    {
        // Validate the request
        $validation = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'abbreviation' => 'nullable|string|max:255',
        ], [
            'name.required' => 'Nama klasifikasi harus diisi.',
            'name.string' => 'Nama klasifikasi harus berupa teks.',
            'name.max' => 'Nama klasifikasi tidak boleh lebih dari 255 karakter.',
            'abbreviation.string' => 'Singkatan harus berupa teks.',
            'abbreviation.max' => 'Singkatan tidak boleh lebih dari 255 karakter.',
        ]);

        // If validation fails, redirect back with errors and input
        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        }

        // Create the new ship class
        $ship_class = new ShipClass;
        $ship_class->name = $validation->validated()['name'];
        $ship_class->abbreviation = $validation->validated()['abbreviation'] ?? null;
        $ship_class->save();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'message' => 'Klasifikasi berhasil ditambahkan.',
                'data' => [
                    'id' => $ship_class->id,
                    'name' => $ship_class->name,
                ],
            ], 201);
        }

        return redirect()->route('ship-classification.index')->with('success', 'Klasifikasi berhasil ditambahkan.');
    }

    /**
     * Delete a ship class record.
     */
    public function class_destroy($id)
    {
        // Delete the specified ship class
        $ship_class = ShipClass::findOrFail($id);
        $ship_class->delete();

        return redirect()->route('ship-classification.index')->with('success', 'Klasifikasi berhasil dihapus.');
    }

}
