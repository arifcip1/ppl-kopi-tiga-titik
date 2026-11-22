<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CourierDashboardController;
use App\Http\Controllers\CustomerProfileController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OwnerDashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShippingController;
use Illuminate\Support\Facades\Route;

// Halaman login
Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

// Proses login
Route::post('/login', [AuthController::class, 'login'])
    ->name('login.process');

// Logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');



Route::get('/', function () {
    if (\Illuminate\Support\Facades\Auth::check()) {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (in_array($user->role, ['admin', 'owner'])) {
            return redirect('/owner/dashboard');
        } elseif ($user->role === 'kurir') {
            return redirect('/kurir/tugas-baru');
        } else {
            return redirect('/katalog');
        }
    }
    return view('Desktop Landing Page');
})->name('home');

// =========================================================================
// 1. AUTENTIKASI PENGGUNA (FR-1.3, FR-1.4 - PPL 07)
// =========================================================================
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Alias Route Akses Langsung Sesuai Role
Route::get('/login-pembeli', fn() => redirect('/login'));
Route::get('/login-kurir', fn() => redirect('/login'));
Route::get('/login-owner', fn() => redirect('/login'));

// Pembeli - Publik (FR-3.1: Dapat diakses tanpa login; Owner & Kurir diarahkan ke dashboard perannya)
Route::get('/landing', function () {
    if (\Illuminate\Support\Facades\Auth::check()) {
        $role = \Illuminate\Support\Facades\Auth::user()->role;
        if (in_array($role, ['admin', 'owner'])) {
            return redirect()->route('owner.dashboard');
        }
        if ($role === 'kurir') {
            return redirect()->route('kurir.tugas-baru');
        }
    }
    return view('Desktop Landing Page');
})->name('landing');
Route::get('/katalog', [CatalogController::class, 'index'])->name('katalog.index');

// Pembeli - Fitur Terlindungi (Wajib Login Sesuai PRD)
Route::middleware(['auth'])->group(function () {
    // Nota Digital & Status Pembayaran (Pelanggan pemilik pesanan atau Admin/Owner)
    Route::get('/orders/{kode}/nota', [OrderController::class, 'nota'])->name('orders.nota');
    Route::get('/nota/{kode}', [OrderController::class, 'nota'])->name('nota');
    Route::get('/api/orders/{kode}/nota', [OrderController::class, 'nota'])->name('api.orders.nota');

    // Endpoint API Riwayat, Detail, & Status Pembayaran (otorisasi dicek di controller)
    Route::get('/api/orders', [OrderController::class, 'index'])->name('api.orders.index');
    Route::get('/api/orders/{kode}', [OrderController::class, 'show'])->name('api.orders.show');
    Route::get('/api/orders/{identifier}/payment-status', [PaymentController::class, 'status'])->name('api.orders.payment-status');
    Route::get('/api/orders/{identifier}/payment', [PaymentController::class, 'status'])->name('api.orders.payment');

    // Endpoint API Quote Ongkos Kirim (PPL 19 & PPL 25)
    Route::post('/api/shipping/quote', [ShippingController::class, 'quote'])->name('api.shipping.quote');

    // Halaman & Aksi KHUSUS PELANGGAN — Owner/Admin & Kurir dilarang masuk tampilan Pelanggan (403 Forbidden)
    Route::middleware(['role:pelanggan'])->group(function () {
        // 2. KERANJANG BELANJA (F4 - PPL 13)
        Route::get('/cart', [CartController::class, 'index'])->name('cart');
        Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
        Route::patch('/cart/items/{id}', [CartController::class, 'update'])->name('cart.items.update');
        Route::delete('/cart/items/{id}', [CartController::class, 'destroy'])->name('cart.items.destroy');

        // Endpoint API Keranjang Sesuai Kontrak Dokumen Penyelarasan
        Route::get('/api/cart', [CartController::class, 'index'])->name('api.cart.index');
        Route::post('/api/cart/items', [CartController::class, 'store'])->name('api.cart.store');
        Route::patch('/api/cart/items/{id}', [CartController::class, 'update'])->name('api.cart.update');
        Route::delete('/api/cart/items/{id}', [CartController::class, 'destroy'])->name('api.cart.destroy');

        // 3. RIWAYAT & STATUS PELACAKAN PESANAN PEMBELI (F8 / F13 - PPL 14 & PPL 24)
        Route::get('/riwayat-pesanan', [OrderController::class, 'index'])->name('orders');
        Route::get('/riwayat-pesanan/{kode}', [OrderController::class, 'show'])->name('orders.show');
        Route::get('/order-tracking', [OrderController::class, 'tracking'])->name('tracking');
        Route::get('/order-tracking/{kode}', [OrderController::class, 'show'])->name('tracking.detail');
        Route::get('/tracking', [OrderController::class, 'tracking']);
        Route::get('/tracking/{kode}', [OrderController::class, 'show']);

        // 4. TRANSAKSI, CHECKOUT & PEMBAYARAN QRIS PELANGGAN (PPL 16, PPL 23, PPL 27)
        Route::get('/checkout', [OrderController::class, 'showCheckout'])->name('checkout');
        Route::post('/checkout', [OrderController::class, 'checkout'])->name('checkout.process');
        Route::post('/api/orders', [OrderController::class, 'checkout'])->name('api.orders.store');
        Route::post('/api/checkout', [OrderController::class, 'checkout'])->name('api.checkout.store');
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::get('/pembayaran-qris/{kode?}', [PaymentController::class, 'show'])->name('payment');
        Route::post('/api/orders/{identifier}/simulate-payment', [PaymentController::class, 'simulateSuccess'])->name('api.orders.simulate-payment');

        // 5. PROFIL & ALAMAT PENGIRIMAN UTAMA PELANGGAN
        Route::get('/profil-pelanggan', [CustomerProfileController::class, 'show'])->name('profile');
        Route::put('/profil-pelanggan/alamat', [CustomerProfileController::class, 'updateAddress'])->name('profile.address.update');
    });
});

