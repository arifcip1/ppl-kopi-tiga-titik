<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds for users (Admin, Kurir, Pelanggan).
     */
    public function run(): void
    {
        // 1. Akun Admin / Owner (Sesuai FR-1.2 & UI Farrel)
        User::updateOrCreate(
            ['email' => 'owner1@gmail.com'],
            [
                'name'     => 'Farrel',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'password123')),
                'role'     => 'admin',
            ]
        );

        // 2. Akun Kurir (Sesuai UI Ilham Ramadhan)
        User::updateOrCreate(
            ['email' => 'kurir1ilham@gmail.com'],
            [
                'name'     => 'Ilham Ramadhan',
                'password' => Hash::make('password123'),
                'role'     => 'kurir',
            ]
        );

        // 3. Akun Pelanggan (Sesuai UI Nabil Pratama)
        User::updateOrCreate(
            ['email' => 'nabil@gmail.com'],
            [
                'name'     => 'Nabil Pratama',
                'password' => Hash::make('password123'),
                'role'     => 'pelanggan',
            ]
        );
    }
}

