# Kopi Tiga Titik — E-Commerce UMKM & POS Kasir Lapak (Mojokerto)

Sistem informasi penjualan berbasis web (*mobile-first* & desktop responsif) untuk UMKM **Kopi Tiga Titik** di Kota Mojokerto (Mata Kuliah Proyek Perangkat Lunak). Aplikasi ini mengintegrasikan pemesanan online pelanggan (antar kurir maupun ambil sendiri di lapak), pembayaran **QRIS Sandbox (Midtrans)**, perhitungan ongkos kirim rute jalan **OpenStreetMap / OpenRouteService (ORS)**, manajemen tugas **Kurir**, **POS Kasir Offline** di lapak fisik, laporan & ekspor **Excel**, serta **Prediksi Stok Moving Average**.

---

## Anggota Tim

| Nama | NIM | Peran |
|---|---|---|
| Muhammad Farrel Fastha | 240411100114 | Product Owner / System Analyst / Frontend & Backend / Dokumentasi & Branding |
| M. ARIF | 240411100084 | Scrum Master / QA Tester / Frontend & Backend / Dokumentasi & Branding |
| Badruz Zaman Ash Sholih | 240411100140 | UI/UX Designer / Frontend & Backend / Dokumentasi & Branding |
| Ahmad Dafi Zidni Alfarisi | 240411100027 | UI/UX Designer / Frontend & Backend / Dokumentasi & Branding |

---

## 1. Peran Pengguna & Fitur Utama

| Peran | Akses Utama |
|---|---|
| **Pelanggan (`pelanggan`)** | Katalog produk *real-time* (publik tanpa login), keranjang belanja persisten, pilih titik antar di peta Leaflet (validasi geofence Kota Mojokerto) atau **Ambil Sendiri (24 jam, ongkir Rp0)**, pembayaran QRIS Dinamis, pelacakan status pesanan *live*, notifikasi web, riwayat pesanan, nota digital (PDF/PNG/Print + Hubungi Penjual WhatsApp), dan manajemen profil/alamat utama. |
| **Owner / Pengelola (`admin`)** | Dashboard ringkasan omset bersih (tanpa ongkir) & antrean pesanan, penugasan kurir, penyerahan pesanan Ambil Sendiri, kelola akun kurir (maks. 2 akun), kelola katalog produk & peringatan restok ($\le 5$ botol), **POS Kasir Lapak** (Tunai & QRIS Statis), **Riwayat Penjualan** + grafik + ekspor `.xlsx`/`.csv`, **Prediksi Moving Average**, notifikasi web global, serta pengaturan lokasi lapak aktif & jam operasional. |
| **Kurir (`kurir`)** | Dashboard tugas antar baru (terisolasi per kurir), tombol cepat rute peta & WhatsApp pelanggan, konfirmasi pesanan selesai diterima pembeli, riwayat pengantaran selesai, lonceng notifikasi tugas, dan manajemen profil kurir. |

---

## 2. Teknologi & Prasyarat

* **PHP:** `^8.2` (disarankan PHP 8.3 dengan ekstensi `zip`, `pdo_mysql`, `gd`/`fileinfo` aktif; `ext-zip` diperlukan agar ekspor Excel menghasilkan `.xlsx` via `maatwebsite/excel` `^4.0`, jika tidak aktif otomatis *fallback* ke `.csv` ber-BOM UTF-8).
* **Framework:** Laravel `^12.0`
* **Frontend:** Blade, Tailwind CSS (via Vite), Leaflet.js, Chart.js, `html2pdf.bundle.min.js` (lokal di `public/js/`), Material Symbols Outlined.
* **Database:** MySQL / MariaDB (lokal via Laragon/XAMPP) dan SQLite `:memory:` untuk PHPUnit.

---

## 3. Panduan Instalasi & Setup Lokal

### 3.1 Kloning & Instalasi Dependensi
```bash
git clone <url-repository>
cd ppl-kopi-tiga-titik
composer install
npm install
npm run build
```

### 3.2 Konfigurasi `.env`
Salin template `.env.example` menjadi `.env`:
```bash
cp .env.example .env
php artisan key:generate
```

