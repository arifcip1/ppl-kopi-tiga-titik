# PPL 01 - Analisis Kebutuhan, Aturan Bisnis & Kontrak Integrasi Antarmodul

Oct 28, 2026 · @adalahpokonya

## Ringkasan dan ruang lingkup

Dokumen ini menetapkan kebutuhan, aturan bisnis, dan kontrak data antarmodul Sistem Penjualan Kopi Tiga Titik sebagai dasar PPL 02 (ERD dan migrasi) serta semua Work Package berikutnya. Isinya disusun dari wawancara dengan pemilik usaha dan pembeli (Nabil, 23 Oktober 2026) serta keputusan ruang lingkup proyek; yang belum final dicatat sebagai usulan untuk disepakati tim pada PPL 01 (28 Oktober 2026). Rujukan wawancara ditulis P-nn untuk pembeli (nomor pertanyaan) dan O-n untuk pemilik (nomor bagian).

Fitur wajib (semuanya harus selesai dikembangkan paling lambat 27 November 2026):

1. Fondasi: database dan migrasi, autentikasi, hak akses berbasis peran, validasi input, penanganan error, dokumentasi, pengujian.
2. Kelola produk oleh Admin/Penjual: tambah, ubah, hapus, harga, stok, foto.
3. Katalog produk untuk pelanggan: daftar, informasi, harga, ketersediaan stok, pilih produk.
4. Keranjang: tambah, ubah jumlah, hapus, subtotal dan total sementara, validasi terhadap stok.
5. Checkout: data pelanggan, item, harga, total, informasi pengiriman, ongkir, informasi pembayaran, pembuatan pesanan.
6. Pembayaran lewat API sandbox dengan verifikasi backend.
7. Ongkir berdasarkan jarak rute dari data OpenStreetMap.
8. Daftar pesanan dan status untuk Admin/Penjual.
9. Moving Average 3 minggu.
10. Rule-Based peringatan restok (stok < 5).

Di luar ruang lingkup: Scoring lima produk terlaris dan diskon otomatis 10% untuk transaksi minimal Rp200.000 (pemilik juga menyatakan belum ada sistem diskon, O-3). Temuan wawancara lain yang belum masuk ruang lingkup dicatat di bagian "Temuan wawancara di luar ruang lingkup".

## Aktor dan hak akses

Sistem punya tiga peran terautentikasi, Owner (Admin/Penjual), Kurir, dan Pelanggan; hak akses ditegakkan di backend, bukan hanya dengan menyembunyikan menu.

| Aktor | Boleh | Tidak boleh |
| --- | --- | --- |
| Owner (Admin/Penjual) | Kelola produk (tambah, ubah, aktif/nonaktif, harga, stok, foto); lihat daftar dan detail semua pesanan; mengubah status pesanan sampai Pesanan Dikirim; menugaskan kurir; melihat status pembayaran, Moving Average, dan peringatan restok | Mengubah status pembayaran menjadi berhasil secara manual; membatalkan pesanan yang sudah dibayar melalui fitur pembatalan |
| Kurir | Melihat hanya pesanan yang ditugaskan kepadanya (alamat, titik peta, kontak penerima, catatan) dan menandai Selesai setelah pesanan diantar dan diterima pembeli | Mengelola produk; melihat pesanan kurir lain; mengubah status selain Selesai |
| Pelanggan | Daftar dan masuk; lihat katalog; kelola keranjang sendiri; checkout; membayar; melihat pesanan sendiri | Membuka data pelanggan lain; fungsi Owner dan Kurir |
| Tamu (belum masuk) | Melihat katalog (usulan, perlu disepakati) | Keranjang, checkout, pembayaran |

Akun Admin hanya dipegang pemilik (O-6). Akun Kurir dibuat oleh Admin/Owner. Setiap pesanan memiliki paling banyak satu kurir aktif; satu kurir dapat menerima banyak pesanan. Kurir hanya boleh mengakses pesanan yang ditugaskan kepadanya.

## Kebutuhan fungsional per modul

Dua belas kebutuhan di bawah menutup seluruh fitur wajib; kolom User Story merujuk nomor di sheet USER STORY (US 2 dan US 24 disesuaikan: tanpa peran Kurir, ambang restok < 5).

