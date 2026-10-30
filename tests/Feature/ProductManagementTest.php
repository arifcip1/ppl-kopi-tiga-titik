<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1: Halaman kelola produk dapat diakses dan menampilkan data produk & statistik.
     */
    public function test_owner_can_view_product_management_page(): void
    {
        Product::create([
            'nama' => 'Titik Temu',
            'rasa' => 'Kopi Susu Gula Aren',
            'harga' => 10000,
            'stok' => 8,
            'komposisi' => 'Espresso, susu segar, gula aren',
            'deskripsi' => 'Kopi susu gula aren murni',
            'keawetan' => '5-7 hari dalam lemari pendingin',
            'aktif' => true,
        ]);

        $response = $this->get('/owner/produk');

        $response->assertStatus(200);
        $response->assertSee('Kelola Produk');
        $response->assertSee('Titik Temu');
        $response->assertSee('Rp 10.000');
        $response->assertSee('8 botol');
    }

    /**
     * Helper membuat fake file gambar PNG valid tanpa memerlukan ekstensi PHP-GD.
     */
    private function createFakeImage(string $filename = 'test.png'): UploadedFile
    {
        // 1x1 piksel PNG valid (Base64)
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        return UploadedFile::fake()->createWithContent($filename, $png);
    }

    /**
     * Test 2: Owner dapat menambahkan produk baru dengan upload foto (FR-2.2 & FR-2.3).
     */
    public function test_owner_can_create_new_product_with_photo(): void
    {
        Storage::fake('public');

        $foto = $this->createFakeImage('produk_baru.png');

        $payload = [
            'nama' => 'Titik Candu',
            'rasa' => 'Kopi Susu Pandan',
            'harga' => 11000,
            'stok' => 15,
            'komposisi' => 'Espresso, susu, ekstrak pandan',
            'deskripsi' => 'Kopi aroma pandan wangi',
            'keawetan' => '5–7 hari bila disimpan di lemari pendingin',
            'foto' => $foto,
            'aktif' => '1',
        ];

        $response = $this->post('/owner/produk', $payload);

        $response->assertRedirect('/owner/produk');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'nama' => 'Titik Candu',
            'harga' => 11000,
            'stok' => 15,
            'aktif' => true,
        ]);

        $product = Product::where('nama', 'Titik Candu')->first();
        $this->assertNotNull($product->foto_path);
        Storage::disk('public')->assertExists($product->foto_path);
    }

    /**
     * Test 3: Validasi menolak harga <= 0 dan stok negatif (BR-01 & FR-2.4).
     */
    public function test_negative_stock_or_invalid_price_is_rejected(): void
    {
        $payload = [
            'nama' => 'Titik Palsu',
            'rasa' => 'Kopi Air',
            'harga' => 0, // Invalid: harus > 0
            'stok' => -5, // Invalid: tidak boleh negatif
            'komposisi' => 'Air',
            'deskripsi' => 'Deskripsi',
            'keawetan' => '5 hari',
        ];

        $response = $this->post('/owner/produk', $payload);

        $response->assertSessionHasErrors(['harga', 'stok']);
        $this->assertDatabaseMissing('products', ['nama' => 'Titik Palsu']);
    }

    /**
     * Test 4: Nama produk ganda ditolak oleh validasi (nama unik).
     */
    public function test_duplicate_product_name_is_rejected(): void
    {
        Product::create([
            'nama' => 'Titik Hitam',
            'rasa' => 'Americano',
            'harga' => 8000,
            'stok' => 10,
            'komposisi' => 'Kopi dan air',
            'deskripsi' => 'Kopi hitam pekat',
            'keawetan' => '5-7 hari',
            'aktif' => true,
        ]);

        $payload = [
            'nama' => 'Titik Hitam', // Duplicate!
            'rasa' => 'Americano Baru',
            'harga' => 9000,
            'stok' => 5,
            'komposisi' => 'Kopi',
            'deskripsi' => 'Deskripsi',
            'keawetan' => '5 hari',
        ];

        $response = $this->post('/owner/produk', $payload);

        $response->assertSessionHasErrors(['nama']);
    }

    /**
     * Test 5: Owner dapat memperbarui data produk dan mengganti foto lama (FR-2.3 & FR-2.4).
     */
    public function test_owner_can_update_product_and_replace_photo(): void
    {
        Storage::fake('public');

        $oldPhoto = $this->createFakeImage('old_photo.png');
        $oldPath = $oldPhoto->store('products', 'public');

        $product = Product::create([
            'nama' => 'Titik Manja',
            'rasa' => 'Karamel',
            'harga' => 10000,
            'stok' => 2,
            'komposisi' => 'Kopi, susu, karamel',
            'deskripsi' => 'Kopi karamel',
            'keawetan' => '5-7 hari',
            'foto_path' => $oldPath,
            'aktif' => true,
        ]);

        $newPhoto = $this->createFakeImage('new_photo.png');

        $payload = [
            'nama' => 'Titik Manja Baru',
            'rasa' => 'Karamel Spesial',
            'harga' => 12000,
            'stok' => 10,
            'komposisi' => 'Kopi, susu, karamel premium',
            'deskripsi' => 'Rasa lebih kaya',
            'keawetan' => '5–7 hari',
            'foto' => $newPhoto,
            'aktif' => '1',
        ];

        $response = $this->put("/owner/produk/{$product->id}", $payload);

        $response->assertRedirect('/owner/produk');
        $response->assertSessionHas('success');

        $product->refresh();
        $this->assertEquals('Titik Manja Baru', $product->nama);
        $this->assertEquals(12000, $product->harga);
        $this->assertEquals(10, $product->stok);

        // Pastikan foto lama dihapus dan foto baru tersimpan (FR-2.3)
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($product->foto_path);
    }

    /**
     * Test 6: Aksi nonaktifkan produk mengubah aktif = false (BR-27 soft deactivate).
     */
    public function test_owner_can_deactivate_product_soft_delete(): void
    {
        $product = Product::create([
            'nama' => 'Titik Jenuh',
            'rasa' => 'Cokelat',
            'harga' => 12000,
            'stok' => 5,
            'komposisi' => 'Cokelat dan susu',
            'deskripsi' => 'Minuman cokelat',
            'keawetan' => '5-7 hari',
            'aktif' => true,
        ]);

        $response = $this->delete("/owner/produk/{$product->id}");

        $response->assertRedirect('/owner/produk');

        // Pastikan record produk TIDAK terhapus fisik dari database
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'aktif' => false,
        ]);
    }

    /**
     * Test 7: Peringatan restok muncul di view ketika stok <= 5 (BR-10, BR-19, BR-20).
     */
    public function test_restock_warning_is_displayed_when_stock_is_five_or_less(): void
    {
        Product::create([
            'nama' => 'Titik Krisis',
            'rasa' => 'Kopi',
            'harga' => 10000,
            'stok' => 3, // <= 5 memicu peringatan restok
            'komposisi' => 'Kopi',
            'deskripsi' => 'Deskripsi',
            'keawetan' => '5 hari',
            'aktif' => true,
        ]);

        $response = $this->get('/owner/produk');

        $response->assertStatus(200);
        $response->assertSee('RESTOK!');
        $response->assertSee('Sisa 3 botol');
        // Saran produksi: max(0, 10 - 3) = 7 botol (BR-20)
        $response->assertSee('Buat');
        $response->assertSee('7 botol');
    }
}

