@extends('layouts.pelanggan')

@section('title', 'Lacak Pesanan - Kopi Tiga Titik')
@section('active_menu', 'pesanan-saya')
@section('page_title', 'Status & Lacak Pesanan')
@section('role_badge', 'PELANGGAN')
@section('page_subtitle', 'Pantau progres peracikan, pengantaran kurir, dan riwayat pesanan secara transparan')

@section('header_actions')
    <a href="{{ route('orders') }}" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-colors shadow-2xs">
        <span class="material-symbols-outlined text-base">arrow_back</span>
        <span class="hidden sm:inline">Daftar Pesanan</span>
    </a>
@endsection

@section('content')

@if(!$order)
    <!-- Empty State: Belum Ada Pesanan yang Dilacak -->
    <div class="p-8 md:p-16 bg-white rounded-3xl shadow-xs border border-slate-200/80 flex flex-col items-center justify-center text-center gap-4">
        <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-900 flex items-center justify-center shadow-inner">
            <span class="material-symbols-outlined text-3xl">track_changes</span>
        </div>
        <div class="max-w-sm">
            <h3 class="text-base md:text-lg font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Belum Ada Pesanan yang Dilacak</h3>
            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                Kamu belum memilih pesanan atau belum memiliki pesanan aktif untuk dipantau.
            </p>
        </div>
        <div class="flex items-center gap-2.5 mt-2">
            <a href="{{ route('orders') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition-colors">
                Lihat Riwayat Pesanan
            </a>
            <a href="{{ url('/katalog') }}" class="px-4 py-2.5 bg-blue-900 hover:bg-blue-800 text-white rounded-xl text-xs font-bold transition-colors shadow-xs">
                Katalog Menu
            </a>
        </div>
    </div>
