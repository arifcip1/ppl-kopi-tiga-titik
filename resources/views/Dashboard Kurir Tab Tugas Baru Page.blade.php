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
    <div class="flex items-center gap-2 border-b border-slate-200 pb-2 overflow-x-auto no-scrollbar">
        <a href="{{ route('kurir.tugas-baru') }}" class="px-3.5 sm:px-4 py-2 bg-blue-900 text-white rounded-xl text-xs font-bold shadow-xs flex items-center gap-2 whitespace-nowrap shrink-0">
            <span class="material-symbols-outlined text-base">moped</span>
            <span>Tugas Baru</span>
            <span class="px-1.5 py-0.2 bg-white/20 text-white rounded-full text-[10px]">
                {{ $stats['active_tasks'] ?? 0 }}
            </span>
        </a>

        <a href="{{ route('kurir.selesai') }}" class="px-3.5 sm:px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold flex items-center gap-2 transition-colors whitespace-nowrap shrink-0">
            <span class="material-symbols-outlined text-base text-slate-500">task_alt</span>
            <span>Riwayat Selesai</span>
            <span class="px-1.5 py-0.2 bg-slate-200 text-slate-700 rounded-full text-[10px]">
                {{ $stats['total_completed'] ?? $stats['completed_today'] ?? 0 }}
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
                <p class="text-[10px] text-slate-400 mt-0.5">{{ $stats['total_completed'] ?? 0 }} total pengantaran selesai</p>
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
                <p class="text-[10px] text-slate-400 mt-0.5">Akumulasi seluruh botol kopi</p>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- 3. DAFTAR TUGAS PENGANTARAN (ISOLASI DATA KURIR FR-8.9)        -->
    <!-- ============================================================== -->
    <div class="flex items-center justify-between flex-wrap gap-2">
        <div class="flex items-center gap-2 min-w-0">
            <span class="material-symbols-outlined text-blue-900 text-lg shrink-0">delivery_dining</span>
            <h3 class="font-extrabold text-slate-900 text-sm md:text-base font-['Plus_Jakarta_Sans'] truncate">Daftar Antaran Aktif Kamu</h3>
        </div>
        <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-xl shrink-0">
            {{ count($tasks ?? []) }} Tugas Menunggu
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @forelse($tasks as $tugas)
            @php
                $customerName = $tugas->nama_penerima ?? $tugas->nama_pelanggan ?? optional($tugas->user)->name ?? 'Pelanggan';
                $customerPhone = $tugas->telepon ?? optional($tugas->user)->telepon ?? '-';
            @endphp
            <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/90 flex flex-col justify-between gap-3 hover:border-blue-300 transition-colors">
                <!-- Header Kartu Tugas -->
                <div>
                    <div class="flex items-center justify-between flex-wrap gap-2 border-b border-slate-100 pb-2.5">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-extrabold text-slate-900 text-xs md:text-sm font-['Plus_Jakarta_Sans']">#{{ $tugas->kode }}</span>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-indigo-50 text-indigo-800 border border-indigo-200">
                                DIKIRIM
                            </span>
                        </div>
                        <span class="text-[10px] text-slate-400 font-medium">
                            Ditugaskan: {{ $tugas->ditugaskan_waktu_formatted ?? ($tugas->created_at ? $tugas->created_at->format('H:i') . ' WIB' : '-') }}
                        </span>
                    </div>

                    <!-- Informasi Penerima & Alamat Lengkap -->
                    <div class="mt-3 flex flex-col gap-1.5">
                        <div class="flex items-center justify-between flex-wrap gap-1.5">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="material-symbols-outlined text-slate-400 text-base shrink-0">person</span>
                                <span class="text-xs font-bold text-slate-900 truncate">{{ $customerName }}</span>
                            </div>
                            <span class="text-[11px] font-semibold text-slate-600 shrink-0">
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
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-1">
                            @if(!empty($tugas->whatsapp_number))
                                <a href="https://wa.me/{{ $tugas->whatsapp_number }}" target="_blank" rel="noopener" class="py-2 px-3 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                                    <span class="material-symbols-outlined text-sm text-emerald-600">chat</span>
                                    <span>Hubungi WhatsApp</span>
                                </a>
                            @else
                                <span class="py-2 px-3 bg-slate-100 text-slate-400 border border-slate-200 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 cursor-not-allowed">
                                    <span class="material-symbols-outlined text-sm">chat_bubble_outline</span>
                                    <span>WhatsApp Tidak Tersedia</span>
                                </span>
                            @endif
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

                    <!-- Rincian Total Tagihan & Status Pembayaran (Point 9) -->
                    <div class="mt-2.5 p-2.5 bg-slate-50 border border-slate-200/80 rounded-xl flex items-center justify-between">
                        <div>
                            <span class="text-[10px] text-slate-500 font-medium block">Total Tagihan:</span>
                            <span class="text-xs md:text-sm font-extrabold text-slate-900 font-['Plus_Jakarta_Sans']">
                                {{ $tugas->total_formatted ?? ('Rp ' . number_format($tugas->total ?? 0, 0, ',', '.')) }}
                            </span>
                        </div>
                        @php
                            $isPaidCourier = in_array(optional($tugas->payment)->status, ['berhasil', 'lunas']) || in_array($tugas->status, ['diproses', 'dikirim', 'selesai']);
                        @endphp
                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold {{ $isPaidCourier ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $isPaidCourier ? '● Lunas (QRIS)' : '● Menunggu Pembayaran' }}
                        </span>
                    </div>
                </div>

                <!-- Footer Kartu: Konfirmasi Selesai dengan Template Alert Modal (Point 12) -->
                <div class="pt-3 border-t border-slate-100">
                    <button type="button" onclick="openCompleteTaskModal('{{ $tugas->id }}', '{{ $tugas->kode }}', '{{ addslashes($customerName) }}')" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-all shadow-xs cursor-pointer active:scale-98">
                        <span class="material-symbols-outlined text-base">check_circle</span>
                        <span>Tandai Selesai (Diterima Pembeli)</span>
                    </button>
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

    <!-- ============================================================== -->
    <!-- MODAL KONFIRMASI TANDAI SELESAI (TEMPLATE ALERT STANDAR PPL-24)-->
    <!-- ============================================================== -->
    <div id="modal-complete-task-confirm" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden opacity-0 transition-opacity duration-200" onclick="if (event.target === this) closeCompleteTaskModal()">
        <div class="bg-white w-full max-w-sm rounded-3xl p-6 shadow-2xl border border-slate-100 text-center flex flex-col items-center">
            <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center mb-3">
                <span class="material-symbols-outlined text-3xl">task_alt</span>
            </div>
            <h3 class="font-bold text-slate-900 text-base font-['Plus_Jakarta_Sans']">Selesaikan Pengantaran?</h3>
            <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                Apakah pesanan <strong id="complete-task-code" class="text-slate-900">#KTT</strong> untuk penerima <strong id="complete-task-customer" class="text-slate-900">-</strong> sudah diterima dengan baik?
            </p>

            <form id="completeTaskForm" method="POST" action="" class="w-full mt-5">
                @csrf
                <div class="grid grid-cols-2 gap-2.5">
                    <button type="button" onclick="closeCompleteTaskModal()" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-colors shadow-xs cursor-pointer flex items-center justify-center gap-1">
                        <span class="material-symbols-outlined text-sm">check_circle</span>
                        <span>Ya, Selesai</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    function openCompleteTaskModal(orderId, orderCode, customerName) {
        const modal = document.getElementById('modal-complete-task-confirm');
        const codeEl = document.getElementById('complete-task-code');
        const customerEl = document.getElementById('complete-task-customer');
        const form = document.getElementById('completeTaskForm');

        codeEl.innerText = '#' + orderCode;
        customerEl.innerText = customerName;
        form.action = '{{ url("/kurir/orders") }}/' + orderId + '/complete';

        modal.classList.remove('hidden');
        setTimeout(() => modal.classList.remove('opacity-0'), 10);
    }

    function closeCompleteTaskModal() {
        const modal = document.getElementById('modal-complete-task-confirm');
        modal.classList.add('opacity-0');
        setTimeout(() => modal.classList.add('hidden'), 200);
    }
</script>
@endpush
