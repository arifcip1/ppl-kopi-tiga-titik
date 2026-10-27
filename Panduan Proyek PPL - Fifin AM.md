# Panduan Spesifikasi Tugas Proyek Mata Kuliah Proyek Perangkat Lunak (PPL) 

Semester Ganjil 2026/2027 

Tema : E-Commerce untuk UMKM dengan Integrasi API Pembayaran dan Pengiriman Pendekatan : Agile (Scrum sederhana) Durasi : Pekan 9 – Pekan 15, Pekan 16 Presentasi dan Penilaian Proyek 

## 1. Latar Belakang dan Keterkaitan dengan Mata Kuliah RPL 

Mata kuliah Proyek Perangkat Lunak (PPL) merupakan kelanjutan dari mata kuliah Rekayasa Perangkat Lunak (RPL). Pada RPL, mahasiswa telah mempelajari siklus pengembangan perangkat lunak (SDLC) secara utuh, mulai dari analisis kebutuhan, desain, implementasi, hingga pengujian. Pada PPL, fokus pembelajaran bergeser ke bagaimana sebuah tim mengelola proyek perangkat lunak: merencanakan dan membagi pekerjaan dalam sprint, memantau progres, serta berkolaborasi menggunakan tools manajemen proyek yang terintegrasi dengan repositori kode (storage sharing). 

Aplikasi e-commerce untuk UMKM yang dibangun berfungsi sebagai wahana untuk menerapkan manajemen proyek. Penilaian utama tidak terletak pada kecanggihan aplikasi, melainkan pada cara tim merencanakan, menjalankan, dan memantau pekerjaan serta jejak kolaborasinya. 

Tabel 1. Perbedaan fokus proyek RPL dan PPL 

|Aspek|RPL (sebelumnya)|PPL (sekarang)|
|---|---|---|
|Fokus utama|Rekayasa produk: SRS, desain UML,<br>database, implementasi, pengujian|Manajemen proyek Agile: backlog, sprint,<br>pemantauan progress (sprint backlog),<br>kolaborasi terintegrasi|
|Tema|Penjualan online (online store)<br>produk|E-commerce UMKM + API pembayaran<br>+ API pengiriman + 1 komponen<br>komputasional|
|Tools yang<br>ditekankan|Tools desain & pemrograman|Board Agile (Trello, monday.com, Jira,<br>Github projects, dll.), tools komunikasi<br>(Slack, Discord, dll.), dan GitHub yang<br>saling terhubung|
|Bukti kerja|Dokumen dan aplikasi|Dokumen, aplikasi, dan jejak digital<br>kolaborasi (riwayat board, commit, pull<br>request, notifikasi kanal, catatan sprint)|
|Luaran akhir|SRS, desain, implementasi, PPT,<br>video promosi & demo|Laporan Requirement standar IEEE<br>(lengkap dengan tautan) dan video<br>iklan/branding aplikasi|



Panduan Proyek PPL — Halaman 1 

### 1.1 Kesesuaian dengan Praktik di Industri 

Tata cara kerja proyek ini disusun menyerupai praktik tim pengembang perangkat lunak di industri yang menggunakan pendekatan Agile, dengan skala yang disederhanakan agar dapat diselesaikan dalam 7 minggu kerja: 

Tabel 2. Padanan praktik proyek PPL dengan praktik industri 

|Praktik dalam Proyek PPL|Padanan di Industri|
|---|---|
|Product backlog berisi user story, dikerjakan<br>dalam sprint|Scrum: product backlog, sprint backlog, dan setiap<br>sprint berdurasi 1–2 minggu|
|Sprint planning, stand-up, sprint review, dan<br>retrospektif|Seremoni (_ceremony_) Scrum yang dijalankan tim secara<br>rutin|
|Kode tugas (PPL-12) di kartu, branch,<br>commit, dan pull request|Konvensi_ticket ID_(misalnya JIRA-123 atau PPL-12)<br>agar setiap perubahan kode dapat dilacak ke tugasnya|
|Pull request wajib di-review anggota lain<br>sebelum merge|_Code review_dan perlindungan branch utama|
|Notifikasi otomatis GitHub dan board ke<br>Slack/Discord|Kanal notifikasi tim agar progres terlihat tanpa rapat<br>tambahan|
|presentasi UAS bersama dosen|Sprint review/demo kepada stakeholder|



