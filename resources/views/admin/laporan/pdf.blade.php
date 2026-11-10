<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Transaksi - {{ appSetting('nama_bank_sampah', 'Bank Sampah BAA') }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #16a34a; padding-bottom: 10px; }
        .header h2 { margin: 0; font-size: 18px; color: #16a34a; }
        .header p { margin: 3px 0 0; font-size: 10px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; font-size: 10px; }
        th { background: #f8fafc; font-weight: bold; text-align: left; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-box { margin-top: 15px; width: 100%; }
        .total-box td { border: none; padding: 3px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ strtoupper(appSetting('nama_bank_sampah', 'BANK SAMPAH BAA')) }}</h2>
        <p>{{ appSetting('alamat', 'Jl. Lingkungan Hidup No. 1') }} &bull; Telp: {{ appSetting('no_telp', '08123456789') }}</p>
        <p style="font-weight: bold; margin-top: 8px; font-size: 12px;">LAPORAN REKAPITULASI TRANSAKSI</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Transaksi</th>
                <th>Tanggal</th>
                <th>Nasabah</th>
                <th>Jenis</th>
                <th class="text-right">Berat (kg)</th>
                <th class="text-right">Nilai (Rp)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaksi as $index => $trx)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $trx->kode_transaksi }}</td>
                    <td>{{ $trx->tanggal_transaksi->format('d/m/Y') }}</td>
                    <td>{{ $trx->nasabah->nama ?? '-' }}</td>
                    <td>{{ ucfirst($trx->jenis) }}</td>
                    <td class="text-right">{{ $trx->total_berat ? number_format($trx->total_berat, 2) : '-' }}</td>
                    <td class="text-right">{{ number_format($trx->total_nilai, 0, ',', '.') }}</td>
                    <td>{{ ucfirst($trx->status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="total-box">
        <tr>
            <td width="70%"></td>
            <td width="30%">
                <p><strong>Total Sampah Masuk:</strong> {{ number_format($totalBerat, 2) }} kg</p>
                <p><strong>Total Nilai Setoran:</strong> Rp {{ number_format($totalSetor, 0, ',', '.') }}</p>
                <p><strong>Total Penarikan:</strong> Rp {{ number_format($totalTarik, 0, ',', '.') }}</p>
            </td>
        </tr>
    </table>
</body>
</html>

