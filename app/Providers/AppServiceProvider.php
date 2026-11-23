<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use App\Services\AuditLogService;

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
        Event::listen(Login::class, function (Login $event) {
            $user = $event->user;
            AuditLogService::log(
                action: 'login',
                module: 'Autentikasi',
                description: "Pengguna {$user->name} ({$user->email}) berhasil login ke sistem.",
                subject: $user,
                user: $user
            );
        });

        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                AuditLogService::log(
                    action: 'logout',
                    module: 'Autentikasi',
                    description: "Pengguna {$event->user->name} keluar (logout) dari sistem.",
                    subject: $event->user,
                    user: $event->user
                );
            }
        });

        Event::listen(Failed::class, function (Failed $event) {
            $identifier = $event->credentials['email'] ?? $event->credentials['no_rekening'] ?? $event->credentials['no_hp'] ?? 'tidak diketahui';
            AuditLogService::log(
                action: 'failed_login',
                module: 'Autentikasi',
                description: "Percobaan login gagal untuk identitas: {$identifier}.",
                user: $event->user
            );
        });
    }
}