Catatan: Konsep penjadwalan tradisional (critical path silahkan dituliskan nantinya). 

## 2. Tujuan Proyek 

Setelah menyelesaikan proyek ini, mahasiswa diharapkan mampu: 

1. Menyusun product backlog dalam bentuk user story dan merencanakan pekerjaan dalam beberapa sprint. 

2. Menjalankan seremoni Agile sederhana (sprint planning, stand-up, sprint review, retrospektif) dan memantau progres tim. 

3. Mengelola tugas tim menggunakan board Agile (Trello, monday.com, Jira, dll.) dan tools komunikasi (Slack, Discord, atau Microsoft Teams). 

4. Menggunakan GitHub sebagai storage sharing dengan alur kerja kolaboratif (branch, pull request, review). 

5. Menghubungkan board, tools komunikasi, dan GitHub sehingga progres pekerjaan terlihat otomatis. 

6. Mendokumentasikan kebutuhan perangkat lunak sesuai standar IEEE dan mempublikasikan produk melalui video iklan/branding. 

## 3. Ketentuan Umum 

- 3.1 Kelompok dan Peran 

   - Setiap kelompok terdiri atas 6–7 orang mahasiswa. 

Panduan Proyek PPL — Halaman 2 

- Setiap anggota memegang minimal satu peran. Peran Product Owner dan Scrum Master wajib ada; satu orang boleh merangkap peran teknis, tetapi Product Owner dan Scrum Master tidak dirangkap oleh orang yang sama. 

- Pembagian peran dicantumkan di laporan dan terlihat pada penugasan kartu di board. 

Tabel 3. Contoh pembagian peran dalam kelompok 

|Peran|Tanggung Jawab Utama|
|---|---|
|Product Owner (merangkap<br>System Analyst)|Penghubung dengan UMKM mitra, menyusun dan memprioritaskan<br>product backlog, menyusun Laporan Requirement IEEE|
|Scrum Master (Project<br>Manager)|Memfasilitasi sprint planning, stand-up, review, dan retrospektif;<br>menjaga board tetap mutakhir; memantau hambatan tim|
|UI/UX Designer|Rancangan tampilan dan konsistensi antarmuka|
|Backend Developer|Database, logika bisnis, integrasi API pembayaran dan pengiriman|
|Frontend Developer|Implementasi antarmuka dan konsumsi API backend|
|QA / Tester|Menguji fitur terhadap Definition of Done, mencatat bug sebagai<br>kartu, memverifikasi perbaikan|
|Dokumentasi & Branding|Mengelola dokumen bersama, catatan sprint, dan produksi video<br>iklan/branding|



### 3.2 Tema Proyek 

Tema proyek adalah aplikasi e-commerce untuk UMKM. yang ditransaksikan adalah produk (barang), bukan jasa. Kelompok bermitra dengan UMKM nyata 

### 3.3 Fitur Minimal Aplikasi 

- Pengelolaan produk (tambah, ubah, hapus produk beserta harga dan stok) oleh penjual/admin. 

- Katalog produk, keranjang belanja, dan checkout oleh pembeli. 

- API pembayaran dalam mode sandbox/testing (misalnya Midtrans, Xendit, atau Duitku). Cukup sampai pembeli dapat membayar dan status pesanan berubah menjadi "dibayar". 

- API pengiriman dalam mode sandbox/testing (misalnya RajaOngkir/Komerce, Biteship, atau KiriminAja). Cukup untuk menghitung ongkos kirim saat checkout; pelacakan resi bersifat opsional. 

- Daftar pesanan beserta statusnya. 

Catatan: Kunci API (API key/secret) tidak boleh di-commit ke repositori. Simpan di file .env yang dimasukkan ke .gitignore. 

### 3.4 Komponen Komputasional Sederhana 

Aplikasi wajib memuat minimal satu komponen komputasional sederhana yang membantu keputusan UMKM. Pilih salah satu dari tabel berikut (atau metode lain yang setara dengan persetujuan dosen). Rumus/aturan dan satu contoh perhitungannya dijelaskan singkat di laporan. 

Panduan Proyek PPL — Halaman 3 

Tabel 4. Pilihan komponen komputasional (pilih minimal satu) 

