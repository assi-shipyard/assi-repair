<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'audit_logs';

    protected $fillable = [
        'unique_id',
        'user_id',
        'employee_id',
        'event_name',
        'http_method',
        'route_name',
        'url',
        'ip_address',
        'user_agent',
        'request_payload',
        'event_payload',
        'response_status',
        'created_at',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'event_payload' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (AuditLog $audit_log): void {
            if (empty($audit_log->unique_id)) {
                $audit_log->unique_id = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
