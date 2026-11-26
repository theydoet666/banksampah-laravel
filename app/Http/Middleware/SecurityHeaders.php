<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request and attach recommended security headers.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $viteHttp = '';
        $viteWs = '';
        if (app()->environment('local') && is_file(public_path('hot'))) {
            $hotUrl = rtrim((string) @file_get_contents(public_path('hot')));
            if ($hotUrl !== '' && filter_var($hotUrl, FILTER_VALIDATE_URL)) {
                // Browser menolak host-source IPv6 literal seperti http://[::1]:5173 di CSP;
                // gunakan scheme-source http:/ws: khusus saat Vite lokal berjalan di IPv6.
                if (str_contains($hotUrl, '[')) {
                    $viteHttp = ' http:';
                    $viteWs = ' ws:';
                } else {
                    $viteHttp = ' ' . $hotUrl;
                    $viteWs = ' ' . preg_replace('/^http/', 'ws', $hotUrl, 1);
                }
            }
        }

        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net{$viteHttp}; style-src 'self' 'unsafe-inline' https://fonts.bunny.net{$viteHttp}; font-src 'self' https://fonts.bunny.net data:; img-src 'self' data: blob:; connect-src 'self'{$viteHttp}{$viteWs}; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'self';"
        );

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
