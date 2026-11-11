# PPL 01 – Analisis Kebutuhan, Aturan Bisnis & Kontrak Integrasi Antarmodul

**Revisi konsolidasi:** 11 November 2026. Cakupan fitur tambahan Sprint 3–4 mengikuti PPL 23–42 dan WBS terbaru. Sprint 1–2, jumlah WP (42), jadwal, PIC, dan pembagian pekerjaan yang telah disepakati tidak diubah.

## 1. Ringkasan dan ruang lingkup

Dokumen ini menjadi acuan kebutuhan fungsional, aturan bisnis, dan kontrak data antarmodul Sistem Penjualan Kopi Tiga Titik. Dokumen ini menjadi dasar teknis PPL 02 (ERD, migrasi database, dan relasi data) serta pelaksanaan Work Package berikutnya.

Isi dokumen bersumber dari wawancara pemilik usaha dan pembeli (Nabil, 23 Oktober 2026), serta keputusan ruang lingkup proyek yang telah disepakati tim. Rujukan wawancara menggunakan kode P-nn untuk pembeli dan O-n untuk pemilik.

### 1.1 Fitur wajib MVP

Pengembangan fitur diselesaikan paling lambat 27 November 2026 sesuai akhir PPL 41 pada WBS. PPL 42 adalah QE akhir pada 28–29 November 2026. Proyek selesai 29 November 2026. Ruang lingkup final mencakup:

1. **Fondasi sistem:** basis data MySQL, migrasi, seeder, autentikasi, hak akses, validasi input, dan penanganan kesalahan.
2. **Kelola produk dan katalog:** Admin/Owner mengelola produk, harga, stok, foto, dan informasi produk; pelanggan melihat produk aktif.
3. **Keranjang dan checkout online:** pelanggan mengelola keranjang, mengisi data penerima, menentukan titik tujuan, memilih pengiriman atau ambil sendiri, dan melihat ringkasan transaksi.
4. **Pembayaran online:** Midtrans QRIS mode sandbox, verifikasi backend, penanganan beberapa percobaan pembayaran, status pembayaran, nota digital, dan tombol Hubungi Penjual.
5. **Pengiriman berbasis jarak:** perhitungan jarak rute OpenStreetMap/OpenRouteService, formula ongkir tetap, validasi koordinat, dan pembatasan wilayah Kota Mojokerto.
6. **Lokasi dan jam operasional:** Owner mengelola lokasi lapak aktif dan jam buka/tutup. Pengaturan hanya memengaruhi checkout baru; pesanan yang telah berhasil tetap memakai snapshot lokasi saat transaksi.
7. **Ambil sendiri:** pelanggan dapat memilih ambil sendiri dengan ongkir Rp0. Batas pengambilan 24 jam sejak pembayaran berhasil; Owner menandai pesanan selesai saat diambil, dan sistem menyelesaikannya otomatis jika melewati batas.
8. **Pengelolaan pesanan dan kurir:** Dashboard Owner dan Kurir, daftar/detail/filter pesanan, pengelolaan akun dan penugasan kurir, serta pembaruan status pengiriman.
9. **Notifikasi web:** notifikasi untuk Owner, Kurir, dan Pelanggan sesuai peristiwa sistem. Notifikasi bersifat sementara/real-time dan tidak menambah tabel domain baru.
10. **POS Kasir Offline:** Owner mencatat penjualan langsung di lapak, memilih produk dan jumlah, memilih pembayaran tunai atau QRIS langsung, lalu mengonfirmasi pembayaran. POS tidak menggunakan Midtrans atau webhook.
11. **Riwayat penjualan dan ekspor:** dashboard histori penjualan (grafik dan rekap harian/mingguan/bulanan) serta ekspor laporan ke Excel. Data online dan POS digabung; ongkir tidak dihitung sebagai omzet.
12. **Moving Average:** rekomendasi penjualan/kebutuhan minggu berikutnya berdasarkan tiga periode 7 hari, menggunakan penjualan nyata online dan POS.
13. **Rule-Based:** peringatan stok rendah ketika stok `<= 5` dan saran produksi; sistem tidak mengubah stok atau memproduksi otomatis.
14. **QE, integrasi, dokumentasi, dan kesiapan demo:** pengujian Sprint 3–4, integrasi end-to-end, regresi, dokumentasi teknis, dan QE akhir.

### 1.2 Di luar ruang lingkup

- Scoring lima produk terlaris dan dashboard analitik lain di luar dashboard histori penjualan yang telah ditetapkan.
- Integrasi API ekspedisi, pelacakan GPS langsung, optimasi rute multi-pesanan, dan manajemen armada.
- COD untuk pesanan online, pembayaran online selain Midtrans QRIS sandbox, serta integrasi Midtrans untuk transaksi POS.
- Notifikasi WhatsApp/email, verifikasi email, dan lupa kata sandi. Tombol Hubungi Penjual merupakan tautan kontak, bukan sistem notifikasi.
- Refund otomatis. Pembayaran online terlambat atau bermasalah ditandai untuk pemeriksaan Owner.
- Fitur yang tidak tercantum dalam daftar wajib MVP atau Work Package PPL 01–42.

## 2. Aktor dan hak akses

Sistem menggunakan tiga peran aplikasi: Admin/Owner, Kurir, dan Pelanggan.

