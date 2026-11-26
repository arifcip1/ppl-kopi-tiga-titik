<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Setting;
use App\Models\ShippingLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * PPL-39 (Branch PPL-43): Verifikasi Penyempurnaan UI Responsif,
 * Notifikasi Web Global, Dokumentasi Fitur, & Kesiapan Demo.
 */
class Ppl39UiAndDemoReadinessTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $courier;
    private User $customer;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::create([
            'name' => 'Farrel Owner',
            'email' => 'owner@kopitigatitik.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $this->courier = User::create([
            'name' => 'Ilham Kurir',
            'email' => 'kurir@kopitigatitik.test',
            'password' => Hash::make('password123'),
            'role' => 'kurir',
        ]);

        $this->customer = User::create([
            'name' => 'Nabil Pelanggan',
            'email' => 'nabil@kopitigatitik.test',
            'password' => Hash::make('password123'),
            'role' => 'pelanggan',
        ]);

        $this->product = Product::create([
            'nama' => 'Titik Temu',
            'rasa' => 'Kopi Susu Gula Aren',
            'komposisi' => 'Espresso, susu segar, gula aren',
            'harga' => 10000,
            'stok' => 4,
            'aktif' => true,
        ]);

        $location = ShippingLocation::create([
            'nama_lokasi' => 'Lapak Jl. Kuwung',
            'alamat' => 'Jl. Kuwung, Kota Mojokerto',
            'latitude' => -7.472600,
            'longitude' => 112.438500,
            'aktif' => true,
        ]);

        Setting::updateOrCreate(['key' => 'lokasi_pengiriman_aktif_id'], ['value' => (string) $location->id]);
        Setting::updateOrCreate(['key' => 'jam_buka'], ['value' => '00:00']);
        Setting::updateOrCreate(['key' => 'jam_tutup'], ['value' => '23:59']);
        Setting::updateOrCreate(['key' => 'status_lapak'], ['value' => 'buka']);
    }

    public function test_owner_global_notification_bell_is_rendered_across_owner_pages(): void
    {
        foreach (['/owner/dashboard', '/owner/pos', '/owner/riwayat-penjualan', '/owner/produk'] as $url) {
            $response = $this->actingAs($this->owner)->get($url);
            $response->assertStatus(200);
            $response->assertSee('id="webNotificationWrapper"', false);
            $response->assertSee('id="webNotificationDropdown"', false);
            $response->assertSee('1 Menu Menipis');
        }
    }

    public function test_customer_layout_renders_favicon_and_notification_bell(): void
    {
        Order::factory()->create([
            'user_id' => $this->customer->id,
            'kode' => 'KTT-PPL39-0001',
            'status' => 'diproses',
            'metode_pemenuhan' => 'ambil_sendiri',
            'sumber_transaksi' => 'online',
        ]);

        $response = $this->actingAs($this->customer)->get('/profil-pelanggan');
        $response->assertStatus(200);
        $response->assertSee('images/logo.png', false);
        $response->assertSee('id="customerNotificationWrapper"', false);
        $response->assertSee('Status Pesanan #KTT-PPL39-0001');
    }

    public function test_pos_and_riwayat_penjualan_and_nota_digital_render_responsively(): void
    {
        $posRes = $this->actingAs($this->owner)->get('/owner/pos');
        $posRes->assertStatus(200)
            ->assertSee('id="mobilePosCartBar"', false)
            ->assertSee('id="posCartPanel"', false);

        $salesRes = $this->actingAs($this->owner)->get('/owner/riwayat-penjualan');
        $salesRes->assertStatus(200)
            ->assertSee('Akumulasi Online &amp; POS Lapak', false)
            ->assertSee('Pendapatan bersih tanpa ongkir');

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'kode' => 'KTT-NOTA-3901',
            'status' => 'selesai',
            'sumber_transaksi' => 'online',
            'subtotal' => 10000,
            'ongkir' => 0,
            'total' => 10000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'nama_produk' => $this->product->nama,
            'jumlah' => 1,
            'harga_satuan' => 10000,
            'subtotal' => 10000,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'metode_pembayaran' => 'qris',
            'status' => 'berhasil',
            'jumlah' => 10000,
            'waktu_verifikasi' => now(),
        ]);

        $notaRes = $this->actingAs($this->customer)->get(route('nota', $order->kode));
        $notaRes->assertStatus(200)
            ->assertSee('Unduh File PDF')
            ->assertSee('Unduh Gambar (PNG)')
            ->assertSee('Hubungi Penjual via WhatsApp');
    }

    public function test_courier_views_and_update_lokasi_render_without_errors(): void
    {
        $this->actingAs($this->courier)->get('/kurir/tugas-baru')
            ->assertStatus(200)
            ->assertSee('id="courierNotificationDropdown"', false);

        $this->actingAs($this->courier)->get('/kurir/selesai')
            ->assertStatus(200);

        $this->actingAs($this->owner)->get('/owner/update-lokasi')
            ->assertStatus(200)
            ->assertSee('Pengaturan Lokasi & Jam Lapak', false);
    }

    public function test_documentation_and_demo_assets_exist_and_readme_is_updated(): void
    {
        $this->assertFileExists(public_path('images/logo.png'));
        $this->assertFileExists(public_path('js/html2pdf.bundle.min.js'));
        $this->assertFileExists(base_path('docs/laporan_sm&qa/PPL_39_PENYEMPURNAAN_UI_DOKUMENTASI_DAN_KESIAPAN_DEMO.md'));

        $readme = file_get_contents(base_path('README.md'));
        $this->assertNotFalse($readme);
        $this->assertStringContainsString('Kopi Tiga Titik', $readme);
        $this->assertStringContainsString('OPENROUTESERVICE_API_KEY', $readme);
        $this->assertStringContainsString('php artisan migrate:fresh --seed', $readme);
    }
}
