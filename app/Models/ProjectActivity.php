<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProjectActivity extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'project_activities';

    protected $fillable = [
        'unique_id',
        'project_id',
        'actor_id',
        'actor_name',
        'event',
        'description',
        'changes',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ProjectActivity $activity): void {
            $activity->unique_id ??= (string) Str::uuid();
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
