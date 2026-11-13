<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\ShippingLocation;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Lokasi Asal Lapak Pengiriman Awal (BR-34, FR-11.1)
        $defaultLocation = ShippingLocation::updateOrCreate(
            ['nama_lokasi' => 'Lapak Jl. Kuwung'],
            [
                'alamat' => 'Jl. Kuwung, Mergelo, Kec. Magersari, Kota Mojokerto',
                'latitude' => -7.4726000,
                'longitude' => 112.4385000,
                'aktif' => true,
            ]
        );

        // Lokasi alternatif cadangan
        ShippingLocation::updateOrCreate(
            ['nama_lokasi' => 'Rumah Produksi Magersari'],
            [
                'alamat' => 'Jl. Benteng Pancasila No. 12, Magersari, Kota Mojokerto',
                'latitude' => -7.4688000,
                'longitude' => 112.4412000,
                'aktif' => true,
            ]
        );

        // 2. Konfigurasi Sistem (BR-35, BR-38, FR-11.4, FR-11.6)
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
            [
                'key' => 'lokasi_pengiriman_aktif_id',
                'value' => (string) $defaultLocation->id,
                'description' => 'ID lokasi lapak pengiriman aktif.',
            ],
            [
                'key' => 'jam_buka',
                'value' => '15:00',
                'description' => 'Jam operasional buka lapak.',
            ],
            [
                'key' => 'jam_tutup',
                'value' => '18:00',
                'description' => 'Jam operasional tutup lapak.',
            ],
            [
                'key' => 'status_lapak',
                'value' => 'buka',
                'description' => 'Status buka/tutup lapak hari ini (buka/tutup).',
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
