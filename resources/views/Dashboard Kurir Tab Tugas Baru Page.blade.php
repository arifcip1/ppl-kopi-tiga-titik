@extends('layouts.kurir')

@section('title', 'Tugas Baru Pengantaran')
@section('active_menu', 'dashboard')
@section('page_title', 'Tugas Baru Pengantaran')
@section('role_badge', 'KURIR')
@section('page_subtitle', 'Kelola pesanan pelanggan yang harus kamu antarkan hari ini')

@section('content')

    <!-- Flash Message Feedback Standar (PPL-24) -->
    <x-flash-message class="mb-2" />

    <!-- ============================================================== -->
    <!-- 1. TABS NAVIGASI UTAMA KURIR (TUGAS BARU VS SELESAI)           -->
    <!-- ============================================================== -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
        <a href="{{ route('kurir.tugas-baru') }}" class="px-4 py-2 bg-blue-900 text-white rounded-xl text-xs font-bold shadow-xs flex items-center gap-2">
            <span class="material-symbols-outlined text-base">moped</span>
            <span>Tugas Baru</span>
            <span class="px-1.5 py-0.2 bg-white/20 text-white rounded-full text-[10px]">
                {{ $stats['active_tasks'] ?? 0 }}
            </span>
        </a>

        <a href="{{ route('kurir.selesai') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold flex items-center gap-2 transition-colors">
            <span class="material-symbols-outlined text-base text-slate-500">task_alt</span>
            <span>Riwayat Selesai</span>
            <span class="px-1.5 py-0.2 bg-slate-200 text-slate-700 rounded-full text-[10px]">
                {{ $stats['completed_today'] ?? 0 }}
            </span>
        </a>
    </div>

    <!-- ============================================================== -->
    <!-- 2. RINGKASAN PERFORMA KURIR HARI INI                           -->
    <!-- ============================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 md:gap-4">
        <!-- Tugas Aktif -->
        <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-900 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-xl">local_shipping</span>
            </div>
            <div>
                <span class="text-[11px] text-slate-500 font-medium">Tugas Siap Diantar</span>
                <div class="text-lg md:text-xl font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">
                    {{ $stats['active_tasks'] ?? 0 }} Pesanan
                </div>
            </div>
        </div>

        <!-- Selesai Hari Ini -->
        <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-xl">check_circle</span>
            </div>
            <div>
                <span class="text-[11px] text-slate-500 font-medium">Selesai Hari Ini</span>
                <div class="text-lg md:text-xl font-extrabold text-emerald-800 font-['Plus_Jakarta_Sans']">
                    {{ $stats['completed_today'] ?? 0 }} Pesanan
                </div>
            </div>
        </div>

        <!-- Total Botol Diantar -->
        <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-xl">sports_bar</span>
            </div>
            <div>
                <span class="text-[11px] text-slate-500 font-medium">Total Botol Berhasil</span>
                <div class="text-lg md:text-xl font-extrabold text-slate-900 font-['Plus_Jakarta_Sans']">
                    {{ $stats['total_bottles_delivered'] ?? 0 }} Botol
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- 3. DAFTAR TUGAS PENGANTARAN (ISOLASI DATA KURIR FR-8.9)        -->
    <!-- ============================================================== -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-blue-900 text-lg">delivery_dining</span>
            <h3 class="font-extrabold text-slate-900 text-sm md:text-base font-['Plus_Jakarta_Sans']">Daftar Antaran Aktif Kamu</h3>
        </div>
        <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-xl">
            {{ count($tasks ?? []) }} Tugas Menunggu
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @forelse($tasks as $tugas)
            @php
                $customerName = $tugas->nama_penerima ?? $tugas->nama_pelanggan ?? optional($tugas->user)->name ?? 'Pelanggan';
                $customerPhone = $tugas->telepon ?? optional($tugas->user)->no_telepon ?? '-';
            @endphp
            <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/90 flex flex-col justify-between gap-3 hover:border-blue-300 transition-colors">
                <!-- Header Kartu Tugas -->
                <div>
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                        <div class="flex items-center gap-2">
                            <span class="font-extrabold text-slate-900 text-xs md:text-sm font-['Plus_Jakarta_Sans']">#{{ $tugas->kode }}</span>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-indigo-50 text-indigo-800 border border-indigo-200">
                                DIKIRIM
                            </span>
                        </div>
                        <span class="text-[10px] text-slate-400 font-medium">
                            Ditugaskan: {{ $tugas->created_at ? $tugas->created_at->format('H:i') : '-' }} WIB
                        </span>
                    </div>

                    <!-- Informasi Penerima & Alamat Lengkap -->
                    <div class="mt-3 flex flex-col gap-1.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-slate-400 text-base">person</span>
                                <span class="text-xs font-bold text-slate-900">{{ $customerName }}</span>
                            </div>
                            <span class="text-[11px] font-semibold text-slate-600">
                                {{ $customerPhone }}
                            </span>
                        </div>

                        <!-- Alamat Detail -->
                        <div class="p-2.5 bg-slate-50 rounded-xl text-xs text-slate-700 flex items-start gap-2 mt-1">
                            <span class="material-symbols-outlined text-rose-500 text-base shrink-0 mt-0.5">place</span>
                            <div class="flex-1">
                                <span class="font-bold text-slate-900 block">Alamat Tujuan:</span>
                                <p class="text-[11px] text-slate-600 mt-0.5 leading-relaxed">{{ $tugas->alamat }}</p>
                            </div>
                        </div>

                        <!-- Catatan Pengiriman jika ada -->
                        @if($tugas->catatan)
                            <div class="p-2 bg-amber-50 rounded-xl text-[11px] text-amber-800 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-sm shrink-0">edit_note</span>
                                <span><strong>Catatan:</strong> {{ $tugas->catatan }}</span>
                            </div>
                        @endif

                        <!-- Tombol Cepat WhatsApp & Google Maps -->
                        <div class="grid grid-cols-2 gap-2 mt-1">
                            <a href="https://wa.me/{{ $tugas->whatsapp_number }}" target="_blank" rel="noopener" class="py-2 px-3 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                                <span class="material-symbols-outlined text-sm text-emerald-600">chat</span>
                                <span>Hubungi WhatsApp</span>
                            </a>
                            <a href="{{ $tugas->map_url }}" target="_blank" rel="noopener" class="py-2 px-3 bg-blue-50 hover:bg-blue-100 text-blue-900 border border-blue-200 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                                <span class="material-symbols-outlined text-sm text-blue-800">directions</span>
                                <span>Buka Peta Rute</span>
                            </a>
                        </div>
                    </div>

                    <!-- Botol Kopi Yang Harus Diantar -->
                    <div class="mt-3 pt-2.5 border-t border-slate-100">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Botol Yang Dibawa:</span>
                        <div class="flex flex-col gap-1">
                            @foreach($tugas->orderItems as $item)
                                <div class="flex items-center justify-between text-xs bg-slate-50 px-2.5 py-1.5 rounded-lg">
                                    <span class="text-slate-800">
                                        <strong class="text-blue-900">{{ $item->jumlah }}x</strong> {{ $item->nama_produk }}
                                    </span>
                                    <span class="text-[10px] font-semibold text-slate-500">250ml</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Footer Kartu: Konfirmasi Selesai -->
                <div class="pt-3 border-t border-slate-100">
                    <form action="{{ route('kurir.orders.complete', $tugas->id) }}" method="POST">
                        @csrf
                        <button type="submit" onclick="return confirm('Konfirmasi bahwa pesanan #{{ $tugas->kode }} telah diterima dengan baik oleh pembeli?')" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-all shadow-xs cursor-pointer active:scale-98">
                            <span class="material-symbols-outlined text-base">check_circle</span>
                            <span>Tandai Selesai (Diterima Pembeli)</span>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 bg-white rounded-2xl border border-slate-200/90 text-center flex flex-col items-center">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-2">
                    <span class="material-symbols-outlined text-2xl">moped</span>
                </div>
                <h4 class="font-bold text-slate-800 text-sm">Tidak ada tugas baru</h4>
                <p class="text-xs text-slate-500 mt-0.5">Semua antaran telah diselesaikan atau belum ada pesanan yang ditugaskan oleh Owner.</p>
            </div>
        @endforelse
    </div>

@endsection
