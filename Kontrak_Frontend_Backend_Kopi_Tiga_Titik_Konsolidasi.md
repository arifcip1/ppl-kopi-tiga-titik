# TECHNICAL IMPLEMENTATION & INTEGRATION GUIDE

**Revisi konsolidasi:** 11 November 2026. Mengikuti PPL 01, PRD, WBS revisi 11 November 2026, dan PPL 19. Fitur tambahan Sprint 3–4 tetap berada pada PPL 23–42; PPL 19 adalah kontrak integrasi yang sudah dialokasikan dalam WBS Sprint 2.

## Sistem Informasi Penjualan Kopi Tiga Titik

**Jenis dokumen:** Pedoman teknis bersama developer dan AI coding
assistant\
**Status:** Draft kerja -- dapat digunakan sebagai acuan awal, dengan
bagian yang perlu diverifikasi terhadap source code aktual\
**Versi:** 1.0\
**Tanggal:** 30 Oktober 2026

------------------------------------------------------------------------

## 1. Tujuan Dokumen

Dokumen ini menjadi panduan teknis bersama bagi seluruh anggota tim
developer dalam membangun, mengembangkan, dan mengintegrasikan Sistem
Informasi Penjualan Kopi Tiga Titik.

Dokumen ditujukan untuk: 1. Menyamakan pemahaman mengenai arsitektur dan
teknologi aplikasi. 2. Menjelaskan pola implementasi frontend dan
backend. 3. Menjadi acuan integrasi antarmodul, API, dan database. 4.
Mengurangi perbedaan pola coding antaranggota. 5. Memberikan konteks
proyek yang konsisten kepada AI coding assistant.

Dokumen ini bukan pengganti PRD, PPL 01, atau PPL 02. Jika terdapat
perbedaan, gunakan aturan prioritas pada Bagian 2.

## 2. Dokumen Acuan dan Prioritas

Gunakan dokumen berikut sebagai sumber kebenaran:

  -----------------------------------------------------------------------
  Dokumen                             Fungsi
  ----------------------------------- -----------------------------------
  PRD Kopi Tiga Titik versi terbaru   Kebutuhan produk, ruang lingkup
                                      fitur, dan kriteria produk

  PPL 01 versi terbaru                Kebutuhan sistem, aturan bisnis,
                                      alur proses, dan kontrak kebutuhan

  PPL 02 / implementasi database      Struktur tabel, kolom, relasi,
  terbaru                             migration, dan model yang
                                      benar-benar diterapkan

  Dokumen ini                         Panduan cara implementasi teknis
                                      dan integrasi
  -----------------------------------------------------------------------

**Aturan prioritas:** - Jangan mengubah kebutuhan atau aturan bisnis
yang sudah ditetapkan PRD dan PPL 01 hanya karena lebih mudah
diimplementasikan. - Untuk struktur database, periksa migration dan
model aktual yang menjadi hasil PPL 02. Jangan mengasumsikan struktur
berdasarkan nama atau gambaran umum. - Jika dokumen saling bertentangan,
jangan memilih sendiri. Catat konflik dan minta keputusan Product
Owner. - Jika source code aktual berbeda dari pedoman ini, identifikasi
perbedaannya dan usulkan pembaruan; jangan melakukan perubahan besar
secara diam-diam.

## 3. Teknologi dan Pendekatan Aplikasi

### 3.1 Teknologi yang telah ditetapkan

  -----------------------------------------------------------------------
  Lapisan                             Teknologi
  ----------------------------------- -----------------------------------
  Backend framework                   Laravel 12

  Bahasa backend                      PHP

  Frontend template                   Laravel Blade

  Markup                              HTML

  Styling                             Tailwind CSS

  Interaksi frontend                  JavaScript

  Database                            MySQL

  Lingkungan pengembangan             Laragon

  Integrasi peta/rute                 OpenStreetMap dan layanan routing
                                      yang ditetapkan tim

  Pembayaran                          Midtrans Sandbox, QRIS
  -----------------------------------------------------------------------

Versi package, library JavaScript, layanan routing, dan konfigurasi
deployment harus diverifikasi dari repository aktual (`composer.json`,
`package.json`, file konfigurasi, dan `.env.example`). Jangan
menambahkan dependensi baru tanpa alasan dan persetujuan tim.

### 3.2 Pola frontend-backend

Pendekatan utama adalah **Laravel Blade + HTML + Tailwind CSS +
JavaScript** dalam satu aplikasi Laravel.

Pedoman umum: - Blade digunakan untuk menyusun halaman dan komponen
tampilan. - Controller menangani request dan menyiapkan data untuk view
atau response JSON. - JavaScript digunakan untuk interaksi yang memang
diperlukan, termasuk `fetch()` bila halaman membutuhkan komunikasi
asynchronous. - Route web digunakan untuk navigasi dan pengiriman form
berbasis halaman. - Endpoint JSON digunakan ketika ada kebutuhan
komunikasi asynchronous atau kontrak API yang jelas. - Jangan membuat
frontend terpisah berbasis React/Vue kecuali ada keputusan baru dari
tim.

Pemilihan antara form biasa dan `fetch()` dilakukan sesuai kebutuhan
halaman. Keduanya boleh digunakan secara konsisten dalam aplikasi yang
sama, tetapi satu alur fitur harus memiliki pola yang jelas dan
terdokumentasi.

## 4. Prinsip Arsitektur

Secara konseptual, alur aplikasi adalah:

``` text
Browser
  |
  | HTTP request
  v
Laravel Routes
  |
  v
Middleware (authentication / authorization)
  |
  v
Controller
  |
  +----> Form Request / Validation
  |
  +----> Service / business process (jika diperlukan)
  |
  +----> Eloquent Model
              |
              v
           MySQL
  |
  v
Blade View atau JSON Response
  |
  v
Browser
```

Integrasi eksternal:

``` text
Laravel Backend
  +----> Midtrans (pembuatan dan verifikasi pembayaran)
  |
  +----> OpenStreetMap / routing service (perhitungan rute)
```

Diagram ini merupakan gambaran tanggung jawab. Detail kelas, service,
middleware, dan struktur folder harus mengikuti source code proyek yang
benar-benar digunakan.

### 4.1 Prinsip tanggung jawab

-   **Route:** mendefinisikan URL, HTTP method, dan tujuan request.
-   **Middleware:** membatasi akses berdasarkan autentikasi dan role.
-   **Controller:** mengatur alur request, memanggil validasi dan proses
    bisnis, lalu mengembalikan view atau response.
-   **Form Request / Validator:** memvalidasi input.
-   **Service:** menampung proses bisnis atau integrasi yang kompleks
    dan digunakan lintas controller.
