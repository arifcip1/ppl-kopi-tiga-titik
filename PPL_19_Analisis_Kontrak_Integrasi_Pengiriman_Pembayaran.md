# PPL 19 — Analisis dan Kontrak Integrasi Pengiriman dan Pembayaran

**Proyek:** Sistem Informasi Penjualan Kopi Tiga Titik  
**Versi:** Konsolidasi 11 November 2026  
**Work Package:** PPL 19 — Analisis & Kontrak Integrasi: Pembayaran Sandbox, Webhook, Routing OSM & Parameter Tarif  
**Estimasi WBS:** 3 poin, 2 hari  
**Dependensi WBS:** PPL 17  
**WP implementasi lanjutan:** PPL 23–26 dan QE/integrasi PPL 33–42  
**Status dokumen:** kontrak analisis/integrasi. Bukan implementasi kode dan bukan perubahan schema.

---

## 1. Tujuan dan batas dokumen

PPL 19 menetapkan kontrak teknis untuk dua integrasi eksternal yang saling berkaitan dengan checkout:

1. Pembayaran online menggunakan Midtrans QRIS Sandbox, termasuk pembuatan percobaan pembayaran, webhook, verifikasi server-side, kedaluwarsa, dan idempotensi.
2. Pengiriman berbasis jarak rute jalan menggunakan peta OpenStreetMap dan layanan routing OpenRouteService (ORS), termasuk validasi titik, lokasi asal aktif, geofence Kota Mojokerto, formula ongkir, dan kegagalan layanan.

Dokumen ini menjadi acuan teknis untuk PPL 23–26. PPL 19 tidak mengambil alih implementasi UI/backend yang telah ditempatkan pada PPL 23–26 dan tidak menambah Work Package di luar WBS PPL 01–42.

### 1.1 Hierarki keputusan

- PPL 01: kebutuhan sistem dan aturan bisnis final.
- PRD: perilaku produk, acceptance criteria, dan cakupan fitur.
- WBS: ID WP, PIC, tanggal, estimasi, dan dependensi.
- PPL 02 serta migration/model aktual: schema database final.
- Kontrak Frontend–Backend: pola komunikasi antarmodul.
- PPL 19: detail integrasi Midtrans dan routing/ongkir yang tidak boleh bertentangan dengan dokumen di atas.

Jika ada perbedaan, jangan mengubah aturan bisnis atau schema secara sepihak. Catat konflik dan minta keputusan Product Owner.

### 1.2 Di luar ruang lingkup

- Pembayaran produksi/live dan transaksi uang sungguhan.
- Metode pembayaran online selain QRIS Midtrans Sandbox.
- Midtrans pada POS offline.
- API ekspedisi, tracking GPS, optimasi armada, dan multi-stop route.
- Pengiriman di luar Kota Mojokerto.
- COD, refund otomatis, dan pemulihan otomatis pembayaran terlambat.
- Menggunakan jarak garis lurus sebagai pengganti jarak rute jalan.

---

## 2. Integrasi pembayaran Midtrans QRIS Sandbox

### 2.1 Batas tanggung jawab

| Komponen | Tanggung jawab |
|---|---|
| Frontend pelanggan | Memulai checkout melalui backend, menampilkan QRIS/instruksi pembayaran yang diterima dari backend, countdown 10 menit, status berdasarkan backend, dan pesan error. Tidak memutuskan status berhasil. |
| Backend aplikasi | Memvalidasi order dan nominal, membuat transaksi Midtrans, menyimpan setiap payment attempt, menerima dan memverifikasi webhook, memperbarui status secara idempoten, dan mengirim data status ke frontend. |
| Midtrans Sandbox | Menyediakan transaksi QRIS uji dan mengirim notifikasi status transaksi ke endpoint webhook backend. |
| Database | Menyimpan order, setiap percobaan pembayaran, status, referensi provider, waktu kedaluwarsa/verifikasi, dan payload mentah yang telah diterima. |

