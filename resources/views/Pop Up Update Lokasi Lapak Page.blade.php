<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengaturan Lokasi Lapak & Jam Operasional - Kopi Tiga Titik</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Leaflet CSS via CDN -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <!-- Tailwind CSS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        #map-lapak {
            width: 100%;
            height: 240px;
            z-index: 10;
        }
        @media (min-width: 640px) {
            #map-lapak {
                height: 280px;
            }
        }

        .leaflet-container {
            font-family: 'Inter', sans-serif !important;
        }

        .custom-lapak-marker {
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: visible !important;
        }

        .pin-radar-owner {
            position: absolute;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(30, 58, 138, 0.25);
            animation: pinPulseOwner 2s ease-out infinite;
            pointer-events: none;
            top: -4px;
            left: -4px;
        }

        @keyframes pinPulseOwner {
            0% {
                transform: scale(0.6);
                opacity: 1;
            }
            100% {
                transform: scale(1.6);
                opacity: 0;
            }
        }
    </style>
</head>
<body class="bg-slate-800/60 min-h-screen flex justify-center items-end sm:items-center py-0 sm:py-6 antialiased font-sans backdrop-blur-xs">

<div class="w-full max-w-xl bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl relative flex flex-col justify-start p-5 sm:p-6 border border-slate-100 animate-in fade-in slide-in-from-bottom duration-300 max-h-[92vh] overflow-y-auto">

    <!-- Modal Grab Handle di Layar Mobile -->
    <div class="w-12 h-1.5 bg-slate-200 rounded-full mx-auto mb-3 sm:hidden"></div>

    <!-- Header -->
    <div class="flex justify-between items-start mb-4 pb-3 border-b border-slate-100">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-900 border border-blue-100 flex items-center justify-center shrink-0 shadow-2xs">
                <span class="material-symbols-outlined text-xl">storefront</span>
            </div>
            <div>
                <h2 class="text-base sm:text-lg font-bold text-slate-900 font-['Plus_Jakarta_Sans'] leading-tight">Pengaturan Lokasi & Jam Lapak</h2>
                <p class="text-xs text-slate-500 mt-0.5">Kelola titik mangkal street coffee dan jam operasional lapak hari ini</p>
            </div>
        </div>
        <a href="{{ url('/owner/dashboard') }}" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-600 transition-colors cursor-pointer" title="Kembali ke Dashboard">
            <span class="material-symbols-outlined text-base">close</span>
        </a>
    </div>

    <!-- Flash Message Notifikasi -->
    <x-flash-message />

    <!-- Form Update Lokasi & Jam Operasional (FR-11.1 s/d FR-11.7) -->
    <form id="formUpdateLokasi" action="{{ route('owner.update-lokasi.save') }}" method="POST" class="flex flex-col gap-4">
        @csrf

        <input type="hidden" name="shipping_location_id" id="shipping_location_id" value="{{ old('shipping_location_id', $lokasiAktif->id ?? '') }}">

        <!-- 1. STATUS OPERASIONAL LAPAK HARI INI -->
        <div class="bg-slate-50 p-3 sm:p-3.5 rounded-2xl border border-slate-200/80">
            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-2">STATUS OPERASIONAL LAPAK</label>
            <div class="grid grid-cols-2 gap-2.5">
                <label id="btn-status-buka" class="flex items-center justify-center gap-2 p-2.5 rounded-xl border-2 cursor-pointer transition-all {{ old('status_lapak', $statusLapak ?? 'buka') === 'buka' ? 'bg-emerald-50 text-emerald-800 border-emerald-500 shadow-2xs font-bold' : 'bg-white text-slate-600 border-slate-200 font-medium' }}">
                    <input type="radio" name="status_lapak" value="buka" {{ old('status_lapak', $statusLapak ?? 'buka') === 'buka' ? 'checked' : '' }} onchange="updateStatusLapakUI('buka')" class="sr-only">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-xs">Lapak Buka (Aktif)</span>
                </label>

                <label id="btn-status-tutup" class="flex items-center justify-center gap-2 p-2.5 rounded-xl border-2 cursor-pointer transition-all {{ old('status_lapak', $statusLapak ?? 'buka') === 'tutup' ? 'bg-rose-50 text-rose-800 border-rose-500 shadow-2xs font-bold' : 'bg-white text-slate-600 border-slate-200 font-medium' }}">
                    <input type="radio" name="status_lapak" value="tutup" {{ old('status_lapak', $statusLapak ?? 'buka') === 'tutup' ? 'checked' : '' }} onchange="updateStatusLapakUI('tutup')" class="sr-only">
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                    <span class="text-xs">Lapak Tutup</span>
                </label>
            </div>
            <p class="text-[10px] text-slate-500 mt-2">Jika lapak tutup, pelanggan online baru akan diberi tahu jam operasional lapak.</p>
        </div>

        <!-- 2. NAMA TEMPAT & PATOKAN MANGKAL -->
        <div>
            <label for="nama_lokasi" class="text-xs font-bold text-slate-700 block mb-1.5 flex items-center justify-between">
                <span class="flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm text-blue-900">place</span>
                    <span>Nama Tempat / Patokan Mangkal</span>
                    <span class="text-rose-500">*</span>
                </span>
                <span class="text-[10px] text-slate-400 font-normal">Contoh: Lapak Jl. Kuwung</span>
            </label>
            <div class="flex items-center bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 focus-within:bg-white focus-within:border-blue-900 focus-within:ring-4 focus-within:ring-blue-900/10 transition-all">
                <input type="text" 
                       id="nama_lokasi" 
                       name="nama_lokasi" 
                       required
                       value="{{ old('nama_lokasi', $lokasiAktif->nama_lokasi ?? 'Lapak Jl. Kuwung') }}" 
                       placeholder="Contoh: Jl. Benteng Pancasila (Depan Taman Sentanan)" 
                       class="w-full bg-transparent border-0 outline-none text-xs text-slate-900 font-medium placeholder:text-slate-400 focus:ring-0" />
            </div>
            <x-input-error for="nama_lokasi" class="mt-1" />
        </div>

        <!-- 3. ALAMAT LENGKAP LAPAK -->
        <div>
            <div class="flex items-center justify-between mb-1.5 gap-2">
                <label for="alamat" class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm text-blue-900">home</span>
                    <span>Alamat Lengkap Lapak di Mojokerto</span>
                    <span class="text-rose-500">*</span>
                </label>
                <button type="button" onclick="triggerForwardGeocode()" class="px-2 py-0.5 bg-blue-50 hover:bg-blue-100 text-blue-900 border border-blue-200 rounded-lg text-[10px] font-bold inline-flex items-center gap-1 transition-colors cursor-pointer shrink-0" title="Sesuaikan titik peta dari teks alamat">
                    <span class="material-symbols-outlined text-[12px]">my_location</span>
                    <span>Sesuaikan Titik Peta</span>
                </button>
            </div>
            <textarea id="alamat" 
                      name="alamat" 
                      rows="2" 
                      required
                      placeholder="Tuliskan nama jalan, kelurahan, dan patokan booth (Contoh: Jl. Kuwung, Mergelo, Kec. Magersari, Kota Mojokerto)"
                      class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 font-medium focus:bg-white focus:outline-none focus:border-blue-900 focus:ring-4 focus:ring-blue-900/10 transition-all leading-relaxed placeholder:text-slate-400">{{ old('alamat', $lokasiAktif->alamat ?? 'Jl. Kuwung, Mergelo, Kec. Magersari, Kota Mojokerto') }}</textarea>
            <div id="geocode-status-owner" class="text-[10px] text-slate-500 mt-1 flex items-center gap-1 hidden">
                <span class="material-symbols-outlined text-[12px] text-blue-900" id="geocode-status-icon">sync</span>
                <span id="geocode-status-text">Menyinkronkan lokasi...</span>
            </div>
            <x-input-error for="alamat" class="mt-1" />
        </div>

        <!-- 4. PETA KOORDINAT LAPAK LEAFLET (DRAGGABLE PIN) -->
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm text-blue-900">pin_drop</span>
                    <span>Tentukan Titik Koordinat Lapak di Peta</span>
                    <span class="text-rose-500">*</span>
                </label>
                <span class="text-[10px] text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md font-mono" id="display-coords-owner">
                    {{ old('latitude', $lokasiAktif->latitude ?? -7.472600) }}, {{ old('longitude', $lokasiAktif->longitude ?? 112.438500) }}
                </span>
            </div>

            <!-- Leaflet Container -->
            <div class="relative rounded-2xl border border-slate-300 overflow-hidden shadow-2xs">
                <div id="map-lapak"></div>
                <div class="absolute bottom-2 left-2 z-20 bg-white/95 backdrop-blur-xs px-2.5 py-1 rounded-lg border border-slate-200 text-[10px] text-slate-700 font-medium flex items-center gap-1 shadow-2xs">
                    <span class="material-symbols-outlined text-xs text-blue-900">touch_app</span>
                    <span>Geser pin kopi untuk menyesuaikan titik lapak</span>
                </div>
            </div>

            <!-- Alert Penolakan Luar Kota Mojokerto (Point 1) -->
            <div id="alert-lapak-out-of-bounds" class="hidden mt-2 p-3 bg-rose-50 border border-rose-200 rounded-xl flex items-start gap-2.5 text-rose-900">
                <span class="material-symbols-outlined text-rose-600 text-lg shrink-0 mt-0.5">error</span>
                <div class="text-xs leading-relaxed">
                    <strong class="font-bold block text-rose-950 mb-0.5">Lokasi di Luar Kota Mojokerto Ditolak!</strong>
                    <span>Lapak street coffee Kopi Tiga Titik wajib berlokasi di dalam wilayah Kota Mojokerto. Titik yang kamu pilih saat ini berada di luar Kota Mojokerto. Silakan geser pin kembali ke area Kota Mojokerto.</span>
                </div>
            </div>

            <input type="hidden" name="latitude" id="input_latitude" value="{{ old('latitude', $lokasiAktif->latitude ?? -7.472600) }}">
            <input type="hidden" name="longitude" id="input_longitude" value="{{ old('longitude', $lokasiAktif->longitude ?? 112.438500) }}">
            <x-input-error for="latitude" class="mt-1" />
            <x-input-error for="longitude" class="mt-1" />
        </div>

        <!-- 5. JAM OPERASIONAL LAPAK (FR-11.6) -->
        <div>
            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5">JAM OPERASIONAL LAPAK</label>
            <div class="grid grid-cols-2 gap-3">
                <!-- Jam Buka -->
                <div>
                    <span class="text-xs font-semibold text-slate-700 flex items-center gap-1 mb-1">
                        <span class="material-symbols-outlined text-xs text-blue-900">schedule</span>
                        <span>Jam Buka</span>
                    </span>
                    <div class="flex items-center bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus-within:bg-white focus-within:border-blue-900">
                        <input type="time" 
                               id="jam_buka" 
                               name="jam_buka" 
                               required
                               value="{{ old('jam_buka', $jamBuka ?? '15:00') }}" 
                               class="w-full bg-transparent border-0 outline-none text-xs font-bold text-slate-900 focus:ring-0" />
                    </div>
                    <x-input-error for="jam_buka" class="mt-1" />
                </div>

                <!-- Jam Tutup -->
                <div>
                    <span class="text-xs font-semibold text-slate-700 flex items-center gap-1 mb-1">
                        <span class="material-symbols-outlined text-xs text-blue-900">schedule</span>
                        <span>Jam Tutup</span>
                    </span>
                    <div class="flex items-center bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus-within:bg-white focus-within:border-blue-900">
                        <input type="time" 
                               id="jam_tutup" 
                               name="jam_tutup" 
                               required
                               value="{{ old('jam_tutup', $jamTutup ?? '18:00') }}" 
                               class="w-full bg-transparent border-0 outline-none text-xs font-bold text-slate-900 focus:ring-0" />
                    </div>
                    <x-input-error for="jam_tutup" class="mt-1" />
                </div>
            </div>
            <p class="text-[10px] text-slate-400 mt-1.5">Jam operasional digunakan sebagai panduan jam kurir siaga dan batas waktu checkout online pelanggan.</p>
        </div>

        <!-- 6. DAFTAR LOKASI TERSIMPAN (FR-11.1 s/d FR-11.4) -->
        @if(isset($locations) && $locations->isNotEmpty())
            <div class="pt-2 border-t border-slate-100">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-2">PILIH DARI LOKASI TERSIMPAN</span>
                <div class="flex flex-col gap-2 max-h-36 overflow-y-auto pr-1">
                    @foreach($locations as $loc)
                        @php
                            $isActive = ($lokasiAktif && $lokasiAktif->id === $loc->id);
                        @endphp
                        <div id="card-saved-loc-{{ $loc->id }}" class="card-saved-location p-2.5 rounded-xl border {{ $isActive ? 'border-blue-900 bg-blue-50/50' : 'border-slate-200 bg-white hover:bg-slate-50' }} flex items-center justify-between gap-2 transition-all">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs font-bold text-slate-900 truncate">{{ $loc->nama_lokasi }}</span>
                                    <span id="badge-active-{{ $loc->id }}" class="badge-active-location px-1.5 py-0.2 bg-blue-900 text-white text-[9px] font-extrabold rounded-full uppercase {{ $isActive ? '' : 'hidden' }}">Lapak Aktif</span>
                                </div>
                                <p class="text-[10px] text-slate-500 truncate">{{ $loc->alamat }}</p>
                            </div>
                            <button type="button" 
                                    id="btn-use-loc-{{ $loc->id }}"
                                    data-loc-id="{{ $loc->id }}"
                                    onclick="loadSavedLocation({{ $loc->id }}, '{{ addslashes($loc->nama_lokasi) }}', '{{ addslashes($loc->alamat) }}', {{ $loc->latitude }}, {{ $loc->longitude }})" 
                                    class="btn-use-location px-2.5 py-1 text-[11px] font-bold rounded-lg border transition-all cursor-pointer shrink-0 {{ $isActive ? 'bg-blue-900 text-white border-blue-900 shadow-2xs' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-100' }}">
                                Gunakan
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- 7. TOMBOL AKSI SIMPAN -->
        <div class="flex flex-col gap-2 pt-3 border-t border-slate-100">
            <button type="submit" class="w-full py-3 bg-blue-900 hover:bg-blue-800 text-white rounded-xl font-bold text-xs flex justify-center items-center gap-1.5 shadow-md transition-all active:scale-98 cursor-pointer">
                <span class="material-symbols-outlined text-base">check_circle</span>
                <span>Simpan Pengaturan Lapak Hari Ini</span>
            </button>
            <a href="{{ url('/owner/dashboard') }}" class="w-full py-2 text-center text-xs text-slate-500 hover:text-slate-800 font-semibold transition-colors">
                Kembali ke Dashboard
            </a>
        </div>
    </form>
