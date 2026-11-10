@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Top Stats Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <x-stat-card 
            title="Total Nasabah Aktif" 
            :value="$totalNasabah . ' Orang'" 
            color="blue"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>'
        />

        <x-stat-card 
            title="Sampah Masuk Bulan Ini" 
            :value="number_format($totalSampahBulanIni, 1, ',', '.') . ' kg'" 
            color="green"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>'
        />

        <x-stat-card 
            title="Total Setoran Bulan Ini" 
            :value="formatRupiah($totalSetoranBulanIni)" 
            color="green"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />

        <x-stat-card 
            title="Total Penarikan Bulan Ini" 
            :value="formatRupiah($totalPenarikanBulanIni)" 
            color="red"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>'
        />
    </div>

    <!-- Charts & Recent Activity Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Chart Col -->
        <div class="lg:col-span-2">
            <x-card title="Aktivitas Transaksi 7 Hari Terakhir">
                <div class="h-72">
                    <canvas id="transaksiChart"></canvas>
                </div>
            </x-card>
        </div>

        <!-- 5 Transaksi Terbaru -->
        <div>
            <x-card title="Transaksi Terbaru">
                <x-slot:action>
                    <a href="{{ route('admin.transaksi.index') }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-semibold">Lihat Semua</a>
                </x-slot:action>

                <div class="divide-y divide-slate-100">
                    @forelse($transaksiTerbaru as $trx)
                        <div class="py-3 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold {{ $trx->jenis === 'setor' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                                    {{ $trx->jenis === 'setor' ? 'IN' : 'OUT' }}
                                </span>
                                <div>
                                    <p class="text-sm font-medium text-slate-800">{{ $trx->nasabah->nama ?? 'Nasabah' }}</p>
                                    <p class="text-xs text-slate-400">{{ $trx->tanggal_transaksi->format('d M Y') }} &bull; {{ $trx->kode_transaksi }}</p>
                                </div>
                            </div>
                            <span class="text-sm font-semibold {{ $trx->jenis === 'setor' ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $trx->jenis === 'setor' ? '+' : '-' }}{{ formatRupiah($trx->total_nilai) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400 py-6 text-center">Belum ada transaksi</p>
                    @endforelse
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('transaksiChart');
        if (ctx) {
            const chartData = @json($chartData);
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        {
                            label: 'Setoran (Rp)',
                            data: chartData.setor,
                            borderColor: '#16a34a',
                            backgroundColor: 'rgba(22, 163, 74, 0.08)',
                            tension: 0.3,
                            fill: true,
                        },
                        {
                            label: 'Penarikan (Rp)',
                            data: chartData.tarik,
                            borderColor: '#dc2626',
                            backgroundColor: 'rgba(220, 38, 38, 0.05)',
                            tension: 0.3,
                            fill: true,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush

