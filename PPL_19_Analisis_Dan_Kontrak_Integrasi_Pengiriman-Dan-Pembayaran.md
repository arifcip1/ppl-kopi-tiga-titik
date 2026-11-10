# DOKUMEN ANALISIS DAN KONTRAK INTEGRASI (PPL-19)
**Sistem Informasi Penjualan Kopi Tiga Titik**  
*Modul: Pembayaran Midtrans QRIS Sandbox, Ongkir Berbasis OpenStreetMap, dan Lokasi Asal Pengiriman*

- **ID Work Package:** PPL-19
- **Status Dokumen:** FINAL – DISETUJUI PRODUCT OWNER
- **Tanggal Persetujuan:** 9 November 2026
- **Dokumen Acuan:** PRD Revisi Dashboard, PPL 01 (Analisis Kebutuhan), PPL 02 (ERD & Database), Kontrak Frontend-Backend Revisi Dashboard

---

## 1. Ringkasan Eksekutif & Ruang Lingkup

Dokumen ini merupakan hasil analisis dan kontrak integrasi teknis antarmodul untuk **PPL-19 (Analisis dan Kontrak Integrasi Pembayaran, Ongkir, dan Lokasi Asal Pengiriman)**. Dokumen ini menjadi acuan kerja mengikat bagi pengerjaan:
1. **PPL-23:** Backend Pembayaran Midtrans QRIS Sandbox & Webhook.
2. **PPL-24:** Frontend Pembayaran QRIS & Interaksi Status Pembayaran.
3. **PPL-25:** Frontend Pemilihan Lokasi Pengiriman & Peta Interaktif Leaflet.
4. **PPL-26:** Backend Ongkos Kirim Berbasis OpenStreetMap & Pengelolaan Lokasi Asal.

Audit menyeluruh terhadap implementasi aktual PPL-17 (skema basis data) dan PPL-18 (checkout & manajemen stok) telah dilakukan, memastikan seluruh kontrak pada dokumen ini mematuhi arsitektur yang sudah berjalan tanpa merusak integritas sistem maupun melakukan perubahan skema database tanpa otorisasi.

---

## 2. Kontrak Integrasi Pembayaran (Midtrans QRIS Sandbox)

### 2.1 Alur Pembuatan Transaksi QRIS Pasca-Checkout
1. Pelanggan menyelesaikan checkout melalui `POST /checkout`. Backend menghasilkan record `Order` (status: `menunggu_pembayaran`) dan record `Payment` (status: `pending`).
2. Browser dialihkan ke halaman pembayaran: `/pembayaran-qris?order_id={id}`.
3. Frontend memanggil endpoint pembuatan/pengambilan transaksi pembayaran: `POST /api/orders/{order}/payment/charge`.
4. Backend memanggil Midtrans Core API `POST https://api.sandbox.midtrans.com/v2/charge` dengan autentikasi Basic Auth menggunakan Server Key dari file `.env`.
5. Parameter payload charge Midtrans:
   - `payment_type`: `"qris"`
   - `qris`: `{"acquirer": "gopay"}`
   - `transaction_details.order_id`: `{orders.kode}-{attempt}` (contoh: `KTT-20261109-0001-1`)
   - `transaction_details.gross_amount`: `$order->total`
   - `custom_expiry`: durasi sisa waktu hingga `$payment->kedaluwarsa_pada` (maksimal 10 menit sejak `orders.created_at`).
6. Midtrans mengembalikan `qr_string` dan action `generate-qr-code` (URL gambar QRIS).
7. Frontend merender QRIS dinamis dan menjalankan hitung mundur sisa waktu 10 menit.