</div>

<!-- Leaflet JS via CDN -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    const DEFAULT_LAPAK_LAT = {{ (float) old('latitude', $lokasiAktif->latitude ?? -7.472600) }};
    const DEFAULT_LAPAK_LNG = {{ (float) old('longitude', $lokasiAktif->longitude ?? 112.438500) }};

    let mapLapak = null;
    let markerLapak = null;

    document.addEventListener('DOMContentLoaded', function () {
        initLapakMap();
    });

    function initLapakMap() {
        const container = document.getElementById('map-lapak');
        if (!container) return;

        mapLapak = L.map('map-lapak', {
            center: [DEFAULT_LAPAK_LAT, DEFAULT_LAPAK_LNG],
            zoom: 16,
            scrollWheelZoom: false,
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a>'
        }).addTo(mapLapak);

        const lapakIcon = L.divIcon({
            className: 'custom-lapak-marker',
            html: `
                <div style="position: relative; display: flex; flex-direction: column; align-items: center; filter: drop-shadow(0 6px 8px rgba(15, 23, 42, 0.3));">
                    <div class="pin-radar-owner"></div>
                    <div style="width: 38px; height: 38px; background: #1e3a8a; border: 2.5px solid #ffffff; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; position: relative; z-index: 2;">
                        <span class="material-symbols-outlined" style="font-size: 20px; line-height: 1;">storefront</span>
                    </div>
                    <div style="width: 0; height: 0; border-left: 6px solid transparent; border-right: 6px solid transparent; border-top: 9px solid #1e3a8a; margin-top: -1px; position: relative; z-index: 2;"></div>
                </div>
            `,
            iconSize: [38, 46],
            iconAnchor: [19, 46],
            popupAnchor: [0, -46]
        });

        markerLapak = L.marker([DEFAULT_LAPAK_LAT, DEFAULT_LAPAK_LNG], {
            draggable: true,
            icon: lapakIcon
        }).addTo(mapLapak);

        markerLapak.bindPopup("<b>Titik Mangkal Lapak Kopi</b><br>Geser ke lokasi mangkal hari ini.").openPopup();

        markerLapak.on('dragend', function () {
            const pos = markerLapak.getLatLng();
            updateLapakCoords(pos.lat, pos.lng);
            reverseGeocodeLapak(pos.lat, pos.lng);
        });

        mapLapak.on('click', function (e) {
            markerLapak.setLatLng(e.latlng);
            updateLapakCoords(e.latlng.lat, e.latlng.lng);
            reverseGeocodeLapak(e.latlng.lat, e.latlng.lng);
        });

        // Sinkronisasi Otomatis Input Manual Alamat / Nama Lokasi -> Geser Pin Peta (Forward Geocoding)
        const inputAlamatEl = document.getElementById('alamat');
        const inputNamaEl = document.getElementById('nama_lokasi');

        if (inputAlamatEl) {
            inputAlamatEl.addEventListener('input', function () {
                if (isUpdatingFromMap) return;
                clearTimeout(forwardGeocodeTimer);
                forwardGeocodeTimer = setTimeout(() => {
                    const val = inputAlamatEl.value.trim();
                    if (val.length >= 5) {
                        forwardGeocodeLapak(val, false);
                    }
                }, 800);
            });
        }

        if (inputNamaEl) {
            inputNamaEl.addEventListener('input', function () {
                if (isUpdatingFromMap) return;
                clearTimeout(forwardGeocodeTimer);
                forwardGeocodeTimer = setTimeout(() => {
                    const rawName = inputNamaEl.value.trim().replace(/^lapak\s+/i, '');
                    if (rawName.length >= 4) {
                        forwardGeocodeLapak(rawName, true);
                    }
                }, 850);
            });
        }

        setTimeout(() => {
            mapLapak.invalidateSize();
        }, 350);
    }

    let isUpdatingFromMap = false;
    let forwardGeocodeTimer = null;

    // Kamus Fallback Jalan & Patokan Utama Kota Mojokerto (jika offline / rate-limit)
    const MOJOKERTO_STREETS = [
        { keywords: ['kuwung', 'mergelo'], nama: 'Lapak Jl. Kuwung', alamat: 'Jl. Kuwung, Mergelo, Kec. Magersari, Kota Mojokerto', lat: -7.472600, lng: 112.438500 },
        { keywords: ['benteng pancasila', 'benpas', 'taman sentanan'], nama: 'Lapak Jl. Benteng Pancasila', alamat: 'Jl. Benteng Pancasila, Mergelo, Kec. Magersari, Kota Mojokerto', lat: -7.468800, lng: 112.441200 },
        { keywords: ['hasyim ashari', 'hasyim asyari', 'kauman'], nama: 'Lapak Jl. KH. Hasyim Ashari', alamat: 'Jl. Kyai H. Hasyim Ashari, Kauman, Kec. Prajurit Kulon, Kota Mojokerto', lat: -7.466200, lng: 112.432500 },
        { keywords: ['majapahit', 'alun-alun', 'alun alun'], nama: 'Lapak Jl. Majapahit', alamat: 'Jl. Majapahit, Mergelo, Kranggan, Kec. Prajurit Kulon, Kota Mojokerto', lat: -7.466500, lng: 112.434200 },
        { keywords: ['gajah mada', 'empu'], nama: 'Lapak Jl. Gajah Mada', alamat: 'Jl. Gajah Mada, Mergelo, Balongsari, Kec. Magersari, Kota Mojokerto', lat: -7.464800, lng: 112.439800 },
        { keywords: ['pahlawan', 'stasiun'], nama: 'Lapak Jl. Pahlawan', alamat: 'Jl. Pahlawan, Mergelo, Kranggan, Kec. Kranggan, Kota Mojokerto', lat: -7.475200, lng: 112.435100 },
        { keywords: ['bhayangkara', 'miji'], nama: 'Lapak Jl. Bhayangkara', alamat: 'Jl. Bhayangkara, Mergelo, Miji, Kec. Kranggan, Kota Mojokerto', lat: -7.470100, lng: 112.434500 },
        { keywords: ['suroso', 'suromulang'], nama: 'Lapak Jl. Suroso', alamat: 'Jl. Suroso, Surodinawan, Kec. Prajurit Kulon, Kota Mojokerto', lat: -7.481500, lng: 112.422000 },
        { keywords: ['wijaya kusuma', 'sookook'], nama: 'Lapak Jl. Wijaya Kusuma', alamat: 'Jl. Wijaya Kusuma, Banjaragung, Kec. Puri, Mojokerto', lat: -7.488200, lng: 112.442500 },
        { keywords: ['empunala', 'kedundung'], nama: 'Lapak Jl. Empunala', alamat: 'Jl. Empunala, Kedundung, Kec. Magersari, Kota Mojokerto', lat: -7.463100, lng: 112.449500 }
    ];

    function showGeocodeStatus(message, isSuccess = true, isLoading = false) {
        const box = document.getElementById('geocode-status-owner');
        const icon = document.getElementById('geocode-status-icon');
        const text = document.getElementById('geocode-status-text');
        if (!box || !icon || !text) return;

        box.classList.remove('hidden');
        text.innerText = message;
        if (isLoading) {
            icon.innerText = 'sync';
            icon.className = 'material-symbols-outlined text-[12px] text-blue-900 animate-spin';
            text.className = 'text-blue-900 font-medium';
        } else if (isSuccess) {
            icon.innerText = 'check_circle';
            icon.className = 'material-symbols-outlined text-[12px] text-emerald-600';
            text.className = 'text-emerald-700 font-semibold';
        } else {
            icon.innerText = 'info';
            icon.className = 'material-symbols-outlined text-[12px] text-amber-600';
            text.className = 'text-amber-700 font-medium';
        }
    }

    // Arah 1: Titik Peta -> Otomatis Isi Alamat & Nama Lokasi (Reverse Geocoding)
    async function reverseGeocodeLapak(lat, lng) {
        showGeocodeStatus('Menyesuaikan alamat otomatis dari titik peta...', true, true);
        isUpdatingFromMap = true;

        try {
            const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}&zoom=18&addressdetails=1&accept-language=id`;
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error('Reverse geocode gagal');
            const data = await res.json();

            if (data && data.address) {
                const addr = data.address;
                const road = addr.road || addr.pedestrian || addr.footway || addr.amenity || addr.shop || addr.tourism || addr.building || data.name || '';
                const houseNumber = addr.house_number ? `No. ${addr.house_number}` : '';
                const kelurahan = addr.village || addr.suburb || addr.neighbourhood || addr.hamlet || '';
                const rawKec = addr.city_district || addr.municipality || addr.subdistrict || '';
                const kecamatan = rawKec ? (rawKec.toLowerCase().startsWith('kec') ? rawKec : `Kec. ${rawKec}`) : '';
                const kota = addr.city || addr.town || addr.county || 'Kota Mojokerto';

                const streetPart = [road, houseNumber].filter(Boolean).join(' ');
                const fullAddress = [streetPart, kelurahan, kecamatan, kota].filter(Boolean).join(', ') || data.display_name;

                let namaLapak = 'Lapak Kopi Tiga Titik';
                if (data.name && road && data.name.toLowerCase() !== road.toLowerCase()) {
                    namaLapak = `Lapak ${data.name} (${road})`;
                } else if (road) {
                    namaLapak = road.toLowerCase().startsWith('lapak') ? road : `Lapak ${road}`;
                } else if (kelurahan) {
                    namaLapak = `Lapak Area ${kelurahan}`;
                }

                const inputAlamat = document.getElementById('alamat');
                const inputNama = document.getElementById('nama_lokasi');
                if (inputAlamat && fullAddress) inputAlamat.value = fullAddress;
                if (inputNama && namaLapak) inputNama.value = namaLapak;

                if (markerLapak) {
                    markerLapak.setPopupContent(`<b>${namaLapak}</b><br><span style="font-size:11px;">${fullAddress}</span>`).openPopup();
                }

                showGeocodeStatus('Alamat & nama tempat otomatis diperbarui dari titik peta.', true, false);
                setTimeout(() => { isUpdatingFromMap = false; }, 150);
                return;
            }
        } catch (err) {
            // Fallback ke pencocokan terdekat Mojokerto
            let nearest = MOJOKERTO_STREETS[0];
            let minDist = Infinity;
            MOJOKERTO_STREETS.forEach(st => {
                const d = Math.hypot(st.lat - lat, st.lng - lng);
                if (d < minDist) {
                    minDist = d;
                    nearest = st;
                }
            });

            if (nearest) {
                document.getElementById('nama_lokasi').value = nearest.nama;
                document.getElementById('alamat').value = nearest.alamat;
                showGeocodeStatus('Alamat disesuaikan ke area terdekat di Mojokerto.', true, false);
            }
        }
        setTimeout(() => { isUpdatingFromMap = false; }, 150);
    }

    // Arah 2: Input Manual Alamat / Nama Lokasi -> Otomatis Geser Pin Peta (Forward Geocoding)
    async function forwardGeocodeLapak(queryText, alsoUpdateAddress = false) {
        if (!queryText || queryText.trim().length < 4) return;
        showGeocodeStatus('Mencari titik koordinat di peta sesuai alamat...', true, true);

        const qLower = queryText.toLowerCase();
        const searchQuery = qLower.includes('mojokerto') ? queryText : `${queryText}, Kota Mojokerto, Jawa Timur`;

        try {
            const url = `https://nominatim.openstreetmap.org/search?format=jsonv2&q=${encodeURIComponent(searchQuery)}&viewbox=112.38,-7.42,112.50,-7.52&bounded=0&limit=1&addressdetails=1&accept-language=id`;
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error('Forward geocode gagal');
            const results = await res.json();

            if (Array.isArray(results) && results.length > 0) {
                const hit = results[0];
                const lat = parseFloat(hit.lat);
                const lng = parseFloat(hit.lon);

                if (!isNaN(lat) && !isNaN(lng)) {
                    updateLapakCoords(lat, lng);
                    if (mapLapak && markerLapak) {
                        markerLapak.setLatLng([lat, lng]);
                        mapLapak.flyTo([lat, lng], 17, { duration: 0.6 });
                        markerLapak.openPopup();
                    }

                    if (alsoUpdateAddress && hit.address) {
                        const addr = hit.address;
                        const road = addr.road || addr.pedestrian || hit.name || queryText;
                        const kelurahan = addr.village || addr.suburb || addr.neighbourhood || '';
                        const rawKec = addr.city_district || addr.municipality || '';
                        const kecamatan = rawKec ? (rawKec.toLowerCase().startsWith('kec') ? rawKec : `Kec. ${rawKec}`) : '';
                        const kota = addr.city || addr.town || 'Kota Mojokerto';
                        const formattedAddr = [road, kelurahan, kecamatan, kota].filter(Boolean).join(', ');
                        if (formattedAddr) {
                            isUpdatingFromMap = true;
                            document.getElementById('alamat').value = formattedAddr;
                            setTimeout(() => { isUpdatingFromMap = false; }, 150);
                        }
                    }

                    showGeocodeStatus('Titik pin di peta otomatis mengikuti alamat yang diketik.', true, false);
                    return;
                }
            }
        } catch (err) {
            // Abaikan error jaringan, lanjutkan ke fallback kamus Mojokerto
        }

        // Fallback kamus lokal jalan Mojokerto
        const matched = MOJOKERTO_STREETS.find(st => st.keywords.some(k => qLower.includes(k)));
        if (matched) {
            updateLapakCoords(matched.lat, matched.lng);
            if (mapLapak && markerLapak) {
                markerLapak.setLatLng([matched.lat, matched.lng]);
                mapLapak.flyTo([matched.lat, matched.lng], 17, { duration: 0.6 });
                markerLapak.openPopup();
            }
            if (alsoUpdateAddress) {
                isUpdatingFromMap = true;
                document.getElementById('alamat').value = matched.alamat;
                setTimeout(() => { isUpdatingFromMap = false; }, 150);
            }
            showGeocodeStatus('Titik pin peta disesuaikan ke lokasi jalan di Mojokerto.', true, false);
        } else {
            showGeocodeStatus('Titik spesifik belum ditemukan, silakan geser pin secara langsung di peta.', false, false);
        }
    }

    function triggerForwardGeocode() {
        const alamatVal = document.getElementById('alamat')?.value?.trim() || '';
        const namaVal = document.getElementById('nama_lokasi')?.value?.trim() || '';
        const query = alamatVal.length >= 4 ? alamatVal : namaVal.replace(/^lapak\s+/i, '');
        if (query) {
            forwardGeocodeLapak(query, false);
        }
    }

    function isInsideMojokertoCity(lat, lng) {
        return lat >= -7.530000 && lat <= -7.420000 &&
               lng >= 112.390000 && lng <= 112.480000;
    }

    function updateLapakCoords(lat, lng) {
        const numLat = parseFloat(lat);
        const numLng = parseFloat(lng);
        const formattedLat = Number(lat).toFixed(6);
        const formattedLng = Number(lng).toFixed(6);

        document.getElementById('input_latitude').value = formattedLat;
        document.getElementById('input_longitude').value = formattedLng;
        
        const coordsDisplay = document.getElementById('display-coords-owner');
        const alertBox = document.getElementById('alert-lapak-out-of-bounds');
        const isMojokerto = isInsideMojokertoCity(numLat, numLng);

        if (isMojokerto) {
            coordsDisplay.className = 'text-[10px] text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md font-mono';
            coordsDisplay.innerText = `${formattedLat}, ${formattedLng}`;
            if (alertBox) alertBox.classList.add('hidden');
        } else {
            coordsDisplay.className = 'text-[10px] text-rose-700 bg-rose-100 border border-rose-300 px-2 py-0.5 rounded-md font-mono font-bold';
            coordsDisplay.innerText = `${formattedLat}, ${formattedLng} (DI LUAR MOJOKERTO)`;
            if (alertBox) alertBox.classList.remove('hidden');
        }

        if (markerLapak) {
            const popupMsg = isMojokerto 
                ? `<b>Titik Lapak:</b><br>${formattedLat}, ${formattedLng}`
                : `<b style="color: #b91c1c;">⚠️ Di Luar Kota Mojokerto</b><br><span style="font-size:11px;">Pilih lokasi di dalam Mojokerto</span>`;
            markerLapak.setPopupContent(popupMsg);
        }
    }

    // Cegah submit form jika titik berada di luar Kota Mojokerto
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('formUpdateLokasi');
        if (form) {
            form.addEventListener('submit', function (e) {
                const lat = parseFloat(document.getElementById('input_latitude').value);
                const lng = parseFloat(document.getElementById('input_longitude').value);
                if (!isInsideMojokertoCity(lat, lng)) {
                    e.preventDefault();
                    const alertBox = document.getElementById('alert-lapak-out-of-bounds');
                    if (alertBox) alertBox.classList.remove('hidden');
                    alert('Titik koordinat lapak berada di luar wilayah Kota Mojokerto!\n\nLapak street coffee Kopi Tiga Titik wajib berlokasi di dalam Kota Mojokerto.');
                    return false;
                }
            });
        }
    });

    function loadSavedLocation(id, nama, alamat, lat, lng) {
        document.getElementById('shipping_location_id').value = id;
        document.getElementById('nama_lokasi').value = nama;
        document.getElementById('alamat').value = alamat;

        updateLapakCoords(lat, lng);
        if (mapLapak && markerLapak) {
            mapLapak.setView([lat, lng], 16);
            markerLapak.setLatLng([lat, lng]);
            markerLapak.openPopup();
        }

        // 1. Reset seluruh kartu lokasi ke tampilan standar (putih/abu)
        document.querySelectorAll('.card-saved-location').forEach(card => {
            card.classList.remove('border-blue-900', 'bg-blue-50/50');
            card.classList.add('border-slate-200', 'bg-white');
        });

        // 2. Sembunyikan semua badge Lapak Aktif
        document.querySelectorAll('.badge-active-location').forEach(badge => {
            badge.classList.add('hidden');
        });

        // 3. Reset seluruh tombol Gunakan menjadi abu/putih
        document.querySelectorAll('.btn-use-location').forEach(btn => {
            btn.className = 'btn-use-location px-2.5 py-1 text-[11px] font-bold rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-100 transition-all cursor-pointer shrink-0';
        });

        // 4. Aktifkan kartu dan tombol lokasi terpilih menjadi biru solid
        const selectedCard = document.getElementById('card-saved-loc-' + id);
        if (selectedCard) {
            selectedCard.classList.add('border-blue-900', 'bg-blue-50/50');
            selectedCard.classList.remove('border-slate-200', 'bg-white');
        }

        const selectedBadge = document.getElementById('badge-active-' + id);
        if (selectedBadge) {
            selectedBadge.classList.remove('hidden');
        }

        const selectedBtn = document.getElementById('btn-use-loc-' + id);
        if (selectedBtn) {
            selectedBtn.className = 'btn-use-location px-2.5 py-1 text-[11px] font-bold rounded-lg border border-blue-900 bg-blue-900 text-white shadow-2xs transition-all cursor-pointer shrink-0';
        }
    }

    function updateStatusLapakUI(status) {
        const btnBuka = document.getElementById('btn-status-buka');
        const btnTutup = document.getElementById('btn-status-tutup');

        if (status === 'buka') {
            btnBuka.className = 'flex items-center justify-center gap-2 p-2.5 rounded-xl border-2 cursor-pointer transition-all bg-emerald-50 text-emerald-800 border-emerald-500 shadow-2xs font-bold';
            btnTutup.className = 'flex items-center justify-center gap-2 p-2.5 rounded-xl border-2 cursor-pointer transition-all bg-white text-slate-600 border-slate-200 font-medium';
        } else {
            btnTutup.className = 'flex items-center justify-center gap-2 p-2.5 rounded-xl border-2 cursor-pointer transition-all bg-rose-50 text-rose-800 border-rose-500 shadow-2xs font-bold';
            btnBuka.className = 'flex items-center justify-center gap-2 p-2.5 rounded-xl border-2 cursor-pointer transition-all bg-white text-slate-600 border-slate-200 font-medium';
        }
    }
</script>
</body>
</html>