-   **Model:** merepresentasikan tabel dan relasi database melalui
    Eloquent.
-   **Blade:** menampilkan data dan komponen antarmuka.
-   **JavaScript:** menangani interaksi di browser, bukan menjadi sumber
    kebenaran aturan bisnis.
-   **Database:** menyimpan data persisten; perubahan data penting harus
    dilakukan melalui backend.

Hindari menempatkan seluruh proses bisnis di Blade atau JavaScript.
Validasi penting dan keputusan yang memengaruhi stok, pembayaran,
ongkir, serta status pesanan wajib diperiksa kembali di backend.

## 5. Pedoman UI/UX dan Frontend

### 5.1 Referensi visual

Screenshot halaman **Kelola Produk** yang diberikan Product Owner
menjadi referensi visual utama. Referensi tersebut menunjukkan
karakter: - Dashboard modern dan bersih. - Sidebar navigasi vertikal. -
Header halaman dengan judul, identitas aplikasi, dan aksi utama. - Latar
konten abu-abu sangat muda. - Card putih dengan sudut membulat. - Warna
biru sebagai warna utama. - Badge untuk kategori dan status. - Ikon
sederhana dan konsisten. - Informasi disusun dalam card dan kelompok
konten yang mudah dipindai.

Screenshot adalah acuan gaya, bukan template yang harus disalin persis.
Setiap halaman harus menyesuaikan kebutuhan fitur, role, kepadatan
informasi, dan perangkat pengguna.

**Catatan:** nilai warna HEX, font, ukuran spacing, radius, shadow,
serta ukuran breakpoint final belum ditetapkan dalam keputusan yang
tersedia. Nilai tersebut perlu diambil dari implementasi frontend yang
sudah ada atau disepakati dalam design tokens; jangan mengarangnya
sebagai standar resmi.

### 5.2 Pola layout bersama

Layout dasar menggunakan tiga area: **Sidebar, Top Bar, dan Konten Utama**.

- **Desktop:** sidebar berada di sisi kiri, top bar berada di bagian atas area kerja, dan konten utama berada di bawah top bar.
- **Mobile:** top bar menjadi akses utama; sidebar berubah menjadi drawer/menu yang dapat dibuka dan ditutup. Konten utama menyesuaikan lebar layar.
- Kerangka layout konsisten untuk semua role, tetapi isi navigasi dan konten menyesuaikan role serta kebutuhan halaman.
- Halaman tertentu seperti POS Kasir dapat menggunakan area konten yang lebih lebar sesuai kebutuhan operasional.

### 5.2.1 Isi Sidebar berdasarkan role

Menu berikut mengikuti kesepakatan navigasi yang telah dibahas. Urutannya menjadi acuan.

**Admin / Owner**
1. Dashboard
2. POS Kasir
3. Kelola Produk
4. Pesanan
5. Riwayat Penjualan
6. Prediksi Stok
7. Pengiriman
8. Pengaturan

Bagian bawah sidebar:
- Profil
- Logout

**Kurir**
1. Dashboard
2. Pesanan Pengiriman

Bagian bawah sidebar:
- Profil
- Logout

**Pelanggan**
1. Katalog / Beranda
2. Keranjang
3. Pesanan Saya

Bagian bawah navigasi:
- Profil
- Logout

Aturan sidebar:
- Hanya tampilkan menu yang relevan untuk role yang sedang login.
- Menu aktif harus menunjukkan halaman yang sedang dibuka.
- Akses tetap wajib dilindungi backend; menyembunyikan menu bukan mekanisme otorisasi.
- Jangan menambahkan menu baru atau mengubah nama/urutan menu tanpa menyelaraskannya dengan Product Owner dan dokumen acuan terbaru.

### 5.2.2 Isi Top Bar

Top bar mengikuti layout bersama, dengan isi yang dapat menyesuaikan role dan halaman:
- Tombol buka/tutup sidebar (pada desktop dapat berfungsi sebagai collapse; pada mobile membuka drawer).
- Identitas atau nama aplikasi Kopi Tiga Titik sesuai rancangan frontend.
- Judul halaman aktif dan, bila diperlukan, deskripsi singkat halaman.
- Area informasi akun pengguna yang sedang login; akses Profil dan Logout tetap tersedia sesuai pola navigasi yang disepakati.
- Tombol aksi utama hanya ditampilkan jika relevan dengan halaman, misalnya **Tambah Produk** pada Kelola Produk.
- Notifikasi web termasuk MVP dan harus ditampilkan sesuai role/peristiwa pada PPL 27–28 serta pemicu stok menipis pada PPL 32. Notifikasi bersifat sementara/real-time, bukan tabel domain persisten.

Top bar tidak boleh dipenuhi menu fitur yang seharusnya berada di sidebar. Pada halaman mobile, elemen harus disusun ulang agar tidak menutupi konten.

### 5.2.3 Konten Utama

Konten utama berubah berdasarkan role dan halaman. Pola umum:
1. Judul halaman dan konteks/penjelasan singkat bila diperlukan.
2. Ringkasan statistik atau informasi penting bila relevan.
3. Area kerja inti: tabel, daftar, katalog, form, detail transaksi, grafik, atau peta sesuai fungsi halaman.
4. Pencarian, filter, dan pengurutan hanya bila dibutuhkan.
5. Pagination untuk daftar yang panjang.
6. State loading, empty, success, dan error sesuai jenis interaksi.

Contoh adaptasi:
- **Dashboard Admin/Owner:** ringkasan operasional, akses POS, pesanan, histori penjualan, prediksi, restok, pengiriman, dan pengaturan.
- **POS Kasir:** area transaksi lebih lebar, daftar produk/keranjang, dan ringkasan pembayaran sesuai alur yang ditetapkan.
- **Kelola Produk:** daftar produk, pencarian/filter bila diperlukan, informasi stok, dan aksi pengelolaan.
- **Pesanan:** daftar dan detail pesanan serta aksi sesuai hak Admin/Owner.
- **Riwayat Penjualan:** grafik histori bulanan dan rekap harian/mingguan/bulanan dari transaksi online dan POS; omzet tidak memasukkan ongkir.
- **Prediksi Stok:** hasil Moving Average dan/atau rekomendasi Rule-Based sesuai PRD/PPL 01; keduanya tidak boleh mengubah stok otomatis.
- **Pengiriman:** informasi pengiriman, penugasan kurir, dan lokasi/rute sesuai kewenangan.
- **Pengaturan:** lokasi lapak aktif, jam buka/tutup, dan pengaturan sistem lain yang disetujui.
- **Dashboard Kurir:** ringkasan tugas pengiriman milik kurir yang login.
- **Pesanan Pengiriman:** hanya pesanan yang ditugaskan kepada kurir tersebut, dengan detail penerima/alamat dan aksi status yang diizinkan.
- **Katalog/Beranda Pelanggan:** produk aktif yang dapat dibeli.
- **Keranjang:** item pilihan pelanggan dan ringkasan sebelum checkout.
- **Pesanan Saya:** pesanan milik pelanggan yang login beserta status, informasi pembayaran/pengiriman/pengambilan, nota digital, tombol Hubungi Penjual, dan notifikasi yang relevan.

