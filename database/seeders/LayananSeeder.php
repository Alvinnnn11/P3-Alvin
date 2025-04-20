<?php

namespace Database\Seeders;

use App\Models\Layanan;
use App\Models\Satuan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LayananSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $satuanKg = Satuan::where('nama_satuan', 'Kg')->first();
        $satuanPcs = Satuan::where('nama_satuan', 'Pcs')->first();
        $satuanSet = Satuan::where('nama_satuan', 'Set')->first();
        // Tambahkan satuan lain jika perlu

        // Cek apakah satuan ditemukan, jika tidak, seeder tidak bisa lanjut
        if (!$satuanKg || !$satuanPcs || !$satuanSet) {
             $this->command->error('Seeder Satuan belum dijalankan atau data Satuan dasar (Kg, Pcs, Set) tidak ditemukan. LayananSeeder tidak dapat dilanjutkan.');
             return; // Hentikan seeder jika satuan tidak ada
        }


        $layananData = [
            [
                'nama_layanan' => 'Cuci Kiloan Reguler',
                'harga_per_unit' => 7000.00,
                'satuan_id' => $satuanKg->satuan_id,
                'estimasi_durasi_hari' => 3,
                'status' => true,
            ],
            [
                'nama_layanan' => 'Cuci Kiloan Express',
                'harga_per_unit' => 12000.00,
                'satuan_id' => $satuanKg->satuan_id,
                'estimasi_durasi_hari' => 1,
                'status' => true,
            ],
            [
                'nama_layanan' => 'Setrika Satuan (Kemeja/Kaos)',
                'harga_per_unit' => 4000.00,
                'satuan_id' => $satuanPcs->satuan_id,
                'estimasi_durasi_hari' => 2,
                'status' => true,
            ],
            [
                'nama_layanan' => 'Cuci Bedcover King',
                'harga_per_unit' => 25000.00,
                'satuan_id' => $satuanPcs->satuan_id, // Bedcover dihitung per Pcs
                'estimasi_durasi_hari' => 4,
                'status' => true,
            ],
            [
                'nama_layanan' => 'Cuci Sepatu',
                'harga_per_unit' => 30000.00,
                'satuan_id' => $satuanSet->satuan_id, // Sepatu dihitung per Set
                'estimasi_durasi_hari' => 5,
                'status' => true,
            ],
             [
                'nama_layanan' => 'Layanan Non-Aktif Contoh',
                'harga_per_unit' => 5000.00,
                'satuan_id' => $satuanKg->satuan_id,
                'estimasi_durasi_hari' => 2,
                'status' => false, // Contoh layanan tidak aktif
            ],
        ];

        // Gunakan firstOrCreate untuk memasukkan data (cek berdasarkan nama_layanan)
        foreach ($layananData as $data) {
            Layanan::firstOrCreate(
                ['nama_layanan' => $data['nama_layanan']], // Kriteria pencarian unik
                $data // Data lengkap untuk dibuat jika belum ada
            );
        }
    }
}