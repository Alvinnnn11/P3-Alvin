<?php

namespace Database\Seeders;

use App\Models\Cabang;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CabangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Cabang::firstOrCreate(
            ['kode_cabang' => 'CBNG0001'], // Kondisi pencarian
            [                          // Data jika belum ada
                // Perhatikan nama kolom 'nama_perusahaan' sesuai skema Anda
                'nama_perusahaan' => 'Laundry Express Antapani',
                'alamat_perusahaan' => 'Jl. Purwakarta No. 10, Antapani Kidul',
                'provinsi_perusahaan' => 'Jawa Barat',
                'kota_perusahaan' => 'Bandung',
                'kecamatan_perusahaan' => 'Antapani',
                'kelurahan_perusahaan' => 'Antapani Kidul',
                'kode_pos' => '40291',
                'logo_perusahaan' => 'images/logo_antapani.png',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        Cabang::firstOrCreate(
            ['kode_cabang' => 'CBNG0002'], // Kondisi pencarian
            [                          // Data jika belum ada
                'nama_perusahaan' => 'Laundry Express Setiabudi', // Nama kolom dari skema
                'alamat_perusahaan' => 'Jl. Setiabudi No. 55',
                'provinsi_perusahaan' => 'Jawa Barat',
                'kota_perusahaan' => 'Bandung',
                'kecamatan_perusahaan' => 'Cidadap',
                'kelurahan_perusahaan' => 'Hegarmanah',
                'kode_pos' => '40141',
                'logo_perusahaan' => 'images/logo_setiabudi.png',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Tambahkan cabang lain jika perlu
        /*
        Cabang::firstOrCreate(
            ['kode_cabang' => 'BDO-XYZ'],
            [
                'nama_perusahaan' => 'Laundry Express XYZ',
                // ... isi data lainnya ...
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        */
    }
}