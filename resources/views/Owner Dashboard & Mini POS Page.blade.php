@extends('layouts.owner')

@section('title', 'Owner Dashboard')
@section('active_menu', 'dashboard')
@section('page_title', 'Owner Dashboard')
@section('role_badge', 'OWNER')
@section('page_subtitle', 'Pantau ringkasan operasional harian, kelola antrean pesanan, dan penugasan kurir')

@section('header_actions')
    <!-- Indikator Status Lapak Buka -->
    <div class="flex items-center gap-2">
        <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold shadow-2xs">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Lapak Buka</span>
        </div>

        <!-- Dropdown Lonceng Notifikasi Web (FR-14) -->
        <div class="relative" id="webNotificationWrapper">
            <button type="button" onclick="toggleWebNotifications()" class="w-9 h-9 rounded-xl bg-slate-50 hover:bg-blue-50 border border-slate-200 text-slate-700 hover:text-blue-900 flex items-center justify-center transition-all cursor-pointer relative shadow-2xs" title="Notifikasi Web">
                <span class="material-symbols-outlined text-xl">notifications</span>
                @if(isset($notifications) && count($notifications) > 0)
                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-rose-500 text-white text-[10px] font-extrabold rounded-full flex items-center justify-center shadow-xs animate-bounce">
                        {{ count($notifications) }}
                    </span>
                @endif
            </button>

            <!-- Menu Dropdown Notifikasi -->
            <div id="webNotificationDropdown" class="hidden absolute right-0 mt-2 w-80 md:w-96 bg-white rounded-2xl shadow-xl border border-slate-200/90 z-50 p-3 flex flex-col gap-2">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="font-bold text-xs text-slate-900 font-['Plus_Jakarta_Sans'] flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-blue-900 text-base">notifications_active</span>
                        Notifikasi Web Hari Ini
                    </span>
                    <span class="text-[10px] text-slate-500 font-semibold">{{ isset($notifications) ? count($notifications) : 0 }} Perhatian</span>
                </div>

                <div class="max-h-72 overflow-y-auto flex flex-col gap-2 divide-y divide-slate-100">
                    @if(isset($notifications) && count($notifications) > 0)
                        @foreach($notifications as $notif)
                            <div class="pt-2 first:pt-0 flex items-start gap-2.5">
                                <div class="w-7 h-7 rounded-lg {{ $notif['tipe'] === 'kurir' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700' }} flex items-center justify-center shrink-0 mt-0.5">
                                    <span class="material-symbols-outlined text-base">
                                        {{ $notif['tipe'] === 'kurir' ? 'local_shipping' : 'inventory' }}
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-slate-800 leading-tight">{{ $notif['judul'] }}</p>
                                    <p class="text-[11px] text-slate-500 mt-0.5">{{ $notif['pesan'] }}</p>
                                    <span class="text-[10px] text-slate-400 mt-1 block">{{ $notif['waktu'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="py-6 text-center text-xs text-slate-400">
                            Tidak ada notifikasi mendesak saat ini.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')

    <!-- Notifikasi Feedback Sukses / Error Validasi Standar (PPL-24) -->
    <x-flash-message class="mb-2" />

    <!-- ============================================================== -->
    <!-- 1. RINGKASAN HARI INI (OPERATIONAL METRICS - FR-15 / BR-16)    -->
    <!-- ============================================================== -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-sm md:text-base font-extrabold text-slate-900 font-['Plus_Jakarta_Sans'] tracking-tight">RINGKASAN HARI INI</h2>
            <p class="text-[11px] text-slate-500">Pantau performa omset lapak dan kesiapan inventaris harian</p>
        </div>
        <div class="text-[11px] font-semibold text-slate-500 bg-white px-3 py-1.5 rounded-xl border border-slate-200/80 shadow-2xs">
            {{ now()->translatedFormat('l, d F Y') }}
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
        <!-- 1. Omset Penjualan Hari Ini (Tanpa Ongkir Sesuai FR-15 / BR-16) -->
        <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] text-slate-500 font-medium">Omset Penjualan</span>
                <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 text-[10px] font-bold">Hari Ini</span>
            </div>
            <div class="mt-2">
                <div class="text-lg md:text-xl font-extrabold text-slate-900 font-['Plus_Jakarta_Sans']">
                    Rp {{ number_format($todayRevenue ?? 0, 0, ',', '.') }}
                </div>
                <p class="text-[10px] text-slate-400 mt-0.5 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[13px] text-slate-400">info</span>
                    <span>Bersih tanpa ongkir</span>
                </p>
            </div>
        </div>

        <!-- 2. Pesanan Aktif -->
        <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] text-slate-500 font-medium">Pesanan Aktif</span>
                <div class="w-6 h-6 rounded-lg bg-blue-50 text-blue-900 flex items-center justify-center">
                    <span class="material-symbols-outlined text-sm">pending_actions</span>
                </div>
            </div>
            <div class="mt-2">
                <div class="text-lg md:text-xl font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">
                    {{ $pendingOrdersCount ?? 0 }} Pesanan
                </div>
                <p class="text-[10px] text-slate-400 mt-0.5">Menunggu / Diproses</p>
            </div>
        </div>

        <!-- 3. Total Stok Fisik Aktif -->
        <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] text-slate-500 font-medium">Total Stok Aktif</span>
                <div class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center">
                    <span class="material-symbols-outlined text-sm">kitchen</span>
                </div>
            </div>
            <div class="mt-2">
                <div class="text-lg md:text-xl font-extrabold text-slate-900 font-['Plus_Jakarta_Sans']">
                    {{ $activeStock ?? 0 }} Botol
                </div>
                <p class="text-[10px] text-slate-400 mt-0.5">di Seluruh Varian</p>
            </div>
        </div>

        <!-- 4. Peringatan Restok (Stok <= 5 BR-10 & BR-19) -->
        <div class="p-4 bg-white rounded-2xl shadow-xs border {{ ($lowStockCount ?? 0) > 0 ? 'border-rose-200 bg-rose-50/40' : 'border-slate-200/80' }} flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] {{ ($lowStockCount ?? 0) > 0 ? 'text-rose-700 font-semibold' : 'text-slate-500' }} font-medium">
                    Perlu Restok (&le;5)
                </span>
                <div class="w-6 h-6 rounded-lg {{ ($lowStockCount ?? 0) > 0 ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center">
                    <span class="material-symbols-outlined text-sm">warning</span>
                </div>
            </div>
            <div class="mt-2">
                <div class="text-lg md:text-xl font-extrabold {{ ($lowStockCount ?? 0) > 0 ? 'text-rose-800' : 'text-slate-900' }} font-['Plus_Jakarta_Sans']">
                    {{ ($lowStockCount ?? 0) > 0 ? $lowStockCount . ' Menu Menipis' : 'Stok Aman' }}
                </div>
                <p class="text-[10px] {{ ($lowStockCount ?? 0) > 0 ? 'text-rose-600 font-medium' : 'text-slate-400' }} mt-0.5">
                    {{ ($lowStockCount ?? 0) > 0 ? 'Segera isi ulang cooler box' : 'Tidak ada stok kritis' }}
                </p>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- 2. INFORMASI TITIK LAPAK HARI INI                              -->
    <!-- ============================================================== -->
    <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-900 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-xl">location_on</span>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="font-bold text-slate-900 text-xs md:text-sm font-['Plus_Jakarta_Sans']">Titik Lapak Hari Ini</h3>
                    <span class="px-2 py-0.2 bg-blue-100 text-blue-900 text-[9px] font-extrabold rounded">15.00 - 18.00 WIB</span>
                </div>
                <p class="text-xs font-semibold text-slate-800 mt-1 flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm text-slate-400">storefront</span>
                    <span>{{ $activeLocation->nama_lokasi ?? 'Jl. Benteng Pancasila (Depan Taman Sentanan)' }}</span>
                </p>
                <p class="text-[11px] text-slate-500 mt-0.5">
                    {{ $activeLocation->alamat_lengkap ?? 'Samping booth es krim • Parkir motor timur, Mojokerto' }}
                </p>
            </div>
        </div>

        <a href="{{ url('/owner/update-lokasi') }}" class="px-4 py-2 bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer shrink-0">
            <span class="material-symbols-outlined text-sm">edit_location</span>
            <span>Perbarui Lokasi Lapak</span>
        </a>
    </div>

    <!-- ============================================================== -->
    <!-- 3. TOOLBAR PENCARIAN & FILTER STATUS PESANAN (F13 & FR-8)      -->
    <!-- ============================================================== -->
    <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
        <!-- Pencarian Kode / Nama / Telepon -->
        <form method="GET" action="{{ route('owner.dashboard') }}" class="relative flex-1 max-w-md">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode pesanan / nama penerima / telepon..." class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder:text-slate-400 focus:outline-none focus:border-blue-900 transition-colors" />
        </form>

        <!-- Horizontal Filter Status Tabs with Badge Counters -->
        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-0.5">
            @php
                $currentStatus = request('status', 'semua');
                $statuses = [
                    'semua' => ['label' => 'Semua', 'count' => $statusCounts['semua'] ?? 0],
                    'menunggu_pembayaran' => ['label' => 'Menunggu Pembayaran', 'count' => $statusCounts['menunggu_pembayaran'] ?? 0],
                    'diproses' => ['label' => 'Diproses', 'count' => $statusCounts['diproses'] ?? 0],
                    'dikirim' => ['label' => 'Dikirim', 'count' => $statusCounts['dikirim'] ?? 0],
                    'selesai' => ['label' => 'Selesai', 'count' => $statusCounts['selesai'] ?? 0],
                    'dibatalkan' => ['label' => 'Dibatalkan', 'count' => $statusCounts['dibatalkan'] ?? 0],
                ];
            @endphp

            @foreach($statuses as $key => $statusData)
                @php
                    $isActive = ($currentStatus === $key);
                    $url = route('owner.dashboard', array_merge(request()->query(), ['status' => $key]));
                @endphp
                <a href="{{ $url }}" class="px-3 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all flex items-center gap-1.5 {{ $isActive ? 'bg-blue-900 text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                    <span>{{ $statusData['label'] }}</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ $isActive ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">
                        {{ $statusData['count'] }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- 4. DAFTAR ANTREAN PESANAN (GRID RESPONSIF DESKTOP & MOBILE)     -->
    <!-- ============================================================== -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-blue-900 text-lg">receipt_long</span>
            <h3 class="font-extrabold text-slate-900 text-sm md:text-base font-['Plus_Jakarta_Sans']">Kelola & Antrean Pesanan</h3>
        </div>
        <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-xl">
            {{ $orders->total() }} Pesanan Ditemukan
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @forelse($orders as $order)
            @php
                $statusColors = [
                    'menunggu_pembayaran' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-800', 'border' => 'border-amber-200', 'label' => 'Menunggu Pembayaran'],
                    'diproses'            => ['bg' => 'bg-blue-50', 'text' => 'text-blue-900', 'border' => 'border-blue-200', 'label' => 'Diproses'],
                    'dikirim'             => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-800', 'border' => 'border-indigo-200', 'label' => 'Dikirim'],
                    'selesai'             => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-800', 'border' => 'border-emerald-200', 'label' => 'Selesai'],
                    'dibatalkan'          => ['bg' => 'bg-rose-50', 'text' => 'text-rose-800', 'border' => 'border-rose-200', 'label' => 'Dibatalkan'],
                ];
                $color = $statusColors[$order->status] ?? ['bg' => 'bg-slate-50', 'text' => 'text-slate-800', 'border' => 'border-slate-200', 'label' => $order->status];
                $customerName = $order->nama_penerima ?? $order->nama_pelanggan ?? optional($order->user)->name ?? 'Pelanggan';
                $customerPhone = $order->telepon ?? optional($order->user)->no_telepon ?? '-';
            @endphp

            <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/90 flex flex-col justify-between gap-3 hover:border-blue-300 transition-colors">
                <!-- Header Kartu -->
                <div>
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                        <div class="flex items-center gap-2">
                            <span class="font-extrabold text-slate-900 text-xs md:text-sm font-['Plus_Jakarta_Sans']">#{{ $order->kode }}</span>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold border {{ $color['bg'] }} {{ $color['text'] }} {{ $color['border'] }}">
                                {{ $color['label'] }}
                            </span>
                        </div>
                        <span class="text-[10px] text-slate-400 font-medium">
                            {{ $order->created_at ? $order->created_at->format('d M, H:i') : '-' }}
                        </span>
                    </div>

                    <!-- Informasi Penerima & Pemenuhan -->
                    <div class="mt-2.5 flex items-start justify-between gap-2">
                        <div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs font-bold text-slate-900">{{ $customerName }}</span>
                                <span class="text-[10px] text-slate-400">• {{ $customerPhone }}</span>
                            </div>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                @if($order->metode_pemenuhan === 'ambil_sendiri')
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.2 bg-purple-50 text-purple-700 text-[10px] font-bold rounded">
                                        <span class="material-symbols-outlined text-[12px]">storefront</span>
                                        Ambil Sendiri di Lapak
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.2 bg-sky-50 text-sky-700 text-[10px] font-bold rounded">
                                        <span class="material-symbols-outlined text-[12px]">local_shipping</span>
                                        Pesan Antar
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Status Bayar -->
                        <div class="text-right">
                            @if(optional($order->payment)->status === 'berhasil')
                                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold rounded-md inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Lunas QRIS
                                </span>
                            @else
                                <span class="px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold rounded-md inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    Menunggu Bayar
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Alamat / Catatan Pengiriman -->
                    @if($order->metode_pemenuhan !== 'ambil_sendiri' && $order->alamat)
                        <div class="mt-2 p-2 bg-slate-50 rounded-xl text-[11px] text-slate-600 flex items-start gap-1.5">
                            <span class="material-symbols-outlined text-slate-400 text-sm mt-0.5 shrink-0">place</span>
                            <span class="line-clamp-2">{{ $order->alamat }}</span>
                        </div>
                    @endif

                    <!-- Rincian Produk Singkat -->
                    <div class="mt-2.5 pt-2.5 border-t border-slate-100 flex flex-col gap-1">
                        @foreach($order->orderItems as $item)
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-700"><strong class="text-slate-900">{{ $item->jumlah }}x</strong> {{ $item->nama_produk }}</span>
                                <span class="text-slate-500 font-medium">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Footer Kartu: Total & Tombol Aksi -->
                <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between gap-2">
                    <div>
                        <span class="text-[10px] text-slate-400 block">Total Tagihan (Termasuk Ongkir)</span>
                        <span class="text-sm font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">
                            Rp {{ number_format($order->total, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <!-- Tombol Detail Pesanan -->
                        <button type="button" onclick="openOrderDetailModal({{ $order->id }})" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-1 shadow-2xs">
                            <span class="material-symbols-outlined text-sm">visibility</span>
                            <span>Detail</span>
                        </button>

                        <!-- Aksi Penugasan Kurir (Untuk Pesan Antar Berstatus Diproses) -->
                        @if($order->status === 'diproses' && $order->metode_pemenuhan !== 'ambil_sendiri')
                            <button type="button" onclick="openAssignModal({{ $order->id }}, '{{ $order->kode }}')" class="px-3 py-1.5 bg-blue-900 hover:bg-blue-800 text-white rounded-xl text-xs font-bold transition-all shadow-xs flex items-center gap-1 cursor-pointer">
                                <span class="material-symbols-outlined text-sm">send</span>
                                <span>Tugaskan Kurir</span>
                            </button>
                        @endif

                        <!-- Aksi Serahkan Pesanan (Untuk Ambil Sendiri Berstatus Diproses) -->
                        @if($order->status === 'diproses' && $order->metode_pemenuhan === 'ambil_sendiri')
                            <form action="{{ route('owner.orders.complete-pickup', $order->id) }}" method="POST">
                                @csrf
                                <button type="submit" onclick="return confirm('Konfirmasi serahkan pesanan ini kepada pembeli di lapak?')" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs flex items-center gap-1 cursor-pointer">
                                    <span class="material-symbols-outlined text-sm">check_circle</span>
                                    <span>Serahkan (Selesai)</span>
                                </button>
                            </form>
                        @endif

                        <!-- Status Kurir Bertugas Jika Sudah Dikirim -->
                        @if($order->status === 'dikirim' && $order->courier)
                            <div class="px-2.5 py-1 bg-indigo-50 border border-indigo-200 rounded-xl text-[11px] font-bold text-indigo-800 flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">motorcycle</span>
                                <span>Kurir: {{ $order->courier->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 bg-white rounded-2xl border border-slate-200/90 text-center flex flex-col items-center">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-2">
                    <span class="material-symbols-outlined text-2xl">inbox</span>
                </div>
                <h4 class="font-bold text-slate-800 text-sm">Tidak ada pesanan ditemukan</h4>
                <p class="text-xs text-slate-500 mt-0.5">Coba ubah filter status atau kata kunci pencarian</p>
            </div>
        @endforelse
    </div>

    <!-- Paginasi -->
    <div class="mt-2">
        {{ $orders->withQueryString()->links() }}
    </div>

    <!-- ============================================================== -->
    <!-- MODAL PENUGASAN KURIR                                          -->
    <!-- ============================================================== -->
    <div id="assign-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden opacity-0 transition-opacity duration-200">
        <div class="bg-white w-full max-w-md rounded-3xl p-6 shadow-2xl border border-slate-100 flex flex-col gap-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-900 flex items-center justify-center">
                        <span class="material-symbols-outlined text-lg">local_shipping</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm font-['Plus_Jakarta_Sans']">Tugaskan Kurir</h3>
                        <p class="text-[11px] text-slate-500" id="assignModalOrderCode">Pesanan: #</p>
                    </div>
                </div>
                <button type="button" onclick="closeAssignModal()" class="w-7 h-7 rounded-lg hover:bg-slate-100 text-slate-400 flex items-center justify-center cursor-pointer">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>

            <form id="assignForm" method="POST" action="">
                @csrf
                <div class="flex flex-col gap-3">
                    <label class="text-xs font-bold text-slate-700">Pilih Kurir Bertugas:</label>
                    <select name="courier_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-blue-900">
                        <option value="">-- Pilih Kurir Aktif --</option>
                        @foreach($couriers as $courier)
                            <option value="{{ $courier->id }}">{{ $courier->name }} ({{ $courier->email }})</option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        Status pesanan akan otomatis berubah menjadi <strong>Dikirim</strong> dan tugas pengantaran akan langsung masuk ke Dashboard Kurir bersangkutan.
                    </p>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-2.5">
                    <button type="button" onclick="closeAssignModal()" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="w-full py-2.5 bg-blue-900 hover:bg-blue-800 text-white rounded-xl text-xs font-bold shadow-xs cursor-pointer">
                        Konfirmasi Kirim
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL DETAIL LENGKAP PESANAN (AJAX BREAKDOWN)                   -->
    <!-- ============================================================== -->
    <div id="detail-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden opacity-0 transition-opacity duration-200">
        <div class="bg-white w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-3xl p-6 shadow-2xl border border-slate-100 flex flex-col gap-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-900 flex items-center justify-center">
                        <span class="material-symbols-outlined text-lg">receipt_long</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm font-['Plus_Jakarta_Sans']">Rincian Lengkap Pesanan</h3>
                        <p class="text-[11px] text-slate-500" id="modalDetailOrderCode">Memuat data...</p>
                    </div>
                </div>
                <button type="button" onclick="closeDetailModal()" class="w-7 h-7 rounded-lg hover:bg-slate-100 text-slate-400 flex items-center justify-center cursor-pointer">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>

            <div id="modalDetailContent" class="flex flex-col gap-4 text-xs">
                <div class="py-8 text-center text-slate-400">
                    <span class="material-symbols-outlined animate-spin text-2xl">sync</span>
                    <p class="mt-1">Mengambil rincian pesanan...</p>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    // Toggle Web Notifications Dropdown
    function toggleWebNotifications() {
        const dropdown = document.getElementById('webNotificationDropdown');
        dropdown.classList.toggle('hidden');
    }

    document.addEventListener('click', function(e) {
        const wrapper = document.getElementById('webNotificationWrapper');
        const dropdown = document.getElementById('webNotificationDropdown');
        if (wrapper && !wrapper.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    // Assign Courier Modal Logic
    function openAssignModal(orderId, orderCode) {
        const modal = document.getElementById('assign-modal');
        const codeEl = document.getElementById('assignModalOrderCode');
        const form = document.getElementById('assignForm');

        codeEl.innerText = 'Pesanan: #' + orderCode;
        form.action = '{{ url("/owner/orders") }}/' + orderId + '/assign-courier';

        modal.classList.remove('hidden');
        setTimeout(() => modal.classList.remove('opacity-0'), 10);
    }

    function closeAssignModal() {
        const modal = document.getElementById('assign-modal');
        modal.classList.add('opacity-0');
        setTimeout(() => modal.classList.add('hidden'), 200);
    }

    // Detail Order Modal AJAX Logic
    function openOrderDetailModal(orderId) {
        const modal = document.getElementById('detail-modal');
        const codeEl = document.getElementById('modalDetailOrderCode');
        const contentEl = document.getElementById('modalDetailContent');

        modal.classList.remove('hidden');
        setTimeout(() => modal.classList.remove('opacity-0'), 10);
        contentEl.innerHTML = '<div class="py-8 text-center text-slate-400"><span class="material-symbols-outlined animate-spin text-2xl">sync</span><p class="mt-1">Mengambil rincian...</p></div>';

        fetch('{{ url("/owner/orders") }}/' + orderId + '/detail')
            .then(res => res.json())
            .then(res => {
                if(!res.success) throw new Error('Gagal memuat');
                const d = res.data;
                codeEl.innerText = 'Kode: #' + d.kode + ' • Waktu: ' + d.created_at;

                let itemsHtml = d.items.map(it => `
                    <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                        <div>
                            <span class="font-bold text-slate-800">${it.jumlah}x</span> ${it.nama_produk}
                            <div class="text-[10px] text-slate-400">@ Rp ${parseInt(it.harga_satuan).toLocaleString('id-ID')}</div>
                        </div>
                        <span class="font-semibold text-slate-700">Rp ${parseInt(it.subtotal).toLocaleString('id-ID')}</span>
                    </div>
                `).join('');

                let timelineHtml = d.history.map(h => `
                    <div class="flex items-start gap-2 text-[11px]">
                        <span class="w-2 h-2 rounded-full bg-blue-900 mt-1 shrink-0"></span>
                        <div class="flex-1">
                            <span class="font-bold text-slate-800 uppercase">${h.status_baru}</span>
                            <span class="text-slate-500"> - ${h.keterangan || 'Diperbarui oleh sistem/user'}</span>
                            <div class="text-[9px] text-slate-400">${h.waktu}</div>
                        </div>
                    </div>
                `).join('');

                contentEl.innerHTML = `
                    <!-- Info Pelanggan & Alamat -->
                    <div class="p-3 bg-slate-50 rounded-2xl flex flex-col gap-1.5">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-slate-900">${d.customer.name}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold ${d.status === 'selesai' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-900'} uppercase">${d.status}</span>
                        </div>
                        <div class="text-[11px] text-slate-600">
                            <strong>Telepon:</strong> ${d.telepon_penerima || d.customer.telepon || '-'}
                        </div>
                        <div class="text-[11px] text-slate-600">
                            <strong>Metode Pemenuhan:</strong> ${d.metode_pemenuhan === 'ambil_sendiri' ? 'Ambil Sendiri di Lapak' : 'Pesan Antar ke Alamat'}
                        </div>
                        ${d.alamat_pengiriman ? `
                            <div class="text-[11px] text-slate-600 mt-1 pt-1 border-t border-slate-200">
                                <strong>Alamat Antar:</strong> ${d.alamat_pengiriman}
                            </div>
                        ` : ''}
                        ${d.catatan ? `
                            <div class="text-[11px] text-amber-800 bg-amber-50 p-2 rounded-xl mt-1">
                                <strong>Catatan:</strong> ${d.catatan}
                            </div>
                        ` : ''}
                    </div>

                    <!-- Rincian Produk -->
                    <div>
                        <h4 class="font-bold text-slate-900 mb-1">Rincian Menu Kopi:</h4>
                        <div class="flex flex-col">${itemsHtml}</div>
                    </div>

                    <!-- Breakdown Biaya -->
                    <div class="p-3 bg-slate-50 rounded-2xl flex flex-col gap-1 text-[11px]">
                        <div class="flex justify-between">
                            <span class="text-slate-600">Subtotal Produk</span>
                            <span class="font-medium text-slate-800">Rp ${parseInt(d.subtotal).toLocaleString('id-ID')}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600">Biaya Pengiriman (Ongkir)</span>
                            <span class="font-medium text-slate-800">Rp ${parseInt(d.ongkir).toLocaleString('id-ID')}</span>
                        </div>
                        <div class="flex justify-between pt-1 border-t border-slate-200 text-xs font-extrabold text-blue-900">
                            <span>Total Pembayaran</span>
                            <span>Rp ${parseInt(d.total).toLocaleString('id-ID')}</span>
                        </div>
                    </div>

                    <!-- Riwayat Status Timeline -->
                    <div>
                        <h4 class="font-bold text-slate-900 mb-2">Riwayat Jejak Status:</h4>
                        <div class="flex flex-col gap-2 p-3 bg-slate-50 rounded-2xl">
                            ${timelineHtml || '<div class="text-slate-400 text-center py-2">Belum ada riwayat audit trail</div>'}
                        </div>
                    </div>
                `;
            })
            .catch(err => {
                contentEl.innerHTML = '<div class="p-4 text-center text-rose-600 font-semibold">Gagal mengambil detail pesanan. Silakan coba kembali.</div>';
            });
    }

    function closeDetailModal() {
        const modal = document.getElementById('detail-modal');
        modal.classList.add('opacity-0');
        setTimeout(() => modal.classList.add('hidden'), 200);
    }
</script>
@endpush
