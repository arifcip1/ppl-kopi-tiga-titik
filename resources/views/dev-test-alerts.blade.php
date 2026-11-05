<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laboratorium Uji Komponen Pesan Validasi & Alert (PPL-24)</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Tailwind CSS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 antialiased font-sans p-4 sm:p-8">

    <div class="max-w-3xl mx-auto flex flex-col gap-6">

        <!-- Header Lab -->
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 bg-blue-100 text-blue-900 font-bold text-xs rounded-full">PPL-24 TEST LAB</span>
                    <span class="text-xs text-slate-400">Sprint 2</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 font-['Plus_Jakarta_Sans']">
                    Uji Coba Komponen Alert & Validasi
                </h1>
                <p class="text-xs text-slate-500 mt-1">Halaman khusus untuk menguji tampilan dan interaktivitas komponen sebelum digabung.</p>
            </div>
            <a href="{{ url('/') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl flex items-center gap-1.5 self-start sm:self-auto transition-colors">
                <span class="material-symbols-outlined text-base">home</span>
                <span>Beranda</span>
            </a>
        </div>

        <!-- 1. Simulasi Flash Message Session -->
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 flex flex-col gap-4">
            <h2 class="text-base font-bold text-slate-900 font-['Plus_Jakarta_Sans'] flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>1. Komponen Flash Message Otomatis (&lt;x-flash-message /&gt;)</span>
            </h2>
            <p class="text-xs text-slate-500">Klik salah satu tombol di bawah untuk menyimulasikan respon dari Controller Laravel:</p>

            <div class="flex flex-wrap gap-2">
                <a href="{{ url('/dev/trigger-flash?type=success') }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs">
                    Simulasi session('success')
                </a>
                <a href="{{ url('/dev/trigger-flash?type=error') }}" class="px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs">
                    Simulasi session('error')
                </a>
                <a href="{{ url('/dev/trigger-flash?type=warning') }}" class="px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold transition-all shadow-xs">
                    Simulasi session('warning')
                </a>
                <a href="{{ url('/dev/trigger-flash?type=validation') }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-all shadow-xs">
                    Simulasi $errors Validasi Form
                </a>
            </div>

            <!-- Area Tampil Flash Message -->
            <div class="mt-2 pt-4 border-t border-slate-100">
                <p class="text-[11px] font-bold text-slate-400 mb-2 uppercase tracking-wider">Hasil Render Komponen:</p>
                <x-flash-message />
            </div>
        </div>

        <!-- 2. Variasi Komponen Alert Manual -->
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 flex flex-col gap-4">
            <h2 class="text-base font-bold text-slate-900 font-['Plus_Jakarta_Sans'] flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                <span>2. Variasi Box Alert (&lt;x-alert /&gt;) dengan Tombol Tutup (X)</span>
            </h2>
            <p class="text-xs text-slate-500">Setiap kotak alert memiliki tombol silang (x) di pojok kanan atas yang dapat diklik untuk menutup notifikasi:</p>

            <div class="flex flex-col gap-3">
                <x-alert type="success" title="Pesanan Berhasil Disimpan" message="Pesanan kopi #KTT-104 telah masuk ke antrean barista lapak." />
                
                <x-alert type="error" title="Validasi Gagal" message="Format nomor WhatsApp tidak valid atau stok botol sudah habis." />
                
                <x-alert type="warning" title="Peringatan Stok Rendah" message="Stok varian Titik Manja sisa 3 botol, segera lakukan brewing restok!" />
                
                <x-alert type="info" title="Info Jam Operasional Lapak" message="Lapak Jalan Kuwung buka setiap sore mulai pukul 15.00 - 18.00 WIB." />
            </div>
        </div>

        <!-- 3. Komponen Pesan Error Input Form -->
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 flex flex-col gap-4">
            <h2 class="text-base font-bold text-slate-900 font-['Plus_Jakarta_Sans'] flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                <span>3. Pesan Error Bawah Kolom Input (&lt;x-input-error /&gt;)</span>
            </h2>
            <p class="text-xs text-slate-500">Komponen ini diletakkan tepat di bawah kolom input form agar user tahu kolom mana yang keliru:</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-200/80">
                <!-- Contoh Input 1 -->
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Nama Produk Kopi</label>
                    <input type="text" value="" class="w-full px-3.5 py-2 bg-white border border-rose-300 rounded-xl text-xs text-slate-800 focus:outline-none ring-2 ring-rose-100" />
                    <!-- Komponen Pesan Error -->
                    <x-input-error messages="Kolom nama produk kopi wajib diisi minimal 3 karakter." />
                </div>

                <!-- Contoh Input 2 -->
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Harga Jual (Rp)</label>
                    <input type="text" value="-5000" class="w-full px-3.5 py-2 bg-white border border-rose-300 rounded-xl text-xs text-slate-800 focus:outline-none ring-2 ring-rose-100" />
                    <!-- Komponen Pesan Error -->
                    <x-input-error messages="Harga jual tidak boleh kurang dari Rp 1.000." />
                </div>
            </div>
        </div>

    </div>

</body>
</html>

