# **PRODUCT REQUIREMENTS DOCUMENT (PRD)** 

# **Sistem Penjualan Online UMKM Kopi Tiga Titik** 

**Versi:** 1.0 | **Penyusun:** Orang 1 (Product Owner + System Analyst) **Acuan jadwal, PIC, dan ID Work Package:** file Kopi_Tiga_Titik_Sprint_Plan_Revisi (PPL 01–PPL 42) 

**Periode proyek:** 28 Oktober – 29 November 2026 

Setiap bagian fitur di Bab 9 bisa diserahkan langsung ke PIC-nya. Parameter yang belum final ditandai **[PARAMETER]** dan terkumpul di Bab 12. 

# **1. Ringkasan Produk** 

**1.1 Latar belakang.** Kopi Tiga Titik adalah usaha street coffee di Mojokerto (berdiri Juli 2026) yang menjual kopi botol 250 ml dalam 4 varian. Pesanan online masih lewat WhatsApp dan direkap manual di Excel, sehingga rawan salah catat. Ongkir dipukul rata dan pembayaran dicek manual. 

**1.2 Tujuan produk.** Aplikasi web mobile-first yang menjalankan alur: katalog → keranjang → checkout → pembayaran sandbox terverifikasi → ongkir berbasis jarak rute → daftar pesanan dan status. Aplikasi juga memberi estimasi penjualan (Moving Average) dan peringatan restok (rule-based). 

# **1.3 Indikator keberhasilan (dapat diverifikasi).** 

- 11 fitur wajib selesai dikembangkan paling lambat 27/11/2026. 

- Sprint 1–3 lulus QE tanpa bug kritis (QE 3–4/11, 11–12/11, 19–20/11). QE akhir 28 –29/11 lulus untuk 12 alur. 

- Tidak ada pesanan berstatus “dibayar” tanpa verifikasi backend. 

- Stok tidak pernah negatif, dan kembali tepat saat pesanan batal atau kedaluwarsa. 

- Ongkir dihitung dari jarak rute dan parameter tarif yang dapat dikonfigurasi. 

- Dokumentasi teknis dan panduan penggunaan tersedia (PPL 35). 

# **2. Ruang Lingkup** 

# **2.1 Dalam cakupan (11 fitur wajib).** 

1. Database dan autentikasi/akses pengguna. 

2. Pengelolaan produk, harga, stok, dan foto. 

3. Katalog produk pelanggan. 

4. Keranjang belanja. 

5. Checkout dan pembuatan pesanan. 

6. Pembayaran API sandbox dengan verifikasi backend. 

7. Ongkir berbasis jarak rute (data OpenStreetMap dan layanan routing), tarif dapat dikonfigurasi. 

8. Daftar pesanan dan status. 

9. Moving Average penjualan 3 minggu terakhir. 

- 10.Rule-Based peringatan restok (stok < 5). 

11.Validasi, penanganan error, integrasi, pengujian, dan dokumentasi. 

# **2.2 Di luar cakupan (tidak dikerjakan kecuali diminta).** 

- Scoring lima produk terlaris. 

- Fitur yang pernah dibahas di wawancara tetapi tidak ada di daftar wajib maupun Work Package: akun peran kurir dan penugasan kurir, penjualan offline di lapak, pengaturan lokasi/jam lapak, opsi ambil sendiri, notifikasi web, nota dan tombol hubungi penjual, dashboard penjualan, ekspor Excel, rekomendasi jumlah produksi (10 − sisa). 

- COD, notifikasi WhatsApp/email, lupa kata sandi, dan verifikasi email. 

# **3. Pengguna dan Peran** 

