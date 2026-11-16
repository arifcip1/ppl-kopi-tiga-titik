<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pembayaran QRIS ({{ $order->kode ?? 'Pesanan' }}) - Kopi Tiga Titik</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Tailwind CSS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-screen flex justify-center items-start py-4 sm:py-6 antialiased font-sans">
<div class="w-full max-w-md min-h-screen bg-slate-50 shadow-2xl relative flex flex-col justify-start pb-12 sm:rounded-3xl overflow-hidden border border-slate-200">

    <!-- Top Header -->
    <div class="bg-blue-900 text-white px-4 py-3.5 shadow-md flex items-center justify-between sticky top-0 z-30">
        <a href="{{ url('/checkout') }}" class="w-9 h-9 rounded-full hover:bg-white/10 flex items-center justify-center transition-colors cursor-pointer" title="Kembali">
            <span class="material-symbols-outlined text-xl">arrow_back</span>
        </a>
        <div class="text-center">
            <h1 class="text-base font-bold font-['Plus_Jakarta_Sans'] leading-tight">Pembayaran QRIS</h1>
            <p class="text-[11px] text-blue-200 leading-none mt-0.5">
                {{ $order->nama_lokasi_asal ?? $lokasiLapak->nama_lokasi ?? 'Lapak Jl. Kuwung' }} • Mojokerto
            </p>
        </div>
        <a href="https://wa.me/6281234567890?text={{ urlencode('Halo Admin Kopi Tiga Titik, saya butuh bantuan untuk pesanan ' . ($order->kode ?? '')) }}" target="_blank" class="w-9 h-9 rounded-full hover:bg-white/10 flex items-center justify-center transition-colors cursor-pointer" title="Bantuan WhatsApp">
            <span class="material-symbols-outlined text-xl">help</span>
        </a>
    </div>

    <!-- Main Content -->
    <div class="px-4 pt-3.5 flex flex-col gap-3.5">

        <!-- Countdown Timer Banner (10 Menit TTL - FR-6.8) -->
        <div id="timer-banner" class="p-3.5 bg-rose-50 rounded-2xl border border-rose-200 flex flex-col gap-2 transition-all">
            <div class="flex justify-between items-center text-xs">
                <div class="flex items-center gap-1.5 font-bold text-rose-900">
                    <span class="material-symbols-outlined text-rose-600 text-base animate-pulse">timer</span>
                    <span>Bayar Sebelum Habis</span>
                </div>
                <span id="countdown-display" class="px-2.5 py-0.5 bg-rose-700 text-white font-mono font-bold text-xs rounded-full shadow-2xs">
                    --:--
                </span>
            </div>
            <p class="text-[11px] text-rose-700 leading-tight">
                Pesanan otomatis dibatalkan jika melebihi batas waktu pembayaran 10 menit.
            </p>
            <div class="w-full h-1.5 bg-rose-200 rounded-full overflow-hidden">
                <div id="countdown-progress" class="h-full bg-rose-600 rounded-full transition-all duration-1000" style="width: 100%;"></div>
            </div>
        </div>

        <!-- Total yang Harus Dibayar Card -->
        <div class="p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex flex-col gap-2">
            <div class="flex justify-between items-center">
                <div>
                    <div class="text-[11px] text-slate-500 font-medium">Total Tagihan ({{ $order->kode ?? 'Pesanan' }})</div>
                    <div class="text-2xl font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">
                        Rp {{ number_format($order->total ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <button type="button" onclick="copyNominal({{ $order->total ?? 0 }})" class="px-3 py-1.5 bg-blue-50 text-blue-900 hover:bg-blue-100 active:scale-95 rounded-xl font-bold text-xs flex items-center gap-1 border border-blue-200 transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-sm">content_copy</span>
                    <span id="btn-copy-text">Salin</span>
                </button>
            </div>
            <div class="pt-2 border-t border-slate-100 flex items-center gap-1.5 text-[11px] text-slate-500">
                <span class="material-symbols-outlined text-xs text-blue-900">info</span>
                <span>Pastikan transfer tepat sesuai nominal termasuk ongkos kirim.</span>
            </div>
        </div>

        <!-- QRIS Frame Card -->
        <div class="p-5 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex flex-col items-center gap-3 text-center">
            <div class="w-full flex justify-between items-center border-b border-slate-100 pb-2.5">
                <div class="flex items-center gap-1.5">
                    <span class="text-base font-black text-slate-900 tracking-tight">QRIS</span>
                    <span class="px-1.5 py-0.2 bg-red-600 text-white text-[8px] font-bold rounded">SANDBOX</span>
                </div>
                <div class="flex items-center gap-1 text-[10px] font-bold text-slate-600">
                    <span class="w-4 h-4 rounded-full border border-blue-900 text-blue-900 flex items-center justify-center text-[8px] font-black">G</span>
                    <span>GPN / MIDTRANS</span>
                </div>
            </div>

            <div>
                <h3 class="text-sm font-bold text-slate-900 uppercase font-['Plus_Jakarta_Sans']">KOPI TIGA TITIK MOJOKERTO</h3>
                <p class="text-[11px] text-slate-500 font-mono mt-0.5">Order Ref: {{ $order->kode ?? 'KTT' }}</p>
            </div>

            <!-- Stylized QR Code Box -->
            <div class="w-64 h-64 bg-white border-2 border-slate-900/10 rounded-2xl p-3 shadow-inner flex flex-col items-center justify-center relative overflow-hidden group">
                @php
                    $qrCodeUrl = $qrisData['qr_url'] ?? null;
                    $qrString = $qrisData['qr_string'] ?? ($order->kode ?? 'KTT-QRIS');
                    $fallbackQrApi = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode($qrString);
                @endphp

                @if(!empty($qrCodeUrl))
                    <img src="{{ $qrCodeUrl }}" alt="QRIS Code Midtrans" class="w-full h-full object-contain" onerror="this.src='{{ $fallbackQrApi }}'" />
                @else
                    <img src="{{ $fallbackQrApi }}" alt="QRIS Code Midtrans" class="w-full h-full object-contain" />
                @endif
            </div>

            <p class="text-xs text-slate-500 leading-tight">
                Scan QRIS menggunakan BCA, Mandiri, GoPay, OVO, ShopeePay, Dana, atau mobile banking apa pun
            </p>

            <!-- Payment Providers Badges -->
            <div class="flex flex-wrap justify-center gap-1.5 pt-1">
                <span class="px-2 py-0.5 bg-blue-50 text-blue-900 rounded text-[10px] font-bold border border-blue-100">BCA</span>
                <span class="px-2 py-0.5 bg-blue-50 text-blue-900 rounded text-[10px] font-bold border border-blue-100">MANDIRI</span>
                <span class="px-2 py-0.5 bg-blue-50 text-blue-900 rounded text-[10px] font-bold border border-blue-100">GOPAY</span>
                <span class="px-2 py-0.5 bg-blue-50 text-blue-900 rounded text-[10px] font-bold border border-blue-100">OVO</span>
                <span class="px-2 py-0.5 bg-blue-50 text-blue-900 rounded text-[10px] font-bold border border-blue-100">DANA</span>
                <span class="px-2 py-0.5 bg-rose-50 text-rose-700 rounded text-[10px] font-bold border border-rose-100">SHOPEEPAY</span>
            </div>
        </div>

        <!-- Collapsible Order Details -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
            <button type="button" onclick="toggleDetails()" class="w-full p-3.5 flex justify-between items-center text-xs font-bold text-slate-800 hover:bg-slate-50 transition-colors cursor-pointer">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-blue-900 text-base">shopping_bag</span>
                    <span>Rincian Pesanan ({{ $order->orderItems->sum('jumlah') ?? 0 }} Botol)</span>
                </div>
                <span id="details-arrow" class="material-symbols-outlined text-slate-400 text-sm transition-transform duration-200">expand_more</span>
            </button>

            <div id="details-content" class="px-4 pb-3.5 pt-1 border-t border-slate-100 hidden flex flex-col gap-2 text-xs">
                @foreach($order->orderItems as $item)
                    <div class="flex justify-between items-center text-slate-700">
                        <span>{{ $item->nama_produk }} x {{ $item->jumlah }}</span>
                        <span class="font-semibold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                @endforeach
                @if($order->ongkir > 0)
                    <div class="flex justify-between items-center text-slate-700 border-t border-dashed border-slate-100 pt-1.5">
                        <span>Ongkos Kirim (Delivery)</span>
                        <span class="font-semibold">Rp {{ number_format($order->ongkir, 0, ',', '.') }}</span>
                    </div>
                @else
                    <div class="flex justify-between items-center text-emerald-700 border-t border-dashed border-slate-100 pt-1.5">
                        <span>Ongkos Kirim</span>
                        <span class="font-semibold">Gratis (Rp 0)</span>
                    </div>
                @endif
                <div class="flex justify-between items-center text-slate-900 font-extrabold border-t border-slate-200 pt-2">
                    <span>Total Pembayaran</span>
                    <span class="text-blue-900">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Submit Button / Check Status -->
        <button type="button" 
                id="btn-check-payment" 
                onclick="checkPaymentStatus(true)" 
                class="w-full py-3.5 bg-blue-900 hover:bg-blue-800 active:scale-98 text-white rounded-2xl font-bold text-xs flex justify-center items-center gap-2 shadow-md transition-all cursor-pointer">
            <span id="btn-check-icon" class="material-symbols-outlined text-base">sync</span>
            <span id="btn-check-text">Saya Sudah Membayar (Cek Status)</span>
        </button>

        <!-- Tombol Pengujian Sandbox (Simulator Resmi Midtrans & Simulasi Instan) -->
        @if(!config('services.midtrans.is_production', false))
            <div class="p-3.5 bg-amber-50/90 rounded-2xl border border-amber-200 text-center flex flex-col gap-2">
                <div class="flex items-center justify-center gap-1.5 text-amber-900 font-bold text-xs">
                    <span class="material-symbols-outlined text-base text-amber-700">science</span>
                    <span>Mode Pengujian Midtrans Sandbox</span>
                </div>
                <p class="text-[11px] text-amber-800 leading-relaxed">
                    Uji pembayaran melalui <strong>Simulator QRIS Resmi Midtrans</strong> (tempel URL QRIS yang tersalin otomatis) atau gunakan tombol verifikasi instan:
                </p>

                @if(!empty($qrCodeUrl))
                    <button type="button"
                            onclick="copyQrUrlAndOpenSimulator('{{ $qrCodeUrl }}')"
                            class="w-full py-2.5 bg-white hover:bg-blue-50 text-blue-900 border border-blue-300 rounded-xl font-bold text-xs flex justify-center items-center gap-1.5 shadow-2xs transition-all cursor-pointer">
                        <span class="material-symbols-outlined text-sm">open_in_new</span>
                        <span id="btn-open-simulator-text">Salin URL QRIS & Buka Simulator Midtrans</span>
                    </button>
                @endif

                <button type="button" 
                        id="btn-simulate-payment" 
                        onclick="simulateSuccessPayment()" 
                        class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-98 text-white rounded-xl font-bold text-xs flex justify-center items-center gap-1.5 shadow-xs transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-sm">bolt</span>
                    <span id="btn-simulate-text">Simulasikan Pembayaran Berhasil (Lunas)</span>
                </button>
            </div>
        @endif

        <!-- WhatsApp Help Link (Hubungi Penjual - FR-13) -->
        <div class="text-center text-xs text-slate-500 flex items-center justify-center gap-1.5 pt-1">
            <span class="material-symbols-outlined text-sm text-emerald-600">chat</span>
            <span>Butuh bantuan?</span>
            <a href="https://wa.me/6281234567890?text={{ urlencode('Halo Admin Kopi Tiga Titik, saya ingin konfirmasi pembayaran pesanan: ' . ($order->kode ?? '')) }}" target="_blank" class="font-bold text-blue-900 hover:underline inline-flex items-center gap-0.5">
                <span>WhatsApp Lapak (0812-3456-7890)</span>
                <span class="material-symbols-outlined text-[14px]">open_in_new</span>
            </a>
        </div>

    </div>

</div>

<script>
    const orderKode = "{{ $order->kode ?? '' }}";
    const orderId = "{{ $order->id ?? '' }}";
    let remainingSeconds = parseInt("{{ $remainingSeconds ?? 600 }}", 10);
    const totalTtlSeconds = 600; // 10 menit
    let pollInterval = null;

    // Salin URL Gambar QRIS Midtrans & Buka Halaman Simulator Resmi Midtrans
    function copyQrUrlAndOpenSimulator(qrUrl) {
        const label = document.getElementById('btn-open-simulator-text');
        if (navigator.clipboard && qrUrl) {
            navigator.clipboard.writeText(qrUrl).then(() => {
                if (label) {
                    label.textContent = 'URL QRIS Tersalin! Membuka Simulator...';
                    setTimeout(() => {
                        label.textContent = 'Salin URL QRIS & Buka Simulator Midtrans';
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
                    window.location.href = `/order-tracking/${orderKode || orderId}`;
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
                    window.location.href = json.redirect_url || `/order-tracking/${targetIdentifier}`;
                }, 800);
            } else {
                alert(json.message || 'Gagal memproses simulasi.');
                if (btn) btn.disabled = false;
                if (btnText) btnText.textContent = 'Simulasikan Pembayaran Berhasil (Lunas)';
            }
        } catch (err) {
            alert('Terjadi kesalahan saat memproses simulasi.');
            if (btn) btn.disabled = false;
            if (btnText) btnText.textContent = 'Simulasikan Pembayaran Berhasil (Lunas)';
        }
    }

    // Inisialisasi Timer dan Polling Realtime setiap 3 detik
    updateCountdown();
    setInterval(updateCountdown, 1000);
    pollInterval = setInterval(() => checkPaymentStatus(false), 3000);
</script>
</body>
</html>
