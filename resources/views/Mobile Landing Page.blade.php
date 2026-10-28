<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mobile Landing Page - Kopi Tiga Titik</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=AR+One+Sans:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Tailwind CSS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-screen flex justify-center items-start py-6 antialiased">
<div class="w-96 relative bg-slate-50 shadow-2xl inline-flex flex-col justify-start items-start pb-24 overflow-hidden border border-slate-200">

    <!-- Top Header -->
    <div class="self-stretch bg-white px-4 py-3 border-b border-slate-100 flex items-center justify-between sticky top-0 z-40 shadow-xs">
        <div class="flex items-center gap-2">
            <img class="size-8 rounded-lg object-contain shadow-xs" src="{{ asset('images/logo.png') }}" alt="Logo" />
            <div class="text-sky-900 text-base font-bold font-['Plus_Jakarta_Sans'] leading-6">Kopi Tiga Titik</div>
        </div>
        <a href="{{ url('/login-pembeli') }}" class="px-4 py-1 rounded-full border border-blue-800 text-blue-800 text-xs font-bold hover:bg-blue-50 transition-colors">Masuk</a>
    </div>

    <!-- Main Content -->
    <div class="self-stretch px-4 pt-4 pb-2 flex flex-col gap-3">
        <!-- Tagline Pill -->
        <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold w-fit">
            <span class="material-symbols-outlined text-xs">bolt</span>
            <span>Kopi Botol Setiap Hari • Mojokerto</span>
        </div>

        <!-- Hero Headline -->
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl font-extrabold text-slate-900 font-['Plus_Jakarta_Sans'] leading-tight">
                Kopi Botol Jalanan<br/>
                Khas <span class="text-blue-800">Mojokerto</span>
            </h1>
            <p class="text-xs text-gray-600 font-['Inter'] leading-relaxed">
                Kemasan botol 250ml siap minum. Pesan online diantar ke rumah atau ambil langsung di lapak kami.
            </p>
        </div>

        <!-- Hero Banner Image -->
        <div class="relative rounded-2xl overflow-hidden shadow-md bg-stone-100 border border-slate-200 mt-1">
            <div class="absolute top-3 left-3 bg-white/90 backdrop-blur-xs px-2.5 py-1 rounded-full text-[10px] font-bold text-slate-800 flex items-center gap-1 shadow-xs z-10">
                <span class="size-2 bg-blue-600 rounded-full inline-block"></span>
                <span>250ml</span>
            </div>
            <div class="absolute top-3 right-3 bg-white/90 backdrop-blur-xs px-2.5 py-1 rounded-full text-[10px] font-bold text-slate-800 flex items-center gap-1 shadow-xs z-10">
                <span class="material-symbols-outlined text-xs text-blue-800">local_cafe</span>
                <span>Racikan Khas Mojokerto</span>
            </div>
            <img class="w-full h-auto object-cover" src="{{ asset('products/All Produk.png') }}" alt="Semua Varian Kopi Tiga Titik" />
        </div>

        <!-- CTA Button -->
        <a href="{{ url('/katalog') }}" class="w-full py-3.5 bg-blue-800 hover:bg-blue-900 text-white rounded-xl font-bold text-sm flex items-center justify-center gap-2 shadow-md transition-all active:scale-[0.98]">
            <span>Pesan Sekarang</span>
            <span class="material-symbols-outlined text-base">arrow_forward</span>
        </a>

        <!-- Lapak Info Card -->
        <div class="bg-white rounded-2xl p-4 border-2 border-blue-600/30 shadow-xs flex flex-col gap-2 mt-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <span class="size-2.5 bg-blue-700 rounded-full inline-block"></span>
                    <span class="text-xs font-bold text-slate-900">Lapak Aktif Hari Ini</span>
                </div>
                <span class="px-2 py-0.5 bg-purple-100 text-purple-700 rounded-md text-[10px] font-bold">Buka Sore</span>
            </div>
            <div class="flex items-start gap-1.5 pt-1">
                <span class="text-red-500 text-sm">📍</span>
                <div class="flex flex-col">
                    <div class="text-xs font-bold text-slate-900">Jl. Benteng Pancasila (Buka 15.00 – 18.00 WIB)</div>
                    <div class="text-[11px] text-gray-500">Buka lapak gerobak sore hari. Tersedia dine-in dan takeaway.</div>
                </div>
            </div>
            <div class="border-t border-slate-100 pt-2.5 flex items-center justify-between text-xs">
                <span class="text-gray-500 text-[11px]">Kec. Magersari, Mojokerto</span>
                <a href="https://maps.google.com" target="_blank" class="text-blue-800 font-bold flex items-center gap-0.5 hover:underline text-[11px]">
                    Buka di Google Maps
                    <span class="material-symbols-outlined text-xs">open_in_new</span>
                </a>
            </div>
        </div>

        <!-- 4 Varian Kopi Section -->
        <div class="flex flex-col gap-2 pt-3">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900 font-['Plus_Jakarta_Sans']">4 Varian Kopi</h2>
                    <p class="text-[11px] text-gray-500 font-['Inter']">Racikan botol 250ml siap diminum</p>
                </div>
                <span class="px-2.5 py-0.5 bg-blue-100 text-blue-800 rounded-full text-[10px] font-bold">Botol Kaca</span>
            </div>

            <!-- Product Grid -->
            <div class="grid grid-cols-2 gap-3 pt-1">
                <!-- 1. Titik Temu -->
                <div class="bg-white rounded-2xl p-3 border border-slate-200/80 shadow-xs flex flex-col justify-between relative hover:shadow-md transition-shadow">
                    <span class="absolute top-2.5 right-2.5 bg-blue-800 text-white text-[9px] font-bold px-2 py-0.5 rounded-full z-10">Best Seller</span>
                    <img class="h-28 mx-auto object-contain my-1" src="{{ asset('products/Titik Temu.png') }}" alt="Titik Temu" />
                    <div class="flex flex-col gap-0.5">
                        <span class="text-[10px] text-gray-500 font-medium">Kopi Susu Aren</span>
                        <span class="text-sm font-bold text-slate-900 leading-tight">Titik Temu</span>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-xs font-bold text-blue-800">Rp 10.000</span>
                        <a href="{{ url('/katalog') }}" class="size-6 bg-blue-800 hover:bg-blue-900 text-white rounded-full flex items-center justify-center transition-colors">
                            <span class="material-symbols-outlined text-sm font-bold">add</span>
                        </a>
                    </div>
                </div>

                <!-- 2. Titik Hitam -->
                <div class="bg-white rounded-2xl p-3 border border-slate-200/80 shadow-xs flex flex-col justify-between relative hover:shadow-md transition-shadow">
                    <span class="absolute top-2.5 right-2.5 bg-stone-900 text-white text-[9px] font-bold px-2 py-0.5 rounded-full z-10">Kopi Murni</span>
                    <img class="h-28 mx-auto object-contain my-1" src="{{ asset('products/Titik Hitam.png') }}" alt="Titik Hitam" />
                    <div class="flex flex-col gap-0.5">
                        <span class="text-[10px] text-gray-500 font-medium">Cold Americano</span>
                        <span class="text-sm font-bold text-slate-900 leading-tight">Titik Hitam</span>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-xs font-bold text-blue-800">Rp 8.000</span>
                        <a href="{{ url('/katalog') }}" class="size-6 bg-blue-800 hover:bg-blue-900 text-white rounded-full flex items-center justify-center transition-colors">
                            <span class="material-symbols-outlined text-sm font-bold">add</span>
                        </a>
                    </div>
                </div>

                <!-- 3. Titik Manja -->
                <div class="bg-white rounded-2xl p-3 border border-slate-200/80 shadow-xs flex flex-col justify-between relative hover:shadow-md transition-shadow">
                    <span class="absolute top-2.5 right-2.5 bg-amber-800 text-white text-[9px] font-bold px-2 py-0.5 rounded-full z-10">Kopi Karamel</span>
                    <img class="h-28 mx-auto object-contain my-1" src="{{ asset('products/Titik Manja.png') }}" alt="Titik Manja" />
                    <div class="flex flex-col gap-0.5">
                        <span class="text-[10px] text-gray-500 font-medium">Caramel Macchiato</span>
                        <span class="text-sm font-bold text-slate-900 leading-tight">Titik Manja</span>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-xs font-bold text-blue-800">Rp 10.000</span>
                        <a href="{{ url('/katalog') }}" class="size-6 bg-blue-800 hover:bg-blue-900 text-white rounded-full flex items-center justify-center transition-colors">
                            <span class="material-symbols-outlined text-sm font-bold">add</span>
                        </a>
                    </div>
                </div>

                <!-- 4. Titik Jenuh -->
                <div class="bg-white rounded-2xl p-3 border border-slate-200/80 shadow-xs flex flex-col justify-between relative hover:shadow-md transition-shadow">
                    <span class="absolute top-2.5 right-2.5 bg-amber-950 text-white text-[9px] font-bold px-2 py-0.5 rounded-full z-10">Non-Kopi</span>
                    <img class="h-28 mx-auto object-contain my-1" src="{{ asset('products/Titik Jenuh.png') }}" alt="Titik Jenuh" />
                    <div class="flex flex-col gap-0.5">
                        <span class="text-[10px] text-gray-500 font-medium">Dark Choco Milk</span>
                        <span class="text-sm font-bold text-slate-900 leading-tight">Titik Jenuh</span>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-xs font-bold text-blue-800">Rp 12.000</span>
                        <a href="{{ url('/katalog') }}" class="size-6 bg-blue-800 hover:bg-blue-900 text-white rounded-full flex items-center justify-center transition-colors">
                            <span class="material-symbols-outlined text-sm font-bold">add</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kenapa Kopi Tiga Titik -->
        <div class="bg-blue-50/70 rounded-2xl p-4 border border-blue-100 flex flex-col items-center gap-3 mt-3">
            <h3 class="text-xs font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Kenapa Kopi Tiga Titik?</h3>
            <div class="grid grid-cols-3 gap-2 w-full text-center">
                <div class="flex flex-col items-center gap-1">
                    <div class="size-9 bg-white rounded-xl shadow-xs flex items-center justify-center">
                        <span class="material-symbols-outlined text-blue-800 text-lg">local_cafe</span>
                    </div>
                    <div class="text-[11px] font-bold text-slate-900">Real Coffe</div>
                    <div class="text-[9px] text-gray-500">Fresh espresso shot</div>
                </div>
                <div class="flex flex-col items-center gap-1">
                    <div class="size-9 bg-white rounded-xl shadow-xs flex items-center justify-center">
                        <span class="material-symbols-outlined text-blue-800 text-lg">moped</span>
                    </div>
                    <div class="text-[11px] font-bold text-slate-900">Fast Delivery</div>
                    <div class="text-[9px] text-gray-500">Kurir lokal se mojokerto</div>
                </div>
                <div class="flex flex-col items-center gap-1">
                    <div class="size-9 bg-white rounded-xl shadow-xs flex items-center justify-center">
                        <span class="material-symbols-outlined text-blue-800 text-lg">payments</span>
                    </div>
                    <div class="text-[11px] font-bold text-slate-900">Budget Friendly</div>
                    <div class="text-[9px] text-gray-500">Mulai Rp 8.000</div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center py-6 flex flex-col items-center gap-2 border-t border-slate-200/60 mt-3">
            <div class="text-sm font-bold text-blue-900">Kopi Tiga Titik</div>
            <div class="text-xs italic text-gray-600">"Ngopi Dulu Ceritanya Nyusul"</div>
            <a href="https://instagram.com/kopitigatitik_" target="_blank" class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-bold hover:bg-blue-200 transition-colors">@kopitigatitik_</a>
            <div class="text-[11px] text-gray-500">Melayani Delivery Area Kota & Kab. Mojokerto</div>
            <div class="text-[11px] font-semibold text-blue-900">Pusat Produksi : Dusun Daleman, Desa Japan, Kecamatan Sooko, Kabupaten Mojokerto</div>
            <div class="text-[10px] text-gray-400">© 2024 Kopi Tiga Titik Mojokerto. Seduhan Khas Mojokerto.</div>
        </div>
    </div>

    <!-- Global Bottom Navigation Bar -->
    <div class="fixed bottom-0 w-96 bg-white border-t border-slate-200 py-1.5 px-6 flex justify-between items-center z-50 shadow-[0_-2px_10px_rgba(0,0,0,0.05)]">
        <a href="{{ url('/landing') }}" class="flex flex-col items-center text-blue-800">
            <span class="material-symbols-outlined text-2xl font-bold">home</span>
            <span class="text-[10px] font-bold font-['Inter']">Beranda</span>
        </a>
        <a href="{{ url('/katalog') }}" class="flex flex-col items-center text-gray-400 hover:text-blue-800 transition-colors">
            <span class="material-symbols-outlined text-2xl">coffee</span>
            <span class="text-[10px] font-medium font-['Inter']">Menu</span>
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