| Kode | Modul | Kebutuhan | User Story | Wawancara |
| --- | --- | --- | --- | --- |
| KF-01 | Basis data | Semua data pesanan dan penjualan tersimpan dalam satu basis data terpadu, dibuat lewat migrasi dan seeder; pencatatan manual di Excel yang rawan salah diganti | US 1 | O-1 |
| KF-02 | Autentikasi dan akses | Daftar (nama, email, kata sandi), masuk, keluar; sesi tetap tersimpan agar tidak login berulang; akses dibatasi menurut tiga peran (Owner, Kurir, Pelanggan) | US 2, 8 | P-14, P-38, O-6 |
| KF-03 | Kelola produk | Admin/Penjual menambah, mengubah, mengaktifkan, dan menonaktifkan produk beserta harga, stok, foto, komposisi, deskripsi rasa, dan keawetan | US 21, 22 | O-2 |
| KF-04 | Katalog | Pelanggan melihat foto, nama, harga, komposisi, deskripsi rasa, keawetan 5-7 hari, dan jumlah stok; tombol pesan nonaktif saat stok 0 | US 9, 11 | O-2, P-12, P-16, P-18 |
| KF-05 | Keranjang | Tambah, ubah jumlah per varian, hapus; tampil subtotal dan total sementara; jumlah divalidasi terhadap stok | US 10 | P-38, P-40 |
| KF-06 | Checkout | Nama penerima, nomor yang dapat dihubungi, detail alamat, titik lokasi di peta (geser pin atau gunakan lokasi saya), catatan khusus; ringkasan pesanan dengan ongkir sebelum membayar; membuat pesanan | US 12, 14 | O-6, P-19, P-20, P-22 |
| KF-07 | Pembayaran sandbox | Pembayaran QRIS lewat satu penyedia (Midtrans, Xendit, atau Duitku) dalam mode sandbox, status diverifikasi backend | US 3, 15 | O-5, P-27, P-28 |
| KF-08 | Ongkir OSM | Ongkir dihitung dari jarak rute rumah produksi ke tujuan memakai data OpenStreetMap, ditampilkan saat checkout | US 4, 14 | O-1, O-4, P-22 |
| KF-09 | Stok dan transaksi | Stok ditahan saat pesanan dibuat dan dikembalikan bila gagal, kedaluwarsa, atau dibatalkan | US 23 | P-40 |
| KF-10 | Daftar pesanan dan status | Admin/Penjual melihat daftar dan detail, memantau status pesanan dan pembayaran, mengubah status sampai Pesanan Dikirim; Kurir menekan Selesai setelah pesanan diterima pembeli | US 17, 26 | O-6, P-33 |
| KF-11 | Moving Average | Estimasi penjualan minggu berikutnya dari rata-rata 3 minggu terakhir untuk mengantisipasi lonjakan permintaan | US 25 | O-3 |
| KF-12 | Rule-Based restok | Peringatan restok saat stok < 5, dengan saran produksi mengacu standar stok harian 10 | US 24 | O-3 |

### Data awal 4 varian produk (dari wawancara pemilik, ukuran 250 ml)

| Varian | Rasa | Harga |
| --- | --- | --- |
| Titik Jenuh | Coklat susu | Rp12.000 |
| Titik Manja | Kopi karamel | Rp10.000 |
| Titik Temu | Kopi gula aren | Rp10.000 |
| Titik Hitam | Americano | Rp8.000 |

Keawetan 5-7 hari bila disimpan di lemari pendingin. Rekap penjualan 3 minggu terakhir tidak ada (O-3), sehingga PPL 03 membuat data simulasi penjualan yang diberi label jelas.

### Cakupan antarmuka dashboard berdasarkan Sprint Planning

Pembagian pekerjaan UI dashboard ditetapkan sebagai berikut. Bagian ini menjelaskan penempatan pekerjaan pada Work Package dan tidak menambah aktor atau aturan bisnis baru.

| Role | Cakupan antarmuka | Work Package |
|---|---|---|
| Pelanggan | Dashboard/beranda pelanggan, navigasi katalog, keranjang, riwayat pesanan, dan pemantauan status pesanan miliknya | PPL 14 |
| Owner (Admin/Penjual) | Dashboard operasional, ringkasan pesanan dan pembayaran, daftar/detail pesanan, filter status, serta informasi stok/analitik sesuai hak akses | PPL 27 |
| Kurir | Dashboard tugas pengiriman, daftar dan detail pesanan yang ditugaskan, informasi penerima/alamat, dan status pengiriman yang dapat dilakukan kurir | PPL 27 |
| Owner dan Kurir | Backend/API yang mendukung daftar/detail pesanan, penugasan kurir, dan perubahan status sesuai kewenangan | PPL 28 |
| Semua dashboard | Integrasi lintas fitur dan alur transaksi | PPL 34 |
| Semua dashboard | Penyempurnaan responsivitas, dokumentasi fitur, dan kesiapan demo | PPL 39 |

