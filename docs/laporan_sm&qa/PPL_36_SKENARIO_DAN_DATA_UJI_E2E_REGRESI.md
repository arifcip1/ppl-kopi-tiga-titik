# DOKUMEN SKENARIO & DATA UJI END-TO-END SERTA REGRESI SPRINT 1–3
## Proyek: Sistem Informasi Penjualan Kopi Tiga Titik (Online & POS Offline)

---

| Informasi | Detail |
|-----------|--------|
| **Jira ID / Work Package** | `PPL-40` / **WBS 4.3.1 (PPL 36)** |
| **Judul Tugas** | Penyusunan Skenario dan Data Uji End-to-End serta Regresi Sprint 1–3 |
| **PIC / Penyusun** | M. Arif (NIM: 240411100084) — QA Tester & Scrum Master |
| **Sprint** | Sprint 4 (21 Nov – 29 Nov 2026) |
| **Dependensi** | PPL 33 (QE Testing Sprint 3 — Selesai) |
| **Dokumen Acuan** | PRD Konsolidasi v2.4 (Bab 5 & Bab 11), PPL 01 Konsolidasi, PPL 24, Kontrak Frontend–Backend (Bagian 20.8) |
| **Repository & Branch** | `ppl-kopi-tiga-titik` / `feature/PPL-40-skenario-data-uji-e2e-regresi` |
| **Penggunaan Lanjutan** | Dasar perbaikan bug (`PPL-41`/WBS PPL 37) & lembar eksekusi QE Akhir (`PPL-46`/WBS PPL 42, 28–29 Nov 2026) |

---

## 1. Tujuan & Ruang Lingkup Pengujian

Dokumen ini menyusun **skenario uji End-to-End (E2E)**, **matriks regresi Sprint 1–3**, dan **katalog data uji terstandar** untuk memastikan seluruh fitur MVP Kopi Tiga Titik terintegrasi dengan baik, mematuhi aturan bisnis global (`BR-1` s.d. `BR-23`), serta menjaga kontrak database **tetap 10 tabel domain** (`users`, `products`, `cart_items`, `orders`, `order_items`, `payments`, `order_status_history`, `sales_history`, `settings`, `shipping_locations`).

### 19 Kelompok Skenario Wajib (Sesuai PRD Bab 11 & Kontrak 20.8)
1. Autentikasi & Hak Akses 3 Role (`admin`/Owner, `kurir`, `pelanggan`)
2. Kelola Produk (CRUD, validasi, nonaktif `aktif=false`)
3. Katalog Produk & Status Stok Real-time
4. Keranjang Belanja & Persistensi
5. Checkout Pengiriman (Delivery) & Penahanan Stok Atomik
6. Checkout Ambil Sendiri (Pickup 24 Jam, Ongkir Rp0)
7. Perhitungan Ongkir Rute OSM & Validasi Geofence Kota Mojokerto
8. Pembayaran Midtrans QRIS Sandbox (Pending, Settlement, Gagal, Kedaluwarsa 10 Menit, Terlambat, Webhook Duplikat)
9. POS Kasir Offline (Tunai & QRIS Langsung, Konfirmasi Owner, Unduh/Cetak Nota)
10. Daftar, Detail, Filter, & Sorting Pesanan Hari Ini
11. Pengelolaan Akun Kurir (Maks. 2 Aktif), Penugasan, & Pembaruan Status
12. Notifikasi Web Real-time per Role (Tanpa Tabel Domain ke-11)
13. Nota Digital (Online & POS) & Tombol Hubungi Penjual (WhatsApp)
14. Dashboard Histori Penjualan (Gabungan Online + POS, Omzet Tanpa Ongkir, Anti-Duplikasi)
15. Ekspor Laporan Penjualan ke Excel (`.xlsx`)
16. Prediksi Kebutuhan Stok Moving Average 3 Minggu (21 Hari Kalender)
17. Rule-Based Peringatan Restok (`stok <= 5`, saran `max(0, 10 - stok)`)
18. Pengelolaan Lokasi Lapak Aktif, Snapshot Lokasi, & Sinkronisasi Jam Operasional (`Asia/Jakarta`)
19. Integrasi End-to-End, Error Layanan Eksternal (ORS/Midtrans), & Halaman Error Kustom (`403/404/419/500`)

