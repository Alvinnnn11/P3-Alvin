<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

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
       
        $password  = Hash::make('admin123'); 
        $password1 = Hash::make('supervisor123'); 
        $password2 = Hash::make('petugas123'); 
        $password3 = Hash::make('member123'); 
        $password4 = Hash::make('pengguna123'); 

        // Create Admin user
        $adminUser = User::create([
            'name'              => 'Admin',
            'email'             => 'admin@gmail.com',
            'level'             => 'admin',
            'password'          => $password,
        ]);
        $supervisorUser = User::create([
            'name'              => 'Supervisor',
            'email'             => 'supervisor@gmail.com',
            'level'             => 'supervisor',
            'password'          => $password1,
        ]);
        
        // Create Petugas user
        $petugasUser = User::create([
   
            'name'              => 'Alvin',
            'email'             => 'petugas@gmail.com',
            'level'             => 'petugas',
            'password'          => $password2,
        ]);

        $teknisiUser = User::create([
            'name'              => 'Alvin',
            'email'             => 'member@gmail.com',
            'level'             => 'member',
            'password'          => $password3,
        ]);
        
        // Create Regular User
        $penggunaUser = User::create([
            'name'              => 'Aziz',
            'email'             => 'user@gmail.com',
            'level'             => 'pengguna',
            'password'          => $password4,
        ]);

        // Assign roles to the users

        $this->command->info('Users created and roles assigned!');
    }
}
