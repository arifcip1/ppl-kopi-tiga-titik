<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SalesHistory;
use App\Models\Setting;
use App\Models\ShippingLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Ppl41AuthOrderIntegrationAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_registration_saves_normalized_telepon_and_supports_whatsapp_login(): void
    {
        $response = $this->post('/register', [
            'name' => 'Pelanggan WhatsApp',
            'email' => 'wa.customer@example.com',
            'phone' => '+62 812-3456-7890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/katalog');

        $user = User::where('email', 'wa.customer@example.com')->firstOrFail();
        $this->assertSame('081234567890', $user->telepon);
        $this->assertSame('pelanggan', $user->role);

        $this->post('/logout');
        $this->assertGuest();

        // Login menggunakan nomor WhatsApp tanpa awalan 0 (sesuai input UI +62 / 08)
        $loginResponse = $this->post('/login', [
            'email' => '812-3456-7890',
            'password' => 'password123',
            'role' => 'pembeli',
        ]);

        $loginResponse->assertRedirect('/katalog');
        $this->assertAuthenticatedAs($user);
    }

    public function test_owner_created_courier_persists_telepon_and_validates_role_on_phone_login(): void
    {
        $owner = User::factory()->create(['role' => 'admin']);

        $storeResponse = $this->actingAs($owner)->post('/owner/couriers', [
            'name' => 'Kurir Satu',
            'email' => 'kurir1@kopitigatitik.test',
            'telepon' => '081398765432',
            'password' => 'kurir12345',
            'password_confirmation' => 'kurir12345',
        ]);

        $storeResponse->assertSessionHasNoErrors();

        $courier = User::where('email', 'kurir1@kopitigatitik.test')->firstOrFail();
        $this->assertSame('kurir', $courier->role);
        $this->assertSame('081398765432', $courier->telepon);

        // Logout owner, lalu coba login memakai nomor WA kurir tetapi salah memilih role owner
        $this->post('/logout');

        $wrongRoleResponse = $this->post('/login', [
            'email' => '081398765432',
            'password' => 'kurir12345',
            'role' => 'owner',
        ]);

        $wrongRoleResponse->assertSessionHasErrors('email');
        $this->assertGuest();

        // Login dengan nomor WA kurir dan role kurir yang benar
        $validLogin = $this->post('/login', [
            'email' => '+62 813-9876-5432',
            'password' => 'kurir12345',
            'role' => 'kurir',
        ]);

        $validLogin->assertRedirect('/kurir/tugas-baru');
        $this->assertAuthenticatedAs($courier);
    }

    public function test_payment_status_and_payment_page_enforce_strict_rbac_against_courier_and_other_customers(): void
    {
        $customerA = User::factory()->create(['role' => 'pelanggan']);
        $customerB = User::factory()->create(['role' => 'pelanggan']);
        $courier = User::factory()->create(['role' => 'kurir']);
        $owner = User::factory()->create(['role' => 'admin']);

        $order = Order::create([
            'kode' => 'KTT-RBAC-001',
            'sumber_transaksi' => 'online',
            'metode_pemenuhan' => 'pengiriman',
            'user_id' => $customerA->id,
            'status' => 'menunggu_pembayaran',
            'subtotal' => 25000,
            'ongkir' => 5000,
            'total' => 30000,
            'nama_pelanggan' => $customerA->name,
            'nama_penerima' => $customerA->name,
            'telepon' => '081200000001',
            'alamat' => 'Jl. Majapahit No. 10, Kota Mojokerto',
            'expires_at' => now()->addMinutes(10),
        ]);

        Payment::create([
            'order_id' => $order->id,
            'referensi_provider' => 'QRIS-RBAC-001',
            'metode_pembayaran' => 'qris',
            'status' => 'pending',
            'jumlah' => 30000,
            'kedaluwarsa_pada' => now()->addMinutes(10),
        ]);

        // Kurir dilarang mengecek API status pembayaran maupun membuka halaman QRIS pesanan pelanggan
        $this->actingAs($courier)
            ->getJson("/api/orders/{$order->kode}/payment-status")
            ->assertStatus(403);

        $this->actingAs($courier)
            ->get("/pembayaran-qris/{$order->kode}")
            ->assertStatus(403);

        // Pelanggan lain dilarang mengakses status maupun halaman QRIS
        $this->actingAs($customerB)
            ->getJson("/api/orders/{$order->kode}/payment-status")
            ->assertStatus(403);

        $this->actingAs($customerB)
            ->get("/pembayaran-qris/{$order->kode}")
            ->assertStatus(403);

        // Pemilik pesanan dan admin berhak memantau status pembayaran
        $this->actingAs($customerA)
            ->getJson("/api/orders/{$order->kode}/payment-status")
            ->assertOk();

        $this->actingAs($owner)
            ->getJson("/api/orders/{$order->kode}/payment-status")
            ->assertOk();
    }

    public function test_order_lookup_guards_against_mysql_numeric_prefix_coercion(): void
    {
        $customer = User::factory()->create(['role' => 'pelanggan']);
        $order = Order::create([
            'kode' => 'KTT-LOOKUP-001',
            'sumber_transaksi' => 'online',
            'metode_pemenuhan' => 'ambil_sendiri',
            'user_id' => $customer->id,
            'status' => 'diproses',
            'subtotal' => 20000,
            'ongkir' => 0,
            'total' => 20000,
            'nama_pelanggan' => $customer->name,
            'nama_penerima' => $customer->name,
            'telepon' => '081200000002',
            'alamat' => 'Lapak Utama',
            'batas_pengambilan' => now()->addHours(24),
        ]);

        Payment::create([
            'order_id' => $order->id,
            'referensi_provider' => 'QRIS-LOOKUP-001',
            'metode_pembayaran' => 'qris',
            'status' => 'berhasil',
            'jumlah' => 20000,
            'waktu_verifikasi' => now(),
        ]);

        $coercedIdentifier = $order->id . '-INVALID-SUFFIX';

        $this->actingAs($customer)
            ->getJson("/api/orders/{$coercedIdentifier}/payment-status")
            ->assertStatus(404);

        $this->actingAs($customer)
            ->getJson("/api/orders/{$coercedIdentifier}")
            ->assertStatus(404);

        $this->actingAs($customer)
            ->getJson("/api/orders/{$coercedIdentifier}/nota")
            ->assertStatus(404);

        // Identifier numerik murni dan kode persis tetap valid
        $this->actingAs($customer)
            ->getJson("/api/orders/{$order->id}/payment-status")
            ->assertOk();

        $this->actingAs($customer)
            ->getJson("/api/orders/{$order->kode}/nota")
            ->assertOk();
    }

    public function test_unpaid_and_cancelled_orders_display_accurate_status_in_nota_and_tracking(): void
    {
        $customer = User::factory()->create(['role' => 'pelanggan']);

        $unpaidOrder = Order::create([
            'kode' => 'KTT-UNPAID-001',
            'sumber_transaksi' => 'online',
            'metode_pemenuhan' => 'ambil_sendiri',
            'user_id' => $customer->id,
            'status' => 'menunggu_pembayaran',
            'subtotal' => 18000,
            'ongkir' => 0,
            'total' => 18000,
            'nama_pelanggan' => $customer->name,
            'nama_penerima' => $customer->name,
            'telepon' => '081200000003',
            'alamat' => 'Lapak Utama',
            'expires_at' => now()->addMinutes(10),
        ]);

        Payment::create([
            'order_id' => $unpaidOrder->id,
            'referensi_provider' => 'QRIS-UNPAID-001',
            'metode_pembayaran' => 'qris',
            'status' => 'pending',
            'jumlah' => 18000,
            'kedaluwarsa_pada' => now()->addMinutes(10),
        ]);

        $notaPending = $this->actingAs($customer)->get(route('orders.nota', $unpaidOrder->kode));
        $notaPending->assertOk()
            ->assertSee('MENUNGGU PEMBAYARAN')
            ->assertDontSee('TERVERIFIKASI');

        $trackPending = $this->actingAs($customer)->get(route('tracking.detail', $unpaidOrder->kode));
        $trackPending->assertOk()
            ->assertSee('Menunggu Pembayaran')
            ->assertSee('Bayar Sekarang via QRIS')
            ->assertSee(route('payment', $unpaidOrder->kode))
            ->assertDontSee('>Terverifikasi<');

        // Setelah kedaluwarsa/dibatalkan, tidak boleh tampil Terverifikasi
        $unpaidOrder->update(['status' => 'dibatalkan']);
        $unpaidOrder->payment->update(['status' => 'kedaluwarsa']);

        $notaExpired = $this->actingAs($customer)->get(route('orders.nota', $unpaidOrder->kode));
        $notaExpired->assertOk()
            ->assertSee('KEDALUWARSA')
            ->assertSee('Belum Dibayar / Dibatalkan')
            ->assertDontSee('TERVERIFIKASI');

        $trackExpired = $this->actingAs($customer)->get(route('tracking.detail', $unpaidOrder->kode));
        $trackExpired->assertOk()
            ->assertSee('Kedaluwarsa')
            ->assertDontSee('Lunas Terverifikasi');
    }

    public function test_owner_detail_and_courier_views_use_telepon_dynamic_stats_and_notifications(): void
    {
        $owner = User::factory()->create(['role' => 'admin']);
        $courier = User::factory()->create([
            'role' => 'kurir',
            'telepon' => '081900001111',
        ]);
        $customer = User::factory()->create([
            'role' => 'pelanggan',
            'telepon' => '085611122233',
        ]);

        $product = Product::create([
            'nama' => 'Tiga Titik Aren',
            'rasa' => 'aren',
            'komposisi' => 'Espresso, Susu, Gula Aren',
            'deskripsi' => 'Kopi susu aren 250ml',
            'keawetan' => '3 hari di kulkas',
            'harga' => 15000,
            'stok' => 10,
            'aktif' => true,
        ]);

        // 2 pesanan selesai untuk kurir
        foreach ([1, 2] as $idx) {
            $doneOrder = Order::create([
                'kode' => "KTT-DONE-00{$idx}",
                'sumber_transaksi' => 'online',
                'metode_pemenuhan' => 'pengiriman',
                'user_id' => $customer->id,
                'courier_id' => $courier->id,
                'status' => 'selesai',
                'subtotal' => 15000,
                'ongkir' => 5000,
                'total' => 20000,
                'nama_pelanggan' => $customer->name,
                'nama_penerima' => $customer->name,
                'telepon' => null,
                'alamat' => 'Jl. Brawijaya No. 12',
            ]);

            OrderItem::create([
                'order_id' => $doneOrder->id,
                'product_id' => $product->id,
                'nama_produk' => $product->nama,
                'rasa' => $product->rasa,
                'jumlah' => 1,
                'harga_satuan' => 15000,
                'subtotal' => 15000,
            ]);
        }

        // 1 tugas pengiriman aktif tanpa kolom order.telepon eksplisit (uji fallback user.telepon)
        $activeOrder = Order::create([
            'kode' => 'KTT-ACTIVE-001',
            'sumber_transaksi' => 'online',
            'metode_pemenuhan' => 'pengiriman',
            'user_id' => $customer->id,
            'courier_id' => $courier->id,
            'status' => 'dikirim',
            'subtotal' => 15000,
            'ongkir' => 5000,
            'total' => 20000,
            'nama_pelanggan' => $customer->name,
            'nama_penerima' => $customer->name,
            'telepon' => null,
            'alamat' => 'Jl. Pahlawan No. 99',
        ]);

        Payment::create([
            'order_id' => $activeOrder->id,
            'referensi_provider' => 'QRIS-ACTIVE-001',
            'metode_pembayaran' => 'qris',
            'status' => 'berhasil',
            'jumlah' => 20000,
            'waktu_verifikasi' => now(),
        ]);

        // Owner detail JSON mengembalikan telepon akun & metode_pembayaran yang benar
        $ownerDetail = $this->actingAs($owner)->getJson("/owner/orders/{$activeOrder->id}/detail");
        $ownerDetail->assertOk()
            ->assertJsonPath('data.customer.telepon', '085611122233')
            ->assertJsonPath('data.telepon_penerima', '085611122233')
            ->assertJsonPath('data.payment.metode_pembayaran', 'qris');

        // Kurir tugas baru memakai fallback telepon customer & link wa.me valid
        $tugasResponse = $this->actingAs($courier)->get('/kurir/tugas-baru');
        $tugasResponse->assertOk()
            ->assertSee('085611122233')
            ->assertSee('https://wa.me/6285611122233');

        // Kurir selesai memakai fallback telepon customer
        $selesaiResponse = $this->actingAs($courier)->get('/kurir/selesai');
        $selesaiResponse->assertOk()
            ->assertSee('085611122233');

        // Profil kurir menampilkan total selesai riil (2), notifikasi web aktif, bukan angka hardcoded 142
        $profilResponse = $this->actingAs($courier)->get('/kurir/profil');
        $profilResponse->assertOk()
            ->assertViewHas('stats', fn ($stats) => ($stats['total_completed'] ?? null) === 2)
            ->assertViewHas('notifications', fn ($notifs) => count($notifs) >= 1)
            ->assertSee('Notifikasi Tugas')
            ->assertDontSee('>142<', false);
    }

    public function test_expire_orders_command_transitions_overdue_pickup_and_syncs_sales_history(): void
    {
        $customer = User::factory()->create(['role' => 'pelanggan']);
        $product = Product::create([
            'nama' => 'Tiga Titik Matcha',
            'rasa' => 'matcha',
            'komposisi' => 'Matcha, Susu',
            'deskripsi' => 'Matcha 250ml',
            'keawetan' => '3 hari di kulkas',
            'harga' => 17000,
            'stok' => 15,
            'aktif' => true,
        ]);

        $order = Order::create([
            'kode' => 'KTT-OVERDUE-PICKUP',
            'sumber_transaksi' => 'online',
            'metode_pemenuhan' => 'ambil_sendiri',
            'user_id' => $customer->id,
            'status' => 'diproses',
            'subtotal' => 34000,
            'ongkir' => 0,
            'total' => 34000,
            'nama_pelanggan' => $customer->name,
            'nama_penerima' => $customer->name,
            'telepon' => '081200009999',
            'alamat' => 'Lapak Kopi Tiga Titik',
            'batas_pengambilan' => now()->subMinutes(5),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'nama_produk' => $product->nama,
            'rasa' => $product->rasa,
            'jumlah' => 2,
            'harga_satuan' => 17000,
            'subtotal' => 34000,
        ]);

        $this->artisan('orders:expire')->assertExitCode(0);
        // Eksekusi kedua bersifat idempoten tanpa duplikasi sales_history
        $this->artisan('orders:expire')->assertExitCode(0);

        $order->refresh();
        $this->assertSame('selesai', $order->status);

        $this->assertDatabaseHas('order_status_history', [
            'order_id' => $order->id,
            'status_baru' => 'selesai',
            'diubah_oleh' => null,
        ]);

        $sales = SalesHistory::where('product_id', $product->id)
            ->where('sumber', 'transaksi')
            ->first();

        $this->assertNotNull($sales);
        $this->assertSame(2, $sales->jumlah_terjual);
    }

    public function test_sales_history_and_excel_export_enforce_owner_rbac(): void
    {
        $owner = User::factory()->create(['role' => 'admin']);
        $courier = User::factory()->create(['role' => 'kurir']);
        $customer = User::factory()->create(['role' => 'pelanggan']);

        $product = Product::create([
            'nama' => 'Tiga Titik Regal',
            'rasa' => 'regal',
            'komposisi' => 'Susu, Biskuit Regal',
            'deskripsi' => 'Milk regal 250ml',
            'keawetan' => '3 hari di kulkas',
            'harga' => 16000,
            'stok' => 20,
            'aktif' => true,
        ]);

        SalesHistory::create([
            'product_id' => $product->id,
            'tanggal' => today(),
            'jumlah_terjual' => 3,
            'sumber' => 'transaksi',
        ]);

        $this->actingAs($customer)
            ->get('/owner/riwayat-penjualan/export?periode=harian')
            ->assertStatus(403);

        $this->actingAs($courier)
            ->get('/owner/riwayat-penjualan/export?periode=harian')
            ->assertStatus(403);

        $reportPage = $this->actingAs($owner)
            ->get('/owner/riwayat-penjualan?periode=harian');
        $reportPage->assertOk()
            ->assertSee('Tiga Titik Regal');

        $exportResponse = $this->actingAs($owner)
            ->get('/owner/riwayat-penjualan/export?periode=harian');
        $exportResponse->assertOk();
        $this->assertStringContainsString(
            'spreadsheetml.sheet',
            (string) $exportResponse->headers->get('content-type')
        );
    }

    public function test_riwayat_pesanan_renders_dynamic_active_lapak_and_operating_hours(): void
    {
        $customer = User::factory()->create(['role' => 'pelanggan']);
        $location = ShippingLocation::create([
            'nama_lokasi' => 'Lapak Taman Benteng Pancasila',
            'alamat' => 'Jl. Benteng Pancasila, Kota Mojokerto',
            'latitude' => -7.4701000,
            'longitude' => 112.4401000,
            'aktif' => true,
        ]);

        Setting::updateOrCreate(['key' => 'lokasi_pengiriman_aktif_id'], ['value' => (string) $location->id]);
        Setting::updateOrCreate(['key' => 'jam_buka'], ['value' => '08:00']);
        Setting::updateOrCreate(['key' => 'jam_tutup'], ['value' => '21:00']);
        Setting::updateOrCreate(['key' => 'status_lapak'], ['value' => 'tutup']);

        $response = $this->actingAs($customer)->get('/riwayat-pesanan');

        $response->assertOk()
            ->assertSee('Lapak Tutup: Lapak Taman Benteng Pancasila')
            ->assertSee('08:00 - 21:00 WIB');
    }
}
