<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
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

// Pembeli - Publik (FR-3.1: Dapat diakses tanpa login)
Route::get('/landing', fn() => view('Desktop Landing Page'))->name('landing');
Route::get('/katalog', [CatalogController::class, 'index'])->name('katalog.index');

// Pembeli - Fitur Terlindungi (Wajib Login Sesuai PRD)
Route::middleware(['auth'])->group(function () {
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

    // 3. RIWAYAT & STATUS PESANAN PEMBELI (F8 - PPL 14)
    Route::get('/riwayat-pesanan', [OrderController::class, 'index'])->name('orders');
    Route::get('/riwayat-pesanan/{kode}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/order-tracking', [OrderController::class, 'tracking'])->name('tracking');
    Route::get('/order-tracking/{kode}', [OrderController::class, 'show'])->name('tracking.detail');
    Route::get('/tracking', [OrderController::class, 'tracking']);
    Route::get('/tracking/{kode}', [OrderController::class, 'show']);

    // Endpoint API Riwayat & Status Pesanan Sisi Pembeli (PPL 14)
    Route::get('/api/orders', [OrderController::class, 'index'])->name('api.orders.index');
    Route::get('/api/orders/{kode}', [OrderController::class, 'show'])->name('api.orders.show');

    // Transaksi & Checkout Pelanggan (PPL 16 / PPL-20)
    Route::middleware(['role:pelanggan'])->group(function () {
        Route::get('/checkout', [OrderController::class, 'showCheckout'])->name('checkout');
        Route::post('/checkout', [OrderController::class, 'checkout'])->name('checkout.process');
        Route::post('/api/orders', [OrderController::class, 'checkout'])->name('api.orders.store');
        Route::post('/api/checkout', [OrderController::class, 'checkout'])->name('api.checkout.store');
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    });

    Route::get('/pembayaran-qris', fn() => view('Pembayaran QRIS Page'))->name('payment');
    Route::get('/profil-pelanggan', fn() => view('Profil Pelanggan Page'))->name('profile');
});

// =========================================================================
// 3. FITUR KHUSUS KURIR (FR-1.5 - Terproteksi Role Kurir)
// =========================================================================
Route::middleware(['role:kurir'])->group(function () {
    Route::get('/kurir/tugas-baru', fn() => view('Dashboard Kurir Tab Tugas Baru Page'));
    Route::get('/kurir/selesai', fn() => view('Dashboard Kurir Tab Selesai Page'));
    Route::get('/kurir/profil', fn() => view('Profil Kurir Page'));
    Route::get('/profil-kurir', fn() => view('Profil Kurir Page'));
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
    Route::get('/owner/dashboard', fn() => view('Owner Dashboard & Mini POS Page'));
    Route::get('/owner/update-lokasi', fn() => view('Pop Up Update Lokasi Lapak Page'));
    
    // PPL-29: Prediksi Penjualan (Moving Average 3 Minggu)
    Route::get('/owner/prediksi', function (\Illuminate\Http\Request $request) {
        // Mendukung testing query ?empty=1 untuk melihat state data belum cukup
        $isDataSufficient = !$request->has('empty');

        $predictions = $isDataSufficient ? [
            [
                'nama' => 'Titik Temu',
                'rasa' => 'Kopi Susu Gula Aren',
                'stok' => 8,
                'minggu_1' => 52,
                'minggu_2' => 58,
                'minggu_3' => 64,
                'estimasi' => 58.0,
                'saran_produksi' => 2,
            ],
            [
                'nama' => 'Titik Hitam',
                'rasa' => 'Americano',
                'stok' => 12,
                'minggu_1' => 30,
                'minggu_2' => 32,
                'minggu_3' => 31,
                'estimasi' => 31.0,
                'saran_produksi' => 0,
            ],
            [
                'nama' => 'Titik Manja',
                'rasa' => 'Kopi Karamel',
                'stok' => 4,
                'minggu_1' => 45,
                'minggu_2' => 48,
                'minggu_3' => 50,
                'estimasi' => 47.7,
                'saran_produksi' => 6,
            ],
            [
                'nama' => 'Titik Jenuh',
                'rasa' => 'Coklat Susu',
                'stok' => 2,
                'minggu_1' => 20,
                'minggu_2' => 24,
                'minggu_3' => 25,
                'estimasi' => 23.0,
                'saran_produksi' => 8,
            ],
        ] : [];

        return view('owner.prediksi', compact('isDataSufficient', 'predictions'));
    })->name('owner.prediksi');

    // PPL-29: Riwayat Penjualan & Ekspor Excel
    Route::get('/owner/riwayat-penjualan', function (\Illuminate\Http\Request $request) {
        $periode = $request->query('periode', 'bulanan');

        $summary = [
            'total_botol' => 358,
            'total_omzet' => 3740000,
            'varian_terlaris' => 'Titik Temu',
        ];

        $chartLabels = ['1 Nov', '3 Nov', '5 Nov', '7 Nov', '9 Nov', '11 Nov', '13 Nov'];
        $chartData = [42, 55, 38, 65, 50, 48, 60];

        $sales = [
            [
                'tanggal' => '2026-11-13',
                'varian' => 'Titik Temu',
                'sumber' => 'Online',
                'jumlah' => 24,
                'total_omzet' => 240000,
            ],
            [
                'tanggal' => '2026-11-13',
                'varian' => 'Titik Hitam',
                'sumber' => 'POS Lapak',
                'jumlah' => 15,
                'total_omzet' => 120000,
            ],
            [
                'tanggal' => '2026-11-12',
                'varian' => 'Titik Manja',
                'sumber' => 'Online',
                'jumlah' => 18,
                'total_omzet' => 180000,
            ],
            [
                'tanggal' => '2026-11-12',
                'varian' => 'Titik Jenuh',
                'sumber' => 'POS Lapak',
                'jumlah' => 10,
                'total_omzet' => 120000,
            ],
            [
                'tanggal' => '2026-11-11',
                'varian' => 'Titik Temu',
                'sumber' => 'Online',
                'jumlah' => 30,
                'total_omzet' => 300000,
            ],
        ];

        return view('owner.riwayat-penjualan', compact('periode', 'summary', 'chartLabels', 'chartData', 'sales'));
    })->name('owner.riwayat-penjualan');

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
// 5. DEV & TESTING PREVIEW HALAMAN ERROR & VALIDASI (PPL-24)
// =========================================================================
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



