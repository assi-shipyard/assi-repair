<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasPublicUniqueId
{
    protected static function bootHasPublicUniqueId(): void
    {
        static::creating(function ($model): void {
            if (empty($model->unique_id)) {
                $model->unique_id = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'unique_id';
    }
}
