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
use App\Services\Shipping\OsmRoutingService;
use App\Services\Shipping\ShippingCostUnavailableException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OsmRouteShippingTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private ShippingLocation $lapakAktif;
    private OsmRoutingService $routingService;
    private OsmRouteShippingCostResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        // Tetapkan jam pengujian di dalam jam operasional 15:00 - 18:00 WIB (BR-10, FR-11.6)
        \Carbon\Carbon::setTestNow(\Carbon\Carbon::today('Asia/Jakarta')->setTime(16, 0));

        config([
            'services.openrouteservice.api_key'  => 'mock-ors-test-key-12345',
            'services.openrouteservice.base_url' => 'https://api.openrouteservice.org',
            'services.openrouteservice.timeout'  => 5,
            'services.openrouteservice.profile'  => 'driving-car',
        ]);

        $this->customer = User::factory()->create([
            'email' => 'pelanggan@kopitigatitik.test',
            'role'  => 'pelanggan',
        ]);

        $this->lapakAktif = ShippingLocation::create([
            'nama_lokasi' => 'Lapak Jl. Kuwung',
            'alamat'      => 'Jl. Kuwung, Mergelo, Kec. Magersari, Kota Mojokerto',
            'latitude'    => -7.472600,
            'longitude'   => 112.438500,
            'aktif'       => true,
        ]);

        Setting::updateOrCreate(['key' => 'lokasi_pengiriman_aktif_id'], ['value' => (string) $this->lapakAktif->id]);
        Setting::updateOrCreate(['key' => 'jam_buka'], ['value' => '15:00']);
        Setting::updateOrCreate(['key' => 'jam_tutup'], ['value' => '18:00']);
        Setting::updateOrCreate(['key' => 'status_lapak'], ['value' => 'buka']);

        $this->routingService = new OsmRoutingService();
        $this->resolver = new OsmRouteShippingCostResolver($this->routingService);
    }

    protected function tearDown(): void
    {
        \Carbon\Carbon::setTestNow();
        parent::tearDown();
    }

    // =========================================================================
    // 1. PENGUJIAN ORS ROUTING & FORMULA ONGKIR BERTINGKAT (FR-7.2, FR-7.3, BR-14)
    // =========================================================================

    public function test_ors_routing_calculates_distance_and_orders_coordinates_as_geojson_lon_lat(): void
    {
        Http::fake([
            'https://api.openrouteservice.org/v2/directions/driving-car' => Http::response([
                'type' => 'FeatureCollection',
                'features' => [
                    [
                        'properties' => [
                            'summary' => [
                                'distance' => 3200.0, // 3.2 km
                                'duration' => 450.0,
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $destLat = -7.465000;
        $destLng = 112.435000;

        $distance = $this->routingService->getRouteDistance(
            (float) $this->lapakAktif->latitude,
            (float) $this->lapakAktif->longitude,
            $destLat,
            $destLng
        );

        $this->assertEquals(3.20, $distance);

        Http::assertSent(function ($request) use ($destLat, $destLng) {
            $data = $request->data();
            $originLonLat = $data['coordinates'][0];
            $destLonLat = $data['coordinates'][1];

            return $request->url() === 'https://api.openrouteservice.org/v2/directions/driving-car'
                && $request->header('Authorization')[0] === 'mock-ors-test-key-12345'
                && $originLonLat[0] == (float) $this->lapakAktif->longitude
                && $originLonLat[1] == (float) $this->lapakAktif->latitude
                && $destLonLat[0] == $destLng
                && $destLonLat[1] == $destLat;
        });
    }

    public function test_tiered_ongkir_distance_within_5_point_5_km_is_free(): void
    {
        Http::fake([
            'https://api.openrouteservice.org/v2/directions/driving-car' => Http::response([
                'features' => [
                    ['properties' => ['summary' => ['distance' => 5450.0]]], // 5.45 km <= 5.5 km
                ],
            ], 200),
        ]);

        $quote = $this->resolver->calculateQuote(-7.465000, 112.435000, 'pengiriman');

        $this->assertSame(0, $quote['ongkir']);
        $this->assertTrue($quote['is_gratis']);
        $this->assertEquals(5.45, $quote['jarak_km']);
        $this->assertStringContainsString('Gratis', $quote['ongkir_formatted']);
    }

    public function test_tiered_ongkir_distance_above_5_point_5_km_first_tier(): void
    {
        // Jarak 5.8 km: d > 5.5 km -> floor(5.8 - 5.5) + 1 = 1 -> Rp 2.000 * 1 = Rp 2.000
        Http::fake([
            'https://api.openrouteservice.org/v2/directions/driving-car' => Http::response([
                'features' => [
                    ['properties' => ['summary' => ['distance' => 5800.0]]],
                ],
            ], 200),
        ]);

        $quote = $this->resolver->calculateQuote(-7.465000, 112.435000, 'pengiriman');

        $this->assertSame(2000, $quote['ongkir']);
        $this->assertFalse($quote['is_gratis']);
        $this->assertEquals(5.80, $quote['jarak_km']);
        $this->assertEquals('Rp 2.000', $quote['ongkir_formatted']);
    }

    public function test_tiered_ongkir_distance_above_5_point_5_km_second_tier(): void
    {
        // Jarak 7.2 km: d > 5.5 km -> floor(7.2 - 5.5) + 1 = 2 -> Rp 2.000 * 2 = Rp 4.000
        Http::fake([
            'https://api.openrouteservice.org/v2/directions/driving-car' => Http::response([
                'features' => [
                    ['properties' => ['summary' => ['distance' => 7200.0]]],
                ],
            ], 200),
        ]);

        $quote = $this->resolver->calculateQuote(-7.465000, 112.435000, 'pengiriman');
        $this->assertSame(4000, $quote['ongkir']);
        $this->assertEquals(7.20, $quote['jarak_km']);
        $this->assertEquals('Rp 4.000', $quote['ongkir_formatted']);
    }

    // =========================================================================
    // 2. PENGUJIAN PENANGANAN ERROR & TIMEOUT ORS (PPL-26 / BL-19)
    // =========================================================================

    public function test_ors_quota_exceeded_throws_unavailable_exception(): void
    {
        Http::fake([
            'https://api.openrouteservice.org/v2/directions/driving-car' => Http::response([
                'error' => ['message' => 'Rate limit exceeded'],
            ], 429),
        ]);

        $this->expectException(ShippingCostUnavailableException::class);
        $this->expectExceptionMessage('Batas kuota layanan rute jalan telah terlampaui');

        $this->routingService->getRouteDistance(-7.472600, 112.438500, -7.465000, 112.435000);
    }

    public function test_ors_unauthorized_key_throws_unavailable_exception(): void
    {
        Http::fake([
            'https://api.openrouteservice.org/v2/directions/driving-car' => Http::response([
                'error' => ['message' => 'Unauthorized'],
            ], 401),
        ]);

        $this->expectException(ShippingCostUnavailableException::class);
        $this->expectExceptionMessage('Otorisasi layanan rute jalan tidak valid');

        $this->routingService->getRouteDistance(-7.472600, 112.438500, -7.465000, 112.435000);
    }

    public function test_ors_timeout_or_connection_failure_throws_unavailable_exception(): void
    {
        Http::fake([
            'https://api.openrouteservice.org/v2/directions/driving-car' => function () {
                throw new ConnectionException('Connection timed out after 5000ms');
            },
        ]);

        $this->expectException(ShippingCostUnavailableException::class);
        $this->expectExceptionMessage('Layanan rute jalan sedang mengalami timeout atau gangguan koneksi');

        $this->routingService->getRouteDistance(-7.472600, 112.438500, -7.465000, 112.435000);
    }

    public function test_missing_api_key_without_simulation_throws_unavailable_exception(): void
    {
        $service = (new OsmRoutingService())->setApiKey('')->setAllowSimulation(false);

        $this->expectException(ShippingCostUnavailableException::class);
        $this->expectExceptionMessage('Fitur perhitungan rute pengiriman belum dikonfigurasi');

        $service->getRouteDistance(-7.472600, 112.438500, -7.465000, 112.435000);
    }

    // =========================================================================
    // 3. PENGUJIAN VALIDASI WILAYAH & STATUS LAPAK (FR-7.5, FR-11.6)
    // =========================================================================

    public function test_destination_outside_mojokerto_boundary_is_rejected_at_quote_and_endpoint(): void
    {
        // Surabaya (luar Mojokerto)
        $outsideLat = -7.257500;
        $outsideLng = 112.752100;

        $response = $this->actingAs($this->customer)
            ->postJson('/api/shipping/quote', [
                'metode_pemenuhan' => 'pengiriman',
                'lat'              => $outsideLat,
                'lng'              => $outsideLng,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['lokasi_tujuan'])
            ->assertJsonFragment([
                'message' => 'Titik lokasi tujuan berada di luar jangkauan wilayah Kota Mojokerto.',
            ]);
    }

    public function test_closed_lapak_rejects_quote_calculation(): void
    {
        Setting::updateOrCreate(['key' => 'status_lapak'], ['value' => 'tutup']);

        $response = $this->actingAs($this->customer)
            ->postJson('/api/shipping/quote', [
                'metode_pemenuhan' => 'pengiriman',
                'lat'              => -7.465000,
                'lng'              => 112.435000,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['lokasi_tujuan'])
            ->assertJsonFragment([
                'message' => 'Lapak Kopi Tiga Titik saat ini sedang tutup. Pemesanan pengantaran kurir tidak dapat diproses.',
            ]);
    }

    public function test_closed_lapak_rejects_checkout_in_checkout_service(): void
    {
        Setting::updateOrCreate(['key' => 'status_lapak'], ['value' => 'tutup']);

        $product = Product::factory()->create(['stok' => 10, 'aktif' => true]);
        CartItem::create([
            'user_id'    => $this->customer->id,
            'product_id' => $product->id,
            'jumlah'     => 1,
        ]);

        $checkoutService = $this->app->make(CheckoutService::class);

        $this->expectException(CheckoutException::class);
        $this->expectExceptionMessage('Lapak Kopi Tiga Titik saat ini sedang tutup');

        $checkoutService->createFromCart($this->customer, [
            'metode_pemenuhan' => 'pengiriman',
            'nama_penerima'    => 'Pelanggan Test',
            'telepon'          => '081234567890',
            'alamat'           => 'Jl. Majapahit No. 5, Mojokerto',
            'lat'              => -7.465000,
            'lng'              => 112.435000,
        ]);
    }

    // =========================================================================
    // 4. PENGUJIAN SNAPSHOT LOKASI ASAL & JARAK PADA ORDER (FR-11.7, FR-7.7, BL-19)
    // =========================================================================

    public function test_checkout_delivery_persists_active_lapak_snapshot_and_route_distance(): void
    {
        Http::fake([
            'https://api.openrouteservice.org/v2/directions/driving-car' => Http::response([
                'features' => [
                    ['properties' => ['summary' => ['distance' => 6200.0]]], // 6.2 km -> Rp 2.000
                ],
            ], 200),
        ]);

        $product = Product::factory()->create([
            'nama'  => 'Titik Temu',
            'harga' => 15000,
            'stok'  => 10,
            'aktif' => true,
        ]);

        CartItem::create([
            'user_id'    => $this->customer->id,
            'product_id' => $product->id,
            'jumlah'     => 2,
        ]);

        $checkoutService = $this->app->make(CheckoutService::class);

        $order = $checkoutService->createFromCart($this->customer, [
            'metode_pemenuhan' => 'pengiriman',
            'nama_penerima'    => 'Budi Santoso',
            'telepon'          => '081298765432',
            'alamat'           => 'Jl. Pahlawan No. 20, Mojokerto',
            'lat'              => -7.465000,
            'lng'              => 112.435000,
        ]);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals('pengiriman', $order->metode_pemenuhan);
        $this->assertEquals(2000, $order->ongkir);
        $this->assertEquals(30000, $order->subtotal);
        $this->assertEquals(32000, $order->total);

        // Snapshot lokasi lapak asal
        $this->assertEquals($this->lapakAktif->nama_lokasi, $order->nama_lokasi_asal);
        $this->assertEquals($this->lapakAktif->alamat, $order->alamat_lokasi_asal);
        $this->assertEquals((float) $this->lapakAktif->latitude, (float) $order->latitude_lokasi_asal);
        $this->assertEquals((float) $this->lapakAktif->longitude, (float) $order->longitude_lokasi_asal);

        // Snapshot jarak rute jalan pengiriman
        $this->assertEquals(6.20, (float) $order->jarak_pengiriman);
        $this->assertNull($order->batas_pengambilan);
    }

    public function test_checkout_pickup_persists_zero_ongkir_and_24h_batas_pengambilan(): void
    {
        $product = Product::factory()->create([
            'nama'  => 'Titik Hitam',
            'harga' => 12000,
            'stok'  => 5,
            'aktif' => true,
        ]);

        CartItem::create([
            'user_id'    => $this->customer->id,
            'product_id' => $product->id,
            'jumlah'     => 1,
        ]);

        $checkoutService = $this->app->make(CheckoutService::class);

        $order = $checkoutService->createFromCart($this->customer, [
            'metode_pemenuhan' => 'ambil_sendiri',
            'nama_penerima'    => 'Siti Aminah',
            'telepon'          => '081211112222',
            'alamat'           => 'Ambil Sendiri di Lapak',
        ]);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals('ambil_sendiri', $order->metode_pemenuhan);
        $this->assertEquals(0, $order->ongkir);
        $this->assertEquals(12000, $order->subtotal);
        $this->assertEquals(12000, $order->total);

        // Snapshot asal & jarak
        $this->assertEquals($this->lapakAktif->nama_lokasi, $order->nama_lokasi_asal);
        $this->assertEquals(0.0, (float) $order->jarak_pengiriman);

        // Batas pengambilan mandiri 24 jam (FR-11.7)
        $this->assertNotNull($order->batas_pengambilan);
        $this->assertTrue(
            now()->addHours(23)->lt($order->batas_pengambilan) &&
            now()->addHours(25)->gt($order->batas_pengambilan)
        );
    }
}

