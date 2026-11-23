@extends('layouts.pelanggan')

@section('title', 'Pembayaran QRIS #' . ($order->kode ?? 'Pesanan') . ' - Kopi Tiga Titik')
@section('active_menu', 'keranjang')
@section('page_title', 'Pembayaran QRIS')
@section('role_badge', 'PELANGGAN')
@section('page_subtitle', 'Selesaikan pembayaran tagihan via scan QRIS Midtrans Sandbox sebelum batas waktu berakhir')

@section('header_actions')
    <div class="flex items-center gap-1.5 sm:gap-2">
        <a href="{{ url('/checkout') }}" class="px-2.5 sm:px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold flex items-center gap-1 sm:gap-1.5 transition-colors shadow-2xs" title="Kembali ke Form Checkout">
            <span class="material-symbols-outlined text-sm sm:text-base">arrow_back</span>
            <span class="hidden sm:inline">Checkout</span>
        </a>

        @php
            $sellerWaNumber = config('services.seller.whatsapp', '6285710885120');
            $sellerWaDisplay = config('services.seller.whatsapp_display', '0857-1088-5120');
        @endphp
        <a href="https://wa.me/{{ $sellerWaNumber }}?text={{ urlencode('Halo Admin Kopi Tiga Titik, saya butuh bantuan untuk pembayaran pesanan #' . ($order->kode ?? '')) }}" 
           target="_blank" 
           rel="noopener noreferrer" 
           class="px-2.5 sm:px-3.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-xl text-xs font-bold flex items-center gap-1 sm:gap-1.5 transition-colors shadow-2xs" 
           title="Bantuan WhatsApp Penjual">
            <span class="material-symbols-outlined text-sm sm:text-base text-emerald-600">chat</span>
            <span class="hidden sm:inline">Bantuan</span>
        </a>
    </div>
@endsection

@section('content')

<!-- Header Status Bar Pesanan (Konsisten dengan Desain Order Tracking & Nota Digital) -->
<div class="p-4 md:p-5 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
    <div class="flex items-center gap-3">
        <a href="{{ url('/checkout') }}" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition-colors" title="Kembali ke Checkout">
            <span class="material-symbols-outlined text-lg">arrow_back</span>
        </a>
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <span class="font-extrabold text-blue-900 font-['Plus_Jakarta_Sans'] text-base tracking-tight">#{{ $order->kode }}</span>
                <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-[10px] font-bold rounded-full">
                    {{ $order->orderItems->sum('jumlah') ?? 0 }} Botol
                </span>
                <span class="px-2 py-0.5 bg-blue-50 text-blue-900 text-[10px] font-bold rounded-full border border-blue-200">
                    {{ $order->nama_lokasi_asal ?? $lokasiLapak->nama_lokasi ?? 'Lapak Jl. Kuwung' }}
                </span>
            </div>
            <p class="text-[11px] text-slate-500 mt-0.5">
                Dibuat pada {{ $order->created_at ? $order->created_at->translatedFormat('d F Y, H:i') . ' WIB' : now()->translatedFormat('d F Y, H:i') . ' WIB' }}
            </p>
        </div>
    </div>

    <div class="flex items-center gap-2 self-stretch sm:self-auto justify-between">
        <div class="px-3 py-1 rounded-full border text-xs font-extrabold flex items-center gap-1.5 bg-amber-50 text-amber-800 border-amber-200">
            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
            <span>Menunggu Pembayaran</span>
        </div>
    </div>
</div>

