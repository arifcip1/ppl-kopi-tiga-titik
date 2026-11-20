@php
    $isOwnerViewer = in_array(auth()->user()->role ?? '', ['admin', 'owner']);
    $isPosOrder = ($order->sumber_transaksi ?? 'online') === 'pos';
@endphp

@extends($isOwnerViewer ? 'layouts.owner' : 'layouts.pelanggan')

@section('title', 'Nota Digital #' . ($order->kode ?? 'Pesanan') . ' - Kopi Tiga Titik')
@section('active_menu', $isOwnerViewer ? 'pos' : 'pesanan-saya')
@section('page_title', $isPosOrder ? 'Nota Transaksi POS Lapak' : 'Nota Digital Pesanan')
@section('role_badge', $isOwnerViewer ? 'OWNER' : 'PELANGGAN')
@section('page_subtitle', 'Bukti transaksi resmi dan rincian pemenuhan pesanan Kopi Tiga Titik')

@section('header_actions')
    <div class="flex items-center gap-1.5 sm:gap-2 print:hidden">
        <!-- Tombol Unduh PDF (Mobile: Icon Button Ringkas 32px, Desktop: Label Lengkap) -->
        <button type="button" 
                id="btn-download-pdf-nav"
                onclick="downloadReceiptPdf()" 
                class="w-8 h-8 sm:w-auto px-0 sm:px-3 py-0 sm:py-1.5 bg-blue-900 hover:bg-blue-800 active:scale-95 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-all shadow-xs cursor-pointer shrink-0" 
                title="Unduh Nota Digital PDF">
            <span class="material-symbols-outlined text-base">file_download</span>
            <span class="hidden sm:inline">Unduh PDF</span>
        </button>

        <!-- Tombol Cetak Nota -->
        <button type="button" 
                onclick="window.print()" 
                class="w-8 h-8 sm:w-auto px-0 sm:px-3 py-0 sm:py-1.5 bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-700 rounded-xl text-xs font-semibold flex items-center justify-center gap-1 transition-all shadow-2xs cursor-pointer shrink-0" 
                title="Cetak Nota">
            <span class="material-symbols-outlined text-base">print</span>
            <span class="hidden sm:inline">Cetak</span>
        </button>

        @if($isPosOrder && $isOwnerViewer)
            <!-- Kembali ke POS Kasir -->
            <a href="{{ route('owner.pos') }}" 
               class="w-8 h-8 sm:w-auto px-0 sm:px-3 py-0 sm:py-1.5 bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-700 rounded-xl text-xs font-semibold flex items-center justify-center gap-1 transition-colors shadow-2xs shrink-0" 
               title="Kembali ke POS Kasir">
                <span class="material-symbols-outlined text-base">point_of_sale</span>
                <span class="hidden sm:inline">Kasir POS</span>
            </a>
        @else
            <!-- Tombol Lacak Live -->
            <a href="{{ route('tracking.detail', $order->kode) }}" 
               class="w-8 h-8 sm:w-auto px-0 sm:px-3 py-0 sm:py-1.5 bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-700 rounded-xl text-xs font-semibold flex items-center justify-center gap-1 transition-colors shadow-2xs shrink-0" 
               title="Pantau Status Pesanan Live">
                <span class="material-symbols-outlined text-base">track_changes</span>
                <span class="hidden sm:inline">Lacak</span>
            </a>
        @endif
    </div>
@endsection

@section('content')

