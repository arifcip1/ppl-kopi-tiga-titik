<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Kurir Tab Selesai - Kopi Tiga Titik</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=AR+One+Sans:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Tailwind CSS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-screen flex justify-center items-start py-6 antialiased">
<div class="w-96 relative bg-slate-50 shadow-2xl inline-flex flex-col justify-start items-start pb-12 overflow-hidden border border-slate-200">

    <!-- Top Kurir Header -->
    <div class="self-stretch bg-white px-4 py-3 border-b border-slate-100 flex flex-col gap-2 shadow-xs">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="size-11 bg-blue-800 rounded-full flex items-center justify-center text-white font-bold text-lg font-['Plus_Jakarta_Sans'] shadow-xs">
                        I
                    </div>
                    <div class="size-3 bg-emerald-500 rounded-full border-2 border-white absolute bottom-0 right-0"></div>
                </div>
                <div class="flex flex-col">
                    <div class="text-slate-900 font-bold text-sm font-['Plus_Jakarta_Sans']">Ilham</div>
                    <div class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-full text-[10px] font-bold w-fit">Kurir 1 - Aktif</div>
                </div>
            </div>
            <a href="{{ url('/kurir/profil') }}" class="size-9 rounded-full hover:bg-slate-100 flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined text-gray-700 text-xl">person</span>
            </a>
        </div>
        <div class="flex items-center gap-1.5 pt-1">
            <span class="size-2 bg-blue-700 rounded-full inline-block"></span>
            <span class="text-slate-700 text-xs font-bold font-['Plus_Jakarta_Sans'] tracking-wide">KOPI TIGA TITIK</span>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="self-stretch p-4 flex flex-col gap-4">

        <!-- Tabs Navigation -->
        <div class="w-full p-1 bg-slate-200/80 rounded-xl flex items-center">
            <a href="{{ url('/kurir/tugas-baru') }}" class="w-1/2 py-2 text-center text-xs font-bold text-slate-700 hover:text-blue-800 transition-colors">
                Tugas Baru (2)
            </a>
            <div class="w-1/2 py-2 text-center text-xs font-bold text-white bg-blue-800 rounded-lg shadow-sm">
                Selesai (6)
            </div>
        </div>

        <!-- Pencapaian Hari Ini Card -->
        <div class="bg-emerald-50/70 border border-emerald-200/80 rounded-2xl p-4 flex flex-col gap-3 shadow-xs">
            <div class="flex items-center gap-1.5 text-emerald-900 font-bold text-xs">
                <span class="material-symbols-outlined text-emerald-600 text-lg">verified</span>
                <span>Pencapaian Pengantaran Hari Ini</span>
            </div>
            <div class="grid grid-cols-2 divide-x divide-emerald-200/80 text-center py-1">
                <div class="flex flex-col items-center">
                    <span class="text-2xl font-extrabold text-emerald-950 font-['Plus_Jakarta_Sans']">8</span>
                    <span class="text-[11px] font-semibold text-emerald-700">Pesanan Sukses</span>
                </div>
                <div class="flex flex-col items-center">
                    <span class="text-2xl font-extrabold text-emerald-950 font-['Plus_Jakarta_Sans']">24</span>
                    <span class="text-[11px] font-semibold text-emerald-700">Total Botol</span>
                </div>
            </div>
            <div class="flex items-center gap-1.5 text-[10px] text-emerald-800 pt-2 border-t border-emerald-200/60 font-medium">
                <span class="material-symbols-outlined text-emerald-600 text-xs">auto_awesome</span>
                <span>Semua pesanan yang ditugaskan hari ini berhasil diantar.</span>
            </div>
        </div>

        <!-- Section Title with Date Filter -->
        <div class="flex items-center justify-between pt-1">
            <div class="flex flex-col">
                <h2 class="text-sm font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Riwayat Pengantaran Hari Ini</h2>
                <span class="text-[10px] text-gray-500 font-['Inter']">8 titik antar terselesaikan</span>
            </div>
            <div class="px-2.5 py-1 bg-white border border-slate-200 rounded-full text-xs font-semibold text-slate-700 flex items-center gap-1 shadow-xs cursor-pointer">
                <span class="material-symbols-outlined text-xs">calendar_today</span>
                <span class="text-[11px]">28 Okt 2026</span>
                <span class="material-symbols-outlined text-xs">expand_more</span>
            </div>
        </div>

        <!-- Deliveries List -->
        <div class="flex flex-col gap-3">

            <!-- Card 1 -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex flex-col gap-2.5">
                <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-full text-[10px] font-bold flex items-center gap-1">
                        <span class="size-1.5 bg-emerald-600 rounded-full"></span>
                        SELESAI DIANTAR
                    </span>
                    <span class="flex items-center gap-1 text-[11px] text-gray-500">
                        <span class="material-symbols-outlined text-xs">schedule</span>
                        14:35 WIB
                    </span>
                </div>
                <div>
                    <div class="text-sm font-bold text-slate-900">Kak Rian Pratama</div>
                    <div class="text-xs text-gray-500 font-mono">+62 812-3456-7890</div>
                </div>
                <div class="bg-slate-50 rounded-xl p-2.5 flex items-start gap-2 border border-slate-100">
                    <span class="material-symbols-outlined text-blue-800 text-base mt-0.5">location_on</span>
                    <span class="text-xs text-slate-700 leading-snug">Perum Griya Japan Asri Blok B-12, Kec. Sooko, Kab. Mojokerto</span>
                </div>
                <div class="flex items-center gap-2 text-xs text-slate-700">
                    <span class="material-symbols-outlined text-blue-800 text-sm">local_cafe</span>
                    <span class="font-medium">3 Botol (2x Titik Temu, 1x Titik Hitam 250ml)</span>
                </div>
                <div class="border-t border-slate-100 pt-2 flex items-center justify-between text-xs">
                    <span class="text-gray-500 text-[11px]">Metode Pembayaran</span>
                    <span class="text-emerald-700 font-bold flex items-center gap-1 text-[11px]">
                        <span class="material-symbols-outlined text-emerald-600 text-sm">check_circle</span>
                        Lunas (QRIS) - Rp 54.000
                    </span>
                </div>
                <div class="bg-emerald-50/50 rounded-lg p-2 text-[11px] text-slate-600 flex items-center gap-1.5 border border-emerald-100">
                    <span class="material-symbols-outlined text-emerald-600 text-xs">check</span>
                    <span>Diterima langsung oleh pemesan di teras depan</span>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex flex-col gap-2.5">
                <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-full text-[10px] font-bold flex items-center gap-1">
                        <span class="size-1.5 bg-emerald-600 rounded-full"></span>
                        SELESAI DIANTAR
                    </span>
                    <span class="flex items-center gap-1 text-[11px] text-gray-500">
                        <span class="material-symbols-outlined text-xs">schedule</span>
                        13:10 WIB
                    </span>
                </div>
                <div>
                    <div class="text-sm font-bold text-slate-900">Mbak Dinda Larasati</div>
                    <div class="text-xs text-gray-500 font-mono">+62 857-1122-3344</div>
                </div>
                <div class="bg-slate-50 rounded-xl p-2.5 flex items-start gap-2 border border-slate-100">
                    <span class="material-symbols-outlined text-blue-800 text-base mt-0.5">location_on</span>
                    <span class="text-xs text-slate-700 leading-snug">Jl. Hayam Wuruk No. 45, Kec. Magersari, Kota Mojokerto</span>
                </div>
                <div class="flex items-center gap-2 text-xs text-slate-700">
                    <span class="material-symbols-outlined text-blue-800 text-sm">local_cafe</span>
                    <span class="font-medium">2 Botol (1x Titik Manja, 1x Titik Jenuh 250ml)</span>
                </div>
                <div class="border-t border-slate-100 pt-2 flex items-center justify-between text-xs">
                    <span class="text-gray-500 text-[11px]">Metode Pembayaran</span>
                    <span class="text-emerald-700 font-bold flex items-center gap-1 text-[11px]">
                        <span class="material-symbols-outlined text-emerald-600 text-sm">check_circle</span>
                        Lunas (QRIS) - Rp 38.000
                    </span>
                </div>
                <div class="bg-emerald-50/50 rounded-lg p-2 text-[11px] text-slate-600 flex items-center gap-1.5 border border-emerald-100">
                    <span class="material-symbols-outlined text-emerald-600 text-xs">check</span>
                    <span>Dititipkan ke satpam pos depan</span>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex flex-col gap-2.5">
                <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-full text-[10px] font-bold flex items-center gap-1">
                        <span class="size-1.5 bg-emerald-600 rounded-full"></span>
                        SELESAI DIANTAR
                    </span>
                    <span class="flex items-center gap-1 text-[11px] text-gray-500">
                        <span class="material-symbols-outlined text-xs">schedule</span>
                        11:45 WIB
                    </span>
                </div>
                <div>
                    <div class="text-sm font-bold text-slate-900">Mas Bagus Satria</div>
                    <div class="text-xs text-gray-500 font-mono">+62 895-4433-2211</div>
                </div>
                <div class="bg-slate-50 rounded-xl p-2.5 flex items-start gap-2 border border-slate-100">
                    <span class="material-symbols-outlined text-blue-800 text-base mt-0.5">location_on</span>
                    <span class="text-xs text-slate-700 leading-snug">Jl. Pahlawan No. 88 (Kantor Bappeda), Kota Mojokerto</span>
                </div>
                <div class="flex items-center gap-2 text-xs text-slate-700">
                    <span class="material-symbols-outlined text-blue-800 text-sm">local_cafe</span>
                    <span class="font-medium">4 Botol (4x Titik Temu 250ml)</span>
                </div>
                <div class="border-t border-slate-100 pt-2 flex items-center justify-between text-xs">
                    <span class="text-gray-500 text-[11px]">Metode Pembayaran</span>
                    <span class="text-emerald-700 font-bold flex items-center gap-1 text-[11px]">
                        <span class="material-symbols-outlined text-emerald-600 text-sm">check_circle</span>
                        Lunas (QRIS) - Rp 72.000
                    </span>
                </div>
                <div class="bg-emerald-50/50 rounded-lg p-2 text-[11px] text-slate-600 flex items-center gap-1.5 border border-emerald-100">
                    <span class="material-symbols-outlined text-emerald-600 text-xs">check</span>
                    <span>Diterima resepsionis lobi utama</span>
                </div>
            </div>

        </div>

    </div>

</div>
</body>
</html>
