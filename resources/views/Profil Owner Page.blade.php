<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil Owner - Kopi Tiga Titik</title>

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
        <a href="{{ url('/owner/dashboard') }}" class="w-9 h-9 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-800 transition-colors">
            <span class="material-symbols-outlined text-xl">arrow_back</span>
        </a>
        <h1 class="text-base font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Profil Owner</h1>
        <div class="w-9"></div>
    </div>

    <!-- Blue Brand Strip -->
    <div class="bg-blue-900 text-white px-4 py-3 flex justify-between items-center text-xs">
        <div class="flex items-center gap-1.5 font-bold uppercase tracking-wider text-[11px]">
            <span>OWNER</span>
        </div>
        <div class="text-[11px] text-blue-200">Kopi Tiga Titik</div>
    </div>

    <!-- Main Content -->
    <div class="px-4 -mt-3 flex flex-col gap-3.5">

        <!-- Owner Header Card -->
        <div class="p-5 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col items-center gap-2 text-center">
            <div class="w-20 h-20 rounded-full bg-blue-900 text-white text-3xl font-extrabold flex items-center justify-center font-['Plus_Jakarta_Sans'] shadow-md">
                F
            </div>
            <h2 class="text-lg font-bold text-slate-900 font-['Plus_Jakarta_Sans'] mt-1">Farrel</h2>
            <div class="px-3 py-1 bg-blue-50 text-blue-900 rounded-full text-xs font-semibold flex items-center gap-1.5 border border-blue-100">
                <span class="material-symbols-outlined text-sm">mail</span>
                <span>owner1@gmail.com</span>
                <span class="material-symbols-outlined text-xs cursor-pointer hover:opacity-75">content_copy</span>
            </div>
            <p class="text-xs text-slate-500 font-medium">Pemilik Usaha Kopi Tiga Titik</p>
        </div>

        <!-- Pusat Operasional Lapak & Produksi -->
        <div class="p-4 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col gap-3">
            <div class="flex items-center gap-1.5 font-bold text-slate-900 text-xs">
                <span class="material-symbols-outlined text-blue-900 text-base">storefront</span>
                <span>Pusat Operasional Lapak & Produksi</span>
            </div>

            <!-- Status Buka -->
            <div class="p-2.5 bg-emerald-50 rounded-xl border border-emerald-200 flex items-center gap-2 text-xs font-bold text-emerald-800">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Lapak Buka (15.00 - 18.00 WIB)</span>
            </div>

            <!-- Dapur Produksi -->
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-900 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-lg">local_cafe</span>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-900">Pusat Produksi & Dapur</h4>
                    <p class="text-[11px] text-slate-600 mt-0.5">Dsn. Daleman, Ds. Japan, Kec. Sooko, Mojokerto</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">Pusat seduh & batching delivery online</p>
                </div>
            </div>

            <!-- Lapak Mangkal -->
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-lg">storefront</span>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-900">Lapak Mangkal Street Coffee</h4>
                    <p class="text-[11px] text-slate-600 mt-0.5">Jl. Benteng Pancasila (Depan Taman Sentanan)</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">Titik temu pelanggan offline & take-away</p>
                </div>
            </div>
        </div>

        <!-- Form Informasi Akun Owner -->
        <div class="p-4 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col gap-3">
            <div>
                <div class="flex items-center gap-1.5 font-bold text-slate-900 text-xs">
                    <span class="material-symbols-outlined text-blue-900 text-base">badge</span>
                    <span>Informasi Akun Owner</span>
                </div>
                <p class="text-[11px] text-slate-500 mt-0.5">Perbarui data login dan kredensial pengelola</p>
            </div>

            <div class="flex flex-col gap-2.5">
                <div>
                    <label class="text-[11px] font-semibold text-slate-700 block mb-1">Nama Lengkap Owner</label>
                    <div class="flex items-center gap-2 p-2.5 bg-slate-50 border border-slate-200 rounded-xl">
                        <span class="material-symbols-outlined text-slate-400 text-base">person</span>
                        <input type="text" value="Farrel" class="bg-transparent text-xs text-slate-900 font-medium flex-1 focus:outline-none" />
                        <span class="material-symbols-outlined text-slate-400 text-base cursor-pointer">edit</span>
                    </div>
                </div>

                <div>
                    <label class="text-[11px] font-semibold text-slate-700 block mb-1">Email Utama</label>
                    <div class="flex items-center gap-2 p-2.5 bg-slate-100 border border-slate-200 rounded-xl text-slate-500">
                        <span class="material-symbols-outlined text-slate-400 text-base">mail</span>
                        <input type="email" value="owner1@gmail.com" readonly class="bg-transparent text-xs text-slate-600 font-medium flex-1 focus:outline-none cursor-not-allowed" />
                    </div>
                </div>

                <div>
                    <label class="text-[11px] font-semibold text-slate-700 block mb-1">Kata Sandi Baru</label>
                    <div class="flex items-center gap-2 p-2.5 bg-slate-50 border border-slate-200 rounded-xl">
                        <span class="material-symbols-outlined text-slate-400 text-base">lock</span>
                        <input type="password" placeholder="Masukkan kata sandi baru (minimal 8 karakter)" class="bg-transparent text-xs text-slate-900 flex-1 focus:outline-none placeholder:text-slate-400" />
                        <span class="material-symbols-outlined text-slate-400 text-base cursor-pointer">visibility</span>
                    </div>
                </div>

                <div>
                    <label class="text-[11px] font-semibold text-slate-700 block mb-1">Ulangi Kata Sandi Baru</label>
                    <div class="flex items-center gap-2 p-2.5 bg-slate-50 border border-slate-200 rounded-xl">
                        <span class="material-symbols-outlined text-slate-400 text-base">lock_reset</span>
                        <input type="password" placeholder="Ketik ulang kata sandi" class="bg-transparent text-xs text-slate-900 flex-1 focus:outline-none placeholder:text-slate-400" />
                    </div>
                </div>

                <button class="w-full py-3 bg-blue-900 hover:bg-blue-800 text-white rounded-xl font-bold text-xs flex justify-center items-center gap-2 shadow-sm transition-colors mt-1">
                    <span class="material-symbols-outlined text-base">save</span>
                    <span>Simpan Perubahan Data</span>
                </button>
            </div>
        </div>

        <!-- Logout Button -->
        <a href="{{ url('/login-owner') }}" class="w-full py-3 bg-white hover:bg-red-50 border border-red-200 text-red-600 rounded-2xl font-bold text-xs flex justify-center items-center gap-2 shadow-xs transition-colors">
            <span class="material-symbols-outlined text-base">logout</span>
            <span>Keluar dari Akun (Logout)</span>
        </a>

    </div>

</div>
</body>
</html>
