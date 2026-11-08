<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ProductSeeder::class,
            UserSeeder::class,
            SettingSeeder::class,
            SalesHistorySeeder::class,
            OrderSeeder::class,
        ]);
    }
}
