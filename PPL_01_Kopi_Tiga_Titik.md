# PPL 01 – Analisis Kebutuhan, Aturan Bisnis & Kontrak Integrasi Antarmodul

## 1. Ringkasan dan ruang lingkup

Dokumen ini menjadi acuan kebutuhan fungsional, aturan bisnis, dan kontrak data antarmodul Sistem Penjualan Kopi Tiga Titik. Dokumen ini menjadi dasar teknis PPL 02 (ERD, migrasi database, dan relasi data) serta pelaksanaan Work Package berikutnya.

Isi dokumen bersumber dari wawancara pemilik usaha dan pembeli (Nabil, 23 Oktober 2026), serta keputusan ruang lingkup proyek yang telah disepakati tim. Rujukan wawancara menggunakan kode P-nn untuk pembeli dan O-n untuk pemilik.

### 1.1 Fitur wajib MVP

Seluruh fitur MVP ditargetkan selesai dikembangkan paling lambat 28 November 2026. Pengujian keseluruhan oleh Scrum Master dijadwalkan 29 November 2026.

1. **Fondasi sistem:** basis data MySQL, migrasi, seeder, autentikasi, hak akses, validasi input, dan penanganan kesalahan.
2. **Kelola produk:** Admin/Owner dapat menambah, mengubah, mengaktifkan, dan menonaktifkan produk, termasuk harga, stok, foto, komposisi, deskripsi rasa, dan keawetan.
3. **Katalog:** pelanggan dapat melihat produk, informasi, harga, dan ketersediaan stok.
4. **Keranjang:** pelanggan dapat menambah, mengubah jumlah, dan menghapus item; sistem menghitung subtotal serta memvalidasi stok.
5. **Checkout dan pesanan:** pelanggan mengisi data penerima dan alamat, memilih titik tujuan, melihat ringkasan belanja serta ongkir, lalu membuat pesanan.
6. **Pembayaran:** integrasi Midtrans QRIS dalam mode sandbox dengan verifikasi status pembayaran di backend.
7. **Pengiriman:** ongkir dihitung berdasarkan jarak rute jalan dari lokasi asal tersimpan yang dipilih Owner ke titik tujuan di wilayah Kota Mojokerto, menggunakan layanan routing berbasis OpenStreetMap.
8. **Pengelolaan pesanan dan kurir:** Admin/Owner melihat daftar dan detail pesanan, menugaskan kurir pada pesanan, serta memperbarui status sampai Dikirim. Kurir melihat pesanan yang ditugaskan kepadanya dan memperbarui status menjadi Selesai setelah pesanan diterima pelanggan.
9. **Moving Average:** rekomendasi penjualan/kebutuhan minggu berikutnya berdasarkan tiga periode 7 hari.
10. **Rule-Based:** peringatan stok rendah ketika stok produk kurang dari atau sama dengan 5, disertai saran produksi.
11. **Lokasi asal pengiriman:** Owner dapat menyimpan beberapa lokasi asal, mengaktifkan atau menonaktifkan lokasi, dan memilih lokasi yang digunakan untuk perhitungan ongkir.

### 1.2 Di luar ruang lingkup

- Scoring lima produk terlaris.
- Diskon otomatis 10% untuk transaksi minimal Rp200.000 karena pemilik menyatakan belum memiliki sistem diskon (O-3).
- Fitur kurir di luar proses pengantaran pesanan, seperti pelacakan GPS langsung, optimasi rute multi-pesanan, dan manajemen armada.
- Integrasi API ekspedisi. Perhitungan ongkir menggunakan jarak rute berbasis OpenStreetMap.
- Pengembalian dana (refund) otomatis untuk pembayaran yang terlambat atau kasus pembayaran bermasalah; kasus tersebut ditandai untuk pemeriksaan Owner.

## 2. Aktor dan hak akses

Sistem menggunakan tiga peran aplikasi: Admin/Owner, Kurir, dan Pelanggan.

| Aktor | Hak akses | Batasan |
|---|---|---|
| Admin/Owner | Mengelola produk dan lokasi asal pengiriman; melihat seluruh pesanan; menugaskan kurir; memperbarui status pesanan sampai `dikirim`; melihat status pembayaran; melihat hasil Moving Average dan peringatan stok; mengelola konfigurasi sistem. | Tidak dapat mengubah status pembayaran menjadi berhasil secara manual. Pesanan yang sudah dibayar tidak dapat dibatalkan melalui fitur pembatalan. |
| Kurir | Melihat pesanan yang ditugaskan kepadanya dan memperbarui status pesanan dari `dikirim` menjadi `selesai` setelah pesanan diterima pelanggan. | Tidak dapat melihat pesanan kurir lain, mengubah data produk, mengubah pembayaran, atau mengubah status pesanan selain sesuai tugasnya. |
| Pelanggan | Mendaftar dan masuk; melihat katalog; mengelola keranjang sendiri; checkout; melakukan pembayaran; melihat pesanan miliknya; membatalkan pesanan yang belum dibayar. | Tidak dapat mengakses data pelanggan lain atau fungsi Admin/Owner dan Kurir. |

