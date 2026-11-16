<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\OrderStockService;
use Illuminate\Console\Command;

class ExpireOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Batalkan pesanan menunggu_pembayaran yang telah melewati batas waktu kedaluwarsa 10 menit dan kembalikan stok satu kali (FR-6.8)';

    /**
     * Execute the console command.
     */
    public function handle(OrderStockService $orderStockService): int
    {
        $this->info('Memeriksa pesanan kedaluwarsa...');

        $now = now();

        $expiredOrders = Order::query()
            ->where('status', 'menunggu_pembayaran')
            ->where(function ($query) use ($now) {
                $query->whereHas('payment', function ($q) use ($now) {
                    $q->where('status', 'pending')
                      ->whereNotNull('kedaluwarsa_pada')
                      ->where('kedaluwarsa_pada', '<=', $now);
                })->orWhere(function ($q) use ($now) {
                    $q->doesntHave('payment')
                      ->where('created_at', '<=', $now->copy()->subMinutes(10));
                });
            })
            ->get();

        $count = 0;
        foreach ($expiredOrders as $order) {
            $restored = $orderStockService->expireOrder($order);
            if ($restored) {
                $count++;
                $this->line("Pesanan {$order->kode} dibatalkan karena kedaluwarsa. Stok berhasil dikembalikan.");
            }
        }

        $this->info("Total {$count} pesanan kedaluwarsa berhasil diproses.");

        return Command::SUCCESS;
    }
}

