<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderStockService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderStockServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderStockService $service;

    /**
     * True jika sebuah test melakukan DB::commit() manual sehingga data lolos
     * dari rollback RefreshDatabase dan harus dibersihkan agar tidak bocor ke test lain.
     */
    private bool $committedOutsideTransaction = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new OrderStockService();
    }

    protected function tearDown(): void
    {
        if ($this->committedOutsideTransaction) {
            while (\Illuminate\Support\Facades\DB::transactionLevel() > 0) {
                \Illuminate\Support\Facades\DB::rollBack();
            }

            // Database in-memory dibuat ulang karena data test sudah ter-commit permanen.
            $this->artisan('migrate:fresh');
        }

        parent::tearDown();
    }

    /**
     * Membuat order dengan dua produk.
     * Stok diasumsikan sudah dikurangi saat checkout.
     */
    private function makeOrder(
        string $paymentStatus = 'pending',
        $expiresAt = null,
        bool $withPayment = true,
        string $orderStatus = 'menunggu_pembayaran'
    ): array {
        $productA = Product::factory()->create([
            'stok' => 7,
        ]);

        $productB = Product::factory()->create([
            'stok' => 3,
        ]);

        $order = Order::factory()->create([
            'status' => $orderStatus,
            'stok_dikembalikan' => false,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $productA->id,
            'jumlah' => 2,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $productB->id,
            'jumlah' => 3,
        ]);

        $payment = $withPayment
            ? Payment::factory()->for($order)->create([
                'status' => $paymentStatus,
                'kedaluwarsa_pada' => $expiresAt
                    ?? now()->addMinutes(10),
            ])
            : null;

        return [$order, $productA, $productB, $payment];
    }

    public function test_restore_stock_returns_stock_cancels_order_and_logs_history(): void
    {
        $actor = User::factory()->create();

        [$order, $a, $b] = $this->makeOrder();

        $this->assertTrue(
            $this->service->restoreStock(
                $order,
                $actor->id,
                'Dibatalkan pelanggan.'
            )
        );

        $this->assertSame(9, $a->fresh()->stok);
        $this->assertSame(6, $b->fresh()->stok);

        $order->refresh();

        $this->assertSame('dibatalkan', $order->status);
        $this->assertTrue($order->stok_dikembalikan);

        $history = OrderStatusHistory::where(
            'order_id',
            $order->id
        )->sole();

        $this->assertSame(
            'dibatalkan',
            $history->status_baru
        );

        $this->assertSame($actor->id, $history->diubah_oleh);
        $this->assertNotNull($history->waktu_perubahan);
        $this->assertNotNull($history->created_at);
    }

    public function test_restore_stock_is_safe_to_call_repeatedly(): void
    {
        [$order, $a, $b] = $this->makeOrder();

        $this->assertTrue($this->service->restoreStock($order));
        $this->assertFalse($this->service->restoreStock($order));
        $this->assertFalse($this->service->restoreStock($order->id));

        $this->assertSame(9, $a->fresh()->stok);
        $this->assertSame(6, $b->fresh()->stok);

        $this->assertSame(
            1,
            OrderStatusHistory::where('order_id', $order->id)->count()
        );
    }

    public function test_restore_stock_skips_order_not_waiting_payment(): void
    {
        [$order, $a] = $this->makeOrder(
            'berhasil',
            null,
            true,
            'diproses'
        );

        $this->assertFalse($this->service->restoreStock($order));

        $this->assertSame(7, $a->fresh()->stok);
        $this->assertSame('diproses', $order->fresh()->status);

        $this->assertSame(0, OrderStatusHistory::count());
    }

    public function test_restore_stock_skips_order_with_successful_payment(): void
    {
        [$order, $a, , $payment] = $this->makeOrder('berhasil');

        $this->assertFalse($this->service->restoreStock($order));

        $this->assertSame(7, $a->fresh()->stok);

        $this->assertSame(
            'menunggu_pembayaran',
            $order->fresh()->status
        );

        $this->assertSame('berhasil', $payment->fresh()->status);
    }

    public function test_restore_stock_returns_false_for_missing_order(): void
    {
        $this->assertFalse($this->service->restoreStock(999999));
    }

    public function test_restore_stock_skips_when_flag_already_set(): void
    {
        [$order, $a] = $this->makeOrder();

        $order->update([
            'stok_dikembalikan' => true,
        ]);

        $this->assertFalse($this->service->restoreStock($order));

        $this->assertSame(7, $a->fresh()->stok);
        $this->assertSame(0, OrderStatusHistory::count());
    }

    public function test_expire_order_sets_payment_expired_and_order_cancelled(): void
    {
        [$order, $a, $b, $payment] = $this->makeOrder(
            'pending',
            now()->subMinute()
        );

        $this->assertTrue($this->service->expireOrder($order));

        $this->assertSame('kedaluwarsa', $payment->fresh()->status);
        $this->assertSame('dibatalkan', $order->fresh()->status);

        $this->assertTrue($order->fresh()->stok_dikembalikan);

        $this->assertSame(9, $a->fresh()->stok);
        $this->assertSame(6, $b->fresh()->stok);

        $history = OrderStatusHistory::where(
            'order_id',
            $order->id
        )->sole();

        $this->assertNull($history->user_id);
    }

    public function test_expire_order_twice_returns_stock_once(): void
    {
        [$order, $a] = $this->makeOrder(
            'pending',
            now()->subMinute()
        );

        $this->assertTrue($this->service->expireOrder($order));
        $this->assertFalse($this->service->expireOrder($order));

        $this->assertSame(9, $a->fresh()->stok);

        $this->assertSame(
            1,
            OrderStatusHistory::where('order_id', $order->id)->count()
        );
    }

    public function test_expire_order_skips_when_not_yet_due(): void
    {
        [$order, $a, , $payment] = $this->makeOrder(
            'pending',
            now()->addMinutes(5)
        );

        $this->assertFalse($this->service->expireOrder($order));

        $this->assertSame('pending', $payment->fresh()->status);

        $this->assertSame(
            'menunggu_pembayaran',
            $order->fresh()->status
        );

        $this->assertSame(7, $a->fresh()->stok);
    }

    public function test_expire_order_skips_when_payment_not_pending(): void
    {
        foreach (['berhasil', 'gagal', 'kedaluwarsa'] as $status) {
            [$order, $a, , $payment] = $this->makeOrder(
                $status,
                now()->subMinute()
            );

            $this->assertFalse(
                $this->service->expireOrder($order),
                "Status {$status} seharusnya tidak diproses."
            );

            $this->assertSame($status, $payment->fresh()->status);

            $this->assertSame(
                'menunggu_pembayaran',
                $order->fresh()->status
            );

            $this->assertSame(7, $a->fresh()->stok);
        }
    }

    public function test_expire_order_without_payment_uses_created_at_fallback(): void
    {
        [$fresh, $a] = $this->makeOrder(
            'pending',
            null,
            false
        );

        $this->assertFalse($this->service->expireOrder($fresh));
        $this->assertSame(7, $a->fresh()->stok);

        [$old, $b] = $this->makeOrder(
            'pending',
            null,
            false
        );

        $old->forceFill([
            'created_at' => now()->subMinutes(11),
        ])->save();

        $this->assertTrue($this->service->expireOrder($old));
        $this->assertSame(9, $b->fresh()->stok);
    }

    public function test_fail_order_requires_payment_already_failed(): void
    {
        [$pendingOrder, $a] = $this->makeOrder('pending');

        $this->assertFalse($this->service->failOrder($pendingOrder));
        $this->assertSame(7, $a->fresh()->stok);

        [$noPayment, $b] = $this->makeOrder(
            'pending',
            null,
            false
        );

        $this->assertFalse($this->service->failOrder($noPayment));
        $this->assertSame(7, $b->fresh()->stok);

        [$paid, $c] = $this->makeOrder('berhasil');

        $this->assertFalse($this->service->failOrder($paid));
        $this->assertSame(7, $c->fresh()->stok);
    }

    public function test_fail_order_restores_stock_once_and_keeps_payment_status(): void
    {
        [$order, $a, $b, $payment] = $this->makeOrder('gagal');

        $this->assertTrue($this->service->failOrder($order));
        $this->assertFalse($this->service->failOrder($order));

        $this->assertSame('gagal', $payment->fresh()->status);
        $this->assertSame('dibatalkan', $order->fresh()->status);

        $this->assertTrue($order->fresh()->stok_dikembalikan);

        $this->assertSame(9, $a->fresh()->stok);
        $this->assertSame(6, $b->fresh()->stok);

        $this->assertSame(
            1,
            OrderStatusHistory::where('order_id', $order->id)->count()
        );
    }

    public function test_restore_stock_rolls_back_and_leaves_flag_false_when_product_not_found(): void
    {
        [$order, $a, $b] = $this->makeOrder();

        // Di SQLite, PRAGMA foreign_keys = OFF hanya berlaku di luar transaksi.
        // Commit transaksi RefreshDatabase sementara untuk menyimulasikan produk terhapus.
        \Illuminate\Support\Facades\DB::commit();
        $this->committedOutsideTransaction = true;
        \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = OFF;');
        $b->delete();
        \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = ON;');
        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            $this->service->restoreStock($order);
            $this->fail('RuntimeException seharusnya dilempar ketika produk tidak ditemukan.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('tidak ditemukan untuk pengembalian stok', $e->getMessage());
        }

        // Pastikan seluruh transaksi di-rollback:
        // 1. Stok produk A tidak bertambah (tetap 7)
        $this->assertSame(7, $a->fresh()->stok);

        // 2. Status order tetap menunggu_pembayaran
        $order->refresh();
        $this->assertSame('menunggu_pembayaran', $order->status);

        // 3. Flag stok_dikembalikan tetap false
        $this->assertFalse($order->stok_dikembalikan);

        // 4. Tidak ada riwayat yang dicatat
        $this->assertSame(0, OrderStatusHistory::where('order_id', $order->id)->count());
    }

    public function test_cancel_by_customer_succeeds_before_expiration(): void
    {
        Carbon::setTestNow($baseTime = Carbon::create(2026, 11, 7, 12, 0, 0));

        try {
            $user = User::factory()->create();
            [$order, $a, $b] = $this->makeOrder('pending', $baseTime->copy()->addMinutes(10));

            $result = $this->service->cancelByCustomer($order, $user->id);

            $this->assertTrue($result);
            $this->assertSame(9, $a->fresh()->stok);
            $this->assertSame(6, $b->fresh()->stok);

            $order->refresh();
            $this->assertSame('dibatalkan', $order->status);
            $this->assertTrue($order->stok_dikembalikan);

            $history = OrderStatusHistory::where('order_id', $order->id)->sole();
            $this->assertSame('dibatalkan', $history->status_baru);
            $this->assertSame($user->id, $history->diubah_oleh);
            $this->assertSame('Dibatalkan oleh pelanggan.', $history->keterangan);
        } finally {
            Carbon::setTestNow(null);
        }
    }

    public function test_cancel_by_customer_fails_after_expiration_and_does_not_modify_stock_or_order(): void
    {
        Carbon::setTestNow($baseTime = Carbon::create(2026, 11, 7, 12, 0, 0));

        try {
            $user = User::factory()->create();
            [$order, $a, $b] = $this->makeOrder('pending', $baseTime->copy()->addMinutes(10));

            // Majukan waktu ke batas kedaluwarsa persis (10 menit)
            Carbon::setTestNow($baseTime->copy()->addMinutes(10));

            $result = $this->service->cancelByCustomer($order, $user->id);

            $this->assertFalse($result);

            // Stok tidak boleh berubah
            $this->assertSame(7, $a->fresh()->stok);
            $this->assertSame(3, $b->fresh()->stok);

            // Status dan flag stok_dikembalikan tidak boleh berubah
            $order->refresh();
            $this->assertSame('menunggu_pembayaran', $order->status);
            $this->assertFalse($order->stok_dikembalikan);

            // Tidak ada riwayat pembatalan
            $this->assertSame(0, OrderStatusHistory::where('order_id', $order->id)->count());
        } finally {
            Carbon::setTestNow(null);
        }
    }

    public function test_cancel_by_customer_fails_when_payment_expires_at_is_null(): void
    {
        Carbon::setTestNow($baseTime = Carbon::create(2026, 11, 7, 12, 0, 0));

        try {
            $user = User::factory()->create();
            [$order, $a, $b, $payment] = $this->makeOrder('pending', null);
            $payment->update(['kedaluwarsa_pada' => null]);

            $result = $this->service->cancelByCustomer($order, $user->id);

            $this->assertFalse($result);
            $this->assertSame(7, $a->fresh()->stok);
            $this->assertSame(3, $b->fresh()->stok);

            $order->refresh();
            $this->assertSame('menunggu_pembayaran', $order->status);
            $this->assertFalse($order->stok_dikembalikan);
            $this->assertSame(0, OrderStatusHistory::where('order_id', $order->id)->count());
        } finally {
            Carbon::setTestNow(null);
        }
    }

    public function test_cancel_by_customer_uses_fallback_when_payment_is_null_and_succeeds_before_fallback(): void
    {
        Carbon::setTestNow($baseTime = Carbon::create(2026, 11, 7, 12, 0, 0));

        try {
            $user = User::factory()->create();
            [$order, $a, $b] = $this->makeOrder('pending', null, false);
            $order->forceFill(['created_at' => $baseTime])->save();

            // Berjalan di menit ke-5 (sebelum 10 menit)
            Carbon::setTestNow($baseTime->copy()->addMinutes(5));

            $result = $this->service->cancelByCustomer($order, $user->id);

            $this->assertTrue($result);
            $this->assertSame(9, $a->fresh()->stok);
            $this->assertSame(6, $b->fresh()->stok);

            $order->refresh();
            $this->assertSame('dibatalkan', $order->status);
            $this->assertTrue($order->stok_dikembalikan);
        } finally {
            Carbon::setTestNow(null);
        }
    }

    public function test_cancel_by_customer_uses_fallback_when_payment_is_null_and_fails_after_fallback(): void
    {
        Carbon::setTestNow($baseTime = Carbon::create(2026, 11, 7, 12, 0, 0));

        try {
            $user = User::factory()->create();
            [$order, $a, $b] = $this->makeOrder('pending', null, false);
            $order->forceFill(['created_at' => $baseTime])->save();

            // Majukan waktu ke menit ke-11 (lewat 10 menit fallback)
            Carbon::setTestNow($baseTime->copy()->addMinutes(11));

            $result = $this->service->cancelByCustomer($order, $user->id);

            $this->assertFalse($result);
            $this->assertSame(7, $a->fresh()->stok);
            $this->assertSame(3, $b->fresh()->stok);

            $order->refresh();
            $this->assertSame('menunggu_pembayaran', $order->status);
            $this->assertFalse($order->stok_dikembalikan);
            $this->assertSame(0, OrderStatusHistory::where('order_id', $order->id)->count());
        } finally {
            Carbon::setTestNow(null);
        }
    }
}
