@extends('layouts.pelanggan')

@section('title', 'My Account')
@section('active_menu', 'profil')
@section('page_title', 'My Account')
@section('role_badge', 'PELANGGAN')
@section('page_subtitle', 'Kelola informasi profil, alamat pengiriman, dan riwayat pesananmu')

@section('content')

    @if (session('success'))
        <div class="p-3.5 bg-emerald-50 text-emerald-800 text-xs font-semibold rounded-2xl border border-emerald-200 flex items-center gap-2">
            <span class="material-symbols-outlined text-base text-emerald-600">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 md:gap-6 items-start">

        <!-- Kolom Kiri: Profil Singkat & Aktivitas -->
        <div class="lg:col-span-2 flex flex-col gap-4">

            <!-- Identitas User Card -->
            <div class="p-5 bg-white rounded-2xl border border-slate-200/90 shadow-xs flex flex-col gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-blue-900 text-white text-2xl font-extrabold flex items-center justify-center font-['Plus_Jakarta_Sans'] shadow-md shrink-0">
                        N
                    </div>
                    <div class="min-w-0 flex flex-col">
                        <h2 class="text-base font-bold text-slate-900 font-['Plus_Jakarta_Sans'] leading-tight truncate">Nabil</h2>
                        <span class="inline-block mt-1 px-2.5 py-0.5 bg-emerald-50 text-emerald-700 font-semibold text-[11px] rounded-full border border-emerald-200 self-start">
                            +62 812-3849-9210
                        </span>
                        <div class="flex items-center gap-1.5 text-slate-500 text-xs mt-1.5">
                            <span class="material-symbols-outlined text-slate-400 text-sm">mail</span>
                            <span class="truncate">nabil@gmail.com</span>
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
                                <div class="text-[10px] text-slate-500">12 Botol Kopi telah dinikmati</div>
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
                            <p class="text-[10px] text-slate-500">Alamat tujuan untuk kurir Kopi Tiga Titik</p>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 bg-blue-100 text-blue-900 text-[10px] font-bold rounded-md">UTAMA</span>
                </div>

                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/80 flex flex-col gap-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Rumah / Kost</span>
                        <span class="text-[11px] text-slate-500 font-medium">Nabil (+62 812-3849-9210)</span>
                    </div>
                    <p class="text-xs text-slate-700 leading-relaxed">
                        Jl. Hayam Wuruk No. 42, Magersari, Kota Mojokerto, Jawa Timur 61318
                    </p>
                    <div class="pt-1 flex items-center gap-1.5 text-[11px] text-slate-500">
                        <span class="material-symbols-outlined text-xs text-slate-400">near_me</span>
                        <span>Patokan: Pagar hitam depan apotek, seberang minimarket</span>
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <button type="button" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer">
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

@endsection
