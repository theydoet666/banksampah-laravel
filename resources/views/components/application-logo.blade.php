@if(appSetting('logo'))
    <img src="{{ asset('storage/' . appSetting('logo')) }}" alt="{{ appSetting('nama_bank_sampah', 'Bank Sampah BAA') }}" {{ $attributes->merge(['class' => 'object-contain']) }}>
@else
    <div {{ $attributes->merge(['class' => 'rounded-2xl bg-emerald-600 flex items-center justify-center text-white shadow-lg shadow-emerald-900/30 font-bold']) }}>
        <svg class="w-2/3 h-2/3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
    </div>
@endif
