# DOKUMENTASI FITUR, AUDIT HAK AKSES, & PANDUAN PERSIAPAN RILIS/DEMO
## Proyek: Sistem Informasi Penjualan Kopi Tiga Titik (Online & POS Kasir Lapak)

---

| Informasi | Detail |
|---|---|
| **Jira ID / Work Package** | `PPL-41` / **WBS 4.3.2 (PPL 37)** |
| **Judul Tugas** | Perbaikan Bug Kelola Produk, Daftar dan Detail Pesanan, Pengelolaan Kurir, Notifikasi Web, Ambil Sendiri, Dokumentasi Fitur, Validasi Hak Akses, Persiapan Rilis & Demo |
| **PIC** | M. Arif (NIM: 240411100084) — Scrum Master, QA Tester & Fullstack Developer |
| **Sprint** | Sprint 4 (21 Nov – 29 Nov 2026) |
| **Tanggal Penyelesaian** | 24 November 2026 |
| **Dependensi** | `PPL 33` (QE Sprint 3) & `PPL 36` (Skenario & Data Uji E2E Regresi) |
| **Kontrak Basis Data** | Tetap **10 tabel domain** (`users`, `products`, `cart_items`, `orders`, `order_items`, `payments`, `order_status_history`, `sales_history`, `settings`, `shipping_locations`) |

---

## 1. Dokumentasi Fitur Utama (Ruang Lingkup PPL 37)

### 1.1 Kelola Produk (`Owner`)
- **Rute Web:** `GET /owner/produk`, `POST /owner/produk`, `PUT /owner/produk/{product}`, `PATCH /owner/produk/{product}/stock`, `PATCH /owner/produk/{product}/toggle-status`, `DELETE /owner/produk/{product}`
- **Rute API:** `POST /api/admin/products`, `PUT /api/admin/products/{product}`, `PATCH /api/admin/products/{product}/status`, `PATCH /api/admin/products/{product}/stock`
- **Aturan Bisnis:**
  - Validasi server-side ketat melalui `StoreProductRequest` & `UpdateProductRequest` (harga integer $\ge 1$, stok integer $\ge 0$, nama unik, foto `jpeg/png/jpg/webp` maks. 2 MB).
  - Penghapusan produk bersifat *soft deactivate* (`aktif = false`) agar histori pesanan lama tetap terjaga.
  - Statistik **Perlu Restok** hanya menghitung produk **aktif** dengan `stok <= batas_stok_minimum` (default 5 botol), konsisten dengan Dashboard Owner & Notifikasi Web.

### 1.2 Daftar & Detail Pesanan (`Owner`)
- **Rute Web & API:** `GET /owner/dashboard`, `GET /owner/orders/{order}/detail`, `GET /api/owner/orders`, `GET /api/owner/orders/{order}`
- **Fungsionalitas:**
  - Menampilkan seluruh pesanan aktif (`menunggu_pembayaran`, `diproses`, `dikirim`) lintas tanggal serta pesanan `selesai` dan `dibatalkan` hari ini.
  - Pencarian berdasarkan kode pesanan, nama penerima, dan nomor telepon; filter status; serta sorting (`terbaru`, `terlama`, `total_tertinggi`, `total_terendah`).
  - Modal detail pesanan menampilkan breakdown item, ongkir, status bayar, riwayat audit status, pembeda label transaksi (`POS Kasir Lapak` vs `Ambil Sendiri` vs `Pesan Antar`), serta aksi langsung **Cetak Nota**, **Unduh PDF**, dan **Unduh PNG**.
  - Omset harian Dashboard Owner dihitung bersih **tanpa ongkir** dan tersinkron otomatis dengan `SalesReportService` (`sales_history`).