| Aktor | Hak akses | Batasan |
|---|---|---|
| Admin/Owner | Mengelola produk, lokasi aktif dan jam operasional; menggunakan POS Kasir Offline; melihat dashboard histori penjualan dan ekspor Excel; melihat seluruh pesanan; mengelola akun/penugasan kurir; memperbarui status pengiriman; melihat pembayaran, Moving Average, peringatan stok, dan notifikasi. | Untuk POS, status pembayaran dicatat berdasarkan konfirmasi penjual. Untuk pembayaran online, Owner tidak dapat menetapkan status berhasil secara manual. Pesanan online yang sudah dibayar tidak dapat dibatalkan melalui fitur pembatalan. |
| Kurir | Melihat pesanan yang ditugaskan kepadanya dan memperbarui status pesanan dari `dikirim` menjadi `selesai` setelah pesanan diterima pelanggan. | Tidak dapat melihat pesanan kurir lain, mengubah data produk, mengubah pembayaran, atau mengubah status pesanan selain sesuai tugasnya. |
| Pelanggan | Mendaftar dan masuk; melihat katalog; mengelola keranjang sendiri; checkout dengan pilihan pengiriman atau ambil sendiri; melakukan pembayaran online; melihat status dan nota digital pesanan; menghubungi penjual; menerima notifikasi web; membatalkan pesanan yang belum dibayar. | Tidak dapat mengakses data pelanggan lain atau fungsi Admin/Owner dan Kurir. |

Akun Admin/Owner dikelola oleh pemilik usaha. Akun Kurir dibuat atau disediakan oleh Admin/Owner dan digunakan oleh kurir yang ditugaskan. Batas akun Kurir aktif adalah maksimal dua akun; validasi batas dilakukan di backend. Satu akun Admin/Owner digunakan bersama oleh tiga pemilik sesuai keputusan proyek. Pelanggan harus memiliki akun untuk melakukan checkout. Hak akses wajib diperiksa di backend, bukan hanya dengan menyembunyikan menu pada antarmuka. Penugasan kurir dilakukan oleh Admin/Owner melalui penyimpanan `orders.courier_id`.

**Catatan keselarasan database:** PPL 02 telah menyediakan nilai `admin`, `kurir`, dan `pelanggan` pada `users.role`, serta kolom `orders.courier_id` nullable sebagai relasi pengguna kurir yang ditugaskan. Kolom dan role tersebut digunakan untuk mendukung proses penugasan dan pengantaran pesanan.

## 3. Kebutuhan fungsional per modul

| Kode | Modul | Kebutuhan | Dasar |
|---|---|---|---|
| KF-01 | Basis data | Data produk, pengguna, keranjang, pesanan, pembayaran, riwayat status, penjualan, konfigurasi, dan lokasi pengiriman disimpan pada satu basis data MySQL melalui migrasi dan seeder. | O-1 |
| KF-02 | Autentikasi dan akses | Pengguna dapat mendaftar, masuk, dan keluar. Sistem membatasi akses berdasarkan tiga peran: Admin/Owner, Kurir, dan Pelanggan. Akun Kurir disediakan oleh Admin/Owner. | P-14, P-38, O-6 |
| KF-03 | Kelola produk | Admin dapat menambah, mengubah, mengaktifkan, dan menonaktifkan produk beserta harga, stok, foto, komposisi, deskripsi rasa, dan keawetan. | O-2 |
| KF-04 | Katalog | Pelanggan dapat melihat foto, nama, harga, komposisi, deskripsi rasa, keawetan 5–7 hari, dan stok. Produk dengan stok 0 atau status nonaktif tidak dapat dipesan. | O-2, P-12, P-16, P-18 |
| KF-05 | Keranjang | Pelanggan dapat menambah, mengubah jumlah, dan menghapus produk dari keranjang. Sistem menampilkan subtotal dan total sementara serta memvalidasi jumlah terhadap stok. | P-38, P-40 |
| KF-06 | Checkout | Pelanggan mengisi nama penerima, nomor telepon, dan catatan opsional. Untuk `pengiriman`, alamat serta koordinat tujuan wajib dan divalidasi; untuk `ambil_sendiri`, koordinat tujuan pengiriman tidak wajib. Sistem menampilkan ringkasan item, total belanja, dan ongkir sebelum pesanan dibuat. | O-6, P-19, P-20, P-22 |
| KF-07 | Pembayaran | Checkout online membuat pembayaran QRIS melalui Midtrans Sandbox dan hanya dianggap berhasil setelah verifikasi backend. POS menggunakan tunai/QRIS langsung dan konfirmasi Owner; POS tidak memakai Midtrans atau webhook. | O-5, P-27, P-28 |
| KF-08 | Ongkir | Sistem menghitung ongkir berdasarkan jarak rute jalan dari lokasi asal terpilih ke titik tujuan menggunakan layanan routing berbasis OpenStreetMap. | O-1, O-4, P-22 |
| KF-09 | Stok dan transaksi | Stok dikurangi/ditahan ketika pesanan dibuat. Jika pesanan belum dibayar dibatalkan atau kedaluwarsa, stok dikembalikan tepat satu kali. | P-40 |
| KF-10 | Daftar pesanan, penugasan kurir, dan status | Admin/Owner dapat melihat seluruh pesanan, memantau pembayaran, menugaskan kurir, dan memperbarui status sampai `dikirim`. Kurir hanya dapat melihat pesanan yang ditugaskan kepadanya dan menandai `selesai`. Pelanggan hanya dapat melihat pesanan miliknya. | O-6, P-33 |
| KF-11 | Moving Average | Sistem menghitung rekomendasi penjualan/kebutuhan minggu berikutnya dari rata-rata penjualan pada tiga periode 7 hari berturut-turut dalam 21 hari sebelum tanggal prediksi. | O-3 |
| KF-12 | Rule-Based restok | Sistem menampilkan peringatan ketika stok produk `<= 5` dan memberikan saran produksi berdasarkan standar stok harian. | O-3 |
| KF-13 | Lokasi asal pengiriman | Admin dapat menambah, mengubah, mengaktifkan/menonaktifkan, dan memilih lokasi asal tersimpan untuk perhitungan ongkir. | Keputusan proyek, 29 Oktober 2026 |
| KF-14 | Ambil sendiri | Pelanggan dapat memilih ambil sendiri dengan ongkir Rp0; lokasi, nama, alamat, dan koordinat pengambilan disimpan sebagai snapshot pada pesanan. Batas pengambilan 24 jam sejak pembayaran berhasil; Owner menandai selesai saat diambil dan sistem menyelesaikan otomatis setelah batas waktu. | Keputusan proyek, 10 November 2026 |
| KF-15 | Jam operasional | Owner dapat mengatur jam buka dan tutup. Checkout online baru ditolak di luar jam operasional; perubahan jam tidak mengubah pesanan yang telah berhasil dibuat/dibayar. | Keputusan proyek, 10 November 2026 |
| KF-16 | POS Kasir Offline | Owner dapat mencatat transaksi langsung di lapak, memilih produk/jumlah, metode tunai atau QRIS langsung, dan mengonfirmasi pembayaran. Transaksi POS tidak menggunakan Midtrans/webhook, mengurangi stok, dan masuk riwayat penjualan. | Keputusan proyek, 10 November 2026 |
| KF-17 | Notifikasi web | Owner menerima notifikasi pesanan baru dan stok menipis; Kurir menerima tugas baru; Pelanggan menerima perubahan status/lokasi pengambilan. Notifikasi sementara/real-time, tanpa tabel domain baru. | Keputusan proyek |
| KF-18 | Riwayat penjualan dan ekspor | Owner melihat grafik histori bulanan dan rekap harian/mingguan/bulanan jumlah botol serta omzet per varian dari transaksi online dan POS; ongkir tidak termasuk omzet. Owner dapat mengekspor rekap ke Excel. | Keputusan proyek, 10 November 2026 |
| KF-19 | Dashboard Owner dan Kurir | Owner melihat ringkasan operasional; Kurir melihat tugas yang ditugaskan kepadanya. Akses dan data dibatasi berdasarkan role. | PPL 27–28 |

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

