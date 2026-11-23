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

class MidtransPaymentServiceTest extends TestCase
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

    public function test_customer_can_view_own_digital_receipt(): void
    {
        $order = $this->createOrderForCustomer($this->customer, 2, 5000);

        $response = $this->actingAs($this->customer)
            ->get(route('orders.nota', $order->kode));

        $response->assertStatus(200);
        $response->assertSee($order->kode);
        $response->assertSee('Nota Digital');
        $response->assertSee(number_format($order->total, 0, ',', '.'));
    }

    public function test_customer_can_fetch_digital_receipt_via_api(): void
    {
        $order = $this->createOrderForCustomer($this->customer, 1, 0);

        $response = $this->actingAs($this->customer)
            ->getJson(route('api.orders.nota', $order->kode));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'order' => [
                        'kode' => $order->kode,
                    ],
                ],
            ]);
    }

    public function test_unauthorized_customer_cannot_view_another_customer_receipt(): void
    {
        $order = $this->createOrderForCustomer($this->customer, 1, 0);

        // Pelanggan lain mencoba mengakses nota
        $response = $this->actingAs($this->otherCustomer)
            ->get(route('orders.nota', $order->kode));

        $response->assertStatus(403);

        // Pelanggan lain via API
        $apiResponse = $this->actingAs($this->otherCustomer)
            ->getJson(route('api.orders.nota', $order->kode));

        $apiResponse->assertStatus(403);
    }

    public function test_admin_can_view_customer_receipt(): void
    {
        $admin = User::factory()->admin()->create();
        $order = $this->createOrderForCustomer($this->customer, 2, 0);

        $response = $this->actingAs($admin)
            ->get(route('orders.nota', $order->kode));

        $response->assertStatus(200);
        $response->assertSee($order->kode);
    }

    public function test_pickup_order_digital_receipt_displays_pickup_deadline(): void
    {
        $order = $this->createOrderForCustomer($this->customer, 1, 0);
        $order->update([
            'metode_pemenuhan' => 'ambil_sendiri',
            'status' => 'diproses',
            'batas_pengambilan' => now()->addHours(24),
        ]);

        $response = $this->actingAs($this->customer)
            ->get(route('orders.nota', $order->kode));

        $response->assertStatus(200);
        $response->assertSee('Ambil Sendiri di Lapak');
        $response->assertSee('Batas Waktu Pengambilan');
        $response->assertSee('Bebas Ongkir');
    }

    // =========================================================================
    // PPL 40 (Jira PPL-44) - QUALITY ENGINEERING (QE) UJI ULANG PEMBAYARAN MIDTRANS & POS VALIDATION
    // =========================================================================

    /**
     * PPL 40 - Skenario 1: Webhook capture dengan fraud_status accept memperbarui pesanan ke diproses
     */
    public function test_webhook_with_capture_and_accept_fraud_status_updates_order_to_diproses(): void
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
            'transaction_status' => 'capture',
            'fraud_status' => 'accept',
            'transaction_id' => 'MID-CAPTURE-12345',
        ];

        $response = $this->postJson(route('api.payments.webhook'), $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'order_status' => 'diproses',
                'payment_status' => 'berhasil',
            ]);

        $order->refresh();
        $this->assertEquals('diproses', $order->status);
        $this->assertEquals('berhasil', $order->payment->status);
    }

    /**
     * PPL 40 - Skenario 2: Webhook capture dengan fraud_status challenge mempertahankan status pending
     */
    public function test_webhook_with_capture_and_challenge_fraud_status_keeps_order_pending(): void
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
            'transaction_status' => 'capture',
            'fraud_status' => 'challenge',
            'transaction_id' => 'MID-CHALLENGE-12345',
        ];

        $response = $this->postJson(route('api.payments.webhook'), $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'order_status' => 'menunggu_pembayaran',
                'payment_status' => 'pending',
            ]);

        $order->refresh();
        $this->assertEquals('menunggu_pembayaran', $order->status);
        $this->assertEquals('pending', $order->payment->status);
    }

    /**
     * PPL 40 - Skenario 3: Webhook status pending mempertahankan status menunggu_pembayaran
     */
    public function test_webhook_with_pending_status_keeps_order_and_payment_in_pending(): void
    {
        $order = $this->createOrderForCustomer($this->customer, 1, 0);
        $payment = $order->payment;
        $orderId = $order->kode . '-' . $payment->id;
        $grossAmount = (string) $order->total;
        $statusCode = '201';
        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'pending',
            'transaction_id' => 'MID-PENDING-12345',
        ];

        $response = $this->postJson(route('api.payments.webhook'), $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'order_status' => 'menunggu_pembayaran',
                'payment_status' => 'pending',
            ]);

        $order->refresh();
        $this->assertEquals('menunggu_pembayaran', $order->status);
        $this->assertEquals('pending', $order->payment->status);
    }

    /**
     * PPL 40 - Skenario 4: Webhook status failure membatalkan pesanan dan mengembalikan stok
     */
    public function test_webhook_with_failure_status_cancels_order_and_restores_stock(): void
    {
        $initialStock = $this->product->stok;
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
            'transaction_status' => 'failure',
        ];

        $response = $this->postJson(route('api.payments.webhook'), $payload);

        $response->assertStatus(200);

        $order->refresh();
        $this->product->refresh();

        $this->assertEquals('dibatalkan', $order->status);
        $this->assertEquals('gagal', $order->payment->status);
        $this->assertTrue((bool) $order->stok_dikembalikan);
        $this->assertEquals($initialStock, $this->product->stok);
    }

    /**
     * PPL 40 - Skenario 5: Webhook menolak payload yang tidak lengkap (missing fields)
     */
    public function test_webhook_rejects_missing_payload_fields(): void
    {
        $order = $this->createOrderForCustomer($this->customer);

        // Tanpa signature_key
        $response = $this->postJson(route('api.payments.webhook'), [
            'order_id' => $order->kode,
            'status_code' => '200',
            'gross_amount' => (string) $order->total,
            'transaction_status' => 'settlement',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Payload notifikasi tidak lengkap.',
            ]);

        // Tanpa order_id
        $response2 = $this->postJson(route('api.payments.webhook'), [
            'status_code' => '200',
            'gross_amount' => (string) $order->total,
            'signature_key' => 'SOME_SIG',
            'transaction_status' => 'settlement',
        ]);

        $response2->assertStatus(400);
    }

    /**
     * PPL 40 - Skenario 6: Webhook menolak referensi pesanan yang tidak ditemukan
     */
    public function test_webhook_rejects_non_existent_order(): void
    {
        $fakeOrderId = 'KTT-99991231-9999-1';
        $statusCode = '200';
        $grossAmount = '50000';
        $signature = hash('sha512', $fakeOrderId . $statusCode . $grossAmount . $this->serverKey);

        $payload = [
            'order_id' => $fakeOrderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
        ];

        $response = $this->postJson(route('api.payments.webhook'), $payload);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Pesanan dengan referensi terkait tidak ditemukan.',
            ]);
    }

    /**
     * PPL 40 - Skenario 7: Validasi isolasi POS (Pesanan POS kasir offline ditolak via Midtrans webhook)
     */
    public function test_webhook_rejects_pos_order(): void
    {
        $posOrder = Order::create([
            'kode' => 'POS-' . date('Ymd') . '-0001',
            'sumber_transaksi' => 'pos',
            'metode_pemenuhan' => null,
            'user_id' => null,
            'status' => 'selesai',
            'subtotal' => 20000,
            'ongkir' => 0,
            'total' => 20000,
            'nama_pelanggan' => 'Pelanggan POS Lapak',
            'stok_dikembalikan' => false,
        ]);

        $orderId = $posOrder->kode . '-1';
        $statusCode = '200';
        $grossAmount = '20000';
        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
        ];

        $response = $this->postJson(route('api.payments.webhook'), $payload);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Pesanan POS offline tidak menggunakan integrasi webhook online.',
            ]);

        $posOrder->refresh();
        $this->assertEquals('selesai', $posOrder->status);
    }

    /**
     * PPL 40 - Skenario 8: Notifikasi terlambat setelah pesanan dibatalkan (FR-6.9)
     * Ditandai untuk penanganan Owner tanpa merusak status pembatalan dan stok.
     */
    public function test_late_settlement_notification_after_cancellation_marks_for_owner_review_without_changing_cancelled_status(): void
    {
        $order = $this->createOrderForCustomer($this->customer, 2, 0);
        $order->update([
            'status' => 'dibatalkan',
            'stok_dikembalikan' => true,
        ]);

        $payment = $order->payment;
        $orderId = $order->kode . '-' . $payment->id;
        $statusCode = '200';
        $grossAmount = (string) $order->total;
        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'transaction_id' => 'MID-LATE-99999',
        ];

        $response = $this->postJson(route('api.payments.webhook'), $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Pembayaran diterima setelah pesanan kedaluwarsa. Ditandai untuk penanganan Owner.',
                'order_status' => 'dibatalkan',
            ]);

        $order->refresh();
        $this->assertEquals('dibatalkan', $order->status);
        $this->assertEquals('MID-LATE-99999', $order->payment->referensi_provider);
    }

    /**
     * PPL 40 - Skenario 9: Webhook duplikat tidak mereset status pesanan yang sudah dikirim atau selesai
     */
    public function test_duplicate_settlement_on_shipped_or_completed_order_does_not_revert_status(): void
    {
        $order = $this->createOrderForCustomer($this->customer, 1, 0);
        $order->update(['status' => 'dikirim']);
        $order->payment->update(['status' => 'berhasil']);

        $payment = $order->payment;
        $orderId = $order->kode . '-' . $payment->id;
        $statusCode = '200';
        $grossAmount = (string) $order->total;
        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'transaction_id' => 'MID-DUP-11111',
        ];

        $response = $this->postJson(route('api.payments.webhook'), $payload);

        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals('dikirim', $order->status); // Status tetap dikirim, tidak mundur jadi diproses
    }

    /**
     * PPL 40 - Skenario 10: Notifikasi expire ganda tidak mengembalikan stok berulang kali
     */
    public function test_duplicate_expire_notification_does_not_restore_stock_twice(): void
    {
        $initialStock = $this->product->stok;
        $this->product->update(['stok' => $initialStock - 2]); // 8

        $order = $this->createOrderForCustomer($this->customer, 2);
        $payment = $order->payment;
        $orderId = $order->kode . '-' . $payment->id;
        $statusCode = '200';
        $grossAmount = (string) $order->total;
        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'expire',
        ];

        // Webhook expire 1
        $this->postJson(route('api.payments.webhook'), $payload)->assertStatus(200);

        $this->product->refresh();
        $this->assertEquals($initialStock, $this->product->stok); // Pulih jadi 10

        // Webhook expire 2 (duplikat)
        $this->postJson(route('api.payments.webhook'), $payload)->assertStatus(200);

        $this->product->refresh();
        // Stok tetap 10, tidak bertambah jadi 12
        $this->assertEquals($initialStock, $this->product->stok);
    }

    /**
     * PPL 40 - Skenario 11: Polling payment-status otomatis membatalkan pesanan yang lewat batas waktu
     */
    public function test_polling_payment_status_api_auto_expires_overdue_order(): void
    {
        $initialStock = $this->product->stok;
        $this->product->update(['stok' => $initialStock - 1]);

        $order = $this->createOrderForCustomer($this->customer, 1);
        $order->payment->update([
            'kedaluwarsa_pada' => now()->subMinutes(12),
        ]);

        $response = $this->actingAs($this->customer)
            ->getJson("/api/orders/{$order->kode}/payment-status");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'order_status' => 'dibatalkan',
                    'is_expired' => true,
                    'is_paid' => false,
                ],
            ]);

        $order->refresh();
        $this->product->refresh();

        $this->assertEquals('dibatalkan', $order->status);
        $this->assertEquals($initialStock, $this->product->stok);
    }

    /**
     * PPL 40 - Skenario 12: Simulate payment menolak pesanan yang sudah dibayar atau dibatalkan
     */
    public function test_simulate_payment_rejects_already_paid_or_cancelled_order(): void
    {
        $order = $this->createOrderForCustomer($this->customer, 1);
        $order->update(['status' => 'diproses']);

        $response = $this->actingAs($this->customer)
            ->postJson(route('api.orders.simulate-payment', ['identifier' => $order->kode]));

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * PPL 40 - Skenario 13: Halaman pembayaran menolak akses ke pesanan kasir POS offline
     */
    public function test_payment_page_rejects_pos_orders(): void
    {
        $customer = User::factory()->create(['role' => 'pelanggan']);

        $posOrder = Order::create([
            'kode' => 'POS-' . date('Ymd') . '-0099',
            'sumber_transaksi' => 'pos',
            'metode_pemenuhan' => null,
            'user_id' => $customer->id,
            'status' => 'selesai',
            'subtotal' => 15000,
            'ongkir' => 0,
            'total' => 15000,
            'nama_pelanggan' => 'Pelanggan POS',
            'stok_dikembalikan' => false,
        ]);

        $response = $this->actingAs($customer)
            ->get(route('payment', ['kode' => $posOrder->kode]));

        $response->assertRedirect(route('orders'));
        $response->assertSessionHas('error', 'Pesanan kasir offline POS tidak menggunakan halaman pembayaran online.');
    }

    /**
     * PPL-34 (K1): Endpoint simulate-payment menolak akses unauthorized (guest atau user lain).
     */
    public function test_simulate_payment_rejects_unauthorized_user(): void
    {
        $order = $this->createOrderForCustomer($this->customer, 1, 0);
        $otherCustomer = User::factory()->create(['role' => 'pelanggan']);

        // 1. Guest ditolak 401
        $guestResponse = $this->postJson(route('api.orders.simulate-payment', ['identifier' => $order->kode]));
        $guestResponse->assertStatus(401);

        // 2. Pelanggan lain ditolak 403 oleh kontrol otorisasi controller
        $otherResponse = $this->actingAs($otherCustomer)
            ->postJson(route('api.orders.simulate-payment', ['identifier' => $order->kode]));
        $otherResponse->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Akses ditolak. Anda tidak berhak melakukan simulasi pembayaran pesanan ini.',
            ]);

        // 3. Pemilik pesanan sah boleh melakukan simulasi pembayaran
        $ownerResponse = $this->actingAs($this->customer)
            ->postJson(route('api.orders.simulate-payment', ['identifier' => $order->kode]));
        $ownerResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * PPL-34 (K2): Webhook out-of-order expire/deny tidak mendowngrade pesanan yang sudah berhasil/diproses.
     */
    public function test_webhook_out_of_order_expire_does_not_downgrade_already_paid_order(): void
    {
        $order = $this->createOrderForCustomer($this->customer, 1, 0);
        $serverKey = config('services.midtrans.server_key', '');
        $midtransOrderId = $order->kode . '-1';

        // 1. Settle order first
        $settlePayload = [
            'order_id' => $midtransOrderId,
            'status_code' => '200',
            'gross_amount' => (string) $order->total,
            'signature_key' => hash('sha512', $midtransOrderId . '200' . (string) $order->total . $serverKey),
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'payment_type' => 'qris',
        ];
        $this->postJson(route('api.payments.webhook'), $settlePayload)->assertStatus(200);

        $order->refresh();
        $this->assertEquals('diproses', $order->status);
        $this->assertEquals('berhasil', $order->payment->status);

        // 2. Delayed webhook expire tiba
        $expirePayload = [
            'order_id' => $midtransOrderId,
            'status_code' => '200',
            'gross_amount' => (string) $order->total,
            'signature_key' => hash('sha512', $midtransOrderId . '200' . (string) $order->total . $serverKey),
            'transaction_status' => 'expire',
            'payment_type' => 'qris',
        ];
        $expireResponse = $this->postJson(route('api.payments.webhook'), $expirePayload);
        $expireResponse->assertStatus(200);

        $order->refresh();
        // Status HARUS tetap 'diproses' dan 'berhasil', TIDAK boleh ter-downgrade menjadi 'kedaluwarsa'/'dibatalkan'
        $this->assertEquals('diproses', $order->status);
        $this->assertEquals('berhasil', $order->payment->status);
    }

    /**
     * PPL-34 (K2): Pembayaran terlambat pada pesanan yang sudah dibatalkan mencatat audit history untuk owner.
     */
    public function test_late_settlement_on_cancelled_order_adds_history_for_owner_review(): void
    {
        $order = $this->createOrderForCustomer($this->customer, 1, 0);
        $order->update(['status' => 'dibatalkan']);
        $serverKey = config('services.midtrans.server_key', '');
        $midtransOrderId = $order->kode . '-1';

        $settlePayload = [
            'order_id' => $midtransOrderId,
            'status_code' => '200',
            'gross_amount' => (string) $order->total,
            'signature_key' => hash('sha512', $midtransOrderId . '200' . (string) $order->total . $serverKey),
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'payment_type' => 'qris',
        ];
        $response = $this->postJson(route('api.payments.webhook'), $settlePayload);
        $response->assertStatus(200);

        $this->assertDatabaseHas('order_status_history', [
            'order_id' => $order->id,
            'status_baru' => 'dibatalkan',
            'keterangan' => '[perhatian_owner] Pembayaran berhasil diterima setelah pesanan kedaluwarsa/dibatalkan.',
        ]);
    }
}