---

## 2. Katalog Data Uji Resmi (Test Data Catalog)

Seluruh pengujian manual menggunakan baseline seeder resmi yang sudah tersedia di repositori (`php artisan migrate:fresh --seed`) tanpa perlu menambah tabel atau seeder baru.

### 2.1 Data Akun Uji (3 Role — Sumber: `UserSeeder.php`)

| Kode Data | Role | Nama | Email | Password | Keterangan / No. WA |
|-----------|------|------|-------|----------|---------------------|
| `TD-USR-01` | `admin` (Owner) | Farrel | `owner1@gmail.com` | `password123` *(atau `ADMIN_PASSWORD` di `.env`)* | Akses penuh dashboard, produk, POS, laporan, lokasi, kurir |
| `TD-USR-02` | `kurir` | Ilham Ramadhan | `kurir1ilham@gmail.com` | `password123` | Kurir aktif #1 — WA: `081234567890` |
| `TD-USR-03` | `kurir` | awaludin | `kurirawal@gmail.com` | `password123` | Kurir aktif #2 — WA: `085712345678` (kuota 2/2 penuh) |
| `TD-USR-04` | `pelanggan` | Nabil Pratama | `nabil@gmail.com` | `password123` | Akun pelanggan utama untuk alur E2E |
| `TD-USR-05` | `pelanggan` | Budi Santoso | `budi@gmail.com` | `password123` | Akun pelanggan pembanding untuk uji isolasi data antar pelanggan |
| `TD-USR-06` | `pelanggan` *(baru)* | Uji Registrasi | `uji.baru@kopitigatitik.test` | `Password123!` | Data input uji registrasi akun baru |

### 2.2 Data Produk & Variasi Stok Uji (Sumber: `ProductSeeder.php` & BR-1)

| Kode Data | Varian Produk | Rasa | Harga (Rp) | Stok Awal Seeder | Kondisi Uji yang Dicakup |
|-----------|---------------|------|-----------:|-----------------:|--------------------------|
| `TD-PRD-01` | **Titik Hitam** | Americano | 8.000 | 10 | Stok normal — uji checkout online & POS |
| `TD-PRD-02` | **Titik Manja** | Kopi Susu Caramel | 10.000 | 3 | Stok menipis (`stok <= 5`) — memicu Rule-Based peringatan restok (saran produksi `+7`) |
| `TD-PRD-03` | **Titik Temu** | Kopi Susu Gula Aren | 10.000 | 0 | Stok habis (`0`) — tombol katalog nonaktif & ditolak di keranjang/POS |
| `TD-PRD-04` | **Titik Jenuh** | Coklat | 12.000 | 0 | Stok habis — uji restok manual oleh Owner sebelum transaksi |
| `TD-PRD-05` | *(Uji Nonaktif)* | Varian Nonaktif | 10.000 | 5 (`aktif=false`) | Uji BR-23: disembunyikan dari katalog & ditolak saat checkout/API |

### 2.3 Data Lokasi Lapak, Koordinat Uji Geofence & Ongkir (Sumber: `SettingSeeder.php` & BR-8)

| Kode Data | Kategori | Nama / Alamat | Latitude | Longitude | Ekspektasi Sistem |
|-----------|----------|---------------|---------:|----------:|-------------------|
| `TD-LOC-01` | Lapak Aktif | Lapak Jl. Kuwung, Mergelo, Magersari, Kota Mojokerto | `-7.472600` | `112.438500` | Lokasi asal default (`lokasi_pengiriman_aktif_id`) |
| `TD-LOC-02` | Lapak Cadangan | Rumah Produksi Magersari, Jl. Benteng Pancasila No. 12 | `-7.468800` | `112.441200` | Uji ganti lokasi aktif & verifikasi snapshot pesanan lama tetap utuh |
| `TD-LOC-03` | Tujuan Dekat (`<= 5,5 km`) | Jl. Hayam Wuruk No. 42, Magersari, Kota Mojokerto | `-7.472100` | `112.434000` | Valid (Mojokerto) — **Gratis Ongkir (Rp0 Promo)** |
| `TD-LOC-04` | Tujuan Jauh (`> 5,5 km`) | Area Utara Kota Mojokerto (batas kota) | `-7.430000` | `112.438500` | Valid (Mojokerto) — **Ongkir bertingkat** `2000 * (floor(d - 5.5) + 1)` |
| `TD-LOC-05` | Luar Wilayah (Negatif) | Jl. Kenjeran, Tambakrejo, Kec. Simokerto, Surabaya | `-7.241916` | `112.758636` | **DITOLAK (422)** di checkout, profil pelanggan, & update lokasi lapak |

