<aside class="fixed inset-y-0 left-0 z-30 w-64 bg-slate-900 text-slate-300 transition-all duration-300 ease-in-out flex flex-col h-screen"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">
    
    <!-- Brand Header (Fixed Top) -->
    <div class="h-16 flex items-center gap-3 px-5 bg-slate-950/60 border-b border-slate-800/80 shrink-0">
        @if(appSetting('logo'))
            <img src="{{ asset('storage/' . appSetting('logo')) }}" alt="Logo" class="w-9 h-9 object-contain rounded-lg bg-white/10 p-1 shrink-0">
        @else
            <div class="w-9 h-9 rounded-lg bg-emerald-600 flex items-center justify-center text-white shadow-md shadow-emerald-900/30 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </div>
        @endif
        <div class="overflow-hidden">
            <h1 class="text-sm font-bold text-white tracking-tight leading-tight truncate" title="{{ appSetting('nama_bank_sampah', 'Bank Sampah BAA') }}">
                {{ appSetting('nama_bank_sampah', 'Bank Sampah BAA') }}
            </h1>
            <span class="text-[10px] text-emerald-400 font-medium truncate block">
                {{ appSetting('tagline', 'Sistem Terintegrasi') }}
            </span>
        </div>
    </div>

    <!-- Scrollable Navigation Area -->
    <div class="flex-1 overflow-y-auto overflow-x-hidden py-3 space-y-3 custom-scrollbar">
        <!-- User Profile Card -->
        <div class="p-3 mx-4 rounded-xl bg-slate-800/60 border border-slate-700/50 flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-emerald-500/20 text-emerald-400 font-bold flex items-center justify-center border border-emerald-500/30 text-xs shrink-0">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="overflow-hidden">
                <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                <div class="flex items-center gap-1.5 mt-0.5">
                    <span class="inline-flex items-center px-1.5 py-0.2 text-[9px] font-semibold uppercase tracking-wider {{ auth()->user()->role === 'admin' ? 'bg-purple-900/50 text-purple-300 border border-purple-700/50' : 'bg-emerald-900/50 text-emerald-300 border border-emerald-700/50' }} rounded">
                        {{ auth()->user()->role }}
                    </span>
                    @if(auth()->user()->unit)
                        <span class="text-[10px] text-slate-400 truncate max-w-[100px]" title="{{ auth()->user()->unit->nama }}">
                            &bull; {{ auth()->user()->unit->kode_unit }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="px-4 space-y-1 text-xs font-medium">
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>

                <div class="pt-2 pb-0.5 px-3 text-[10px] font-semibold tracking-wider text-slate-500 uppercase">Master Data</div>
                
                <a href="{{ route('admin.nasabah.index') }}" class="flex items-center gap-3 px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('admin.nasabah.*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Data Nasabah
                </a>

                <a href="{{ route('admin.units.index') }}" class="flex items-center gap-3 px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('admin.units.*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Unit Bank Sampah
                </a>

                <a href="{{ route('admin.kategori-sampah.index') }}" class="flex items-center gap-3 px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('admin.kategori-sampah.*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    Kategori Sampah
                </a>

                <a href="{{ route('admin.jenis-sampah.index') }}" class="flex items-center gap-3 px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('admin.jenis-sampah.*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    Jenis Sampah
                </a>

                <a href="{{ route('admin.pengepul.index') }}" class="flex items-center gap-3 px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('admin.pengepul.*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Mitra Pengepul
                </a>

                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Pengguna Sistem
                </a>

                <div class="pt-2 pb-0.5 px-3 text-[10px] font-semibold tracking-wider text-slate-500 uppercase">Gudang & Penjualan Desa</div>

                <a href="{{ route('admin.penjualan.stok') }}" class="flex items-center gap-3 px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('admin.penjualan.stok') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Stok Gudang Desa
                </a>

                <a href="{{ route('admin.penjualan.index') }}" class="flex items-center gap-3 px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('admin.penjualan.index') || request()->routeIs('admin.penjualan.show') || request()->routeIs('admin.penjualan.create') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Jual ke Pengepul
                </a>

                <div class="pt-2 pb-0.5 px-3 text-[10px] font-semibold tracking-wider text-slate-500 uppercase">Transaksi Warga</div>

                <a href="{{ route('admin.transaksi.setor.create') }}" class="flex items-center gap-3 px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('admin.transaksi.setor.*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Setor Sampah
                </a>

                <a href="{{ route('admin.transaksi.tarik.create') }}" class="flex items-center gap-3 px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('admin.transaksi.tarik.*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Tarik Saldo
                </a>

                @php
                    $pendingTarikCount = \App\Models\Transaksi::where('jenis', 'tarik')->where('status', 'menunggu_persetujuan')->count();
                @endphp
                <a href="{{ route('admin.persetujuan-tarik.index') }}" class="flex items-center justify-between px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('admin.persetujuan-tarik.*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Persetujuan Tarik</span>
                    </div>
                    @if($pendingTarikCount > 0)
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black bg-amber-400 text-slate-900 animate-pulse">{{ $pendingTarikCount }}</span>
                    @endif
                </a>

                <a href="{{ route('admin.transaksi.index') }}" class="flex items-center gap-3 px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('admin.transaksi.index') || request()->routeIs('admin.transaksi.show') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Riwayat Transaksi
                </a>

                <div class="pt-2 pb-0.5 px-3 text-[10px] font-semibold tracking-wider text-slate-500 uppercase">Laporan & Pengaturan</div>

                <a href="{{ route('admin.laporan.index') }}" class="flex items-center gap-3 px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('admin.laporan.index') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Laporan Transaksi
                </a>

                <a href="{{ route('admin.laporan.laba-rugi') }}" class="flex items-center gap-3 px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('admin.laporan.laba-rugi*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    Laba/Rugi Desa
                </a>

                <a href="{{ route('admin.pengaturan.index') }}" class="flex items-center gap-3 px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('admin.pengaturan.*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Identitas & Logo
                </a>
            @else
                <!-- Menu Petugas -->
                <a href="{{ route('petugas.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('petugas.dashboard') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>

                <div class="pt-2 pb-0.5 px-3 text-[10px] font-semibold tracking-wider text-slate-500 uppercase">Pelayanan Unit</div>

                <a href="{{ route('petugas.nasabah.index') }}" class="flex items-center gap-3 px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('petugas.nasabah.*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Data Nasabah
                </a>

                <a href="{{ route('petugas.transaksi.setor.create') }}" class="flex items-center gap-3 px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('petugas.transaksi.setor.*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Setor Sampah
                </a>

                <a href="{{ route('petugas.transaksi.tarik.create') }}" class="flex items-center gap-3 px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('petugas.transaksi.tarik.*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Ajukan Tarik Saldo
                </a>

                <a href="{{ route('petugas.transaksi.index') }}" class="flex items-center gap-3 px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('petugas.transaksi.index') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Riwayat Transaksi
                </a>
            @endif
        </nav>
    </div>

    <!-- Bottom Action / Logout (Fixed Bottom) -->
    <div class="p-4 border-t border-slate-800 bg-slate-950/50 shrink-0">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-slate-400 hover:bg-rose-500/10 hover:text-rose-400 text-xs font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Keluar Aplikasi
            </button>
        </form>
    </div>
</aside>