Akun Admin/Owner dikelola oleh pemilik usaha. Akun Kurir dibuat atau disediakan oleh Admin/Owner dan digunakan oleh kurir yang ditugaskan. Pelanggan harus memiliki akun untuk melakukan checkout. Hak akses wajib diperiksa di backend, bukan hanya dengan menyembunyikan menu pada antarmuka. Penugasan kurir dilakukan oleh Admin/Owner melalui penyimpanan `orders.courier_id`.

**Catatan keselarasan database:** PPL 02 telah menyediakan nilai `admin`, `kurir`, dan `pelanggan` pada `users.role`, serta kolom `orders.courier_id` nullable sebagai relasi pengguna kurir yang ditugaskan. Kolom dan role tersebut digunakan untuk mendukung proses penugasan dan pengantaran pesanan.

## 3. Kebutuhan fungsional per modul

| Kode | Modul | Kebutuhan | Dasar |
|---|---|---|---|
| KF-01 | Basis data | Data produk, pengguna, keranjang, pesanan, pembayaran, riwayat status, penjualan, konfigurasi, dan lokasi pengiriman disimpan pada satu basis data MySQL melalui migrasi dan seeder. | O-1 |
| KF-02 | Autentikasi dan akses | Pengguna dapat mendaftar, masuk, dan keluar. Sistem membatasi akses berdasarkan tiga peran: Admin/Owner, Kurir, dan Pelanggan. Akun Kurir disediakan oleh Admin/Owner. | P-14, P-38, O-6 |
| KF-03 | Kelola produk | Admin dapat menambah, mengubah, mengaktifkan, dan menonaktifkan produk beserta harga, stok, foto, komposisi, deskripsi rasa, dan keawetan. | O-2 |
| KF-04 | Katalog | Pelanggan dapat melihat foto, nama, harga, komposisi, deskripsi rasa, keawetan 5–7 hari, dan stok. Produk dengan stok 0 atau status nonaktif tidak dapat dipesan. | O-2, P-12, P-16, P-18 |
| KF-05 | Keranjang | Pelanggan dapat menambah, mengubah jumlah, dan menghapus produk dari keranjang. Sistem menampilkan subtotal dan total sementara serta memvalidasi jumlah terhadap stok. | P-38, P-40 |
| KF-06 | Checkout | Pelanggan mengisi nama penerima, nomor telepon, alamat, titik tujuan pada peta, dan catatan opsional. Sistem menampilkan ringkasan item, total belanja, dan ongkir sebelum pesanan dibuat. | O-6, P-19, P-20, P-22 |
| KF-07 | Pembayaran | Sistem membuat pembayaran QRIS melalui Midtrans Sandbox. Status pembayaran hanya diperbarui setelah verifikasi backend. | O-5, P-27, P-28 |
| KF-08 | Ongkir | Sistem menghitung ongkir berdasarkan jarak rute jalan dari lokasi asal terpilih ke titik tujuan menggunakan layanan routing berbasis OpenStreetMap. | O-1, O-4, P-22 |
| KF-09 | Stok dan transaksi | Stok dikurangi/ditahan ketika pesanan dibuat. Jika pesanan belum dibayar dibatalkan atau kedaluwarsa, stok dikembalikan tepat satu kali. | P-40 |
| KF-10 | Daftar pesanan, penugasan kurir, dan status | Admin/Owner dapat melihat seluruh pesanan, memantau pembayaran, menugaskan kurir, dan memperbarui status sampai `dikirim`. Kurir hanya dapat melihat pesanan yang ditugaskan kepadanya dan menandai `selesai`. Pelanggan hanya dapat melihat pesanan miliknya. | O-6, P-33 |
| KF-11 | Moving Average | Sistem menghitung rekomendasi penjualan/kebutuhan minggu berikutnya dari rata-rata penjualan pada tiga periode 7 hari berturut-turut dalam 21 hari sebelum tanggal prediksi. | O-3 |
| KF-12 | Rule-Based restok | Sistem menampilkan peringatan ketika stok produk `<= 5` dan memberikan saran produksi berdasarkan standar stok harian. | O-3 |
| KF-13 | Lokasi asal pengiriman | Admin dapat menambah, mengubah, mengaktifkan/menonaktifkan, dan memilih lokasi asal tersimpan untuk perhitungan ongkir. | Keputusan proyek, 29 Oktober 2026 |