|Metode|Contoh Penerapan|
|---|---|
|Moving Average|Memperkirakan penjualan/kebutuhan stok minggu depan dari rata-rata<br>penjualan 3 minggu terakhir|
|Rule-Based System|Peringatan restock (JIKA stok < 5 MAKA tampilkan "segera restock")<br>atau diskon otomatis (JIKA total belanja ≥ Rp200.000 MAKA diskon<br>10%)|
|Scoring sederhana|Menampilkan 5 produk terlaris berdasarkan jumlah terjual|



Contoh _Simple Moving Average_ 3 periode: jika penjualan 3 minggu terakhir 20, 25, dan 30 unit, maka perkiraan minggu depan = (20 + 25 + 30) / 3 = 25 unit. 

## 4. Manajemen Proyek Agile dan Kolaborasi Terintegrasi 

Bagian ini merupakan inti mata kuliah PPL. Setiap kelompok menggunakan tiga jenis tools berikut dan menghubungkannya satu sama lain. 

Tabel 5. Jenis tools manajemen proyek 

|Jenis Tools|Fungsi|Pilihan Tools|
|---|---|---|
|Board Agile|Product backlog, sprint backlog,<br>pembagian dan pemantauan tugas|Pilih satu: Trello, monday.com,<br>Jira, ClickUp, atau GitHub Projects<br>atau tools yang lain|
|Komunikasi Tim|Diskusi, stand-up, notifikasi otomatis|Pilih satu: Slack, Discord, atau<br>Microsoft Teams|
|Storage Sharing|Kode sumber, pull request; dokumen non-<br>kode|GitHub (disarankan) atau GitLab;<br>Google Drive/OneDrive untuk<br>dokumen|



- 4.1 Kerangka Kerja Agile (Scrum Sederhana) 

### _4.1.1 Product Backlog dan User Story_ 

- Product Owner menyusun product backlog di board berisi seluruh fitur dalam bentuk user story, diurutkan berdasarkan prioritas. 

- Format user story: Sebagai [peran], saya ingin [kebutuhan], agar [manfaat]. Contoh: _PPL-07 — Sebagai pembeli, saya ingin melihat ongkos kirim saat checkout, agar saya tahu total biaya sebelum membayar._ 

- Setiap user story diberi kode unik (PPL-01, PPL-02, dst.) dan kriteria penerimaan singkat (2–3 butir). 

- Tim menyepakati Definition of Done (DoD) sederhana, misalnya: kode sudah di-merge melalui PR yang di-review, fitur sudah diuji QA, dan tidak ada error saat dijalankan. 

### _4.1.2 Sprint dan Seremoni_ 

Proyek dibagi menjadi 4 sprint (lihat jadwal pada Bagian 5). Pada setiap sprint, tim menjalankan kegiatan berikut: 

Panduan Proyek PPL — Halaman 4 

Tabel 6. Seremoni dalam setiap sprint 

|Kegiatan|Waktu|Yang Dilakukan|Bukti|
|---|---|---|---|
|Sprint<br>Planning|Awal sprint (±30<br>menit)|Menentukan tujuan sprint, memilih<br>user story dari backlog, membagi<br>tugas ke anggota|Kartu berlabel sprint &<br>ter-assign; catatan tujuan<br>sprint|
|Stand-up|1 kali per minggu<br>(tertulis di kanal)|Setiap anggota menulis: sudah<br>mengerjakan apa, akan mengerjakan<br>apa, kendala apa|Pesan stand-up di kanal<br>#diskusi|
|Sprint Review|Akhir sprint (±30<br>menit)|Mendemokan hasil sprint kepada<br>tim/Product Owner (atau UMKM<br>mitra)|Catatan hasil review|
|Retrospektif|Setelah review<br>(±15 menit)|Membahas apa yang berjalan baik,<br>apa yang perlu diperbaiki, dan 1–2<br>tindakan perbaikan|Catatan retrospektif|



Catatan planning, review, dan retrospektif cukup ditulis singkat (maksimal satu halaman per sprint) dan disimpan di storage sharing (Wiki/README GitHub atau Google Drive). 

### _4.1.3 Pemantauan Progres/ Dialy scrum_ 