Ketentuan khusus:
1. Seluruh pekerjaan UI Dashboard Pembeli dipusatkan pada PPL 14. Dashboard Pembeli tidak dimasukkan ke PPL 27.
2. PPL 27 mencakup UI Dashboard Owner dan UI Dashboard Kurir. PPL 27 tidak menjadi tempat pekerjaan UI Dashboard Pembeli.
3. PPL 28 mencakup backend/API untuk kebutuhan Owner dan Kurir. Akses Kurir hanya untuk pesanan yang ditugaskan kepadanya.
4. Dashboard bukan fitur analitik terpisah. Ringkasan Owner hanya menampilkan informasi operasional yang sudah termasuk ruang lingkup, sedangkan Moving Average dan peringatan restok tetap mengikuti KF-11/KF-12.
5. Tampilan dashboard tidak memberikan kewenangan baru. Semua aksi tetap tunduk pada tabel aktor, BR-17, BR-18, BR-24, dan kontrak endpoint.

## Aturan bisnis

Aturan paling penting: pesanan hanya berstatus "dibayar" setelah backend memverifikasi pembayaran; tombol atau respons frontend tidak pernah mengubah status itu.

| Kode | Area | Aturan | Dasar |
| --- | --- | --- | --- |
| BR-01 | Produk | Produk punya nama, harga bilangan bulat rupiah lebih dari 0, stok bilangan bulat 0 atau lebih, komposisi, deskripsi rasa, dan keawetan; foto opsional; ukuran hanya 250 ml | O-2 |
| BR-02 | Produk | Perubahan stok oleh Admin/Penjual langsung tampil di katalog | P-12 |
| BR-03 | Katalog | Produk dengan stok 0 tidak dapat dipesan; tombol pesan nonaktif; jumlah stok ditampilkan | P-12 |
| BR-04 | Keranjang | Jumlah per item minimal 1 dan tidak melebihi stok tersedia; validasi di backend; saat checkout stok divalidasi ulang dan bila tidak cukup tampil pesan stok tidak tersedia | P-40 |
| BR-05 | Keranjang | Subtotal = harga x jumlah; total sementara = jumlah seluruh subtotal | US 10 |
| BR-06 | Pemesanan | Pesanan dibuat dari stok yang tersedia (bukan pre-order); tidak ada batas jumlah pesanan per orang selain stok | O-2, P-13 |
| BR-07 | Checkout | Checkout membuat pesanan berstatus menunggu\_pembayaran; harga dan jumlah disalin ke pesanan (snapshot) | US 12 |
| BR-08 | Checkout | Pesanan memuat data pelanggan, item, jumlah, harga, total belanja, alamat dan koordinat tujuan, ongkir, dan informasi pembayaran; ringkasan item, total, dan ongkir tampil sebelum membayar | P-19 |
| BR-09 | Checkout | Total bayar = total belanja + ongkir; menekan tombol checkout tidak membuat pesanan dianggap terbayar | US 14 |
| BR-10 | Stok | Stok ditahan saat pesanan dibuat; dikembalikan bila pembayaran gagal atau kedaluwarsa, atau pesanan dibatalkan | US 23 |
| BR-11 | Pembayaran | Metode QRIS lewat satu penyedia sandbox yang dipilih tim; tidak memakai uang sungguhan | O-5, P-27 |
| BR-12 | Pembayaran | Status dibayar hanya diberikan setelah verifikasi backend (webhook terverifikasi atau pengecekan resmi penyedia) | Keputusan proyek |
| BR-13 | Pembayaran | Status pembayaran: pending, berhasil, gagal, kedaluwarsa; webhook yang sama tidak memproses ulang pesanan (idempoten); batas waktu bayar 10 menit bila didukung penyedia; gagal atau kedaluwarsa memunculkan peringatan di web (pop-up atau banner) | P-29, P-31 |
| BR-14 | Ongkir | Ongkir dihitung dari jarak rute jalan: jarak <= 5,5 km gratis; > 5,5 km dan < 6,5 km Rp2.000; >= 6,5 km dan < 7,5 km Rp4.000; >= 7,5 km dan < 8,5 km Rp6.000; dan seterusnya naik Rp2.000 untuk setiap tingkat jarak 1 km. Untuk jarak d > 5,5 km, tarif = Rp2.000 x (floor(d - 5,5) + 1). Tarif ditampilkan sebelum pembayaran dan dihitung ulang bila pin/alamat berubah. | Keputusan proyek |
| BR-15 | Ongkir | Jarak adalah jarak rute jalan dari rumah produksi (Ds. Ndaleman, Kec. Sooko, Kab. Mojokerto; koordinat disimpan di konfigurasi) ke titik tujuan, lewat layanan routing berbasis OpenStreetMap; ongkir tampil saat checkout | O-4, P-22 |
| BR-16 | Ongkir | Titik tujuan wajib dan valid, dipilih dengan menggeser pin atau tombol gunakan lokasi saya; bila routing gagal, tampil pesan error dan checkout tidak dilanjutkan dengan ongkir tebakan | P-20 |
| BR-17 | Status pesanan | Alur: Menunggu Pembayaran -> Pesanan Diproses -> Pesanan Dikirim -> Selesai (kode: menunggu_pembayaran, diproses, dikirim, selesai). Pesanan belum dibayar dapat dibatalkan pelanggan; pesanan yang belum dibayar kedaluwarsa otomatis setelah 10 menit. Pesanan yang sudah dibayar tidak dapat dibatalkan melalui fitur pembatalan. | Keputusan proyek |
| BR-18 | Status pesanan | Admin/Owner dapat mengubah status pesanan yang sudah dibayar sampai Dikirim. Kurir hanya dapat menandai Selesai pada pesanan berstatus Dikirim yang ditugaskan kepadanya, setelah pesanan diterima pembeli. Pelanggan tidak dapat mengubah atau membatalkan pesanan yang sudah dibayar. | Keputusan proyek |
| BR-19 | Rule-Based | JIKA stok produk < 5 MAKA tampil peringatan restok; stok tepat 5 tidak memicu peringatan | Keputusan proyek |
| BR-20 | Rule-Based | Pada peringatan, sistem menampilkan saran produksi berdasarkan standar stok harian 10 unit per varian dan stok saat ini. Saran hanya rekomendasi; keputusan produksi tetap pada Owner. Nilai saran tidak boleh negatif. | O-3 dan keputusan proyek |
| BR-21 | Moving Average | Saran kebutuhan/permintaan stok minggu berikutnya dihitung sebagai (penjualan minggu 1 + minggu 2 + minggu 3) / 3 per produk. Jika data kurang dari 3 minggu, sistem menampilkan pesan data belum cukup. Hasil hanya rekomendasi; Owner menentukan produksi dan tidak ada perubahan stok otomatis. | O-3 dan keputusan proyek |
| BR-22 | Moving Average | Penjualan dihitung dari pesanan yang pembayarannya terverifikasi berhasil (status pesanan diproses, dikirim, atau selesai). Karena data historis 3 minggu belum tersedia, data seeder harus diberi label simulasi. | O-3 |
| BR-23 | Validasi dan error | Semua input divalidasi di backend; error dikembalikan dalam format standar (lihat kontrak) | Fondasi |
| BR-24 | Akses | Setiap endpoint memeriksa peran; Pelanggan hanya mengakses data miliknya; Kurir hanya fungsi pengantaran; akun admin hanya dipegang pemilik | O-6 |
| BR-25 | Sesi | Sesi login dapat tetap tersimpan sesuai mekanisme autentikasi yang dipilih tim, dengan tetap menjaga keamanan akun. | P-38 |