### 2.2 Pemanfaatan Record `payments` dan Skema Partisi JSON `payload_mentah`
- `CheckoutService` (PPL-18) telah membuat 1 record `Payment` per order dengan status `pending` dan `kedaluwarsa_pada = now() + 10 menit`.
- Karena constraint `payments.order_id` adalah UNIQUE, service pembayaran **tidak membuat baris baru** di tabel `payments`, melainkan memperbarui baris yang sudah ada.
- Untuk mencegah penimpaan data (*overwriting*) antara riwayat attempt, log webhook berulang, dan penanda anomali, kolom JSON `payments.payload_mentah` distrukturkan ke dalam 4 partisi independen:
  ```json
  {
    "active_attempt": "KTT-20261109-0001-2",
    "attempts": {
      "KTT-20261109-0001-1": {
        "attempt_number": 1,
        "transaction_id": "1015a919-b03f-450a-bc85-b38202a79a96",
        "created_at": "2026-11-09T13:00:00Z",
        "qr_string": "00020101021226620014COM.GO-JEK.WWW...",
        "qr_image_url": "https://api.sandbox.midtrans.com/v2/qris/1015a919.../qr-code",
        "status": "superseded_by_earlier_settlement"
      },
      "KTT-20261109-0001-2": {
        "attempt_number": 2,
        "transaction_id": "2026b828-c14e-461b-cd96-c49303b80b07",
        "created_at": "2026-11-09T13:04:00Z",
        "qr_string": "00020101021226620014COM.GO-JEK.WWW...",
        "qr_image_url": "https://api.sandbox.midtrans.com/v2/qris/2026b828.../qr-code",
        "status": "pending"
      }
    },
    "webhook_logs": [
      {
        "received_at": "2026-11-09T13:05:12Z",
        "source_order_id": "KTT-20261109-0001-1",
        "transaction_status": "settlement",
        "status_code": "200",
        "gross_amount": "28000.00",
        "raw_payload": { }
      }
    ],
    "anomalies": {
      "has_anomaly": false,
      "flags": [],
      "details": []
    }
  }
  ```

### 2.3 Kontrak Endpoint Pembayaran

#### A. Inisialisasi Transaksi QRIS (`POST /api/orders/{order}/payment/charge`)
- **Aktor:** Pelanggan (pemilik pesanan)
- **Otorisasi:** `auth:web` + verifikasi kepemilikan `order.user_id === auth()->id()`
- **Format Respons Sukses (200 OK):**
  ```json
  {
    "success": true,
    "message": "Transaksi QRIS berhasil diinisialisasi.",
    "data": {
      "order_id": 12,
      "order_code": "KTT-20261109-0001",
      "attempt": 1,
      "gross_amount": 28000,
      "qr_string": "00020101021226620014COM.GO-JEK.WWW...",
      "qr_image_url": "https://api.sandbox.midtrans.com/v2/qris/1015a919-b03f.../qr-code",
      "expires_at": "2026-11-09T13:10:00Z",
      "remaining_seconds": 580
    }
  }
  ```

#### B. Polling Status Pembayaran (`GET /api/orders/{order}/payment-status`)
- **Aktor:** Pelanggan (pemilik pesanan) & Admin
- **Frekuensi Polling Frontend:** Interval 3–5 detik selama halaman QRIS aktif.
- **Format Respons Sukses (200 OK):**
  ```json
  {
    "success": true,
    "message": "Status pembayaran berhasil diambil.",
    "data": {
      "order_id": 12,
      "order_status": "diproses",
      "payment_status": "berhasil",
      "waktu_verifikasi": "2026-11-09T13:05:15Z",
      "is_final": true
    }
  }
  ```

### 2.4 Validasi Keamanan Webhook & Penanganan Status
1. **Validasi Signature SHA512:**
   $$\text{Computed Signature} = \text{hash('sha512', } \text{order\_id} + \text{status\_code} + \text{gross\_amount} + \text{ServerKey})$$
   Jika tidak cocok, tolak dengan HTTP `403 Forbidden` atau `400 Bad Request`.
2. **Presisi Validasi Nominal Integer (Anti Floating-Point Issue):**
   Gunakan pemotongan string untuk memisahkan sen guna menghindari inkonsistensi representasi biner `floatval()`:
   ```php
   $nominalParts = explode('.', (string) $payload['gross_amount']);
   $grossAmountInteger = (int) $nominalParts[0];
   $cents = $nominalParts[1] ?? '00';

   if ($grossAmountInteger !== (int) $order->total || !in_array($cents, ['0', '00', ''])) {
       // Anomali nominal mismatch
   }
   ```
3. **Penanganan Webhook dari Stale Attempt yang Berstatus Settlement (DISETUJUI PO):**
   Jika webhook masuk membawa identifier attempt lama (misal `-1`) dengan status `settlement`, sementara pesanan lokal masih `menunggu_pembayaran` dan attempt terbaru (`-2`) masih `pending`:
   - Pembayaran diakui sah dan pesanan dimajukan menjadi `diproses`.
   - `payments.status` diubah menjadi `berhasil`.
   - `payments.referensi_provider` disinkronkan ke attempt lama yang terbayar tersebut.
   - Attempt `-2` ditandai sebagai `superseded_by_earlier_settlement`.
   - Riwayat dicatat pada `order_status_history`.
   - Respons HTTP `200 OK` dikirimkan ke Midtrans.
