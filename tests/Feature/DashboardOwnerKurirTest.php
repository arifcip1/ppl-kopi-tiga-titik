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

class DashboardOwnerKurirTest extends TestCase
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

        $this->owner = User::factory()->create(['role' => 'admin', 'name' => 'Farrel Owner']);
        $this->courier1 = User::factory()->create(['role' => 'kurir', 'name' => 'Ilham Kurir']);
        $this->courier2 = User::factory()->create(['role' => 'kurir', 'name' => 'Awal Kurir']);
        $this->customer = User::factory()->create(['role' => 'pelanggan', 'name' => 'Nabil Pelanggan']);

        $this->product = Product::factory()->create([
            'nama'   => 'Titik Temu',
            'harga'  => 10000,
            'stok'   => 15,
            'aktif'  => true,
        ]);
    }

    /**
     * 1. Owner dapat membuka dashboard dan melihat metrik operasional serta daftar pesanan.
     */
    public function test_owner_can_view_dashboard_with_metrics_and_orders(): void
    {
        // Buat pesanan contoh
        $order = Order::factory()->create([
            'user_id'          => $this->customer->id,
            'status'           => 'diproses',
            'subtotal'         => 20000,
            'ongkir'           => 0,
            'total'            => 20000,
            'metode_pemenuhan' => 'pengiriman',
        ]);

        OrderItem::create([
            'order_id'     => $order->id,
            'product_id'   => $this->product->id,
            'nama_produk'  => $this->product->nama,
            'jumlah'       => 2,
            'harga_satuan' => 10000,
            'subtotal'     => 20000,
        ]);

        $response = $this->actingAs($this->owner)->get(route('owner.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Kopi Tiga Titik');
        $response->assertSee($order->kode);
        $response->assertSee('RINGKASAN HARI INI');
    }

    /**
     * 2. Owner dapat memfilter daftar pesanan berdasarkan status.
     */
    public function test_owner_can_filter_orders_by_status(): void
    {
        $orderDiproses = Order::factory()->create([
            'user_id' => $this->customer->id,
            'status'  => 'diproses',
            'kode'    => 'KTT-TEST-0001',
        ]);

        $orderSelesai = Order::factory()->create([
            'user_id' => $this->customer->id,
            'status'  => 'selesai',
            'kode'    => 'KTT-TEST-0002',
        ]);

        // Filter status: diproses
        $response = $this->actingAs($this->owner)->get(route('owner.dashboard', ['status' => 'diproses']));
        $response->assertStatus(200);
        $response->assertSee('KTT-TEST-0001');
        $response->assertDontSee('KTT-TEST-0002');

        // Filter status: selesai
        $responseSelesai = $this->actingAs($this->owner)->get(route('owner.dashboard', ['status' => 'selesai']));
        $responseSelesai->assertStatus(200);
        $responseSelesai->assertSee('KTT-TEST-0002');
        $responseSelesai->assertDontSee('KTT-TEST-0001');
    }

    /**
     * 3. Owner dapat menugaskan kurir pada pesanan 'diproses', mengubah status ke 'dikirim', dan mencatat history.
     */
    public function test_owner_can_assign_courier_to_order_and_updates_status(): void
    {
        $order = Order::factory()->create([
            'user_id'          => $this->customer->id,
            'status'           => 'diproses',
            'metode_pemenuhan' => 'pengiriman',
            'courier_id'       => null,
        ]);

        $response = $this->actingAs($this->owner)->post(route('owner.orders.assign-courier', $order->id), [
            'courier_id' => $this->courier1->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('dikirim', $order->status);
        $this->assertEquals($this->courier1->id, $order->courier_id);

        // Verifikasi pencatatan order_status_history (FR-8.5)
        $this->assertDatabaseHas('order_status_history', [
            'order_id'    => $order->id,
            'status_baru' => 'dikirim',
            'diubah_oleh' => $this->owner->id,
        ]);
    }

    /**
     * 4. Owner dapat melihat detail pesanan via endpoint JSON.
     */
    public function test_owner_can_fetch_order_detail_json(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'status'  => 'diproses',
        ]);

        $response = $this->actingAs($this->owner)->getJson(route('owner.orders.detail', $order->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data'    => [
                'id'   => $order->id,
                'kode' => $order->kode,
            ],
        ]);
    }

    /**
     * 5. Kurir hanya dapat melihat pesanan yang ditugaskan kepadanya (Isolasi Data FR-8.9).
     */
    public function test_courier_only_sees_orders_assigned_to_them(): void
    {
        // Pesanan untuk Kurir 1
        $orderKurir1 = Order::factory()->create([
            'user_id'    => $this->customer->id,
            'courier_id' => $this->courier1->id,
            'status'     => 'dikirim',
            'kode'       => 'KTT-KURIR1-0001',
        ]);

        // Pesanan untuk Kurir 2
        $orderKurir2 = Order::factory()->create([
            'user_id'    => $this->customer->id,
            'courier_id' => $this->courier2->id,
            'status'     => 'dikirim',
            'kode'       => 'KTT-KURIR2-0002',
        ]);

        // Kurir 1 membuka tugas baru
        $response = $this->actingAs($this->courier1)->get(route('kurir.tugas-baru'));

        $response->assertStatus(200);
        $response->assertSee('KTT-KURIR1-0001');
        $response->assertDontSee('KTT-KURIR2-0002'); // Tidak boleh melihat pesanan kurir lain!
    }

    /**
     * 6. Kurir dapat menandai pesanan miliknya selesai setelah diantar (FR-8.3).
     */
    public function test_courier_can_mark_their_order_as_completed(): void
    {
        $order = Order::factory()->create([
            'user_id'    => $this->customer->id,
            'courier_id' => $this->courier1->id,
            'status'     => 'dikirim',
        ]);

        $response = $this->actingAs($this->courier1)->post(route('kurir.orders.complete', $order->id));

        $response->assertRedirect(route('kurir.tugas-baru'));
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('selesai', $order->status);

        // Verifikasi pencatatan order_status_history
        $this->assertDatabaseHas('order_status_history', [
            'order_id'    => $order->id,
            'status_baru' => 'selesai',
            'diubah_oleh' => $this->courier1->id,
        ]);
    }

    /**
     * 7. Kurir ditolak saat mencoba menyelesaikan pesanan yang bukan miliknya (403 Forbidden).
     */
    public function test_courier_cannot_complete_another_couriers_order(): void
    {
        // Pesanan milik Kurir 2
        $orderKurir2 = Order::factory()->create([
            'user_id'    => $this->customer->id,
            'courier_id' => $this->courier2->id,
            'status'     => 'dikirim',
        ]);

        // Kurir 1 mencoba menyelesaikan pesanan Kurir 2
        $response = $this->actingAs($this->courier1)->post(route('kurir.orders.complete', $orderKurir2->id));

        $response->assertStatus(403);

        $orderKurir2->refresh();
        $this->assertEquals('dikirim', $orderKurir2->status); // Status tetap tidak berubah
    }

    /**
     * 8. Pelanggan biasa ditolak saat mengakses Dashboard Owner atau Dashboard Kurir (403 Forbidden).
     */
    public function test_customer_cannot_access_owner_or_courier_dashboard(): void
    {
        $responseOwner = $this->actingAs($this->customer)->get(route('owner.dashboard'));
        $responseOwner->assertStatus(403);

        $responseKurir = $this->actingAs($this->customer)->get(route('kurir.tugas-baru'));
        $responseKurir->assertStatus(403);
    }

    /**
     * 9. Owner dapat menyelesaikan pesanan ambil sendiri di lapak.
     */
    public function test_owner_can_complete_pickup_order(): void
    {
        $order = Order::factory()->create([
            'user_id'          => $this->customer->id,
            'status'           => 'diproses',
            'metode_pemenuhan' => 'ambil_sendiri',
        ]);

        $response = $this->actingAs($this->owner)->post(route('owner.orders.complete-pickup', $order->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('selesai', $order->status);

        $this->assertDatabaseHas('order_status_history', [
            'order_id'    => $order->id,
            'status_baru' => 'selesai',
            'diubah_oleh' => $this->owner->id,
        ]);
    }
}
