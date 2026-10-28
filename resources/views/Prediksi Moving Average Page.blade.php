<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Prediksi Penjualan Moving Average - Kopi Tiga Titik</title>

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
        <a href="{{ url('/owner/dashboard') }}" class="w-9 h-9 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-800 transition-colors">
            <span class="material-symbols-outlined text-xl">arrow_back</span>
        </a>
        <div class="text-center">
            <h1 class="text-base font-bold text-slate-900 font-['Plus_Jakarta_Sans'] leading-tight">Prediksi Penjualan</h1>
            <p class="text-[11px] text-blue-900 font-medium leading-none">Kopi Tiga Titik</p>
        </div>
        <div class="w-9"></div>
    </div>

    <!-- Main Content -->
    <div class="px-4 pt-3 flex flex-col gap-3.5">

        <!-- Method Card -->
        <div class="p-4 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col gap-2.5">
            <div>
                <span class="px-3 py-1 bg-blue-900 text-white rounded-full text-xs font-bold inline-flex items-center gap-1.5 shadow-xs">
                    <span class="material-symbols-outlined text-xs">calculate</span>
                    <span>Metode : Moving Average</span>
                </span>
            </div>

            <div class="p-3 bg-blue-50/60 rounded-xl border border-blue-100 flex flex-col gap-1 text-xs">
                <div class="flex justify-between items-center text-slate-600">
                    <span>Formula Standar:</span>
                    <strong class="text-blue-900 font-bold">(Minggu 1 + Minggu 2 + Minggu 3) ÷ 3</strong>
                </div>
                <div class="flex justify-between items-center text-slate-600">
                    <span>Target Produksi Harian:</span>
                    <strong class="text-slate-900">10 Botol/Hari / Varian</strong>
                </div>
            </div>

            <div class="flex items-center gap-1.5 text-[11px] text-slate-500 pt-0.5">
                <span class="material-symbols-outlined text-xs">schedule</span>
                <span>Periode Analisis: 3 Minggu Terakhir (Data Riwayat Penjualan)</span>
            </div>
        </div>

        <!-- Quick Stats Grid -->
        <div class="grid grid-cols-2 gap-2.5">
            <!-- Prediksi Minggu Depan -->
            <div class="p-3.5 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col justify-between">
                <div class="flex justify-between items-center">
                    <span class="text-[11px] font-bold text-slate-500">Prediksi Minggu Depan</span>
                    <span class="material-symbols-outlined text-blue-900 text-lg">calendar_month</span>
                </div>
                <div class="mt-2">
                    <div class="text-2xl font-extrabold text-slate-900 font-['Plus_Jakarta_Sans']">184 <span class="text-xs font-normal text-slate-500">Botol</span></div>
                    <span class="inline-block mt-1 px-2 py-0.5 bg-emerald-50 text-emerald-700 font-bold text-[10px] rounded-full border border-emerald-200">
                        +12% vs minggu lalu
                    </span>
                </div>
            </div>

            <!-- Varian Terlaris -->
            <div class="p-3.5 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col justify-between">
                <div class="flex justify-between items-center">
                    <span class="text-[11px] font-bold text-slate-500">Varian Terlaris</span>
                    <span class="material-symbols-outlined text-amber-500 text-lg">local_fire_department</span>
                </div>
                <div class="mt-2">
                    <div class="text-sm font-extrabold text-slate-900 font-['Plus_Jakarta_Sans'] leading-tight">Titik Temu</div>
                    <div class="text-[10px] text-slate-500">(Gula Aren)</div>
                    <span class="inline-block mt-1 px-2 py-0.5 bg-blue-50 text-blue-900 font-bold text-[10px] rounded-full border border-blue-200">
                        ⚡ 58 Botol/mg
                    </span>
                </div>
            </div>
        </div>

        <!-- Section: Rincian Prediksi per Varian -->
        <div class="flex flex-col gap-3">
            <h3 class="font-bold text-slate-900 text-xs">Rincian Prediksi per Varian</h3>

            <!-- Variant 1: Titik Temu -->
            <div class="p-4 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col gap-3">
                <div class="flex items-start gap-3">
                    <div class="w-14 h-20 bg-slate-50 rounded-xl p-1 flex items-center justify-center border border-slate-100 shrink-0">
                        <img src="{{ asset('products/Titik Temu.png') }}" alt="Titik Temu" class="w-full h-full object-contain" />
                    </div>
                    <div class="flex-1">
                        <span class="px-2 py-0.5 bg-blue-900 text-white font-bold text-[9px] rounded uppercase">BEST SELLER</span>
                        <h4 class="font-bold text-slate-900 text-xs mt-1">Titik Temu (Kopi Susu Gula Aren 250ml)</h4>
                        <p class="text-[10px] text-slate-500 mt-0.5">Signature Espresso, Fresh Milk, Organic Palm Sugar</p>
                    </div>
                </div>

                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200 flex justify-between items-center text-xs">
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">HISTORI PENJUALAN:</div>
                        <div class="font-semibold text-slate-800 text-[11px]">W1: 52 btl • W2: 58 btl • W3: 64 btl</div>
                    </div>
                    <span class="text-emerald-600 font-bold text-xs flex items-center gap-0.5">
                        <span class="material-symbols-outlined text-xs">trending_up</span>
                        <span>+23% Tren</span>
                    </span>
                </div>

                <div class="text-[11px] text-slate-600">
                    Kalkulasi SMA: <strong class="text-blue-900">(52 + 58 + 64) ÷ 3 = 58 Botol</strong>
                </div>

                <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 flex justify-between items-center">
                    <div>
                        <div class="text-xs font-bold text-emerald-900">Saran Produksi Minggu Depan:</div>
                        <div class="text-[10px] text-emerald-700">Alokasi Harian: ~8-9 botol/hari (Cadangan Weekend +15%)</div>
                    </div>
                    <span class="text-base font-extrabold text-emerald-700 font-['Plus_Jakarta_Sans']">58 Botol</span>
                </div>

                <div>
                    <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 font-bold text-[10px] rounded-full">Permintaan Tinggi</span>
                </div>
            </div>

            <!-- Variant 2: Titik Hitam -->
            <div class="p-4 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col gap-3">
                <div class="flex items-start gap-3">
                    <div class="w-14 h-20 bg-slate-50 rounded-xl p-1 flex items-center justify-center border border-slate-100 shrink-0">
                        <img src="{{ asset('products/Titik Hitam.png') }}" alt="Titik Hitam" class="w-full h-full object-contain" />
                    </div>
                    <div class="flex-1">
                        <span class="px-2 py-0.5 bg-neutral-800 text-white font-bold text-[9px] rounded uppercase">AMERICANO</span>
                        <h4 class="font-bold text-slate-900 text-xs mt-1">Titik Hitam (Americano / Cold Brew 250ml)</h4>
                        <p class="text-[10px] text-slate-500 mt-0.5">Arabica Specialty Blend, 16h Steep Cold Extraction</p>
                    </div>
                </div>

                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200 flex justify-between items-center text-xs">
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">HISTORI PENJUALAN:</div>
                        <div class="font-semibold text-slate-800 text-[11px]">W1: 30 btl • W2: 32 btl • W3: 31 btl</div>
                    </div>
                    <span class="text-slate-500 font-bold text-xs flex items-center gap-0.5">
                        <span>Stabil</span>
                    </span>
                </div>

                <div class="text-[11px] text-slate-600">
                    Kalkulasi SMA: <strong class="text-blue-900">(30 + 32 + 31) ÷ 3 = 31 Botol</strong>
                </div>

                <div class="p-3 bg-blue-50/70 rounded-xl border border-blue-200 flex justify-between items-center">
                    <div>
                        <div class="text-xs font-bold text-blue-900">Saran Produksi:</div>
                        <div class="text-[10px] text-blue-700">Alokasi Harian: ~4-5 botol/hari (Kebutuhan Stabil)</div>
                    </div>
                    <span class="text-base font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">31 Botol</span>
                </div>

                <div>
                    <span class="px-2.5 py-0.5 bg-slate-100 text-slate-700 font-bold text-[10px] rounded-full">✓ Stabil & Konsisten</span>
                </div>
            </div>

            <!-- Variant 3: Titik Manja -->
            <div class="p-4 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col gap-3">
                <div class="flex items-start gap-3">
                    <div class="w-14 h-20 bg-slate-50 rounded-xl p-1 flex items-center justify-center border border-slate-100 shrink-0">
                        <img src="{{ asset('products/Titik Manja.png') }}" alt="Titik Manja" class="w-full h-full object-contain" />
                    </div>
                    <div class="flex-1">
                        <span class="px-2 py-0.5 bg-amber-600 text-white font-bold text-[9px] rounded uppercase">KARAMEL</span>
                        <h4 class="font-bold text-slate-900 text-xs mt-1">Titik Manja (Kopi Karamel 250ml)</h4>
                        <p class="text-[10px] text-slate-500 mt-0.5">Signature Coffee, Handcrafted Salted Butter Caramel</p>
                    </div>
                </div>

                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200 flex justify-between items-center text-xs">
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">HISTORI PENJUALAN:</div>
                        <div class="font-semibold text-slate-800 text-[11px]">W1: 45 btl • W2: 48 btl • W3: 50 btl</div>
                    </div>
                    <span class="text-amber-600 font-bold text-xs flex items-center gap-0.5">
                        <span class="material-symbols-outlined text-xs">trending_up</span>
                        <span>+11% Tren</span>
                    </span>
                </div>

                <div class="text-[11px] text-slate-600">
                    Kalkulasi SMA: <strong class="text-blue-900">(45 + 48 + 50) ÷ 3 = 48 Botol</strong>
                </div>

                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 flex justify-between items-center">
                    <div>
                        <div class="text-xs font-bold text-amber-900">Saran Produksi:</div>
                        <div class="text-[10px] text-amber-700">Alokasi Harian: ~7 botol/hari (Potensi lonjakan sore)</div>
                    </div>
                    <span class="text-base font-extrabold text-amber-800 font-['Plus_Jakarta_Sans']">48 Botol</span>
                </div>

                <div>
                    <span class="px-2.5 py-0.5 bg-amber-100 text-amber-800 font-bold text-[10px] rounded-full">📈 Tren Meningkat</span>
                </div>
            </div>

            <!-- Variant 4: Titik Jenuh -->
            <div class="p-4 bg-white rounded-2xl shadow-sm border border-slate-200/80 flex flex-col gap-3">
                <div class="flex items-start gap-3">
                    <div class="w-14 h-20 bg-slate-50 rounded-xl p-1 flex items-center justify-center border border-slate-100 shrink-0">
                        <img src="{{ asset('products/Titik Jenuh.png') }}" alt="Titik Jenuh" class="w-full h-full object-contain" />
                    </div>
                    <div class="flex-1">
                        <span class="px-2 py-0.5 bg-stone-800 text-white font-bold text-[9px] rounded uppercase">NON-COFFEE</span>
                        <h4 class="font-bold text-slate-900 text-xs mt-1">Titik Jenuh (Cokelat Susu 250ml)</h4>
                        <p class="text-[10px] text-slate-500 mt-0.5">Pure Dark Cocoa, Fresh Milk, Less Sweetened</p>
                    </div>
                </div>

                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200 flex justify-between items-center text-xs">
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">HISTORI PENJUALAN:</div>
                        <div class="font-semibold text-slate-800 text-[11px]">W1: 42 btl • W2: 46 btl • W3: 47 btl</div>
                    </div>
                    <span class="text-blue-700 font-bold text-xs flex items-center gap-0.5">
                        <span class="material-symbols-outlined text-xs">trending_up</span>
                        <span>+5% Tren</span>
                    </span>
                </div>

                <div class="text-[11px] text-slate-600">
                    Kalkulasi SMA: <strong class="text-blue-900">(42 + 46 + 47) ÷ 3 = 45 Botol</strong>
                </div>

                <div class="p-3 bg-blue-50/70 rounded-xl border border-blue-200 flex justify-between items-center">
                    <div>
                        <div class="text-xs font-bold text-blue-900">Saran Produksi:</div>
                        <div class="text-[10px] text-blue-700">Alokasi Harian: ~6-7 botol/hari (Kebutuhan Konsisten)</div>
                    </div>
                    <span class="text-base font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">45 Botol</span>
                </div>

                <div>
                    <span class="px-2.5 py-0.5 bg-blue-100 text-blue-800 font-bold text-[10px] rounded-full">Kebutuhan Konsisten</span>
                </div>
            </div>

        </div>

        <!-- Download Button -->
        <button class="w-full py-3 bg-white hover:bg-slate-50 border-2 border-blue-900 text-blue-900 rounded-2xl font-bold text-xs flex justify-center items-center gap-2 shadow-xs transition-colors mt-1">
            <span class="material-symbols-outlined text-base">download</span>
            <span>Unduh Data Rekap (Excel)</span>
        </button>

    </div>

</div>
</body>
</html>
