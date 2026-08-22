<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuditLogService
{
    /** @var list<string> */
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'token',
        'remember_token',
    ];

    public function log_http_request(Request $request, Response $response): void
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return;
        }

        $route_name = $request->route()?->getName();

        $this->persist([
            'user_id' => $user->id,
            'employee_id' => $user->employee_id,
            'event_name' => 'http.' . strtolower($request->method()),
            'http_method' => $request->method(),
            'route_name' => $route_name,
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_payload' => $this->sanitize_payload($request->except(self::SENSITIVE_KEYS)),
            'response_status' => $response->getStatusCode(),
            'event_payload' => [
                'route_parameters' => $request->route()?->parameters() ?? [],
            ],
        ]);
    }

    /**
     * @param array<string, mixed> $event_payload
     */
    public function log_auth_event(string $event_name, ?User $user = null, array $event_payload = []): void
    {
        $this->persist([
            'user_id' => $user?->id,
            'employee_id' => $user?->employee_id,
            'event_name' => $event_name,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'event_payload' => $this->sanitize_payload($event_payload),
        ]);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function persist(array $attributes): void
    {
        try {
            if (! Schema::hasTable('audit_logs')) {
                return;
            }

            AuditLog::query()->create($attributes);
        } catch (Throwable) {
            // Do not block user flow when audit logging fails.
        }
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function sanitize_payload(array $payload): array
    {
        $sanitized_payload = [];

        foreach ($payload as $key => $value) {
            $sanitized_payload[(string) $key] = $this->sanitize_value((string) $key, $value);
        }

        return $sanitized_payload;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private function sanitize_value(string $key, mixed $value): mixed
    {
        if (in_array(strtolower($key), self::SENSITIVE_KEYS, true)) {
            return '[REDACTED]';
        }

        if ($value instanceof UploadedFile) {
            return [
                'file_name' => $value->getClientOriginalName(),
                'mime_type' => $value->getClientMimeType(),
                'size' => $value->getSize(),
            ];
        }

        if (is_array($value)) {
            $sanitized_values = [];
            foreach ($value as $nested_key => $nested_value) {
                $sanitized_values[(string) $nested_key] = $this->sanitize_value((string) $nested_key, $nested_value);
            }

            return $sanitized_values;
        }

        if (is_string($value) && mb_strlen($value) > 2000) {
            return mb_substr($value, 0, 2000) . '...';
        }

        return $value;
    }
}
