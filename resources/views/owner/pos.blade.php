@extends('layouts.owner')

@section('title', 'POS Kasir Lapak - Kopi Tiga Titik')
@section('page_title', 'POS Kasir Lapak')
@section('page_subtitle', 'Pencatatan transaksi langsung di lapak fisik (Metode Tunai & QRIS Langsung)')
@section('active_menu', 'pos')

@section('header_actions')
    <!-- Tombol Cepat Reset Form Transaksi Kasir -->
    <!-- <button type="button" onclick="resetCartConfirm()" class="px-3 py-1.5 bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-700 text-xs font-semibold rounded-xl inline-flex items-center gap-1.5 transition-colors cursor-pointer" title="Reset Keranjang Kasir">
        <span class="material-symbols-outlined text-base">restart_alt</span>
        <span>Reset Kasir</span>
    </button> -->
@endsection

@section('content')
<div class="flex flex-col gap-4">

    <!-- Flash Message Global -->
    <x-flash-message />

    <!-- ============================================================== -->
    <!-- 1. WIDGET ATURAN RULE-BASED PERINGATAN RESTOK (F10 / BR-10)     -->
    <!-- ============================================================== -->
    @php
        $lowStockProducts = $products->filter(fn($p) => $p->stok <= 5);
        $standardStockTarget = 10; // Nilai acuan standar stok harian per varian (settings.stok_harian_standar)
    @endphp

    <div id="restockWarningContainer" class="{{ $lowStockProducts->isEmpty() ? 'hidden' : '' }}">
        <div class="p-4 bg-amber-50/90 border border-amber-200/90 rounded-2xl shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center shrink-0 mt-0.5">
                    <span class="material-symbols-outlined text-xl">warning</span>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h4 class="text-xs font-bold text-amber-950 font-['Plus_Jakarta_Sans']">Peringatan Restok (Aturan BR-10 & BR-19)</h4>
                        <span class="px-2 py-0.2 rounded-full text-[10px] font-extrabold bg-amber-200/80 text-amber-900 uppercase">
                            Stok &le; 5 Botol
                        </span>
                    </div>
                    <p class="text-[11px] text-amber-800 mt-0.5 leading-relaxed">
                        Terdapat <strong id="restockCountText">{{ $lowStockProducts->count() }} varian kopi</strong> dengan persediaan menipis di cooler box lapak. Sistem menyarankan produksi tambahan harian:
                    </p>
                </div>
            </div>

            <!-- Daftar Pill Varian Kritis & Saran Produksi -->
            <div id="restockPillsContainer" class="flex items-center gap-1.5 flex-wrap shrink-0">
                @foreach($lowStockProducts as $lp)
                    @php
                        $saranProduksi = max(0, $standardStockTarget - (int) $lp->stok);
                    @endphp
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-[11px] font-bold {{ $lp->stok == 0 ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'bg-white text-amber-900 border border-amber-200/80 shadow-2xs' }}">
                        <span>{{ $lp->nama }}</span>
                        <span class="font-mono text-[10px] {{ $lp->stok == 0 ? 'text-rose-950' : 'text-slate-500' }}">({{ $lp->stok }} btl)</span>
                        <span class="text-[10px] text-blue-900 bg-blue-50 px-1 py-0.2 rounded-md font-extrabold">+{{ $saranProduksi }}</span>
                    </span>
                @endforeach
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- 2. KONTEN UTAMA: KATALOG LAPAK (KIRI) & PANEL KERANJANG (KANAN) -->
    <!-- ============================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

        <!-- ==================== KOLOM KIRI: MENU LAPAK ==================== -->
        <div class="lg:col-span-7 xl:col-span-8 flex flex-col gap-4">
            
            <!-- Toolbar Pencarian & Filter Produk Lapak -->
            <div class="bg-white p-3.5 rounded-2xl border border-slate-200/90 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="relative flex-1">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                    <input 
                        type="text" 
                        id="searchPosInput" 
                        oninput="filterPosProducts()" 
                        placeholder="Cari varian kopi lapak (cth: Titik Temu, Aren, Americano)..." 
                        class="w-full pl-9 pr-8 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-900/20 focus:border-blue-900 transition-all"
                    />
                    <button 
                        type="button" 
                        id="clearSearchBtn" 
                        onclick="clearSearch()" 
                        class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-0.5 rounded-full"
                    >
                        <span class="material-symbols-outlined text-sm block">close</span>
                    </button>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-xs text-slate-500 font-medium">Tersedia:</span>
                    <span class="px-2.5 py-1 bg-blue-50 text-blue-900 rounded-xl text-xs font-extrabold">
                        {{ $products->count() }} Menu Aktif
                    </span>
                </div>
            </div>

            <!-- Grid Kartu Menu Kopi Lapak -->
            <div id="productGrid" class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3.5">
                @forelse($products as $product)
                    @php
                        $isOutOfStock = ((int) $product->stok === 0);
                        $isLowStock   = ((int) $product->stok > 0 && (int) $product->stok <= 5);
                    @endphp

                    <div 
                        id="product-card-{{ $product->id }}"
                        class="product-pos-card bg-white rounded-2xl border transition-all flex flex-col justify-between overflow-hidden p-3.5 shadow-2xs relative
                            {{ $isOutOfStock ? 'opacity-60 border-slate-200 bg-slate-50/60' : 'border-slate-200/90 hover:border-blue-900/30 hover:shadow-xs' }}"
                        data-id="{{ $product->id }}"
                        data-name="{{ strtolower($product->nama . ' ' . ($product->rasa ?? '')) }}"
                        data-price="{{ $product->harga }}"
                        data-stock="{{ $product->stok }}"
                    >
                        <!-- Visual Header: Badge Stok F10 & Foto/Ikon -->
                        <div>
                            <div class="flex items-center justify-between gap-1 mb-2">
                                <div id="stock-badge-container-{{ $product->id }}">
                                    @if($isOutOfStock)
                                        <span class="px-2 py-0.5 bg-slate-200 text-slate-700 font-extrabold text-[10px] rounded-md uppercase">
                                            Habis
                                        </span>
                                    @elseif($isLowStock)
                                        <span class="px-2 py-0.5 bg-rose-100 text-rose-800 font-extrabold text-[10px] rounded-md flex items-center gap-0.5" title="Stok kritis &le; 5 botol">
                                            <span class="material-symbols-outlined text-[11px]">warning</span>
                                            <span>Sisa {{ $product->stok }} btl</span>
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 font-bold text-[10px] rounded-md">
                                            Stok: {{ $product->stok }} btl
                                        </span>
                                    @endif
                                </div>

                                <span class="text-[10px] font-semibold text-slate-400">250ml</span>
                            </div>

                            <!-- Thumbnail Produk (Foto / Fallback Ikon Kopi) -->
                            <div class="w-full h-24 rounded-xl bg-slate-100/80 border border-slate-100 flex items-center justify-center overflow-hidden mb-2.5">
                                @if(!empty($product->foto_path))
                                    <img src="{{ asset('storage/' . $product->foto_path) }}" alt="{{ $product->nama }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-900 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-2xl">local_cafe</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Nama & Deskripsi Singkat Varian -->
                            <h5 class="text-xs font-bold text-slate-900 font-['Plus_Jakarta_Sans'] line-clamp-1">
                                {{ $product->nama }}
                            </h5>
                            <p class="text-[11px] text-slate-500 line-clamp-1 mt-0.5">
                                {{ $product->rasa ?? 'Kopi Botol Segar' }}
                            </p>
                        </div>

                        <!-- Harga & Tombol Tambah ke Keranjang Kasir -->
                        <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between gap-1">
                            <div>
                                <span class="text-[10px] text-slate-400 block font-medium">Harga</span>
                                <span class="text-xs font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">
                                    Rp {{ number_format($product->harga, 0, ',', '.') }}
                                </span>
                            </div>

                            <div id="product-action-container-{{ $product->id }}">
                                @if($isOutOfStock)
                                    <button 
                                        type="button" 
                                        disabled 
                                        class="px-2.5 py-1.5 bg-slate-100 text-slate-400 rounded-xl text-xs font-bold cursor-not-allowed"
                                    >
                                        Habis
                                    </button>
                                @else
                                    <button 
                                        type="button" 
                                        onclick="addToCart({{ $product->id }}, '{{ addslashes($product->nama) }}', '{{ addslashes($product->rasa ?? '') }}', {{ $product->harga }}, {{ $product->stok }})"
                                        class="px-3 py-1.5 bg-blue-900 hover:bg-blue-800 text-white rounded-xl text-xs font-bold flex items-center gap-1 shadow-2xs active:scale-95 transition-all cursor-pointer"
                                    >
                                        <span class="material-symbols-outlined text-sm font-bold">add</span>
                                        <span>Pilih</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 text-center text-slate-400 bg-white rounded-2xl border border-slate-200/90">
                        <span class="material-symbols-outlined text-3xl text-slate-300 block mb-1">inventory_2</span>
                        <span>Belum ada menu produk kopi aktif di katalog.</span>
                    </div>
                @endforelse
            </div>

            <!-- Empty Search State -->
            <div id="emptySearchState" class="hidden py-12 text-center text-slate-400 bg-white rounded-2xl border border-slate-200/90">
                <span class="material-symbols-outlined text-3xl text-slate-300 block mb-1">search_off</span>
                <span>Tidak ada menu yang sesuai dengan kata kunci pencarian.</span>
            </div>

        </div>

        <!-- ==================== KOLOM KANAN: PANEL KERANJANG KASIR ==================== -->
        <div class="lg:col-span-5 xl:col-span-4 sticky top-20 flex flex-col gap-3">
            
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-4 flex flex-col gap-4">
                
                <!-- Header Panel Kasir -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-900 flex items-center justify-center">
                            <span class="material-symbols-outlined text-lg">shopping_basket</span>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Keranjang Pesanan</h4>
                            <span id="cartCountBadge" class="text-[10px] text-slate-500 font-medium">0 item dipilih</span>
                        </div>
                    </div>

                    <button 
                        type="button" 
                        onclick="clearCart()" 
                        class="text-[11px] text-rose-600 hover:text-rose-800 font-bold flex items-center gap-0.5 transition-colors cursor-pointer"
                        title="Kosongkan Semua Item"
                    >
                        <span class="material-symbols-outlined text-sm">delete</span>
                        <span>Kosongkan</span>
                    </button>
                </div>

                <!-- Input Nama Pelanggan (Opsional POS) -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                        Nama Pelanggan <span class="font-normal text-slate-400">(Opsional Lapak)</span>
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-base">person</span>
                        <input 
                            type="text" 
                            id="customerNameInput" 
                            placeholder="Contoh: Mas Nabil / Pelanggan Lapak" 
                            value="Pelanggan Lapak"
                            class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-900/20 focus:border-blue-900 transition-all font-medium"
                        />
                    </div>
                </div>

                <!-- List Item Keranjang (Scrollable) -->
                <div class="flex flex-col gap-2">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Daftar Item</span>
                    
                    <div id="cartItemsContainer" class="flex flex-col gap-2 max-h-56 overflow-y-auto pr-1">
                        <!-- Rendered by JavaScript -->
                    </div>

                    <!-- Empty State Keranjang -->
                    <div id="cartEmptyState" class="py-8 text-center text-slate-400 bg-slate-50/70 rounded-xl border border-dashed border-slate-200">
                        <span class="material-symbols-outlined text-2xl text-slate-300 block mb-1">point_of_sale</span>
                        <span class="text-xs">Keranjang kasir masih kosong.<br>Pilih menu varian kopi di sebelah kiri.</span>
                    </div>
                </div>

                <!-- Ringkasan Biaya -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex flex-col gap-1.5">
                    <div class="flex items-center justify-between text-xs text-slate-600">
                        <span>Total Kuantitas:</span>
                        <span id="totalQtyText" class="font-bold text-slate-900">0 Botol</span>
                    </div>
                    <div class="flex items-center justify-between pt-1.5 border-t border-slate-200/80">
                        <span class="text-xs font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Total Bayar:</span>
                        <span id="totalPriceText" class="text-base font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">Rp 0</span>
                    </div>
                </div>

                <!-- Pilihan Metode Pembayaran POS (Tunai vs QRIS Lapak Langsung) -->
                <div class="flex flex-col gap-2 pt-1 border-t border-slate-100">
                    <label class="block text-[11px] font-bold text-slate-700">Metode Pembayaran Lapak :</label>
                    
                    <div class="grid grid-cols-2 gap-2">
                        <button 
                            type="button" 
                            id="btnMethodCash" 
                            onclick="setPaymentMethod('tunai')" 
                            class="py-2 px-3 rounded-xl border text-xs font-bold flex items-center justify-center gap-1.5 transition-all cursor-pointer bg-blue-900 text-white border-blue-900 shadow-2xs"
                        >
                            <span class="material-symbols-outlined text-base">payments</span>
                            <span>Tunai (Cash)</span>
                        </button>

                        <button 
                            type="button" 
                            id="btnMethodQris" 
                            onclick="setPaymentMethod('qris')" 
                            class="py-2 px-3 rounded-xl border text-xs font-semibold flex items-center justify-center gap-1.5 transition-all cursor-pointer bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100"
                        >
                            <span class="material-symbols-outlined text-base">qr_code_scanner</span>
                            <span>QRIS Langsung</span>
                        </button>
                    </div>

                    <!-- Panel Opsi Tunai (Input Diterima & Kembalian) -->
                    <div id="cashPaymentPanel" class="flex flex-col gap-2.5 mt-1 bg-slate-50/80 p-3 rounded-xl border border-slate-200/80">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 uppercase tracking-wider mb-1">
                                Uang Tunai Diterima:
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-500">Rp</span>
                                <input 
                                    type="number" 
                                    id="cashReceivedInput" 
                                    oninput="calculateChange()" 
                                    placeholder="0" 
                                    min="0"
                                    class="w-full pl-9 pr-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-900/20 focus:border-blue-900"
                                />
                            </div>
                        </div>

                        <!-- Tombol Cepat Nominal Uang Pas / Pecahan Uang -->
                        <div class="grid grid-cols-4 gap-1.5">
                            <button type="button" onclick="setExactCash()" class="py-1 px-1 bg-white hover:bg-slate-100 border border-slate-200 rounded-lg text-[10px] font-bold text-slate-700 transition-colors shadow-2xs cursor-pointer">
                                Pas
                            </button>
                            <button type="button" onclick="setCashAmount(20000)" class="py-1 px-1 bg-white hover:bg-slate-100 border border-slate-200 rounded-lg text-[10px] font-semibold text-slate-700 transition-colors shadow-2xs cursor-pointer">
                                20rb
                            </button>
                            <button type="button" onclick="setCashAmount(50000)" class="py-1 px-1 bg-white hover:bg-slate-100 border border-slate-200 rounded-lg text-[10px] font-semibold text-slate-700 transition-colors shadow-2xs cursor-pointer">
                                50rb
                            </button>
                            <button type="button" onclick="setCashAmount(100000)" class="py-1 px-1 bg-white hover:bg-slate-100 border border-slate-200 rounded-lg text-[10px] font-semibold text-slate-700 transition-colors shadow-2xs cursor-pointer">
                                100rb
                            </button>
                        </div>

                        <!-- Bar Kembalian -->
                        <div class="flex items-center justify-between pt-2 border-t border-slate-200/80">
                            <span class="text-xs font-bold text-slate-700">Uang Kembalian:</span>
                            <span id="cashChangeText" class="text-sm font-extrabold text-slate-500 font-mono">Rp 0</span>
                        </div>
                    </div>

                    <!-- Panel Opsi QRIS Langsung -->
                    <div id="qrisPaymentPanel" class="hidden flex-col gap-2 mt-1 bg-blue-50/60 p-3 rounded-xl border border-blue-200/80">
                        <div class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-blue-900 text-lg shrink-0 mt-0.5">verified</span>
                            <div class="text-[11px] text-blue-950 leading-relaxed">
                                Pelanggan melakukan pembayaran dengan memindai <strong>QRIS Statis Lapak</strong>. Sesuai PRD F12, transaksi kasir diselesaikan setelah Owner memverifikasi dana masuk secara langsung (tanpa Midtrans/webhook).
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tombol Konfirmasi Selesaikan Transaksi -->
                <button 
                    type="button" 
                    id="btnSubmitPos" 
                    onclick="submitPosTransaction()" 
                    disabled 
                    class="w-full py-3 bg-slate-200 text-slate-400 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 cursor-not-allowed transition-all shadow-xs"
                >
                    <span class="material-symbols-outlined text-base">task_alt</span>
                    <span>Konfirmasi & Selesaikan Transaksi</span>
                </button>

            </div>

        </div>

    </div>

