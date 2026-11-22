<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SalesHistory;
use App\Models\Setting;
use App\Models\ShippingLocation;
use App\Models\User;
use App\Services\ForecastService;
use App\Services\OrderStockService;
use App\Services\SalesReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Suite Pengujian End-to-End (E2E) & Regresi Lintas Modul Sprint 1-3 (Jira PPL-40 / WBS PPL 36).
 *
 * Memverifikasi integrasi penuh 19 kelompok fitur MVP sesuai PRD Konsolidasi v2.4 Bab 11
 * dan Kontrak Frontend-Backend Bagian 20.8.
 */
class EndToEndRegressionSprint1To3Test extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $courier1;
    protected User $courier2;
    protected User $customer1;
    protected User $customer2;
    protected Product $titikHitam;
    protected Product $titikManja;
    protected ShippingLocation $lapakUtama;
    protected ShippingLocation $lapakCadangan;
    protected string $midtransServerKey = 'SB-Mid-server-e2e-test-key';

    protected function setUp(): void
    {
        parent::setUp();

        // Tetapkan jam pengujian pada jam operasional aktif (16:00 WIB)
        Carbon::setTestNow(Carbon::create(2026, 11, 21, 16, 0, 0, 'Asia/Jakarta'));

        config([
            'services.midtrans.server_key'    => $this->midtransServerKey,
            'services.midtrans.client_key'    => 'SB-Mid-client-e2e-test-key',
            'services.midtrans.is_production' => false,
            'services.openrouteservice.simulation' => true,
        ]);

        $this->owner = User::factory()->create([
            'name'  => 'Farrel Owner',
            'email' => 'owner-e2e@kopitigatitik.test',
            'role'  => 'admin',
        ]);

        $this->courier1 = User::factory()->create([
            'name'    => 'Ilham Kurir',
            'email'   => 'kurir1-e2e@kopitigatitik.test',
            'role'    => 'kurir',
            'telepon' => '081234567890',
        ]);

        $this->courier2 = User::factory()->create([
            'name'    => 'Awal Kurir',
            'email'   => 'kurir2-e2e@kopitigatitik.test',
            'role'    => 'kurir',
            'telepon' => '085712345678',
        ]);

        $this->customer1 = User::factory()->create([
            'name'  => 'Nabil Pelanggan',
            'email' => 'nabil-e2e@kopitigatitik.test',
            'role'  => 'pelanggan',
        ]);

        $this->customer2 = User::factory()->create([
            'name'  => 'Budi Pelanggan',
            'email' => 'budi-e2e@kopitigatitik.test',
            'role'  => 'pelanggan',
        ]);

        $this->titikHitam = Product::factory()->create([
            'nama'  => 'Titik Hitam',
            'rasa'  => 'Americano',
            'harga' => 8000,
            'stok'  => 10,
            'aktif' => true,
        ]);

        $this->titikManja = Product::factory()->create([
            'nama'  => 'Titik Manja',
            'rasa'  => 'Kopi Susu Caramel',
            'harga' => 10000,
            'stok'  => 6,
            'aktif' => true,
        ]);

        $this->lapakUtama = ShippingLocation::create([
            'nama_lokasi' => 'Lapak Jl. Kuwung',
            'alamat'      => 'Jl. Kuwung, Mergelo, Kec. Magersari, Kota Mojokerto',
            'latitude'    => -7.472600,
            'longitude'   => 112.438500,
            'aktif'       => true,
        ]);

        $this->lapakCadangan = ShippingLocation::create([
            'nama_lokasi' => 'Rumah Produksi Magersari',
            'alamat'      => 'Jl. Benteng Pancasila No. 12, Magersari, Kota Mojokerto',
            'latitude'    => -7.468800,
            'longitude'   => 112.441200,
            'aktif'       => true,
        ]);

        Setting::updateOrCreate(['key' => 'lokasi_pengiriman_aktif_id'], ['value' => (string) $this->lapakUtama->id]);
        Setting::updateOrCreate(['key' => 'jam_buka'], ['value' => '15:00']);
        Setting::updateOrCreate(['key' => 'jam_tutup'], ['value' => '18:00']);
        Setting::updateOrCreate(['key' => 'status_lapak'], ['value' => 'buka']);
        Setting::updateOrCreate(['key' => 'batas_stok_minimum'], ['value' => '5']);
        Setting::updateOrCreate(['key' => 'stok_harian_standar'], ['value' => '10']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * Helper membuat payload webhook Midtrans ber-signature SHA-512 valid.
     */
    private function buildMidtransPayload(Order $order, string $transactionStatus = 'settlement'): array
    {
        $payment = $order->payment()->latest('id')->first();
        $midtransOrderId = $order->kode . '-' . ($payment?->id ?? 1);
        $statusCode = $transactionStatus === 'settlement' ? '200' : '202';
        $grossAmount = (string) $order->total;
        $signature = hash('sha512', $midtransOrderId . $statusCode . $grossAmount . $this->midtransServerKey);

        return [
            'order_id'           => $midtransOrderId,
            'status_code'        => $statusCode,
            'gross_amount'       => $grossAmount,
            'signature_key'      => $signature,
            'transaction_status' => $transactionStatus,
            'transaction_id'     => 'MID-E2E-' . $order->id,
            'fraud_status'       => 'accept',
        ];
    }

    /**
     * E2E-01: Alur lengkap pembelian online delivery dari Profil -> Katalog -> Keranjang -> Checkout
     * -> Midtrans Webhook (+ duplikat idempoten) -> Penugasan Kurir -> Selesai Kurir -> Nota & Riwayat Penjualan.
     */
    public function test_e2e_01_online_delivery_full_lifecycle_from_catalog_to_courier_and_sales_report(): void
    {
        // 1. Pelanggan memperbarui alamat pengiriman utama di Profil (Kota Mojokerto)
        $this->actingAs($this->customer1)
            ->put(route('profile.address.update'), [
                'label'   => 'Rumah Nabil',
                'alamat'  => 'Jl. Hayam Wuruk No. 42, Magersari, Kota Mojokerto',
                'patokan' => 'Depan apotek',
                'lat'     => -7.472100,
                'lng'     => 112.434000,
            ])
            ->assertRedirect(route('profile'));

        // 2. Pelanggan membuka katalog & menambahkan 2x Titik Hitam ke keranjang
        $this->actingAs($this->customer1)->get(route('katalog.index'))->assertStatus(200);
        $this->actingAs($this->customer1)
            ->postJson(route('api.cart.store'), [
                'product_id' => $this->titikHitam->id,
                'jumlah'     => 2,
            ])
            ->assertSuccessful();

        // 3. Pelanggan meminta quote ongkir jarak dekat (<= 5.5 km -> gratis)
        $this->actingAs($this->customer1)
            ->postJson(route('api.shipping.quote'), [
                'metode_pemenuhan' => 'pengiriman',
                'lat'              => -7.472100,
                'lng'              => 112.434000,
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.ongkir', 0);

        // 4. Pelanggan melakukan checkout delivery -> stok berkurang atomik (10 -> 8)
        $checkoutRes = $this->actingAs($this->customer1)
            ->postJson(route('api.checkout.store'), [
                'metode_pemenuhan' => 'pengiriman',
                'nama_penerima'    => 'Nabil Pelanggan',
                'telepon'          => '081234567890',
                'alamat'           => 'Jl. Hayam Wuruk No. 42, Magersari, Kota Mojokerto',
                'lat'              => -7.472100,
                'lng'              => 112.434000,
            ]);

        $checkoutRes->assertStatus(201);
        $this->titikHitam->refresh();
        $this->assertEquals(8, $this->titikHitam->stok);

        $order = Order::where('user_id', $this->customer1->id)->latest('id')->firstOrFail();
        $this->assertEquals('menunggu_pembayaran', $order->status);

        // 5. Midtrans webhook settlement valid -> status diproses & tercatat 1x di sales_history
        $payload = $this->buildMidtransPayload($order, 'settlement');
        $this->postJson(route('api.payments.webhook'), $payload)->assertStatus(200);

        // Kirim webhook duplikat (harus idempoten, tidak menggandakan sales_history)
        $this->postJson(route('api.payments.webhook'), $payload)->assertStatus(200);

        $order->refresh();
        $this->assertEquals('diproses', $order->status);
        $this->assertEquals(
            2,
            SalesHistory::where('product_id', $this->titikHitam->id)
                ->whereDate('tanggal', today())
                ->where('sumber', 'transaksi')
                ->value('jumlah_terjual')
        );

        // 6. Owner menugaskan Kurir 1 -> status dikirim
        $this->actingAs($this->owner)
            ->postJson(route('api.owner.orders.assign-courier', $order->id), [
                'courier_id' => $this->courier1->id,
            ])
            ->assertStatus(200);

        $order->refresh();
        $this->assertEquals('dikirim', $order->status);
        $this->assertEquals($this->courier1->id, $order->courier_id);

        // 7. Isolasi kurir: Kurir 2 ditolak (403) menyelesaikan antaran Kurir 1
        $this->actingAs($this->courier2)
            ->postJson(route('kurir.orders.complete', $order->id))
            ->assertStatus(403);

        // 8. Kurir 1 menyelesaikan antaran -> status selesai & sales_history TIDAK tergandakan (tetap 2)
        $this->actingAs($this->courier1)
            ->postJson(route('kurir.orders.complete', $order->id))
            ->assertStatus(200);

        $order->refresh();
        $this->assertEquals('selesai', $order->status);
        $this->assertEquals(
            2,
            SalesHistory::where('product_id', $this->titikHitam->id)
                ->whereDate('tanggal', today())
                ->where('sumber', 'transaksi')
                ->value('jumlah_terjual')
        );

        // 9. Pelanggan dapat membuka Nota Digital & laporan omzet bersih sinkron (Rp 16.000)
        $this->actingAs($this->customer1)
            ->get(route('nota', $order->kode))
            ->assertStatus(200)
            ->assertSee($order->kode);

        $report = app(SalesReportService::class)->getReport('harian');
        $this->assertEquals(2, $report['summary']['total_botol']);
        $this->assertEquals(16000, $report['summary']['total_omzet']);
    }

    /**
     * E2E-02: Alur Ambil Sendiri 24 Jam & Integritas Snapshot Lokasi (FR-11.7, BR-9, BR-11, BR-12).
     */
    public function test_e2e_02_online_pickup_24h_lifecycle_with_location_snapshot_integrity(): void
    {
        $this->actingAs($this->customer1)
            ->postJson(route('api.cart.store'), [
                'product_id' => $this->titikHitam->id,
                'jumlah'     => 1,
            ])
            ->assertSuccessful();

        $this->actingAs($this->customer1)
            ->postJson(route('api.checkout.store'), [
                'metode_pemenuhan' => 'ambil_sendiri',
                'nama_penerima'    => 'Nabil Pickup',
                'telepon'          => '081234567890',
                'alamat'           => 'Ambil Sendiri di Lapak Jl. Kuwung',
            ])
            ->assertStatus(201);

        $order = Order::where('user_id', $this->customer1->id)->latest('id')->firstOrFail();
        $this->assertEquals('ambil_sendiri', $order->metode_pemenuhan);
        $this->assertEquals(0, $order->ongkir);
        $this->assertEquals('Lapak Jl. Kuwung', $order->nama_lokasi_asal);
        $this->assertNotNull($order->batas_pengambilan);

        // Pembayaran Midtrans terverifikasi
        $this->postJson(route('api.payments.webhook'), $this->buildMidtransPayload($order, 'settlement'))
            ->assertStatus(200);

        // Owner mengganti lokasi lapak aktif ke lokasi cadangan (Rumah Produksi Magersari)
        $this->actingAs($this->owner)
            ->patch(route('owner.lokasi.select', $this->lapakCadangan->id))
            ->assertRedirect();

        // Snapshot pesanan lama TIDAK boleh berubah mengikuti lokasi baru (BR-9 / FR-11.7)
        $order->refresh();
        $this->assertEquals('Lapak Jl. Kuwung', $order->nama_lokasi_asal);

        // Owner menyerahkan pesanan ambil sendiri di lapak
        $this->actingAs($this->owner)
            ->postJson(route('api.owner.orders.complete-pickup', $order->id))
            ->assertStatus(200);

        $order->refresh();
        $this->assertEquals('selesai', $order->status);

        // Jumlah di sales_history tepat 1 (tidak double-record setelah Midtrans)
        $this->assertEquals(
            1,
            SalesHistory::where('product_id', $this->titikHitam->id)
                ->whereDate('tanggal', today())
                ->where('sumber', 'transaksi')
                ->value('jumlah_terjual')
        );
    }

    /**
     * E2E-03: POS Kasir Offline (Tunai & QRIS), Peringatan Restok Rule-Based (<= 5), Nota POS, & Ekspor Excel.
     */
    public function test_e2e_03_pos_offline_cash_and_qris_triggers_restock_warning_and_syncs_sales_and_export(): void
    {
        // 1. Transaksi POS Tunai: 2x Titik Manja (stok 6 -> 4, memicu ambang restok <= 5!)
        $cashPos = $this->actingAs($this->owner)
            ->postJson(route('owner.pos.transaction'), [
                'items' => [
                    ['product_id' => $this->titikManja->id, 'jumlah' => 2],
                ],
                'metode_pembayaran' => 'tunai',
                'nama_pelanggan'    => 'Pelanggan Tunai',
                'uang_diterima'     => 25000,
            ]);

        $cashPos->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.order.metode_pembayaran', 'tunai')
            ->assertJsonPath('data.order.kembalian', 5000);

        $posKode = $cashPos->json('data.order.kode');
        $this->titikManja->refresh();
        $this->assertEquals(4, $this->titikManja->stok);

        // 2. Transaksi POS QRIS Langsung: 1x Titik Hitam (stok 10 -> 9)
        $this->actingAs($this->owner)
            ->postJson(route('owner.pos.transaction'), [
                'items' => [
                    ['product_id' => $this->titikHitam->id, 'jumlah' => 1],
                ],
                'metode_pembayaran' => 'qris',
                'nama_pelanggan'    => 'Pelanggan QRIS',
            ])
            ->assertStatus(201);

        // 3. Verifikasi data POS pada tabel orders (BR-13: sumber_transaksi=pos, metode_pemenuhan=NULL)
        $this->assertDatabaseHas('orders', [
            'kode'             => $posKode,
            'sumber_transaksi' => 'pos',
            'metode_pemenuhan' => null,
            'status'           => 'selesai',
        ]);

        // 4. Notifikasi web Owner memunculkan peringatan stok menipis (<= 5)
        $notifRes = $this->actingAs($this->owner)->getJson(route('api.notifications'));
        $notifRes->assertStatus(200);
        $this->assertTrue(collect($notifRes->json('data'))->contains('tipe', 'stok'));

        // 5. Owner dapat membuka Nota Digital POS untuk cetak/unduh PDF
        $this->actingAs($this->owner)
            ->get(route('nota', $posKode))
            ->assertStatus(200)
            ->assertSee($posKode)
            ->assertSee('LUNAS (TUNAI LAPAK)');

        // 6. Riwayat Penjualan & Ekspor Excel mencakup transaksi POS (2x10.000 + 1x8.000 = Rp28.000)
        $report = app(SalesReportService::class)->getReport('harian');
        $this->assertEquals(3, $report['summary']['total_botol']);
        $this->assertEquals(28000, $report['summary']['total_omzet']);

        $this->actingAs($this->owner)
            ->get(route('owner.export.sales', ['periode' => 'harian']))
            ->assertStatus(200);
    }

    /**
     * E2E-04: Pembatalan Pelanggan & Kedaluwarsa 10 Menit Mengembalikan Stok Tepat Satu Kali (BR-2, BR-5, BR-6).
     */
    public function test_e2e_04_unpaid_order_cancellation_and_expiration_restores_stock_exactly_once(): void
    {
        $this->actingAs($this->customer1)
            ->postJson(route('api.cart.store'), [
                'product_id' => $this->titikHitam->id,
                'jumlah'     => 3,
            ])
            ->assertSuccessful();

        $this->actingAs($this->customer1)
            ->postJson(route('api.checkout.store'), [
                'metode_pemenuhan' => 'ambil_sendiri',
                'nama_penerima'    => 'Nabil Batal',
                'telepon'          => '081234567890',
                'alamat'           => 'Ambil Sendiri',
            ])
            ->assertStatus(201);

        $order = Order::where('user_id', $this->customer1->id)->latest('id')->firstOrFail();
        $this->titikHitam->refresh();
        $this->assertEquals(7, $this->titikHitam->stok);

        // Pelanggan membatalkan dalam batas waktu (< 10 menit)
        $this->actingAs($this->customer1)
            ->postJson(route('orders.cancel', $order->id))
            ->assertStatus(200);

        $order->refresh();
        $this->titikHitam->refresh();
        $this->assertEquals('dibatalkan', $order->status);
        $this->assertTrue((bool) $order->stok_dikembalikan);
        $this->assertEquals(10, $this->titikHitam->stok);

        // Pemanggilan ulang restoreStock harus idempoten (stok tetap 10, tidak menjadi 13)
        $secondRestore = app(OrderStockService::class)->restoreStock($order);
        $this->assertFalse($secondRestore);
        $this->titikHitam->refresh();
        $this->assertEquals(10, $this->titikHitam->stok);

        // Pesanan dibatalkan tidak boleh masuk sales_history
        $this->assertEquals(0, SalesHistory::count());
    }

    /**
     * E2E-05: Jam Operasional Otomatis & Validasi Geofence Kota Mojokerto (FR-7.5, FR-11.6, BR-8, BR-10).
     */
    public function test_e2e_05_operational_hours_and_geofence_enforce_online_checkout_boundaries(): void
    {
        // 1. Di dalam jam operasional (16:00 WIB): tujuan Surabaya ditolak 422 geofence
        $this->actingAs($this->customer1)
            ->postJson(route('api.shipping.quote'), [
                'metode_pemenuhan' => 'pengiriman',
                'lat'              => -7.241916,
                'lng'              => 112.758636,
            ])
            ->assertStatus(422)
            ->assertJsonPath('error_type', 'geofence');

        // 2. Di luar jam operasional (19:00 WIB, lewat jam_tutup 18:00): delivery & pickup keduanya diblokir
        Carbon::setTestNow(Carbon::create(2026, 11, 21, 19, 0, 0, 'Asia/Jakarta'));

        $this->actingAs($this->customer1)
            ->postJson(route('api.shipping.quote'), [
                'metode_pemenuhan' => 'pengiriman',
                'lat'              => -7.472100,
                'lng'              => 112.434000,
            ])
            ->assertStatus(422)
            ->assertJsonPath('error_type', 'lapak_closed');

        $this->actingAs($this->customer1)
            ->postJson(route('api.shipping.quote'), [
                'metode_pemenuhan' => 'ambil_sendiri',
            ])
            ->assertStatus(422)
            ->assertJsonPath('error_type', 'lapak_closed');
    }

    /**
     * E2E-06: Audit Hak Akses 3 Role & Isolasi Data Antar Pengguna (FR-1.5, FR-8.9, BR-24).
     */
    public function test_e2e_06_role_based_access_control_and_cross_user_data_isolation_across_all_roles(): void
    {
        // 1. Guest diarahkan ke login pada seluruh halaman terproteksi
        $this->get(route('cart'))->assertRedirect(route('login'));
        $this->get(route('owner.dashboard'))->assertRedirect(route('login'));
        $this->get(route('kurir.tugas-baru'))->assertRedirect(route('login'));

        // 2. Pelanggan ditolak 403 di rute Owner, POS, Ekspor, dan Kurir
        $this->actingAs($this->customer1)->get(route('owner.dashboard'))->assertStatus(403);
        $this->actingAs($this->customer1)->get(route('owner.pos'))->assertStatus(403);
        $this->actingAs($this->customer1)->get(route('owner.export.sales'))->assertStatus(403);
        $this->actingAs($this->customer1)->get(route('kurir.tugas-baru'))->assertStatus(403);

        // 3. Kurir ditolak 403 di rute Owner, POS, dan halaman khusus Pelanggan
        $this->actingAs($this->courier1)->get(route('owner.dashboard'))->assertStatus(403);
        $this->actingAs($this->courier1)->get(route('owner.pos'))->assertStatus(403);
        $this->actingAs($this->courier1)->get(route('cart'))->assertStatus(403);
        $this->actingAs($this->courier1)->get(route('orders'))->assertStatus(403);
        $this->actingAs($this->courier1)->get(route('katalog.index'))->assertRedirect(route('kurir.tugas-baru'));

        // 4. Owner (admin) DILARANG masuk tampilan/dashboard Pelanggan (403 di halaman pelanggan, redirect di katalog)
        $this->actingAs($this->owner)->get(route('cart'))->assertStatus(403);
        $this->actingAs($this->owner)->get(route('orders'))->assertStatus(403);
        $this->actingAs($this->owner)->get(route('tracking'))->assertStatus(403);
        $this->actingAs($this->owner)->get(route('profile'))->assertStatus(403);
        $this->actingAs($this->owner)->get(route('checkout'))->assertStatus(403);
        $this->actingAs($this->owner)->get(route('katalog.index'))->assertRedirect(route('owner.dashboard'));

        // 5. Isolasi lintas pelanggan: Pelanggan 2 ditolak 403 melihat pesanan & nota Pelanggan 1
        $orderCustomer1 = Order::factory()->create([
            'user_id' => $this->customer1->id,
            'status'  => 'diproses',
        ]);

        $this->actingAs($this->owner)->get(route('tracking.detail', $orderCustomer1->kode))->assertStatus(403);
        $this->actingAs($this->customer2)->get(route('orders.show', $orderCustomer1->kode))->assertStatus(403);
        $this->actingAs($this->customer2)->get(route('nota', $orderCustomer1->kode))->assertStatus(403);
    }

    /**
     * E2E-07: Isolasi Data Simulasi pada Prediksi Moving Average 21 Hari (BR-15, BR-17, BR-21).
     */
    public function test_e2e_07_moving_average_excludes_simulation_data_and_handles_missing_days_as_zero(): void
    {
        // Masukkan 25 hari data simulasi (sumber = 'simulasi')
        for ($i = 1; $i <= 25; $i++) {
            SalesHistory::create([
                'product_id'     => $this->titikHitam->id,
                'tanggal'        => today()->subDays($i)->toDateString(),
                'jumlah_terjual' => 10,
                'sumber'         => 'simulasi',
            ]);
        }

        // Moving Average produksi WAJIB menolak/menganggap data belum cukup karena belum ada sumber = 'transaksi'
        $forecastService = app(ForecastService::class);
        $resultSimOnly = $forecastService->getForecast();
        $this->assertFalse($resultSimOnly['isDataSufficient']);

        // Tambahkan data transaksi nyata pada H-21 (usia riwayat >= 21 hari) dan H-1 (total 21 botol; hari lain = 0)
        SalesHistory::create([
            'product_id'     => $this->titikHitam->id,
            'tanggal'        => today()->subDays(21)->toDateString(),
            'jumlah_terjual' => 14,
            'sumber'         => 'transaksi',
        ]);
        SalesHistory::create([
            'product_id'     => $this->titikHitam->id,
            'tanggal'        => today()->subDays(1)->toDateString(),
            'jumlah_terjual' => 7,
            'sumber'         => 'transaksi',
        ]);

        $resultReal = $forecastService->getForecast();
        $this->assertTrue($resultReal['isDataSufficient']);

        $hitamForecast = collect($resultReal['predictions'])->firstWhere('nama', $this->titikHitam->nama);
        $this->assertNotNull($hitamForecast);
        // Blok minggu 1 = 14, minggu 2 = 0 (hari kosong), minggu 3 = 7 -> SMA harian 21 hari = (14 + 0 + 7) / 21 = 1.0 (simulasi diabaikan!)
        $this->assertEquals(14, $hitamForecast['minggu_1']);
        $this->assertEquals(0, $hitamForecast['minggu_2']);
        $this->assertEquals(7, $hitamForecast['minggu_3']);
        $this->assertEquals(1.0, $hitamForecast['estimasi']);
    }
}
