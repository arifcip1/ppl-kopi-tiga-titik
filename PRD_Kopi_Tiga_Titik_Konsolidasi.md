# **PRODUCT REQUIREMENTS DOCUMENT (PRD)** 

# **Sistem Informasi Penjualan Kopi Tiga Titik (Online dan POS Offline)** 

**Versi:** 2.4 (konsolidasi keputusan Sprint 3–4, 11 November 2026) | **Penyusun:** Orang 1 (Product Owner + System Analyst) **Acuan jadwal, PIC, dan ID Work Package:** file Kopi_Tiga_Titik_Sprint_Plan_Revisi (PPL 01–PPL 42) 

**Periode proyek:** 28 Oktober – 29 November 2026 

Setiap bagian fitur di Bab 9 bisa diserahkan langsung ke PIC-nya. Keputusan final mengikuti PPL 01 revisi konsolidasi 11 November 2026 dan WBS PPL 01–42 terbaru. Hanya hal yang memang belum diputuskan yang ditandai sebagai parameter. 

> **Acuan konsistensi:** Dokumen ini diselaraskan dengan PPL 01 revisi dan ERD 10 tabel. Jika terdapat perbedaan pada versi lama PRD, ikuti revisi 11 November 2026: POS Kasir Offline, ambil sendiri, lokasi/jam operasional, notifikasi web, nota digital, histori penjualan dan ekspor Excel termasuk MVP; ambang restok `stok <= 5`; Midtrans QRIS sandbox hanya untuk transaksi online; POS memakai tunai/QRIS langsung; Moving Average 21 hari kalender mencakup penjualan online dan POS; dan database domain tetap 10 tabel.

# **1. Ringkasan Produk** 

**1.1 Latar belakang.** Kopi Tiga Titik adalah usaha street coffee di Mojokerto (berdiri Juli 2026) yang menjual kopi botol 250 ml dalam 4 varian. Pesanan online masih lewat WhatsApp dan direkap manual di Excel, sehingga rawan salah catat. Ongkir dipukul rata dan pembayaran dicek manual. 

**1.2 Tujuan produk.** Aplikasi web mobile-first yang menjalankan alur: katalog → keranjang → checkout → pembayaran sandbox terverifikasi → ongkir berbasis jarak rute → daftar pesanan dan status. Aplikasi juga memberi estimasi penjualan (Moving Average) dan peringatan restok (rule-based). 

# **1.3 Indikator keberhasilan (dapat diverifikasi).** 

- Pengembangan fitur wajib selesai paling lambat 27/11/2026 (akhir PPL 41); QE akhir PPL 42 berlangsung 28–29/11/2026. 

- Sprint 1–3 lulus QE tanpa bug kritis (QE 3–4/11, 11–12/11, 19–20/11). QE akhir PPL 42 berlangsung 28–29/11/2026 dan mencakup seluruh kelompok skenario fitur MVP termasuk POS, ambil sendiri, notifikasi, histori penjualan, dan ekspor. 

- Tidak ada pesanan online yang diproses tanpa pembayaran Midtrans berstatus `berhasil` yang telah diverifikasi backend; transaksi POS hanya diselesaikan setelah konfirmasi pembayaran oleh Owner. 

- Stok tidak pernah negatif, dan kembali tepat saat pesanan batal atau kedaluwarsa. 

- Ongkir dihitung dari jarak rute menggunakan formula tarif tetap yang telah disepakati. 

- Dokumentasi teknis dan panduan penggunaan tersedia (PPL 35). 

# **2. Ruang Lingkup** 

# **2.1 Dalam cakupan (fitur wajib MVP).**

Ruang lingkup berikut mencakup fitur produk dan fitur pendukung yang sudah dialokasikan pada PPL 01–42. Fitur tambahan dikerjakan pada PPL 23–42 yang telah tersedia; tidak menambah Work Package baru dan tidak mengubah Sprint 1–2 yang telah selesai.

1. Database, autentikasi, dan hak akses tiga role (Admin/Owner, Kurir, Pelanggan).
2. Pengelolaan produk, harga, stok, dan foto.
3. Katalog produk pelanggan.
4. Keranjang belanja.
5. Checkout online dan pembuatan pesanan.
6. Pembayaran online Midtrans QRIS sandbox dengan verifikasi backend, percobaan pembayaran, nota digital, dan tombol Hubungi Penjual.
7. Pengiriman berbasis jarak rute OpenStreetMap/OpenRouteService dengan formula tarif tetap dan batas wilayah Kota Mojokerto saja.
8. Pilihan ambil sendiri dengan ongkir Rp0, snapshot lokasi pengambilan, batas 24 jam sejak pembayaran, dan penyelesaian manual/otomatis.
9. Pengelolaan lokasi lapak aktif serta jam operasional oleh Owner; pengaturan berlaku untuk checkout online baru.
10. Dashboard Owner dan Kurir, daftar/detail/filter pesanan, pengelolaan akun dan penugasan kurir, serta pembaruan status pengiriman.
11. Notifikasi web bagi Owner, Kurir, dan Pelanggan.
12. POS Kasir Offline untuk transaksi lapak dengan pembayaran tunai atau QRIS langsung yang dikonfirmasi Owner, tanpa Midtrans/webhook.
13. Dashboard histori penjualan online dan POS (grafik bulanan, rekap harian/mingguan/bulanan) serta ekspor Excel; ongkir tidak termasuk omzet.
14. Moving Average 3 minggu sebagai rekomendasi berdasarkan penjualan online terverifikasi dan POS terkonfirmasi.
15. Rule-Based peringatan restok `stok <= 5` dan saran produksi.
16. Integrasi, QE, pengujian regresi, dokumentasi, penyempurnaan responsif, dan kesiapan demo.

# **2.2 Di luar cakupan (tidak dikerjakan kecuali diminta).**

- Scoring lima produk terlaris dan dashboard analitik lain di luar dashboard histori penjualan yang disebut pada cakupan.
- Integrasi API ekspedisi, pelacakan GPS langsung, optimasi rute multi-pesanan, dan manajemen armada.
- COD untuk checkout online, metode pembayaran online selain Midtrans QRIS sandbox, dan integrasi Midtrans untuk POS.
- Notifikasi WhatsApp/email, lupa kata sandi, dan verifikasi email. Tombol Hubungi Penjual hanya membuka kanal kontak penjual.
- Refund otomatis. Pembayaran online terlambat/bermasalah ditandai untuk pemeriksaan Owner.
- Fitur yang tidak tercantum dalam PPL 01–42.

# **3. Pengguna dan Peran**

