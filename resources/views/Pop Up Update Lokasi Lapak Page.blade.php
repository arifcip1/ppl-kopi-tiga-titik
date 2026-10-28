<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Update Lokasi Lapak - Kopi Tiga Titik</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Tailwind CSS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-800/60 min-h-screen flex justify-center items-end sm:items-center py-0 sm:py-6 antialiased font-sans backdrop-blur-xs">
<div class="w-full max-w-96 bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl relative flex flex-col justify-start p-5 border border-slate-100 animate-in fade-in slide-in-from-bottom duration-300">

    <!-- Modal Grab Handle -->
    <div class="w-12 h-1.5 bg-slate-200 rounded-full mx-auto mb-3"></div>

    <!-- Header -->
    <div class="flex justify-between items-start mb-4">
        <div>
            <h2 class="text-base font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Update Lokasi Lapak Hari Ini</h2>
            <p class="text-xs text-slate-500 mt-0.5">Atur titik mangkal street coffee untuk pelanggan ambil di tempat</p>
        </div>
        <a href="{{ url('/owner/dashboard') }}" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-600 transition-colors">
            <span class="material-symbols-outlined text-base">close</span>
        </a>
    </div>

    <!-- Form Content -->
    <div class="flex flex-col gap-3.5">

        <!-- Status Lapak Saat Ini -->
        <div>
            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">STATUS LAPAK SAAT INI</label>
            <div class="flex items-center gap-2">
                <button class="px-4 py-2 bg-emerald-50 text-emerald-700 border-2 border-emerald-500 rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Buka</span>
                </button>
                <button class="px-4 py-2 bg-white text-slate-500 border border-slate-200 hover:bg-slate-50 rounded-xl text-xs font-medium flex items-center gap-1.5 transition-colors">
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                    <span>Tutup</span>
                </button>
            </div>
        </div>

        <!-- Nama Tempat / Patokan Mangkal -->
        <div>
            <label class="text-[11px] font-semibold text-slate-700 block mb-1 flex items-center gap-1">
                <span class="material-symbols-outlined text-xs text-blue-900">location_on</span>
                <span>Nama Tempat / Patokan Mangkal</span>
            </label>
            <div class="flex items-center gap-2 p-2.5 bg-slate-50 border border-slate-200 rounded-xl">
                <input type="text" value="Jl. Benteng Pancasila (Depan Taman)" class="bg-transparent text-xs text-slate-900 font-medium flex-1 focus:outline-none" />
                <span class="material-symbols-outlined text-slate-400 text-sm">edit</span>
            </div>
        </div>

        <!-- Preview Pin Koordinat -->
        <div>
            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">PREVIEW PIN KOORDINAT</label>
            <div class="w-full h-36 bg-slate-100 rounded-2xl border border-slate-200 relative overflow-hidden flex items-center justify-center">
                <!-- Map Background -->
                <div class="absolute inset-0 bg-[linear-gradient(to_right,#cbd5e1_1px,transparent_1px),linear-gradient(to_bottom,#cbd5e1_1px,transparent_1px)] bg-[size:24px_24px] opacity-50"></div>
                
                <!-- Geser Pin Button -->
                <button class="absolute top-2 right-2 px-2.5 py-1 bg-white/95 text-slate-800 text-[10px] font-bold rounded-lg shadow-sm border border-slate-200 flex items-center gap-1 hover:bg-slate-50">
                    <span class="material-symbols-outlined text-xs">open_with</span>
                    <span>Geser Pin Lokasi</span>
                </button>

                <!-- Road Label -->
                <div class="absolute bottom-6 left-3 text-[9px] font-bold text-slate-500 uppercase tracking-wider">
                    JL. BENTENG PANCASILA
                </div>

                <!-- Pin Marker -->
                <div class="relative z-10 flex flex-col items-center">
                    <div class="w-7 h-7 rounded-full bg-blue-900 text-white flex items-center justify-center shadow-lg border-2 border-white ring-4 ring-blue-300/50">
                        <span class="w-2.5 h-2.5 bg-white rounded-full"></span>
                    </div>
                    <div class="mt-1 px-2 py-0.5 bg-slate-900/90 text-white text-[10px] font-bold rounded-full shadow-md flex items-center gap-1 whitespace-nowrap">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        <span>Lapak Kopi Tiga Titik</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Jam Operasional Lapak -->
        <div>
            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">JAM OPERASIONAL LAPAK</label>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <span class="text-[10px] text-slate-500 flex items-center gap-1 mb-1">
                        <span class="material-symbols-outlined text-xs">schedule</span>
                        <span>Jam Buka</span>
                    </span>
                    <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-xl flex justify-between items-center text-xs font-bold text-slate-800 cursor-pointer">
                        <span>15:00 WIB</span>
                        <span class="material-symbols-outlined text-slate-400 text-xs">expand_more</span>
                    </div>
                </div>
                <div>
                    <span class="text-[10px] text-slate-500 flex items-center gap-1 mb-1">
                        <span class="material-symbols-outlined text-xs">schedule</span>
                        <span>Jam Tutup</span>
                    </span>
                    <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-xl flex justify-between items-center text-xs font-bold text-slate-800 cursor-pointer">
                        <span>18:00 WIB</span>
                        <span class="material-symbols-outlined text-slate-400 text-xs">expand_more</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col gap-2 pt-2">
            <a href="{{ url('/owner/dashboard') }}" class="w-full py-3 bg-blue-900 hover:bg-blue-800 text-white rounded-xl font-bold text-xs flex justify-center items-center gap-1.5 shadow-md transition-colors">
                <span class="material-symbols-outlined text-base">check_circle</span>
                <span>Simpan</span>
            </a>
            <a href="{{ url('/owner/dashboard') }}" class="w-full py-2 text-center text-xs text-slate-500 hover:text-slate-800 font-semibold transition-colors">
                Batalkan
            </a>
        </div>

    </div>

</div>
</body>
</html>