### 2.2 Aturan bisnis yang mengikat

1. Metode online MVP adalah QRIS melalui Midtrans Sandbox.
2. Batas pembayaran adalah 10 menit sejak order dibuat.
3. Status payment yang digunakan: `pending`, `berhasil`, `gagal`, `kedaluwarsa`.
4. Status pembayaran online hanya `berhasil` setelah backend memverifikasi notifikasi resmi atau melakukan pemeriksaan status resmi ke Midtrans.
5. Redirect browser, query parameter, JavaScript, dan status yang dikirim frontend bukan bukti pembayaran.
6. Nominal hasil verifikasi harus sama dengan `orders.total`. Jika berbeda, jangan mengaktifkan order; tandai untuk pemeriksaan Owner.
7. Satu order boleh memiliki beberapa percobaan pembayaran Midtrans. Setiap percobaan disimpan sebagai baris `payments` tersendiri; jangan menimpa percobaan sebelumnya.
8. Notifikasi webhook berulang harus idempoten. Satu pembayaran tidak boleh menggandakan perubahan status, stok, riwayat, atau penjualan.
9. Pembayaran yang terkonfirmasi setelah order kedaluwarsa tetap dicatat sebagai hasil provider, tetapi order tidak otomatis aktif kembali. Tandai untuk pemeriksaan Owner; refund otomatis di luar MVP.
10. POS memakai `tunai` atau `qris` langsung dan konfirmasi Owner; POS tidak membuat transaksi Midtrans, tidak memakai webhook, dan tidak memakai `referensi_provider` Midtrans.

### 2.3 Urutan proses pembayaran online

1. Pelanggan menekan tombol bayar dari halaman checkout.
2. Backend memvalidasi kepemilikan cart, produk aktif, stok, data penerima, fulfillment, lokasi, jam operasional, subtotal, ongkir, dan total.
3. Backend membuat `orders` dan `order_items` serta menahan/mengurangi stok secara atomik sesuai mekanisme PPL 18.
4. Backend membuat satu baris `payments` berstatus `pending` untuk percobaan tersebut, dengan `metode_pembayaran=midtrans` dan batas kedaluwarsa.
5. Backend meminta transaksi QRIS ke Midtrans Sandbox menggunakan nominal dan identitas order yang sesuai.
6. Backend menyimpan referensi transaksi provider dan data respons relevan. Frontend menampilkan QR/instruksi pembayaran dari respons backend.
7. Midtrans mengirim webhook ke backend. Backend memverifikasi signature/credential, status transaksi, identitas order, dan nominal sesuai dokumentasi Midtrans.
8. Backend memeriksa idempotensi dan status lokal sebelum menerapkan perubahan.
9. Jika valid dan berhasil, payment menjadi `berhasil`, order online menjadi `diproses`, histori status ditulis, dan penjualan nyata dicatat satu kali.
10. Jika gagal/kedaluwarsa sesuai status provider atau batas waktu lokal, backend memperbarui payment/order sesuai aturan PPL 01 dan mengembalikan stok tepat satu kali untuk order belum dibayar.
11. Frontend melakukan polling/refresh status melalui endpoint status yang disediakan aplikasi; UI tidak menganggap pembayaran berhasil hanya karena QR ditutup atau browser diarahkan kembali.

### 2.4 Percobaan pembayaran berulang

- `payments.order_id` tidak unik; beberapa baris boleh mengacu ke satu `orders.id`.
- Setiap attempt memiliki identitas provider tersendiri jika diberikan Midtrans.
- `payments.referensi_provider` harus unik ketika terisi.
- Percobaan baru tidak menghapus catatan attempt lama.
- Backend harus menetapkan aturan apakah attempt baru hanya dapat dibuat setelah attempt lama gagal/kedaluwarsa; jangan membuat attempt aktif paralel untuk satu order tanpa keputusan PO.
- Pembayaran sukses yang sudah terverifikasi mengunci order agar tidak dibayar ulang.

