<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Konfirmasi Pesanan - Kopi Tiga Titik</title>

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
    <div class="bg-blue-900 text-white px-4 py-3 shadow-md flex items-center justify-between sticky top-0 z-30">
        <a href="{{ url('/cart') }}" class="w-9 h-9 rounded-full hover:bg-white/10 flex items-center justify-center transition-colors">
            <span class="material-symbols-outlined text-xl">arrow_back</span>
        </a>
        <h1 class="text-base font-bold font-['Plus_Jakarta_Sans']">Konfirmasi Pesanan</h1>
        <button class="w-9 h-9 rounded-full hover:bg-white/10 flex items-center justify-center transition-colors">
            <span class="material-symbols-outlined text-xl">help</span>
        </button>
    </div>

    <!-- Main Content -->
    <div class="px-4 pt-3 flex flex-col gap-3">

        <!-- Status & Delivery Switch -->
        <div class="flex flex-col gap-2.5">
            <div class="flex justify-between items-center text-xs px-1">
                <div class="flex items-center gap-1.5 font-medium text-slate-800">
                    <span class="w-2 h-2 rounded-full bg-blue-700"></span>
                    <span>Lapak Kuwung • Mojokerto</span>
                </div>
                <span class="px-2.5 py-0.5 bg-blue-50 text-blue-800 font-semibold text-[11px] rounded-full border border-blue-100">15-25 Mnt</span>
            </div>
            <div class="bg-white p-1 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-1">
                <button class="flex-1 py-2 bg-blue-900 text-white rounded-xl shadow-sm font-semibold text-xs flex justify-center items-center gap-1.5 transition-colors">
                    <span class="material-symbols-outlined text-sm">moped</span>
                    <span>Diantar ke Rumah</span>
                </button>
                <button class="flex-1 py-2 text-slate-600 hover:text-slate-900 rounded-xl font-medium text-xs flex justify-center items-center gap-1.5 transition-colors">
                    <span class="material-symbols-outlined text-sm">storefront</span>
                    <span>Ambil di Lapak</span>
                </button>
            </div>
        </div>

        <!-- Delivery Address Card -->
        <div class="p-3.5 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col gap-3">
            <div class="flex items-center gap-1.5 text-xs font-bold text-slate-900">
                <span class="material-symbols-outlined text-blue-900 text-base">location_on</span>
                <span>Lokasi Pengantaran</span>
            </div>

            <!-- Stylized Map Container -->
            <div class="w-full h-32 bg-slate-100 rounded-xl relative overflow-hidden border border-slate-200 flex items-center justify-center">
                <!-- Grid Map lines -->
                <div class="absolute inset-0 bg-[linear-gradient(to_right,#cbd5e1_1px,transparent_1px),linear-gradient(to_bottom,#cbd5e1_1px,transparent_1px)] bg-[size:24px_24px] opacity-40"></div>
                <div class="absolute top-2 left-2 px-2 py-0.5 bg-white/90 rounded text-[9px] font-bold text-slate-700 shadow-sm">Jl. Kuwung</div>
                <!-- Pin -->
                <div class="relative z-10 flex flex-col items-center">
                    <div class="w-8 h-8 rounded-full bg-blue-900 text-white flex items-center justify-center shadow-md animate-bounce">
                        <span class="material-symbols-outlined text-base">location_on</span>
                    </div>
                </div>
                <div class="absolute bottom-2 left-2 right-2 flex justify-between items-center text-[10px]">
                    <span class="px-2 py-0.5 bg-white/95 text-blue-900 font-semibold rounded-lg shadow-sm flex items-center gap-1 border border-slate-200">
                        <span class="material-symbols-outlined text-[12px]">my_location</span>
                        <span>Lokasi Sekarang</span>
                    </span>
                    <span class="px-2 py-0.5 bg-white/90 text-slate-600 font-medium rounded-lg shadow-sm">Magersari, Mojokerto</span>
                </div>
            </div>

            <!-- Recipient Info -->
            <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200 flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-900 flex items-center justify-center">
                        <span class="material-symbols-outlined text-base">badge</span>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-slate-900 flex items-center gap-1">
                            <span>Dimas Suryo</span>
                            <span class="material-symbols-outlined text-emerald-600 text-sm">verified</span>
                        </div>
                        <div class="text-[11px] text-slate-500">0812-3456-7890</div>
                    </div>
                </div>
                <button class="text-[11px] font-bold text-blue-900 hover:underline">Ganti Kontak</button>
            </div>

            <!-- Full Address -->
            <div>
                <label class="text-[11px] font-semibold text-slate-700 mb-1 block">Alamat Lengkap & Patokan Rumah</label>
                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-700 leading-relaxed">
                    Jl. Hayam Wuruk No. 42, RT 02/RW 01, Magersari, Mojokerto <span class="text-slate-500">(Pagar hitam depan toko kelontong Bu Sri)</span>
                </div>
            </div>

            <!-- Free Delivery Promo Banner -->
            <div class="p-2.5 bg-emerald-50 rounded-xl border border-emerald-200 flex items-center gap-2.5">
                <span class="material-symbols-outlined text-emerald-700 text-lg">local_shipping</span>
                <div class="text-xs">
                    <div class="font-bold text-emerald-900 flex items-center gap-1.5">
                        <span>Jarak 3.2 km dari Lapak</span>
                        <span class="px-1.5 py-0.2 bg-emerald-600 text-white text-[9px] font-bold rounded">GRATIS ONGKIR</span>
                    </div>
                    <div class="text-emerald-700 text-[11px]">Promo Radius Gratis hingga 5 km</div>
                </div>
            </div>
        </div>

        <!-- Order Items Summary Card -->
        <div class="p-3.5 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col gap-3">
            <div class="flex justify-between items-center text-xs">
                <div class="flex items-center gap-1.5 font-bold text-slate-900">
                    <span class="material-symbols-outlined text-blue-900 text-base">shopping_bag</span>
                    <span>Pesanan Kamu (3 Botol)</span>
                </div>
                <a href="{{ url('/cart') }}" class="text-[11px] font-bold text-blue-900 hover:underline">Ubah &gt;</a>
            </div>

            <!-- Item 1 -->
            <div class="flex items-center gap-3 py-1 border-b border-slate-100">
                <div class="relative w-14 h-16 bg-slate-50 rounded-xl flex items-center justify-center p-1 border border-slate-100">
                    <span class="absolute -bottom-1 left-1 right-1 bg-blue-900 text-white text-[8px] font-bold text-center rounded">250ml</span>
                    <img src="{{ asset('products/Titik Temu.png') }}" alt="Titik Temu" class="w-full h-full object-contain" />
                </div>
                <div class="flex-1">
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="font-bold text-slate-900 text-xs">2x Titik Temu</h4>
                            <p class="text-[11px] text-slate-500">Kopi Susu Gula Aren</p>
                            <p class="text-[10px] text-slate-400">Rp 10.000 / botol</p>
                        </div>
                        <div class="text-right">
                            <span class="font-bold text-blue-900 text-xs font-['Plus_Jakarta_Sans']">Rp 20.000</span>
                            <div class="flex items-center gap-1.5 bg-slate-50 px-1.5 py-0.5 rounded-full border border-slate-200 mt-1">
                                <button class="w-4 h-4 bg-white rounded-full text-slate-700 flex items-center justify-center text-[10px] font-bold">-</button>
                                <span class="text-[11px] font-bold text-slate-800">2</span>
                                <button class="w-4 h-4 bg-blue-900 text-white rounded-full flex items-center justify-center text-[10px] font-bold">+</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Item 2 -->
            <div class="flex items-center gap-3 py-1">
                <div class="relative w-14 h-16 bg-slate-50 rounded-xl flex items-center justify-center p-1 border border-slate-100">
                    <span class="absolute -bottom-1 left-1 right-1 bg-neutral-800 text-white text-[8px] font-bold text-center rounded">250ml</span>
                    <img src="{{ asset('products/Titik Hitam.png') }}" alt="Titik Hitam" class="w-full h-full object-contain" />
                </div>
                <div class="flex-1">
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="font-bold text-slate-900 text-xs">1x Titik Hitam</h4>
                            <p class="text-[11px] text-slate-500">Americano Dingin Pekat Bold</p>
                            <p class="text-[10px] text-slate-400">Rp 8.000 / botol</p>
                        </div>
                        <div class="text-right">
                            <span class="font-bold text-blue-900 text-xs font-['Plus_Jakarta_Sans']">Rp 8.000</span>
                            <div class="flex items-center gap-1.5 bg-slate-50 px-1.5 py-0.5 rounded-full border border-slate-200 mt-1">
                                <button class="w-4 h-4 bg-white rounded-full text-slate-700 flex items-center justify-center text-[10px] font-bold">-</button>
                                <span class="text-[11px] font-bold text-slate-800">1</span>
                                <button class="w-4 h-4 bg-blue-900 text-white rounded-full flex items-center justify-center text-[10px] font-bold">+</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="pt-1">
                <div class="text-[11px] font-semibold text-slate-700 flex items-center gap-1 mb-1">
                    <span class="material-symbols-outlined text-xs">notes</span>
                    <span>Catatan:</span>
                </div>
                <div class="p-2 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-500 italic">
                    Contoh: Es batu dipisah, jangan terlalu manis...
                </div>
            </div>
        </div>

        <!-- Payment Breakdown Card -->
        <div class="p-3.5 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col gap-2.5">
            <div class="flex items-center gap-1.5 text-xs font-bold text-slate-900">
                <span class="material-symbols-outlined text-blue-900 text-base">receipt_long</span>
                <span>Rincian Pembayaran</span>
            </div>
            <div class="flex flex-col gap-1.5 text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal Produk (3 item)</span>
                    <span class="font-semibold text-slate-900">Rp 28.000</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span class="flex items-center gap-1">
                        <span>Ongkos Kirim Kurir (3.2 km)</span>
                        <span class="px-1.5 py-0.2 bg-emerald-100 text-emerald-800 text-[9px] font-bold rounded">Promo</span>
                    </span>
                    <span class="font-semibold text-emerald-600"><span class="line-through text-slate-400 font-normal mr-1">Rp 5.000</span> Rp 0</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Biaya Kemasan & Es Pendingin</span>
                    <span class="font-bold text-emerald-600">Gratis</span>
                </div>
            </div>

            <div class="border-t border-dashed border-slate-200 my-1"></div>

            <div class="flex justify-between items-center">
                <div>
                    <div class="text-xs font-bold text-slate-900">Total Tagihan</div>
                    <div class="text-[10px] text-slate-400">Sudah termasuk PPN</div>
                </div>
                <span class="text-lg font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">Rp 28.000</span>
            </div>

            <!-- Payment Methods -->
            <div class="flex flex-col gap-2 pt-2">
                <div class="p-2.5 bg-blue-50/60 rounded-xl border-2 border-blue-800 flex justify-between items-center cursor-pointer">
                    <div class="flex items-center gap-2.5">
                        <span class="px-2 py-1 bg-blue-900 text-white rounded font-black text-[10px]">QRIS</span>
                        <div>
                            <div class="text-xs font-bold text-slate-900">Metode Bayar: QRIS</div>
                            <div class="text-[10px] text-slate-500">BCA, GoPay, OVO, ShopeePay</div>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-slate-400 text-sm">chevron_right</span>
                </div>

                <div class="p-2.5 bg-white rounded-xl border border-slate-200 flex justify-between items-center cursor-pointer hover:bg-slate-50">
                    <div class="flex items-center gap-2.5">
                        <span class="px-2 py-1 bg-slate-200 text-slate-800 rounded font-bold text-[10px]">Cash</span>
                        <div class="text-xs font-bold text-slate-900">Metode Bayar: Cash</div>
                    </div>
                    <span class="material-symbols-outlined text-slate-400 text-sm">chevron_right</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Fixed Bottom Action Bar -->
    <div class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-96 bg-white/95 backdrop-blur-md border-t border-slate-200 px-4 py-3 flex justify-between items-center z-50 shadow-[0_-4px_15px_rgba(0,0,0,0.05)]">
        <div>
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total (3 Botol)</div>
            <div class="text-lg font-extrabold text-blue-900 font-['Plus_Jakarta_Sans'] leading-tight">Rp 28.000</div>
        </div>
        <a href="{{ url('/pembayaran-qris') }}" class="px-6 py-2.5 bg-blue-900 hover:bg-blue-800 text-white rounded-xl font-bold text-xs flex items-center gap-1.5 shadow-md transition-colors">
            <span>Bayar QRIS</span>
            <span class="material-symbols-outlined text-sm font-bold">arrow_forward</span>
        </a>
    </div>

</div>
</body>
</html>
