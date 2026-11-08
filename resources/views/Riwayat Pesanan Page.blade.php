@extends('layouts.pelanggan')

@section('title', 'Pesanan Saya - Kopi Tiga Titik')
@section('active_menu', 'pesanan-saya')
@section('page_title', 'Pesanan Saya')
@section('role_badge', 'PELANGGAN')
@section('page_subtitle', 'Pantau status pesanan kopi botolmu secara langsung dan lihat arsip riwayat transaksi')

@section('header_actions')
    <a href="{{ url('/katalog') }}" class="px-3.5 py-1.5 bg-blue-900 hover:bg-blue-800 text-white rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-colors shadow-xs">
        <span class="material-symbols-outlined text-base">storefront</span>
        <span class="hidden sm:inline">Pesan Lagi</span>
    </a>
@endsection

@section('content')

    <!-- Banner Info / Status Lapak -->
    <div class="p-3.5 md:p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div class="flex items-center gap-2.5">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="text-xs md:text-sm font-bold text-slate-800">Lapak Aktif: Jl. Kuwung, Mergelo, Kota Mojokerto</span>
            <span class="px-2 py-0.5 bg-slate-100 rounded-full border border-slate-200/60 text-[11px] font-medium text-slate-600 hidden sm:inline-block">
                ⏰ 15.00 - 18.00 WIB
            </span>
        </div>
        <div class="text-[11px] text-slate-500 flex items-center gap-1.5">
            <span class="material-symbols-outlined text-base text-blue-900">sync</span>
            <span>Data riwayat otomatis tersimpan</span>
        </div>
    </div>

    <!-- Tab Navigasi: Sedang Berjalan vs Riwayat Selesai -->
    <div class="flex items-center gap-2 p-1 bg-white rounded-2xl border border-slate-200/80 shadow-xs max-w-md w-full">
        <button type="button" onclick="switchOrderTab('berjalan')" id="btn-tab-berjalan"
            class="flex-1 py-2 px-3 rounded-xl text-xs font-bold flex items-center justify-center gap-2 transition-all cursor-pointer bg-blue-900 text-white shadow-xs">
            <span class="material-symbols-outlined text-base">moped</span>
            <span>Sedang Berjalan</span>
            <span id="badge-count-berjalan" class="px-1.5 py-0.2 text-[10px] rounded-full font-extrabold {{ $activeOrders->count() > 0 ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-600' }}">
                {{ $activeOrders->count() }}
            </span>
        </button>

        <button type="button" onclick="switchOrderTab('selesai')" id="btn-tab-selesai"
            class="flex-1 py-2 px-3 rounded-xl text-xs font-semibold flex items-center justify-center gap-2 transition-all cursor-pointer text-slate-600 hover:text-slate-900 hover:bg-slate-50">
            <span class="material-symbols-outlined text-base">check_circle</span>
            <span>Riwayat Selesai</span>
            <span id="badge-count-selesai" class="px-1.5 py-0.2 text-[10px] rounded-full font-extrabold bg-slate-100 text-slate-600">
                {{ $completedOrders->count() }}
            </span>
        </button>
    </div>

    <!-- ========================================================================= -->
    <!-- PANEL 1: PESANAN SEDANG BERJALAN (Menunggu Pembayaran, Diproses, Dikirim) -->
    <!-- ========================================================================= -->
    <div id="panel-berjalan" class="flex flex-col gap-4">
        @if($activeOrders->isEmpty())
            <!-- Empty State Pesanan Berjalan -->
            <div class="p-8 md:p-14 bg-white rounded-3xl shadow-xs border border-slate-200/80 flex flex-col items-center justify-center text-center gap-3.5">
                <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-900 flex items-center justify-center shadow-inner">
                    <span class="material-symbols-outlined text-3xl">local_shipping</span>
                </div>
                <div class="max-w-sm">
                    <h3 class="text-base font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Tidak Ada Pesanan yang Sedang Berjalan</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Saat ini kamu tidak memiliki pesanan yang sedang diproses atau diantar oleh kurir.
                    </p>
                </div>
                <a href="{{ url('/katalog') }}" class="mt-2 px-5 py-2.5 bg-blue-900 hover:bg-blue-800 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 transition-colors shadow-xs">
                    <span class="material-symbols-outlined text-base">storefront</span>
                    <span>Pesan Kopi Sekarang</span>
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                @foreach($activeOrders as $order)
                    <div class="p-4 md:p-5 bg-white rounded-3xl shadow-xs border-2 border-blue-900/40 hover:border-blue-900 transition-all flex flex-col justify-between gap-3.5 group">
                        <!-- Header Kartu: Kode & Status -->
                        <div class="flex items-start justify-between gap-2 pb-3 border-b border-slate-100">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-extrabold text-blue-900 font-['Plus_Jakarta_Sans'] text-sm tracking-tight">
                                        {{ $order->kode }}
                                    </span>
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-[10px] font-bold rounded-full">
                                        {{ $order->total_botol }} Botol
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[13px]">calendar_today</span>
                                    <span>{{ $order->tanggal_formatted }}</span>
                                </p>
                            </div>

                            <!-- Badge Status -->
                            <div class="px-2.5 py-1 rounded-full border text-[11px] font-extrabold flex items-center gap-1.5 shrink-0 {{ $order->status_badge }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $order->status_dot }}"></span>
                                <span>{{ $order->status_label }}</span>
                            </div>
                        </div>

                        <!-- Daftar Item Pesanan -->
                        <div class="p-3 bg-slate-50/80 rounded-2xl border border-slate-200/70 flex flex-col gap-2.5">
                            @foreach($order->orderItems as $item)
                                <div class="flex items-center justify-between text-xs gap-3">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-9 h-11 bg-white rounded-lg border border-slate-200/70 p-0.5 shrink-0 flex items-center justify-center">
                                            @php
                                                $imgSrc = $item->product && $item->product->foto_path
                                                    ? asset('storage/' . $item->product->foto_path)
                                                    : asset('products/' . $item->nama_produk . '.png');
                                            @endphp
                                            <img src="{{ $imgSrc }}" alt="{{ $item->nama_produk }}" class="w-full h-full object-contain" onerror="this.src='https://placehold.co/80x100/1e3a8a/ffffff?text=KTT'" />
                                        </div>
                                        <div class="min-w-0 truncate">
                                            <span class="font-bold text-slate-900 truncate block">{{ $item->nama_produk }}</span>
                                            <span class="text-[11px] text-slate-500 font-medium">{{ $item->jumlah }} x Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                    <span class="font-bold text-slate-900 shrink-0">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                </div>
                            @endforeach

                            <!-- Info Pengantaran & Kurir -->
                            <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between text-[11px] text-slate-600">
                                <div class="flex items-center gap-1 truncate max-w-[70%]">
                                    <span class="material-symbols-outlined text-blue-900 text-sm shrink-0">location_on</span>
                                    <span class="truncate">{{ $order->alamat ?? 'Diantar ke lokasi tujuan' }}</span>
                                </div>
                                <span class="text-blue-900 font-bold shrink-0">
                                    {{ $order->courier ? 'Kurir: ' . $order->courier->name : 'Kurir Toko' }}
                                </span>
                            </div>
                        </div>

                        <!-- Footer: Total & Tombol Aksi -->
                        <div class="flex items-center justify-between pt-1">
                            <div>
                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Total Tagihan</span>
                                <span class="text-base font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">{{ $order->total_formatted }}</span>
                            </div>

                            <a href="{{ route('tracking.detail', $order->kode) }}" class="px-4 py-2.5 bg-blue-900 hover:bg-blue-800 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 transition-colors shadow-xs">
                                <span>Lacak Status Live</span>
                                <span class="material-symbols-outlined text-base">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- ========================================================================= -->
    <!-- PANEL 2: RIWAYAT PESANAN SELESAI & DIBATALKAN                              -->
    <!-- ========================================================================= -->
    <div id="panel-selesai" class="flex flex-col gap-4 hidden">
        @if($completedOrders->isEmpty())
            <!-- Empty State Riwayat Selesai -->
            <div class="p-8 md:p-14 bg-white rounded-3xl shadow-xs border border-slate-200/80 flex flex-col items-center justify-center text-center gap-3.5">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-500 flex items-center justify-center shadow-inner">
                    <span class="material-symbols-outlined text-3xl">receipt_long</span>
                </div>
                <div class="max-w-sm">
                    <h3 class="text-base font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Belum Ada Riwayat Selesai</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Seluruh pesanan yang telah tuntas diterima atau dibatalkan akan tersimpan rapi di tab ini.
                    </p>
                </div>
                <a href="{{ url('/katalog') }}" class="mt-2 px-5 py-2.5 bg-blue-900 hover:bg-blue-800 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 transition-colors shadow-xs">
                    <span class="material-symbols-outlined text-base">storefront</span>
                    <span>Mulai Belanja</span>
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                @foreach($completedOrders as $order)
                    <div class="p-4 md:p-5 bg-white rounded-3xl shadow-xs border border-slate-200/80 hover:border-slate-300 transition-all flex flex-col justify-between gap-3.5">
                        <!-- Header Kartu: Kode & Status -->
                        <div class="flex items-start justify-between gap-2 pb-3 border-b border-slate-100">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900 font-['Plus_Jakarta_Sans'] text-sm tracking-tight">
                                        {{ $order->kode }}
                                    </span>
                                    <span class="px-2 py-0.2 bg-slate-100 text-slate-600 text-[10px] font-semibold rounded-full">
                                        {{ $order->total_botol }} Botol
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-400 mt-0.5">
                                    {{ $order->tanggal_formatted }}
                                </p>
                            </div>

                            <!-- Badge Status -->
                            <div class="px-2.5 py-1 rounded-full border text-[11px] font-bold flex items-center gap-1.5 shrink-0 {{ $order->status_badge }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $order->status_dot }}"></span>
                                <span>{{ $order->status_label }}</span>
                            </div>
                        </div>

                        <!-- Ringkasan Produk -->
                        <div class="p-3 bg-slate-50/70 rounded-2xl border border-slate-200/60 flex flex-col gap-2">
                            @foreach($order->orderItems as $item)
                                <div class="flex items-center justify-between text-xs gap-3">
                                    <span class="text-slate-800 font-medium truncate">{{ $item->jumlah }}x {{ $item->nama_produk }}</span>
                                    <span class="font-bold text-slate-700 shrink-0">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                            <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between text-xs font-bold text-slate-900">
                                <span>Total Pembayaran</span>
                                <span class="text-blue-900 font-extrabold">{{ $order->total_formatted }}</span>
                            </div>
                        </div>

                        <!-- Footer: Aksi -->
                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <a href="{{ route('tracking.detail', $order->kode) }}" class="py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors">
                                <span class="material-symbols-outlined text-sm">visibility</span>
                                <span>Detail Pesanan</span>
                            </a>
                            <a href="{{ url('/katalog') }}" class="py-2.5 bg-white hover:bg-blue-50 border border-blue-900 text-blue-900 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-colors shadow-2xs">
                                <span class="material-symbols-outlined text-sm">replay</span>
                                <span>Pesan Lagi</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