|**Peran**|**Deskripsi**|**Akses utama**|
|---|---|---|
|Pelanggan|Pembeli (perwakilan: Nabil), wajib<br>punya akun|Katalog, keranjang, checkout,<br>pembayaran|
||Pemilik Kopi Tiga Titik (Ilham dan dua|Kelola produk, daftar pesanan|
|Admin/Penjual|pemilik lain). Satu akun dipakai<br>bergantian, dibuat lewat seeder|dan status, estimasi MA,<br>peringatan restok|



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
|F11 Validasi, error,<br>integrasi, QE,<br>dokumentasi<br> <br> <br> <br>|PPL 04, PPL 12 (O2), PPL 20 (O2, 5/11), PPL 21 (O2, 6–8/11), PPL<br>22 (O2), PPL 33 (O2), PPL 34 (O1, 21–23/11), PPL 35 (O1, 24–<br>26/11), PPL 36–37 (O2, 21–24/11), PPL 39 (O3, 25–27/11), PPL<br>42 (O2, 28–29/11)|
|Lintas fitur (UI<br>responsif)<br>|PPL 05 (O3, 28–29/10)|
|**Sprint Pengembangan**|**QE**|
|1<br>28/10 – 2/11|3–4/11 (PPL 12)|
|2<br>5/11 – 10/11|11–12/11 (PPL 22)|
|3<br>13/11 – 18/11|19–20/11 (PPL 33)|
|4<br>21/11 – 27/11|28–29/11 (PPL 42)|



# **5. Aturan Bisnis Global** 

- **BR-1 Produk.** 4 varian botol 250 ml: Titik Hitam Rp8.000, Titik Temu Rp10.000, Titik Manja Rp10.000, Titik Jenuh Rp12.000. Keawetan 5–7 hari ditulis di deskripsi singkat. Komposisi tampil sebagai daftar bahan tanpa takaran. 

- **BR-2 Pesanan.** Pesanan dilayani dari stok hari itu (bukan pre-order). Tidak ada batas jumlah per pelanggan. Pelanggan wajib punya akun. 

- **BR-3 Pembayaran.** Pembayaran hanya lewat API sandbox. Status pembayaran menjadi dibayar hanya setelah backend memverifikasi notifikasi penyedia. 

- **BR-4 Batas waktu.** Pesanan belum dibayar kedaluwarsa dalam **[PARAMETER] batas waktu pembayaran** (default 10 menit sesuai wawancara 22/10, dapat dikonfigurasi). 

- **BR-5 Stok.** Stok berkurang saat pesanan dibuat (status menunggu_pembayaran). Stok dikembalikan satu kali jika pesanan dibatalkan atau kedaluwarsa. Stok tidak boleh negatif. 

- **BR-6 Pembatalan.** Pesanan belum dibayar boleh dibatalkan pelanggan. Pesanan yang sudah dibayar tidak dapat dibatalkan atau diubah, dan peringatannya tampil sebelum membayar. 

- **BR-7 Ongkir.** Dihitung dari jarak rute OSM (titik asal ke titik tujuan) dengan parameter tarif terkonfigurasi (Bab 9, F7). 

- **BR-8 Wilayah.** Pengiriman hanya untuk Kota dan Kabupaten Mojokerto, diperiksa dari alamat hasil titik lokasi. 

- **BR-9 Moving Average.** Rata-rata penjualan 3 minggu terakhir per varian. Hanya saran, tidak mengubah stok. 

- **BR-10 Restok.** Stok < 5 berstatus peringatan restok. Stok = 5 dan stok > 5 tidak diperingatkan. 

- **BR-11 Teknis.** Uang disimpan sebagai integer Rupiah (tanpa desimal). Zona waktu Asia/Jakarta. Penanganan pesanan tumpah atau rusak dilakukan di luar aplikasi. 

# **6. Arsitektur dan Konvensi Teknis** 

**6.1 Stack.** Laravel (backend, Blade/views), Tailwind CSS (UI mobile-first), Leaflet + OpenStreetMap (peta), layanan routing berbasis OSM (dipilih di PPL 19), satu penyedia pembayaran sandbox: Midtrans, Xendit, atau Duitku (dipilih di PPL 19). 

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

# **6.5 Konvensi Git dan ID.** 

- ID Work Package di dokumen: PPL 01, PPL 02, dst. 

- Branch: feature/PPL-01-nama-singkat. Commit: [PPL-01] pesan. Judul PR: [PPL-01] judul. Spasi pada ID diganti tanda hubung agar valid di Git. 

- Perubahan masuk main hanya lewat pull request yang direview minimal satu anggota lain. 

**6.6 Standar UI.** Mobile-first (1 kolom di HP, grid di desktop). Komponen konsisten dari UI Kit (PPL 05). Setiap form menampilkan pesan error per field. Format uang Rp12.000. 

# **7. Model Data** 

- **users:** id, name, email (unik), password (hash), role (admin/pelanggan), timestamps. 