| Peran | Deskripsi | Akses utama |
|---|---|---|
| Admin/Owner | Akun pemilik yang dikelola melalui seeder; pada implementasi awal satu akun dipakai pemilik. | Mengelola produk, POS Kasir Offline, lokasi dan jam operasional, seluruh pesanan, akun/penugasan kurir, histori penjualan, ekspor Excel, pembayaran, Moving Average, peringatan stok, dan notifikasi. Tidak dapat menetapkan pembayaran online berhasil secara manual; pembayaran POS dikonfirmasi langsung oleh Owner. |
| Kurir | Pengguna terautentikasi yang ditugaskan Admin pada pesanan. | Melihat pesanan yang ditugaskan kepadanya dan menandai Selesai setelah pesanan diterima pelanggan. |
| Pelanggan | Pembeli yang wajib memiliki akun untuk checkout online. | Melihat katalog, mengelola keranjang, checkout pengiriman/ambil sendiri, membayar, melihat status dan nota digital, menghubungi penjual, menerima notifikasi web, dan membatalkan pesanan yang belum dibayar. |

Hak akses wajib ditegakkan di backend, bukan hanya dengan menyembunyikan menu. Guest bukan peran tersimpan di database; guest yang mengakses fitur terlindungi diarahkan ke login.

### Cakupan dashboard per role

Dashboard merupakan antarmuka ringkasan dan navigasi ke fitur yang telah masuk ruang lingkup, bukan penambahan modul bisnis baru.

| Dashboard | Cakupan | Penempatan Work Package |
|---|---|---|
| Pembeli | Beranda/navigasi pembeli, katalog, keranjang, riwayat/status pesanan, notifikasi status/lokasi, nota digital, dan Hubungi Penjual | PPL 14, PPL 23–24, PPL 27–28 |
| Owner | Ringkasan operasional, POS Kasir, produk, pesanan, kurir, notifikasi, histori penjualan, ekspor, prediksi, restok, lokasi dan jam operasional | PPL 25–32 |
| Kurir | Ringkasan tugas, pesanan yang ditugaskan, detail penerima, aksi status, notifikasi tugas baru | PPL 27–28 |
| Integrasi | Integrasi alur dashboard dan fitur transaksi lintas modul | PPL 33–42 |

Dashboard Pembeli tetap dikerjakan pada PPL 14 untuk UI dasar. PPL 27 hanya mencakup UI Dashboard Owner dan Kurir; PPL 28 menangani backend/API. Dashboard histori penjualan adalah fitur yang termasuk MVP, bukan dashboard analitik tambahan di luar cakupan.


Tim: Orang 1 (PO + System Analyst), Orang 2 (Scrum Master + QA/Tester), Orang 3 dan 4 (UI/UX Designer). Semua anggota dapat mengerjakan frontend, backend, integrasi, dokumentasi, dan pengujian fitur.

# **4. Peta Fitur ke Work Package dan Jadwal**

Pemetaan berikut menggunakan nomor fitur yang sama dengan Bab 9. Work Package tambahan Sprint 3–4 tetap berada pada PPL 23–42; tidak ada WP baru.

| Fitur | Nama fitur | Work Package utama | WP integrasi/QE |
|---|---|---|---|
| F1 | Database dan Autentikasi/Akses | PPL 01–07 | PPL 34–42 |
| F2 | Pengelolaan Produk, Harga, Stok, Foto | PPL 08–09 | PPL 37, 41–42 |
| F3 | Katalog Produk Pelanggan | PPL 10–11 | PPL 14, 34, 38, 42 |
| F4 | Keranjang Belanja | PPL 13–14 | PPL 34, 36, 38, 42 |
| F5 | Checkout dan Pembuatan Pesanan | PPL 15–18, 23–26 | PPL 34, 36, 38–42 |
| F6 | Pembayaran API Sandbox dengan Verifikasi Backend | PPL 19, 23–24 | PPL 33–34, 40–42 |
| F7 | Ongkir Berbasis Jarak Rute (OpenStreetMap/OpenRouteService) | PPL 19, 25–26 | PPL 33–35, 38, 42 |
| F8 | Daftar Pesanan dan Status (Owner, Kurir, Pelanggan) | PPL 14, 27–28 | PPL 34, 37, 41–42 |
| F9 | Moving Average 3 Minggu | PPL 03, 29–30 | PPL 33–36, 39, 42 |
| F10 | Rule-Based Peringatan Restok | PPL 31–32 | PPL 33–35, 39, 42 |
| F11 | Pengelolaan Lokasi Aktif dan Jam Operasional | PPL 25–26 | PPL 33–35, 38–39, 42 |
| F12 | POS Kasir Offline | PPL 31–32 | PPL 29–30, 33–35, 39–42 |
| F13 | Pesanan Ambil Sendiri, Nota Digital, dan Hubungi Penjual | PPL 23–24 | PPL 33–34, 37–42 |
| F14 | Notifikasi Web | PPL 27–28, 32 | PPL 33–42 |
| F15 | Dashboard Histori Penjualan dan Ekspor Excel | PPL 29–30 | PPL 33–36, 39, 41–42 |

Validasi, penanganan error, integrasi, QE, dokumentasi, dan kesiapan demo adalah persyaratan lintas fitur, bukan F16. PPL 33–42 adalah WP QE/integrasi/perbaikan/penyempurnaan, bukan implementasi fitur bisnis baru.

**Jadwal acuan:** pengembangan sampai PPL 41 selesai paling lambat 27 November 2026; PPL 42 QE akhir 28–29 November 2026. PIC, poin, durasi, dan tanggal rinci mengikuti WBS revisi 11 November 2026.


# **5. Aturan Bisnis Global**