### 2.4 Data Jam Operasional & Pengaturan Sistem (`settings`)

| Kode Data | Key Settings | Nilai Uji | Kondisi / Ekspektasi |
|-----------|--------------|-----------|----------------------|
| `TD-SET-01` | `jam_buka` / `jam_tutup` / `status_lapak` | `00:00` / `23:59` / `buka` | Lapak **BUKA** untuk pengujian E2E kapan saja |
| `TD-SET-02` | `jam_buka` / `jam_tutup` / `status_lapak` | `00:10` / `01:00` / `buka` *(diuji di luar rentang, mis. 01:06 / 19:00 WIB)* | Status efektif otomatis **TUTUP (Di Luar Jam)** — blokir delivery & ambil sendiri |
| `TD-SET-03` | `status_lapak` | `tutup` *(meski jam aktif)* | Status **TUTUP MANUAL** — blokir seluruh checkout online baru |
| `TD-SET-04` | `batas_stok_minimum` / `stok_harian_standar` | `5` / `10` | Acuan Rule-Based restok (`stok <= 5`, saran `max(0, 10 - stok)`) |

### 2.5 Data Uji Pembayaran Midtrans, POS, & Histori Penjualan

| Kode Data | Skenario | Payload / Nilai Uji | Ekspektasi Sistem |
|-----------|----------|---------------------|-------------------|
| `TD-MID-01` | Webhook Settlement Valid | `transaction_status: settlement`, `fraud_status: accept`, `signature_key = SHA512(order_id + status_code + gross_amount + ServerKey)` | `payments.status = berhasil`, `orders.status = diproses`, tercatat 1x di `sales_history` (`sumber='transaksi'`) |
| `TD-MID-02` | Webhook Duplikat | Kirim payload `TD-MID-01` dua kali berturut-turut | HTTP 200 idempoten — stok & `sales_history` tidak tergandakan |
| `TD-MID-03` | Signature Tidak Valid | `signature_key: invalid-hash` | HTTP 401 ditolak — status order & payment tidak berubah |
| `TD-MID-04` | Nominal Tidak Cocok | `gross_amount` berbeda dari `orders.total` | HTTP 400 ditolak — status tidak berubah |
| `TD-MID-05` | Expire / Deny / Cancel | `transaction_status: expire` atau `deny` | `orders.status = dibatalkan`, stok kembali tepat 1x (`stok_dikembalikan = true`) |
| `TD-POS-01` | POS Tunai | 2x Titik Hitam (`Rp16.000`), `metode_pembayaran: tunai`, `uang_diterima: 20000` | Kembalian `Rp4.000`, `sumber_transaksi = pos`, `metode_pemenuhan = NULL`, stok -2, masuk `sales_history` |
| `TD-POS-02` | POS QRIS Langsung | 1x Titik Manja (`Rp10.000`), `metode_pembayaran: qris` | Tanpa panggilan Midtrans, status `selesai`, stok -1, nota bisa diunduh PNG/PDF |
| `TD-MA-01` | Data Simulasi vs Nyata | 21 hari `sales_history` dengan `sumber = 'simulasi'` vs `sumber = 'transaksi'` | Data `simulasi` **dikecualikan** dari Moving Average produksi & laporan transaksi nyata (BR-15, BR-21) |

---

## 3. Skenario Pengujian End-to-End (E2E) Lintas Role

Skenario E2E menguji keterhubungan antarmodul secara utuh dari hulu ke hilir.

