<div {{ $attributes->merge(['class' => 'bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden']) }}>
    @if(isset($title) || isset($action))
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            @if(isset($title))
                <h3 class="font-semibold text-slate-800 text-base">{{ $title }}</h3>
            @endif
            @if(isset($action))
                <div>{{ $action }}</div>
            @endif
        </div>
    @endif
    <div class="p-6">
        {{ $slot }}
    </div>
</div>