### 1.3 Pengelolaan Akun & Tugas Kurir (`Owner` & `Kurir`)
- **Rute Owner:** `GET /owner/couriers`, `POST /owner/couriers`, `DELETE /owner/couriers/{user}`, `POST /owner/orders/{order}/assign-courier`
- **Rute Kurir:** `GET /kurir/tugas-baru`, `GET /kurir/selesai`, `POST /kurir/orders/{order}/complete`, `GET /api/kurir/tasks`, `GET/PUT /kurir/profil`
- **Aturan Bisnis (`US-31` & `US-32`):**
  - Jumlah akun kurir dibatasi maksimal **2 akun aktif** (`CourierManagementService::MAX_ACTIVE_COURIERS = 2`).
  - Kurir yang masih memiliki pesanan berstatus `dikirim` tidak dapat dihapus (tampil modal peringatan standar).
  - Isolasi data ketat: setiap kurir hanya melihat dan dapat menyelesaikan pesanan dengan `courier_id` miliknya sendiri (akses pesanan kurir lain ditolak `403 Forbidden`).

### 1.4 Notifikasi Web Dinamis (`Owner`, `Kurir`, `Pelanggan`)
- **Rute API:** `GET /api/notifications` (terautentikasi; tanpa tabel domain ke-11, dihitung dinamis oleh `WebNotificationService`).
- **Jenis Notifikasi per Peran:**
  - **Owner (`admin`):**
    1. Pesanan Pesan Antar `diproses` yang membutuhkan penugasan kurir (`tipe: kurir`).
    2. Pesanan Ambil Sendiri `diproses` yang siap diracik & diserahkan di lapak (`tipe: pickup`).
    3. Peringatan stok produk aktif menipis `stok <= batas_stok_minimum` (`tipe: stok`).
  - **Kurir (`kurir`):** Daftar tugas pengantaran baru berstatus `dikirim` khusus miliknya (`tipe: tugas_baru`).
  - **Pelanggan (`pelanggan`):** Pembaruan status pesanan miliknya (`diproses` Delivery/Pickup 24 jam, `dikirim`, `selesai`).

### 1.5 Pesanan Ambil Sendiri (*Self-Pickup 24 Jam*)
- **Rute Aksi Owner:** `POST /owner/orders/{order}/complete-pickup`, `POST /api/owner/orders/{order}/complete-pickup`
- **Aturan Bisnis:**
  - Ongkos kirim otomatis `Rp 0`, titik alamat tujuan menggunakan lokasi lapak aktif, batas waktu pengambilan 24 jam.
  - Saat lapak tutup atau di luar jam operasional (`Asia/Jakarta`), checkout Ambil Sendiri ikut diblokir tegas dengan pesan lapak tutup.
  - Penyerahan pesanan oleh Owner hanya diizinkan pada pesanan `metode_pemenuhan = ambil_sendiri` berstatus `diproses` melalui modal konfirmasi standar, lalu otomatis mencatat penjualan ke `sales_history`.

### 1.6 Profil Owner Dinamis
- **Rute:** `GET /owner/profil`, `PUT /owner/profil`
- Menampilkan identitas akun Owner yang sedang login, status operasional lapak *real-time* dari `LapakOperationalService`, daftar lokasi tersimpan dari `shipping_locations`, serta formulir pembaruan nama & kata sandi dengan validasi Laravel.

---

## 2. Matriks Validasi Hak Akses (RBAC 3 Peran)

