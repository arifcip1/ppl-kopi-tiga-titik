<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Menu & Katalog - Kopi Tiga Titik</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Tailwind CSS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-screen flex justify-center items-start py-6 antialiased font-sans">
<div class="w-full max-w-96 min-h-screen bg-slate-50 shadow-2xl relative flex flex-col justify-start pb-28">

    <!-- Top Header -->
    <div class="bg-white px-4 py-3 border-b border-slate-100 shadow-sm flex justify-between items-center sticky top-0 z-30">
        <div class="flex items-center gap-2.5">
            <img class="w-9 h-9 rounded-xl object-contain shadow-sm" src="{{ asset('images/logo.png') }}" alt="Logo" />
            <div>
                <div class="text-blue-900 text-sm font-extrabold font-['Plus_Jakarta_Sans'] leading-tight tracking-tight">KOPI TIGA TITIK</div>
                <div class="text-slate-500 text-[11px] font-medium leading-none mt-0.5">Mojokerto Street Coffee</div>
            </div>
        </div>
        <a href="{{ url('/cart') }}" class="relative p-2 rounded-xl text-blue-900 hover:bg-slate-50 transition-colors">
            <span class="material-symbols-outlined text-2xl">shopping_cart</span>
            <span class="absolute top-1 right-1 w-4 h-4 bg-red-600 text-white rounded-full text-[10px] font-bold flex items-center justify-center border-2 border-white">1</span>
        </a>
    </div>

    <!-- Main Content -->
    <div class="px-4 pt-3 flex flex-col gap-3.5">

        <!-- Lapak Info Card -->
        <div class="p-3 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col gap-2.5">
            <div class="flex justify-between items-center text-xs">
                <div class="flex items-center gap-1.5 font-medium text-slate-800">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>📍 Lapak Aktif: <strong>Jl. Kuwung</strong></span>
                </div>
                <span class="px-2.5 py-0.5 bg-blue-50 text-blue-800 font-semibold text-[11px] rounded-full border border-blue-100">15.00 - 18.00 WIB</span>
            </div>
            <div class="bg-slate-100 p-1 rounded-xl flex items-center gap-1">
                <button class="flex-1 py-1.5 bg-blue-800 text-white rounded-lg shadow-sm font-semibold text-xs flex justify-center items-center gap-1.5 transition-colors">
                    <span>🛵</span>
                    <span>Delivery</span>
                </button>
                <button class="flex-1 py-1.5 text-slate-600 hover:text-slate-900 rounded-lg font-medium text-xs flex justify-center items-center gap-1.5 transition-colors">
                    <span>🚶</span>
                    <span>Self Pickup</span>
                </button>
            </div>
        </div>

        <!-- Filter Chips -->
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-0.5">
            <button class="px-4 py-1.5 bg-blue-800 text-white rounded-full text-xs font-semibold shadow-sm whitespace-nowrap">Semua</button>
            <button class="px-4 py-1.5 bg-white text-slate-700 hover:bg-slate-50 border border-slate-200 rounded-full text-xs font-medium whitespace-nowrap">Kopi Susu</button>
            <button class="px-4 py-1.5 bg-white text-slate-700 hover:bg-slate-50 border border-slate-200 rounded-full text-xs font-medium whitespace-nowrap">Kopi Murni</button>
            <button class="px-4 py-1.5 bg-white text-slate-700 hover:bg-slate-50 border border-slate-200 rounded-full text-xs font-medium whitespace-nowrap">Non-Kopi</button>
        </div>

        <!-- Product List -->
        <div class="flex flex-col gap-3">

            <!-- Product 1: Titik Temu -->
            <div class="p-3 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex items-center gap-3">
                <div class="relative w-20 h-24 bg-slate-50 rounded-xl flex items-center justify-center p-1 border border-slate-100">
                    <span class="absolute -top-1.5 left-1 px-2 py-0.5 bg-blue-800 text-white text-[9px] font-bold rounded-full shadow-sm">Favorit</span>
                    <img src="{{ asset('products/Titik Temu.png') }}" alt="Titik Temu" class="w-full h-full object-contain" />
                </div>
                <div class="flex-1 flex flex-col justify-between self-stretch py-0.5">
                    <div>
                        <div class="flex justify-between items-start gap-1">
                            <h3 class="font-bold text-slate-900 text-sm font-['Plus_Jakarta_Sans']">Titik Temu</h3>
                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 font-semibold text-[10px] rounded-full border border-emerald-200 whitespace-nowrap">Stok: 8 botol</span>
                        </div>
                        <p class="text-slate-500 text-xs mt-0.5">Kopi Susu Gula Aren (250 ml)</p>
                    </div>
                    <div class="flex justify-between items-center mt-2">
                        <span class="text-blue-900 font-extrabold text-base font-['Plus_Jakarta_Sans']">Rp 10.000</span>
                        <a href="{{ url('/cart') }}" class="px-3.5 py-1.5 bg-blue-800 hover:bg-blue-900 text-white rounded-xl text-xs font-bold flex items-center gap-1 shadow-sm transition-colors">
                            <span>+</span>
                            <span>Cart</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Product 2: Titik Hitam -->
            <div class="p-3 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex items-center gap-3">
                <div class="relative w-20 h-24 bg-slate-50 rounded-xl flex items-center justify-center p-1 border border-slate-100">
                    <span class="absolute -top-1.5 left-1 px-2 py-0.5 bg-neutral-800 text-white text-[9px] font-bold rounded-full shadow-sm">Kopi Murni</span>
                    <img src="{{ asset('products/Titik Hitam.png') }}" alt="Titik Hitam" class="w-full h-full object-contain" />
                </div>
                <div class="flex-1 flex flex-col justify-between self-stretch py-0.5">
                    <div>
                        <div class="flex justify-between items-start gap-1">
                            <h3 class="font-bold text-slate-900 text-sm font-['Plus_Jakarta_Sans']">Titik Hitam</h3>
                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 font-semibold text-[10px] rounded-full border border-emerald-200 whitespace-nowrap">Stok: 10 botol</span>
                        </div>
                        <p class="text-slate-500 text-xs mt-0.5">Americano Dingin Pekat (250 ml)</p>
                    </div>
                    <div class="flex justify-between items-center mt-2">
                        <span class="text-blue-900 font-extrabold text-base font-['Plus_Jakarta_Sans']">Rp 8.000</span>
                        <a href="{{ url('/cart') }}" class="px-3.5 py-1.5 bg-blue-800 hover:bg-blue-900 text-white rounded-xl text-xs font-bold flex items-center gap-1 shadow-sm transition-colors">
                            <span>+</span>
                            <span>Cart</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Product 3: Titik Manja -->
            <div class="p-3 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex items-center gap-3">
                <div class="relative w-20 h-24 bg-slate-50 rounded-xl flex items-center justify-center p-1 border border-slate-100">
                    <span class="absolute -top-1.5 left-1 px-2 py-0.5 bg-amber-800 text-white text-[9px] font-bold rounded-full shadow-sm">Kopi Karamel</span>
                    <img src="{{ asset('products/Titik Manja.png') }}" alt="Titik Manja" class="w-full h-full object-contain" />
                </div>
                <div class="flex-1 flex flex-col justify-between self-stretch py-0.5">
                    <div>
                        <div class="flex justify-between items-start gap-1">
                            <h3 class="font-bold text-slate-900 text-sm font-['Plus_Jakarta_Sans']">Titik Manja</h3>
                            <span class="px-2 py-0.5 bg-rose-50 text-rose-600 font-semibold text-[10px] rounded-full border border-rose-200 whitespace-nowrap">⚠️ Sisa 2 botol!</span>
                        </div>
                        <p class="text-slate-500 text-xs mt-0.5">Kopi Susu Karamel Lembut (250 ml)</p>
                    </div>
                    <div class="flex justify-between items-center mt-2">
                        <span class="text-blue-900 font-extrabold text-base font-['Plus_Jakarta_Sans']">Rp 10.000</span>
                        <a href="{{ url('/cart') }}" class="px-3.5 py-1.5 bg-blue-800 hover:bg-blue-900 text-white rounded-xl text-xs font-bold flex items-center gap-1 shadow-sm transition-colors">
                            <span>+</span>
                            <span>Cart</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Product 4: Titik Jenuh -->
            <div class="p-3 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex items-center gap-3">
                <div class="relative w-20 h-24 bg-slate-50 rounded-xl flex items-center justify-center p-1 border border-slate-100">
                    <span class="absolute -top-1.5 left-1 px-2 py-0.5 bg-stone-800 text-white text-[9px] font-bold rounded-full shadow-sm">Non-Kopi</span>
                    <img src="{{ asset('products/Titik Jenuh.png') }}" alt="Titik Jenuh" class="w-full h-full object-contain" />
                </div>
                <div class="flex-1 flex flex-col justify-between self-stretch py-0.5">
                    <div>
                        <div class="flex justify-between items-start gap-1">
                            <h3 class="font-bold text-slate-900 text-sm font-['Plus_Jakarta_Sans']">Titik Jenuh</h3>
                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 font-semibold text-[10px] rounded-full border border-emerald-200 whitespace-nowrap">Stok: 5 botol</span>
                        </div>
                        <p class="text-slate-500 text-xs mt-0.5">Cokelat Susu Velvety (250 ml)</p>
                    </div>
                    <div class="flex justify-between items-center mt-2">
                        <span class="text-blue-900 font-extrabold text-base font-['Plus_Jakarta_Sans']">Rp 12.000</span>
                        <a href="{{ url('/cart') }}" class="px-3.5 py-1.5 bg-blue-800 hover:bg-blue-900 text-white rounded-xl text-xs font-bold flex items-center gap-1 shadow-sm transition-colors">
                            <span>+</span>
                            <span>Cart</span>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Floating Checkout Bar -->
    <div class="fixed bottom-14 left-1/2 -translate-x-1/2 w-full max-w-96 px-4 z-40">
        <div class="w-full px-4 py-2.5 bg-blue-900/95 backdrop-blur-md rounded-2xl shadow-xl border border-blue-700/50 flex justify-between items-center text-white">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 bg-white/20 rounded-full flex justify-center items-center">
                    <span class="material-symbols-outlined text-white text-lg">shopping_bag</span>
                </div>
                <div>
                    <div class="text-white text-xs font-bold font-['Plus_Jakarta_Sans']">1 Botol • Titik Temu</div>
                    <div class="text-blue-200 text-xs font-medium">Rp 10.000</div>
                </div>
            </div>
            <a href="{{ url('/cart') }}" class="px-4 py-2 bg-white hover:bg-slate-50 text-blue-900 rounded-xl font-bold text-xs flex items-center gap-1.5 shadow-sm transition-colors">
                <span>Checkout</span>
                <span class="material-symbols-outlined text-sm font-bold">arrow_forward</span>
            </a>
        </div>
    </div>

    <!-- Global Bottom Navigation Bar -->
    <div class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-96 bg-white border-t border-slate-200 py-1.5 px-6 flex justify-between items-center z-50 shadow-[0_-2px_10px_rgba(0,0,0,0.05)]">
        <a href="{{ url('/landing') }}" class="flex flex-col items-center text-gray-400 hover:text-blue-800 transition-colors">
            <span class="material-symbols-outlined text-2xl">home</span>
            <span class="text-[10px] font-medium font-['Inter']">Beranda</span>
        </a>
        <a href="{{ url('/katalog') }}" class="flex flex-col items-center text-blue-800">
            <span class="material-symbols-outlined text-2xl font-bold">coffee</span>
            <span class="text-[10px] font-bold font-['Inter']">Menu</span>
        </a>
        <a href="{{ url('/riwayat-pesanan') }}" class="flex flex-col items-center text-gray-400 hover:text-blue-800 transition-colors">
            <span class="material-symbols-outlined text-2xl">receipt_long</span>
            <span class="text-[10px] font-medium font-['Inter']">Pesanan</span>
        </a>
        <a href="{{ url('/profil-pelanggan') }}" class="flex flex-col items-center text-gray-400 hover:text-blue-800 transition-colors">
            <span class="material-symbols-outlined text-2xl">person</span>
            <span class="text-[10px] font-medium font-['Inter']">Akun</span>
        </a>
    </div>

</div>
</body>
</html>