### 3.1 Data awal produk

Data berikut berasal dari wawancara pemilik usaha untuk produk ukuran 250 ml.

| Varian | Rasa | Harga |
|---|---|---:|
| Titik Jenuh | Coklat susu | Rp12.000 |
| Titik Manja | Kopi karamel | Rp10.000 |
| Titik Temu | Kopi gula aren | Rp10.000 |
| Titik Hitam | Americano | Rp8.000 |

Keawetan produk adalah 5–7 hari jika disimpan di lemari pendingin. Rekap penjualan historis tiga minggu belum tersedia (O-3). Karena itu, data awal untuk pengujian Moving Average dapat menggunakan data simulasi yang diberi penanda `simulasi`, bukan dianggap sebagai transaksi nyata.

## 4. Aturan bisnis

Aturan utama sistem adalah status pembayaran hanya dinyatakan berhasil setelah backend memverifikasi informasi resmi dari Midtrans. Respons atau tindakan dari frontend tidak boleh menjadi dasar untuk menyatakan pembayaran berhasil.

| Kode | Area | Aturan |
|---|---|---|
| BR-01 | Produk | Produk memiliki nama, harga dalam rupiah berupa bilangan bulat lebih dari 0, stok bilangan bulat 0 atau lebih, komposisi, deskripsi rasa, dan keawetan. Foto bersifat opsional. Ukuran produk MVP adalah 250 ml. |
| BR-02 | Produk | Perubahan harga, stok, atau status aktif produk oleh Admin akan tercermin pada katalog. |
| BR-03 | Katalog | Produk dengan stok 0 atau `aktif = false` tidak dapat dipesan. Produk nonaktif tidak ditampilkan pada katalog pelanggan. |
| BR-04 | Keranjang | Jumlah setiap item minimal 1 dan tidak boleh melebihi stok tersedia. Validasi dilakukan di backend dan diulang saat checkout. |
| BR-05 | Keranjang | Subtotal item = harga produk × jumlah. Total sementara keranjang = jumlah seluruh subtotal item. |
| BR-06 | Pemesanan | Pesanan dibuat dari stok yang tersedia, bukan pre-order. Tidak ada batas jumlah pesanan per pelanggan selain ketersediaan stok. |
| BR-07 | Checkout | Checkout membuat pesanan dengan status `menunggu_pembayaran`. Nama produk, rasa, harga satuan, jumlah, dan subtotal item disimpan sebagai snapshot pada detail pesanan. |
| BR-08 | Checkout | Pesanan menyimpan data penerima, nomor telepon, alamat, koordinat tujuan, item, subtotal, ongkir, total, dan informasi pembayaran yang diperlukan. |
| BR-09 | Checkout | Total bayar = subtotal pesanan + ongkir. Pembuatan pesanan tidak berarti pembayaran telah berhasil. |
| BR-10 | Stok | Stok dikurangi/ditahan saat pesanan dibuat. Jika pesanan belum dibayar dibatalkan atau kedaluwarsa, stok dikembalikan tepat satu kali. |
| BR-11 | Pembayaran | Metode pembayaran MVP hanya QRIS melalui Midtrans dalam mode Sandbox. Pengujian tidak menggunakan uang sungguhan. |
| BR-12 | Pembayaran | Status pembayaran `berhasil` hanya diberikan setelah backend memverifikasi notifikasi resmi atau melakukan pengecekan resmi ke penyedia. |
| BR-13 | Pembayaran | Status pembayaran terdiri dari `pending`, `berhasil`, `gagal`, dan `kedaluwarsa`. Batas pembayaran adalah 10 menit sejak pesanan dibuat. Notifikasi webhook duplikat tidak boleh memproses transaksi atau perubahan stok berulang kali. |
| BR-14 | Ongkir | Jarak `<= 5,5 km` dikenakan ongkir Rp0. Jarak di atas 5,5 km dikenakan Rp2.000 untuk setiap tingkat jarak 1 km berikutnya sesuai formula pada Bagian 4.1. |
| BR-15 | Ongkir | Jarak dihitung sebagai jarak rute jalan dari lokasi asal tersimpan yang dipilih Admin ke titik tujuan pelanggan, menggunakan layanan routing berbasis OpenStreetMap; bukan jarak garis lurus. |
| BR-16 | Ongkir | Titik tujuan wajib dan harus valid. Jika routing gagal atau lokasi asal aktif tidak valid, sistem menampilkan pesan kesalahan dan checkout tidak boleh dilanjutkan menggunakan ongkir perkiraan. |
| BR-17 | Status pesanan | Alur status pesanan: `menunggu_pembayaran` → `diproses` → `dikirim` → `selesai`. Pesanan yang belum dibayar dapat dibatalkan pelanggan dan otomatis kedaluwarsa setelah 10 menit jika belum ada pembayaran terverifikasi. Admin/Owner memperbarui status menjadi `dikirim`; Kurir yang ditugaskan memperbarui status menjadi `selesai` setelah pesanan diterima pelanggan. |
| BR-18 | Status pesanan dan penugasan | Admin/Owner hanya dapat mengubah status pesanan yang telah dibayar dari `diproses` menjadi `dikirim` dan menugaskan satu Kurir melalui `orders.courier_id`. Kurir hanya dapat mengubah pesanan yang ditugaskan kepadanya dari `dikirim` menjadi `selesai`. Pelanggan tidak dapat mengubah status setelah checkout, selain membatalkan pesanan yang belum dibayar. Pesanan yang sudah dibayar tidak dapat dibatalkan melalui fitur pembatalan. |
| BR-19 | Rule-Based | Jika stok produk `<= 5`, sistem menampilkan peringatan stok rendah. |
| BR-20 | Rule-Based | Saran produksi dihitung berdasarkan standar stok harian 10 unit per varian dan stok saat ini. Saran hanya rekomendasi; keputusan produksi tetap pada Owner. Nilai saran tidak boleh negatif. |
| BR-21 | Moving Average | Moving Average menggunakan 21 hari kalender sebelum tanggal prediksi, dibagi menjadi tiga periode berurutan yang masing-masing terdiri dari 7 hari. Periode dihitung mundur dari tanggal prediksi, bukan berdasarkan minggu kalender. |
| BR-22 | Moving Average | Perhitungan dilakukan per produk: (total penjualan periode 7 hari pertama + periode 7 hari kedua + periode 7 hari ketiga) / 3. Hari tanpa penjualan dihitung sebagai 0. Jika data belum mencakup tiga periode, sistem menampilkan informasi bahwa data belum cukup. |
| BR-23 | Riwayat penjualan | Penjualan nyata online dicatat setelah pembayaran terverifikasi berhasil. Pesanan belum dibayar, gagal, kedaluwarsa, atau dibatalkan tidak dihitung sebagai penjualan. Data simulasi harus memiliki sumber `simulasi` dan tidak dicampur sebagai transaksi nyata. |
| BR-24 | Validasi dan error | Semua input penting divalidasi di backend. Pesan kesalahan kepada pengguna harus jelas dan tidak menampilkan detail teknis internal. |
| BR-25 | Akses | Admin/Owner dapat mengakses fungsi pengelolaan dan seluruh pesanan. Kurir hanya dapat mengakses pesanan yang ditugaskan kepadanya melalui `orders.courier_id`. Pelanggan hanya dapat mengakses keranjang dan pesanan miliknya sendiri. Pemeriksaan peran dan kepemilikan/penugasan data dilakukan di backend. |
| BR-26 | Snapshot pesanan | Perubahan harga, nama produk, profil pelanggan, atau keranjang setelah checkout tidak mengubah data pesanan yang sudah dibuat. |
| BR-27 | Produk nonaktif | Produk dinonaktifkan melalui `products.aktif = false`, bukan dihapus permanen melalui aplikasi. Riwayat transaksi produk tetap dipertahankan. |
| BR-28 | Idempotensi pembayaran | Notifikasi pembayaran yang sama hanya diproses satu kali. Notifikasi duplikat tidak membuat pembayaran atau pencatatan penjualan ganda dan tidak mengubah stok berulang kali. |
| BR-29 | Nominal pembayaran | Nominal yang dikonfirmasi penyedia harus sama dengan total pesanan. Jika tidak sama, pembayaran tidak langsung dianggap valid dan harus ditandai untuk pemeriksaan Admin/Owner. |
| BR-30 | Pembayaran terlambat | Jika pembayaran dikonfirmasi setelah pesanan kedaluwarsa, hasil pembayaran tetap dicatat secara akurat. Pesanan tidak diaktifkan kembali otomatis dan kasus ditandai untuk pemeriksaan Admin/Owner. Penanganan refund berada di luar ruang lingkup MVP. |
| BR-31 | Riwayat status | Setiap perubahan status pesanan dicatat pada `order_status_history`, termasuk status sebelumnya, status baru, waktu perubahan, aktor jika tersedia, dan keterangan. Perubahan status dan pencatatan riwayat dilakukan dalam satu transaksi database. |
| BR-32 | Pengembalian stok | Pengembalian stok harus aman terhadap proses bersamaan dan hanya boleh dilakukan satu kali, termasuk ketika proses kedaluwarsa dan notifikasi pembayaran berjalan berdekatan. |
| BR-33 | Batas wilayah | Pengiriman MVP dibatasi pada wilayah Kota Mojokerto. |
| BR-34 | Lokasi asal | Admin dapat menyimpan lebih dari satu lokasi asal, misalnya rumah produksi dan lapak. Lokasi disimpan dalam tabel `shipping_locations`. |
| BR-35 | Pemilihan lokasi asal | Hanya lokasi dengan `aktif = true` yang dapat dipilih. ID lokasi yang sedang digunakan disimpan pada `settings` dengan key `lokasi_pengiriman_aktif_id`. Backend wajib memvalidasi bahwa lokasi tersebut tersedia dan aktif. |
| BR-36 | Lokasi tidak valid | Jika lokasi asal belum dipilih, tidak ditemukan, atau nonaktif, perhitungan ongkir gagal dan checkout diblokir. |
| BR-37 | Perubahan lokasi | Perubahan lokasi asal memengaruhi perhitungan ongkir berikutnya. Pesanan yang sudah dibuat tetap menggunakan snapshot ongkir dan alamat tujuan saat checkout. |
| BR-38 | Konfigurasi | Seeder menyediakan konfigurasi awal secara idempoten, termasuk `batas_stok_minimum = 5` dan `stok_harian_standar = 10`. Menjalankan seeder berulang kali tidak boleh membuat key konfigurasi duplikat. |

