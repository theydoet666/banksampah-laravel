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

if (! function_exists('appLogoBase64')) {
    function appLogoBase64(): ?string
    {
        $logo = appSetting('logo');
        if (! $logo) {
            return null;
        }

        $candidates = [
            public_path('storage/' . $logo),
            storage_path('app/public/' . $logo),
        ];

        foreach ($candidates as $fullPath) {
            if (file_exists($fullPath) && is_file($fullPath)) {
                $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
                $mime = match($ext) {
                    'png' => 'image/png',
                    'jpg', 'jpeg' => 'image/jpeg',
                    'svg' => 'image/svg+xml',
                    'webp' => 'image/webp',
                    'ico' => 'image/x-icon',
                    default => 'image/png'
                };
                $content = file_get_contents($fullPath);
                return 'data:' . $mime . ';base64,' . base64_encode($content);
            }
        }

        return null;
    }
}