</div>

<!-- ============================================================== -->
<!-- 3. MODAL DIALOG NOTA SUKSES TRANSAKSI KASIR (PREVIEW PPL-31)   -->
<!-- ============================================================== -->
<div id="posSuccessModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden opacity-0 transition-opacity duration-200">
    <div class="bg-white w-full max-w-sm rounded-3xl p-5 shadow-2xl border border-slate-100 flex flex-col gap-4">
        
        <!-- Header Sukses -->
        <div class="text-center flex flex-col items-center">
            <div class="w-13 h-13 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center mb-2 shadow-xs">
                <span class="material-symbols-outlined text-3xl">check_circle</span>
            </div>
            <h3 class="font-extrabold text-slate-900 text-base font-['Plus_Jakarta_Sans']">Transaksi Lapak Berhasil!</h3>
            <p class="text-xs text-slate-500 mt-0.5">Nota penjualan kasir POS telah diterbitkan</p>
        </div>

        <!-- Rincian Nota Digital -->
        <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-200/80 flex flex-col gap-2 font-mono text-[11px] text-slate-700">
            <div class="flex items-center justify-between pb-1.5 border-b border-dashed border-slate-200">
                <span class="text-slate-400">Nota:</span>
                <span id="receiptCode" class="font-bold text-slate-900">POS-20261117-0001</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-slate-400">Waktu:</span>
                <span id="receiptTime">17 Nov 2026, 06:45</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-slate-400">Pelanggan:</span>
                <span id="receiptCustomer" class="font-bold">Pelanggan Lapak</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-slate-400">Pembayaran:</span>
                <span id="receiptMethod" class="font-bold text-blue-900 uppercase">Tunai</span>
            </div>

            <!-- List Item di Nota -->
            <div class="pt-2 border-t border-dashed border-slate-200 flex flex-col gap-1" id="receiptItemsList">
                <!-- Injected via JS -->
            </div>

            <div class="pt-2 border-t border-slate-300 flex items-center justify-between font-bold text-xs text-slate-900">
                <span>Total Belanja:</span>
                <span id="receiptTotal">Rp 0</span>
            </div>
            <div id="receiptCashDetails" class="flex flex-col gap-1 pt-1 text-[10px] text-slate-600">
                <div class="flex items-center justify-between">
                    <span>Uang Diterima:</span>
                    <span id="receiptCashGiven">Rp 0</span>
                </div>
                <div class="flex items-center justify-between text-emerald-700 font-bold">
                    <span>Kembalian:</span>
                    <span id="receiptCashBack">Rp 0</span>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi Nota -->
        <div class="flex flex-col gap-2">
            <button 
                type="button" 
                onclick="closeSuccessModal()" 
                class="w-full py-2.5 bg-blue-900 hover:bg-blue-800 text-white rounded-xl text-xs font-bold transition-all shadow-xs cursor-pointer flex items-center justify-center gap-1.5"
            >
                <span class="material-symbols-outlined text-base">add_circle</span>
                <span>Transaksi Baru</span>
            </button>
        </div>

    </div>
