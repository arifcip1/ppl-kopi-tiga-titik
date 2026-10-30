<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'nama' => 'Titik Hitam',
                'rasa' => 'Americano',
                'komposisi' => 'Kopi dan air',
                'deskripsi' => 'Kopi hitam dengan cita rasa khas americano.',
                'keawetan' => '5-7 hari dalam lemari pendingin',
                'harga' => 8000,
                'stok' => 0,
                'foto_path' => null,
                'aktif' => true,
            ],
            [
                'nama' => 'Titik Temu',
                'rasa' => 'Kopi Susu Gula Aren',
                'komposisi' => 'Kopi, susu, dan gula aren',
                'deskripsi' => 'Perpaduan kopi susu dengan manis alami gula aren.',
                'keawetan' => '5-7 hari dalam lemari pendingin',
                'harga' => 10000,
                'stok' => 0,
                'foto_path' => null,
                'aktif' => true,
            ],
            [
                'nama' => 'Titik Manja',
                'rasa' => 'Kopi Susu Caramel',
                'komposisi' => 'Kopi, susu, dan caramel',
                'deskripsi' => 'Kopi susu dengan sentuhan rasa caramel.',
                'keawetan' => '5-7 hari dalam lemari pendingin',
                'harga' => 10000,
                'stok' => 0,
                'foto_path' => null,
                'aktif' => true,
            ],
            [
                'nama' => 'Titik Jenuh',
                'rasa' => 'Coklat',
                'komposisi' => 'Coklat dan susu',
                'deskripsi' => 'Minuman coklat dengan rasa manis dan lembut.',
                'keawetan' => '5-7 hari dalam lemari pendingin',
                'harga' => 12000,
                'stok' => 0,
                'foto_path' => null,
                'aktif' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                [
                    'nama' => $product['nama'],
                ],
                $product
            );
        }
    }
}
