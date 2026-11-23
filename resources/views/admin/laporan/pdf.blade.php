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
    @php
        $logoBase64 = appLogoBase64();
    @endphp
    <table style="width: 100%; border-bottom: 2px solid #16a34a; padding-bottom: 10px; margin-bottom: 15px;">
        <tr>
            @if($logoBase64)
                <td style="width: 75px; vertical-align: middle; text-align: left;">
                    <img src="{{ $logoBase64 }}" alt="Logo" style="height: 60px; max-width: 75px; object-fit: contain;">
                </td>
            @endif
            <td style="vertical-align: middle; text-align: center; {{ $logoBase64 ? 'padding-right: 75px;' : '' }}">
                <h2 style="margin: 0; font-size: 15px; color: #16a34a; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">
                    {{ strtoupper(appSetting('nama_bank_sampah', 'BANK SAMPAH BALA AGA ASRI')) }}
                </h2>
                <p style="margin: 3px 0 0; font-size: 9.5px; color: #475569;">
                    {{ appSetting('alamat', 'Jalan Raya Belega, Desa Belega, Kec. Blahbatuh, Kab. Gianyar, Bali') }}
                </p>
                <p style="margin: 2px 0 0; font-size: 9.5px; color: #475569;">
                    Telp/WA: {{ appSetting('no_telp', '08123456789') }} &bull; Email: {{ appSetting('email', 'info@banksampah.test') }}
                </p>
                <p style="font-weight: bold; margin-top: 8px; margin-bottom: 0; font-size: 12px; color: #0f172a;">
                    LAPORAN REKAPITULASI TRANSAKSI
                </p>
            </td>
        </tr>
    </table>

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

