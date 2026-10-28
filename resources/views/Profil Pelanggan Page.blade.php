<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil Pelanggan - Kopi Tiga Titik</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Tailwind CSS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-screen flex justify-center items-start py-6 antialiased font-sans">
<div class="w-full max-w-96 min-h-screen bg-slate-50 shadow-2xl relative flex flex-col justify-start pb-24">

    <!-- Top Header -->
    <div class="bg-white px-4 py-3 border-b border-slate-100 shadow-sm flex items-center justify-between sticky top-0 z-30">
        <a href="{{ url('/landing') }}" class="w-9 h-9 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-800 transition-colors">
            <span class="material-symbols-outlined text-xl">arrow_back</span>
        </a>
        <h1 class="text-base font-bold text-slate-900 font-['Plus_Jakarta_Sans']">My Account</h1>
        <div class="w-9"></div>
    </div>

    <!-- Blue Brand Strip -->
    <div class="bg-blue-900 text-white px-4 py-3 flex justify-between items-center text-xs">
        <div class="flex items-center gap-1.5 font-bold uppercase tracking-wider text-[11px]">
            <span class="w-2 h-2 rounded-full bg-blue-400"></span>
            <span>PELANGGAN</span>
        </div>
        <div class="text-[11px] text-blue-200">Kopi Tiga Titik Mojokerto</div>
    </div>

    <!-- Main Content -->
    <div class="px-4 -mt-3 flex flex-col gap-3.5">

        <!-- User Info Card -->
        <div class="p-4 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-14 h-14 rounded-full bg-blue-900 text-white text-xl font-extrabold flex items-center justify-center font-['Plus_Jakarta_Sans'] shadow-md">
                        N
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Nabil</h2>
                        <span class="inline-block mt-0.5 px-2 py-0.5 bg-emerald-50 text-emerald-700 font-semibold text-[11px] rounded-full border border-emerald-200">+62 812-3849-9210</span>
                    </div>
                </div>
                <button class="px-3 py-1 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 rounded-xl text-xs font-semibold flex items-center gap-1 shadow-xs transition-colors">
                    <span class="material-symbols-outlined text-xs">edit</span>
                    <span>Ubah</span>
                </button>
            </div>
            <div class="pt-2 border-t border-slate-100 flex items-center gap-2 text-xs text-slate-600">
                <span class="material-symbols-outlined text-sm text-slate-400">mail</span>
                <span>nabil@gmail.com</span>
            </div>
        </div>

        <!-- Alamat Pengiriman Utama -->
        <div class="flex flex-col gap-2">
            <div class="flex justify-between items-center text-xs">
                <h3 class="font-bold text-slate-900">Alamat Pengiriman Utama</h3>
                <button class="font-bold text-blue-900 hover:underline text-[11px]">Ubah</button>
            </div>

            <div class="p-3.5 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col gap-2.5">
                <div class="flex justify-between items-center text-xs">
                    <div class="flex items-center gap-1.5">
                        <div class="w-6 h-6 rounded-full bg-blue-50 text-blue-900 flex items-center justify-center">
                            <span class="material-symbols-outlined text-xs">location_on</span>
                        </div>
                        <span class="px-2 py-0.5 bg-blue-900 text-white font-bold text-[10px] rounded-md">Rumah (Utama)</span>
                    </div>
                    <span class="text-slate-400 text-[11px]">Mojokerto</span>
                </div>
                <p class="text-xs text-slate-700 font-medium leading-relaxed">
                    Jl. Hayam Wuruk No. 42, RT 02/RW 01, Magersari, Mojokerto
                </p>
                <div class="p-2 bg-blue-50/70 rounded-xl border border-blue-100 flex items-center gap-1.5 text-xs text-blue-900">
                    <span class="material-symbols-outlined text-xs text-blue-700">push_pin</span>
                    <span><strong>Patokan:</strong> Pagar hitam depan toko kelontong Bu Sri</span>
                </div>
            </div>
        </div>

        <!-- Aktivitas & Bantuan -->
        <div class="flex flex-col gap-2">
            <h3 class="font-bold text-slate-900 text-xs">Aktivitas & Bantuan</h3>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden divide-y divide-slate-100">
                <a href="{{ url('/riwayat-pesanan') }}" class="p-3.5 flex items-center justify-between hover:bg-slate-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-900 flex items-center justify-center">
                            <span class="material-symbols-outlined text-lg">receipt_long</span>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900">Riwayat Pesanan Saya</div>
                            <div class="text-[11px] text-slate-500">Lihat pesanan aktif & transaksi lalu</div>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-slate-400 text-sm">chevron_right</span>
                </a>

                <div class="p-3.5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                            <span class="material-symbols-outlined text-lg">star</span>
                        </div>
                        <div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs font-bold text-slate-900">Varian Kopi Favorit</span>
                                <span class="px-1.5 py-0.2 bg-amber-100 text-amber-800 text-[9px] font-bold rounded">2 Menu</span>
                            </div>
                            <div class="text-[11px] text-slate-500">Titik Temu & Titik Hitam</div>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-slate-400 text-sm">chevron_right</span>
                </div>
            </div>
        </div>

        <!-- Logout Button -->
        <a href="{{ url('/login-pembeli') }}" class="w-full py-3 bg-white hover:bg-red-50 border border-red-200 text-red-600 rounded-2xl font-bold text-xs flex justify-center items-center gap-2 shadow-xs transition-colors mt-1">
            <span class="material-symbols-outlined text-base">logout</span>
            <span>Keluar dari Akun (Logout)</span>
        </a>

        <!-- Footer Notice -->
        <div class="text-center text-[10px] text-slate-400 py-1">
            Kopi Tiga Titik • Seduhan Khas Mojokerto
        </div>

    </div>

    <!-- Global Bottom Navigation Bar -->
    <div class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-96 bg-white border-t border-slate-200 py-1.5 px-6 flex justify-between items-center z-50 shadow-[0_-2px_10px_rgba(0,0,0,0.05)]">
        <a href="{{ url('/landing') }}" class="flex flex-col items-center text-gray-400 hover:text-blue-800 transition-colors">
            <span class="material-symbols-outlined text-2xl">home</span>
            <span class="text-[10px] font-medium font-['Inter']">Beranda</span>
        </a>
        <a href="{{ url('/katalog') }}" class="flex flex-col items-center text-gray-400 hover:text-blue-800 transition-colors">
            <span class="material-symbols-outlined text-2xl">coffee</span>
            <span class="text-[10px] font-medium font-['Inter']">Menu</span>
        </a>
        <a href="{{ url('/riwayat-pesanan') }}" class="flex flex-col items-center text-gray-400 hover:text-blue-800 transition-colors">
            <span class="material-symbols-outlined text-2xl">receipt_long</span>
            <span class="text-[10px] font-medium font-['Inter']">Pesanan</span>
        </a>
        <a href="{{ url('/profil-pelanggan') }}" class="flex flex-col items-center text-blue-800">
            <span class="material-symbols-outlined text-2xl font-bold">person</span>
            <span class="text-[10px] font-bold font-['Inter']">Akun</span>
        </a>
    </div>

</div>
</body>
</html>