4. **Penanganan Pembayaran Ganda (DISETUJUI PO):**
   Jika pelanggan ternyata membayar dua attempt berbeda pada pesanan yang sama:
   - Webhook yang masuk belakangan mendeteksi bahwa pesanan sudah lunas (`isSuccess()`).
   - Tidak ada perubahan status pesanan maupun stok (menjaga idempotensi).
   - Data pembayaran kedua dicatat pada `payload_mentah['anomali']` dan `order_status_history`.
   - Penyelesaian pengembalian dana (refund) kelebihan bayar dilakukan secara **manual oleh Admin/Owner di luar aplikasi**.
   - Respons HTTP `200 OK` dikirimkan ke Midtrans agar webhook tidak retry.
5. **Penanganan Pembayaran Terlambat (Late Settlement):**
   Jika notifikasi `settlement` masuk setelah pesanan berstatus `dibatalkan` (kedaluwarsa):
   - Pesanan **TIDAK DIAKTIFKAN KEMBALI** dan stok **TIDAK DIKURANGI KEMBALI**.
   - `payments.status` diperbarui menjadi `berhasil`.
   - Kasus ditandai pada `payload_mentah['anomali']` dan `order_status_history` untuk penanganan/refund manual oleh Admin.
   - Respons HTTP `200 OK` dikirimkan ke Midtrans.
6. **Penanganan Nominal Tidak Sesuai (Amount Mismatch):**
   - Transaksi resmi dari Midtrans namun nominal tidak cocok dengan total pesanan.
   - Pesanan **tidak diubah** menjadi diproses.
   - Anomali dicatat ke `anomalies` dan `order_status_history`.
   - Penanganan diselesaikan secara manual oleh Owner/Admin.
   - Respons HTTP `200 OK` dikirimkan ke Midtrans setelah logging berhasil dicatat agar Midtrans menghentikan pengiriman ulang.
7. **Pengamanan Webhook:**
   Route `POST /api/payments/webhook` dikecualikan dari verifikasi CSRF di `bootstrap/app.php` dan berada di luar middleware grup `auth`.

---

## 3. Kontrak Integrasi Ongkos Kirim (OpenStreetMap)

### 3.1 Endpoint Perhitungan Ongkir (`POST /api/shipping/quote`)
- **Aktor:** Pelanggan (terautentikasi)
- **Input Request Body:**
  ```json
  {
    "lat": -7.472600,
    "lng": 112.438500
  }
  ```
- **Validasi:** `lat` required, numeric, between -90 and 90; `lng` required, numeric, between -180 and 180.

### 3.2 Pemilihan Layanan Routing (DISETUJUI PO)
- **Penyedia Terpilih:** **OpenRouteService (ORS)**.
- **Alasan Pemilihan:** Menyediakan kuota resmi 2.000 request/hari (40 req/menit) dengan API key gratis, memiliki SLA dan stabilitas tinggi, serta format respon terstruktur (GeoJSON).
- **Implementasi Abstraksi:** Implementasi konkret dibuat pada PPL-26 mengimplementasikan interface `App\Services\Shipping\ShippingCostResolver` dan didaftarkan pada service container di `AppServiceProvider`.

### 3.3 Formula Perhitungan Tarif Bertingkat (FR-7.3)
Perhitungan menggunakan jarak rute jalan ($d$) dalam kilometer tanpa pembulatan awal:
- Jika $d \le 5,5\text{ km} \implies \text{Ongkir} = \text{Rp } 0$.
- Jika $d > 5,5\text{ km} \implies \text{Ongkir} = 2000 \times (\lfloor d - 5,5 \rfloor + 1)$.

### 3.4 Validasi Batas Wilayah Kota Mojokerto (FR-7.5)
- Menggunakan reverse geocoding Nominatim OpenStreetMap dengan header wajib:
  `User-Agent: KopiTigaTitikApp/1.0 (contact@kopitigatitik.local)`
- Rate limit dibatasi maksimal 1 req/detik dan didukung caching koordinat di backend.
- Titik koordinat wajib berada dalam wilayah administratif Kota Mojokerto (`address.city`, `address.town`, atau `address.city_district` mengandung `"Mojokerto"`).

### 3.5 Penanganan Kegagalan Layanan Routing (FR-7.6)
- Jika layanan routing gagal, timeout, atau rute jalan tidak ditemukan:
  - Kembalikan HTTP `422 Unprocessable Entity` atau `502 Bad Gateway`.
  - **Dilarang keras** menggunakan jarak garis lurus (*Haversine*) atau ongkir tebakan default sebagai fallback.
  - Checkout wajib diblokir hingga rute valid berhasil dihitung.

---

## 4. Kontrak Lokasi Asal Pengiriman (F11)

