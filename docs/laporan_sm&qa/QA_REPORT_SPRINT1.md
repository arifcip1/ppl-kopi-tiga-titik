# LAPORAN QA TESTING — SPRINT 1
## Proyek: Kopi Tiga Titik (PPL)

---

| Informasi | Detail |
|-----------|--------|
| **Nama QA Tester** | M. Arif (QA Tester & Scrum Master) |
| **Sprint** | Sprint 1 |
| **Tanggal Testing** | 30 Oktober – 4 November 2026 |
| **Tanggal Laporan** | 4 November 2026 |
| **Repository** | ppl-kopi-tiga-titik |
| **Branch Utama** | main / develop |

---

## 1. Ringkasan Eksekutif

Pengujian dilakukan pada Sprint 1 untuk memverifikasi fungsionalitas fitur autentikasi, otorisasi, registrasi, katalog pelanggan, dan keamanan akses halaman. QA Testing difokuskan pada pengujian fungsional dan keamanan (security) aplikasi web Kopi Tiga Titik.

### Statistik Hasil Testing

| Metrik | Jumlah |
|--------|--------|
| Total Bug Ditemukan | 6 |
| Bug FIXED ✅ | 6 |
| Bug OPEN ❌ | 0 |
| **Persentase Keberhasilan** | **100%** |

---

## 2. Lingkup Pengujian

### Fitur yang Diuji
- Autentikasi (Login & Register) pelanggan
- Otorisasi akses halaman Owner vs Pelanggan
- Guest protection (akses tanpa login)
- Tampilan halaman Profil pelanggan
- Tampilan halaman Katalog pelanggan
- Validasi data sisi server (server-side validation)

### Yang Tidak Diuji (Out of Scope)
- Fitur pembayaran
- Fitur pengiriman
- Halaman Owner (selain otorisasi)
- Performa dan load testing

---

## 3. Lingkungan Pengujian

| Komponen | Detail |
|----------|--------|
| **Framework** | Laravel (PHP) |
| **Database** | MySQL |
| **Browser** | Google Chrome (latest) |
| **Mode Pengujian** | Lokal (`http://127.0.0.1:8000`) |
| **Mode Incognito** | Digunakan untuk uji guest protection |
| **Tools** | Browser DevTools (Inspect Element) |

---

## 4. Detail Bug yang Ditemukan

---

### BUG-01

**Bug:** Auth – Produk muncul di keranjang saat registrasi  
**Ditemukan oleh:** M. Arif (QA Tester)  
**Ditugaskan ke:** Frontend Developer  
**Status:** FIXED ✅  
**Prioritas:** Tinggi  

**Masalah:** Setelah akun pelanggan baru terdaftar, keranjang menampilkan beberapa produk secara otomatis, padahal user belum melakukan aksi apapun.

**Langkah Reproduksi:**
1. Buka halaman **Register** → buat akun baru.
2. Login dengan akun yang baru dibuat.
3. Buka halaman **Keranjang**.

**Hasil Diharapkan:** Keranjang kosong karena user belum menambahkan produk apapun.

**Hasil Aktual:** Produk-produk dari akun lain / data seeder muncul di keranjang.

**Saran Perbaikan:** Pastikan session `cart` di-inisialisasi kosong pada `RegisterController` atau middleware after login. Hapus/clear `session('cart')` bila belum ada item yang dikaitkan dengan user baru.

**Branch Revisi:** `revisi/PPL-XX-revisi-auth-keranjang-qa-tester`

---

### BUG-02

**Bug:** Auth – Profil pelanggan menampilkan data akun orang lain  
**Ditemukan oleh:** M. Arif (QA Tester)  
**Ditugaskan ke:** Frontend/Backend Developer  
**Status:** FIXED ✅  
**Prioritas:** Tinggi  

**Masalah:** Saat pelanggan mengklik tombol "Profil Saya", halaman profil menampilkan data milik user lain (dari data seeder), bukan data user yang sedang login. Sidebar sudah menampilkan nama yang benar, tetapi isi halaman profil salah.

**Langkah Reproduksi:**
1. Login sebagai pelanggan A.
2. Klik tombol **Profil Saya**.
3. Amati data yang ditampilkan di halaman profil.

