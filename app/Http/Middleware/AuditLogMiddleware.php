<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\AuditLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditLogMiddleware
{
    public function __construct(private readonly AuditLogService $audit_log_service)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->should_log_request($request)) {
            return $response;
        }

        $this->audit_log_service->log_http_request($request, $response);

        return $response;
    }

    private function should_log_request(Request $request): bool
    {
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return false;
        }

        if ($request->routeIs('login.process') || $request->routeIs('admin.login.process')) {
            return false;
        }

        return true;
    }
}
