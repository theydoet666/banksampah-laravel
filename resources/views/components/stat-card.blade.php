@props(['title', 'value', 'icon' => null, 'color' => 'green'])

@php
    $colorClasses = [
        'green' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
        'blue' => 'bg-sky-50 text-sky-600 border-sky-100',
        'yellow' => 'bg-amber-50 text-amber-600 border-amber-100',
        'red' => 'bg-rose-50 text-rose-600 border-rose-100',
    ];
    $iconColor = $colorClasses[$color] ?? $colorClasses['green'];
@endphp

<div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 flex items-center justify-between">
    <div>
        <p class="text-sm font-medium text-slate-500 mb-1">{{ $title }}</p>
        <h4 class="text-2xl font-bold text-slate-800 tracking-tight">{{ $value }}</h4>
    </div>
    @if($icon)
        <div class="w-12 h-12 rounded-xl flex items-center justify-center border {{ $iconColor }}">
            {!! $icon !!}
        </div>
    @endif
</div>

