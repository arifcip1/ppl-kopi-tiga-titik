# Kopi Tiga Titik — Sistem Informasi Penjualan Kopi Botol (Online & POS Kasir Lapak)

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Midtrans](https://img.shields.io/badge/Payment-Midtrans_QRIS_Sandbox-002B49?style=for-the-badge)](https://midtrans.com)
[![Tests](https://img.shields.io/badge/Tests-306%20Passed-success?style=for-the-badge)](tests)

Sistem Informasi Penjualan Kopi Tiga Titik adalah aplikasi terintegrasi berbasis web (*mobile-first* & desktop responsif) untuk UMKM **Kopi Tiga Titik** di Kota Mojokerto (Mata Kuliah Proyek Perangkat Lunak). Aplikasi ini mengintegrasikan pemesanan daring pelanggan (*online delivery* maupun *ambil sendiri di lapak*), pembayaran digital **QRIS Sandbox (Midtrans)**, perhitungan ongkos kirim rute jalan **OpenStreetMap / OpenRouteService (ORS)**, manajemen tugas **Kurir**, **POS Kasir Offline** di lapak fisik (Jl. Kuwung, Magersari, Kota Mojokerto), laporan & ekspor **Excel**, serta **Prediksi Kebutuhan Stok Moving Average**.

---

## Anggota Tim

| Nama | NIM | Peran |
|---|---|---|
| Muhammad Farrel Fastha | 240411100114 | Product Owner / System Analyst / Frontend & Backend / Dokumentasi & Branding |
| M. ARIF | 240411100084 | Scrum Master / QA Tester / Frontend & Backend / Dokumentasi & Branding |
| Badruz Zaman Ash Sholih | 240411100140 | UI/UX Designer / Frontend & Backend / Dokumentasi & Branding |
| Ahmad Dafi Zidni Alfarisi | 240411100027 | UI/UX Designer / Frontend & Backend / Dokumentasi & Branding |

---

## 1. Gambaran Umum & Fitur Utama

Sistem ini dibangun dengan arsitektur **Basis Data Terpadu (Single Source of Truth — 10 Tabel Domain)** untuk mencegah *race condition*, *double-counting*, atau selisih stok antara transaksi daring dan luring.

### 14 Modul Fungsional Utama:
1. **Katalog Produk:** Menampilkan varian menu aktif, status ketersediaan, badge stok real-time, dan informasi lapak.
2. **Keranjang Belanja:** Manajemen keranjang multi-item, validasi stok dinamis, dan persistensi database per akun.
3. **Checkout Transaksi:** Pilihan metode pemenuhan (*Pesan Antar* vs *Ambil Sendiri di Lapak*), penentuan titik koordinat peta (Leaflet OSM), dan penahanan stok atomik.
4. **Perhitungan Ongkos Kirim Bertingkat:** Integrasi rute jalan riil OpenRouteService (ORS) dengan batasan *Geofence* Kota Mojokerto.
5. **Pembayaran Digital Midtrans QRIS Sandbox:** Generate Snap token, tampilan QR Code QRIS, penanganan webhook otomatis (*settlement, expire, deny, cancel*), dan batas waktu pembayaran 10 menit.
6. **Ambil Sendiri (Self-Pickup):** Bebas ongkir (Rp 0), tanpa kurir, dengan batas waktu pengambilan 24 jam terhitung sejak pembayaran lunas.
7. **POS Kasir Offline Lapak:** Kasir cepat untuk transaksi fisik lapak (Tunai & QRIS Dinamis), kalkulasi uang diterima dan kembalian, cetak nota, serta unduh PDF/PNG struk belanja.
8. **Pengelolaan Stok Atomik:** Menggunakan `lockForUpdate` database transaction untuk menjamin integritas stok dan pemulihan otomatis saat pesanan kedaluwarsa/dibatalkan.
9. **Manajemen Antrean & Pesanan (Owner):** Filter pesanan berdasarkan status, pencarian nomor resi/telepon, detail audit status, dan monitoring pesanan aktif lintas hari (*overnight queue*).
10. **Dashboard & Manajemen Kurir:** Kuota maksimal 2 kurir aktif, penugasan kurir oleh Owner, serta dasbor kurir khusus untuk memantau tugas baru dan menandai pesanan selesai antar.
11. **Histori Penjualan Terkonsolidasi:** Agregasi harian otomatis per varian dari transaksi daring dan luring, bebas dari duplikasi data.
12. **Prediksi Kebutuhan Stok (Simple Moving Average 21 Hari):** Analisis tren penjualan riil 3 minggu kalender untuk rekomendasi estimasi kebutuhan dan saran produksi harian.
13. **Peringatan Restok Rule-Based (F10):** Notifikasi otomatis saat stok varian menyentuh ambang batas minimum ($\le 5$ botol) beserta saran isi ulang cooler box.
14. **Pengelolaan Lokasi & Jam Operasional:** Pengaturan koordinat lokasi lapak aktif, jam buka-tutup operasional, dan proteksi otomatis checkout saat lapak tutup.

### Peran Pengguna (*Role-Based Access Control*):
| Peran | Hak Akses Utama |
|---|---|
| **Pelanggan (`pelanggan`)** | Katalog publik, keranjang persisten, checkout antar kurir / ambil sendiri, pembayaran QRIS, lacak status *live*, notifikasi web, riwayat pesanan, nota digital (PDF/PNG/Print), dan hubungi penjual via WhatsApp. |
| **Owner / Pengelola (`admin`)** | Dashboard operasional & omset bersih (tanpa ongkir), penugasan kurir, penyerahan pesanan ambil sendiri, POS Kasir lapak, kelola produk & restok ($\le 5$), riwayat penjualan & ekspor Excel, prediksi Moving Average 21 hari, serta pengaturan lokasi & jam lapak. |
| **Kurir (`kurir`)** | Dashboard tugas baru (terisolasi per kurir), tombol rute Google Maps & WhatsApp pelanggan, konfirmasi pesanan selesai antar, dan riwayat tugas selesai. |

---

## 2. Panduan Instalasi & Setup Lokal (Setup from Scratch)

### Prasyarat Sistem:
* PHP $\ge$ 8.2 (ekstensi wajib: `pdo`, `pdo_mysql`, `curl`, `mbstring`, `openssl`, `gd`, `zip`, `fileinfo`)
* Composer $\ge$ 2.5
* Node.js $\ge$ 18.x & NPM
* MySQL $\ge$ 8.0 atau MariaDB $\ge$ 10.4

### Langkah Instalasi:

1. **Clone Repositori:**
   ```bash
   git clone https://github.com/arifcip1/ppl-kopi-tiga-titik.git
   cd ppl-kopi-tiga-titik
   ```

2. **Instal Dependensi Backend (Composer):**
   ```bash
   composer install
   ```

3. **Instal & Build Dependensi Frontend (NPM):**
   ```bash
   npm install
   npm run build
   ```

4. **Konfigurasi Berkas Lingkungan (`.env`):**
   Salin berkas contoh `.env.example` menjadi `.env`:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Buka berkas `.env` dan sesuaikan parameter utama berikut:
   ```env
   APP_NAME="Kopi Tiga Titik"
   APP_ENV=local
   APP_KEY=
   APP_DEBUG=true
   APP_URL=http://localhost:8000
   APP_TIMEZONE=Asia/Jakarta

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ppl_kopi_tiga_titik
   DB_USERNAME=root
   DB_PASSWORD=

   # Kredensial Midtrans Sandbox
   MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxxxxxxxxxxxxxxxxxx
   MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxxxxxxxxxxxxxxxxxx
   MIDTRANS_IS_PRODUCTION=false
   MIDTRANS_IS_SANITIZED=true
   MIDTRANS_IS_3DS=true

   # OpenRouteService (Routing Jarak Riil)
   # Jika OPENROUTESERVICE_API_KEY kosong, sistem otomatis beralih ke Fallback Mode Simulasi Haversine x 1.25
   OPENROUTESERVICE_API_KEY=
   OPENROUTESERVICE_SIMULATION=false

   # Kontak WhatsApp Resmi Lapak (Nota & Hubungi Penjual)
   SELLER_WHATSAPP_NUMBER=6285710885120
   SELLER_WHATSAPP_DISPLAY="0857-1088-5120"
   SELLER_STORE_NAME="Kopi Tiga Titik Mojokerto"
   SELLER_STORE_ADDRESS="Jl. Kuwung, Mergelo, Kec. Magersari, Kota Mojokerto"
   ```

5. **Migrasi Database & Seeding Data Awal:**
   Pastikan database kosong bernama `ppl_kopi_tiga_titik` sudah dibuat di MySQL Anda, kemudian jalankan:
   ```bash
   php artisan migrate:fresh --seed
   php artisan storage:link
   php artisan optimize:clear
   ```
   *Perintah ini menyiapkan 10 tabel domain, akun default seluruh peran, katalog menu, lokasi lapak aktif, konfigurasi operasional, serta data transaksi historis 2 bulan.*

6. **Jalankan Server Lokal:**
   ```bash
   php artisan serve
   ```
   Aplikasi dapat diakses melalui peramban web di: `http://localhost:8000`.

---

### Menjalankan Laravel Scheduler (Otomatisasi Pembatalan Pesanan):
Sistem memiliki perintah terjadwal `orders:expire` yang memeriksa pesanan kedaluwarsa (lewat dari 10 menit tanpa bayar) dan mengembalikan stok tertahan.
* **Lingkungan Pengembangan (Lokal):**
  ```bash
  php artisan schedule:work
  ```
* **Lingkungan Produksi (Cron Job Server):**
  Tambahkan entri cron berikut pada server:
  ```bash
  * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
  ```

---

### Panduan Tunneling Webhook Midtrans & Simulasi Lokal:
Agar Midtrans Sandbox dapat mengirimkan notifikasi HTTP POST ke server lokal Anda:
1. Jalankan tunneling via Ngrok atau Expose:
   ```bash
   ngrok http 8000
   ```
2. Salin URL HTTPS yang dihasilkan (misal: `https://abcd-1234.ngrok-free.app`).
3. Masuk ke [Midtrans MAP Dashboard](https://dashboard.sandbox.midtrans.com) $\rightarrow$ **Settings** $\rightarrow$ **Configuration** $\rightarrow$ Isi **Payment Notification URL** dengan:
   ```text
   https://abcd-1234.ngrok-free.app/api/payments/webhook
   ```
4. **Fitur Simulasi Cepat (Tanpa Internet / Mode Lokal):**
   Di lingkungan `local` dan `testing`, sistem menyediakan tombol dan endpoint simulasi bayar lunas:
   * **Endpoint:** `POST /api/orders/{identifier}/simulate-payment`
   * Memungkinkan penguji mensimulasikan pelunasan pesanan secara instan dengan verifikasi otorisasi pemilik pesanan yang aman.

---

### Menjalankan Automated Testing:
Untuk memverifikasi integritas seluruh modul dan regresi:
```bash
php artisan test
```
*Hasil yang diharapkan: **306 passed, 1511 assertions** (100% hijau).*

---

## 3. Akun Demo Terstandar

Data akun bawaan yang di-generate melalui `UserSeeder.php`:

| Peran (Role) | Nama Pengguna | Alamat Email | Password | Hak Akses Utama |
|---|---|---|---|---|
| **Admin / Owner** | Farrel | `owner1@gmail.com` | `password123` | Akses penuh Dashboard Owner, POS Kasir, Kelola Produk, Alur Pesanan, Penugasan Kurir, Prediksi Moving Average, Laporan Penjualan & Ekspor Excel, Pengaturan Lokasi & Jam Lapak. |
| **Kurir 1** | Ilham Ramadhan | `kurir1ilham@gmail.com` | `password123` | Dasbor Kurir, melihat daftar tugas antar pesanan yang ditugaskan, navigasi Google Maps, menandai pesanan selesai antar. |
| **Kurir 2** | Awaludin | `kurirawal@gmail.com` | `password123` | Akun kurir kedua (kuota sistem maks. 2 kurir aktif). |
| **Pelanggan 1** | Nabil Pratama | `nabil@gmail.com` | `password123` | Belanja katalog, keranjang, checkout pengiriman & ambil sendiri, pelunasan QRIS, lacak pesanan, riwayat belanja. |
| **Pelanggan 2** | Arif | `arif@gmail.com` | `password123` | Akun pelanggan alternatif untuk pengujian isolasi data antar-pembeli. |
| **Pelanggan 3** | Nizam | `nizam@gmail.com` | `password123` | Akun pelanggan alternatif. |

---

## 4. Dokumentasi Logika Bisnis & Contoh Perhitungan

### 4.1 Peramalan Kebutuhan Stok (Simple Moving Average 21 Hari — FR-9.5)
Sistem menggunakan data transaksi riil (`sumber = 'transaksi'`) selama **3 minggu kalender terakhir (21 hari)** yang dibagi menjadi 3 blok mingguan ($H-21$ s.d. $H-1$):
* **Minggu 1:** $H-21$ s.d. $H-15$ (7 hari)
* **Minggu 2:** $H-14$ s.d. $H-8$ (7 hari)
* **Minggu 3:** $H-7$ s.d. $H-1$ (7 hari)

$$\text{Estimasi Penjualan Harian} = \frac{\text{Penjualan M1} + \text{Penjualan M2} + \text{Penjualan M3}}{21}$$

$$\text{Saran Produksi} = \max\left(0, \lceil\text{Estimasi}\rceil - \text{Stok Fisik Saat Ini}\right)$$

> **Contoh Perhitungan Angka (Varian Titik Temu):**
> * Penjualan Minggu 1 ($W_1$): 56 botol
> * Penjualan Minggu 2 ($W_2$): 70 botol
> * Penjualan Minggu 3 ($W_3$): 84 botol
> * Total 21 Hari: $56 + 70 + 84 = 210$ botol
> * **Estimasi Harian:** $210 \div 21 = \mathbf{10{,}0\text{ botol/hari}}$
> * Jika stok fisik tersisa di cooler box = **4 botol**:
>   $$\text{Saran Produksi} = \max(0, 10 - 4) = \mathbf{6\text{ botol}}$$

---

### 4.2 Peringatan Restok Rule-Based (F10 & BR-18)
* **Ambang Batas Peringatan:** Dibaca dinamis dari `Setting::where('key', 'batas_stok_minimum')` (default: **$\le 5$ botol**).
* **Acuan Stok Standar:** `Setting::where('key', 'stok_harian_standar')` (default: **10 botol**).
* **Rumus Saran Produksi:**
  $$\text{Saran Produksi Restok} = \max\left(0, \text{Stok Harian Standar} - \text{Stok Saat Ini}\right)$$
* **Contoh Kasus:**
  Saat kasir POS menjual produk hingga stok tersisa **3 botol** ($\le 5$ botol):
  1. Kartu produk di POS menampilkan badge merah **"Restok"**.
  2. Panel notifikasi atas (F10) dan Notifikasi Web Owner memunculkan peringatan restok.
  3. Saran produksi otomatis: $\max(0, 10 - 3) = \mathbf{7\text{ botol}}$.

---

### 4.3 Perhitungan Tarif Ongkir & Geofence Kota Mojokerto (BR-14 & FR-7.3)
Pengiriman dihitung dari titik lapak aktif (`ShippingLocation`) menuju titik koordinat pembeli:
* **Validasi Geofence Wilayah:** Khusus wilayah Kota Mojokerto dengan *bounding box* koordinat:
  * Latitude: $-7.530000$ s.d. $-7.420000$
  * Longitude: $112.390000$ s.d. $112.480000$
  * *Jika di luar jangkauan:* Menolak pesanan dengan pesan `"Titik lokasi tujuan berada di luar jangkauan wilayah Kota Mojokerto."`
* **Formula Tarif Bertingkat:**
  * Jarak rute riil $d \le 5{,}5\text{ km}$: **Gratis Ongkir (Rp 0 / Promo Lokal)**.
  * Jarak rute riil $d > 5{,}5\text{ km}$: Dikenakan tarif bertingkat Rp 2.000 per kelipatan km:
    $$\text{Step} = \lfloor d - 5{,}5 \rfloor + 1$$
    $$\text{Ongkos Kirim} = 2.000 \times \text{Step}$$
* **Contoh:**
  * Jarak 4,2 km $\rightarrow$ Rp 0 (Gratis).
  * Jarak 6,3 km $\rightarrow \lfloor 6{,}3 - 5{,}5 \rfloor + 1 = 1 \rightarrow \mathbf{\text{Rp } 2.000}$.
  * Jarak 7,8 km $\rightarrow \lfloor 7{,}8 - 5{,}5 \rfloor + 1 = 3 \rightarrow \mathbf{\text{Rp } 6.000}$.
* **Fallback Jarak:** Jika API OpenRouteService tidak tersedia atau `OPENROUTESERVICE_SIMULATION=true`, sistem menggunakan formula *Haversine* dikalikan faktor koreksi jaringan jalan perkotaan ($1{,}25 \times \text{Haversine}$).

---

### 4.4 Aturan Transaksi POS Kasir Lapak & Ambil Sendiri
1. **POS Kasir Offline (BR-13):**
   * Transaksi bersifat atomik melalui database lock `lockForUpdate`.
   * Nilai `sumber_transaksi = 'pos'`.
   * Nilai `metode_pemenuhan = null` (karena dilayani langsung di lapak fisik tanpa pengiriman).
   * Nilai omzet dan botol terjual langsung terakumulasi ke `sales_history` hari ini.
2. **Ambil Sendiri (Self-Pickup — S7 & BR-19):**
   * Nilai `sumber_transaksi = 'online'`, `metode_pemenuhan = 'ambil_sendiri'`, `ongkir = 0`.
   * Field `alamat` bersifat opsional (otomatis terisi alamat lapak aktif jika dikosongkan).
   * **Batas Waktu Pengambilan (24 Jam):** Dihitung tepat **24 jam sejak pembayaran lunas terverifikasi** (`now()->addHours(24)`).
   * Penyerahan produk dilakukan langsung di lapak oleh Owner dengan menekan tombol **"Selesaikan Pesanan"** pada detail pesanan di Dashboard.

---

## 5. Ringkasan Endpoint & Route Utama

Seluruh endpoint terdaftar aktif di `routes/web.php` dan dikelompokkan berdasarkan fungsinya:

### A. Katalog, Keranjang, & Checkout Pelanggan
| Method | URI Route | Nama Rute | Deskripsi |
|---|---|---|---|
| `GET` | `/katalog` | `katalog.index` | Halaman menu produk aktif & status lapak |
| `GET` | `/api/products` | `api.products.index` | API JSON daftar produk aktif |
| `GET` | `/cart` | `cart` | Tampilan keranjang belanja pembeli |
| `POST` | `/api/cart/items` | `api.cart.store` | Tambah produk ke keranjang belanja |
| `PATCH` | `/api/cart/items/{id}` | `api.cart.update` | Perbarui jumlah pesanan item keranjang |
| `DELETE`| `/api/cart/items/{id}` | `api.cart.destroy` | Hapus item dari keranjang belanja |
| `GET` | `/checkout` | `checkout` | Tampilan form pengiriman dan ringkasan biaya |
| `POST` | `/checkout` | `checkout.process` | Eksekusi pembuatan pesanan (menahan stok) |
| `POST` | `/api/shipping/quote` | `api.shipping.quote` | API estimasi jarak rute dan tarif ongkos kirim |

### B. Pembayaran Digital & Webhook Provider
| Method | URI Route | Nama Rute | Deskripsi |
|---|---|---|---|
| `GET` | `/pembayaran-qris/{kode?}` | `payment` | Tampilan kode bayar QRIS Snap Midtrans |
| `POST` | `/api/orders/{identifier}/simulate-payment` | `api.orders.simulate-payment` | Simulasi lunas lokal (khusus dev/testing) |
| `GET` | `/api/orders/{identifier}/payment-status` | `api.orders.payment-status` | Polling status pelunasan transaksi |
| `POST` | `/api/payments/webhook` | `api.payments.webhook` | Webhook notifikasi pembayaran server-to-server Midtrans |

### C. Riwayat Pesanan & Dasbor Tugas Kurir
| Method | URI Route | Nama Rute | Deskripsi |
|---|---|---|---|
| `GET` | `/riwayat-pesanan` | `orders` | Daftar riwayat transaksi pelanggan |
| `GET` | `/riwayat-pesanan/{kode}` | `orders.show` | Detail pesanan, nota digital & link WhatsApp |
| `POST` | `/orders/{order}/cancel` | `orders.cancel` | Pembatalan mandiri pesanan sebelum kedaluwarsa |
| `GET` | `/kurir/tugas-baru` | `kurir.tugas-baru` | Antrean pesanan siap antar milik kurir |
| `GET` | `/kurir/selesai` | `kurir.selesai` | Riwayat pengantaran berhasil kurir |
| `POST` | `/kurir/orders/{order}/complete` | `kurir.orders.complete` | Konfirmasi kurir bahwa pesanan telah diterima |

### D. POS Kasir Offline Lapak
| Method | URI Route | Nama Rute | Deskripsi |
|---|---|---|---|
| `GET` | `/owner/pos` | `owner.pos` | Tampilan antarmuka layar sentuh POS Kasir Lapak |
| `POST` | `/owner/pos/transactions` | `owner.pos.transaction` | Simpan transaksi kasir (Tunai / QRIS Fisik) |
| `GET` | `/api/orders/{kode}/nota` | `api.orders.nota` | Cetak dan unduh nota struk kasir |

### E. Dasbor Operasional, Analitik, & Ekspor (Owner)
| Method | URI Route | Nama Rute | Deskripsi |
|---|---|---|---|
| `GET` | `/owner/dashboard` | `owner.dashboard` | Ringkasan omzet hari ini dan antrean pesanan |
| `GET` | `/owner/orders/{order}/detail`| `owner.orders.detail` | Modal detail audit pesanan dan aksi cetak nota |
| `POST` | `/owner/orders/{order}/assign-courier` | `owner.orders.assign-courier` | Menugaskan kurir pada pesanan siap antar |
| `POST` | `/owner/orders/{order}/complete-pickup` | `owner.orders.complete-pickup` | Konfirmasi penyerahan pesanan ambil sendiri |
| `GET` | `/owner/prediksi` | `owner.prediksi` | Analisis Moving Average & perkiraan restok |
| `GET` | `/owner/riwayat-penjualan` | `owner.riwayat-penjualan` | Riwayat gabungan online + offline per tanggal |
| `GET` | `/owner/riwayat-penjualan/export` | `owner.export.sales` | Ekspor rekapitulasi penjualan ke format Excel |
| `GET` | `/owner/produk` | `owner.produk.index` | Kelola menu katalog (CRUD varian & stok) |
| `GET` | `/owner/couriers` | `owner.couriers.index` | Kelola data akun kurir lapak (maks. 2 akun) |
| `GET` | `/owner/update-lokasi` | `owner.update-lokasi` | Pengaturan titik lokasi lapak dan jam buka |
| `GET` | `/api/notifications` | `api.notifications` | API Notifikasi dinamis berbasis peran (F14) |

---

## 6. Panduan Penggunaan Aplikasi (User Manual)

```
       +-----------------------------------------------------------+
       |               ALUR PENGGUNAAN UTAMA SISTEM                |
       +-----------------------------------------------------------+
                                     |
         +---------------------------+---------------------------+
         |                                                       |
 [1. PELANGGAN]                                            [2. OWNER / KASIR]
         |                                                       |
   Buka Katalog                                             Buka POS Kasir
         |                                                       |
 Pilih Menu & Keranjang                                     Pilih Menu Langsung
         |                                                       |
 Pilih Ambil / Antar                                        Bayar Tunai / QRIS
         |                                                       |
 Bayar QRIS (Midtrans)                                      Stok Berkurang Otomatis
         |                                                       |
 Lacak Pesanan Realtime                                     Cetak / Unduh Nota
         |                                                       |
         +---------------------------+---------------------------+
                                     |
                               [3. KURIR]
                                     |
                         Terima Notifikasi Tugas
                                     |
                             Antar ke Pembeli
                                     |
                           Klik Selesai Antar
                                     |
                                [SELESAI]
```

### 6.1 Peran Pelanggan (Customer)
1. **Pemesanan Menu:** Masuk ke menu **Katalog**, pilih varian kopi botol yang diinginkan, tentukan jumlah, dan masukkan ke **Keranjang**.
2. **Pilihan Checkout:**
   * **Pesan Antar:** Tentukan titik lokasi rumah pada peta interaktif. Sistem otomatis menghitung tarif ongkir dan memvalidasi batas wilayah Kota Mojokerto.
   * **Ambil Sendiri:** Pilih opsi *Ambil Sendiri di Lapak*. Biaya ongkir otomatis Rp 0 dan alamat otomatis disinkronkan ke alamat lapak Jl. Kuwung.
3. **Pembayaran QRIS:** Pindai kode QRIS yang muncul di layar melalui aplikasi e-wallet atau m-banking. Pesanan otomatis terverifikasi dan masuk status `diproses`.
4. **Pelacakan & Nota:** Pantau perjalanan pesanan di halaman **Riwayat Pesanan** atau unduh nota digital struk belanja.

### 6.2 Peran Kurir (Courier)
1. **Melihat Tugas:** Buka menu **Tugas Baru** untuk melihat pesanan yang telah ditugaskan oleh Owner lengkap dengan nama penerima, alamat tujuan, nomor telepon WhatsApp, dan rincian pesanan.
2. **Pengantaran:** Antarkan pesanan ke alamat pembeli. Tersedia tombol tautan cepat untuk membuka navigasi rute Google Maps.
3. **Penyelesaian Pengantaran:** Setelah pesanan diterima pembeli, tekan tombol **"Selesaikan Pengantaran"**. Status pesanan otomatis berubah menjadi `selesai` dan berpindah ke tab **Selesai**.

### 6.3 Peran Owner / Admin
1. **Operasional Harian (Dashboard):**
   * Memantau ringkasan omzet bersih hari ini (tanpa ongkir), jumlah botol terjual, dan total pesanan masuk.
   * Pada tab antrean pesanan: lakukan penugasan kurir untuk pesanan antar, atau serahkan pesanan untuk opsi ambil sendiri di lapak.
2. **POS Kasir Offline Lapak:**
   * Masuk ke menu **POS Kasir (F10)** saat melayani pembeli langsung di lapak fisik.
   * Klik varian menu, masukkan nominal uang diterima, tekan **Bayar**, lalu berikan struk belanja fisik kepada pembeli.
   * Jika stok varian tersisa $\le 5$ botol, sistem langsung memberikan peringatan restok di layar POS.
3. **Analitik & Ekspor:**
   * Masuk ke menu **Prediksi** untuk melihat tren Simple Moving Average 21 hari dan anjuran jumlah produksi besok.
   * Masuk ke **Riwayat Penjualan** untuk melihat rincian transaksi gabungan, serta unduh rekapitulasi berkas Excel melalui tombol **Ekspor Excel**.
4. **Pengaturan Lokasi Lapak:**
   * Perbarui titik lapak atau jam operasional melalui menu **Update Lokasi** jika lapak berpindah tempat atau libur operasional.

---

## 7. Lisensi & Hak Cipta
Sistem Informasi Penjualan Kopi Tiga Titik dikembangkan untuk memenuhi tugas mata kuliah Proyek Pengembangan Perangkat Lunak (PPL). Seluruh kode berada di bawah lisensi terbuka [MIT License](LICENSE).