### 4.1 Sumber Kebenaran Lokasi Aktif
- **Satu-satunya Sumber Kebenaran:** Record pada tabel `settings` dengan `key = 'lokasi_pengiriman_aktif_id'` yang merujuk pada `shipping_locations.id`.
- Kolom `shipping_locations.aktif` diperlakukan sebagai status bantu tampilan dan wajib disinkronkan saat Owner mengubah lokasi aktif.

### 4.2 Prosedur Validasi Lokasi Asal
Sebelum menghitung rute dan ongkir, sistem memverifikasi bahwa:
1. `settings.lokasi_pengiriman_aktif_id` terdefinisi dan valid.
2. Record `ShippingLocation` terkait tersedia di database.
3. Koordinat latitude dan longitude asal valid.
Jika tidak valid, sistem menolak pembuatan quote dan checkout dengan pesan kesalahan konfigurasi asal belum ditentukan.

### 4.3 Prinsip Snapshot Pesanan (BR-32)
Seluruh pesanan yang telah berhasil dibuat mengunci nilai `ongkir`, `subtotal`, `total`, `lat`, `lng`, dan `alamat` sebagai snapshot statis. Perubahan lokasi aktif oleh Owner di masa depan tidak mempengaruhi nilai pesanan yang sudah tercatat.

---

## 5. Keputusan Resmi Product Owner (PO)

Ketiga butir keputusan tingkat bisnis telah resmi disetujui oleh Product Owner pada tanggal 9 November 2026:

| No | Butir Keputusan Bisnis | Status Persetujuan | Ketentuan Implementasi |
|---|---|---|---|
| 1 | **Penerimaan Pembayaran dari Stale Attempt** | **DISETUJUI** | Jika attempt lama berhasil dibayar saat pesanan masih menunggu pembayaran, pesanan tetap diproses normal dan status dimajukan ke `diproses`. Attempt baru ditandai *superseded*. |
| 2 | **Penanganan Manual Pembayaran Ganda & Nominal Tidak Sesuai** | **DISETUJUI** | Sistem mencatat anomali ke log dan membekukan status pesanan. Pengembalian dana (refund) atau klarifikasi diselesaikan secara manual oleh Owner/Admin di luar aplikasi. Webhook direspons HTTP `200 OK` setelah logging. |
| 3 | **Penggunaan OpenRouteService (ORS)** | **DISETUJUI** | Tim disetujui mendaftarkan API key gratis ORS (kuota 2.000 req/hari) untuk perhitungan jarak rute jalan pengantaran kurir Kota Mojokerto. |

---

## 6. Standarisasi Respons API & Penyelarasan Dokumen

### 6.1 Format Respons API Standar
Seluruh endpoint baru mengadopsi format standar dengan kunci boolean `success`:
- **Format Sukses:**
  ```json
  {
    "success": true,
    "message": "Pesan deskriptif keberhasilan.",
    "data": { ... }
  }
  ```
- **Format Gagal:**
  ```json
  {
    "success": false,
    "message": "Pesan deskriptif kegagalan.",
    "errors": { "field": ["Detail pesan validasi."] }
  }
  ```

### 6.2 Daftar Dokumen yang Perlu Diselaraskan pada Implementasi
1. **PPL 01 (BR-36 & Tabel Status):** Menghapus enum `kedaluwarsa` pada `orders.status` (status order yang benar adalah `dibatalkan`, sedangkan payment adalah `kedaluwarsa`).
2. **PPL 01 (Tabel Konfirmasi):** Menghapus teks usang "gratis 5 km" dan memperbarui ke formula final batas 5,5 km.
3. **PPL-18 (`OrderStockService::failOrder`):** Menyesuaikan pemanggilan webhook di PPL-23 agar pembayaran gagal tidak membatalkan pesanan seketika (selaras dengan PRD FR-6.7).

---

## 7. Kesimpulan Kesiapan Dokumen

Dengan dimasukkannya ketiga keputusan resmi Product Owner, seluruh analisis teknis, kontrak data antar-modul, format webhook, mitigasi idempotensi, dan skema error handling untuk PPL-19 telah **SELESAI, TUNTAS, DAN RESMI BERSTATUS FINAL**.

Tim pengembang memiliki spesifikasi yang lengkap, presisi, dan konsisten untuk melanjutkan ke tahap implementasi teknis berikutnya:
- **PPL-23:** Backend Pembayaran Midtrans & Scheduler Expire.
- **PPL-24:** Frontend Halaman Pembayaran QRIS Dinamis.
- **PPL-25:** Frontend Pemilihan Lokasi & Peta Leaflet.
- **PPL-26:** Backend Ongkir OpenRouteService & Pengelolaan Lokasi Asal.
