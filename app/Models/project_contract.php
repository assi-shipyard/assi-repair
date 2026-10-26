<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class project_contract extends Model
{
    public const STATUS_LABELS = [
        'drafting' => 'Kontrak sedang disusun',
        'awaiting_shipyard_signature' => 'Menunggu tanda tangan direktur galangan',
        'signed_by_shipyard' => 'Ditandatangani pihak pertama (galangan)',
        'sent_to_customer' => 'Dikirim ke pihak kedua (pelanggan)',
        'signed_by_customer' => 'Ditandatangani pihak kedua (pelanggan)',
    ];

    protected $fillable = ['project_id', 'contract_number', 'status', 'signing_due_date'];

    protected function casts(): array
    {
        return ['signing_due_date' => 'date'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(project_contract_document::class)->latest('id');
    }

    public function remaining_days(): ?int
    {
        if ($this->status === 'signed_by_customer' || $this->signing_due_date === null) {
            return null;
        }

        $today = now('Asia/Jakarta')->startOfDay();

        return (int) $today->diffInDays($this->signing_due_date->format('Y-m-d'), false);
    }
}
