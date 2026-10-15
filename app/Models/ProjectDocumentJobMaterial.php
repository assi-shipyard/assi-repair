<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUniqueId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectDocumentJobMaterial extends Model
{
    use HasPublicUniqueId;

    protected $table = 'project_document_job_materials';

    protected $fillable = [
        'unique_id',
        'project_document_job_id',
        'price_book_item_id',
        'calculation_rule',
        'material_name',
        'owner_supplied',
        'volume',
        'unit',
        'unit_price',
        'minimum_quantity',
        'billed_quantity',
        'sort_order',
        'density',
        'dimension',
        'price',
        'currency',
        'diameter',
        'length',
        'thickness',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'unique_id' => 'string',
            'project_document_job_id' => 'integer',
            'price_book_item_id' => 'integer',
            'owner_supplied' => 'boolean',
            'volume' => 'decimal:4',
            'unit_price' => 'decimal:2',
            'minimum_quantity' => 'decimal:4',
            'billed_quantity' => 'decimal:4',
            'sort_order' => 'integer',
            'density' => 'decimal:6',
            'price' => 'decimal:2',
            'diameter' => 'decimal:4',
            'length' => 'decimal:4',
            'thickness' => 'decimal:4',
        ];
    }

    public function price_book_item(): BelongsTo
    {
        return $this->belongsTo(PriceBookItem::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(ProjectDocumentJob::class, 'project_document_job_id');
    }
}
