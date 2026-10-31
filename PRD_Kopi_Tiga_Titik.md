# **PRODUCT REQUIREMENTS DOCUMENT (PRD)** 

# **Sistem Penjualan Online UMKM Kopi Tiga Titik** 

**Versi:** 2.3 (penyelarasan dengan PPL 01 dan migration PPL 02, 30 Oktober 2026) | **Penyusun:** Orang 1 (Product Owner + System Analyst) **Acuan jadwal, PIC, dan ID Work Package:** file Kopi_Tiga_Titik_Sprint_Plan_Revisi (PPL 01–PPL 42) 

**Periode proyek:** 28 Oktober – 29 November 2026 

Setiap bagian fitur di Bab 9 bisa diserahkan langsung ke PIC-nya. Keputusan final mengikuti PPL 01 revisi. Hanya hal yang memang belum diputuskan yang ditandai sebagai parameter. 

> **Acuan konsistensi:** Dokumen ini diselaraskan dengan PPL 01 revisi dan ERD 10 tabel. Jika terdapat perbedaan pada versi lama PRD, ikuti keputusan pada dokumen ini: ambang restok `stok <= 5`, Midtrans QRIS sandbox, tiga peran (`admin`, `kurir`, `pelanggan`), Moving Average 21 hari kalender dalam tiga periode 7 hari, serta struktur data sepuluh tabel sebagaimana Bab 7, termasuk `shipping_locations` untuk lokasi asal pengiriman tersimpan.

# **1. Ringkasan Produk** 

**1.1 Latar belakang.** Kopi Tiga Titik adalah usaha street coffee di Mojokerto (berdiri Juli 2026) yang menjual kopi botol 250 ml dalam 4 varian. Pesanan online masih lewat WhatsApp dan direkap manual di Excel, sehingga rawan salah catat. Ongkir dipukul rata dan pembayaran dicek manual. 

**1.2 Tujuan produk.** Aplikasi web mobile-first yang menjalankan alur: katalog → keranjang → checkout → pembayaran sandbox terverifikasi → ongkir berbasis jarak rute → daftar pesanan dan status. Aplikasi juga memberi estimasi penjualan (Moving Average) dan peringatan restok (rule-based). 

# **1.3 Indikator keberhasilan (dapat diverifikasi).** 

- Seluruh fitur wajib selesai dikembangkan paling lambat 28/11/2026 sesuai PPL 01; QE akhir dilaksanakan 29/11/2026. 

- Sprint 1–3 lulus QE tanpa bug kritis (QE 3–4/11, 11–12/11, 19–20/11). QE akhir dilaksanakan 29/11/2026 dan mencakup 12 alur. 

- Tidak ada pesanan yang diproses tanpa pembayaran berstatus `berhasil` yang telah diverifikasi backend. 

- Stok tidak pernah negatif, dan kembali tepat saat pesanan batal atau kedaluwarsa. 

- Ongkir dihitung dari jarak rute menggunakan formula tarif tetap yang telah disepakati. 

- Dokumentasi teknis dan panduan penggunaan tersedia (PPL 35). 

# **2. Ruang Lingkup** 

# **2.1 Dalam cakupan (11 fitur wajib).**

Jumlah 11 merujuk pada kelompok fitur tingkat produk dan harus sama dengan pengelompokan pada PPL 01. Kebutuhan fungsional rinci (FR) dan kebutuhan PPL (KF) dapat lebih banyak karena memecah satu fitur menjadi beberapa kewajiban. Validasi, penanganan error, integrasi, QE, dan dokumentasi merupakan pekerjaan lintas fitur, bukan pengganti fitur produk. 

1. Database dan autentikasi/akses pengguna. 

2. Pengelolaan produk, harga, stok, dan foto. 

3. Katalog produk pelanggan. 

4. Keranjang belanja. 

5. Checkout dan pembuatan pesanan. 

6. Pembayaran API sandbox dengan verifikasi backend. 

7. Ongkir berbasis jarak rute (data OpenStreetMap dan layanan routing) dengan formula tarif tetap. 

8. Daftar pesanan dan status, termasuk penugasan kurir. 

9. Moving Average penjualan 3 minggu terakhir. 

10. Rule-Based peringatan restok (stok <= 5). 

11. Pengelolaan lokasi asal pengiriman tersimpan oleh Admin/Owner. 

# **2.2 Di luar cakupan (tidak dikerjakan kecuali diminta).** 

- Scoring lima produk terlaris. 

- Fitur di luar cakupan: pencatatan transaksi penjualan offline di lapak, pengaturan jam operasional lapak, opsi ambil sendiri, notifikasi web, nota dan tombol hubungi penjual, dashboard analitik penjualan terpisah, dan ekspor Excel. **Catatan:** rekomendasi jumlah produksi berdasarkan aturan `10 − stok` termasuk cakupan F10 dan hanya berupa saran; sistem tidak menjalankan produksi atau mengubah stok otomatis. Validasi, penanganan error, integrasi, QE, dan dokumentasi tetap wajib sebagai persyaratan lintas fitur. 

- COD, notifikasi WhatsApp/email, lupa kata sandi, dan verifikasi email. 

# **3. Pengguna dan Peran**

| Peran | Deskripsi | Akses utama |
|---|---|---|
| Admin/Owner | Akun pemilik yang dikelola melalui seeder; pada implementasi awal satu akun dipakai pemilik. | Mengelola produk, melihat seluruh pesanan, mengubah status pesanan sampai Dikirim, menugaskan kurir, melihat pembayaran, Moving Average, dan peringatan stok rendah. Tidak dapat mengubah pembayaran menjadi berhasil secara manual atau membatalkan pesanan yang sudah dibayar. |
| Kurir | Pengguna terautentikasi yang ditugaskan Admin pada pesanan. | Melihat pesanan yang ditugaskan kepadanya dan menandai Selesai setelah pesanan diterima pelanggan. |
| Pelanggan | Pembeli yang wajib memiliki akun. | Melihat katalog, mengelola keranjang, checkout, membayar, melihat pesanan miliknya, dan membatalkan pesanan yang belum dibayar. |

Hak akses wajib ditegakkan di backend, bukan hanya dengan menyembunyikan menu. Guest bukan peran tersimpan di database; guest yang mengakses fitur terlindungi diarahkan ke login.

Tim: Orang 1 (PO + System Analyst), Orang 2 (Scrum Master + QA/Tester), Orang 3 dan 4 (UI/UX Designer). Semua anggota dapat mengerjakan frontend, backend, integrasi, dokumentasi, dan pengujian fitur.

# **4. Peta Fitur ke Work Package dan Jadwal** 

