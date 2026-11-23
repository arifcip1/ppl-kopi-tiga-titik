<?php

namespace App\Services;

use App\Models\SalesHistory;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Laporan riwayat penjualan riil. Basis: sales_history (sumber='transaksi') join products.
 * Kanal penjualan dicari lewat query terpisah (agregat per tanggal+produk) agar tidak ada
 * cartesian join yang menggandakan botol/omzet.
 */
class SalesReportService
{
    public const PERIODES = ['harian', 'mingguan', 'bulanan'];
    public const DEFAULT_SUMBER = 'Transaksi Riil (Online/POS)';

    public function normalizePeriode(?string $periode): string
    {
        return in_array($periode, self::PERIODES, true) ? $periode : 'bulanan';
    }

    /** @return array{0: Carbon, 1: Carbon} */
    public function range(string $periode): array
    {
        $today = today();

        return match ($periode) {
            'harian' => [$today->copy(), $today->copy()],
            'mingguan' => [$today->copy()->subDays(6), $today->copy()],
            default => [$today->copy()->startOfMonth(), $today->copy()],
        };
    }

    public function getReport(?string $periode): array
    {
        $periode = $this->normalizePeriode($periode);
        $sales = $this->getRows($periode);

        $totalBotol = (int) $sales->sum('jumlah');
        $totalOmzet = (int) $sales->sum('total_omzet');

        $terlaris = $sales->groupBy('varian')
            ->map(fn ($g) => $g->sum('jumlah'))
            ->sortDesc()
            ->keys()
            ->first();

        $perTanggal = $sales->groupBy('tanggal')->map(fn ($g) => (int) $g->sum('jumlah'))->sortKeys();

        return [
            'periode' => $periode,
            'summary' => [
                'total_botol' => $totalBotol,
                'total_omzet' => $totalOmzet,
                'varian_terlaris' => $terlaris ?? '-',
            ],
            'chartLabels' => $perTanggal->keys()
                ->map(fn ($d) => Carbon::parse($d)->format('j M'))->values()->all(),
            'chartData' => $perTanggal->values()->all(),
            'sales' => $sales->all(),
        ];
    }

    /** Baris rincian (terbaru dulu), dipakai UI dan export Excel. */
    public function getRows(string $periode): Collection
    {
        [$start, $end] = $this->range($periode);

        $history = SalesHistory::query()
            ->join('products', 'products.id', '=', 'sales_history.product_id')
            ->where('sales_history.sumber', 'transaksi')
            ->whereDate('sales_history.tanggal', '>=', $start->toDateString())
            ->whereDate('sales_history.tanggal', '<=', $end->toDateString())
            ->orderByDesc('sales_history.tanggal')
            ->orderBy('products.nama')
            ->get([
                'sales_history.product_id',
                'sales_history.tanggal',
                'sales_history.jumlah_terjual',
                'products.nama',
                'products.rasa',
                'products.harga',
            ]);

        [$kanal, $unitPrices] = $this->transactionData($start, $end);

        return $history->map(function ($h) use ($kanal, $unitPrices) {
            $tanggal = Carbon::parse($h->tanggal)->toDateString();
            $key = $tanggal.'|'.$h->product_id;

            // Prioritaskan harga satuan saat transaksi dari order_items (S3 / FR-15).
            // Fallback ke harga produk terkini jika nota order_items historis tidak ditemukan.
            $hargaSatuan = $unitPrices[$key] ?? (int) $h->harga;
            $totalOmzet = (int) $h->jumlah_terjual * $hargaSatuan;

            return [
                'tanggal' => $tanggal,
                'varian' => $h->nama,
                'sumber' => $kanal[$key] ?? self::DEFAULT_SUMBER,
                'jumlah' => (int) $h->jumlah_terjual,
                'total_omzet' => $totalOmzet,
            ];
        });
    }

    /**
     * Peta kanal dominan dan harga satuan transaksi dari orders valid (order_items.harga_satuan / subtotal).
     */
    private function transactionData(Carbon $start, Carbon $end): array
    {
        $rows = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('orders.status', ['diproses', 'dikirim', 'selesai'])
            ->whereBetween(DB::raw('DATE(orders.created_at)'), [$start->toDateString(), $end->toDateString()])
            ->groupBy(DB::raw('DATE(orders.created_at)'), 'order_items.product_id', 'orders.sumber_transaksi')
            ->select([
                DB::raw('DATE(orders.created_at) as tgl'),
                'order_items.product_id',
                'orders.sumber_transaksi',
                DB::raw('SUM(order_items.jumlah) as qty'),
                DB::raw('SUM(order_items.subtotal) as subtotal_real'),
            ])
            ->get();

        $channels = [];
        $unitPrices = [];

        foreach ($rows->groupBy(fn ($r) => $r->tgl.'|'.$r->product_id) as $key => $group) {
            $online = (int) $group->where('sumber_transaksi', 'online')->sum('qty');
            $pos = (int) $group->where('sumber_transaksi', 'pos')->sum('qty');

            $channels[$key] = match (true) {
                $online > 0 && $pos > 0 => 'Online & POS',
                $online > 0 => 'Online',
                $pos > 0 => 'POS Lapak',
                default => self::DEFAULT_SUMBER,
            };

            $totalQty = (int) $group->sum('qty');
            $totalSubtotal = (int) $group->sum('subtotal_real');
            $unitPrices[$key] = $totalQty > 0 ? (int) round($totalSubtotal / $totalQty) : null;
        }

        return [$channels, $unitPrices];
    }

    /**
     * Peta "tanggal|product_id" => label kanal dominan, dari orders valid.
     * Query terpisah & di-GROUP BY sehingga hanya dipakai sebagai label, bukan penjumlahan.
     */
    private function channelMap(Carbon $start, Carbon $end): array
    {
        [$channels] = $this->transactionData($start, $end);
        return $channels;
    }
}
