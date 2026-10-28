# **PPL 01 - Analisis Kebutuhan, Aturan Bisnis & Kontrak Integrasi Antarmodul** 

## **Ringkasan dan ruang lingkup** 

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

## **Aktor dan hak akses** 

Sistem punya tiga peran terautentikasi, Owner (Admin/Penjual), Kurir, dan Pelanggan; hak akses ditegakkan di backend, bukan hanya dengan menyembunyikan menu. 

|**Aktor**|**Boleh**|**Tidak boleh**|
|---|---|---|
|Owner (Admin/Penjual)|Kelola produk (tambah, ubah, aktif/nonaktif,<br>harga, stok, foto); lihat daftar dan detail semua<br>pesanan; mengubah status pesanan sampai<br>Pesanan Dikirim; menugaskan kurir; melihat<br>status pembayaran, Moving Average, dan<br>peringatan restok|Mengubah status pembayaran menjadi<br>berhasil secara manual; membatalkan<br>pesanan yang sudah dibayar melalui fitur<br>pembatalan|
|Kurir|Melihat hanya pesanan yang ditugaskan<br>kepadanya (alamat, titik peta, kontak<br>penerima, catatan) dan menandai Selesai<br>setelah pesanan diantar dan diterima pembeli|Mengelola produk; melihat pesanan kurir lain;<br>mengubah status selain Selesai|
|Pelanggan|Daftar dan masuk; lihat katalog; kelola<br>keranjang sendiri; checkout; membayar;<br>melihat pesanan sendiri|Membuka data pelanggan lain; fungsi Owner<br>dan Kurir|



Akun Admin hanya dipegang pemilik . Akun Kurir dibuat oleh Owner. Setiap pesanan memiliki paling banyak satu kurir aktif; satu kurir dapat menerima banyak pesanan. Kurir hanya boleh mengakses pesanan yang ditugaskan kepadanya. 

## **Kebutuhan fungsional per modul** 

Dua belas kebutuhan di bawah menutup seluruh fitur wajib; kolom User Story merujuk nomor di sheet USER STORY (US 2 dan US 24 disesuaikan: tanpa peran Kurir, ambang restok < 5). 

Dokumen kerja — keputusan yang belum ditetapkan ditandai pada bagian konfirmasi 

|**Kode**|**Modul**|**Kebutuhan**<br>|**User Story**|**Wawancara**|
|---|---|---|---|---|
|KF-01|Basis data|Semua data pesanan dan<br>penjualan tersimpan<br>dalam satu basis data<br>terpadu, dibuat lewat<br>migrasi dan seeder;<br>pencatatan manual di<br>Excel yang rawan salah<br>diganti|US 1|O-1|
|KF-02|Autentikasi dan akses|Daftar (nama, email, kata<br>sandi), masuk, keluar; sesi<br>tetap tersimpan agar tidak<br>login berulang; akses<br>dibatasi menurut tiga<br>peran (Owner, Kurir,<br>Pelanggan)|US 2, 8|P-14, P-38, O-6|
|KF-03|Kelola produk|Admin/Penjual<br>menambah, mengubah,<br>mengaktifkan, dan<br>menonaktifkan produk<br>beserta harga, stok, foto,<br>komposisi, deskripsi rasa,<br>dan keawetan|US 21, 22|O-2|
|KF-04|Katalog|Pelanggan melihat foto,<br>nama, harga, komposisi,<br>deskripsi rasa, keawetan<br>5-7 hari, dan jumlah stok;<br>tombol pesan nonaktif saat<br>stok 0|US 9, 11|O-2, P-12, P-16, P-18|
|KF-05|Keranjang|Tambah, ubah jumlah per<br>varian, hapus; tampil<br>subtotal dan total<br>sementara; jumlah<br>divalidasi terhadap stok|US 10|P-38, P-40|
|KF-06|Checkout|Nama penerima, nomor<br>yang dapat dihubungi,<br>detail alamat, titik lokasi di<br>peta (geser pin atau<br>gunakan lokasi saya),<br>catatan khusus; ringkasan<br>pesanan dengan ongkir<br>sebelum membayar;<br>membuat pesanan|US 12, 14|O-6, P-19, P-20, P-22|
|KF-07|Pembayaran sandbox|Pembayaran QRIS lewat<br>satu penyedia (Midtrans,<br>Xendit, atau Duitku) dalam<br>mode sandbox, status<br>diverifikasi backend|US 3, 15|O-5, P-27, P-28|
|KF-08|Ongkir OSM|Ongkir dihitung dari jarak<br>rute rumah produksi ke<br>tujuan memakai data<br>OpenStreetMap,<br>ditampilkan saat checkout|US 4, 14|O-1, O-4, P-22|
|KF-09|Stok dan transaksi|Stok ditahan saat pesanan<br>dibuat dan dikembalikan<br>bila gagal, kedaluwarsa,<br>atau dibatalkan|US 23|P-40|
|KF-10|Daftar pesanan dan status|Admin/Penjual melihat<br>daftar dan detail,<br>memantau status pesanan<br>dan pembayaran,<br>mengubah status sampai<br>Pesanan Dikirim; Kurir<br>menekan Selesai setelah<br>pesanan diterima pembeli|US 17, 26|O-6, P-33|
|KF-11|Moving Average|Estimasi penjualan minggu<br>berikutnya dari rata-rata 3<br>minggu terakhir untuk<br>mengantisipasi lonjakan<br>permintaan|US 25|O-3|
|KF-12|Rule-Based restok|Peringatan restok saat<br>stok < 5, dengan saran<br>produksi mengacu standar|US 24|O-3|



