@extends('layouts.pelanggan')

@section('title', 'Checkout & Pengiriman - Kopi Tiga Titik')
@section('active_menu', 'keranjang')
@section('page_title', 'Checkout & Pengiriman')
@section('role_badge', 'PELANGGAN')
@section('page_subtitle', 'Lengkapi detail penerima, tentukan titik lokasi tujuan atau pilih ambil di lapak, dan bayar via QRIS')

@push('styles')
    <!-- Leaflet CSS via CDN -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        /* Standarisasi Alignment Material Symbols */
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        /* Container Peta Leaflet Responsif */
        #map {
            width: 100%;
            height: 280px;
            z-index: 10;
        }
        @media (min-width: 640px) {
            #map {
                height: 330px;
            }
        }
        @media (min-width: 768px) {
            #map {
                height: 370px;
            }
        }
        .leaflet-container {
            font-family: 'Inter', sans-serif !important;
        }
        .leaflet-popup-content-wrapper {
            border-radius: 0.875rem;
            padding: 4px;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.15), 0 8px 10px -6px rgba(15, 23, 42, 0.1);
            border: 1px solid #e2e8f0;
        }
        .leaflet-popup-content {
            margin: 8px 12px;
            font-size: 11px;
            line-height: 1.45;
            color: #1e293b;
        }

        /* Custom Map Pin Marker dengan Efek Radar Pulse */
        .custom-pin-marker {
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: visible !important;
        }
        .pin-radar {
            position: absolute;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(30, 58, 138, 0.25);
            animation: pinPulse 2s ease-out infinite;
            pointer-events: none;
            top: -4px;
            left: -4px;
        }
        @keyframes pinPulse {
            0% {
                transform: scale(0.6);
                opacity: 1;
            }
            100% {
                transform: scale(1.6);
                opacity: 0;
            }
        }

        /* Marker Khusus Lapak Aktif */
        .custom-lapak-marker {
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
@endpush

@section('header_actions')
    <!-- Tombol Navigasi Kembali ke Keranjang Belanja -->
    <a href="{{ url('/cart') }}" class="px-3 sm:px-3.5 py-1.5 sm:py-2 bg-white hover:bg-slate-50 text-slate-700 rounded-xl border border-slate-200 transition-all inline-flex items-center gap-1.5 text-xs font-semibold shadow-2xs hover:shadow-xs active:scale-95">
        <span class="material-symbols-outlined text-base">arrow_back</span>
        <span class="hidden sm:inline">Kembali ke Keranjang</span>
    </a>
@endsection

@section('content')

    <!-- ========================================================================= -->
    <!-- 1. STEP PROGRESS INDICATOR & LAPAK BANNER (FR-11.4 & FR-11.6) -->
    <!-- ========================================================================= -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-3.5 sm:gap-4 items-stretch">
        
        <!-- Step Progress Indicator -->
        <div class="md:col-span-7 bg-white p-3.5 sm:p-4 md:p-5 rounded-2xl shadow-xs border border-slate-200/80 flex items-center justify-between">
            <!-- Step 1: Keranjang (Selesai) -->
            <a href="{{ url('/cart') }}" class="flex items-center gap-2 sm:gap-2.5 group cursor-pointer shrink-0" title="Kembali ke Keranjang">
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center font-bold text-xs shrink-0 group-hover:bg-emerald-100 transition-colors">
                    <span class="material-symbols-outlined text-sm sm:text-base font-bold">check</span>
                </div>
                <div>
                    <span class="text-xs font-bold text-slate-900 block leading-tight group-hover:text-blue-900 transition-colors hidden sm:block">1. Keranjang</span>
                    <span class="text-[10px] text-emerald-600 font-semibold hidden md:block">Item Terpilih</span>
                </div>
            </a>

            <!-- Divider 1 -> 2 -->
            <div class="flex-1 h-0.5 bg-emerald-300 mx-2 sm:mx-3 rounded-full min-w-4"></div>

            <!-- Step 2: Lokasi & Pengiriman (Aktif) -->
            <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-blue-900 text-white flex items-center justify-center font-bold text-xs shrink-0 ring-4 ring-blue-100 shadow-xs">
                    2
                </div>
                <div>
                    <span class="text-xs font-bold text-blue-900 block leading-tight">Pengiriman</span>
                    <span class="text-[10px] text-blue-700 font-semibold hidden md:block">Lokasi & Ongkir</span>
                </div>
            </div>

            <!-- Divider 2 -> 3 -->
            <div class="flex-1 h-0.5 bg-slate-200 mx-2 sm:mx-3 rounded-full min-w-4"></div>

            <!-- Step 3: Pembayaran QRIS (Berikutnya) -->
            <div class="flex items-center gap-2 sm:gap-2.5 opacity-60 shrink-0">
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-slate-100 text-slate-500 border border-slate-200 flex items-center justify-center font-bold text-xs shrink-0">
                    3
                </div>
                <div>
                    <span class="text-xs font-bold text-slate-600 block leading-tight hidden sm:block">Pembayaran</span>
                    <span class="text-[10px] text-slate-400 font-semibold hidden md:block">QRIS Dinamis</span>
                </div>
            </div>
        </div>

        <!-- Lapak Info Banner Mojokerto (FR-11.4 & FR-11.6) -->
        <div class="md:col-span-5 bg-white p-3.5 sm:p-4 rounded-2xl shadow-xs border border-slate-200/80 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-blue-50 text-blue-900 flex items-center justify-center shrink-0 border border-blue-100 shadow-2xs">
                    <span class="material-symbols-outlined text-amber-500 text-lg sm:text-xl">storefront</span>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="w-2 h-2 rounded-full {{ ($statusLapak ?? 'buka') === 'buka' ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }} shrink-0"></span>
                        <span class="text-xs font-bold font-['Plus_Jakarta_Sans'] text-slate-900 truncate">{{ $lokasiAktif->nama_lokasi ?? 'Lapak Jl. Kuwung' }}</span>
                        @if(($statusLapak ?? 'buka') === 'buka')
                            <span class="px-1.5 py-0.2 bg-emerald-50 text-emerald-700 border border-emerald-200 text-[9px] font-bold rounded-full">Buka</span>
                        @else
                            <span class="px-1.5 py-0.2 bg-rose-50 text-rose-700 border border-rose-200 text-[9px] font-bold rounded-full">Tutup</span>
                        @endif
                    </div>
                    <p class="text-[10px] sm:text-[11px] text-slate-500 mt-0.5 truncate">{{ $jamBuka ?? '15:00' }} - {{ $jamTutup ?? '18:00' }} WIB • {{ ($statusLapak ?? 'buka') === 'buka' ? 'Kurir Siaga' : 'Lapak Tutup' }}</p>
                </div>
            </div>
            <span id="badge-metode-pemenuhan" class="px-2.5 sm:px-3 py-1 sm:py-1.5 bg-blue-50 text-blue-900 border border-blue-200 rounded-xl text-[11px] sm:text-xs font-bold tracking-tight shrink-0 flex items-center gap-1 shadow-2xs">
                <span class="material-symbols-outlined text-sm" id="badge-metode-icon">two_wheeler</span>
                <span id="badge-metode-text">Delivery</span>
            </span>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 2. FORM CHECKOUT & PETA LOKASI DENGAN LAYOUT RESPONSIVE 2-KOLOM -->
    <!-- ========================================================================= -->
    <form id="formCheckout" action="{{ route('checkout.process') }}" method="POST" onsubmit="return handleCheckoutSubmit(event)">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-5 items-start mt-1">
            
            <!-- ================================================================= -->
            <!-- KOLOM KIRI (7 SPAN): DATA PENERIMA, METODE PEMENUHAN & PETA LEAFLET -->
            <!-- ================================================================= -->
            <div class="lg:col-span-7 flex flex-col gap-4">

                <!-- CARD 1: DATA PENERIMA PESANAN (FR-5.1) -->
                <div class="bg-white p-4 md:p-5 rounded-2xl shadow-xs border border-slate-200/80 flex flex-col gap-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 gap-2">
                        <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                            <span class="w-9 h-9 rounded-xl bg-blue-50 text-blue-900 flex items-center justify-center shrink-0 border border-blue-100 shadow-2xs">
                                <span class="material-symbols-outlined text-lg">person</span>
                            </span>
                            <div class="min-w-0">
                                <h2 class="text-xs sm:text-sm font-bold text-slate-900 font-['Plus_Jakarta_Sans'] truncate">1. Informasi Penerima</h2>
                                <p class="text-[10px] sm:text-[11px] text-slate-500 truncate">Kontak aktif kurir atau konfirmasi pengambilan</p>
                            </div>
                        </div>
                        <span class="px-2 sm:px-2.5 py-0.5 sm:py-1 bg-blue-50 text-blue-900 border border-blue-200 text-[10px] font-bold rounded-full shrink-0">Wajib Diisi</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 sm:gap-4">
                        <!-- Nama Penerima -->
                        <div>
                            <label for="nama_penerima" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Nama Lengkap Penerima <span class="text-rose-500">*</span>
                            </label>
                            <div class="flex items-center bg-slate-50/80 border border-slate-300 rounded-xl px-3 py-2.5 hover:border-slate-400 focus-within:bg-white focus-within:border-blue-900 focus-within:ring-4 focus-within:ring-blue-900/10 transition-all">
                                <span class="material-symbols-outlined text-slate-400 text-lg mr-2 shrink-0 select-none">badge</span>
                                <input type="text" 
                                       id="nama_penerima" 
                                       name="nama_penerima" 
                                       required
                                       value="{{ old('nama_penerima', Auth::user()->name ?? 'Dimas Suryo') }}"
                                       placeholder="Contoh: Dimas Suryo" 
                                       class="w-full bg-transparent border-0 outline-none p-0 text-xs text-slate-900 font-medium placeholder:text-slate-400 focus:ring-0 focus:outline-none" />
                            </div>
                            <x-input-error for="nama_penerima" class="mt-1" />
                        </div>

                        <!-- Nomor Kontak / WhatsApp -->
                        <div>
                            <label for="telepon" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Nomor WhatsApp / HP <span class="text-rose-500">*</span>
                            </label>
                            <div class="flex items-center bg-slate-50/80 border border-slate-300 rounded-xl px-3 py-2.5 hover:border-slate-400 focus-within:bg-white focus-within:border-blue-900 focus-within:ring-4 focus-within:ring-blue-900/10 transition-all">
                                <span class="material-symbols-outlined text-slate-400 text-lg mr-2 shrink-0 select-none">call</span>
                                <input type="tel" 
                                       id="telepon" 
                                       name="telepon" 
                                       required
                                       value="{{ old('telepon', Auth::user()->telepon ?? '081234567890') }}"
                                       placeholder="08xxxxxxxxxx" 
                                       pattern="08[0-9]{8,12}"
                                       class="w-full bg-transparent border-0 outline-none p-0 text-xs text-slate-900 font-medium placeholder:text-slate-400 focus:ring-0 focus:outline-none" />
                            </div>
                            <x-input-error for="telepon" class="mt-1" />
                            <span class="text-[10px] text-slate-400 mt-1 block">Format: 08xx (10 - 13 digit angka WhatsApp aktif)</span>
                        </div>
                    </div>
                </div>

                <!-- CARD 2: PILIHAN METODE PEMENUHAN PESANAN (DELIVERY vs AMBIL SENDIRI - BR-41 & FR-14) -->
                <div class="bg-white p-4 md:p-5 rounded-2xl shadow-xs border border-slate-200/80 flex flex-col gap-3.5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 gap-2">
                        <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                            <span class="w-9 h-9 rounded-xl bg-blue-50 text-blue-900 flex items-center justify-center shrink-0 border border-blue-100 shadow-2xs">
                                <span class="material-symbols-outlined text-lg">local_shipping</span>
                            </span>
                            <div class="min-w-0">
                                <h2 class="text-xs sm:text-sm font-bold text-slate-900 font-['Plus_Jakarta_Sans'] truncate">2. Metode Pemenuhan Pesanan</h2>
                                <p class="text-[10px] sm:text-[11px] text-slate-500 truncate">Pilih diantar oleh kurir atau ambil langsung di lapak</p>
                            </div>
                        </div>
                        <span class="px-2 sm:px-2.5 py-0.5 sm:py-1 bg-blue-50 text-blue-900 border border-blue-200 text-[10px] font-bold rounded-full shrink-0">Wajib Dipilih</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Opsi 1: Diantar Kurir (Delivery) -->
                        <label class="relative flex items-start gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition-all border-blue-900 bg-blue-50/40" id="card-option-pengiriman">
                            <input type="radio" 
                                   name="metode_pemenuhan" 
                                   id="radio-pengiriman"
                                   value="pengiriman" 
                                   checked 
                                   onchange="handleFulfillmentChange('pengiriman')"
                                   class="mt-1 w-4 h-4 text-blue-900 border-slate-300 focus:ring-blue-900 cursor-pointer">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-1 mb-1">
                                    <div class="flex items-center gap-1.5 font-bold text-xs text-slate-900 font-['Plus_Jakarta_Sans']">
                                        <span class="material-symbols-outlined text-base text-blue-900">two_wheeler</span>
                                        <span>Diantar Kurir</span>
                                    </div>
                                    <span class="px-1.5 py-0.2 bg-emerald-50 text-emerald-700 border border-emerald-200 text-[9px] font-bold rounded-full">Gratis s/d 5,5 km</span>
                                </div>
                                <p class="text-[11px] text-slate-600 leading-relaxed">
                                    Kurir mengantar langsung ke titik koordinat tujuan di wilayah Kota Mojokerto.
                                </p>
                            </div>
                        </label>

                        <!-- Opsi 2: Ambil Sendiri di Lapak (Pickup) -->
                        <label class="relative flex items-start gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition-all border-slate-200 bg-white hover:border-slate-300" id="card-option-ambil">
                            <input type="radio" 
                                   name="metode_pemenuhan" 
                                   id="radio-ambil"
                                   value="ambil_sendiri" 
                                   onchange="handleFulfillmentChange('ambil_sendiri')"
                                   class="mt-1 w-4 h-4 text-blue-900 border-slate-300 focus:ring-blue-900 cursor-pointer">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-1 mb-1">
                                    <div class="flex items-center gap-1.5 font-bold text-xs text-slate-900 font-['Plus_Jakarta_Sans']">
                                        <span class="material-symbols-outlined text-base text-amber-600">storefront</span>
                                        <span>Ambil Sendiri</span>
                                    </div>
                                    <span class="px-1.5 py-0.2 bg-blue-50 text-blue-900 border border-blue-200 text-[9px] font-bold rounded-full">Bebas Ongkir (Rp 0)</span>
                                </div>
                                <p class="text-[11px] text-slate-600 leading-relaxed">
                                    Ambil pesanan langsung di {{ $lokasiAktif->nama_lokasi ?? 'Lapak Jl. Kuwung' }} (Batas waktu 24 jam).
                                </p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- SECTION A: PENGIRIMAN DELIVERY - TITIK PENGANTARAN KURIR & PETA LEAFLET (FR-5.2, FR-7.1, FR-7.3) -->
                <div id="section-delivery-details" class="bg-white p-4 md:p-5 rounded-2xl shadow-xs border border-slate-200/80 flex flex-col gap-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                            <span class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-800 flex items-center justify-center shrink-0 border border-emerald-100 shadow-2xs">
                                <span class="material-symbols-outlined text-lg">pin_drop</span>
                            </span>
                            <div class="min-w-0">
                                <h2 class="text-xs sm:text-sm font-bold text-slate-900 font-['Plus_Jakarta_Sans'] truncate">3. Titik Lokasi Pengantaran Kurir</h2>
                                <p class="text-[10px] sm:text-[11px] text-slate-500 truncate">Geser pin di peta untuk hitung jarak dan ongkos kirim real-time</p>
                            </div>
                        </div>

                        <!-- Tombol Geolocation HTML5 -->
                        <button type="button" 
                                id="btn-use-location" 
                                onclick="getCurrentLocation()" 
                                class="w-full sm:w-auto px-3.5 py-2 bg-blue-50 hover:bg-blue-100 active:scale-95 text-blue-900 rounded-xl border border-blue-200 text-xs font-bold inline-flex items-center justify-center gap-2 transition-all cursor-pointer shadow-2xs">
                            <span class="material-symbols-outlined text-[18px] text-blue-900 leading-none">my_location</span>
                            <span id="btn-use-location-text">Gunakan Lokasi Saya</span>
                        </button>
                    </div>

                    <!-- Koordinat Indicator & Live Chip -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-slate-50 p-2.5 rounded-xl border border-slate-200/80 text-xs">
                        <div class="flex items-center gap-1.5 text-slate-700 font-medium min-w-0 flex-wrap">
                            <span class="material-symbols-outlined text-base text-emerald-600 shrink-0">location_searching</span>
                            <span class="text-[11px] sm:text-xs shrink-0">Koordinat Tujuan:</span>
                            <span class="font-mono font-bold text-slate-900 bg-white px-2 py-0.5 rounded-md border border-slate-200 text-[11px] shadow-2xs truncate" id="display-coords">
                                {{ old('lat', $lokasiAktif ? $lokasiAktif->latitude : '-7.472600') }}, {{ old('lng', $lokasiAktif ? $lokasiAktif->longitude : '112.438500') }}
                            </span>
                        </div>
                        <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded-full inline-flex items-center gap-1.5 border border-emerald-200 self-start sm:self-auto shrink-0">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>GPS Mojokerto Siaga</span>
                        </span>
                    </div>

                    <!-- Live Quote & Jarak Badges (FR-7.1, FR-7.2, FR-7.3) -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 text-xs">
                        <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200/80 flex flex-col justify-center">
                            <span class="text-[10px] text-slate-500 font-medium flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs text-blue-900">route</span>
                                Estimasi Jarak Rute
                            </span>
                            <span class="font-bold text-slate-900 font-['Plus_Jakarta_Sans'] text-xs sm:text-sm mt-0.5" id="display-jarak">0.0 km</span>
                        </div>
                        <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200/80 flex flex-col justify-center">
                            <span class="text-[10px] text-slate-500 font-medium flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs text-emerald-700">payments</span>
                                Tarif Ongkir
                            </span>
                            <span class="font-bold text-emerald-700 font-['Plus_Jakarta_Sans'] text-xs sm:text-sm mt-0.5" id="display-ongkir">Rp 0 (Promo)</span>
                        </div>
                        <div class="col-span-2 sm:col-span-1 p-2.5 bg-blue-50/70 rounded-xl border border-blue-100 flex flex-col justify-center">
                            <span class="text-[10px] text-blue-800 font-bold flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs text-blue-900">local_offer</span>
                                Ketentuan Tarif:
                            </span>
                            <span class="text-[10px] text-blue-900 mt-0.5 leading-snug" id="display-formula-ket">Gratis ongkir untuk jarak &le; 5,5 km</span>
                        </div>
                    </div>

                    <!-- Alert Peringatan Geofence di Luar Kota Mojokerto (Hidden by Default) -->
                    <div id="alert-geofence" class="hidden p-3.5 bg-rose-50 border border-rose-200 rounded-xl flex items-start gap-2.5 text-rose-900">
                        <span class="material-symbols-outlined text-rose-600 text-lg shrink-0 mt-0.5">error</span>
                        <div class="text-xs leading-relaxed">
                            <strong class="font-bold block text-rose-950 mb-0.5">Titik Lokasi di Luar Wilayah Layanan Kota Mojokerto</strong>
                            <span id="geofence-error-text">Layanan pengantaran kurir Kopi Tiga Titik saat ini hanya menjangkau area Kota Mojokerto. Silakan geser pin ke dalam wilayah Kota Mojokerto atau pilih opsi "Ambil Sendiri di Lapak".</span>
                        </div>
                    </div>

                    <!-- Leaflet Map Container -->
                    <div class="relative overflow-hidden rounded-2xl border border-slate-300 shadow-2xs">
                        <div id="map"></div>
                        <!-- Petunjuk Floating di Peta -->
                        <div class="absolute bottom-2.5 left-2.5 right-2.5 sm:right-auto z-20 bg-white/95 backdrop-blur-md px-3 py-1.5 rounded-xl border border-slate-200 shadow-sm text-[10px] sm:text-[11px] text-slate-700 font-medium flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm text-blue-900 shrink-0">touch_app</span>
                            <span class="truncate">Klik peta atau seret pin biru untuk menentukan titik antar</span>
                        </div>
                    </div>

                    <!-- Hidden Inputs Koordinat -->
                    <input type="hidden" name="lat" id="input_lat" value="{{ old('lat', $lokasiAktif ? $lokasiAktif->latitude : '-7.472600') }}">
                    <input type="hidden" name="lng" id="input_lng" value="{{ old('lng', $lokasiAktif ? $lokasiAktif->longitude : '112.438500') }}">
                    <x-input-error for="lat" class="mt-1" />
                    <x-input-error for="lng" class="mt-1" />

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
                                  class="w-full px-3.5 py-2.5 bg-slate-50/70 border border-slate-300 rounded-xl text-xs text-slate-900 font-medium hover:border-slate-400 focus:bg-white focus:outline-none focus:border-blue-900 focus:ring-4 focus:ring-blue-900/10 transition-all leading-relaxed placeholder:text-slate-400">{{ old('alamat', Auth::user()->alamat ?? 'Jl. Kuwung No. 24, RT 02 / RW 01, Kel. Mergelo, Kota Mojokerto (Pagar hitam samping warung kelontong)') }}</textarea>
                        <x-input-error for="alamat" class="mt-1" />
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
                                  class="w-full px-3.5 py-2.5 bg-slate-50/70 border border-slate-300 rounded-xl text-xs text-slate-900 font-medium hover:border-slate-400 focus:bg-white focus:outline-none focus:border-blue-900 focus:ring-4 focus:ring-blue-900/10 transition-all leading-relaxed placeholder:text-slate-400">{{ old('catatan') }}</textarea>
                        <x-input-error for="catatan" class="mt-1" />
                    </div>
                </div>

                <!-- SECTION B: AMBIL SENDIRI - LOKASI LAPAK & BATAS PENGAMBILAN 24 JAM (BR-41 & FR-14) -->
                <div id="section-pickup-details" class="hidden bg-white p-4 md:p-5 rounded-2xl shadow-xs border border-slate-200/80 flex flex-col gap-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 gap-2">
                        <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                            <span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-800 flex items-center justify-center shrink-0 border border-amber-100 shadow-2xs">
                                <span class="material-symbols-outlined text-lg">store</span>
                            </span>
                            <div class="min-w-0">
                                <h2 class="text-xs sm:text-sm font-bold text-slate-900 font-['Plus_Jakarta_Sans'] truncate">3. Titik Lapak Pengambilan</h2>
                                <p class="text-[10px] sm:text-[11px] text-slate-500 truncate">Datang langsung ke lapak saat pesanan sudah siap diracik</p>
                            </div>
                        </div>
                        <span class="px-2 sm:px-2.5 py-0.5 sm:py-1 bg-emerald-50 text-emerald-800 border border-emerald-200 text-[10px] font-bold rounded-full shrink-0">Bebas Ongkir Rp 0</span>
                    </div>

                    <!-- Info Detail Lapak Aktif (FR-11.4) -->
                    <div class="p-3.5 sm:p-4 bg-gradient-to-br from-amber-50/40 via-white to-slate-50 rounded-xl border border-amber-200/80 flex flex-col gap-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shadow-xs shrink-0 mt-0.5">
                                    <span class="material-symbols-outlined text-xl">storefront</span>
                                </div>
                                <div>
                                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 font-['Plus_Jakarta_Sans']">{{ $lokasiAktif->nama_lokasi ?? 'Lapak Jl. Kuwung' }}</h4>
                                    <p class="text-[11px] text-slate-600 mt-0.5 leading-relaxed">{{ $lokasiAktif->alamat ?? 'Jl. Kuwung, Mergelo, Kec. Magersari, Kota Mojokerto' }}</p>
                                </div>
                            </div>
                            <a href="https://www.google.com/maps/search/?api=1&query={{ $lokasiAktif->latitude ?? -7.4726 }},{{ $lokasiAktif->longitude ?? 112.4385 }}" 
                               target="_blank" 
                               class="px-2.5 py-1.5 bg-white hover:bg-slate-50 text-slate-700 text-[11px] font-bold rounded-lg border border-slate-200 shadow-2xs inline-flex items-center gap-1 shrink-0 transition-colors">
                                <span class="material-symbols-outlined text-sm text-blue-900">map</span>
                                <span class="hidden sm:inline">Petunjuk Arah</span>
                            </a>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2 border-t border-amber-100 text-xs">
                            <div class="flex items-center gap-2 text-slate-700">
                                <span class="material-symbols-outlined text-amber-600 text-base">schedule</span>
                                <span>Jam Operasional: <strong>{{ $jamBuka ?? '15:00' }} - {{ $jamTutup ?? '18:00' }} WIB</strong></span>
                            </div>
                            <div class="flex items-center gap-2 text-slate-700">
                                <span class="material-symbols-outlined text-emerald-600 text-base">verified</span>
                                <span>Status Lapak: <strong class="text-emerald-700">{{ ($statusLapak ?? 'buka') === 'buka' ? 'Buka Siaga' : 'Tutup Sementara' }}</strong></span>
                            </div>
                        </div>
                    </div>

                    <!-- Alert Batas Pengambilan 24 Jam (BR-41 & FR-14) -->
                    <div class="p-3.5 bg-blue-50/80 rounded-xl border border-blue-200 flex items-start gap-2.5 text-blue-950">
                        <span class="material-symbols-outlined text-blue-900 text-lg shrink-0 mt-0.5">timer</span>
                        <div class="text-[11px] leading-relaxed">
                            <strong class="font-bold block text-blue-950 mb-0.5">Ketentuan Batas Pengambilan 24 Jam:</strong>
                            Setelah barista selesai meracik kopi dan status pesanan berubah menjadi <em>Siap Diambil</em>, Anda memiliki batas waktu maksimal <strong>24 jam</strong> untuk mengambil pesanan di lapak. Demi menjamin mutu dan kesegaran rasa kopi, pesanan yang tidak diambil dalam 24 jam akan ditutup otomatis.
                        </div>
                    </div>
                </div>

                <!-- CARD 4: PILIHAN METODE PEMBAYARAN (FR-6.3 - QRIS Dinamis) -->
                <div class="bg-white p-4 md:p-5 rounded-2xl shadow-xs border border-slate-200/80 flex flex-col gap-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 gap-2">
                        <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                            <span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-800 flex items-center justify-center shrink-0 border border-amber-100 shadow-2xs">
                                <span class="material-symbols-outlined text-lg">payments</span>
                            </span>
                            <div class="min-w-0">
                                <h2 class="text-xs sm:text-sm font-bold text-slate-900 font-['Plus_Jakarta_Sans'] truncate">4. Metode Pembayaran</h2>
                                <p class="text-[10px] sm:text-[11px] text-slate-500 truncate">Verifikasi otomatis tanpa upload bukti transfer</p>
                            </div>
                        </div>
                        <span class="px-2 sm:px-2.5 py-0.5 sm:py-1 bg-emerald-50 text-emerald-800 border border-emerald-200 text-[10px] font-bold rounded-full shrink-0">Otomatis</span>
                    </div>

                    <!-- Pilihan QRIS Dinamis (Active Card) -->
                    <label class="relative block p-3.5 sm:p-4 bg-gradient-to-br from-blue-50/70 via-blue-50/40 to-white border-2 border-blue-900 rounded-2xl cursor-pointer hover:shadow-xs transition-all">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-2.5 sm:gap-3 flex-1 min-w-0">
                                <div class="pt-0.5 shrink-0">
                                    <input type="radio" 
                                           name="metode_pembayaran" 
                                           value="qris" 
                                           checked 
                                           class="w-4 h-4 text-blue-900 border-slate-300 focus:ring-blue-900 cursor-pointer">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                                        <span class="px-1.5 py-0.5 bg-[#E1251B] text-white font-black text-[9px] sm:text-[10px] rounded tracking-wider shadow-2xs">QRIS</span>
                                        <span class="text-xs font-bold text-slate-900 font-['Plus_Jakarta_Sans']">QRIS Dinamis (Midtrans)</span>
                                        <span class="px-1.5 py-0.2 bg-blue-900 text-white font-extrabold text-[8px] sm:text-[9px] rounded-full uppercase tracking-wider">INSTAN</span>
                                    </div>
                                    <p class="text-[11px] text-slate-600 mt-1 leading-relaxed">
                                        Scan barcode QRIS dengan m-Banking (BCA, Mandiri, BRI, BNI) atau e-Wallet favoritmu (GoPay, OVO, DANA, ShopeePay).
                                    </p>
                                    <div class="flex items-center gap-1 sm:gap-1.5 mt-2 pt-2 border-t border-blue-100 flex-wrap">
                                        <span class="px-1.5 py-0.5 bg-white rounded-md border border-slate-200 text-[9px] sm:text-[10px] font-bold text-slate-700 shadow-2xs">GoPay</span>
                                        <span class="px-1.5 py-0.5 bg-white rounded-md border border-slate-200 text-[9px] sm:text-[10px] font-bold text-slate-700 shadow-2xs">OVO</span>
                                        <span class="px-1.5 py-0.5 bg-white rounded-md border border-slate-200 text-[9px] sm:text-[10px] font-bold text-slate-700 shadow-2xs">DANA</span>
                                        <span class="px-1.5 py-0.5 bg-white rounded-md border border-slate-200 text-[9px] sm:text-[10px] font-bold text-slate-700 shadow-2xs">ShopeePay</span>
                                        <span class="px-1.5 py-0.5 bg-white rounded-md border border-slate-200 text-[9px] sm:text-[10px] font-bold text-slate-700 shadow-2xs">BCA</span>
                                        <span class="px-1.5 py-0.5 bg-white rounded-md border border-slate-200 text-[9px] sm:text-[10px] font-bold text-slate-700 shadow-2xs">Mandiri</span>
                                        <span class="px-1.5 py-0.5 bg-white rounded-md border border-slate-200 text-[9px] sm:text-[10px] font-bold text-slate-700 shadow-2xs">BRI</span>
                                        <span class="px-1.5 py-0.5 bg-white rounded-md border border-slate-200 text-[9px] sm:text-[10px] font-bold text-slate-700 shadow-2xs">BNI</span>
                                    </div>
                                </div>
                            </div>
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-white border border-blue-200 flex items-center justify-center shrink-0 shadow-xs hidden sm:flex">
                                <span class="material-symbols-outlined text-blue-900 text-xl sm:text-2xl">qr_code_2</span>
                            </div>
                        </div>
                    </label>

                    <div class="p-2.5 sm:p-3 bg-slate-50 rounded-xl border border-slate-200/80 flex items-center gap-2.5 text-[11px] sm:text-xs text-slate-600">
                        <span class="material-symbols-outlined text-base text-blue-900 shrink-0">timer</span>
                        <span>QRIS dinamis berlaku selama 10 menit setelah tombol konfirmasi ditekan.</span>
                    </div>
                </div>

            </div>

            <!-- ================================================================= -->
            <!-- KOLOM KANAN (5 SPAN): RINGKASAN BELANJA & TOMBOL PESAN (STICKY) -->
            <!-- ================================================================= -->
            <div class="lg:col-span-5 flex flex-col gap-4 lg:sticky lg:top-20">

                <!-- CARD 5: DAFTAR ITEM PESANAN (FR-5.6) -->
                <div class="bg-white p-4 md:p-5 rounded-2xl shadow-xs border border-slate-200/80 flex flex-col gap-3.5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2.5 sm:gap-3">
                            <span class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-indigo-50 text-indigo-900 flex items-center justify-center shrink-0 border border-indigo-100 shadow-2xs">
                                <span class="material-symbols-outlined text-base sm:text-lg">shopping_bag</span>
                            </span>
                            <div>
                                <h2 class="text-xs sm:text-sm font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Ringkasan Pesanan</h2>
                                <span class="text-[10px] sm:text-[11px] text-slate-500">{{ isset($totalJumlah) ? $totalJumlah : 3 }} item siap diracik</span>
                            </div>
                        </div>
                        <a href="{{ url('/cart') }}" class="text-xs font-bold text-blue-900 hover:text-blue-800 hover:underline inline-flex items-center gap-1 py-1 px-2.5 rounded-lg bg-blue-50/70">
                            <span>Ubah</span>
                            <span class="material-symbols-outlined text-sm">edit</span>
                        </a>
                    </div>

                    <!-- List Produk Keranjang Riil Pelanggan -->
                    <div class="flex flex-col gap-2.5 max-h-[280px] sm:max-h-[320px] overflow-y-auto pr-0.5">
                        @if(isset($cartItems) && $cartItems->isNotEmpty())
                            @foreach($cartItems as $item)
                                <div class="p-2 sm:p-2.5 bg-slate-50/80 hover:bg-slate-50 rounded-xl border border-slate-200/70 flex items-center gap-2.5 sm:gap-3 transition-colors">
                                    <div class="w-12 h-14 sm:w-13 sm:h-14 bg-white rounded-xl p-1.5 border border-slate-200 flex items-center justify-center shrink-0 shadow-2xs">
                                        <img src="{{ $item->product?->foto ? asset('storage/' . $item->product->foto) : asset('products/' . ($item->product?->nama ?? 'Titik Temu') . '.png') }}" 
                                             alt="{{ $item->product?->nama ?? 'Produk' }}" 
                                             class="w-full h-full object-contain"
                                             onerror="this.src='{{ asset('products/All Produk.png') }}'" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-1">
                                            <h4 class="text-xs font-bold text-slate-900 truncate font-['Plus_Jakarta_Sans']">{{ $item->product?->nama ?? 'Produk Kopi' }}</h4>
                                            <span class="text-xs font-extrabold text-blue-900 font-['Plus_Jakarta_Sans'] shrink-0">Rp {{ number_format($item->subtotal ?? (($item->product?->harga ?? 10000) * $item->jumlah), 0, ',', '.') }}</span>
                                        </div>
                                        <p class="text-[10px] text-slate-500 truncate">{{ $item->product?->deskripsi ?? 'Kopi Botol 250ml' }}</p>
                                        <div class="flex items-center justify-between text-[10px] text-slate-600 mt-1 font-semibold">
                                            <span>{{ $item->jumlah }} botol @ Rp {{ number_format($item->product?->harga ?? 10000, 0, ',', '.') }}</span>
                                            <span class="text-emerald-700 font-bold bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200">Botol Segar</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <!-- Fallback Snapshot -->
                            <div class="p-2 sm:p-2.5 bg-slate-50/80 rounded-xl border border-slate-200/70 flex items-center gap-2.5 sm:gap-3">
                                <div class="w-12 h-14 sm:w-13 sm:h-14 bg-white rounded-xl p-1.5 border border-slate-200 flex items-center justify-center shrink-0 shadow-2xs">
                                    <img src="{{ asset('products/Titik Temu.png') }}" alt="Titik Temu" class="w-full h-full object-contain" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-1">
                                        <h4 class="text-xs font-bold text-slate-900 truncate font-['Plus_Jakarta_Sans']">Titik Temu</h4>
                                        <span class="text-xs font-extrabold text-blue-900 font-['Plus_Jakarta_Sans'] shrink-0">Rp 20.000</span>
                                    </div>
                                    <p class="text-[10px] text-slate-500">Kopi Susu Gula Aren 250ml</p>
                                    <div class="flex items-center justify-between text-[10px] text-slate-600 mt-1 font-semibold">
                                        <span>2 botol @ Rp 10.000</span>
                                        <span class="text-emerald-700 font-bold bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200">Botol Segar</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Total Item Badge -->
                    <div class="p-2 sm:p-2.5 bg-blue-50/70 rounded-xl border border-blue-100 flex items-center justify-between text-xs text-blue-900 font-semibold">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm">local_cafe</span>
                            <span>Total Minuman:</span>
                        </span>
                        <span class="font-bold">{{ isset($totalJumlah) ? $totalJumlah : 3 }} Botol</span>
                    </div>
                </div>

                <!-- CARD 6: RINCIAN BIAYA & TOTAL PEMBAYARAN (FR-5.6, FR-5.8, FR-7.3, FR-14) -->
                <div class="bg-white p-4 md:p-5 rounded-2xl shadow-xs border border-slate-200/80 flex flex-col gap-3.5">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Rincian Pembayaran</h3>

                    <div class="flex flex-col gap-2.5 text-xs text-slate-700">
                        <div class="flex justify-between items-center">
                            <span>Subtotal Produk ({{ isset($totalJumlah) ? $totalJumlah : 3 }} Botol)</span>
                            <span class="font-bold text-slate-900">Rp {{ isset($subtotal) ? number_format($subtotal, 0, ',', '.') : '28.000' }}</span>
                        </div>

                        <!-- Ongkir Dinamis Sesuai PPL-25, FR-7.3, FR-14 -->
                        <div class="flex justify-between items-center">
                            <span class="inline-flex items-center gap-1">
                                Ongkos Kirim Kurir
                                <span class="material-symbols-outlined text-xs text-slate-400" title="Gratis ongkir promo jarak <= 5,5 km di Kota Mojokerto">help</span>
                            </span>
                            <div class="flex items-center gap-1.5" id="summary-ongkir">
                                <span class="text-[10px] line-through text-slate-400">Rp 5.000</span>
                                <span class="font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200 text-[11px]">Rp 0 (Promo)</span>
                            </div>
                        </div>

                        <div class="flex justify-between items-center text-[11px] text-slate-500" id="summary-distance-row">
                            <span>Estimasi Jarak Antar</span>
                            <span class="font-medium font-mono" id="summary-distance-note">0.0 km</span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="inline-flex items-center gap-1">
                                Kemasan Higienis & Es Pendingin ❄️
                            </span>
                            <span class="font-bold text-emerald-700">Gratis</span>
                        </div>
                    </div>

                    <div class="border-t border-dashed border-slate-200 my-0.5"></div>

                    <!-- Total Akhir -->
                    <div class="flex justify-between items-baseline">
                        <div>
                            <span class="text-xs font-bold text-slate-500 block uppercase tracking-wider">Total Tagihan</span>
                            <span class="text-[10px] text-slate-400 font-medium" id="summary-total-subtitle">Termasuk PPN & Jasa Pengantaran</span>
                        </div>
                        <div class="text-right">
                            <span class="text-xl sm:text-2xl md:text-3xl font-extrabold text-blue-900 font-['Plus_Jakarta_Sans'] block leading-none" id="summary-total">Rp {{ isset($total) ? number_format($total, 0, ',', '.') : '28.000' }}</span>
                        </div>
                    </div>

                    <!-- ========================================================= -->
                    <!-- PERINGATAN WAJIB PRD (FR-5.2) -->
                    <!-- ========================================================= -->
                    <div class="p-3 sm:p-3.5 bg-amber-50 rounded-2xl border border-amber-200 flex items-start gap-2.5 sm:gap-3 text-amber-950">
                        <span class="material-symbols-outlined text-amber-600 text-lg sm:text-xl shrink-0 mt-0.5">warning</span>
                        <div class="text-[11px] leading-relaxed">
                            <strong class="font-bold block text-amber-950 mb-0.5">Peringatan Penting:</strong>
                            Jika pesanan sudah dibayar, pesanan langsung masuk ke antrean racik barista dan <strong>tidak dapat dibatalkan atau diubah</strong>.
                        </div>
                    </div>

                    <!-- TOMBOL SUBMIT & PENCEGAHAN DOUBLE SUBMIT (FR-5.2) -->
                    <button type="submit" 
                            id="btn-submit-order" 
                            class="w-full py-3.5 sm:py-4 px-4 bg-gradient-to-r from-blue-900 to-indigo-900 hover:from-blue-800 hover:to-indigo-800 active:scale-98 text-white rounded-2xl font-bold text-xs sm:text-sm inline-flex items-center justify-center gap-2 shadow-lg shadow-blue-900/20 transition-all cursor-pointer">
                        <span class="material-symbols-outlined text-lg sm:text-xl leading-none" id="btn-submit-icon">qr_code_2</span>
                        <span id="btn-submit-text">Buat Pesanan & Bayar QRIS (Rp {{ isset($total) ? number_format($total, 0, ',', '.') : '28.000' }})</span>
                    </button>

                    <!-- Jaminan & Keamanan -->
                    <div class="inline-flex items-center justify-center gap-2 text-[10px] sm:text-[11px] text-slate-400 font-medium pt-1">
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
        // Koordinat Default Lapak Kopi Tiga Titik Mojokerto
        const DEFAULT_LAT = {{ $lokasiAktif ? (float)$lokasiAktif->latitude : -7.472600 }};
        const DEFAULT_LNG = {{ $lokasiAktif ? (float)$lokasiAktif->longitude : 112.438500 }};
        const LAPAK_NAME = "{{ addslashes($lokasiAktif->nama_lokasi ?? 'Lapak Jl. Kuwung') }}";
        const LAPAK_ADDRESS = "{{ addslashes($lokasiAktif->alamat ?? 'Jl. Kuwung, Mergelo, Kec. Magersari, Kota Mojokerto') }}";
        const BASE_SUBTOTAL = {{ (int) ($subtotal ?? 28000) }};

        let map;
        let customerMarker;
        let lapakMarker;
        let currentOngkir = 0;
        let isOutsideMojokerto = false;

        // Inisialisasi saat DOM siap
        document.addEventListener('DOMContentLoaded', function () {
            initLeafletMap();
            // Jalankan perhitungan awal ongkir untuk lokasi default
            fetchShippingQuote(DEFAULT_LAT, DEFAULT_LNG);
        });

        function initLeafletMap() {
            const mapContainer = document.getElementById('map');
            if (!mapContainer) return;

            // 1. Inisialisasi instance Leaflet Map
            map = L.map('map', {
                center: [DEFAULT_LAT, DEFAULT_LNG],
                zoom: 15,
                scrollWheelZoom: false,
            });

            // 2. Tile Layer OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> contributors'
            }).addTo(map);

            // 3. Marker Lapak Aktif (Ikon Toko Hijau)
            const lapakIcon = L.divIcon({
                className: 'custom-lapak-marker',
                html: `
                    <div style="position: relative; display: flex; flex-direction: column; align-items: center; filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.3));">
                        <div style="width: 32px; height: 32px; background: #059669; border: 2.5px solid #ffffff; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                            <span class="material-symbols-outlined" style="font-size: 18px; line-height: 1;">storefront</span>
                        </div>
                        <div style="width: 0; height: 0; border-left: 5px solid transparent; border-right: 5px solid transparent; border-top: 6px solid #059669; margin-top: -1px;"></div>
                    </div>
                `,
                iconSize: [32, 38],
                iconAnchor: [16, 38],
                popupAnchor: [0, -38]
            });

            lapakMarker = L.marker([DEFAULT_LAT, DEFAULT_LNG], { icon: lapakIcon }).addTo(map);
            lapakMarker.bindPopup(`<b>${LAPAK_NAME} (Lapak Asal)</b><br>${LAPAK_ADDRESS}`);

            // 4. Custom Marker Pin Pelanggan (Draggable Biru dengan Pulse Radar)
            const pinIcon = L.divIcon({
                className: 'custom-pin-marker',
                html: `
                    <div style="position: relative; display: flex; flex-direction: column; align-items: center; filter: drop-shadow(0 6px 8px rgba(15, 23, 42, 0.35));">
                        <div class="pin-radar"></div>
                        <div style="width: 38px; height: 38px; background: #1e3a8a; border: 2.5px solid #ffffff; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; position: relative; z-index: 2;">
                            <span class="material-symbols-outlined" style="font-size: 20px; line-height: 1;">local_cafe</span>
                        </div>
                        <div style="width: 0; height: 0; border-left: 6px solid transparent; border-right: 6px solid transparent; border-top: 9px solid #1e3a8a; margin-top: -1px; position: relative; z-index: 2;"></div>
                    </div>
                `,
                iconSize: [38, 46],
                iconAnchor: [19, 46],
                popupAnchor: [0, -46]
            });

            // 5. Tambahkan Marker Draggable Pelanggan
            customerMarker = L.marker([DEFAULT_LAT, DEFAULT_LNG], {
                draggable: true,
                icon: pinIcon
            }).addTo(map);

            customerMarker.bindPopup("<b>Titik Pengantaran Anda</b><br>Geser pin ke lokasi rumah / kantor Anda.").openPopup();

            // 6. Event Listener: Selesai drag marker
            customerMarker.on('dragend', function (event) {
                const position = customerMarker.getLatLng();
                updateCoordinates(position.lat, position.lng);
                fetchShippingQuote(position.lat, position.lng);
            });

            // 7. Event Listener: Klik langsung pada area peta
            map.on('click', function (event) {
                const lat = event.latlng.lat;
                const lng = event.latlng.lng;
                customerMarker.setLatLng([lat, lng]);
                updateCoordinates(lat, lng);
                fetchShippingQuote(lat, lng);
            });

            // Invalidate size setelah delay kecil agar render tile optimal
            setTimeout(function () {
                map.invalidateSize();
            }, 300);
        }

        // Format Angka Ribuan Rupiah
        function formatRupiah(amount) {
            return new Intl.NumberFormat('id-ID').format(amount);
        }

        // Perbarui Koordinat di Input Form & Tampilan
        function updateCoordinates(lat, lng) {
            const formattedLat = Number(lat).toFixed(6);
            const formattedLng = Number(lng).toFixed(6);

            const inputLat = document.getElementById('input_lat');
            const inputLng = document.getElementById('input_lng');
            const displayCoords = document.getElementById('display-coords');

            if (inputLat) inputLat.value = formattedLat;
            if (inputLng) inputLng.value = formattedLng;
            if (displayCoords) displayCoords.innerText = `${formattedLat}, ${formattedLng}`;

            if (customerMarker) {
                customerMarker.setPopupContent(`<b>Titik Pengantaran Terpilih:</b><br>${formattedLat}, ${formattedLng}`);
            }
        }

        // Fungsi Hitung Quote Ongkos Kirim via API Backend (FR-7.1, FR-7.2, FR-7.3)
        function fetchShippingQuote(lat, lng) {
            const currentMethod = document.querySelector('input[name="metode_pemenuhan"]:checked')?.value || 'pengiriman';
            if (currentMethod === 'ambil_sendiri') return;

            const displayDistance = document.getElementById('display-jarak');
            const displayOngkir = document.getElementById('display-ongkir');
            const displayFormula = document.getElementById('display-formula-ket');
            const summaryOngkir = document.getElementById('summary-ongkir');
            const summaryDistance = document.getElementById('summary-distance-note');
            const summaryTotal = document.getElementById('summary-total');
            const btnSubmitText = document.getElementById('btn-submit-text');
            const alertGeofence = document.getElementById('alert-geofence');
            const geofenceErrorText = document.getElementById('geofence-error-text');
            const submitBtn = document.getElementById('btn-submit-order');

            if (displayOngkir) displayOngkir.innerHTML = '<span class="animate-pulse text-slate-500">Menghitung...</span>';

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

            fetch('/api/shipping/quote', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    metode_pemenuhan: 'pengiriman',
                    lat: lat,
                    lng: lng
                })
            })
            .then(async response => {
                const result = await response.json();
                if (response.ok && result.success) {
                    isOutsideMojokerto = false;
                    if (alertGeofence) alertGeofence.classList.add('hidden');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    }

                    const data = result.data;
                    currentOngkir = data.ongkir;
                    const newTotal = BASE_SUBTOTAL + currentOngkir;

                    if (displayDistance) displayDistance.textContent = data.jarak_formatted;
                    if (displayOngkir) {
                        displayOngkir.textContent = data.ongkir === 0 ? 'Rp 0 (Promo)' : data.ongkir_formatted;
                    }
                    if (displayFormula) displayFormula.textContent = data.formula_ket;

                    if (summaryOngkir) {
                        summaryOngkir.innerHTML = data.ongkir === 0 
                            ? `<span class="text-[10px] line-through text-slate-400">Rp 5.000</span><span class="font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200 text-[11px]">Rp 0 (Promo)</span>`
                            : `<span class="font-bold text-slate-900">${data.ongkir_formatted}</span>`;
                    }
                    if (summaryDistance) summaryDistance.textContent = data.jarak_formatted;
                    if (summaryTotal) summaryTotal.textContent = `Rp ${formatRupiah(newTotal)}`;
                    if (btnSubmitText) btnSubmitText.textContent = `Buat Pesanan & Bayar QRIS (Rp ${formatRupiah(newTotal)})`;
                } else {
                    // Validasi Geofence Gagal (Di luar Kota Mojokerto)
                    isOutsideMojokerto = true;
                    const errorMsg = result.message || 'Titik lokasi tujuan berada di luar jangkauan wilayah Kota Mojokerto.';
                    if (alertGeofence) {
                        alertGeofence.classList.remove('hidden');
                        if (geofenceErrorText) geofenceErrorText.textContent = errorMsg;
                    }
                    if (displayOngkir) displayOngkir.textContent = 'Di luar jangkauan';
                    if (displayDistance) displayDistance.textContent = '-';
                    if (summaryOngkir) summaryOngkir.innerHTML = `<span class="text-rose-600 font-bold text-[11px]">Di luar jangkauan</span>`;
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    }
                }
            })
            .catch(error => {
                console.warn('Gagal memanggil API quote shipping, menjalankan fallback client-side:', error);
                fallbackCalculateQuote(lat, lng);
            });
        }

        // Fallback Perhitungan Jarak & Ongkir di Sisi Klien
        function fallbackCalculateQuote(lat, lng) {
            // Geofence Bounding Box Kota Mojokerto
            const isInside = (lat >= -7.530000 && lat <= -7.420000 && lng >= 112.390000 && lng <= 112.480000);
            const alertGeofence = document.getElementById('alert-geofence');
            const submitBtn = document.getElementById('btn-submit-order');

            if (!isInside) {
                isOutsideMojokerto = true;
                if (alertGeofence) alertGeofence.classList.remove('hidden');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                }
                const summaryOngkir = document.getElementById('summary-ongkir');
                if (summaryOngkir) summaryOngkir.innerHTML = `<span class="text-rose-600 font-bold text-[11px]">Di luar jangkauan</span>`;
                return;
            }

            isOutsideMojokerto = false;
            if (alertGeofence) alertGeofence.classList.add('hidden');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }

            const R = 6371; // Radius Bumi km
            const dLat = (lat - DEFAULT_LAT) * Math.PI / 180;
            const dLng = (lng - DEFAULT_LNG) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                      Math.cos(DEFAULT_LAT * Math.PI / 180) * Math.cos(lat * Math.PI / 180) *
                      Math.sin(dLng / 2) * Math.sin(dLng / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            const straightDistance = R * c;
            const routeDistance = Math.max(0.3, Math.round(straightDistance * 1.25 * 10) / 10);

            let ongkir = 0;
            let formulaKet = 'Gratis ongkir untuk jarak <= 5,5 km';
            if (routeDistance > 5.5) {
                const step = Math.floor(routeDistance - 5.5) + 1;
                ongkir = 2000 * step;
                formulaKet = `Jarak ${routeDistance} km (Kenaikan Rp 2.000 / km setelah 5,5 km)`;
            }

            currentOngkir = ongkir;
            const newTotal = BASE_SUBTOTAL + currentOngkir;

            const displayDistance = document.getElementById('display-jarak');
            const displayOngkir = document.getElementById('display-ongkir');
            const displayFormula = document.getElementById('display-formula-ket');
            const summaryOngkir = document.getElementById('summary-ongkir');
            const summaryDistance = document.getElementById('summary-distance-note');
            const summaryTotal = document.getElementById('summary-total');
            const btnSubmitText = document.getElementById('btn-submit-text');

            if (displayDistance) displayDistance.textContent = `${routeDistance} km`;
            if (displayOngkir) displayOngkir.textContent = ongkir === 0 ? 'Rp 0 (Promo)' : `Rp ${formatRupiah(ongkir)}`;
            if (displayFormula) displayFormula.textContent = formulaKet;

            if (summaryOngkir) {
                summaryOngkir.innerHTML = ongkir === 0 
                    ? `<span class="text-[10px] line-through text-slate-400">Rp 5.000</span><span class="font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200 text-[11px]">Rp 0 (Promo)</span>`
                    : `<span class="font-bold text-slate-900">Rp ${formatRupiah(ongkir)}</span>`;
            }
            if (summaryDistance) summaryDistance.textContent = `${routeDistance} km`;
            if (summaryTotal) summaryTotal.textContent = `Rp ${formatRupiah(newTotal)}`;
            if (btnSubmitText) btnSubmitText.textContent = `Buat Pesanan & Bayar QRIS (Rp ${formatRupiah(newTotal)})`;
        }

        // Penggantian Metode Pemenuhan (Delivery vs Ambil Sendiri)
        function handleFulfillmentChange(method) {
            const cardPengiriman = document.getElementById('card-option-pengiriman');
            const cardAmbil = document.getElementById('card-option-ambil');
            const sectionDelivery = document.getElementById('section-delivery-details');
            const sectionPickup = document.getElementById('section-pickup-details');
            const badgeMetodeText = document.getElementById('badge-metode-text');
            const badgeMetodeIcon = document.getElementById('badge-metode-icon');
            const summaryOngkir = document.getElementById('summary-ongkir');
            const summaryDistance = document.getElementById('summary-distance-note');
            const summaryTotal = document.getElementById('summary-total');
            const btnSubmitText = document.getElementById('btn-submit-text');
            const alamatTextarea = document.getElementById('alamat');
            const alertGeofence = document.getElementById('alert-geofence');
            const submitBtn = document.getElementById('btn-submit-order');

            if (method === 'ambil_sendiri') {
                // Tampilan Switcher Card
                cardAmbil.classList.add('border-blue-900', 'bg-blue-50/40');
                cardAmbil.classList.remove('border-slate-200', 'bg-white');
                cardPengiriman.classList.remove('border-blue-900', 'bg-blue-50/40');
                cardPengiriman.classList.add('border-slate-200', 'bg-white');

                // Switch Sections
                sectionDelivery.classList.add('hidden');
                sectionPickup.classList.remove('hidden');

                // Badge Header
                badgeMetodeText.textContent = 'Ambil Sendiri';
                badgeMetodeIcon.textContent = 'storefront';

                // Ongkir Rp 0 & Update Total
                currentOngkir = 0;
                const newTotal = BASE_SUBTOTAL;
                summaryOngkir.innerHTML = `<span class="font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200 text-[11px]">Rp 0 (Ambil di Lapak)</span>`;
                summaryDistance.textContent = 'Ambil di Lapak';
                summaryTotal.textContent = `Rp ${formatRupiah(newTotal)}`;
                btnSubmitText.textContent = `Buat Pesanan & Bayar QRIS (Rp ${formatRupiah(newTotal)})`;

                // Izinkan Submit
                if (alertGeofence) alertGeofence.classList.add('hidden');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                }

                // Otomatisasi Alamat jika kosong untuk Ambil Sendiri
                if (alamatTextarea && !alamatTextarea.value.trim()) {
                    alamatTextarea.value = `Ambil Sendiri di ${LAPAK_NAME} (${LAPAK_ADDRESS})`;
                }
            } else {
                // Tampilan Switcher Card
                cardPengiriman.classList.add('border-blue-900', 'bg-blue-50/40');
                cardPengiriman.classList.remove('border-slate-200', 'bg-white');
                cardAmbil.classList.remove('border-blue-900', 'bg-blue-50/40');
                cardAmbil.classList.add('border-slate-200', 'bg-white');

                // Switch Sections
                sectionPickup.classList.add('hidden');
                sectionDelivery.classList.remove('hidden');

                // Badge Header
                badgeMetodeText.textContent = 'Delivery';
                badgeMetodeIcon.textContent = 'two_wheeler';

                // Refresh leaflet map render setelah container terlihat
                if (map) {
                    setTimeout(() => {
                        map.invalidateSize();
                    }, 200);
                }

                // Hitung kembali ongkir berdasarkan koordinat saat ini
                const lat = parseFloat(document.getElementById('input_lat')?.value || DEFAULT_LAT);
                const lng = parseFloat(document.getElementById('input_lng')?.value || DEFAULT_LNG);
                fetchShippingQuote(lat, lng);
            }
        }

        // Ambil Lokasi Sekarang via HTML5 Geolocation API
        function getCurrentLocation() {
            const btn = document.getElementById('btn-use-location');
            const btnText = document.getElementById('btn-use-location-text');

            if (!navigator.geolocation) {
                alert("Perangkat atau browser Anda tidak mendukung fitur Geolocation.");
                return;
            }

            btn.disabled = true;
            btnText.innerText = "Mencari GPS...";

            navigator.geolocation.getCurrentPosition(
                function (position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    if (map && customerMarker) {
                        map.setView([lat, lng], 17);
                        customerMarker.setLatLng([lat, lng]);
                        updateCoordinates(lat, lng);
                        fetchShippingQuote(lat, lng);
                        customerMarker.bindPopup("<b>Lokasi Anda Ditemukan!</b><br>Titik pengantaran berhasil diperbarui.").openPopup();
                    }

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
                        msg = "Izin akses lokasi GPS ditolak oleh browser/perangkat Anda. Silakan tentukan lokasi dengan menggeser pin manual pada peta.";
                    } else if (error.code === error.POSITION_UNAVAILABLE) {
                        msg = "Informasi lokasi GPS tidak tersedia saat ini. Silakan klik manual pada peta.";
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
            const currentMethod = document.querySelector('input[name="metode_pemenuhan"]:checked')?.value || 'pengiriman';
            const nama = document.getElementById('nama_penerima').value.trim();
            const telp = document.getElementById('telepon').value.trim();
            const alamat = document.getElementById('alamat').value.trim();

            if (!nama || !telp) {
                event.preventDefault();
                alert("Harap lengkapi Nama Penerima dan Nomor WhatsApp.");
                return false;
            }

            if (currentMethod === 'pengiriman') {
                if (!alamat) {
                    event.preventDefault();
                    alert("Harap isi Alamat Lengkap Pengiriman.");
                    return false;
                }

                if (isOutsideMojokerto) {
                    event.preventDefault();
                    alert("Titik pengantaran berada di luar wilayah layanan Kota Mojokerto. Silakan pilih titik dalam Kota Mojokerto atau pilih Ambil Sendiri di Lapak.");
                    return false;
                }
            } else {
                // Ambil sendiri: pastikan alamat terisi agar validasi model backend tetap terpenuhi
                if (!alamat) {
                    document.getElementById('alamat').value = `Ambil Sendiri di ${LAPAK_NAME} (${LAPAK_ADDRESS})`;
                }
            }

            const submitBtn = document.getElementById('btn-submit-order');
            const submitIcon = document.getElementById('btn-submit-icon');
            const submitText = document.getElementById('btn-submit-text');

            setTimeout(function() {
                submitBtn.disabled = true;
            }, 10);
            submitBtn.classList.remove('hover:from-blue-800', 'hover:to-indigo-800', 'cursor-pointer');
            submitBtn.classList.add('opacity-75', 'cursor-not-allowed');

            submitIcon.innerHTML = `
                <svg class="animate-spin h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
            `;
            submitText.innerText = "Memproses Pesanan & Membuka QRIS...";

            return true;
        }
    </script>
@endpush