### 2.5 Kontrak endpoint pembayaran

Path berikut adalah kontrak fungsi yang diselaraskan dengan PPL 01. Periksa route repository sebelum implementasi; gunakan route yang sudah ada jika fungsinya sama.

| Operasi | Method/path | Pemanggil | Akses |
|---|---|---|---|
| Membuat checkout dan payment attempt | `POST /api/checkout` | Frontend pelanggan | Pelanggan terautentikasi |
| Menerima webhook Midtrans | `POST /api/payments/webhook` | Midtrans | Server-to-server; tidak menggunakan session pelanggan |
| Melihat status payment order | `GET /api/orders/{id}/payment` | Frontend | Pemilik order atau Admin |

#### Request checkout online (kontrak minimal)

```json
{
  "metode_pemenuhan": "pengiriman",
  "nama_penerima": "Nama Penerima",
  "nomor_telepon": "08xxxxxxxxxx",
  "alamat_tujuan": "Alamat tujuan",
  "latitude_tujuan": -7.0,
  "longitude_tujuan": 112.0,
  "catatan": "Opsional"
}
```

Semua nilai contoh hanya menjelaskan bentuk payload. Backend mengambil harga, stok, item, ongkir, dan total dari database/perhitungan server. Jangan mempercayai nominal dari browser.

#### Respons checkout (bentuk kontrak)

```json
{
  "success": true,
  "message": "Checkout berhasil dibuat.",
  "data": {
    "order_id": 123,
    "kode_pesanan": "KTT-EXAMPLE",
    "payment_status": "pending",
    "order_status": "menunggu_pembayaran",
    "expires_at": "2026-11-11T10:10:00+07:00",
    "payment": {
      "method": "midtrans",
      "qris_payload_or_url": "DIPEROLEH_DARI_RESPONS_PROVIDER"
    }
  }
}
```

Nama field final harus konsisten dengan response wrapper aplikasi. Placeholder QRIS di atas bukan URL atau token yang dapat digunakan.

#### Respons status pembayaran

```json
{
  "success": true,
  "data": {
    "order_id": 123,
    "payment_status": "pending",
    "order_status": "menunggu_pembayaran",
    "verified_at": null,
    "expires_at": "2026-11-11T10:10:00+07:00"
  }
}
```

#### Respons error

Gunakan wrapper standar aplikasi dengan HTTP status yang sesuai (`401`, `403`, `404`, `422`, `502`, `500`). Kegagalan Midtrans tidak boleh mengubah status menjadi berhasil. Pesan pengguna tidak boleh memuat secret, signature, stack trace, atau payload sensitif.

### 2.6 Keamanan webhook dan idempotensi

Backend wajib:

1. Memvalidasi signature/authentication sesuai mekanisme resmi Midtrans yang dipakai.
2. Mengidentifikasi order dan attempt berdasarkan referensi yang dapat diverifikasi.
3. Memastikan nominal, currency, status provider, dan identitas transaksi cocok dengan data lokal.
4. Memastikan event yang sama tidak diterapkan lebih dari sekali.
5. Melindungi proses dari webhook bersamaan (race condition), misalnya dengan transaksi database dan penguncian record yang sesuai.
6. Memperbarui payment, order, stock, history, dan sales dalam batas transaksi atomik yang tepat.
7. Menyimpan payload mentah pada `payments.payload_mentah` hanya untuk audit/diagnostik; payload mentah bukan bukti keberhasilan sebelum verifikasi.
8. Menangani notifikasi terlambat tanpa mengaktifkan kembali order yang telah kedaluwarsa.
9. Mencatat error provider pada log server tanpa mengekspos credential.

### 2.7 Konfigurasi dan environment