Pastikan variabel penting berikut terisi di `.env`:
```env
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ppl_kopi_tiga_titik
DB_USERNAME=root
DB_PASSWORD=

# OpenRouteService (ORS) untuk rute jalan Kota Mojokerto
# Catatan: Di local/testing dapat menggunakan fallback simulasi jika kosong,
# namun untuk rute riil gunakan API key dari openrouteservice.org
OPENROUTESERVICE_API_KEY=

# Midtrans QRIS Sandbox
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxx
MIDTRANS_MERCHANT_ID=Gxxxxxxxxx
MIDTRANS_IS_PRODUCTION=false

# Identitas Seller & WhatsApp (Nota Digital & Hubungi Penjual)
SELLER_WHATSAPP_NUMBER=6285710885120
SELLER_WHATSAPP_DISPLAY="0857-1088-5120"
SELLER_STORE_NAME="Kopi Tiga Titik Mojokerto"
SELLER_STORE_ADDRESS="Jl. Kuwung, Mergelo, Kec. Magersari, Kota Mojokerto"
```

> **Keamanan Tim:** Jangan pernah meng-commit file `.env` asli. Gunakan `.env.example` sebagai template tanpa nilai rahasia.

### 3.3 Migrasi Database, Seeder, & Storage Link
```bash
php artisan migrate:fresh --seed
php artisan storage:link
php artisan optimize:clear
```

### 3.4 Menjalankan Server, Scheduler, & Webhook Tunnel
1. **Jalankan aplikasi lokal:**
   ```bash
   php artisan serve
   # atau jalankan stack dev (server + queue + pail + vite)
   composer run dev
   ```
2. **Jalankan Scheduler Pembatalan Otomatis Pesanan Kedaluwarsa (`orders:expire` setiap menit):**
   ```bash
   php artisan schedule:work
   ```
3. **Tunneling Webhook Midtrans (opsional saat menguji callback live Sandbox):**
   Gunakan ngrok / Cloudflare Tunnel yang mengarah ke `http://127.0.0.1:8000` dan arahkan URL notifikasi Midtrans Sandbox ke:
   ```text
   https://<domain-tunnel>/api/payments/webhook
   ```

---

## 4. Akun Demo Bawaan (`UserSeeder`)

| Peran | Nama | Email | Password |
|---|---|---|---|
| **Owner (`admin`)** | Farrel | `owner1@gmail.com` | `password123` |
| **Kurir #1 (`kurir`)** | Ilham Ramadhan | `kurir1ilham@gmail.com` | `password123` |
| **Kurir #2 (`kurir`)** | awaludin | `kurirawal@gmail.com` | `password123` |
| **Pelanggan #1 (`pelanggan`)** | Nabil Pratama | `nabil@gmail.com` | `password123` |
| **Pelanggan #2 (`pelanggan`)** | Budi Santoso | `budi@gmail.com` | `password123` |

*(Catatan: Untuk demo sesi checkout online, pastikan di halaman Owner **Pengaturan Lapak** `/owner/update-lokasi` status lapak **Buka** dan jam operasional mencakup waktu demo berlangsung.)*

---

## 5. Aturan Bisnis & Parameter Komputasional

1. **Tarif Ongkir Berbasis Rute Jalan OSM/ORS (`OsmRouteShippingCostResolver`):**
   * Format koordinat yang dikirim ke OpenRouteService Directions API (`driving-car`) adalah `[longitude, latitude]`.
   * **Geofence Wilayah Layanan Kota Mojokerto:**
     $$\text{Lat} \in [-7.530000,\ -7.420000], \quad \text{Lng} \in [112.390000,\ 112.480000]$$
     Titik di luar rentang tersebut otomatis ditolak (`HTTP 422`).
   * **Formula Tarif Delivery:**
     * Jarak rute $d \le 5{,}5\text{ km}$ $\rightarrow$ **Rp 0 (Gratis Ongkir Promo)**.
     * Jarak rute $d > 5{,}5\text{ km}$ $\rightarrow$ $\text{Rp } 2.000 \times (\lfloor d - 5{,}5 \rfloor + 1)$.
   * **Ambil Sendiri di Lapak:** Ongkir otomatis **Rp 0** dan tenggat waktu pengambilan **24 jam** sejak pesanan dibuat/terverifikasi.