### 5.2.4 Adaptasi berdasarkan role

**Admin/Owner**
- Menggunakan dashboard pengelolaan dengan menu sesuai daftar Sidebar Admin/Owner.
- Mengakses fungsi pengelolaan sesuai PRD/PPL 01.
- Aksi dan data yang ditampilkan harus sesuai kewenangannya.

**Kurir**
- Menggunakan antarmuka yang berfokus pada tugas pengiriman.
- Hanya menampilkan pesanan yang ditugaskan kepada kurir yang sedang login.
- Menyediakan detail penerima dan alamat yang diperlukan untuk pengantaran.
- Menyediakan aksi perubahan status sesuai aturan bisnis.

**Pelanggan**
- Menggunakan antarmuka katalog dan transaksi.
- Menyediakan katalog, detail produk, keranjang, checkout, pembayaran, dan riwayat pesanan sesuai ruang lingkup.
- Navigasi dan aksi berfokus pada kebutuhan pelanggan, bukan menyalin sidebar Admin/Owner.

### 5.3 Komponen yang disarankan

Bangun komponen Blade yang dapat digunakan ulang jika pola tampilannya
memang berulang, misalnya: - Layout per kelompok role. - Sidebar dan
header. - Tombol dengan variasi aksi. - Card ringkasan. - Badge
status. - Form input dan pesan validasi. - Tabel data. - Modal
konfirmasi. - Alert/notifikasi. - Empty state dan loading state.

Nama file dan lokasi komponen harus mengikuti struktur repository
aktual. Jangan membuat struktur folder baru sebelum memeriksa struktur
yang sudah ada.

### 5.4 Aturan tampilan

1.  Pertahankan konsistensi visual dengan screenshot referensi.
2.  Sesuaikan layout untuk desktop, tablet, dan mobile.
3.  Tampilkan menu berdasarkan role; jangan hanya menyembunyikan tombol
    tanpa mengamankan route backend.
4.  Tampilkan status dan angka berdasarkan response backend.
5.  Gunakan format rupiah yang konsisten.
6.  Sediakan state loading, kosong, berhasil, dan gagal untuk interaksi
    asynchronous.
7.  Form harus menampilkan pesan validasi yang dapat dipahami.
8.  Aksi penting seperti pembatalan atau perubahan status perlu
    konfirmasi sesuai tingkat risikonya.
9.  Jangan menaruh credential, secret key, atau aturan otorisasi di
    JavaScript frontend.

## 6. Struktur Project dan Konvensi File

Struktur folder final **belum boleh ditetapkan hanya dari asumsi**.
Developer atau AI wajib memeriksa repository terlebih dahulu,
khususnya: - `app/Http/Controllers` - `app/Models` -
`app/Http/Middleware` - `app/Http/Requests` (jika digunakan) -
`app/Services` (jika digunakan) - `routes/web.php` - `routes/api.php`
(jika digunakan) - `resources/views` - `resources/css` -
`resources/js` - `database/migrations` - `database/seeders` -
`composer.json` - `package.json`

Konvensi: - Ikuti pola penamaan yang sudah digunakan repository. -
Gunakan nama yang deskriptif dan konsisten. - Hindari duplikasi fungsi
atau query yang sama. - Jangan membuat controller, model, service, atau
komponen baru sebelum memeriksa apakah fungsi serupa sudah tersedia. -
Jangan mengubah file konfigurasi atau dependensi tanpa memahami
dampaknya.

## 7. Backend dan Routing

### 7.1 Route web dan endpoint JSON

Pisahkan tujuan route berdasarkan jenis respons: - **Web route:**
mengembalikan halaman Blade atau memproses form lalu redirect. - **JSON
endpoint:** mengembalikan data terstruktur untuk kebutuhan JavaScript
atau integrasi.

Jangan menganggap semua route harus berada di `routes/api.php`. Periksa
konfigurasi Laravel 12 dan pola route yang benar-benar diaktifkan pada
repository.

### 7.2 Dokumentasi setiap endpoint

Setiap endpoint yang menjadi kontrak antarmodul wajib didokumentasikan
dengan format:

  Elemen            Keterangan
  ----------------- ----------------------------------------------------
  Nama fungsi       Tujuan endpoint
  Method dan URL    HTTP method dan path
  Aktor/role        Role yang boleh mengakses
  Authentication    Apakah wajib login
  Input             Path parameter, query, dan body
  Validasi          Aturan field dan batasan
  Proses            Ringkasan proses backend
  Database          Tabel/model yang dibaca atau diubah
  Response sukses   HTTP status dan contoh JSON/redirect
  Response gagal    HTTP status dan format error
  Efek samping      Perubahan stok, status, pembayaran, atau data lain

Jangan menulis endpoint sebagai endpoint final jika belum diverifikasi
terhadap route dan kebutuhan yang disetujui.

### 7.3 Validasi dan keamanan request

-   Validasi input di backend meskipun frontend sudah melakukan
    validasi.
-   Gunakan CSRF protection untuk form web sesuai mekanisme Laravel.
-   Pastikan pengguna hanya dapat mengakses data yang menjadi haknya.
-   Gunakan mass assignment secara aman melalui konfigurasi model dan
    validasi.
-   Jangan menerima harga, total, ongkir, status pembayaran, atau status
    pesanan sebagai nilai tepercaya dari browser.
-   Hitung ulang nilai penting di backend berdasarkan data yang
    tersimpan dan aturan bisnis.
-   Gunakan database transaction untuk proses yang melibatkan beberapa
    perubahan data yang harus konsisten.

## 8. Integrasi Database

### 8.1 Sumber kebenaran

PPL 02 dan migration Laravel yang telah diterapkan menjadi acuan
struktur database. Sebelum menulis query atau mengubah model: 1. Periksa
migration aktual. 2. Periksa model dan relationship yang sudah dibuat.
3. Periksa nama kolom, tipe data, nullable, default, index, unique, dan
foreign key. 4. Jangan membuat nama kolom berdasarkan perkiraan. 5.
Jangan menjalankan `migrate:fresh` pada database proyek yang berisi data
kerja.

