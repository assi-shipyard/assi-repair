<?php

namespace App\Http\Controllers;

use App\Models\NotificationFlag;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NotificationFlagController extends Controller
{
    public function index()
    {
        $flags = NotificationFlag::orderBy('name')->get();
        return view('notification-flag.index', compact('flags'));
    }

    public function create()
    {
        return view('notification-flag.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:191|unique:notification_flags,code',
            'name' => 'required|string|max:191',
            'description' => 'nullable|string',
        ]);

        NotificationFlag::create($data);

        return redirect()->route('notification-flag.index')->with('success', 'Tipe Notifikasi (Flag) berhasil ditambahkan.');
    }

    public function edit(string $id)
    {
        $flag = NotificationFlag::findOrFail($id);
        return view('notification-flag.edit', compact('flag'));
    }

    public function update(Request $request, string $id)
    {
        $flag = NotificationFlag::findOrFail($id);
        
        $data = $request->validate([
            'code' => ['required', 'string', 'max:191', Rule::unique('notification_flags')->ignore($flag->id)],
            'name' => 'required|string|max:191',
            'description' => 'nullable|string',
        ]);

        $flag->update($data);

        return redirect()->route('notification-flag.index')->with('success', 'Tipe Notifikasi (Flag) berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $flag = NotificationFlag::findOrFail($id);
        $flag->delete();

        return redirect()->route('notification-flag.index')->with('success', 'Tipe Notifikasi (Flag) berhasil dihapus.');
    }
}