|**Fitur**<br>|**Work Package (PIC, tanggal)**|
|---|---|
|F1 Database dan<br>autentikasi<br> <br> <br>|PPL 01 (O1, 28/10), PPL 02 (O1, 29–30/10), PPL 03 (O1, 31/10),<br>PPL 04 (O1, 1–2/11), PPL 06 (O4, 28–30/10), PPL 07 (O4, 31/10–<br>2/11)|
|F2 Kelola produk<br>|PPL 08 (O2, 28–30/10), PPL 09 (O2, 31/10–2/11)|
|F3 Katalog<br>|PPL 10 (O3, 30–31/10), PPL 11 (O3, 1–2/11)|
|F4 Keranjang<br>|PPL 13 (O3, 5–7/11), PPL 14 (O3, 8–10/11)|
|F5 Checkout dan<br>pesanan<br> <br>|PPL 15 (O4, 5–7/11), PPL 16 (O4, 8–10/11), PPL 17 (O1, 5–6/11),<br>PPL 18 (O1, 7–8/11)|
|F6 Pembayaran<br>sandbox<br> <br>|PPL 19 (O1, 9–10/11), PPL 23 (O4, 13–16/11), PPL 24 (O4, 17–<br>18/11), PPL 40 (O4, 21–24/11), PPL 41 (O4, 25–27/11)|
|F7 Ongkir OSM<br> <br>|PPL 19, PPL 25 (O3, 13–14/11), PPL 26 (O3, 15–18/11), PPL 38<br>(O3, 21–24/11)|
|F8 Daftar pesanan dan<br>status<br>|PPL 27 (O2, 13/11), PPL 28 (O2, 14–16/11)|
|F9 Moving Average<br>|PPL 03, PPL 29 (O1, 13/11), PPL 30 (O1, 14–16/11)|
|F10 Rule-Based restok|PPL 31 (O1, 17/11), PPL 32 (O1, 18/11)|
|F11 Lokasi asal<br>pengiriman tersimpan|PPL 19 (aturan/lokasi), PPL 25–26 (integrasi lokasi dengan ongkir)|
|Lintas fitur: validasi,<br>error, integrasi, QE,<br>dokumentasi|PPL 04, PPL 12, PPL 20–22, PPL 33–37, PPL 39, PPL 41–42|
|Lintas fitur (UI<br>responsif)<br>|PPL 05 (O3, 28–29/10)|
|**Sprint Pengembangan**|**QE**|
|1<br>28/10 – 2/11|3–4/11 (PPL 12)|
|2<br>5/11 – 10/11|11–12/11 (PPL 22)|
|3<br>13/11 – 18/11|19–20/11 (PPL 33)|
|4<br>21/11 – 28/11|29/11 (PPL 42)|



# **5. Aturan Bisnis Global**

- **BR-1 Produk.** Empat varian botol 250 ml: Titik Hitam Rp8.000, Titik Temu Rp10.000, Titik Manja Rp10.000, Titik Jenuh Rp12.000. Keawetan 5–7 hari bila disimpan di lemari pendingin. Komposisi ditampilkan sebagai daftar bahan tanpa takaran.
- **BR-2 Pesanan.** Pesanan dilayani dari stok tersedia, bukan pre-order. Tidak ada batas jumlah per pelanggan selain stok. Pelanggan wajib memiliki akun.
- **BR-3 Pembayaran.** Hanya Midtrans QRIS mode sandbox. Status berhasil hanya setelah backend memverifikasi notifikasi resmi atau melakukan pemeriksaan status resmi; frontend tidak boleh menetapkan status pembayaran.
- **BR-4 Batas waktu.** Pesanan belum dibayar kedaluwarsa 10 menit sejak pesanan dibuat.
- **BR-5 Stok.** Stok ditahan/dikurangi ketika pesanan dibuat. Jika pesanan belum dibayar dibatalkan atau kedaluwarsa, stok dikembalikan tepat satu kali. Stok tidak boleh negatif; proses bersamaan harus aman.
- **BR-6 Pembatalan.** Pelanggan hanya boleh membatalkan pesanan yang belum dibayar. Pesanan yang sudah dibayar tidak dapat dibatalkan melalui fitur aplikasi; refund berada di luar cakupan.
- **BR-7 Ongkir.** Ongkir menggunakan jarak rute jalan dari layanan routing berbasis OpenStreetMap, bukan jarak garis lurus. Jika routing gagal, checkout diblokir dan ongkir tidak boleh ditebak.
- **BR-8 Wilayah dan asal pengiriman.** Pengiriman dibatasi pada Kota Mojokerto. Titik asal ongkir adalah lokasi tersimpan yang dipilih Owner dari tabel `shipping_locations` (misalnya rumah produksi atau lapak offline), bukan koordinat tetap yang di-hardcode. Koordinat setiap lokasi harus diverifikasi sebelum dipakai.
- **BR-9 Moving Average.** Gunakan 21 hari kalender sebelum tanggal prediksi, dibagi menjadi tiga periode berurutan masing-masing 7 hari relatif terhadap tanggal prediksi. Hari tanpa penjualan dihitung 0. Hasil hanya rekomendasi dan tidak mengubah stok atau produksi otomatis.
- **BR-10 Restok.** Peringatan restok muncul jika `stok <= 5` (stok 0–5). Nilai konfigurasi awal `batas_stok_minimum = 5`; stok 6 atau lebih tidak memicu peringatan.
- **BR-11 Teknis.** Harga dan nilai uang memakai Rupiah; waktu menggunakan zona Asia/Jakarta. Detail teknis internal tidak ditampilkan kepada pengguna.
- **BR-12 Riwayat penjualan.** `sales_history` direkap berdasarkan `product_id`, `tanggal`, dan `sumber` (`transaksi` atau `simulasi`). Kombinasi ketiganya unik dan nilai dengan kombinasi sama diakumulasikan. Moving Average hanya menggunakan baris `sumber=transaksi` dari penjualan online setelah pembayaran terverifikasi; pesanan belum dibayar atau dibatalkan tidak dihitung. Data simulasi hanya untuk pengujian, harus diberi label `simulasi`, dan tidak boleh masuk perhitungan Moving Average produksi.
- **BR-13 Produk nonaktif.** Produk nonaktif tidak ditampilkan di katalog dan tidak dapat di-checkout. Jika sudah ada di keranjang, item boleh tetap tersimpan tetapi checkout ditolak sampai produk aktif dan stok valid.
- **BR-14 Snapshot.** Nama produk, harga satuan, jumlah, subtotal, ongkir, total, dan alamat penerima disimpan sebagai snapshot pada checkout.
- **BR-15 Riwayat status.** Setiap perubahan status pesanan dicatat di `order_status_history` dalam transaksi database yang sama dengan perubahan status. Aktor boleh NULL untuk proses otomatis.
- **BR-16 Pembayaran terlambat.** Pembayaran yang dikonfirmasi setelah pesanan kedaluwarsa dicatat dan ditandai untuk pemeriksaan Admin/Owner; pesanan tidak diaktifkan kembali otomatis.
- **BR-17 Konfigurasi.** Seeder membuat nilai awal secara idempoten, termasuk `batas_stok_minimum = 5` dan `stok_harian_standar = 10`. Tarif ongkir mengikuti formula tetap pada BR-7 dan tidak dapat diubah melalui pengaturan. Lokasi awal dapat dimasukkan sebagai data lokasi tersimpan; `settings.lokasi_pengiriman_aktif_id` menunjuk lokasi aktif terpilih setelah ID lokasi tersedia.

# **6. Arsitektur dan Konvensi Teknis** 

**6.1 Stack.** Laravel (backend, Blade/views), Tailwind CSS (UI mobile-first), Leaflet + OpenStreetMap (peta), layanan routing berbasis OSM (dipilih di PPL 19), satu penyedia pembayaran sandbox: Midtrans QRIS dalam mode sandbox. 

**6.2 Struktur kode (saran).** app/Services/: StockService, CartService, OrderService, PaymentService, ShippingService, ForecastService, RestockRule. app/Http/Requests/ untuk validasi. Middleware role. Console command orders:expire. 

