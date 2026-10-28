<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pembayaran QRIS - Kopi Tiga Titik</title>

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
    <div class="bg-blue-900 text-white px-4 py-3 shadow-md flex items-center justify-between sticky top-0 z-30">
        <a href="{{ url('/checkout') }}" class="w-9 h-9 rounded-full hover:bg-white/10 flex items-center justify-center transition-colors">
            <span class="material-symbols-outlined text-xl">arrow_back</span>
        </a>
        <div class="text-center">
            <h1 class="text-base font-bold font-['Plus_Jakarta_Sans'] leading-tight">Pembayaran</h1>
            <p class="text-[11px] text-blue-200 leading-none">Lapak Kuwung • Mojokerto</p>
        </div>
        <button class="w-9 h-9 rounded-full hover:bg-white/10 flex items-center justify-center transition-colors">
            <span class="material-symbols-outlined text-xl">help</span>
        </button>
    </div>

    <!-- Main Content -->
    <div class="px-4 pt-3 flex flex-col gap-3.5">

        <!-- Countdown Timer Banner -->
        <div class="p-3.5 bg-rose-50 rounded-2xl border border-rose-200 flex flex-col gap-2">
            <div class="flex justify-between items-center text-xs">
                <div class="flex items-center gap-1.5 font-bold text-rose-900">
                    <span class="material-symbols-outlined text-rose-600 text-base">timer</span>
                    <span>Bayar Sebelum Habis</span>
                </div>
                <span class="px-2.5 py-0.5 bg-rose-700 text-white font-mono font-bold text-xs rounded-full">09:44</span>
            </div>
            <p class="text-[11px] text-rose-700">Pesanan otomatis dibatalkan jika melebihi batas waktu 10 menit</p>
            <div class="w-full h-1.5 bg-rose-200 rounded-full overflow-hidden">
                <div class="w-3/4 h-full bg-rose-600 rounded-full"></div>
            </div>
        </div>

        <!-- Total yang Harus Dibayar Card -->
        <div class="p-4 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col gap-2">
            <div class="flex justify-between items-center">
                <div>
                    <div class="text-[11px] text-slate-500 font-medium">Total yang Harus Dibayar</div>
                    <div class="text-2xl font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">Rp 28.000</div>
                </div>
                <button class="px-3 py-1.5 bg-blue-50 text-blue-900 hover:bg-blue-100 rounded-xl font-bold text-xs flex items-center gap-1 border border-blue-200 transition-colors">
                    <span class="material-symbols-outlined text-sm">content_copy</span>
                    <span>Salin</span>
                </button>
            </div>
            <div class="pt-2 border-t border-slate-100 flex items-center gap-1.5 text-[11px] text-slate-500">
                <span class="material-symbols-outlined text-xs text-blue-900">info</span>
                <span>Bayar tepat sesuai nominal yang ditentukan</span>
            </div>
        </div>

        <!-- QRIS Frame Card -->
        <div class="p-5 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col items-center gap-3 text-center">
            <div class="w-full flex justify-between items-center border-b border-slate-100 pb-2.5">
                <div class="flex items-center gap-1.5">
                    <span class="text-base font-black text-slate-900 tracking-tight">QRIS</span>
                    <span class="px-1.5 py-0.2 bg-red-600 text-white text-[8px] font-bold rounded">NASIONAL</span>
                </div>
                <div class="flex items-center gap-1 text-[10px] font-bold text-slate-600">
                    <span class="w-4 h-4 rounded-full border border-blue-900 text-blue-900 flex items-center justify-center text-[8px]">G</span>
                    <span>GPN</span>
                </div>
            </div>

            <div>
                <h3 class="text-sm font-bold text-slate-900 uppercase font-['Plus_Jakarta_Sans']">KOPI TIGA TITIK MOJOKERTO</h3>
                <p class="text-[11px] text-slate-500">NMID: ID1020039281920</p>
            </div>

            <!-- Stylized QR Code Box -->
            <div class="w-60 h-60 bg-white border-2 border-slate-900/10 rounded-2xl p-3 shadow-inner flex flex-col justify-between relative">
                <!-- SVG authentic looking QR Code with Logo Badge in center -->
                <svg viewBox="0 0 100 100" class="w-full h-full text-slate-900" fill="currentColor">
                    <!-- Corner Top-Left -->
                    <rect x="5" y="5" width="28" height="28" rx="4" fill="none" stroke="currentColor" stroke-width="6"/>
                    <rect x="12" y="12" width="14" height="14" rx="2"/>
                    <!-- Corner Top-Right -->
                    <rect x="67" y="5" width="28" height="28" rx="4" fill="none" stroke="currentColor" stroke-width="6"/>
                    <rect x="74" y="12" width="14" height="14" rx="2"/>
                    <!-- Corner Bottom-Left -->
                    <rect x="5" y="67" width="28" height="28" rx="4" fill="none" stroke="currentColor" stroke-width="6"/>
                    <rect x="12" y="74" width="14" height="14" rx="2"/>
                    
                    <!-- Random QR Matrix Blocks -->
                    <rect x="40" y="8" width="6" height="6"/>
                    <rect x="52" y="8" width="8" height="6"/>
                    <rect x="42" y="18" width="16" height="6"/>
                    <rect x="38" y="28" width="6" height="8"/>
                    <rect x="50" y="28" width="10" height="6"/>

                    <rect x="8" y="40" width="6" height="10"/>
                    <rect x="18" y="44" width="10" height="6"/>
                    <rect x="8" y="54" width="18" height="6"/>

                    <rect x="74" y="40" width="10" height="6"/>
                    <rect x="88" y="40" width="6" height="10"/>
                    <rect x="70" y="50" width="14" height="6"/>
                    <rect x="80" y="60" width="14" height="6"/>

                    <rect x="40" y="68" width="6" height="12"/>
                    <rect x="52" y="72" width="10" height="6"/>
                    <rect x="40" y="86" width="18" height="6"/>
                    <rect x="68" y="74" width="6" height="12"/>
                    <rect x="80" y="78" width="14" height="6"/>
                    <rect x="74" y="88" width="8" height="6"/>
                </svg>

                <!-- Center Brand Badge -->
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-11 h-11 bg-blue-900 text-white rounded-xl flex items-center justify-center shadow-md border-2 border-white">
                        <span class="font-extrabold text-sm tracking-widest leading-none">•••</span>
                    </div>
                </div>
            </div>

            <p class="text-xs text-slate-500 leading-tight">Scan QRIS menggunakan aplikasi m-Banking atau e-Wallet</p>

            <!-- Payment Providers Badges -->
            <div class="flex flex-wrap justify-center gap-1.5 pt-1">
                <span class="px-2 py-0.5 bg-blue-50 text-blue-900 rounded text-[10px] font-bold border border-blue-100">BCA</span>
                <span class="px-2 py-0.5 bg-blue-50 text-blue-900 rounded text-[10px] font-bold border border-blue-100">MANDIRI</span>
                <span class="px-2 py-0.5 bg-blue-50 text-blue-900 rounded text-[10px] font-bold border border-blue-100">GOPAY</span>
                <span class="px-2 py-0.5 bg-blue-50 text-blue-900 rounded text-[10px] font-bold border border-blue-100">OVO</span>
                <span class="px-2 py-0.5 bg-blue-50 text-blue-900 rounded text-[10px] font-bold border border-blue-100">DANA</span>
                <span class="px-2 py-0.5 bg-rose-50 text-rose-700 rounded text-[10px] font-bold border border-rose-100">SHOPEEPAY</span>
            </div>
        </div>

        <!-- Collapsible Order Details -->
        <div class="p-3.5 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex justify-between items-center text-xs font-bold text-slate-800 cursor-pointer">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-900 text-base">shopping_bag</span>
                <span>Rincian Pesanan (3 Botol)</span>
            </div>
            <span class="material-symbols-outlined text-slate-400 text-sm">expand_more</span>
        </div>

        <!-- Submit Button -->
        <a href="{{ url('/order-tracking') }}" class="w-full py-3.5 bg-blue-900 hover:bg-blue-800 text-white rounded-2xl font-bold text-xs flex justify-center items-center gap-2 shadow-md transition-colors">
            <span>Saya Sudah Membayar</span>
            <span class="material-symbols-outlined text-base">arrow_forward</span>
        </a>

        <!-- WhatsApp Help -->
        <div class="text-center text-xs text-slate-500 flex items-center justify-center gap-1 pt-1">
            <span class="material-symbols-outlined text-sm text-emerald-600">chat</span>
            <span>Butuh bantuan? Hubungi WhatsApp Lapak: <strong class="text-blue-900">0812-3456-7890</strong></span>
        </div>

    </div>

</div>
</body>
</html>
