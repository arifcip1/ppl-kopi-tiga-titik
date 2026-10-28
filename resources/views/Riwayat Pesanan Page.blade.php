<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pesanan Saya - Kopi Tiga Titik</title>

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
        <div class="text-center">
            <h1 class="text-base font-bold text-slate-900 font-['Plus_Jakarta_Sans'] leading-tight">Pesanan Saya</h1>
            <p class="text-[11px] text-blue-800 font-medium leading-none">Pelanggan: Nabil</p>
        </div>
        <div class="w-9"></div>
    </div>

    <!-- Main Content -->
    <div class="px-4 pt-3 flex flex-col gap-3.5">

        <!-- Tabs -->
        <div class="flex items-center gap-2">
            <button class="px-4 py-1.5 bg-blue-900 text-white rounded-full text-xs font-semibold shadow-sm flex items-center gap-1.5">
                <span>🛵</span>
                <span>Sedang Berjalan</span>
                <span class="px-1.5 py-0.2 bg-white/20 rounded-full text-[10px]">1</span>
            </button>
            <button class="px-4 py-1.5 bg-white text-slate-700 hover:bg-slate-50 border border-slate-200 rounded-full text-xs font-medium flex items-center gap-1.5 transition-colors">
                <span class="text-emerald-600">✓</span>
                <span>Riwayat Selesai</span>
                <span class="px-1.5 py-0.2 bg-slate-100 text-slate-600 rounded-full text-[10px]">2</span>
            </button>
        </div>

        <!-- Section: Pesanan yang Sedang Diproses -->
        <div>
            <div class="flex items-center gap-1.5 text-xs font-bold text-slate-900 mb-2">
                <span class="w-2 h-2 rounded-full bg-blue-700"></span>
                <span>Pesanan yang Sedang Diproses</span>
            </div>

            <!-- Active Order Card -->
            <div class="p-3.5 bg-white rounded-2xl border-2 border-blue-900 shadow-md flex flex-col gap-3">
                <div class="flex justify-between items-start pb-2 border-b border-slate-100 text-xs">
                    <div>
                        <div class="font-bold text-blue-900 flex items-center gap-1">
                            <span>Kopi Sedang Disiapkan</span>
                        </div>
                        <div class="text-blue-700 text-[11px] font-semibold">• Tiba ±15-20 Mnt</div>
                    </div>
                    <span class="text-slate-400 text-[11px]">Hari ini, 15:20 WIB</span>
                </div>

                <div class="flex justify-between items-center text-xs">
                    <div>
                        <h3 class="font-bold text-slate-900">Pesanan</h3>
                        <p class="text-[11px] text-slate-500">Lapak Kuwung • Delivery</p>
                    </div>
                    <span class="px-2 py-0.5 bg-blue-50 text-blue-900 font-bold text-[10px] rounded-full border border-blue-100">3 Botol</span>
                </div>

                <!-- Product Preview Box -->
                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200 flex flex-col gap-2">
                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-1 bg-white p-1 rounded-lg border border-slate-200 shadow-xs">
                            <img src="{{ asset('products/Titik Temu.png') }}" alt="Titik Temu" class="w-7 h-9 object-contain" />
                            <img src="{{ asset('products/Titik Hitam.png') }}" alt="Titik Hitam" class="w-7 h-9 object-contain" />
                        </div>
                        <div class="flex-1">
                            <div class="text-xs font-bold text-slate-900">2x Titik Temu (Aren 250ml)</div>
                            <div class="text-[11px] text-slate-500">1x Titik Hitam (Cold Brew 250ml)</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 pt-1 border-t border-slate-200 text-[11px] text-slate-600">
                        <span class="material-symbols-outlined text-blue-900 text-sm">moped</span>
                        <span>Kurir: <strong>Mas Ilham</strong> • Diantar ke Jl. Hayam Wuruk No. 42</span>
                    </div>
                </div>

                <div class="flex justify-between items-center">
                    <div>
                        <div class="text-[10px] text-slate-400 font-medium">Total Tagihan</div>
                        <div class="text-base font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">Rp 28.000</div>
                    </div>
                    <span class="px-2.5 py-1 bg-blue-50 text-blue-900 font-bold text-xs rounded-lg flex items-center gap-1 border border-blue-200">
                        <span class="material-symbols-outlined text-xs">verified</span>
                        <span>QRIS</span>
                    </span>
                </div>

                <a href="{{ url('/order-tracking') }}" class="w-full py-2.5 bg-blue-900 hover:bg-blue-800 text-white rounded-xl font-bold text-xs flex justify-center items-center gap-1.5 shadow-sm transition-colors">
                    <span>Lacak Pesanan Live</span>
                    <span class="material-symbols-outlined text-sm font-bold">arrow_forward</span>
                </a>
            </div>
        </div>

        <!-- Section: Pesanan Sebelumnya -->
        <div class="flex flex-col gap-2.5">
            <h2 class="text-xs font-bold text-slate-900">Pesanan Sebelumnya</h2>

            <!-- History Card 1 -->
            <div class="p-3.5 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col gap-2.5">
                <div class="flex justify-between items-start text-xs">
                    <div>
                        <div class="flex items-center gap-1.5">
                            <h3 class="font-bold text-slate-900">Pesanan</h3>
                            <span class="px-2 py-0.2 bg-blue-50 text-blue-900 font-semibold text-[10px] rounded-full">2 Botol</span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-0.5">24 Okt 2026 • 16:15 WIB</p>
                    </div>
                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 font-bold text-[10px] rounded-full border border-emerald-200">Selesai Diterima</span>
                </div>

                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1 bg-slate-50 p-1 rounded-lg border border-slate-200">
                        <img src="{{ asset('products/Titik Manja.png') }}" alt="Titik Manja" class="w-6 h-8 object-contain" />
                        <img src="{{ asset('products/Titik Temu.png') }}" alt="Titik Temu" class="w-6 h-8 object-contain" />
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-800">1x Titik Manja (Karamel), 1x Titik Temu (Aren)</div>
                        <div class="text-xs text-slate-500 font-medium">Total: <strong class="text-blue-900">Rp 20.000</strong> <span class="text-[10px] text-slate-400">(QRIS Lunas)</span></div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 pt-1 border-t border-slate-100">
                    <a href="{{ url('/katalog') }}" class="py-2 bg-white hover:bg-slate-50 border border-blue-900 text-blue-900 rounded-xl text-xs font-bold flex justify-center items-center gap-1 transition-colors">
                        <span class="material-symbols-outlined text-sm">replay</span>
                        <span>Pesan Lagi</span>
                    </a>
                    <button class="py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-medium flex justify-center items-center gap-1 transition-colors">
                        <span class="material-symbols-outlined text-sm">receipt_long</span>
                        <span>Lihat Nota Digital</span>
                    </button>
                </div>
            </div>

            <!-- History Card 2 -->
            <div class="p-3.5 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col gap-2.5">
                <div class="flex justify-between items-start text-xs">
                    <div>
                        <div class="flex items-center gap-1.5">
                            <h3 class="font-bold text-slate-900">Pesanan</h3>
                            <span class="px-2 py-0.2 bg-blue-50 text-blue-900 font-semibold text-[10px] rounded-full">2 Botol</span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-0.5">18 Okt 2026 • 15:40 WIB</p>
                    </div>
                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 font-bold text-[10px] rounded-full border border-emerald-200">Selesai Diterima</span>
                </div>

                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1 bg-slate-50 p-1 rounded-lg border border-slate-200">
                        <img src="{{ asset('products/Titik Jenuh.png') }}" alt="Titik Jenuh" class="w-6 h-8 object-contain" />
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-800">2x Titik Jenuh (Cokelat Susu 250ml)</div>
                        <div class="text-xs text-slate-500 font-medium">Total: <strong class="text-blue-900">Rp 24.000</strong> <span class="text-[10px] text-slate-400">(Tunai Lapak)</span></div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 pt-1 border-t border-slate-100">
                    <a href="{{ url('/katalog') }}" class="py-2 bg-white hover:bg-slate-50 border border-blue-900 text-blue-900 rounded-xl text-xs font-bold flex justify-center items-center gap-1 transition-colors">
                        <span class="material-symbols-outlined text-sm">replay</span>
                        <span>Pesan Lagi</span>
                    </a>
                    <button class="py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-medium flex justify-center items-center gap-1 transition-colors">
                        <span class="material-symbols-outlined text-sm">receipt_long</span>
                        <span>Lihat Nota Digital</span>
                    </button>
                </div>
            </div>

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
        <a href="{{ url('/riwayat-pesanan') }}" class="flex flex-col items-center text-blue-800">
            <span class="material-symbols-outlined text-2xl font-bold">receipt_long</span>
            <span class="text-[10px] font-bold font-['Inter']">Pesanan</span>
        </a>
        <a href="{{ url('/profil-pelanggan') }}" class="flex flex-col items-center text-gray-400 hover:text-blue-800 transition-colors">
            <span class="material-symbols-outlined text-2xl">person</span>
            <span class="text-[10px] font-medium font-['Inter']">Akun</span>
        </a>
    </div>

</div>
</body>
</html>
