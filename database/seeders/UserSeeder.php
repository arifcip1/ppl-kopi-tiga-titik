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

        User::updateOrCreate(
            ['email' => 'kurirawal@gmail.com'],
            [
                'name'     => 'awaludin',
                'password' => Hash::make('password123'),
                'role'     => 'kurir',
            ]
        );

        // 3. Akun Pelanggan (Data Awal)
        User::updateOrCreate(
            ['email' => 'nabil@gmail.com'],
            [
                'name'     => 'Nabil Pratama',
                'password' => Hash::make('password123'),
                'role'     => 'pelanggan',
            ]
        );

        User::updateOrCreate(
            ['email' => 'arif@gmail.com'],
            [
                'name'     => 'arif',
                'password' => Hash::make('password123'),
                'role'     => 'pelanggan',
            ]
        );

        User::updateOrCreate(
            ['email' => 'nizam@gmail.com'],
            [
                'name'     => 'nizam',
                'password' => Hash::make('password123'),
                'role'     => 'pelanggan',
            ]
        );

        User::updateOrCreate(
            ['email' => 'davi@gmail.com'],
            [
                'name'     => 'davi',
                'password' => Hash::make('password123'),
                'role'     => 'pelanggan',
            ]
        );

        User::updateOrCreate(
            ['email' => 'farrel@gmail.com'],
            [
                'name'     => 'farrel',
                'password' => Hash::make('password123'),
                'role'     => 'pelanggan',
            ]
        );

        // 4. Tambahan Akun Pelanggan Baru untuk Variasi Simulasi
        User::updateOrCreate(
            ['email' => 'budi@gmail.com'],
            [
                'name'     => 'Budi Santoso',
                'password' => Hash::make('password123'),
                'role'     => 'pelanggan',
            ]
        );

        User::updateOrCreate(
            ['email' => 'siti@gmail.com'],
            [
                'name'     => 'Siti Aminah',
                'password' => Hash::make('password123'),
                'role'     => 'pelanggan',
            ]
        );

        User::updateOrCreate(
            ['email' => 'dimas@gmail.com'],
            [
                'name'     => 'Dimas Anggara',
                'password' => Hash::make('password123'),
                'role'     => 'pelanggan',
            ]
        );

        User::updateOrCreate(
            ['email' => 'ayu@gmail.com'],
            [
                'name'     => 'Ayu Lestari',
                'password' => Hash::make('password123'),
                'role'     => 'pelanggan',
            ]
        );

        User::updateOrCreate(
            ['email' => 'rina@gmail.com'],
            [
                'name'     => 'Rina Melati',
                'password' => Hash::make('password123'),
                'role'     => 'pelanggan',
            ]
        );
    }
}