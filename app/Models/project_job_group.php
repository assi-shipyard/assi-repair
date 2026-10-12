<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicUniqueId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class project_job_group extends Model
{
    use HasPublicUniqueId;

    protected $fillable = ['name', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(ProjectDocumentJob::class, 'project_job_group_id');
    }
}