| Area / Endpoint | Guest (Belum Login) | Pelanggan (`pelanggan`) | Kurir (`kurir`) | Owner (`admin`) |
|---|:---:|:---:|:---:|:---:|
| `/`, `/landing`, `/katalog` | ✅ Boleh | ✅ Boleh | ↪️ Redirect `/kurir/tugas-baru` | ↪️ Redirect `/owner/dashboard` |
| `/cart*`, `/checkout*`, `/riwayat-pesanan*`, `/order-tracking*`, `/pembayaran-qris*`, `/profil-pelanggan*` | ↪️ Redirect `/login` | ✅ Boleh (milik sendiri) | ❌ `403 Forbidden` | ❌ `403 Forbidden` |
| `/nota/{kode}` & `/orders/{kode}/nota` | ↪️ Redirect `/login` | ✅ Hanya milik sendiri (`403` untuk milik orang lain) | ❌ `403 Forbidden` | ✅ Boleh (Layout Owner) |
| `/kurir/tugas-baru`, `/kurir/selesai`, `/kurir/orders/{id}/complete`, `/kurir/profil` | ↪️ Redirect `/login` | ❌ `403 Forbidden` | ✅ Hanya tugas miliknya (`403` untuk kurir lain) | ❌ `403 Forbidden` |
| `/owner/dashboard`, `/owner/produk*`, `/owner/pos*`, `/owner/couriers*`, `/owner/update-lokasi`, `/owner/prediksi`, `/owner/riwayat-penjualan*`, `/owner/profil` | ↪️ Redirect `/login` | ❌ `403 Forbidden` | ❌ `403 Forbidden` | ✅ Boleh |
| `/api/notifications` | ❌ `401` / Redirect | ✅ Notifikasi Pelanggan | ✅ Notifikasi Kurir | ✅ Notifikasi Owner |

---

## 3. Log Perbaikan Bug pada `PPL-41` (`WBS PPL 37`)

| No | Area | Temuan Audit | Perbaikan Diterapkan | Status |
|---|---|---|---|---|
| 1 | Profil Owner | `PUT /owner/profil` belum terdaftar di `routes/web.php` (405) dan data profil/lokasi di view masih hardcoded | Menambahkan `OwnerDashboardController::showProfile` & `updateProfile`, mendaftarkan route `PUT /owner/profil`, dan mendinamiskan seluruh view dari database & `LapakOperationalService` | **FIXED ✅** |
| 2 | Kelola Produk | Kartu `Perlu Restok` ikut menghitung produk nonaktif (`aktif = false`), dan API `aktif: false` terbaca `true` karena `$request->has('aktif')` | Memfilter `aktif = true` pada metrik `perluRestok` dan menggunakan `$request->boolean('aktif')` saat field dikirim | **FIXED ✅** |
| 3 | Notifikasi Web & Ambil Sendiri | Owner belum mendapat notifikasi web saat ada pesanan `ambil_sendiri` lunas (`diproses`), dan item dropdown belum bisa diklik | Menambahkan notifikasi `owner_pickup_ready` (`tipe: pickup`), memperjelas pesan pickup pelanggan, serta menjadikan item dropdown notifikasi Owner clickable dengan ikon khusus pickup | **FIXED ✅** |
| 4 | Ambil Sendiri | Endpoint `completePickup` belum memberi pesan validasi khusus jika status bukan `diproses` | Menambahkan guard eksplisit `status !== 'diproses'` dengan respon error `422` berbahasa Indonesia | **FIXED ✅** |
| 5 | Validasi Hak Akses & Routing | Duplikasi definisi rute `/login` & `/logout` di `routes/web.php`; perbandingan `user_id !==` di `OrderController::show/nota` tanpa cast `(int)` | Menghapus rute duplikat dan menyeragamkan cast `(int)` pada pengecekan kepemilikan pesanan & nota | **FIXED ✅** |

---

## 4. Checklist Persiapan Rilis & Demo Sistem

