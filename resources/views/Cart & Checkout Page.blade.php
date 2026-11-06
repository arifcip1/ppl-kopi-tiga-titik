@extends('layouts.pelanggan')

@section('title', 'Checkout & Pengiriman - Kopi Tiga Titik')
@section('active_menu', 'keranjang')
@section('page_title', 'Checkout & Pengiriman')
@section('role_badge', 'PELANGGAN')
@section('page_subtitle', 'Lengkapi detail penerima, tentukan titik lokasi pengantaran di peta, dan selesaikan pembayaran QRIS')

@push('styles')
    <!-- Leaflet CSS via CDN -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        /* Standarisasi Alignment Material Symbols agar selalu presisi di tengah */
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        /* Custom Leaflet Map Container & Control Stacking */
        #map {
            width: 100%;
            height: 320px;
            z-index: 10;
            border-radius: 1rem;
        }
        @media (min-width: 768px) {
            #map {
                height: 360px;
            }
        }
        .leaflet-container {
            font-family: 'Inter', sans-serif !important;
        }
        .leaflet-popup-content-wrapper {
            border-radius: 0.75rem;
            padding: 2px;
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }
        .leaflet-popup-content {
            margin: 8px 12px;
            font-size: 11px;
            line-height: 1.4;
            color: #1e293b;
            font-weight: 600;
        }
        /* Custom Map Pin Marker */
        .custom-pin-marker {
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: visible !important;
        }
    </style>
@endpush

@section('header_actions')
    <!-- Tombol Navigasi Kembali ke Keranjang Belanja -->
    <a href="{{ url('/cart') }}" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-colors inline-flex items-center gap-1.5 text-xs font-semibold shadow-xs">
        <span class="material-symbols-outlined text-base">arrow_back</span>
        <span class="hidden sm:inline">Kembali ke Keranjang</span>
    </a>
@endsection