- **BR-1 Produk.** Empat varian botol 250 ml: Titik Hitam Rp8.000, Titik Temu Rp10.000, Titik Manja Rp10.000, Titik Jenuh Rp12.000. Keawetan 5–7 hari bila disimpan di lemari pendingin.
- **BR-2 Pesanan online.** Pelanggan wajib memiliki akun. Pesanan online dibuat dari stok tersedia; stok ditahan/dikurangi saat checkout dan dikembalikan tepat satu kali jika pesanan belum dibayar dibatalkan atau kedaluwarsa.
- **BR-3 Pembayaran online.** Hanya Midtrans QRIS mode sandbox. Status berhasil hanya setelah backend memverifikasi notifikasi resmi atau pemeriksaan status resmi. Frontend dan Owner tidak dapat menetapkan status pembayaran online berhasil secara manual.
- **BR-4 Percobaan pembayaran.** Satu pesanan dapat memiliki beberapa percobaan pembayaran Midtrans; setiap percobaan disimpan sebagai baris `payments` tersendiri. Percobaan gagal tidak mengganti atau menimpa riwayat percobaan sebelumnya.
- **BR-5 Batas pembayaran.** Pesanan online yang belum dibayar kedaluwarsa 10 menit sejak pesanan dibuat. Pembayaran terlambat dicatat untuk pemeriksaan Owner dan tidak mengaktifkan pesanan kembali otomatis.
- **BR-6 Pembatalan online.** Pelanggan hanya dapat membatalkan pesanan online yang belum dibayar. Pesanan yang sudah dibayar tidak dapat dibatalkan melalui aplikasi; refund otomatis di luar cakupan.
- **BR-7 Ongkir.** Ongkir dihitung dari jarak rute jalan menggunakan layanan routing berbasis OpenStreetMap/OpenRouteService. Jika routing gagal atau koordinat tidak valid, checkout pengiriman diblokir; tidak menggunakan jarak garis lurus atau ongkir tebakan.
- **BR-8 Tarif dan wilayah.** Jarak `d <= 5,5 km` gratis. Untuk `d > 5,5 km`, ongkir = `Rp2.000 × (floor(d - 5,5) + 1)`. Pengiriman hanya untuk Kota Mojokerto, bukan Kabupaten Mojokerto.
- **BR-9 Lokasi aktif.** Owner dapat mengelola lokasi lapak dan memilih satu lokasi aktif. Perubahan lokasi berlaku untuk checkout baru. Pesanan yang telah berhasil dibuat tetap menggunakan snapshot nama, alamat, dan koordinat lokasi saat checkout.
- **BR-10 Jam operasional.** Owner dapat mengubah jam buka/tutup kapan saja. Validasi jam hanya berlaku pada checkout online baru; perubahan jam tidak membatalkan atau mengubah pesanan yang telah berhasil dibuat/dibayar.
- **BR-11 Ambil sendiri.** Checkout online menyediakan pilihan `pengiriman` atau `ambil_sendiri`. Ambil sendiri memiliki ongkir Rp0 dan tidak mewajibkan koordinat tujuan pengiriman. Nama, alamat, dan koordinat lokasi pengambilan disimpan sebagai snapshot pada pesanan.
- **BR-12 Batas pengambilan.** Batas ambil sendiri adalah 24 jam sejak pembayaran berhasil. Owner menandai pesanan selesai ketika diambil; jika tidak diambil sampai batas waktu, sistem mengubah status menjadi selesai otomatis. Tidak ada refund otomatis.
- **BR-13 POS offline.** POS hanya untuk transaksi langsung di lapak. Pesanan POS memakai `sumber_transaksi=pos`, `metode_pemenuhan=NULL`, dan `user_id` boleh NULL. Pembayaran tunai atau QRIS dilakukan langsung di lapak dan status berhasil dicatat setelah konfirmasi Owner; POS tidak memanggil Midtrans atau webhook.
- **BR-14 Stok POS.** Transaksi POS yang dikonfirmasi berhasil mengurangi stok secara atomik. Transaksi gagal/belum dikonfirmasi tidak mengurangi stok dan tidak masuk penjualan nyata.
- **BR-15 Riwayat penjualan.** Penjualan online dicatat setelah pembayaran Midtrans terverifikasi; penjualan POS setelah konfirmasi Owner. Keduanya masuk `sales_history` dengan `sumber=transaksi`. Data simulasi menggunakan `sumber=simulasi` dan tidak boleh memengaruhi hasil produksi. Pencatatan harus idempoten.
- **BR-16 Dashboard histori dan omzet.** Rekap menggabungkan online dan POS, per produk/varian, harian/mingguan/bulanan. Omzet tidak memasukkan ongkir. Ekspor tersedia dalam format Excel.
- **BR-17 Moving Average.** Gunakan 21 hari kalender sebelum tanggal prediksi, dibagi tiga periode berurutan masing-masing 7 hari. Perhitungan mencakup transaksi online terverifikasi dan POS terkonfirmasi; hari tanpa penjualan dihitung 0. Hasil hanya rekomendasi.
- **BR-18 Restok.** Peringatan muncul jika `stok <= 5`; saran produksi `max(0, 10 - stok)` hanya rekomendasi dan tidak mengubah stok otomatis.
- **BR-19 Notifikasi web.** Owner menerima notifikasi pesanan baru dan stok menipis; Kurir menerima tugas baru; Pelanggan menerima perubahan status pesanan dan lokasi pengambilan. Notifikasi bersifat sementara/real-time dan tidak menambah tabel domain ke-11.
- **BR-20 Status pesanan.** Status pesanan mengikuti enum pada migration aktual: `menunggu_pembayaran`, `diproses`, `dikirim`, `selesai`, `dibatalkan`. Setiap perubahan dicatat di `order_status_history`.
- **BR-21 Data simulasi.** Data simulasi diberi label `simulasi`, digunakan untuk pengujian/demo, dan tidak dicampur dengan transaksi nyata.
- **BR-22 Waktu.** Semua waktu menggunakan zona Asia/Jakarta; nominal disimpan sebagai Rupiah bilangan bulat.
- **BR-23 Produk nonaktif.** Produk nonaktif tidak ditampilkan pada katalog dan tidak dapat di-checkout. Produk yang telah digunakan transaksi dinonaktifkan, bukan dihapus permanen.

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

# **7. Model Data**

Model data PRD harus mengikuti ERD PPL 02 yang terdiri dari **10 tabel**. Sepuluh tabelnya adalah `users`, `products`, `cart_items`, `orders`, `order_items`, `payments`, `order_status_history`, `sales_history`, `settings`, dan `shipping_locations`. Jangan menambahkan tabel `stock_movements`, `payment_events`, `simulated_sales`, atau `carts` tambahan. Data simulasi tetap berada di `sales_history` dengan `sumber='simulasi'`; jangan menyimpan `payments.status` terpisah pada `orders`.

- **`users`**: `id`, `name`, `email` (unik), `password`, `role` (`admin`, `kurir`, `pelanggan`), serta kolom bawaan Laravel yang digunakan migrasi. Kata sandi disimpan dalam bentuk hash. Role divalidasi backend.
- **`products`**: `id`, `nama`, `rasa`, `komposisi`, `deskripsi`, `keawetan`, `harga`, `stok`, `foto_path`, `aktif`. Harga dan stok tidak boleh negatif. Produk yang pernah digunakan dalam transaksi dinonaktifkan, bukan dihapus permanen.
- **`cart_items`**: `id`, `user_id`, `product_id`, `jumlah`. Kombinasi `user_id` dan `product_id` unik; `jumlah > 0`. Keranjang tidak menahan stok.
- **`orders`**: `id`, `kode` (unik), `sumber_transaksi` (`online`/`pos`), `metode_pemenuhan` (`pengiriman`/`ambil_sendiri`, nullable untuk POS), `user_id` (nullable untuk POS), `courier_id`, `status`, `subtotal`, `ongkir`, `total`, `nama_pelanggan`, data penerima, koordinat tujuan, snapshot nama/alamat/koordinat lokasi asal atau pengambilan, `jarak_pengiriman`, `batas_pengambilan`, `catatan`, `stok_dikembalikan`, timestamps. Semua kolom wajib mengikuti migration aktual.
- **`order_items`**: `id`, `order_id`, `product_id`, `nama_produk`, `rasa` (nullable), `jumlah`, `harga_satuan`, `subtotal`. Nama, rasa, harga satuan, dan subtotal merupakan snapshot saat checkout. Aturan unik mengikuti migrasi PPL 02; jangan mengasumsikan pasangan produk dalam satu pesanan unik jika constraint tersebut tidak dibuat.
- **`payments`**: `id`, `order_id` (tidak unik; satu pesanan dapat memiliki banyak percobaan Midtrans), `metode_pembayaran` (`midtrans`, `tunai`, `qris`), `referensi_provider` (unik nullable), `status`, `jumlah`, `kedaluwarsa_pada`, `waktu_verifikasi`, `payload_mentah` (JSON). POS memakai tunai/QRIS langsung tanpa referensi Midtrans.
- **`order_status_history`**: `id`, `order_id`, `status_baru`, `diubah_oleh` (nullable untuk proses otomatis), `waktu_perubahan`, `keterangan`. Gunakan nama kolom sesuai migration aktual; jangan menambahkan `status_sebelumnya` atau `user_id`.
- **`sales_history`**: `id`, `product_id`, `tanggal`, `jumlah_terjual`, `sumber` (`transaksi` atau `simulasi`). Kombinasi `product_id`, `tanggal`, `sumber` unik; data dengan kombinasi sama diakumulasikan.
- **`settings`**: `id`, `key` (unik), `value` (nullable), `description` (nullable), serta timestamps sesuai migrasi. Memuat antara lain `batas_stok_minimum=5`, `stok_harian_standar=10`, `lokasi_pengiriman_aktif_id`, `jam_buka`, dan `jam_tutup`. Tidak menyimpan tarif ongkir karena tarif bersifat tetap. Backend wajib memvalidasi ID dan status aktif lokasi sebelum digunakan.
- **`shipping_locations`**: `id`, `nama_lokasi`, `latitude`, `longitude`, `alamat`, `aktif`, `created_at`, `updated_at`. Menyimpan beberapa lokasi asal milik Owner, misalnya rumah produksi dan lapak offline. Lokasi yang tidak tersedia dinonaktifkan, bukan dihapus permanen. Hanya lokasi aktif yang dapat dipilih sebagai asal ongkir.