## Kontrak integrasi antarmodul

Setiap modul bertukar data lewat API JSON dengan entitas dan status di bawah, sehingga UI dan backend dapat dikerjakan paralel begitu kontrak ini disepakati. Konvensi: harga dalam rupiah (bilangan bulat), waktu format ISO 8601, koordinat lat/lng desimal, autentikasi sesi atau token per peran.

### Entitas data

| Entitas | Field utama | Catatan |
| --- | --- | --- |
| users | id, nama, email, password\_hash, role (admin, kurir, pelanggan) | email unik; akun admin hanya dipegang pemilik |
| products | id, nama, rasa, komposisi, deskripsi, keawetan, harga, stok, foto_path, aktif | stok >= 0; ukuran selalu 250 ml; produk nonaktif tidak tampil/tidak dapat dipesan; transaksi lama tetap tersimpan |
| cart\_items | id, user\_id, product\_id, jumlah | satu baris per produk per pengguna |
| orders | id, kode, user_id, courier_id, status, subtotal, ongkir, total, nama_penerima, telepon, alamat, lat, lng, catatan, created_at, stok_dikembalikan | courier_id nullable sebelum penugasan; detail harga/alamat menjadi snapshot; stok_dikembalikan mencegah pengembalian stok ganda |
| order\_items | id, order\_id, product\_id, nama\_produk, harga, jumlah | snapshot nama dan harga |
| payments | id, order\_id, provider, referensi\_provider, status, jumlah, kedaluwarsa\_pada, waktu\_verifikasi, payload\_mentah | status hanya diubah oleh verifikasi backend |
| sales\_history | id, product\_id, minggu\_mulai, jumlah\_terjual, sumber (simulasi atau transaksi) | sumber data Moving Average; data awal berlabel simulasi |
| settings | kunci, nilai | koordinat rumah produksi, batas ongkir dan tarif bertingkat, ambang restok (5), standar stok harian (10) |

