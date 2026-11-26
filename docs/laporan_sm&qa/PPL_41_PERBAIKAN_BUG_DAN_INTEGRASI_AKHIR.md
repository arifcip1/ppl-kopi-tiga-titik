# Dokumentasi Perbaikan Bug & Integrasi Akhir (WBS PPL 41 / Jira PPL-45)

**Branch:** `feature/PPL-45-perbaikan-auth-dan-alur-pesanan`  
**Owner Branch (O4):** Ahmad Dafi Zidni Alfarisi (240411100027)  
**Ruang Lingkup:** Perbaikan Bug Autentikasi & Hak Akses Owner, Kurir, serta Pelanggan; Integrasi Status Pembayaran, Pesanan, Pengiriman, Pengambilan Mandiri, dan Stok; Validasi Penugasan Kurir, Notifikasi Web, Riwayat Penjualan, Ekspor Excel, serta Dokumentasi Fitur.

---

## 1. Ringkasan Perbaikan Autentikasi & Manajemen Akun

1. **Mass Assignment Nomor Telepon Kurir & Pelanggan (`app/Models/User.php`)**
   - Menambahkan `'telepon'` pada properti `User::$fillable` agar pembuatan akun kurir melalui `CourierManagementService::createCourier()` serta registrasi pelanggan tidak lagi membuang nomor WhatsApp secara diam-diam.

2. **Registrasi & Login Email / Nomor WhatsApp (`app/Http/Controllers/AuthController.php`)**
   - `register()` kini memvalidasi dan menyimpan input `'phone'` (dari UI pendaftaran) maupun `'telepon'` setelah dinormalisasi ke format lokal Indonesia (`08xxxxxxxxxx`), sambil tetap menjaga kompatibilitas payload tanpa nomor telepon untuk skenario API/regresi.
   - `login()` kini mendukung autentikasi menggunakan alamat **email** maupun **nomor WhatsApp** (`08...`, `+62...`, `62...`, `8...`) selaras dengan label antarmuka *"Email atau No. WhatsApp *"*, termasuk normalisasi rate-limiter key dan penegakan validasi kecocokan peran (*Pelanggan*, *Owner*, *Kurir*).

---

## 2. Penguatan RBAC Pembayaran & Keamanan Lookup Pesanan

1. **Isolasi Hak Akses Pembayaran (`app/Http/Controllers/PaymentController.php`)**
   - `status()` (`GET /api/orders/{identifier}/payment-status`) menutup celah privilese dengan menghapus role `'kurir'`, melakukan type casting `(int)` pada perbandingan kepemilikan `user_id`, dan hanya mengizinkan pemilik pesanan atau `'admin'`/`'owner'`.
   - `show()` (`GET /pembayaran-qris/{kode?}`) memperketat validasi otorisasi untuk pengguna terautentikasi agar kurir maupun pelanggan lain menerima respons `403`, serta memvalidasi parameter `order_id` dengan `ctype_digit`.

2. **Pencegahan Type Coercion Lookup ID MySQL (`app/Models/Order.php`, `PaymentController.php`, `OrderController.php`)**
   - Menambahkan scope `Order::scopeByIdentifier()` yang hanya melakukan query kolom integer `id` apabila `$identifier` lulus pemeriksaan `ctype_digit($identifier)`.
   - Mencegah string seperti `12-INVALID` ter-coercion menjadi `id = 12` oleh MySQL pada endpoint detail pesanan, nota digital, status pembayaran, dan simulasi pembayaran.

---

## 3. Integrasi Status Pembayaran, Pemenuhan, Stok & Dashboard Lintas Role

