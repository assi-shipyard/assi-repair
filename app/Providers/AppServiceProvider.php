<?php

namespace App\Providers;

use App\Models\User;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        $this->register_indonesian_date_macros();

        Event::listen(Login::class, function (Login $event): void {
            $user = $event->user instanceof User ? $event->user : null;

            app(AuditLogService::class)->log_auth_event('auth.login_success', $user, [
                'guard' => $event->guard,
                'remember' => $event->remember,
            ]);
        });

        Event::listen(Failed::class, function (Failed $event): void {
            $user = $event->user instanceof User ? $event->user : null;

            app(AuditLogService::class)->log_auth_event('auth.login_failed', $user, [
                'guard' => $event->guard,
                'attempted_employee_id' => $event->credentials['employee_id'] ?? null,
            ]);
        });

        Event::listen(Logout::class, function (Logout $event): void {
            $user = $event->user instanceof User ? $event->user : null;

            app(AuditLogService::class)->log_auth_event('auth.logout', $user, [
                'guard' => $event->guard,
            ]);
        });
    }

    /**
     * Dates stay in the default database format; views call these macros to render Indonesian full dates.
     * Macros are registered on both Carbon variants because each keeps its own macro registry.
     */
    private function register_indonesian_date_macros(): void
    {
        foreach ([Carbon::class, CarbonImmutable::class] as $carbon_class) {
            $carbon_class::macro('tanggal_indonesia', function (): string {
                /** @var Carbon|CarbonImmutable $this */
                return $this->copy()->locale('id')->translatedFormat('j F Y');
            });

            $carbon_class::macro('tanggal_waktu_indonesia', function (bool $with_seconds = false): string {
                /** @var Carbon|CarbonImmutable $this */
                return $this->copy()->locale('id')->translatedFormat($with_seconds ? 'j F Y, H:i:s' : 'j F Y, H:i');
            });
        }
    }
}
