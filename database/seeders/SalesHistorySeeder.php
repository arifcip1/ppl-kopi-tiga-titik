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
        $products = Product::orderBy('id')->take(4)->get();

        // Periode 2 bulan terakhir sampai hari ini
        $startDate = Carbon::today()->subMonths(2);
        $endDate = Carbon::today();

        // Pola penjualan untuk masing-masing produk
        $salesPatterns = [
            // Produk 1: Penjualan meningkat
            [5, 6, 7, 8, 6, 9, 10, 8, 11, 12, 10, 13, 14, 12],

            // Produk 2: Penjualan menurun
            [18, 17, 16, 15, 17, 14, 13, 12, 14, 11, 10, 9, 11, 8],

            // Produk 3: Penjualan stabil
            [10, 11, 9, 10, 12, 10, 9, 11, 10, 12, 10, 9, 11, 10],

            // Produk 4: Penjualan fluktuatif
            [4, 15, 7, 18, 5, 13, 8, 20, 6, 16, 9, 19, 5, 14],
        ];

        foreach ($products as $index => $product) {
            $date = $startDate->copy();
            $pattern = $salesPatterns[$index];

            $day = 0;

            while ($date->lte($endDate)) {
                $jumlahTerjual = $pattern[$day % count($pattern)];

                SalesHistory::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'tanggal' => $date->toDateString(),
                        'sumber' => 'simulasi',
                    ],
                    [
                        'jumlah_terjual' => $jumlahTerjual,
                    ]
                );

                $date->addDay();
                $day++;
            }
        }
    }
}