<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    /**
     * Uji TC-INT-04: Server menolak input stok negatif meskipun validasi HTML5 di-bypass.
     */
    public function test_server_rejects_negative_stock_on_create(): void
    {
        $response = $this->actingAs($this->admin)->post('/owner/produk', [
            'nama'      => 'Kopi Test Minus',
            'harga'     => 10000,
            'stok'      => -5,
            'komposisi' => 'Kopi, Susu',
            'deskripsi' => 'Rasa kopi nikmat',
            'keawetan'  => '5-7 hari',
        ]);

        $response->assertSessionHasErrors(['stok']);
        $this->assertDatabaseMissing('products', [
            'nama' => 'Kopi Test Minus',
        ]);
    }

    /**
     * Uji BR-01: Server menolak harga Rp 0 atau bilangan negatif.
     */
    public function test_server_rejects_zero_or_negative_price_on_create(): void
    {
        $responseZero = $this->actingAs($this->admin)->post('/owner/produk', [
            'nama'      => 'Kopi Gratisan',
            'harga'     => 0,
            'stok'      => 10,
            'komposisi' => 'Kopi, Gula',
            'deskripsi' => 'Kopi murah',
            'keawetan'  => '5-7 hari',
        ]);
        $responseZero->assertSessionHasErrors(['harga']);

        $responseNegative = $this->actingAs($this->admin)->post('/owner/produk', [
            'nama'      => 'Kopi Minus Harga',
            'harga'     => -1000,
            'stok'      => 10,
            'komposisi' => 'Kopi, Gula',
            'deskripsi' => 'Kopi murah',
            'keawetan'  => '5-7 hari',
        ]);
        $responseNegative->assertSessionHasErrors(['harga']);

        $this->assertDatabaseCount('products', 0);
    }

    /**
     * Uji BR-01: Server menolak harga dan stok yang bukan bilangan bulat (string/desimal).
     */
    public function test_server_rejects_non_integer_price_and_stock(): void
    {
        $response = $this->actingAs($this->admin)->post('/owner/produk', [
            'nama'      => 'Kopi Desimal',
            'harga'     => 'sepuluh_ribu',
            'stok'      => 'sepuluh',
            'komposisi' => 'Kopi',
            'deskripsi' => 'Deskripsi',
            'keawetan'  => '5 hari',
        ]);

        $response->assertSessionHasErrors(['harga', 'stok']);
    }

    /**
     * Helper membuat dummy gambar valid tanpa membutuhkan ekstensi GD PHP.
     */
    private function createFakeImage(string $filename = 'test.png', int $kilobytes = 10): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        if ($kilobytes > 1) {
            $png .= str_repeat('X', ($kilobytes * 1024) - strlen($png));
        }

        return UploadedFile::fake()->createWithContent($filename, $png);
    }

    /**
     * Uji validasi foto botol produk maksimal 2 MB (FR-2.3).
     */
    public function test_server_rejects_oversized_product_photo_exceeding_2mb(): void
    {
        Storage::fake('public');

        // Buat file gambar 3 MB (3072 KB) yang melebihi batas 2048 KB
        $oversizedPhoto = $this->createFakeImage('large-coffee.png', 3072);

        $response = $this->actingAs($this->admin)->post('/owner/produk', [
            'nama'      => 'Kopi Foto Raksasa',
            'harga'     => 12000,
            'stok'      => 15,
            'komposisi' => 'Kopi robusta',
            'deskripsi' => 'Profil rasa pekat',
            'keawetan'  => '7 hari',
            'foto'      => $oversizedPhoto,
        ]);

        $response->assertSessionHasErrors(['foto']);
        $this->assertDatabaseMissing('products', ['nama' => 'Kopi Foto Raksasa']);
    }

    /**
     * Uji validasi foto botol harus berekstensi gambar (JPG, PNG, WebP).
     */
    public function test_server_rejects_invalid_photo_file_format(): void
    {
        Storage::fake('public');

        $fakePdf = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->admin)->post('/owner/produk', [
            'nama'      => 'Kopi File Palsu',
            'harga'     => 12000,
            'stok'      => 15,
            'komposisi' => 'Kopi robusta',
            'deskripsi' => 'Profil rasa pekat',
            'keawetan'  => '7 hari',
            'foto'      => $fakePdf,
        ]);

        $response->assertSessionHasErrors(['foto']);
    }

    /**
     * Uji update produk menolak harga 0 dan stok minus.
     */
    public function test_server_rejects_invalid_price_and_stock_on_update(): void
    {
        $product = Product::factory()->create([
            'nama'  => 'Kopi Original',
            'harga' => 10000,
            'stok'  => 10,
        ]);

        $response = $this->actingAs($this->admin)->put("/owner/produk/{$product->id}", [
            'nama'      => 'Kopi Original',
            'harga'     => 0,
            'stok'      => -3,
            'komposisi' => 'Kopi murni',
            'deskripsi' => 'Deskripsi update',
            'keawetan'  => '5 hari',
        ]);

        $response->assertSessionHasErrors(['harga', 'stok']);

        $product->refresh();
        $this->assertEquals(10000, $product->harga);
        $this->assertEquals(10, $product->stok);
    }

    /**
     * Uji Kontrak JSON Bab 13 & BR-41: Permintaan API/AJAX yang gagal validasi mengembalikan 422 JSON standar.
     */
    public function test_api_product_store_returns_standard_json_422_on_validation_failure(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/owner/produk', [
            'nama'  => '',
            'harga' => -500,
            'stok'  => -2,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => [
                    'nama',
                    'harga',
                    'stok',
                ],
            ]);
    }
}
