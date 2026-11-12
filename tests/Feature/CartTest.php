<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private Product $activeProduct;
    private Product $limitedProduct;
    private Product $inactiveProduct;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Akun Pelanggan Terdaftar
        $this->customer = User::create([
            'name'     => 'Nabil Pratama',
            'email'    => 'nabil.tester@gmail.com',
            'password' => bcrypt('password123'),
            'role'     => 'pelanggan',
        ]);

        // 2. Produk Aktif dengan Stok Normal
        $this->activeProduct = Product::create([
            'nama'      => 'Titik Temu',
            'rasa'      => 'Kopi Susu Gula Aren',
            'harga'     => 10000,
            'stok'      => 10,
            'komposisi' => 'Espresso, susu, gula aren',
            'deskripsi' => 'Kopi susu gula aren 250ml',
            'keawetan'  => '5-7 hari',
            'aktif'     => true,
        ]);

        // 3. Produk dengan Stok Terbatas (3 botol)
        $this->limitedProduct = Product::create([
            'nama'      => 'Titik Hitam',
            'rasa'      => 'Americano',
            'harga'     => 8000,
            'stok'      => 3,
            'komposisi' => 'Espresso, air',
            'deskripsi' => 'Americano 250ml',
            'keawetan'  => '5-7 hari',
            'aktif'     => true,
        ]);

        // 4. Produk Nonaktif
        $this->inactiveProduct = Product::create([
            'nama'      => 'Titik Rahasia',
            'rasa'      => 'Nonaktif',
            'harga'     => 12000,
            'stok'      => 5,
            'komposisi' => 'Rahasia',
            'deskripsi' => 'Produk tidak aktif',
            'keawetan'  => '5-7 hari',
            'aktif'     => false,
        ]);
    }

    /**
     * Test 1: Guest yang mencoba mengakses keranjang diarahkan ke login (FR-1.4).
     */
    public function test_guest_cannot_access_cart_and_redirects_to_login(): void
    {
        $response = $this->get('/cart');
        $response->assertRedirect('/login');

        $responseApi = $this->getJson('/api/cart');
        $responseApi->assertStatus(401);
    }

    /**
     * Test 2: Pelanggan yang baru daftar melihat halaman keranjang dalam kondisi kosong (Bug 1 QA).
     */
    public function test_cart_page_shows_empty_state_when_cart_is_empty(): void
    {
        $response = $this->actingAs($this->customer)->get('/cart');

        $response->assertStatus(200);
        $response->assertSee('Keranjang Belanja Kosong');
        $response->assertSee('Mulai Belanja Sekarang');
    }

    /**
     * Test 3: Pelanggan dapat menambahkan produk aktif ke keranjang dan tersimpan di database (FR-4.1, FR-4.2).
     */
    public function test_customer_can_add_product_to_cart_and_stored_in_database(): void
    {
        $response = $this->actingAs($this->customer)->postJson('/cart/items', [
            'product_id' => $this->activeProduct->id,
            'jumlah'     => 2,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message'    => 'Produk berhasil ditambahkan ke keranjang',
            'cart_count' => 1,
            'total_jumlah' => 2,
        ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id'    => $this->customer->id,
            'product_id' => $this->activeProduct->id,
            'jumlah'     => 2,
        ]);
    }

    /**
     * Test 4: Menambahkan produk yang sudah ada di keranjang menjumlahkan jumlahnya (FR-4.6).
     */
    public function test_adding_existing_product_in_cart_increments_quantity(): void
    {
        CartItem::create([
            'user_id'    => $this->customer->id,
            'product_id' => $this->activeProduct->id,
            'jumlah'     => 2,
        ]);

        $response = $this->actingAs($this->customer)->postJson('/cart/items', [
            'product_id' => $this->activeProduct->id,
            'jumlah'     => 3,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('cart_items', [
            'user_id'    => $this->customer->id,
            'product_id' => $this->activeProduct->id,
            'jumlah'     => 5,
        ]);
    }

    /**
     * Test 5: Validasi stok - Menambahkan produk melebihi batas stok yang tersedia ditolak (FR-4.4 & BR-04).
     */
    public function test_adding_product_exceeding_available_stock_is_rejected(): void
    {
        // Stok limitedProduct = 3 botol, pelanggan mencoba menambah 4 botol
        $response = $this->actingAs($this->customer)->postJson('/cart/items', [
            'product_id' => $this->limitedProduct->id,
            'jumlah'     => 4,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['jumlah']);
        $this->assertDatabaseMissing('cart_items', [
            'user_id'    => $this->customer->id,
            'product_id' => $this->limitedProduct->id,
        ]);
    }

    /**
     * Test 6: Akumulasi penambahan yang melebihi batas stok juga ditolak (FR-4.4).
     */
    public function test_accumulated_quantity_exceeding_stock_is_rejected(): void
    {
        // Stok = 3, sudah ada 2 di keranjang
        CartItem::create([
            'user_id'    => $this->customer->id,
            'product_id' => $this->limitedProduct->id,
            'jumlah'     => 2,
        ]);

        // Coba tambah 2 lagi (total jadi 4 > 3)
        $response = $this->actingAs($this->customer)->postJson('/cart/items', [
            'product_id' => $this->limitedProduct->id,
            'jumlah'     => 2,
        ]);

        $response->assertStatus(422);
        // Jumlah di database tetap 2
        $this->assertDatabaseHas('cart_items', [
            'user_id'    => $this->customer->id,
            'product_id' => $this->limitedProduct->id,
            'jumlah'     => 2,
        ]);
    }

    /**
     * Test 7: Menambahkan produk yang berstatus nonaktif ditolak (BR-13).
     */
    public function test_adding_inactive_product_to_cart_is_rejected(): void
    {
        $response = $this->actingAs($this->customer)->postJson('/cart/items', [
            'product_id' => $this->inactiveProduct->id,
            'jumlah'     => 1,
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('cart_items', [
            'user_id'    => $this->customer->id,
            'product_id' => $this->inactiveProduct->id,
        ]);
    }

    /**
     * Test 8: Pelanggan dapat mengubah kuantitas item di keranjang (PATCH) (FR-4.2).
     */
    public function test_customer_can_update_cart_item_quantity(): void
    {
        $item = CartItem::create([
            'user_id'    => $this->customer->id,
            'product_id' => $this->activeProduct->id,
            'jumlah'     => 2,
        ]);

        $response = $this->actingAs($this->customer)->patchJson("/cart/items/{$item->id}", [
            'jumlah' => 4,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.jumlah', 4);
        $response->assertJsonPath('data.subtotal', 40000); // 4 x Rp 10.000

        $this->assertDatabaseHas('cart_items', [
            'id'     => $item->id,
            'jumlah' => 4,
        ]);
    }

    /**
     * Test 9: Mengubah kuantitas item melebihi stok ditolak (FR-4.4 & BR-04).
     */
    public function test_updating_cart_item_quantity_exceeding_stock_is_rejected(): void
    {
        $item = CartItem::create([
            'user_id'    => $this->customer->id,
            'product_id' => $this->limitedProduct->id, // Stok = 3
            'jumlah'     => 2,
        ]);

        $response = $this->actingAs($this->customer)->patchJson("/cart/items/{$item->id}", [
            'jumlah' => 5,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['jumlah']);
        $this->assertDatabaseHas('cart_items', [
            'id'     => $item->id,
            'jumlah' => 2,
        ]);
    }

    /**
     * Test 10: Mengubah kuantitas item menjadi 0 atau angka negatif ditolak (FR-4.4: integer >= 1).
     */
    public function test_updating_cart_item_quantity_less_than_one_is_rejected(): void
    {
        $item = CartItem::create([
            'user_id'    => $this->customer->id,
            'product_id' => $this->activeProduct->id,
            'jumlah'     => 2,
        ]);

        $response = $this->actingAs($this->customer)->patchJson("/cart/items/{$item->id}", [
            'jumlah' => 0,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['jumlah']);
    }

    /**
     * Test 11: Pelanggan dapat menghapus item dari keranjang belanja (FR-4.2).
     */
    public function test_customer_can_delete_item_from_cart(): void
    {
        $item = CartItem::create([
            'user_id'    => $this->customer->id,
            'product_id' => $this->activeProduct->id,
            'jumlah'     => 1,
        ]);

        $response = $this->actingAs($this->customer)->deleteJson("/cart/items/{$item->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.is_empty', true);
        $this->assertDatabaseMissing('cart_items', [
            'id' => $item->id,
        ]);
    }

    /**
     * Test 12: Keamanan/Otorisasi - Pengguna tidak dapat mengubah atau menghapus item keranjang milik orang lain (BR-04).
     */
    public function test_customer_cannot_update_or_delete_other_customer_cart_item(): void
    {
        $otherUser = User::create([
            'name'     => 'Orang Lain',
            'email'    => 'other@example.com',
            'password' => bcrypt('password123'),
            'role'     => 'pelanggan',
        ]);

        $itemOther = CartItem::create([
            'user_id'    => $otherUser->id,
            'product_id' => $this->activeProduct->id,
            'jumlah'     => 2,
        ]);

        // Coba PATCH item orang lain
        $responsePatch = $this->actingAs($this->customer)->patchJson("/cart/items/{$itemOther->id}", [
            'jumlah' => 3,
        ]);
        $responsePatch->assertStatus(404);

        // Coba DELETE item orang lain
        $responseDelete = $this->actingAs($this->customer)->deleteJson("/cart/items/{$itemOther->id}");
        $responseDelete->assertStatus(404);

        // Data orang lain tetap aman di database
        $this->assertDatabaseHas('cart_items', [
            'id'     => $itemOther->id,
            'jumlah' => 2,
        ]);
    }

    /**
     * Test 13: Subtotal dan total sementara dihitung ulang otomatis dengan benar (FR-4.3 & BR-05).
     */
    public function test_cart_subtotal_and_total_are_calculated_correctly(): void
    {
        // Item 1: 2 botol Titik Temu @ Rp 10.000 = Rp 20.000
        CartItem::create([
            'user_id'    => $this->customer->id,
            'product_id' => $this->activeProduct->id,
            'jumlah'     => 2,
        ]);

        // Item 2: 3 botol Titik Hitam @ Rp 8.000 = Rp 24.000
        CartItem::create([
            'user_id'    => $this->customer->id,
            'product_id' => $this->limitedProduct->id,
            'jumlah'     => 3,
        ]);

        $response = $this->actingAs($this->customer)->get('/cart');

        $response->assertStatus(200);
        $response->assertSee('Titik Temu');
        $response->assertSee('Titik Hitam');
        // Total sementara = 20.000 + 24.000 = 44.000
        $response->assertSee('Rp 44.000');
        // Total botol = 2 + 3 = 5 Botol
        $response->assertSeeText('5 Botol');
    }

    /**
     * Test 14: Keranjang tidak mereservasi stok produk (FR-4.5).
     */
    public function test_cart_does_not_reserve_product_stock(): void
    {
        $initialStock = $this->activeProduct->stok;

        // Tambah ke keranjang
        $this->actingAs($this->customer)->postJson('/cart/items', [
            'product_id' => $this->activeProduct->id,
            'jumlah'     => 3,
        ]);

        // Stok di tabel products tetap tidak berubah (stok baru berkurang di F5 checkout)
        $this->assertEquals($initialStock, $this->activeProduct->fresh()->stok);
    }

    /**
     * Test 15: Keranjang tetap tersimpan di database setelah logout dan login kembali (Kriteria Penerimaan F4).
     */
    public function test_cart_persists_in_database_after_logout_and_relogin(): void
    {
        CartItem::create([
            'user_id'    => $this->customer->id,
            'product_id' => $this->activeProduct->id,
            'jumlah'     => 2,
        ]);

        // Logout
        $this->actingAs($this->customer)->post('/logout');

        // Login kembali
        $this->post('/login', [
            'email'    => 'nabil.tester@gmail.com',
            'password' => 'password123',
            'role'     => 'pelanggan',
        ]);

        // Cek data di database tetap ada
        $this->assertDatabaseHas('cart_items', [
            'user_id'    => $this->customer->id,
            'product_id' => $this->activeProduct->id,
            'jumlah'     => 2,
        ]);
    }

    /**
     * Test 16: Halaman keranjang menampilkan modal konfirmasi sebelum menghapus item (TC-04).
     */
    public function test_cart_page_renders_delete_confirmation_modal_for_cart_items(): void
    {
        $item = CartItem::create([
            'user_id'    => $this->customer->id,
            'product_id' => $this->activeProduct->id,
            'jumlah'     => 2,
        ]);

        $response = $this->actingAs($this->customer)->get('/cart');

        $response->assertStatus(200);
        $response->assertSee('id="modal-delete-cart-confirm"', false);
        $response->assertSee('Hapus Item dari Keranjang?', false);
        $response->assertSee('onclick="openDeleteCartModal(' . $item->id . ')"', false);
        $response->assertDontSee('onclick="deleteCartItem(' . $item->id . ')"', false);
        $response->assertSee('data-name="Titik Temu"', false);
    }
}
