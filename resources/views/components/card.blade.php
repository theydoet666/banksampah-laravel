<div {{ $attributes->merge(['class' => 'bg-white rounded-xl shadow-xs border border-slate-100 overflow-hidden w-full']) }}>
    @if(isset($title) || isset($action))
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-slate-100 flex items-center justify-between gap-2">
            @if(isset($title))
                <h3 class="font-semibold text-slate-800 text-sm sm:text-base truncate">{{ $title }}</h3>
            @endif
            @if(isset($action))
                <div class="shrink-0">{{ $action }}</div>
            @endif
        </div>
    @endif
    <div class="p-4 sm:p-6">
        {{ $slot }}
    </div>
</div>

