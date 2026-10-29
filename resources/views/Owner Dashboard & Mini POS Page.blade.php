<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Owner Dashboard & Mini POS - Kopi Tiga Titik</title>

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
        <div class="flex items-center gap-2">
            <img class="w-8 h-8 rounded-lg object-contain shadow-xs" src="{{ asset('images/logo.png') }}" alt="Logo" />
            <div class="flex items-center gap-1.5">
                <span class="font-extrabold text-blue-900 font-['Plus_Jakarta_Sans'] text-sm tracking-tight">Kopi Tiga Titik</span>
                <span class="px-1.5 py-0.2 bg-blue-100 text-blue-900 font-bold text-[9px] rounded uppercase tracking-wider">OWNER</span>
            </div>
        </div>
        <a href="{{ url('/owner/profil') }}" class="flex flex-col items-center text-slate-700 hover:text-blue-900 transition-colors">
            <span class="material-symbols-outlined text-2xl">person</span>
            <span class="text-[9px] font-bold">Akun</span>
        </a>
    </div>

    <!-- Status Strip -->
    <div class="px-4 py-2 flex justify-end">
        <div class="px-3 py-1 bg-blue-900 text-white rounded-full text-xs font-bold flex items-center gap-1.5 shadow-sm cursor-pointer">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>Lapak Buka</span>
            <span class="material-symbols-outlined text-sm">expand_more</span>
        </div>
    </div>

    <!-- Main Content -->
    <div class="px-4 flex flex-col gap-3.5">

        <!-- Ringkasan Hari Ini -->
        <div>
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">RINGKASAN HARI INI</div>
            <div class="grid grid-cols-3 gap-2">
                <!-- Omset -->
                <div class="p-2.5 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col justify-between">
                    <div class="flex justify-between items-center">
                        <span class="material-symbols-outlined text-blue-900 text-lg">payments</span>
                        <span class="text-[10px] font-bold text-emerald-600">+12%</span>
                    </div>
                    <div class="mt-2">
                        <div class="text-[10px] text-slate-500 font-medium">Omset Hari Ini</div>
                        <div class="text-sm font-extrabold text-slate-900 font-['Plus_Jakarta_Sans'] leading-tight">Rp 340rb</div>
                        <div class="text-[9px] text-blue-900 font-semibold mt-0.5">34 botol terjual</div>
                    </div>
                </div>

                <!-- Pesanan Masuk -->
                <div class="p-2.5 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col justify-between">
                    <div class="flex justify-between items-center">
                        <span class="material-symbols-outlined text-blue-900 text-lg">moped</span>
                        <span class="px-1.5 py-0.2 bg-rose-100 text-rose-700 text-[9px] font-bold rounded">1 Baru</span>
                    </div>
                    <div class="mt-2">
                        <div class="text-[10px] text-slate-500 font-medium">Pesanan Masuk</div>
                        <div class="text-sm font-extrabold text-slate-900 font-['Plus_Jakarta_Sans'] leading-tight">4 Online</div>
                        <div class="text-[9px] text-rose-600 font-semibold mt-0.5">Menunggu Kurir</div>
                    </div>
                </div>

                <!-- Sisa Stok -->
                <div class="p-2.5 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col justify-between">
                    <div class="flex justify-between items-center">
                        <span class="material-symbols-outlined text-blue-900 text-lg">kitchen</span>
                        <span class="text-[10px] text-slate-400 font-medium">Cooler</span>
                    </div>
                    <div class="mt-2">
                        <div class="text-[10px] text-slate-500 font-medium">Sisa Stok</div>
                        <div class="text-sm font-extrabold text-slate-900 font-['Plus_Jakarta_Sans'] leading-tight">25 Botol</div>
                        <div class="text-[9px] text-slate-500 mt-0.5">di Cooler Box</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Titik Lapak Jalanan Hari Ini -->
        <div class="p-3.5 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col gap-2.5">
            <div class="flex justify-between items-start">
                <div class="flex items-start gap-2">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-900 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-lg">location_on</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-xs">Titik Lapak Jalanan Hari Ini</h3>
                        <p class="text-[11px] text-slate-500">Mojokerto • Sesi Sore</p>
                    </div>
                </div>
                <span class="px-2 py-0.5 bg-blue-50 text-blue-900 font-semibold text-[10px] rounded-full border border-blue-100">15.00 - 18.00 WIB</span>
            </div>

            <div class="p-2.5 bg-blue-50/60 rounded-xl border border-blue-100 flex items-start gap-2 text-xs">
                <span class="material-symbols-outlined text-blue-900 text-base shrink-0">storefront</span>
                <div>
                    <div class="font-bold text-slate-900">Jl. Benteng Pancasila (Depan Taman Sentanan)</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Samping booth es krim • Parkir motor timur</div>
                </div>
            </div>

            <a href="{{ url('/owner/update-lokasi') }}" class="w-full py-2 bg-white hover:bg-slate-50 border border-blue-900 text-blue-900 rounded-xl font-bold text-xs flex justify-center items-center gap-1.5 transition-colors shadow-xs">
                <span class="material-symbols-outlined text-sm">edit_location</span>
                <span>Perbarui Lokasi Lapak Hari Ini</span>
            </a>
        </div>

        <!-- Catat Penjualan Lapak Offline (Mini POS) -->
        <div class="p-4 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col gap-3">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm font-['Plus_Jakarta_Sans']">Catat Penjualan Lapak Offline</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Pilih botol yang dibeli di tempat, stok otomatis terpotong di katalog online pembeli.</p>
                </div>
                <span class="px-2 py-0.5 bg-blue-100 text-blue-900 font-bold text-[10px] rounded-full">Kasir</span>
            </div>

            <!-- Items -->
            <div class="flex flex-col gap-2">
                <!-- Titik Temu -->
                <div class="p-2 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('products/Titik Temu.png') }}" alt="Titik Temu" class="w-7 h-9 object-contain" />
                        <div>
                            <div class="text-xs font-bold text-slate-900">Titik Temu</div>
                            <div class="text-[11px] text-slate-500">Aren 250ml • <strong class="text-blue-900">Rp 10.000</strong></div>
                            <div class="text-[10px] text-emerald-600 font-semibold">Sisa stok: 8 botol</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 bg-white px-2 py-1 rounded-full border border-slate-300">
                        <button class="w-5 h-5 rounded-full text-slate-600 flex items-center justify-center font-bold text-xs">-</button>
                        <span class="text-xs font-bold text-slate-900 w-3 text-center">1</span>
                        <button class="w-5 h-5 rounded-full text-blue-900 flex items-center justify-center font-bold text-xs">+</button>
                    </div>
                </div>

                <!-- Titik Hitam -->
                <div class="p-2 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('products/Titik Hitam.png') }}" alt="Titik Hitam" class="w-7 h-9 object-contain" />
                        <div>
                            <div class="text-xs font-bold text-slate-900">Titik Hitam</div>
                            <div class="text-[11px] text-slate-500">Americano 250ml • <strong class="text-blue-900">Rp 8.000</strong></div>
                            <div class="text-[10px] text-emerald-600 font-semibold">Sisa stok: 10 botol</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 bg-white px-2 py-1 rounded-full border border-slate-300">
                        <button class="w-5 h-5 rounded-full text-slate-600 flex items-center justify-center font-bold text-xs">-</button>
                        <span class="text-xs font-bold text-slate-900 w-3 text-center">0</span>
                        <button class="w-5 h-5 rounded-full text-blue-900 flex items-center justify-center font-bold text-xs">+</button>
                    </div>
                </div>

                <!-- Titik Manja -->
                <div class="p-2 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('products/Titik Manja.png') }}" alt="Titik Manja" class="w-7 h-9 object-contain" />
                        <div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs font-bold text-slate-900">Titik Manja</span>
                                <span class="px-1 py-0.2 bg-rose-100 text-rose-700 text-[8px] font-bold rounded">KRITIS</span>
                            </div>
                            <div class="text-[11px] text-slate-500">Karamel 250ml • <strong class="text-blue-900">Rp 10.000</strong></div>
                            <div class="text-[10px] text-rose-600 font-semibold">⚠️ Sisa: 2 botol (Restok!)</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 bg-white px-2 py-1 rounded-full border border-slate-300">
                        <button class="w-5 h-5 rounded-full text-slate-600 flex items-center justify-center font-bold text-xs">-</button>
                        <span class="text-xs font-bold text-slate-900 w-3 text-center">0</span>
                        <button class="w-5 h-5 rounded-full text-blue-900 flex items-center justify-center font-bold text-xs">+</button>
                    </div>
                </div>

                <!-- Titik Jenuh -->
                <div class="p-2 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('products/Titik Jenuh.png') }}" alt="Titik Jenuh" class="w-7 h-9 object-contain" />
                        <div>
                            <div class="text-xs font-bold text-slate-900">Titik Jenuh</div>
                            <div class="text-[11px] text-slate-500">Cokelat 250ml • <strong class="text-blue-900">Rp 12.000</strong></div>
                            <div class="text-[10px] text-emerald-600 font-semibold">Sisa stok: 5 botol</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 bg-white px-2 py-1 rounded-full border border-slate-300">
                        <button class="w-5 h-5 rounded-full text-slate-600 flex items-center justify-center font-bold text-xs">-</button>
                        <span class="text-xs font-bold text-slate-900 w-3 text-center">0</span>
                        <button class="w-5 h-5 rounded-full text-blue-900 flex items-center justify-center font-bold text-xs">+</button>
                    </div>
                </div>
            </div>

            <!-- Input Summary -->
            <div class="pt-2 border-t border-slate-100 flex justify-between items-center">
                <div>
                    <div class="text-[10px] text-slate-400 font-medium">Ringkasan Input</div>
                    <div class="text-base font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">Rp 10.000 <span class="text-xs font-medium text-slate-500">(1 Botol)</span></div>
                </div>
                <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl">
                    <button class="px-2.5 py-1 text-xs font-semibold text-slate-600 rounded-lg">QRIS</button>
                    <button class="px-2.5 py-1 bg-blue-900 text-white text-xs font-bold rounded-lg shadow-xs">Tunai</button>
                </div>
            </div>

            <button class="w-full py-3 bg-blue-900 hover:bg-blue-800 text-white rounded-xl font-bold text-xs flex justify-center items-center gap-2 shadow-sm transition-colors mt-1">
                <span class="material-symbols-outlined text-base">save</span>
                <span>Simpan Transaksi Lapak (Stok Terpotong)</span>
            </button>
        </div>

        <!-- Antrean Pesanan Online Masuk -->
        <div class="flex flex-col gap-2.5">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-1.5 font-bold text-slate-900 text-xs">
                    <span class="material-symbols-outlined text-blue-900 text-base">swap_horiz</span>
                    <span>Antrean Pesanan Online Masuk</span>
                </div>
                <span class="px-2 py-0.5 bg-blue-900 text-white font-bold text-[10px] rounded-full">3 Pesanan</span>
            </div>

            <!-- Online Order 1 -->
            <div class="p-3.5 bg-white rounded-2xl border-l-4 border-l-blue-900 border border-slate-200/80 shadow-sm flex flex-col gap-2.5">
                <div class="flex justify-between items-start text-xs">
                    <div>
                        <span class="px-2 py-0.2 bg-rose-100 text-rose-700 font-bold text-[9px] rounded">Butuh Kurir</span>
                        <div class="font-bold text-slate-900 mt-1">2x Titik Temu, 1x Titik Jenuh</div>
                    </div>
                    <div class="text-right">
                        <div class="font-extrabold text-blue-900 text-sm font-['Plus_Jakarta_Sans']">Rp 32.000</div>
                        <div class="text-[10px] text-emerald-600 font-bold">QRIS Lunas</div>
                    </div>
                </div>
                <div class="p-2 bg-blue-50/50 rounded-xl border border-blue-100 flex items-center gap-1.5 text-[11px] text-slate-700">
                    <span class="material-symbols-outlined text-blue-900 text-sm">near_me</span>
                    <span><strong>Jl. Majapahit No. 12</strong> (1.8 km) • Siap Diantar</span>
                </div>
                <button class="w-full py-2 bg-blue-900 hover:bg-blue-800 text-white rounded-xl text-xs font-bold flex justify-center items-center gap-1.5 shadow-xs transition-colors">
                    <span class="material-symbols-outlined text-sm">moped</span>
                    <span>Tugaskan ke Mas Ilham (Kurir 1)</span>
                </button>
            </div>

            <!-- Online Order 2 -->
            <div class="p-3.5 bg-white rounded-2xl border-l-4 border-l-blue-600 border border-slate-200/80 shadow-sm flex flex-col gap-2.5">
                <div class="flex justify-between items-start text-xs">
                    <div>
                        <span class="px-2 py-0.2 bg-blue-100 text-blue-800 font-bold text-[9px] rounded">Diantar</span>
                        <div class="font-bold text-slate-900 mt-1">2x Titik Temu, 1x Titik Hitam</div>
                    </div>
                    <div class="text-right">
                        <div class="font-extrabold text-blue-900 text-sm font-['Plus_Jakarta_Sans']">Rp 28.000</div>
                        <div class="text-[10px] text-emerald-600 font-bold">QRIS Lunas</div>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 text-[11px] text-slate-600">
                    <span class="material-symbols-outlined text-blue-900 text-sm">two_wheeler</span>
                    <span>Jl. Mojopahit No. 45, Sooko (3.2 km) • <strong>Mas Budi sedang jalan</strong></span>
                </div>
                <button class="w-full py-2 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 rounded-xl text-xs font-semibold flex justify-center items-center gap-1.5 transition-colors">
                    <span class="material-symbols-outlined text-sm text-emerald-600">check_circle</span>
                    <span>Tandai Pesanan Selesai</span>
                </button>
            </div>

            <!-- Online Order 3 -->
            <div class="p-3.5 bg-white rounded-2xl border-l-4 border-l-slate-400 border border-slate-200/80 shadow-sm flex flex-col gap-2.5">
                <div class="flex justify-between items-start text-xs">
                    <div>
                        <span class="px-2 py-0.2 bg-slate-100 text-slate-700 font-bold text-[9px] rounded">Ambil di Lapak</span>
                        <div class="font-bold text-slate-900 mt-1">1x Titik Manja</div>
                    </div>
                    <div class="text-right">
                        <div class="font-extrabold text-blue-900 text-sm font-['Plus_Jakarta_Sans']">Rp 10.000</div>
                        <div class="text-[10px] text-slate-500 font-bold">Tunai Lapak</div>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 text-[11px] text-slate-700">
                    <span class="material-symbols-outlined text-blue-900 text-sm">storefront</span>
                    <span>Status: <strong>Pembeli sudah di depan lapak</strong></span>
                </div>
                <button class="w-full py-2 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 rounded-xl text-xs font-semibold flex justify-center items-center gap-1.5 transition-colors">
                    <span class="material-symbols-outlined text-sm text-blue-900">shopping_bag</span>
                    <span>Serahkan ke Pembeli</span>
                </button>
            </div>
        </div>

    </div>

    <!-- Owner Bottom Navigation Bar -->
    <div class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-96 bg-white border-t border-slate-200 py-1.5 px-6 flex justify-between items-center z-50 shadow-[0_-2px_10px_rgba(0,0,0,0.05)]">
        <a href="{{ url('/owner/dashboard') }}" class="flex flex-col items-center text-blue-900">
            <span class="material-symbols-outlined text-2xl font-bold">point_of_sale</span>
            <span class="text-[10px] font-bold font-['Inter']">POS Kasir</span>
        </a>
        <a href="{{ url('/owner/produk') }}" class="flex flex-col items-center text-gray-400 hover:text-blue-900 transition-colors">
            <span class="material-symbols-outlined text-2xl">inventory_2</span>
            <span class="text-[10px] font-medium font-['Inter']">Produk</span>
        </a>
        <a href="{{ url('/owner/prediksi') }}" class="flex flex-col items-center text-gray-400 hover:text-blue-900 transition-colors">
            <span class="material-symbols-outlined text-2xl">query_stats</span>
            <span class="text-[10px] font-medium font-['Inter']">Prediksi</span>
        </a>
    </div>

</div>
</body>
</html>