- Server key, client key, merchant identifier, dan credential lain disimpan pada environment/config sesuai SDK/API Midtrans yang dipilih.
- Credential tidak boleh ditulis pada Blade, JavaScript, repository publik, log, atau dokumen yang dibagikan.
- Gunakan konfigurasi Sandbox untuk pengembangan dan pengujian.
- Endpoint webhook lokal harus dapat dijangkau oleh Midtrans Sandbox melalui tunnel publik yang disiapkan tim; URL tunnel bukan URL produksi permanen.
- Detail nama environment variable dan SDK wajib mengikuti package yang benar-benar terpasang di `composer.lock`/konfigurasi proyek. Jangan mengarang nama package.

---

## 3. Integrasi peta dan routing pengiriman

### 3.1 Pembagian fungsi layanan

- **OpenStreetMap (OSM):** sumber data peta dasar.
- **Leaflet:** komponen tampilan peta dan interaksi pemilihan titik pada frontend.
- **OpenRouteService (ORS):** layanan routing untuk memperoleh jarak rute jalan dari koordinat asal ke tujuan.
- **Backend Laravel:** sumber kebenaran untuk validasi koordinat/wilayah, pemilihan lokasi asal aktif, permintaan routing, formula ongkir, dan keputusan boleh/tidaknya checkout.

Peta frontend tidak boleh menghitung atau menetapkan ongkir final. Koordinat dan ongkir yang dikirim browser harus diverifikasi ulang oleh backend.

### 3.2 Alur pengiriman online

1. Owner menyimpan satu atau lebih lokasi asal pada `shipping_locations`.
2. Owner memilih satu lokasi aktif. Backend memeriksa `aktif=true` dan menyimpan ID pada `settings` dengan `key=lokasi_pengiriman_aktif_id`.
3. Pelanggan memilih metode pemenuhan `pengiriman` dan menentukan titik tujuan pada peta.
4. Frontend mengirim latitude/longitude tujuan ke endpoint quote.
5. Backend memvalidasi format/rentang koordinat, keberadaan lokasi asal aktif, dan apakah tujuan termasuk Kota Mojokerto.
6. Backend meminta jarak rute jalan ke ORS menggunakan koordinat asal dan tujuan.
7. Backend mengonversi jarak ke kilometer dan menerapkan formula tarif final.
8. Backend mengembalikan jarak/ongkir sebagai estimasi quote.
9. Saat checkout, backend menghitung ulang quote (atau memverifikasi quote yang masih valid) dan menyimpan snapshot lokasi asal, titik tujuan, jarak, ongkir, serta total pada order.
10. Jika lokasi asal berubah setelah order berhasil dibuat, order lama tetap memakai snapshot saat checkout.

### 3.3 Validasi titik dan batas wilayah

- Latitude dan longitude wajib untuk `pengiriman`.
- Latitude dan longitude tujuan tidak wajib untuk `ambil_sendiri`.
- Koordinat harus numerik, berada pada rentang latitude `-90..90` dan longitude `-180..180`, serta bukan nilai kosong/0,0 yang dipakai sebagai placeholder.
- Titik tujuan pengiriman wajib berada dalam wilayah Kota Mojokerto, bukan Kabupaten Mojokerto.
- Pemeriksaan wilayah harus menggunakan batas administratif/polygon Kota Mojokerto yang disepakati dan diuji. Reverse geocoding atau nama alamat saja tidak cukup jika hasilnya ambigu.
- Sumber polygon/batas administratif belum dikunci dalam dokumen proyek; Product Owner/implementer harus memilih dan mencatat sumber data resmi/tepercaya sebelum fitur dianggap selesai. Jangan membuat polygon perkiraan.
- Lokasi asal aktif harus ditemukan pada `shipping_locations`, `aktif=true`, dan koordinatnya sudah diverifikasi Owner.
- Jika lokasi asal tidak dipilih/tidak valid atau tujuan di luar wilayah, backend menolak quote/checkout dengan pesan yang dapat dipahami.