### Riwayat status pesanan

Tambahkan entitas `order_status_history` dengan field `id`, `order_id`, `status_sebelumnya` (boleh NULL untuk status awal), `status_baru`, `diubah_oleh` (boleh NULL untuk proses otomatis), `waktu_perubahan`, dan `keterangan` opsional. Setiap perubahan status wajib dicatat; perubahan status dan riwayat dicatat dalam satu transaksi database.

### Status

| Objek | Nilai (kode) | Tampilan |
| --- | --- | --- |
| order.status | menunggu\_pembayaran, diproses, dikirim, selesai, dibatalkan, kedaluwarsa | Menunggu Pembayaran, Pesanan Diproses, Pesanan Dikirim, Selesai, Dibatalkan, Kedaluwarsa |
| payment.status | pending, berhasil, gagal, kedaluwarsa | Menunggu, Berhasil, Gagal, Kedaluwarsa |

### Endpoint API (usulan)

| Modul | Metode dan path | Fungsi | Peran |
| --- | --- | --- | --- |
| Auth | POST /api/register, POST /api/login, POST /api/logout | Daftar, masuk, keluar | Tamu / semua |
| Produk | GET /api/products, GET /api/products/{id} | Katalog dan detail; mengembalikan stok dan flag stok\_habis | Semua |
| Produk | POST /api/admin/products, PUT /api/admin/products/{id}, PATCH /api/admin/products/{id}/status | Tambah, ubah (termasuk harga, stok, foto), aktif/nonaktif; tidak ada penghapusan permanen melalui aplikasi | Admin |
| Keranjang | GET /api/cart, POST /api/cart/items, PATCH /api/cart/items/{id}, DELETE /api/cart/items/{id} | Lihat (dengan subtotal dan total), tambah, ubah jumlah, hapus | Pelanggan |
| Ongkir | POST /api/shipping/quote | Masukan: lat, lng tujuan. Keluaran: jarak\_km, ongkir, atau error routing | Pelanggan |
| Checkout | POST /api/checkout | Masukan: data penerima, lat, lng, catatan. Membuat pesanan menunggu\_pembayaran, menahan stok, membuat tagihan pembayaran | Pelanggan |
| Pembayaran | POST /api/payments/webhook | Notifikasi penyedia; tanda tangan diverifikasi sebelum status diubah | Penyedia |
| Pembayaran | GET /api/orders/{id}/payment | Status pembayaran terkini | Pelanggan, Admin |
| Pesanan | GET /api/orders, GET /api/orders/{id} | Pelanggan melihat miliknya; Admin melihat semua, dengan filter status | Pelanggan, Admin |
| Pesanan | PATCH /api/admin/orders/{id}/status | Memajukan status sesuai alur sampai Dikirim; mencatat riwayat status | Admin |
| Analitik | GET /api/admin/forecast | Estimasi Moving Average per produk, atau pesan data belum cukup | Admin |
| Analitik | GET /api/admin/low-stock | Daftar produk dengan stok < 5 | Admin
| Penugasan kurir | PATCH /api/admin/orders/{id}/courier | Menugaskan atau mengganti kurir pada pesanan | Admin |
| Pesanan kurir | GET /api/courier/orders, GET /api/courier/orders/{id} | Melihat daftar dan detail pesanan yang ditugaskan kepada kurir yang login | Kurir |
| Pengantaran | PATCH /api/courier/orders/{id}/complete | Menandai selesai setelah pesanan diterima pelanggan; hanya untuk pesanan yang ditugaskan | Kurir | |

