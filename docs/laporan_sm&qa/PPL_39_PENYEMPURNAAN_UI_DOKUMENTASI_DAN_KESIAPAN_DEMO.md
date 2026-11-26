# DOKUMENTASI PENYEMPURNAAN UI, FITUR LENGKAP, & KESIAPAN DEMO (PPL-39)
## Proyek: Sistem Informasi Penjualan Kopi Tiga Titik (Online & POS Kasir Lapak)

---

| Informasi | Detail |
|---|---|
| **Jira ID / Work Package** | `PPL-43` / **WBS (PPL 39)** |
| **Judul Tugas** | Penyempurnaan UI Responsif, Perbaikan Tampilan Dashboard Owner dan Kurir, POS Kasir Offline, Riwayat Penjualan, Ekspor Excel, Notifikasi Web, Nota Digital, Pengaturan Lokasi dan Jam Operasional, Dokumentasi Fitur & Kesiapan Demo |
| **Branch** | `feature/PPL-43-penyempurnaan-ui-dokumentasi-fitur-dan-kesiapan-demo` |
| **Acuan Dokumen** | `PRD_Kopi_Tiga_Titik_Konsolidasi.md` (`FR-X.6`, `BL-2`, `BL-17`, `BL-25`) & `PPL_37_DOKUMENTASI_FITUR_DAN_PERSIAPAN_RILIS_DEMO.md` |

---

## 1. Audit & Perbaikan UI Responsif (Mobile & Desktop) pada `PPL-39`

| No | Halaman / Komponen | File Terkait | Penyempurnaan yang Diterapkan |
|---|---|---|---|
| 1 | **Layout & Notifikasi Web Owner** | `resources/views/layouts/owner.blade.php`, `app/Providers/AppServiceProvider.php`, `resources/views/Owner Dashboard & Mini POS Page.blade.php` | Memindahkan lonceng Notifikasi Web ke header global `layouts/owner.blade.php` dengan injeksi otomatis via `AppServiceProvider`, sehingga notifikasi Owner (butuh kurir, pickup siap diserahkan, stok $\le 5$) tersedia konsisten di Dashboard, POS Kasir, Kelola Produk, Riwayat Penjualan, Prediksi Stok, dan Profil Owner. Dropdown dibatasi `w-[calc(100vw-2rem)] max-w-sm` agar tidak terpotong di HP sempit. |
| 2 | **Layout, Favicon, & Notifikasi Web Pelanggan** | `resources/views/layouts/pelanggan.blade.php`, `app/Providers/AppServiceProvider.php` | Menambahkan tag favicon `images/logo.png`, merapikan header dengan `min-w-0` / `truncate`, serta memasang lonceng Notifikasi Web untuk Pelanggan yang menampilkan pembaruan status pesanan secara responsif. |
| 3 | **Dashboard Owner** | `resources/views/Owner Dashboard & Mini POS Page.blade.php` | Menambahkan `flex-wrap gap-2` pada baris judul *Ringkasan Hari Ini*, baris badge *Titik Lapak Hari Ini*, header & footer kartu pesanan, serta `max-h-[90vh] overflow-y-auto` pada modal penugasan kurir agar nyaman pada viewport mobile `320px–400px`. |
| 4 | **Dashboard Kurir** | `resources/views/layouts/kurir.blade.php`, `resources/views/Dashboard Kurir Tab Tugas Baru Page.blade.php`, `resources/views/Dashboard Kurir Tab Selesai Page.blade.php` | Membatasi lebar dropdown notifikasi kurir di mobile (`w-[calc(100vw-2rem)] max-w-sm`), menambahkan scroll horizontal halus pada tab navigasi, `flex-wrap gap-2` pada header/footer kartu tugas, dan tombol cepat WhatsApp/Peta responsif (`grid-cols-1 sm:grid-cols-2`). |
| 5 | **POS Kasir Offline** | `resources/views/owner/pos.blade.php` | Menambahkan tombol cepat loncat ke keranjang (`#mobilePosCartBar`) khusus mobile/tablet (`< 1024px`) yang tampil dinamis saat keranjang terisi, `flex-wrap` pada footer kartu menu 2 kolom, scroll internal pada modal struk sukses, serta toast container responsif (`left-4 right-4 sm:left-auto`). |
| 6 | **Riwayat Penjualan & Ekspor Excel** | `resources/views/owner/riwayat-penjualan.blade.php` | Mengisi label keterangan yang sebelumnya kosong di 3 kartu ringkasan, header grafik, dan header tabel; menyederhanakan label tombol header **Ekspor ke Excel** menjadi `Excel` di mobile agar judul header tetap terbaca; serta menjaga keterbacaan tabel via `min-w-[540px]` dalam `overflow-x-auto`. |
| 7 | **Pengaturan Lokasi & Jam Operasional** | `resources/views/Pop Up Update Lokasi Lapak Page.blade.php` | Menambahkan `flex-wrap` pada baris label alamat + tombol *Sesuaikan Titik Peta* serta label koordinat peta + badge koordinat supaya tidak saling bertumpuk di layar HP sempit. |
| 8 | **Nota Digital** | `resources/views/Nota Digital Page.blade.php` | Memastikan seluruh tombol aksi (**Unduh File PDF**, **Unduh Gambar PNG**, **Cetak Nota**, **Hubungi Penjual via WhatsApp**, serta tombol kembali POS/Dashboard) dan layout `@media print` tampil rapi di mobile & desktop. |

