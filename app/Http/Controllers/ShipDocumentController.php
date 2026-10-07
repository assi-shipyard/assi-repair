<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Ship;
use App\Models\ShipDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ShipDocumentController extends Controller
{
    private const DISK = 'local';

    private const DIRECTORY = 'ship_documents';

    public function store(Request $request, string $ship): RedirectResponse
    {
        $ship_model = Ship::query()->firstWhere('unique_id', $ship);

        if (! $ship_model) {
            return redirect()->route('ship.index')->with('error', 'Kapal tidak ditemukan.');
        }

        $validation = Validator::make($request->all(), [
            'document_name' => 'required|string|max:255',
            'document_type' => 'required|string|max:255',
            'document_file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
        ], [
            'document_name.required' => 'Nama dokumen wajib diisi.',
            'document_name.max' => 'Nama dokumen maksimal 255 karakter.',
            'document_type.required' => 'Tipe dokumen wajib diisi.',
            'document_type.max' => 'Tipe dokumen maksimal 255 karakter.',
            'document_file.required' => 'File dokumen wajib diunggah.',
            'document_file.file' => 'File dokumen harus berupa file yang valid.',
            'document_file.mimes' => 'Format file harus pdf, doc, docx, xls, xlsx, jpg, jpeg, atau png.',
            'document_file.max' => 'Ukuran file maksimal 10MB.',
        ]);

        if ($validation->fails()) {
            return back()->withErrors($validation)->withInput();
        }

        $data = $validation->validated();
        // Random stored name; the original client name is never used on disk.
        $path = $data['document_file']->store(self::DIRECTORY, self::DISK);

        $ship_model->documents()->create([
            'unique_id' => (string) Str::uuid(),
            'document_name' => $data['document_name'],
            'document_type' => $data['document_type'],
            'document_path' => $path,
        ]);

        return redirect()->route('ship.show', $ship_model->unique_id)->with('success', 'Dokumen kapal berhasil diunggah.');
    }

    public function download(string $ship, string $document): StreamedResponse|RedirectResponse
    {
        $ship_model = Ship::query()->firstWhere('unique_id', $ship);
        $document_model = $ship_model?->documents()->firstWhere('unique_id', $document);

        if (! $document_model || ! Storage::disk(self::DISK)->exists($document_model->document_path)) {
            return redirect()->route('ship.show', $ship)->with('error', 'Dokumen tidak ditemukan.');
        }

        $extension = pathinfo($document_model->document_path, PATHINFO_EXTENSION);
        $download_name = Str::slug($document_model->document_name) . ($extension !== '' ? '.' . $extension : '');

        return Storage::disk(self::DISK)->download($document_model->document_path, $download_name);
    }

    public function destroy(string $ship, string $document): RedirectResponse
    {
        $ship_model = Ship::query()->firstWhere('unique_id', $ship);
        $document_model = $ship_model?->documents()->firstWhere('unique_id', $document);

        if (! $document_model) {
            return redirect()->route('ship.show', $ship)->with('error', 'Dokumen tidak ditemukan.');
        }

        Storage::disk(self::DISK)->delete($document_model->document_path);
        $document_model->delete();

        return redirect()->route('ship.show', $ship)->with('success', 'Dokumen kapal berhasil dihapus.');
    }
}