<div class="max-w-2xl mx-auto w-full flex flex-col gap-4 sm:gap-5">

    <!-- Card Nota Digital Utama (Printable) -->
    <div id="printable-receipt" class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-8 shadow-xs border border-slate-200/90 flex flex-col gap-5 sm:gap-6 relative overflow-hidden">
        
        <!-- Watermark / Dekorasi Latar -->
        <div class="absolute -right-8 -top-8 w-36 h-36 bg-blue-50/50 rounded-full pointer-events-none blur-2xl"></div>

        <!-- Header Nota dengan Logo Resmi Kopi Tiga Titik -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 sm:pb-6 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 sm:w-14 sm:h-14 bg-white rounded-2xl border border-slate-200/90 shadow-2xs flex items-center justify-center p-1.5 shrink-0 overflow-hidden">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo Kopi Tiga Titik" class="w-full h-full object-contain" onerror="this.onerror=null; this.src='https://placehold.co/100x100/1e3a8a/ffffff?text=KTT'" />
                </div>
                <div class="min-w-0">
                    <h2 class="text-sm sm:text-base font-black text-slate-900 uppercase tracking-tight font-['Plus_Jakarta_Sans'] truncate sm:whitespace-normal">
                        {{ $sellerStoreName ?? 'Kopi Tiga Titik Mojokerto' }}
                    </h2>
                    <p class="text-[11px] text-slate-500 leading-tight mt-0.5">
                        {{ $sellerStoreAddress ?? 'Jl. Kuwung, Mergelo, Kec. Magersari, Kota Mojokerto' }}
                    </p>
                    <p class="text-[10px] text-slate-400 font-mono mt-0.5">
                        WhatsApp: {{ $sellerWaDisplay ?? '0812-3456-7890' }}
                    </p>
                </div>
            </div>

            <div class="flex flex-row sm:flex-col justify-between sm:items-end items-center pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100/80">
                <div class="text-left sm:text-right">
                    <span class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-slate-400 block">NOMOR NOTA RESMI</span>
                    <span class="text-xs sm:text-base font-extrabold text-blue-900 font-mono tracking-tight block">
                        #{{ $order->kode }}
                    </span>
                </div>
                <span class="text-[10px] text-slate-500 text-right font-medium">
                    {{ $order->created_at ? $order->created_at->translatedFormat('d M Y, H:i') . ' WIB' : now()->translatedFormat('d M Y, H:i') . ' WIB' }}
                </span>
            </div>
        </div>

        <!-- Badge Status Transaksi & Pembayaran -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-200/80">
            <div class="flex items-center gap-2.5 min-w-0">
                <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-lg">verified</span>
                </span>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-bold text-slate-900">Status Pembayaran:</span>
                        <span class="px-2 py-0.5 bg-emerald-600 text-white font-extrabold text-[10px] rounded-md tracking-wider">
                            @if($isPosOrder)
                                {{ ($order->payment?->metode_pembayaran ?? 'tunai') === 'tunai' ? 'LUNAS (TUNAI LAPAK)' : 'LUNAS (QRIS LAPAK)' }}
                            @else
                                {{ $order->payment && $order->payment->isSuccess() ? 'LUNAS (VERIFIKASI QRIS)' : 'TERVERIFIKASI' }}
                            @endif
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-500 truncate mt-0.5">
                        @if($isPosOrder)
                            Ref: {{ $order->kode }} • POS Kasir Offline (Dikonfirmasi Owner)
                        @else
                            Ref: {{ $order->payment?->referensi_provider ?? $order->kode }} • Kanal QRIS Sandbox
                        @endif
                    </p>
                </div>
            </div>

            <div class="sm:text-right shrink-0">
                <span class="text-[10px] text-slate-500 block">Waktu Verifikasi Lunas:</span>
                <span class="text-[11px] font-bold text-slate-800 font-mono">
                    {{ $order->payment?->waktu_verifikasi ? $order->payment->waktu_verifikasi->translatedFormat('d M Y, H:i') . ' WIB' : ($order->created_at ? $order->created_at->translatedFormat('d M Y, H:i') . ' WIB' : 'Otomatis Server') }}
                </span>
            </div>
        </div>

        <!-- Data Pemesan & Rincian Pemenuhan (Pengiriman vs Ambil Sendiri vs POS Lapak) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            
            <!-- Info Pelanggan -->
            <div class="p-4 bg-white rounded-2xl border border-slate-200/80 flex flex-col gap-1.5 shadow-2xs">
                <div class="flex items-center gap-1.5 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                    <span class="material-symbols-outlined text-sm text-blue-900">person</span>
                    <span>Pelanggan / Penerima</span>
                </div>
                <div class="font-bold text-slate-900 text-sm mt-0.5">{{ $order->nama_pelanggan ?? $order->nama_penerima ?? 'Pelanggan Lapak' }}</div>
                @if($order->telepon)
                    <div class="text-slate-600 font-mono text-[11px]">{{ $order->telepon }}</div>
                @elseif($isPosOrder)
                    <div class="text-slate-500 text-[11px]">Pembelian Langsung di Lapak</div>
                @endif
                @if($order->catatan)
                    <div class="mt-1 pt-1 border-t border-slate-100 text-[10px] text-slate-500 italic">
                        "Catatan: {{ $order->catatan }}"
                    </div>
                @endif
            </div>

            <!-- Info Metode Pemenuhan -->
            <div class="p-4 bg-white rounded-2xl border border-slate-200/80 flex flex-col gap-1.5 shadow-2xs">
                <div class="flex items-center gap-1.5 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                    <span class="material-symbols-outlined text-sm text-blue-900">
                        {{ $isPosOrder ? 'point_of_sale' : ($order->metode_pemenuhan === 'ambil_sendiri' ? 'storefront' : 'local_shipping') }}
                    </span>
                    <span>Metode Pemenuhan</span>
                </div>

                @if($isPosOrder)
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="font-bold text-emerald-800 text-xs">Transaksi Langsung di Lapak (POS)</span>
                        <span class="px-1.5 py-0.2 bg-emerald-100 text-emerald-800 font-bold text-[9px] rounded">Selesai</span>
                    </div>
                    <p class="text-[11px] text-slate-600 leading-tight">
                        {{ $order->nama_lokasi_asal ?? ($lokasiAktif->nama_lokasi ?? 'Lapak Kopi Tiga Titik') }}
                        @if($order->alamat_lokasi_asal ?? ($lokasiAktif->alamat ?? null))
                            — {{ $order->alamat_lokasi_asal ?? $lokasiAktif->alamat }}
                        @endif
                    </p>
                @elseif($order->metode_pemenuhan === 'ambil_sendiri')
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="font-bold text-blue-900 text-xs">Ambil Sendiri di Lapak</span>
                        <span class="px-1.5 py-0.2 bg-emerald-100 text-emerald-800 font-bold text-[9px] rounded">Bebas Ongkir</span>
                    </div>
                    <p class="text-[11px] text-slate-600 leading-tight">
                        {{ $order->nama_lokasi_asal ?? 'Lapak Jl. Kuwung' }} ({{ $order->alamat_lokasi_asal ?? 'Jl. Kuwung, Magersari, Mojokerto' }})
                    </p>

                    <!-- Batas Pengambilan 24 Jam (FR-13 / PPL-24) -->
                    <div class="mt-2 p-2 bg-amber-50 rounded-xl border border-amber-200/80 flex flex-col gap-0.5">
                        <span class="text-[10px] font-bold text-amber-900 flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs text-amber-700">timer</span>
                            <span>Batas Waktu Pengambilan (Maks 24 Jam):</span>
                        </span>
                        <span class="text-[11px] font-black text-amber-950 font-mono">
                            @php
                                $pickupDeadline = $order->batas_pengambilan 
                                    ?? ($order->payment?->waktu_verifikasi ? $order->payment->waktu_verifikasi->copy()->addHours(24) : $order->created_at->copy()->addHours(24));
                            @endphp
                            {{ $pickupDeadline->translatedFormat('d F Y, H:i') }} WIB
                        </span>
                        <span class="text-[9px] text-amber-800 mt-0.5">
                            Tunjukkan nomor nota ini ke barista di lapak saat pengambilan pesanan.
                        </span>
                    </div>

                @else
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="font-bold text-purple-900 text-xs">Pengantaran Kurir Toko</span>
                        <span class="px-1.5 py-0.2 bg-purple-100 text-purple-900 font-bold text-[9px] rounded">Delivery</span>
                    </div>
                    <p class="text-[11px] text-slate-600 leading-relaxed">
                        {{ $order->alamat }}
                    </p>
                    <div class="text-[10px] text-slate-500 mt-0.5">
                        Kurir: <span class="font-semibold text-slate-800">{{ $order->courier ? $order->courier->name : 'Kurir Resmi Kopi Tiga Titik' }}</span>
                        @if($order->jarak_pengiriman)
                            • Jarak: <span class="font-semibold text-slate-800">{{ $order->jarak_pengiriman }} km</span>
                        @endif
                    </div>
                @endif
            </div>

        </div>

        <!-- Tabel Item Produk Kopi -->
        <div class="flex flex-col gap-2">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">RINCIAN PESANAN KOPI (250ML)</span>
            <div class="border border-slate-200 rounded-2xl overflow-hidden shadow-2xs overflow-x-auto">
                <table class="w-full text-xs text-left min-w-[300px]">
                    <thead class="bg-slate-50 border-b border-slate-200 text-[10px] font-bold text-slate-600 uppercase">
                        <tr>
                            <th class="py-2.5 px-3">Menu Kopi</th>
                            <th class="py-2.5 px-2 text-center">Qty</th>
                            <th class="py-2.5 px-3 text-right">Harga Satuan</th>
                            <th class="py-2.5 px-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach($order->orderItems as $item)
                            <tr>
                                <td class="py-2.5 sm:py-3 px-3">
                                    <div class="font-bold text-slate-900 text-xs sm:text-sm">{{ $item->nama_produk }}</div>
                                    <div class="text-[10px] text-slate-500">Botol Segar 250ml</div>
                                </td>
                                <td class="py-2.5 sm:py-3 px-2 text-center font-bold text-slate-900">{{ $item->jumlah }}</td>
                                <td class="py-2.5 sm:py-3 px-3 text-right font-mono text-[11px] sm:text-xs">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                <td class="py-2.5 sm:py-3 px-3 text-right font-bold text-slate-900 font-mono text-[11px] sm:text-xs">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Rincian Biaya & Total -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4 pt-2 border-t border-slate-100">
            <div class="text-[10px] text-slate-500 max-w-xs leading-relaxed">
                <p class="font-bold text-slate-700">Terima kasih atas pesananmu!</p>
                <p>Kopi botol 250ml disajikan dingin dan segar. Simpan di lemari pendingin (chiller) untuk ketahanan maksimal.</p>
            </div>

            <div class="w-full sm:w-64 flex flex-col gap-1.5 text-xs text-slate-600 bg-slate-50 sm:bg-transparent p-3.5 sm:p-0 rounded-2xl sm:rounded-none border sm:border-0 border-slate-200/70">
                <div class="flex justify-between">
                    <span>Subtotal Produk:</span>
                    <span class="font-mono font-semibold text-slate-900">{{ $order->subtotal_formatted ?? ('Rp ' . number_format($order->subtotal, 0, ',', '.')) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Ongkos Kirim:</span>
                    <span class="font-mono font-semibold {{ $order->ongkir == 0 ? 'text-emerald-700' : 'text-slate-900' }}">
                        {{ $order->ongkir == 0 ? 'Bebas Ongkir (Rp 0)' : ('Rp ' . number_format($order->ongkir, 0, ',', '.')) }}
                    </span>
                </div>
                <div class="border-t border-slate-200 pt-2 mt-1 flex justify-between items-center text-sm">
                    <span class="font-extrabold text-slate-900">Total Pembayaran:</span>
                    <span class="font-black text-blue-900 font-['Plus_Jakarta_Sans'] text-base">
                        {{ $order->total_formatted ?? ('Rp ' . number_format($order->total, 0, ',', '.')) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Footer Nota & Barcode Digital -->
        <div class="pt-4 border-t border-dashed border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-center sm:text-left text-[10px] text-slate-400">
            <div>
                <span>ID Transaksi Sistem: </span>
                <span class="font-mono font-bold text-slate-600">{{ $order->kode }}-{{ $order->id }}</span>
                <span class="block">Kopi Tiga Titik Mojokerto • Dokumen Digital Sah</span>
            </div>
            <div class="flex items-center gap-1 font-mono text-[9px] bg-slate-50 px-2 py-1 rounded border border-slate-200">
                <span class="material-symbols-outlined text-xs text-emerald-600">check_circle</span>
                <span>AUTHENTICATED RECEIPT</span>
            </div>
        </div>

    </div>

    <!-- Baris Tombol Aksi Unduh PDF, Gambar PNG & Cetak di Bawah Struk (Mobile & Desktop Friendly) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 print:hidden">
        <!-- 1. Unduh File PDF Resmi A4 -->
        <button type="button" 
                onclick="downloadReceiptPdf()" 
                id="btn-download-pdf-bottom"
                class="py-3 px-3 bg-blue-900 hover:bg-blue-800 active:scale-98 text-white rounded-2xl text-xs font-bold flex items-center justify-center gap-2 transition-all shadow-xs cursor-pointer">
            <span class="material-symbols-outlined text-base">picture_as_pdf</span>
            <span id="btn-download-text-bottom">Unduh File PDF</span>
        </button>

        <!-- 2. Unduh Gambar Struk (PNG) untuk Kirim Cepat / Galeri HP -->
        <button type="button" 
                onclick="downloadReceiptPng()" 
                id="btn-download-png-bottom"
                class="py-3 px-3 bg-indigo-50 hover:bg-indigo-100 text-indigo-900 border border-indigo-200 active:scale-98 rounded-2xl text-xs font-bold flex items-center justify-center gap-2 transition-all shadow-2xs cursor-pointer">
            <span class="material-symbols-outlined text-base text-indigo-700">image</span>
            <span id="btn-download-png-text">Unduh Gambar (PNG)</span>
        </button>

        <!-- 3. Cetak Nota / Thermal Print -->
        <button type="button" 
                onclick="window.print()" 
                class="py-3 px-3 bg-white hover:bg-slate-50 active:scale-98 text-slate-700 border border-slate-300 rounded-2xl text-xs font-bold flex items-center justify-center gap-2 transition-all shadow-2xs cursor-pointer">
            <span class="material-symbols-outlined text-base text-slate-500">print</span>
            <span>Cetak Nota</span>
        </button>
    </div>

    <!-- Tombol Aksi Hubungi Penjual & Navigasi (FR-13 / PPL-24) -->
    <div class="flex flex-col sm:flex-row gap-2.5 sm:gap-3 print:hidden">
        
        <!-- Tombol Hubungi Penjual (WhatsApp Resmi) -->
        @php
            $waMessage = "Halo Admin Kopi Tiga Titik, saya ingin konfirmasi pesanan dengan nomor Nota: #" . ($order->kode ?? '') . " atas nama " . ($order->nama_penerima ?? '') . ".";
            $waUrl = "https://wa.me/" . preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $sellerWa ?? '6281234567890')) . "?text=" . urlencode($waMessage);
        @endphp

        <a href="{{ $waUrl }}" 
           target="_blank" 
           rel="noopener noreferrer"
           class="w-full sm:flex-1 py-3 px-4 bg-emerald-600 hover:bg-emerald-700 active:scale-98 text-white rounded-2xl text-xs font-bold flex items-center justify-center gap-2 transition-all shadow-xs">
            <span class="material-symbols-outlined text-base">chat</span>
            <span>Hubungi Penjual via WhatsApp</span>
        </a>

        <a href="{{ route('tracking.detail', $order->kode) }}" 
           class="w-full sm:flex-1 py-3 px-4 bg-blue-900/10 hover:bg-blue-900/20 text-blue-900 active:scale-98 rounded-2xl text-xs font-bold flex items-center justify-center gap-2 transition-all shadow-2xs">
            <span class="material-symbols-outlined text-base">track_changes</span>
            <span>Pantau Status Live</span>
        </a>

        <a href="{{ route('orders') }}" 
           class="w-full sm:w-auto py-3 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-2xl text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors">
            <span class="material-symbols-outlined text-base">receipt_long</span>
            <span class="inline">Daftar Pesanan</span>
        </a>

    </div>

</div>

@endsection

@push('styles')
<style>
/* Fix container html2pdf agar koordinat rendering selalu tepat di x=0 tanpa terpotong di layar mobile maupun desktop */
.html2pdf__overlay {
    position: fixed !important;
    left: 0 !important;
    top: 0 !important;
    right: auto !important;
    bottom: auto !important;
    width: 720px !important;
    margin: 0 !important;
    padding: 0 !important;
    z-index: 9999 !important;
    background: transparent !important;
}
.html2pdf__container {
    position: absolute !important;
    left: 0 !important;
    top: 0 !important;
    right: auto !important;
    bottom: auto !important;
    width: 720px !important;
    max-width: 720px !important;
    margin: 0 !important;
    padding: 0 !important;
    box-sizing: border-box !important;
    background: #ffffff !important;
}

@media print {
    /* Sembunyikan seluruh navigasi web dan sidebar saat dicetak */
    nav, header, footer, aside, #mainSidebar, #mobileBackdrop, .print\:hidden, #header-container, #global-toast-container {
        display: none !important;
    }
    body, #contentWrapper, main {
        background: #ffffff !important;
        margin: 0 !important;
        padding: 0 !important;
        padding-left: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
    }
    #printable-receipt {
        border: none !important;
        box-shadow: none !important;
        padding: 12px 16px !important;
        max-width: 100% !important;
        width: 100% !important;
    }
    /* Sembunyikan watermark saat dicetak */
    .blur-2xl {
        display: none !important;
    }
}
</style>
@endpush

