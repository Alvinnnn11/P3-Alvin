<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
{
        // Assign roles to the users

        $this->call([
            // Urutan penting jika ada ketergantungan foreign key
            SatuanSeeder::class,
            SettingSeeder::class,
            CabangSeeder::class,
            // Panggil LayananSeeder SETELAH SatuanSeeder
            LayananSeeder::class,
            UserSeeder::class,
            // Panggil seeder lain jika ada (misal UserSeeder)
        ]);
    }
}
