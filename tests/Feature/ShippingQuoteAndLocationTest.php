<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\Setting;
use App\Models\ShippingLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShippingQuoteAndLocationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $customer;
    private ShippingLocation $defaultLocation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create([
            'email' => 'owner@kopitigatitik.com',
            'role'  => 'admin',
        ]);

        $this->customer = User::factory()->create([
            'email' => 'pelanggan@kopitigatitik.com',
            'role'  => 'pelanggan',
        ]);

        $this->defaultLocation = ShippingLocation::create([
            'nama_lokasi' => 'Lapak Jl. Kuwung',
            'alamat'      => 'Jl. Kuwung, Mergelo, Kec. Magersari, Kota Mojokerto',
            'latitude'    => -7.472600,
            'longitude'   => 112.438500,
            'aktif'       => true,
        ]);

        Setting::updateOrCreate(['key' => 'lokasi_pengiriman_aktif_id'], ['value' => (string) $this->defaultLocation->id]);
        Setting::updateOrCreate(['key' => 'jam_buka'], ['value' => '15:00']);
        Setting::updateOrCreate(['key' => 'jam_tutup'], ['value' => '18:00']);
        Setting::updateOrCreate(['key' => 'status_lapak'], ['value' => 'buka']);
    }

    // =========================================================================
    // 1. PENGUJIAN API QUOTE ONGKIR (POST /api/shipping/quote)
    // =========================================================================

    public function test_quote_delivery_within_promo_distance_returns_zero_ongkir(): void
    {
        $response = $this->actingAs($this->customer)
            ->postJson('/api/shipping/quote', [
                'metode_pemenuhan' => 'pengiriman',
                'lat'              => -7.472800,
                'lng'              => 112.438800,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'metode_pemenuhan' => 'pengiriman',
                    'ongkir'           => 0,
                    'is_gratis'        => true,
                ],
            ]);
    }

    public function test_quote_delivery_outside_promo_distance_returns_tiered_ongkir(): void
    {
        // Koordinat di ujung utara Kota Mojokerto (sekitar 5.8 km jalan dari Jl. Kuwung)
        $response = $this->actingAs($this->customer)
            ->postJson('/api/shipping/quote', [
                'metode_pemenuhan' => 'pengiriman',
                'lat'              => -7.430000,
                'lng'              => 112.438500,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'metode_pemenuhan' => 'pengiriman',
                    'is_gratis'        => false,
                ],
            ]);

        $this->assertGreaterThan(0, $response->json('data.ongkir'));
    }

    public function test_quote_delivery_outside_mojokerto_returns_422_geofence_error(): void
    {
        // Koordinat Kota Surabaya (di luar bounding box Kota Mojokerto)
        $response = $this->actingAs($this->customer)
            ->postJson('/api/shipping/quote', [
                'metode_pemenuhan' => 'pengiriman',
                'lat'              => -7.257500,
                'lng'              => 112.752100,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonValidationErrors(['lokasi_tujuan']);
    }

    public function test_quote_pickup_returns_zero_ongkir_and_lapak_info(): void
    {
        $response = $this->actingAs($this->customer)
            ->postJson('/api/shipping/quote', [
                'metode_pemenuhan' => 'ambil_sendiri',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'metode_pemenuhan' => 'ambil_sendiri',
                    'ongkir'           => 0,
                    'is_gratis'        => true,
                    'lokasi_asal'      => [
                        'nama_lokasi' => 'Lapak Jl. Kuwung',
                    ],
                ],
            ]);
    }

    // =========================================================================
    // 2. PENGUJIAN PENGATURAN LOKASI & JAM OPERASIONAL LAPAK OWNER
    // =========================================================================

    public function test_owner_can_view_update_lokasi_page(): void
    {
        $response = $this->actingAs($this->owner)
            ->get('/owner/update-lokasi');

        $response->assertStatus(200)
            ->assertSee('Pengaturan Lokasi')
            ->assertSee('Lapak Jl. Kuwung')
            ->assertSee('15:00')
            ->assertSee('18:00');
    }

    public function test_customer_cannot_access_owner_update_lokasi_page(): void
    {
        $response = $this->actingAs($this->customer)
            ->get('/owner/update-lokasi');

        $response->assertStatus(403);
    }

    public function test_owner_can_update_lokasi_and_jam_operasional(): void
    {
        $payload = [
            'shipping_location_id' => $this->defaultLocation->id,
            'nama_lokasi'          => 'Lapak Benteng Pancasila',
            'alamat'               => 'Jl. Benteng Pancasila No. 12, Kota Mojokerto',
            'latitude'             => -7.469000,
            'longitude'            => 112.441000,
            'jam_buka'             => '16:00',
            'jam_tutup'            => '21:00',
            'status_lapak'         => 'buka',
        ];

        $response = $this->actingAs($this->owner)
            ->post('/owner/update-lokasi', $payload);

        $response->assertRedirect('/owner/update-lokasi')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('shipping_locations', [
            'id'          => $this->defaultLocation->id,
            'nama_lokasi' => 'Lapak Benteng Pancasila',
            'latitude'    => -7.469000,
            'longitude'   => 112.441000,
        ]);

        $this->assertEquals('16:00', Setting::where('key', 'jam_buka')->value('value'));
        $this->assertEquals('21:00', Setting::where('key', 'jam_tutup')->value('value'));
        $this->assertEquals('buka', Setting::where('key', 'status_lapak')->value('value'));
    }

    // =========================================================================
    // 3. PENGUJIAN CHECKOUT VIEW DENGAN LOKASI & JAM OPERASIONAL LAPAK
    // =========================================================================

    public function test_customer_can_view_checkout_page_with_active_lapak_info(): void
    {
        $product = Product::factory()->create([
            'nama'  => 'Titik Temu',
            'harga' => 10000,
            'stok'  => 50,
            'aktif' => true,
        ]);

        CartItem::create([
            'user_id'    => $this->customer->id,
            'product_id' => $product->id,
            'jumlah'     => 2,
        ]);

        $response = $this->actingAs($this->customer)
            ->get('/checkout');

        $response->assertStatus(200)
            ->assertSee('Lapak Jl. Kuwung')
            ->assertSee('15:00')
            ->assertSee('18:00')
            ->assertSee('Diantar Kurir')
            ->assertSee('Ambil Sendiri');
    }
}

