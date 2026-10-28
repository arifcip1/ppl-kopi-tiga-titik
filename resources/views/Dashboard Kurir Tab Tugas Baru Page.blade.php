<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Kurir - Tugas Baru - Kopi Tiga Titik</title>

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
        <div class="flex items-center gap-2.5">
            <img class="w-8 h-8 rounded-lg object-contain shadow-xs" src="{{ asset('images/logo.png') }}" alt="Logo" />
            <span class="font-extrabold text-blue-900 font-['Plus_Jakarta_Sans'] text-sm tracking-tight">Kopi Tiga Titik</span>
        </div>
        <a href="{{ url('/kurir/profil') }}" class="flex flex-col items-center text-slate-700 hover:text-blue-900 transition-colors">
            <span class="material-symbols-outlined text-2xl">person</span>
            <span class="text-[9px] font-bold">Akun</span>
        </a>
    </div>

    <!-- Blue Status Header Card -->
    <div class="bg-blue-900 text-white px-4 pt-4 pb-4 rounded-b-3xl shadow-md flex flex-col gap-3">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center text-white border border-white/30 shadow-inner">
                        <span class="material-symbols-outlined text-2xl">person</span>
                    </div>
                    <span class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-white text-blue-900 flex items-center justify-center shadow-xs">
                        <span class="material-symbols-outlined text-[11px]">two_wheeler</span>
                    </span>
                </div>
                <div>
                    <h2 class="text-sm font-bold font-['Plus_Jakarta_Sans'] leading-tight">Mas Ilham (Kurir 1)</h2>
                    <div class="text-[11px] text-blue-200 flex items-center gap-1.5 mt-0.5">
                        <span>Honda Vario Hitam</span>
                        <span class="px-1.5 py-0.2 bg-white/10 rounded text-[10px] text-white font-mono">W 4821 NA</span>
                    </div>
                </div>
            </div>
            <div class="text-right">
                <span class="px-2.5 py-1 bg-white/20 backdrop-blur-xs rounded-full text-[10px] font-bold uppercase tracking-wider flex items-center gap-1 border border-white/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    <span>SEDANG BERTUGAS</span>
                </span>
                <div class="text-[10px] text-blue-200 mt-1">Shift: 15:00 - 18:00</div>
            </div>
        </div>

        <div class="p-2 bg-blue-800/80 rounded-xl border border-white/20 flex items-center gap-2 text-xs">
            <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></span>
            <span><strong>2 Tugas Baru</strong> Perlu Diantar Segera</span>
        </div>
    </div>

    <!-- Main Content -->
    <div class="px-4 pt-3 flex flex-col gap-3.5">

        <!-- Tabs -->
        <div class="p-1 bg-slate-200/70 rounded-xl flex items-center gap-1 w-60">
            <a href="{{ url('/kurir/tugas-baru') }}" class="flex-1 py-1.5 bg-blue-900 text-white rounded-lg text-xs font-bold text-center shadow-sm">
                Tugas Baru (2)
            </a>
            <a href="{{ url('/kurir/selesai') }}" class="flex-1 py-1.5 text-slate-600 hover:text-slate-900 rounded-lg text-xs font-medium text-center transition-colors">
                Selesai (6)
            </a>
        </div>

        <!-- Task 1 Card (Active Delivery) -->
        <div class="p-4 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col gap-3">
            <div class="flex justify-between items-center text-xs">
                <div class="flex items-center gap-2">
                    <h3 class="font-bold text-slate-900">Pesanan</h3>
                    <span class="text-slate-400 text-[11px]">15:20 WIB</span>
                </div>
                <span class="px-2.5 py-0.5 bg-blue-50 text-blue-900 font-bold text-[10px] rounded-full border border-blue-200">Siap Diantar</span>
            </div>

            <!-- Recipient Info -->
            <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-900 font-bold text-xs flex items-center justify-center">
                        DS
                    </div>
                    <div>
                        <div class="text-xs font-bold text-slate-900">Dimas Suryo</div>
                        <div class="text-[11px] text-slate-500">0812 3849 9210</div>
                    </div>
                </div>
                <a href="tel:081238499210" class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-blue-900 flex items-center justify-center hover:bg-slate-50">
                    <span class="material-symbols-outlined text-base">call</span>
                </a>
            </div>

            <!-- Destination Address Box -->
            <div class="p-3 bg-blue-50/50 rounded-xl border border-blue-100 flex flex-col gap-1.5">
                <div class="flex items-start gap-1.5 text-xs">
                    <span class="material-symbols-outlined text-blue-900 text-base">location_on</span>
                    <div>
                        <div class="font-bold text-slate-900">Jl. Hayam Wuruk No. 42, Magersari, Mojokerto</div>
                        <div class="p-1.5 bg-white rounded-lg border border-slate-200 text-[11px] text-slate-600 mt-1 flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs text-rose-500">push_pin</span>
                            <span><strong>Patokan:</strong> Pagar hitam depan toko kelontong Bu Sri</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-1 text-[11px] text-blue-900 font-semibold pt-1">
                    <span class="material-symbols-outlined text-xs">near_me</span>
                    <span>3.2 km dari Lapak Benteng Pancasila • ± 8-10 Menit</span>
                </div>
            </div>

            <!-- Drink items box -->
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex flex-col gap-1.5 text-xs">
                <div class="flex justify-between items-center">
                    <div class="font-bold text-slate-900 flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm text-blue-900">storefront</span>
                        <span>Daftar Minuman (3 Botol)</span>
                    </div>
                    <span class="px-2 py-0.2 bg-blue-100 text-blue-900 text-[9px] font-bold rounded">Cooler Bag #2</span>
                </div>
                <div class="space-y-1 text-[11px] pt-1">
                    <div class="flex justify-between text-slate-700">
                        <span>• 2x Titik Temu (Aren 250ml)</span>
                        <span class="text-slate-400">Chilled</span>
                    </div>
                    <div class="flex justify-between text-slate-700">
                        <span>• 1x Titik Hitam (Cold Brew 250ml)</span>
                        <span class="text-slate-400">Less Sugar</span>
                    </div>
                </div>
                <div class="p-1.5 bg-white rounded border border-slate-200 text-[10px] text-slate-500 italic mt-0.5">
                    Kemasan botol kaca
                </div>
            </div>

            <!-- Payment Status -->
            <div class="flex justify-between items-center text-xs pt-1">
                <div>
                    <div class="text-[10px] text-slate-400 font-medium">Total Tagihan Pesanan</div>
                    <div class="text-base font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">Rp 28.000</div>
                </div>
                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 font-bold text-xs rounded-full border border-emerald-200 flex items-center gap-1">
                    <span class="material-symbols-outlined text-xs">check_circle</span>
                    <span>QRIS LUNAS</span>
                </span>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col gap-2 pt-1">
                <button class="w-full py-2.5 bg-blue-900 hover:bg-blue-800 text-white rounded-xl font-bold text-xs flex justify-center items-center gap-1.5 shadow-sm transition-colors">
                    <span class="material-symbols-outlined text-sm">moped</span>
                    <span>Kirim ke tujuan</span>
                </button>
                <button class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-medium text-xs flex justify-center items-center gap-1.5 transition-colors">
                    <span class="material-symbols-outlined text-sm">check_circle</span>
                    <span>Tandai Selesai (Diterima Pembeli)</span>
                </button>
            </div>
        </div>

        <!-- Antrean Berikutnya Card -->
        <div class="p-3.5 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col gap-2">
            <div class="flex justify-between items-center text-xs">
                <div class="flex items-center gap-1.5 font-bold text-slate-900">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span>Antrean Berikutnya</span>
                </div>
                <span class="px-2 py-0.5 bg-amber-50 text-amber-700 font-semibold text-[10px] rounded-full border border-amber-200">Menunggu Antrean</span>
            </div>
            <div class="flex justify-between items-center text-xs pt-1">
                <div>
                    <div class="font-bold text-slate-800">Jl. Majapahit No. 12 • Anisah</div>
                    <div class="text-[11px] text-slate-500">2 Botol (Titik Manis & Titik Hitam)</div>
                </div>
                <div class="text-right">
                    <div class="font-bold text-blue-900 text-xs">1.8 km</div>
                    <div class="text-[10px] text-slate-400">Lunas (Gopay)</div>
                </div>
            </div>
        </div>

        <!-- Performa Kurir Hari Ini Card -->
        <div class="p-3.5 bg-blue-50/50 rounded-2xl border border-blue-100 flex flex-col gap-2.5">
            <div class="flex justify-between items-center text-xs">
                <div class="flex items-center gap-1.5 font-bold text-blue-900">
                    <span class="material-symbols-outlined text-base">bar_chart</span>
                    <span>Performa Kurir Hari Ini</span>
                </div>
                <span class="text-slate-400 text-[10px]">24 Mei 2024</span>
            </div>
            <div class="grid grid-cols-2 gap-2 text-center">
                <div class="p-3 bg-white rounded-xl border border-slate-200">
                    <div class="text-xl font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">8</div>
                    <div class="text-[11px] text-slate-500">Botol Terantar</div>
                </div>
                <div class="p-3 bg-white rounded-xl border border-slate-200">
                    <div class="text-xl font-extrabold text-emerald-600 font-['Plus_Jakarta_Sans']">3</div>
                    <div class="text-[11px] text-slate-500">Antaran Sukses</div>
                </div>
            </div>
        </div>

    </div>

</div>
</body>
</html>
