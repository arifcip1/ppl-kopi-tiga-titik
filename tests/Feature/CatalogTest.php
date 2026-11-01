<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1: Katalog produk dapat dilihat oleh publik/guest tanpa perlu login (FR-3.1).
     */
    public function test_catalog_can_be_viewed_by_guest_without_login(): void
    {
        Product::create([
            'nama' => 'Titik Temu',
            'rasa' => 'Kopi Susu Gula Aren',
            'harga' => 10000,
            'stok' => 8,
            'komposisi' => 'Espresso, susu segar, gula aren murni',
            'deskripsi' => 'Perpaduan kopi susu dengan manis alami gula aren.',
            'keawetan' => '5-7 hari dalam lemari pendingin',
            'aktif' => true,
        ]);

        // Uji rute resmi /katalog
        $response = $this->get('/katalog');
        $response->assertStatus(200);
        $response->assertSee('Titik Temu');
    }

    /**
     * Test 2: Hanya produk dengan status aktif (aktif = true) yang tampil di katalog (FR-3.1 & BR-03 & BR-27).
     */
    public function test_catalog_only_displays_active_products(): void
    {
        Product::create([
            'nama' => 'Titik Temu Aktif',
            'rasa' => 'Kopi Susu Gula Aren',
            'harga' => 10000,
            'stok' => 8,
            'komposisi' => 'Espresso, susu segar, gula aren',
            'deskripsi' => 'Kopi aktif tampil di katalog',
            'keawetan' => '5-7 hari dalam lemari pendingin',
            'aktif' => true,
        ]);

        Product::create([
            'nama' => 'Titik Rahasia Nonaktif',
            'rasa' => 'Kopi Rahasia',
            'harga' => 15000,
            'stok' => 5,
            'komposisi' => 'Rahasia',
            'deskripsi' => 'Kopi nonaktif tidak boleh tampil',
            'keawetan' => '5-7 hari dalam lemari pendingin',
            'aktif' => false,
        ]);

        $response = $this->get('/katalog');

        $response->assertStatus(200);
        $response->assertSee('Titik Temu Aktif');
        $response->assertDontSee('Titik Rahasia Nonaktif');
    }

    /**
     * Test 3: Kartu produk menampilkan informasi lengkap sesuai spesifikasi (FR-3.2, FR-3.3 & BR-01).
     */
    public function test_catalog_displays_all_required_product_attributes(): void
    {
        Product::create([
            'nama' => 'Titik Manja',
            'rasa' => 'Kopi Susu Caramel Lembut',
            'harga' => 10000,
            'stok' => 4,
            'komposisi' => 'Espresso, susu segar, sirup karamel gurih',
            'deskripsi' => 'Espresso creamy dengan saus karamel mentega gurih manis.',
            'keawetan' => '5–7 hari bila disimpan di lemari pendingin',
            'aktif' => true,
        ]);

        $response = $this->get('/katalog');

        $response->assertStatus(200);
        $response->assertSee('Titik Manja');
        $response->assertSee('Kopi Susu Caramel Lembut');
        $response->assertSee('Rp 10.000');
        $response->assertSee('250 ml');
        $response->assertSee('Espresso, susu segar, sirup karamel gurih');
    }

    /**
     * Test 4: Produk dengan stok 0 menampilkan status "Stok Habis" dan tombol pesan nonaktif (FR-3.2 & FR-3.4 & BR-03).
     */
    public function test_out_of_stock_product_shows_stok_habis_and_disabled_button(): void
    {
        Product::create([
            'nama' => 'Titik Hitam Habis',
            'rasa' => 'Americano',
            'harga' => 8000,
            'stok' => 0,
            'komposisi' => 'Espresso dan air',
            'deskripsi' => 'Americano pekat',
            'keawetan' => '5-7 hari dalam lemari pendingin',
            'aktif' => true,
        ]);

        $response = $this->get('/katalog');

        $response->assertStatus(200);
        $response->assertSee('Titik Hitam Habis');
        $response->assertSee('Stok Habis');
        $response->assertSee('disabled');
        $response->assertSee('remove_shopping_cart');
    }

    /**
     * Test 5: Produk dengan stok tersedia menampilkan sisa stok dan tombol "+ Keranjang" aktif (FR-3.2 & FR-3.4).
     */
    public function test_available_product_shows_stock_count_and_active_button(): void
    {
        Product::create([
            'nama' => 'Titik Jenuh Melimpah',
            'rasa' => 'Coklat Velvety',
            'harga' => 12000,
            'stok' => 10,
            'komposisi' => 'Kakao murni dan susu segar',
            'deskripsi' => 'Minuman cokelat segar kental',
            'keawetan' => '5-7 hari dalam lemari pendingin',
            'aktif' => true,
        ]);

        $response = $this->get('/katalog');

        $response->assertStatus(200);
        $response->assertSee('Titik Jenuh Melimpah');
        $response->assertSee('Stok: 10 botol');
        $response->assertSee('+ Keranjang');
    }

    /**
     * Test 6: Perubahan stok dan status aktif di database langsung tercermin tanpa cache (FR-3.4 & BR-02).
     */
    public function test_stock_and_status_changes_are_immediately_reflected_without_cache(): void
    {
        $product = Product::create([
            'nama' => 'Titik Temu Realtime',
            'rasa' => 'Kopi Susu Aren',
            'harga' => 10000,
            'stok' => 10,
            'komposisi' => 'Espresso, susu, aren',
            'deskripsi' => 'Cita rasa kopi susu',
            'keawetan' => '5-7 hari dalam lemari pendingin',
            'aktif' => true,
        ]);

        // Request 1: Stok masih 10 botol
        $firstResponse = $this->get('/katalog');
        $firstResponse->assertStatus(200);
        $firstResponse->assertSee('Stok: 10 botol');

        // Simulasi perubahan stok oleh Admin (misal stok habis terjual)
        $product->update(['stok' => 0]);

        // Request 2: Langsung menampilkan Stok Habis dan tombol nonaktif (tidak di-cache)
        $secondResponse = $this->get('/katalog');
        $secondResponse->assertStatus(200);
        $secondResponse->assertSee('Stok Habis');
        $secondResponse->assertSee('disabled');

        // Simulasi penonaktifan produk oleh Admin
        $product->update(['aktif' => false]);

        // Request 3: Langsung tidak muncul di katalog
        $thirdResponse = $this->get('/katalog');
        $thirdResponse->assertStatus(200);
        $thirdResponse->assertDontSee('Titik Temu Realtime');
    }

    /**
     * Test 7: API Publik GET /api/products mengembalikan produk aktif dalam format JSON (Kontrak Dokumen PPL 01 Bagian 7).
     */
    public function test_api_products_index_returns_active_products_json(): void
    {
        Product::create([
            'nama' => 'Titik Temu API',
            'rasa' => 'Aren',
            'harga' => 10000,
            'stok' => 5,
            'komposisi' => 'Kopi, susu, aren',
            'deskripsi' => 'Varian API',
            'keawetan' => '5-7 hari dalam lemari pendingin',
            'aktif' => true,
        ]);

        Product::create([
            'nama' => 'Titik Rahasia API',
            'rasa' => 'Rahasia',
            'harga' => 12000,
            'stok' => 2,
            'komposisi' => 'Rahasia',
            'deskripsi' => 'Nonaktif',
            'keawetan' => '5-7 hari dalam lemari pendingin',
            'aktif' => false,
        ]);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Berhasil',
        ]);
        $response->assertJsonFragment(['nama' => 'Titik Temu API']);
        $response->assertJsonMissing(['nama' => 'Titik Rahasia API']);
    }

    /**
     * Test 8: API Publik GET /api/products/{id} mengembalikan detail produk aktif atau 404 jika nonaktif.
     */
    public function test_api_product_detail_returns_active_product_and_404_for_inactive(): void
    {
        $activeProduct = Product::create([
            'nama' => 'Titik Temu Detail',
            'rasa' => 'Aren',
            'harga' => 10000,
            'stok' => 8,
            'komposisi' => 'Kopi, susu, aren',
            'deskripsi' => 'Detail produk aktif',
            'keawetan' => '5-7 hari dalam lemari pendingin',
            'aktif' => true,
        ]);

        $inactiveProduct = Product::create([
            'nama' => 'Titik Tertutup Detail',
            'rasa' => 'Rahasia',
            'harga' => 15000,
            'stok' => 3,
            'komposisi' => 'Kopi rahasia',
            'deskripsi' => 'Detail produk nonaktif',
            'keawetan' => '5-7 hari dalam lemari pendingin',
            'aktif' => false,
        ]);

        // Produk aktif -> status 200
        $responseActive = $this->getJson('/api/products/' . $activeProduct->id);
        $responseActive->assertStatus(200);
        $responseActive->assertJson([
            'message' => 'Berhasil',
            'data' => [
                'nama' => 'Titik Temu Detail',
                'harga' => 10000,
                'stok' => 8,
            ],
        ]);

        // Produk nonaktif -> status 404
        $responseInactive = $this->getJson('/api/products/' . $inactiveProduct->id);
        $responseInactive->assertStatus(404);
        $responseInactive->assertJson([
            'message' => 'Produk tidak ditemukan atau tidak aktif.',
        ]);
    }

    /**
     * Test 9: Jika tidak ada produk aktif di katalog, tampilkan empty state yang ramah.
     */
    public function test_empty_catalog_displays_empty_state_message(): void
    {
        // Pastikan tidak ada produk aktif
        Product::query()->delete();

        $response = $this->get('/katalog');

        $response->assertStatus(200);
        $response->assertSee('Belum Ada Menu Aktif');
        $response->assertSee('Semua Menu (0)');
    }

    /**
     * Test 10: Produk tanpa foto menampilkan fallback placeholder tanpa error (Uji QE F3).
     */
    public function test_product_without_photo_displays_fallback_placeholder(): void
    {
        Product::create([
            'nama' => 'Titik Baru Tanpa Foto',
            'rasa' => 'Kopi Original',
            'harga' => 9000,
            'stok' => 7,
            'komposisi' => 'Kopi murni',
            'deskripsi' => 'Produk tanpa upload foto',
            'keawetan' => '5-7 hari dalam lemari pendingin',
            'foto_path' => null,
            'aktif' => true,
        ]);

        $response = $this->get('/katalog');

        $response->assertStatus(200);
        $response->assertSee('Titik Baru Tanpa Foto');
        $response->assertSee('placehold.co');
    }
}