</div>

<!-- ============================================================== -->
<!-- 4. MODAL KONFIRMASI RESET KERANJANG KASIR (TEMPLATE ALERT)    -->
<!-- ============================================================== -->
<div id="modal-reset-cart-confirm" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden opacity-0 transition-opacity duration-200" onclick="if (event.target === this) closeResetCartModal()">
    <div class="bg-white w-full max-w-sm rounded-3xl p-6 shadow-2xl border border-slate-100 text-center flex flex-col items-center">
        <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center mb-3">
            <span class="material-symbols-outlined text-3xl">restart_alt</span>
        </div>
        <h3 class="font-bold text-slate-900 text-base font-['Plus_Jakarta_Sans']">Reset Keranjang Kasir?</h3>
        <p class="text-xs text-slate-600 mt-2 leading-relaxed">
            Apakah kamu yakin ingin mengosongkan seluruh item pesanan di keranjang kasir dan mengulang formulir transaksi?
        </p>

        <div class="mt-5 w-full grid grid-cols-2 gap-2.5">
            <button type="button" onclick="closeResetCartModal()" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition-colors cursor-pointer">
                Batal
            </button>
            <button type="button" onclick="executeResetCart()" class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition-colors shadow-xs cursor-pointer flex items-center justify-center gap-1">
                <span class="material-symbols-outlined text-sm">restart_alt</span>
                <span>Ya, Reset</span>
            </button>
        </div>
    </div>
