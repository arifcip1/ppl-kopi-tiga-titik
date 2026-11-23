<?php

namespace Tests\Feature\E2E;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SalesHistory;
use App\Models\Setting;
use App\Models\ShippingLocation;
use App\Models\User;
use App\Services\PosService;
use App\Services\SalesReportService;
use App\Services\WebNotificationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PPL-34: Pengujian Integrasi End-to-End (E2E) Mengunci 4 Alur Utama Lintas 14 Modul.
 *
 * 1. Alur A: Online - Pengiriman (Katalog -> Keranjang -> Checkout -> QRIS Midtrans -> Antrean Owner -> Kurir -> Riwayat & Notifikasi)
 * 2. Alur B: Online - Ambil Sendiri (Katalog -> Keranjang -> Checkout Ambil Sendiri -> QRIS -> Batas 24 Jam -> Selesai di Lapak)
 * 3. Alur C: Offline - POS Kasir (Transaksi POS -> Stok Atomik -> Peringatan Restok <= 5 -> Sales History & Laporan Excel)
 * 4. Alur D: Pengelolaan Lokasi & Operasional (Perubahan Lokasi / Jam Operasional -> Proteksi Checkout)
 */
class Ppl34EndToEndFourFlowsTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $courier;
    protected User $customer;
    protected Product $product;
    protected ShippingLocation $lapak;
    protected string $serverKey = 'SB-Mid-server-ppl34-e2e-key';

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2026, 11, 23, 16, 0, 0, 'Asia/Jakarta'));

        config([
            'services.midtrans.server_key'    => $this->serverKey,
            'services.midtrans.is_production' => false,
            'services.openrouteservice.simulation' => true,
        ]);

        $this->lapak = ShippingLocation::create([
            'nama_lokasi' => 'Lapak Jl. Kuwung',
            'alamat'      => 'Jl. Kuwung, Mergelo, Magersari, Mojokerto',
            'latitude'    => -7.4726000,
            'longitude'   => 112.4385000,
            'aktif'       => true,
        ]);

        Setting::updateOrCreate(['key' => 'lokasi_pengiriman_aktif_id'], ['value' => (string) $this->lapak->id]);
        Setting::updateOrCreate(['key' => 'jam_buka'], ['value' => '15:00']);
        Setting::updateOrCreate(['key' => 'jam_tutup'], ['value' => '21:00']);
        Setting::updateOrCreate(['key' => 'status_lapak'], ['value' => 'buka']);
        Setting::updateOrCreate(['key' => 'batas_stok_minimum'], ['value' => '5']);
        Setting::updateOrCreate(['key' => 'stok_harian_standar'], ['value' => '10']);

        $this->owner = User::factory()->create([
            'name'  => 'Owner Farrel',
            'email' => 'owner@kopitigatitik.test',
            'role'  => 'admin',
        ]);

        $this->courier = User::factory()->create([
            'name'  => 'Kurir Budi',
            'email' => 'kurir@kopitigatitik.test',
            'role'  => 'kurir',
        ]);

        $this->customer = User::factory()->create([
            'name'  => 'Pelanggan Siti',
            'email' => 'siti@customer.test',
            'role'  => 'pelanggan',
        ]);

        $this->product = Product::create([
            'nama'      => 'Titik Manis',
            'rasa'      => 'Kopi Susu Aren Spesial',
            'komposisi' => 'Espresso, susu segar, gula aren murni',
            'harga'     => 15000,
            'stok'      => 10,
            'aktif'     => true,
            'deskripsi' => 'Kopi botol 250ml siap minum',
        ]);
    }

    /**
     * ALUR A: Online Pengiriman Penuh Lintas Modul
     */
    public function test_alur_a_online_pengiriman_end_to_end(): void
    {
        // 1. Katalog: produk tersedia & tombol aktif
        $catalogRes = $this->get(route('katalog.index'));
        $catalogRes->assertStatus(200)->assertSee('Titik Manis');

        // 2. Keranjang: tambah 2 botol
        $this->actingAs($this->customer)->postJson(route('api.cart.store'), [
            'product_id' => $this->product->id,
            'jumlah'     => 2,
        ])->assertStatus(200);

        $this->assertDatabaseHas('cart_items', [
            'user_id'    => $this->customer->id,
            'product_id' => $this->product->id,
            'jumlah'     => 2,
        ]);

        // 3. Checkout: Pengiriman dalam Mojokerto
        $checkoutData = [
            'metode_pemenuhan' => 'pengiriman',
            'nama_penerima'    => 'Siti Mojokerto',
            'telepon'          => '081234567890',
            'alamat'           => 'Jl. Pahlawan No. 45, Magersari, Mojokerto',
            'lat'              => -7.4680000,
            'lng'              => 112.4350000,
        ];

        $checkoutRes = $this->actingAs($this->customer)->post(route('checkout.process'), $checkoutData);
        $checkoutRes->assertRedirect(route('payment'));

        $order = Order::where('user_id', $this->customer->id)->latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('menunggu_pembayaran', $order->status);
        $this->assertEquals(30000, $order->subtotal);

        // Stok berkurang atomik saat reservasi checkout
        $this->product->refresh();
        $this->assertEquals(8, $this->product->stok);

        // 4. Pembayaran: Webhook Midtrans settlement
        $midtransOrderId = $order->kode . '-1';
        $grossAmount = (string) $order->total;
        $signature = hash('sha512', $midtransOrderId . '200' . $grossAmount . $this->serverKey);

        $webhookPayload = [
            'order_id'           => $midtransOrderId,
            'status_code'        => '200',
            'gross_amount'       => $grossAmount,
            'signature_key'      => $signature,
            'transaction_status' => 'settlement',
            'fraud_status'       => 'accept',
            'payment_type'       => 'qris',
        ];

        $this->postJson(route('api.payments.webhook'), $webhookPayload)->assertStatus(200);

        $order->refresh();
        $this->assertEquals('diproses', $order->status);
        $this->assertEquals('berhasil', $order->payment->status);

        // Sales history tercatat akumulatif
        $this->assertDatabaseHas('sales_history', [
            'product_id'     => $this->product->id,
            'jumlah_terjual' => 2,
            'sumber'         => 'transaksi',
        ]);

        // 5. Antrean Owner: Owner melihat pesanan diproses dan menugaskan kurir
        $this->actingAs($this->owner)->get(route('owner.dashboard', ['status' => 'diproses']))
            ->assertStatus(200)
            ->assertSee($order->kode);

        $assignRes = $this->actingAs($this->owner)->post(route('owner.orders.assign-courier', $order->id), [
            'courier_id' => $this->courier->id,
        ]);
        $assignRes->assertRedirect();

        $order->refresh();
        $this->assertEquals('dikirim', $order->status);
        $this->assertEquals($this->courier->id, $order->courier_id);

        // 6. Kurir menyelesaikan pengantaran
        $completeRes = $this->actingAs($this->courier)->post(route('kurir.orders.complete', $order->id));
        $completeRes->assertRedirect();

        $order->refresh();
        $this->assertEquals('selesai', $order->status);

        // 7. Notifikasi & Laporan Riwayat Penjualan
        $notifService = app(WebNotificationService::class);
        $custNotifs = $notifService->getNotificationsForUser($this->customer);
        $this->assertNotEmpty($custNotifs);

        $reportService = app(SalesReportService::class);
        $report = $reportService->getReport('harian');
        $this->assertEquals(2, $report['summary']['total_botol']);
        $this->assertEquals(30000, $report['summary']['total_omzet']);
    }

    /**
     * ALUR B: Online Ambil Sendiri (Self-Pickup)
     */
    public function test_alur_b_online_ambil_sendiri_end_to_end(): void
    {
        // 1. Customer isi keranjang
        $this->actingAs($this->customer)->postJson(route('api.cart.store'), [
            'product_id' => $this->product->id,
            'jumlah'     => 1,
        ])->assertStatus(200);

        // 2. Checkout Ambil Sendiri (ongkir 0, alamat opsional)
        $checkoutData = [
            'metode_pemenuhan' => 'ambil_sendiri',
            'nama_penerima'    => 'Siti Ambil Sendiri',
            'telepon'          => '081234567890',
            'catatan'          => 'Ambil jam 5 sore',
        ];

        $res = $this->actingAs($this->customer)->post(route('checkout.process'), $checkoutData);
        $res->assertRedirect(route('payment'));

        $order = Order::where('user_id', $this->customer->id)->latest()->first();
        $this->assertEquals('ambil_sendiri', $order->metode_pemenuhan);
        $this->assertEquals(0, $order->ongkir);
        $this->assertNotNull($order->batas_pengambilan);

        // 3. Simulasi Pelunasan
        $this->actingAs($this->customer)
            ->postJson(route('api.orders.simulate-payment', ['identifier' => $order->kode]))
            ->assertStatus(200);

        $order->refresh();
        $this->assertEquals('diproses', $order->status);
        $this->assertEquals('berhasil', $order->payment->status);
        // Batas pengambilan terbarui 24 jam ke depan dari pelunasan
        $this->assertTrue($order->batas_pengambilan->gt(now()->addHours(23)));

        // 4. Owner menyelesaikan pengambilan pesanan di lapak
        $completePickupRes = $this->actingAs($this->owner)->post(route('owner.orders.complete-pickup', $order->id));
        $completePickupRes->assertRedirect();

        $order->refresh();
        $this->assertEquals('selesai', $order->status);
    }

    /**
     * ALUR C: POS Kasir Lapak + Pengurangan Stok + Peringatan Restok (<= 5) + Laporan Excel
     */
    public function test_alur_c_pos_offline_restock_and_report_end_to_end(): void
    {
        // Set stok awal menjadi 7 botol
        $this->product->update(['stok' => 7]);

        $posService = app(PosService::class);

        // 1. Transaksi POS: Beli 3 botol (stok turun dari 7 menjadi 4, memicu restok <= 5)
        $transaction = $posService->createTransaction([
            'metode_pembayaran' => 'tunai',
            'nama_pelanggan'    => 'Beli Offline Lapak',
            'uang_diterima'     => 50000,
            'items'             => [
                [
                    'product_id' => $this->product->id,
                    'jumlah'     => 3,
                ],
            ],
        ], $this->owner);

        $this->assertNotNull($transaction['order']);
        $this->assertEquals(45000, $transaction['order']['total']);
        $this->assertEquals(5000, $transaction['order']['kembalian']);

        $this->product->refresh();
        $this->assertEquals(4, $this->product->stok);

        // 2. Notifikasi Web Owner memuat peringatan stok menipis
        $notifService = app(WebNotificationService::class);
        $ownerNotifs = $notifService->getNotificationsForUser($this->owner);
        $hasRestockNotif = collect($ownerNotifs)->contains(fn ($n) => $n['tipe'] === 'stok');
        $this->assertTrue($hasRestockNotif, 'Peringatan stok menipis harus muncul di notifikasi owner.');

        // 3. Ekspor Excel Riwayat Penjualan menghasilkan 200 OK
        $exportRes = $this->actingAs($this->owner)->get(route('owner.export.sales', ['periode' => 'harian']));
        $exportRes->assertStatus(200);
        $this->assertTrue(str_contains($exportRes->headers->get('content-type', ''), 'spreadsheet') || str_contains($exportRes->headers->get('content-disposition', ''), 'riwayat-penjualan'));
    }

    /**
     * ALUR D: Pengelolaan Lokasi & Jam Operasional Lapak
     */
    public function test_alur_d_location_and_operating_hours_guards(): void
    {
        // 1. Isi keranjang customer
        $this->actingAs($this->customer)->postJson(route('api.cart.store'), [
            'product_id' => $this->product->id,
            'jumlah'     => 1,
        ])->assertStatus(200);

        // 2. Owner mengubah jam operasional sehingga lapak berstatus TUTUP
        Setting::updateOrCreate(['key' => 'jam_buka'], ['value' => '17:00']);
        Setting::updateOrCreate(['key' => 'jam_tutup'], ['value' => '18:00']);
        // Saat ini waktu pengujian 16:00 WIB, jadi lapak di luar jam buka (TUTUP)

        $checkoutAttempt = $this->actingAs($this->customer)->post(route('checkout.process'), [
            'metode_pemenuhan' => 'ambil_sendiri',
            'nama_penerima'    => 'Siti Gagal',
            'telepon'          => '081234567890',
        ]);

        // Harus diblokir dengan pesan lapak tutup
        $checkoutAttempt->assertSessionHas('error');

        // 3. Lapak dibuka kembali secara manual oleh Owner
        Setting::updateOrCreate(['key' => 'status_lapak'], ['value' => 'buka']);
        Setting::updateOrCreate(['key' => 'jam_buka'], ['value' => '15:00']);
        Setting::updateOrCreate(['key' => 'jam_tutup'], ['value' => '22:00']);

        // Checkout berhasil
        $successCheckout = $this->actingAs($this->customer)->post(route('checkout.process'), [
            'metode_pemenuhan' => 'ambil_sendiri',
            'nama_penerima'    => 'Siti Sukses',
            'telepon'          => '081234567890',
        ]);
        $successCheckout->assertRedirect(route('payment'));
    }
}