### 4.1 Formula ongkir bertingkat

- Jarak `d <= 5,5 km`: Rp0.
- `5,5 < d < 6,5 km`: Rp2.000.
- `6,5 <= d < 7,5 km`: Rp4.000.
- `7,5 <= d < 8,5 km`: Rp6.000.
- Selanjutnya naik Rp2.000 untuk setiap tingkat jarak 1 km.

Untuk `d > 5,5 km`:

`ongkir = Rp2.000 × (floor(d - 5,5) + 1)`

Contoh: 5,51 km = Rp2.000; 6,49 km = Rp2.000; 6,50 km = Rp4.000; 7,50 km = Rp6.000. Jarak yang digunakan adalah jarak rute jalan. Koordinat setiap lokasi asal harus diverifikasi sebelum digunakan.

## 5. Kontrak data antarmodul

Bagian ini menjelaskan data yang digunakan bersama oleh modul aplikasi. Nama tabel dan kolom diselaraskan dengan implementasi database pada PPL 02. Tipe data dan detail constraint mengikuti ERD serta migration PPL 02.

| Tabel | Fungsi dan kolom utama | Modul pengguna |
|---|---|---|
| `users` | Akun pengguna: `id`, `name`, `email`, `password`, `role`. Role aplikasi MVP: `admin`, `kurir`, dan `pelanggan`. | Autentikasi, akses, pesanan, penugasan kurir |
| `products` | Data produk: `id`, `nama`, `rasa`, `komposisi`, `deskripsi`, `keawetan`, `harga`, `stok`, `foto_path`, `aktif`. | Kelola produk, katalog, keranjang, pesanan, analitik |
| `cart_items` | Item keranjang: `id`, `user_id`, `product_id`, `jumlah`. Satu pengguna memiliki paling banyak satu baris untuk produk yang sama. | Keranjang, checkout |
| `orders` | Data pesanan: `id`, `kode`, `user_id`, `courier_id` (nullable sebelum kurir ditugaskan), `status`, `subtotal`, `ongkir`, `total`, `nama_penerima`, `telepon`, `alamat`, `lat`, `lng`, `catatan`, `stok_dikembalikan`. | Checkout, pembayaran, pesanan, penugasan kurir |
| `order_items` | Rincian snapshot produk pada pesanan: `id`, `order_id`, `product_id`, `nama_produk`, `rasa`, `jumlah`, `harga_satuan`, `subtotal`. | Checkout, detail pesanan, riwayat transaksi |
| `payments` | Informasi pembayaran: `id`, `order_id`, `referensi_provider`, `status`, `jumlah`, `kedaluwarsa_pada`, `waktu_verifikasi`, `payload_mentah`. Satu pesanan memiliki paling banyak satu baris pembayaran. | Pembayaran, pesanan |
| `order_status_history` | Riwayat status: `id`, `order_id`, `status_sebelumnya`, `status_baru`, `user_id`, `keterangan`, timestamp. | Pesanan, pelacakan status |
| `sales_history` | Rekap penjualan: `id`, `product_id`, `tanggal`, `jumlah_terjual`, `sumber` (`transaksi` atau `simulasi`). | Moving Average, analitik |
| `settings` | Konfigurasi sistem: `id`, `key`, `value`, `description`. Contoh key: `batas_stok_minimum`, `stok_harian_standar`, `lokasi_pengiriman_aktif_id`. | Rule-Based, ongkir, konfigurasi |
| `shipping_locations` | Lokasi asal tersimpan: `id`, `nama_lokasi`, `latitude`, `longitude`, `alamat`, `aktif`. | Pengelolaan lokasi, perhitungan ongkir |