</div>

<!-- Container Toast Notifikasi Dinamis Kasir POS (Standar Alert) -->
<div id="pos-toast-container" class="fixed top-20 right-4 md:right-8 z-50 flex flex-col gap-2.5 max-w-sm w-full pointer-events-none"></div>
@endsection

@push('scripts')
<script>
    /**
     * STATE MANAGEMENT KERANJANG KASIR (VANILLA JS - PPL-31)
     */
    let cart = []; // Format: { id, name, flavor, price, stock, qty }
    let paymentMethod = 'tunai'; // 'tunai' | 'qris'

    // Format mata uang Rupiah
    function formatRupiah(amount) {
        return 'Rp ' + Number(amount).toLocaleString('id-ID');
    }

    /**
     * Toast Notifikasi Dinamis (Mengikuti Standar Desain Alert PPL-24)
     */
    function showPosToast(message, type = 'warning', title = null) {
        const container = document.getElementById('pos-toast-container');
        if (!container) return;

        const typeStyles = {
            success: {
                bg: 'bg-emerald-50',
                border: 'border-emerald-200',
                text: 'text-emerald-900',
                icon: 'check_circle',
                iconColor: 'text-emerald-600',
                closeColor: 'text-emerald-500 hover:text-emerald-800',
                defaultTitle: 'Berhasil'
            },
            error: {
                bg: 'bg-rose-50',
                border: 'border-rose-200',
                text: 'text-rose-900',
                icon: 'error',
                iconColor: 'text-rose-600',
                closeColor: 'text-rose-500 hover:text-rose-800',
                defaultTitle: 'Perhatian'
            },
            warning: {
                bg: 'bg-amber-50',
                border: 'border-amber-200',
                text: 'text-amber-900',
                icon: 'warning',
                iconColor: 'text-amber-600',
                closeColor: 'text-amber-500 hover:text-amber-800',
                defaultTitle: 'Peringatan'
            },
            info: {
                bg: 'bg-sky-50',
                border: 'border-sky-200',
                text: 'text-sky-900',
                icon: 'info',
                iconColor: 'text-sky-600',
                closeColor: 'text-sky-500 hover:text-sky-800',
                defaultTitle: 'Informasi'
            }
        };

        const style = typeStyles[type] || typeStyles.warning;
        const alertTitle = title !== null ? title : style.defaultTitle;

        const toast = document.createElement('div');
        toast.className = `p-3.5 sm:p-4 rounded-2xl border ${style.bg} ${style.border} ${style.text} shadow-lg flex items-start gap-3 transition-all duration-300 pointer-events-auto transform -translate-y-2 opacity-0`;
        toast.setAttribute('role', 'alert');

        toast.innerHTML = `
            <span class="material-symbols-outlined ${style.iconColor} text-xl shrink-0 mt-0.5 select-none">
                ${style.icon}
            </span>
            <div class="flex-1 text-xs leading-relaxed">
                ${alertTitle ? `<h4 class="font-bold font-['Plus_Jakarta_Sans'] text-xs sm:text-sm mb-0.5">${alertTitle}</h4>` : ''}
                <p class="font-medium">${message}</p>
            </div>
            <button type="button" class="p-1 -mr-1 -mt-1 rounded-lg ${style.closeColor} hover:bg-black/5 transition-colors cursor-pointer" title="Tutup">
                <span class="material-symbols-outlined text-base block">close</span>
            </button>
        `;

        toast.querySelector('button').addEventListener('click', () => {
            toast.classList.add('opacity-0', '-translate-y-2');
            setTimeout(() => toast.remove(), 250);
        });

        container.appendChild(toast);

        requestAnimationFrame(() => {
            toast.classList.remove('opacity-0', '-translate-y-2');
            toast.classList.add('opacity-100', 'translate-y-0');
        });

        setTimeout(() => {
            if (toast.isConnected) {
                toast.classList.add('opacity-0', '-translate-y-2');
                setTimeout(() => toast.remove(), 250);
            }
        }, 3500);
    }

    // Tambah item ke keranjang kasir
    function addToCart(id, name, flavor, price, stock) {
        const existing = cart.find(item => item.id === id);

        if (existing) {
            if (existing.qty < stock) {
                existing.qty++;
            } else {
                showPosToast(`Kuantitas tidak dapat melebihi stok yang tersedia (${stock} botol).`, 'warning', 'Batas Stok Tercapai');
                return;
            }
        } else {
            if (stock <= 0) {
                showPosToast('Stok varian ini telah habis di cooler box lapak.', 'error', 'Stok Habis');
                return;
            }
            cart.push({
                id: id,
                name: name,
                flavor: flavor,
                price: Number(price),
                stock: Number(stock),
                qty: 1
            });
        }

        renderCart();
    }

    // Ubah kuantitas item
    function updateQty(id, delta) {
        const item = cart.find(i => i.id === id);
        if (!item) return;

        const newQty = item.qty + delta;

        if (newQty <= 0) {
            cart = cart.filter(i => i.id !== id);
        } else if (newQty > item.stock) {
            showPosToast(`Maksimal stok tersedia untuk varian ${item.name} adalah ${item.stock} botol.`, 'warning', 'Batas Stok');
            return;
        } else {
            item.qty = newQty;
        }

        renderCart();
    }

    // Hapus satu item
    function removeFromCart(id) {
        cart = cart.filter(i => i.id !== id);
        renderCart();
    }

    // Kosongkan keranjang (tombol "Kosongkan" di header keranjang)
    function clearCart() {
        if (cart.length === 0) return;
        openResetCartModal();
    }

    // Dialog Konfirmasi Modal Reset Keranjang Kasir
    function resetCartConfirm() {
        if (cart.length > 0) {
            openResetCartModal();
        } else {
            executeResetCart(false);
            showPosToast('Keranjang kasir telah disegarkan.', 'info', 'Kasir Direset');
        }
    }

    function openResetCartModal() {
        const modal = document.getElementById('modal-reset-cart-confirm');
        if (!modal) return;
        modal.classList.remove('hidden');
        setTimeout(() => modal.classList.remove('opacity-0'), 10);
    }

    function closeResetCartModal() {
        const modal = document.getElementById('modal-reset-cart-confirm');
        if (!modal) return;
        modal.classList.add('opacity-0');
        setTimeout(() => modal.classList.add('hidden'), 200);
    }

    function executeResetCart(fromModal = true) {
        cart = [];
        const customerInput = document.getElementById('customerNameInput');
        if (customerInput) customerInput.value = 'Pelanggan Lapak';
        const cashInput = document.getElementById('cashReceivedInput');
        if (cashInput) cashInput.value = '';
        setPaymentMethod('tunai');
        renderCart();

        if (fromModal) {
            closeResetCartModal();
            showPosToast('Seluruh item keranjang kasir berhasil direset.', 'success', 'Keranjang Dikosongkan');
        }
    }

    // Render HTML Keranjang Kasir
    function renderCart() {
        const container = document.getElementById('cartItemsContainer');
        const emptyState = document.getElementById('cartEmptyState');
        const badgeCount = document.getElementById('cartCountBadge');
        const totalQtyText = document.getElementById('totalQtyText');
        const totalPriceText = document.getElementById('totalPriceText');

        if (cart.length === 0) {
            container.innerHTML = '';
            emptyState.classList.remove('hidden');
            badgeCount.textContent = '0 item dipilih';
            totalQtyText.textContent = '0 Botol';
            totalPriceText.textContent = 'Rp 0';
            calculateChange();
            return;
        }

        emptyState.classList.add('hidden');

        let totalQty = 0;
        let totalPrice = 0;
        let html = '';

        cart.forEach(item => {
            const subtotal = item.qty * item.price;
            totalQty += item.qty;
            totalPrice += subtotal;

            html += `
                <div class="p-2 bg-slate-50 border border-slate-200/80 rounded-xl flex items-center justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <div class="text-xs font-bold text-slate-900 truncate font-['Plus_Jakarta_Sans']">${item.name}</div>
                        <div class="text-[10px] text-slate-500">${formatRupiah(item.price)} × ${item.qty} btl</div>
                    </div>

                    <div class="flex items-center gap-1.5 shrink-0">
                        <div class="flex items-center bg-white border border-slate-200 rounded-lg p-0.5 shadow-2xs">
                            <button type="button" onclick="updateQty(${item.id}, -1)" class="w-5 h-5 flex items-center justify-center rounded-md hover:bg-slate-100 text-slate-700 font-bold text-xs cursor-pointer">
                                -
                            </button>
                            <span class="w-6 text-center text-xs font-extrabold text-slate-900">${item.qty}</span>
                            <button type="button" onclick="updateQty(${item.id}, 1)" ${item.qty >= item.stock ? 'disabled' : ''} class="w-5 h-5 flex items-center justify-center rounded-md hover:bg-slate-100 text-slate-700 font-bold text-xs cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed">
                                +
                            </button>
                        </div>

                        <div class="text-right min-w-16">
                            <span class="text-xs font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">${formatRupiah(subtotal)}</span>
                        </div>

                        <button type="button" onclick="removeFromCart(${item.id})" class="text-slate-400 hover:text-rose-600 p-0.5 rounded-md transition-colors cursor-pointer" title="Hapus">
                            <span class="material-symbols-outlined text-sm block">close</span>
                        </button>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        badgeCount.textContent = `${totalQty} botol (${cart.length} varian)`;
        totalQtyText.textContent = `${totalQty} Botol`;
        totalPriceText.textContent = formatRupiah(totalPrice);

        calculateChange();
    }

    // Toggle Metode Pembayaran
    function setPaymentMethod(method) {
        paymentMethod = method;
        const btnCash = document.getElementById('btnMethodCash');
        const btnQris = document.getElementById('btnMethodQris');
        const cashPanel = document.getElementById('cashPaymentPanel');
        const qrisPanel = document.getElementById('qrisPaymentPanel');

        if (method === 'tunai') {
            btnCash.className = 'py-2 px-3 rounded-xl border text-xs font-bold flex items-center justify-center gap-1.5 transition-all cursor-pointer bg-blue-900 text-white border-blue-900 shadow-2xs';
            btnQris.className = 'py-2 px-3 rounded-xl border text-xs font-semibold flex items-center justify-center gap-1.5 transition-all cursor-pointer bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100';
            cashPanel.classList.remove('hidden');
            qrisPanel.classList.add('hidden');
        } else {
            btnQris.className = 'py-2 px-3 rounded-xl border text-xs font-bold flex items-center justify-center gap-1.5 transition-all cursor-pointer bg-blue-900 text-white border-blue-900 shadow-2xs';
            btnCash.className = 'py-2 px-3 rounded-xl border text-xs font-semibold flex items-center justify-center gap-1.5 transition-all cursor-pointer bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100';
            cashPanel.classList.add('hidden');
            qrisPanel.classList.remove('hidden');
        }

        calculateChange();
    }

    // Kalkulasi Uang Kembalian & Validasi Tombol Submit
    function calculateChange() {
        const total = cart.reduce((sum, item) => sum + (item.qty * item.price), 0);
        const cashInput = document.getElementById('cashReceivedInput');
        const changeText = document.getElementById('cashChangeText');
        const submitBtn = document.getElementById('btnSubmitPos');

        if (cart.length === 0) {
            submitBtn.disabled = true;
            submitBtn.className = 'w-full py-3 bg-slate-200 text-slate-400 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 cursor-not-allowed transition-all shadow-xs';
            changeText.textContent = 'Rp 0';
            changeText.className = 'text-sm font-extrabold text-slate-500 font-mono';
            return;
        }

        if (paymentMethod === 'qris') {
            submitBtn.disabled = false;
            submitBtn.className = 'w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-all shadow-xs cursor-pointer';
            return;
        }

        // Metode Tunai
        const cashReceived = Number(cashInput.value) || 0;
        const change = cashReceived - total;

        if (cashReceived === 0) {
            changeText.textContent = 'Rp 0';
            changeText.className = 'text-sm font-extrabold text-slate-500 font-mono';
            submitBtn.disabled = true;
            submitBtn.className = 'w-full py-3 bg-slate-200 text-slate-400 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 cursor-not-allowed transition-all shadow-xs';
        } else if (change < 0) {
            changeText.textContent = `Kurang ${formatRupiah(Math.abs(change))}`;
            changeText.className = 'text-sm font-extrabold text-rose-600 font-mono';
            submitBtn.disabled = true;
            submitBtn.className = 'w-full py-3 bg-slate-200 text-slate-400 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 cursor-not-allowed transition-all shadow-xs';
        } else {
            changeText.textContent = formatRupiah(change);
            changeText.className = 'text-sm font-extrabold text-emerald-700 font-mono';
            submitBtn.disabled = false;
            submitBtn.className = 'w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-all shadow-xs cursor-pointer';
        }
    }

    // Tombol Cepat Nominal Uang Pas
    function setExactCash() {
        const total = cart.reduce((sum, item) => sum + (item.qty * item.price), 0);
        document.getElementById('cashReceivedInput').value = total > 0 ? total : '';
        calculateChange();
    }

    // Tombol Cepat Nominal Tertentu
    function setCashAmount(amount) {
        document.getElementById('cashReceivedInput').value = amount;
        calculateChange();
    }

    // Filter Pencarian Menu Realtime
    function filterPosProducts() {
        const query = document.getElementById('searchPosInput').value.toLowerCase().trim();
        const clearBtn = document.getElementById('clearSearchBtn');
        const cards = document.querySelectorAll('.product-pos-card');
        const emptyState = document.getElementById('emptySearchState');

        clearBtn.classList.toggle('hidden', query === '');

        let matchCount = 0;

        cards.forEach(card => {
            const name = card.getAttribute('data-name');
            if (name.includes(query)) {
                card.classList.remove('hidden');
                matchCount++;
            } else {
                card.classList.add('hidden');
            }
        });

        emptyState.classList.toggle('hidden', matchCount > 0);
    }

    function clearSearch() {
        document.getElementById('searchPosInput').value = '';
        filterPosProducts();
    }

    function escapeJsString(str) {
        return (str || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
    }

    // Konfirmasi & Selesaikan Transaksi POS (PPL-32 Backend Transaction & F10 Reactivity)
    function submitPosTransaction() {
        if (cart.length === 0) return;

        const submitBtn = document.getElementById('btnSubmitPos');
        const customerName = document.getElementById('customerNameInput').value.trim() || 'Pelanggan Lapak';
        const cashReceivedVal = document.getElementById('cashReceivedInput').value;

        const payload = {
            items: cart.map(item => ({
                product_id: item.id,
                jumlah: item.qty
            })),
            metode_pembayaran: paymentMethod,
            nama_pelanggan: customerName,
            uang_diterima: (paymentMethod === 'tunai' && cashReceivedVal !== '') ? Number(cashReceivedVal) : null
        };

        // Loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="material-symbols-outlined text-base animate-spin">progress_activity</span><span>Memproses Transaksi...</span>';

        fetch('{{ route('owner.pos.transaction') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        })
        .then(async response => {
            const res = await response.json();
            if (!response.ok) {
                let errorMsg = res.message || 'Terjadi kesalahan saat memproses transaksi kasir.';
                if (res.errors) {
                    const firstErrorKey = Object.keys(res.errors)[0];
                    if (firstErrorKey && res.errors[firstErrorKey][0]) {
                        errorMsg = res.errors[firstErrorKey][0];
                    }
                }
                throw new Error(errorMsg);
            }
            return res;
        })
        .then(res => {
            const data = res.data;
            const order = data.order;

            // Populate Modal Nota Digital dari Database Nyata
            document.getElementById('receiptCode').textContent = order.kode;
            document.getElementById('receiptTime').textContent = order.waktu;
            document.getElementById('receiptCustomer').textContent = order.nama_pelanggan;
            document.getElementById('receiptMethod').textContent = order.metode_pembayaran === 'tunai' ? 'Tunai (Cash)' : 'QRIS Langsung';
            document.getElementById('receiptTotal').textContent = formatRupiah(order.total);

            // Rincian Item di Nota
            let itemsHtml = '';
            (order.items || []).forEach(item => {
                itemsHtml += `
                    <div class="flex items-center justify-between">
                        <span>${item.jumlah}x ${item.nama_produk}</span>
                        <span>${formatRupiah(item.subtotal)}</span>
                    </div>
                `;
            });
            document.getElementById('receiptItemsList').innerHTML = itemsHtml;

            const cashDetails = document.getElementById('receiptCashDetails');
            if (order.metode_pembayaran === 'tunai') {
                cashDetails.classList.remove('hidden');
                document.getElementById('receiptCashGiven').textContent = formatRupiah(order.uang_diterima);
                document.getElementById('receiptCashBack').textContent = formatRupiah(order.kembalian);
            } else {
                cashDetails.classList.add('hidden');
            }

            // Tampilkan Modal Nota Sukses
            const modal = document.getElementById('posSuccessModal');
            modal.classList.remove('hidden');
            setTimeout(() => modal.classList.remove('opacity-0'), 10);

            // Kosongkan state keranjang kasir
            cart = [];
            document.getElementById('cashReceivedInput').value = '';
            renderCart();

            // Reaktivitas UI Peringatan Restok (F10) & Pembaruan Stok Kartu Real-time
            updateProductsUI(data.products, data.low_stock_products, data.restock_settings);

            showPosToast('Transaksi kasir #' + order.kode + ' berhasil disimpan!', 'success', 'Transaksi Berhasil');
        })
        .catch(err => {
            showPosToast(err.message, 'error', 'Transaksi Gagal');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<span class="material-symbols-outlined text-base">task_alt</span><span>Konfirmasi & Selesaikan Transaksi</span>';
            calculateChange();
        });
    }

    // Reaktivitas UI Real-time: Kartu Produk & Widget Rule-Based Peringatan Restok (F10)
    function updateProductsUI(products, lowStockProducts, restockSettings) {
        if (!products || !Array.isArray(products)) return;

        // 1. Perbarui masing-masing kartu produk di katalog kasir
        products.forEach(p => {
            const card = document.getElementById(`product-card-${p.id}`);
            const badgeContainer = document.getElementById(`stock-badge-container-${p.id}`);
            const actionContainer = document.getElementById(`product-action-container-${p.id}`);

            if (card) {
                card.setAttribute('data-stock', p.stok);
                if (p.stok <= 0) {
                    card.className = 'product-pos-card bg-white rounded-2xl border transition-all flex flex-col justify-between overflow-hidden p-3.5 shadow-2xs relative opacity-60 border-slate-200 bg-slate-50/60';
                } else {
                    card.className = 'product-pos-card bg-white rounded-2xl border transition-all flex flex-col justify-between overflow-hidden p-3.5 shadow-2xs relative border-slate-200/90 hover:border-blue-900/30 hover:shadow-xs';
                }
            }

            if (badgeContainer) {
                if (p.stok <= 0) {
                    badgeContainer.innerHTML = '<span class="px-2 py-0.5 bg-slate-200 text-slate-700 font-extrabold text-[10px] rounded-md uppercase">Habis</span>';
                } else if (p.stok <= 5) {
                    badgeContainer.innerHTML = `<span class="px-2 py-0.5 bg-rose-100 text-rose-800 font-extrabold text-[10px] rounded-md flex items-center gap-0.5" title="Stok kritis &le; 5 botol"><span class="material-symbols-outlined text-[11px]">warning</span><span>Sisa ${p.stok} btl</span></span>`;
                } else {
                    badgeContainer.innerHTML = `<span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 font-bold text-[10px] rounded-md">Stok: ${p.stok} btl</span>`;
                }
            }

            if (actionContainer) {
                if (p.stok <= 0) {
                    actionContainer.innerHTML = '<button type="button" disabled class="px-2.5 py-1.5 bg-slate-100 text-slate-400 rounded-xl text-xs font-bold cursor-not-allowed">Habis</button>';
                } else {
                    actionContainer.innerHTML = `<button type="button" onclick="addToCart(${p.id}, '${escapeJsString(p.nama)}', '${escapeJsString(p.rasa || '')}', ${p.harga}, ${p.stok})" class="px-3 py-1.5 bg-blue-900 hover:bg-blue-800 text-white rounded-xl text-xs font-bold flex items-center gap-1 shadow-2xs active:scale-95 transition-all cursor-pointer" title="Tambah ke Keranjang Kasir"><span class="material-symbols-outlined text-sm font-bold">add</span><span>Pilih</span></button>`;
                }
            }
        });

        // 2. Perbarui Widget Peringatan Restok (Aturan F10 & BR-10)
        const warningContainer = document.getElementById('restockWarningContainer');
        const warningCountText = document.getElementById('restockCountText');
        const pillsContainer = document.getElementById('restockPillsContainer');

        if (warningContainer && pillsContainer) {
            if (!lowStockProducts || lowStockProducts.length === 0) {
                warningContainer.classList.add('hidden');
            } else {
                warningContainer.classList.remove('hidden');
                if (warningCountText) {
                    warningCountText.textContent = `${lowStockProducts.length} varian kopi`;
                }

                let pillsHtml = '';
                lowStockProducts.forEach(lp => {
                    const isZero = lp.stok <= 0;
                    const badgeClass = isZero ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'bg-white text-amber-900 border border-amber-200/80 shadow-2xs';
                    const countClass = isZero ? 'text-rose-950' : 'text-slate-500';

                    pillsHtml += `
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-[11px] font-bold ${badgeClass}">
                            <span>${lp.nama}</span>
                            <span class="font-mono text-[10px] ${countClass}">(${lp.stok} btl)</span>
                            <span class="text-[10px] text-blue-900 bg-blue-50 px-1 py-0.2 rounded-md font-extrabold">+${lp.saran_produksi}</span>
                        </span>
                    `;
                });
                pillsContainer.innerHTML = pillsHtml;
            }
        }
    }

    function closeSuccessModal() {
        const modal = document.getElementById('posSuccessModal');
        modal.classList.add('opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            // Reset keranjang setelah transaksi selesai
            cart = [];
            document.getElementById('cashReceivedInput').value = '';
            document.getElementById('customerNameInput').value = 'Pelanggan Lapak';
            renderCart();
        }, 200);
    }
</script>
@endpush