- **products:** id, name, price (int), stock (int ≥ 0), description (termasuk keawetan), ingredients, photo_path, is_active, soft delete. 

- **stock_movements:** id, product_id, order_id (nullable), type (reservasi/pengembalian/penyesuaian), quantity, note, created_at. Unik pada (order_id, product_id, type) untuk reservasi dan pengembalian agar pengembalian stok tidak ganda. 

- **carts / cart_items:** user_id, product_id, quantity (unik per user dan produk). 

- **orders:** id, order_number (unik), user_id, status, payment_status, subtotal, shipping_cost, total, recipient_name, recipient_phone, address_detail, address_full, dest_lat, dest_lng, distance_km, notes, expires_at, paid_at, timestamps. 

- **order_items:** order_id, product_id, product_name, unit_price, quantity, line_total (snapshot harga dan nama). 

- **payments:** id, order_id, provider, provider_transaction_id (unik), method, status, amount, expires_at, paid_at, raw_payload (json). 

- **payment_events:** id, payment_id, event_key (unik), payload (audit dan idempotensi webhook). 

- **simulated_sales:** product_id, week_start, quantity, label simulasi (data historis simulasi untuk MA). 

- **settings:** key, value (shipping.base_fee, shipping.per_km_fee, shipping.free_distance_km, shipping.rounding, shipping.origin_lat, shipping.origin_lng, shipping.region, payment.expiry_minutes, restock.threshold). 

# **8. Status dan Transisi** 

- **Status pesanan:** menunggu_pembayaran, diproses, dikirim, selesai, dibatalkan. 

- **Status pembayaran:** pending, dibayar (label UI: Berhasil), gagal, kedaluwarsa. 

|**Dari**<br>**Ke**|**Pemicu**|**Pelaku**|
|---|---|---|
|(baru)<br>menunggu_pembayaran|<sup>Checkout selesai,</sup><br>stok berkurang|<br>Pelanggan|
||Pembayaran||
|menunggu_pembayaran diproses|terverifikasi<br>(dibayar)|Sistem (webhook)|
|menunggu_pembayaran dibatalkan|Dibatalkan<br>pelanggan, atau|Pelanggan/Sistem|



|**Dari**|**Ke**|**Pemicu**|**Pelaku**|
|---|---|---|---|
|||kedaluwarsa (stok<br>kembali)||
|diproses|dikirim|Admin mengirim<br>pesanan|Admin|
|dikirim|selesai|Pesanan diterima|Admin|



Transisi lain ditolak backend dengan error 409/422 (misalnya membatalkan pesanan yang sudah dibayar). Pembayaran gagal tidak membatalkan pesanan; pelanggan dapat membuat percobaan pembayaran baru selama pesanan belum kedaluwarsa. 

# **9. PRD per Fitur** 

# **F1. Database dan Autentikasi/Akses** 

**Tujuan:** fondasi data terpadu dan akses sesuai peran. **WP:** PPL 01–04, 06, 07 (Dep PPL 07: PPL 02, 06). 

**Alur:** pelanggan mendaftar, login, lalu diarahkan ke katalog. Admin login, lalu diarahkan ke /admin/orders. Guest yang membuka checkout diarahkan ke login dan kembali ke halaman semula setelah masuk. 

# **Kebutuhan fungsional:** 

- FR-1.1 Skema database sesuai Bab 7 dibuat lewat migrasi Laravel dengan foreign key dan index (PPL 02). 

- FR-1.2 Seeder (PPL 03): akun admin (kata sandi dari .env), 4 varian produk beserta bahan dan keawetan, stok awal 10 per varian (acuan standar stok harian dari wawancara), data simulasi penjualan minimal 3 minggu per varian berlabel simulasi, nilai awal settings. 

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

- FR-2.4 Ubah stok dengan mengisi nilai baru. Setiap perubahan dicatat di stock_movements (type penyesuaian). 

- FR-2.5 Hapus produk: produk yang pernah dipesan dinonaktifkan (is_active=false), bukan dihapus fisik. Tampil dialog konfirmasi. 

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

- FR-5.3 Pembuatan pesanan dalam satu transaksi database: kunci baris produk (lockForUpdate), cek stok, kurangi stok, catat stock_movements (reservasi), buat orders dan order_items (snapshot harga dan nama), isi expires_at = waktu sekarang + batas waktu pembayaran, kosongkan keranjang. Jika salah satu gagal, seluruh transaksi di-rollback. 

