<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /**
     * Test 1: Halaman login dapat dibuka publik.
     */
    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Masuk ke Akun');
    }

    /**
     * Test 2: Halaman registrasi dapat dibuka publik.
     */
    public function test_register_page_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Daftar Akun');
    }

    /**
     * Test 3: Registrasi pelanggan berhasil dengan role 'pelanggan' dan password di-hash.
     */
    public function test_customer_can_register_successfully(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Budi Santoso',
            'email'                 => 'budi@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/katalog');
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'name'  => 'Budi Santoso',
            'email' => 'budi@example.com',
            'role'  => 'pelanggan',
        ]);

        $user = User::where('email', 'budi@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    /**
     * Test 4: Registrasi dengan email duplikat ditolak.
     */
    public function test_registration_fails_if_email_already_registered(): void
    {
        User::create([
            'name'     => 'User Lama',
            'email'    => 'duplikat@example.com',
            'password' => Hash::make('password123'),
            'role'     => 'pelanggan',
        ]);

        $response = $this->post('/register', [
            'name'                  => 'User Baru',
            'email'                 => 'duplikat@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test 5: Login pelanggan berhasil dan diarahkan ke katalog.
     */
    public function test_customer_can_login_and_redirects_to_catalog(): void
    {
        $user = User::create([
            'name'     => 'Pelanggan Kopi',
            'email'    => 'pelanggan@example.com',
            'password' => Hash::make('password123'),
            'role'     => 'pelanggan',
        ]);

        $response = $this->post('/login', [
            'email'    => 'pelanggan@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/katalog');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test 6: Login admin/owner diarahkan ke dashboard owner.
     */
    public function test_admin_can_login_and_redirects_to_owner_dashboard(): void
    {
        $admin = User::create([
            'name'     => 'Farrel Owner',
            'email'    => 'owner@example.com',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
        ]);

        $response = $this->post('/login', [
            'email'    => 'owner@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/owner/dashboard');
        $this->assertAuthenticatedAs($admin);
    }

    /**
     * Test 7: Login kurir diarahkan ke dashboard tugas kurir.
     */
    public function test_courier_can_login_and_redirects_to_courier_dashboard(): void
    {
        $courier = User::create([
            'name'     => 'Ilham Kurir',
            'email'    => 'kurir@example.com',
            'password' => Hash::make('password123'),
            'role'     => 'kurir',
        ]);

        $response = $this->post('/login', [
            'email'    => 'kurir@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/kurir/tugas-baru');
        $this->assertAuthenticatedAs($courier);
    }

    /**
     * Test 8: Login gagal dengan kredensial salah tanpa membocorkan eksistensi email.
     */
    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->post('/login', [
            'email'    => 'tidakada@example.com',
            'password' => 'salahpassword',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'Email atau kata sandi yang Anda masukkan salah.',
        ]);
        $this->assertGuest();
    }

    /**
     * Test 9: Logout berhasil dan mengakhiri sesi pengguna.
     */
    public function test_user_can_logout(): void
    {
        $user = User::create([
            'name'     => 'User Aktif',
            'email'    => 'aktif@example.com',
            'password' => Hash::make('password123'),
            'role'     => 'pelanggan',
        ]);

        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    /**
     * Test 10: Pelanggan tidak dapat mengakses halaman owner (FR-1.5: 403 Forbidden).
     */
    public function test_customer_cannot_access_owner_pages_and_gets_403(): void
    {
        $customer = User::create([
            'name'     => 'Pelanggan Biasa',
            'email'    => 'biasa@example.com',
            'password' => Hash::make('password123'),
            'role'     => 'pelanggan',
        ]);

        $this->actingAs($customer);

        $response = $this->get('/owner/dashboard');
        $response->assertStatus(403);
    }

    /**
     * Test 11: Tamu (Guest) yang mengakses halaman terproteksi diarahkan ke login (FR-1.5).
     */
    public function test_guest_accessing_protected_page_is_redirected_to_login(): void
    {
        $response = $this->get('/owner/dashboard');
        $response->assertRedirect('/login');
    }
}