### 8.2 Tabel aplikasi yang telah ditetapkan

Daftar tabel aplikasi berdasarkan implementasi PPL 02:

1.  `users`
2.  `products`
3.  `cart_items`
4.  `orders`
5.  `order_items`
6.  `payments`
7.  `order_status_history`
8.  `sales_history`
9.  `settings`
10. `shipping_locations`

Tabel internal Laravel lain dapat ada sesuai konfigurasi framework.
Jangan menganggap tabel internal framework sebagai bagian dari sepuluh
tabel domain aplikasi tersebut.

### 8.3 Pedoman akses data

-   Gunakan Eloquent Model dan relationship yang sudah tersedia bila
    sesuai.
-   Hindari query langsung dari Blade.
-   Hindari mengambil seluruh kolom jika hanya beberapa kolom yang
    diperlukan.
-   Terapkan pembatasan data berdasarkan pengguna dan role di backend.
-   Gunakan eager loading bila diperlukan untuk mencegah query berulang.
-   Gunakan transaction untuk operasi multi-tabel yang harus atomik.
-   Perubahan schema harus melalui migration dan persetujuan tim.
-   Jangan menghapus atau mengganti kolom yang sudah dipakai modul lain
    tanpa analisis dampak.

### 8.4 Konsistensi transaksi penting

Operasi berikut perlu dirancang agar konsisten dan diuji: - Pembuatan
pesanan dan pengurangan/reservasi stok. - Pembatalan atau kedaluwarsa
pembayaran dan pengembalian stok satu kali. - Pencatatan pembayaran dan
perubahan status pesanan. - Pencatatan riwayat perubahan status. -
Pencatatan penjualan yang menjadi sumber Moving Average.

Detail urutan proses harus mengikuti PRD/PPL 01 dan implementasi yang
disetujui. Hindari mengimplementasikan perubahan stok atau pembayaran
hanya di frontend.

## 9. Integrasi Antar-Modul

Modul dikelompokkan mengikuti fitur PRD dan PPL 01. Nama controller,
service, dan endpoint final ditetapkan setelah pemeriksaan repository.

  -----------------------------------------------------------------------
  Modul                   Tanggung jawab utama    Ketergantungan utama
  ----------------------- ----------------------- -----------------------
  Autentikasi dan         Login, logout,          Users
  pengguna                identitas, role         

  Produk                  CRUD dan status produk  Products

  Katalog                 Menampilkan produk      Products
                          aktif                   

  Keranjang               Menambah, mengubah,     Cart items, Products
                          menghapus item          
                          keranjang               

  Checkout dan pesanan    Membuat pesanan dan     Cart, Products, Orders,
                          item pesanan            Order items

  Pembayaran              Membuat dan             Orders, Payments,
                          memverifikasi           Midtrans
                          pembayaran              

  Pengiriman              Menghitung jarak dan    Shipping locations,
                          ongkir                  layanan routing

  Penugasan kurir         Menetapkan kurir dan    Orders, Users
                          menampilkan tugas kurir 

  Riwayat status          Mencatat perubahan      Orders, Users, Order
                          status                  status history

  Moving Average          Menghasilkan            Sales history, Products
                          rekomendasi berdasarkan 
                          riwayat penjualan       

  Rule-Based              Peringatan stok dan     Products, Settings
                          rekomendasi produksi    

  Lokasi asal pengiriman  Mengelola titik asal    Shipping locations,
                          yang digunakan untuk    Settings sesuai
                          perhitungan rute        implementasi
  -----------------------------------------------------------------------

Tabel ini menjelaskan batas tanggung jawab konseptual, bukan keputusan
nama class atau file.

## 10. Autentikasi dan Otorisasi

Role aplikasi: `admin` (Admin/Owner), `kurir`, dan `pelanggan`. Owner menggunakan role `admin`; jangan menambah role `owner`. Maksimal dua akun Kurir aktif dan backend wajib memvalidasi batas tersebut.

Pedoman: 1. Semua halaman privat memerlukan autentikasi. 2. Backend
wajib memeriksa role pada route atau middleware yang sesuai. 3.
Menyembunyikan menu di frontend bukan pengganti otorisasi backend. 4.
Kurir hanya boleh melihat dan mengubah pesanan yang ditugaskan
kepadanya. 5. Pelanggan hanya boleh mengakses keranjang dan pesanan
miliknya. 6. Admin/Owner memperoleh akses pengelolaan sesuai kewenangan
pada PRD/PPL 01. 7. Jangan mempercayai `user_id`, `courier_id`, atau
role yang dikirim bebas oleh browser; tentukan identitas pengguna dari
session autentikasi dan validasi otorisasi di backend.

Detail nama middleware dan implementasi login harus mengikuti source
code aktual.

## 11. Integrasi Layanan Eksternal

### 11.1 Midtrans -- QRIS Sandbox

-   Integrasi dilakukan dari backend.
-   Credential dan secret key disimpan pada environment/configuration,
    tidak ditulis di Blade atau JavaScript.
-   Backend membuat transaksi berdasarkan pesanan yang valid.
-   Status pembayaran harus diverifikasi melalui mekanisme server-side
    Midtrans yang digunakan proyek.
-   Jangan menganggap redirect atau callback dari browser sebagai bukti
    pembayaran berhasil.
-   Terapkan verifikasi autentikasi/signature sesuai dokumentasi
    Midtrans dan konfigurasi aktual.
-   Batas kedaluwarsa pembayaran online mengikuti PRD/PPL 01: 10 menit.
-   Setiap percobaan pembayaran Midtrans untuk pesanan yang sama disimpan sebagai baris `payments` tersendiri; jangan menimpa percobaan sebelumnya.
-   Ketentuan Midtrans hanya berlaku untuk checkout online. POS menggunakan tunai/QRIS langsung dan konfirmasi Owner, tanpa Midtrans/webhook.
-   Penanganan callback berulang harus idempotent agar tidak
    menggandakan perubahan status atau pengembalian stok.

Nama endpoint, payload, dan konfigurasi final harus dicocokkan dengan
integrasi Midtrans aktual.

### 11.2 OpenStreetMap dan perhitungan ongkir

-   OpenStreetMap digunakan sebagai sumber data peta; perhitungan jarak
    jalan membutuhkan layanan routing yang benar-benar dipilih dan
    dikonfigurasi.
-   Titik asal berasal dari lokasi yang disimpan dan dipilih Owner.
-   Tujuan berasal dari alamat/koordinat pengiriman pelanggan yang
    divalidasi.
