<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Keranjang Belanja - Kopi Tiga Titik</title>

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
    <div class="bg-white px-4 py-3 border-b border-slate-100 shadow-sm flex items-center justify-between sticky top-0 z-30">
        <a href="{{ url('/katalog') }}" class="w-9 h-9 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-800 transition-colors">
            <span class="material-symbols-outlined text-xl">arrow_back</span>
        </a>
        <h1 class="text-base font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Keranjang</h1>
        <div class="w-9"></div>
    </div>

    <!-- Main Content -->
    <div class="px-4 pt-3 flex flex-col gap-3">

        <!-- Cart Item 1: Titik Temu -->
        <div class="p-3.5 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col gap-3">
            <div class="flex items-start gap-3">
                <div class="w-16 h-20 bg-slate-50 rounded-xl flex items-center justify-center p-1 border border-slate-100">
                    <img src="{{ asset('products/Titik Temu.png') }}" alt="Titik Temu" class="w-full h-full object-contain" />
                </div>
                <div class="flex-1 flex flex-col justify-between self-stretch">
                    <div>
                        <div class="flex justify-between items-start">
                            <h3 class="font-bold text-slate-900 text-sm font-['Plus_Jakarta_Sans']">Titik Temu</h3>
                            <button class="text-red-500 hover:text-red-700 transition-colors">
                                <span class="material-symbols-outlined text-lg">delete</span>
                            </button>
                        </div>
                        <p class="text-slate-500 text-xs">Kopi Susu Gula Aren 250ml</p>
                        <p class="text-slate-600 text-xs mt-0.5">Rp 10.000 / btl</p>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t border-slate-100 mt-2">
                        <span class="text-blue-900 font-extrabold text-sm font-['Plus_Jakarta_Sans']">Rp 20.000</span>
                        <div class="flex items-center gap-2 bg-slate-50 px-2 py-0.5 rounded-full border border-slate-200">
                            <button class="w-6 h-6 bg-white rounded-full border border-slate-300 text-slate-700 flex items-center justify-center font-bold text-xs hover:bg-slate-100">
                                <span class="material-symbols-outlined text-xs">remove</span>
                            </button>
                            <span class="text-xs font-bold text-slate-800 w-4 text-center">2</span>
                            <button class="w-6 h-6 bg-blue-800 rounded-full text-white flex items-center justify-center font-bold text-xs shadow-sm hover:bg-blue-900">
                                <span class="material-symbols-outlined text-xs">add</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cart Item 2: Titik Hitam -->
        <div class="p-3.5 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col gap-3">
            <div class="flex items-start gap-3">
                <div class="w-16 h-20 bg-slate-50 rounded-xl flex items-center justify-center p-1 border border-slate-100">
                    <img src="{{ asset('products/Titik Hitam.png') }}" alt="Titik Hitam" class="w-full h-full object-contain" />
                </div>
                <div class="flex-1 flex flex-col justify-between self-stretch">
                    <div>
                        <div class="flex justify-between items-start">
                            <h3 class="font-bold text-slate-900 text-sm font-['Plus_Jakarta_Sans']">Titik Hitam</h3>
                            <button class="text-red-500 hover:text-red-700 transition-colors">
                                <span class="material-symbols-outlined text-lg">delete</span>
                            </button>
                        </div>
                        <p class="text-slate-500 text-xs">Americano 250ml</p>
                        <p class="text-slate-600 text-xs mt-0.5">Rp 8.000 / btl</p>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t border-slate-100 mt-2">
                        <span class="text-blue-900 font-extrabold text-sm font-['Plus_Jakarta_Sans']">Rp 8.000</span>
                        <div class="flex items-center gap-2 bg-slate-50 px-2 py-0.5 rounded-full border border-slate-200">
                            <button class="w-6 h-6 bg-white rounded-full border border-slate-300 text-slate-700 flex items-center justify-center font-bold text-xs hover:bg-slate-100">
                                <span class="material-symbols-outlined text-xs">remove</span>
                            </button>
                            <span class="text-xs font-bold text-slate-800 w-4 text-center">1</span>
                            <button class="w-6 h-6 bg-blue-800 rounded-full text-white flex items-center justify-center font-bold text-xs shadow-sm hover:bg-blue-900">
                                <span class="material-symbols-outlined text-xs">add</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upsell Row -->
        <div class="grid grid-cols-2 gap-2.5">
            <div class="p-2.5 bg-white rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-800 flex items-center justify-center">
                        <span class="material-symbols-outlined text-base">coffee</span>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold text-slate-900 leading-tight">Titik Manja</div>
                        <div class="text-[10px] text-blue-900 font-semibold">Rp 10.000</div>
                    </div>
                </div>
                <button class="w-6 h-6 rounded-full bg-slate-100 text-blue-900 flex items-center justify-center hover:bg-blue-100 transition-colors">
                    <span class="material-symbols-outlined text-xs">add</span>
                </button>
            </div>
            <div class="p-2.5 bg-white rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-800 flex items-center justify-center">
                        <span class="material-symbols-outlined text-base">coffee</span>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold text-slate-900 leading-tight">Titik Jenuh</div>
                        <div class="text-[10px] text-blue-900 font-semibold">Rp 12.000</div>
                    </div>
                </div>
                <button class="w-6 h-6 rounded-full bg-slate-100 text-blue-900 flex items-center justify-center hover:bg-blue-100 transition-colors">
                    <span class="material-symbols-outlined text-xs">add</span>
                </button>
            </div>
        </div>

        <!-- Notes Card -->
        <div class="p-3.5 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col gap-2">
            <div class="flex items-center gap-1.5 text-xs font-bold text-slate-900">
                <span class="material-symbols-outlined text-blue-900 text-sm">edit_note</span>
                <span>Catatan Pesanan untuk Barista</span>
            </div>
            <textarea rows="2" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-800 resize-none" placeholder="Contoh: Es batu dipisah, kurangi manis arennya..."></textarea>
            <div class="text-right text-[10px] text-slate-400">0/150</div>
        </div>

        <!-- Billing Summary -->
        <div class="p-3.5 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col gap-2">
            <div class="flex justify-between items-center text-xs">
                <span class="font-bold text-slate-900">Ringkasan Biaya</span>
                <span class="text-slate-500 text-[11px]">Kopi Tiga Titik</span>
            </div>
            <div class="flex flex-col gap-1.5 pt-1 text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal Produk (3 Botol)</span>
                    <span class="font-semibold text-slate-800">Rp 28.000</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span class="flex items-center gap-1">Estimasi Kemasan & Es Pendingin ❄️</span>
                    <span class="font-bold text-emerald-600">Gratis</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span class="flex items-center gap-1">Voucher Diskon Ongkir 🎟️</span>
                    <span class="text-slate-500">- Rp 0</span>
                </div>
            </div>
            <div class="border-t border-dashed border-slate-200 my-1"></div>
            <div class="flex justify-between items-center">
                <span class="text-xs font-bold text-slate-900">Total Sementara</span>
                <span class="text-base font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">Rp 28.000</span>
            </div>
        </div>

        <!-- Hygiene Badge -->
        <div class="flex items-center justify-center gap-1.5 py-1 text-slate-400 text-xs">
            <span class="material-symbols-outlined text-sm">verified_user</span>
            <span>Kopi botol higienis & diseduh segar tiap hari</span>
        </div>

    </div>

    <!-- Fixed Bottom Action Bar -->
    <div class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-96 bg-white/95 backdrop-blur-md border-t border-slate-200 px-4 py-3 flex justify-between items-center z-50 shadow-[0_-4px_15px_rgba(0,0,0,0.05)]">
        <div>
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">TOTAL PESANAN</div>
            <div class="text-lg font-extrabold text-blue-900 font-['Plus_Jakarta_Sans'] leading-tight">Rp 28.000</div>
            <div class="text-[10px] text-emerald-600 font-semibold flex items-center gap-1 mt-0.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <span>3 Botol Segar</span>
            </div>
        </div>
        <a href="{{ url('/checkout') }}" class="px-5 py-2.5 bg-blue-900 hover:bg-blue-800 text-white rounded-xl font-bold text-xs flex items-center gap-1.5 shadow-md transition-colors">
            <span>Lanjut Pembayaran</span>
            <span class="material-symbols-outlined text-sm font-bold">arrow_forward</span>
        </a>
    </div>

</div>
</body>
</html>