### 5.1 Relasi data utama

1. `users` memiliki banyak `cart_items`.
2. `users` memiliki banyak `orders` sebagai pelanggan dan banyak pesanan yang ditugaskan melalui relasi `courier_id` sebagai kurir.
3. `products` memiliki banyak `cart_items`, `order_items`, dan `sales_history`.
4. `orders` memiliki banyak `order_items` dan `order_status_history`.
5. `orders` memiliki satu `payment`.
6. `order_items` mengacu pada satu `order` dan satu `product`.
7. `payments` mengacu pada satu `order`.
8. `order_status_history` mengacu pada satu `order`; `user_id` dapat kosong untuk perubahan otomatis oleh sistem.
9. `sales_history` mengacu pada satu `product`.
10. `shipping_locations` berdiri sebagai data lokasi asal. Lokasi yang dipilih disimpan sebagai ID teks pada `settings` dengan key `lokasi_pengiriman_aktif_id`.

### 5.2 Ketentuan integrasi data

- Harga, jumlah, subtotal, ongkir, dan total disimpan sebagai bilangan bulat rupiah.
- Koordinat disimpan sebagai latitude dan longitude desimal.
- Waktu menggunakan timestamp database dan ditampilkan sesuai zona waktu aplikasi.
- Relasi antartabel mengikuti foreign key pada migration PPL 02.
- Gunakan Eloquent relationship yang sudah disediakan model; hindari mengambil data relasi secara manual jika relasi model tersedia.
- `payments.payload_mentah` hanya untuk menyimpan payload notifikasi penyedia sebagai data mentah. Payload tersebut tidak boleh dianggap sebagai bukti pembayaran berhasil sebelum verifikasi backend.
- `order_items` menyimpan snapshot nama, rasa, harga satuan, jumlah, dan subtotal agar transaksi lama tidak berubah ketika data produk diperbarui.
- `order_status_history` menjadi sumber riwayat perubahan status, sedangkan `orders.status` menyimpan status terkini.
- `sales_history` direkap per produk, tanggal, dan sumber. Data simulasi harus tetap dibedakan dari transaksi nyata.
- `settings.value` bertipe teks; nilai yang berisi ID lokasi harus dikonversi dan divalidasi oleh backend sebelum digunakan.

