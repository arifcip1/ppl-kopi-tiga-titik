# LAPORAN REKAP SCRUM MASTER (SM) - SPRINT 1
**Mata Kuliah:** Proyek Perangkat Lunak (PPL) - Semester Ganjil 2026/2027  
**Dosen Pengampu:** Fifin Ayu Mufarroha, S.Kom., M.Kom.  
**Proyek:** E-Commerce UMKM Kopi Tiga Titik  
**Peran Pelapor:** Scrum Master & QA Tester (24-084 M. Arif)  
**Product Owner:** 24-114 Muhammad Farrel Fastha  
**Periode Sprint 1:** 28 Oktober 2026 – 4 November 2026  
**Referensi:** Lampiran D (Dokumentasi Manajemen Proyek) & Tabel 6, Tabel 7 Panduan Proyek PPL  

---

## 1. Ringkasan Eksekutif Sprint 1

Pada tahap perencanaan awal (*Sprint Planning*), tim memetakan keseluruhan **36 User Story** pada Product Backlog menjadi struktur rincian kerja (*Work Breakdown Structure* / WBS `PPL 01` s.d. `PPL 42`) untuk seluruh siklus proyek (**Sprint 1 s.d. Sprint 4**).

Khusus pada **Sprint 1**, eksekusi difokuskan pada pembangunan fondasi sistem yang mencakup analisis aturan bisnis, desain ERD & migrasi database terpadu, seeder produk & simulasi penjualan, antarmuka responsif mobile-first, autentikasi & hak akses berbasis peran, pengelolaan produk oleh Owner, serta katalog produk pelanggan.

- **Total User Story (Product Backlog) yang Dicakup di Sprint 1:** **9 User Story** (`US 1`, `US 2`, `US 6`, `US 7`, `US 8`, `US 9`, `US 11`, `US 21`, `US 22`)
- **Total Work Package / Kartu di Jira Sprint 1:** **12 Work Package Utama (`PPL-5` s.d. `PPL-16` / `PPL 01` s.d. `PPL 12`)** + **1 Kartu Integrasi (`PPL-1`)** = **13 Kartu Jira (48 Story Points)**
- **Total Kartu Selesai (Done):** **100% Selesai** (9/9 User Story & 13/13 Kartu Jira Done pada akhir siklus QA 4 November 2026)
- **Status Integrasi Tools (I-1, I-2, I-3):** Terpasang & Berjalan Otomatis
- **Status QA Testing Sprint 1 (`PPL-16`):** 6 Bug Terdeteksi & 6 Bug Berhasil Diperbaiki (100% Fixed)

---

## 2. Tabel Pemetaan User Story ke WBS Sprint 1

Dari 36 User Story pada Product Backlog, sebanyak **9 User Story** dieksekusi pada Sprint 1 (mencakup modul `KF-01` s.d. `KF-04` serta fondasi UI dan data seeder), yang dipecah menjadi **12 Work Package teknis** di Jira:

| No. US | Peran | Deskripsi User Story (Product Backlog) | Modul / Kebutuhan | Tiket Jira / WBS (Sprint 1) | Status |
|:---:|---|---|---|---|:---:|
| **US 1** | Product Owner | Seluruh data pesanan online dan penjualan tersimpan dalam satu basis data terpadu | **KF-01** (Basis Data) | `PPL-5` (PPL 01), `PPL-6` (PPL 02), `PPL-7` (PPL 03 - ERD & Migrasi DB) | Done ✅ |
| **US 2** | Product Owner | Sistem membedakan hak akses Owner, Kurir, dan Pelanggan | **KF-02** (Autentikasi & Akses) | `PPL-11` (PPL 07 - Backend Auth & Hak Akses Berbasis Peran) | Done ✅ |
| **US 6** | Product Owner | Aplikasi nyaman digunakan di HP (mobile-first) dan tetap dapat digunakan di komputer | Fondasi UI Responsif | `PPL-9` (PPL 05 - Desain UI/UX, Layout Mobile-First & Komponen Dasar) | Done ✅ |
| **US 7** | Product Owner | Data awal 4 varian produk dan data simulasi penjualan yang diberi label jelas tersedia untuk demonstrasi | Fondasi Data & Seeder | `PPL-8` (PPL 04 - Seeder Varian Produk & Data Simulasi Penjualan Berlabel) | Done ✅ |
| **US 8** | Pelanggan | Mendaftar akun (nama, email, kata sandi) dan masuk ke aplikasi | **KF-02** (Autentikasi & Akses) | `PPL-10` (PPL 06 - FE Daftar, Login & Logout), `PPL-11` (PPL 07 - BE Auth) | Done ✅ |
| **US 9** | Pelanggan | Melihat katalog berisi foto, nama, harga, daftar bahan, deskripsi singkat (keawetan 5–7 hari), dan stok terbaru | **KF-04** (Katalog Produk) | `PPL-14` (PPL 10 - FE Katalog Produk), `PPL-15` (PPL 11 - BE Katalog Produk) | Done ✅ |
| **US 11** | Pelanggan | Tombol pesan nonaktif otomatis ketika stok suatu varian habis | **KF-04** (Katalog & Stok) | `PPL-14` (PPL 10 - FE Katalog), `PPL-15` (PPL 11 - BE Ketersediaan Stok) | Done ✅ |
| **US 21** | Owner Kopi 3 Titik | Menambah, mengubah, dan menghapus produk beserta nama, harga, daftar bahan, deskripsi singkat, foto, dan stok | **KF-03** (Kelola Produk) | `PPL-12` (PPL 08 - FE Kelola Produk), `PPL-13` (PPL 09 - BE Kelola Produk) | Done ✅ |
| **US 22** | Owner Kopi 3 Titik | Memperbarui jumlah stok tiap varian kapan saja | **KF-03** (Kelola Produk) | `PPL-12` (PPL 08 - FE Kelola Produk), `PPL-13` (PPL 09 - BE Kelola Produk) | Done ✅ |
| *Lintas Fitur* | Tim / QA | Pengujian integrasi GitHub serta QA Testing Sprint 1 (Fungsional, Integrasi, Alur Bisnis, Pencatatan Hasil & Retest) | Quality Engineering & Integrasi | `PPL-1` (Test integrasi GitHub), `PPL-16` (PPL 12 - QA Testing Sprint 1) | Done ✅ |