@section('content')

    <!-- ========================================================================= -->
    <!-- 1. STEP PROGRESS INDICATOR & LAPAK BANNER -->
    <!-- ========================================================================= -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-3.5 items-stretch">
        
        <!-- Step Indicator -->
        <div class="md:col-span-7 bg-white p-3.5 rounded-2xl shadow-xs border border-slate-200/80 flex items-center justify-between">
            <!-- Step 1: Keranjang (Done) -->
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs shrink-0">
                    <span class="material-symbols-outlined text-sm font-bold">check</span>
                </div>
                <div>
                    <span class="text-xs font-bold text-slate-900 block leading-tight">Keranjang</span>
                    <span class="text-[10px] text-emerald-600 font-semibold hidden sm:block">Item Terpilih</span>
                </div>
            </div>

            <div class="flex-1 h-0.5 bg-emerald-200 mx-3"></div>

            <!-- Step 2: Checkout & Alamat (Active) -->
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-full bg-blue-900 text-white flex items-center justify-center font-bold text-xs shrink-0 ring-4 ring-blue-100">
                    2
                </div>
                <div>
                    <span class="text-xs font-bold text-blue-900 block leading-tight">Pengiriman</span>
                    <span class="text-[10px] text-blue-800 font-semibold hidden sm:block">Titik Lokasi</span>
                </div>
            </div>

            <div class="flex-1 h-0.5 bg-slate-200 mx-3"></div>

            <!-- Step 3: Pembayaran QRIS (Next) -->
            <div class="flex items-center gap-2 opacity-60">
                <div class="w-7 h-7 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center font-bold text-xs shrink-0">
                    3
                </div>
                <div>
                    <span class="text-xs font-bold text-slate-500 block leading-tight">Pembayaran</span>
                    <span class="text-[10px] text-slate-400 font-semibold hidden sm:block">QRIS Dinamis</span>
                </div>
            </div>
        </div>

        <!-- Lapak Info Banner -->
        <div class="md:col-span-5 bg-gradient-to-r from-blue-900 to-indigo-900 text-white p-3.5 rounded-2xl shadow-xs flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center shrink-0 border border-white/20">
                    <span class="material-symbols-outlined text-amber-300 text-xl">storefront</span>
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="text-xs font-bold font-['Plus_Jakarta_Sans']">Lapak Jl. Kuwung, Mojokerto</span>
                    </div>
                    <p class="text-[10px] text-blue-100">Jam Operasional: 15.00 - 18.00 WIB • Kurir Siaga</p>
                </div>
            </div>
            <span class="px-2.5 py-1 bg-white/15 rounded-lg text-[10px] font-bold tracking-tight border border-white/20 shrink-0">
                🛵 Delivery
            </span>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 2. FORM CHECKOUT & PETA LOKASI DENGAN LAYOUT RESPONSIVE 2-KOLOM -->
    <!-- ========================================================================= -->
    <form id="formCheckout" action="{{ url('/pembayaran-qris') }}" method="GET" onsubmit="handleCheckoutSubmit(event)">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
            
            <!-- ================================================================= -->
            <!-- KOLOM KIRI (7 SPAN): DATA PENERIMA, PETA LEAFLET & METODE BAYAR -->
            <!-- ================================================================= -->
            <div class="lg:col-span-7 flex flex-col gap-4">

                <!-- CARD 1: DATA PENERIMA PESANAN (FR-5.1) -->
                <div class="bg-white p-4 md:p-5 rounded-2xl shadow-xs border border-slate-200/80 flex flex-col gap-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-900 flex items-center justify-center shrink-0 shadow-2xs">
                                <span class="material-symbols-outlined text-lg">person</span>
                            </span>
                            <div>
                                <h2 class="text-sm font-bold text-slate-900 font-['Plus_Jakarta_Sans']">1. Informasi Penerima Pesanan</h2>
                                <p class="text-[11px] text-slate-500">Pastikan nomor telepon terhubung ke WhatsApp aktif untuk kurir</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-0.5 bg-blue-50 text-blue-900 border border-blue-200 text-[10px] font-bold rounded-lg">Wajib Diisi</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Nama Penerima -->
                        <div>
                            <label for="nama_penerima" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Nama Lengkap Penerima <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <span class="material-symbols-outlined text-[19px]">person</span>
                                </div>
                                <input type="text" 
                                       id="nama_penerima" 
                                       name="nama_penerima" 
                                       required
                                       value="{{ Auth::user()->name ?? 'Dimas Suryo' }}"
                                       placeholder="Contoh: Dimas Suryo" 
                                       class="w-full pl-11 pr-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 font-medium focus:bg-white focus:outline-none focus:border-blue-900 focus:ring-2 focus:ring-blue-900/10 transition-all placeholder:text-slate-400" />
                            </div>
                        </div>

                        <!-- Nomor Kontak / WhatsApp -->
                        <div>
                            <label for="telepon" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Nomor WhatsApp / HP <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <span class="material-symbols-outlined text-[19px]">call</span>
                                </div>
                                <input type="tel" 
                                       id="telepon" 
                                       name="telepon" 
                                       required
                                       value="{{ Auth::user()->telepon ?? '081234567890' }}"
                                       placeholder="08xxxxxxxxxx" 
                                       pattern="08[0-9]{8,12}"
                                       class="w-full pl-11 pr-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 font-medium focus:bg-white focus:outline-none focus:border-blue-900 focus:ring-2 focus:ring-blue-900/10 transition-all placeholder:text-slate-400" />
                            </div>
                            <span class="text-[10px] text-slate-400 mt-1 block">Format: 08xx (10 - 13 digit angka)</span>
                        </div>
                    </div>
                </div>

                <!-- CARD 2: TITIK PENGANTARAN KURIR & PETA INTERAKTIF LEAFLET (FR-5.2 & FR-7.1) -->
                <div class="bg-white p-4 md:p-5 rounded-2xl shadow-xs border border-slate-200/80 flex flex-col gap-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 shadow-2xs">
                                <span class="material-symbols-outlined text-lg">pin_drop</span>
                            </span>
                            <div>
                                <h2 class="text-sm font-bold text-slate-900 font-['Plus_Jakarta_Sans']">2. Titik Pengantaran Kurir (Peta Mojokerto)</h2>
                                <p class="text-[11px] text-slate-500">Geser pin pada peta atau klik tombol GPS agar kurir sampai tepat waktu</p>
                            </div>
                        </div>

                        <!-- Tombol Geolocation HTML5 -->
                        <button type="button" 
                                id="btn-use-location" 
                                onclick="getCurrentLocation()" 
                                class="px-3.5 py-2 bg-blue-50 hover:bg-blue-100 active:scale-95 text-blue-900 rounded-xl border border-blue-200 text-xs font-bold inline-flex items-center gap-2 transition-all cursor-pointer self-start sm:self-auto shadow-2xs">
                            <span class="material-symbols-outlined text-[18px] text-blue-900 leading-none">my_location</span>
                            <span id="btn-use-location-text">Gunakan Lokasi Saya</span>
                        </button>
                    </div>

                    <!-- Koordinat Indicator & Live Chip -->
                    <div class="flex flex-wrap items-center justify-between gap-2 bg-slate-50 p-2.5 rounded-xl border border-slate-200/70 text-xs">
                        <div class="flex items-center gap-2 text-slate-600 font-medium">
                            <span class="material-symbols-outlined text-lg text-emerald-600">location_searching</span>
                            <span>Koordinat Terpilih:</span>
                            <span class="font-mono font-bold text-slate-900 bg-white px-2.5 py-0.5 rounded border border-slate-200 text-[11px]" id="display-coords">
                                -7.472600, 112.438500
                            </span>
                        </div>
                        <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded-md flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Akurasi GPS Mojokerto</span>
                        </span>
                    </div>

                    <!-- Leaflet Map Container -->
                    <div class="relative overflow-hidden rounded-2xl border border-slate-300">
                        <div id="map"></div>
                        <!-- Petunjuk Overlay di Peta -->
                        <div class="absolute bottom-2.5 left-2.5 z-20 bg-white/95 backdrop-blur-xs px-3 py-1.5 rounded-xl border border-slate-200/90 shadow-xs text-[10px] text-slate-700 font-medium flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-xs text-blue-900">touch_app</span>
                            <span>Klik di peta atau geser pin kopi untuk memindahkan titik</span>
                        </div>
                    </div>

                    <!-- Hidden Inputs Koordinat (Untuk Payload Backend Form Submission) -->
                    <input type="hidden" name="lat" id="input_lat" value="-7.472600">
                    <input type="hidden" name="lng" id="input_lng" value="112.438500">

                    <!-- Detail Alamat Lengkap (Textarea) -->
                    <div>
                        <label for="alamat" class="flex items-center gap-1.5 text-xs font-bold text-slate-700 mb-1.5">
                            <span class="material-symbols-outlined text-base text-slate-500">home_pin</span>
                            <span>Alamat Lengkap & Patokan Rumah / Kantor</span>
                            <span class="text-rose-500">*</span>
                        </label>
                        <textarea id="alamat" 
                                  name="alamat" 
                                  rows="3" 
                                  required
                                  placeholder="Tuliskan nama jalan, nomor rumah, RT/RW, kelurahan, dan patokan (Contoh: Jl. Kuwung No. 24, RT 02 / RW 01, Kel. Mergelo, pagar hitam depan pos satpam)" 
                                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 font-medium focus:bg-white focus:outline-none focus:border-blue-900 focus:ring-2 focus:ring-blue-900/10 transition-all leading-relaxed placeholder:text-slate-400">Jl. Kuwung No. 24, RT 02 / RW 01, Kel. Mergelo, Kota Mojokerto (Pagar hitam samping warung kelontong)</textarea>
                    </div>

                    <!-- Catatan Khusus Barista & Kurir (Opsional) -->
                    <div>
                        <label for="catatan" class="flex items-center gap-1.5 text-xs font-bold text-slate-700 mb-1.5">
                            <span class="material-symbols-outlined text-base text-slate-500">sticky_note_2</span>
                            <span>Catatan Khusus untuk Barista & Kurir</span>
                            <span class="text-slate-400 font-normal text-[11px]">(Opsional)</span>
                        </label>
                        <textarea id="catatan" 
                                  name="catatan" 
                                  rows="2" 
                                  placeholder="Contoh: Titik Temu mohon less sugar, jika sampai tolong bunyikan bel pagar / WA" 
                                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 font-medium focus:bg-white focus:outline-none focus:border-blue-900 focus:ring-2 focus:ring-blue-900/10 transition-all leading-relaxed placeholder:text-slate-400"></textarea>
                    </div>
                </div>

                <!-- CARD 3: PILIHAN METODE PEMBAYARAN (FR-6.3 - QRIS Dinamis) -->
                <div class="bg-white p-4 md:p-5 rounded-2xl shadow-xs border border-slate-200/80 flex flex-col gap-3.5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-800 flex items-center justify-center shrink-0 shadow-2xs">
                                <span class="material-symbols-outlined text-lg">payments</span>
                            </span>
                            <div>
                                <h2 class="text-sm font-bold text-slate-900 font-['Plus_Jakarta_Sans']">3. Metode Pembayaran</h2>
                                <p class="text-[11px] text-slate-500">Pembayaran instan dan otomatis terverifikasi sistem</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-800 border border-emerald-200 text-[10px] font-bold rounded-lg">Verifikasi Otomatis</span>
                    </div>

                    <!-- Pilihan QRIS (Active Card) -->
                    <label class="relative block p-4 bg-blue-50/50 border-2 border-blue-900 rounded-2xl cursor-pointer hover:bg-blue-50/80 transition-colors">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3">
                                <div class="pt-0.5">
                                    <input type="radio" 
                                           name="metode_pembayaran" 
                                           value="qris" 
                                           checked 
                                           class="w-4 h-4 text-blue-900 border-slate-300 focus:ring-blue-900 cursor-pointer">
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-xs font-bold text-slate-900 font-['Plus_Jakarta_Sans']">QRIS (Midtrans Sandbox / QRIS Dinamis)</span>
                                        <span class="px-2 py-0.5 bg-blue-900 text-white font-extrabold text-[9px] rounded uppercase tracking-wider">INSTAN</span>
                                    </div>
                                    <p class="text-[11px] text-slate-600 mt-1 leading-relaxed">
                                        Scan barcode QRIS dengan aplikasi m-Banking (BCA, Mandiri Livin, BRImo, BNI) atau e-Wallet favoritmu (GoPay, OVO, Dana, ShopeePay).
                                    </p>
                                    <div class="flex items-center gap-2 mt-2 pt-2 border-t border-blue-100/80 flex-wrap">
                                        <span class="px-2 py-0.5 bg-white rounded-md border border-slate-200 text-[9px] font-bold text-slate-700 shadow-2xs">GoPay</span>
                                        <span class="px-2 py-0.5 bg-white rounded-md border border-slate-200 text-[9px] font-bold text-slate-700 shadow-2xs">OVO</span>
                                        <span class="px-2 py-0.5 bg-white rounded-md border border-slate-200 text-[9px] font-bold text-slate-700 shadow-2xs">DANA</span>
                                        <span class="px-2 py-0.5 bg-white rounded-md border border-slate-200 text-[9px] font-bold text-slate-700 shadow-2xs">ShopeePay</span>
                                        <span class="px-2 py-0.5 bg-white rounded-md border border-slate-200 text-[9px] font-bold text-slate-700 shadow-2xs">Semua Bank</span>
                                    </div>
                                </div>
                            </div>
                            <div class="w-11 h-11 rounded-xl bg-white border border-blue-200 flex items-center justify-center shrink-0 shadow-2xs">
                                <span class="material-symbols-outlined text-blue-900 text-2xl">qr_code_2</span>
                            </div>
                        </div>
                    </label>

                    <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200/70 flex items-center gap-2 text-[11px] text-slate-500">
                        <span class="material-symbols-outlined text-base text-blue-900 shrink-0">info</span>
                        <span>QRIS dinamis berlaku selama 10 menit setelah tombol pesanan dikonfirmasi.</span>
                    </div>
                </div>

            </div>

            <!-- ================================================================= -->
            <!-- KOLOM KANAN (5 SPAN): RINGKASAN BELANJA & TOMBOL PESAN (STICKY) -->
            <!-- ================================================================= -->
            <div class="lg:col-span-5 flex flex-col gap-4 lg:sticky lg:top-20">

                <!-- CARD 4: DAFTAR ITEM PESANAN (FR-5.6) -->
                <div class="bg-white p-4 md:p-5 rounded-2xl shadow-xs border border-slate-200/80 flex flex-col gap-3.5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-900 flex items-center justify-center shrink-0 shadow-2xs">
                                <span class="material-symbols-outlined text-lg">shopping_bag</span>
                            </span>
                            <h2 class="text-sm font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Ringkasan Pesanan</h2>
                        </div>
                        <a href="{{ url('/cart') }}" class="text-xs font-bold text-blue-900 hover:text-blue-800 hover:underline inline-flex items-center gap-1">
                            <span>Ubah</span>
                            <span class="material-symbols-outlined text-sm">edit</span>
                        </a>
                    </div>

                    <!-- List Produk (2 Item Snapshot Kopi Botol 250ml) -->
                    <div class="flex flex-col gap-3">
                        <!-- Item 1: Titik Temu -->
                        <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200/60 flex items-center gap-3">
                            <div class="w-12 h-14 bg-white rounded-lg p-1 border border-slate-200/80 flex items-center justify-center shrink-0">
                                <img src="{{ asset('products/Titik Temu.png') }}" 
                                     alt="Titik Temu" 
                                     class="w-full h-full object-contain"
                                     onerror="this.src='https://placehold.co/100x120/1e3a8a/ffffff?text=Temu'" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-1">
                                    <h4 class="text-xs font-bold text-slate-900 truncate font-['Plus_Jakarta_Sans']">Titik Temu</h4>
                                    <span class="text-xs font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">Rp 20.000</span>
                                </div>
                                <p class="text-[10px] text-slate-500">Kopi Susu Gula Aren 250ml</p>
                                <div class="flex items-center justify-between text-[10px] text-slate-600 mt-1 font-semibold">
                                    <span>2 botol @ Rp 10.000</span>
                                    <span class="text-emerald-700 font-bold bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200">Botol Segar</span>
                                </div>
                            </div>
                        </div>

                        <!-- Item 2: Titik Hitam -->
                        <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200/60 flex items-center gap-3">
                            <div class="w-12 h-14 bg-white rounded-lg p-1 border border-slate-200/80 flex items-center justify-center shrink-0">
                                <img src="{{ asset('products/Titik Hitam.png') }}" 
                                     alt="Titik Hitam" 
                                     class="w-full h-full object-contain"
                                     onerror="this.src='https://placehold.co/100x120/1e3a8a/ffffff?text=Hitam'" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-1">
                                    <h4 class="text-xs font-bold text-slate-900 truncate font-['Plus_Jakarta_Sans']">Titik Hitam</h4>
                                    <span class="text-xs font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">Rp 8.000</span>
                                </div>
                                <p class="text-[10px] text-slate-500">Americano Dingin 250ml</p>
                                <div class="flex items-center justify-between text-[10px] text-slate-600 mt-1 font-semibold">
                                    <span>1 botol @ Rp 8.000</span>
                                    <span class="text-emerald-700 font-bold bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200">Botol Segar</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Item Badge -->
                    <div class="p-2 bg-blue-50/60 rounded-xl border border-blue-100 flex items-center justify-between text-xs text-blue-900 font-semibold">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm">local_cafe</span>
                            <span>Total Minuman:</span>
                        </span>
                        <span class="font-bold">3 Botol 250ml Siap Seduh</span>
                    </div>
                </div>

                <!-- CARD 5: RINCIAN BIAYA & TOTAL PEMBAYARAN (FR-5.6 & FR-5.8) -->
                <div class="bg-white p-4 md:p-5 rounded-2xl shadow-xs border border-slate-200/80 flex flex-col gap-3.5">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Rincian Pembayaran</h3>

                    <div class="flex flex-col gap-2.5 text-xs text-slate-700">
                        <div class="flex justify-between items-center">
                            <span>Subtotal Produk (3 Botol)</span>
                            <span class="font-bold text-slate-900">Rp 28.000</span>
                        </div>

                        <!-- Ongkir Sementara Rp 0 Sesuai FR-5.8 -->
                        <div class="flex justify-between items-center">
                            <span class="inline-flex items-center gap-1">
                                Ongkos Kirim Kurir
                                <span class="material-symbols-outlined text-xs text-slate-400" title="Gratis ongkir promo lokal Mojokerto">help</span>
                            </span>
                            <div class="flex items-center gap-1.5">
                                <span class="text-[10px] line-through text-slate-400">Rp 5.000</span>
                                <span class="font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200 text-[11px]">Rp 0 (Promo)</span>
                            </div>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="inline-flex items-center gap-1">
                                Kemasan Higienis & Es Pendingin ❄️
                            </span>
                            <span class="font-bold text-emerald-700">Gratis</span>
                        </div>
                    </div>

                    <div class="border-t border-slate-200 my-0.5"></div>

                    <!-- Total Akhir -->
                    <div class="flex justify-between items-baseline">
                        <div>
                            <span class="text-xs font-bold text-slate-500 block uppercase tracking-wider">Total Tagihan</span>
                            <span class="text-[10px] text-slate-400 font-medium">Termasuk PPN & Jasa Pengantaran</span>
                        </div>
                        <div class="text-right">
                            <span class="text-2xl font-extrabold text-blue-900 font-['Plus_Jakarta_Sans'] block leading-none">Rp 28.000</span>
                        </div>
                    </div>

                    <!-- ========================================================= -->
                    <!-- PERINGATAN WAJIB PRD (FR-5.2) -->
                    <!-- ========================================================= -->
                    <div class="p-3 bg-amber-50 rounded-xl border border-amber-200/80 flex items-start gap-2.5 text-amber-900">
                        <span class="material-symbols-outlined text-amber-600 text-lg shrink-0 mt-0.5">warning</span>
                        <div class="text-[11px] leading-relaxed">
                            <strong class="font-bold block text-amber-950 mb-0.5">Peringatan Penting:</strong>
                            Jika pesanan sudah dibayar, pesanan langsung masuk ke antrean racik barista dan <strong>tidak dapat dibatalkan atau diubah</strong>.
                        </div>
                    </div>

                    <!-- TOMBOL SUBMIT & PENCEGAHAN DOUBLE SUBMIT (FR-5.2) -->
                    <button type="submit" 
                            id="btn-submit-order" 
                            class="w-full py-3.5 bg-blue-900 hover:bg-blue-800 active:scale-98 text-white rounded-xl font-bold text-sm inline-flex items-center justify-center gap-2 shadow-md shadow-blue-900/20 transition-all cursor-pointer">
                        <span class="material-symbols-outlined text-lg leading-none" id="btn-submit-icon">qr_code_2</span>
                        <span id="btn-submit-text">Buat Pesanan & Bayar QRIS (Rp 28.000)</span>
                    </button>

                    <!-- Jaminan & Keamanan -->
                    <div class="inline-flex items-center justify-center gap-2 text-[10px] text-slate-400 font-medium pt-1">
                        <span class="material-symbols-outlined text-sm text-emerald-600">verified_user</span>
                        <span>Pembayaran Aman Enkripsi 256-Bit via QRIS Midtrans</span>
                    </div>
                </div>

            </div>

        </div>
    </form>