### 3.4 Formula ongkir final

Misalkan `d` adalah jarak rute jalan dalam kilometer yang dikembalikan layanan routing.

- Jika `d <= 5,5 km`, ongkir = Rp0.
- Jika `d > 5,5 km`, ongkir = `Rp2.000 × (floor(d - 5,5) + 1)`.

| Jarak rute | Ongkir |
|---:|---:|
| 5,50 km | Rp0 |
| 5,51 km | Rp2.000 |
| 6,49 km | Rp2.000 |
| 6,50 km | Rp4.000 |
| 7,49 km | Rp4.000 |
| 7,50 km | Rp6.000 |

Gunakan nilai jarak presisi dari response routing untuk formula; jangan membulatkan lebih dulu menjadi satu desimal sebelum menghitung tarif. Pembulatan hanya untuk tampilan.

### 3.5 Kontrak endpoint quote

`POST /api/shipping/quote` — pelanggan terautentikasi.

Request:

```json
{
  "metode_pemenuhan": "pengiriman",
  "latitude_tujuan": -7.0,
  "longitude_tujuan": 112.0
}
```

Response sukses (contoh struktur):

```json
{
  "success": true,
  "data": {
    "metode_pemenuhan": "pengiriman",
    "jarak_km": 5.6,
    "ongkir": 2000,
    "mata_uang": "IDR",
    "lokasi_asal": {
      "id": 1,
      "nama_lokasi": "Lokasi Aktif"
    }
  }
}
```

Response error (contoh):

```json
{
  "success": false,
  "message": "Lokasi tujuan berada di luar wilayah layanan atau rute tidak dapat dihitung.",
  "errors": {
    "lokasi_tujuan": ["Periksa titik lokasi pengiriman."]
  }
}
```

Untuk pickup, quote ongkir tidak memanggil ORS dan mengembalikan ongkir Rp0. Endpoint boleh mengembalikan hasil pickup tanpa koordinat tujuan; backend tetap menyimpan snapshot lokasi pengambilan aktif.

### 3.6 Penanganan kegagalan routing

- Timeout, quota limit, invalid API key, response kosong, koordinat tidak dapat dirutekan, atau error provider menyebabkan quote gagal.
- Jangan memakai jarak garis lurus, cache kadaluarsa, ongkir nol, atau angka default sebagai fallback diam-diam.
- Checkout pengiriman diblokir sampai routing berhasil.
- Frontend menampilkan pesan retry yang ramah tanpa detail credential/provider.
- Backend mencatat kategori error, waktu, correlation/request ID bila tersedia, tanpa mencatat secret.
- Terapkan timeout dan batas request agar layanan tidak menggantung; nilai konfigurasi ditentukan dari dokumentasi ORS dan batas paket yang digunakan.

### 3.7 Konfigurasi ORS

- API key ORS disimpan di environment/config backend.
- Request ORS dilakukan server-side; API key tidak dikirim ke browser.
- Gunakan endpoint/profile routing yang sesuai untuk jarak rute kendaraan/antar sesuai kebutuhan pengiriman dan dokumentasi layanan yang dipilih.
- Catat endpoint, profile, unit, timeout, kuota, attribution, dan kebijakan penggunaan pada konfigurasi proyek.
- Peta OSM harus menampilkan attribution sesuai kebijakan tile provider. Jangan menganggap server tile OSM standar bebas batas tanpa syarat.

---

## 4. Kontrak database untuk pengiriman dan pembayaran

Database domain tetap 10 tabel. PPL 19 tidak membuat migration atau tabel baru.