Aturan pembayaran dibedakan berdasarkan sumber transaksi. Pembayaran online hanya dinyatakan berhasil setelah backend memverifikasi informasi resmi Midtrans. Pembayaran POS tunai/QRIS langsung dinyatakan berhasil setelah Owner mengonfirmasi transaksi. Respons atau tindakan browser tidak boleh menjadi bukti pembayaran online.

| Kode | Area | Aturan |
|---|---|---|
| BR-01 | Produk | Produk memiliki nama, harga dalam rupiah berupa bilangan bulat lebih dari 0, stok bilangan bulat 0 atau lebih, komposisi, deskripsi rasa, dan keawetan. Foto bersifat opsional. Ukuran produk MVP adalah 250 ml. |
| BR-02 | Produk | Perubahan harga, stok, atau status aktif produk oleh Admin akan tercermin pada katalog. |
| BR-03 | Katalog | Produk dengan stok 0 atau `aktif = false` tidak dapat dipesan. Produk nonaktif tidak ditampilkan pada katalog pelanggan. |
| BR-04 | Keranjang | Jumlah setiap item minimal 1 dan tidak boleh melebihi stok tersedia. Validasi dilakukan di backend dan diulang saat checkout. |
| BR-05 | Keranjang | Subtotal item = harga produk × jumlah. Total sementara keranjang = jumlah seluruh subtotal item. |
| BR-06 | Pemesanan | Pesanan dibuat dari stok yang tersedia, bukan pre-order. Tidak ada batas jumlah pesanan per pelanggan selain ketersediaan stok. |
| BR-07 | Checkout | Checkout membuat pesanan dengan status `menunggu_pembayaran`. Nama produk, rasa, harga satuan, jumlah, dan subtotal item disimpan sebagai snapshot pada detail pesanan. |
| BR-08 | Checkout | Pesanan menyimpan data penerima, nomor telepon, item, subtotal, ongkir, total, dan snapshot lokasi asal/pengambilan. Alamat serta koordinat tujuan wajib hanya untuk `pengiriman`; `ambil_sendiri` tidak membutuhkan koordinat tujuan. |
| BR-09 | Checkout | Total bayar = subtotal pesanan + ongkir. Pembuatan pesanan tidak berarti pembayaran telah berhasil. |
| BR-10 | Stok | Stok dikurangi/ditahan saat pesanan dibuat. Jika pesanan belum dibayar dibatalkan atau kedaluwarsa, stok dikembalikan tepat satu kali. |
| BR-11 | Pembayaran online | Metode pembayaran online MVP hanya QRIS melalui Midtrans dalam mode Sandbox. POS menggunakan tunai/QRIS langsung sesuai BR-39 dan tidak menggunakan payment gateway. |
| BR-12 | Pembayaran online | Status pembayaran online `berhasil` hanya diberikan setelah backend memverifikasi notifikasi resmi atau melakukan pengecekan resmi ke Midtrans. Pembayaran POS mengikuti konfirmasi langsung Owner sesuai BR-39. |
| BR-13 | Pembayaran | Status pembayaran terdiri dari `pending`, `berhasil`, `gagal`, dan `kedaluwarsa`. Batas pembayaran adalah 10 menit sejak pesanan dibuat. Notifikasi webhook duplikat tidak boleh memproses transaksi atau perubahan stok berulang kali. |
| BR-14 | Ongkir | Jarak `<= 5,5 km` dikenakan ongkir Rp0. Jarak di atas 5,5 km dikenakan Rp2.000 untuk setiap tingkat jarak 1 km berikutnya sesuai formula pada Bagian 4.1. |
| BR-15 | Ongkir | Jarak dihitung sebagai jarak rute jalan dari lokasi asal tersimpan yang dipilih Admin ke titik tujuan pelanggan, menggunakan layanan routing berbasis OpenStreetMap; bukan jarak garis lurus. |
| BR-16 | Ongkir | Untuk `pengiriman`, titik tujuan wajib, harus valid, dan berada di Kota Mojokerto. Jika routing gagal atau lokasi asal aktif tidak valid, checkout diblokir tanpa ongkir perkiraan. Aturan ini tidak berlaku untuk `ambil_sendiri`. |
| BR-17 | Status pesanan online pengiriman | Alur: `menunggu_pembayaran` → `diproses` → `dikirim` → `selesai`. Pesanan yang belum dibayar dapat dibatalkan pelanggan dan otomatis kedaluwarsa setelah 10 menit jika belum ada pembayaran terverifikasi. Owner menetapkan `dikirim`; Kurir yang ditugaskan menetapkan `selesai` setelah diterima pelanggan. Alur pickup dan POS mengikuti BR-41–BR-43. |
| BR-18 | Status pesanan dan penugasan | Admin/Owner hanya dapat mengubah status pesanan yang telah dibayar dari `diproses` menjadi `dikirim` dan menugaskan satu Kurir melalui `orders.courier_id`. Kurir hanya dapat mengubah pesanan yang ditugaskan kepadanya dari `dikirim` menjadi `selesai`. Pelanggan tidak dapat mengubah status setelah checkout, selain membatalkan pesanan yang belum dibayar. Pesanan yang sudah dibayar tidak dapat dibatalkan melalui fitur pembatalan. |
| BR-19 | Rule-Based | Jika stok produk `<= 5`, sistem menampilkan peringatan stok rendah. |
| BR-20 | Rule-Based | Saran produksi dihitung berdasarkan standar stok harian 10 unit per varian dan stok saat ini. Saran hanya rekomendasi; keputusan produksi tetap pada Owner. Nilai saran tidak boleh negatif. |
| BR-21 | Moving Average | Moving Average menggunakan 21 hari kalender sebelum tanggal prediksi, dibagi menjadi tiga periode berurutan yang masing-masing terdiri dari 7 hari. Periode dihitung mundur dari tanggal prediksi, bukan berdasarkan minggu kalender. |
| BR-22 | Moving Average | Perhitungan dilakukan per produk: (total penjualan periode 7 hari pertama + periode 7 hari kedua + periode 7 hari ketiga) / 3. Hari tanpa penjualan dihitung sebagai 0. Jika data belum mencakup tiga periode, sistem menampilkan informasi bahwa data belum cukup. |
| BR-23 | Riwayat penjualan | Penjualan online dicatat setelah pembayaran Midtrans terverifikasi berhasil; penjualan POS dicatat setelah Owner mengonfirmasi pembayaran tunai/QRIS langsung. Kedua sumber dicatat sebagai `transaksi` dan digabung dalam histori serta Moving Average. Data simulasi bersumber `simulasi` dan tidak boleh masuk perhitungan produksi. |
| BR-24 | Validasi dan error | Semua input penting divalidasi di backend. Pesan kesalahan kepada pengguna harus jelas dan tidak menampilkan detail teknis internal. |
| BR-25 | Akses | Admin/Owner dapat mengakses fungsi pengelolaan dan seluruh pesanan. Kurir hanya dapat mengakses pesanan yang ditugaskan kepadanya melalui `orders.courier_id`. Pelanggan hanya dapat mengakses keranjang dan pesanan miliknya sendiri. Pemeriksaan peran dan kepemilikan/penugasan data dilakukan di backend. |
| BR-26 | Snapshot pesanan | Perubahan harga, nama produk, profil pelanggan, atau keranjang setelah checkout tidak mengubah data pesanan yang sudah dibuat. |
| BR-27 | Produk nonaktif | Produk dinonaktifkan melalui `products.aktif = false`, bukan dihapus permanen melalui aplikasi. Riwayat transaksi produk tetap dipertahankan. |
| BR-28 | Idempotensi pembayaran | Notifikasi pembayaran yang sama hanya diproses satu kali. Notifikasi duplikat tidak membuat pembayaran atau pencatatan penjualan ganda dan tidak mengubah stok berulang kali. |
| BR-29 | Nominal pembayaran | Nominal yang dikonfirmasi penyedia harus sama dengan total pesanan. Jika tidak sama, pembayaran tidak langsung dianggap valid dan harus ditandai untuk pemeriksaan Admin/Owner. |
| BR-30 | Pembayaran terlambat | Jika pembayaran dikonfirmasi setelah pesanan kedaluwarsa, hasil pembayaran tetap dicatat secara akurat. Pesanan tidak diaktifkan kembali otomatis dan kasus ditandai untuk pemeriksaan Admin/Owner. Penanganan refund berada di luar ruang lingkup MVP. |
| BR-31 | Riwayat status | Setiap perubahan status pesanan dicatat pada `order_status_history` menggunakan kolom final `order_id`, `status_baru`, `diubah_oleh`, `waktu_perubahan`, dan `keterangan`. Aktor boleh NULL untuk proses otomatis. Perubahan status dan pencatatan riwayat dilakukan dalam satu transaksi database. |
| BR-32 | Pengembalian stok | Pengembalian stok harus aman terhadap proses bersamaan dan hanya boleh dilakukan satu kali, termasuk ketika proses kedaluwarsa dan notifikasi pembayaran berjalan berdekatan. |
| BR-33 | Batas wilayah | Pengiriman MVP dibatasi pada wilayah Kota Mojokerto. |
| BR-34 | Lokasi asal | Admin dapat menyimpan lebih dari satu lokasi asal, misalnya rumah produksi dan lapak. Lokasi disimpan dalam tabel `shipping_locations`. |
| BR-35 | Pemilihan lokasi asal | Hanya lokasi dengan `aktif = true` yang dapat dipilih. ID lokasi yang sedang digunakan disimpan pada `settings` dengan key `lokasi_pengiriman_aktif_id`. Backend wajib memvalidasi bahwa lokasi tersebut tersedia dan aktif. |
| BR-36 | Lokasi tidak valid | Jika lokasi asal belum dipilih, tidak ditemukan, atau nonaktif, perhitungan ongkir gagal dan checkout diblokir. |
| BR-37 | Perubahan lokasi | Perubahan lokasi aktif memengaruhi checkout baru. Pesanan yang sudah berhasil dibuat, termasuk ambil sendiri, tetap menggunakan snapshot nama, alamat, dan koordinat lokasi saat checkout; perubahan lokasi tidak memindahkan pesanan lama. |
| BR-38 | Konfigurasi | Seeder menyediakan konfigurasi awal secara idempoten, termasuk `batas_stok_minimum = 5` dan `stok_harian_standar = 10`. Menjalankan seeder berulang kali tidak boleh membuat key konfigurasi duplikat. |
| BR-39 | POS | POS hanya untuk transaksi offline di lapak. `orders.sumber_transaksi = pos`, `metode_pemenuhan = NULL`, dan `user_id` boleh NULL. Pembayaran menggunakan `tunai` atau `qris` langsung; status berhasil disimpan setelah konfirmasi Owner. POS tidak memanggil Midtrans/webhook. Karena transaksi lapak diserahkan langsung, setelah pembayaran dikonfirmasi dan barang diserahkan, `orders.status = selesai`. |
| BR-40 | Stok POS | Transaksi POS yang dikonfirmasi berhasil mengurangi stok secara atomik. Transaksi yang gagal atau belum dikonfirmasi tidak boleh mengurangi stok atau masuk penjualan nyata. |
| BR-41 | Ambil sendiri | Ambil sendiri memiliki ongkir Rp0 dan tidak memerlukan koordinat tujuan pengiriman. Snapshot lokasi pengambilan disimpan pada pesanan. Batas pengambilan 24 jam dihitung sejak pembayaran online berhasil. |
| BR-42 | Penyelesaian ambil sendiri | Owner dapat menandai pesanan selesai saat pelanggan mengambilnya. Jika tidak diambil sampai 24 jam, sistem mengubah status menjadi selesai otomatis; tidak ada refund otomatis. |
| BR-43 | Jam operasional | Owner dapat mengubah jam buka/tutup kapan saja. Jam berlaku untuk checkout online baru; pesanan yang sudah berhasil dibuat/dibayar tidak dibatalkan atau diubah karena perubahan jam. |
| BR-44 | Notifikasi web | Notifikasi untuk Owner (pesanan baru dan stok menipis), Kurir (penugasan baru), dan Pelanggan (perubahan status/lokasi pengambilan) bersifat sementara/real-time. Tidak menambahkan tabel domain notifikasi atau tabel aplikasi ke-11. Notifikasi stok menipis tidak dikirim berulang selama produk masih berada dalam episode stok rendah yang sama; kirim kembali setelah stok pulih di atas ambang lalu turun lagi. |
| BR-45 | Riwayat penjualan | Dashboard histori dan ekspor Excel menggabungkan penjualan online terverifikasi dan POS terkonfirmasi. Omzet produk tidak memasukkan ongkir. Pencatatan idempoten agar webhook/permintaan ulang tidak menggandakan penjualan. |
| BR-46 | Batas wilayah | Layanan pengiriman hanya untuk Kota Mojokerto, bukan Kabupaten Mojokerto. Validasi wilayah dilakukan terhadap titik/alamat tujuan. |

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
| `orders` | Data transaksi/pesanan: `id`, `kode`, `sumber_transaksi` (`online`/`pos`), `metode_pemenuhan` (`pengiriman`/`ambil_sendiri`, nullable untuk POS), `user_id` (nullable untuk POS), `courier_id`, `status`, `subtotal`, `ongkir`, `total`, `nama_pelanggan`, data penerima, koordinat tujuan, snapshot lokasi asal/pengambilan, `jarak_pengiriman`, `batas_pengambilan`, `catatan`, `stok_dikembalikan`, timestamps. | Checkout online, POS, pembayaran, pesanan, pengiriman, pengambilan |
| `order_items` | Rincian snapshot produk pada pesanan: `id`, `order_id`, `product_id`, `nama_produk`, `rasa`, `jumlah`, `harga_satuan`, `subtotal`. | Checkout, detail pesanan, riwayat transaksi |
| `payments` | Percobaan/catatan pembayaran: `id`, `order_id` (tidak unik; satu pesanan dapat memiliki beberapa percobaan Midtrans), `metode_pembayaran` (`midtrans`, `tunai`, `qris`), `referensi_provider` (unik nullable), `status`, `jumlah`, `kedaluwarsa_pada`, `waktu_verifikasi`, `payload_mentah`. POS menyimpan tunai/QRIS langsung tanpa referensi Midtrans. | Pembayaran online, POS, pesanan |
| `order_status_history` | Riwayat status: `id`, `order_id`, `status_baru`, `diubah_oleh` (nullable), `waktu_perubahan`, `keterangan`. | Pesanan, pelacakan status, audit perubahan |
| `sales_history` | Rekap penjualan: `id`, `product_id`, `tanggal`, `jumlah_terjual`, `sumber` (`transaksi` atau `simulasi`). | Moving Average, analitik |
| `settings` | Konfigurasi sistem: `id`, `key`, `value`, `description`. Key mencakup `batas_stok_minimum`, `stok_harian_standar`, `lokasi_pengiriman_aktif_id`, `jam_buka`, dan `jam_tutup`. | Rule-Based, ongkir, lokasi/jam operasional, konfigurasi |
| `shipping_locations` | Lokasi asal tersimpan: `id`, `nama_lokasi`, `latitude`, `longitude`, `alamat`, `aktif`. | Pengelolaan lokasi, perhitungan ongkir |