- FR-5.4 Stok tidak cukup: pesanan ditolak dan item bermasalah disebutkan. 

- FR-5.5 Nomor pesanan unik (usulan format KTT-YYYYMMDD-NNNN). 

- FR-5.6 Mencegah submit ganda (tombol dinonaktifkan dan token idempoten). 

- FR-5.7 Pembatalan oleh pelanggan sebelum dibayar mengembalikan stok satu kali (BR-5, BR-6). 

- FR-5.8 Hingga F7 terintegrasi (Sprint 3), shipping_cost bernilai 0 sebagai placeholder dan total = subtotal. 

**Validasi:** nama wajib; nomor kontak wajib dengan format telepon Indonesia; detail alamat wajib; titik lokasi wajib (lat/lng valid, Bab F7); keranjang tidak kosong. **Kriteria penerimaan:** stok berkurang tepat sesuai jumlah; dua pelanggan memesan botol terakhir bersamaan hanya satu yang berhasil; total sama dengan penjumlahan item (+ ongkir); kegagalan tengah proses tidak meninggalkan stok yang terpotong. **Uji QE:** pesanan normal, stok tidak cukup, dua sesi paralel, rollback saat error, batas waktu pembayaran. 

# **F6. Pembayaran API Sandbox dengan Verifikasi Backend** 

**WP:** PPL 19 (kontrak integrasi, Orang 1; Dep PPL 17), PPL 23 (backend; Dep PPL 16, 18, 19), PPL 24 (UI; Dep PPL 23), PPL 40–41 (uji ulang dan keamanan). 

**Alur:** pesanan dibuat → halaman bayar membuat transaksi di penyedia → pelanggan membayar → penyedia memanggil webhook → backend memverifikasi → status dibayar dan pesanan diproses. 

# **Kebutuhan fungsional:** 

- FR-6.1 PPL 19 menetapkan satu penyedia, parameter, kontrak endpoint, format webhook, dan mekanisme verifikasi. Hasilnya didokumentasikan. 

- FR-6.2 Transaksi pembayaran dibuat dari server (kunci penyedia hanya di .env) dengan nominal = orders.total dan waktu kedaluwarsa = orders.expires_at. 

- FR-6.3 Halaman pembayaran menampilkan instruksi atau kode pembayaran, nominal, hitung mundur, dan status (Pending/Gagal/Kedaluwarsa/Berhasil). Status diperbarui lewat polling ke /api/orders/{id}/payment-status. 

- FR-6.4 Endpoint webhook memverifikasi keaslian notifikasi sesuai mekanisme penyedia (tanda tangan/token) dan/atau memeriksa status transaksi langsung ke API penyedia dari server. Notifikasi yang tidak lolos ditolak dan dicatat. 

- FR-6.5 Status dibayar hanya diset jika: notifikasi terverifikasi, order_number cocok, nominal sama dengan orders.total, dan pesanan masih menunggu_pembayaran. Redirect atau data dari browser tidak pernah dipercaya. 

- FR-6.6 Pemrosesan webhook idempoten: notifikasi duplikat tidak mengubah data dua kali (payment_events.event_key unik). 

- FR-6.7 Pembayaran gagal: pesanan tetap menunggu_pembayaran, pelanggan dapat membuat percobaan baru selama belum kedaluwarsa. 

- FR-6.8 Kedaluwarsa: command orders:expire (dijadwalkan tiap menit) dan pengecekan saat halaman dibuka mengubah pesanan menjadi dibatalkan, pembayaran kedaluwarsa, dan mengembalikan stok satu kali. 

- FR-6.9 Notifikasi sukses yang datang setelah pesanan kedaluwarsa tidak mengaktifkan kembali pesanan. Kejadian dicatat dan ditandai di detail admin untuk penanganan manual di luar aplikasi. 

- FR-6.10 Perubahan pembayaran dan pesanan dilakukan dalam satu transaksi database. 

**Catatan implementasi:** penyedia sandbox butuh URL webhook yang dapat dijangkau publik. Saat pengembangan lokal pakai tunnel publik (misalnya ngrok atau Cloudflare Tunnel). 

