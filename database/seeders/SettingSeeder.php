<?php

namespace Database\Seeders;

use App\Models\setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        setting::updateOrCreate(
            ['id_setting' => 1], // Kondisi pencarian (cari setting dengan id 1)
            [                 // Data yang akan dimasukkan atau diupdate
                'nama_perusahaan' => 'Laundry Express Bandung',
                'email' => 'info@laundryexpressbdg.com',
                'alamat' => 'Jl. Merdeka No. 101, Citarum, Bandung Wetan, Bandung, Jawa Barat 40115',
                'telepon' => '022-1234567 / 081298765432',
                'website' => 'https://laundryexpressbdg.com',
                'logo' => 'images/logo_utama.png', // Path relatif ke logo
                'membership_fee' => 50000.00, // Biaya membership baru
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Alternatif jika Anda tidak mau menggunakan ID sebagai acuan utama
        /*
        Setting::updateOrCreate(
             ['email' => 'info@laundryexpressbdg.com'], // Cari berdasarkan email
             [
                 'nama_perusahaan' => 'Laundry Express Bandung',
                 'alamat' => 'Jl. Merdeka No. 101, Citarum, Bandung Wetan, Bandung, Jawa Barat 40115',
                 'telepon' => '022-1234567 / 081298765432',
                 'website' => 'https://laundryexpressbdg.com',
                 'logo' => 'images/logo_utama.png',
                 'membership_fee' => 50000.00,
                 'created_at' => now(),
                 'updated_at' => now(),
             ]
         );
        */
    }
}