<!-- Grid 2-Kolom Desktop Responsif (Menyatu dengan Desain Pelanggan) -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

    <!-- KOLOM KIRI (lg:col-span-7): QR Code, Instruksi Pemindaian & Aksi Pembayaran -->
    <div class="lg:col-span-7 flex flex-col gap-4 order-2 lg:order-1">

        <!-- Kartu Utama QRIS Midtrans Sandbox -->
        <div class="p-5 sm:p-7 bg-white rounded-3xl shadow-xs border border-slate-200/80 flex flex-col items-center gap-4 text-center">
            
            <!-- Header Kartu QRIS -->
            <div class="w-full flex justify-between items-center border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-base sm:text-lg font-black text-slate-900 tracking-tight">QRIS</span>
                    <span class="px-2 py-0.5 bg-red-600 text-white text-[9px] font-bold rounded-md tracking-wider">SANDBOX</span>
                </div>
                <div class="flex items-center gap-1.5 text-[10px] sm:text-xs font-bold text-slate-600">
                    <span class="w-4 h-4 rounded-full border border-blue-900 text-blue-900 flex items-center justify-center text-[8px] font-black">G</span>
                    <span>GPN / MIDTRANS</span>
                </div>
            </div>

            <!-- Identitas Merchant & Logo Resmi -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white rounded-2xl border border-slate-200/90 shadow-2xs flex items-center justify-center p-1.5 shrink-0 overflow-hidden">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo Kopi Tiga Titik" class="w-full h-full object-contain" onerror="this.onerror=null; this.src='https://placehold.co/100x100/1e3a8a/ffffff?text=KTT'" />
                </div>
                <div class="text-left">
                    <h3 class="text-xs sm:text-sm font-bold text-slate-900 uppercase font-['Plus_Jakarta_Sans'] leading-tight">KOPI TIGA TITIK MOJOKERTO</h3>
                    <p class="text-[10px] sm:text-[11px] text-slate-500 font-mono">Order Ref: {{ $order->kode ?? 'KTT' }}</p>
                </div>
            </div>

            <!-- Stylized QR Code Box -->
            <div class="w-64 h-64 sm:w-72 sm:h-72 bg-white border-2 border-slate-900/10 rounded-3xl p-3.5 shadow-inner flex flex-col items-center justify-center relative overflow-hidden group">
                @php
                    $qrCodeUrl = $qrisData['qr_url'] ?? null;
                    $qrString = $qrisData['qr_string'] ?? ($order->kode ?? 'KTT-QRIS');
                    $fallbackQrApi = 'https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=' . urlencode($qrString);
                @endphp

                @if(!empty($qrCodeUrl))
                    <img src="{{ $qrCodeUrl }}" alt="QRIS Code Midtrans" class="w-full h-full object-contain" onerror="this.src='{{ $fallbackQrApi }}'" />
                @else
                    <img src="{{ $fallbackQrApi }}" alt="QRIS Code Midtrans" class="w-full h-full object-contain" />
                @endif
            </div>

            <div class="max-w-md">
                <p class="text-xs text-slate-600 leading-relaxed font-medium">
                    Buka aplikasi m-banking (BCA, Mandiri, BRI, BNI) atau e-wallet (GoPay, OVO, ShopeePay, Dana), lalu scan QRIS di atas untuk membayar tagihan.
                </p>
            </div>

            <!-- Payment Providers Badges -->
            <div class="flex flex-wrap justify-center gap-1.5 pt-1">
                <span class="px-2.5 py-1 bg-blue-50 text-blue-900 rounded-lg text-[10px] font-bold border border-blue-100">BCA</span>
                <span class="px-2.5 py-1 bg-blue-50 text-blue-900 rounded-lg text-[10px] font-bold border border-blue-100">MANDIRI</span>
                <span class="px-2.5 py-1 bg-blue-50 text-blue-900 rounded-lg text-[10px] font-bold border border-blue-100">GOPAY</span>
                <span class="px-2.5 py-1 bg-blue-50 text-blue-900 rounded-lg text-[10px] font-bold border border-blue-100">OVO</span>
                <span class="px-2.5 py-1 bg-blue-50 text-blue-900 rounded-lg text-[10px] font-bold border border-blue-100">DANA</span>
                <span class="px-2.5 py-1 bg-rose-50 text-rose-700 rounded-lg text-[10px] font-bold border border-rose-100">SHOPEEPAY</span>
            </div>
        </div>

        <!-- Tombol Aksi Verifikasi Pembayaran -->
        <button type="button" 
                id="btn-check-payment" 
                onclick="checkPaymentStatus(true)" 
                class="w-full py-3.5 bg-blue-900 hover:bg-blue-800 active:scale-98 text-white rounded-2xl font-bold text-xs sm:text-sm flex justify-center items-center gap-2 shadow-md transition-all cursor-pointer">
            <span id="btn-check-icon" class="material-symbols-outlined text-base">sync</span>
            <span id="btn-check-text">Saya Sudah Membayar (Cek Status)</span>
        </button>

        <!-- Mode Pengujian Sandbox (Uji Coba) -->
        @if(!config('services.midtrans.is_production', false))
            <div class="p-4 sm:p-5 bg-amber-50/90 rounded-3xl border border-amber-200 text-center flex flex-col gap-2.5 shadow-xs">
                <div class="flex items-center justify-center gap-1.5 text-amber-900 font-bold text-xs sm:text-sm">
                    <span class="material-symbols-outlined text-base text-amber-700">science</span>
                    <span>Mode Pengujian Midtrans Sandbox (Uji Coba)</span>
                </div>
                <p class="text-[11px] sm:text-xs text-amber-800 leading-relaxed max-w-lg mx-auto">
                    Uji pembayaran melalui <strong>Simulator QRIS Resmi Midtrans</strong> (tempel URL QRIS yang tersalin otomatis) atau gunakan tombol simulasi instan lunas di bawah:
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                    @if(!empty($qrCodeUrl))
                        <button type="button"
                                onclick="copyQrUrlAndOpenSimulator('{{ $qrCodeUrl }}')"
                                class="w-full py-2.5 px-3 bg-white hover:bg-blue-50 text-blue-900 border border-blue-300 rounded-xl font-bold text-xs flex justify-center items-center gap-1.5 shadow-2xs transition-all cursor-pointer">
                            <span class="material-symbols-outlined text-sm">open_in_new</span>
                            <span id="btn-open-simulator-text" class="truncate">Salin URL & Buka Simulator</span>
                        </button>
                    @endif

                    <button type="button" 
                            id="btn-simulate-payment" 
                            onclick="simulateSuccessPayment()" 
                            class="w-full py-2.5 px-3 bg-emerald-600 hover:bg-emerald-700 active:scale-98 text-white rounded-xl font-bold text-xs flex justify-center items-center gap-1.5 shadow-xs transition-all cursor-pointer">
                        <span class="material-symbols-outlined text-sm">bolt</span>
                        <span id="btn-simulate-text" class="truncate">Simulasikan Lunas Instan</span>
                    </button>
                </div>
            </div>
        @endif

    </div>

    <!-- KOLOM KANAN (lg:col-span-5): Countdown, Total Tagihan, Pemenuhan & Rincian Pesanan -->
    <div class="lg:col-span-5 flex flex-col gap-4 order-1 lg:order-2 lg:sticky lg:top-24">

        <!-- Countdown Timer Banner (10 Menit TTL - FR-6.8) -->
        <div id="timer-banner" class="p-4 sm:p-5 bg-rose-50 rounded-3xl border border-rose-200 flex flex-col gap-2.5 transition-all shadow-xs">
            <div class="flex justify-between items-center text-xs">
                <div class="flex items-center gap-1.5 font-bold text-rose-900">
                    <span class="material-symbols-outlined text-rose-600 text-lg animate-pulse">timer</span>
                    <span>Bayar Sebelum Habis</span>
                </div>
                <span id="countdown-display" class="px-3 py-0.5 bg-rose-700 text-white font-mono font-bold text-xs sm:text-sm rounded-full shadow-2xs">
                    --:--
                </span>
            </div>
            <p class="text-[11px] sm:text-xs text-rose-700 leading-tight">
                Pesanan otomatis dibatalkan jika melebihi batas waktu pembayaran 10 menit.
            </p>
            <div class="w-full h-1.5 bg-rose-200 rounded-full overflow-hidden">
                <div id="countdown-progress" class="h-full bg-rose-600 rounded-full transition-all duration-1000" style="width: 100%;"></div>
            </div>
        </div>

        <!-- Total Tagihan Card -->
        <div class="p-5 bg-white rounded-3xl shadow-xs border border-slate-200/80 flex flex-col gap-3">
            <div class="flex justify-between items-center">
                <div>
                    <div class="text-[11px] sm:text-xs text-slate-500 font-medium">Total Tagihan ({{ $order->kode ?? 'Pesanan' }})</div>
                    <div class="text-2xl sm:text-3xl font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">
                        Rp {{ number_format($order->total ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <button type="button" onclick="copyNominal({{ $order->total ?? 0 }})" class="px-3 py-1.5 bg-blue-50 text-blue-900 hover:bg-blue-100 active:scale-95 rounded-xl font-bold text-xs flex items-center gap-1 border border-blue-200 transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-sm">content_copy</span>
                    <span id="btn-copy-text">Salin</span>
                </button>
            </div>
            <div class="pt-2 border-t border-slate-100 flex items-center gap-1.5 text-[11px] text-slate-500">
                <span class="material-symbols-outlined text-xs text-blue-900 shrink-0">info</span>
                <span>Pastikan transfer tepat sesuai nominal termasuk ongkos kirim.</span>
            </div>
        </div>

        <!-- Metode Pemenuhan: Ambil Sendiri vs Delivery (FR-13 - PPL 24) -->
        @if(($order->metode_pemenuhan ?? '') === 'ambil_sendiri')
            <div class="p-5 bg-amber-50/90 rounded-3xl border border-amber-200 flex flex-col gap-2.5 text-xs shadow-xs">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1.5 font-bold text-amber-950 font-['Plus_Jakarta_Sans']">
                        <span class="material-symbols-outlined text-amber-700 text-base">storefront</span>
                        <span>Pilihan: Ambil Sendiri di Lapak</span>
                    </div>
                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded-full border border-emerald-200">Bebas Ongkir Rp 0</span>
                </div>
                <div class="text-[11px] sm:text-xs text-amber-900 leading-snug">
                    <span class="font-medium text-slate-700">Titik Pengambilan:</span>
                    <strong class="text-slate-900 block mt-0.5">{{ $order->nama_lokasi_asal ?? 'Lapak Kopi Tiga Titik' }}</strong>
                    <p class="text-slate-600 mt-0.5">{{ $order->alamat_lokasi_asal ?? 'Jl. Kuwung, Mergelo, Kec. Magersari, Kota Mojokerto' }}</p>
                </div>
                <div class="pt-2 border-t border-amber-200/80 flex items-start gap-1.5 text-[10px] sm:text-[11px] text-amber-900 font-medium">
                    <span class="material-symbols-outlined text-xs text-amber-700 mt-0.5 shrink-0">timer</span>
                    <span><strong>Batas Pengambilan:</strong> Maksimal 24 jam setelah pembayaran lunas diverifikasi.</span>
                </div>
            </div>
        @else
            <div class="p-5 bg-blue-50/70 rounded-3xl border border-blue-200/80 flex flex-col gap-2 text-xs shadow-xs">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1.5 font-bold text-blue-950 font-['Plus_Jakarta_Sans']">
                        <span class="material-symbols-outlined text-blue-900 text-base">local_shipping</span>
                        <span>Pengantaran: Kurir Toko (Delivery)</span>
                    </div>
                    <span class="px-2 py-0.5 bg-blue-100 text-blue-900 text-[10px] font-bold rounded-full">Ongkir Rp {{ number_format($order->ongkir ?? 0, 0, ',', '.') }}</span>
                </div>
                <p class="text-[11px] sm:text-xs text-slate-600 leading-snug truncate">
                    Tujuan: <strong class="text-slate-900">{{ $order->nama_penerima ?? Auth::user()->name }}</strong> ({{ $order->alamat ?? '-' }})
                </p>
            </div>
        @endif

        <!-- Rincian Pesanan Card -->
        <div class="bg-white rounded-3xl shadow-xs border border-slate-200/80 overflow-hidden">
            <button type="button" onclick="toggleDetails()" class="w-full p-4 flex justify-between items-center text-xs sm:text-sm font-bold text-slate-800 hover:bg-slate-50 transition-colors cursor-pointer">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-blue-900 text-base sm:text-lg">shopping_bag</span>
                    <span>Rincian Pesanan ({{ $order->orderItems->sum('jumlah') ?? 0 }} Botol)</span>
                </div>
                <span id="details-arrow" class="material-symbols-outlined text-slate-400 text-base transition-transform duration-200">expand_more</span>
            </button>

            <div id="details-content" class="px-5 pb-4 pt-1 border-t border-slate-100 hidden flex flex-col gap-2 text-xs">
                @foreach($order->orderItems as $item)
                    <div class="flex justify-between items-center text-slate-700 py-1">
                        <div>
                            <span class="font-bold text-slate-900">{{ $item->nama_produk }}</span>
                            <span class="text-[11px] text-slate-400 block">x {{ $item->jumlah }} botol</span>
                        </div>
                        <span class="font-semibold font-mono text-slate-800">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                @endforeach
                @if($order->ongkir > 0)
                    <div class="flex justify-between items-center text-slate-700 border-t border-dashed border-slate-100 pt-2">
                        <span>Ongkos Kirim (Delivery)</span>
                        <span class="font-semibold font-mono text-slate-800">Rp {{ number_format($order->ongkir, 0, ',', '.') }}</span>
                    </div>
                @else
                    <div class="flex justify-between items-center text-emerald-700 border-t border-dashed border-slate-100 pt-2">
                        <span>Ongkos Kirim</span>
                        <span class="font-semibold">Bebas Ongkir (Rp 0)</span>
                    </div>
                @endif
                <div class="flex justify-between items-center text-slate-900 font-extrabold border-t border-slate-200 pt-2.5">
                    <span>Total Pembayaran</span>
                    <span class="text-blue-900 text-sm font-bold font-mono">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Tombol Hubungi Penjual via WhatsApp (FR-13 - PPL 24) -->
        <div class="p-4 bg-white rounded-3xl border border-slate-200/80 flex items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100">
                    <span class="material-symbols-outlined text-lg">chat</span>
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-bold text-slate-900 truncate">Butuh Bantuan Penjual?</div>
                    <div class="text-[10px] text-slate-500 font-mono">{{ $sellerWaDisplay ?? '0857-1088-5120' }}</div>
                </div>
            </div>
            <a href="https://wa.me/{{ $sellerWaNumber ?? '6285710885120' }}?text={{ urlencode('Halo Barista Kopi Tiga Titik, saya ingin konfirmasi pembayaran pesanan: #' . ($order->kode ?? '')) }}" 
               target="_blank" 
               rel="noopener noreferrer" 
               class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white rounded-xl text-xs font-bold flex items-center gap-1 shadow-xs transition-all shrink-0">
                <span class="material-symbols-outlined text-sm">open_in_new</span>
                <span>Chat WhatsApp</span>
            </a>
        </div>

    </div>

</div>

@endsection

@push('scripts')
<script>
    const orderKode = "{{ $order->kode ?? '' }}";
    const orderId = "{{ $order->id ?? '' }}";
    let remainingSeconds = parseInt("{{ $remainingSeconds ?? 600 }}", 10);
    const totalTtlSeconds = 600; // 10 menit
    let pollInterval = null;

    // Di layar desktop (>= 1024px), buka rincian pesanan secara otomatis
    if (window.innerWidth >= 1024) {
        const content = document.getElementById('details-content');
        const arrow = document.getElementById('details-arrow');
        if (content) content.classList.remove('hidden');
        if (arrow) arrow.style.transform = 'rotate(180deg)';
    }

    // Salin URL Gambar QRIS Midtrans & Buka Halaman Simulator Resmi Midtrans
    function copyQrUrlAndOpenSimulator(qrUrl) {
        const label = document.getElementById('btn-open-simulator-text');
        if (navigator.clipboard && qrUrl) {
            navigator.clipboard.writeText(qrUrl).then(() => {
                if (label) {
                    label.textContent = 'URL Tersalin! Membuka...';
                    setTimeout(() => {
                        label.textContent = 'Salin URL & Buka Simulator';
                    }, 3500);
                }
            }).catch(() => {});
        }
        window.open('https://simulator.sandbox.midtrans.com/v2/qris/index', '_blank', 'noopener');
    }

    // Salin Nominal ke Clipboard
    function copyNominal(amount) {
        navigator.clipboard.writeText(amount.toString()).then(() => {
            const btnText = document.getElementById('btn-copy-text');
            if (btnText) {
                btnText.textContent = 'Tersalin!';
                setTimeout(() => { btnText.textContent = 'Salin'; }, 2000);
            }
        }).catch(() => {
            alert('Nominal: Rp ' + amount);
        });
    }

    // Toggle Accordion Rincian Pesanan
    function toggleDetails() {
        const content = document.getElementById('details-content');
        const arrow = document.getElementById('details-arrow');
        if (!content) return;
        const isHidden = content.classList.contains('hidden');
        if (isHidden) {
            content.classList.remove('hidden');
            if (arrow) arrow.style.transform = 'rotate(180deg)';
        } else {
            content.classList.add('hidden');
            if (arrow) arrow.style.transform = 'rotate(0deg)';
        }
    }

    // Countdown Timer 10 Menit (FR-6.8)
    function updateCountdown() {
        const display = document.getElementById('countdown-display');
        const progress = document.getElementById('countdown-progress');

        if (remainingSeconds <= 0) {
            if (display) display.textContent = '00:00';
            if (progress) progress.style.width = '0%';
            clearInterval(pollInterval);
            handleExpiredOrder();
            return;
        }

        const minutes = Math.floor(remainingSeconds / 60);
        const seconds = remainingSeconds % 60;
        const formatted = String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');

        if (display) display.textContent = formatted;
        if (progress) {
            const pct = Math.max(0, Math.min(100, (remainingSeconds / totalTtlSeconds) * 100));
            progress.style.width = pct + '%';
        }

        remainingSeconds--;
    }

    // Tangani saat waktu pembayaran habis
    function handleExpiredOrder() {
        alert('Batas waktu pembayaran 10 menit telah habis. Pesanan ini telah otomatis dibatalkan.');
        window.location.href = "{{ route('orders') }}";
    }

    // Polling Status Pembayaran Realtime ke Backend (FR-6.3)
    async function checkPaymentStatus(isManualClick = false) {
        if (!orderKode && !orderId) return;

        const btn = document.getElementById('btn-check-payment');
        const btnText = document.getElementById('btn-check-text');
        const btnIcon = document.getElementById('btn-check-icon');

        if (isManualClick && btn) {
            btn.disabled = true;
            if (btnText) btnText.textContent = 'Memverifikasi Pembayaran...';
            if (btnIcon) btnIcon.classList.add('animate-spin');
        }

        try {
            const targetIdentifier = orderKode || orderId;
            const res = await fetch(`/api/orders/${targetIdentifier}/payment-status`, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!res.ok) {
                if (isManualClick) alert('Gagal memeriksa status pembayaran.');
                return;
            }

            const json = await res.json();
            if (json.success && json.data) {
                if (json.data.is_paid) {
                    clearInterval(pollInterval);
                    if (btnText) btnText.textContent = 'Pembayaran Berhasil! Mengalihkan...';
                    window.location.replace(`/order-tracking/${orderKode || orderId}`);
                    return;
                }

                if (json.data.is_expired) {
                    clearInterval(pollInterval);
                    handleExpiredOrder();
                    return;
                }

                if (isManualClick) {
                    alert('Pembayaran belum terdeteksi. Silakan selesaikan scan QRIS pada aplikasi Anda.');
                }
            }
        } catch (err) {
            console.error('Payment polling error:', err);
        } finally {
            if (isManualClick && btn) {
                btn.disabled = false;
                if (btnText) btnText.textContent = 'Saya Sudah Membayar (Cek Status)';
                if (btnIcon) btnIcon.classList.remove('animate-spin');
            }
        }
    }

    // Fungsi Simulasi Pembayaran Sukses Sandbox
    async function simulateSuccessPayment() {
        const btn = document.getElementById('btn-simulate-payment');
        const btnText = document.getElementById('btn-simulate-text');
        if (btn) btn.disabled = true;
        if (btnText) btnText.textContent = 'Memproses Simulasi Lunas...';

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            const targetIdentifier = orderKode || orderId;
            const res = await fetch(`/api/orders/${targetIdentifier}/simulate-payment`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                }
            });

            const json = await res.json();
            if (json.success) {
                if (btnText) btnText.textContent = 'Lunas ✓ Mengalihkan ke Tracking...';
                setTimeout(() => {
                    window.location.replace(json.redirect_url || `/order-tracking/${targetIdentifier}`);
                }, 800);
            } else {
                alert(json.message || 'Gagal memproses simulasi.');
                if (btn) btn.disabled = false;
                if (btnText) btnText.textContent = 'Simulasikan Lunas Instan';
            }
        } catch (err) {
            alert('Terjadi kesalahan saat memproses simulasi.');
            if (btn) btn.disabled = false;
            if (btnText) btnText.textContent = 'Simulasikan Lunas Instan';
        }
    }

    // Inisialisasi Timer dan Polling Realtime setiap 3 detik
    updateCountdown();
    setInterval(updateCountdown, 1000);
    pollInterval = setInterval(() => checkPaymentStatus(false), 3000);

    // Deteksi jika user kembali lewat tombol Back browser dari bfcache
    window.addEventListener('pageshow', function (event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
</script>
@endpush