**Kriteria penerimaan:** simulasi pembayaran berhasil di sandbox mengubah status menjadi dibayar dan pesanan diproses; webhook palsu (tanpa tanda tangan atau 

nominal salah) tidak mengubah apa pun; notifikasi ganda tidak menimbulkan efek ganda; pesanan kedaluwarsa mengembalikan stok. **Uji QE (PPL 40):** berhasil, pending, gagal, kedaluwarsa, webhook ganda, webhook palsu, nominal tidak cocok, notifikasi terlambat. 

# **F7. Ongkir Berbasis Jarak Rute (OpenStreetMap)** 

**WP:** PPL 19 (parameter tarif dan routing), PPL 25 (UI; Dep PPL 15, 19), PPL 26 (backend; Dep PPL 16, 19), PPL 38 (perbaikan dan uji ulang). 

# **Kebutuhan fungsional:** 

- FR-7.1 Pelanggan memilih titik tujuan di peta (PPL 25). Rincian ongkir tampil di checkout setelah titik dipilih. 

- FR-7.2 Endpoint /api/shipping/quote menerima lat dan lng tujuan, mengambil jarak rute dari layanan routing berbasis OSM (asal = shipping.origin_lat/lng), lalu menghitung ongkir. 

- FR-7.3 Formula: 

   - Jarak rute dikonversi ke km lalu dibulatkan sesuai shipping.rounding (default: km terdekat) menjadi D. 

   - Jika D ≤ free_distance_km → ongkir = 0. 

   - Jika tidak → ongkir = base_fee + per_km_fee × (D − free_distance_km), dibulatkan ke integer Rupiah. 

- FR-7.4 Semua parameter tarif dibaca dari settings, tidak ada angka tarif di kode. Mengubah parameter tidak membutuhkan perubahan kode. 

- FR-7.5 Validasi koordinat: lat dan lng numerik, lat −90..90, lng −180..180, tidak kosong. Titik harus berada di wilayah layanan Kota atau Kabupaten Mojokerto, diperiksa lewat reverse geocoding (patuhi kebijakan pemakaian Nominatim: jeda antar-permintaan dan User-Agent). Jika wilayah tidak dapat diverifikasi, pesanan ditolak dengan pesan jelas. 

- FR-7.6 Penanganan error: jika layanan routing gagal, timeout, atau tidak menemukan rute, tampil pesan yang jelas dengan tombol coba lagi. Checkout tidak boleh lanjut dengan ongkir tidak valid. Tidak ada ongkir tebakan atau jarak garis lurus sebagai pengganti. Kegagalan dicatat di log. 

- FR-7.7 Ongkir yang dihitung disimpan di orders.shipping_cost dan distance_km, lalu dikunci saat pesanan dibuat. Backend menghitung ulang ongkir saat checkout dan tidak mempercayai nilai dari browser. 

**Parameter belum final (wajib dikonfirmasi):** base_fee, per_km_fee, free_distance_km, rounding, titik asal (origin_lat/lng). Sebagai data uji dan nilai awal seeder dipakai acuan wawancara 22/10 (gratis sampai 5 km, Rp2.000/km di atas 5 km, dibulatkan ke km terdekat) dan koordinat uji yang ditandai sebagai data uji. Semuanya bukan angka final. **Kriteria penerimaan:** perubahan parameter di settings mengubah hasil ongkir tanpa mengubah kode; layanan routing yang dimatikan menghasilkan pesan error, bukan crash; titik di luar wilayah ditolak. 

**Uji QE:** jarak di bawah, tepat di, dan di atas ambang gratis; titik tidak valid; wilayah luar Mojokerto; layanan routing down. 

# **F8. Daftar Pesanan dan Status (Admin)** 

**WP:** PPL 27 (UI; Dep PPL 17), PPL 28 (backend; Dep PPL 16, 17). 

# **Kebutuhan fungsional:** 

- FR-8.1 Daftar pesanan terbaru di atas, dengan filter status (Semua, Menunggu Pembayaran, Diproses, Dikirim, Selesai, Dibatalkan) dan paginasi. 

- FR-8.2 Detail pesanan: nomor, waktu, item (nama, harga, jumlah), subtotal, ongkir, total, data penerima, alamat, titik peta (tautan ke peta), catatan, status pesanan, status pembayaran, paid_at. 

- FR-8.3 Admin mengubah status hanya sesuai Bab 8 (diproses → dikirim → selesai). Transisi lain ditolak dengan pesan jelas. 