| Tabel | Kolom yang relevan | Cara penggunaan dalam integrasi |
|---|---|---|
| `orders` | `id`, `kode`, `sumber_transaksi`, `metode_pemenuhan`, `user_id`, `courier_id`, `status`, `subtotal`, `ongkir`, `total`, data penerima, koordinat tujuan, `nama_lokasi_asal`, `alamat_lokasi_asal`, `latitude_lokasi_asal`, `longitude_lokasi_asal`, `jarak_pengiriman`, `batas_pengambilan`, `catatan`, `stok_dikembalikan` | Online mengisi `sumber_transaksi=online`; `metode_pemenuhan` `pengiriman`/`ambil_sendiri`. POS mengisi `sumber_transaksi=pos`, `metode_pemenuhan=NULL`. Snapshot lokasi asal/pickup wajib disimpan untuk pesanan online. |
| `payments` | `id`, `order_id`, `metode_pembayaran`, `referensi_provider`, `status`, `jumlah`, `kedaluwarsa_pada`, `waktu_verifikasi`, `payload_mentah` | Setiap attempt Midtrans menjadi row terpisah. `order_id` tidak unik. `referensi_provider` unique nullable. POS menggunakan `tunai`/`qris` langsung. |
| `order_status_history` | `id`, `order_id`, `status_baru`, `diubah_oleh`, `waktu_perubahan`, `keterangan` | Catat setiap transisi; `diubah_oleh=NULL` untuk scheduler. Tidak ada kolom `status_sebelumnya` atau `user_id`. |
| `shipping_locations` | `id`, `nama_lokasi`, `latitude`, `longitude`, `alamat`, `aktif` | Menyimpan lebih dari satu lokasi asal; hanya lokasi aktif terpilih yang dipakai untuk quote baru. |
| `settings` | `id`, `key`, `value`, `description` | `lokasi_pengiriman_aktif_id` menunjuk ID `shipping_locations`; `jam_buka` dan `jam_tutup` untuk checkout baru. `value` adalah teks dan wajib dikonversi/divalidasi backend. |
| `order_items` | `order_id`, `product_id`, `nama_produk`, `rasa`, `jumlah`, `harga_satuan`, `subtotal` | Snapshot rincian produk; perubahan produk tidak mengubah order lama. |
| `products` | `id`, `harga`, `stok`, `aktif` | Harga dan stok sumber kebenaran server. Perubahan stok harus atomik. |
| `sales_history` | `product_id`, `tanggal`, `jumlah_terjual`, `sumber` | Catat `transaksi` setelah pembayaran online terverifikasi; POS setelah Owner konfirmasi. Jangan catat percobaan pending/gagal. |

**Penting:** kolom koordinat asal harus diverifikasi terhadap migration/model aktif PPL 02 sebelum query ditulis. Jika nama aktual berbeda, gunakan migration sebagai acuan dan selaraskan dokumen melalui persetujuan PO; jangan menambah kolom duplikat.

### 4.1 Snapshot lokasi

- Saat checkout pengiriman/pickup, simpan nama, alamat, latitude, longitude lokasi asal terpilih ke `orders`.
- Perubahan `settings.lokasi_pengiriman_aktif_id` hanya memengaruhi transaksi baru.
- Detail dan nota order lama membaca snapshot dari `orders`, bukan lokasi aktif terbaru.
- Pickup menyimpan snapshot lokasi pengambilan; koordinat tujuan pelanggan tidak dibutuhkan.

### 4.2 Tidak ada tabel domain tambahan

Jangan membuat `payment_attempts`, `payment_events`, `webhook_logs`, `shipping_quotes`, `shipping_rates`, `pos_transactions`, atau `notifications` tanpa persetujuan PO. `payments` sudah mendukung beberapa attempt per order; `payload_mentah` mendukung penyimpanan payload provider yang diperlukan; log teknis dapat memakai logging Laravel tanpa tabel domain baru.

---

## 5. Proses checkout terpadu: pengiriman vs ambil sendiri