## 6. Kontrak proses antarmodul

Urutan proses utama yang harus dipatuhi tim:

1. **Katalog → Keranjang:** pelanggan memilih produk aktif dan jumlahnya. Backend memeriksa stok dan kepemilikan keranjang.
2. **Keranjang → Checkout:** backend memvalidasi ulang produk, status aktif, jumlah, dan stok. Harga diambil dari database, bukan dipercaya dari frontend.
3. **Checkout → Ongkir:** backend mengambil lokasi asal aktif dari `settings`, memeriksa status lokasi pada `shipping_locations`, lalu menghitung jarak rute ke titik tujuan pelanggan.
4. **Checkout → Pesanan:** backend menghitung subtotal, ongkir, dan total; membuat pesanan berstatus `menunggu_pembayaran`; menyimpan snapshot item dan alamat; serta mengurangi/menahan stok secara atomik.
5. **Pesanan → Pembayaran:** sistem membuat transaksi Midtrans Sandbox dan menyimpan referensi serta batas waktu pembayaran pada `payments`.
6. **Pembayaran → Pesanan:** backend memverifikasi notifikasi atau status resmi Midtrans. Jika valid dan nominal sesuai, pembayaran ditandai berhasil dan pesanan bergerak ke status `diproses`.
7. **Pembayaran gagal/kedaluwarsa → Stok:** jika pesanan belum dibayar dan dibatalkan/kedaluwarsa, backend mengembalikan stok tepat satu kali.
8. **Pesanan → Riwayat status:** setiap perubahan status pesanan dicatat pada `order_status_history` dalam transaksi database yang sama.
9. **Pesanan dibayar → Riwayat penjualan:** penjualan dicatat ke `sales_history` setelah pembayaran terverifikasi. Data simulasi menggunakan sumber `simulasi`.
10. **Riwayat penjualan → Analitik:** Moving Average membaca data penjualan per produk untuk tiga periode 7 hari. Hasilnya hanya rekomendasi bagi Owner.
11. **Produk → Rule-Based:** sistem memeriksa stok. Jika `stok <= 5`, sistem menampilkan peringatan dan saran produksi.
12. **Pesanan → Penugasan Kurir:** Admin/Owner memilih Kurir untuk pesanan yang telah diproses dan menyimpan ID Kurir pada `orders.courier_id`.
13. **Pengantaran → Pesanan selesai:** Kurir hanya melihat pesanan yang ditugaskan kepadanya. Setelah pesanan diterima pelanggan, Kurir memperbarui status menjadi `selesai`; sistem mencatat aktor dan waktu perubahan pada `order_status_history`.

