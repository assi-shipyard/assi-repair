<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicUniqueId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class project_contract_document extends Model
{
    use HasPublicUniqueId;

    public const DISK = 'ship';

    protected $fillable = [
        'unique_id', 'project_contract_id', 'document_name', 'document_path', 'status', 'uploaded_by',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(project_contract::class, 'project_contract_id');
    }
}