- FR-8.4 Status pembayaran hanya tampil (read-only). Hanya sistem yang mengubahnya lewat F6. 

- FR-8.5 Setiap perubahan status mencatat waktu (status_logs atau kolom timestamp). 

- FR-8.6 Halaman hanya untuk admin. Pelanggan melihat status pesanannya sendiri pada halaman pembayaran/hasil pesanan (PPL 24). Halaman riwayat pelanggan tidak dijadwalkan. 

**Kriteria penerimaan:** pesanan baru dari checkout langsung muncul; filter benar; transisi tidak valid ditolak; pesanan sudah dibayar tidak bisa dibatalkan. **Uji QE:** semua filter, semua transisi valid dan tidak valid, akses non-admin. 

# **F9. Moving Average 3 Minggu** 

**WP:** PPL 03 (data simulasi), PPL 29 (UI; Dep PPL 03), PPL 30 (backend; Dep PPL 03, 17). 

**Rumus:** estimasi minggu berikutnya varian v = (P₁ + P₂ + P₃) / 3, dengan P₁–P₃ jumlah botol terjual varian v pada tiga minggu penuh terakhir (Senin–Minggu, tidak termasuk minggu berjalan). 

# **Kebutuhan fungsional:** 

- FR-9.1 Sumber data: penjualan nyata (order_items dari pesanan berstatus dibayar, tidak termasuk yang dibatalkan) dan data simulasi berlabel. Data simulasi dipakai untuk minggu yang belum punya data nyata dan selalu diberi label. 

- FR-9.2 Halaman admin /admin/forecast menampilkan per varian: penjualan 3 minggu terakhir, estimasi minggu berikutnya (satu desimal) dan kebutuhan stok yang disarankan (dibulatkan ke atas), serta label sumber data. 

- FR-9.3 Jika data kurang dari 3 minggu, tampil “Data belum cukup” tanpa perhitungan. 

- FR-9.4 Estimasi hanya saran. Tidak mengubah stok maupun target. 

- FR-9.5 Rumus dan contoh perhitungan didokumentasikan (PPL 30, PPL 35). 

**Contoh uji (ilustrasi):** Titik Temu 60, 70, 80 botol → (60 + 70 + 80) / 3 = 70. **Kriteria penerimaan:** hasil sesuai hitungan manual untuk data uji; label simulasi tampil; kasus kurang dari 3 minggu menampilkan pesan. 

**Uji QE:** data uji dengan hasil diketahui, batas pergantian minggu, varian tanpa penjualan (hasil 0), data campuran nyata dan simulasi. 

# **F10. Rule-Based Peringatan Restok** 

**WP:** PPL 31 (UI; Dep PPL 09), PPL 32 (backend; Dep PPL 09, 18). 

**Aturan:** stok < restock.threshold (default 5) → status “Stok Menipis/Segera Restok”. Stok = 5 dan stok > 5 tidak diperingatkan. Stok 0 ditampilkan sebagai “Habis” (tombol pesan di katalog nonaktif, F3). 

# **Kebutuhan fungsional:** 

- FR-10.1 Aturan dievaluasi dari stok terkini setiap halaman dibuka (tanpa tabel notifikasi). 

- FR-10.2 UI: lencana di daftar produk admin dan panel “Peringatan Restok” di /admin/restock-alerts berisi produk yang stoknya di bawah ambang, beserta nama dan stok saat ini. 

- FR-10.3 Ambang dibaca dari settings. 

- FR-10.4 Peringatan hilang otomatis setelah stok dinaikkan ke ≥ 5. 

**Kriteria penerimaan:** stok 4 memperingatkan; stok 5 tidak; stok 6 tidak; pengurangan stok oleh pesanan memicu peringatan di tampilan berikutnya. **Uji QE (PPL 32):** nilai batas 4, 5, 6, dan 0; perubahan ambang di settings. 

# **F11. Validasi, Penanganan Error, Integrasi, Pengujian, Dokumentasi** 

**WP:** PPL 20 (UI pesan error; Dep PPL 05), PPL 21 (backend; Dep PPL 07, 09, 11), PPL 12/22/33/42 (QE), PPL 34 (integrasi end-to-end), PPL 35 (dokumentasi), PPL 36–37 (persiapan pengujian akhir), PPL 39 dan PPL 41 (penyempurnaan dan kesiapan demo). 

