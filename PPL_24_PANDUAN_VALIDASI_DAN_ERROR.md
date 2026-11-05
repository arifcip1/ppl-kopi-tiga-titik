# Panduan Komponen Pesan Validasi & Halaman Error (PPL-24)
## Tim Pengembang — Kopi Tiga Titik

Dokumen ini menjelaskan hasil pengerjaan task **PPL-24 (Frontend Pesan Validasi & Halaman Error)** serta cara penggunaan komponen UI validasi di seluruh halaman aplikasi.

---

### 1. Halaman Error Kustom (Custom Error Pages)
Semua halaman error dirancang responsif, ramah pengguna, dan seragam dengan identitas brand **Kopi Tiga Titik**:

| Kode Error | Keterangan & Kapan Muncul | File View |
|------------|---------------------------|-----------|
| **403 Forbidden** | Pengguna biasa/pelanggan mencoba membuka URL khusus owner/barista | `resources/views/errors/403.blade.php` |
| **404 Not Found** | URL yang dimasukkan salah atau halaman tidak ditemukan | `resources/views/errors/404.blade.php` |
| **419 Page Expired** | Sesi CSRF token form kadaluwarsa karena lama tidak disubmit | `resources/views/errors/419.blade.php` |
| **500 Server Error** | Terjadi kendala atau exception internal di sisi backend/server | `resources/views/errors/500.blade.php` |

> **Catatan Pengujian:**  
> Untuk melihat langsung tampilan masing-masing error di browser saat development, buka URL:
> - `http://127.0.0.1:8000/dev/error/403`
> - `http://127.0.0.1:8000/dev/error/404`
> - `http://127.0.0.1:8000/dev/error/419`
> - `http://127.0.0.1:8000/dev/error/500`

---

### 2. Komponen Notifikasi Global (`<x-flash-message />`)
Komponen ini otomatis membaca flash session dari controller dan error form:
- `session('success')` -> Menampilkan banner sukses hijau (Emerald)
- `session('error')` -> Menampilkan banner error merah (Rose)
- `session('warning')` -> Menampilkan banner peringatan kuning (Amber)
- `session('info')` -> Menampilkan banner informasi biru (Sky)
- `$errors->any()` -> Menampilkan ringkasan error validasi jika ada form yang gagal disubmit

**Cara Pakai di File Blade:**
Cukup letakkan satu baris ini di atas form atau halaman:
```blade
<x-flash-message />
```

---

### 3. Komponen Pesan Error Input Form (`<x-input-error />`)
Gunakan komponen ini di bawah elemen `<input>`, `<select>`, atau `<textarea>` agar pesan kesalahan dari validasi backend muncul rapi di bawah masing-masing kolom input:

**Cara Pakai:**
```blade
<div>
    <label class="text-xs font-bold text-slate-700">Nama Produk</label>
    <input type="text" name="nama" value="{{ old('nama') }}" class="..." />
    
    <!-- Pesan error validasi otomatis dari backend -->
    <x-input-error for="nama" />
</div>
```

---

### 4. Komponen Alert Manual (`<x-alert />`)
Jika ingin menampilkan kotak alert custom di halaman tertentu:

```blade
<x-alert type="success" title="Pembayaran Berhasil" message="Pesanan Anda sedang diproses oleh barista." />
<x-alert type="error" title="Gagal" message="Stok kopi tidak mencukupi untuk jumlah yang diminta." />
<x-alert type="warning" title="Perhatian" message="Pesanan akan dibatalkan otomatis jika belum dibayar dalam 15 menit." />
<x-alert type="info" message="Lapak Kopi Tiga Titik buka setiap hari pukul 16.00 - 23.00 WIB." />
```

---

*Dibuat untuk memenuhi spesifikasi task PPL-24 (Sprint 2).*