**6.3 Konfigurasi.** Kunci API dan kredensial hanya di .env (masuk .gitignore, tidak dicommit). Parameter bisnis disimpan di tabel settings (diisi seeder), bukan hardcode. 

# **6.4 Route (saran).** 

- Auth: GET/POST /register, GET/POST /login, POST /logout. 

- Katalog: GET /products, GET /products/{id}. 

- Keranjang: GET /cart, POST /cart/items, PATCH /cart/items/{id}, DELETE /cart/items/{id}. 

- Checkout: GET /checkout, POST /checkout, POST /api/shipping/quote. 

- Pembayaran: GET /orders/{id}/pay, POST /orders/{id}/payments, GET /api/orders/{id}/payment-status, POST /api/payments/webhook (dikecualikan dari CSRF, wajib diverifikasi). 

- Pelanggan: POST /orders/{id}/cancel (hanya saat belum dibayar). 

- Admin: resource /admin/products, PATCH /admin/products/{id}/stock, GET /admin/orders, GET /admin/orders/{id}, PATCH /admin/orders/{id}/status, GET /admin/forecast, GET /admin/restock-alerts.
- Admin lokasi asal: GET/POST /api/admin/shipping-locations, PUT /api/admin/shipping-locations/{id}, PATCH /api/admin/shipping-locations/{id}/status, PATCH /api/admin/shipping-locations/{id}/select. Samakan prefix dan metode dengan kontrak PPL 01 saat implementasi. 

# **6.5 Konvensi Git dan ID.** 

- ID Work Package di dokumen: PPL 01, PPL 02, dst. 

- Branch: feature/PPL-01-nama-singkat. Commit: [PPL-01] pesan. Judul PR: [PPL-01] judul. Spasi pada ID diganti tanda hubung agar valid di Git. 

- Perubahan masuk main hanya lewat pull request yang direview minimal satu anggota lain. 

**6.6 Standar UI.** Mobile-first (1 kolom di HP, grid di desktop). Komponen konsisten dari UI Kit (PPL 05). Setiap form menampilkan pesan error per field. Format uang Rp12.000. 

**6.7 Konvensi Pemetaan Model Laravel ke Tabel Database**

Nama tabel pada database mengikuti struktur yang ditetapkan dalam migration dan ERD PPL 02. Nama tabel tidak boleh diubah hanya untuk mengikuti konvensi penamaan otomatis Laravel.

Apabila nama tabel tidak sesuai dengan konvensi pluralisasi Laravel, model terkait wajib menetapkan properti `$table` secara eksplisit.

Pemetaan khusus yang digunakan:

| Model Laravel | Nama tabel database |
|---|---|
| `OrderStatusHistory` | `order_status_history` |
| `SalesHistory` | `sales_history` |

Contoh implementasi:

```php
protected $table = 'sales_history';
```

Aturan ini hanya mengatur pemetaan model ke tabel database. Perubahan tersebut tidak mengubah struktur tabel, nama kolom, relasi bisnis, kontrak API, maupun antarmuka pengguna.

Setiap perubahan pemetaan model harus diuji menggunakan fitur atau seeder yang memanfaatkan model terkait. Pengembang juga wajib memeriksa migration sebelum menetapkan nama tabel pada model.


# **7. Model Data**

Model data PRD harus mengikuti ERD PPL 02 yang terdiri dari **10 tabel**. Sepuluh tabelnya adalah `users`, `products`, `cart_items`, `orders`, `order_items`, `payments`, `order_status_history`, `sales_history`, `settings`, dan `shipping_locations`. Jangan menambahkan tabel `stock_movements`, `payment_events`, `simulated_sales`, atau `carts` tambahan. Data simulasi tetap berada di `sales_history` dengan `sumber='simulasi'`; jangan menyimpan `payments.status` terpisah pada `orders`.

- **`users`**: `id`, `name`, `email` (unik), `password`, `role` (`admin`, `kurir`, `pelanggan`), serta kolom bawaan Laravel yang digunakan migrasi. Kata sandi disimpan dalam bentuk hash. Role divalidasi backend.
- **`products`**: `id`, `nama`, `rasa`, `komposisi`, `deskripsi`, `keawetan`, `harga`, `stok`, `foto_path`, `aktif`. Harga dan stok tidak boleh negatif. Produk yang pernah digunakan dalam transaksi dinonaktifkan, bukan dihapus permanen.
- **`cart_items`**: `id`, `user_id`, `product_id`, `jumlah`. Kombinasi `user_id` dan `product_id` unik; `jumlah > 0`. Keranjang tidak menahan stok.
- **`orders`**: `id`, `kode` (unik), `user_id`, `courier_id` (nullable sebelum penugasan), `status`, `subtotal`, `ongkir`, `total`, `nama_penerima`, `telepon`, `alamat`, `lat`, `lng`, `catatan`, `stok_dikembalikan`, `created_at`. Snapshot data pesanan dipertahankan.
- **`order_items`**: `id`, `order_id`, `product_id`, `nama_produk`, `rasa` (nullable), `jumlah`, `harga_satuan`, `subtotal`. Nama, rasa, harga satuan, dan subtotal merupakan snapshot saat checkout. Aturan unik mengikuti migrasi PPL 02; jangan mengasumsikan pasangan produk dalam satu pesanan unik jika constraint tersebut tidak dibuat.
- **`payments`**: `id`, `order_id` (unik), `referensi_provider`, `status`, `jumlah`, `kedaluwarsa_pada`, `waktu_verifikasi`, `payload_mentah` (JSON). Satu pesanan memiliki maksimal satu baris pembayaran; retry memperbarui baris tersebut. Tidak ada kolom `provider` karena penyedia final adalah Midtrans QRIS.
- **`order_status_history`**: `id`, `order_id`, `status_sebelumnya` (nullable), `status_baru`, `user_id` (nullable untuk proses otomatis), `keterangan` (nullable), `created_at`, `updated_at`. Waktu perubahan dicatat melalui timestamp `created_at`; semua perubahan status wajib dicatat.
- **`sales_history`**: `id`, `product_id`, `tanggal`, `jumlah_terjual`, `sumber` (`transaksi` atau `simulasi`). Kombinasi `product_id`, `tanggal`, `sumber` unik; data dengan kombinasi sama diakumulasikan.
- **`settings`**: `id`, `key` (unik), `value` (nullable), `description` (nullable), serta timestamps sesuai migrasi. Memuat antara lain `batas_stok_minimum=5`, `stok_harian_standar=10`, dan `lokasi_pengiriman_aktif_id` yang nilainya berupa ID lokasi terpilih. Tidak menyimpan tarif ongkir karena tarif bersifat tetap. Backend wajib memvalidasi ID dan status aktif lokasi sebelum digunakan.
- **`shipping_locations`**: `id`, `nama_lokasi`, `latitude`, `longitude`, `alamat`, `aktif`, `created_at`, `updated_at`. Menyimpan beberapa lokasi asal milik Owner, misalnya rumah produksi dan lapak offline. Lokasi yang tidak tersedia dinonaktifkan, bukan dihapus permanen. Hanya lokasi aktif yang dapat dipilih sebagai asal ongkir.