Dashboard Pembeli menggunakan endpoint pesanan pelanggan yang sama (`GET /api/orders` dan `GET /api/orders/{id}`) dengan pembatasan data berdasarkan pengguna yang login. Dashboard Kurir menggunakan endpoint `/api/courier/orders` dan `/api/courier/orders/{id}`. Dashboard Owner menggunakan endpoint pesanan Admin serta endpoint analitik Admin yang telah ditetapkan. Tidak perlu membuat endpoint hanya karena nama halaman disebut dashboard.

Format respons API: semua respons menggunakan JSON. Respons sukses memakai `{"message":"...","data":{...}}` sesuai kebutuhan endpoint. Respons error memakai `{"message":"...","errors":{"field":["..."]}}` bila relevan. Gunakan status HTTP yang sesuai: 200 sukses, 201 data baru dibuat, 400 permintaan tidak valid, 401 belum login, 403 peran tidak sesuai, 404 data tidak ditemukan, 422 validasi gagal, 500 kesalahan internal, dan 502 layanan eksternal gagal. Detail teknis internal tidak ditampilkan ke pengguna.

Semua endpoint harus memeriksa autentikasi dan otorisasi di backend. Pelanggan hanya mengakses data sendiri; kurir hanya pesanan yang ditugaskan; Admin/Owner hanya satu akun yang dikelola pemilik. Produk dinonaktifkan memakai `PATCH /api/admin/products/{id}/status`, bukan DELETE. Harga, jumlah, subtotal, ongkir, total, dan alamat pesanan disimpan sebagai snapshot pada saat checkout.

## Hal yang perlu dikonfirmasi

Tujuh hal belum final dan tidak boleh diisi dengan angka karangan; masing-masing harus jelas sebelum Work Package yang memakainya dimulai.

| Hal | Keputusan oleh | Dibutuhkan sebelum |
| --- | --- | --- |
| Tarif ongkir final (usulan awal: gratis sampai 5 km, Rp2.000 per km di atasnya) | Pemilik usaha (Ilham) | PPL 26 (mulai 15 Nov 2026) |
| Titik koordinat rumah produksi (alamat sudah ada: Ds. Ndaleman, Kec. Sooko, Kab. Mojokerto) | Pemilik usaha (Ilham) | PPL 26 (mulai 15 Nov 2026) |
| Penyedia pembayaran sandbox yang mendukung QRIS: Midtrans, Xendit, atau Duitku | Tim | PPL 19 (9 Nov 2026) |
| Batas wilayah pengiriman (pemilik menyebut pengiriman lokal saja) | Pemilik usaha (Ilham) | PPL 26 (mulai 15 Nov 2026) |
| Definisi satu minggu untuk Moving Average (Senin sampai Minggu atau 7 hari mundur) | Tim | PPL 30 (mulai 14 Nov 2026) |
| File logo beresolusi tinggi dan warna dominan (pemilik menyatakan ada) | Pemilik usaha (Ilham) | PPL 05 (28 Okt 2026) |
| Apakah temuan wawancara di bawah dimasukkan ke ruang lingkup | Pemilik usaha dan tim | PPL 02 (29 Okt 2026) bila berdampak pada ERD |

## Temuan wawancara di luar ruang lingkup

Sebelas temuan wawancara tidak ada di daftar fitur wajib, sehingga tidak dimasukkan ke Sprint Backlog sampai pemilik dan tim memutuskan.

| Temuan | Sumber | Catatan |
| --- | --- | --- |
| Pesanan ambil sendiri, jam pemesanan online 08.00-15.00, lokasi lapak berpindah | P-11, P-21, P-23 | Pembeli biasanya membeli sekitar pukul 19.00 dan memilih antar atau ambil sendiri sesuai situasi |
| Notifikasi di web saat status atau lokasi berubah | P-24, P-34 | Pembeli hanya membuka web saat memesan |
| Alamat tersimpan untuk pesanan berikutnya | P-15, P-38 | Tambahan kecil di checkout; berdampak pada ERD (tabel alamat) |
| Fitur lupa kata sandi | P-14 | Pembeli menilai cukup mengganggu bila tidak ada |
| Bukti pembayaran atau nota digital | P-32 | Sementara, status dan referensi pembayaran di detail pesanan |
| Menghubungi penjual lewat web | P-36 | Pembeli lebih suka lewat web daripada WhatsApp |
| Mengedit pesanan setelah dibayar | P-39 | Bertentangan dengan aturan pesanan terbayar tidak dapat diubah (diterima pembeli di P-30); usulan: buat pesanan baru |
| Refund bila pesanan salah atau terlambat | P-9 | Kebijakan usaha, bukan fitur sistem |
| Pesan ulang dan riwayat pesanan lanjutan | P-35, P-44 | Pembeli menilai tidak perlu |
| Kurir pihak ketiga (GoSend, Grab Express, ShopeeFood) | O-4 | Tidak dipakai karena ongkir dihitung dari jarak OpenStreetMap; pengantaran dilakukan kurir internal usaha |
| Diskon | O-3 | Pemilik menyatakan belum ada sistem diskon |