1. **Status Pembayaran Akurat pada Nota Digital & Tracking (`resources/views/Nota Digital Page.blade.php`, `resources/views/Order Tracking & Live Status.blade.php`)**
   - Pesanan berstatus `menunggu_pembayaran` maupun yang dibatalkan/kedaluwarsa (`dibatalkan`, `kedaluwarsa`, `gagal`) tidak lagi menampilkan badge hijau `TERVERIFIKASI` ataupun stempel waktu verifikasi palsu.
   - Halaman pelacakan (`Order Tracking & Live Status`) menampilkan badge status pembayaran sesuai kondisi aktual dan menyediakan tombol *Bayar Sekarang via QRIS* (`route('payment', $order->kode)`) ketika pesanan masih menunggu pembayaran.

2. **Konsistensi Kolom Database & Tampilan Dashboard Owner & Kurir**
   - Menyeragamkan pemanggilan kolom `no_telepon` menjadi `telepon` (dan `d.payment.metode_pembayaran || d.payment.metode` pada JS) pada:
     - `app/Http/Controllers/OwnerDashboardController.php`
     - `app/Http/Controllers/CourierDashboardController.php` (`formatTaskDisplay()` fallback ke `order->user->telepon`)
     - `resources/views/Owner Dashboard & Mini POS Page.blade.php`
     - `resources/views/Dashboard Kurir Tab Tugas Baru Page.blade.php` (termasuk pengaman jika nomor WA kosong)
     - `resources/views/Dashboard Kurir Tab Selesai Page.blade.php`
   - Memperbarui `CourierDashboardController::showProfile()` untuk mengirimkan metrik `$stats` aktual dan mengganti angka statis `142` di `Profil Kurir Page.blade.php` serta `Desktop Profil Kurir Page.blade.php` menjadi `{{ $stats['total_completed'] ?? 0 }}`.

3. **Notifikasi Web Kurir & Info Lapak Dinamis**
   - Menambahkan `'layouts.kurir'` pada view composer notifikasi di `app/Providers/AppServiceProvider.php`.
   - Mengganti alamat lapak dan jam buka statis di `resources/views/Riwayat Pesanan Page.blade.php` dengan variabel dinamis (`$lokasiAktif`, `$isLapakOpen`, `$jamBuka`, `$jamTutup`).

4. **Otomasi Penyelesaian Pickup 24 Jam & Konkurensi `sales_history`**
   - Memperbarui `app/Console/Commands/ExpireOrdersCommand.php` agar penyelesaian otomatis pesanan `ambil_sendiri` yang melewati batas pengambilan 24 jam berjalan atomik melalui `OrderStatusService::transition()`, mencatat `order_status_history`, dan menyinkronkan `sales_history`.
   - Menambahkan `->lockForUpdate()` pada pembaruan agregasi `SalesHistory` di `MidtransService.php`, `OrderStockService.php`, dan `PosService.php`.

---

## 4. Verifikasi Pengujian Otomatis

Seluruh skenario regresi dan pengujian baru PPL 41 pada `tests/Feature/Ppl41AuthOrderIntegrationAndSecurityTest.php` mencakup:
- Registrasi nomor WA ternormalisasi & login menggunakan nomor WhatsApp + validasi role.
- Persistensi kolom `telepon` kurir via `POST /owner/couriers`.
- Penegakan RBAC terhadap `kurir` dan pelanggan lain pada endpoint status & halaman QRIS.
- Proteksi `Order::byIdentifier` terhadap prefix numerik invalid.
- Konsistensi tampilan status `MENUNGGU PEMBAYARAN` dan `KEDALUWARSA` pada Nota & Tracking.
- Sinkronisasi `telepon`, `metode_pembayaran`, statistik profil kurir, dan notifikasi `layouts.kurir`.
- Idempotensi `orders:expire` untuk pesanan *pickup* lewat batas 24 jam serta sinkronisasi `sales_history`.
- RBAC halaman riwayat penjualan serta ekspor Excel (`owner.export.sales`).
- Info lapak & jam operasional dinamis pada halaman Riwayat Pesanan.

**Hasil Verifikasi Suite Lengkap (`php artisan test`):**
- **Total Pengujian:** `306 passed` (`1511 assertions`), 0 failed, 0 skipped.
