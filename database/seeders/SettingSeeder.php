<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'batas_stok_minimum',
                'value' => '5',
                'description' => 'Batas minimum stok produk.',
            ],
            [
                'key' => 'stok_harian_standar',
                'value' => '10',
                'description' => 'Acuan stok harian standar.',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
