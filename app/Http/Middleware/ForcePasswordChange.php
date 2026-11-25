<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Paksa user mengganti password jika flag must_change_password aktif.
     * Dikecualikan untuk: formulir khusus ganti password pertama dan logout.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password) {
            $allowedRoutes = [
                'password.first_login',
                'password.first_login.update',
                'logout',
                'profile.edit',
                'profile.update',
                'password.update',
                'nasabah.profil.edit',
                'nasabah.profil.update',
                'nasabah.profil.password',
            ];

            if (! $request->routeIs(...$allowedRoutes)) {
                return redirect()->route('password.first_login')
                    ->with('warning', 'Demi keamanan, Anda wajib mengganti kata sandi bawaan terlebih dahulu sebelum melanjutkan.');
            }
        }

        return $next($request);
    }
}