@endsection

@push('scripts')
    <!-- Leaflet JS via CDN -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        // Koordinat Default Lapak Kopi Tiga Titik Mojokerto (Jl. Kuwung, Mergelo, Mojokerto)
        const DEFAULT_LAT = -7.4726;
        const DEFAULT_LNG = 112.4385;

        let map;
        let marker;

        // Inisialisasi Peta Leaflet saat DOM siap
        document.addEventListener('DOMContentLoaded', function () {
            initLeafletMap();
        });

        function initLeafletMap() {
            const mapContainer = document.getElementById('map');
            if (!mapContainer) return;

            // 1. Buat instance Leaflet Map
            map = L.map('map', {
                center: [DEFAULT_LAT, DEFAULT_LNG],
                zoom: 15,
                scrollWheelZoom: false, // Hindari scroll halaman terganggu
            });

            // 2. Pasang Tile Layer OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> contributors'
            }).addTo(map);

            // 3. Custom Marker Icon (Pin Merah Modern dengan Icon Kopi)
            const pinIcon = L.divIcon({
                className: 'custom-pin-marker',
                html: `
                    <div style="position: relative; display: flex; flex-direction: column; align-items: center; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.25));">
                        <div style="width: 36px; height: 36px; background: #1e3a8a; border: 2.5px solid #ffffff; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                            <span class="material-symbols-outlined" style="font-size: 19px; line-height: 1;">local_cafe</span>
                        </div>
                        <div style="width: 0; height: 0; border-left: 6px solid transparent; border-right: 6px solid transparent; border-top: 8px solid #1e3a8a; margin-top: -1px;"></div>
                    </div>
                `,
                iconSize: [36, 44],
                iconAnchor: [18, 44],
                popupAnchor: [0, -44]
            });

            // 4. Tambahkan Marker Draggable
            marker = L.marker([DEFAULT_LAT, DEFAULT_LNG], {
                draggable: true,
                icon: pinIcon
            }).addTo(map);

            marker.bindPopup("<b>Titik Pengantaran Anda</b><br>Geser pin ke lokasi rumah Anda.").openPopup();

            // 5. Event Listener: Saat pin selesai digeser (dragend)
            marker.on('dragend', function (event) {
                const position = marker.getLatLng();
                updateCoordinates(position.lat, position.lng);
            });

            // 6. Event Listener: Klik langsung pada area peta
            map.on('click', function (event) {
                const lat = event.latlng.lat;
                const lng = event.latlng.lng;
                marker.setLatLng([lat, lng]);
                updateCoordinates(lat, lng);
            });

            // Invalidate size setelah delay kecil agar render tile sempurna di berbagai resolusi layar
            setTimeout(function () {
                map.invalidateSize();
            }, 300);
        }

        // Fungsi Update Koordinat ke Hidden Inputs dan Tampilan Teks
        function updateCoordinates(lat, lng) {
            const formattedLat = Number(lat).toFixed(6);
            const formattedLng = Number(lng).toFixed(6);

            const inputLat = document.getElementById('input_lat');
            const inputLng = document.getElementById('input_lng');
            const displayCoords = document.getElementById('display-coords');

            if (inputLat) inputLat.value = formattedLat;
            if (inputLng) inputLng.value = formattedLng;
            if (displayCoords) displayCoords.innerText = `${formattedLat}, ${formattedLng}`;

            if (marker) {
                marker.setPopupContent(`<b>Titik Pengantaran:</b><br>${formattedLat}, ${formattedLng}`);
            }
        }

        // Fungsi Ambil Lokasi Sekarang via HTML5 Geolocation API
        function getCurrentLocation() {
            const btn = document.getElementById('btn-use-location');
            const btnText = document.getElementById('btn-use-location-text');

            if (!navigator.geolocation) {
                alert("Perangkat Anda tidak mendukung fitur Geolocation.");
                return;
            }

            // Ubah tampilan tombol ke state loading
            btn.disabled = true;
            btnText.innerText = "Mencari GPS...";

            navigator.geolocation.getCurrentPosition(
                function (position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    // Pindahkan posisi peta dan marker
                    map.setView([lat, lng], 16);
                    marker.setLatLng([lat, lng]);
                    updateCoordinates(lat, lng);
                    marker.bindPopup("<b>Lokasi Anda Terdeteksi!</b><br>Titik pengantaran berhasil diperbarui.").openPopup();

                    // Kembalikan tampilan tombol
                    btn.disabled = false;
                    btnText.innerText = "Lokasi Ditemukan ✓";
                    setTimeout(() => {
                        btnText.innerText = "Gunakan Lokasi Saya";
                    }, 3000);
                },
                function (error) {
                    btn.disabled = false;
                    btnText.innerText = "Gunakan Lokasi Saya";
                    let msg = "Gagal mendapatkan lokasi GPS.";
                    if (error.code === error.PERMISSION_DENIED) {
                        msg = "Izin akses lokasi GPS ditolak oleh browser/perangkat Anda. Silakan geser pin manual pada peta.";
                    } else if (error.code === error.POSITION_UNAVAILABLE) {
                        msg = "Informasi lokasi tidak tersedia saat ini. Silakan klik manual pada peta.";
                    } else if (error.code === error.TIMEOUT) {
                        msg = "Permintaan lokasi GPS melebihi batas waktu (timeout).";
                    }
                    alert(msg);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }

        // Handler Submit Form & Pencegahan Double Submit (FR-5.2)
        function handleCheckoutSubmit(event) {
            const form = document.getElementById('formCheckout');
            const submitBtn = document.getElementById('btn-submit-order');
            const submitIcon = document.getElementById('btn-submit-icon');
            const submitText = document.getElementById('btn-submit-text');

            // Validasi input wajib
            const nama = document.getElementById('nama_penerima').value.trim();
            const telp = document.getElementById('telepon').value.trim();
            const alamat = document.getElementById('alamat').value.trim();

            if (!nama || !telp || !alamat) {
                event.preventDefault();
                alert("Harap lengkapi semua kolom wajib (Nama Penerima, Nomor WhatsApp, dan Alamat Pengiriman).");
                return false;
            }

            // Cegah double submit dengan menonaktifkan tombol dan memunculkan animasi loading
            submitBtn.disabled = true;
            submitBtn.classList.remove('hover:bg-blue-800', 'cursor-pointer');
            submitBtn.classList.add('opacity-75', 'cursor-not-allowed');

            submitIcon.innerHTML = `
                <svg class="animate-spin h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
            `;
            submitText.innerText = "Memproses Pesanan & Membuka QRIS...";

            // Lanjutkan navigasi/submit secara mulus
            return true;
        }
    </script>
@endpush