Semua relasi mengikuti ERD: keranjang ke pengguna dan produk; pesanan ke pelanggan dan kurir; detail pesanan ke pesanan dan produk; pembayaran satu-ke-banyak dengan pesanan (percobaan Midtrans terpisah); riwayat status ke pesanan dan aktor; riwayat penjualan ke produk. `settings.lokasi_pengiriman_aktif_id` merupakan referensi logis ke `shipping_locations.id`, bukan foreign key database karena `settings.value` dipakai untuk beragam konfigurasi dan bertipe teks. Backend wajib memastikan ID menunjuk lokasi yang ada dan aktif. Operasi checkout, pembayaran, perubahan status, dan pengembalian stok harus atomik serta idempoten sesuai kebutuhan.

# **8. Status dan Transisi** 

- **Status pesanan:** `menunggu_pembayaran`, `diproses`, `dikirim`, `selesai`, `dibatalkan`. Pesanan yang melewati batas waktu pembayaran berubah menjadi `dibatalkan`; status pembayaran menjadi `kedaluwarsa`. 

- **Status pembayaran:** `pending`, `berhasil`, `gagal`, `kedaluwarsa` (label UI: Menunggu, Berhasil, Gagal, Kedaluwarsa). POS menyimpan metode `tunai`/`qris` langsung dan hanya ditandai berhasil setelah konfirmasi Owner. 

|**Dari**<br>**Ke**|**Pemicu**|**Pelaku**|
|---|---|---|
|(baru)<br>menunggu_pembayaran|<sup>Checkout selesai,</sup><br>stok berkurang|<br>Pelanggan|
||Pembayaran||
|menunggu_pembayaran → diproses|Pembayaran `berhasil` setelah verifikasi backend|Sistem (webhook)|
|menunggu_pembayaran dibatalkan|Dibatalkan pelanggan|Pelanggan|



|menunggu_pembayaran|kedaluwarsa|Batas 10 menit terlewati; stok dikembalikan satu kali|Sistem|
|diproses|dikirim|Admin mengirim<br>pesanan|Admin|
|dikirim|selesai|Pesanan diterima pelanggan|Kurir yang ditugaskan|



Transisi lain ditolak backend dengan error 409/422 (misalnya membatalkan pesanan yang sudah dibayar). Pembayaran gagal tidak membatalkan pesanan; pelanggan dapat membuat percobaan pembayaran baru selama pesanan belum kedaluwarsa. Pesanan ambil sendiri yang telah dibayar selesai saat diambil atau otomatis selesai setelah 24 jam. Transaksi POS yang berhasil dicatat sebagai penjualan dan mengurangi stok setelah konfirmasi Owner. 

### Ketentuan status khusus yang melengkapi tabel transisi

- **POS:** setelah Owner mengonfirmasi pembayaran tunai/QRIS langsung dan penyerahan barang di lapak, pesanan POS berstatus `selesai`; tidak masuk alur kurir.
- **Ambil sendiri online:** setelah pembayaran Midtrans berhasil, status menjadi `diproses`; Owner dapat mengubah ke `selesai` saat diambil. Scheduler menyelesaikan otomatis setelah `batas_pengambilan` terlewati. Perubahan otomatis mencatat `diubah_oleh = NULL` dan tidak melakukan refund.
- **Pengiriman online:** Owner mengubah `diproses` menjadi `dikirim` sekaligus/terpisah menugaskan Kurir; Kurir yang ditugaskan mengubah `dikirim` menjadi `selesai`.
- Perubahan lokasi aktif tidak mengubah snapshot lokasi pada pesanan yang telah dibuat.

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

- FR-5.8 Selama F7 belum terintegrasi, pengujian lokal boleh memakai fixture/stub yang diberi label khusus dan tidak dapat dipakai pada alur checkout nyata. Pada integrasi dan QE, checkout pengiriman wajib memakai hasil routing valid; jika routing gagal, checkout diblokir dan tidak boleh memakai ongkir nol/perkiraan sebagai fallback. Total = subtotal + ongkir. 

**Validasi:** nama wajib; nomor kontak wajib dengan format telepon Indonesia; detail alamat wajib; titik lokasi tujuan wajib untuk `pengiriman` dan tidak wajib untuk `ambil_sendiri` (lat/lng valid, Bab F7); keranjang tidak kosong. **Kriteria penerimaan:** stok berkurang tepat sesuai jumlah; dua pelanggan memesan botol terakhir bersamaan hanya satu yang berhasil; total sama dengan penjumlahan item (+ ongkir); kegagalan tengah proses tidak meninggalkan stok yang terpotong. **Uji QE:** pesanan normal, stok tidak cukup, dua sesi paralel, rollback saat error, batas waktu pembayaran. 

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

- FR-7.7 Nilai ongkir dan jarak pengiriman disimpan pada kolom `orders.ongkir` dan `orders.jarak_pengiriman` sesuai migration aktual, lalu menjadi snapshot saat pesanan dibuat. Backend menghitung ulang ongkir saat checkout dan tidak mempercayai nilai dari browser. 

**Ketentuan final:** formula tarif bersifat tetap: jarak rute `d ≤ 5,5 km` gratis; untuk `d > 5,5 km`, ongkir = Rp2.000 × (`floor(d − 5,5) + 1`). Nominal dan ambang tarif bukan pengaturan yang dapat diubah Owner melalui aplikasi. Koordinat asal diambil dari lokasi tersimpan yang dipilih Owner. Lokasi uji harus diberi label sebagai data uji dan tidak dianggap lokasi produksi final. **Kriteria penerimaan:** hasil ongkir sesuai formula tetap; layanan routing yang dimatikan menghasilkan pesan error, bukan crash; titik di luar Kota Mojokerto ditolak. 