### 5.1 Relasi data utama

1. `users` memiliki banyak `cart_items`.
2. `users` memiliki banyak `orders` sebagai pelanggan dan banyak pesanan yang ditugaskan melalui relasi `courier_id` sebagai kurir.
3. `products` memiliki banyak `cart_items`, `order_items`, dan `sales_history`.
4. `orders` memiliki banyak `order_items`, `payments`, dan `order_status_history`.
5. Satu `orders` dapat memiliki beberapa baris `payments` untuk percobaan pembayaran Midtrans; setiap baris pembayaran mengacu pada satu pesanan.
6. `order_items` mengacu pada satu `order` dan satu `product`.
7. `order_status_history` mengacu pada satu `order`; `diubah_oleh` dapat kosong untuk perubahan otomatis oleh sistem.
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
9. **Transaksi berhasil → Riwayat penjualan:** transaksi online dicatat setelah Midtrans terverifikasi berhasil; transaksi POS dicatat setelah Owner mengonfirmasi pembayaran. Keduanya menggunakan `sumber=transaksi`; data uji memakai `sumber=simulasi`.
10. **Riwayat penjualan → Analitik:** dashboard histori dan ekspor Excel membaca gabungan transaksi online dan POS; Moving Average membaca penjualan nyata per produk untuk tiga periode 7 hari. Hasilnya hanya rekomendasi bagi Owner.
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
| POS | `POST /api/admin/pos/transactions` | Membuat transaksi lapak, memvalidasi produk/stok, menyimpan pesanan `sumber_transaksi=pos`, dan pembayaran tunai/QRIS langsung berdasarkan konfirmasi Owner. | Admin/Owner |
| Histori penjualan | `GET /api/admin/sales-history` | Menampilkan rekap penjualan online dan POS berdasarkan rentang waktu; omzet tidak memasukkan ongkir. | Admin/Owner |
| Ekspor | `GET /api/admin/sales-history/export` | Mengunduh rekap harian, mingguan, atau bulanan ke Excel. | Admin/Owner |
| Pengaturan jam | `PUT /api/admin/operating-hours` | Menyimpan jam buka dan tutup untuk validasi checkout online baru. | Admin/Owner |
| Notifikasi web | Mekanisme aplikasi sementara/real-time | Mengirim peristiwa pesanan baru, perubahan status/lokasi, tugas kurir, dan stok menipis tanpa tabel domain notifikasi. | Owner, Kurir, Pelanggan sesuai peristiwa |
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