Di akhir setiap sprint, Scrum Master mencatat perbandingan rencana dan realisasi sprint. Rekap ini dilampirkan di laporan dan menjadi dasar diskusi retrospektif. 

Tabel 7. Format rekap rencana vs realisasi per sprint 

|Sprint|Tujuan Sprint|User Story|User Story|Tindakan dari|
|---|---|---|---|---|
|||Direncanakan|Selesai (Done)|Retrospektif|
|Sprint 1|Contoh: katalog &<br>keranjang berjalan|6|5|Contoh: PR di-review<br>maksimal 1 hari|
|Sprint 2|…|…|…|…|
|Sprint 3|…|…|…|…|
|Sprint 4|…|…|…|…|



### 4.2 Integrasi Tools (Wajib) 

### _4.2.1 Konsep Integrasi_ 

Yang dimaksud integrasi adalah kondisi ketika board, tools komunikasi, dan GitHub saling terhubung sehingga informasi mengalir otomatis, tanpa harus dilaporkan ulang secara manual. Menggunakan Trello, Slack, dan GitHub secara bersamaan tetapi terpisah belum dianggap integrasi. 

|Board Agile|⇄|GitHub|⇄|Slack / Discord|
|---|---|---|---|---|
|(Trello / monday.com / Jira)||(kode, pull request)||(notifikasi & diskusi)|



Gambar 1. Arus informasi antar-tools dalam proyek PPL 

1. User story di board dikerjakan melalui branch, commit, dan pull request di GitHub yang tertaut ke kartunya. 

Panduan Proyek PPL — Halaman 5 

2. Kejadian penting (kartu dipindah, PR dibuat, PR di-merge) dikirim otomatis ke kanal Slack/Discord. 

3. Seluruh anggota dan dosen dapat memantau progres cukup dari kanal dan board, tanpa perlu bertanya satu per satu. 

Kunci integrasi adalah kode tugas yang sama. Setiap user story memiliki satu kode (misalnya PPL-12) yang dipakai di judul kartu, nama branch, pesan commit, dan judul pull request. 

### _4.2.2 Struktur Kanal Komunikasi_ 

Tabel 8. Struktur kanal minimal 

|Kanal|Fungsi|Diisi oleh|
|---|---|---|
|#diskusi|Diskusi tim, pengumuman, dan stand-up mingguan|Anggota|
|#github-notif|Notifikasi otomatis dari GitHub (commit, PR, review)|Bot (otomatis)|
|#board-notif|Notifikasi otomatis dari board (kartu baru, pindah status)|Bot (otomatis)|



### _4.2.3 Ringkasan Integrasi Wajib_ 

Tabel 9. Ringkasan tiga integrasi wajib 

|Kode|Arah Integrasi|Pemicu|Hasil yang Terjadi|
|---|---|---|---|
|I-1|GitHub →<br>Slack/Discord|Commit, PR dibuat, PR di-<br>review/di-merge|Pesan otomatis masuk ke #github-<br>notif|
|I-2|Board ↔ GitHub|PR/commit dilampirkan ke kartu,<br>atau memuat kode kartu|Kartu menampilkan PR/commit<br>terkait beserta statusnya|
|I-3|Board →<br>Slack/Discord|Kartu dibuat, dipindah status,<br>atau ditugaskan|Pesan otomatis masuk ke #board-<br>notif|



Ketiga integrasi menggunakan fitur bawaan aplikasi dan umumnya dapat dipasang dalam kurang dari satu jam. Integrasi wajib sudah berfungsi pada Sprint 0 (minggu ke-6) agar jejak kolaborasi terekam sejak awal. 

### _4.2.4 Cara Memasang Setiap Integrasi_ 

I-1. GitHub → Slack/Discord (notifikasi aktivitas kode) 

- Slack: pasang aplikasi _GitHub_ dari Slack Marketplace, lalu di kanal #github-notif ketik /github subscribe nama-akun/nama-repo. 

- Discord: buat webhook pada kanal #github-notif (Edit Channel → Integrations → Webhooks), salin URL-nya, lalu di repositori GitHub buka Settings → Webhooks → Add webhook, tempel URL dengan tambahan /github di akhir dan pilih content type _application/json_ . 

- Microsoft Teams: pasang aplikasi _GitHub_ untuk Teams, lalu ketik @GitHub subscribe nama-akun/nama-repo di kanal. 