**Uji QE:** jarak di bawah, tepat di, dan di atas ambang gratis; titik tidak valid; wilayah luar Mojokerto; layanan routing down. 

# **F8. Daftar Pesanan dan Status (Owner, Kurir, serta tampilan status Pembeli)** 

**WP:** PPL 27 (UI Dashboard Owner dan Kurir; Dep PPL 17), PPL 28 (backend/API Owner dan Kurir; Dep PPL 16, 17). UI Dashboard Pembeli, termasuk riwayat dan pemantauan status pesanannya, dipusatkan pada PPL 14. 

# **Kebutuhan fungsional:** 

- FR-8.1 Daftar pesanan terbaru di atas, dengan filter status (Semua, Menunggu Pembayaran, Diproses, Dikirim, Selesai, Dibatalkan) dan paginasi. 

- FR-8.2 Detail pesanan: nomor, waktu, item (nama, harga, jumlah), subtotal, ongkir, total, data penerima, alamat, titik peta (tautan ke peta), catatan, status pesanan, serta status dan waktu verifikasi pembayaran jika tersedia. Waktu verifikasi diambil dari `payments.waktu_verifikasi`; tidak menggunakan kolom `paid_at` karena tidak ada pada ERD. 

- FR-8.3 Admin mengubah status hanya sesuai Bab 8 (diproses → dikirim → selesai). Transisi lain ditolak dengan pesan jelas. 

- FR-8.4 Status pembayaran hanya tampil (read-only). Hanya sistem yang mengubahnya lewat F6. 

- FR-8.5 Setiap perubahan status dicatat pada `order_status_history` menggunakan kolom final `order_id`, `status_baru`, `diubah_oleh`, `waktu_perubahan`, dan `keterangan`; tidak menggunakan kolom `status_sebelumnya` atau `user_id`. 

- FR-8.6 Halaman daftar pesanan dan pengelolaan pesanan Owner hanya dapat diakses Admin/Owner. Pembeli melihat riwayat dan status pesanan miliknya melalui Dashboard Pembeli (PPL 14), dengan data dibatasi berdasarkan akun yang login.

- FR-8.7 Dashboard Kurir (UI pada PPL 27) menampilkan ringkasan tugas pengiriman dan daftar pesanan yang ditugaskan kepada kurir yang login. Kurir dapat membuka detail penerima/alamat dan melakukan aksi status yang diizinkan.

- FR-8.8 Dashboard Owner (UI pada PPL 27) menampilkan ringkasan operasional pesanan dan pembayaran serta akses ke daftar/detail pesanan. Informasi Moving Average dan peringatan restok tetap mengikuti F9 dan F10; dashboard tidak menjadi modul analitik terpisah.

- FR-8.9 Backend PPL 28 wajib membatasi data kurir hanya pada pesanan yang ditugaskan kepadanya dan membatasi data Pembeli hanya pada pesanan miliknya. Penugasan kurir dan perubahan status mengikuti Bab 8. 

**Kriteria penerimaan:** pesanan baru dari checkout langsung muncul; filter benar; transisi tidak valid ditolak; pesanan sudah dibayar tidak bisa dibatalkan. **Uji QE:** semua filter, semua transisi valid dan tidak valid, akses non-admin. 

# **F9. Moving Average 3 Minggu** 

**WP:** PPL 03 (data simulasi), PPL 29 (UI; Dep PPL 03), PPL 30 (backend; Dep PPL 03, 17). 

**Rumus:** estimasi minggu berikutnya varian v = (P₁ + P₂ + P₃) / 3. P₁–P₃ adalah total penjualan pada tiga periode berurutan, masing-masing 7 hari, dalam 21 hari kalender sebelum tanggal prediksi. Periode dihitung mundur dari tanggal prediksi, bukan mengikuti minggu kalender. 

# **Kebutuhan fungsional:** 

- FR-9.1 Sumber perhitungan: penjualan nyata (`sumber=transaksi`) dari pembayaran online Midtrans yang telah terverifikasi dan transaksi POS yang telah dikonfirmasi Owner. Rekap dilakukan melalui `sales_history` berdasarkan produk, tanggal, dan sumber; hari tanpa penjualan dihitung 0. Baris `sumber=simulasi` hanya digunakan untuk pengujian dan dikecualikan dari perhitungan Moving Average produksi. 

- FR-9.2 Halaman admin /admin/forecast menampilkan per varian: penjualan nyata pada tiga periode 7 hari terakhir, estimasi minggu berikutnya (satu desimal), kebutuhan stok yang disarankan (dibulatkan ke atas), serta keterangan bahwa perhitungan menggunakan transaksi terverifikasi. 

- FR-9.3 Jika histori transaksi nyata belum mencakup 21 hari kalender penuh, tampil “Data transaksi belum cukup” dan jangan mengganti kekurangan data dengan data simulasi. Setelah periode 21 hari tersedia, hari tanpa penjualan nyata dihitung 0. Seeder boleh menyediakan data simulasi untuk menguji tampilan dan rumus pada lingkungan pengujian, tetapi data tersebut tidak boleh dipakai oleh perhitungan produksi. 

- FR-9.4 Estimasi hanya saran. Tidak mengubah stok maupun target. 

- FR-9.5 Rumus dan contoh perhitungan didokumentasikan (PPL 30, PPL 35). 

**Contoh uji (ilustrasi):** Titik Temu 60, 70, 80 botol → (60 + 70 + 80) / 3 = 70. **Kriteria penerimaan:** hasil sesuai hitungan manual untuk data transaksi nyata uji; data simulasi tidak memengaruhi hasil produksi; kasus kurang dari 3 minggu transaksi nyata menampilkan pesan. 

**Uji QE:** data transaksi nyata dengan hasil diketahui, batas pergantian periode 7 hari, varian tanpa penjualan (hasil 0), histori transaksi nyata kurang dari 21 hari, serta verifikasi bahwa data simulasi tidak ikut dihitung. 

# **F10. Rule-Based Peringatan Restok** 

**WP:** PPL 31 (UI Rule-Based dan POS; Dep PPL 09), PPL 32 (backend Rule-Based dan POS; Dep PPL 09, 18). 

**Aturan:** stok `<= batas_stok_minimum` (default 5) → status “Stok Menipis/Segera Restok”. Stok 0 sampai 5 memicu peringatan; stok 6 atau lebih tidak. Stok 0 juga ditampilkan sebagai “Habis” (tombol pesan di katalog nonaktif, F3). Sistem juga menampilkan saran jumlah produksi `max(0, 10 − stok)` dengan standar 10 botol per varian (nilai awal `settings.stok_harian_standar = 10`). Saran hanya informasi bagi Owner; tidak membuat produksi, pesanan, atau perubahan stok otomatis. 

# **Kebutuhan fungsional:** 

- FR-10.1 Aturan dievaluasi dari stok terkini. Saat stok masuk ambang `<= 5`, sistem juga memicu notifikasi web sementara kepada Owner melalui mekanisme PPL 27–28/32; tidak menambah tabel domain notifikasi. 

