<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Paksa user mengganti password jika flag must_change_password aktif.
     * Dikecualikan untuk: halaman ganti password itu sendiri, logout, profil edit.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password) {
            // Tentukan route profil sesuai role
            $allowedRoutes = ['logout', 'profile.edit', 'profile.update', 'nasabah.profil.edit', 'nasabah.profil.update', 'nasabah.profil.password'];

            if (! $request->routeIs(...$allowedRoutes)) {
                $message = 'Demi keamanan, Anda wajib mengganti password sebelum melanjutkan.';

                if ($user->isNasabah()) {
                    return redirect()->route('nasabah.profil.edit')->with('warning', $message);
                }

                return redirect()->route('profile.edit')->with('warning', $message);
            }
        }

        return $next($request);
    }
}