## 7. Kontrak endpoint (panduan pembagian modul)

Daftar berikut merupakan rancangan kontrak endpoint tingkat fungsi. Tim boleh menyesuaikan implementasi teknis selama perilaku, hak akses, dan aturan bisnis tetap sama.

| Modul | Metode dan path | Fungsi | Akses |
|---|---|---|---|
| Auth | `POST /api/register`, `POST /api/login`, `POST /api/logout` | Daftar, masuk, dan keluar. | Publik untuk daftar/masuk; pengguna login untuk keluar |
| Produk | `GET /api/products`, `GET /api/products/{id}` | Katalog dan detail produk. | Publik/pelanggan |
| Produk | `POST /api/admin/products`, `PUT /api/admin/products/{id}`, `PATCH /api/admin/products/{id}/status` | Tambah, ubah, aktif/nonaktif produk. | Admin |
| Keranjang | `GET /api/cart`, `POST /api/cart/items`, `PATCH /api/cart/items/{id}`, `DELETE /api/cart/items/{id}` | Lihat, tambah, ubah jumlah, dan hapus item keranjang. | Pelanggan pemilik keranjang |
| Lokasi asal | `GET /api/admin/shipping-locations`, `POST /api/admin/shipping-locations`, `PUT /api/admin/shipping-locations/{id}`, `PATCH /api/admin/shipping-locations/{id}/status` | Kelola lokasi asal tersimpan. | Admin |
| Lokasi asal | `PATCH /api/admin/shipping-locations/{id}/select` | Memilih lokasi asal aktif dan menyimpan ID-nya di `settings`. | Admin |
| Ongkir | `POST /api/shipping/quote` | Menghitung jarak dan ongkir berdasarkan titik tujuan; lokasi asal diambil dari lokasi aktif tersimpan. | Pelanggan |
| Checkout | `POST /api/checkout` | Memvalidasi keranjang, menghitung total, membuat pesanan, menahan stok, dan memulai pembayaran. | Pelanggan |
| Pembayaran | `POST /api/payments/webhook` | Menerima notifikasi penyedia dan memverifikasinya di backend. | Midtrans |
| Pembayaran | `GET /api/orders/{id}/payment` | Melihat status pembayaran pesanan sendiri. | Pelanggan pemilik pesanan/Admin |
| Pesanan | `GET /api/orders`, `GET /api/orders/{id}` | Melihat daftar dan detail pesanan sesuai hak akses. | Pelanggan pemilik pesanan/Admin |
| Pesanan | `PATCH /api/admin/orders/{id}/status` | Memperbarui status pesanan sesuai alur sampai `dikirim` dan mencatat riwayat. | Admin/Owner |
| Penugasan kurir | `PATCH /api/admin/orders/{id}/courier` | Menugaskan atau mengganti Kurir pada pesanan dengan menyimpan `courier_id`. | Admin/Owner |
| Pesanan kurir | `GET /api/courier/orders`, `GET /api/courier/orders/{id}` | Melihat daftar dan detail pesanan yang ditugaskan kepada Kurir yang sedang login. | Kurir yang ditugaskan |
| Status kurir | `PATCH /api/courier/orders/{id}/status` | Mengubah status pesanan yang ditugaskan dari `dikirim` menjadi `selesai` dan mencatat riwayat. | Kurir yang ditugaskan |
| Analitik | `GET /api/admin/forecast` | Menampilkan rekomendasi Moving Average per produk atau informasi data belum cukup. | Admin |
| Analitik | `GET /api/admin/low-stock` | Menampilkan produk dengan stok `<= 5` dan saran produksi. | Admin |