## Kriteria selesai dan serah terima

PPL 01 selesai pada 28 Oktober 2026 bila daftar berikut sudah tercentang; hasilnya langsung dipakai PPL 02 (ERD dan migrasi dari entitas data) dan PPL 04 (dokumentasi teknis awal).

- [ ] Seluruh anggota tim membaca dan menyetujui aturan bisnis BR-01 sampai BR-41
- [ ] Entitas data dan status disepakati sebagai bahan ERD PPL 02
- [ ] Daftar endpoint dan format error disepakati sebagai kontrak UI dan backend
- [ ] Hal yang masih perlu dikonfirmasi (koordinat rumah produksi, penyedia sandbox, definisi minggu MA, logo, keputusan temuan di luar ruang lingkup, dan akses katalog tamu) dicatat dengan penanggung jawabnya
- [ ] Temuan wawancara di luar ruang lingkup dibahas dengan pemilik: masuk atau tidak
- [ ] Dokumen ini disimpan di halaman Docs proyek Jira atau di repositori (misalnya docs/kebutuhan.md)
- [ ] Task PPL 01 di Jira dipindahkan ke Done


## Aturan bisnis tambahan hasil konfirmasi 28 Oktober 2026

| Kode | Area | Aturan final |
|---|---|---|
| BR-26 | Penugasan kurir | Tabel `orders` memiliki `courier_id` yang mereferensikan `users.id` dengan role `kurir`; nilainya boleh NULL sebelum penugasan. Satu pesanan memiliki satu kurir aktif, satu kurir dapat menangani banyak pesanan. Backend memeriksa penugasan pada setiap permintaan kurir. |
| BR-27 | Penonaktifan produk | Produk dinonaktifkan dengan `products.aktif = false`; produk tidak tampil di katalog dan tidak dapat dipesan. Riwayat transaksi dipertahankan dan produk dapat diaktifkan kembali. Tidak ada penghapusan permanen produk melalui aplikasi. |
| BR-28 | Pembayaran QRIS | Pembayaran hanya dianggap berhasil setelah backend menerima dan memverifikasi notifikasi resmi penyedia atau melakukan pemeriksaan resmi. Frontend tidak dapat mengubah status pembayaran. |
| BR-29 | Idempotensi pembayaran | Notifikasi webhook yang sama hanya diproses satu kali; notifikasi duplikat tidak membuat transaksi ganda atau mengubah stok berulang kali. |
| BR-30 | Nominal pembayaran | Nominal yang dikonfirmasi penyedia harus sama dengan total pesanan. Ketidaksesuaian ditandai untuk pemeriksaan Admin/Owner dan tidak langsung dianggap valid. |
| BR-31 | Notifikasi terlambat | Jika pembayaran berhasil dikonfirmasi setelah pesanan kedaluwarsa, hasil pembayaran tetap dicatat secara akurat, pesanan tidak diaktifkan kembali otomatis, dan kasus ditandai untuk pemeriksaan Admin/Owner. |
| BR-32 | Snapshot pesanan | Saat checkout, nama produk, harga satuan, jumlah, subtotal, ongkir, total, dan alamat pengiriman disimpan sebagai snapshot. Perubahan harga produk, profil, atau keranjang tidak mengubah pesanan yang sudah dibuat. |
| BR-33 | Riwayat status | Setiap perubahan status pesanan dicatat dalam `order_status_history`, termasuk status sebelumnya, status baru, waktu, dan aktor; aktor boleh NULL untuk proses otomatis. |
| BR-34 | Pengembalian stok | Stok yang dicadangkan dikembalikan saat pesanan belum dibayar dibatalkan atau kedaluwarsa, tepat satu kali. Backend harus mencegah pengembalian ganda, termasuk saat webhook dan proses kedaluwarsa berjalan bersamaan. |
| BR-35 | Pembatalan | Pelanggan boleh membatalkan pesanan sebelum pembayaran. Pesanan berstatus dibayar tidak dapat dibatalkan pelanggan atau melalui fitur pembatalan Admin/Owner. Kebijakan refund tetap di luar ruang lingkup fitur ini. |
| BR-36 | Batas pembayaran | Batas waktu pembayaran adalah 10 menit sejak pesanan dibuat. Jika belum ada pembayaran terverifikasi, pesanan otomatis menjadi `kedaluwarsa` dan stok dikembalikan tepat satu kali. |
| BR-37 | Konflik pembayaran/kedaluwarsa | Verifikasi pembayaran dan kedaluwarsa harus ditangani secara atomik. Jika pembayaran diverifikasi sebelum kedaluwarsa ditetapkan, pesanan mengikuti alur normal. Jika sudah kedaluwarsa, pembayaran terlambat dicatat dan ditinjau tanpa mengaktifkan kembali pesanan. |
| BR-38 | Notifikasi pelanggan | Status pesanan/pembayaran dan kegagalan yang relevan ditampilkan melalui antarmuka web dengan pesan yang jelas. Detail kesalahan teknis internal hanya dicatat pada log backend. |
| BR-39 | Validasi ongkir | Ongkir menggunakan jarak rute jalan dari alamat rumah produksi ke titik tujuan di Kota Mojokerto. Jika routing tidak dapat menghitung rute, checkout diblokir dan pelanggan diberi pesan kesalahan. Perubahan pin/alamat memicu perhitungan ulang ongkir dan total. |
| BR-40 | Moving Average | Moving Average adalah rekomendasi kebutuhan/permintaan untuk minggu berikutnya dari rata-rata penjualan 3 minggu sebelumnya. Hasil hanya saran bagi Owner; tidak otomatis memproduksi atau mengubah stok. |
| BR-41 | Respons API | Semua respons API berformat JSON yang konsisten; sukses memuat `message` dan `data` yang relevan, sedangkan error memakai `message` dan `errors` bila diperlukan serta status HTTP yang sesuai. |