| ID E2E | Nama Alur End-to-End | Role Terlibat | Alur Lengkap yang Diuji | Hasil yang Diharapkan | Status Automated Test |
|--------|----------------------|---------------|-------------------------|-----------------------|:---------------------:|
| **E2E-01** | Pembelian Online Delivery Lengkap (Katalog → Kurir → Laporan) | Pelanggan → Midtrans → Owner → Kurir | 1. Pelanggan ubah alamat utama di Profil (`TD-LOC-03`).<br>2. Tambah 2x Titik Hitam ke keranjang → Checkout Delivery.<br>3. Verifikasi stok berkurang 2 & webhook Midtrans `settlement` (+ uji duplikat webhook).<br>4. Owner lihat antrean hari ini & tugaskan Kurir 1 (`dikirim`).<br>5. Kurir 1 lihat tugas (terisolasi dari Kurir 2) & klik Selesaikan Antaran (`selesai`).<br>6. Pelanggan buka Nota Digital & Owner cek Riwayat Penjualan. | Stok berkurang tepat 1x; status berjalan `menunggu_pembayaran` → `diproses` → `dikirim` → `selesai`; `sales_history` mencatat `2` botol (tidak double saat kurir selesai); omzet bersih = subtotal tanpa ongkir; nota digital valid. | ✅ Teruji Otomatis |
| **E2E-02** | Pembelian Online Ambil Sendiri 24 Jam & Integritas Snapshot Lokasi | Pelanggan → Midtrans → Owner | 1. Pelanggan checkout `ambil_sendiri` saat lokasi aktif `TD-LOC-01` (ongkir Rp0, `batas_pengambilan` +24 jam).<br>2. Pembayaran Midtrans `settlement`.<br>3. Owner mengubah lokasi lapak aktif ke `TD-LOC-02`.<br>4. Owner menyerahkan pesanan via modal konfirmasi template (`complete-pickup`). | Ongkir Rp0; snapshot alamat pesanan tetap menunjuk `TD-LOC-01` (tidak ikut berpindah); status menjadi `selesai`; `sales_history` tidak terduplikasi. | ✅ Teruji Otomatis |
| **E2E-03** | POS Kasir Offline (Tunai & QRIS), Peringatan Restok, & Ekspor Excel | Owner | 1. Owner proses POS Tunai (`TD-POS-01`) dan POS QRIS (`TD-POS-02`) hingga stok produk `<= 5`.<br>2. Cek notifikasi web Owner & saran produksi `max(0, 10 - stok)`.<br>3. Buka Nota POS (`/nota/{kode}`) & unduh PNG/PDF.<br>4. Buka Riwayat Penjualan & unduh Excel (`/owner/riwayat-penjualan/export`). | Stok berkurang atomik; transaksi POS tidak masuk penugasan kurir; peringatan restok & notifikasi muncul; omzet masuk `sales_history` (`sumber='transaksi'`); file `.xlsx` berhasil diunduh. | ✅ Teruji Otomatis |
| **E2E-04** | Pembatalan Pelanggan & Kedaluwarsa 10 Menit + Pengembalian Stok 1x | Pelanggan / Scheduler | 1. Pelanggan checkout 3x Titik Hitam (stok 10 → 7) tanpa membayar.<br>2. Uji pembatalan pelanggan dalam `< 10` menit **atau** jalankan `orders:expire` setelah `> 10` menit.<br>3. Panggil ulang proses batal/expire untuk pesanan yang sama. | Status `dibatalkan`, `stok_dikembalikan = true`, stok kembali tepat 1x (7 → 10), pemanggilan ulang tidak menambah stok lagi, dan tidak masuk `sales_history`. | ✅ Teruji Otomatis |
| **E2E-05** | Jam Operasional Otomatis & Geofence Wilayah Kota Mojokerto | Owner → Pelanggan | 1. Owner atur `jam_buka = 15:00`, `jam_tutup = 18:00`, `status_lapak = buka`.<br>2. Uji pada pukul `19:00 WIB`: cek Dashboard Owner & coba quote/checkout (delivery & pickup).<br>3. Uji pada pukul `16:00 WIB` dengan titik Surabaya (`TD-LOC-05`) vs Mojokerto (`TD-LOC-03`). | Pukul `19:00` status otomatis `Tutup (Di Luar Jam)` dan memblokir delivery + pickup (`error_type = lapak_closed`); pukul `16:00` titik Surabaya ditolak `422` (`geofence`), titik Mojokerto lolos. | ✅ Teruji Otomatis |
| **E2E-06** | Audit Hak Akses 3 Role & Isolasi Data Lintas Pengguna | Guest, Pelanggan, Kurir, Owner | 1. Guest akses `/cart`, `/checkout`, `/owner/dashboard`, `/kurir/tugas-baru`.<br>2. Pelanggan akses endpoint Owner, Kurir, POS, Ekspor, serta order/nota milik Pelanggan lain.<br>3. Kurir 2 akses Dashboard Owner & mencoba menyelesaikan antaran milik Kurir 1. | Guest diarahkan ke `/login`; seluruh pelanggaran role dan akses lintas pengguna ditolak keras oleh backend dengan HTTP `403 Forbidden`. | ✅ Teruji Otomatis |
| **E2E-07** | Isolasi Data Simulasi pada Prediksi Moving Average 21 Hari | Owner | 1. Database berisi 21 hari data `sales_history` dengan `sumber = 'simulasi'` tanpa data `sumber = 'transaksi'`.<br>2. Owner akses `/owner/prediksi`.<br>3. Tambahkan data transaksi nyata `>= 21` hari (termasuk hari tanpa penjualan = 0). | Saat hanya ada `simulasi`, sistem menampilkan status **data belum cukup**; setelah ada `transaksi` 21 hari, Moving Average menghitung rata-rata 3 periode x 7 hari dengan hari kosong = 0. | ✅ Teruji Otomatis |

