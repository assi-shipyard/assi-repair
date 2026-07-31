<?php

namespace App\Http\Controllers;

use App\Models\NotificationSetting;
use App\Models\NotificationFlag;
use App\Models\Employee;
use Illuminate\Http\Request;

class NotificationSettingController extends Controller
{
    public function index()
    {
        $employees = Employee::whereNotNull('user_id')
            ->with('position.organizational_unit')
            ->orderBy('name')
            ->get();
            
        $flags = NotificationFlag::orderBy('name')->get();
        $settings = NotificationSetting::all()->groupBy('notification_flag_id');

        return view('notification-setting.index', compact('employees', 'flags', 'settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'nullable|array',
            'settings.*' => 'array',
            'settings.*.*' => 'exists:users,id',
        ]);

        NotificationSetting::truncate();

        $settings = $request->input('settings', []);
        $insertData = [];

        foreach ($settings as $flagId => $userIds) {
            foreach ($userIds as $userId) {
                $insertData[] = [
                    'notification_flag_id' => $flagId,
                    'user_id' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty($insertData)) {
            NotificationSetting::insert($insertData);
        }

        return redirect()->route('notification-settings.index')->with('success', 'Pengaturan notifikasi berhasil diperbarui.');
    }
}
