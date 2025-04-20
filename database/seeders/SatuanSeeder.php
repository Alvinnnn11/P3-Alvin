<?php

namespace Database\Seeders;

use App\Models\Satuan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SatuanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    $satuanData = [
        ['nama_satuan' => 'Kg', 'deskripsi' => 'Kilogram, untuk layanan cuci kiloan', 'created_at' => now(), 'updated_at' => now()],
        ['nama_satuan' => 'Pcs', 'deskripsi' => 'Pieces / Satuan, untuk item seperti baju, celana', 'created_at' => now(), 'updated_at' => now()],
        ['nama_satuan' => 'Set', 'deskripsi' => 'Set / Pasang, contohnya untuk sepatu atau setelan', 'created_at' => now(), 'updated_at' => now()],
        ['nama_satuan' => 'Lembar', 'deskripsi' => 'Lembar, contohnya untuk gorden atau sprei', 'created_at' => now(), 'updated_at' => now()],
        ['nama_satuan' => 'Meter', 'deskripsi' => 'Meter Persegi (m²), mungkin untuk karpet', 'created_at' => now(), 'updated_at' => now()],
    ];

    // Masukkan data menggunakan Eloquent Model
    foreach ($satuanData as $data) {
        Satuan::create($data);
    }

    // Alternatif: Menggunakan DB Facade (Query Builder)
    // DB::table('satuan')->insert($satuanData);
}
}
