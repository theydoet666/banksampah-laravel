<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Nasabah;
use App\Models\JenisSampah;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $petugas = User::where('role', 'petugas')->first() ?? User::where('role', 'admin')->first();
        $admin = User::where('role', 'admin')->first() ?? $petugas;
        $jenisSampahList = JenisSampah::where('is_active', true)->get();

        if ($jenisSampahList->isEmpty()) {
            return;
        }

        $year = date('Y');

        // 1. Buat 20 Nasabah baru dengan data realistis
        $createdNasabahList = [];

        // Hitung no_rekening terakhir
        $lastNasabah = Nasabah::orderBy('id', 'desc')->first();
        $startIndex = $lastNasabah ? ((int) substr($lastNasabah->no_rekening, -5)) + 1 : 1;

        for ($i = 0; $i < 20; $i++) {
            $seq = str_pad($startIndex + $i, 5, '0', STR_PAD_LEFT);
            $noRekening = "BS-{$year}-{$seq}";
            $jk = $faker->randomElement(['L', 'P']);
            $nama = $faker->name($jk === 'L' ? 'male' : 'female');

            $nasabah = Nasabah::create([
                'no_rekening' => $noRekening,
                'nama' => $nama,
                'nik' => '320' . $faker->numerify('#############'),
                'tempat_lahir' => $faker->city,
                'tanggal_lahir' => $faker->date('Y-m-d', '-20 years'),
                'jenis_kelamin' => $jk,
                'alamat' => $faker->streetAddress . ', ' . $faker->city,
                'no_hp' => '08' . $faker->numerify('##########'),
                'saldo' => 0, // Dihitung dinamis dari transaksi
                'status' => ($i === 19) ? 'nonaktif' : 'aktif', // 1 nasabah nonaktif untuk testing
                'registered_by' => $petugas->id,
                'created_at' => Carbon::now()->subMonths(3)->addDays($i * 3),
            ]);

            $createdNasabahList[] = $nasabah;
        }

        // Ambil semua nasabah aktif untuk dijadikan sasaran transaksi
        $allNasabah = Nasabah::where('status', 'aktif')->get();

        // 2. Buat 50 Transaksi Setoran (didistribusikan dalam 90 hari terakhir)
        $setorTransactions = [];
        $trxCounter = 1;

        for ($s = 1; $s <= 50; $s++) {
            $nasabah = $allNasabah->random();
            $daysAgo = rand(1, 90);
            $trxDate = Carbon::now()->subDays($daysAgo)->setTime(rand(8, 16), rand(0, 59));
            $kodeTrx = sprintf('TRX-%s-%05d', $trxDate->format('Ymd'), $trxCounter++);

            // Random 2 - 4 jenis sampah unik
            $itemCount = rand(2, min(4, $jenisSampahList->count()));
            $selectedJenis = $jenisSampahList->random($itemCount);

            $totalBerat = 0;
            $totalNilai = 0;
            $detailsToInsert = [];

            foreach ($selectedJenis as $jenis) {
                $berat = round(rand(5, 100) / 10, 2); // 0.5 - 10.0 kg
                $hargaSatuan = $jenis->harga_per_satuan;
                $subtotal = round($berat * $hargaSatuan, 2);

                $totalBerat += $berat;
                $totalNilai += $subtotal;

                $detailsToInsert[] = [
                    'jenis_sampah_id' => $jenis->id,
                    'berat' => $berat,
                    'harga_satuan' => $hargaSatuan,
                    'subtotal' => $subtotal,
                ];
            }

            $transaksi = Transaksi::create([
                'kode_transaksi' => $kodeTrx,
                'nasabah_id' => $nasabah->id,
                'jenis' => 'setor',
                'total_berat' => $totalBerat,
                'total_nilai' => $totalNilai,
                'catatan' => 'Setoran sampah demo #' . $s,
                'status' => 'aktif',
                'petugas_id' => $petugas->id,
                'tanggal_transaksi' => $trxDate->toDateString(),
                'created_at' => $trxDate,
                'updated_at' => $trxDate,
            ]);

            foreach ($detailsToInsert as $detail) {
                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'jenis_sampah_id' => $detail['jenis_sampah_id'],
                    'berat' => $detail['berat'],
                    'harga_satuan' => $detail['harga_satuan'],
                    'subtotal' => $detail['subtotal'],
                    'created_at' => $trxDate,
                    'updated_at' => $trxDate,
                ]);
            }

            // Tambahkan saldo nasabah
            $nasabah->saldo += $totalNilai;
            $nasabah->save();

            $setorTransactions[] = $transaksi;
        }

        // 3. Buat 15 Transaksi Penarikan (pastikan tidak melebihi saldo nasabah)
        $tarikTransactions = [];
        $tarikCount = 0;

        // Shuffle nasabah dan cari yang punya saldo cukup
        $shuffledNasabah = $allNasabah->shuffle();

        foreach ($shuffledNasabah as $nasabah) {
            $nasabah->refresh();
            if ($nasabah->saldo >= 25000 && $tarikCount < 15) {
                // Tarik sebagian saldo (misal 30% - 70% dari saldo, dibulatkan ke kelipatan 5000)
                $maxTarik = $nasabah->saldo - 5000;
                $rawTarik = rand(15000, max(20000, (int) $maxTarik));
                $nominalTarik = min($maxTarik, round($rawTarik / 5000) * 5000);

                if ($nominalTarik > 0 && $nominalTarik <= $nasabah->saldo) {
                    $daysAgo = rand(1, 30);
                    $trxDate = Carbon::now()->subDays($daysAgo)->setTime(rand(9, 15), rand(0, 59));
                    $kodeTrx = sprintf('TRX-%s-%05d', $trxDate->format('Ymd'), $trxCounter++);

                    $transaksi = Transaksi::create([
                        'kode_transaksi' => $kodeTrx,
                        'nasabah_id' => $nasabah->id,
                        'jenis' => 'tarik',
                        'total_berat' => null,
                        'total_nilai' => $nominalTarik,
                        'catatan' => 'Penarikan saldo tunai demo',
                        'status' => 'aktif',
                        'petugas_id' => $petugas->id,
                        'tanggal_transaksi' => $trxDate->toDateString(),
                        'created_at' => $trxDate,
                        'updated_at' => $trxDate,
                    ]);

                    $nasabah->saldo -= $nominalTarik;
                    $nasabah->save();

                    $tarikTransactions[] = $transaksi;
                    $tarikCount++;
                }
            }
        }

        // 4. Buat 2 Transaksi yang di-void (1 setoran dan 1 penarikan untuk variasi)
        if (!empty($setorTransactions)) {
            $voidSetor = end($setorTransactions);
            $voidSetor->status = 'void';
            $voidSetor->catatan = ($voidSetor->catatan ?? '') . ' [DIBATALKAN/VOID: Salah input timbangan]';
            $voidSetor->save();

            // Saldo nasabah dikurangi kembali
            $nasabahVoid = $voidSetor->nasabah;
            $nasabahVoid->saldo = max(0, $nasabahVoid->saldo - $voidSetor->total_nilai);
            $nasabahVoid->save();
        }

        if (!empty($tarikTransactions)) {
            $voidTarik = end($tarikTransactions);
            $voidTarik->status = 'void';
            $voidTarik->catatan = ($voidTarik->catatan ?? '') . ' [DIBATALKAN/VOID: Nasabah membatalkan penarikan]';
            $voidTarik->save();

            // Saldo nasabah dikembalikan
            $nasabahVoidTarik = $voidTarik->nasabah;
            $nasabahVoidTarik->saldo += $voidTarik->total_nilai;
            $nasabahVoidTarik->save();
        }
    }
}