| Data/proses | `pengiriman` | `ambil_sendiri` |
|---|---|---|
| Login pelanggan | Wajib | Wajib |
| Nama penerima dan nomor telepon | Wajib | Wajib |
| Alamat tujuan kirim | Wajib | Tidak digunakan sebagai alamat pengiriman |
| Koordinat tujuan | Wajib | Tidak wajib |
| Lokasi asal aktif | Wajib | Wajib sebagai lokasi pengambilan |
| Routing ORS | Wajib | Tidak dipanggil |
| Validasi geofence Kota Mojokerto | Wajib untuk tujuan kirim | Tidak memvalidasi koordinat tujuan pelanggan |
| Ongkir | Formula berdasarkan jarak rute | Rp0 |
| Snapshot lokasi | Snapshot asal saat checkout | Snapshot lokasi pengambilan saat checkout |
| Kurir | Dapat ditugaskan Owner | Tidak perlu kurir |
| Deadline | Batas pembayaran 10 menit | Pembayaran 10 menit; setelah berhasil deadline pickup 24 jam |

Checkout online hanya dapat dilakukan saat jam operasional. Perubahan jam operasional tidak membatalkan pesanan yang sudah berhasil dibuat/dibayar.

---

## 6. POS bukan bagian dari integrasi Midtrans

POS offline menggunakan tabel yang sama untuk menjaga single source of truth, tetapi jalur pembayaran terpisah:

1. Owner memilih produk dan jumlah.
2. Backend memvalidasi produk aktif, stok, dan harga.
3. Backend membuat order dengan `sumber_transaksi=pos`, `metode_pemenuhan=NULL`, dan `user_id` boleh NULL.
4. Payment menggunakan `metode_pembayaran=tunai` atau `qris` langsung.
5. Owner mengonfirmasi pembayaran; backend memproses payment, pengurangan stok, status order, dan `sales_history` secara atomik/idempoten.
6. Setelah barang diserahkan, order POS berstatus `selesai`.
7. Tidak ada transaksi Midtrans, QRIS gateway, webhook, atau `referensi_provider` untuk POS.

---

## 7. Pengujian integrasi dan acceptance criteria PPL 19

### 7.1 Pengujian Midtrans

1. Checkout QRIS sandbox berhasil membuat order dan payment `pending`.
2. Nominal yang dikirim ke provider sama dengan total order hasil hitung backend.
3. Webhook signature valid dan nominal cocok mengubah payment menjadi `berhasil`.
4. Webhook signature tidak valid ditolak/tidak mengubah order.
5. Webhook duplikat tidak menggandakan status, stok, riwayat, atau penjualan.
6. Status `pending`, `gagal`, dan `kedaluwarsa` ditampilkan sesuai backend.
7. Order yang melewati 10 menit tanpa pembayaran terverifikasi kedaluwarsa dan stok dikembalikan tepat satu kali.
8. Pembayaran terlambat tercatat tetapi tidak mengaktifkan kembali order otomatis.
9. Satu order dapat memiliki lebih dari satu payment attempt tanpa menimpa riwayat sebelumnya.
10. POS tunai/QRIS tidak memanggil Midtrans.

### 7.2 Pengujian routing/ongkir

1. Lokasi aktif ditemukan dan `aktif=true`.
2. Lokasi belum dipilih, tidak ditemukan, atau nonaktif memblokir quote.
3. Koordinat tujuan valid dan berada di Kota Mojokerto dapat dihitung.
4. Tujuan di luar Kota Mojokerto ditolak.
5. Jarak 5,50 km menghasilkan Rp0.
6. Jarak 5,51 km dan 6,49 km menghasilkan Rp2.000.
7. Jarak 6,50 km menghasilkan Rp4.000.
8. Jarak 7,50 km menghasilkan Rp6.000.
9. Routing timeout/quota/invalid response memblokir checkout; tidak ada fallback ongkir tebakan.
10. Pickup tanpa koordinat tujuan tidak memanggil ORS dan ongkir Rp0.
11. Perubahan lokasi aktif tidak mengubah snapshot order yang sudah dibuat.
12. Jam operasional menolak checkout online baru di luar jam; tidak mengubah order lama.