-   Perhitungan ongkir dilakukan di backend.
-   Jangan menggunakan jarak garis lurus jika aturan produk mensyaratkan
    jarak rute jalan.
-   Simpan atau tampilkan hasil sesuai kebutuhan sistem; jangan
    menganggap hasil routing dari browser sebagai nilai final yang
    tepercaya.

Aturan ongkir yang menjadi acuan: jarak `d <= 5,5 km` gratis; jika `d > 5,5 km`, ongkir `Rp2.000 × (floor(d - 5,5) + 1)`. Area layanan pengiriman hanya Kota Mojokerto, bukan Kabupaten Mojokerto. Pickup tidak memanggil routing dan ongkirnya Rp0.

Owner dapat mengelola lokasi aktif dan jam buka/tutup. Jam operasional hanya membatasi checkout online baru. Pesanan yang telah berhasil dibuat mempertahankan snapshot lokasi. Nama layanan routing, batas penggunaan, format koordinat, dan strategi fallback tetap harus diverifikasi dari implementasi aktual.

## 12. Aturan Bisnis Komputasi

### 12.1 Moving Average

Acuan perhitungan: - Menggunakan 21 hari kalender sebelum tanggal
prediksi. - Dibagi menjadi tiga periode berurutan, masing-masing 7
hari. - Nilai prediksi adalah rata-rata total penjualan dari ketiga
periode 7 hari: `(P1 + P2 + P3) / 3`. - Hari tanpa penjualan dihitung
nol. - Penjualan online yang pembayaran Midtrans-nya terverifikasi dan transaksi POS yang dikonfirmasi Owner menjadi sumber produksi. - Data simulasi hanya untuk pengujian dan tidak boleh
masuk ke hasil produksi. - Jika data transaksi nyata belum mencukupi 21
hari, tampilkan kondisi data belum cukup. - Hasil Moving Average adalah
rekomendasi; keputusan akhir tetap pada Owner.

Implementasi wajib merujuk aturan lengkap pada PRD dan PPL 01. Dashboard histori dan ekspor Excel menggabungkan online+POS, dengan ongkir dikecualikan dari omzet.

### 12.2 Rule-Based

Acuan: - Peringatan stok minimum aktif ketika `stok <= 5`. - Standar
stok harian yang menjadi dasar rekomendasi adalah 10. - Saran jumlah
produksi: `max(0, 10 - stok)`. - Sistem hanya memberikan rekomendasi. -
Sistem tidak boleh otomatis memproduksi atau mengubah stok berdasarkan
rekomendasi.

Nilai konfigurasi harus dibaca dari sumber konfigurasi yang ditetapkan
dan tidak diduplikasi sebagai angka berbeda di banyak tempat.

## 13. Format Response dan Penanganan Error

Untuk endpoint JSON, gunakan format konsisten. Contoh berikut adalah
**usulan format**, bukan klaim bahwa seluruh endpoint saat ini sudah
menerapkannya:

Respons sukses:

``` json
{
  "success": true,
  "message": "Data berhasil diproses.",
  "data": {}
}
```

Respons gagal:

``` json
{
  "success": false,
  "message": "Data tidak dapat diproses.",
  "errors": {}
}
```

Pedoman: - Gunakan HTTP status yang sesuai dengan hasil request. -
Validasi field mengembalikan pesan yang dapat ditampilkan frontend. -
Jangan mengirim stack trace, secret, atau detail internal server ke
pengguna. - Catat detail teknis error pada log server. - Frontend harus
membedakan error validasi, tidak terautentikasi, tidak berwenang, data
tidak ditemukan, konflik proses, dan kegagalan server. - Untuk form
Blade biasa, gunakan mekanisme error dan old input Laravel secara
konsisten.

## 14. Standar Coding Bersama

1.  Baca file terkait sebelum mengubahnya.
2.  Pahami alur dari route hingga view/database sebelum menambahkan
    kode.
3.  Ikuti gaya dan struktur yang sudah digunakan repository.
4.  Jangan menggandakan aturan bisnis pada frontend dan backend dengan
    implementasi yang berbeda.
5.  Backend tetap menjadi sumber kebenaran untuk validasi dan proses
    bisnis.
6.  Gunakan nama variabel dan fungsi yang menjelaskan maksudnya.
7.  Pisahkan proses yang kompleks dari controller bila sesuai pola
    proyek.
8.  Hindari perubahan tidak terkait dalam satu pekerjaan.
9.  Jangan menghapus kode atau data yang belum dipahami.
10. Jalankan pengujian yang relevan setelah perubahan.
11. Laporkan file yang diubah, alasan perubahan, migration baru (jika
    ada), dan hasil pengujian.
12. Jika ada asumsi atau keputusan yang belum ditetapkan, tuliskan
    secara eksplisit.

## 15. Pedoman Penggunaan AI Coding Assistant

Bagian ini wajib diberikan sebagai konteks ketika AI membantu coding
proyek.

### 15.1 Konteks proyek

AI harus memahami: - Proyek adalah Sistem Informasi Penjualan Kopi Tiga
Titik. - Backend menggunakan Laravel 12 dan PHP. - Frontend menggunakan
Blade, HTML, Tailwind CSS, dan JavaScript. - Database menggunakan
MySQL. - Role aplikasi adalah admin, kurir, dan pelanggan. - PRD terbaru
dan PPL 01 terbaru adalah acuan kebutuhan dan aturan bisnis. - PPL 02
serta migration/model aktual adalah acuan database. - Dokumen ini adalah
pedoman implementasi dan integrasi.

### 15.2 Instruksi wajib untuk AI

Sebelum menulis atau mengubah kode: 1. Periksa struktur folder dan file
yang relevan. 2. Baca route, controller, model, migration, view, dan
JavaScript yang terkait. 3. Identifikasi pola implementasi yang sudah
digunakan. 4. Cocokkan kebutuhan dengan PRD dan PPL 01. 5. Cocokkan
akses data dengan migration dan model aktual. 6. Sebutkan rencana file
yang akan dibuat atau diubah. 7. Tandai informasi yang belum tersedia;
jangan mengarang nama kolom, endpoint, class, atau package.

Saat mengimplementasikan: - Ubah hanya bagian yang diperlukan. -
Pertahankan kompatibilitas dengan modul lain. - Terapkan validasi dan
otorisasi di backend. - Jangan menaruh secret pada frontend. - Jangan
mengubah migration atau struktur database tanpa kebutuhan dan
persetujuan. - Jangan menjalankan perintah yang menghapus database atau
data. - Gunakan komponen dan pola UI yang sudah ada. - Pastikan tampilan
sesuai referensi visual dan role pengguna.