---

## 2. Dokumentasi Modul Tambahan Cakupan `PPL-39`

*(Melengkapi dokumentasi utama di `README.md` dan `docs/laporan_sm&qa/PPL_37_DOKUMENTASI_FITUR_DAN_PERSIAPAN_RILIS_DEMO.md`)*

### 2.1 POS Kasir Offline (`Owner`)
* **Rute:** `GET /owner/pos`, `POST /owner/pos/transactions`, `POST /api/admin/pos/transactions`
* **Alur Kerja:**
  1. Menampilkan seluruh menu kopi aktif (`aktif = true`) dengan indikator stok dan widget peringatan restok (`stok <= 5`).
  2. Mendukung dua kanal pembayaran di lapak: **Tunai (Cash)** dengan kalkulator uang pas/pecahan & kembalian otomatis, serta **QRIS Langsung** (statis di lapak).
  3. Setiap transaksi POS langsung mengurangi stok fisik di database, membuat record `orders` (`sumber_transaksi = 'pos'`, kode prefix `POS-`), dan mencatat transaksi ke `sales_history`.
  4. Setelah transaksi sukses, Owner dapat mengunduh **Struk PNG** atau **Nota PDF** langsung dari modal maupun membuka halaman Nota Digital penuh (`/nota/{kode}`).

### 2.2 Riwayat Penjualan & Ekspor Excel (`Owner`)
* **Rute:** `GET /owner/riwayat-penjualan`, `GET /owner/riwayat-penjualan/export`
* **Fungsionalitas:**
  * Filter periode: `harian` (hari ini), `mingguan` (7 hari terakhir), dan `bulanan` (bulan berjalan).
  * Menampilkan ringkasan **Total Botol Terjual**, **Total Omzet Bersih (tanpa ongkir)**, **Varian Paling Diminati**, grafik batang interaktif **Chart.js**, dan tabel rekapitulasi gabungan transaksi **Online & POS** tanpa duplikasi (`SalesReportService`).
  * Ekspor laporan melalui `ExportController::exportSales`: menghasilkan file `.xlsx` (`SalesHistoryExport`) bila paket `maatwebsite/excel` + PHP `ext-zip` aktif, dan otomatis menyediakan fallback `.csv` ber-BOM UTF-8 yang tetap rapi saat dibuka di Microsoft Excel.

### 2.3 Nota Digital (`Owner` & `Pelanggan`)
* **Rute:** `GET /nota/{kode}`, `GET /orders/{kode}/nota`
* **Fungsionalitas:**
  * Menampilkan logo resmi Kopi Tiga Titik, identitas lapak dari konfigurasi `.env`, status pembayaran lunas, metode pemenuhan (*Pesan Antar*, *Ambil Sendiri + batas waktu 24 jam*, atau *POS Kasir Lapak*), rincian item 250ml, dan rincian biaya.
  * Tombol **Unduh PDF** & **Unduh Gambar (PNG)** berjalan secara *client-side* menggunakan library lokal `public/js/html2pdf.bundle.min.js` (tidak bergantung CDN eksternal saat demo).

### 2.4 Pengaturan Lokasi Lapak & Jam Operasional (`Owner`)
* **Rute:** `GET /owner/update-lokasi`, `POST /owner/update-lokasi`, `PATCH /owner/lokasi/{location}/select`
* **Fungsionalitas:**
  * Peta interaktif Leaflet dengan draggable marker & forward geocoding alamat, divalidasi ketat berada di *bounding box* Kota Mojokerto (`Lat: -7.530000..-7.420000`, `Lng: 112.390000..112.480000`).
  * Pemilik dapat memilih lokasi tersimpan, memperbarui jam buka/tutup (`jam_buka`, `jam_tutup`), serta status buka/tutup manual. Perubahan langsung tersinkron ke banner Katalog, Checkout, dan kalkulator ongkir OSM.

---

## 3. Verifikasi Kesiapan Demo (`Demo Readiness`)

1. **Dokumentasi Proyek (`README.md`):** Telah diperbarui penuh menggantikan template bawaan Laravel sesuai kriteria `FR-X.6` PRD.
2. **Aset Statis Kritis:** `public/images/logo.png` dan `public/js/html2pdf.bundle.min.js` tersedia lokal.
3. **Konfigurasi Template (`.env.example`):** Memuat `APP_TIMEZONE=Asia/Jakarta`, `OPENROUTESERVICE_API_KEY`, `MIDTRANS_*`, dan `SELLER_*`.
4. **Pengujian Otomatis:** Diverifikasi via `tests/Feature/Ppl39UiAndDemoReadinessTest.php` serta full suite `php artisan test`.
