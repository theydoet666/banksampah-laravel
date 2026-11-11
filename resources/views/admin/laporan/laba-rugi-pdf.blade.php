<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Laba/Rugi Operasional Bank Sampah Desa</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #333; margin: 0; padding: 15px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { font-size: 16px; margin: 0; text-transform: uppercase; }
        .header p { margin: 2px 0; font-size: 10px; color: #666; }
        .summary-box { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .summary-box td { border: 1px solid #ddd; padding: 10px; vertical-align: top; }
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th, .table td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        .table th { background-color: #f5f5f5; font-size: 10px; text-transform: uppercase; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .signatures { width: 100%; margin-top: 40px; }
        .signatures td { text-align: center; width: 50%; padding-bottom: 60px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ appSetting('nama_bank_sampah', 'Bank Sampah BAA') }}</h1>
        <p>{{ appSetting('alamat_bank_sampah', 'Desa BAA, Jawa Barat') }} | Telp: {{ appSetting('telepon', '08123456789') }}</p>
        <h2 style="font-size: 13px; margin-top: 8px; margin-bottom: 2px;">REKAPITULASI LABA / RUGI OPERASIONAL BANK SAMPAH DESA</h2>
        <p>Periode: <strong>{{ \Carbon\Carbon::parse($tanggalDari)->format('d/m/Y') }}</strong> s/d <strong>{{ \Carbon\Carbon::parse($tanggalSampai)->format('d/m/Y') }}</strong></p>
    </div>

    <table class="summary-box">
        <tr>
            <td style="width: 33%;">
                <small style="color: #666; text-transform: uppercase;">(+) Total Pendapatan Penjualan ke Pengepul</small><br>
                <strong style="font-size: 14px; color: #059669;">{{ formatRupiah($totalPendapatanJual) }}</strong><br>
                <small>Volume: {{ number_format($totalBeratJual, 1, ',', '.') }} kg</small>
            </td>
            <td style="width: 33%;">
                <small style="color: #666; text-transform: uppercase;">(-) Total Beban Pembelian dari Warga</small><br>
                <strong style="font-size: 14px; color: #e11d48;">{{ formatRupiah($totalBebanBeli) }}</strong><br>
                <small>Volume: {{ number_format($totalBeratBeli, 1, ',', '.') }} kg</small>
            </td>
            <td style="width: 34%; background-color: #f9f9f9;">
                <small style="color: #666; text-transform: uppercase;">(=) Laba Bersih Usaha Desa</small><br>
                <strong style="font-size: 14px; color: #1e293b;">{{ formatRupiah($labaKotor) }}</strong><br>
                <small>Marjin Usaha: <strong>{{ $marginPersen }}%</strong></small>
            </td>
        </tr>
    </table>

    <h3 style="font-size: 11px; text-transform: uppercase; margin-bottom: 5px;">Rincian Transaksi Pengiriman ke Pengepul:</h3>
    <table class="table">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th>Kode Penjualan</th>
                <th>Pengepul</th>
                <th>Tanggal</th>
                <th class="text-right">Berat (kg)</th>
                <th class="text-right">Pendapatan</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($penjualanList as $idx => $jual)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td style="font-family: monospace;">{{ $jual->kode_penjualan }}</td>
                    <td>{{ $jual->pengepul->nama_perusahaan ?? '-' }}</td>
                    <td>{{ $jual->tanggal_penjualan->format('d/m/Y') }}</td>
                    <td class="text-right">{{ number_format($jual->total_berat, 2, ',', '.') }} kg</td>
                    <td class="text-right" style="font-weight: bold;">{{ formatRupiah($jual->total_pendapatan) }}</td>
                    <td class="text-center">{{ strtoupper($jual->status_pembayaran) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="font-style: italic; color: #777;">Tidak ada transaksi penjualan pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #f5f5f5;">
                <td colspan="4" class="text-right">TOTAL:</td>
                <td class="text-right">{{ number_format($totalBeratJual, 2, ',', '.') }} kg</td>
                <td class="text-right">{{ formatRupiah($totalPendapatanJual) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <table class="signatures">
        <tr>
            <td>
                Mengetahui,<br>
                Kepala Desa / Direktur BUMDes<br><br><br><br>
                ( ______________________ )
            </td>
            <td>
                {{ date('d F Y') }}<br>
                Pengelola Bank Sampah Induk Desa<br><br><br><br>
                ( <strong>{{ auth()->user()->name ?? 'Administrator' }}</strong> )
            </td>
        </tr>
    </table>
</body>
</html>
