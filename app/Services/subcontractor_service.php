<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Subcontractor;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class subcontractor_service
{
    public function save_vendor(array $data, User $user, ?Subcontractor $vendor = null): Subcontractor
    {
        return DB::transaction(function () use ($data, $user, $vendor): Subcontractor {
            $is_new = $vendor === null;
            $vendor = $is_new ? new Subcontractor : Subcontractor::query()->lockForUpdate()->findOrFail($vendor->id);
            $before = $vendor->getAttributes();
            $vendor->fill($data);
            $vendor->save();

            AuditLog::query()->create([
                'user_id' => $user->id,
                'employee_id' => $user->employee_id,
                'event_name' => $is_new ? 'subcontractor.created' : 'subcontractor.updated',
                'event_payload' => [
                    'resource_uuid' => $vendor->unique_id,
                    'before' => $before,
                    'after' => $vendor->getAttributes(),
                ],
            ]);

            return $vendor;
        });
    }
}