Berhasil jika: commit, PR baru, dan merge PR muncul otomatis di #github-notif. 

Panduan Proyek PPL — Halaman 6 

- I-2. Board ↔ GitHub (menautkan tugas dengan kode) 

   - Jira (paling otomatis): pasang _GitHub for Jira_ , lalu cukup tulis kode issue (misalnya PPL-15) di nama branch, pesan commit, atau judul PR; tautan muncul otomatis di panel _Development_ pada issue. Buat _project key_ Jira = PPL agar kode issue otomatis menjadi PPL-1, PPL-2, dst. 

   - GitHub Projects (otomatis): issue dan PR otomatis menjadi item board karena berada di platform yang sama; tulis Closes #15 di deskripsi PR agar issue tertutup otomatis saat dimerge. 

   - Trello (semi-otomatis): aktifkan Power-Up _GitHub_ pada board. Setelah PR dibuat, buka kartu lalu klik tombol GitHub untuk melampirkan PR tersebut (satu kali klik). Setelah dilampirkan, status PR (open/merged) diperbarui otomatis di kartu. Kode PPL-xx ditulis manual pada judul kartu. 

   - monday.com: Integrations Center → GitHub, lalu aktifkan _recipe_ yang tersedia (misalnya membuat item dari issue GitHub atau mengubah status item saat PR di-merge). Bentuk integrasinya berupa pembaruan otomatis, bukan tampilan PR di dalam item. 

Berhasil jika: minimal 5 kartu berstatus Done menampilkan PR/commit terkait. 

Saran pemilihan board: kelompok yang menginginkan integrasi board ↔ GitHub paling otomatis disarankan memilih Jira (paket gratis) atau GitHub Projects. Trello dan monday.com tetap boleh digunakan dengan cara penautan seperti dijelaskan di atas. 

- I-3. Board → Slack/Discord (notifikasi progres tugas) 

   - Trello → Slack: aktifkan Power-Up _Slack_ pada board, lalu tambahkan alert ke kanal #board-notif untuk kartu dibuat dan kartu dipindah. 

   - monday.com → Slack: Integrations Center → Slack → pilih recipe seperti "ketika status berubah, kirim pesan ke kanal". 

   - Jira → Slack: pasang aplikasi _Jira Cloud_ untuk Slack dan hubungkan proyek ke kanal #board-notif. 

   - Tools → Discord: gunakan webhook Discord melalui fitur otomasi bawaan tools, atau Zapier/Make (paket gratis) bila integrasi langsung tidak tersedia. 

Berhasil jika: perpindahan kartu ke kolom Done muncul otomatis di #board-notif. 

Nama menu aplikasi dapat berubah mengikuti pembaruan aplikasi. Jika tampilan berbeda, ikuti dokumentasi resmi aplikasi; yang dinilai adalah integrasinya berfungsi dan terbukti, bukan langkah pemasangannya. 

### _4.2.5 Demonstrasi Integrasi_ 

Screenshot saja belum cukup membuktikan bahwa integrasi benar-benar dipakai. Oleh karena itu, pada Monev UTS dan presentasi UAS kelompok mempraktikkan secara langsung satu siklus kerja kecil (±5 menit) di depan dosen. Perubahan kode yang didemokan boleh sangat sederhana (misalnya mengubah satu teks tombol); yang dinilai adalah alur dan integrasinya, bukan isi kodenya. 

Panduan Proyek PPL — Halaman 7 

Persiapan: presenter membagikan layar dengan tiga jendela/tab terbuka: board, repositori GitHub, dan Slack/Discord. Integrasi harus sudah terpasang sebelum presentasi. 

Istilah: _kartu_ = satu tugas di board (card di Trello, issue di Jira, item di monday.com); _branch_ = cabang kode terpisah untuk mengerjakan satu tugas; _commit_ dan _push_ = menyimpan perubahan kode lalu mengirimnya ke GitHub; _pull request (PR)_ = permintaan agar perubahan digabung ke kode utama; _review_ dan _merge_ = anggota lain memeriksa, menyetujui, lalu menggabungkan perubahan. 

Tabel 10. Contoh skenario demonstrasi integrasi 

