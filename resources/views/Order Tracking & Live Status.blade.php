<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Live Tracking Pesanan - Kopi Tiga Titik</title>

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
        <a href="{{ url('/riwayat-pesanan') }}" class="w-9 h-9 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-800 transition-colors">
            <span class="material-symbols-outlined text-xl">arrow_back</span>
        </a>
        <div class="text-center">
            <h1 class="text-base font-bold text-slate-900 font-['Plus_Jakarta_Sans'] leading-tight">Pesanan</h1>
            <p class="text-[11px] text-blue-900 font-medium leading-none flex items-center justify-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-700"></span>
                <span>Lapak Kuwung • Mojokerto</span>
            </p>
        </div>
        <button class="w-9 h-9 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-800 transition-colors">
            <span class="material-symbols-outlined text-xl">support_agent</span>
        </button>
    </div>

    <!-- Main Content -->
    <div class="px-4 pt-3 flex flex-col gap-3.5">

        <!-- ETA Banner Card -->
        <div class="p-4 bg-white rounded-2xl shadow-sm border-2 border-blue-900 flex flex-col gap-2.5">
            <div class="flex justify-between items-center text-xs">
                <span class="px-2.5 py-1 bg-blue-50 text-blue-900 font-bold rounded-full flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-700"></span>
                    <span>Sedang Diproses</span>
                </span>
                <span class="text-slate-400 text-[11px]">Perkiraan 15.55 WIB</span>
                <span class="px-2 py-0.5 border border-blue-200 text-blue-900 font-semibold text-[10px] rounded-full">Delivery</span>
            </div>
            <div class="flex justify-between items-end pt-1">
                <div>
                    <div class="text-[11px] text-slate-500 font-medium">Estimasi Waktu Tiba</div>
                    <div class="text-xl font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">15 – 20 Menit</div>
                </div>
                <div class="text-right">
                    <div class="text-[10px] text-slate-400">Status Terkini</div>
                    <div class="text-xs font-bold text-slate-800 flex items-center gap-1 justify-end">
                        <span class="material-symbols-outlined text-sm text-blue-900">local_cafe</span>
                        <span>Peracikan Kopi</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stepper Timeline -->
        <div class="p-4 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col gap-3">
            <div class="flex justify-between items-center text-xs">
                <div class="flex items-center gap-1.5 font-bold text-slate-900">
                    <span class="material-symbols-outlined text-blue-900 text-base">timeline</span>
                    <span>Status Pemesanan</span>
                </div>
                <span class="text-slate-500 text-[11px] flex items-center gap-1">
                    <span class="material-symbols-outlined text-xs">sync</span>
                    <span>Live</span>
                </span>
            </div>

            <!-- Steps -->
            <div class="relative pl-6 space-y-4 pt-1">
                <!-- Vertical Line -->
                <div class="absolute left-2.5 top-3 bottom-3 w-0.5 bg-slate-200"></div>

                <!-- Step 1 (Completed) -->
                <div class="relative flex items-start gap-3">
                    <div class="absolute -left-6 w-5 h-5 rounded-full bg-blue-900 text-white flex items-center justify-center text-xs shadow-sm">
                        <span class="material-symbols-outlined text-xs">check</span>
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-center text-xs">
                            <span class="font-bold text-slate-900">Pembayaran Diterima</span>
                            <span class="text-slate-400 text-[10px]">15:32 WIB</span>
                        </div>
                        <p class="text-[11px] text-slate-500">QRIS Terverifikasi otomatis oleh sistem toko.</p>
                    </div>
                </div>

                <!-- Step 2 (Active) -->
                <div class="relative flex items-start gap-3">
                    <div class="absolute -left-6 w-5 h-5 rounded-full bg-blue-900 text-white flex items-center justify-center text-xs shadow-md ring-4 ring-blue-100">
                        <span class="material-symbols-outlined text-xs">coffee</span>
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-center text-xs">
                            <span class="font-bold text-blue-900">Kopi Sedang Disiapkan & Cooler Box</span>
                            <span class="px-1.5 py-0.2 bg-blue-100 text-blue-900 font-bold text-[9px] rounded">Diproses</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-0.5">Barista sedang meracik 3 kopi botol 250ml dan memasukkan ice cooling pack agar tetap dingin.</p>
                    </div>
                </div>

                <!-- Step 3 (Pending) -->
                <div class="relative flex items-start gap-3">
                    <div class="absolute -left-6 w-5 h-5 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-xs">
                        <span class="material-symbols-outlined text-xs">moped</span>
                    </div>
                    <div class="flex-1">
                        <div class="text-xs font-semibold text-slate-600">Kurir Mengantar ke Lokasimu</div>
                        <p class="text-[11px] text-slate-400">Kurir bersiap berangkat menuju Jl. Mojopahit, Sooko.</p>
                    </div>
                </div>

                <!-- Step 4 (Pending) -->
                <div class="relative flex items-start gap-3">
                    <div class="absolute -left-6 w-5 h-5 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-xs">
                        <span class="material-symbols-outlined text-xs">check_circle</span>
                    </div>
                    <div class="flex-1">
                        <div class="text-xs font-semibold text-slate-600">Pesanan Selesai Diterima</div>
                        <p class="text-[11px] text-slate-400">Konfirmasi botol kopi sampai di tujuan.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Courier & Delivery Info -->
        <div class="p-4 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col gap-3">
            <div class="flex justify-between items-center text-xs">
                <div class="flex items-center gap-1.5 font-bold text-slate-900">
                    <span class="material-symbols-outlined text-blue-900 text-base">moped</span>
                    <span>Informasi Pengantaran & Kurir</span>
                </div>
                <span class="px-2 py-0.5 bg-blue-50 text-blue-900 font-semibold text-[10px] rounded-full border border-blue-100">Kurir Toko</span>
            </div>

            <!-- Driver Card -->
            <div class="p-3 bg-blue-50/60 rounded-xl border border-blue-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-full bg-blue-900 text-white font-bold text-sm flex items-center justify-center font-['Plus_Jakarta_Sans']">
                        MI
                    </div>
                    <div>
                        <div class="text-xs font-bold text-slate-900">Mas Ilham (Kurir Kopi Tiga Titik)</div>
                        <div class="text-[11px] text-slate-600 flex items-center gap-1 mt-0.5">
                            <span>🛵 Honda Vario Hitam</span>
                            <span>•</span>
                            <strong class="text-blue-900">W 4821 NA</strong>
                        </div>
                    </div>
                </div>
                <button class="w-9 h-9 rounded-xl bg-white text-blue-900 border border-slate-200 flex items-center justify-center hover:bg-blue-50 shadow-xs transition-colors">
                    <span class="material-symbols-outlined text-lg">call</span>
                </button>
            </div>

            <!-- Destination Address -->
            <div class="text-xs flex items-start gap-2 pt-1 text-slate-700">
                <span class="material-symbols-outlined text-blue-900 text-base">location_on</span>
                <div>
                    <div class="font-bold text-slate-900">Alamat Tujuan Pengiriman</div>
                    <div class="text-slate-600 mt-0.5">Jl. Mojopahit No. 45, Sooko, Mojokerto</div>
                    <div class="text-slate-400 italic text-[11px]">Catatan: Pagar hitam samping minimarket</div>
                </div>
            </div>
        </div>

        <!-- Order Items Detail -->
        <div class="p-4 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col gap-2.5">
            <div class="flex justify-between items-center text-xs">
                <div>
                    <span class="font-bold text-slate-900">Rincian Pesanan (3 Botol)</span>
                    <p class="text-[11px] text-slate-400">Kemasan Botol 250ml</p>
                </div>
                <span class="text-sm font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">Rp 28.000</span>
            </div>

            <!-- Item 1 -->
            <div class="flex items-center gap-3 py-1.5 border-t border-slate-100">
                <img src="{{ asset('products/Titik Temu.png') }}" alt="Titik Temu" class="w-8 h-10 object-contain" />
                <div class="flex-1">
                    <div class="flex justify-between">
                        <span class="text-xs font-bold text-slate-900">2x Titik Temu (Gula Aren)</span>
                        <span class="text-xs font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Rp 20.000</span>
                    </div>
                    <div class="text-[10px] text-slate-500">250ml • Normal Ice • Aren Asli</div>
                    <div class="text-[10px] text-slate-400">Rp 10.000 / botol</div>
                </div>
            </div>

            <!-- Item 2 -->
            <div class="flex items-center gap-3 py-1.5 border-t border-slate-100">
                <img src="{{ asset('products/Titik Hitam.png') }}" alt="Titik Hitam" class="w-8 h-10 object-contain" />
                <div class="flex-1">
                    <div class="flex justify-between">
                        <span class="text-xs font-bold text-slate-900">1x Titik Hitam (Cold Brew)</span>
                        <span class="text-xs font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Rp 8.000</span>
                    </div>
                    <div class="text-[10px] text-slate-500">250ml • Black Americano Pekat</div>
                    <div class="text-[10px] text-slate-400">Rp 8.000 / botol</div>
                </div>
            </div>

            <div class="border-t border-dashed border-slate-200 my-1"></div>

            <div class="flex flex-col gap-1 text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal Produk</span>
                    <span class="font-semibold text-slate-800">Rp 28.000</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Ongkos Kirim Lapak (Promo)</span>
                    <span class="font-bold text-emerald-600">Gratis</span>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-2 flex justify-between items-center">
                <span class="text-xs font-bold text-slate-900">Total Pembayaran</span>
                <span class="text-base font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">Rp 28.000</span>
            </div>

            <div class="flex justify-between items-center pt-1 text-xs text-slate-500">
                <span>Metode Pembayaran</span>
                <span class="font-bold text-slate-800 flex items-center gap-1">
                    <span class="material-symbols-outlined text-xs">qr_code_2</span>
                    <span>QRIS</span>
                </span>
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
