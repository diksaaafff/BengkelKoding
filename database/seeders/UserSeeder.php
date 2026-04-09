<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Add Admin
        User::create([
            'nama' => 'Administrator',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Add Doctor
        User::create([
            'nama' => 'Dr. Budi',
            'email' => 'dokter@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'dokter',
            'no_hp' => '081234567890',
            'alamat' => 'Semarang',
        ]);

        // Add Patient
        User::create([
            'nama' => 'Siti Aminah',
            'email' => 'pasien@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'pasien',
            'no_ktp' => '33210123456789',
            'no_hp' => '089876543210',
            'alamat' => 'Jogja',
        ]);
    }
}