Dokumen kerja — keputusan yang belum ditetapkan ditandai pada bagian konfirmasi 

stok harian 10 

### **Data awal 4 varian produk (dari wawancara pemilik, ukuran 250 ml)** 

|**Varian**|**Rasa**|**Harga**|
|---|---|---|
|Titik Jenuh|Coklat susu|Rp12.000|
|Titik Manja|Kopi karamel|Rp10.000|
|Titik Temu|Kopi gula aren|Rp10.000|
|Titik Hitam|Americano|Rp8.000|



Keawetan 5-7 hari bila disimpan di lemari pendingin. Rekap penjualan 3 minggu terakhir tidak ada (O-3), sehingga PPL 03 membuat data simulasi penjualan yang diberi label jelas. 

## **Aturan bisnis** 

Aturan paling penting: pesanan hanya berstatus "dibayar" setelah backend memverifikasi pembayaran; tombol atau respons frontend tidak pernah mengubah status itu. 

|**Kode**|**Area**|**Aturan**|**Dasar**|
|---|---|---|---|
|BR-01|Produk|Produk punya nama, harga<br>bilangan bulat rupiah lebih dari 0,<br>stok bilangan bulat 0 atau lebih,<br>komposisi, deskripsi rasa, dan<br>keawetan; foto opsional; ukuran<br>hanya 250 ml|O-2|
|BR-02|Produk|Perubahan stok oleh<br>Admin/Penjual langsung tampil di<br>katalog|P-12|
|BR-03|Katalog|Produk dengan stok 0 tidak dapat<br>dipesan; tombol pesan nonaktif;<br>jumlah stok ditampilkan|P-12|
|BR-04|Keranjang|Jumlah per item minimal 1 dan<br>tidak melebihi stok tersedia;<br>validasi di backend; saat checkout<br>stok divalidasi ulang dan bila tidak<br>cukup tampil pesan stok tidak<br>tersedia|P-40|
|BR-05|Keranjang|Subtotal = harga x jumlah; total<br>sementara = jumlah seluruh<br>subtotal|US 10|
|BR-06|Pemesanan|Pesanan dibuat dari stok yang<br>tersedia (bukan pre-order); tidak<br>ada batas jumlah pesanan per<br>orang selain stok|O-2, P-13|
|BR-07|Checkout|Checkout membuat pesanan<br>berstatus<br>menunggu\_pembayaran; harga<br>dan jumlah disalin ke pesanan<br>(snapshot)|US 12|
|BR-08|Checkout|Pesanan memuat data pelanggan,<br>item, jumlah, harga, total belanja,<br>alamat dan koordinat tujuan,<br>ongkir, dan informasi<br>pembayaran; ringkasan item, total,<br>dan ongkir tampil sebelum<br>membayar|P-19|
|BR-09|Checkout|Total bayar = total belanja +<br>ongkir; menekan tombol checkout<br>tidak membuat pesanan dianggap<br>terbayar|US 14|
|BR-10|Stok|Stok ditahan saat pesanan dibuat;<br>dikembalikan bila pembayaran<br>gagal atau kedaluwarsa, atau<br>pesanan dibatalkan|US 23|
|BR-11|Pembayaran|Metode QRISlewat satu penyedia|O-5,P-27|



