<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DockingRequestPostponement extends Model
{
    protected $fillable = [
        'project_docking_request_id',
        'docking_space_id',
        'previous_start_at',
        'previous_end_at',
        'new_start_at',
        'new_end_at',
        'reason',
        'postponed_by',
    ];

    protected $casts = [
        'previous_start_at' => 'datetime',
        'previous_end_at' => 'datetime',
        'new_start_at' => 'datetime',
        'new_end_at' => 'datetime',
    ];

    public function docking_request(): BelongsTo
    {
        return $this->belongsTo(ProjectDockingRequest::class, 'project_docking_request_id');
    }

    public function postponer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'postponed_by');
    }
}