## 12. Catatan konsolidasi perubahan Sprint 3–4 (11 November 2026)

Ketentuan pada bagian ini menegaskan bahwa fitur tambahan berikut merupakan bagian MVP dan harus dikerjakan dalam Work Package PPL 23–42 yang sudah ada; tidak dibuat Work Package baru.

- **PPL 23–24:** integrasi pembayaran Midtrans, halaman/status pembayaran, pilihan ambil sendiri dengan batas 24 jam, nota digital, dan tombol Hubungi Penjual.
- **PPL 25–26:** titik tujuan dan rincian ongkir checkout, lokasi lapak aktif, jam operasional, validasi koordinat, dan penolakan checkout online di luar jam operasional.
- **PPL 27–28:** Dashboard Owner/Kurir, daftar/detail/filter pesanan, akun dan penugasan kurir, pembaruan status pengiriman, serta notifikasi web.
- **PPL 29–30:** UI dan backend Moving Average, dashboard histori penjualan, rekap harian/mingguan/bulanan, penggabungan transaksi online dan POS, pendapatan tanpa ongkir, serta ekspor Excel.
- **PPL 31–32:** UI/backend peringatan restok dan POS Kasir Offline, pembayaran tunai/QRIS langsung, konfirmasi Owner, pengurangan stok, dan pencatatan ke histori penjualan.
- **PPL 33–42:** QE Sprint 3, integrasi end-to-end, dokumentasi, skenario/regresi, perbaikan bug, penyempurnaan UI, uji ulang pembayaran, integrasi akhir, dan QE akhir untuk seluruh fitur tambahan.

