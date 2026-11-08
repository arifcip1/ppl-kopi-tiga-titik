<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use Closure;
use Illuminate\Support\Facades\DB;

/**
 * Pengembalian stok untuk pesanan yang belum dibayar (PPL-18, BR-10, BR-34, BR-36).
 *
 * Seluruh jalur pembatalan (kedaluwarsa, pembayaran gagal, pembatalan pelanggan)
 * lewat restoreStock() sehingga stok hanya dikembalikan satu kali.
 *
 * Pembagian tanggung jawab:
 * - Penjadwal kedaluwarsa  -> expireOrder(): payment pending -> kedaluwarsa, order -> dibatalkan.
 * - Webhook Midtrans (PPL-19) mengubah payments.status menjadi gagal, lalu memanggil failOrder().
 * - Service ini tidak pernah mengubah payment menjadi berhasil atau gagal.
 */
class OrderStockService
{
    /**
     * Batalkan pesanan yang masih menunggu_pembayaran dan kembalikan stok satu kali.
     * Payment tidak diubah. Pesanan dengan payment berhasil tidak akan dibatalkan.
     *
     * @return bool true jika stok dikembalikan pada pemanggilan ini, false jika tidak ada yang diproses.
     */
    public function restoreStock(
        Order|int $order,
        ?int $userId = null,
        string $keterangan = 'Pesanan dibatalkan, stok dikembalikan.'
    ): bool {
        return $this->cancelAndRestore($order, $userId, $keterangan);
    }

    /**
     * Pembatalan pesanan oleh pelanggan yang masih dalam batas waktu kedaluwarsa.
     * Validasi batas waktu pembayaran dijalankan di dalam transaksi setelah order dan payment dikunci.
     *
     * @return bool true jika pembatalan dan pengembalian stok berhasil, false jika tidak memenuhi syarat atau kedaluwarsa.
     */
    public function cancelByCustomer(Order|int $order, int $userId): bool
    {
        return $this->cancelAndRestore(
            $order,
            $userId,
            'Dibatalkan oleh pelanggan.',
            function (Order $locked, ?Payment $payment): bool {
                $expiresAt = $payment !== null
                    ? $payment->kedaluwarsa_pada
                    : $locked->created_at?->copy()->addMinutes(10);

                if ($expiresAt === null || now()->greaterThanOrEqualTo($expiresAt)) {
                    return false;
                }

                return true;
            }
        );
    }

    /**
     * Kedaluwarsa otomatis: hanya jika payment masih pending dan payments.kedaluwarsa_pada sudah lewat.
     * Tanpa payment, memakai fallback Order::expires_at (created_at + 10 menit).
     * Payment diubah pending -> kedaluwarsa, order menjadi dibatalkan.
     */
    public function expireOrder(Order|int $order, ?int $userId = null): bool
    {
        return $this->cancelAndRestore(
            $order,
            $userId,
            'Pembayaran kedaluwarsa, stok dikembalikan.',
            function (Order $locked, ?Payment $payment): bool {
                if ($payment === null) {
                    $expiresAt = $locked->expires_at;

                    return $expiresAt !== null && now()->greaterThanOrEqualTo($expiresAt);
                }

                return $payment->isPending()
                    && $payment->kedaluwarsa_pada !== null
                    && now()->greaterThanOrEqualTo($payment->kedaluwarsa_pada);
            },
            'kedaluwarsa'
        );
    }

    /**
     * Dipanggil webhook (PPL-19) setelah payments.status = gagal ditetapkan oleh verifikasi Midtrans.
     * Payment harus sudah berstatus gagal; service tidak mengubah status payment.
     */
    public function failOrder(Order|int $order, ?int $userId = null): bool
    {
        return $this->cancelAndRestore(
            $order,
            $userId,
            'Pembayaran gagal, stok dikembalikan.',
            fn (Order $locked, ?Payment $payment): bool => $payment !== null && $payment->isFailed()
        );
    }

    /**
     * Inti proses. Seluruh langkah dalam satu transaksi dengan lockForUpdate().
     *
     * @param  Closure(Order, ?Payment): bool|null  $precondition  Syarat tambahan, dicek setelah lock.
     * @param  string|null  $newPaymentStatus  Transisi payment dari pending (hanya 'kedaluwarsa').
     */
    private function cancelAndRestore(
        Order|int $order,
        ?int $userId,
        string $keterangan,
        ?Closure $precondition = null,
        ?string $newPaymentStatus = null
    ): bool {
        $orderId = $order instanceof Order ? $order->getKey() : $order;

        return DB::transaction(function () use ($orderId, $userId, $keterangan, $precondition, $newPaymentStatus) {
            $locked = Order::query()->lockForUpdate()->find($orderId);

            // Penanda stok_dikembalikan dan status mencegah pengembalian ganda.
            if ($locked === null
                || $locked->stok_dikembalikan
                || $locked->status !== 'menunggu_pembayaran') {
                return false;
            }

            $payment = Payment::query()
                ->where('order_id', $locked->id)
                ->lockForUpdate()
                ->first();

            // Pesanan yang pembayarannya berhasil tidak boleh dibatalkan/dikembalikan stoknya.
            if ($payment !== null && $payment->isSuccess()) {
                return false;
            }

            if ($precondition !== null && ! $precondition($locked, $payment)) {
                return false;
            }

            $this->incrementStock($locked);

            if ($newPaymentStatus !== null && $payment !== null) {
                // Transisi valid hanya dari pending (dijamin oleh precondition expireOrder).
                $payment->update(['status' => $newPaymentStatus]);
            }

            $statusSebelumnya = $locked->status;

            $locked->update([
                'status' => 'dibatalkan',
                'stok_dikembalikan' => true,
            ]);

            OrderStatusHistory::create([
                'order_id' => $locked->id,
                'status_sebelumnya' => $statusSebelumnya,
                'status_baru' => 'dibatalkan',
                'user_id' => $userId,
                'keterangan' => $keterangan,
            ]);

            return true;
        });
    }

    /**
     * Tambah stok sesuai order_items. Produk dikunci berurutan menurut id agar tidak deadlock.
     */
    private function incrementStock(Order $order): void
    {
        $quantities = $order->orderItems()
            ->get(['product_id', 'jumlah'])
            ->groupBy('product_id')
            ->map(fn ($items) => (int) $items->sum('jumlah'))
            ->sortKeys();

        foreach ($quantities as $productId => $jumlah) {
            $product = Product::query()->lockForUpdate()->find($productId);

            if ($product === null) {
                throw new \RuntimeException("Produk dengan ID {$productId} tidak ditemukan untuk pengembalian stok.");
            }

            $product->update(['stok' => $product->stok + $jumlah]);
        }
    }
}
