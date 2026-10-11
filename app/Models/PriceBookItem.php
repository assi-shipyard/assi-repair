<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicUniqueId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceBookItem extends Model
{
    use HasPublicUniqueId;

    public const GROUPS = ['material' => 'Material', 'service' => 'Jasa'];

    protected $fillable = [
        'group', 'code', 'name', 'unit', 'unit_price',
        'has_minimum_charge', 'minimum_quantity', 'description',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'has_minimum_charge' => 'boolean',
        'minimum_quantity' => 'decimal:3',
    ];

    public function price_book(): BelongsTo
    {
        return $this->belongsTo(PriceBook::class);
    }
}