---

## 3. Rincian Kartu Jira Sprint 1 (Work Items & Story Points)

| Kode Jira | Kode WP | Nama Tugas / Work Item | Story Points | Tenggat | Status Akhir |
|:---:|:---:|---|:---:|:---:|:---:|
| `PPL-1` | - | Test integrasi GitHub | - | 28 Okt | **Done** ✅ |
| `PPL-5` | PPL 01 | Analisis Kebutuhan, Aturan Bisnis & Kontrak Integrasi Antarmodul | 3 | 28 Okt | **Done** ✅ |
| `PPL-6` | PPL 02 | Dokumentasi Teknis Awal & Setup Repositori | 5 | 29 Okt | **Done** ✅ |
| `PPL-7` | PPL 03 | Desain ERD, Migrasi Database Laravel & Testing Relasi Data | 3 | 30 Okt | **Done** ✅ |
| `PPL-8` | PPL 04 | Seeder Varian Produk & Data Simulasi Penjualan Berlabel (min. 3 Minggu) | 3 | 31 Okt | **Done** ✅ |
| `PPL-9` | PPL 05 | Desain UI UX, Layout Mobile-First & Komponen Dasar (Tailwind CSS) | 5 | 29 Okt | **Done** ✅ |
| `PPL-10` | PPL 06 | Frontend Daftar, Login & Logout | 3 | 30 Okt | **Done** ✅ |
| `PPL-11` | PPL 07 | Backend/API Autentikasi, Hak Akses Berbasis Peran, Validasi, Integrasi & Testing | 5 | 2 Nov | **Done** ✅ |
| `PPL-12` | PPL 08 | Frontend Kelola Produk (Tambah, Edit, Hapus, Harga, Stok, Foto) | 4 | 30 Okt | **Done** ✅ |
| `PPL-13` | PPL 09 | Backend/API Produk, Stok, Unggah Foto, Integrasi & Testing Kelola Produk | 5 | 2 Nov | **Done** ✅ |
| `PPL-14` | PPL 10 | Frontend Katalog Produk (Daftar, Detail, Harga, Info Stok, Pilih Produk) | 4 | 31 Okt | **Done** ✅ |
| `PPL-15` | PPL 11 | Backend/API Katalog, Ketersediaan Stok, Integrasi & Testing Katalog Produk | 4 | 2 Nov | **Done** ✅ |
| `PPL-16` | PPL 12 | QA Testing Sprint 1: Fungsional, Integrasi, Alur Bisnis, Pencatatan Hasil & Retest | 4 | 4 Nov | **Done** ✅ |
| **TOTAL** | | **13 Work Items (Mencakup 9 User Story Product Backlog)** | **48 Pts** | | **100% Done** |

---

## 4. Pelaksanaan 4 Seremoni Scrum (Sesuai Tabel 6 Panduan PPL)

