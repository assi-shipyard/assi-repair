<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicUniqueId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceBook extends Model
{
    use HasPublicUniqueId;

    protected $fillable = ['code', 'name', 'reference_slot', 'company_id', 'description'];

    protected $casts = ['reference_slot' => 'integer'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PriceBookItem::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