|No|Pelaku|Tindakan|Yang Harus Terlihat|Integrasi|
|---|---|---|---|---|
|1|Anggota A|Membuat kartu PPL-15 Ubah<br>teks tombol checkout di board<br>dan menugaskannya ke dirinya|Pesan otomatis di #board-notif:<br>kartu PPL-15 dibuat/ditugaskan|I-3|
|2|Anggota A|Membuat branch<br>feature/PPL-15-teks-checkout,<br>mengubah teks tombol, commit<br>dengan pesan [PPL-15] ubah<br>teks tombol checkout, lalu push|Pesan otomatis di #github-notif:<br>ada commit/push baru dari<br>Anggota A|I-1|
|3|Anggota A|Membuat pull request di GitHub|PR tampil pada kartu PPL-15:<br>otomatis (Jira/GitHub Projects)<br>atau setelah dilampirkan melalui<br>Power-Up GitHub (Trello);<br>notifikasi PR baru di #github-<br>notif|I-2, I-1|
|4|Anggota B|Me-review, menyetujui, lalu<br>merge PR|Pesan otomatis review dan merge<br>di #github-notif; status PR pada<br>kartu berubah menjadi merged|I-1, I-2|
|5|Anggota A|Memindahkan kartu PPL-15 ke<br>kolom Done|Pesan otomatis di #board-notif:<br>kartu PPL-15 dipindah ke Done|I-3|



Dengan siklus ini, dapat melihat bahwa satu tugas dapat dilacak dari kartu hingga kodenya melalui kode yang sama (PPL-15), pekerjaan diperiksa anggota lain sebelum digabung, dan seluruh anggota mengetahui progres tanpa perlu bertanya. 

4.3 Alur Kerja dan Aturan Kolaborasi 

- Kolom board minimal: Product Backlog → To Do (Sprint) → In Progress → Review → Done. Kartu sprint diberi label sprint (Sprint 1, Sprint 2, dst.). 

- Nama branch dan pesan commit memuat kode kartu, contoh branch feature/PPL-12checkout dan commit [PPL-12] tambah halaman checkout. 

- Perubahan digabung ke branch main melalui pull request yang di-review minimal satu anggota lain. 

- Setiap anggota memiliki kartu yang dikerjakan dan kontribusi di GitHub (commit, PR, atau review). Anggota non-programmer dapat berkontribusi melalui dokumen di 

Panduan Proyek PPL — Halaman 8 

repositori (misalnya README, catatan sprint dalam format Markdown) atau melalui review PR. 

- Repositori memiliki README berisi deskripsi aplikasi, cara menjalankan, dan daftar anggota beserta peran. 

- Dosen diundang ke board, workspace komunikasi, dan repositori (atau repositori dibuat publik) paling lambat akhir minggu ke-6. 

Penting: Jejak kolaborasi dinilai dari riwayat selama proyek. Aktivitas di board dan GitHub diharapkan tersebar minimal pada 5 dari 7 minggu kerja, bukan menumpuk di minggu terakhir. 

## 5. Jadwal Proyek 

Proyek berlangsung selama pekan 9 – pekan 15 

Sesuaikan jadwal proyek sesuai dengan pekan yang telah ditetapkan diatas. Pastikan seluruh backlog dan user story telah di di jadwalkan dan dapat terlihat pada sprint planning dan sprint backlog. 

## 6. Luaran Proyek yang Dikumpulkan 

Luaran akhir terdiri atas dua komponen yang dikumpulkan paling lambat akhir minggu ke-15: 

6.1 Laporan Requirement Standar IEEE (Lengkap dengan Tautan) 

Laporan mengikuti template Software Requirements Specification IEEE yang telah diberikan, ditambah dua lampiran. Subbagian template yang tidak relevan dengan aplikasi cukup diisi "Tidak berlaku" disertai alasan singkat. 

Tabel 11. Struktur Laporan Requirement standar IEEE 

