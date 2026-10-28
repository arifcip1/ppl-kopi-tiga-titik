# Alur Desain & Prototipe UI-Mobile: E-Commerce UMKM Kopi Tiga Titik

Dokumen arsitektur layar, pemetaan alur interaksi prototipe Figma, dan panduan navigasi lengkap aplikasi **Mobile Web E-Commerce UMKM Kopi Tiga Titik Mojokerto** secara berurutan dan terstruktur (19 Pages).

---

## Informasi Proyek
* **Aplikasi:** Mobile Web E-Commerce Kopi Tiga Titik Mojokerto (Street Bottled Coffee)
* **Target Viewport:** Mobile First (`390 x 844 px`)
* **Warna Utama Brand:** Cobalt Blue (`#004AAD`), Pure White (`#FFFFFF`), Emerald Green (`#10B981`), Amber (`#F59E0B`), Slate-50 (`#F8FAFC`)
* **Link Prototipe Figma:** [Akses Prototipe Figma Interaktif](https://www.figma.com/proto/vfNU3eumtUc242QA1kXXuS/E---Commerce-UMKM-Kopi-Tiga-Titik?node-id=2021-1535&p=f&t=zQI445cQPiAQkz2c-1&scaling=scale-down&content-scaling=fixed&page-id=0%3A1&starting-point-node-id=2021%3A1535)
* **Link Design UI Figma:** [Akses UI](https://www.figma.com/design/vfNU3eumtUc242QA1kXXuS E---Commerce-UMKM-Kopi-Tiga-Titik?node-id=0-1&t=F8QVJyREhXQndbxe-1)

---

## Daftar Layar & Pemetaan File Desain (19 Pages)

| No | Nama Layar | File Gambar |
|---|---|---|
| 01 | **Mobile Landing Page** | [Kopi Tiga Titik - Mobile Landing Page.jpg] |
| 02 | **Menu & Catalog Page** | [Kopi Tiga Titik - Menu & Catalog Page.jpg] |
| 03 | **Cart Page (Keranjang Belanja)** | [Kopi Tiga Titik - Cart Page.jpg] |
| 04 | **Cart & Checkout** | [Kopi Tiga Titik - Cart & Checkout.jpg] |
| 05 | **Pembayaran QRIS** | [Kopi Tiga Titik - Pembayaran QRIS.jpg] |
| 06 | **Order Tracking & Live Status** | [Kopi Tiga Titik - Order Tracking & Live Status.jpg] |
| 07 | **Riwayat Pesanan (Pesanan Saya)** | [Kopi Tiga Titik - History Order (Pesanan Saya).jpg] |
| 08 | **Profil Pelanggan (Akun Saya)** | [Kopi Tiga Titik - Profil Pelanggan (Akun Saya).jpg] |
| 09 | **Register Page** | [Kopi Tiga Titik - Register Page.jpg] |
| 10 | **Login Sebagai Pembeli** | [Kopi Tiga Titik - Login Sebagai Pembeli.jpg] |
| 11 | **Login Sebagai Kurir** | [Kopi Tiga Titik - Login Sebagai Kurir.jpg] |
| 12 | **Login Sebagai Owner** | [Kopi Tiga Titik - Login Sebagai Owner.jpg] |
| 13 | **Dashboard Kurir: Tugas Baru** | [Kopi Tiga Titik - Dashboard Kurir Pengantaran.jpg] |
| 14 | **Dashboard Kurir: Tab Selesai** | [Kopi Tiga Titik - Dasboard Kurir Pengantaran Tab _Selesai_.jpg] |
| 15 | **Profil Kurir Page** | [Kopi Tiga TItik - Profil Kurir Page.jpg] |
| 16 | **Owner Dashboard & Mini POS** | [Kopi Tiga Titik - Owner Dashboard & Mini POS.jpg] |
| 17 | **Pop-up Update Lokasi Lapak** | [Kopi Tiga Titik - Pop up Update Lokasi Lapak.jpg] |
| 18 | **Prediksi Moving Average** | [Kopi Tiga Titik - Prediksi Moving Average & Forecasting Stok.jpg] |
| 19 | **Profil Owner Page** | [Kopi Tiga Titik - Profil Owner Page.jpg] |

---

## Flowchart Prototipe

```mermaid
flowchart TD
    Start([Pengunjung Buka Web]) --> Landing [01. Mobile Landing Page]

    subgraph AUTH ["Alur Autentikasi dan Multi-Role"]
        Landing -- "Klik Tombol Masuk" --> LoginPembeli [10. Login Sebagai Pembeli]
        LoginPembeli -- "Tab Daftar Akun" --> Register [09. Register Page]
        Register -. "Kembali / Sudah Punya Akun" .-> LoginPembeli
        
        LoginPembeli <--> |Switch Capsul Kurir| LoginKurir [11. Login Sebagai Kurir]
        LoginPembeli <--> |Switch Capsul Owner| LoginOwner [12. Login Sebagai Owner]
        LoginKurir <--> |Switch Capsul Owner| LoginOwner
    end

    subgraph PEMBELI ["Customer Journey"]
        Landing -- "Klik Pesan Sekarang" --> Catalog [02. Menu & Catalog Page]
        LoginPembeli -- "Masuk ke Akun" --> Catalog
        Catalog -- "Klik Ikon Keranjang" --> CartPage[03. Cart Page Keranjang]
        CartPage -- "Lanjut Pembayaran" --> Checkout[04. Cart & Checkout]
        Checkout -- "Pilih Pengiriman & Bayar QRIS" --> QRIS[05. Pembayaran QRIS]
        QRIS -- "Klik Saya Sudah Bayar" --> Tracking[06. Order Tracking & Live Status]
        Tracking -- "Lihat Semua Transaksi" --> History[07. Riwayat Pesanan / Pesanan Saya]
        History -. "Pesan Lagi" .-> Catalog
    end

    subgraph KURIR ["Dashboard dan Profil Kurir"]
        LoginKurir -- "Masuk ke Akun" --> KurirTugas[13. Dashboard Kurir: Tugas Baru]
        KurirTugas <--> |Tab Selesai / Tugas Baru| KurirSelesai[14. Dashboard Kurir: Tab Selesai]
        KurirTugas -- "Kelola Profil" --> ProfilKurir[15. Profil Kurir Page]
        ProfilKurir -- "Kembali ke Tugas" --> KurirTugas
        ProfilKurir -- "Keluar dari Akun (Logout)" --> Landing
    end

    subgraph OWNER ["Dashboard dan Profil Owner"]
        LoginOwner -- "Masuk ke Akun" --> OwnerDash[16. Owner Dashboard & Mini POS]
        OwnerDash <--> |Modal Pop-up| ModalLokasi[17. Pop-up Update Lokasi Lapak]
        OwnerDash <--> |Klik Prediksi Penjualan| PageSMA[18. Prediksi Moving Average]
        OwnerDash -- "Kelola Profil" --> ProfilOwner[19. Profil Owner Page]
        ProfilOwner -- "Kembali ke Dashboard" --> OwnerDash
        ProfilOwner -- "Keluar dari Akun (Logout)" --> Landing
    end

    subgraph NAV ["Global Bottom Navigation Bar"]
        BottomNav{{Global Bottom Navigation Bar}} -.-> Landing
        BottomNav -.-> Catalog
        BottomNav -.-> History
        BottomNav -.-> Profile[08. Profil Pelanggan]
        Profile -. "Klik Riwayat Transaksi" .-> History
        Profile -. "Keluar dari Akun (Logout)" .-> Landing
    end
```

---

## Rincian Alur Berurutan (Sequential User Journey)

### 1. Alur Autentikasi & Multi-Role Switching (Pages 09, 10, 11, 12)
Dirancang untuk demonstrasi multi-hak akses (Pelanggan, Kurir, Owner):
* **Langkah 1 (Akses Login):** Dari [Mobile Landing Page] klik tombol *"Masuk"* di kanan atas header.

* **Langkah 2 (Role Default):** Masuk ke [Login Sebagai Pembeli] dengan data `nabil@gmail.com`.

* **Langkah 3 (Peralihan Role Instan):**
  * Klik Capsul / Button **"Kurir"** diarahkan ke Page [Login Sebagai Kurir] (`kurir1ilham@gmail.com`).
  * Klik Capsul / Button **"Owner"** diarahkan ke Page [Login Sebagai Owner] (`owner1@gmail.com`).
  * Klik Capsul / Button **"Pembeli"** diarahkan kembali ke form login sebagai pembeli.

* **Langkah 4 (Daftar Akun Baru):** Klik tab *"Daftar Akun Baru"* di form login untuk membuka [Register Page]. 
Tombol back pada [Register Page] membawa pengguna kembali ke form login.

---

### 2. Alur Pemesanan & Transaksi Pelanggan (Customer Journey: Pages 01 ke 08)

Alur *end-to-end* pemesanan kopi botol 250ml:
1. **Pages 01 - Mobile Landing Page:**
   * Melihat hero banner resmi 4 botol (*Titik Temu, Titik Hitam, Titik Manja, Titik Jenuh*).
   * Memeriksa banner status lapak aktif hari ini (lokasi mangkal dan jam operasional).
   * Klik tombol **"Pesan Sekarang"** menuju katalog.

2. **Pages 02 - Menu & Catalog Page:**
   * Memilih layanan Delivery atau Self-Pickup. 
   * Memilih varian kopi botol 250ml dan mengatur jumlah pesanan via tombol `+` / `-`.
   * Meninjau label ketersediaan stok botol realtime.
   * Menekan tombol *"Tambah ke Keranjang"* atau klik floating bar keranjang belanja untuk menuju Cart Page.

3. **Pages 03 - Cart Page (Keranjang Belanja):**
   * Meninjau daftar item kopi botol yang ada di keranjang (*Titik Temu 250ml Rp 20.000 / 2 botol*, *Titik Hitam 250ml Rp 8.000 / 1 botol*).
   * Mengatur jumlah botol secara cepat menggunakan tombol `-` / `+`, atau menghapus varian dengan ikon tempat sampah merah.
   * Menambahkan varian rekomendasi secara instan via quick chip (*Titik Manja +*, *Titik Jenuh +*).
   * Mengisi kolom **Catatan Pesanan untuk Barista** (misal: "Es batu dipisah, kurangi manis arennya").
   * Memeriksa rincian ringkasan biaya (Subtotal Produk Rp 28.000, Kemasan & Es Pendingin Gratis, Total Sementara Rp 28.000).
   * Klik tombol sticky bottom bar **"Lanjut Pembayaran →"** menuju halaman konfirmasi & checkout.

4. **Pages 04 - Cart & Checkout (Konfirmasi Pesanan):**
   * Memilih metode layanan: **Diantar ke Rumah** (dengan rincian ongkir Mojokerto & alamat Jl. Hayam Wuruk No. 42) atau **Ambil di Lapak** (di lapak jalanan/rumah produksi tanpa ongkir).
   * Memilih metode pembayaran: **QRIS** atau **Cash**.
   * Klik tombol **Bayar QRIS** jika metode pembayaran QRIS dipilih.

5. **Pages 05 - Pembayaran QRIS:**
   * Menampilkan QRIS dinamis resmi dengan batas waktu pembayaran **10 Menit**.
   * Setelah transfer berhasil, klik tombol **"Saya Sudah Bayar"**.

6. **Pages 06 - Order Tracking & Live Status:**
   * Pelanggan memantau status pesanan secara bertahap: *Menunggu Konfirmasi*, *Diproses*, *Sedang Diantar Kurir*, dan *Selesai*.
   * Terlihat identitas kurir yang bertugas (Mas Ilham, estimasi tiba 15–20 menit) dan tombol WhatsApp kurir.
   * Klik tombol atau icon Pesanan di bottom bar untuk melihat riwayat pesanan lama dan pesanan baru (on going).

7. **Pages 07 - Riwayat Pesanan (Pesanan Saya):**
   * Menampilkan tab **Sedang Berjalan** (dengan tombol *"Lacak Pesanan Live"*) dan tab **Riwayat Selesai**.
   * Transaksi lama dilengkapi tombol **"Pesan Lagi"** (*re-order* instan) dan **"Lihat Nota Digital"**.
   * Pesanan yang sedang berjalan tidak dilengkapi tombol **"Pesan Lagi"** dan **"Lihat Nota Digital"**.

8. **Pages 08 - Profil Pelanggan (Akun Saya):**
   * Menampilkan data diri pelanggan (Nabil, nomor HP, email), Alamat Pengiriman Utama, Varian Kopi Favorit, dan tombol **"Keluar dari Akun (Logout)"**.

---

### 3. Alur Bottom Navigation Bar
Bottom bar 4 tab selalu hadir di semua halaman pelanggan untuk berpindah antar halaman secara efisien:
* **Beranda:** Menuju [Mobile Landing Page]
* **Menu:** Menuju [Menu & Catalog Page]
* **Pesanan:** Menuju [History Order (Pesanan Saya)]
* **Akun:** Menuju [Profil Pelanggan (Akun Saya)]

---

### 4. Alur Kerja Kurir Pengantaran (Page 13, 14, 15)

Dirancang khusus untuk operasional pengantaran kurir:
* **Page 13 - Dashboard Kurir: Tugas Baru:**
  * Kurir melihat kartu pesanan aktif yang ditugaskan padanya hari ini (siap diantar dan menunggu antrean).
  * Dilengkapi info nama pemesan, no WhatsApp, alamat antar, jumlah botol kopi, dan tombol aksi *"Kirim ke Tujuan"*.
  * Setelah pesanan diterima pelanggan, kurir mengklik tombol *"Tandai Selesai"*.

* **Page 14 - Dashboard Kurir: Tab Selesai:**
  * Klik ke tab *"Selesai (6)"*.
  * Menampilkan kartu rekap pencapaian harian (8 Pesanan Sukses, 24 Total Botol).
  * Daftar riwayat pengantaran tuntas hari ini dengan badge hijau *SELESAI DIANTAR*, jam serah terima, dan bukti catatan penerima.

* **Page 15 - Profil Kurir Page (Kelola Data Diri Kurir):**
  * Diakses dari header / menu profil di area kurir.
  * Menampilkan identitas kurir: Avatar inisial "I", nama Ilham Ramadhan, email `kurir1ilham@gmail.com`, dan WhatsApp `+62 812-3456-7890`.
  * Kartu kinerja harian: Total 142 pesanan terantar sukses serta data kendaraan operasional (Motor Honda Beat Hitam, Plat S 4821 NA).
  * **Form CRUD Profil Kurir:** Fitur pembaruan Nama Kurir, Alamat Email, Nomor WhatsApp aktif (untuk koordinasi pelanggan), dan Ubah Kata Sandi Baru.
  * Tombol **"Simpan Perubahan Data"** dan tombol **"Keluar dari Akun (Logout)"**.

---

### 5. Alur Manajemen Owner & Mini POS (Page 16, 17, 18, 19)

Pusat kontrol operasional untuk owner UMKM Kopi Tiga Titik:
* **Page 16 - Owner Dashboard & Mini POS:**
  * **Ringkasan Finansial:** Omset hari ini, total botol terjual online & offline, dan status stok harian.
  * **Mini POS Lapak Offline:** Kasir cepat di pinggir jalan untuk menginput penjualan tunai/QRIS langsung yang otomatis mengurangi stok botol di database.
  * **Kontrol Stok Realtime:** Menampilkan peringatan rule-based *"Stok Menipis"* jika botol <3.

* **Page 17 - Pop-up Update Lokasi Lapak:**
  * Dibuka dari tombol *"Perbarui Lokasi lapak Hari ini"* di dashboard owner.
  * Owner mengatur status lapak (*Buka / Tutup*), nama patokan mangkal (*Jl. Benteng Pancasila depan Taman Sentanan*), geser pin koordinat peta, dan jam operasional (*15.00 - 18.00 WIB*).
  * Menekan tombol *"Simpan"* otomatis memperbarui banner lokasi di landing page pelanggan.

* **Page 18 - Prediksi Moving Average & Forecasting Stok (Komponen Komputasional):**
  * Dibuka dari menu navigasi "Prediksi".
  * Menerapkan rumus **Moving Average (3 Minggu)**:
    `Prediksi = (Minggu 1 + Minggu 2 + Minggu 3) ÷ 3`
  * Menampilkan rekomendasi produksi minggu depan per varian:
    * *Titik Temu:* Prediksi **58 Botol** (~8–9 botol/hari, permintaan tinggi).
    * *Titik Hitam:* Prediksi **31 Botol** (~4–5 botol/hari, stabil).
    * *Titik Manja:* Prediksi **48 Botol** (~7 botol/hari, tren naik).
    * *Titik Jenuh:* Prediksi **45 Botol** (~6–7 botol/hari, stabil).
  * Tombol *"Unduh Data Rekap (Excel)"* untuk pelaporan berkala.

* **Page 19 - Profil Owner Page (Kelola Akun Owner):**
  * Diakses dari header / menu profil di dashboard owner.
  * Menampilkan identitas pengelola: Avatar inisial "F", nama Farrel, email `owner1@gmail.com`, status Pemilik Usaha Kopi Tiga Titik.
  * Kartu status operasional UMKM: Status Lapak Buka (15.00 - 18.00 WIB), Pusat Produksi & Dapur (Dsn. Daleman, Ds. Japan, Kec. Sooko), dan Lapak Mangkal Street Coffee (Jl. Benteng Pancasila).
  * **Form CRUD Akun Owner:** Fitur pembaruan Nama Lengkap Owner, Email Utama, Kata Sandi Baru, dan Konfirmasi Kata Sandi Baru.
  * Tombol **"Simpan Perubahan Data"** dan tombol **"Keluar dari Akun (Logout)"**.
