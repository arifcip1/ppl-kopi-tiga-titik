<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Setting;
use App\Models\ShippingLocation;
use App\Models\User;
use App\Services\LapakOperationalService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QaSprint3FixesTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $customer;
    protected Product $product;
    protected ShippingLocation $lapak;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create([
            'name'  => 'Owner Kopi',
            'email' => 'owner-qa@kopitigatitik.test',
            'role'  => 'admin',
        ]);

        $this->customer = User::factory()->create([
            'name'  => 'Nabil Pelanggan',
            'email' => 'nabil-qa@kopitigatitik.test',
            'role'  => 'pelanggan',
        ]);

        $this->product = Product::factory()->create([
            'nama'  => 'Titik Hitam',
            'harga' => 8000,
            'stok'  => 20,
            'aktif' => true,
        ]);

        $this->lapak = ShippingLocation::create([
            'nama_lokasi' => 'Lapak Gang Mbah Bakung',
            'alamat'      => 'Gang Mbah Bakung, Kedundung, Kec. Magersari, Kota Mojokerto',
            'latitude'    => -7.472600,
            'longitude'   => 112.438500,
            'aktif'       => true,
        ]);

        Setting::updateOrCreate(['key' => 'lokasi_pengiriman_aktif_id'], ['value' => (string) $this->lapak->id]);
        Setting::updateOrCreate(['key' => 'jam_buka'], ['value' => '00:10']);
        Setting::updateOrCreate(['key' => 'jam_tutup'], ['value' => '01:00']);
        Setting::updateOrCreate(['key' => 'status_lapak'], ['value' => 'buka']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * TC-LOC-02: Halaman Update Lokasi Lapak memakai modal template brand (bukan native alert browser).
     */
    public function test_update_lokasi_page_uses_custom_template_alert_modal_instead_of_native_alert(): void
    {
        $response = $this->actingAs($this->owner)->get(route('owner.update-lokasi'));

        $response->assertStatus(200);
        $response->assertSee('modal-lapak-alert');
        $response->assertSee('openLapakAlertModal');
        $response->assertDontSee("alert('Titik koordinat lapak berada di luar wilayah Kota Mojokerto!");
    }

    /**
     * TC-LOC-03: Status lapak otomatis berubah menjadi TUTUP jika waktu saat ini melewati jam_tutup.
     */
    public function test_lapak_status_automatically_becomes_tutup_when_current_time_passes_jam_tutup(): void
    {
        // Set waktu sistem ke 01:06 WIB (melewati jam operasional 00:10 - 01:00 WIB)
        Carbon::setTestNow(Carbon::create(2026, 11, 20, 1, 6, 0, 'Asia/Jakarta'));

        $service = app(LapakOperationalService::class);
        $status = $service->getStatus();

        $this->assertFalse($status['is_open']);
        $this->assertTrue($status['is_outside_hours']);
        $this->assertEquals('tutup', $status['effective_status']);
        $this->assertEquals('outside_hours', $status['closed_reason']);

        // Dashboard Owner harus menampilkan status Tutup (Di Luar Jam)
        $dashboard = $this->actingAs($this->owner)->get(route('owner.dashboard'));
        $dashboard->assertStatus(200);
        $dashboard->assertSee('Tutup (Di Luar Jam)');
    }

    /**
     * TC-LOC-04: Saat lapak tutup / di luar jam operasional, error_type = lapak_closed & blokir delivery + ambil_sendiri.
     */
    public function test_closed_or_outside_hours_lapak_blocks_both_delivery_and_pickup_with_correct_error(): void
    {
        // Waktu di luar jam operasional 00:10 - 01:00 WIB
        Carbon::setTestNow(Carbon::create(2026, 11, 20, 1, 6, 0, 'Asia/Jakarta'));

        // 1. Quote delivery ditolak dengan error_type = lapak_closed (bukan geofence)
        $deliveryQuote = $this->actingAs($this->customer)->postJson('/api/shipping/quote', [
            'metode_pemenuhan' => 'pengiriman',
            'lat'              => -7.472100,
            'lng'              => 112.434000,
        ]);

        $deliveryQuote->assertStatus(422)
            ->assertJson([
                'success'    => false,
                'error_type' => 'lapak_closed',
            ]);

        // 2. Quote ambil_sendiri juga ditolak sesuai PRD FR-11.6 & BR-10
        $pickupQuote = $this->actingAs($this->customer)->postJson('/api/shipping/quote', [
            'metode_pemenuhan' => 'ambil_sendiri',
        ]);

        $pickupQuote->assertStatus(422)
            ->assertJson([
                'success'    => false,
                'error_type' => 'lapak_closed',
            ]);

        // 3. Halaman checkout menampilkan banner Lapak Tutup (bukan judul di luar Mojokerto)
        CartItem::create([
            'user_id'    => $this->customer->id,
            'product_id' => $this->product->id,
            'jumlah'     => 1,
        ]);

        $checkoutPage = $this->actingAs($this->customer)->get(route('checkout'));
        $checkoutPage->assertStatus(200);
        $checkoutPage->assertSee('Lapak Kopi Tiga Titik Sedang Tutup');
        $checkoutPage->assertSee('Lapak Sedang Tutup');

        // 4. Submit checkout ambil_sendiri di luar jam operasional wajib ditolak backend
        $submitPickup = $this->actingAs($this->customer)->postJson(route('api.checkout.store'), [
            'metode_pemenuhan' => 'ambil_sendiri',
            'nama_penerima'    => 'Nabil Pelanggan',
            'telepon'          => '081234567890',
            'alamat'           => 'Ambil Sendiri di Lapak',
        ]);

        $submitPickup->assertStatus(422);
    }

    /**
     * TC-CHK-01: Pelanggan dapat mengubah alamat pengiriman utama di profil dan otomatis dipakai di checkout.
     */
    public function test_customer_can_update_default_address_in_profile_and_syncs_to_checkout(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 11, 20, 0, 30, 0, 'Asia/Jakarta'));

        // 1. Update alamat di luar Mojokerto ditolak
        $invalidRes = $this->actingAs($this->customer)->put(route('profile.address.update'), [
            'label'   => 'Rumah Surabaya',
            'alamat'  => 'Jl. Kenjeran No. 1, Surabaya',
            'patokan' => 'Dekat jembatan',
            'lat'     => -7.241916,
            'lng'     => 112.758636,
        ]);
        $invalidRes->assertSessionHasErrors('lat');

        // 2. Update alamat valid di dalam Kota Mojokerto berhasil disimpan ke settings
        $validRes = $this->actingAs($this->customer)->put(route('profile.address.update'), [
            'label'   => 'Kost Magersari',
            'alamat'  => 'Jl. Majapahit No. 88, Kranggan, Kota Mojokerto',
            'patokan' => 'Gerbang biru sebelah fotokopi',
            'lat'     => -7.468500,
            'lng'     => 112.435200,
        ]);
        $validRes->assertRedirect(route('profile'));
        $validRes->assertSessionHas('success');

        // 3. Halaman profil menampilkan alamat baru tersebut
        $profilePage = $this->actingAs($this->customer)->get(route('profile'));
        $profilePage->assertStatus(200);
        $profilePage->assertSee('Kost Magersari');
        $profilePage->assertSee('Jl. Majapahit No. 88, Kranggan, Kota Mojokerto');
        $profilePage->assertSee('Gerbang biru sebelah fotokopi');

        // 4. Halaman checkout otomatis memakai titik & alamat default dari profil
        CartItem::create([
            'user_id'    => $this->customer->id,
            'product_id' => $this->product->id,
            'jumlah'     => 1,
        ]);

        $checkoutPage = $this->actingAs($this->customer)->get(route('checkout'));
        $checkoutPage->assertStatus(200);
        $checkoutPage->assertSee('Jl. Majapahit No. 88, Kranggan, Kota Mojokerto');
        $checkoutPage->assertSee('-7.4685');
        $checkoutPage->assertSee('112.4352');
    }

    /**
     * TC-OWN-04: POS Mini Owner menyediakan aksi unduh & cetak nota resmi pelanggan.
     */
    public function test_pos_page_has_download_and_print_receipt_actions_and_owner_can_open_pos_nota(): void
    {
        $posPage = $this->actingAs($this->owner)->get(route('owner.pos'));
        $posPage->assertStatus(200);
        $posPage->assertSee('downloadPosReceiptPng');
        $posPage->assertSee('btnOpenOfficialNota');
        $posPage->assertSee('Unduh Nota');

        // Buat order POS dan pastikan Owner dapat membuka halaman Nota Digital resminya untuk cetak/PDF
        $posOrder = Order::factory()->create([
            'kode'             => 'POS-20261120-0001',
            'sumber_transaksi' => 'pos',
            'metode_pemenuhan' => null,
            'user_id'          => null,
            'nama_pelanggan'   => 'Pelanggan Lapak',
            'status'           => 'selesai',
            'subtotal'         => 16000,
            'ongkir'           => 0,
            'total'            => 16000,
        ]);

        OrderItem::create([
            'order_id'     => $posOrder->id,
            'product_id'   => $this->product->id,
            'nama_produk'  => $this->product->nama,
            'jumlah'       => 2,
            'harga_satuan' => 8000,
            'subtotal'     => 16000,
        ]);

        Payment::create([
            'order_id'          => $posOrder->id,
            'metode_pembayaran' => 'tunai',
            'status'            => 'berhasil',
            'jumlah'            => 16000,
            'waktu_verifikasi'  => now(),
        ]);

        $notaPage = $this->actingAs($this->owner)->get(route('nota', $posOrder->kode));
        $notaPage->assertStatus(200);
        $notaPage->assertSee('POS-20261120-0001');
        $notaPage->assertSee('LUNAS (TUNAI LAPAK)');
        $notaPage->assertSee('Unduh File PDF');
    }

    /**
     * TC-OWN-06: Tombol Serahkan (Selesai) di Dashboard Owner memakai modal konfirmasi template (bukan native confirm).
     */
    public function test_owner_dashboard_pickup_completion_uses_custom_template_modal(): void
    {
        Order::factory()->create([
            'user_id'          => $this->customer->id,
            'status'           => 'diproses',
            'metode_pemenuhan' => 'ambil_sendiri',
            'sumber_transaksi' => 'online',
        ]);

        $dashboard = $this->actingAs($this->owner)->get(route('owner.dashboard'));
        $dashboard->assertStatus(200);
        $dashboard->assertSee('modal-complete-pickup-confirm');
        $dashboard->assertSee('openCompletePickupModal');
        $dashboard->assertDontSee("onclick=\"return confirm('Konfirmasi serahkan pesanan ini kepada pembeli di lapak?')\"");
    }
}