Dokumen kerja — keputusan yang belum ditetapkan ditandai pada bagian konfirmasi 

|||sandbox yang dipilih tim; tidak<br>memakai uang sungguhan||
|---|---|---|---|
|BR-12|Pembayaran|Status dibayar hanya diberikan<br>setelah verifikasi backend<br>(webhook terverifikasi atau<br>pengecekan resmi penyedia)|Keputusan proyek|
|BR-13|Pembayaran|Status pembayaran: pending,<br>berhasil, gagal, kedaluwarsa;<br>webhook yang sama tidak<br>memproses ulang pesanan<br>(idempoten); batas waktu bayar<br>10 menit; gagal atau kedaluwarsa<br>memunculkan peringatan di web<br>(pop-up atau banner)|P-29, P-31|
|BR-14|Ongkir|Ongkir dihitung dari jarak rute<br>jalan: jarak <= 5,5 km gratis; > 5,5<br>km dan < 6,5 km Rp2.000; >= 6,5<br>km dan < 7,5 km Rp4.000; >= 7,5<br>km dan < 8,5 km Rp6.000; dan<br>seterusnya naik Rp2.000 untuk<br>setiap tingkat jarak 1 km. Untuk<br>jarak d > 5,5 km, tarif = Rp2.000 x<br>(floor(d - 5,5) + 1). Tarif<br>ditampilkan sebelum pembayaran<br>dan dihitung ulang bila pin/alamat<br>berubah.|Keputusan proyek|
|BR-15|Ongkir|Jarak adalah jarak rute jalan dari<br>rumah produksi (Ds. Ndaleman,<br>Kec. Sooko, Kab. Mojokerto;<br>koordinat disimpan di konfigurasi)<br>atau lapak ke titik tujuan, lewat<br>layanan routing berbasis<br>OpenStreetMap; ongkir tampil<br>saat checkout|O-4, P-22|
|BR-16|Ongkir|Titik tujuan wajib dan valid, dipilih<br>dengan menggeser pin; bila<br>routing gagal, tampil pesan error<br>dan checkout tidak dilanjutkan<br>dengan ongkir tebakan|P-20|
|BR-17|Status pesanan|Alur: Menunggu Pembayaran -><br>Pesanan Diproses -> Pesanan<br>Dikirim -> Selesai (kode:<br>menunggu_pembayaran,<br>diproses, dikirim, selesai).<br>Pesanan belum dibayar dapat<br>dibatalkan pelanggan; pesanan<br>yang belum dibayar kedaluwarsa<br>otomatis setelah 10 menit.<br>Pesanan yang sudah dibayar tidak<br>dapat dibatalkan melalui fitur<br>pembatalan.|Keputusan proyek|
|BR-18|Status pesanan|Owner dapat mengubah status<br>pesanan yang sudah dibayar<br>sampai Dikirim. Kurir hanya dapat<br>menandai Selesai pada pesanan<br>berstatus Dikirim yang ditugaskan<br>kepadanya, setelah pesanan<br>diterima pembeli. Pelanggan tidak<br>dapat mengubah atau<br>membatalkan pesanan yang<br>sudah dibayar.|Keputusan proyek|
|BR-19|Rule-Based|JIKA stok produk < 3 MAKA tampil<br>peringatan restok; stok tepat 3<br>tidak memicu peringatan|Keputusan proyek|
|BR-20|Rule-Based|Pada peringatan, sistem<br>menampilkan saran produksi<br>berdasarkan standar stok harian<br>10 unit per varian dan stok saat<br>ini. Saran hanya rekomendasi;<br>keputusan produksi tetap pada<br>Owner. Nilai saran tidak boleh<br>negatif.|O-3 dan keputusan proyek|
|BR-21|Moving Average|Saran kebutuhan/permintaan stok<br>minggu berikutnya dihitung|O-3 dan keputusan proyek|