---

## 4. Matriks Kasus Uji Regresi Sprint 1–3 (Lembar Kerja QE Akhir PPL 42)

Kolom `Hasil Aktual`, `Status`, dan `Severity` disiapkan mengikuti format resmi **PRD Bab 11** untuk diisi saat eksekusi QE Akhir (`PPL-46` / WBS PPL 42, 28–29 Nov 2026).  
*Tingkat Keparahan: **Kritis** (alur utama/data rusak), **Mayor** (fitur sebagian gagal), **Minor** (UI/teks).*

| ID Kasus | Kelompok Uji (WP) | Role | Data Uji | Langkah Pengujian Singkat | Hasil yang Diharapkan | Hasil Aktual | Status | Severity |
|----------|-------------------|------|----------|---------------------------|-----------------------|--------------|:------:|:--------:|
| **REG-01** | Auth & Role (PPL 07) | Guest / Pelanggan | `TD-USR-06`, `TD-USR-04` | Register akun baru → login salah password → login benar → logout. | Register otomatis role `pelanggan`; password salah ditolak; login benar arahkan sesuai role. | *(Siap QE PPL 42)* | `[ ]` | - |
| **REG-02** | Hak Akses 3 Role (PPL 07, 41) | Semua Role | `TD-USR-01..05` | Coba akses URL lintas role (`/owner/*`, `/kurir/*`, `/owner/pos`, `/orders/{lain}`). | Backend mengembalikan `403 Forbidden` & halaman error kustom 403. | *(Siap QE PPL 42)* | `[ ]` | - |
| **REG-03** | Kelola Produk CRUD (PPL 08–09) | Owner | `TD-PRD-01..05` | Tambah produk valid, uji harga `<= 0` / stok negatif / foto `> 2MB`, ubah stok, nonaktifkan produk (`aktif=false`). | Input invalid ditolak validasi per-field (`<x-input-error />`); nonaktif tanpa hard delete. | *(Siap QE PPL 42)* | `[ ]` | - |
| **REG-04** | Katalog & Stok (PPL 10–11) | Guest / Pelanggan | `TD-PRD-01..05` | Buka `/katalog` tanpa login; cek produk stok `10`, stok `0`, dan produk nonaktif. | Produk nonaktif tidak tampil; stok `0` berlabel "Stok Habis" & tombol nonaktif; perubahan stok langsung terlihat. | *(Siap QE PPL 42)* | `[ ]` | - |
| **REG-05** | Keranjang Belanja (PPL 13–14) | Pelanggan | `TD-USR-04`, `TD-PRD-01` | Tambah produk, tambah melebihi stok, update qty `< 1`, hapus item, logout lalu login ulang. | Melebihi stok/qty `< 1` ditolak; keranjang tidak menahan stok; isi keranjang persisten di DB. | *(Siap QE PPL 42)* | `[ ]` | - |
| **REG-06** | Profil & Alamat Utama (TC-CHK-01) | Pelanggan | `TD-LOC-03`, `TD-LOC-05` | Buka `/profil-pelanggan` → klik "Ubah Alamat Pengiriman" → uji Surabaya (tolak) → simpan alamat Mojokerto → buka `/checkout`. | Alamat Surabaya ditolak geofence; alamat Mojokerto tersimpan di `settings` & otomatis jadi pin default checkout. | *(Siap QE PPL 42)* | `[ ]` | - |
| **REG-07** | Checkout Pengiriman & Ongkir (PPL 15–18, 25–26) | Pelanggan | `TD-LOC-03`, `TD-LOC-04`, `TD-LOC-05` | Pilih `pengiriman`, geser pin `<= 5,5 km`, geser pin `> 5,5 km`, geser ke Surabaya. | `<= 5,5 km` Rp0 Promo; `> 5,5 km` tarif bertingkat ORS; Surabaya ditolak 422; submit menahan stok atomik. | *(Siap QE PPL 42)* | `[ ]` | - |
| **REG-08** | Checkout Ambil Sendiri (PPL 23–24) | Pelanggan | `TD-LOC-01` | Pilih `ambil_sendiri` → cek ongkir, info lapak, dan submit checkout. | Ongkir Rp0, koordinat tujuan tidak wajib, snapshot lokasi lapak tersimpan, batas ambil 24 jam tercatat. | *(Siap QE PPL 42)* | `[ ]` | - |
| **REG-09** | Midtrans QRIS Sukses & Idempoten (PPL 23–24, 40) | Pelanggan / Midtrans | `TD-MID-01`, `TD-MID-02` | Buka `/pembayaran-qris/{kode}` → simulasikan/kirim webhook `settlement` → kirim webhook sama ke-2 kalinya → klik Back browser. | Status jadi `diproses`, `payments.status = berhasil`, `sales_history` +qty tepat 1x, back browser ditolak/dialihkan ke tracking. | *(Siap QE PPL 42)* | `[ ]` | - |
| **REG-10** | Midtrans Gagal, Invalid & Expire 10 Menit (PPL 18, 23, 40) | Pelanggan / Scheduler | `TD-MID-03..05` | Kirim signature salah (401), nominal beda (400), status `deny`/`expire`, dan jalankan `php artisan orders:expire`. | Payload palsu ditolak; `deny`/`expire`/lewat 10 menit membatalkan order & mengembalikan stok tepat 1x. | *(Siap QE PPL 42)* | `[ ]` | - |
| **REG-11** | Pembatalan Pelanggan (PPL 18) | Pelanggan | `TD-USR-04` | Batalkan pesanan `menunggu_pembayaran` dalam `< 10` menit; coba batalkan pesanan yang sudah `diproses`. | Pesanan belum bayar berhasil batal & stok kembali 1x; pesanan sudah bayar ditolak dibatalkan (BR-6). | *(Siap QE PPL 42)* | `[ ]` | - |
| **REG-12** | Lokasi Lapak & Jam Operasional (PPL 25–26) | Owner & Pelanggan | `TD-LOC-01`, `TD-LOC-02`, `TD-LOC-05`, `TD-SET-02` | Owner coba simpan lokasi Surabaya (modal template, bukan `alert()`); ganti lokasi aktif; set jam lewat (`00:10-01:00`). | Modal template menolak Surabaya; status otomatis `Tutup (Di Luar Jam)`; checkout delivery & pickup terblokir dengan judul "Lapak Sedang Tutup". | *(Siap QE PPL 42)* | `[ ]` | - |
| **REG-13** | Dashboard Owner: Antrean Hari Ini, Sort, Peta & Pickup (PPL 27–28) | Owner | `TD-USR-01` | Buka `/owner/dashboard`; uji filter status, sort (Terbaru/Terlama/Tertinggi/Terendah), tombol Peta, & tombol "Serahkan (Selesai)". | Hanya transaksi hari ini yang tampil; sort akurat; modal konfirmasi template muncul pada "Serahkan (Selesai)". | *(Siap QE PPL 42)* | `[ ]` | - |
| **REG-14** | Kelola Kurir, Penugasan & Antaran (PPL 27–28) | Owner & Kurir | `TD-USR-01..03` | Cek daftar kurir (tampil no. WA), coba tambah kurir ke-3 (ditolak kuota 2/2), tugaskan Kurir 1, Kurir 1 selesaikan antaran. | Kuota maks 2 aktif terjaga; status pembeli update live ke "Diantar"; Kurir 2 tidak melihat tugas Kurir 1; selesai tercatat di `order_status_history`. | *(Siap QE PPL 42)* | `[ ]` | - |
| **REG-15** | Notifikasi Web 3 Role (PPL 27–28, 32) | Owner, Kurir, Pelanggan | `TD-USR-01..04` | Periksa lonceng notifikasi & `GET /api/notifications` saat ada order baru, stok `<= 5`, tugas kurir baru, dan perubahan status. | Setiap role hanya menerima notifikasi yang relevan dengan hak aksesnya tanpa tabel DB baru. | *(Siap QE PPL 42)* | `[ ]` | - |
| **REG-16** | POS Kasir Offline & Unduh Nota (PPL 31–32) | Owner | `TD-POS-01`, `TD-POS-02` | Transaksi POS Tunai & QRIS di `/owner/pos`; uji qty melebihi stok; klik "Unduh Nota" (PNG) & "Cetak / PDF". | Stok terpotong atomik, status langsung `selesai`, tanpa Midtrans, PNG terunduh & `/nota/{kode}` menampilkan nota resmi POS. | *(Siap QE PPL 42)* | `[ ]` | - |
| **REG-17** | Rule-Based Peringatan Restok (PPL 31–32) | Owner | `TD-PRD-02` | Cek produk dengan `stok <= 5` di Dashboard & POS. | Muncul peringatan restok & saran produksi `max(0, 10 - stok)`; stok tidak berubah otomatis (BR-18). | *(Siap QE PPL 42)* | `[ ]` | - |
| **REG-18** | Histori Penjualan, Ekspor Excel & Moving Average (PPL 29–30) | Owner | `TD-MA-01` | Buka `/owner/riwayat-penjualan` (harian/mingguan/bulanan), klik Ekspor Excel, lalu buka `/owner/prediksi`. | Online + POS tergabung tanpa ongkir; `.xlsx` terunduh valid; MA 21 hari menghitung hari kosong = 0 & mengecualikan `simulasi`. | *(Siap QE PPL 42)* | `[ ]` | - |
| **REG-19** | Nota Digital, Hubungi Penjual & Error Handling (PPL 24, 34) | Pelanggan / Semua | `/dev/error/{403,404,419,500}` | Buka `/nota/{kode}`, klik Hubungi Penjual (WA), uji halaman error kustom & simulasi kegagalan ORS. | Nota menampilkan rincian valid + PDF/PNG/Print; WA terbuka dengan nomor terkonfigurasi; tidak ada error 500 mentah tanpa penanganan. | *(Siap QE PPL 42)* | `[ ]` | - |