Setelah mengimplementasikan: - Jelaskan ringkasan perubahan. - Sebutkan
file yang berubah. - Sebutkan route/endpoint dan tabel yang terdampak. -
Laporkan pengujian yang dijalankan dan hasilnya. - Jelaskan hal yang
belum diuji atau masih memerlukan keputusan manusia.

### 15.3 Template prompt untuk anggota tim

``` text
Kamu membantu mengembangkan Sistem Informasi Penjualan Kopi Tiga Titik.

ACUAN:
1. PRD terbaru: kebutuhan dan aturan produk.
2. PPL 01 terbaru: kebutuhan sistem dan aturan bisnis.
3. PPL 02 serta migration/model aktual: struktur database.
4. Technical Implementation & Integration Guide: pola implementasi teknis.

STACK:
- Laravel 12, PHP
- Laravel Blade, HTML, Tailwind CSS, JavaScript
- MySQL

ATURAN KERJA:
1. Periksa source code yang relevan sebelum memberi solusi.
2. Jangan mengarang nama tabel, kolom, route, endpoint, atau package.
3. Jangan mengubah aturan bisnis yang sudah ditetapkan.
4. Backend adalah sumber kebenaran untuk validasi, otorisasi, stok,
   pembayaran, ongkir, dan status pesanan.
5. Ikuti pola UI yang sudah ada dan referensi visual dashboard Kopi Tiga Titik.
6. Jangan mengubah database atau menghapus data tanpa persetujuan.
7. Jika informasi kurang, tanyakan atau tandai sebagai belum ditentukan.

TUGAS:
[Tuliskan fitur atau masalah yang akan dikerjakan]

Sebelum coding, jelaskan:
- pemahaman tugas,
- file yang perlu diperiksa,
- rencana perubahan,
- route, model, dan tabel yang terdampak.

Setelah coding, laporkan:
- file yang diubah,
- ringkasan implementasi,
- pengujian dan hasilnya,
- hal yang belum selesai.
```

## 16. Pengujian Integrasi

Setiap fitur perlu diuji pada tiga tingkat:

1.  **Fungsional halaman:** tampilan, form, navigasi, loading, empty
    state, dan pesan error.
2.  **Integrasi backend-database:** validasi, query, relasi, transaksi,
    otorisasi, dan konsistensi data.
3.  **Integrasi antarmodul/eksternal:** alur dari awal hingga akhir,
    termasuk kegagalan layanan eksternal.

Skenario minimal harus mencakup: - Akses tanpa login. - Akses dengan
role yang tidak sesuai. - Data tidak ditemukan. - Input tidak valid. -
Request ganda atau pengiriman ulang. - Kegagalan layanan eksternal. -
Konsistensi stok dan status pesanan. - Callback pembayaran berulang. -
Akses data milik pengguna lain.

Rincian test case dan hasil aktual dicatat dalam dokumen pengujian
terpisah atau artefak QA proyek.

## 17. Definition of Done Teknis

Sebuah pekerjaan integrasi dapat dinyatakan selesai apabila: - \[ \]
Sesuai kebutuhan PRD dan PPL 01. - \[ \] Mengikuti struktur dan pola
source code proyek. - \[ \] UI mengikuti pedoman visual dan role
terkait. - \[ \] Route dan otorisasi backend telah diperiksa. - \[ \]
Validasi dilakukan di backend. - \[ \] Query dan perubahan database
sesuai migration/model aktual. - \[ \] Response atau redirect ditangani
frontend dengan benar. - \[ \] Kondisi loading, kosong, berhasil, dan
gagal ditangani jika relevan. - \[ \] Tidak membocorkan credential atau
data yang tidak berhak diakses. - \[ \] Pengujian relevan telah
dijalankan. - \[ \] Perubahan dan hasil pengujian dilaporkan kepada tim.

## 18. Hal yang Masih Perlu Diverifikasi

Dokumen ini sengaja tidak mengarang detail yang belum didukung oleh
source code atau keputusan tim. Sebelum dianggap sebagai spesifikasi
teknis final, verifikasi:

1.  Struktur folder dan pola Blade aktual.
2.  Screenshot dan halaman frontend lain yang sudah tersedia.
3.  Design tokens final: warna HEX, font, spacing, radius, shadow,
    breakpoint.
4.  Daftar route web dan endpoint JSON aktual.
5.  Format response yang benar-benar digunakan.
6.  Nama controller, service, middleware, Form Request, dan model
    aktual.
7.  Relationship dan constraint migration aktual.
8.  Layanan routing OSM yang digunakan, endpoint, batas pemakaian, dan
    fallback.
9.  Detail konfigurasi Midtrans Sandbox dan alur callback aktual.
10. Strategi pengujian dan perintah test yang tersedia di repository.

Setiap poin yang telah diverifikasi dapat diperbarui pada dokumen ini
dengan menyertakan sumber file atau keputusan tim.

------------------------------------------------------------------------

**Catatan status:** Dokumen ini merupakan pedoman teknis awal yang
disusun dari keputusan teknologi dan aturan proyek yang telah tersedia.
Dokumen ini menetapkan kontrak perilaku dan data lintas modul. Nama file/class, route aktual, design tokens yang belum tersedia, dan detail library tetap wajib dicocokkan dengan repository. Jika route contoh belum ada, implementasikan mengikuti pola repository; jangan menggandakan route yang sudah tersedia.


## 19. Konsolidasi Work Package PPL 23–42

Pembagian fitur tambahan yang menjadi acuan integrasi:

| WP | Frontend/backend dan tanggung jawab |
|---|---|
| PPL 23–24 | Backend pembayaran Midtrans, status dan percobaan pembayaran; backend/UI ambil sendiri 24 jam, nota digital, Hubungi Penjual |
| PPL 25–26 | UI/backend lokasi aktif, jam operasional, titik tujuan dan ongkir OSM; checkout online di luar jam ditolak |
| PPL 27–28 | UI/backend Dashboard Owner/Kurir, daftar/detail/filter pesanan, akun/penugasan kurir, status pengiriman, notifikasi web |
| PPL 29–30 | UI/backend Moving Average, dashboard histori penjualan online+POS, rekap periode, pendapatan tanpa ongkir, ekspor Excel |
| PPL 31–32 | UI/backend Rule-Based restok dan POS offline; konfirmasi pembayaran tunai/QRIS, pengurangan stok, histori, notifikasi stok |
| PPL 33–42 | QE, integrasi end-to-end, dokumentasi, regresi, perbaikan bug, penyempurnaan UI, uji pembayaran dan QE akhir |

