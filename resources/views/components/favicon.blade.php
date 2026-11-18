@php
    $favicon = appSetting('favicon');
    $logo = appSetting('logo');

    $faviconUrl = null;
    $faviconType = 'image/png';

    if ($favicon) {
        $faviconUrl = asset('storage/' . $favicon);
        $ext = strtolower(pathinfo($favicon, PATHINFO_EXTENSION));
        $faviconType = match ($ext) {
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };
    } elseif ($logo) {
        $faviconUrl = asset('storage/' . $logo);
        $ext = strtolower(pathinfo($logo, PATHINFO_EXTENSION));
        $faviconType = match ($ext) {
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };
    } else {
        $faviconUrl = "data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23059669'><path d='M4.5 9.5l3.5-6 3.5 6H9.2A5.5 5.5 0 0014 16.5h1v2h-1A7.5 7.5 0 017.3 11H4.5v-1.5zm15 5l-3.5 6-3.5-6h2.3A5.5 5.5 0 0010 7.5H9v-2h1a7.5 7.5 0 016.7 7.5h2.8v1.5z'/></svg>";
        $faviconType = 'image/svg+xml';
    }
@endphp
<link rel="icon" type="{{ $faviconType }}" href="{{ $faviconUrl }}">
@if($faviconUrl && !str_starts_with($faviconUrl, 'data:'))
<link rel="apple-touch-icon" href="{{ $faviconUrl }}">
@endif
