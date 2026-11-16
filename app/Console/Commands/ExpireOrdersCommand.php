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

        // Selesaikan otomatis pesanan ambil_sendiri yang melewati batas_pengambilan 24 jam (PRD Bab 8 / BR-41)
        $overduePickupOrders = Order::query()
            ->where('status', 'diproses')
            ->where('metode_pemenuhan', 'ambil_sendiri')
            ->whereNotNull('batas_pengambilan')
            ->where('batas_pengambilan', '<=', $now)
            ->get();

        $pickupCount = 0;
        foreach ($overduePickupOrders as $pickupOrder) {
            $pickupOrder->update(['status' => 'selesai']);
            \App\Models\OrderStatusHistory::create([
                'order_id' => $pickupOrder->id,
                'status_baru' => 'selesai',
                'diubah_oleh' => null,
                'waktu_perubahan' => $now,
                'keterangan' => 'Batas waktu pengambilan 24 jam terlewati, pesanan diselesaikan otomatis oleh sistem.',
            ]);
            $pickupCount++;
        }

        if ($pickupCount > 0) {
            $this->info("Total {$pickupCount} pesanan ambil sendiri melewati 24 jam berhasil diselesaikan otomatis.");
        }

        return Command::SUCCESS;
    }
}

