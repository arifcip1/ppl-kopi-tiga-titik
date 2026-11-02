<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
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



Route::get('/', fn() => view('Desktop Landing Page'))->name('home');

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
    Route::get('/cart', fn() => view('Cart Page'))->name('cart');
    Route::get('/checkout', fn() => view('Cart & Checkout Page'))->name('checkout');
    Route::get('/pembayaran-qris', fn() => view('Pembayaran QRIS Page'))->name('payment');
    Route::get('/tracking', fn() => view('Order Tracking & Live Status'));
    Route::get('/order-tracking', fn() => view('Order Tracking & Live Status'))->name('tracking');
    Route::get('/riwayat-pesanan', fn() => view('Riwayat Pesanan Page'))->name('orders');
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
// Owner - Kelola Produk (F2 & PPL-13)
Route::prefix('owner/produk')->name('owner.produk.')->group(function () {
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
    Route::get('/owner/prediksi', fn() => view('Prediksi Moving Average Page'));
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

