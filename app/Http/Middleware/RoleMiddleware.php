<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if (! in_array($request->user()->role, $roles)) {
            // Jika petugas membuka URL struk admin, arahkan otomatis ke struk petugas
            if ($request->user()->isPetugas() && $request->is('admin/transaksi/*/struk')) {
                $target = str_replace('admin/transaksi/', 'petugas/transaksi/', $request->getRequestUri());
                return redirect()->to($target);
            }

            abort(403, 'Akses Ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}

