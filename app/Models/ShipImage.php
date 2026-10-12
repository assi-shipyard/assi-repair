<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicUniqueId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipImage extends Model
{
    use HasPublicUniqueId;

    protected $table = 'ship_images';

    protected $fillable = [
        'unique_id',
        'ship_id',
        'image_path',
    ];

    public function ship(): BelongsTo
    {
        return $this->belongsTo(Ship::class);
    }
}