Jumlah Work Package tetap 42. Sprint 1–2 tidak diubah. Tidak boleh membuat WP baru di luar PPL 01–42. Perubahan implementasi harus mengikuti migration dan model aktual; jangan membuat ulang kolom yang sudah tersedia.

---

# 20. Kontrak Modul Lengkap dan Matriks Pemakaian Database

## 20.1 Batas kontrak dan prioritas

- PPL 01/PRD menetapkan apa yang harus dilakukan sistem dan aturan bisnisnya.
- WBS menetapkan PIC, jadwal, dan WP; total tetap 42.
- PPL 02/migration/model aktual menetapkan nama tabel, kolom, tipe, nullable, unique, foreign key, dan relasi.
- Dokumen ini menetapkan pembagian tanggung jawab UI/backend, request/response, akses, dan pola integrasi.
- PPL 19 menetapkan detail khusus Midtrans, webhook, routing, dan tarif. PPL 19 tidak boleh mengubah aturan bisnis PPL 01.

## 20.2 Kontrak fitur tambahan sesuai PPL 23–42

| WP | Modul | Frontend | Backend | Database utama |
|---|---|---|---|---|
| PPL 23–24 | Pembayaran online, pickup, nota, kontak | Halaman pembayaran, countdown 10 menit, status backend, pilihan pickup, detail nota, tombol kontak | Membuat percobaan Midtrans, verifikasi webhook, update payment/order, pickup deadline 24 jam, scheduler, data nota | `orders`, `order_items`, `payments`, `order_status_history`, `shipping_locations`, `settings` |
| PPL 25–26 | Tujuan, ongkir, lokasi aktif, jam operasional | Peta Leaflet, pin tujuan, rincian ongkir, CRUD lokasi, pilih lokasi aktif, form jam buka/tutup | Validasi koordinat, geofence Kota Mojokerto, routing ORS, formula ongkir, snapshot lokasi, blokir checkout di luar jam | `orders`, `settings`, `shipping_locations` |
| PPL 27–28 | Dashboard Owner/Kurir, pesanan, akun/assignment, notifikasi | Dashboard per role, daftar/detail/filter pesanan, form assignment, notifikasi | Query ringkasan dan pesanan, batas 2 akun kurir, assignment, otorisasi, event notification sementara | `users`, `orders`, `order_status_history`, `products`, `payments` |
| PPL 29–30 | Moving Average, histori, rekap, Excel | Grafik bulanan, tabel rekap, filter periode, unduh Excel | Agregasi online+POS, hitung omzet tanpa ongkir, MA 21 hari, ekspor | `sales_history`, `products`, `orders`, `order_items`, `payments` |
| PPL 31–32 | Rule-Based dan POS | UI stok rendah, saran produksi, POS pilih item/qty, total, metode, konfirmasi | Validasi stok, transaksi atomik, konfirmasi Owner, kurangi stok sekali, catat penjualan dan pemicu notifikasi | `products`, `orders`, `order_items`, `payments`, `sales_history`, `settings` |
| PPL 33–42 | QE, integrasi, regresi, dokumentasi, bug fix, final QE | Retest dan perbaikan tampilan sesuai modul | Integrasi end-to-end, idempotensi, perbaikan, audit akses, data consistency | Seluruh 10 tabel sesuai fitur |

## 20.3 Kontrak endpoint tambahan

Path di bawah adalah nama kontrak logis yang mengikuti PPL 01. Sebelum implementasi, cocokkan dengan `routes/web.php`, `routes/api.php` (jika aktif), dan route list Laravel. Jika path sudah ada dengan fungsi sama, gunakan yang ada; jangan membuat duplikat.

| Operasi | Method/path kontrak | Akses | Tabel utama |
|---|---|---|---|
| Buat quote ongkir | `POST /api/shipping/quote` | Pelanggan login | `settings`, `shipping_locations` |
| Simpan lokasi asal | `POST /api/admin/shipping-locations` | Admin | `shipping_locations` |
| Ubah lokasi asal | `PUT /api/admin/shipping-locations/{id}` | Admin | `shipping_locations` |
| Pilih lokasi aktif | `PATCH /api/admin/shipping-locations/{id}/select` | Admin | `shipping_locations`, `settings` |
| Atur jam operasional | `PUT /api/admin/operating-hours` | Admin | `settings` |
| Buat checkout | `POST /api/checkout` | Pelanggan login | `cart_items`, `products`, `orders`, `order_items`, `payments` |
| Terima webhook | `POST /api/payments/webhook` | Midtrans server-to-server | `payments`, `orders`, `order_status_history`, `sales_history`, `products` |
| Lihat status bayar | `GET /api/orders/{id}/payment` | Pemilik pesanan/Admin | `payments`, `orders` |
| Daftar pesanan Owner | `GET /api/admin/orders` | Admin | `orders`, `payments`, `users` |
| Assignment Kurir | `PATCH /api/admin/orders/{id}/courier` | Admin | `orders`, `users` |
| Status oleh Kurir | `PATCH /api/courier/orders/{id}/status` | Kurir yang ditugaskan | `orders`, `order_status_history` |
| POS transaksi | `POST /api/admin/pos/transactions` | Admin | `orders`, `order_items`, `payments`, `products`, `sales_history` |
| Histori penjualan | `GET /api/admin/sales-history` | Admin | `sales_history`, `products` |
| Ekspor | `GET /api/admin/sales-history/export` | Admin | `sales_history`, `products` |
| Prediksi MA | `GET /api/admin/forecast` | Admin | `sales_history`, `products` |
| Restok | `GET /api/admin/low-stock` | Admin | `products`, `settings` |

## 20.4 Kontrak payload minimum frontend-backend

### Quote pengiriman

Request:
```json
{
  "latitude_tujuan": -7.0,
  "longitude_tujuan": 112.0,
  "metode_pemenuhan": "pengiriman"
}
```

Response sukses:
```json
{
  "success": true,
  "data": {
    "metode_pemenuhan": "pengiriman",
    "jarak_km": 5.6,
    "ongkir": 2000,
    "mata_uang": "IDR"
  }
}
```

Nilai koordinat di atas hanya contoh format, bukan koordinat operasional. Backend mengambil lokasi asal aktif dari database, menghitung rute, memvalidasi wilayah, dan menghitung ongkir sendiri. Frontend tidak mengirim ongkir sebagai nilai tepercaya.

### Checkout pickup

Request harus mengirim `metode_pemenuhan="ambil_sendiri"`; koordinat tujuan boleh tidak ada. Backend menetapkan ongkir Rp0 dan menyimpan snapshot lokasi pengambilan. Frontend tidak boleh memaksa input koordinat tujuan untuk pickup.