@push('scripts')
<!-- Library html2pdf.bundle.min.js lokal untuk generate & download PDF instan tanpa dependensi internet -->
<script src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
<script>
    /**
     * Fungsi Helper Pembuat Klon Struk yang Terstandarisasi (720px)
     * Menghasilkan struktur A4 rapi tanpa terpotong oleh margin sidebar desktop atau viewport mobile
     */
    function createReceiptStandardClone() {
        const receipt = document.getElementById('printable-receipt');
        if (!receipt) return null;

        const clone = receipt.cloneNode(true);
        clone.style.width = '720px';
        clone.style.maxWidth = '720px';
        clone.style.minWidth = '720px';
        clone.style.margin = '0';
        clone.style.padding = '28px';
        clone.style.boxSizing = 'border-box';
        clone.style.boxShadow = 'none';
        clone.style.border = '1px solid #e2e8f0';
        clone.style.borderRadius = '16px';
        clone.style.backgroundColor = '#ffffff';

        // Hapus elemen blur dekoratif agar tidak menimbulkan glitch visual pada canvas
        const blurDecorations = clone.querySelectorAll('.blur-2xl');
        blurDecorations.forEach(el => el.remove());

        return clone;
    }

    /**
     * Unduh Nota Digital dalam format Dokumen PDF Resmi A4
     */
    function downloadReceiptPdf() {
        const btnNav = document.getElementById('btn-download-pdf-nav');
        const btnBottom = document.getElementById('btn-download-pdf-bottom');
        const textBottom = document.getElementById('btn-download-text-bottom');

        const clone = createReceiptStandardClone();
        if (!clone) {
            window.print();
            return;
        }

        // Tampilkan indikator proses unduh
        if (btnNav) btnNav.disabled = true;
        if (btnBottom) btnBottom.disabled = true;
        if (textBottom) textBottom.textContent = 'Membuat Dokumen PDF...';

        const orderKode = "{{ $order->kode ?? 'Pesanan' }}";
        const filename = 'Nota-Kopi-Tiga-Titik-' + orderKode + '.pdf';

        const opt = {
            margin: [8, 8, 8, 8],
            filename: filename,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { 
                scale: 2, 
                useCORS: true, 
                logging: false,
                scrollX: 0,
                scrollY: 0,
                x: 0,
                y: 0,
                width: 720,
                windowWidth: 720
            },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };

        const cleanupAndReset = () => {
            if (btnNav) btnNav.disabled = false;
            if (btnBottom) btnBottom.disabled = false;
            if (textBottom) textBottom.textContent = 'Unduh File PDF';
        };

        if (typeof html2pdf !== 'undefined') {
            html2pdf().set(opt).from(clone).save().then(() => {
                cleanupAndReset();
            }).catch(err => {
                console.error('PDF export error:', err);
                cleanupAndReset();
                window.print();
            });
        } else {
            cleanupAndReset();
            window.print();
        }
    }

    /**
     * Unduh Nota Digital dalam format Gambar PNG Jernih (Sangat cocok untuk mobile / share ke WhatsApp)
     */
    function downloadReceiptPng() {
        const btnPng = document.getElementById('btn-download-png-bottom');
        const textPng = document.getElementById('btn-download-png-text');

        const clone = createReceiptStandardClone();
        if (!clone) return;

        if (btnPng) btnPng.disabled = true;
        if (textPng) textPng.textContent = 'Membuat Gambar...';

        const opt = {
            margin: [0, 0, 0, 0],
            html2canvas: { 
                scale: 2, 
                useCORS: true, 
                logging: false,
                scrollX: 0,
                scrollY: 0,
                x: 0,
                y: 0,
                width: 720,
                windowWidth: 720
            }
        };

        const cleanupPng = () => {
            if (btnPng) btnPng.disabled = false;
            if (textPng) textPng.textContent = 'Unduh Gambar (PNG)';
        };

        if (typeof html2pdf !== 'undefined') {
            html2pdf().set(opt).from(clone).toCanvas().get('canvas').then(canvas => {
                const dataUrl = canvas.toDataURL('image/png');
                const a = document.createElement('a');
                a.download = 'Nota-Kopi-Tiga-Titik-{{ $order->kode ?? 'Pesanan' }}.png';
                a.href = dataUrl;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                cleanupPng();
            }).catch(err => {
                console.error('PNG export error:', err);
                cleanupPng();
            });
        } else {
            cleanupPng();
        }
    }
</script>
@endpush
