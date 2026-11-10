<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;

class TransaksiExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected Collection $transaksi;

    public function __construct(Collection $transaksi)
    {
        $this->transaksi = $transaksi;
    }

    public function collection()
    {
        return $this->transaksi;
    }

    public function headings(): array
    {
        return [
            'Kode Transaksi',
            'Tanggal',
            'Nasabah',
            'No. Rekening',
            'Jenis Transaksi',
            'Total Berat (kg)',
            'Total Nilai (Rp)',
            'Status',
            'Petugas Loket',
        ];
    }

    public function map($row): array
    {
        return [
            $row->kode_transaksi,
            $row->tanggal_transaksi->format('d/m/Y'),
            $row->nasabah->nama ?? '-',
            $row->nasabah->no_rekening ?? '-',
            ucfirst($row->jenis),
            $row->total_berat ? number_format($row->total_berat, 2) : '-',
            $row->total_nilai,
            ucfirst($row->status),
            $row->petugas->name ?? '-',
        ];
    }
}