- FR-10.2 UI: lencana di daftar produk admin dan panel “Peringatan Restok” berisi produk dengan stok `<= batas_stok_minimum`, beserta nama dan stok saat ini. Tampilkan pula saran jumlah produksi `max(0, 10 − stok)`; Owner tetap menentukan jumlah produksi sebenarnya. 

- FR-10.3 Ambang peringatan dan standar stok harian dibaca dari `settings`. 

- FR-10.4 Peringatan hilang otomatis setelah stok dinaikkan menjadi lebih dari 5 (minimal 6). Saran produksi menjadi 0 apabila stok sama dengan atau melebihi standar stok harian. 

**Kriteria penerimaan:** stok 4 dan 5 memicu peringatan; stok 6 tidak; stok 0 menampilkan status habis sekaligus peringatan stok rendah; pengurangan stok oleh pesanan memicu peringatan di tampilan berikutnya. **Uji QE (PPL 32):** nilai stok 0, 4, 5, dan 6; perubahan ambang di settings. 

# **Persyaratan Lintas Fitur: Validasi, Penanganan Error, Integrasi, Pengujian, Dokumentasi** 

**WP:** PPL 04, PPL 12, PPL 20–22, PPL 33–37, PPL 39, PPL 41–42. Pekerjaan ini melekat pada seluruh fitur F1–F15 dan tidak dihitung sebagai kelompok fitur produk tersendiri. 

# **Kebutuhan fungsional:** 

- FR-X.1 Semua input divalidasi di backend (Form Request). Pesan error berbahasa Indonesia, per field, dan tidak menghapus data yang sudah diisi. 