### Checkout pengiriman

Request mengirim `metode_pemenuhan="pengiriman"`, data penerima, alamat, `latitude_tujuan`, `longitude_tujuan`, dan catatan opsional. Backend memvalidasi geofence, lokasi asal aktif, rute, dan ongkir; kemudian menghitung ulang subtotal/total dan membuat snapshot order.

### POS

Request berisi daftar `product_id` dan `jumlah`, `metode_pembayaran` (`tunai` atau `qris`), serta konfirmasi eksplisit Owner. Browser tidak boleh mengirim harga, subtotal, total, atau status `berhasil` sebagai sumber kebenaran. Backend membaca harga produk dan stok dari database. Setelah konfirmasi dan penyerahan barang, order POS diselesaikan.

### Respons standar

```json
{
  "success": true,
  "message": "Berhasil diproses.",
  "data": {}
}
```

```json
{
  "success": false,
  "message": "Permintaan tidak dapat diproses.",
  "errors": {}
}
```

Contoh adalah kontrak bentuk respons; sesuaikan wrapper dengan pola JSON/redirect yang sudah dipakai repository secara konsisten.

## 20.5 Matriks database dan ownership modul

| Tabel | Owner modul utama | Modul pembaca lain | Aturan penggunaan |
|---|---|---|---|
| `users` | Auth/Account | Orders, assignment, dashboard | Role `admin`, `kurir`, `pelanggan`; maksimal dua akun Kurir aktif; jangan menambah role Owner. |
| `products` | Product Management | Catalog, Cart, POS, Rule-Based, MA | Nonaktifkan via `aktif=false`; jangan hard delete produk yang direferensikan transaksi. |
| `cart_items` | Cart | Checkout | Keranjang tidak reservasi stok. Checkout memvalidasi ulang. |
| `orders` | Checkout/Order | Payment, shipping, pickup, POS, dashboard, courier | `sumber_transaksi` wajib diisi `online`/`pos`; POS `metode_pemenuhan=NULL`, `user_id` nullable; online pilih fulfillment. Snapshot lokasi dan item tidak diubah. |
| `order_items` | Checkout/POS | Nota, dashboard, histori | Simpan snapshot nama/rasa/harga/jumlah/subtotal. |
| `payments` | Payment/POS | Order, nota, dashboard | `order_id` tidak unik; banyak percobaan Midtrans per order. `referensi_provider` unique nullable. Metode `midtrans`, `tunai`, `qris`; status `pending`, `berhasil`, `gagal`, `kedaluwarsa`. |
| `order_status_history` | Order lifecycle | Dashboard, tracking, audit | Kolom `order_id`, `status_baru`, `diubah_oleh` nullable, `waktu_perubahan`, `keterangan`; jangan gunakan `status_sebelumnya`/`user_id`. |
| `sales_history` | SalesHistory | MA, dashboard, export | Rekap per produk/tanggal/sumber; transaksi nyata `transaksi`, uji `simulasi`. Idempotensi aplikasi tetap wajib. |
| `settings` | Settings | Shipping, hours, Rule-Based | Kolom `key`, `value`, `description`; key `lokasi_pengiriman_aktif_id`, `jam_buka`, `jam_tutup`, `batas_stok_minimum`, `stok_harian_standar`. |
| `shipping_locations` | Shipping Location | Shipping quote, pickup | Banyak lokasi dapat tersimpan; hanya `aktif=true` dapat dipilih. `settings.value` menyimpan ID lokasi aktif. |

**Larangan perubahan schema:** tidak membuat tabel `pos_transactions`, `sales_reports`, `notifications`, `payment_events`, `stock_movements`, atau tabel domain lain tanpa persetujuan PO. Jangan `migrate:fresh`, `db:wipe`, menghapus data, atau mengubah schema melalui phpMyAdmin. Semua perubahan schema harus melalui migration yang disetujui setelah audit dampak.

## 20.6 Kepemilikan data dan transaksi atomik

- Harga produk, ongkir, total, status pembayaran, role, dan status pesanan ditetapkan backend.
- Operasi multi-tabel yang saling bergantung menggunakan DB transaction.
- Lock/validasi stok dilakukan saat checkout dan POS agar tidak terjadi overselling.
- Callback Midtrans dan request ulang POS harus idempotent.
- `sales_history` hanya bertambah satu kali untuk setiap transaksi nyata per produk/tanggal/sumber sesuai desain agregasi.
- `orders.stok_dikembalikan` mencegah pengembalian stok ganda.
- Perubahan `orders.status` dan penambahan `order_status_history` dilakukan dalam transaksi yang sama.
- Notifikasi tidak menjadi sumber status dan tidak disimpan ke tabel domain baru; mekanisme teknis sementara harus dipilih dan didokumentasikan tanpa mengubah 10 tabel domain.

## 20.7 Koreksi redaksional User Story yang perlu dicerminkan pada WBS

US-21 “hapus” berarti soft deactivate; US-18 tidak mengubah snapshot pesanan lama; US-32 kewenangan status `dikirim` ada pada Owner dan `selesai` pada Kurir yang ditugaskan; US-12/13 koordinat tujuan kondisional; US-31 maksimal dua akun Kurir. Dokumen ini mengunci interpretasi tersebut, tetapi sheet WBS tetap perlu diperbarui redaksinya agar tidak menjadi sumber instruksi yang berbeda.

## 20.8 QE lintas modul

Gunakan seluruh skenario PPL 01/PRD: auth/role; CRUD dan nonaktif produk; katalog/stok; keranjang; checkout pengiriman/pickup; geofence; quote ongkir; lokasi aktif dan snapshot; jam operasional; Midtrans pending/berhasil/gagal/kedaluwarsa/terlambat; webhook duplikat; stok ditahan/dikembalikan satu kali; POS tunai/QRIS; status order; assignment dan akses Kurir; pickup auto-complete; nota/kontak; notifikasi per role; sales history; export; MA; Rule-Based; dashboard; error eksternal; regression.


## 20.9 Tindak lanjut konsistensi WBS

WBS terbaru sudah memuat 36 US, 26 backlog, dan 42 WP; seluruh US-1–US-36 telah terhubung dengan backlog. Namun sebelum workbook dipakai sebagai versi final berikutnya, perbarui redaksi US-18 dan US-32 serta ringkasan tujuan Sprint 3 sesuai PPL 01 Bagian 13.4–13.5. Tambahkan kolom ID PPL pada `DAFTAR BACKLOG` bila diperlukan untuk memudahkan pembagian kerja. Jangan mengubah PIC, poin, durasi, atau tanggal dalam proses perapian tersebut.
