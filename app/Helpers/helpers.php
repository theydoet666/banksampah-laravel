<?php

if (! function_exists('formatRupiah')) {
    function formatRupiah($angka): string
    {
        return 'Rp ' . number_format((float) $angka, 0, ',', '.');
    }
}

if (! function_exists('appSetting')) {
    function appSetting(string $key, $default = null)
    {
        return \App\Models\AppSetting::get($key, $default);
    }
}

if (! function_exists('roleRoute')) {
    function roleRoute(string $name, $parameters = [], bool $absolute = true): string
    {
        $prefix = auth()->user()?->isPetugas() ? 'petugas.' : 'admin.';
        return route($prefix . $name, $parameters, $absolute);
    }
}

