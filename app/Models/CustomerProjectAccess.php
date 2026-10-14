<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicUniqueId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerProjectAccess extends Model
{
    use HasPublicUniqueId;

    protected $table = 'customer_project_access';

    protected $fillable = [
        'user_id', 'project_id', 'is_active', 'view_prices',
        'approve_initial_boq', 'approve_satisfaction_notes', 'approve_final_boq', 'access_version',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'view_prices' => 'boolean',
            'approve_initial_boq' => 'boolean',
            'approve_satisfaction_notes' => 'boolean',
            'approve_final_boq' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function can_approve(string $document_type): bool
    {
        return $this->is_active && match ($document_type) {
            'initial_boq' => $this->approve_initial_boq && $this->view_prices,
            'satisfaction_notes' => $this->approve_satisfaction_notes,
            'final_boq' => $this->approve_final_boq && $this->view_prices,
            default => false,
        };
    }
}