### 4.1 Sprint Planning & Pemetaan WBS (Work Breakdown Structure)
- **Waktu Pelaksanaan:** 28 Oktober 2026 (±30–45 menit)
- **Media:** Discord kanal `#diskusi` & Jira Board
- **Agenda & Yang Dilakukan:**
  1. **Seleksi & Prioritisasi Product Backlog:** Meninjau total **36 User Story** hasil wawancara dengan mitra UMKM Kopi Tiga Titik, kemudian menyepakati **25 User Story utama** yang masuk ke dalam ruang lingkup pengembangan (*in-scope* 11 fitur wajib) dan mencatat **11 User Story tambahan** sebagai *out-of-scope* sesuai dokumen kontrak `PPL 01` dan `PRD`.
  2. **Penyusunan WBS Menyeluruh (Sprint 1 s.d. Sprint 4):** Memecah seluruh User Story terpilih ke dalam **42 Work Package (`PPL 01` s.d. `PPL 42` / Tiket Jira `PPL-5` s.d. `PPL-46`)** yang langsung dipetakan dan dijadwalkan secara utuh ke dalam **4 Sprint (28 Oktober – 29 November 2026)** beserta penanggung jawab (PIC) dan estimasi *Story Points*.
  3. **Penetapan Fokus & Tujuan Sprint 1 (*Sprint 1 Goal*):** *"Membangun fondasi sistem yang mencakup database, autentikasi dan hak akses, antarmuka responsif, pengelolaan produk, serta katalog produk. Seluruh fitur Sprint 1 ditargetkan selesai dan siap diuji pada 3–4 November 2026."*
  4. **Komitmen Backlog Sprint 1:** Menarik **12 Work Package pertama (`PPL 01` s.d. `PPL 12` / Jira `PPL-5` s.d. `PPL-16`)** ditambah 1 kartu uji integrasi (`PPL-1`) yang merepresentasikan **9 User Story fondasi & inti** dengan total beban **48 Story Points**.
  5. **Kesepakatan Konvensi Integrasi:** Menyepakati penggunaan ID tiket `PPL-xx` secara konsisten pada nama branch, pesan commit, dan judul Pull Request agar terhubung otomatis antara Jira, GitHub, dan Discord.
- **Bukti Artefak:** Seluruh Work Package (`PPL 01` s.d. `PPL 42`) telah terjadwal di Jira lengkap dengan pembagian Sprint 1–4, assignee, tenggat waktu, dan estimasi *Story Point*.

### 4.2 Daily / Weekly Stand-up
- **Waktu Pelaksanaan:** Rutin tertulis di kanal Discord `#diskusi`
- **Format Pertanyaan:**
  1. *Apa yang sudah dikerjakan?*
  2. *Apa yang akan dikerjakan selanjutnya?*
  3. *Kendala/blocker apa yang dihadapi?*
- **Bukti Artefak:** Rekaman chat stand-up tertulis di kanal `#diskusi`.
- **Rangkuman Kendala Sprint 1:**
  - Proteksi middleware pada route `/owner/produk` sempat belum terpasang sehingga dapat diakses oleh role pelanggan atau guest (ditemukan saat QA Testing BUG-05 & BUG-06).
  - Sesi keranjang dan halaman profil sempat membaca data statis/seeder sebelum diperbaiki menggunakan `auth()->user()`.
  - Validasi form tambah produk memerlukan penguatan validasi sisi server (*server-side validation*) yang dilanjutkan pada Sprint 2 (`PPL-24` & `PPL-25`).

### 4.3 Sprint Review / Demo (Akhir Sprint)
- **Waktu Pelaksanaan:** 4 November 2026 (±30 menit)
- **Peserta:** Scrum Master, Product Owner, dan Tim Pengembang
- **Yang Dilakukan:**
  1. Mendemokan skema database 10 tabel dan hasil seeder 4 varian produk Kopi Tiga Titik (`Titik Hitam`, `Titik Temu`, `Titik Manja`, `Titik Jenuh`).
  2. Mendemokan alur Registrasi, Login, Logout, serta pembatasan hak akses (*Role Middleware*).
  3. Mendemokan halaman Kelola Produk (CRUD produk, update stok, unggah foto) oleh Owner.
  4. Mendemokan halaman Katalog Produk pelanggan beserta kondisi tombol nonaktif saat stok habis.
  5. Memaparkan laporan hasil QA Testing Sprint 1 (`PPL-16`) di mana 6 temuan bug telah diperbaiki dan diverifikasi ulang (*re-test*).
- **Catatan Hasil Review:**
  - Seluruh **9 User Story (12 Work Package / 13 kartu Jira)** memenuhi *Definition of Done (DoD)* dan diterima sebagai **Done**.