### 7.1 Format respons API

Semua respons API menggunakan JSON.

Respons sukses:
```json
{
  "message": "Berhasil",
  "data": {}
}
```

Respons validasi/error:
```json
{
  "message": "Data tidak valid",
  "errors": {
    "field": ["Keterangan kesalahan"]
  }
}
```

Gunakan status HTTP yang sesuai, antara lain: `200` sukses, `201` data baru dibuat, `400` permintaan tidak valid, `401` belum login, `403` akses ditolak, `404` data tidak ditemukan, `422` validasi gagal, `500` kesalahan internal, dan `502` layanan eksternal gagal. Detail teknis internal tidak ditampilkan kepada pengguna.

## 8. Kriteria penerimaan dan Definition of Done

Pekerjaan dinyatakan selesai jika:

1. Fitur berjalan sesuai kebutuhan fungsional dan aturan bisnis pada dokumen ini.
2. Validasi dan otorisasi dilakukan di backend.
3. Data tersimpan sesuai relasi dan constraint database PPL 02.
4. Proses checkout, pembayaran, perubahan status, dan pengembalian stok menjaga konsistensi data.
5. Produk nonaktif atau stok tidak cukup tidak dapat di-checkout.
6. Ongkir dihitung dari rute jalan dan tidak menggunakan nilai perkiraan ketika layanan routing gagal.
7. Status pembayaran hanya berubah setelah verifikasi backend.
8. Riwayat status dan penjualan dicatat sesuai aturan.
9. Moving Average dan Rule-Based menghasilkan keluaran yang sesuai aturan perhitungan.
10. Pengujian modul terkait berhasil dan tidak merusak fungsi modul lain.
11. Tidak ada data rahasia atau detail kesalahan internal yang ditampilkan kepada pengguna.

## 9. Risiko dan penanganan

| Risiko | Penanganan |
|---|---|
| Notifikasi pembayaran duplikat atau terlambat | Terapkan verifikasi backend, idempotensi, dan pencatatan kasus pembayaran terlambat untuk pemeriksaan Admin/Owner. |
| Stok berubah bersamaan saat checkout | Validasi ulang dan gunakan transaksi database serta penguncian data yang sesuai. |
| Stok dikembalikan lebih dari sekali | Gunakan penanda `stok_dikembalikan` dan proses atomik. |
| Layanan routing gagal | Blokir checkout dan tampilkan pesan yang jelas; jangan memakai ongkir tebakan. |
| Lokasi asal belum dipilih atau nonaktif | Validasi lokasi aktif sebelum menghitung ongkir. |
| Data penjualan historis tidak cukup | Gunakan data simulasi berlabel jelas untuk pengujian; jangan mencampurnya dengan transaksi nyata. |
| Perbedaan pemahaman antaranggota tim | Jadikan PPL 01 sebagai acuan aturan bisnis dan PPL 02 sebagai acuan teknis skema serta relasi database. |

## 10. Temuan wawancara di luar ruang lingkup

Temuan wawancara yang tidak menjadi fitur MVP tetap dapat disimpan sebagai catatan pengembangan berikutnya. Fitur scoring lima produk terlaris dan diskon otomatis 10% untuk transaksi minimal Rp200.000 tidak dikembangkan pada MVP karena belum menjadi kebutuhan operasional pemilik usaha (O-3).

## 11. Keputusan dan pengendalian perubahan

1. PPL 01 menjadi acuan kebutuhan dan aturan bisnis.
2. PPL 02 menjadi acuan teknis ERD, nama tabel/kolom, migration, constraint, dan relasi model.
3. Jika implementasi membutuhkan perubahan aturan bisnis, perubahan harus disepakati Product Owner dan dicatat sebelum diterapkan.
4. Jika perubahan hanya menyangkut detail teknis database tanpa mengubah perilaku bisnis, perubahan dicatat pada PPL 02.
5. Semua anggota tim wajib menggunakan versi dokumen terbaru agar implementasi antarmodul konsisten.

---

**Catatan keselarasan dengan PPL 02:** kolom `orders.courier_id` yang nullable dan nilai role `kurir` pada `users.role` digunakan dalam MVP untuk mendukung penugasan kurir oleh Admin/Owner dan pembatasan akses pesanan berdasarkan kurir yang ditugaskan.
