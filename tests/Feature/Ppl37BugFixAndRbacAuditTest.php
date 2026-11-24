<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\ShippingLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Automated Verification Suite untuk PPL-41 / WBS 4.3.2 (PPL 37):
 * Perbaikan Bug Kelola Produk, Daftar & Detail Pesanan, Pengelolaan Kurir,
 * Notifikasi Web, Ambil Sendiri, Profil Owner, & Audit Validasi Hak Akses (RBAC).
 */
class Ppl37BugFixAndRbacAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $courier1;
    protected User $courier2;
    protected User $customer1;
    protected User $customer2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::create([
            'name'     => 'Farrel Owner',
            'email'    => 'owner1@gmail.com',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
        ]);

        $this->courier1 = User::create([
            'name'     => 'Ilham Kurir',
            'email'    => 'kurir1@gmail.com',
            'password' => Hash::make('password123'),
            'role'     => 'kurir',
            'telepon'  => '081234567890',
        ]);

        $this->courier2 = User::create([
            'name'     => 'Awal Kurir',
            'email'    => 'kurir2@gmail.com',
            'password' => Hash::make('password123'),
            'role'     => 'kurir',
            'telepon'  => '085712345678',
        ]);

        $this->customer1 = User::create([
            'name'     => 'Nabil Pelanggan',
            'email'    => 'nabil@gmail.com',
            'password' => Hash::make('password123'),
            'role'     => 'pelanggan',
        ]);

        $this->customer2 = User::create([
            'name'     => 'Budi Pelanggan',
            'email'    => 'budi@gmail.com',
            'password' => Hash::make('password123'),
            'role'     => 'pelanggan',
        ]);
    }

    /**
     * Bug Fix #1: Profil Owner menampilkan data dinamis dan PUT /owner/profil berhasil menyimpan perubahan.
     */
    public function test_owner_profile_displays_dynamic_data_and_can_be_updated(): void
    {
        $location = ShippingLocation::create([
            'nama_lokasi' => 'Lapak Alun-Alun Mojokerto',
            'alamat'      => 'Jl. Majapahit No. 1, Kota Mojokerto',
            'latitude'    => -7.472600,
            'longitude'   => 112.438500,
            'aktif'       => true,
        ]);

        Setting::updateOrCreate(['key' => 'lokasi_pengiriman_aktif_id'], ['value' => (string) $location->id]);
        Setting::updateOrCreate(['key' => 'status_lapak'], ['value' => 'buka']);
        Setting::updateOrCreate(['key' => 'jam_buka'], ['value' => '00:00']);
        Setting::updateOrCreate(['key' => 'jam_tutup'], ['value' => '23:59']);

        $viewRes = $this->actingAs($this->owner)->get('/owner/profil');
        $viewRes->assertStatus(200)
            ->assertSee('Farrel Owner')
            ->assertSee('owner1@gmail.com')
            ->assertSee('Lapak Alun-Alun Mojokerto')
            ->assertSee('00:00 - 23:59 WIB');

        $updateRes = $this->actingAs($this->owner)->put('/owner/profil', [
            'name'                  => 'Farrel Fastha Updated',
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $updateRes->assertRedirect();
        $updateRes->assertSessionHas('success');

        $this->owner->refresh();
        $this->assertSame('Farrel Fastha Updated', $this->owner->name);
        $this->assertTrue(Hash::check('newpassword123', $this->owner->password));

        // Role lain ditolak 403
        $this->actingAs($this->customer1)->put('/owner/profil', ['name' => 'Hacker'])->assertStatus(403);
        $this->actingAs($this->courier1)->put('/owner/profil', ['name' => 'Hacker'])->assertStatus(403);
    }

    /**
     * Bug Fix #2: Metrik Perlu Restok Kelola Produk hanya menghitung produk aktif,
     * dan API produk menghormati nilai eksplisit aktif=false.
     */
    public function test_product_management_restock_metric_excludes_inactive_and_api_respects_aktif_false(): void
    {
        Product::create([
            'nama'      => 'Titik Aktif Menipis',
            'rasa'      => 'Caramel',
            'harga'     => 10000,
            'stok'      => 2,
            'komposisi' => 'Espresso, susu',
            'deskripsi' => 'Aktif menipis',
            'keawetan'  => '5 hari',
            'aktif'     => true,
        ]);

        Product::create([
            'nama'      => 'Titik Nonaktif Habis',
            'rasa'      => 'Coklat',
            'harga'     => 12000,
            'stok'      => 0,
            'komposisi' => 'Coklat, susu',
            'deskripsi' => 'Sudah nonaktif',
            'keawetan'  => '5 hari',
            'aktif'     => false,
        ]);

        $indexRes = $this->actingAs($this->owner)->get('/owner/produk');
        $indexRes->assertStatus(200);
        $indexRes->assertViewHas('perluRestok', 1);

        // API store dengan aktif: false harus tersimpan false (bukan true)
        $apiCreate = $this->actingAs($this->owner)->postJson('/api/admin/products', [
            'nama'      => 'Titik Draft Nonaktif',
            'rasa'      => 'Matcha',
            'harga'     => 13000,
            'stok'      => 10,
            'komposisi' => 'Matcha, susu',
            'deskripsi' => 'Draft menu baru',
            'keawetan'  => '5 hari',
            'aktif'     => false,
        ]);

        $apiCreate->assertStatus(201);
        $this->assertDatabaseHas('products', [
            'nama'  => 'Titik Draft Nonaktif',
            'aktif' => false,
        ]);

        $created = Product::where('nama', 'Titik Draft Nonaktif')->firstOrFail();

        // API update mengaktifkan lalu menonaktifkan kembali
        $this->actingAs($this->owner)->putJson("/api/admin/products/{$created->id}", [
            'nama'      => 'Titik Draft Nonaktif',
            'rasa'      => 'Matcha',
            'harga'     => 13000,
            'stok'      => 10,
            'komposisi' => 'Matcha, susu',
            'deskripsi' => 'Draft menu baru',
            'keawetan'  => '5 hari',
            'aktif'     => true,
        ])->assertStatus(200);

        $this->assertTrue((bool) $created->fresh()->aktif);

        $this->actingAs($this->owner)->putJson("/api/admin/products/{$created->id}", [
            'nama'      => 'Titik Draft Nonaktif',
            'rasa'      => 'Matcha',
            'harga'     => 13000,
            'stok'      => 10,
            'komposisi' => 'Matcha, susu',
            'deskripsi' => 'Draft menu baru',
            'keawetan'  => '5 hari',
            'aktif'     => false,
        ])->assertStatus(200);

        $this->assertFalse((bool) $created->fresh()->aktif);
    }

    /**
     * Bug Fix #3: Notifikasi Web Owner mencakup pesanan Ambil Sendiri berstatus diproses,
     * dan Notifikasi Pelanggan menampilkan pesan khusus ambil di lapak 24 jam.
     */
    public function test_web_notifications_include_pickup_ready_for_owner_and_customer(): void
    {
        Order::factory()->create([
            'user_id'          => $this->customer1->id,
            'kode'             => 'KTT-20261124-0001',
            'status'           => 'diproses',
            'metode_pemenuhan' => 'ambil_sendiri',
            'sumber_transaksi' => 'online',
        ]);

        $ownerNotifRes = $this->actingAs($this->owner)->getJson('/api/notifications');
        $ownerNotifRes->assertStatus(200)->assertJsonPath('success', true);
        $ownerIds = collect($ownerNotifRes->json('data'))->pluck('id')->all();
        $this->assertContains('owner_pickup_ready', $ownerIds);

        $customerNotifRes = $this->actingAs($this->customer1)->getJson('/api/notifications');
        $customerNotifRes->assertStatus(200)->assertJsonPath('success', true);
        $firstMsg = $customerNotifRes->json('data.0.pesan');
        $this->assertStringContainsString('siap diambil di lapak (batas 24 jam)', $firstMsg);
    }

    /**
     * Bug Fix #4: Endpoint completePickup menolak pesanan ambil sendiri yang belum lunas (menunggu_pembayaran).
     */
    public function test_complete_pickup_rejects_unpaid_pickup_order(): void
    {
        $unpaidPickup = Order::factory()->create([
            'user_id'          => $this->customer1->id,
            'status'           => 'menunggu_pembayaran',
            'metode_pemenuhan' => 'ambil_sendiri',
            'sumber_transaksi' => 'online',
        ]);

        $res = $this->actingAs($this->owner)->postJson("/api/owner/orders/{$unpaidPickup->id}/complete-pickup");
        $res->assertStatus(422);
        $this->assertSame('menunggu_pembayaran', $unpaidPickup->fresh()->status);
    }

    /**
     * Audit Hak Akses (RBAC 3 Role): Memastikan isolasi ketat antar Guest, Pelanggan, Kurir, dan Owner.
     */
    public function test_strict_rbac_and_cross_user_isolation_across_all_roles(): void
    {
        $orderCustomer1 = Order::factory()->create([
            'user_id'          => $this->customer1->id,
            'courier_id'       => $this->courier1->id,
            'kode'             => 'KTT-20261124-0077',
            'status'           => 'dikirim',
            'metode_pemenuhan' => 'pengiriman',
            'sumber_transaksi' => 'online',
        ]);

        // 1. Pelanggan 2 tidak boleh melihat detail atau nota milik Pelanggan 1
        $this->actingAs($this->customer2)->get("/riwayat-pesanan/{$orderCustomer1->kode}")->assertStatus(403);
        $this->actingAs($this->customer2)->get("/nota/{$orderCustomer1->kode}")->assertStatus(403);
        $this->actingAs($this->customer2)->getJson("/api/orders/{$orderCustomer1->kode}")->assertStatus(403);

        // 2. Kurir 2 tidak boleh menyelesaikan pesanan yang ditugaskan ke Kurir 1
        $this->actingAs($this->courier2)->post("/kurir/orders/{$orderCustomer1->id}/complete")->assertStatus(403);

        // 3. Kurir tidak boleh membuka halaman Owner maupun halaman Pelanggan
        $this->actingAs($this->courier1)->get('/owner/dashboard')->assertStatus(403);
        $this->actingAs($this->courier1)->get('/owner/produk')->assertStatus(403);
        $this->actingAs($this->courier1)->get('/cart')->assertStatus(403);
        $this->actingAs($this->courier1)->get('/riwayat-pesanan')->assertStatus(403);
        $this->actingAs($this->courier1)->get("/nota/{$orderCustomer1->kode}")->assertStatus(403);

        // 4. Owner tidak boleh membuka halaman Kurir maupun halaman Pelanggan
        $this->actingAs($this->owner)->get('/kurir/tugas-baru')->assertStatus(403);
        $this->actingAs($this->owner)->get('/cart')->assertStatus(403);
        $this->actingAs($this->owner)->get('/checkout')->assertStatus(403);
        $this->actingAs($this->owner)->get('/profil-pelanggan')->assertStatus(403);

        // 5. Pelanggan tidak boleh mengakses endpoint Owner & Kurir
        $this->actingAs($this->customer1)->get('/owner/dashboard')->assertStatus(403);
        $this->actingAs($this->customer1)->get('/owner/produk')->assertStatus(403);
        $this->actingAs($this->customer1)->get('/kurir/tugas-baru')->assertStatus(403);
        $this->actingAs($this->customer1)->getJson('/api/owner/orders')->assertStatus(403);
    }
}