# **Kebutuhan fungsional:** 

- FR-11.1 Semua input divalidasi di backend (Form Request). Pesan error berbahasa Indonesia, per field, dan tidak menghapus data yang sudah diisi. 

- FR-11.2 Penanganan error global: halaman 403, 404, 419, 500 yang rapi (PPL 20). Respons JSON untuk endpoint /api/* dengan kode status dan pesan yang konsisten. Detail teknis (stack trace) tidak tampil ke pengguna (APP_DEBUG=false di produksi). 

- FR-11.3 Error layanan eksternal (pembayaran, routing, geocoding) ditangkap, dicatat di log, dan ditampilkan sebagai pesan ramah. 

- FR-11.4 Setiap operasi multi-tabel (checkout, pembayaran, pembatalan) memakai transaksi database. 

- FR-11.5 Integrasi alur beli end-to-end dari katalog sampai stok diuji dan diperbaiki (PPL 34). 

- FR-11.6 Dokumentasi teknis dan panduan penggunaan (PPL 35): setup (README: env, migrasi, seeder, menjalankan scheduler dan tunnel), endpoint dan contoh payload, rumus Moving Average, aturan restok, parameter tarif ongkir, serta panduan Pelanggan dan Admin. 

- FR-11.7 Setiap fitur memiliki dokumentasi singkat dan hasil pengujian yang dicatat (DoD). 

**Kriteria penerimaan:** tidak ada error 500 tanpa penanganan pada skenario QE; semua pesan validasi tampil per field; dokumentasi memungkinkan orang lain menjalankan sistem dari nol. 

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

**12 alur QE akhir (usulan, PPL 36 dan PPL 42):** (1) login, daftar, dan hak akses; (2) kelola produk; (3) katalog; (4) keranjang; (5) checkout; (6) ongkir; (7) pembayaran berhasil; (8) pembayaran gagal atau kedaluwarsa; (9) daftar pesanan dan status; (10) Moving Average; (11) Rule-Based restok; (12) integrasi lintas fitur dan penanganan error. 

# **Definition of Done (setiap Work Package pengembangan):** 

1. UI dan backend/API fitur terintegrasi. 

2. Fungsi utama berjalan sesuai kebutuhan. 

3. Pengujian fitur dilakukan dan hasilnya dicatat. 

4. Tidak ada bug kritis; bug minor dicatat jelas. 

5. Kode digabungkan ke repositori yang disepakati. 

6. Dokumentasi terkait diperbarui. 

7. Integrasi dengan fitur terkait diperiksa. 

8. Fitur siap menjalani QE. 

# **12. Parameter Belum Final dan Risiko** 

|**Parameter**|**Status**|**Penanggung**<br>**jawab**|
|---|---|---|
|Tarif ongkir (base_fee, per_km_fee,<br>free_distance_km, rounding)|<br>Belum final, wajib dikonfirmasi.<br>Nilai wawancara dipakai hanya<br>sebagai data uji|Orang 1|
|Titik koordinat asal (rumah<br>produksi)|Menunggu data dari pemilik.<br>Koordinat uji ditandai|Orang 1|
|Penyedia pembayaran sandbox|Dipilih tim di PPL 19|Orang 1|
|Layanan routing OSM|Dipilih di PPL 19|Orang 1|
|Batas waktu pembayaran|Default 10 menit, dapat<br>dikonfigurasi|Orang 1|
|Logo, warna, dan font Kopi Tiga<br>Titik|Dari pemilik, menyusul|Orang 1|



# **Risiko dan mitigasi:** 

- _Webhook sandbox tidak terjangkau saat pengembangan lokal._ Siapkan tunnel publik sejak PPL 19. 

- _Batas pemakaian layanan gratis (routing, geocoding)._ Jaga jumlah permintaan, patuhi kebijakan, dan tangani error (FR-7.6). 

- _Tarif belum final._ Sudah ditangani lewat parameter di settings. 

- _Jadwal padat dan padat QE._ Tidak ada fitur wajib baru di Sprint 4. Semua pengembangan selesai paling lambat 27/11. 

- _Konflik merge antar PIC._ Gunakan PR kecil dan konvensi branch di Bab 6.5. 

- _Data simulasi dikira data nyata._ Label simulasi wajib tampil di UI dan dokumentasi. 