Semua relasi mengikuti ERD: keranjang ke pengguna dan produk; pesanan ke pelanggan dan kurir; detail pesanan ke pesanan dan produk; pembayaran satu-ke-satu opsional dengan pesanan; riwayat status ke pesanan dan aktor; riwayat penjualan ke produk. `settings.lokasi_pengiriman_aktif_id` merupakan referensi logis ke `shipping_locations.id`, bukan foreign key database karena `settings.nilai` dipakai untuk beragam konfigurasi dan bertipe teks. Backend wajib memastikan ID menunjuk lokasi yang ada dan aktif. Operasi checkout, pembayaran, perubahan status, dan pengembalian stok harus atomik serta idempoten sesuai kebutuhan.

# **8. Status dan Transisi** 

- **Status pesanan:** `menunggu_pembayaran`, `diproses`, `dikirim`, `selesai`, `dibatalkan`. Pesanan yang melewati batas waktu pembayaran berubah menjadi `dibatalkan`; status pembayaran menjadi `kedaluwarsa`. 

- **Status pembayaran:** `pending`, `berhasil`, `gagal`, `kedaluwarsa` (label UI: Menunggu, Berhasil, Gagal, Kedaluwarsa). 

|**Dari**<br>**Ke**|**Pemicu**|**Pelaku**|
|---|---|---|
|(baru)<br>menunggu_pembayaran|<sup>Checkout selesai,</sup><br>stok berkurang|<br>Pelanggan|
||Pembayaran||
|menunggu_pembayaran → diproses|Pembayaran `berhasil` setelah verifikasi backend|Sistem (webhook)|
|menunggu_pembayaran dibatalkan|Dibatalkan pelanggan|Pelanggan|



|menunggu_pembayaran|kedaluwarsa|Batas 10 menit terlewati; stok dikembalikan satu kali|Sistem|
|diproses|dikirim|Admin mengirim<br>pesanan|Admin|
|dikirim|selesai|Pesanan diterima pelanggan|Kurir yang ditugaskan|



Transisi lain ditolak backend dengan error 409/422 (misalnya membatalkan pesanan yang sudah dibayar). Pembayaran gagal tidak membatalkan pesanan; pelanggan dapat membuat percobaan pembayaran baru selama pesanan belum kedaluwarsa. 

# **9. PRD per Fitur** 

# **F1. Database dan Autentikasi/Akses** 

**Tujuan:** fondasi data terpadu dan akses sesuai peran. **WP:** PPL 01–04, 06, 07 (Dep PPL 07: PPL 02, 06). 

**Alur:** pelanggan mendaftar, login, lalu diarahkan ke katalog. Admin login, lalu diarahkan ke /admin/orders. Guest yang membuka checkout diarahkan ke login dan kembali ke halaman semula setelah masuk. 

# **Kebutuhan fungsional:** 

- FR-1.1 Skema database sesuai Bab 7 dibuat lewat migrasi Laravel dengan foreign key dan index (PPL 02). 

- FR-1.2 Seeder (PPL 03): akun admin (kata sandi dari .env), 4 varian produk beserta bahan dan keawetan, stok awal sesuai data seeder yang disepakati, data simulasi penjualan untuk pengujian 21 hari kalender per varian yang disimpan di `sales_history` dengan `sumber='simulasi'`, serta nilai awal settings. Data simulasi tidak digunakan dalam perhitungan Moving Average produksi. 

- FR-1.3 Registrasi pelanggan: nama, email, kata sandi, konfirmasi kata sandi. Role otomatis pelanggan. 

- FR-1.4 Login dan logout dengan email dan kata sandi. Percobaan login dibatasi (throttle). 

