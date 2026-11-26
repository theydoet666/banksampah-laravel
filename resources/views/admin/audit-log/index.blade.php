@extends('layouts.app')

@section('title', 'Audit Log Sistem')
@section('breadcrumb', 'Pengaturan / Audit Log')

@section('content')
<div class="space-y-6" x-data="{ 
    selectedLog: null,
    showModal: false,
    viewDetail(log) {
        this.selectedLog = log;
        this.showModal = true;
    }
}">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-bold text-slate-800">Audit Log & Rekam Jejak Aktivitas</h2>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                    Khusus Administrator
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">Pemantauan riwayat aktivitas seluruh akun pengguna (Admin, Pengelola, Petugas, dan Nasabah) secara real-time.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.audit-log.export', request()->query()) }}" class="px-4 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs inline-flex items-center gap-1.5 transition-colors">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Ekspor Log (CSV)
            </a>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 rounded-xl bg-white border border-slate-100 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 uppercase font-semibold">Total Log Tercatat</span>
                <h4 class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($totalLogs) }}</h4>
            </div>
            <div class="w-10 h-10 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-100 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 uppercase font-semibold">Aktivitas Hari Ini</span>
                <h4 class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($todayLogs) }}</h4>
            </div>
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-100 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 uppercase font-semibold">Log Transaksi Kas</span>
                <h4 class="text-2xl font-bold text-sky-600 mt-1">{{ number_format($financialLogs) }}</h4>
            </div>
            <div class="w-10 h-10 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><text x="12" y="15" text-anchor="middle" font-size="8.5" font-weight="700" font-family="ui-sans-serif, system-ui, sans-serif" fill="currentColor" stroke="none">Rp</text></svg>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-100 shadow-xs flex items-center justify-between">
            <div class="min-w-0">
                <span class="text-xs text-slate-400 uppercase font-semibold block truncate">Akun Teraktif Hari Ini</span>
                <h4 class="text-base font-bold text-slate-800 mt-1 truncate">{{ $activeUserToday->user_name ?? '-' }}</h4>
                <p class="text-[11px] text-slate-400">{{ $activeUserToday ? $activeUserToday->total . ' aktivitas' : 'Belum ada data' }}</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-100 p-4">
        <form method="GET" action="{{ route('admin.audit-log.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Cari Deskripsi / Pengguna / IP</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Kata kunci aktivitas..." class="w-full rounded-lg border-slate-200 text-xs focus:border-emerald-500 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Akun Pengguna</label>
                    <select name="user_id" class="w-full rounded-lg border-slate-200 text-xs focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">Semua Pengguna</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }} ({{ ucfirst($u->role) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Peran (Role)</label>
                    <select name="role" class="w-full rounded-lg border-slate-200 text-xs focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">Semua Role</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="pengelola" {{ request('role') === 'pengelola' ? 'selected' : '' }}>Pengelola</option>
                        <option value="petugas" {{ request('role') === 'petugas' ? 'selected' : '' }}>Petugas</option>
                        <option value="nasabah" {{ request('role') === 'nasabah' ? 'selected' : '' }}>Nasabah</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Modul Sistem</label>
                    <select name="module" class="w-full rounded-lg border-slate-200 text-xs focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">Semua Modul</option>
                        @foreach($modules as $m)
                            <option value="{{ $m }}" {{ request('module') === $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Jenis Aksi</label>
                    <select name="action" class="w-full rounded-lg border-slate-200 text-xs focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">Semua Aksi</option>
                        @foreach($actions as $act)
                            <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $act)) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Dari Tanggal</label>
                    <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="w-full rounded-lg border-slate-200 text-xs focus:border-emerald-500 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Sampai Tanggal</label>
                    <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="w-full rounded-lg border-slate-200 text-xs focus:border-emerald-500 focus:ring-emerald-500">
                </div>

                <div class="lg:col-span-4 flex items-end gap-2">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs inline-flex items-center gap-1.5 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Terapkan Filter
                    </button>
                    @if(request()->hasAny(['search', 'user_id', 'role', 'module', 'action', 'tanggal_dari', 'tanggal_sampai']))
                        <a href="{{ route('admin.audit-log.index') }}" class="px-3 py-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4 w-40">Waktu</th>
                        <th class="py-3 px-4 w-48">Pengguna (Pelaku)</th>
                        <th class="py-3 px-4 w-32">Modul</th>
                        <th class="py-3 px-4 w-28">Aksi</th>
                        <th class="py-3 px-4">Deskripsi Aktivitas</th>
                        <th class="py-3 px-4 w-36">IP & Perangkat</th>
                        <th class="py-3 px-4 text-right w-16">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="font-semibold text-slate-800 block">{{ $log->created_at->format('d/m/Y H:i:s') }}</span>
                                <span class="text-[11px] text-slate-400">{{ $log->created_at->diffForHumans() }}</span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-[11px] shrink-0">
                                        {{ strtoupper(substr($log->user_name ?? 'S', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-800 truncate">{{ $log->user_name ?? 'Sistem' }}</p>
                                        <div class="mt-0.5">{!! $log->role_badge !!}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap font-medium text-slate-700">
                                {{ $log->module }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                {!! $log->action_badge !!}
                            </td>
                            <td class="py-3 px-4 text-slate-700 leading-relaxed font-normal">
                                {{ $log->description }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="font-mono text-[11px] text-slate-600 block">{{ $log->ip_address ?: '127.0.0.1' }}</span>
                                <span class="text-[10px] text-slate-400 block truncate max-w-[130px]" title="{{ $log->user_agent }}">
                                    @if(str_contains(strtolower($log->user_agent ?? ''), 'mobile'))
                                        Mobile Device
                                    @elseif(str_contains(strtolower($log->user_agent ?? ''), 'windows'))
                                        Windows PC
                                    @elseif(str_contains(strtolower($log->user_agent ?? ''), 'mac'))
                                        Mac OS
                                    @else
                                        Web Client
                                    @endif
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <button type="button" 
                                        @click="viewDetail(@js([
                                            'id' => $log->id,
                                            'user_name' => $log->user_name,
                                            'user_role' => $log->user_role,
                                            'action' => $log->action,
                                            'module' => $log->module,
                                            'description' => $log->description,
                                            'ip_address' => $log->ip_address,
                                            'user_agent' => $log->user_agent,
                                            'old_values' => $log->old_values,
                                            'new_values' => $log->new_values,
                                            'created_at' => $log->created_at->format('d F Y, H:i:s'),
                                            'diff' => $log->created_at->diffForHumans(),
                                        ]))" 
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors" 
                                        title="Rincian Lengkap">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-3 text-slate-300">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <p class="text-sm font-semibold text-slate-600">Tidak ada riwayat log yang sesuai kriteria</p>
                                <p class="text-xs text-slate-400 mt-0.5">Silakan sesuaikan filter pencarian atau tanggal di atas.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Detail Log -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="showModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-900/10 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 text-sm">Rincian Audit Log #<span x-text="selectedLog?.id"></span></h3>
                            <p class="text-[11px] text-slate-400" x-text="selectedLog?.created_at"></p>
                        </div>
                    </div>
                    <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="grid grid-cols-2 gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <div>
                            <span class="text-slate-400 block font-medium">Pelaku / Pengguna</span>
                            <span class="font-bold text-slate-800" x-text="selectedLog?.user_name || 'Sistem'"></span>
                            <span class="block text-[10px] uppercase font-bold text-slate-500" x-text="selectedLog?.user_role"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block font-medium">Modul & Aksi</span>
                            <span class="font-bold text-slate-800" x-text="selectedLog?.module"></span>
                            <span class="block text-[10px] text-emerald-600 font-semibold uppercase" x-text="selectedLog?.action"></span>
                        </div>
                    </div>

                    <div>
                        <span class="text-slate-400 block font-medium mb-1">Deskripsi Aktivitas</span>
                        <div class="p-3 rounded-lg bg-slate-50 border border-slate-100 text-slate-800 font-medium" x-text="selectedLog?.description"></div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <span class="text-slate-400 block font-medium">Alamat IP Jaringan</span>
                            <span class="font-mono text-slate-700" x-text="selectedLog?.ip_address || '-'"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block font-medium">Waktu Relatif</span>
                            <span class="text-slate-700" x-text="selectedLog?.diff"></span>
                        </div>
                    </div>

                    <div>
                        <span class="text-slate-400 block font-medium mb-1">User Agent (Browser & Device)</span>
                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-100 font-mono text-[11px] text-slate-600 break-all" x-text="selectedLog?.user_agent || '-'"></div>
                    </div>

                    <template x-if="selectedLog?.old_values || selectedLog?.new_values">
                        <div class="space-y-2 pt-2 border-t border-slate-100">
                            <span class="text-slate-400 block font-medium">Perubahan Data (Snapshot)</span>
                            <pre class="p-2.5 rounded-lg bg-slate-900 text-slate-100 text-[10px] overflow-x-auto" x-text="JSON.stringify({ lama: selectedLog?.old_values, baru: selectedLog?.new_values }, null, 2)"></pre>
                        </div>
                    </template>
                </div>

                <div class="pt-3 border-t border-slate-100 flex justify-end">
                    <button type="button" @click="showModal = false" class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
