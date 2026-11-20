@extends('layouts.pelanggan')

@section('title', 'My Account')
@section('active_menu', 'profil')
@section('page_title', 'My Account')
@section('role_badge', 'PELANGGAN')
@section('page_subtitle', 'Kelola informasi profil, alamat pengiriman, dan riwayat pesananmu')

@push('styles')
    <!-- Leaflet CSS via CDN -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        #profile-address-map {
            height: 240px;
            width: 100%;
            z-index: 10;
            border-radius: 1rem;
        }
    </style>
@endpush

@section('content')

    @php
        $addressData = $savedAddress ?? [
            'label'   => 'Rumah / Kost',
            'alamat'  => 'Jl. Hayam Wuruk No. 42, Mergelo, Kec. Magersari, Kota Mojokerto',
            'patokan' => 'Pagar hitam depan apotek, seberang minimarket',
            'lat'     => -7.472100,
            'lng'     => 112.434000,
        ];
    @endphp

    <!-- Notifikasi Flash Global Template PPL-24 -->
    <x-flash-message />

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 md:gap-6 items-start">

        <!-- Kolom Kiri: Profil Singkat & Aktivitas -->
        <div class="lg:col-span-2 flex flex-col gap-4">

            <!-- Identitas User Card -->
            <div class="p-5 bg-white rounded-2xl border border-slate-200/90 shadow-xs flex flex-col gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-blue-900 text-white text-2xl font-extrabold flex items-center justify-center font-['Plus_Jakarta_Sans'] shadow-md shrink-0">
                        {{ strtoupper(substr(auth()->user()->name ?? 'P', 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex flex-col">
                        <h2 class="text-base font-bold text-slate-900 font-['Plus_Jakarta_Sans'] leading-tight truncate">{{ auth()->user()->name ?? 'Pelanggan' }}</h2>
                        <span class="inline-block mt-1 px-2.5 py-0.5 bg-emerald-50 text-emerald-700 font-semibold text-[11px] rounded-full border border-emerald-200 self-start">
                            Pelanggan Aktif
                        </span>
                        <div class="flex items-center gap-1.5 text-slate-500 text-xs mt-1.5">
                            <span class="material-symbols-outlined text-slate-400 text-sm">mail</span>
                            <span class="truncate">{{ auth()->user()->email ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <div class="h-px bg-slate-100"></div>

                <div class="flex items-center gap-2 text-xs text-slate-600">
                    <span class="material-symbols-outlined text-blue-900 text-base">verified_user</span>
                    <span>Pelanggan Terdaftar Kopi Tiga Titik</span>
                </div>
            </div>

            <!-- Ringkasan Aktivitas Belanja -->
            <div class="p-5 bg-white rounded-2xl border border-slate-200/90 shadow-xs flex flex-col gap-3">
                <div class="flex items-center justify-between pb-1 border-b border-slate-100">
                    <h3 class="text-xs md:text-sm font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Aktivitas Pesanan</h3>
                    <a href="{{ url('/riwayat-pesanan') }}" class="text-[11px] font-bold text-blue-900 hover:underline">Lihat Semua</a>
                </div>

                <div class="flex flex-col gap-2.5">
                    <a href="{{ url('/riwayat-pesanan') }}" class="p-3 bg-slate-50 hover:bg-blue-50/60 rounded-xl border border-slate-200/80 flex items-center justify-between transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-blue-100 text-blue-900 rounded-lg flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-lg">receipt_long</span>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-slate-800 font-['Plus_Jakarta_Sans']">Riwayat Pesanan</div>
                                <div class="text-[10px] text-slate-500">{{ $totalBottlesCount ?? 0 }} Botol Kopi telah dinikmati</div>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-slate-400 text-base">chevron_right</span>
                    </a>

                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 flex items-center gap-3">
                        <div class="w-9 h-9 bg-amber-100 text-amber-800 rounded-lg flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-lg">favorite</span>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-800 font-['Plus_Jakarta_Sans']">Varian Favorit</div>
                            <div class="text-[11px] text-amber-800 font-semibold">Titik Temu (Gula Aren)</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Kolom Kanan: Alamat Pengiriman & Pengaturan Akun -->
        <div class="lg:col-span-3 flex flex-col gap-4">

            <!-- Alamat Pengiriman Utama Card -->
            <div class="p-5 md:p-6 bg-white rounded-2xl border border-slate-200/90 shadow-xs flex flex-col gap-3.5">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-blue-50 text-blue-900 rounded-lg flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-base">location_on</span>
                        </div>
                        <div>
                            <h3 class="text-xs md:text-sm font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Alamat Pengiriman Utama</h3>
                            <p class="text-[10px] text-slate-500">Digunakan otomatis sebagai titik default kurir saat checkout</p>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 bg-blue-100 text-blue-900 text-[10px] font-bold rounded-md">UTAMA</span>
                </div>

                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/80 flex flex-col gap-1.5">
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <span class="text-xs font-bold text-slate-900 font-['Plus_Jakarta_Sans']">{{ $addressData['label'] }}</span>
                        <span class="text-[11px] text-slate-500 font-medium">{{ auth()->user()->name ?? 'Penerima' }}</span>
                    </div>
                    <p class="text-xs text-slate-700 leading-relaxed">
                        {{ $addressData['alamat'] }}
                    </p>
                    @if(!empty($addressData['patokan']))
                        <div class="pt-1 flex items-center gap-1.5 text-[11px] text-slate-500">
                            <span class="material-symbols-outlined text-xs text-slate-400">near_me</span>
                            <span>Patokan: {{ $addressData['patokan'] }}</span>
                        </div>
                    @endif
                    <div class="pt-1.5 mt-1 border-t border-slate-200/70 flex items-center justify-between gap-2 flex-wrap">
                        <span class="text-[10px] font-mono text-slate-500 flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs text-emerald-600">my_location</span>
                            <span>Koordinat: {{ number_format((float) $addressData['lat'], 6) }}, {{ number_format((float) $addressData['lng'], 6) }}</span>
                        </span>
                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-[9px] font-bold">
                            Area Kota Mojokerto
                        </span>
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <button type="button"
                            id="btn-open-edit-address"
                            onclick="openEditAddressModal()"
                            class="px-4 py-2 bg-blue-900 hover:bg-blue-800 active:scale-98 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 transition-all shadow-xs cursor-pointer">
                        <span class="material-symbols-outlined text-sm">edit_location</span>
                        <span>Ubah Alamat Pengiriman</span>
                    </button>
                </div>
            </div>

            <!-- Bantuan & Aksi Akun Card -->
            <div class="p-5 md:p-6 bg-white rounded-2xl border border-slate-200/90 shadow-xs flex flex-col gap-3.5">
                <div class="pb-2 border-b border-slate-100 flex items-center gap-2">
                    <div class="w-8 h-8 bg-blue-50 text-blue-900 rounded-lg flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-base">security</span>
                    </div>
                    <div>
                        <h3 class="text-xs md:text-sm font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Keamanan &amp; Aksi Akun</h3>
                        <p class="text-[10px] text-slate-500">Akses bantuan dan keluar dari sesi aplikasi</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <a href="https://wa.me/6281234567890" target="_blank" rel="noopener"
                       class="p-3.5 bg-slate-50 hover:bg-blue-50/70 rounded-xl border border-slate-200/80 flex items-center gap-3 transition-colors">
                        <div class="w-9 h-9 bg-emerald-100 text-emerald-800 rounded-lg flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-lg">support_agent</span>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="text-xs font-bold text-slate-800 font-['Plus_Jakarta_Sans']">Bantuan CS Lapak</span>
                            <span class="text-[10px] text-slate-500">Hubungi via WhatsApp</span>
                        </div>
                    </a>

                    <button type="button" onclick="openLogoutModal()"
                            class="p-3.5 bg-rose-50/60 hover:bg-rose-100/70 rounded-xl border border-rose-200 text-rose-700 flex items-center gap-3 transition-colors text-left cursor-pointer">
                        <div class="w-9 h-9 bg-rose-100 text-rose-700 rounded-lg flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-lg">logout</span>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="text-xs font-bold font-['Plus_Jakarta_Sans']">Keluar dari Akun</span>
                            <span class="text-[10px] text-rose-600">Akhiri sesi login</span>
                        </div>
                    </button>
                </div>
            </div>

        </div>

    </div>

    <!-- ============================================================== -->
    <!-- MODAL UBAH ALAMAT PENGIRIMAN UTAMA + PETA LEAFLET (TC-CHK-01) -->
    <!-- ============================================================== -->
    <div id="modal-edit-address" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 z-50 hidden opacity-0 transition-opacity duration-200" onclick="if (event.target === this) closeEditAddressModal()">
        <div class="bg-white w-full max-w-lg max-h-[92vh] overflow-y-auto rounded-3xl p-5 sm:p-6 shadow-2xl border border-slate-100 flex flex-col gap-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-900 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-xl">edit_location_alt</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm sm:text-base font-['Plus_Jakarta_Sans']">Ubah Alamat Pengiriman Utama</h3>
                        <p class="text-[11px] text-slate-500">Tentukan titik lokasi rumah / kantor di area Kota Mojokerto</p>
                    </div>
                </div>
                <button type="button" onclick="closeEditAddressModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>

            <form id="form-edit-address" action="{{ route('profile.address.update') }}" method="POST" class="flex flex-col gap-3.5">
                @csrf
                @method('PUT')

                <!-- Alert Validasi Geofence Template (PPL-24) -->
                <div id="profile-geofence-alert" class="hidden p-3 bg-rose-50 border border-rose-200 rounded-xl flex items-start gap-2 text-rose-900 text-xs">
                    <span class="material-symbols-outlined text-rose-600 text-base shrink-0 mt-0.5">wrong_location</span>
                    <div>
                        <strong class="font-bold block text-rose-950">Lokasi di Luar Wilayah Kota Mojokerto</strong>
                        <span id="profile-geofence-alert-text">Pengantaran Kopi Tiga Titik hanya melayani wilayah Kota Mojokerto. Silakan geser pin ke dalam Kota Mojokerto.</span>
                    </div>
                </div>

                <!-- Label Alamat -->
                <div>
                    <label for="profile_label" class="text-xs font-bold text-slate-700 block mb-1">Label Alamat</label>
                    <input type="text"
                           id="profile_label"
                           name="label"
                           value="{{ old('label', $addressData['label']) }}"
                           placeholder="Contoh: Rumah, Kost, Kantor"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 font-medium focus:bg-white focus:outline-none focus:border-blue-900 focus:ring-4 focus:ring-blue-900/10" />
                    <x-input-error for="label" class="mt-1" />
                </div>

                <!-- Alamat Lengkap -->
                <div>
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <label for="profile_alamat" class="text-xs font-bold text-slate-700">Alamat Lengkap <span class="text-rose-500">*</span></label>
                        <button type="button" onclick="searchProfileAddressOnMap()" class="px-2 py-0.5 bg-blue-50 hover:bg-blue-100 text-blue-900 border border-blue-200 rounded-lg text-[10px] font-bold inline-flex items-center gap-1 cursor-pointer">
                            <span class="material-symbols-outlined text-[12px]">search</span>
                            <span>Sesuaikan Titik Peta</span>
                        </button>
                    </div>
                    <textarea id="profile_alamat"
                              name="alamat"
                              rows="2"
                              required
                              placeholder="Tuliskan jalan, nomor rumah, RT/RW, kelurahan di Kota Mojokerto"
                              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 font-medium focus:bg-white focus:outline-none focus:border-blue-900 focus:ring-4 focus:ring-blue-900/10">{{ old('alamat', $addressData['alamat']) }}</textarea>
                    <x-input-error for="alamat" class="mt-1" />
                </div>

                <!-- Patokan -->
                <div>
                    <label for="profile_patokan" class="text-xs font-bold text-slate-700 block mb-1">Patokan (Opsional)</label>
                    <input type="text"
                           id="profile_patokan"
                           name="patokan"
                           value="{{ old('patokan', $addressData['patokan']) }}"
                           placeholder="Contoh: Pagar hitam depan apotek, sebelah masjid"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 font-medium focus:bg-white focus:outline-none focus:border-blue-900 focus:ring-4 focus:ring-blue-900/10" />
                    <x-input-error for="patokan" class="mt-1" />
                </div>

                <!-- Peta Interaktif Leaflet -->
                <div>
                    <div class="flex items-center justify-between gap-2 mb-1.5 flex-wrap">
                        <span class="text-xs font-bold text-slate-700 flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm text-blue-900">pin_drop</span>
                            <span>Titik Koordinat Peta</span>
                        </span>
                        <div class="flex items-center gap-2">
                            <span id="profile-coords-badge" class="text-[10px] font-mono bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md">
                                {{ number_format((float) old('lat', $addressData['lat']), 6) }}, {{ number_format((float) old('lng', $addressData['lng']), 6) }}
                            </span>
                            <button type="button" onclick="useCurrentGpsForProfile()" id="btn-profile-gps" class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-lg text-[10px] font-bold inline-flex items-center gap-1 cursor-pointer">
                                <span class="material-symbols-outlined text-[13px]">my_location</span>
                                <span id="btn-profile-gps-text">Lokasi Saya</span>
                            </button>
                        </div>
                    </div>

                    <div class="relative rounded-2xl overflow-hidden border border-slate-300 shadow-2xs">
                        <div id="profile-address-map"></div>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1">Klik peta atau geser pin biru untuk menentukan titik pengantaran utama Anda.</p>

                    <input type="hidden" name="lat" id="profile_lat" value="{{ old('lat', $addressData['lat']) }}">
                    <input type="hidden" name="lng" id="profile_lng" value="{{ old('lng', $addressData['lng']) }}">
                    <x-input-error for="lat" class="mt-1" />
                </div>

                <div class="pt-2 grid grid-cols-2 gap-2.5">
                    <button type="button" onclick="closeEditAddressModal()" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" id="btn-save-profile-address" class="w-full py-2.5 bg-blue-900 hover:bg-blue-800 active:scale-98 text-white rounded-xl text-xs font-bold transition-all shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                        <span class="material-symbols-outlined text-sm">save</span>
                        <span>Simpan Alamat Utama</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        let profileMap = null;
        let profileMarker = null;

        function isInsideMojokertoProfile(lat, lng) {
            return lat >= -7.530000 && lat <= -7.420000 &&
                   lng >= 112.390000 && lng <= 112.480000;
        }

        function openEditAddressModal() {
            const modal = document.getElementById('modal-edit-address');
            if (!modal) return;
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                initProfileAddressMap();
            }, 20);
        }

        function closeEditAddressModal() {
            const modal = document.getElementById('modal-edit-address');
            if (!modal) return;
            modal.classList.add('opacity-0');
            setTimeout(() => modal.classList.add('hidden'), 200);
        }

        function initProfileAddressMap() {
            const lat = parseFloat(document.getElementById('profile_lat').value) || -7.472100;
            const lng = parseFloat(document.getElementById('profile_lng').value) || 112.434000;

            if (!profileMap) {
                profileMap = L.map('profile-address-map', {
                    center: [lat, lng],
                    zoom: 16,
                    scrollWheelZoom: false,
                });

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap'
                }).addTo(profileMap);

                profileMarker = L.marker([lat, lng], { draggable: true }).addTo(profileMap);
                profileMarker.bindPopup('<b>Titik Alamat Utama Anda</b><br>Geser pin untuk menyesuaikan lokasi.').openPopup();

                profileMarker.on('dragend', function () {
                    const pos = profileMarker.getLatLng();
                    updateProfileCoords(pos.lat, pos.lng, true);
                });

                profileMap.on('click', function (e) {
                    profileMarker.setLatLng(e.latlng);
                    updateProfileCoords(e.latlng.lat, e.latlng.lng, true);
                });
            } else {
                profileMap.invalidateSize();
                profileMap.setView([lat, lng], 16);
                if (profileMarker) profileMarker.setLatLng([lat, lng]);
            }

            updateProfileCoords(lat, lng, false);
        }

        function updateProfileCoords(lat, lng, shouldReverseGeocode = false) {
            const numLat = parseFloat(lat);
            const numLng = parseFloat(lng);
            const fLat = numLat.toFixed(6);
            const fLng = numLng.toFixed(6);

            document.getElementById('profile_lat').value = fLat;
            document.getElementById('profile_lng').value = fLng;

            const badge = document.getElementById('profile-coords-badge');
            const alertBox = document.getElementById('profile-geofence-alert');
            const saveBtn = document.getElementById('btn-save-profile-address');
            const inside = isInsideMojokertoProfile(numLat, numLng);

            if (inside) {
                if (badge) {
                    badge.className = 'text-[10px] font-mono bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md';
                    badge.textContent = `${fLat}, ${fLng}`;
                }
                if (alertBox) alertBox.classList.add('hidden');
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            } else {
                if (badge) {
                    badge.className = 'text-[10px] font-mono bg-rose-100 text-rose-700 font-bold px-2 py-0.5 rounded-md border border-rose-300';
                    badge.textContent = `${fLat}, ${fLng} (Luar Mojokerto)`;
                }
                if (alertBox) alertBox.classList.remove('hidden');
                if (saveBtn) {
                    saveBtn.disabled = true;
                    saveBtn.classList.add('opacity-50', 'cursor-not-allowed');
                }
            }

            if (shouldReverseGeocode) {
                fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${fLat}&lon=${fLng}&accept-language=id`)
                    .then(r => r.json())
                    .then(data => {
                        if (data && data.display_name) {
                            const addr = data.address || {};
                            const parts = [addr.road, addr.suburb || addr.village, addr.city_district || addr.district, addr.city || addr.county].filter(Boolean);
                            document.getElementById('profile_alamat').value = parts.length >= 2 ? parts.join(', ') : data.display_name;
                        }
                    })
                    .catch(() => {});
            }
        }

        function searchProfileAddressOnMap() {
            const query = (document.getElementById('profile_alamat').value || '').trim();
            if (!query) return;

            const searchText = query.toLowerCase().includes('mojokerto') ? query : `${query}, Kota Mojokerto, Jawa Timur`;
            fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(searchText)}&accept-language=id`)
                .then(r => r.json())
                .then(results => {
                    if (results && results.length > 0) {
                        const lat = parseFloat(results[0].lat);
                        const lng = parseFloat(results[0].lon);
                        if (profileMap && profileMarker) {
                            profileMap.setView([lat, lng], 16);
                            profileMarker.setLatLng([lat, lng]);
                            updateProfileCoords(lat, lng, false);
                        }
                    }
                })
                .catch(() => {});
        }

        function useCurrentGpsForProfile() {
            const btnText = document.getElementById('btn-profile-gps-text');
            if (!navigator.geolocation) {
                const alertBox = document.getElementById('profile-geofence-alert');
                const alertText = document.getElementById('profile-geofence-alert-text');
                if (alertBox && alertText) {
                    alertText.textContent = 'Browser Anda tidak mendukung fitur GPS Geolocation. Silakan klik manual pada peta.';
                    alertBox.classList.remove('hidden');
                }
                return;
            }
            if (btnText) btnText.textContent = 'Mencari...';
            navigator.geolocation.getCurrentPosition(
                function (pos) {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    if (profileMap && profileMarker) {
                        profileMap.setView([lat, lng], 16);
                        profileMarker.setLatLng([lat, lng]);
                        updateProfileCoords(lat, lng, true);
                    }
                    if (btnText) btnText.textContent = 'Lokasi Saya';
                },
                function () {
                    if (btnText) btnText.textContent = 'Lokasi Saya';
                    const alertBox = document.getElementById('profile-geofence-alert');
                    const alertText = document.getElementById('profile-geofence-alert-text');
                    if (alertBox && alertText) {
                        alertText.textContent = 'Gagal membaca lokasi GPS. Silakan izinkan akses lokasi atau geser pin manual di peta.';
                        alertBox.classList.remove('hidden');
                    }
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        }

        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('form-edit-address');
            if (form) {
                form.addEventListener('submit', function (e) {
                    const lat = parseFloat(document.getElementById('profile_lat').value);
                    const lng = parseFloat(document.getElementById('profile_lng').value);
                    if (!isInsideMojokertoProfile(lat, lng)) {
                        e.preventDefault();
                        const alertBox = document.getElementById('profile-geofence-alert');
                        if (alertBox) alertBox.classList.remove('hidden');
                        return false;
                    }
                });
            }

            @if($errors->has('alamat') || $errors->has('lat') || $errors->has('lng'))
                openEditAddressModal();
            @endif
        });
    </script>
@endpush