### 4.4 Sprint Retrospektif (Setelah Review)
- **Waktu Pelaksanaan:** 4 November 2026 (±15 menit)
- **Hasil Retrospektif:**
  - **What Went Well (Yang Berjalan Baik):**
    - Integrasi otomatis GitHub ↔ Jira ↔ Discord (`#github-notif` dan `#board-notif`) berjalan mulus sejak `PPL-1`.
    - Pengujian QA (`PPL-16`) berhasil menangkap celah keamanan kritis pada otorisasi route Owner (BUG-05 & BUG-06) dan berhasil di-fix sebelum Sprint 1 ditutup.
  - **What Needs Improvement (Yang Perlu Ditingkatkan):**
    - Validasi input tidak boleh hanya mengandalkan atribut HTML5 di browser (`min`/`required`), melainkan harus diperkuat di backend agar tahan terhadap manipulasi *Inspect Element* (temuan `TC-INT-04`).
    - Review Pull Request perlu dipercepat agar tidak menumpuk di hari terakhir pengembangan.
  - **Tindakan Perbaikan (Action Items untuk Sprint 2):**
    1. Memperkuat validasi server-side menggunakan Laravel Form Request dan Global Error Handling pada awal Sprint 2 (`PPL 20 / PPL-24` & `PPL 21 / PPL-25`).
    2. Menetapkan batas waktu review PR maksimal 1x24 jam sebelum merge ke `main`.

---

## 5. Rekap Rencana vs Realisasi Per Sprint (Format Tabel 7 Panduan PPL)

> **Catatan:** Tabel ini diisi secara bertahap setiap kali sebuah sprint selesai dilaksanakan.

| Sprint | Tujuan Sprint | User Story Direncanakan | User Story Selesai (Done) | Tindakan dari Retrospektif |
|---|---|:---:|:---:|---|
| **Sprint 1**<br>(28 Okt – 4 Nov 2026) | Membangun fondasi sistem yang mencakup database, autentikasi dan hak akses, antarmuka responsif, pengelolaan produk, serta katalog produk. | **9 User Story**<br>(12 Work Package `PPL 01`–`PPL 12` / 13 Kartu Jira — 48 Story Points) | **9 User Story (100%)**<br>(12 Work Package / 13 Kartu Jira Done — 48 Story Points) | 1. Memperkuat validasi input sisi server (*server-side validation*) dan penanganan error global di Sprint 2 (`PPL 20` & `PPL 21`) sebagai tindak lanjut temuan uji bypass `TC-INT-04`.<br>2. Menetapkan batas waktu *code review* Pull Request maksimal 1x24 jam.<br>3. Memastikan setiap route baru langsung dilengkapi proteksi middleware role. |
| **Sprint 2** | - | - | - | - |
| **Sprint 3** | - | - | - | - |
| **Sprint 4** | - | - | - | - |

---

## 6. Bukti Integrasi Tiga Tools Wajib (Sesuai Subbab 4.2 Panduan PPL)

1. **I-1: GitHub → Discord (`#github-notif`)**
   - *Status:* Aktif & Terbukti (dimulai sejak kartu `PPL-1: Test integrasi GitHub`).
   - *Hasil:* Setiap commit, pembuatan PR, review PR, dan merge PR otomatis memunculkan pesan bot di kanal `#github-notif`.
2. **I-2: Jira ↔ GitHub**
   - *Status:* Aktif & Terbukti.
   - *Hasil:* Penulisan kode `PPL-5` s.d. `PPL-16` pada nama branch (misal `feature/PPL-12-frontend-kelola-produk`) dan commit otomatis menautkan status branch & PR pada panel *Development* di kartu Jira.
3. **I-3: Jira → Discord (`#board-notif`)**
   - *Status:* Aktif & Terbukti.
   - *Hasil:* Pembuatan kartu baru, perubahan status (*To Do* → *In Progress* → *Done*), dan penugasan otomatis terkirim ke kanal `#board-notif`.

---

## 7. Checklist Screenshot untuk Lampiran D Laporan IEEE

Untuk kelengkapan **Appendix D (Dokumentasi Manajemen Proyek)** pada Laporan Requirement IEEE, siapkan screenshot berikut:
1. **Screenshot Jira Backlog & Sprint 1:** Menampilkan daftar `PPL-1` dan `PPL-5` s.d. `PPL-16` dengan status **Done**.
2. **Screenshot Discord `#github-notif` (Bukti I-1):** Menampilkan notifikasi otomatis commit/PR dari GitHub.
3. **Screenshot Kartu Jira Panel Development (Bukti I-2):** Minimal 3 kartu berstatus *Done* (misal `PPL-11`, `PPL-12`, `PPL-13`) yang memperlihatkan tautan PR GitHub berstatus *Merged*.
4. **Screenshot Discord `#board-notif` (Bukti I-3):** Menampilkan notifikasi otomatis perpindahan kartu ke kolom *Done*.
5. **Screenshot Discord `#diskusi`:** Menampilkan pesan stand-up mingguan anggota tim.

---
*Laporan ini disusun oleh Scrum Master & QA Tester (M. Arif) berdasarkan data aktual Jira Sprint 1 dan Product Backlog Kopi Tiga Titik.*