- FR-X.2 Penanganan error global: halaman 403, 404, 419, 500 yang rapi (PPL 20). Respons JSON untuk endpoint /api/* dengan kode status dan pesan yang konsisten. Detail teknis (stack trace) tidak tampil ke pengguna (APP_DEBUG=false di produksi). 

- FR-X.3 Error layanan eksternal (pembayaran, routing, geocoding) ditangkap, dicatat di log, dan ditampilkan sebagai pesan ramah. 

- FR-X.4 Setiap operasi multi-tabel (checkout, pembayaran, pembatalan) memakai transaksi database. 

- FR-X.5 Integrasi alur beli end-to-end dari katalog sampai stok diuji dan diperbaiki (PPL 34). 

- FR-X.6 Dokumentasi teknis dan panduan penggunaan (PPL 35): setup (README: env, migrasi, seeder, menjalankan scheduler dan tunnel), endpoint dan contoh payload, rumus Moving Average, aturan restok, parameter tarif ongkir, serta panduan Pelanggan dan Admin. 

- FR-X.7 Setiap fitur memiliki dokumentasi singkat dan hasil pengujian yang dicatat (DoD). 

**Kriteria penerimaan:** tidak ada error 500 tanpa penanganan pada skenario QE; semua pesan validasi tampil per field; dokumentasi memungkinkan orang lain menjalankan sistem dari nol. 

# **F11. Pengelolaan Lokasi Aktif dan Jam Operasional**

**WP:** PPL 19 (aturan dan kebutuhan lokasi asal), PPL 25–26 (integrasi lokasi asal dengan perhitungan ongkir).

**Tujuan:** Admin/Owner dapat menyimpan dan memilih lokasi lapak aktif untuk keperluan pengiriman/ambil sendiri serta mengatur jam buka dan tutup. Lokasi dan jam baru berlaku untuk checkout baru; pesanan yang telah berhasil dibuat mempertahankan snapshot.

**Kebutuhan fungsional:**

- FR-11.1 Admin/Owner dapat menambah lokasi dengan nama, alamat, dan koordinat peta. Data disimpan di `shipping_locations`.
- FR-11.2 Admin/Owner dapat melihat daftar lokasi tersimpan dan mengubah nama, alamat, serta koordinatnya.
- FR-11.3 Admin/Owner dapat mengaktifkan atau menonaktifkan lokasi. Lokasi yang tidak digunakan dinonaktifkan, bukan dihapus permanen.
- FR-11.4 Admin/Owner dapat memilih satu lokasi aktif sebagai asal pengiriman. ID lokasi terpilih disimpan pada `settings` dengan `key = lokasi_pengiriman_aktif_id`.
- FR-11.5 Backend wajib memastikan lokasi terpilih benar-benar ada dan `aktif = true`. Jika lokasi terpilih akan dinonaktifkan, Owner harus memilih lokasi aktif lain terlebih dahulu. Jika tidak ada lokasi asal yang valid dan aktif, quote ongkir dan checkout diblokir.
- FR-11.6 Owner dapat mengatur jam buka/tutup melalui pengaturan. Backend menolak checkout online baru di luar jam operasional; perubahan jam tidak mengubah pesanan yang sudah berhasil dibuat.
- FR-11.7 Lokasi aktif disimpan sebagai snapshot nama, alamat, dan koordinat pada pesanan ambil sendiri. Perubahan lokasi aktif tidak memindahkan pesanan lama.
- FR-11.8 Endpoint pengelolaan lokasi dan jam hanya dapat diakses Admin/Owner; nama dan path final mengikuti kontrak route aktual.

**Kriteria penerimaan:** Owner dapat menambah dan mengubah lebih dari satu lokasi, menonaktifkan lokasi, dan memilih lokasi aktif; F7 menggunakan koordinat lokasi terpilih; lokasi nonaktif tidak dapat dipilih; tidak ada fallback ke koordinat hardcode.

**Uji QE:** tambah lokasi, ubah koordinat, pilih lokasi, nonaktifkan lokasi terpilih, pilih lokasi pengganti, dan kondisi tanpa lokasi aktif.

# **F12. POS Kasir Offline**

**WP:** PPL 31 (UI), PPL 32 (backend/API), PPL 33–42 (integrasi, pengujian, dokumentasi, dan QE).

- Owner memilih produk dan jumlah; sistem menampilkan ringkasan subtotal dan total.
- Metode pembayaran POS hanya tunai atau QRIS langsung di lapak.
- Penjual mengonfirmasi pembayaran. Aplikasi tidak memanggil Midtrans, tidak membuat webhook, dan tidak meminta verifikasi payment gateway.
- Transaksi POS menyimpan `orders.sumber_transaksi = pos`, `metode_pemenuhan = NULL`, serta `user_id` boleh NULL. Baris pembayaran menyimpan `metode_pembayaran = tunai` atau `qris`.
- Setelah dikonfirmasi berhasil, stok berkurang secara atomik dan penjualan dicatat ke `sales_history` sebagai transaksi nyata. Request ulang tidak boleh menggandakan pengurangan stok atau histori penjualan.
- Transaksi yang belum dibayar atau gagal tidak masuk penjualan nyata.

**Kriteria penerimaan:** POS dapat menyelesaikan transaksi tunai dan QRIS langsung; validasi stok berlaku; stok dan histori penjualan berubah tepat satu kali setelah konfirmasi Owner; tidak ada panggilan Midtrans. Status order POS menjadi `selesai` setelah pembayaran dikonfirmasi Owner dan barang diserahkan langsung di lapak; POS tidak masuk alur penugasan Kurir.

# **F13. Pesanan Ambil Sendiri, Nota Digital, dan Hubungi Penjual**

**WP:** PPL 23 (backend), PPL 24 (UI), PPL 33–42 (integrasi dan QE).

- Checkout online menyediakan pilihan `pengiriman` dan `ambil_sendiri`.
- Ambil sendiri memiliki ongkir Rp0; koordinat tujuan pengiriman tidak wajib.
- Nama, alamat, dan koordinat lokasi pengambilan disimpan sebagai snapshot saat checkout. Perubahan lokasi aktif tidak mengubah pesanan yang sudah berhasil dibuat.
- Batas pengambilan 24 jam sejak pembayaran berhasil. Owner menandai selesai ketika pesanan diambil; sistem menyelesaikan otomatis jika melewati batas. Tidak ada refund otomatis.
- Nota digital menampilkan ringkasan pesanan dan status pembayaran berdasarkan data backend.
- Tombol Hubungi Penjual membuka kanal WhatsApp/kontak penjual yang telah dikonfigurasi; jangan mengarang nomor kontak.

**Kriteria penerimaan:** ongkir ambil sendiri Rp0; snapshot lokasi tetap; batas 24 jam dihitung dari pembayaran berhasil; status selesai manual/otomatis; nota dan tombol kontak menampilkan data yang benar.

# **F14. Notifikasi Web**

**WP:** PPL 27 (UI), PPL 28 (backend/event), PPL 32 (pemicu stok menipis), PPL 33–42 (integrasi dan QE).

- Owner menerima notifikasi pesanan online baru dan stok menipis.
- Kurir menerima notifikasi saat pesanan baru ditugaskan kepadanya.
- Pelanggan menerima notifikasi saat status pesanan berubah atau lokasi pengambilan untuk pesanan baru berubah.
- Notifikasi bersifat sementara/real-time dan tidak disimpan ke tabel domain baru. Database aplikasi tetap 10 tabel; gunakan mekanisme aplikasi yang telah tersedia.
- Notifikasi tidak menggantikan status pesanan atau riwayat status sebagai sumber kebenaran.

**Kriteria penerimaan:** penerima sesuai role dan peristiwa; pengguna tidak menerima data pesanan yang bukan haknya; notifikasi tidak membuat tabel domain ke-11.

# **F15. Dashboard Histori Penjualan dan Ekspor Excel**

**WP:** PPL 29 (UI), PPL 30 (backend/API), PPL 33–42 (integrasi, dokumentasi, dan QE).

- Owner melihat grafik histori penjualan per bulan dan tabel rekap harian pada bulan terpilih.
- Rekap menampilkan jumlah botol dan omzet per varian; data online dan POS digabung.
- Omzet tidak memasukkan ongkos kirim.
- Owner dapat mengekspor rekap harian, mingguan, atau bulanan ke file Excel.
- Data yang dihitung hanya transaksi online yang pembayaran Midtrans-nya terverifikasi dan POS yang pembayarannya dikonfirmasi Owner. Data simulasi diberi label dan tidak dianggap penjualan nyata.
- Pencatatan penjualan harus idempoten agar webhook/request ulang tidak menggandakan jumlah.

**Kriteria penerimaan:** hasil rekap sesuai data sumber untuk online dan POS; ongkir tidak masuk omzet; ekspor mengikuti periode yang dipilih; tidak ada data simulasi dalam laporan transaksi nyata.

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

**Skenario QE akhir (PPL 36 dan PPL 42; seluruh kelompok berikut wajib diuji):** login/akses; kelola produk; katalog; keranjang; checkout pengiriman; checkout ambil sendiri; ongkir dan validasi wilayah; pembayaran Midtrans berhasil/gagal/kedaluwarsa; POS tunai/QRIS; daftar/detail/filter pesanan; penugasan kurir dan pembaruan status; notifikasi web per role; nota digital/Hubungi Penjual; histori penjualan online+POS; ekspor Excel; Moving Average; Rule-Based restok; lokasi/jam operasional; integrasi end-to-end dan penanganan error. Pengujian lokasi tersimpan mencakup tambah/ubah lokasi, pilih lokasi, lokasi nonaktif, dan ongkir memakai koordinat lokasi terpilih. 

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

- _Jadwal padat dan padat QE._ Tidak ada fitur wajib baru di Sprint 4. Pengembangan fitur selesai paling lambat 27/11; QE akhir PPL 42 berlangsung 28–29/11. 

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


# **14. Catatan Konsolidasi PPL 23–42 (11 November 2026)**

Keputusan tambahan yang dimasukkan ke MVP dan dipetakan ke WBS yang sudah ada:

| Work Package | Cakupan final |
|---|---|
| PPL 23–24 | Backend/UI pembayaran Midtrans, status pembayaran, ambil sendiri 24 jam, nota digital, tombol Hubungi Penjual |
| PPL 25–26 | UI/backend titik tujuan dan ongkir, lokasi lapak aktif, jam operasional, validasi koordinat, blokir checkout online di luar jam |
| PPL 27–28 | Dashboard Owner/Kurir, daftar/detail/filter pesanan, akun dan penugasan kurir, pembaruan status, notifikasi web |
| PPL 29–30 | UI/backend Moving Average, dashboard histori penjualan, gabungan online+POS, rekap harian/mingguan/bulanan, pendapatan tanpa ongkir, ekspor Excel |
| PPL 31–32 | UI/backend peringatan restok dan POS Kasir Offline, tunai/QRIS langsung, konfirmasi Owner, pengurangan stok, pencatatan histori |
| PPL 33–42 | QE Sprint 3, integrasi end-to-end, dokumentasi, skenario/regresi, perbaikan bug, penyempurnaan UI, uji ulang pembayaran, integrasi akhir, QE akhir |

**Ketentuan pelaksanaan:** jumlah Work Package tetap 42. PPL 23–42 yang ada menjadi tempat fitur tambahan; tidak menambah PPL baru. Sprint 1–2 tidak diubah. PIC, story point, durasi, dan tanggal tetap mengikuti WBS/Sprint Plan terbaru. Area pengiriman hanya Kota Mojokerto. Pesanan yang telah berhasil dibuat mempertahankan snapshot lokasi. Database domain tetap 10 tabel; notifikasi web tidak menambah tabel domain.


---

# **15. Matriks Keterlacakan WBS dan Panduan Database untuk Fitur**

## 15.1 Keterlacakan User Story → Backlog → WP

Gunakan matriks keterlacakan pada PPL 01 Bagian 13 sebagai matriks induk. Ringkasan fitur PRD ke backlog dan WP:

| Fitur PRD | Backlog | User Story | WP utama |
|---|---|---|---|
| F1 Database & autentikasi | BL-1, BL-3, BL-4 | US-1, US-2, US-7, US-8 | PPL 01–07 |
| F2 Produk | BL-5, BL-7 | US-21, US-22 | PPL 08–09 |
| F3 Katalog | BL-6 | US-9, US-11 | PPL 10–11 |
| F4 Keranjang | BL-9 | US-10 | PPL 13–14 |
| F5 Checkout | BL-10, BL-13, BL-18, BL-19 | US-12, US-13, US-16, US-23, US-28, US-29 | PPL 15–18, 23–26 |
| F6 Pembayaran | BL-12, BL-13 | US-3, US-15, US-16, US-23 | PPL 19, 23–24 |
| F7 Ongkir | BL-11 | US-4, US-5, US-14, US-30 | PPL 19, 25–26 |
| F8 Pesanan & status | BL-14, BL-15, BL-21, BL-22 | US-17, US-26, US-31, US-32 | PPL 14, 27–28 |
| F9 Moving Average | BL-23 | US-5, US-25 | PPL 03, 29–30 |
| F10 Rule-Based | BL-8 | US-5, US-24 | PPL 31–32 |
| F11 Lokasi & jam | BL-18 | US-19, US-28 | PPL 25–26 |
| F12 POS | BL-20 | US-1, US-27 | PPL 31–32 |
| F13 Pickup, nota, kontak | BL-17, BL-19 | US-13, US-20, US-29 | PPL 23–24 |
| F14 Notifikasi | BL-16 | US-18, US-33 | PPL 27–28, 32 |
| F15 Histori & ekspor | BL-24, BL-25 | US-34, US-35 | PPL 29–30 |
| Dashboard Owner | BL-26 | US-36 | PPL 27–32 |

PPL 33–42 mendukung QE, integrasi, regresi, dokumentasi, perbaikan bug, dan rilis semua fitur di atas. Daftar backlog WBS belum memuat ID PPL sebagai kolom; matriks PPL 01 menjadi crosswalk resmi.

## 15.2 Aturan penggunaan database

Struktur database domain tetap sepuluh tabel pada PPL 02. Jangan menambah tabel hanya karena membuat fitur baru.

| Tabel | Fitur yang menggunakannya | Aturan penting |
|---|---|---|
| `users` | Login, role, kurir | Role tetap `admin`, `kurir`, `pelanggan`; maksimal dua akun Kurir aktif; Owner menggunakan role `admin`. |
| `products` | Produk, katalog, POS, stok, Rule-Based | Gunakan `aktif=false` untuk menonaktifkan; jangan hard delete produk yang sudah punya histori transaksi. |
| `cart_items` | Keranjang dan checkout | Keranjang tidak menahan stok; stok divalidasi ulang ketika checkout. |
| `orders` | Online, POS, pickup, pengiriman, status | Set `sumber_transaksi` eksplisit `online`/`pos`; POS `metode_pemenuhan=NULL`, `user_id` boleh NULL; online wajib memilih `pengiriman`/`ambil_sendiri`. Simpan snapshot asal dan penerima. |
| `order_items` | Rincian semua pesanan | Simpan snapshot nama produk, rasa, harga, jumlah, subtotal. |
| `payments` | Midtrans online dan POS | Satu order dapat memiliki banyak percobaan Midtrans; `order_id` tidak unik. `referensi_provider` unik nullable. POS memakai `tunai`/`qris` langsung tanpa referensi provider. |
| `order_status_history` | Semua perubahan status | Kolom final `order_id`, `status_baru`, `diubah_oleh` nullable, `waktu_perubahan`, `keterangan`; tidak ada `status_sebelumnya` atau `user_id`. |
| `sales_history` | POS, transaksi online, dashboard, MA, ekspor | `sumber='transaksi'` untuk penjualan nyata, `simulasi` hanya data uji. Unique key tidak menggantikan idempotensi aplikasi. |
| `settings` | Tarif/ambang, lokasi aktif, jam | Kolom `key`, `value`, `description`; baca `settings.value`, bukan `nilai`. Simpan key `lokasi_pengiriman_aktif_id`, `jam_buka`, `jam_tutup`, `batas_stok_minimum`, `stok_harian_standar`. |
| `shipping_locations` | Lokasi asal, ongkir, pickup | Simpan lokasi asal yang dapat dipilih; `aktif` harus true. Pesanan menyimpan snapshot, tidak membaca lokasi aktif untuk mengubah histori lama. |

**Kolom order yang relevan dengan integrasi:** `sumber_transaksi`, `metode_pemenuhan`, `user_id`, `courier_id`, `status`, `subtotal`, `ongkir`, `total`, `nama_pelanggan`, data penerima, koordinat tujuan, `nama_lokasi_asal`, `alamat_lokasi_asal`, `latitude_lokasi_asal`, `longitude_lokasi_asal`, `jarak_pengiriman`, `batas_pengambilan`, `catatan`, `stok_dikembalikan`. Nama kolom rinci penerima/koordinat wajib dicocokkan dengan migration PPL 02 sebelum coding; jangan menebak alias kolom.

## 15.3 Koreksi redaksional User Story WBS yang wajib diterapkan

- US-21: “menghapus” berarti menonaktifkan produk, bukan hard delete.
- US-18: lokasi aktif baru hanya memengaruhi pesanan baru; snapshot pesanan lama tetap.
- US-32: Owner yang menetapkan `dikirim`; Kurir hanya dapat menetapkan `selesai` untuk tugas yang ditugaskan. Kalimat WBS saat ini perlu dirapikan agar tidak memberi kewenangan status yang salah.
- US-12/US-13: koordinat tujuan hanya wajib untuk pengiriman.
- US-31: batas maksimal dua akun Kurir aktif.

## 15.4 Konsistensi jadwal

PPL 41 adalah akhir pengembangan (paling lambat 27 November 2026). PPL 42 adalah QE akhir pada 28–29 November 2026. Jangan menyebut QE akhir hanya pada 29 November atau pengembangan fitur sampai 28 November.


## 15.5 Penyelarasan ringkasan Sprint 3 di WBS

Deskripsi PPL 23–32 sudah memuat seluruh fitur tambahan, tetapi ringkasan tujuan Sprint 3 pada WBS perlu menyebut secara eksplisit pembayaran Midtrans; pickup/nota/kontak; lokasi aktif, jam, dan ongkir OSM; dashboard Owner/Kurir, daftar pesanan, akun/assignment Kurir; notifikasi; Moving Average; histori penjualan/ekspor; Rule-Based; dan POS. Ini pembaruan redaksional tujuan sprint, bukan perubahan jumlah WP, PIC, poin, durasi, atau jadwal. Ambang restok harus `stok <= 5`.

## 15.6 Redaksi User Story yang perlu dibetulkan pada workbook WBS

- US-18 harus menjelaskan bahwa notifikasi lokasi berlaku untuk lokasi baru/pesanan baru; snapshot lokasi pesanan yang sudah dibuat tidak berubah.
- US-32 harus menjelaskan bahwa Owner menetapkan status `dikirim`, sedangkan Kurir yang ditugaskan hanya menetapkan `selesai`.
- Tambahkan kolom `Work Package (PPL)` pada sheet `DAFTAR BACKLOG` menggunakan crosswalk PPL 01 Bagian 13 agar tiap backlog dapat ditelusuri langsung ke WP.
