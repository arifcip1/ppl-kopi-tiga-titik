<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\SalesHistory;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SalesHistorySeeder extends Seeder
{
    public function run(): void
    {
        $produk = Product::whereIn('nama', [
            'Titik Jenuh',
            'Titik Manja',
            'Titik Temu',
            'Titik Hitam',
        ])->get()->keyBy('nama');

        $dataSimulasi = [
            'Titik Jenuh' => [
                5, 6, 4, 7, 5, 8, 6,
                4, 7, 5, 6, 8, 5, 7,
                6, 9, 5, 7, 6, 8, 7,
            ],
            'Titik Manja' => [
                8, 7, 9, 6, 10, 8, 7,
                9, 8, 11, 7, 10, 9, 8,
                10, 9, 12, 8, 11, 10, 9,
            ],
            'Titik Temu' => [
                7, 9, 8, 10, 6, 9, 8,
                8, 10, 9, 11, 7, 10, 9,
                9, 11, 10, 12, 8, 11, 10,
            ],
            'Titik Hitam' => [
                4, 5, 3, 6, 4, 5, 4,
                5, 6, 4, 7, 5, 6, 5,
                6, 7, 5, 8, 6, 7, 6,
            ],
        ];

        // Menggunakan 21 hari kalender terakhir, termasuk hari ini.
        $tanggalAkhir = Carbon::today();

        foreach ($dataSimulasi as $namaProduk => $jumlahHarian) {
            if (!isset($produk[$namaProduk])) {
                throw new \RuntimeException(
                    "Produk '{$namaProduk}' tidak ditemukan. Jalankan ProductSeeder terlebih dahulu."
                );
            }

            foreach ($jumlahHarian as $index => $jumlahTerjual) {
                $tanggal = $tanggalAkhir
                    ->copy()
                    ->subDays(20 - $index)
                    ->toDateString();

                SalesHistory::updateOrCreate(
                    [
                        'product_id' => $produk[$namaProduk]->id,
                        'tanggal' => $tanggal,
                        'sumber' => 'simulasi',
                    ],
                    [
                        'jumlah_terjual' => $jumlahTerjual,
                    ]
                );
            }
        }
    }
}
