@extends('layouts.app')

@section('title', 'Pengaturan Sistem & Identitas')
@section('breadcrumb', 'Pengaturan / Identitas Bank Sampah')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <h2 class="text-xl font-bold text-slate-800">Pengaturan Identitas & Logo Bank Sampah</h2>
        <p class="text-xs text-slate-500">Kelola nama sistem, logo resmi, dan informasi kontak untuk kop surat, struk kasir, dan identitas aplikasi</p>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-xs">
        <form method="POST" action="{{ route('admin.pengaturan.update') }}" enctype="multipart/form-data" class="space-y-6" x-data="pengaturanForm()" @submit="submitting = true">
            @csrf
            @method('PUT')

            <!-- Branding: Logo & Favicon Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Logo Box -->
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-3 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Logo Utama Sistem</label>
                            <span class="text-[10px] text-slate-400 bg-white px-2 py-0.5 rounded-md border border-slate-200 font-mono">Max 2 MB</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <!-- Preview Box -->
                            <div class="w-20 h-20 rounded-2xl bg-white border-2 border-dashed border-slate-200 flex items-center justify-center overflow-hidden shrink-0 shadow-xs p-2">
                                <template x-if="imageUrl">
                                    <img :src="imageUrl" class="w-full h-full object-contain" alt="Preview Logo">
                                </template>
                                <template x-if="!imageUrl">
                                    @if(appSetting('logo'))
                                        <img src="{{ asset('storage/' . appSetting('logo')) }}" class="w-full h-full object-contain" alt="Logo Saat Ini">
                                    @else
                                        <div class="text-center text-slate-400">
                                            <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <span class="text-[9px] block mt-0.5">Belum Ada</span>
                                        </div>
                                    @endif
                                </template>
                            </div>

                            <!-- Upload Controls -->
                            <div class="space-y-1.5 flex-1">
                                <input type="file" name="logo" id="logo_input" accept="image/png,image/jpeg,image/webp,image/svg+xml" @change="previewImage($event)" class="hidden">
                                <label for="logo_input" class="inline-flex items-center gap-2 px-3 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold cursor-pointer shadow-xs transition-colors">
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    Pilih Logo
                                </label>
                                <p class="text-[10px] text-slate-400 leading-tight">PNG, JPG, SVG, WEBP. Tampil di sidebar, struk kasir, dan kartu anggota.</p>
                                @error('logo') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Favicon Box -->
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-3 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Favicon Tab Browser</label>
                            <span class="text-[10px] text-slate-400 bg-white px-2 py-0.5 rounded-md border border-slate-200 font-mono">Max 1 MB</span>
                        </div>

                        <!-- Mini Browser Mockup Preview -->
                        <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden mb-3">
                            <!-- Browser Chrome Bar -->
                            <div class="bg-slate-100 px-2.5 py-1.5 flex items-center gap-2 border-b border-slate-200">
                                <div class="flex items-center gap-1 shrink-0">
                                    <div class="w-2 h-2 rounded-full bg-rose-400"></div>
                                    <div class="w-2 h-2 rounded-full bg-amber-400"></div>
                                    <div class="w-2 h-2 rounded-full bg-emerald-400"></div>
                                </div>
                                <!-- Simulated Active Tab -->
                                <div class="bg-white px-2.5 py-1 rounded-t-lg border-t border-x border-slate-200 text-slate-700 flex items-center gap-1.5 shadow-2xs max-w-[180px]">
                                    <!-- Favicon in Tab -->
                                    <div class="w-3.5 h-3.5 flex items-center justify-center shrink-0">
                                        <template x-if="faviconUrl && !hapusFavicon">
                                            <img :src="faviconUrl" class="w-3.5 h-3.5 object-contain" alt="Favicon Preview">
                                        </template>
                                        <template x-if="!faviconUrl || hapusFavicon">
                                            @if(appSetting('favicon'))
                                                <img src="{{ asset('storage/' . appSetting('favicon')) }}" class="w-3.5 h-3.5 object-contain" alt="Favicon Saat Ini" x-show="!hapusFavicon">
                                                <svg x-show="hapusFavicon" class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M4.5 9.5l3.5-6 3.5 6H9.2A5.5 5.5 0 0014 16.5h1v2h-1A7.5 7.5 0 017.3 11H4.5v-1.5zm15 5l-3.5 6-3.5-6h2.3A5.5 5.5 0 0010 7.5H9v-2h1a7.5 7.5 0 016.7 7.5h2.8v1.5z"/></svg>
                                            @elseif(appSetting('logo'))
                                                <img src="{{ asset('storage/' . appSetting('logo')) }}" class="w-3.5 h-3.5 object-contain" alt="Logo Favicon">
                                            @else
                                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M4.5 9.5l3.5-6 3.5 6H9.2A5.5 5.5 0 0014 16.5h1v2h-1A7.5 7.5 0 017.3 11H4.5v-1.5zm15 5l-3.5 6-3.5-6h2.3A5.5 5.5 0 0010 7.5H9v-2h1a7.5 7.5 0 016.7 7.5h2.8v1.5z"/></svg>
                                            @endif
                                        </template>
                                    </div>
                                    <span class="text-[10px] font-medium text-slate-700 truncate leading-none">{{ appSetting('nama_bank_sampah', 'Bank Sampah BAA') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Controls -->
                        <div class="flex items-center gap-3">
                            <input type="file" name="favicon" id="favicon_input" accept="image/x-icon,image/png,image/svg+xml,image/webp,image/jpeg" @change="previewFavicon($event)" class="hidden">
                            <label for="favicon_input" class="inline-flex items-center gap-2 px-3 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold cursor-pointer shadow-xs transition-colors shrink-0">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                Pilih Favicon
                            </label>

                            @if(appSetting('favicon'))
                                <label class="inline-flex items-center gap-1.5 text-[11px] text-slate-600 cursor-pointer">
                                    <input type="checkbox" name="hapus_favicon" value="1" x-model="hapusFavicon" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500 text-xs">
                                    <span class="text-rose-600 font-medium">Hapus Favicon</span>
                                </label>
                            @endif
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1.5 leading-tight">ICO, PNG, SVG, WEBP. Disarankan persegi 32x32 atau 64x64 piksel.</p>
                        @error('favicon') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Nama & Tagline -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nama Sistem / Bank Sampah <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_bank_sampah" value="{{ old('nama_bank_sampah', appSetting('nama_bank_sampah', 'Bank Sampah BAA')) }}" required class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 font-semibold text-slate-800">
                    @error('nama_bank_sampah') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Tagline / Slogan</label>
                    <input type="text" name="tagline" value="{{ old('tagline', appSetting('tagline', 'Sistem Informasi Manajemen Terpadu')) }}" placeholder="Contoh: Menabung Sampah, Merawat Bumi" class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                </div>
            </div>

            <!-- Alamat -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Alamat Kantor / Pusat Operasional <span class="text-rose-500">*</span></label>
                <textarea name="alamat" rows="2" required class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">{{ old('alamat', appSetting('alamat', 'Jl. Raya Desa No. 12, Balai Desa')) }}</textarea>
                @error('alamat') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Kontak & Identitas -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">No. Kontak / WhatsApp <span class="text-rose-500">*</span></label>
                    <input type="text" name="no_telp" value="{{ old('no_telp', appSetting('no_telp', '081234567890')) }}" required class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 font-mono">
                    @error('no_telp') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Email Resmi <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', appSetting('email', 'info@banksampah.test')) }}" required class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                    @error('email') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Tahun Berdiri</label>
                    <input type="text" name="tahun_berdiri" value="{{ old('tahun_berdiri', appSetting('tahun_berdiri', '2024')) }}" maxlength="4" placeholder="2024" class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 font-mono">
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end">
                <button type="submit" x-bind:disabled="submitting" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs inline-flex items-center gap-2 disabled:bg-emerald-400 transition-colors">
                    <svg x-show="submitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" style="display: none;">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span x-text="submitting ? 'Menyimpan...' : 'Simpan Pengaturan'">Simpan Pengaturan</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function pengaturanForm() {
    return {
        imageUrl: null,
        faviconUrl: null,
        submitting: false,
        hapusFavicon: false,
        previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                this.imageUrl = URL.createObjectURL(file);
            }
        },
        previewFavicon(event) {
            const file = event.target.files[0];
            if (file) {
                this.faviconUrl = URL.createObjectURL(file);
                this.hapusFavicon = false;
            }
        }
    };
}
</script>
@endpush
@endsection