### 4.1 Checklist Pra-Demo (Jalankan 15 Menit Sebelum Demo)
1. **Verifikasi Environment (`.env`):**
   - `APP_TIMEZONE=Asia/Jakarta`
   - Koneksi MySQL aktif & nama database sesuai.
   - `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, `MIDTRANS_IS_PRODUCTION=false` terisi (mode Sandbox).
   - `ORS_API_KEY` terisi (jika internet aktif; fallback routing siap bekerja).
2. **Reset & Seed Data Demo Bersih:**
   ```powershell
   php artisan migrate:fresh --seed
   php artisan storage:link
   php artisan optimize:clear
   ```
3. **Verifikasi 10 Tabel Domain:** Pastikan hanya terdapat 10 tabel domain resmi (`users`, `products`, `cart_items`, `orders`, `order_items`, `payments`, `order_status_history`, `sales_history`, `settings`, `shipping_locations`) plus tabel standar bawaan Laravel (`cache`, `jobs`, `migrations`, `sessions`).
4. **Verifikasi Jam Operasional Demo:** Login sebagai Owner → buka **Pengaturan Lapak** → pastikan status **Buka** dan rentang jam mencakup jam pelaksanaan demo (misal `00:00` – `23:59` saat sesi demo agar transaksi online lancar, lalu uji tutup secara terpisah).
5. **Verifikasi Test Otomatis:** Jalankan `php artisan test` dan pastikan **100% PASSED (0 Failed)**.

### 4.2 Akun Demo Resmi (`UserSeeder`)
| Peran | Nama | Email | Password |
|---|---|---|---|
| **Owner (`admin`)** | Farrel | `owner1@gmail.com` | `password123` |
| **Kurir #1 (`kurir`)** | Ilham Ramadhan | `kurir1ilham@gmail.com` | `password123` |
| **Kurir #2 (`kurir`)** | awaludin | `kurirawal@gmail.com` | `password123` |
| **Pelanggan #1 (`pelanggan`)** | Nabil Pratama | `nabil@gmail.com` | `password123` |
| **Pelanggan #2 (`pelanggan`)** | Budi Santoso | `budi@gmail.com` | `password123` |

### 4.3 Urutan Skenario Demo End-to-End (±10 Menit)
1. **Pelanggan (Katalog → Keranjang → Checkout → QRIS → Nota):**
   - Login `nabil@gmail.com` → cek stok real-time di Katalog → tambah produk ke Keranjang → buka Profil untuk cek/ubah alamat Mojokerto → lanjut Checkout (tunjukkan ongkir OSM gratis $\le 5{,}5$ km vs bertingkat $> 5{,}5$ km & penolakan luar Mojokerto) → bayar QRIS Sandbox → lihat Pelacakan Status & Nota Digital.
2. **Owner (Dashboard → Notifikasi → Kurir → Ambil Sendiri → POS → Laporan & Prediksi):**
   - Login `owner1@gmail.com` → tunjukkan ringkasan omset tanpa ongkir, lonceng Notifikasi Web, & Kelola Kurir (kuota 2/2) → tugaskan kurir ke pesanan Delivery → serahkan pesanan Ambil Sendiri dengan modal konfirmasi → lakukan 1 transaksi POS Kasir Tunai sampai unduh nota PDF/PNG → buka detail pesanan di Dashboard Owner & tunjukkan tombol **Cetak Nota** → buka **Riwayat Penjualan** (ekspor Excel `.xlsx`) & **Prediksi Stok Moving Average**.
3. **Kurir (Tugas Baru → Rute Maps → Selesai Antar):**
   - Login `kurir1ilham@gmail.com` → tunjukkan tugas yang baru ditugaskan Owner → klik tombol Selesai Antar → pastikan pesanan berpindah ke tab Selesai & omset Owner langsung ter-update.
4. **Pembuktian Keamanan RBAC:**
   - Coba akses `/owner/dashboard` dari akun Pelanggan (`403 Forbidden`) dan akses `/cart` dari akun Owner (`403 Forbidden`).

---

## 5. Bukti Pengujian Otomatis `PPL 37`
Seluruh perbaikan bug dan audit hak akses pada dokumen ini diverifikasi secara otomatis melalui `tests/Feature/Ppl37BugFixAndRbacAuditTest.php` serta full regression suite `php artisan test` (0 failures) dan siap dilanjutkan ke **QE Testing Akhir (`PPL-46` / WBS PPL 42)**.
