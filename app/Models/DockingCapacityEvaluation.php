<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DockingCapacityEvaluation extends Model
{
    protected $table = 'docking_capacity_evaluations';

    protected $fillable = [
        'project_docking_request_id',
        'docking_space_id',
        'ship_id',
        'is_compatible',
        'compatibility_score',
        'compatibility_detail',
        'ship_snapshot',
        'docking_space_snapshot',
        'evaluated_by',
        'evaluated_at',
    ];

    protected $casts = [
        'is_compatible' => 'boolean',
        'compatibility_detail' => 'array',
        'ship_snapshot' => 'array',
        'docking_space_snapshot' => 'array',
        'evaluated_at' => 'datetime',
    ];

    public function docking_request(): BelongsTo
    {
        return $this->belongsTo(ProjectDockingRequest::class, 'project_docking_request_id');
    }

    public function docking_space(): BelongsTo
    {
        return $this->belongsTo(DockingSpace::class, 'docking_space_id');
    }

    public function ship(): BelongsTo
    {
        return $this->belongsTo(Ship::class, 'ship_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }
}