Dokumen kerja — keputusan yang belum ditetapkan ditandai pada bagian konfirmasi 

|||sebagai (penjualan minggu 1 +<br>minggu 2 + minggu 3) / 3 per<br>produk. Jika data kurang dari 3<br>minggu, sistem menampilkan<br>pesan data belum cukup. Hasil<br>hanya rekomendasi; Owner<br>menentukan produksi dan tidak<br>ada perubahan stok otomatis.||
|---|---|---|---|
|BR-22|Moving Average|Penjualan dihitung dari pesanan<br>yang pembayarannya terverifikasi<br>berhasil dari online maupun<br>offline(status pesanan diproses,<br>dikirim, atau selesai). Karena data<br>historis 3 minggu belum tersedia,<br>data seeder harus diberi label<br>simulasi.|O-3|
|BR-23|Validasi dan error|Semua input divalidasi di<br>backend; error dikembalikan<br>dalam format standar (lihat<br>kontrak)|Fondasi|
|BR-24|Akses|Setiap endpoint memeriksa peran;<br>Pelanggan hanya mengakses<br>data miliknya; Kurir hanya fungsi<br>pengantaran; akun admin hanya<br>dipegang pemilik|O-6|
|BR-25|Sesi|Sesi login dapat tetap tersimpan<br>sesuai mekanisme autentikasi<br>yang dipilih, dengan tetap<br>menjaga keamanan akun.|P-38|



## **Kontrak integrasi antarmodul** 

Setiap modul bertukar data lewat API JSON dengan entitas dan status di bawah, sehingga UI dan backend dapat dikerjakan paralel begitu kontrak ini disepakati. Konvensi: harga dalam rupiah (bilangan bulat), waktu format ISO 8601, koordinat lat/lng desimal, autentikasi sesi atau token per peran. 

### **Entitas data** 

|**Entitas**|**Field utama**|**Catatan**|
|---|---|---|
|users|id, nama, email, password\_hash, role (admin,<br>kurir, pelanggan)|email unik; akun admin hanya dipegang<br>pemilik|
|products|id, nama, rasa, komposisi, deskripsi, keawetan,<br>harga, stok, foto_path, aktif|stok >= 0; ukuran selalu 250 ml; produk<br>nonaktif tidak tampil/tidak dapat dipesan;<br>transaksi lama tetap tersimpan|
|cart\_items|id, user\_id, product\_id, jumlah|satu baris per produk per pengguna|
|orders|id, kode, user_id, courier_id, status, subtotal,<br>ongkir, total, nama_penerima, telepon, alamat,<br>lat, lng, catatan, created_at, stok_dikembalikan|courier_id nullable sebelum penugasan; detail<br>harga/alamat menjadi snapshot;<br>stok_dikembalikan mencegah pengembalian<br>stok ganda|
|order\_items|id, order\_id, product\_id, nama\_produk,<br>harga, jumlah|snapshot nama dan harga|
|payments|id, order\_id,provider, referensi\_provider,<br>status, jumlah, kedaluwarsa\_pada,<br>waktu\_verifikasi, payload\_mentah|status hanya diubah oleh verifikasi backend|
|sales\_history|id, product\_id, bulan\_mulai, jumlah\_terjual,<br>sumber (simulasi atau transaksi)|sumber data Moving Average; data awal<br>berlabel simulasi|
|settings|kunci, nilai / key, value|koordinat rumah produksi, batas ongkir dan<br>tarif bertingkat, ambang restok (3), standar<br>stok harian (10)|
|order_status_history|id,order_id,status_sebelumnya,<br>status_baru,diubah_oleh,<br>waktu_perubahan, keterangan|Setiap perubahan status wajib dicatat;<br>perubahan status dan riwayat dicatat<br>dalam satu transaksi database.|



