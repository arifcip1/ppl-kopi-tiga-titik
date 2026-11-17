<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerPosKasirTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_guest_cannot_access_owner_pos(): void
    {
        $response = $this->get('/owner/pos');
        $response->assertRedirect('/login');
    }

    public function test_customer_cannot_access_owner_pos(): void
    {
        $customer = User::factory()->pelanggan()->create();

        $response = $this->actingAs($customer)->get('/owner/pos');
        $response->assertForbidden();
    }

    public function test_courier_cannot_access_owner_pos(): void
    {
        $courier = User::factory()->kurir()->create();

        $response = $this->actingAs($courier)->get('/owner/pos');
        $response->assertForbidden();
    }

    public function test_admin_can_access_owner_pos_and_view_active_products(): void
    {
        $admin = $this->createAdmin();

        $p1 = Product::factory()->create([
            'nama' => 'Titik Temu',
            'rasa' => 'Kopi Susu Gula Aren',
            'harga' => 10000,
            'stok' => 15,
            'aktif' => true,
        ]);

        $p2 = Product::factory()->create([
            'nama' => 'Titik Hitam',
            'rasa' => 'Americano',
            'harga' => 8000,
            'stok' => 3, // Low stock <= 5
            'aktif' => true,
        ]);

        $p3 = Product::factory()->create([
            'nama' => 'Titik Manja',
            'rasa' => 'Karamel',
            'harga' => 12000,
            'stok' => 0, // Out of stock
            'aktif' => true,
        ]);

        $pInactive = Product::factory()->create([
            'nama' => 'Titik Rahasia',
            'aktif' => false,
        ]);

        $response = $this->actingAs($admin)->get('/owner/pos');

        $response->assertOk();
        $response->assertViewIs('owner.pos');
        $response->assertSee('POS Kasir Lapak');
        $response->assertSee('Titik Temu');
        $response->assertSee('Titik Hitam');
        $response->assertSee('Titik Manja');
        $response->assertDontSee('Titik Rahasia');

        // Rule-Based F10 visual checks
        $response->assertSee('Sisa 3 btl');
        $response->assertSee('Habis');

        // Modal alert reset kasir & toast feedback
        $response->assertSee('modal-reset-cart-confirm');
        $response->assertSee('Reset Keranjang Kasir?');
        $response->assertSee('pos-toast-container');
        $response->assertSee('posSuccessModal');
    }
}