---

## 5. Verifikasi Otomasi Regresi & Checklist DoD (`PPL-40` / WBS PPL 36)

### 5.1 Suite Pengujian Otomatis (`php artisan test`)
Skenario E2E di atas telah dikodekan dalam suite pengujian otomatis baru:
* **[tests/Feature/EndToEndRegressionSprint1To3Test.php](../../tests/Feature/EndToEndRegressionSprint1To3Test.php)** — menjalankan 7 skenario E2E lintas modul Sprint 1–3 (`E2E-01` s.d. `E2E-07`).

Perintah verifikasi:
```powershell
# Menjalankan khusus E2E & Regresi Sprint 1-3
php artisan test tests/Feature/EndToEndRegressionSprint1To3Test.php

# Menjalankan seluruh regression suite proyek
php artisan test
```

### 5.2 Checklist Definition of Done (DoD Jira `PPL-40`)
- [x] UI & backend terintegrasi (terverifikasi lintas 19 kelompok modul Sprint 1–3)
- [x] Fungsi utama berjalan sesuai PRD v2.4, PPL 01, dan Kontrak 10 tabel
- [x] Skenario uji E2E, regresi, dan katalog data uji terdokumentasi lengkap
- [x] Tidak ada bug kritis yang tersisa pada suite regresi otomatis (`php artisan test` 100% PASS)
- [x] Integrasi lintas fitur diperiksa melalui `EndToEndRegressionSprint1To3Test`
- [x] Siap digunakan sebagai acuan perbaikan bug (`PPL-41`/WBS PPL 37) dan QE Akhir (`PPL-46`/WBS PPL 42)