|Bagian|Isi yang Diharapkan|
|---|---|
|Halaman judul & Revision<br>History|Nama aplikasi, kelompok & anggota, versi dokumen|
|1. Introduction|Purpose, Document Conventions, Intended Audience, Product<br>Scope, References|
|2. Overall Description|Product Perspective, Product Functions, User Classes (pembeli,<br>penjual/admin), Operating Environment, Constraints, Assumptions<br>& Dependencies|
|3. External Interface<br>Requirements|User Interfaces dan Software Interfaces (API pembayaran & API<br>pengiriman)|
|4. System Features|Setiap fitur: deskripsi & prioritas, alur stimulus/respons, functional<br>requirements (REQ-1, REQ-2, …) yang dapat dirujuk ke kode user<br>story, termasuk komponen komputasional|
|5. Other Nonfunctional<br>Requirements|Performance, Security (keamanan transaksi & API key), Quality<br>Attributes, Business Rules|
|6. Other Requirements &<br>Appendix A–C|Sesuai template (Glossary, Analysis Models, TBD List)|



Panduan Proyek PPL — Halaman 9 

|Bagian|Isi yang Diharapkan|
|---|---|
|Appendix D: Dokumentasi|Peran anggota; screenshot product backlog; ringkasan tiap sprint|
|Manajemen Proyek|(tujuan, hasil review, tindakan retrospektif); rekap rencana vs<br>realisasi (Tabel 8); screenshot bukti integrasi I-1 s.d. I-3; ringkasan<br>kontribusi tiap anggota|
|Appendix E: Daftar Tautan|Tabel tautan seluruh artefak proyek (Tabel 14)|



Bukti integrasi pada Appendix D cukup berupa screenshot: (1) kanal #github-notif berisi notifikasi commit/PR/merge, (2) minimal 3 kartu yang menampilkan PR terkait, dan (3) kanal #board-notif berisi notifikasi perpindahan kartu. 

Seluruh tautan berikut wajib dapat diakses oleh dosen (publik atau dibagikan ke email dosen): 

Tabel 12. Format daftar tautan pada Laporan Requirement 

|No|Artefak|Keterangan|Tautan|
|---|---|---|---|
|1|Repositori kode|GitHub/GitLab berisi README dan<br>riwayat commit/PR|https://github.com/…|
|2|Board Agile|Trello/monday.com/Jira (dosen sebagai<br>member/observer)|https://…|
|3|Workspace<br>komunikasi|Slack/Discord/Teams (undangan dosen)|https://…|
|4|Catatan sprint|Catatan planning, review, dan retrospektif<br>(Wiki GitHub/Google Drive)|https://…|
|5|Aplikasi|URL aplikasi (jika di-deploy) atau video<br>demo singkat|https://…|
|6|Video iklan/branding|Tautan YouTube (Public/Unlisted)|https://youtu.be/…|



Format laporan: file .docx dan .pdf, Times New Roman 12, spasi 1,5, kertas A4. Semua gambar dan tabel wajib memiliki nomor dan caption serta dirujuk di dalam teks. 

### 6.2 Video Iklan / Branding Aplikasi 

- Format landscape (16:9), durasi 1–2 menit, diunggah ke YouTube (Public/Unlisted). 

- Berisi nama/logo aplikasi, masalah UMKM yang diselesaikan, tampilan aplikasi yang sudah dibuat, fitur unggulan (pembayaran online, cek ongkir, fitur komputasional), dan ajakan menggunakan aplikasi. 

- Menggunakan voice-over dan/atau teks; musik latar bebas hak cipta. 

- Akhir video mencantumkan nama kelompok, anggota, mata kuliah, dan institusi. 

## 7. Ketentuan dan Penilaian 

### 7.1 Ketentuan 

- Setiap ketidaksesuaian terhadap spesifikasi tugas akan mendapatkan penalti. 

- Tautan yang tidak dapat diakses saat penilaian dianggap tidak dikumpulkan. 

Panduan Proyek PPL — Halaman 10 

- Link pengumpulan akan disediakan melalui Google Drive. 

- Seluruh anggota wajib hadir pada Monev UTS dan presentasi UAS. Dosen dapat mengajukan pertanyaan kepada anggota mana pun, sehingga setiap anggota perlu memahami keseluruhan proyek. 

### 7.2 Komposisi Nilai Proyek 

### Ringkasan Ekspektasi Minimal 

Tabel berikut merangkum batas minimal yang harus dipenuhi. Kelompok yang memenuhi seluruh kolom "Wajib" dengan baik sudah dapat memperoleh nilai baik; kolom "Nilai Tambah" bersifat opsional. 

Tabel 13. Ringkasan ekspektasi minimal proyek 