Dokumen kerja — keputusan yang belum ditetapkan ditandai pada bagian konfirmasi 

### **Status** 

|**Objek**|**Nilai (kode)**|**Tampilan**|
|---|---|---|
|order.status|menunggu\_pembayaran, diproses, dikirim,<br>selesai, dibatalkan, kedaluwarsa|Menunggu Pembayaran, Pesanan Diproses,<br>Pesanan Dikirim, Selesai, Dibatalkan,<br>Kedaluwarsa|
|payment.status|pending, berhasil, gagal, kedaluwarsa|Menunggu, Berhasil, Gagal, Kedaluwarsa|



### **Endpoint API** 

|**Modul**|**Metode dan path**|**Fungsi**|**Peran**|
|---|---|---|---|
|Auth|POST /api/register, POST<br>/api/login, POST /api/logout|Daftar, masuk, keluar|Tamu / semua|
|Produk|GET /api/products, GET<br>/api/products/{id}|Katalog dan detail;<br>mengembalikan stok dan<br>flag stok\_habis|Semua|
|Produk|POST /api/admin/products,<br>PUT /api/admin/products/{id},<br>PATCH<br>/api/admin/products/{id}/status|Tambah, ubah (termasuk<br>harga, stok, foto),<br>aktif/nonaktif; tidak ada<br>penghapusan permanen<br>melalui aplikasi|Admin|
|Keranjang|GET /api/cart, POST<br>/api/cart/items, PATCH<br>/api/cart/items/{id}, DELETE<br>/api/cart/items/{id}|Lihat (dengan subtotal dan<br>total), tambah, ubah<br>jumlah, hapus|Pelanggan|
|Ongkir|POST /api/shipping/quote|Masukan: lat, lng tujuan.<br>Keluaran: jarak\_km,<br>ongkir, atau error routing|Pelanggan|
|Checkout|POST /api/checkout|Masukan: data penerima,<br>lat, lng, catatan. Membuat<br>pesanan<br>menunggu\_pembayaran,<br>menahan stok, membuat<br>tagihan pembayaran|Pelanggan|
|Pembayaran|POST /api/payments/webhook|Notifikasi penyedia; tanda<br>tangan diverifikasi<br>sebelum status diubah|Penyedia|
|Pembayaran|GET /api/orders/{id}/payment|Status pembayaran terkini|Pelanggan, owner|
|Pesanan|GET /api/orders, GET<br>/api/orders/{id}|Pelanggan melihat<br>miliknya; Admin melihat<br>semua, dengan filter<br>status|Pelanggan, owner|
|Pesanan|PATCH<br>/api/admin/orders/{id}/status|Memajukan status sesuai<br>alur sampai Dikirim;<br>mencatat riwayat status|Admin|
|Analitik|GET /api/admin/forecast|Estimasi Moving Average<br>per produk, atau pesan<br>data belum cukup|Admin|
|Analitik|GET /api/admin/low-stock|Daftar produk dengan stok<br>< 3|Admin|
|Penugasan kurir|PATCH<br>/api/admin/orders/{id}/courier|Menugaskan atau<br>mengganti kurir pada<br>pesanan|Admin|
|Pesanan kurir|GET /api/courier/orders, GET<br>/api/courier/orders/{id}|Melihat daftar dan detail<br>pesanan yang ditugaskan<br>kepada kurir yang login|Kurir|
|Pengantaran|PATCH<br>/api/courier/orders/{id}/complete|Menandai selesai setelah<br>pesanan diterima<br>pelanggan; hanya untuk<br>pesanan yang ditugaskan|Kurir|