@else

    <!-- Header Status Bar -->
    <div class="p-4 md:p-5 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('orders') }}" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition-colors" title="Kembali ke Riwayat">
                <span class="material-symbols-outlined text-lg">arrow_back</span>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <span class="font-extrabold text-blue-900 font-['Plus_Jakarta_Sans'] text-base tracking-tight">{{ $order->kode }}</span>
                    <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-[10px] font-bold rounded-full">
                        {{ $order->total_botol }} Botol
                    </span>
                </div>
                <p class="text-[11px] text-slate-500 mt-0.5">Dipesan pada {{ $order->tanggal_formatted }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2 self-stretch sm:self-auto justify-between">
            <div class="px-3 py-1 rounded-full border text-xs font-extrabold flex items-center gap-1.5 {{ $order->status_badge }}">
                <span class="w-2 h-2 rounded-full {{ $order->status_dot }} animate-pulse"></span>
                <span>{{ $order->status_label }}</span>
            </div>
        </div>
    </div>

    <!-- Grid 2-Kolom Desktop Responsif -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

        <!-- Kolom Kiri: Live Tracking Stepper & Log Status (lg:col-span-7) -->
        <div class="lg:col-span-7 flex flex-col gap-4">

            <!-- Banner Estimasi Waktu / Status Terkini -->
            <div class="p-4 md:p-5 bg-white rounded-3xl shadow-xs border-2 border-blue-900/30 flex flex-col gap-3">
                <div class="flex items-center justify-between text-xs">
                    <span class="px-2.5 py-1 bg-blue-50 text-blue-900 font-bold rounded-full flex items-center gap-1.5 border border-blue-200/60">
                        <span class="material-symbols-outlined text-sm">local_cafe</span>
                        <span>Lapak Kuwung • Delivery</span>
                    </span>
                    <span class="text-[11px] font-semibold text-slate-500">Live Status</span>
                </div>

                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-2 pt-1 border-t border-slate-100">
                    <div>
                        <span class="text-[11px] text-slate-400 font-medium">Estimasi Waktu</span>
                        @if($order->status === 'diproses')
                            <div class="text-xl md:text-2xl font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">15 – 20 Menit</div>
                        @elseif($order->status === 'dikirim')
                            <div class="text-xl md:text-2xl font-extrabold text-purple-900 font-['Plus_Jakarta_Sans']">Dalam Perjalanan 🛵</div>
                        @elseif($order->status === 'selesai')
                            <div class="text-xl md:text-2xl font-extrabold text-emerald-700 font-['Plus_Jakarta_Sans']">Telah Diterima ✓</div>
                        @elseif($order->status === 'dibatalkan')
                            <div class="text-xl md:text-2xl font-extrabold text-rose-700 font-['Plus_Jakarta_Sans']">Pesanan Dibatalkan</div>
                        @else
                            <div class="text-xl md:text-2xl font-extrabold text-amber-700 font-['Plus_Jakarta_Sans']">Menunggu Pembayaran</div>
                        @endif
                    </div>

                    <div class="sm:text-right">
                        <span class="text-[10px] text-slate-400 block font-semibold uppercase tracking-wider">Tahap Sekarang</span>
                        <span class="text-xs font-bold text-slate-800 flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm text-blue-900">{{ $order->status_icon }}</span>
                            <span>{{ $order->status_label }}</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Stepper Timeline Pelacakan Status -->
            <div class="p-5 md:p-6 bg-white rounded-3xl shadow-xs border border-slate-200/80 flex flex-col gap-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-900 text-lg">timeline</span>
                        <h3 class="text-sm font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Alur Pelacakan Pesanan</h3>
                    </div>
                    <span class="text-[11px] text-emerald-600 font-bold flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Update Otomatis</span>
                    </span>
                </div>

                @php
                    $step = $order->status_step; // 1: Menunggu, 2: Diproses, 3: Dikirim, 4: Selesai, 0: Batal
                @endphp

                <!-- 4 Step Alur Transaksi -->
                <div class="relative pl-7 space-y-6 pt-1">
                    <!-- Garis Vertikal Penanda Alur -->
                    <div class="absolute left-3 top-3 bottom-3 w-0.5 bg-slate-200"></div>

                    <!-- Step 1: Pesanan Dibuat / Pembayaran -->
                    <div class="relative flex items-start gap-3.5">
                        <div class="absolute -left-7 w-6 h-6 rounded-full {{ $step >= 1 ? 'bg-blue-900 text-white shadow-xs' : 'bg-slate-200 text-slate-400' }} flex items-center justify-center text-xs shrink-0">
                            <span class="material-symbols-outlined text-xs">check</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold {{ $step >= 1 ? 'text-slate-900' : 'text-slate-400' }}">Pesanan Dibuat & Verifikasi Pembayaran</span>
                                <span class="text-[10px] text-slate-400">{{ $order->created_at ? $order->created_at->format('H:i') . ' WIB' : '' }}</span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                                Pesanan dicatat di sistem Kopi Tiga Titik dengan metode pembayaran QRIS.
                            </p>
                        </div>
                    </div>

                    <!-- Step 2: Peracikan Kopi (Diproses) -->
                    <div class="relative flex items-start gap-3.5">
                        <div class="absolute -left-7 w-6 h-6 rounded-full {{ $step >= 2 ? 'bg-blue-900 text-white shadow-xs ring-4 ring-blue-100' : 'bg-slate-200 text-slate-400' }} flex items-center justify-center text-xs shrink-0">
                            <span class="material-symbols-outlined text-xs">local_cafe</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold {{ $step >= 2 ? 'text-slate-900' : 'text-slate-400' }}">Kopi Sedang Diracik & Dikemas</span>
                                @if($step === 2)
                                    <span class="px-1.5 py-0.2 bg-blue-100 text-blue-900 text-[9px] font-extrabold rounded">Diproses</span>
                                @endif
                            </div>
                            <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                                Barista menyiapkan botol 250ml dalam cooler pack agar kualitas dan kesegaran rasa terjaga.
                            </p>
                        </div>
                    </div>

                    <!-- Step 3: Kurir Mengantar (Dikirim) -->
                    <div class="relative flex items-start gap-3.5">
                        <div class="absolute -left-7 w-6 h-6 rounded-full {{ $step >= 3 ? 'bg-purple-900 text-white shadow-xs ring-4 ring-purple-100' : 'bg-slate-200 text-slate-400' }} flex items-center justify-center text-xs shrink-0">
                            <span class="material-symbols-outlined text-xs">moped</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold {{ $step >= 3 ? 'text-slate-900' : 'text-slate-400' }}">Kurir Mengantar Pesanan</span>
                                @if($step === 3)
                                    <span class="px-1.5 py-0.2 bg-purple-100 text-purple-900 text-[9px] font-extrabold rounded">Diantar</span>
                                @endif
                            </div>
                            <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                                Kurir toko sedang dalam perjalanan menuju alamat pengirimanmu.
                            </p>
                        </div>
                    </div>

                    <!-- Step 4: Selesai Diterima -->
                    <div class="relative flex items-start gap-3.5">
                        <div class="absolute -left-7 w-6 h-6 rounded-full {{ $step >= 4 ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-200 text-slate-400' }} flex items-center justify-center text-xs shrink-0">
                            <span class="material-symbols-outlined text-xs">check_circle</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold {{ $step >= 4 ? 'text-slate-900' : 'text-slate-400' }}">Pesanan Selesai Diterima</span>
                                @if($step >= 4)
                                    <span class="px-1.5 py-0.2 bg-emerald-100 text-emerald-800 text-[9px] font-extrabold rounded">Selesai</span>
                                @endif
                            </div>
                            <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                                Pesanan telah sampai di tujuan dan diterima dengan baik.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Log Catatan Riwayat Status (order_status_history) -->
                @if($order->statusHistories && $order->statusHistories->isNotEmpty())
                    <div class="pt-3 border-t border-slate-100 flex flex-col gap-2">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Catatan Riwayat Aktivitas</span>
                        <div class="space-y-1.5">
                            @foreach($order->statusHistories as $log)
                                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200/60 text-xs flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="material-symbols-outlined text-sm text-blue-900 shrink-0">history</span>
                                        <span class="text-slate-700 truncate font-medium">
                                            Status diubah menjadi <strong class="text-slate-900 uppercase">{{ str_replace('_', ' ', $log->status_baru) }}</strong>
                                            @if($log->keterangan)
                                                - {{ $log->keterangan }}
                                            @endif
                                        </span>
                                    </div>
                                    <span class="text-[10px] text-slate-400 shrink-0">{{ $log->created_at ? $log->created_at->format('d/m H:i') : '' }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Kartu Alamat & Informasi Kurir -->
            <div class="p-5 bg-white rounded-3xl shadow-xs border border-slate-200/80 flex flex-col gap-3">
                <div class="flex items-center justify-between pb-2.5 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-900 text-lg">location_on</span>
                        <h3 class="text-sm font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Tujuan Pengiriman & Penerima</h3>
                    </div>
                    <span class="text-[10px] font-extrabold text-blue-900 bg-blue-50 px-2 py-0.5 rounded-md">DELIVERY</span>
                </div>

                <div class="flex flex-col gap-1 text-xs">
                    <div class="font-bold text-slate-900">{{ $order->nama_penerima ?? Auth::user()->name }}</div>
                    <p class="text-slate-500 leading-relaxed">{{ $order->alamat }}</p>
                    <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[13px]">call</span>
                        <span>{{ $order->telepon ?? '-' }}</span>
                    </div>
                    @if($order->catatan)
                        <div class="mt-1.5 p-2 bg-amber-50/70 border border-amber-200/60 rounded-xl text-[11px] text-amber-900 flex items-start gap-1.5">
                            <span class="material-symbols-outlined text-sm shrink-0 mt-0.5">sticky_note_2</span>
                            <span><strong>Catatan:</strong> {{ $order->catatan }}</span>
                        </div>
                    @endif
                </div>

                <!-- Kurir Info -->
                <div class="mt-2 p-3 bg-blue-50/70 rounded-2xl border border-blue-100 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-blue-900 text-white font-extrabold text-xs flex items-center justify-center shrink-0">
                            {{ $order->courier ? strtoupper(substr($order->courier->name, 0, 1)) : 'K' }}
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900">{{ $order->courier ? $order->courier->name : 'Kurir Toko Resmi' }}</div>
                            <div class="text-[10px] text-blue-900 font-semibold">Pengantar Khusus Kopi Tiga Titik</div>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 bg-white text-blue-900 font-bold text-[10px] rounded-lg border border-blue-200">
                        Siap Antar
                    </span>
                </div>
            </div>

        </div>

        <!-- Kolom Kanan: Rincian Produk & Ringkasan Pembayaran (lg:col-span-5) -->
        <div class="lg:col-span-5 flex flex-col gap-4 lg:sticky lg:top-24">

            <!-- Rincian Produk Pesanan -->
            <div class="p-5 bg-white rounded-3xl shadow-xs border border-slate-200/80 flex flex-col gap-3.5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Rincian Item Kopi</h3>
                    <span class="text-xs text-slate-500 font-semibold">{{ $order->total_botol }} Botol 250ml</span>
                </div>

                <div class="flex flex-col gap-3">
                    @foreach($order->orderItems as $item)
                        <div class="flex items-center justify-between text-xs gap-3">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-10 h-12 bg-slate-50 rounded-xl border border-slate-200/70 p-1 shrink-0 flex items-center justify-center">
                                    @php
                                        $imgSrc = $item->product && $item->product->foto_path
                                            ? asset('storage/' . $item->product->foto_path)
                                            : asset('products/' . $item->nama_produk . '.png');
                                    @endphp
                                    <img src="{{ $imgSrc }}" alt="{{ $item->nama_produk }}" class="w-full h-full object-contain" onerror="this.src='https://placehold.co/80x100/1e3a8a/ffffff?text=KTT'" />
                                </div>
                                <div class="min-w-0 truncate">
                                    <span class="font-bold text-slate-900 truncate block">{{ $item->nama_produk }}</span>
                                    <span class="text-[11px] text-slate-500">{{ $item->jumlah }} botol x Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <span class="font-bold text-slate-900 shrink-0">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Ringkasan Biaya & Pembayaran -->
            <div class="p-5 bg-white rounded-3xl shadow-xs border border-slate-200/80 flex flex-col gap-3">
                <h3 class="text-sm font-bold text-slate-900 font-['Plus_Jakarta_Sans'] pb-2 border-b border-slate-100">Ringkasan Tagihan</h3>

                <div class="flex flex-col gap-2 text-xs text-slate-600">
                    <div class="flex justify-between">
                        <span>Subtotal Produk</span>
                        <span class="font-semibold text-slate-800">{{ $order->subtotal_formatted }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Biaya Ongkos Kirim</span>
                        <span class="font-semibold text-slate-800">{{ $order->ongkir_formatted }}</span>
                    </div>
                    <div class="border-t border-dashed border-slate-200 my-1"></div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="font-bold text-slate-900">Total Pembayaran</span>
                        <span class="font-extrabold text-blue-900 font-['Plus_Jakarta_Sans'] text-base">{{ $order->total_formatted }}</span>
                    </div>
                </div>

                <!-- Metode Pembayaran Badge -->
                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200/60 flex items-center justify-between text-xs mt-1">
                    <div class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-blue-900 text-base">qr_code_scanner</span>
                        <span class="font-semibold text-slate-800">QRIS (Midtrans)</span>
                    </div>
                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-800 font-bold text-[10px] rounded-md border border-emerald-200">
                        {{ $order->payment && $order->payment->isSuccess() ? 'Lunas Terverifikasi' : 'Terverifikasi' }}
                    </span>
                </div>

                <!-- Tombol Aksi Bawah -->
                <div class="flex flex-col gap-2 pt-2">
                    <a href="{{ url('/katalog') }}" class="w-full py-2.5 bg-blue-900 hover:bg-blue-800 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-colors shadow-xs">
                        <span class="material-symbols-outlined text-sm">storefront</span>
                        <span>Pesan Menu Lain</span>
                    </a>
                    <a href="{{ route('orders') }}" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors">
                        <span class="material-symbols-outlined text-sm">receipt_long</span>
                        <span>Lihat Semua Pesanan Saya</span>
                    </a>
                </div>
            </div>

        </div>

    </div>

@endif

@endsection
