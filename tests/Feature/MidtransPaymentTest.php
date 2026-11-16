<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SalesHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class MidtransPaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $otherCustomer;
    private Product $product;
    private string $serverKey = 'SB-Mid-server-test-key-12345';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.midtrans.server_key', $this->serverKey);
        Config::set('services.midtrans.client_key', 'SB-Mid-client-test-key-12345');
        Config::set('services.midtrans.is_production', false);

        $this->customer = User::factory()->create([
            'role' => 'pelanggan',
            'name' => 'Dimas Suryo',
            'telepon' => '081234567890',
        ]);

        $this->otherCustomer = User::factory()->create([
            'role' => 'pelanggan',
            'name' => 'Budi Santoso',
            'telepon' => '081298765432',
        ]);

        $this->product = Product::create([
            'nama' => 'Titik Temu Kopi Susu',
            'rasa' => 'Aren Creamy',
            'harga' => 12000,
            'stok' => 10,
            'komposisi' => 'Kopi Espresso, Susu UHT, Gula Aren Murni',
            'deskripsi' => 'Kopi susu gula aren 250ml',
            'aktif' => true,
        ]);
    }

    private function createOrderForCustomer(User $user, int $qty = 2, int $ongkir = 0): Order
    {
        $subtotal = $this->product->harga * $qty;
        $total = $subtotal + $ongkir;

        $order = Order::create([
            'kode' => Order::generateOrderCode(),
            'sumber_transaksi' => 'online',
            'metode_pemenuhan' => 'pengiriman',
            'user_id' => $user->id,
            'status' => 'menunggu_pembayaran',
            'subtotal' => $subtotal,
            'ongkir' => $ongkir,
            'total' => $total,
            'nama_penerima' => $user->name,
            'telepon' => $user->telepon,
            'alamat' => 'Jl. Kuwung No. 12, Mergelo, Kota Mojokerto',
            'lat' => -7.4725,
            'lng' => 112.4385,
            'stok_dikembalikan' => false,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'nama_produk' => $this->product->nama,
            'rasa' => $this->product->rasa,
            'jumlah' => $qty,
            'harga_satuan' => $this->product->harga,
            'subtotal' => $subtotal,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'metode_pembayaran' => 'midtrans',
            'status' => 'pending',
            'jumlah' => $total,
            'kedaluwarsa_pada' => now()->addMinutes(10),
        ]);

        return $order;
    }

    public function test_customer_can_view_payment_page_with_active_qris(): void
    {
        $order = $this->createOrderForCustomer($this->customer, 2, 2000);

        $response = $this->actingAs($this->customer)
            ->get(route('payment', ['kode' => $order->kode]));

        $response->assertStatus(200);
        $response->assertSee($order->kode);
        $response->assertSee('26.000'); // total 24000 + 2000
        $response->assertSee('QRIS');
        $response->assertSee('Bayar Sebelum Habis');
    }

    public function test_guest_accessing_payment_page_is_redirected_to_login(): void
    {
        $order = $this->createOrderForCustomer($this->customer);

        $response = $this->get(route('payment', ['kode' => $order->kode]));

        $response->assertRedirect(route('login'));
    }

    public function test_other_customer_cannot_access_order_payment(): void
    {
        $order = $this->createOrderForCustomer($this->customer);

        $response = $this->actingAs($this->otherCustomer)
            ->get(route('payment', ['kode' => $order->kode]));

        $response->assertStatus(403);
    }

    public function test_already_paid_order_redirects_to_tracking_detail(): void
    {
        $order = $this->createOrderForCustomer($this->customer);
        $order->update(['status' => 'diproses']);

        $response = $this->actingAs($this->customer)
            ->get(route('payment', ['kode' => $order->kode]));

        $response->assertRedirect(route('tracking.detail', $order->kode));
    }

    public function test_polling_payment_status_api(): void
    {
        $order = $this->createOrderForCustomer($this->customer);

        $response = $this->actingAs($this->customer)
            ->getJson("/api/orders/{$order->kode}/payment-status");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'order_id' => $order->id,
                    'kode_pesanan' => $order->kode,
                    'order_status' => 'menunggu_pembayaran',
                    'payment_status' => 'pending',
                    'is_paid' => false,
                    'is_expired' => false,
                ],
            ]);
    }

    public function test_webhook_with_valid_settlement_updates_order_to_diproses_and_records_sales(): void
    {
        $order = $this->createOrderForCustomer($this->customer, 2, 0);
        $payment = $order->payment;
        $orderId = $order->kode . '-' . $payment->id;
        $grossAmount = (string) $order->total;
        $statusCode = '200';

        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'transaction_id' => 'MID-TRANS-12345',
            'fraud_status' => 'accept',
        ];

        $response = $this->postJson(route('api.payments.webhook'), $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'order_status' => 'diproses',
                'payment_status' => 'berhasil',
            ]);

        $order->refresh();
        $payment->refresh();

        $this->assertEquals('diproses', $order->status);
        $this->assertEquals('berhasil', $payment->status);
        $this->assertNotNull($payment->waktu_verifikasi);
        $this->assertEquals('MID-TRANS-12345', $payment->referensi_provider);

        // Memastikan riwayat status tercatat
        $this->assertDatabaseHas('order_status_history', [
            'order_id' => $order->id,
            'status_baru' => 'diproses',
            'keterangan' => 'Pembayaran QRIS Midtrans berhasil diverifikasi.',
        ]);

        // Memastikan histori penjualan tercatat
        $this->assertDatabaseHas('sales_history', [
            'product_id' => $this->product->id,
            'jumlah_terjual' => 2,
            'sumber' => 'transaksi',
        ]);
    }

    public function test_webhook_is_idempotent_on_duplicate_notification(): void
    {
        $order = $this->createOrderForCustomer($this->customer, 2, 0);
        $payment = $order->payment;
        $orderId = $order->kode . '-' . $payment->id;
        $grossAmount = (string) $order->total;
        $statusCode = '200';
        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'transaction_id' => 'MID-TRANS-12345',
            'fraud_status' => 'accept',
        ];

        // Panggilan pertama
        $this->postJson(route('api.payments.webhook'), $payload)->assertStatus(200);

        $salesCountFirst = SalesHistory::where('product_id', $this->product->id)->count();

        // Panggilan kedua (duplikat notifikasi)
        $responseSecond = $this->postJson(route('api.payments.webhook'), $payload);
        $responseSecond->assertStatus(200);

        $salesCountSecond = SalesHistory::where('product_id', $this->product->id)->count();

        // Idempoten: Tidak menggandakan baris penjualan
        $this->assertEquals($salesCountFirst, $salesCountSecond);
    }

    public function test_webhook_with_invalid_signature_is_rejected(): void
    {
        $order = $this->createOrderForCustomer($this->customer);
        $payment = $order->payment;
        $orderId = $order->kode . '-' . $payment->id;

        $payload = [
            'order_id' => $orderId,
            'status_code' => '200',
            'gross_amount' => (string) $order->total,
            'signature_key' => 'INVALID_SIGNATURE_KEY_ABCDEF',
            'transaction_status' => 'settlement',
        ];

        $response = $this->postJson(route('api.payments.webhook'), $payload);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Signature key tidak valid.',
            ]);

        $order->refresh();
        $this->assertEquals('menunggu_pembayaran', $order->status);
    }

    public function test_webhook_with_mismatched_amount_is_rejected(): void
    {
        $order = $this->createOrderForCustomer($this->customer);
        $payment = $order->payment;
        $orderId = $order->kode . '-' . $payment->id;
        $tamperedAmount = (string) ($order->total + 5000);
        $statusCode = '200';
        $signature = hash('sha512', $orderId . $statusCode . $tamperedAmount . $this->serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $tamperedAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
        ];

        $response = $this->postJson(route('api.payments.webhook'), $payload);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);

        $order->refresh();
        $this->assertEquals('menunggu_pembayaran', $order->status);
    }

    public function test_webhook_expire_cancels_order_and_restores_stock(): void
    {
        $initialStock = $this->product->stok; // 10
        // Simulasikan checkout yang telah memotong stok 2 botol
        $this->product->update(['stok' => $initialStock - 2]);

        $order = $this->createOrderForCustomer($this->customer, 2);
        $payment = $order->payment;
        $orderId = $order->kode . '-' . $payment->id;
        $grossAmount = (string) $order->total;
        $statusCode = '200';
        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'expire',
        ];

        $response = $this->postJson(route('api.payments.webhook'), $payload);

        $response->assertStatus(200);

        $order->refresh();
        $payment->refresh();
        $this->product->refresh();

        $this->assertEquals('dibatalkan', $order->status);
        $this->assertEquals('kedaluwarsa', $payment->status);
        $this->assertTrue((bool) $order->stok_dikembalikan);
        $this->assertEquals($initialStock, $this->product->stok); // Stok kembali jadi 10
    }

    public function test_webhook_deny_or_cancel_fails_order_and_restores_stock(): void
    {
        $initialStock = $this->product->stok;
        $this->product->update(['stok' => $initialStock - 3]);

        $order = $this->createOrderForCustomer($this->customer, 3);
        $payment = $order->payment;
        $orderId = $order->kode . '-' . $payment->id;
        $grossAmount = (string) $order->total;
        $statusCode = '200';
        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'deny',
        ];

        $response = $this->postJson(route('api.payments.webhook'), $payload);

        $response->assertStatus(200);

        $order->refresh();
        $payment->refresh();
        $this->product->refresh();

        $this->assertEquals('dibatalkan', $order->status);
        $this->assertEquals('gagal', $payment->status);
        $this->assertTrue((bool) $order->stok_dikembalikan);
        $this->assertEquals($initialStock, $this->product->stok);
    }

    public function test_orders_expire_artisan_command_cancels_overdue_orders(): void
    {
        $initialStock = $this->product->stok;
        $this->product->update(['stok' => $initialStock - 1]);

        $order = $this->createOrderForCustomer($this->customer, 1);
        $payment = $order->payment;

        // Set kedaluwarsa 15 menit yang lalu
        $payment->update([
            'kedaluwarsa_pada' => now()->subMinutes(15),
        ]);

        Artisan::call('orders:expire');

        $order->refresh();
        $payment->refresh();
        $this->product->refresh();

        $this->assertEquals('dibatalkan', $order->status);
        $this->assertEquals('kedaluwarsa', $payment->status);
        $this->assertTrue((bool) $order->stok_dikembalikan);
        $this->assertEquals($initialStock, $this->product->stok);
    }

    public function test_multiple_paid_orders_on_same_day_accumulates_sales_history_without_duplicate_error(): void
    {
        // Pesanan 1: 2 botol
        $order1 = $this->createOrderForCustomer($this->customer, 2, 0);
        $payment1 = $order1->payment;
        $orderId1 = $order1->kode . '-' . $payment1->id;
        $sig1 = hash('sha512', $orderId1 . '200' . (string) $order1->total . $this->serverKey);

        $this->postJson(route('api.payments.webhook'), [
            'order_id' => $orderId1,
            'status_code' => '200',
            'gross_amount' => (string) $order1->total,
            'signature_key' => $sig1,
            'transaction_status' => 'settlement',
        ])->assertStatus(200);

        // Pesanan 2: 3 botol untuk produk yang sama di hari yang sama
        $order2 = $this->createOrderForCustomer($this->customer, 3, 0);
        $payment2 = $order2->payment;
        $orderId2 = $order2->kode . '-' . $payment2->id;
        $sig2 = hash('sha512', $orderId2 . '200' . (string) $order2->total . $this->serverKey);

        $this->postJson(route('api.payments.webhook'), [
            'order_id' => $orderId2,
            'status_code' => '200',
            'gross_amount' => (string) $order2->total,
            'signature_key' => $sig2,
            'transaction_status' => 'settlement',
        ])->assertStatus(200);

        // Jumlah terjual harus terakumulasi menjadi 2 + 3 = 5 tanpa duplicate key error
        $this->assertDatabaseHas('sales_history', [
            'product_id' => $this->product->id,
            'jumlah_terjual' => 5,
            'sumber' => 'transaksi',
        ]);
    }

    public function test_simulate_payment_endpoint_marks_order_as_paid_and_redirects(): void
    {
        $order = $this->createOrderForCustomer($this->customer, 1, 0);

        $response = $this->actingAs($this->customer)
            ->postJson(route('api.orders.simulate-payment', ['identifier' => $order->kode]));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $order->refresh();
        $this->assertEquals('diproses', $order->status);
        $this->assertEquals('berhasil', $order->payment->status);
    }
}
