@extends('layouts.kurir')

@section('title', 'Riwayat Pengantaran Selesai')
@section('active_menu', 'pengantaran')
@section('page_title', 'Riwayat Pengantaran Selesai')
@section('role_badge', 'KURIR')
@section('page_subtitle', 'Daftar pengantaran pesanan yang telah berhasil kamu selesaikan')

@section('content')

    <!-- Flash Message Feedback Standar (PPL-24) -->
    <x-flash-message class="mb-2" />

    <!-- ============================================================== -->
    <!-- 1. TABS NAVIGASI UTAMA KURIR                                   -->
    <!-- ============================================================== -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
        <a href="{{ route('kurir.tugas-baru') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold flex items-center gap-2 transition-colors">
            <span class="material-symbols-outlined text-base text-slate-500">moped</span>
            <span>Tugas Baru</span>
            <span class="px-1.5 py-0.2 bg-slate-200 text-slate-700 rounded-full text-[10px]">
                {{ $stats['active_tasks'] ?? 0 }}
            </span>
        </a>

        <a href="{{ route('kurir.selesai') }}" class="px-4 py-2 bg-blue-900 text-white rounded-xl text-xs font-bold shadow-xs flex items-center gap-2">
            <span class="material-symbols-outlined text-base">task_alt</span>
            <span>Riwayat Selesai</span>
            <span class="px-1.5 py-0.2 bg-white/20 text-white rounded-full text-[10px]">
                {{ $completedTasks->total() }}
            </span>
        </a>
    </div>

    <!-- ============================================================== -->
    <!-- 2. DAFTAR RIWAYAT PENGANTARAN SELESAI                          -->
    <!-- ============================================================== -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-emerald-700 text-lg">check_circle</span>
            <h3 class="font-extrabold text-slate-900 text-sm md:text-base font-['Plus_Jakarta_Sans']">Daftar Pengantaran Selesai</h3>
        </div>
        <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-xl">
            Total {{ $completedTasks->total() }} Pengantaran
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @forelse($completedTasks as $order)
            @php
                $customerName = $order->nama_penerima ?? $order->nama_pelanggan ?? optional($order->user)->name ?? 'Pelanggan';
                $customerPhone = $order->telepon ?? optional($order->user)->no_telepon ?? '-';
            @endphp
            <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/90 flex flex-col justify-between gap-3">
                <!-- Header Kartu Riwayat -->
                <div>
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                        <div class="flex items-center gap-2">
                            <span class="font-extrabold text-slate-900 text-xs md:text-sm font-['Plus_Jakarta_Sans']">#{{ $order->kode }}</span>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                SELESAI
                            </span>
                        </div>
                        <span class="text-[10px] text-slate-400 font-medium">
                            {{ $order->updated_at ? $order->updated_at->format('d M Y, H:i') : '-' }} WIB
                        </span>
                    </div>

                    <!-- Informasi Penerima & Alamat -->
                    <div class="mt-3 flex flex-col gap-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-900">{{ $customerName }}</span>
                            <span class="text-[11px] font-semibold text-slate-500">
                                {{ $customerPhone }}
                            </span>
                        </div>

                        <div class="p-2.5 bg-slate-50 rounded-xl text-xs text-slate-600 flex items-start gap-1.5">
                            <span class="material-symbols-outlined text-slate-400 text-sm mt-0.5 shrink-0">place</span>
                            <p class="text-[11px] line-clamp-2">{{ $order->alamat }}</p>
                        </div>
                    </div>

                    <!-- Botol Kopi Yang Telah Diantar -->
                    <div class="mt-3 pt-2.5 border-t border-slate-100">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Botol Terkirim:</span>
                        <div class="flex flex-col gap-1">
                            @foreach($order->orderItems as $item)
                                <div class="flex items-center justify-between text-xs bg-slate-50 px-2.5 py-1.5 rounded-lg">
                                    <span class="text-slate-800">
                                        <strong>{{ $item->jumlah }}x</strong> {{ $item->nama_produk }}
                                    </span>
                                    <span class="text-[10px] font-semibold text-slate-500">250ml</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Footer Kartu -->
                <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-[11px] font-semibold text-emerald-700 flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">verified</span>
                        Pesanan Diterima Pembeli
                    </span>
                    <span class="font-extrabold text-blue-900">
                        Total Tagihan: Rp {{ number_format($order->total, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 bg-white rounded-2xl border border-slate-200/90 text-center flex flex-col items-center">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-2">
                    <span class="material-symbols-outlined text-2xl">history</span>
                </div>
                <h4 class="font-bold text-slate-800 text-sm">Belum ada riwayat selesai</h4>
                <p class="text-xs text-slate-500 mt-0.5">Pesanan yang telah kamu tandai selesai akan tersimpan di sini.</p>
            </div>
        @endforelse
    </div>

    <!-- Paginasi -->
    <div class="mt-3">
        {{ $completedTasks->links() }}
    </div>

@endsection