|Komponen|Wajib (Minimal)|Nilai Tambah (Opsional)|
|---|---|---|
|Manajemen<br>Agile|Product backlog (user story), 4 sprint sesuai<br>jadwal, catatan planning–review–retrospektif tiap<br>sprint, rekap rencana vs realisasi|Estimasi story point, burndown<br>chart|
|Integrasi tools|3 integrasi otomatis (I-1 s.d. I-3) dan kode tugas<br>konsisten|GitHub Milestones per sprint,<br>GitHub Actions, otomasi board|
|Kolaborasi|Setiap anggota punya kartu & kontribusi di<br>GitHub; stand-up 1 kali/minggu|Wiki, template pull request|
|Aplikasi|Fitur minimal 3.3 + API pembayaran & ongkir<br>(sandbox) + 1 komponen komputasional|Pelacakan resi, dashboard<br>penjualan, deploy online|
|Luaran|Laporan Requirement IEEE + video iklan 1–2<br>menit|—|



### 7.3 Penilaian UAS 

Format: presentasi 20 menit yang memuat ringkasan proyek, rekap sprint, demo aplikasi, dan demo integrasi, dilanjutkan tanya jawab 10 menit. Seluruh luaran (Bagian 6) sudah dikumpulkan paling lambat akhir minggu ke-14. 

Tabel 14. Komponen penilaian UAS 

|No|Komponen|Yang Harus Disiapkan dan Ditunjukkan|Bobot|
|---|---|---|---|
|1|Pengelolaan Sprint|Sprint dijalankan sesuai jadwal; catatan planning, review,<br>dan retrospektif setiap sprint; rekap rencana vs realisasi<br>(Tabel 8); tindak lanjut hasil retrospektif dan masukan<br>Monev UTS terlihat|20%|
|2|Integrasi Tools|Integrasi I-1 s.d. I-3 tetap berfungsi sampai akhir proyek;<br>demo siklus kerja berhasil; kode tugas konsisten di board,<br>branch, commit, dan PR|15%|
|3|Konsistensi &<br>Kontribusi Kolaborasi|Aktivitas board dan GitHub tersebar minimal 5 dari 7<br>minggu kerja; PR di-review; setiap anggota memiliki kartu<br>dan kontribusi yang terlihat|15%|



Panduan Proyek PPL — Halaman 11 

|No|Komponen|Yang Harus Disiapkan dan Ditunjukkan|Bobot|
|---|---|---|---|
|4|Laporan Requirement<br>Standar IEEE|Sesuai template IEEE; requirement jelas dan dapat diuji;<br>Appendix D dan E lengkap; semua tautan dapat diakses|20%|
|5|Fungsionalitas<br>Aplikasi|Fitur minimal berjalan; API pembayaran dan ongkos kirim<br>(sandbox) berfungsi; komponen komputasional berjalan<br>dengan benar|15%|
|6|Video Iklan/Branding|Sesuai format; pesan, masalah UMKM, dan fitur unggulan<br>tersampaikan jelas|10%|
|7|Presentasi & Kerja<br>Sama Tim|Penyampaian jelas, setiap anggota mampu menjelaskan<br>bagian yang dikerjakan|5%|
||Total||100%|



### 7.5 Skala Penilaian 

Setiap komponen pada Tabel 14 dinilai menggunakan skala berikut: 

Tabel 15. Skala penilaian setiap komponen 

|Rentang Nilai|Kategori|Deskripsi|
|---|---|---|
|85–100|Sangat Baik|Seluruh hal yang diminta ada, berfungsi, konsisten, dan didukung<br>bukti yang jelas|
|70–84|Baik|Seluruh hal yang diminta ada dan berfungsi, dengan sedikit<br>kekurangan pada kelengkapan atau konsistensi|
|55–69|Cukup|Sebagian besar ada, tetapi ada bagian penting yang belum<br>berfungsi atau buktinya kurang|
|< 55|Kurang|Banyak bagian yang tidak ada, tidak berfungsi, atau tidak dapat<br>dibuktikan|



Nilai individu dapat disesuaikan berdasarkan jejak kontribusi di board dan GitHub serta penilaian antar-anggota ( _peer assessment_ ). 

Panduan Proyek PPL — Halaman 12 

