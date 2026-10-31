<?php


use App\Http\Controllers\AuthController;
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
    return view('welcome');
});

// Autentikasi & Akun
Route::get('/login-pembeli', fn() => view('Login Sebagai Pembeli Page'));
Route::get('/login-kurir', fn() => view('Login Sebagai Kurir Page'));
Route::get('/login-owner', fn() => view('Login Sebagai Owner Page'));
Route::get('/register', fn() => view('Register Page'));

// Pembeli
Route::get('/landing', fn() => view('Mobile Landing Page'));
Route::get('/katalog', fn() => view('Menu & Catalog Page'));
Route::get('/desktop-katalog', fn() => view('Desktop Catalog Product Page'));
Route::get('/cart', fn() => view('Cart Page'));
Route::get('/checkout', fn() => view('Cart & Checkout Page'));
Route::get('/pembayaran-qris', fn() => view('Pembayaran QRIS Page'));
Route::get('/tracking', fn() => view('Order Tracking & Live Status'));
Route::get('/order-tracking', fn() => view('Order Tracking & Live Status'));
Route::get('/riwayat-pesanan', fn() => view('Riwayat Pesanan Page'));
Route::get('/profil-pelanggan', fn() => view('Profil Pelanggan Page'));

// Kurir
Route::get('/kurir/tugas-baru', fn() => view('Dashboard Kurir Tab Tugas Baru Page'));
Route::get('/kurir/selesai', fn() => view('Dashboard Kurir Tab Selesai Page'));
Route::get('/kurir/profil', fn() => view('Profil Kurir Page'));
Route::get('/profil-kurir', fn() => view('Profil Kurir Page'));

// Owner
Route::get('/owner/dashboard', fn() => view('Owner Dashboard & Mini POS Page'));
// Owner - Kelola Produk (F2 & PPL-13)
Route::prefix('owner/produk')->name('owner.produk.')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::post('/', [ProductController::class, 'store'])->name('store');
    Route::put('/{product}', [ProductController::class, 'update'])->name('update');
    Route::patch('/{product}/stock', [ProductController::class, 'updateStock'])->name('stock');
    Route::patch('/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('toggle-status');
    Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
});

// API Routes untuk Kelola Produk (Sesuai Kontrak Dokumen Tabel 7)
Route::prefix('api/admin/products')->group(function () {
    Route::post('/', [ProductController::class, 'store']);
    Route::put('/{product}', [ProductController::class, 'update']);
    Route::patch('/{product}/status', [ProductController::class, 'toggleStatus']);
    Route::patch('/{product}/stock', [ProductController::class, 'updateStock']);
});
Route::get('/owner/update-lokasi', fn() => view('Pop Up Update Lokasi Lapak Page'));
Route::get('/owner/prediksi', fn() => view('Prediksi Moving Average Page'));
Route::get('/owner/profil', fn() => view('Profil Owner Page'));
Route::get('/profil-owner', fn() => view('Profil Owner Page'));

