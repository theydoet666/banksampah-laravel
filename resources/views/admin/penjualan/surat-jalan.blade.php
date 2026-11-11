<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Jalan & Nota Penjualan - {{ $penjualan->kode_penjualan }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0;
            text-transform: uppercase;
        }
        .header p {
            margin: 2px 0;
            font-size: 11px;
            color: #666;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-table td {
            vertical-align: top;
            padding: 3px 0;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .items-table th, .items-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .items-table th {
            background-color: #f5f5f5;
            font-size: 11px;
            text-transform: uppercase;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .signatures {
            width: 100%;
            margin-top: 40px;
        }
        .signatures td {
            text-align: center;
            width: 33%;
            padding-bottom: 70px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background-color: #059669; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">
            Cetak Dokumen
        </button>
        <button onclick="window.close()" style="padding: 8px 16px; background-color: #6b7280; color: white; border: none; border-radius: 6px; cursor: pointer; margin-left: 8px;">
            Tutup
        </button>
    </div>

    <div class="header">
        <h1>{{ appSetting('nama_bank_sampah', 'Bank Sampah BAA') }}</h1>
        <p>{{ appSetting('alamat_bank_sampah', 'Desa BAA, Jawa Barat') }} | Telp: {{ appSetting('telepon', '08123456789') }}</p>
        <h2 style="font-size: 14px; margin-top: 10px; margin-bottom: 0;">SURAT JALAN & NOTA PENJUALAN SAMPAH</h2>
        <p style="font-family: monospace; font-size: 12px; font-weight: bold;">Nomor: {{ $penjualan->kode_penjualan }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td style="width: 50%;">
                <strong>Kepada Yth. Pengepul/Pembeli:</strong><br>
                {{ $penjualan->pengepul->nama_perusahaan }}<br>
                UP: {{ $penjualan->pengepul->penanggung_jawab ?? '-' }} ({{ $penjualan->pengepul->no_hp ?? '-' }})<br>
                Alamat: {{ $penjualan->pengepul->alamat ?? '-' }}
            </td>
            <td style="width: 50%; text-align: right;">
                <strong>Tanggal Pengiriman:</strong> {{ $penjualan->tanggal_penjualan->format('d F Y') }}<br>
                <strong>Status Pembayaran:</strong> {{ strtoupper($penjualan->status_pembayaran) }}<br>
                <strong>Petugas Gudang:</strong> {{ $penjualan->pengelola->name ?? '-' }}
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 30px;" class="text-center">No</th>
                <th>Jenis Sampah</th>
                <th class="text-right" style="width: 100px;">Berat (kg)</th>
                <th class="text-right" style="width: 120px;">Harga Satuan</th>
                <th class="text-right" style="width: 130px;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($penjualan->details as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->jenisSampah->nama }}</strong><br>
                        <small style="color: #666;">Kategori: {{ $item->jenisSampah->kategori->nama ?? '-' }}</small>
                    </td>
                    <td class="text-right">{{ number_format($item->berat, 2, ',', '.') }} kg</td>
                    <td class="text-right">{{ formatRupiah($item->harga_jual_satuan) }}</td>
                    <td class="text-right">{{ formatRupiah($item->subtotal) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #f9f9f9;">
                <td colspan="2" class="text-right">TOTAL:</td>
                <td class="text-right">{{ number_format($penjualan->total_berat, 2, ',', '.') }} kg</td>
                <td></td>
                <td class="text-right">{{ formatRupiah($penjualan->total_pendapatan) }}</td>
            </tr>
        </tfoot>
    </table>

    @if($penjualan->catatan)
        <p style="font-size: 11px; margin-bottom: 20px;"><strong>Catatan Pengiriman:</strong> {{ $penjualan->catatan }}</p>
    @endif

    <table class="signatures">
        <tr>
            <td>
                Penerima / Pengepul,<br><br><br><br>
                ( ______________________ )
            </td>
            <td>
                Sopir / Pengangkut,<br><br><br><br>
                ( ______________________ )
            </td>
            <td>
                Pengelola Bank Sampah,<br><br><br><br>
                ( <strong>{{ $penjualan->pengelola->name ?? 'Admin' }}</strong> )
            </td>
        </tr>
    </table>
</body>
</html>