**Batas konsistensi:** WBS tetap berjumlah 42 WP. Fitur tambahan tidak boleh dikerjakan sebagai WP baru di luar PPL 23–42. Struktur database domain tetap 10 tabel; notifikasi web tidak menambah tabel domain. Pengiriman hanya Kota Mojokerto. Snapshot lokasi pada pesanan yang telah berhasil dibuat tidak berubah ketika Owner mengganti lokasi atau jam operasional.


---

## 13. Matriks keterlacakan User Story, Backlog, dan Work Package

Matriks ini diturunkan dari WBS revisi 11 November 2026. Nomor US dan BL mengikuti sheet `USER STORY` dan `DAFTAR BACKLOG`; PPL mengikuti sheet `SPRINT PLANNING` dan `SPRINT BACKLOG`. Satu US dapat terhubung ke lebih dari satu backlog dan satu backlog dapat dikerjakan pada beberapa WP.

| User Story | Backlog terkait | Work Package utama/pendukung |
|---|---|---|
| US-1 | BL-1, BL-20 | PPL 01–04, 17, 31–32, 34–35, 42 |
| US-2 | BL-3 | PPL 01–02, 06–07, 28, 37, 41–42 |
| US-3 | BL-12 | PPL 19, 23–24, 33–34, 40–42 |
| US-4 | BL-11 | PPL 19, 25–26, 33–35, 38, 42 |
| US-5 | BL-8, BL-11, BL-23 | PPL 03, 19, 26, 29–32, 33–36, 39, 42 |
| US-6 | BL-2 | PPL 05, 39, 42 |
| US-7 | BL-4 | PPL 03, 30, 35–36 |
| US-8 | BL-3 | PPL 06–07, 37, 41–42 |
| US-9 | BL-6 | PPL 10–11, 14, 34, 38, 42 |
| US-10 | BL-9 | PPL 13–14, 34, 36, 38, 42 |
| US-11 | BL-6 | PPL 10–11, 14, 34, 38, 42 |
| US-12 | BL-10 | PPL 15–16, 25–26, 34, 36, 38–39, 41–42 |
| US-13 | BL-10, BL-19 | PPL 15–16, 23–24, 25–26, 34, 36, 38–39, 41–42 |
| US-14 | BL-11 | PPL 19, 25–26, 33–35, 38, 42 |
| US-15 | BL-12 | PPL 19, 23–24, 33–34, 40–42 |
| US-16 | BL-13 | PPL 17–18, 23, 34, 40–42 |
| US-17 | BL-14 | PPL 14, 27–28, 34, 37, 41–42 |
| US-18 | BL-16 | PPL 27–28, 32–42 |
| US-19 | BL-18 | PPL 25–26, 33–35, 38–39, 42 |
| US-20 | BL-17 | PPL 23–24, 37, 39–42 |
| US-21 | BL-5 | PPL 08–09, 37, 41–42 |
| US-22 | BL-7 | PPL 08–09, 31–32, 34, 42 |
| US-23 | BL-13 | PPL 17–18, 23, 34, 40–42 |
| US-24 | BL-8 | PPL 31–32, 33–35, 39, 42 |
| US-25 | BL-23 | PPL 03, 29–30, 33–36, 39, 42 |
| US-26 | BL-15 | PPL 27–28, 34, 37, 41–42 |
| US-27 | BL-20 | PPL 31–32, 29–30, 33–35, 39–42 |
| US-28 | BL-18 | PPL 25–26, 33–35, 38–39, 42 |
| US-29 | BL-19 | PPL 23–24, 33–34, 38–39, 41–42 |
| US-30 | BL-11 | PPL 19, 25–26, 33–35, 38, 42 |
| US-31 | BL-21 | PPL 27–28, 33–34, 37, 41–42 |
| US-32 | BL-22 | PPL 27–28, 33–34, 37, 41–42 |
| US-33 | BL-16 | PPL 27–28, 32–42 |
| US-34 | BL-24 | PPL 29–30, 33–36, 39, 42 |
| US-35 | BL-25 | PPL 29–30, 35–36, 39, 41–42 |
| US-36 | BL-26 | PPL 27–32, 34–37, 39, 41–42 |