**Hasil Diharapkan:** Menampilkan data pelanggan A (nama, email, alamat sesuai akun yang login).

**Hasil Aktual:** Menampilkan data pelanggan lain yang berasal dari data seeder.

**Saran Perbaikan:** Pada `ProfileController@show` gunakan `auth()->user()` untuk mengambil data user, bukan `User::find($id)` yang hard-coded atau mengambil ID dari parameter yang salah.

**Branch Revisi:** `revisi/PPL-XX-revisi-auth-profil-qa-tester`

---

### BUG-03

**Bug:** Register – Kolom `email_verified_at` terisi otomatis tanpa verifikasi  
**Ditemukan oleh:** M. Arif (QA Tester)  
**Ditugaskan ke:** Backend Developer  
**Status:** FIXED ✅  
**Prioritas:** Sedang  

**Masalah:** Saat user mendaftar akun baru, kolom `email_verified_at` di database langsung terisi dengan nilai timestamp, padahal user belum melakukan verifikasi email sama sekali. Seharusnya nilai tetap `null` sampai user mengklik link verifikasi.

**Langkah Reproduksi:**
1. Buka halaman **Register** → daftarkan akun baru.
2. Periksa tabel `users` di database.
3. Lihat kolom `email_verified_at` pada user yang baru dibuat.

**Hasil Diharapkan:** Kolom `email_verified_at` bernilai `null` sampai user mengklik link verifikasi email.

**Hasil Aktual:** Kolom `email_verified_at` langsung terisi timestamp saat registrasi.

**Saran Perbaikan:** Pada `RegisteredUserController@store`, hapus atau comment baris yang secara otomatis meng-set `email_verified_at = now()`. Implementasikan Laravel built-in email verification dengan menggunakan interface `MustVerifyEmail` pada model `User`.

**Branch Revisi:** `revisi/PPL-XX-revisi-register-email-verification-qa-tester`

---

### BUG-04

**Bug:** Katalog – Ikon keranjang muncul di header halaman katalog  
**Ditemukan oleh:** M. Arif (QA Tester)  
**Ditugaskan ke:** Frontend Developer  
**Status:** FIXED ✅  
**Prioritas:** Rendah  

**Masalah:** Pada halaman katalog pelanggan, ikon keranjang muncul di bagian header. Berdasarkan desain yang disepakati, ikon keranjang seharusnya hanya ada di sidebar, dan sidebar harus menampilkan badge notifikasi angka sesuai jumlah item dalam keranjang.

**Langkah Reproduksi:**
1. Login sebagai pelanggan.
2. Buka halaman **Katalog**.
3. Amati bagian header.

**Hasil Diharapkan:** Header tidak memiliki ikon keranjang. Ikon keranjang dengan badge jumlah item hanya ada di sidebar.

**Hasil Aktual:** Ikon keranjang muncul di header halaman katalog.

**Saran Perbaikan:** Edit file layout/header katalog → hapus elemen ikon keranjang dari header. Tambahkan badge notifikasi angka pada ikon keranjang di sidebar menggunakan data jumlah item keranjang user yang sedang login.

**Branch Revisi:** `revisi/PPL-XX-revisi-katalog-header-keranjang-qa-tester`

---

### BUG-05

**Bug:** Otorisasi – Pelanggan dapat mengakses halaman Owner  
**Ditemukan oleh:** M. Arif (QA Tester)  
**Ditugaskan ke:** Backend Developer  
**Status:** FIXED ✅  
**Prioritas:** Kritis 🔴  

**Masalah:** Setelah login sebagai pelanggan, mengubah URL secara manual menjadi `/owner/produk` berhasil menampilkan halaman Owner tanpa ada pesan error atau pengalihan. Ini merupakan celah keamanan serius karena pelanggan dapat mengakses area yang seharusnya hanya untuk Owner.

**Langkah Reproduksi:**
1. Login sebagai pelanggan (bukan Owner).
2. Ubah URL di browser menjadi `http://127.0.0.1:8000/owner/produk`.
3. Tekan Enter.

**Hasil Diharapkan:** Dialihkan ke halaman login atau menampilkan halaman 403 Forbidden.

**Hasil Aktual:** Halaman Owner `/owner/produk` berhasil tampil dan dapat diakses sepenuhnya.

