<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\CourierManagementService;
use App\Services\OrderStatusService;
use App\Services\WebNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class OrderStatusAndCourierBackendTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $courier1;
    private User $courier2;
    private User $customer;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create([
            'role' => 'admin',
            'name' => 'Farrel Owner',
            'email' => 'owner1@gmail.com',
        ]);

        $this->courier1 = User::factory()->create([
            'role' => 'kurir',
            'name' => 'Ilham Kurir',
            'email' => 'kurir1ilham@gmail.com',
            'telepon' => '081234567891',
        ]);

        $this->courier2 = User::factory()->create([
            'role' => 'kurir',
            'name' => 'Awal Kurir',
            'email' => 'kurirawal@gmail.com',
            'telepon' => '081234567892',
        ]);

        $this->customer = User::factory()->create([
            'role' => 'pelanggan',
            'name' => 'Nabil Pelanggan',
            'email' => 'nabil@gmail.com',
            'telepon' => '081234567893',
        ]);

        $this->product = Product::factory()->create([
            'nama' => 'Titik Temu',
            'harga' => 10000,
            'stok' => 15,
            'aktif' => true,
        ]);
    }

    /**
     * 1. Test Transisi Sah & Pencatatan History (Bab 8 & FR-8.3 & FR-8.5).
     */
    public function test_allowed_status_transitions_succeed_and_record_history(): void
    {
        $statusService = app(OrderStatusService::class);

        // A. Pesanan Baru: menunggu_pembayaran -> diproses (via sistem/webhook)
        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'menunggu_pembayaran',
            'metode_pemenuhan' => 'pengiriman',
        ]);

        $this->assertTrue($statusService->canTransitionTo($order, 'diproses'));
        $statusService->transition($order, 'diproses', null, 'Pembayaran QRIS Midtrans berhasil diverifikasi.');

        $order->refresh();
        $this->assertEquals('diproses', $order->status);
        $this->assertDatabaseHas('order_status_history', [
            'order_id' => $order->id,
            'status_baru' => 'diproses',
            'diubah_oleh' => null,
            'keterangan' => 'Pembayaran QRIS Midtrans berhasil diverifikasi.',
        ]);

        // B. diproses -> dikirim (oleh Owner yang menugaskan kurir)
        $this->assertTrue($statusService->canTransitionTo($order, 'dikirim', $this->owner));
        $statusService->transition($order, 'dikirim', $this->owner, 'Pesanan diserahkan ke kurir.', [
            'courier_id' => $this->courier1->id,
        ]);

        $order->refresh();
        $this->assertEquals('dikirim', $order->status);
        $this->assertEquals($this->courier1->id, $order->courier_id);
        $this->assertDatabaseHas('order_status_history', [
            'order_id' => $order->id,
            'status_baru' => 'dikirim',
            'diubah_oleh' => $this->owner->id,
        ]);

        // C. dikirim -> selesai (oleh Kurir yang bertugas)
        $this->assertTrue($statusService->canTransitionTo($order, 'selesai', $this->courier1));
        $statusService->transition($order, 'selesai', $this->courier1, 'Pesanan diterima pembeli.');

        $order->refresh();
        $this->assertEquals('selesai', $order->status);
        $this->assertDatabaseHas('order_status_history', [
            'order_id' => $order->id,
            'status_baru' => 'selesai',
            'diubah_oleh' => $this->courier1->id,
        ]);
    }

    /**
     * 2. Test Transisi Ilegal Ditolak dengan Exception / Error (Bab 8 & FR-8.3).
     */
    public function test_illegal_status_transitions_are_rejected(): void
    {
        $statusService = app(OrderStatusService::class);

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'diproses',
            'metode_pemenuhan' => 'pengiriman',
        ]);

        // Mencoba langsung dari diproses ke menunggu_pembayaran (Mundur - Ditolak)
        $this->assertFalse($statusService->canTransitionTo($order, 'menunggu_pembayaran', $this->owner));

        // Mencoba membatalkan pesanan yang sudah berstatus diproses (Ditolak)
        $this->assertFalse($statusService->canTransitionTo($order, 'dibatalkan', $this->customer));

        $this->expectException(HttpException::class);
        $statusService->transition($order, 'dibatalkan', $this->customer, 'Pelanggan ingin batalkan pesanan');
    }

    /**
     * 3. Test Owner Dapat Melihat Daftar Kurir dan Statistik Pengantarannya.
     */
    public function test_owner_can_list_couriers_with_stats(): void
    {
        $response = $this->actingAs($this->owner)->getJson('/owner/couriers');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'name', 'email', 'telepon', 'role', 'active_tasks_count'],
            ],
            'meta' => ['active_couriers_count', 'max_allowed_couriers'],
        ]);
        $this->assertEquals(2, $response->json('meta.active_couriers_count'));
        $this->assertEquals(2, $response->json('meta.max_allowed_couriers'));
    }

    /**
     * 4. Test Batas Maksimal 2 Akun Kurir Aktif (US-31 / PRD Bab 15.3).
     * Saat ini sudah ada 2 kurir (courier1 dan courier2). Menambah kurir ke-3 harus ditolak dengan 422!
     */
    public function test_owner_cannot_create_more_than_two_active_couriers(): void
    {
        $response = $this->actingAs($this->owner)->postJson('/owner/couriers', [
            'name' => 'Kurir Ketiga',
            'email' => 'kurir3@gmail.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'telepon' => '081234567899',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['courier']);
        $this->assertStringContainsString('Maksimal hanya 2 akun kurir', $response->json('errors.courier.0'));
    }

    /**
     * 5. Test Owner Dapat Menambah Kurir Baru Jika Kuota Masih Ada (< 2).
     */
    public function test_owner_can_create_courier_when_under_quota(): void
    {
        // Hapus salah satu kurir agar kuota tersisa 1
        $this->courier2->delete();
        $this->assertEquals(1, User::where('role', 'kurir')->count());

        $response = $this->actingAs($this->owner)->postJson('/owner/couriers', [
            'name' => 'Kurir Baru Rizky',
            'email' => 'kurir_rizky@gmail.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'telepon' => '081987654321',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'kurir_rizky@gmail.com',
            'role' => 'kurir',
        ]);
        $this->assertEquals(2, User::where('role', 'kurir')->count());
    }

    /**
     * 6. Test Owner Menghapus Kurir yang Tidak Memiliki Antaran Aktif.
     */
    public function test_owner_can_delete_courier_without_active_deliveries(): void
    {
        $response = $this->actingAs($this->owner)->deleteJson('/owner/couriers/' . $this->courier2->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', ['id' => $this->courier2->id]);
        $this->assertEquals(1, User::where('role', 'kurir')->count());
    }

    /**
     * 7. Test Owner Tidak Boleh Menghapus Kurir yang Sedang Menjalankan Antaran 'dikirim'.
     */
    public function test_owner_cannot_delete_courier_with_active_deliveries(): void
    {
        // Beri tugas aktif ke courier1
        Order::factory()->create([
            'user_id' => $this->customer->id,
            'courier_id' => $this->courier1->id,
            'status' => 'dikirim',
        ]);

        $response = $this->actingAs($this->owner)->deleteJson('/owner/couriers/' . $this->courier1->id);

        $response->assertStatus(422);
        $this->assertDatabaseHas('users', ['id' => $this->courier1->id]);
    }

    /**
     * 8. Test Penugasan Kurir Membutuhkan Pesanan 'diproses' dan Kurir Valid.
     */
    public function test_courier_assignment_validates_order_and_courier(): void
    {
        // A. Pesanan Ambil Sendiri tidak boleh ditugaskan kurir
        $orderPickup = Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'diproses',
            'metode_pemenuhan' => 'ambil_sendiri',
        ]);

        $responsePickup = $this->actingAs($this->owner)->postJson('/owner/orders/' . $orderPickup->id . '/assign-courier', [
            'courier_id' => $this->courier1->id,
        ]);
        $responsePickup->assertStatus(422);

        // B. Menugaskan user yang bukan ber-role kurir (misal customer)
        $orderDelivery = Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'diproses',
            'metode_pemenuhan' => 'pengiriman',
        ]);

        $responseInvalidRole = $this->actingAs($this->owner)->postJson('/owner/orders/' . $orderDelivery->id . '/assign-courier', [
            'courier_id' => $this->customer->id,
        ]);
        $responseInvalidRole->assertStatus(422);
    }

    /**
     * 9. Test Kurir Hanya Bisa Mengakses & Menyelesaikan Tugasnya Sendiri (FR-8.9 Isolasi Data).
     */
    public function test_courier_data_isolation_and_completion(): void
    {
        $orderKurir1 = Order::factory()->create([
            'user_id' => $this->customer->id,
            'courier_id' => $this->courier1->id,
            'status' => 'dikirim',
            'kode' => 'KTT-KURIR1-ACTIVE',
        ]);

        $orderKurir2 = Order::factory()->create([
            'user_id' => $this->customer->id,
            'courier_id' => $this->courier2->id,
            'status' => 'dikirim',
            'kode' => 'KTT-KURIR2-ACTIVE',
        ]);

        // Kurir 1 mengambil daftar tugas API
        $responseTasks = $this->actingAs($this->courier1)->getJson('/api/kurir/tasks');
        $responseTasks->assertStatus(200);
        $responseTasks->assertJsonFragment(['kode' => 'KTT-KURIR1-ACTIVE']);
        $responseTasks->assertJsonMissing(['kode' => 'KTT-KURIR2-ACTIVE']);

        // Kurir 1 mencoba menyelesaikan order milik Kurir 2 (Harus 403 Forbidden)
        $responseForbidden = $this->actingAs($this->courier1)->postJson('/kurir/orders/' . $orderKurir2->id . '/complete');
        $responseForbidden->assertStatus(403);

        // Kurir 1 menyelesaikan order miliknya (Sukses)
        $responseSuccess = $this->actingAs($this->courier1)->postJson('/kurir/orders/' . $orderKurir1->id . '/complete');
        $responseSuccess->assertStatus(200);

        $orderKurir1->refresh();
        $this->assertEquals('selesai', $orderKurir1->status);
    }

    /**
     * 10. Test API Daftar Pesanan Owner dengan Filter & Search (FR-8.1).
     */
    public function test_api_owner_orders_list_with_filter_and_search(): void
    {
        Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'diproses',
            'kode' => 'KTT-FILTER-DIPROSES',
            'nama_penerima' => 'Budi Santoso',
        ]);

        Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'selesai',
            'kode' => 'KTT-FILTER-SELESAI',
            'nama_penerima' => 'Siti Aminah',
        ]);

        // Filter status: diproses
        $responseFilter = $this->actingAs($this->owner)->getJson('/api/owner/orders?status=diproses');
        $responseFilter->assertStatus(200);
        $responseFilter->assertJsonFragment(['kode' => 'KTT-FILTER-DIPROSES']);
        $responseFilter->assertJsonMissing(['kode' => 'KTT-FILTER-SELESAI']);

        // Search nama: Siti
        $responseSearch = $this->actingAs($this->owner)->getJson('/api/owner/orders?search=Siti');
        $responseSearch->assertStatus(200);
        $responseSearch->assertJsonFragment(['kode' => 'KTT-FILTER-SELESAI']);
        $responseSearch->assertJsonMissing(['kode' => 'KTT-FILTER-DIPROSES']);
    }

    /**
     * 11. Test Web Notifications Dinamis untuk Seluruh Peran (F14 / FR-14 / BR-19 Tanpa Tabel Baru).
     */
    public function test_web_notifications_service_for_all_roles(): void
    {
        $notifService = app(WebNotificationService::class);

        // 1. Notifikasi Owner: Pesanan butuh kurir & stok menipis
        Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'diproses',
            'metode_pemenuhan' => 'pengiriman',
            'courier_id' => null,
        ]);

        Product::factory()->create([
            'nama' => 'Titik Kritis',
            'stok' => 3, // <= 5
            'aktif' => true,
        ]);

        $ownerNotifs = $notifService->getNotificationsForUser($this->owner);
        $this->assertNotEmpty($ownerNotifs);
        $this->assertTrue(collect($ownerNotifs)->contains('tipe', 'kurir'));
        $this->assertTrue(collect($ownerNotifs)->contains('tipe', 'stok'));

        // 2. Notifikasi Kurir: Ada tugas baru yang ditugaskan
        Order::factory()->create([
            'user_id' => $this->customer->id,
            'courier_id' => $this->courier1->id,
            'status' => 'dikirim',
        ]);

        $courierNotifs = $notifService->getNotificationsForUser($this->courier1);
        $this->assertNotEmpty($courierNotifs);
        $this->assertTrue(collect($courierNotifs)->contains('tipe', 'tugas_baru'));

        // Endpoint API notifikasi
        $response = $this->actingAs($this->owner)->getJson('/api/notifications');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
    }

    /**
     * 12. Test Sinkronisasi: Pesanan selesai otomatis mencatat ke sales_history dan sinkron ke omset dashboard.
     */
    public function test_completed_order_synchronizes_with_sales_history_and_dashboard_omzet(): void
    {
        $statusService = app(OrderStatusService::class);
        $reportService = app(\App\Services\SalesReportService::class);

        // Buat pesanan dengan 3 botol Titik Temu
        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'diproses',
            'metode_pemenuhan' => 'ambil_sendiri',
            'subtotal' => 30000,
            'ongkir' => 0,
            'total' => 30000,
            'sumber_transaksi' => 'online',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'nama_produk' => $this->product->nama,
            'jumlah' => 3,
            'harga_satuan' => 10000,
            'subtotal' => 30000,
        ]);

        // Selesaikan pesanan
        $statusService->transition($order, 'selesai', $this->owner, 'Pesanan diambil pelanggan di lapak.');

        // 1. Verifikasi sales_history bertambah
        $salesRecord = \App\Models\SalesHistory::where('product_id', $this->product->id)
            ->whereDate('tanggal', today())
            ->where('sumber', 'transaksi')
            ->first();

        $this->assertNotNull($salesRecord);
        $this->assertEquals(3, $salesRecord->jumlah_terjual);

        // 2. Verifikasi SalesReportService harian memuat 3 botol dan Rp 30.000
        $report = $reportService->getReport('harian');
        $this->assertEquals(3, $report['summary']['total_botol']);
        $this->assertEquals(30000, $report['summary']['total_omzet']);

        // 3. Verifikasi Dashboard Owner memuat omset Rp 30.000
        $dashboardResponse = $this->actingAs($this->owner)->get(route('owner.dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Rp 30.000');
    }
}