@endsection

@push('scripts')
<script>
    function switchOrderTab(tab) {
        const btnBerjalan = document.getElementById('btn-tab-berjalan');
        const btnSelesai = document.getElementById('btn-tab-selesai');
        const panelBerjalan = document.getElementById('panel-berjalan');
        const panelSelesai = document.getElementById('panel-selesai');
        const badgeBerjalan = document.getElementById('badge-count-berjalan');
        const badgeSelesai = document.getElementById('badge-count-selesai');

        if (tab === 'berjalan') {
            panelBerjalan.classList.remove('hidden');
            panelSelesai.classList.add('hidden');

            btnBerjalan.className = 'flex-1 py-2 px-3 rounded-xl text-xs font-bold flex items-center justify-center gap-2 transition-all cursor-pointer bg-blue-900 text-white shadow-xs';
            btnSelesai.className = 'flex-1 py-2 px-3 rounded-xl text-xs font-semibold flex items-center justify-center gap-2 transition-all cursor-pointer text-slate-600 hover:text-slate-900 hover:bg-slate-50';

            badgeBerjalan.className = 'px-1.5 py-0.2 text-[10px] rounded-full font-extrabold bg-white/20 text-white';
            badgeSelesai.className = 'px-1.5 py-0.2 text-[10px] rounded-full font-extrabold bg-slate-100 text-slate-600';
        } else {
            panelSelesai.classList.remove('hidden');
            panelBerjalan.classList.add('hidden');

            btnSelesai.className = 'flex-1 py-2 px-3 rounded-xl text-xs font-bold flex items-center justify-center gap-2 transition-all cursor-pointer bg-blue-900 text-white shadow-xs';
            btnBerjalan.className = 'flex-1 py-2 px-3 rounded-xl text-xs font-semibold flex items-center justify-center gap-2 transition-all cursor-pointer text-slate-600 hover:text-slate-900 hover:bg-slate-50';

            badgeSelesai.className = 'px-1.5 py-0.2 text-[10px] rounded-full font-extrabold bg-white/20 text-white';
            badgeBerjalan.className = 'px-1.5 py-0.2 text-[10px] rounded-full font-extrabold bg-slate-100 text-slate-600';
        }
    }

    // Periksa query parameter tab saat halaman dimuat
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tab') === 'selesai') {
            switchOrderTab('selesai');
        }
    });
</script>
@endpush
