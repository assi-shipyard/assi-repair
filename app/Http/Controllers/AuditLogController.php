<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'event_name' => 'nullable|string|max:64',
            'employee_id' => 'nullable|string|max:64',
            'route_name' => 'nullable|string|max:191',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ], [
            'to_date.after_or_equal' => 'Tanggal sampai harus lebih besar atau sama dengan tanggal dari.',
        ]);

        $audit_logs_query = AuditLog::query()
            ->with('user')
            ->latest('created_at');

        if (! empty($validated['event_name'])) {
            $audit_logs_query->where('event_name', 'like', '%' . $validated['event_name'] . '%');
        }

        if (! empty($validated['employee_id'])) {
            $audit_logs_query->where('employee_id', 'like', '%' . $validated['employee_id'] . '%');
        }

        if (! empty($validated['route_name'])) {
            $audit_logs_query->where('route_name', 'like', '%' . $validated['route_name'] . '%');
        }

        if (! empty($validated['from_date'])) {
            $audit_logs_query->whereDate('created_at', '>=', $validated['from_date']);
        }

        if (! empty($validated['to_date'])) {
            $audit_logs_query->whereDate('created_at', '<=', $validated['to_date']);
        }

        $audit_logs = $audit_logs_query->paginate(25)->withQueryString();

        return view('audit-log.index', [
            'audit_logs' => $audit_logs,
            'filters' => $validated,
        ]);
    }
}
