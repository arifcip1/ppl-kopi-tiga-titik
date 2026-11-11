<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\CheckoutException;
use App\Services\CheckoutService;
use App\Services\Shipping\ShippingCostResolver;
use App\Services\Shipping\ShippingCostUnavailableException;
use App\Services\Shipping\UnavailableShippingCostResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class CheckoutServiceTest extends TestCase
{
    use RefreshDatabase;

    private const ONGKIR = 6000;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    private function fakeResolver(int $ongkir = self::ONGKIR): ShippingCostResolver
    {
        return new class($ongkir) implements ShippingCostResolver {
            public function __construct(private int $ongkir)
            {
            }

            public function resolve(float $lat, float $lng): int
            {
                return $this->ongkir;
            }
        };
    }

    private function service(?ShippingCostResolver $resolver = null): CheckoutService
    {
        return new CheckoutService($resolver ?? $this->fakeResolver());
    }

    private function recipient(array $override = []): array
    {
        return array_merge([
            'nama_penerima' => 'Budi Santoso',
            'telepon' => '081234567890',
            'alamat' => 'Jl. Majapahit No. 1, Mojokerto',
            'lat' => -7.4722,
            'lng' => 112.4339,
            'catatan' => 'Titip di satpam',
        ], $override);
    }

    private function addToCart(Product $product, int $jumlah): CartItem
    {
        return CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $product->id,
            'jumlah' => $jumlah,
        ]);
    }

    private function assertNothingPersisted(): void
    {
        $this->assertSame(0, Order::count());
        $this->assertSame(0, OrderItem::count());
        $this->assertSame(0, Payment::count());
        $this->assertSame(0, OrderStatusHistory::count());
    }

    public function test_checkout_succeeds_with_faked_resolver(): void
    {
        $a = Product::factory()->create(['harga' => 12000, 'stok' => 10, 'aktif' => true]);
        $b = Product::factory()->create(['harga' => 8000, 'stok' => 5, 'aktif' => true]);
        $this->addToCart($a, 2);
        $this->addToCart($b, 1);

        $order = $this->service()->createFromCart($this->user, $this->recipient());

        $this->assertSame('menunggu_pembayaran', $order->status);
        $this->assertSame($this->user->id, $order->user_id);
        $this->assertNull($order->courier_id);
        $this->assertFalse($order->stok_dikembalikan);
        $this->assertSame(32000, $order->subtotal);
        $this->assertSame(self::ONGKIR, $order->ongkir);
        $this->assertSame(32000 + self::ONGKIR, $order->total);
        $this->assertMatchesRegularExpression('/^KTT-\d{8}-\d{4}$/', $order->kode);
        $this->assertSame('Budi Santoso', $order->nama_penerima);
        $this->assertSame('Titip di satpam', $order->catatan);
        $this->assertCount(2, $order->orderItems);

        $history = OrderStatusHistory::where('order_id', $order->id)->sole();
        $this->assertSame('menunggu_pembayaran', $history->status_baru);
        $this->assertSame($this->user->id, $history->diubah_oleh);
        $this->assertNotNull($history->waktu_perubahan);
    }

    public function test_empty_cart_is_rejected(): void
    {
        try {
            $this->service()->createFromCart($this->user, $this->recipient());
            $this->fail('CheckoutException tidak dilempar.');
        } catch (CheckoutException $e) {
            $this->assertArrayHasKey('keranjang', $e->errors());
        }

        $this->assertNothingPersisted();
    }

    public function test_inactive_product_is_rejected_without_side_effects(): void
    {
        $active = Product::factory()->create(['stok' => 10, 'aktif' => true]);
        $inactive = Product::factory()->create(['stok' => 10, 'aktif' => false]);
        $this->addToCart($active, 2);
        $this->addToCart($inactive, 1);

        try {
            $this->service()->createFromCart($this->user, $this->recipient());
            $this->fail('CheckoutException tidak dilempar.');
        } catch (CheckoutException $e) {
            $this->assertArrayHasKey('produk', $e->errors());
        }

        $this->assertNothingPersisted();
        $this->assertSame(10, $active->fresh()->stok);
        $this->assertSame(2, CartItem::count());
    }

    public function test_insufficient_stock_is_rejected_without_side_effects(): void
    {
        $enough = Product::factory()->create(['stok' => 10, 'aktif' => true]);
        $short = Product::factory()->create(['stok' => 2, 'aktif' => true]);
        $this->addToCart($enough, 3);
        $this->addToCart($short, 3);

        try {
            $this->service()->createFromCart($this->user, $this->recipient());
            $this->fail('CheckoutException tidak dilempar.');
        } catch (CheckoutException $e) {
            $this->assertArrayHasKey('stok', $e->errors());
        }

        $this->assertNothingPersisted();
        $this->assertSame(10, $enough->fresh()->stok);
        $this->assertSame(2, $short->fresh()->stok);
        $this->assertSame(2, CartItem::count());
    }

    public function test_price_total_and_shipping_from_browser_are_ignored(): void
    {
        $product = Product::factory()->create(['harga' => 10000, 'stok' => 10, 'aktif' => true]);
        $this->addToCart($product, 2);

        $order = $this->service()->createFromCart($this->user, $this->recipient([
            'harga' => 1,
            'harga_satuan' => 1,
            'subtotal' => 1,
            'total' => 1,
            'ongkir' => 0,
            'status' => 'diproses',
            'user_id' => 9999,
            'courier_id' => 9999,
            'stok_dikembalikan' => true,
        ]));

        $this->assertSame(20000, $order->subtotal);
        $this->assertSame(self::ONGKIR, $order->ongkir);
        $this->assertSame(20000 + self::ONGKIR, $order->total);
        $this->assertSame('menunggu_pembayaran', $order->status);
        $this->assertSame($this->user->id, $order->user_id);
        $this->assertNull($order->courier_id);
        $this->assertFalse($order->stok_dikembalikan);
        $this->assertSame(10000, $order->orderItems->first()->harga_satuan);
    }

    public function test_order_item_snapshot_is_correct_and_independent_of_later_changes(): void
    {
        $product = Product::factory()->create([
            'nama' => 'Titik Jenuh',
            'rasa' => 'Coklat susu',
            'harga' => 12000,
            'stok' => 10,
            'aktif' => true,
        ]);
        $this->addToCart($product, 3);

        $order = $this->service()->createFromCart($this->user, $this->recipient());

        $product->update(['nama' => 'Nama Baru', 'rasa' => 'Rasa Baru', 'harga' => 99000]);

        $item = $order->fresh()->orderItems->sole();
        $this->assertSame($product->id, $item->product_id);
        $this->assertSame('Titik Jenuh', $item->nama_produk);
        $this->assertSame('Coklat susu', $item->rasa);
        $this->assertSame(3, $item->jumlah);
        $this->assertSame(12000, $item->harga_satuan);
        $this->assertSame(36000, $item->subtotal);
    }

    public function test_payment_is_created_pending_with_ten_minute_expiry(): void
    {
        $product = Product::factory()->create(['stok' => 10, 'aktif' => true]);
        $this->addToCart($product, 1);

        $this->travelTo(now()->startOfSecond());

        $order = $this->service()->createFromCart($this->user, $this->recipient());

        $payment = Payment::where('order_id', $order->id)->sole();
        $this->assertSame('pending', $payment->status);
        $this->assertSame($order->total, $payment->jumlah);
        $this->assertNull($payment->waktu_verifikasi);
        $this->assertEquals(
            now()->addMinutes(10)->timestamp,
            $payment->kedaluwarsa_pada->timestamp
        );

        $this->travelBack();
    }

    public function test_stock_is_reduced_exactly_by_ordered_quantity(): void
    {
        $a = Product::factory()->create(['stok' => 10, 'aktif' => true]);
        $b = Product::factory()->create(['stok' => 4, 'aktif' => true]);
        $untouched = Product::factory()->create(['stok' => 8, 'aktif' => true]);
        $this->addToCart($a, 3);
        $this->addToCart($b, 4);

        $this->service()->createFromCart($this->user, $this->recipient());

        $this->assertSame(7, $a->fresh()->stok);
        $this->assertSame(0, $b->fresh()->stok);
        $this->assertSame(8, $untouched->fresh()->stok);
    }

    public function test_cart_is_emptied_only_for_the_checking_out_user(): void
    {
        $product = Product::factory()->create(['stok' => 10, 'aktif' => true]);
        $this->addToCart($product, 1);

        $other = User::factory()->create();
        CartItem::create(['user_id' => $other->id, 'product_id' => $product->id, 'jumlah' => 1]);

        $this->service()->createFromCart($this->user, $this->recipient());

        $this->assertSame(0, CartItem::where('user_id', $this->user->id)->count());
        $this->assertSame(1, CartItem::where('user_id', $other->id)->count());
    }

    public function test_default_resolver_rejects_checkout_without_side_effects(): void
    {
        $this->assertInstanceOf(
            UnavailableShippingCostResolver::class,
            $this->app->make(ShippingCostResolver::class)
        );

        $product = Product::factory()->create(['stok' => 10, 'aktif' => true]);
        $this->addToCart($product, 2);

        try {
            $this->app->make(CheckoutService::class)->createFromCart($this->user, $this->recipient());
            $this->fail('ShippingCostUnavailableException tidak dilempar.');
        } catch (ShippingCostUnavailableException $e) {
            $this->assertStringContainsString('ongkir belum tersedia', $e->getMessage());
        }

        $this->assertNothingPersisted();
        $this->assertSame(10, $product->fresh()->stok);
        $this->assertSame(1, CartItem::count());
    }

    public function test_resolver_failure_leaves_no_order_payment_or_stock_change(): void
    {
        $product = Product::factory()->create(['stok' => 10, 'aktif' => true]);
        $this->addToCart($product, 2);

        $failing = new class implements ShippingCostResolver {
            public function resolve(float $lat, float $lng): int
            {
                throw new RuntimeException('Layanan routing gagal.');
            }
        };

        try {
            $this->service($failing)->createFromCart($this->user, $this->recipient());
            $this->fail('Exception resolver tidak diteruskan.');
        } catch (RuntimeException $e) {
            $this->assertSame('Layanan routing gagal.', $e->getMessage());
        }

        $this->assertNothingPersisted();
        $this->assertSame(10, $product->fresh()->stok);
        $this->assertSame(1, CartItem::count());
    }

    public function test_failure_midway_rolls_back_everything(): void
    {
        $a = Product::factory()->create(['stok' => 10, 'aktif' => true]);
        $b = Product::factory()->create(['stok' => 5, 'aktif' => true]);
        $this->addToCart($a, 2);
        $this->addToCart($b, 1);

        // Gagal pada langkah terakhir sebelum keranjang dikosongkan: order, item, payment,
        // dan pengurangan stok sudah terjadi dan harus ikut di-rollback.
        OrderStatusHistory::creating(function () {
            throw new RuntimeException('Gagal menulis riwayat.');
        });

        try {
            $this->service()->createFromCart($this->user, $this->recipient());
            $this->fail('Exception tidak diteruskan.');
        } catch (RuntimeException $e) {
            $this->assertSame('Gagal menulis riwayat.', $e->getMessage());
        } finally {
            OrderStatusHistory::flushEventListeners();
        }

        $this->assertNothingPersisted();
        $this->assertSame(10, $a->fresh()->stok);
        $this->assertSame(5, $b->fresh()->stok);
        $this->assertSame(2, CartItem::count());
    }

    public function test_invalid_recipient_data_is_rejected_before_any_work(): void
    {
        $product = Product::factory()->create(['stok' => 10, 'aktif' => true]);
        $this->addToCart($product, 1);

        foreach ([
            ['nama_penerima' => ''],
            ['telepon' => null],
            ['alamat' => '   '],
            ['lat' => null],
            ['lng' => 'abc'],
            ['lat' => 120],
        ] as $override) {
            try {
                $this->service()->createFromCart($this->user, $this->recipient($override));
                $this->fail('CheckoutException tidak dilempar untuk ' . json_encode($override));
            } catch (CheckoutException $e) {
                $this->assertNotEmpty($e->errors());
            }
        }

        $this->assertNothingPersisted();
        $this->assertSame(10, $product->fresh()->stok);
        $this->assertSame(1, CartItem::count());
    }

    public function test_checked_out_order_can_be_expired_and_stock_restored_once(): void
    {
        $product = Product::factory()->create(['stok' => 10, 'aktif' => true]);
        $this->addToCart($product, 4);

        $order = $this->service()->createFromCart($this->user, $this->recipient());
        $this->assertSame(6, $product->fresh()->stok);

        $this->travel(11)->minutes();

        $stockService = new \App\Services\OrderStockService();
        $this->assertTrue($stockService->expireOrder($order));
        $this->assertFalse($stockService->expireOrder($order));

        $this->assertSame(10, $product->fresh()->stok);
    }

    public function test_order_code_collision_is_retried_and_succeeds(): void
    {
        $product = Product::factory()->create(['harga' => 10000, 'stok' => 10, 'aktif' => true]);
        $this->addToCart($product, 2);

        $existingCode = 'KTT-' . now()->format('Ymd') . '-0001';
        Order::factory()->create(['kode' => $existingCode]);

        $attemptCount = 0;
        Order::creating(function (Order $order) use (&$attemptCount, $existingCode) {
            $attemptCount++;
            if ($attemptCount === 1) {
                // Simulasikan collision pada attempt 1
                $order->kode = $existingCode;
            }
        });

        try {
            $order = $this->service()->createFromCart($this->user, $this->recipient());

            $this->assertSame(2, $attemptCount);
            $this->assertSame('KTT-' . now()->format('Ymd') . '-0002', $order->kode);
            $this->assertSame('menunggu_pembayaran', $order->status);
            $this->assertSame(8, $product->fresh()->stok);
            $this->assertSame(0, CartItem::where('user_id', $this->user->id)->count());
        } finally {
            Order::flushEventListeners();
        }
    }

    public function test_order_code_collision_rolls_back_everything_when_retry_limit_exceeded(): void
    {
        $product = Product::factory()->create(['harga' => 10000, 'stok' => 10, 'aktif' => true]);
        $this->addToCart($product, 2);

        $existingCode = 'KTT-' . now()->format('Ymd') . '-0001';
        Order::factory()->create(['kode' => $existingCode]);

        $attemptCount = 0;
        Order::creating(function (Order $order) use (&$attemptCount, $existingCode) {
            $attemptCount++;
            // Paksa tabrakan kode pada seluruh percobaan
            $order->kode = $existingCode;
        });

        try {
            $this->service()->createFromCart($this->user, $this->recipient());
            $this->fail('QueryException seharusnya dilempar setelah 3 kali percobaan gagal.');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertSame(3, $attemptCount);
        } finally {
            Order::flushEventListeners();
        }

        // Pastikan tidak ada data parsial tertinggal (hanya 1 existing order awal)
        $this->assertSame(1, Order::count());
        $this->assertSame(0, OrderItem::count());
        $this->assertSame(0, Payment::count());
        $this->assertSame(0, OrderStatusHistory::count());

        $this->assertSame(10, $product->fresh()->stok);
        $this->assertSame(1, CartItem::where('user_id', $this->user->id)->count());
    }

    public function test_duplicate_key_on_other_table_is_not_retried(): void
    {
        $product = Product::factory()->create(['harga' => 10000, 'stok' => 10, 'aktif' => true]);
        $this->addToCart($product, 2);

        $attemptCount = 0;
        Payment::creating(function () use (&$attemptCount) {
            $attemptCount++;
            throw new \Illuminate\Database\QueryException(
                'sqlite',
                'insert into payments...',
                [],
                new \Exception('UNIQUE constraint failed: payments.order_id')
            );
        });

        try {
            $this->service()->createFromCart($this->user, $this->recipient());
            $this->fail('QueryException duplicate key pada tabel lain seharusnya dilempar langsung tanpa retry.');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertSame(1, $attemptCount);
        } finally {
            Payment::flushEventListeners();
        }

        $this->assertNothingPersisted();
        $this->assertSame(10, $product->fresh()->stok);
        $this->assertSame(1, CartItem::where('user_id', $this->user->id)->count());
    }

    public function test_foreign_key_query_exception_is_not_retried(): void
    {
        $product = Product::factory()->create(['harga' => 10000, 'stok' => 10, 'aktif' => true]);
        $this->addToCart($product, 2);

        $attemptCount = 0;
        Order::creating(function () use (&$attemptCount) {
            $attemptCount++;
            // Foreign key violation di MySQL (error 1452) atau SQLite
            $previous = new \PDOException('Integrity constraint violation: 1452 Cannot add or update a child row: a foreign key constraint fails');
            $previous->errorInfo = ['23000', 1452, 'Cannot add or update a child row: a foreign key constraint fails'];

            throw new \Illuminate\Database\QueryException(
                'mysql',
                'insert into orders...',
                [],
                $previous
            );
        });

        try {
            $this->service()->createFromCart($this->user, $this->recipient());
            $this->fail('QueryException foreign key violation seharusnya dilempar langsung tanpa retry.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Harus langsung gagal pada percobaan pertama
            $this->assertSame(1, $attemptCount);
        } finally {
            Order::flushEventListeners();
        }

        $this->assertNothingPersisted();
        $this->assertSame(10, $product->fresh()->stok);
        $this->assertSame(1, CartItem::where('user_id', $this->user->id)->count());
    }

    public function test_mysql_order_code_collision_format_is_recognized_and_retried(): void
    {
        $product = Product::factory()->create(['harga' => 10000, 'stok' => 10, 'aktif' => true]);
        $this->addToCart($product, 2);

        $attemptCount = 0;
        Order::creating(function (Order $order) use (&$attemptCount) {
            $attemptCount++;
            if ($attemptCount === 1) {
                // Simulasikan QueryException persis MySQL error 1062 untuk orders_kode_unique pada attempt 1
                $previous = new \PDOException("Integrity constraint violation: 1062 Duplicate entry '{$order->kode}' for key 'orders_kode_unique'");
                $previous->errorInfo = ['23000', 1062, "Duplicate entry '{$order->kode}' for key 'orders_kode_unique'"];

                throw new \Illuminate\Database\QueryException(
                    'mysql',
                    'insert into orders...',
                    [],
                    $previous
                );
            }
        });

        try {
            $order = $this->service()->createFromCart($this->user, $this->recipient());

            // Percobaan pertama ditangkap dan di-retry, percobaan kedua berhasil
            $this->assertSame(2, $attemptCount);
            $this->assertSame('menunggu_pembayaran', $order->status);
            $this->assertSame(8, $product->fresh()->stok);
        } finally {
            Order::flushEventListeners();
        }
    }

    public function test_checkout_with_ambil_sendiri_has_zero_shipping_and_correct_fulfillment_method(): void
    {
        $product = Product::factory()->create(['harga' => 15000, 'stok' => 10, 'aktif' => true]);
        $this->addToCart($product, 2);

        $neverCalledResolver = new class implements ShippingCostResolver {
            public function resolve(float $lat, float $lng): int
            {
                throw new \RuntimeException('Shipping resolver tidak boleh dipanggil untuk ambil_sendiri.');
            }
        };

        $service = new CheckoutService($neverCalledResolver);

        $order = $service->createFromCart($this->user, $this->recipient([
            'metode_pemenuhan' => 'ambil_sendiri',
            'lat' => null,
            'lng' => null,
        ]));

        $this->assertSame('online', $order->sumber_transaksi);
        $this->assertSame('ambil_sendiri', $order->metode_pemenuhan);
        $this->assertNull($order->lat);
        $this->assertNull($order->lng);
        $this->assertSame(30000, $order->subtotal);
        $this->assertSame(0, $order->ongkir);
        $this->assertSame(30000, $order->total);

        $payment = Payment::where('order_id', $order->id)->sole();
        $this->assertSame('midtrans', $payment->metode_pembayaran);
        $this->assertSame(30000, $payment->jumlah);
    }

    public function test_checkout_pengiriman_without_lat_is_rejected(): void
    {
        $product = Product::factory()->create(['harga' => 10000, 'stok' => 5, 'aktif' => true]);
        $this->addToCart($product, 1);

        $this->expectException(CheckoutException::class);
        $this->expectExceptionMessage('Data pengiriman tidak valid.');

        $data = $this->recipient(['lat' => null, 'metode_pemenuhan' => 'pengiriman']);
        $this->service()->createFromCart($this->user, $data);
    }

    public function test_checkout_pengiriman_without_lng_is_rejected(): void
    {
        $product = Product::factory()->create(['harga' => 10000, 'stok' => 5, 'aktif' => true]);
        $this->addToCart($product, 1);

        $this->expectException(CheckoutException::class);
        $this->expectExceptionMessage('Data pengiriman tidak valid.');

        $data = $this->recipient(['lng' => null, 'metode_pemenuhan' => 'pengiriman']);
        $this->service()->createFromCart($this->user, $data);
    }

    public function test_checkout_pengiriman_with_invalid_coordinates_is_rejected(): void
    {
        $product = Product::factory()->create(['harga' => 10000, 'stok' => 5, 'aktif' => true]);
        $this->addToCart($product, 1);

        $this->expectException(CheckoutException::class);
        $this->expectExceptionMessage('Data pengiriman tidak valid.');

        $data = $this->recipient(['lat' => 100, 'lng' => 200, 'metode_pemenuhan' => 'pengiriman']);
        $this->service()->createFromCart($this->user, $data);
    }

    public function test_payment_factory_supports_online_and_pos_payment_methods(): void
    {
        $onlinePayment = Payment::factory()->midtrans()->create();
        $this->assertSame('midtrans', $onlinePayment->metode_pembayaran);

        $posTunai = Payment::factory()->tunai()->create();
        $this->assertSame('tunai', $posTunai->metode_pembayaran);

        $posQris = Payment::factory()->qris()->create();
        $this->assertSame('qris', $posQris->metode_pembayaran);

        $posOrder = Order::factory()->pos()->create();
        $this->assertSame('pos', $posOrder->sumber_transaksi);
        $this->assertNull($posOrder->metode_pemenuhan);
    }
}
