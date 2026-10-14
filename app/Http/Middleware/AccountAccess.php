<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AccountAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            abort(403, 'Akun tidak aktif. Hubungi administrator.');
        }

        if (! $request->routeIs('logout')) {
            abort_unless($user->is_customer() === $request->routeIs('customer.*'), 403, 'Akses tidak diizinkan.');
        }

        if ($user->is_customer() && $user->must_change_password && ! $request->routeIs('customer.settings', 'customer.password', 'logout')) {
            return redirect()->route('customer.settings')->with('error', 'Ubah kata sandi awal sebelum mengakses proyek.');
        }

        $response = $next($request);
        if ($user->is_customer()) {
            $response->headers->set('Cache-Control', 'private, no-store, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Referrer-Policy', 'same-origin');
        }

        return $response;
    }
}