- FR-1.5 Hak akses berbasis peran lewat middleware. Pelanggan ke /admin/* mendapat 403. Guest ke halaman terproteksi diarahkan ke login. 

- FR-1.6 Kata sandi di-hash dan semua form memakai CSRF. 

**Validasi:** nama wajib dan ≤ 255 karakter; email wajib, valid, dan unik; kata sandi minimal 8 karakter (usulan) dan sama dengan konfirmasi. Pesan login gagal tidak membocorkan apakah email terdaftar (“Email atau kata sandi salah”). 

# **Kriteria penerimaan:** 

- Migrasi dan seeder berjalan di database kosong tanpa error. 

- Pendaftaran email ganda ditolak dengan pesan jelas. 

- Pelanggan tidak dapat membuka halaman admin; admin dapat. 

- Tidak ada kata sandi tersimpan sebagai teks biasa. 

**Uji QE:** daftar valid/tidak valid, login benar/salah, akses lintas peran, relasi data (hapus produk yang sudah punya order_items ditolak). 

**Catatan:** lupa kata sandi dan verifikasi email di luar cakupan. 

# **F2. Pengelolaan Produk, Harga, Stok, Foto** 

**Tujuan:** admin mengelola katalog dan stok tanpa Excel. **WP:** PPL 08 (UI), PPL 09 (backend; Dep PPL 02, 08). 

# **Kebutuhan fungsional:** 

- FR-2.1 Daftar produk admin: foto kecil, nama, harga, stok, status aktif, penanda stok menipis (dari F10). 

- FR-2.2 Form tambah/ubah: nama, harga, stok, foto, deskripsi singkat (termasuk keawetan), daftar bahan. 

- FR-2.3 Unggah foto: jpg/jpeg/png/webp, maksimal 2 MB (usulan), nama file diacak, disimpan di storage publik (storage:link). Ganti foto menghapus file lama. 

- FR-2.4 Ubah stok dengan mengisi nilai baru. Perubahan stok tidak boleh menghasilkan nilai negatif dan tidak menambah tabel di luar ERD 10 tabel. 

- FR-2.5 Aksi “hapus” produk dilakukan dengan menonaktifkan `products.aktif=false`, bukan menghapus fisik. Tampil dialog konfirmasi. 

- FR-2.6 Semua halaman hanya untuk admin. 

**Validasi:** nama wajib dan unik; harga integer > 0; stok integer ≥ 0; foto sesuai tipe dan ukuran. 

**Kriteria penerimaan:** produk baru langsung tampil di katalog; harga dan stok yang diubah langsung berlaku; stok negatif ditolak; foto tidak valid ditolak dengan pesan jelas. 

**Uji QE:** CRUD lengkap, unggah foto valid/tidak valid, hapus produk yang pernah dipesan. 

# **F3. Katalog Produk Pelanggan** 

**WP:** PPL 10 (UI; Dep PPL 05), PPL 11 (backend; Dep PPL 02, 03). 

# **Kebutuhan fungsional:** 

- FR-3.1 Katalog dapat dilihat tanpa login. Hanya produk aktif tampil. 

- FR-3.2 Kartu produk: foto, nama, harga, status stok (tersedia atau “Stok Habis”). 

- FR-3.3 Detail produk: deskripsi (termasuk keawetan), daftar bahan, stok, input jumlah, tombol tambah ke keranjang. 

- FR-3.4 Stok dibaca dari database pada setiap tampil, tidak di-cache. Jika stok 0, tombol pesan nonaktif. 

- FR-3.5 Tampilan mobile-first. 

**Kriteria penerimaan:** perubahan stok di F2 langsung terlihat; produk nonaktif tidak muncul; tombol nonaktif saat stok 0; guest yang menekan tambah ke keranjang diarahkan login. 

**Uji QE:** tampil di lebar HP dan desktop, stok 0, produk tanpa foto (placeholder). 

# **F4. Keranjang Belanja** 

**WP:** PPL 13 (backend; Dep PPL 09, 11), PPL 14 (UI; Dep PPL 10, 13). 

# **Kebutuhan fungsional:** 

- FR-4.1 Keranjang tersimpan di database per pengguna (unik per produk). 

- FR-4.2 Tambah item, ubah jumlah, hapus item. 

- FR-4.3 Subtotal per item dan total sementara dihitung ulang otomatis. 

- FR-4.4 Validasi jumlah: integer ≥ 1 dan ≤ stok saat ini. Jika stok berubah atau produk nonaktif, tampil pesan dan jumlah disesuaikan. 

- FR-4.5 Keranjang tidak mereservasi stok. Stok baru berkurang saat pesanan dibuat (F5). 

- FR-4.6 Menambah produk yang sudah ada di keranjang menjumlahkan jumlahnya. 

**Kriteria penerimaan:** jumlah melebihi stok ditolak; total sesuai harga terbaru; keranjang tetap ada setelah logout dan login. 

**Uji QE:** batas stok, produk yang menjadi nonaktif saat di keranjang, dua perangkat berbeda. 

# **F5. Checkout dan Pembuatan Pesanan** 

**WP:** PPL 15 (UI; Dep PPL 05), PPL 16 (backend; Dep PPL 13, 17), PPL 17 (skema pesanan; Dep PPL 02), PPL 18 (stok transaksi; Dep PPL 09, 17). 

**Alur:** keranjang → checkout → isi data penerima dan pilih titik di peta → lihat ringkasan dan ongkir (F7) → buat pesanan → halaman pembayaran (F6). 

# **Kebutuhan fungsional:** 

- FR-5.1 Form checkout: nama penerima, nomor kontak, detail alamat, catatan khusus (opsional), titik lokasi di peta Leaflet (geser pin atau “gunakan lokasi saya”). Alamat lengkap diisi otomatis dari titik, bisa diperiksa pelanggan. 

- FR-5.2 Ringkasan pesanan: item, subtotal, ongkir, total. Peringatan tampil jelas: “Jika pesanan sudah dibayar, tidak dapat dibatalkan atau diubah.” 

- FR-5.3 Pembuatan pesanan dalam satu transaksi database: kunci baris produk (lockForUpdate), cek stok, kurangi stok, catat status reservasi stok melalui transaksi pesanan dan penanda `stok_dikembalikan`, buat orders dan order_items (snapshot harga dan nama), tetapkan batas pembayaran 10 menit sejak `orders.created_at`; simpan waktu kedaluwarsa pada `payments.kedaluwarsa_pada` saat data pembayaran dibuat, kosongkan keranjang. Jika salah satu gagal, seluruh transaksi di-rollback. 

- FR-5.4 Stok tidak cukup: pesanan ditolak dan item bermasalah disebutkan. 

- FR-5.5 Nomor pesanan unik (usulan format KTT-YYYYMMDD-NNNN). 

- FR-5.6 Mencegah submit ganda (tombol dinonaktifkan dan token idempoten). 

- FR-5.7 Pembatalan oleh pelanggan sebelum dibayar mengembalikan stok satu kali (BR-5, BR-6). 

- FR-5.8 Selama F7 belum terintegrasi, ongkir dapat memakai nilai 0 sebagai placeholder khusus lingkungan pengembangan; sebelum integrasi dan QE akhir, checkout wajib memakai hasil perhitungan ongkir yang valid. Total = subtotal + ongkir. 

**Validasi:** nama wajib; nomor kontak wajib dengan format telepon Indonesia; detail alamat wajib; titik lokasi wajib (lat/lng valid, Bab F7); keranjang tidak kosong. **Kriteria penerimaan:** stok berkurang tepat sesuai jumlah; dua pelanggan memesan botol terakhir bersamaan hanya satu yang berhasil; total sama dengan penjumlahan item (+ ongkir); kegagalan tengah proses tidak meninggalkan stok yang terpotong. **Uji QE:** pesanan normal, stok tidak cukup, dua sesi paralel, rollback saat error, batas waktu pembayaran. 

# **F6. Pembayaran API Sandbox dengan Verifikasi Backend** 

**WP:** PPL 19 (kontrak integrasi, Orang 1; Dep PPL 17), PPL 23 (backend; Dep PPL 16, 18, 19), PPL 24 (UI; Dep PPL 23), PPL 40–41 (uji ulang dan keamanan). 

**Alur:** pesanan dibuat → halaman bayar membuat transaksi di penyedia → pelanggan membayar → penyedia memanggil webhook → backend memverifikasi → status pembayaran berhasil dan pesanan berstatus diproses. 

# **Kebutuhan fungsional:** 

- FR-6.1 PPL 19 menetapkan satu penyedia, parameter, kontrak endpoint, format webhook, dan mekanisme verifikasi. Hasilnya didokumentasikan. 

- FR-6.2 Transaksi pembayaran dibuat dari server (kunci penyedia hanya di .env) dengan nominal = orders.total dan waktu kedaluwarsa = `payments.kedaluwarsa_pada`, yaitu 10 menit sejak `orders.created_at`. 

- FR-6.3 Halaman pembayaran menampilkan instruksi atau kode pembayaran, nominal, hitung mundur, dan status (Pending/Gagal/Kedaluwarsa/Berhasil). Status diperbarui lewat polling ke /api/orders/{id}/payment-status. 

- FR-6.4 Endpoint webhook memverifikasi keaslian notifikasi sesuai mekanisme penyedia (tanda tangan/token) dan/atau memeriksa status transaksi langsung ke API penyedia dari server. Notifikasi yang tidak lolos ditolak dan dicatat. 

- FR-6.5 Status pembayaran menjadi berhasil hanya jika: notifikasi terverifikasi, order_number cocok, nominal sama dengan orders.total, dan pesanan masih menunggu_pembayaran. Redirect atau data dari browser tidak pernah dipercaya. 

- FR-6.6 Pemrosesan webhook idempoten: notifikasi duplikat tidak mengubah data atau stok dua kali. Idempotensi dijamin melalui pemeriksaan status pembayaran/pesanan dan pembaruan atomik menggunakan tabel yang sudah ada; tidak menambahkan tabel atau kolom khusus untuk event webhook. 

- FR-6.7 Pembayaran gagal: pesanan tetap menunggu_pembayaran, pelanggan dapat membuat percobaan baru selama belum kedaluwarsa. 

- FR-6.8 Kedaluwarsa: command `orders:expire` (dijadwalkan tiap menit) dan pengecekan saat halaman dibuka mengubah status pesanan menjadi `dibatalkan`, status pembayaran menjadi `kedaluwarsa`, serta mengembalikan stok satu kali. Batas waktu dihitung 10 menit sejak `orders.created_at`; waktu kedaluwarsa disimpan di `payments.kedaluwarsa_pada`. 

- FR-6.9 Notifikasi sukses yang datang setelah pesanan kedaluwarsa tidak mengaktifkan kembali pesanan. Kejadian dicatat dan ditandai di detail admin untuk penanganan manual di luar aplikasi. 

- FR-6.10 Perubahan pembayaran dan pesanan dilakukan dalam satu transaksi database. 

**Catatan implementasi:** penyedia sandbox butuh URL webhook yang dapat dijangkau publik. Saat pengembangan lokal pakai tunnel publik (misalnya ngrok atau Cloudflare Tunnel). 

**Kriteria penerimaan:** simulasi pembayaran berhasil di sandbox mengubah status pembayaran menjadi `berhasil` dan pesanan menjadi `diproses`; webhook palsu (tanpa tanda tangan atau 

nominal salah) tidak mengubah apa pun; notifikasi ganda tidak menimbulkan efek ganda; pesanan kedaluwarsa mengembalikan stok. **Uji QE (PPL 40):** berhasil, pending, gagal, kedaluwarsa, webhook ganda, webhook palsu, nominal tidak cocok, notifikasi terlambat. 

# **F7. Ongkir Berbasis Jarak Rute (OpenStreetMap)** 

**WP:** PPL 19 (parameter tarif dan kebutuhan routing), PPL 25 (UI; Dep PPL 15, 19), PPL 26 (backend; Dep PPL 16, 19), PPL 38 (perbaikan dan uji ulang). Pengelolaan data lokasi asal dijabarkan sebagai F11. 

# **Kebutuhan fungsional:**

- FR-7.1 Pelanggan memilih titik tujuan di peta (PPL 25). Rincian ongkir tampil di checkout setelah titik dipilih. 

- FR-7.2 Endpoint /api/shipping/quote menerima lat dan lng tujuan, mengambil jarak rute dari layanan routing berbasis OSM (asal = koordinat lokasi aktif yang ID-nya tersimpan pada `settings` dengan key `lokasi_pengiriman_aktif_id`), lalu menghitung ongkir. 

- FR-7.3 Formula: 

   - Gunakan jarak rute jalan dalam kilometer tanpa membulatkan ke kilometer terdekat sebelum menentukan tingkat tarif. 

   - Jika d ≤ 5,5 km → ongkir = Rp0. 

   - Jika d > 5,5 km → ongkir = Rp2.000 × (floor(d − 5,5) + 1). 

- FR-7.4 Tarif menggunakan formula tetap pada FR-7.3. Tidak tersedia halaman atau parameter untuk mengubah nominal tarif. Perubahan formula hanya dapat dilakukan melalui revisi kebutuhan dan kode yang disetujui PO. 

- FR-7.5 Validasi koordinat: lat dan lng numerik, lat −90..90, lng −180..180, tidak kosong. Titik harus berada di wilayah layanan Kota Mojokerto, diperiksa lewat reverse geocoding (patuhi kebijakan pemakaian Nominatim: jeda antar-permintaan dan User-Agent). Jika wilayah tidak dapat diverifikasi, pesanan ditolak dengan pesan jelas. 

- FR-7.6 Penanganan error: jika layanan routing gagal, timeout, atau tidak menemukan rute, tampil pesan yang jelas dengan tombol coba lagi. Checkout tidak boleh lanjut dengan ongkir tidak valid. Tidak ada ongkir tebakan atau jarak garis lurus sebagai pengganti. Kegagalan dicatat di log. Jika belum ada lokasi asal terpilih yang valid dan aktif, quote dan checkout juga diblokir. Pemilihan dan pengelolaan lokasi dilakukan melalui F11. 

- FR-7.7 Nilai ongkir disimpan pada kolom `orders.ongkir` dan dikunci saat pesanan dibuat. Jarak rute digunakan untuk menghitung tarif, tetapi tidak disimpan pada kolom `orders` karena kolom `distance_km` tidak terdapat dalam ERD. Backend menghitung ulang ongkir saat checkout dan tidak mempercayai nilai dari browser. 

**Ketentuan final:** formula tarif bersifat tetap: jarak rute `d ≤ 5,5 km` gratis; untuk `d > 5,5 km`, ongkir = Rp2.000 × (`floor(d − 5,5) + 1`). Nominal dan ambang tarif bukan pengaturan yang dapat diubah Owner melalui aplikasi. Koordinat asal diambil dari lokasi tersimpan yang dipilih Owner. Lokasi uji harus diberi label sebagai data uji dan tidak dianggap lokasi produksi final. **Kriteria penerimaan:** hasil ongkir sesuai formula tetap; layanan routing yang dimatikan menghasilkan pesan error, bukan crash; titik di luar Kota Mojokerto ditolak. 

**Uji QE:** jarak di bawah, tepat di, dan di atas ambang gratis; titik tidak valid; wilayah luar Mojokerto; layanan routing down. 

# **F8. Daftar Pesanan dan Status (Admin)** 

**WP:** PPL 27 (UI; Dep PPL 17), PPL 28 (backend; Dep PPL 16, 17). 

# **Kebutuhan fungsional:** 

- FR-8.1 Daftar pesanan terbaru di atas, dengan filter status (Semua, Menunggu Pembayaran, Diproses, Dikirim, Selesai, Dibatalkan) dan paginasi. 

- FR-8.2 Detail pesanan: nomor, waktu, item (nama, harga, jumlah), subtotal, ongkir, total, data penerima, alamat, titik peta (tautan ke peta), catatan, status pesanan, serta status dan waktu verifikasi pembayaran jika tersedia. Waktu verifikasi diambil dari `payments.waktu_verifikasi`; tidak menggunakan kolom `paid_at` karena tidak ada pada ERD. 

- FR-8.3 Admin mengubah status hanya sesuai Bab 8 (diproses → dikirim → selesai). Transisi lain ditolak dengan pesan jelas. 

- FR-8.4 Status pembayaran hanya tampil (read-only). Hanya sistem yang mengubahnya lewat F6. 

- FR-8.5 Setiap perubahan status dicatat pada `order_status_history` dengan status sebelumnya, status baru, waktu perubahan, aktor, dan catatan; tidak menggunakan tabel `status_logs`. 

- FR-8.6 Halaman hanya untuk admin. Pelanggan melihat status pesanannya sendiri pada halaman pembayaran/hasil pesanan (PPL 24). Halaman riwayat pelanggan tidak dijadwalkan. 

**Kriteria penerimaan:** pesanan baru dari checkout langsung muncul; filter benar; transisi tidak valid ditolak; pesanan sudah dibayar tidak bisa dibatalkan. **Uji QE:** semua filter, semua transisi valid dan tidak valid, akses non-admin. 

# **F9. Moving Average 3 Minggu** 

**WP:** PPL 03 (data simulasi), PPL 29 (UI; Dep PPL 03), PPL 30 (backend; Dep PPL 03, 17). 

**Rumus:** estimasi minggu berikutnya varian v = (P₁ + P₂ + P₃) / 3. P₁–P₃ adalah total penjualan pada tiga periode berurutan, masing-masing 7 hari, dalam 21 hari kalender sebelum tanggal prediksi. Periode dihitung mundur dari tanggal prediksi, bukan mengikuti minggu kalender. 

# **Kebutuhan fungsional:** 

- FR-9.1 Sumber perhitungan: hanya penjualan nyata (`sumber=transaksi`) yang pembayarannya telah terverifikasi. Rekap dilakukan melalui `sales_history` berdasarkan produk, tanggal, dan sumber; hari tanpa penjualan dihitung 0. Baris `sumber=simulasi` hanya digunakan untuk pengujian dan dikecualikan dari perhitungan Moving Average produksi. 

- FR-9.2 Halaman admin /admin/forecast menampilkan per varian: penjualan nyata pada tiga periode 7 hari terakhir, estimasi minggu berikutnya (satu desimal), kebutuhan stok yang disarankan (dibulatkan ke atas), serta keterangan bahwa perhitungan menggunakan transaksi terverifikasi. 

- FR-9.3 Jika histori transaksi nyata belum mencakup 21 hari kalender penuh, tampil “Data transaksi belum cukup” dan jangan mengganti kekurangan data dengan data simulasi. Setelah periode 21 hari tersedia, hari tanpa penjualan nyata dihitung 0. Seeder boleh menyediakan data simulasi untuk menguji tampilan dan rumus pada lingkungan pengujian, tetapi data tersebut tidak boleh dipakai oleh perhitungan produksi. 

- FR-9.4 Estimasi hanya saran. Tidak mengubah stok maupun target. 

- FR-9.5 Rumus dan contoh perhitungan didokumentasikan (PPL 30, PPL 35). 

**Contoh uji (ilustrasi):** Titik Temu 60, 70, 80 botol → (60 + 70 + 80) / 3 = 70. **Kriteria penerimaan:** hasil sesuai hitungan manual untuk data transaksi nyata uji; data simulasi tidak memengaruhi hasil produksi; kasus kurang dari 3 minggu transaksi nyata menampilkan pesan. 

**Uji QE:** data transaksi nyata dengan hasil diketahui, batas pergantian periode 7 hari, varian tanpa penjualan (hasil 0), histori transaksi nyata kurang dari 21 hari, serta verifikasi bahwa data simulasi tidak ikut dihitung. 

# **F10. Rule-Based Peringatan Restok** 

**WP:** PPL 31 (UI; Dep PPL 09), PPL 32 (backend; Dep PPL 09, 18). 

**Aturan:** stok `<= batas_stok_minimum` (default 5) → status “Stok Menipis/Segera Restok”. Stok 0 sampai 5 memicu peringatan; stok 6 atau lebih tidak. Stok 0 juga ditampilkan sebagai “Habis” (tombol pesan di katalog nonaktif, F3). Sistem juga menampilkan saran jumlah produksi `max(0, 10 − stok)` dengan standar 10 botol per varian (nilai awal `settings.stok_harian_standar = 10`). Saran hanya informasi bagi Owner; tidak membuat produksi, pesanan, atau perubahan stok otomatis. 

# **Kebutuhan fungsional:** 

- FR-10.1 Aturan dievaluasi dari stok terkini setiap halaman dibuka (tanpa tabel notifikasi). 

- FR-10.2 UI: lencana di daftar produk admin dan panel “Peringatan Restok” berisi produk dengan stok `<= batas_stok_minimum`, beserta nama dan stok saat ini. Tampilkan pula saran jumlah produksi `max(0, 10 − stok)`; Owner tetap menentukan jumlah produksi sebenarnya. 

- FR-10.3 Ambang peringatan dan standar stok harian dibaca dari `settings`. 

- FR-10.4 Peringatan hilang otomatis setelah stok dinaikkan menjadi lebih dari 5 (minimal 6). Saran produksi menjadi 0 apabila stok sama dengan atau melebihi standar stok harian. 

**Kriteria penerimaan:** stok 4 dan 5 memicu peringatan; stok 6 tidak; stok 0 menampilkan status habis sekaligus peringatan stok rendah; pengurangan stok oleh pesanan memicu peringatan di tampilan berikutnya. **Uji QE (PPL 32):** nilai stok 0, 4, 5, dan 6; perubahan ambang di settings. 

# **Persyaratan Lintas Fitur: Validasi, Penanganan Error, Integrasi, Pengujian, Dokumentasi** 

**WP:** PPL 04, PPL 12, PPL 20–22, PPL 33–37, PPL 39, PPL 41–42. Pekerjaan ini melekat pada seluruh fitur F1–F11 dan tidak dihitung sebagai kelompok fitur produk tersendiri. 

# **Kebutuhan fungsional:** 

- FR-X.1 Semua input divalidasi di backend (Form Request). Pesan error berbahasa Indonesia, per field, dan tidak menghapus data yang sudah diisi. 

- FR-X.2 Penanganan error global: halaman 403, 404, 419, 500 yang rapi (PPL 20). Respons JSON untuk endpoint /api/* dengan kode status dan pesan yang konsisten. Detail teknis (stack trace) tidak tampil ke pengguna (APP_DEBUG=false di produksi). 

- FR-X.3 Error layanan eksternal (pembayaran, routing, geocoding) ditangkap, dicatat di log, dan ditampilkan sebagai pesan ramah. 

- FR-X.4 Setiap operasi multi-tabel (checkout, pembayaran, pembatalan) memakai transaksi database. 

- FR-X.5 Integrasi alur beli end-to-end dari katalog sampai stok diuji dan diperbaiki (PPL 34). 

- FR-X.6 Dokumentasi teknis dan panduan penggunaan (PPL 35): setup (README: env, migrasi, seeder, menjalankan scheduler dan tunnel), endpoint dan contoh payload, rumus Moving Average, aturan restok, parameter tarif ongkir, serta panduan Pelanggan dan Admin. 

- FR-X.7 Setiap fitur memiliki dokumentasi singkat dan hasil pengujian yang dicatat (DoD). 

**Kriteria penerimaan:** tidak ada error 500 tanpa penanganan pada skenario QE; semua pesan validasi tampil per field; dokumentasi memungkinkan orang lain menjalankan sistem dari nol. 

# **F11. Pengelolaan Lokasi Asal Pengiriman**

**WP:** PPL 19 (aturan dan kebutuhan lokasi asal), PPL 25–26 (integrasi lokasi asal dengan perhitungan ongkir).

**Tujuan:** Admin/Owner dapat menyimpan beberapa titik asal pengiriman, misalnya rumah produksi dan lapak, lalu memilih satu lokasi aktif yang digunakan F7 untuk menghitung jarak rute.

**Kebutuhan fungsional:**

- FR-11.1 Admin/Owner dapat menambah lokasi dengan nama, alamat, dan koordinat peta. Data disimpan di `shipping_locations`.
- FR-11.2 Admin/Owner dapat melihat daftar lokasi tersimpan dan mengubah nama, alamat, serta koordinatnya.
- FR-11.3 Admin/Owner dapat mengaktifkan atau menonaktifkan lokasi. Lokasi yang tidak digunakan dinonaktifkan, bukan dihapus permanen.
- FR-11.4 Admin/Owner dapat memilih satu lokasi aktif sebagai asal pengiriman. ID lokasi terpilih disimpan pada `settings` dengan `key = lokasi_pengiriman_aktif_id`.
- FR-11.5 Backend wajib memastikan lokasi terpilih benar-benar ada dan `aktif = true`. Jika lokasi terpilih akan dinonaktifkan, Owner harus memilih lokasi aktif lain terlebih dahulu. Jika tidak ada lokasi asal yang valid dan aktif, quote ongkir dan checkout diblokir.
- FR-11.6 Endpoint admin: `GET/POST /admin/shipping-locations`, `PUT/PATCH /admin/shipping-locations/{id}` untuk ubah data/status, dan `PATCH /admin/shipping-locations/{id}/select` untuk memilih lokasi aktif. Semua endpoint hanya untuk Admin/Owner. Jika implementasi memakai endpoint JSON, gunakan prefix `/api/admin/...` secara konsisten dengan kontrak API PPL 01.

**Kriteria penerimaan:** Owner dapat menambah dan mengubah lebih dari satu lokasi, menonaktifkan lokasi, dan memilih lokasi aktif; F7 menggunakan koordinat lokasi terpilih; lokasi nonaktif tidak dapat dipilih; tidak ada fallback ke koordinat hardcode.

**Uji QE:** tambah lokasi, ubah koordinat, pilih lokasi, nonaktifkan lokasi terpilih, pilih lokasi pengganti, dan kondisi tanpa lokasi aktif.

# **10. Kebutuhan Nonfungsional** 

- **Keamanan:** kata sandi di-hash; CSRF aktif; otorisasi per peran di backend (bukan hanya menyembunyikan menu); kunci API di .env; webhook diverifikasi; harga, total, dan ongkir selalu dihitung ulang di backend. 

- **Konsistensi data:** stok tidak pernah negatif; pengembalian stok tepat satu kali; operasi kritis dalam transaksi. 

- **Usability:** mobile-first; langkah dari katalog ke pembayaran singkat; pesan error jelas. 

- **Kompatibilitas:** peramban modern di HP dan komputer. 

- **Maintainability:** logika bisnis di class service; parameter bisnis di settings; PR direview. 

- **Kinerja:** tidak ada target angka dari pemilik. Kinerja tidak menjadi kriteria penerimaan, tetapi query katalog dan daftar pesanan memakai index dan paginasi. 

- **Batasan:** akun admin dipakai bersama sehingga sistem tidak dapat membedakan pemilik yang melakukan perubahan. 

# **11. QE dan Definition of Done** 

**Aturan QE:** dimulai sehari setelah pengembangan sprint selesai, berlangsung 2 hari, PIC QE Orang 2. Perbaikan bug dilakukan PIC fitur terkait dan Orang 2 

mengoordinasikan pengujian ulang. Sprint lulus hanya jika tidak ada bug kritis tersisa. 

**Dokumentasi uji:** ID kasus, langkah, hasil yang diharapkan, hasil aktual, status, dan tingkat keparahan. Tingkat keparahan: kritis (alur utama atau data terganggu), mayor (fitur sebagian tidak berfungsi), minor (tampilan atau teks). 

**12 alur QE akhir (usulan, PPL 36 dan PPL 42):** (1) login, daftar, dan hak akses; (2) kelola produk; (3) katalog; (4) keranjang; (5) checkout; (6) ongkir; (7) pembayaran berhasil; (8) pembayaran gagal atau kedaluwarsa; (9) daftar pesanan dan status; (10) Moving Average; (11) Rule-Based restok; (12) integrasi lintas fitur dan penanganan error. Pengujian lokasi tersimpan mencakup tambah/ubah lokasi, pilih lokasi, lokasi nonaktif, dan ongkir memakai koordinat lokasi terpilih. 

# **Definition of Done (setiap Work Package pengembangan):** 

1. UI dan backend/API fitur terintegrasi. 

2. Fungsi utama berjalan sesuai kebutuhan. 

3. Pengujian fitur dilakukan dan hasilnya dicatat. 

4. Tidak ada bug kritis; bug minor dicatat jelas. 

5. Kode digabungkan ke repositori yang disepakati. 

6. Dokumentasi terkait diperbarui. 

7. Integrasi dengan fitur terkait diperiksa. 

8. Fitur siap menjalani QE. 

# **12. Parameter Operasional dan Risiko**

Aturan bisnis inti berikut sudah final; bagian ini hanya mencatat hal operasional yang masih perlu dikonfirmasi atau dipilih sebelum integrasi produksi. 

|**Parameter**|**Status**|**Penanggung**<br>**jawab**|
|---|---|---|
|Formula tarif ongkir|Final dan tetap: gratis sampai 5,5 km; setelah itu bertambah Rp2.000 per kenaikan kilometer sesuai formula FR-7.3|Orang 1|
|Koordinat lokasi asal|Disimpan per lokasi di `shipping_locations`; Owner memilih lokasi aktif. Koordinat setiap lokasi harus diverifikasi|Orang 1|
|Penyedia pembayaran sandbox|Midtrans QRIS sandbox, sesuai PPL 01|Orang 1|
|Layanan routing OSM|Dipilih di PPL 19|Orang 1|
|Batas waktu pembayaran|10 menit sejak pesanan dibuat, sesuai PPL 01|Orang 1|
|Logo, warna, dan font Kopi Tiga<br>Titik|Dari pemilik, menyusul|Orang 1|



# **Risiko dan mitigasi:** 

- _Webhook sandbox tidak terjangkau saat pengembangan lokal._ Siapkan tunnel publik sejak PPL 19. 

- _Batas pemakaian layanan gratis (routing, geocoding)._ Jaga jumlah permintaan, patuhi kebijakan, dan tangani error (FR-7.6). 

- _Koordinat lokasi asal belum diverifikasi._ Owner dapat menyimpan rumah produksi dan lapak sebagai lokasi terpisah. Gunakan koordinat uji yang diberi label jelas selama pengembangan; jangan menganggapnya sebagai koordinat produksi final. Formula tarif ongkir sudah final sesuai PPL 01. 

- _Jadwal padat dan padat QE._ Tidak ada fitur wajib baru di Sprint 4. Seluruh pengembangan fitur selesai paling lambat 28/11; QE akhir dilakukan 29/11. 

- _Konflik merge antar PIC._ Gunakan PR kecil dan konvensi branch di Bab 6.5. 

- _Data simulasi dikira data nyata._ Label simulasi wajib tampil di UI dan dokumentasi; data simulasi dikecualikan dari Moving Average produksi. 



# **13. Catatan Audit Konsistensi Revisi**

- Jumlah kelompok fitur wajib tetap 11 dan mengikuti PPL 01; pengelolaan lokasi asal tersimpan adalah F11 tersendiri. Validasi, penanganan error, integrasi, QE, dan dokumentasi adalah persyaratan lintas fitur.
- Model data menggunakan tepat 10 tabel. Tidak menambahkan tabel percobaan pembayaran, event webhook, mutasi stok, atau keranjang terpisah.
- Kolom catatan riwayat status pada migration PPL 02 bernama `keterangan`; jangan menggantinya menjadi `catatan`. Tabel `payments` tidak memiliki kolom `provider`; referensi provider disimpan pada `referensi_provider`.
- Status pesanan `selesai` dilakukan oleh kurir yang ditugaskan setelah diterima pelanggan; Admin mengelola status sampai `dikirim` dan menugaskan kurir; Kurir hanya melihat pesanan yang ditugaskan kepadanya dan mengubah status `dikirim` menjadi `selesai`.
- Lokasi asal terpilih harus ada dan aktif; ID dalam `settings` divalidasi backend. Tidak ada fallback ke koordinat hardcode atau ongkir tebakan.
- ERD dan migration PPL 02 yang sudah dibuat harus diverifikasi silang untuk tipe data, nullable, unique/index, foreign key, dan aturan penghapusan. Perubahan skema hanya dilakukan melalui persetujuan PO dan prosedur migrasi tim; jangan menjalankan `migrate:fresh`.
- Koordinat lokasi asal produksi belum dianggap final sampai diverifikasi Owner.