2. **Prediksi Stok Moving Average (`ForecastService`):**
   * Menggunakan jendela **3 minggu (21 hari kalender: H-21 s.d. H-1)** hanya dari histori transaksi riil (`sales_history.sumber = 'transaksi'`; baris `'simulasi'` diabaikan).
   * Hari tanpa penjualan tetap dihitung dalam pembagi 21 hari:
     $$\text{Estimasi Harian} = \text{round}\left(\frac{\text{Minggu}_1 + \text{Minggu}_2 + \text{Minggu}_3}{21},\ 1\right)$$
     $$\text{Saran Produksi} = \max\big(0,\ \lceil \text{Estimasi Harian} \rceil - \text{Stok Saat Ini}\big)$$

3. **Aturan Peringatan Restok (`BR-10` / `BR-19`):**
   * Produk aktif dengan `stok <= batas_stok_minimum` (default **5 botol** di tabel `settings`) otomatis memicu badge peringatan di Kelola Produk, Dashboard Owner, POS Kasir, serta lonceng Notifikasi Web Owner.

4. **Proteksi Jam Operasional (`LapakOperationalService`):**
   * Checkout online baru (Delivery maupun Ambil Sendiri) diblokir secara otomatis jika status manual `tutup` atau waktu server (`Asia/Jakarta`) berada di luar rentang `jam_buka` – `jam_tutup`.

---

## 6. Ringkasan Endpoint Utama

### Web & API Pelanggan
* `GET /katalog` & `GET /api/products` — Katalog publik produk aktif & stok *real-time*.
* `GET /cart`, `POST /cart/items`, `PATCH /cart/items/{id}`, `DELETE /cart/items/{id}` — Manajemen keranjang.
* `POST /api/shipping/quote` — Perhitungan ongkir OSM & validasi geofence Mojokerto.
* `GET /checkout`, `POST /checkout`, `POST /api/checkout` — Proses checkout & pembuatan pesanan.
* `GET /pembayaran-qris/{kode?}`, `POST /api/payments/webhook` — Pembayaran QRIS & webhook Midtrans.
* `GET /riwayat-pesanan`, `GET /order-tracking/{kode}`, `GET /orders/{kode}/nota` — Pelacakan & Nota Digital.

### Web & API Owner / Kurir / Bersama
* `GET /owner/dashboard`, `GET /owner/orders/{order}/detail`, `POST /owner/orders/{order}/assign-courier`, `POST /owner/orders/{order}/complete-pickup`
* `GET /owner/pos`, `POST /owner/pos/transactions` — POS Kasir Lapak (Tunai & QRIS Langsung).
* `GET /owner/produk*` — CRUD & update stok produk kopi.
* `GET /owner/riwayat-penjualan`, `GET /owner/riwayat-penjualan/export?periode=harian|mingguan|bulanan` — Laporan & Ekspor Excel.
* `GET /owner/prediksi` — Forecasting stok Moving Average 21 hari.
* `GET /owner/update-lokasi`, `POST /owner/update-lokasi` — Pengaturan titik koordinat lapak aktif & jam operasional.
* `GET /kurir/tugas-baru`, `GET /kurir/selesai`, `POST /kurir/orders/{order}/complete` — Portal tugas Kurir.
* `GET /api/notifications` — Notifikasi web dinamis berbasis peran (*Owner, Kurir, Pelanggan*).

---

## 7. Pengujian Otomatis (`PHPUnit`)

Jalankan seluruh rangkaian automated test sebelum rilis atau demo:
```bash
php artisan test
```
Atau jalankan suite verifikasi spesifik per modul (misalnya `Ppl37BugFixAndRbacAuditTest`, `Ppl38IntegrationAndRegressionTest`, `Ppl39UiAndDemoReadinessTest`, `EndToEndRegressionSprint1To3Test`).
