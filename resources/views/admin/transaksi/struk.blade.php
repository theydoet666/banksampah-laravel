<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Struk - {{ $transaksi->kode_transaksi }}</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 20px;
        }
        .struk-container {
            max-width: 320px;
            margin: 0 auto;
            border: 1px dashed #ccc;
            padding: 15px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 8px 0; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th, td { padding: 3px 0; }
        @media print {
            body { padding: 0; }
            .struk-container { border: none; max-width: 100%; width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #16a34a; color: #fff; border: none; border-radius: 4px; cursor: pointer;">Cetak Struk Sekarang</button>
    </div>

    <div class="struk-container">
        <div class="text-center">
            <h2 style="margin: 0; font-size: 15px;" class="bold">{{ strtoupper(appSetting('nama_bank_sampah', 'BANK SAMPAH BAA')) }}</h2>
            <p style="margin: 3px 0 0; font-size: 10px;">{{ appSetting('alamat', 'Jl. Lingkungan Hidup No. 1') }}</p>
            <p style="margin: 2px 0 0; font-size: 10px;">Telp: {{ appSetting('no_telp', '08123456789') }}</p>
        </div>

        <div class="divider"></div>

        <div style="font-size: 11px;">
            <div>No. TRX : {{ $transaksi->kode_transaksi }}</div>
            <div>Tanggal : {{ $transaksi->tanggal_transaksi->format('d/m/Y') }}</div>
            <div>Nasabah : {{ $transaksi->nasabah->nama ?? '-' }}</div>
            <div>Rekening: {{ $transaksi->nasabah->no_rekening ?? '-' }}</div>
            <div>Petugas : {{ $transaksi->petugas->name ?? '-' }}</div>
            <div>Status  : {{ strtoupper($transaksi->status) }}</div>
        </div>

        <div class="divider"></div>

        @if($transaksi->jenis === 'setor')
            <table>
                <thead>
                    <tr>
                        <th align="left">Item</th>
                        <th align="right">Berat</th>
                        <th align="right">Harga</th>
                        <th align="right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaksi->details as $item)
                        <tr>
                            <td>{{ $item->jenisSampah->nama ?? '-' }}</td>
                            <td class="text-right">{{ number_format($item->berat, 2) }}</td>
                            <td class="text-right">{{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="divider"></div>
            <table>
                <tr>
                    <td class="bold">Total Berat</td>
                    <td class="text-right bold">{{ number_format($transaksi->total_berat, 2) }} kg</td>
                </tr>
                <tr>
                    <td class="bold">Total Setoran</td>
                    <td class="text-right bold">Rp {{ number_format($transaksi->total_nilai, 0, ',', '.') }}</td>
                </tr>
            </table>
        @else
            <table>
                <tr>
                    <td class="bold">Jenis Transaksi</td>
                    <td class="text-right">PENARIKAN SALDO</td>
                </tr>
                <tr>
                    <td class="bold">Jumlah Ditarik</td>
                    <td class="text-right bold">Rp {{ number_format($transaksi->total_nilai, 0, ',', '.') }}</td>
                </tr>
            </table>
        @endif

        <div class="divider"></div>

        <table>
            <tr>
                <td class="bold">Sisa Saldo Terkini</td>
                <td class="text-right bold">Rp {{ number_format($transaksi->nasabah->saldo ?? 0, 0, ',', '.') }}</td>
            </tr>
        </table>

        <div class="divider"></div>

        <div class="text-center" style="font-size: 10px; margin-top: 10px;">
            <p style="margin: 0;">Terima kasih atas partisipasi Anda</p>
            <p style="margin: 2px 0 0;">Menjaga Lingkungan Bersih & Lestari</p>
        </div>
    </div>
</body>
</html>

