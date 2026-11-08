<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\Shipping\ShippingCostResolver;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderEndpointTest extends TestCase
{
    use RefreshDatabase;

    private const FAKE_ONGKIR = 6000;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create(['role' => 'pelanggan']);

        // Default fake resolver untuk pengujian endpoint
        $this->app->bind(ShippingCostResolver::class, function () {
            return new class(self::ONGKIR_STATIC) implements ShippingCostResolver {
                public function resolve(float $lat, float $lng): int
                {
                    return 6000;
                }
            };
        });
    }

    private const ONGKIR_STATIC = 6000;

    private function validRecipient(array $overrides = []): array
    {
        return array_merge([
            'nama_penerima' => 'Ahmad Pelanggan',
            'telepon'       => '081234567890',
            'alamat'        => 'Jl. Pahlawan No. 10, Mojokerto',
            'lat'           => -7.4722,
            'lng'           => 112.4339,
            'catatan'       => 'Catatan pengantaran',
        ], $overrides);
    }

    // =========================================================================
    // PENGUJIAN ENDPOINT CHECKOUT (POST /checkout)
    // =========================================================================

    public function test_authenticated_customer_can_checkout_successfully_with_faked_resolver(): void
    {
        $productA = Product::factory()->create(['harga' => 12000, 'stok' => 10, 'aktif' => true]);
        $productB = Product::factory()->create(['harga' => 8000, 'stok' => 5, 'aktif' => true]);

        CartItem::create(['user_id' => $this->customer->id, 'product_id' => $productA->id, 'jumlah' => 2]);
        CartItem::create(['user_id' => $this->customer->id, 'product_id' => $productB->id, 'jumlah' => 1]);

        $response = $this->actingAs($this->customer)
            ->postJson('/checkout', $this->validRecipient());

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'kode', 'subtotal', 'ongkir', 'total', 'status', 'expires_at'],
            ]);

        // Verifikasi database order
        $order = Order::where('user_id', $this->customer->id)->sole();
        $this->assertSame('menunggu_pembayaran', $order->status);
        $this->assertSame(32000, $order->subtotal);
        $this->assertSame(6000, $order->ongkir);
        $this->assertSame(38000, $order->total);
        $this->assertFalse($order->stok_dikembalikan);

        // Stok berkurang tepat
        $this->assertSame(8, $productA->fresh()->stok);
        $this->assertSame(4, $productB->fresh()->stok);

        // Keranjang pelanggan dikosongkan
        $this->assertSame(0, CartItem::where('user_id', $this->customer->id)->count());

        // Payment dibuat pending
        $payment = Payment::where('order_id', $order->id)->sole();
        $this->assertSame('pending', $payment->status);
        $this->assertSame(38000, $payment->jumlah);

        // Status history tercatat
        $history = OrderStatusHistory::where('order_id', $order->id)->sole();
        $this->assertSame('menunggu_pembayaran', $history->status_baru);
        $this->assertSame($this->customer->id, $history->user_id);
    }

    public function test_unauthenticated_user_cannot_checkout(): void
    {
        $response = $this->postJson('/checkout', $this->validRecipient());

        $response->assertStatus(401);
        $this->assertSame(0, Order::count());
    }

    public function test_user_with_role_other_than_customer_cannot_checkout(): void
    {
        $admin = User::factory()->admin()->create();
        $courier = User::factory()->kurir()->create();

        $responseAdmin = $this->actingAs($admin)->postJson('/checkout', $this->validRecipient());
        $responseAdmin->assertStatus(403);

        $responseCourier = $this->actingAs($courier)->postJson('/checkout', $this->validRecipient());
        $responseCourier->assertStatus(403);

        $this->assertSame(0, Order::count());
    }

    public function test_checkout_with_invalid_recipient_data_is_rejected(): void
    {
        $product = Product::factory()->create(['stok' => 10, 'aktif' => true]);
        CartItem::create(['user_id' => $this->customer->id, 'product_id' => $product->id, 'jumlah' => 1]);

        $invalidData = [
            'nama_penerima' => '',
            'telepon'       => '',
            'alamat'        => '',
            'lat'           => 'bukan_angka',
            'lng'           => 999, // Di luar range -180..180
        ];

        $response = $this->actingAs($this->customer)->postJson('/checkout', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nama_penerima', 'telepon', 'alamat', 'lat', 'lng']);

        $this->assertSame(0, Order::count());
        $this->assertSame(10, $product->fresh()->stok);
        $this->assertSame(1, CartItem::where('user_id', $this->customer->id)->count());
    }

    public function test_checkout_with_empty_cart_is_rejected(): void
    {
        $response = $this->actingAs($this->customer)
            ->postJson('/checkout', $this->validRecipient());

        $response->assertStatus(422)
            ->assertJsonPath('errors.keranjang.0', 'Keranjang masih kosong.');

        $this->assertSame(0, Order::count());
    }

    public function test_checkout_with_insufficient_stock_is_rejected(): void
    {
        $product = Product::factory()->create(['stok' => 2, 'aktif' => true]);
        CartItem::create(['user_id' => $this->customer->id, 'product_id' => $product->id, 'jumlah' => 5]);

        $response = $this->actingAs($this->customer)
            ->postJson('/checkout', $this->validRecipient());

        $response->assertStatus(422);

        $this->assertSame(0, Order::count());
        $this->assertSame(2, $product->fresh()->stok);
        $this->assertSame(1, CartItem::where('user_id', $this->customer->id)->count());
    }

    public function test_checkout_failure_does_not_clear_cart_or_reduce_stock_partially(): void
    {
        $goodProduct = Product::factory()->create(['stok' => 10, 'aktif' => true]);
        $outOfStockProduct = Product::factory()->create(['stok' => 1, 'aktif' => true]);

        CartItem::create(['user_id' => $this->customer->id, 'product_id' => $goodProduct->id, 'jumlah' => 3]);
        CartItem::create(['user_id' => $this->customer->id, 'product_id' => $outOfStockProduct->id, 'jumlah' => 5]);

        $response = $this->actingAs($this->customer)
            ->postJson('/checkout', $this->validRecipient());

        $response->assertStatus(422);

        $this->assertSame(0, Order::count());
        $this->assertSame(10, $goodProduct->fresh()->stok);
        $this->assertSame(1, $outOfStockProduct->fresh()->stok);
        $this->assertSame(2, CartItem::where('user_id', $this->customer->id)->count());
    }

    public function test_financial_inputs_from_user_are_not_trusted(): void
    {
        $product = Product::factory()->create(['harga' => 15000, 'stok' => 10, 'aktif' => true]);
        CartItem::create(['user_id' => $this->customer->id, 'product_id' => $product->id, 'jumlah' => 2]);

        $tamperedData = $this->validRecipient([
            'harga'             => 1,
            'subtotal'          => 1,
            'ongkir'            => 0,
            'total'             => 1,
            'status'            => 'diproses',
            'user_id'           => 9999,
            'stok_dikembalikan' => true,
        ]);

        $response = $this->actingAs($this->customer)->postJson('/checkout', $tamperedData);

        $response->assertStatus(201);

        $order = Order::where('user_id', $this->customer->id)->sole();
        $this->assertSame(30000, $order->subtotal);
        $this->assertSame(6000, $order->ongkir);
        $this->assertSame(36000, $order->total);
        $this->assertSame('menunggu_pembayaran', $order->status);
        $this->assertFalse($order->stok_dikembalikan);
        $this->assertSame($this->customer->id, $order->user_id);
    }

    // =========================================================================
    // PENGUJIAN ENDPOINT PEMBATALAN PESANAN (POST /orders/{order}/cancel)
    // =========================================================================

    public function test_customer_can_cancel_eligible_order(): void
    {
        Carbon::setTestNow($baseTime = Carbon::create(2026, 11, 7, 14, 0, 0));

        try {
            $product = Product::factory()->create(['stok' => 5, 'aktif' => true]);

            $order = Order::factory()->create([
                'user_id'           => $this->customer->id,
                'status'            => 'menunggu_pembayaran',
                'stok_dikembalikan' => false,
            ]);

            OrderItem::factory()->create([
                'order_id'   => $order->id,
                'product_id' => $product->id,
                'jumlah'     => 3,
            ]);

            Payment::factory()->for($order)->create([
                'status'           => 'pending',
                'kedaluwarsa_pada' => $baseTime->copy()->addMinutes(10),
            ]);

            $response = $this->actingAs($this->customer)
                ->postJson("/orders/{$order->id}/cancel");

            $response->assertStatus(200)
                ->assertJsonPath('data.status', 'dibatalkan')
                ->assertJsonPath('data.stok_dikembalikan', true);

            // Verifikasi stok kembali
            $this->assertSame(8, $product->fresh()->stok);

            // Verifikasi database order
            $order->refresh();
            $this->assertSame('dibatalkan', $order->status);
            $this->assertTrue($order->stok_dikembalikan);

            // Verifikasi history dicatat dengan user_id pembeli
            $history = OrderStatusHistory::where('order_id', $order->id)->sole();
            $this->assertSame('menunggu_pembayaran', $history->status_sebelumnya);
            $this->assertSame('dibatalkan', $history->status_baru);
            $this->assertSame($this->customer->id, $history->user_id);
        } finally {
            Carbon::setTestNow(null);
        }
    }

    public function test_cancellation_restores_stock_exactly_once(): void
    {
        $product = Product::factory()->create(['stok' => 5]);

        $order = Order::factory()->create([
            'user_id'           => $this->customer->id,
            'status'            => 'menunggu_pembayaran',
            'stok_dikembalikan' => false,
        ]);

        OrderItem::factory()->create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'jumlah'     => 3,
        ]);

        Payment::factory()->for($order)->create([
            'status'           => 'pending',
            'kedaluwarsa_pada' => now()->addMinutes(10),
        ]);

        // Pembatalan pertama berhasil
        $res1 = $this->actingAs($this->customer)->postJson("/orders/{$order->id}/cancel");
        $res1->assertStatus(200);
        $this->assertSame(8, $product->fresh()->stok);

        // Pembatalan kedua ditolak dan tidak menambah stok
        $res2 = $this->actingAs($this->customer)->postJson("/orders/{$order->id}/cancel");
        $res2->assertStatus(422);

        $this->assertSame(8, $product->fresh()->stok);
        $this->assertSame(1, OrderStatusHistory::where('order_id', $order->id)->count());
    }

    public function test_customer_cannot_cancel_order_belonging_to_another_user(): void
    {
        $otherCustomer = User::factory()->create(['role' => 'pelanggan']);
        $product = Product::factory()->create(['stok' => 5]);

        $order = Order::factory()->create([
            'user_id'           => $otherCustomer->id,
            'status'            => 'menunggu_pembayaran',
            'stok_dikembalikan' => false,
        ]);

        OrderItem::factory()->create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'jumlah'     => 2,
        ]);

        Payment::factory()->for($order)->create([
            'status'           => 'pending',
            'kedaluwarsa_pada' => now()->addMinutes(10),
        ]);

        $response = $this->actingAs($this->customer)->postJson("/orders/{$order->id}/cancel");

        $response->assertStatus(403);
        $this->assertSame('menunggu_pembayaran', $order->fresh()->status);
        $this->assertSame(5, $product->fresh()->stok);
    }

    public function test_unauthenticated_user_cannot_cancel_order(): void
    {
        $order = Order::factory()->create(['status' => 'menunggu_pembayaran']);

        $response = $this->postJson("/orders/{$order->id}/cancel");

        $response->assertStatus(401);
    }

    public function test_user_with_role_other_than_customer_cannot_cancel_order(): void
    {
        $admin = User::factory()->admin()->create();
        $courier = User::factory()->kurir()->create();
        $order = Order::factory()->create(['user_id' => $this->customer->id, 'status' => 'menunggu_pembayaran']);

        $resAdmin = $this->actingAs($admin)->postJson("/orders/{$order->id}/cancel");
        $resAdmin->assertStatus(403);

        $resCourier = $this->actingAs($courier)->postJson("/orders/{$order->id}/cancel");
        $resCourier->assertStatus(403);
    }

    public function test_paid_order_cannot_be_cancelled_by_customer(): void
    {
        $product = Product::factory()->create(['stok' => 5]);

        $order = Order::factory()->create([
            'user_id'           => $this->customer->id,
            'status'            => 'menunggu_pembayaran',
            'stok_dikembalikan' => false,
        ]);

        OrderItem::factory()->create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'jumlah'     => 2,
        ]);

        Payment::factory()->for($order)->create([
            'status' => 'berhasil',
        ]);

        $response = $this->actingAs($this->customer)->postJson("/orders/{$order->id}/cancel");

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Pesanan yang sudah dibayar tidak dapat dibatalkan.');

        $this->assertSame(5, $product->fresh()->stok);
        $this->assertFalse($order->fresh()->stok_dikembalikan);
    }

    public function test_expired_or_ineligible_order_cannot_be_cancelled(): void
    {
        Carbon::setTestNow($baseTime = Carbon::create(2026, 11, 7, 14, 0, 0));

        try {
            $product = Product::factory()->create(['stok' => 5]);

            // 1. Pesanan sudah lewat waktu kedaluwarsa
            $expiredOrder = Order::factory()->create([
                'user_id'           => $this->customer->id,
                'status'            => 'menunggu_pembayaran',
                'stok_dikembalikan' => false,
            ]);

            OrderItem::factory()->create(['order_id' => $expiredOrder->id, 'product_id' => $product->id, 'jumlah' => 2]);
            Payment::factory()->for($expiredOrder)->create([
                'status'           => 'pending',
                'kedaluwarsa_pada' => $baseTime->copy()->subMinutes(5),
            ]);

            $resExpired = $this->actingAs($this->customer)->postJson("/orders/{$expiredOrder->id}/cancel");
            $resExpired->assertStatus(422);

            // Verifikasi bahwa penolakan tidak mengubah stok, status, dan stok_dikembalikan
            $this->assertSame(5, $product->fresh()->stok);
            $expiredOrder->refresh();
            $this->assertSame('menunggu_pembayaran', $expiredOrder->status);
            $this->assertFalse($expiredOrder->stok_dikembalikan);

            // 2. Pesanan sudah diproses
            $processingOrder = Order::factory()->create([
                'user_id'           => $this->customer->id,
                'status'            => 'diproses',
                'stok_dikembalikan' => false,
            ]);

            $resProcessing = $this->actingAs($this->customer)->postJson("/orders/{$processingOrder->id}/cancel");
            $resProcessing->assertStatus(422);

            $this->assertSame(5, $product->fresh()->stok);
            $this->assertSame('diproses', $processingOrder->fresh()->status);
        } finally {
            Carbon::setTestNow(null);
        }
    }

    public function test_cancelling_non_existent_order_returns_404(): void
    {
        $response = $this->actingAs($this->customer)->postJson('/orders/99999/cancel');

        $response->assertStatus(404);
    }
}