Format respons API: semua respons menggunakan JSON. Respons sukses memakai {"message":"...","data":{...}} sesuai kebutuhan endpoint. Respons error memakai {"message":"...","errors":{"field":["..."]}} bila relevan. Gunakan status HTTP yang sesuai: 200 sukses, 201 data baru dibuat, 400 permintaan tidak valid, 401 belum login, 403 peran tidak sesuai, 404 data tidak 

Dokumen kerja — keputusan yang belum ditetapkan ditandai pada bagian konfirmasi 

ditemukan, 422 validasi gagal, 500 kesalahan internal, dan 502 layanan eksternal gagal. Detail teknis internal tidak ditampilkan ke pengguna. 

Semua endpoint harus memeriksa autentikasi dan otorisasi di backend. Pelanggan hanya mengakses data sendiri; kurir hanya pesanan yang ditugaskan; Admin/Owner hanya satu akun yang dikelola pemilik. Produk dinonaktifkan memakai PATCH /api/admin/products/{id}/status , bukan DELETE. Harga, jumlah, subtotal, ongkir, total, dan alamat pesanan disimpan sebagai snapshot pada saat checkout. 

## **Aturan bisnis tambahan hasil konfirmasi 28 Oktober 2026** 

|**Kode**|**Area**|**Aturan final**|
|---|---|---|
|BR-26|Penugasan kurir|Tabelordersmemilikicourier_idyang<br>mereferensikanusers.iddengan rolekurir;<br>nilainya boleh NULL sebelum penugasan. Satu<br>pesanan memiliki satu kurir aktif, satu kurir<br>dapat menangani banyak pesanan. Backend<br>memeriksa penugasan pada setiap permintaan<br>kurir.|
|BR-27|Penonaktifan produk|Produk dinonaktifkan denganproducts.aktif =<br>false; produk tidak tampil di katalog dan tidak<br>dapat dipesan. Riwayat transaksi<br>dipertahankan dan produk dapat diaktifkan<br>kembali. Tidak ada penghapusan permanen<br>produk melalui aplikasi.|
|BR-28|Pembayaran QRIS|Pembayaran hanya dianggap berhasil setelah<br>backend menerima dan memverifikasi<br>notifikasi resmi penyedia atau melakukan<br>pemeriksaan resmi. Frontend tidak dapat<br>mengubah status pembayaran.|
|BR-29|Idempotensi pembayaran|Notifikasi webhook yang sama hanya diproses<br>satu kali; notifikasi duplikat tidak membuat<br>transaksi ganda atau mengubah stok berulang<br>kali.|
|BR-30|Nominal pembayaran|Nominal yang dikonfirmasi penyedia harus<br>sama dengan total pesanan. Ketidaksesuaian<br>ditandai untuk pemeriksaan Admin/Owner dan<br>tidak langsung dianggap valid.|
|BR-31|Notifikasi terlambat|Jika pembayaran berhasil dikonfirmasi setelah<br>pesanan kedaluwarsa, hasil pembayaran tetap<br>dicatat secara akurat, pesanan tidak diaktifkan<br>kembali otomatis, dan kasus ditandai untuk<br>pemeriksaan Admin/Owner.|
|BR-32|Snapshot pesanan|Saat checkout, nama produk, harga satuan,<br>jumlah, subtotal, ongkir, total, dan alamat<br>pengiriman disimpan sebagai snapshot.<br>Perubahan harga produk, profil, atau keranjang<br>tidak mengubah pesanan yang sudah dibuat.|
|BR-33|Riwayat status|Setiap perubahan status pesanan dicatat<br>dalamorder_status_history, termasuk status<br>sebelumnya, status baru, waktu, dan aktor;<br>aktor boleh NULL untuk proses otomatis.|
|BR-34|Pengembalian stok|Stok yang dicadangkan dikembalikan saat<br>pesanan belum dibayar dibatalkan atau<br>kedaluwarsa, tepat satu kali. Backend harus<br>mencegah pengembalian ganda, termasuk<br>saat webhook dan proses kedaluwarsa<br>berjalan bersamaan.|
|BR-35|Pembatalan|Pelanggan boleh membatalkan pesanan<br>sebelum pembayaran. Pesanan berstatus<br>dibayar tidak dapat dibatalkan pelanggan atau<br>melalui fitur pembatalan Admin/Owner.<br>Kebijakan refund tetap di luar ruang lingkup<br>fitur ini.|
|BR-36|Batas pembayaran|Batas waktu pembayaran adalah 10 menit<br>sejak pesanan dibuat. Jika belum ada<br>pembayaran terverifikasi, pesanan otomatis<br>menjadikedaluwarsadan stok dikembalikan<br>tepat satu kali.|