**Saran Perbaikan:** Tambahkan middleware `auth:owner` atau custom middleware `CheckOwner` pada semua route Owner di `routes/web.php`. Pastikan guard `owner` terdefinisi di `config/auth.php` dan middleware memeriksa `auth()->user()->role === 'owner'`.

**Branch Revisi:** `revisi/PPL-XX-revisi-otorisasi-owner-qa-tester`

---

### BUG-06

**Bug:** Guest Protection – Halaman Owner dapat diakses tanpa login (mode incognito)  
**Ditemukan oleh:** M. Arif (QA Tester)  
**Ditugaskan ke:** Backend Developer  
**Status:** FIXED ✅  
**Prioritas:** Kritis 🔴  

**Masalah:** Saat membuka browser dalam mode incognito (tanpa sesi login apapun), mengakses URL `/owner/produk` secara langsung tetap menampilkan halaman Owner. Seharusnya pengguna yang belum login dialihkan ke halaman login terlebih dahulu.

**Langkah Reproduksi:**
1. Buka browser dalam mode **incognito** (tanpa login).
2. Akses URL `http://127.0.0.1:8000/owner/produk` langsung.
3. Amati hasilnya.

**Hasil Diharapkan:** Dialihkan ke halaman **login** karena belum ada sesi autentikasi.

**Hasil Aktual:** Halaman Owner tampil dan dapat diakses meskipun belum login.

**Saran Perbaikan:** Pastikan semua route Owner dilindungi dengan middleware `auth` atau `auth:owner` di `routes/web.php`. Periksa apakah ada `Route::fallback` atau route tanpa middleware yang mengembalikan view Owner.

**Branch Revisi:** `revisi/PPL-XX-revisi-guest-protection-qa-tester`

---

## 5. Tambahan: Hasil Uji Validasi Server-Side (TC-INT-04)

**Test Case:** TC-INT-04 – Validasi Server-Side form tambah produk  
**Metode:** Bypass form validation via browser Inspect Element  
**Status:** DITINDAKLANJUTI DI SPRINT 2 (PPL-25) ⚠️  

**Langkah yang dilakukan:**
1. Buka halaman tambah produk Owner.
2. Buka DevTools (F12) → ubah atribut `min` pada input harga dan stok.
3. Submit form dengan nilai harga `Rp 0` dan stok `-1`.

**Hasil Aktual Sprint 1:**
- Browser menampilkan pesan validasi HTML5 bawaan (bukan dari backend).
- Pesan error: *"Value must be greater than or equal to -999"* dan *"Please enter a valid value. The two nearest valid values are -499 and 1."*
- Ini menunjukkan validasi masih rentan manipulasi client jika backend belum memvalidasi dengan ketat.

**Rencana Solusi di Sprint 2 (PPL-25):**
Form Request Laravel (`StoreProductRequest` & `UpdateProductRequest`) dengan rule `integer|min:1` untuk harga dan `integer|min:0` untuk stok, ditambah Global Exception Handler untuk respon JSON seragam.

---

## 6. Kesimpulan

Semua **6 bug** yang ditemukan pada Sprint 1 telah berhasil diperbaiki oleh author masing-masing dan diverifikasi oleh QA Tester. Aplikasi Kopi Tiga Titik pada Sprint 1 dinyatakan **LULUS QA** dengan catatan tambahan terkait validasi server-side (TC-INT-04) yang dialihkan sebagai fokus utama Sprint 2 (PPL-25).

### Rekomendasi untuk Sprint 2
1. Implementasi validasi server-side pada semua form input (terutama form produk) via Form Request Laravel.
2. Implementasi verifikasi email saat registrasi pelanggan baru.
3. Review ulang semua route dan middleware untuk memastikan tidak ada celah otorisasi lain.

---

## 7. Tanda Tangan & Persetujuan

| Peran | Nama | Tanggal |
|-------|------|---------|
| QA Tester & SM | M. Arif | 4 November 2026 |
| Product Owner | M. Farrel Fastha | 4 November 2026 |
| Tim Pengembang | Dev Team Kopi Tiga Titik | 4 November 2026 |

---

*Dokumen ini dibuat sebagai bagian dari dokumentasi resmi Sprint 1 — PPL Kopi Tiga Titik.*