### 13.1 Interpretasi User Story yang harus dipakai lintas dokumen

1. **US-21 (hapus produk):** istilah hapus berarti menonaktifkan produk (`products.aktif = false`) agar histori transaksi tetap utuh. Penghapusan permanen tidak disediakan pada UI aplikasi.
2. **US-18 (lokasi pengambilan berpindah):** perubahan lokasi aktif hanya memengaruhi checkout baru. Notifikasi perubahan lokasi tidak boleh mengubah atau menyatakan snapshot pesanan lama ikut berpindah. Untuk pesanan yang sudah dibuat, detail tetap menampilkan snapshot lokasi pesanan tersebut.
3. **US-32 (status Kurir):** kalimat WBS yang menyebut Kurir memperbarui status “Dikirim dan Selesai” harus dibaca sesuai pembagian hak akses: Owner menetapkan `dikirim` dan menugaskan Kurir; Kurir hanya mengubah `dikirim` menjadi `selesai`. Ini adalah koreksi redaksional yang perlu diterapkan pada sheet User Story WBS agar tidak menjadi instruksi berbeda bagi PIC.
4. **US-12/US-13 (koordinat):** koordinat tujuan wajib untuk `pengiriman`, tidak wajib untuk `ambil_sendiri`.
5. **US-31 (akun Kurir):** jumlah akun Kurir aktif maksimal dua; validasi dilakukan backend saat pembuatan/pengaktifan akun.
6. **US-3/US-15 (pembayaran):** otomatisasi verifikasi berlaku untuk Midtrans online. POS QRIS langsung bukan payment gateway dan memerlukan konfirmasi Owner.

### 13.2 Daftar backlog dan aturan pengaitan WP

WBS mempunyai 26 backlog dan 36 User Story; semua nomor US-1 sampai US-36 digunakan setidaknya sekali. Sheet `DAFTAR BACKLOG` belum memiliki kolom ID PPL. Gunakan matriks Bagian 13 sebagai crosswalk resmi selama backlog spreadsheet belum ditambah kolom WP. Jangan menebak WP hanya dari judul backlog.

### 13.3 Crosswalk lengkap 26 backlog ke User Story dan Work Package

