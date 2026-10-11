<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicUniqueId;
use Illuminate\Database\Eloquent\Model;

class RepairType extends Model
{
    use HasPublicUniqueId;

    public const CATEGORIES = [
        'docking' => 'Docking',
        'hull' => 'Lambung & Konstruksi',
        'painting' => 'Pengecatan',
        'machinery' => 'Permesinan',
        'piping' => 'Perpipaan',
        'electrical' => 'Kelistrikan',
        'other' => 'Lainnya',
    ];

    protected $fillable = [
        'code',
        'name',
        'category',
        'unit',
        'standard_price',
        'description',
        'is_active',
    ];

    protected $casts = [
        'standard_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
