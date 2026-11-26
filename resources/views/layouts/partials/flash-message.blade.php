<div class="space-y-3">
    @if(session('success'))
        <div x-data="{ show: true, copied: false }" 
             x-show="show" 
             @if(!session('generated_password'))
             x-init="setTimeout(() => show = false, 8000)"
             @endif
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="-translate-y-4 opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-y-0 opacity-100"
             x-transition:leave-end="-translate-y-4 opacity-0"
             class="flex items-start justify-between p-4 bg-emerald-50/90 backdrop-blur-xs border border-emerald-200 text-emerald-800 rounded-xl shadow-xs">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <h5 class="text-xs font-bold uppercase tracking-wider text-emerald-700">Berhasil</h5>
                    <p class="text-sm font-medium text-emerald-900">{{ session('success') }}</p>
                    @if(session('generated_password'))
                        <div class="mt-2.5 p-2.5 bg-white border border-emerald-300 rounded-lg inline-flex flex-wrap items-center gap-2 shadow-xs">
                            <span class="text-xs text-slate-500 font-medium">Kata Sandi Sementara:</span>
                            <code id="generated-temp-password" class="px-2 py-0.5 bg-emerald-100 text-emerald-900 font-mono font-bold text-sm rounded select-all">{{ session('generated_password') }}</code>
                            <button type="button"
                                    @click="navigator.clipboard.writeText(@js(session('generated_password'))); copied = true; setTimeout(() => copied = false, 2000)"
                                    class="px-2.5 py-1 rounded-md text-xs font-semibold border transition-colors"
                                    :class="copied ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200'">
                                <span x-text="copied ? 'Tersalin!' : 'Salin'"></span>
                            </button>
                            <span class="text-2xs text-amber-600 font-semibold">(Hanya tampil sekali ini &amp; wajib diganti saat login pertama)</span>
                        </div>
                    @endif
                </div>
            </div>
            <button @click="show = false" class="p-1 rounded-lg text-emerald-500 hover:text-emerald-800 hover:bg-emerald-100/50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div x-data="{ show: true }" 
             x-show="show" 
             x-init="setTimeout(() => show = false, 7000)"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="-translate-y-4 opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-y-0 opacity-100"
             x-transition:leave-end="-translate-y-4 opacity-0"
             class="flex items-center justify-between p-4 bg-rose-50/90 backdrop-blur-xs border border-rose-200 text-rose-800 rounded-xl shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <div>
                    <h5 class="text-xs font-bold uppercase tracking-wider text-rose-700">Perhatian / Gagal</h5>
                    <p class="text-sm font-medium text-rose-900">{{ session('error') }}</p>
                </div>
            </div>
            <button @click="show = false" class="p-1 rounded-lg text-rose-500 hover:text-rose-800 hover:bg-rose-100/50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    @if(session('warning'))
        <div x-data="{ show: true }" 
             x-show="show" 
             x-init="setTimeout(() => show = false, 6000)"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="-translate-y-4 opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-y-0 opacity-100"
             x-transition:leave-end="-translate-y-4 opacity-0"
             class="flex items-center justify-between p-4 bg-amber-50/90 backdrop-blur-xs border border-amber-200 text-amber-800 rounded-xl shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h5 class="text-xs font-bold uppercase tracking-wider text-amber-700">Pemberitahuan</h5>
                    <p class="text-sm font-medium text-amber-900">{{ session('warning') }}</p>
                </div>
            </div>
            <button @click="show = false" class="p-1 rounded-lg text-amber-500 hover:text-amber-800 hover:bg-amber-100/50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif
</div>