### Formula ongkir bertingkat yang disepakati

- `d <= 5,5 km`: Rp0.
- `5,5 < d < 6,5 km`: Rp2.000.
- `6,5 <= d < 7,5 km`: Rp4.000.
- `7,5 <= d < 8,5 km`: Rp6.000, dan seterusnya naik Rp2.000 untuk setiap tingkat jarak 1 km.
- Untuk `d > 5,5`, formula umum: `ongkir = Rp2.000 × (floor(d - 5,5) + 1)`. Untuk `d <= 5,5`, ongkir = Rp0.

Contoh: 5,51 km = Rp2.000; 6,49 km = Rp2.000; 6,50 km = Rp4.000; 7,50 km = Rp6.000. Jarak adalah jarak rute jalan, bukan jarak garis lurus. Tujuan pengiriman dibatasi pada Kota Mojokerto; koordinat rumah produksi harus dikonfirmasi oleh pemilik.

### Struktur data tambahan yang dibutuhkan

| Entitas/kolom | Ketentuan |
|---|---|
| `orders.courier_id` | Foreign key ke `users.id` untuk kurir yang ditugaskan; NULL sebelum penugasan. |
| `orders.stok_dikembalikan` | Penanda pengembalian stok agar proses hanya terjadi sekali; mekanisme database setara boleh digunakan jika menjamin atomisitas. |
| `order_status_history` | `id`, `order_id`, `status_sebelumnya`, `status_baru`, `diubah_oleh`, `waktu_perubahan`, `keterangan` opsional. |

### Hal yang tetap perlu ditetapkan tim/pemilik

Keputusan yang belum tersedia dari wawancara atau belum dikonfirmasi pada sesi revisi tidak diisi dengan asumsi: (1) koordinat pasti rumah produksi; (2) penyedia QRIS sandbox yang dipilih; (3) definisi periode minggu untuk Moving Average; (4) ketersediaan file logo resolusi tinggi; (5) keputusan tim/pemilik atas temuan wawancara yang masih di luar ruang lingkup; dan (6) apakah katalog boleh diakses tamu tanpa login. Batas wilayah pengiriman telah diputuskan untuk dokumen ini sebagai Kota Mojokerto saja. Tarif ongkir telah diperbarui berdasarkan keputusan terbaru, menggantikan usulan awal gratis 5 km.
