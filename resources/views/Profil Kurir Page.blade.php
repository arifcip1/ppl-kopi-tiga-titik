<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil Kurir - Kopi Tiga Titik</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Tailwind CSS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-screen flex justify-center items-start py-6 antialiased font-sans">
<div class="w-full max-w-96 min-h-screen bg-slate-50 shadow-2xl relative flex flex-col justify-start pb-12">

    <!-- Top Header -->
    <div class="bg-white px-4 py-3 border-b border-slate-100 shadow-sm flex items-center justify-between sticky top-0 z-30">
        <a href="{{ url('/kurir/tugas-baru') }}" class="w-9 h-9 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-800 transition-colors">
            <span class="material-symbols-outlined text-xl">arrow_back</span>
        </a>
        <h1 class="text-base font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Profil Kurir</h1>
        <div class="w-9"></div>
    </div>

    <!-- Blue Brand Strip -->
    <div class="bg-blue-900 text-white px-4 py-3 flex justify-between items-center text-xs">
        <div class="flex items-center gap-1.5 font-bold uppercase tracking-wider text-[11px]">
            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
            <span>KURIR PENGANTARAN</span>
        </div>
        <div class="text-[11px] text-blue-200">Kopi Tiga Titik Mojokerto</div>
    </div>

    <!-- Main Content -->
    <div class="px-4 -mt-3 flex flex-col gap-3.5">

        <!-- Kurir Header Card -->
        <div class="p-4 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col gap-3">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="w-14 h-14 rounded-full bg-blue-900 text-white text-xl font-extrabold flex items-center justify-center font-['Plus_Jakarta_Sans'] shadow-md">
                        I
                    </div>
                    <span class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center border-2 border-white shadow-xs">
                        <span class="material-symbols-outlined text-xs">moped</span>
                    </span>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Ilham Ramadhan</h2>
                    <div class="flex items-center gap-1 text-slate-500 text-xs mt-0.5">
                        <span class="material-symbols-outlined text-xs">mail</span>
                        <span>kurir1ilham@gmail.com</span>
                    </div>
                </div>
            </div>
            <div class="p-2.5 bg-blue-50/70 rounded-xl border border-blue-100 text-xs text-blue-900 font-semibold">
                WhatsApp: +62 812-3456-7890
            </div>
        </div>

        <!-- Pengantaran Hari Ini -->
        <div class="p-4 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col gap-3">
            <h3 class="font-bold text-slate-900 text-xs">Pengantaran Hari Ini</h3>

            <div class="p-3 bg-blue-50/60 rounded-xl border border-blue-100 flex justify-between items-center">
                <div>
                    <div class="text-[11px] text-slate-500">Pengantaran</div>
                    <div class="text-xl font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">142 Pesanan</div>
                    <div class="text-[10px] text-slate-400">Total</div>
                </div>
                <span class="material-symbols-outlined text-blue-900 text-2xl">verified</span>
            </div>

            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-900 flex items-center justify-center">
                    <span class="material-symbols-outlined text-xl">two_wheeler</span>
                </div>
                <div>
                    <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">KENDARAAN OPERASIONAL</div>
                    <div class="text-xs font-bold text-slate-900">Motor: Honda Beat Hitam</div>
                    <div class="text-xs font-extrabold text-blue-900">Plat S 4821 NA</div>
                </div>
            </div>
        </div>

        <!-- Form Kelola Informasi Data Diri -->
        <div class="p-4 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col gap-3">
            <div>
                <h3 class="font-bold text-slate-900 text-xs">Kelola Informasi Data Diri</h3>
                <p class="text-[11px] text-slate-500">Perbarui profil dan kontak koordinasi kurir</p>
            </div>

            <div class="flex flex-col gap-2.5">
                <div>
                    <label class="text-[11px] font-semibold text-slate-700 block mb-1">Nama Kurir</label>
                    <div class="flex items-center gap-2 p-2.5 bg-slate-50 border border-slate-200 rounded-xl">
                        <span class="material-symbols-outlined text-slate-400 text-base">person</span>
                        <input type="text" value="Ilham Ramadhan" class="bg-transparent text-xs text-slate-900 font-medium flex-1 focus:outline-none" />
                    </div>
                </div>

                <div>
                    <label class="text-[11px] font-semibold text-slate-700 block mb-1">Alamat Email</label>
                    <div class="flex items-center gap-2 p-2.5 bg-slate-50 border border-slate-200 rounded-xl">
                        <span class="material-symbols-outlined text-slate-400 text-base">mail</span>
                        <input type="email" value="kurir1ilham@gmail.com" class="bg-transparent text-xs text-slate-900 font-medium flex-1 focus:outline-none" />
                    </div>
                </div>

                <div>
                    <label class="text-[11px] font-semibold text-slate-700 block mb-1">Nomor WhatsApp (Koordinasi Pelanggan)</label>
                    <div class="flex items-center gap-2 p-2.5 bg-slate-50 border border-slate-200 rounded-xl">
                        <span class="material-symbols-outlined text-slate-400 text-base">call</span>
                        <input type="tel" value="+62 812-3456-7890" class="bg-transparent text-xs text-slate-900 font-medium flex-1 focus:outline-none" />
                    </div>
                </div>

                <div>
                    <label class="text-[11px] font-semibold text-slate-700 block mb-1">Kata Sandi Baru</label>
                    <div class="flex items-center gap-2 p-2.5 bg-slate-50 border border-slate-200 rounded-xl">
                        <span class="material-symbols-outlined text-slate-400 text-base">lock</span>
                        <input type="password" placeholder="Masukkan kata sandi baru jika ingin diubah" class="bg-transparent text-xs text-slate-900 flex-1 focus:outline-none placeholder:text-slate-400" />
                        <span class="material-symbols-outlined text-slate-400 text-base cursor-pointer">visibility</span>
                    </div>
                </div>

                <button class="w-full py-3 bg-blue-900 hover:bg-blue-800 text-white rounded-xl font-bold text-xs flex justify-center items-center gap-2 shadow-sm transition-colors mt-1">
                    <span class="material-symbols-outlined text-base">save</span>
                    <span>Simpan Perubahan Data</span>
                </button>
            </div>
        </div>

        <!-- Logout Button -->
        <a href="{{ url('/login-kurir') }}" class="w-full py-3 bg-white hover:bg-red-50 border border-red-200 text-red-600 rounded-2xl font-bold text-xs flex justify-center items-center gap-2 shadow-xs transition-colors">
            <span class="material-symbols-outlined text-base">logout</span>
            <span>Keluar dari Akun (Logout)</span>
        </a>

    </div>

</div>
</body>
</html>