| ID backlog | Item backlog dari WBS | User Story | WP implementasi utama | WP pendukung |
|---|---|---|---|---|
| BL-1 | Basis Data Terpadu (Single Source of Truth) | US-1 | PPL 01–04, 17 | PPL 34–35, 42 |
| BL-2 | Antarmuka Responsif Mobile-First (HP & Desktop) | US-6 | PPL 05 | PPL 39, 42 |
| BL-3 | Autentikasi & Hak Akses (Owner, Kurir, Pelanggan) | US-2, US-8 | PPL 01–02, 06–07 | PPL 28, 37, 41–42 |
| BL-4 | Data Awal Produk & Data Simulasi Penjualan | US-7 | PPL 03 | PPL 30, 35–36 |
| BL-5 | Kelola Produk (Tambah, Ubah, Hapus) | US-21 | PPL 08–09 | PPL 37, 41–42 |
| BL-6 | Katalog Produk & Status Stok | US-9, US-11 | PPL 10–11 | PPL 14, 34, 38, 42 |
| BL-7 | Pembaruan Stok oleh Owner | US-22 | PPL 08–09 | PPL 31–32, 34, 42 |
| BL-8 | Peringatan Stok Menipis & Rekomendasi Produksi (Rule-Based) | US-5, US-24 | PPL 31–32 | PPL 27–28, 33–35, 39, 42 |
| BL-9 | Keranjang Belanja | US-10 | PPL 13–14 | PPL 34, 36, 38, 42 |
| BL-10 | Checkout, Titik Lokasi & Pilihan Pengambilan | US-12, US-13 | PPL 15–16, 23–26 | PPL 34, 36, 38–39, 41–42 |
| BL-11 | Ongkos Kirim Berbasis Jarak & Validasi Wilayah (Rule-Based) | US-4, US-5, US-14, US-30 | PPL 19, 25–26 | PPL 33–35, 38, 42 |
| BL-12 | Pembayaran QRIS (Sandbox) | US-3, US-15 | PPL 19, 23–24 | PPL 33–34, 40–42 |
| BL-13 | Batas Waktu Pembayaran, Penahanan Stok & Pembatalan Pesanan | US-16, US-23 | PPL 17–18, 23 | PPL 34, 40–42 |
| BL-14 | Status & Riwayat Pesanan | US-17 | PPL 14, 27–28 | PPL 34, 37, 41–42 |
| BL-15 | Daftar Pesanan Online untuk Owner | US-26 | PPL 27–28 | PPL 34, 37, 41–42 |
| BL-16 | Notifikasi di Web | US-18, US-33 | PPL 27–28, 32 | PPL 33–42 |
| BL-17 | Nota Digital & Tombol Hubungi Penjual | US-20 | PPL 23–24 | PPL 37, 39–42 |
| BL-18 | Pengaturan Lokasi & Jam Operasional | US-19, US-28 | PPL 25–26 | PPL 33–35, 38–39, 42 |
| BL-19 | Pesanan Ambil Sendiri (Batas 24 Jam) | US-13, US-29 | PPL 23–24 | PPL 33–34, 38–39, 41–42 |
| BL-20 | Pencatatan Penjualan Offline di Lapak | US-1, US-27 | PPL 31–32 | PPL 29–30, 33–35, 39–42 |
| BL-21 | Akun & Penugasan Kurir | US-31 | PPL 27–28 | PPL 33–34, 37, 41–42 |
| BL-22 | Tugas & Status Pengiriman Kurir | US-32 | PPL 27–28 | PPL 33–34, 37, 41–42 |
| BL-23 | Prediksi Penjualan (Moving Average) | US-5, US-25 | PPL 03, 29–30 | PPL 33–36, 39, 42 |
| BL-24 | Dashboard Histori Penjualan | US-34 | PPL 29–30 | PPL 33–36, 39, 42 |
| BL-25 | Ekspor Laporan Penjualan ke Excel | US-35 | PPL 29–30 | PPL 35–36, 39, 41–42 |
| BL-26 | Dashboard Owner — Ringkasan Operasional | US-36 | PPL 27–32 | PPL 34–37, 39, 41–42 |

**Pemeriksaan kelengkapan:** WBS memuat US-1 sampai US-36, BL-1 sampai BL-26, dan PPL 01 sampai PPL 42. Tidak ada nomor User Story yang hilang atau berada di luar rentang. Setiap backlog di atas terhubung ke US dan WP. PPL 33–42 adalah WP pendukung QE/integrasi/perbaikan, bukan backlog fitur baru.

### 13.3 Urutan prioritas sumber kebenaran

- Kebutuhan, aktor, dan aturan bisnis: PPL 01.
- Perilaku produk, fitur, acceptance criteria, dan UI: PRD.
- Pembagian siapa mengerjakan apa dan kapan: WBS terbaru.
- Struktur tabel, kolom, tipe, constraint, relasi: PPL 02 dan migration/model aktual.
- Kontrak request/response, integrasi antarmodul, frontend/backend: Kontrak Frontend–Backend.
- Detail integrasi Midtrans dan routing: PPL 19, tidak boleh mengubah aturan bisnis PPL 01/PRD.

Jika ditemukan selisih, hentikan asumsi lokal, catat konflik, dan minta persetujuan PO sebelum mengubah schema, route, aturan, atau alokasi WP.


### 13.4 Koreksi tujuan Sprint 3 pada WBS

Deskripsi Work Package PPL 23–32 sudah memuat fitur tambahan, tetapi tujuan Sprint 3 pada ringkasan Sprint Planning WBS perlu diperluas agar mencakup: pembayaran Midtrans; pickup 24 jam, nota dan kontak; lokasi aktif/jam operasional/ongkir OSM; dashboard Owner/Kurir, daftar pesanan, akun dan assignment Kurir; notifikasi; Moving Average; histori penjualan dan ekspor; Rule-Based; POS offline. Perubahan ini hanya memperjelas tujuan sprint, bukan menambah WP, PIC, estimasi, atau tanggal. Ambang stok harus tertulis `stok <= 5`, bukan `stok < 5`.

### 13.5 Catatan perbedaan redaksi WBS yang belum dapat diperbaiki melalui dokumen ini

Sheet WBS sumber masih perlu pembaruan redaksional pada US-18 dan US-32 serta penambahan kolom ID PPL pada `DAFTAR BACKLOG` bila tim menghendaki crosswalk langsung di spreadsheet. Dokumen konsolidasi ini menetapkan interpretasi bisnis yang benar, tetapi tidak mengubah workbook WBS. Karena itu, sebelum WBS dibagikan sebagai versi final berikutnya, PO perlu memperbarui tiga hal tersebut agar tidak ada dua instruksi berbeda.
