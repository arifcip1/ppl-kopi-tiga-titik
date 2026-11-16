<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\SalesHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Service untuk mengelola siklus hidup & transisi status pesanan (Bab 8 & FR-8.3 & FR-8.5).
 * Menjamin integritas alur status dan pencatatan audit trail ke order_status_history.
 */
class OrderStatusService
{
    /**
     * Memeriksa apakah transisi dari status lama ke status baru diizinkan berdasarkan aktor dan aturan Bab 8.
     */
    public function canTransitionTo(Order $order, string $newStatus, ?User $actor = null): bool
    {
        $current = $order->status;

        // Tidak boleh transisi ke status yang sama
        if ($current === $newStatus) {
            return false;
        }

        // Pesanan selesai atau dibatalkan adalah status akhir (terminal state)
        if (in_array($current, ['selesai', 'dibatalkan'])) {
            return false;
        }

        return match ($current) {
            'menunggu_pembayaran' => in_array($newStatus, ['diproses', 'dibatalkan']),
            'diproses' => match ($newStatus) {
                'dikirim' => $order->metode_pemenuhan === 'pengiriman'
                    && ($actor === null || in_array($actor->role, ['admin', 'owner'])),
                'selesai' => $order->metode_pemenuhan === 'ambil_sendiri'
                    && ($actor === null || in_array($actor->role, ['admin', 'owner'])),
                default => false,
            },
            'dikirim' => $newStatus === 'selesai' && (
                $actor === null
                || in_array($actor->role, ['admin', 'owner'])
                || ($actor->role === 'kurir' && (int)$order->courier_id === (int)$actor->id)
            ),
            default => false,
        };
    }

    /**
     * Mengeksekusi transisi status pesanan dan mencatat riwayat ke order_status_history secara atomik.
     *
     * @throws HttpException Jika transisi tidak sah (409 Conflict) atau akses ditolak (403 Forbidden).
     */
    public function transition(
        Order $order,
        string $newStatus,
        ?User $actor = null,
        ?string $keterangan = null,
        array $extraAttributes = []
    ): Order {
        return DB::transaction(function () use ($order, $newStatus, $actor, $keterangan, $extraAttributes) {
            // Lock row untuk konkurensi aman
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            if (!$this->canTransitionTo($lockedOrder, $newStatus, $actor)) {
                // Khusus jika kurir mencoba menyelesaikan order milik kurir lain
                if ($lockedOrder->status === 'dikirim' && $newStatus === 'selesai' && $actor && $actor->role === 'kurir' && (int)$lockedOrder->courier_id !== (int)$actor->id) {
                    abort(403, 'Akses ditolak. Anda tidak berhak mengubah pesanan kurir lain.');
                }

                throw new HttpException(409, "Transisi status pesanan tidak diizinkan: dari [{$lockedOrder->status}] ke [{$newStatus}].");
            }

            // Perbarui atribut pesanan
            $lockedOrder->status = $newStatus;
            foreach ($extraAttributes as $key => $value) {
                $lockedOrder->{$key} = $value;
            }
            $lockedOrder->save();

            // Catat audit trail ke tabel order_status_history (FR-8.5)
            OrderStatusHistory::create([
                'order_id' => $lockedOrder->id,
                'status_baru' => $newStatus,
                'diubah_oleh' => $actor?->id,
                'waktu_perubahan' => now(),
                'keterangan' => $keterangan ?? "Status pesanan diubah menjadi {$newStatus}.",
            ]);

            // Catat ke sales_history secara atomik jika pesanan selesai (PPL-30 / Kontrak 20.4)
            if ($newStatus === 'selesai') {
                $this->syncSalesHistory($lockedOrder);
            }

            // Sinkronkan status payment jika pesanan sudah masuk diproses / dikirim / selesai
            if (in_array($newStatus, ['diproses', 'dikirim', 'selesai'])) {
                $lockedOrder->loadMissing('payment');
                if ($lockedOrder->payment && $lockedOrder->payment->status !== 'berhasil') {
                    $lockedOrder->payment->update([
                        'status'           => 'berhasil',
                        'waktu_verifikasi' => $lockedOrder->payment->waktu_verifikasi ?? now(),
                    ]);
                }
            }

            return $lockedOrder;
        });
    }

    /**
     * Mencatat agregasi penjualan produk ke tabel sales_history saat pesanan selesai.
     * Mencegah pencatatan ganda melalui OrderStockService::recordSalesHistory.
     */
    protected function syncSalesHistory(Order $order): void
    {
        app(OrderStockService::class)->recordSalesHistory($order);
    }
}

