<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SalesHistory;
use App\Models\Setting;
use App\Models\User;
use App\Services\WebNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerPosBackendTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::updateOrCreate(
            ['key' => 'batas_stok_minimum'],
            ['value' => '5', 'description' => 'Batas minimum stok produk']
        );

        Setting::updateOrCreate(
            ['key' => 'stok_harian_standar'],
            ['value' => '10', 'description' => 'Standar stok harian']
        );
    }

    private function createAdmin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_admin_can_process_pos_cash_transaction_successfully(): void
    {
        $admin = $this->createAdmin();

        $product1 = Product::factory()->create([
            'nama'  => 'Titik Temu',
            'rasa'  => 'Gula Aren',
            'harga' => 10000,
            'stok'  => 20,
            'aktif' => true,
        ]);

        $product2 = Product::factory()->create([
            'nama'  => 'Titik Hitam',
            'rasa'  => 'Americano',
            'harga' => 8000,
            'stok'  => 15,
            'aktif' => true,
        ]);

        $payload = [
            'items' => [
                ['product_id' => $product1->id, 'jumlah' => 2],
                ['product_id' => $product2->id, 'jumlah' => 1],
            ],
            'metode_pembayaran' => 'tunai',
            'nama_pelanggan'    => 'Budi Lapak',
            'uang_diterima'     => 50000,
        ];

        $response = $this->actingAs($admin)
            ->postJson('/owner/pos/transactions', $payload);

        $response->assertCreated();
        $response->assertJson([
            'success' => true,
            'message' => 'Transaksi kasir lapak offline berhasil diselesaikan.',
        ]);

        $totalExpected = (10000 * 2) + (8000 * 1); // 28.000
        $kembalianExpected = 50000 - $totalExpected; // 22.000

        // 1. Verifikasi Order di Database
        $this->assertDatabaseHas('orders', [
            'sumber_transaksi' => 'pos',
            'metode_pemenuhan' => null,
            'user_id'          => $admin->id,
            'courier_id'       => null,
            'status'           => 'selesai',
            'subtotal'         => $totalExpected,
            'ongkir'           => 0,
            'total'            => $totalExpected,
            'nama_pelanggan'   => 'Budi Lapak',
        ]);

        $order = Order::where('nama_pelanggan', 'Budi Lapak')->first();
        $this->assertNotNull($order);
        $this->assertStringStartsWith('POS-', $order->kode);

        // 2. Verifikasi Order Items
        $this->assertDatabaseHas('order_items', [
            'order_id'     => $order->id,
            'product_id'   => $product1->id,
            'nama_produk'  => 'Titik Temu',
            'jumlah'       => 2,
            'harga_satuan' => 10000,
            'subtotal'     => 20000,
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id'     => $order->id,
            'product_id'   => $product2->id,
            'nama_produk'  => 'Titik Hitam',
            'jumlah'       => 1,
            'harga_satuan' => 8000,
            'subtotal'     => 8000,
        ]);

        // 3. Verifikasi Payment
        $this->assertDatabaseHas('payments', [
            'order_id'          => $order->id,
            'metode_pembayaran' => 'tunai',
            'status'            => 'berhasil',
            'jumlah'            => $totalExpected,
        ]);

        // 4. Verifikasi Pengurangan Stok Atomik
        $this->assertEquals(18, $product1->fresh()->stok);
        $this->assertEquals(14, $product2->fresh()->stok);

        // 5. Verifikasi Pencatatan sales_history
        $this->assertDatabaseHas('sales_history', [
            'product_id'     => $product1->id,
            'jumlah_terjual' => 2,
            'sumber'         => 'transaksi',
        ]);

        $this->assertDatabaseHas('sales_history', [
            'product_id'     => $product2->id,
            'jumlah_terjual' => 1,
            'sumber'         => 'transaksi',
        ]);
    }

    public function test_admin_can_process_pos_qris_transaction_successfully(): void
    {
        $admin = $this->createAdmin();

        $product = Product::factory()->create([
            'nama'  => 'Titik Manja',
            'harga' => 12000,
            'stok'  => 10,
            'aktif' => true,
        ]);

        $payload = [
            'items' => [
                ['product_id' => $product->id, 'jumlah' => 2],
            ],
            'metode_pembayaran' => 'qris',
            'nama_pelanggan'    => 'Pelanggan QRIS Lapak',
        ];

        $response = $this->actingAs($admin)
            ->postJson('/api/admin/pos/transactions', $payload);

        $response->assertCreated();
        $response->assertJson([
            'success' => true,
        ]);

        $order = Order::where('nama_pelanggan', 'Pelanggan QRIS Lapak')->first();
        $this->assertNotNull($order);
        $this->assertEquals(24000, $order->total);

        $this->assertDatabaseHas('payments', [
            'order_id'          => $order->id,
            'metode_pembayaran' => 'qris',
            'status'            => 'berhasil',
            'jumlah'            => 24000,
        ]);

        $this->assertEquals(8, $product->fresh()->stok);
    }

    public function test_pos_transaction_rejects_insufficient_stock(): void
    {
        $admin = $this->createAdmin();

        $product = Product::factory()->create([
            'nama'  => 'Titik Sedap',
            'stok'  => 3,
            'harga' => 10000,
            'aktif' => true,
        ]);

        $payload = [
            'items' => [
                ['product_id' => $product->id, 'jumlah' => 5], // Meminta 5 saat stok hanya 3
            ],
            'metode_pembayaran' => 'tunai',
            'uang_diterima'     => 50000,
        ];

        $response = $this->actingAs($admin)
            ->postJson('/owner/pos/transactions', $payload);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);

        // Pastikan stok tidak berkurang (Rollback)
        $this->assertEquals(3, $product->fresh()->stok);

        // Pastikan tidak ada order yang terbuat
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('sales_history', 0);
    }

    public function test_pos_transaction_rejects_inactive_products(): void
    {
        $admin = $this->createAdmin();

        $product = Product::factory()->create([
            'nama'  => 'Titik Nonaktif',
            'stok'  => 10,
            'harga' => 10000,
            'aktif' => false,
        ]);

        $payload = [
            'items' => [
                ['product_id' => $product->id, 'jumlah' => 1],
            ],
            'metode_pembayaran' => 'tunai',
            'uang_diterima'     => 10000,
        ];

        $response = $this->actingAs($admin)
            ->postJson('/owner/pos/transactions', $payload);

        $response->assertStatus(422);
        $this->assertEquals(10, $product->fresh()->stok);
    }

    public function test_pos_cash_transaction_rejects_insufficient_cash(): void
    {
        $admin = $this->createAdmin();

        $product = Product::factory()->create([
            'nama'  => 'Titik Mantap',
            'stok'  => 10,
            'harga' => 20000,
            'aktif' => true,
        ]);

        $payload = [
            'items' => [
                ['product_id' => $product->id, 'jumlah' => 1],
            ],
            'metode_pembayaran' => 'tunai',
            'uang_diterima'     => 15000, // Kurang dari 20.000
        ];

        $response = $this->actingAs($admin)
            ->postJson('/owner/pos/transactions', $payload);

        $response->assertStatus(422);
        $this->assertDatabaseCount('orders', 0);
        $this->assertEquals(10, $product->fresh()->stok);
    }

    public function test_non_admin_cannot_access_pos_endpoints(): void
    {
        // 1. Guest ditolak
        $response = $this->postJson('/owner/pos/transactions', []);
        $response->assertUnauthorized();

        // 2. Pelanggan ditolak (403)
        $customer = User::factory()->pelanggan()->create();
        $responseCustomer = $this->actingAs($customer)
            ->postJson('/owner/pos/transactions', []);
        $responseCustomer->assertForbidden();

        // 3. Kurir ditolak (403)
        $courier = User::factory()->kurir()->create();
        $responseCourier = $this->actingAs($courier)
            ->postJson('/owner/pos/transactions', []);
        $responseCourier->assertForbidden();
    }

    public function test_pos_transaction_triggers_restock_warning_when_stock_drops_to_five_or_below(): void
    {
        $admin = $this->createAdmin();

        $product = Product::factory()->create([
            'nama'  => 'Titik Segar',
            'harga' => 10000,
            'stok'  => 7, // Awalnya > 5 (tidak memicu restok)
            'aktif' => true,
        ]);

        $payload = [
            'items' => [
                ['product_id' => $product->id, 'jumlah' => 3], // 7 - 3 = 4 (<= 5, pemicu restok)
            ],
            'metode_pembayaran' => 'tunai',
            'uang_diterima'     => 30000,
        ];

        $response = $this->actingAs($admin)
            ->postJson('/owner/pos/transactions', $payload);

        $response->assertCreated();

        // Cek bahwa response JSON menyertakan status stok menipis
        $lowStockProducts = $response->json('data.low_stock_products');
        $this->assertNotEmpty($lowStockProducts);
        $this->assertEquals($product->id, $lowStockProducts[0]['id']);
        $this->assertEquals(4, $lowStockProducts[0]['stok']);
        $this->assertEquals(6, $lowStockProducts[0]['saran_produksi']); // 10 - 4 = 6

        // Cek evaluasi WebNotificationService untuk Owner
        $notificationService = app(WebNotificationService::class);
        $notifications = $notificationService->getNotificationsForUser($admin);

        $hasRestockNotif = collect($notifications)->contains(fn ($n) => $n['tipe'] === 'stok');
        $this->assertTrue($hasRestockNotif, 'Notifikasi web stok menipis harus muncul untuk Owner saat stok <= 5.');
    }

    public function test_sales_history_is_accumulated_accurately_and_idempotently(): void
    {
        $admin = $this->createAdmin();

        $product = Product::factory()->create([
            'nama'  => 'Titik Konsisten',
            'harga' => 10000,
            'stok'  => 50,
            'aktif' => true,
        ]);

        // Transaksi 1: Beli 2 botol
        $this->actingAs($admin)->postJson('/owner/pos/transactions', [
            'items'             => [['product_id' => $product->id, 'jumlah' => 2]],
            'metode_pembayaran' => 'tunai',
            'uang_diterima'     => 20000,
        ])->assertCreated();

        // Transaksi 2: Beli 3 botol lagi pada hari yang sama
        $this->actingAs($admin)->postJson('/owner/pos/transactions', [
            'items'             => [['product_id' => $product->id, 'jumlah' => 3]],
            'metode_pembayaran' => 'tunai',
            'uang_diterima'     => 30000,
        ])->assertCreated();

        // Harus ada tepat 1 baris di sales_history untuk product_id dan tanggal hari ini
        $records = SalesHistory::where('product_id', $product->id)
            ->whereDate('tanggal', today())
            ->where('sumber', 'transaksi')
            ->get();

        $this->assertCount(1, $records, 'Baris sales_history harus unik per produk, tanggal, dan sumber.');
        $this->assertEquals(5, $records->first()->jumlah_terjual, 'Jumlah terjual harus diakumulasikan (2 + 3 = 5).');
    }
}