Dokumen kerja — keputusan yang belum ditetapkan ditandai pada bagian konfirmasi 

|BR-37|Konflik pembayaran/kedaluwarsa|Verifikasi pembayaran dan kedaluwarsa harus<br>ditangani secara atomik. Jika pembayaran<br>diverifikasi sebelum kedaluwarsa ditetapkan,<br>pesanan mengikuti alur normal. Jika sudah<br>kedaluwarsa, pembayaran terlambat dicatat<br>dan ditinjau tanpa mengaktifkan kembali<br>pesanan.|
|---|---|---|
|BR-38|Notifikasi pelanggan|Status pesanan/pembayaran dan kegagalan<br>yang relevan ditampilkan melalui antarmuka<br>web dengan pesan yang jelas. Detail<br>kesalahan teknis internal hanya dicatat pada<br>log backend.|
|BR-39|Validasi ongkir|Ongkir menggunakan jarak rute jalan dari<br>alamat rumah produksi ke titik tujuan di Kota<br>Mojokerto. Jika routing tidak dapat menghitung<br>rute, checkout diblokir dan pelanggan diberi<br>pesan kesalahan. Perubahan pin/alamat<br>memicu perhitungan ulang ongkir dan total.|
|BR-40|Moving Average|Moving Average adalah rekomendasi<br>kebutuhan/permintaan untuk minggu<br>berikutnya dari rata-rata penjualan 3 minggu<br>sebelumnya. Hasil hanya saran bagi Owner;<br>tidak otomatis memproduksi atau mengubah<br>stok.|
|BR-41|Respons API|Semua respons API berformat JSON yang<br>konsisten; sukses memuatmessagedandata<br>yang relevan, sedangkan error memakai<br>messagedanerrorsbila diperlukan serta<br>status HTTP yang sesuai.|



### **Formula ongkir bertingkat yang disepakati** 

- d <= 5,5 km : Rp0. 

- 5,5 < d < 6,5 km : Rp2.000. 

- 6,5 <= d < 7,5 km : Rp4.000. 

- 7,5 <= d < 8,5 km : Rp6.000, dan seterusnya naik Rp2.000 untuk setiap tingkat jarak 1 km. 

- Untuk d > 5,5 , formula umum: ongkir = Rp2.000 × (floor(d - 5,5) + 1) . Untuk d <= 5,5 , ongkir = Rp0. 

Contoh: 5,51 km = Rp2.000; 6,49 km = Rp2.000; 6,50 km = Rp4.000; 7,50 km = Rp6.000. Jarak adalah jarak rute jalan, bukan jarak garis lurus. Tujuan pengiriman dibatasi pada Kota Mojokerto; koordinat rumah produksi harus dikonfirmasi oleh pemilik. 

### **Struktur data tambahan yang dibutuhkan** 

|**Entitas/kolom**|**Ketentuan**|
|---|---|
|orders.courier_id|Foreign key keusers.iduntuk kurir yang ditugaskan; NULL sebelum<br>penugasan.|
|orders.stok_dikembalikan|Penanda pengembalian stok agar proses hanya terjadi sekali;<br>mekanisme database setara boleh digunakan jika menjamin atomisitas.|
|order_status_history|id,order_id,status_sebelumnya,status_baru,diubah_oleh,<br>waktu_perubahan,keteranganopsional.|



Dokumen kerja — keputusan yang belum ditetapkan ditandai pada bagian konfirmasi 