### 7.3 Definition of Done PPL 19

- [ ] Aturan Midtrans dan routing sesuai PPL 01/PRD.
- [ ] Detail request/response dan alur error didokumentasikan.
- [ ] Tidak ada credential pada frontend/repository/log.
- [ ] Percobaan Midtrans disimpan per row pada `payments`.
- [ ] Webhook diverifikasi server-side dan idempoten.
- [ ] Formula ongkir diuji pada nilai batas.
- [ ] Area Kota Mojokerto divalidasi menggunakan boundary data yang ditetapkan, bukan perkiraan.
- [ ] Pickup melewati routing dan ongkir menjadi Rp0.
- [ ] Snapshot lokasi order tidak berubah saat lokasi aktif diganti.
- [ ] Tidak ada tabel domain baru atau perubahan schema tanpa persetujuan.
- [ ] Seluruh skenario uji dicatat dan dapat diulang.
- [ ] Hasil analisis diserahkan kepada PIC PPL 23–26 dan Scrum Master.

---

## 8. Parameter yang harus diverifikasi sebelum integrasi dianggap final

| Parameter | Keputusan/Status | Penanggung jawab |
|---|---|---|
| Payment provider | Midtrans QRIS Sandbox — final | Product Owner / backend |
| Batas pembayaran | 10 menit sejak order dibuat — final | Backend |
| Webhook verification | Wajib server-side sesuai dokumentasi Midtrans — final | Backend |
| Routing provider | OpenRouteService — pilihan layanan dalam WBS/PRD | Backend |
| Peta frontend | OpenStreetMap + Leaflet — final | Frontend |
| Tarif | Gratis sampai 5,5 km; setelahnya formula PPL 01 — final | Backend |
| Wilayah | Kota Mojokerto saja — final | Backend |
| Polygon batas Kota Mojokerto | Sumber dataset/boundary belum disebut eksplisit; wajib dipilih dan dicatat sebelum implementasi geofence | Product Owner + backend |
| Koordinat lokasi asal | Owner harus memasukkan dan memverifikasi lokasi aktual | Owner |
| ORS API key, profile, quota, timeout | Diatur melalui environment/config dan diverifikasi terhadap akun/dokumentasi provider | Backend |
| Midtrans SDK/API version dan environment key | Cocokkan package/konfigurasi aktual repository | Backend |
| Mekanisme polling status frontend | Ikuti pola aplikasi; interval dan penghentian polling ditentukan implementer, tidak mengubah aturan status | Frontend + backend |

---

## 9. Relasi dengan Work Package lain

| WP | Ketergantungan/serah terima |
|---|---|
| PPL 17 | Struktur tabel orders, order_items, payments menjadi input PPL 19. PPL 19 tidak mengubah schema tanpa persetujuan. |
| PPL 18 | Mekanisme penahanan/pengembalian stok harus dipanggil secara konsisten saat pembayaran kedaluwarsa/gagal. |
| PPL 23 | Mengimplementasikan Midtrans, webhook, payment status, pickup, nota, kontak sesuai kontrak ini. |
| PPL 24 | Menampilkan QRIS/status/countdown dan pickup/nota/kontak berdasarkan data backend. |
| PPL 25 | UI titik tujuan, rincian ongkir, lokasi aktif, dan jam operasional. |
| PPL 26 | Implementasi backend ORS, validasi wilayah/koordinat, formula ongkir, lokasi/jam, fallback error. |
| PPL 33–42 | QE, integrasi, regresi, retest pembayaran, perbaikan bug, dan QE akhir. |

**PPL 19 tidak menambah jadwal, PIC, story point, durasi, atau WP.** Pekerjaan implementasi tetap berada di PPL 23–26 sebagaimana WBS.
