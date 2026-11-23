<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\ShippingLocation;
use App\Models\User;
use App\Services\CheckoutException;
use App\Services\CheckoutService;
use App\Services\Shipping\OsmRouteShippingCostResolver;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * PPL-38: Pengujian Integrasi dan Regresi:
 * Katalog, Keranjang, Checkout, Ongkir OSM, Validasi Koordinat, Lokasi Aktif & Jam Operasional Lapak, serta Pilihan Ambil Sendiri.
 */
class Ppl38IntegrationAndRegressionTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $owner;
    private ShippingLocation $lapakAktif;
    private OsmRouteShippingCostResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        // Tetapkan waktu pengujian di dalam jam buka operasional: 16:00 WIB (Asia/Jakarta)
        Carbon::setTestNow(Carbon::today('Asia/Jakarta')->setTime(16, 0));

        config([
            'services.openrouteservice.api_key' => 'mock-ors-api-key-ppl38',
            'services.openrouteservice.base_url' => 'https://api.openrouteservice.org',
            'services.openrouteservice.timeout' => 5,
            'services.openrouteservice.profile' => 'driving-car',
        ]);

        $this->customer = User::factory()->create([
            'name' => 'Nizam Pelanggan',
            'email' => 'nizam@kopitigatitik.test',
            'role' => 'pelanggan',
        ]);

        $this->owner = User::factory()->create([
            'name' => 'Owner Kopi Tiga Titik',
            'email' => 'owner@kopitigatitik.test',
            'role' => 'admin',
        ]);

        $this->lapakAktif = ShippingLocation::create([
            'nama_lokasi' => 'Lapak Jl. Kuwung',
            'alamat' => 'Jl. Kuwung, Mergelo, Kec. Magersari, Kota Mojokerto',
            'latitude' => -7.472600,
            'longitude' => 112.438500,
            'aktif' => true,
        ]);

        Setting::updateOrCreate(['key' => 'lokasi_pengiriman_aktif_id'], ['value' => (string) $this->lapakAktif->id]);
        Setting::updateOrCreate(['key' => 'jam_buka'], ['value' => '15:00']);
        Setting::updateOrCreate(['key' => 'jam_tutup'], ['value' => '22:00']);
        Setting::updateOrCreate(['key' => 'status_operasional_manual'], ['value' => 'buka']);

        $this->resolver = app(OsmRouteShippingCostResolver::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * Helper untuk membuat produk valid dengan komposisi.
     */
    private function createProduct(array $attributes = []): Product
    {
        return Product::create(array_merge([
            'nama' => 'Titik Hitam',
            'rasa' => 'Americano',
            'komposisi' => 'Biji kopi pilihan dan air murni',
            'harga' => 8000,
            'stok' => 10,
            'aktif' => true,
        ], $attributes));
    }

    /**
     * 1. KATALOG & STATUS STOK REAL-TIME (PPL 10–11, BL-6, US-9, US-11).
     */
    public function test_catalog_stock_and_operational_banner(): void
    {
        $this->createProduct([
            'nama' => 'Titik Hitam',
            'rasa' => 'Americano',
            'harga' => 8000,
            'stok' => 10,
            'aktif' => true,
        ]);

        $this->createProduct([
            'nama' => 'Titik Manja',
            'rasa' => 'Kopi Susu Caramel',
            'harga' => 10000,
            'stok' => 3,
            'aktif' => true,
        ]);

        $this->createProduct([
            'nama' => 'Titik Temu',
            'rasa' => 'Kopi Susu Gula Aren',
            'harga' => 10000,
            'stok' => 0,
            'aktif' => true,
        ]);

        $this->createProduct([
            'nama' => 'Titik Rahasia',
            'rasa' => 'Eksperimen',
            'harga' => 15000,
            'stok' => 5,
            'aktif' => false,
        ]);

        $response = $this->get(route('katalog.index'));
        $response->assertStatus(200);

        // Produk aktif tampil
        $response->assertSee('Titik Hitam');
        $response->assertSee('Titik Manja');
        $response->assertSee('Titik Temu');

        // Produk tidak aktif tidak tampil
        $response->assertDontSee('Titik Rahasia');

        // Banner lapak aktif tampil dengan jam operasional
        $response->assertSee('Lapak: Lapak Jl. Kuwung');
        $response->assertSee('15:00 - 22:00 WIB');
        $response->assertSee('Buka');

        // Indikator stok
        $response->assertSee('Stok: 10 botol');
        $response->assertSee('Sisa 3 botol!');
        $response->assertSee('Stok Habis');
    }

    /**
     * 2. KERANJANG BELANJA (PPL 13–14, BL-9, US-10).
     */
    public function test_cart_management_and_stock_validation(): void
    {
        $product = $this->createProduct([
            'nama' => 'Titik Hitam',
            'stok' => 5,
        ]);

        // Tambah ke keranjang valid (JSON response)
        $response = $this->actingAs($this->customer)->postJson(route('cart.items.store'), [
            'product_id' => $product->id,
            'jumlah' => 2,
        ]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->customer->id,
            'product_id' => $product->id,
            'jumlah' => 2,
        ]);

        // Tambah melebihi stok ditolak (HTTP 422)
        $responseExceed = $this->actingAs($this->customer)->postJson(route('cart.items.store'), [
            'product_id' => $product->id,
            'jumlah' => 4, // 2 + 4 = 6 > 5 stok
        ]);
        $responseExceed->assertStatus(422);

        // Update jumlah di keranjang
        $cartItem = CartItem::where('user_id', $this->customer->id)->first();
        $this->actingAs($this->customer)->patch(route('cart.items.update', $cartItem->id), [
            'jumlah' => 4,
        ]);
        $this->assertEquals(4, $cartItem->fresh()->jumlah);

        // Hapus item dari keranjang
        $this->actingAs($this->customer)->delete(route('cart.items.destroy', $cartItem->id));
        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }

    /**
     * 3. GEOFENCE BOUNDING BOX KOTA MOJOKERTO (PPL 25–26, BL-11, FR-7.5).
     */
    public function test_geofence_mojokerto_boundary_validation(): void
    {
        // Titik Dalam Kota Mojokerto (Valid: dekat Alun-Alun / Jl. Majapahit)
        $insideLat = -7.472200;
        $insideLng = 112.433900;
        $this->assertTrue($this->resolver->isInsideMojokertoCity($insideLat, $insideLng));

        // Titik Luar Kota Mojokerto (Surabaya: Ditolak)
        $outsideLat = -7.257500;
        $outsideLng = 112.752100;
        $this->assertFalse($this->resolver->isInsideMojokertoCity($outsideLat, $outsideLng));

        // Request quote shipping ke luar Mojokerto mengembalikan error 422
        $response = $this->actingAs($this->customer)->postJson('/api/shipping/quote', [
            'lat' => $outsideLat,
            'lng' => $outsideLng,
            'metode_pemenuhan' => 'pengiriman',
        ]);
        $response->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonFragment(['message' => 'Titik lokasi tujuan berada di luar jangkauan wilayah Kota Mojokerto.']);
    }

    /**
     * 4. ONGKIR OSM TIERED & GRATIS ONGKIR PROMO (PPL 25–26, BL-11, BR-14).
     */
    public function test_osm_tiered_shipping_calculation(): void
    {
        // Mock ORS response untuk jarak <= 5.5 km (Misal 3.2 km -> 3200 meter) dan > 5.5 km (6.4 km -> 6400 meter)
        Http::fake([
            'https://api.openrouteservice.org/v2/directions/driving-car' => Http::sequence()
                ->push([
                    'features' => [
                        [
                            'properties' => [
                                'summary' => [
                                    'distance' => 3200.0,
                                ],
                            ],
                        ],
                    ],
                ], 200)
                ->push([
                    'features' => [
                        [
                            'properties' => [
                                'summary' => [
                                    'distance' => 6400.0, // 6.4 km -> > 5.5 km tier 1 (Rp 2.000)
                                ],
                            ],
                        ],
                    ],
                ], 200),
        ]);

        // Jarak 3.2 km -> Rp 0 (Gratis Promo)
        $quotePromo = $this->resolver->calculateQuote(-7.4722, 112.4339, 'pengiriman');
        $this->assertEquals(0, $quotePromo['ongkir']);
        $this->assertTrue($quotePromo['is_gratis']);
        $this->assertEquals(3.2, $quotePromo['jarak_km']);

        // Jarak 6.4 km -> Rp 2.000 (Tier 1)
        $quoteTiered = $this->resolver->calculateQuote(-7.4850, 112.4450, 'pengiriman');
        $this->assertEquals(2000, $quoteTiered['ongkir']);
        $this->assertFalse($quoteTiered['is_gratis']);
        $this->assertEquals(6.4, $quoteTiered['jarak_km']);
    }

    /**
     * 5. AMBIL SENDIRI DI LAPAK (SELF PICKUP 24 JAM, ONGKIR RP0) (PPL 23–24, BL-19, US-13, US-29).
     */
    public function test_self_pickup_flow_with_zero_shipping_and_24h_deadline(): void
    {
        $product = $this->createProduct([
            'nama' => 'Titik Temu',
            'harga' => 10000,
            'stok' => 10,
        ]);

        CartItem::create([
            'user_id' => $this->customer->id,
            'product_id' => $product->id,
            'jumlah' => 2,
        ]);

        $checkoutService = app(CheckoutService::class);

        // Eksekusi checkout ambil sendiri
        $order = $checkoutService->createFromCart($this->customer, [
            'nama_penerima' => 'Nizam Pelanggan',
            'telepon' => '081234567890',
            'alamat' => 'Ambil Sendiri di Lapak Jl. Kuwung',
            'metode_pemenuhan' => 'ambil_sendiri',
            'catatan' => 'Ambil sore jam 5',
        ]);

        $this->assertEquals('ambil_sendiri', $order->metode_pemenuhan);
        $this->assertEquals(0, $order->ongkir);
        $this->assertEquals(20000, $order->total);

        // Snapshot lapak aktif tersimpan
        $this->assertEquals('Lapak Jl. Kuwung', $order->nama_lokasi_asal);
        $this->assertNotNull($order->batas_pengambilan);

        // Batas pengambilan adalah 24 jam ke depan
        $expectedDeadline = Carbon::now('Asia/Jakarta')->addHours(24);
        $this->assertEquals(
            $expectedDeadline->format('Y-m-d H:i'),
            $order->batas_pengambilan->format('Y-m-d H:i')
        );
    }

    /**
     * 6. PROTEKSI JAM OPERASIONAL LAPAK (PPL 25–26, BL-18, US-28, BR-10).
     */
    public function test_checkout_rejected_when_lapak_is_closed(): void
    {
        // Ubah jam pengujian di luar jam operasional: Pukul 23:30 malam (Lapak tutup 22:00)
        Carbon::setTestNow(Carbon::today('Asia/Jakarta')->setTime(23, 30));

        $product = $this->createProduct([
            'nama' => 'Titik Hitam',
            'harga' => 8000,
            'stok' => 5,
        ]);

        CartItem::create([
            'user_id' => $this->customer->id,
            'product_id' => $product->id,
            'jumlah' => 1,
        ]);

        $checkoutService = app(CheckoutService::class);

        $this->expectException(CheckoutException::class);
        $this->expectExceptionMessage('Lapak Kopi Tiga Titik saat ini sedang tutup');

        $checkoutService->createFromCart($this->customer, [
            'nama_penerima' => 'Nizam Pelanggan',
            'telepon' => '081234567890',
            'alamat' => 'Jl. Kuwung No. 12',
            'lat' => -7.4726,
            'lng' => 112.4385,
            'metode_pemenuhan' => 'pengiriman',
        ]);
    }
}
