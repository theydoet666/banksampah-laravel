<?php

namespace App\Exports;

use App\Support\SpreadsheetSanitizer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class NasabahCredentialsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected Collection $rows;

    public function __construct(Collection $rows)
    {
        $this->rows = $rows;
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'No. Rekening',
            'Nama Nasabah',
            'Unit',
            'No. HP',
            'Email Akun',
            'Status Password',
            'Kata Sandi Sementara',
        ];
    }

    public function map($nasabah): array
    {
        $user = $nasabah->user;
        $statusPassword = 'Belum Ada Akun';
        $plainTempPassword = '-';

        if ($user) {
            if (! $user->must_change_password) {
                $statusPassword = 'Sudah Aktif (Diganti Nasabah)';
                $plainTempPassword = 'Sudah diganti oleh nasabah';
            } else {
                $statusPassword = 'Menunggu Login Pertama';
                if (! empty($user->temp_password_encrypted)) {
                    try {
                        $plainTempPassword = Crypt::decryptString($user->temp_password_encrypted);
                    } catch (\Throwable $e) {
                        $plainTempPassword = 'Tidak dapat didekripsi (Reset ulang)';
                    }
                } else {
                    $plainTempPassword = 'Belum tersedia (Reset ulang)';
                }
            }
        }

        return SpreadsheetSanitizer::sanitizeRow([
            $nasabah->no_rekening,
            $nasabah->nama,
            $nasabah->unit->nama ?? '-',
            $nasabah->no_hp,
            $user->email ?? '-',
            $statusPassword,
            $plainTempPassword,
        ]);
    }
}