// Endpoint Webhook Notifikasi Server-to-Server Midtrans Sandbox (FR-6.4 / Tanpa Autentikasi Sesi)
Route::post('/api/payments/webhook', [PaymentController::class, 'webhook'])->name('api.payments.webhook');
Route::post('/payments/webhook', [PaymentController::class, 'webhook'])->name('payments.webhook');

// =========================================================================
// 3. FITUR KHUSUS KURIR (FR-1.5, FR-8.7, FR-8.9 - PPL 27/28)
// =========================================================================
Route::middleware(['role:kurir'])->group(function () {
    Route::get('/kurir/tugas-baru', [CourierDashboardController::class, 'tugasBaru'])->name('kurir.tugas-baru');
    Route::get('/kurir/selesai', [CourierDashboardController::class, 'selesai'])->name('kurir.selesai');
    Route::post('/kurir/orders/{order}/complete', [CourierDashboardController::class, 'markComplete'])->name('kurir.orders.complete');
    Route::get('/api/kurir/tasks', [CourierDashboardController::class, 'apiTasks'])->name('api.kurir.tasks');
    Route::get('/kurir/profil', [CourierDashboardController::class, 'showProfile'])->name('kurir.profil');
    Route::put('/kurir/profil', [CourierDashboardController::class, 'updateProfile'])->name('kurir.profil.update');
    Route::get('/profil-kurir', [CourierDashboardController::class, 'showProfile']);
});

// =========================================================================
// 4. FITUR OWNER & PRODUK (F2 & PPL-13)
// =========================================================================
// Owner - Kelola Produk (F2 & PPL-13) - Terproteksi Role Admin/Owner (Bug 4 & 5)
Route::prefix('owner/produk')->middleware(['role:admin'])->name('owner.produk.')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::post('/', [ProductController::class, 'store'])->name('store');
    Route::put('/{product}', [ProductController::class, 'update'])->name('update');
    Route::patch('/{product}/stock', [ProductController::class, 'updateStock'])->name('stock');
    Route::patch('/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('toggle-status');
    Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
});

