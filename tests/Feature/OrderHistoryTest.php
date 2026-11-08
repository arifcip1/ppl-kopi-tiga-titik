<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderHistoryTest extends TestCase
{
    use RefreshDatabase;

    private User $customerA;
    private User $customerB;
    private Product $product1;
    private Product $product2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerA = User::factory()->create([
            'name' => 'Pelanggan A',
            'email' => 'pelanggana@gmail.com',
            'role' => 'pelanggan',
        ]);

        $this->customerB = User::factory()->create([
            'name' => 'Pelanggan B',
            'email' => 'pelangganb@gmail.com',
            'role' => 'pelanggan',
        ]);

        $this->product1 = Product::factory()->create([
            'nama' => 'Titik Hitam',
            'rasa' => 'Americano',
            'harga' => 8000,
            'stok' => 10,
            'aktif' => true,
        ]);

        $this->product2 = Product::factory()->create([
            'nama' => 'Titik Temu',
            'rasa' => 'Kopi Susu Gula Aren',
            'harga' => 10000,
            'stok' => 10,
            'aktif' => true,
        ]);
    }

    /**
     * Helper untuk membuat pesanan lengkap dengan items, status history, dan payment.
     */
    private function createOrderFor(User $user, string $status = 'diproses', string $kode = 'KTT-20261108-0001'): Order
    {
        $order = Order::create([
            'kode' => $kode,
            'user_id' => $user->id,
            'courier_id' => null,
            'status' => $status,
            'subtotal' => 28000,
            'ongkir' => 0,
            'total' => 28000,
            'nama_penerima' => $user->name,
            'telepon' => '081234567890',
            'alamat' => 'Jl. Hayam Wuruk No. 42, Kota Mojokerto',
            'lat' => -7.4721,
            'lng' => 112.4340,
            'catatan' => 'Cooler box',
            'stok_dikembalikan' => false,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product1->id,
            'nama_produk' => $this->product1->nama,
            'rasa' => $this->product1->rasa,
            'jumlah' => 1,
            'harga_satuan' => 8000,
            'subtotal' => 8000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product2->id,
            'nama_produk' => $this->product2->nama,
            'rasa' => $this->product2->rasa,
            'jumlah' => 2,
            'harga_satuan' => 10000,
            'subtotal' => 20000,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'referensi_provider' => 'QRIS-TEST-001',
            'status' => 'berhasil',
            'jumlah' => 28000,
            'kedaluwarsa_pada' => now()->addMinutes(10),
            'waktu_verifikasi' => now(),
        ]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status_sebelumnya' => null,
            'status_baru' => 'menunggu_pembayaran',
            'user_id' => $user->id,
            'keterangan' => 'Pesanan dibuat',
        ]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status_sebelumnya' => 'menunggu_pembayaran',
            'status_baru' => $status,
            'user_id' => null,
            'keterangan' => 'Status diubah ke ' . $status,
        ]);

        return $order;
    }

    /**
     * 1. Guest tidak dapat mengakses halaman riwayat pesanan (FR-1.4).
     */
    public function test_guest_cannot_access_order_history_and_redirects_to_login(): void
    {
        $response = $this->get('/riwayat-pesanan');
        $response->assertRedirect('/login');
    }

    /**
     * 2. Guest tidak dapat mengakses API riwayat pesanan.
     */
    public function test_guest_cannot_access_orders_api(): void
    {
        $response = $this->getJson('/api/orders');
        $response->assertStatus(401);
    }

    /**
     * 3. Pembeli dapat membuka halaman riwayat pesanan dan melihat pesanannya.
     */
    public function test_customer_can_view_order_history_page(): void
    {
        $order = $this->createOrderFor($this->customerA, 'diproses', 'KTT-20261108-0001');

        $response = $this->actingAs($this->customerA)->get('/riwayat-pesanan');

        $response->assertStatus(200);
        $response->assertSee('KTT-20261108-0001');
        $response->assertSee('Titik Hitam');
        $response->assertSee('Titik Temu');
        $response->assertSee('Rp 28.000');
    }

    /**
     * 4. Pembeli HANYA melihat pesanannya sendiri, BUKAN pesanan pembeli lain (BR-24, FR-8.6, FR-8.9).
     */
    public function test_customer_only_sees_their_own_orders_and_not_others(): void
    {
        $orderA = $this->createOrderFor($this->customerA, 'diproses', 'KTT-A-0001');
        $orderB = $this->createOrderFor($this->customerB, 'diproses', 'KTT-B-0002');

        $response = $this->actingAs($this->customerA)->get('/riwayat-pesanan');

        $response->assertStatus(200);
        $response->assertSee('KTT-A-0001');
        $response->assertDontSee('KTT-B-0002');
    }

    /**
     * 5. API /api/orders mengembalikan JSON riwayat pesanan pembeli dengan format yang benar.
     */
    public function test_orders_api_returns_correct_json_structure(): void
    {
        $this->createOrderFor($this->customerA, 'diproses', 'KTT-20261108-0001');

        $response = $this->actingAs($this->customerA)->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.active_count', 1)
            ->assertJsonPath('data.orders.0.kode', 'KTT-20261108-0001');
    }

    /**
     * 6. API /api/orders dapat memfilter pesanan berdasarkan status.
     */
    public function test_orders_api_can_filter_by_status(): void
    {
        $this->createOrderFor($this->customerA, 'diproses', 'KTT-AKTIF-0001');
        $this->createOrderFor($this->customerA, 'selesai', 'KTT-SELESAI-0002');

        $response = $this->actingAs($this->customerA)->getJson('/api/orders?status=diproses');

        $response->assertStatus(200)
            ->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.orders.0.kode', 'KTT-AKTIF-0001');
    }

    /**
     * 7. Pembeli dapat membuka halaman tracking pesanan miliknya.
     */
    public function test_customer_can_view_order_tracking_page(): void
    {
        $order = $this->createOrderFor($this->customerA, 'diproses', 'KTT-20261108-0001');

        $response = $this->actingAs($this->customerA)->get('/order-tracking/' . $order->kode);

        $response->assertStatus(200);
        $response->assertSee('KTT-20261108-0001');
        $response->assertSee('Sedang Diproses');
        $response->assertSee('Jl. Hayam Wuruk No. 42');
        $response->assertSee('QRIS');
    }

    /**
     * 8. API /api/orders/{kode} mengembalikan detail pesanan dengan relasi lengkap.
     */
    public function test_order_detail_api_returns_complete_json(): void
    {
        $order = $this->createOrderFor($this->customerA, 'diproses', 'KTT-20261108-0001');

        $response = $this->actingAs($this->customerA)->getJson('/api/orders/' . $order->kode);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.kode', 'KTT-20261108-0001')
            ->assertJsonPath('data.subtotal', 28000)
            ->assertJsonPath('data.status', 'diproses')
            ->assertJsonCount(2, 'data.order_items')
            ->assertJsonCount(2, 'data.status_histories');
    }

    /**
     * 9. Pembeli DITOLAK (403) saat mencoba mengakses detail pesanan milik pembeli lain (BR-24, FR-8.6).
     */
    public function test_customer_cannot_view_another_customer_order_and_gets_403(): void
    {
        $orderB = $this->createOrderFor($this->customerB, 'diproses', 'KTT-B-0002');

        // Akses via Web View
        $responseWeb = $this->actingAs($this->customerA)->get('/order-tracking/' . $orderB->kode);
        $responseWeb->assertStatus(403);

        // Akses via JSON API
        $responseApi = $this->actingAs($this->customerA)->getJson('/api/orders/' . $orderB->kode);
        $responseApi->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Anda tidak memiliki hak akses untuk melihat pesanan ini.');
    }

    /**
     * 10. Mengakses kode pesanan yang tidak ada menghasilkan error 404.
     */
    public function test_non_existent_order_returns_404(): void
    {
        $response = $this->actingAs($this->customerA)->get('/order-tracking/KTT-TIDAK-ADA');
        $response->assertStatus(404);

        $responseApi = $this->actingAs($this->customerA)->getJson('/api/orders/KTT-TIDAK-ADA');
        $responseApi->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    /**
     * 11. Halaman riwayat pesanan menampilkan empty state saat pembeli belum pernah memesan.
     */
    public function test_order_history_shows_empty_state_when_no_orders(): void
    {
        $newCustomer = User::factory()->create(['role' => 'pelanggan']);

        $response = $this->actingAs($newCustomer)->get('/riwayat-pesanan');

        $response->assertStatus(200);
        $response->assertSee('Tidak Ada Pesanan yang Sedang Berjalan');
    }

    /**
     * 12. Halaman tracking pesanan menampilkan empty state saat pembeli belum pernah memesan.
     */
    public function test_order_tracking_shows_empty_state_when_no_orders(): void
    {
        $newCustomer = User::factory()->create(['role' => 'pelanggan']);

        $response = $this->actingAs($newCustomer)->get('/order-tracking');

        $response->assertStatus(200);
        $response->assertSee('Belum Ada Pesanan yang Dilacak');
    }
}

