<?php

namespace App\Services;

use App\Models\Product;
use App\Models\SalesHistory;

/**
 * Simple Moving Average 3 minggu (21 hari kalender) berbasis sales_history riil.
 * Hanya sumber = 'transaksi'; baris 'simulasi' diabaikan.
 */
class ForecastService
{
    public function getForecast(): array
    {
        $today = today();

        $isDataSufficient = $this->isDataSufficient();

        if (!$isDataSufficient) {
            return ['isDataSufficient' => false, 'predictions' => []];
        }

        // Jendela: H-21 s.d. H-1 (tiga blok 7 hari)
        $blocks = [
            1 => [$today->copy()->subDays(21), $today->copy()->subDays(15)],
            2 => [$today->copy()->subDays(14), $today->copy()->subDays(8)],
            3 => [$today->copy()->subDays(7), $today->copy()->subDays(1)],
        ];

        $rows = SalesHistory::query()
            ->where('sumber', 'transaksi')
            ->whereDate('tanggal', '>=', $blocks[1][0]->toDateString())
            ->whereDate('tanggal', '<=', $blocks[3][1]->toDateString())
            ->get(['product_id', 'tanggal', 'jumlah_terjual']);

        $predictions = [];

        foreach (Product::where('aktif', true)->orderBy('id')->get() as $product) {
            $productRows = $rows->where('product_id', $product->id);
            $sums = [];

            foreach ($blocks as $i => [$from, $to]) {
                $sums[$i] = (int) $productRows
                    ->filter(fn ($r) => $r->tanggal->between($from, $to))
                    ->sum('jumlah_terjual');
            }

            // Rata-rata harian (SMA 21 hari kalender)
            $estimasi = round(($sums[1] + $sums[2] + $sums[3]) / 21, 1);

            $predictions[] = [
                'nama' => $product->nama,
                'rasa' => $product->rasa,
                'stok' => (int) $product->stok,
                'minggu_1' => $sums[1],
                'minggu_2' => $sums[2],
                'minggu_3' => $sums[3],
                'estimasi' => $estimasi,
                'saran_produksi' => max(0, (int) ceil($estimasi) - (int) $product->stok),
            ];
        }

        return ['isDataSufficient' => true, 'predictions' => $predictions];
    }

    /**
     * Cukup bila histori transaksi riil tertua sudah mencakup jendela 21 hari kalender
     * (bukan menghitung jumlah tanggal unik, karena hari tanpa penjualan tidak punya baris).
     */
    public function isDataSufficient(): bool
    {
        $oldest = SalesHistory::where('sumber', 'transaksi')->min('tanggal');

        if ($oldest === null) {
            return false;
        }

        return \Carbon\Carbon::parse($oldest)->startOfDay()->lte(today()->subDays(20));
    }
}
