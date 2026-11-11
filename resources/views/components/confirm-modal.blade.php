@props([
    'name' => 'confirm-modal',
    'title' => 'Konfirmasi Aksi',
    'message' => 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
    'confirmText' => 'Ya, Lanjutkan',
    'cancelText' => 'Batal',
    'confirmClass' => 'bg-emerald-600 hover:bg-emerald-700 text-white',
])

<div x-data="{ 
        open: false, 
        actionUrl: '', 
        formMethod: 'POST',
        methodField: '',
        modalTitle: '{{ $title }}',
        modalMessage: '{{ $message }}',
        modalConfirmText: '{{ $confirmText }}',
        modalConfirmClass: '{{ $confirmClass }}'
    }"
    @open-confirm-modal.window="
        open = true;
        actionUrl = $event.detail.action || '';
        formMethod = $event.detail.method === 'GET' ? 'GET' : 'POST';
        methodField = ['PUT', 'PATCH', 'DELETE'].includes($event.detail.method?.toUpperCase()) ? $event.detail.method.toUpperCase() : '';
        if ($event.detail.title) modalTitle = $event.detail.title;
        if ($event.detail.message) modalMessage = $event.detail.message;
        if ($event.detail.confirmText) modalConfirmText = $event.detail.confirmText;
        if ($event.detail.confirmClass) modalConfirmClass = $event.detail.confirmClass;
    "
    @keydown.escape.window="open = false"
    x-show="open" 
    class="fixed inset-0 z-50 overflow-y-auto" 
    style="display: none;">
    
    <!-- Backdrop -->
    <div x-show="open" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
         @click="open = false"></div>

    <!-- Modal Content -->
    <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
        <div x-show="open" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-100">
            
            <div class="bg-white p-6 sm:p-7">
                <div class="flex items-start gap-4">
                    <div class="mx-auto flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 sm:mx-0 border border-amber-100">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="text-left mt-0.5">
                        <h3 class="text-base font-bold text-slate-800" x-text="modalTitle"></h3>
                        <div class="mt-1.5">
                            <p class="text-sm text-slate-500 leading-relaxed" x-text="modalMessage"></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 px-6 py-4 flex flex-row-reverse gap-3 border-t border-slate-100">
                <form :action="actionUrl" :method="formMethod" class="inline">
                    @csrf
                    <template x-if="methodField">
                        <input type="hidden" name="_method" :value="methodField">
                    </template>
                    <button type="submit" 
                            :class="modalConfirmClass"
                            class="inline-flex w-full justify-center rounded-xl px-4 py-2.5 text-sm font-semibold shadow-xs sm:w-auto transition-colors"
                            x-text="modalConfirmText">
                    </button>
                </form>
                <button type="button" 
                        class="inline-flex w-full justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-xs ring-1 ring-inset ring-slate-200 hover:bg-slate-100 sm:w-auto transition-colors"
                        @click="open = false">
                    {{ $cancelText }}
                </button>
            </div>
        </div>
    </div>
</div>