Route::middleware(['role:admin'])->group(function () {
    Route::get('/owner/dashboard', [OwnerDashboardController::class, 'index'])->name('owner.dashboard');
    Route::get('/owner/orders/{order}/detail', [OwnerDashboardController::class, 'show'])->name('owner.orders.detail');
    Route::post('/owner/orders/{order}/assign-courier', [OwnerDashboardController::class, 'assignCourier'])->name('owner.orders.assign-courier');
    Route::post('/owner/orders/{order}/complete-pickup', [OwnerDashboardController::class, 'completePickup'])->name('owner.orders.complete-pickup');

    // PPL-32: Pengelolaan Akun Kurir (US-31)
    Route::get('/owner/couriers', [OwnerDashboardController::class, 'couriersIndex'])->name('owner.couriers.index');
    Route::post('/owner/couriers', [OwnerDashboardController::class, 'couriersStore'])->name('owner.couriers.store');
    Route::delete('/owner/couriers/{user}', [OwnerDashboardController::class, 'couriersDestroy'])->name('owner.couriers.destroy');

    // PPL-32: APIs Pesanan Owner (FR-8.1 & FR-8.2)
    Route::get('/api/owner/orders', [OwnerDashboardController::class, 'apiOrdersIndex'])->name('api.owner.orders.index');
    Route::get('/api/owner/orders/{order}', [OwnerDashboardController::class, 'show'])->name('api.owner.orders.show');
    Route::post('/api/owner/orders/{order}/assign-courier', [OwnerDashboardController::class, 'assignCourier'])->name('api.owner.orders.assign-courier');
    Route::post('/api/owner/orders/{order}/complete-pickup', [OwnerDashboardController::class, 'completePickup'])->name('api.owner.orders.complete-pickup');

    // Pengaturan Lokasi & Jam Operasional Lapak (PPL-25 / PPL-30)
    Route::get('/owner/update-lokasi', [ShippingController::class, 'showLocationSettings'])->name('owner.update-lokasi');
    Route::post('/owner/update-lokasi', [ShippingController::class, 'updateLocationSettings'])->name('owner.update-lokasi.save');
    Route::patch('/owner/lokasi/{location}/select', [ShippingController::class, 'selectLocation'])->name('owner.lokasi.select');

    // PPL-30: Prediksi, Riwayat Penjualan & Ekspor Excel
    Route::get('/owner/prediksi', [OwnerDashboardController::class, 'prediksi'])->name('owner.prediksi');
    Route::get('/owner/riwayat-penjualan', [OwnerDashboardController::class, 'riwayatPenjualan'])->name('owner.riwayat-penjualan');
    Route::get('/owner/riwayat-penjualan/export', [ExportController::class, 'exportSales'])->name('owner.export.sales');

    // PPL-31 & PPL-32: POS Kasir Offline (Lapak Fisik) & Transaksi
    Route::get('/owner/pos', [PosController::class, 'index'])->name('owner.pos');
    Route::post('/owner/pos/transactions', [PosController::class, 'transaction'])->name('owner.pos.transaction');
    Route::post('/api/admin/pos/transactions', [PosController::class, 'transaction'])->name('api.admin.pos.transaction');

    Route::get('/owner/profil', fn() => view('Profil Owner Page'));
    Route::get('/profil-owner', fn() => view('Profil Owner Page'));
});

// API Routes untuk Kelola Produk (Sesuai Kontrak Dokumen Tabel 7)
Route::prefix('api/admin/products')->middleware(['role:admin'])->group(function () {
    Route::post('/', [ProductController::class, 'store']);
    Route::put('/{product}', [ProductController::class, 'update']);
    Route::patch('/{product}/status', [ProductController::class, 'toggleStatus']);
    Route::patch('/{product}/stock', [ProductController::class, 'updateStock']);
});
// API Routes untuk Katalog Produk Publik (Sesuai Kontrak Dokumen PPL 01 Bagian 7)
Route::prefix('api/products')->group(function () {
    Route::get('/', [CatalogController::class, 'apiIndex'])->name('api.products.index');
    Route::get('/{product}', [CatalogController::class, 'apiShow'])->name('api.products.show');
});

// =========================================================================
// 6. NOTIFIKASI WEB DINAMIS (F14 / FR-14 / BR-19 - PPL 28)
// =========================================================================
Route::middleware(['auth'])->get('/api/notifications', function (\Illuminate\Http\Request $request, \App\Services\WebNotificationService $notificationService) {
    return response()->json([
        'success' => true,
        'data'    => $notificationService->getNotificationsForUser($request->user()),
    ]);
})->name('api.notifications');

Route::get('/dev/error/{code}', function ($code) {
    abort((int) $code);
})->where('code', '403|404|419|500')->name('dev.error');

Route::get('/dev/test-alerts', fn() => view('dev-test-alerts'))->name('dev.test-alerts');

Route::get('/dev/trigger-flash', function (\Illuminate\Http\Request $request) {
    $type = $request->query('type', 'success');

    if ($type === 'validation') {
        return redirect('/dev/test-alerts')->withErrors([
            'nama' => 'Nama varian produk kopi tidak boleh kosong.',
            'harga' => 'Harga jual harus berupa angka minimal Rp 1.000.',
            'stok' => 'Jumlah stok fisik tidak boleh bernilai negatif.',
        ]);
    }

    $messages = [
        'success' => 'Menu baru "Titik Cerita" berhasil disimpan ke katalog!',
        'error' => 'Gagal memperbarui status produk, silakan periksa data.',
        'warning' => 'Peringatan: Stok varian kopi botol tersisa kurang dari 5 botol.',
    ];

    return redirect('/dev/test-alerts')->with($type, $messages[$type] ?? 'Notifikasi percobaan.');
});



