@props([
    'message' => 'Tidak ada data yang ditemukan',
    'submessage' => null,
    'icon' => null,
    'actionText' => null,
    'actionUrl' => null,
])

<div class="text-center py-12 px-4 space-y-4">
    <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-100 border border-slate-200/80 text-slate-400 flex items-center justify-center">
        @if($icon)
            {!! $icon !!}
        @else
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
        @endif
    </div>

    <div class="space-y-1">
        <h4 class="text-sm font-bold text-slate-700">{{ $message }}</h4>
        @if($submessage)
            <p class="text-xs text-slate-400 max-w-sm mx-auto">{{ $submessage }}</p>
        @endif
    </div>

    @if($actionText && $actionUrl)
        <div class="pt-2">
            <a href="{{ $actionUrl }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ $actionText }}
            </a>
        </div>
    @endif
</div>

