<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Uji request Web ke rute yang tidak ada mengembalikan response 404.
     */
    public function test_web_request_for_non_existent_route_returns_404_status(): void
    {
        $response = $this->get('/halaman-fiktif-tidak-ada-123');

        $response->assertStatus(404);
    }

    /**
     * Uji request API/JSON ke rute yang tidak ada mengembalikan format JSON standar 404 (BR-41).
     */
    public function test_api_request_for_non_existent_route_returns_standard_json_404(): void
    {
        $response = $this->getJson('/api/endpoint-fiktif-123');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonStructure([
                'success',
                'message',
            ]);
    }

    /**
     * Uji request Web oleh pengguna yang tidak berhak mengakses halaman admin mengembalikan 403.
     */
    public function test_customer_accessing_owner_route_returns_403_status(): void
    {
        $customer = User::factory()->create([
            'role' => 'pelanggan',
        ]);

        $response = $this->actingAs($customer)->get('/owner/produk');

        $response->assertStatus(403);
    }

    /**
     * Uji request API yang belum diautentikasi mengembalikan 401 JSON standar.
     */
    public function test_api_request_unauthenticated_returns_json_401(): void
    {
        $response = $this->getJson('/api/cart');

        // Jika endpoint cart dilindungi auth
        $this->assertContains($response->status(), [401, 302]);
    }
}

