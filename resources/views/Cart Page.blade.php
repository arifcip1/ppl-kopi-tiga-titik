@extends('layouts.pelanggan')

@section('title', 'Keranjang Pesanan - Kopi Tiga Titik')
@section('active_menu', 'keranjang')
@section('page_title', 'Keranjang Pesanan')
@section('role_badge', 'PELANGGAN')
@section('page_subtitle', 'Tinjau pesanan kopi botol segarmu dan atur jumlah botol sebelum checkout')

@section('content')

    <!-- Toast Notifikasi Interaktif -->
    <div id="cart-toast"
        class="fixed top-20 right-4 md:right-8 z-50 px-4 py-3 rounded-2xl shadow-xl text-xs font-semibold flex items-center gap-2.5 transition-all duration-300 opacity-0 pointer-events-none transform -translate-y-2 max-w-sm">
        <span id="toast-icon" class="material-symbols-outlined text-lg">info</span>
        <span id="toast-message">Notifikasi</span>
    </div>

    <!-- Status Lapak & Banner Lokasi -->
    <div
        class="p-3.5 md:p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div class="flex items-center gap-2.5">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="text-xs md:text-sm font-bold text-slate-800">Lapak Aktif: Jl. Kuwung, Mergelo, Kota
                Mojokerto</span>
            <span
                class="px-2 py-0.5 bg-slate-100 rounded-full border border-slate-200/60 text-[11px] font-medium text-slate-600 hidden sm:inline-block">
                ⏰ 15.00 - 18.00 WIB
            </span>
        </div>
        <a href="{{ url('/katalog') }}"
            class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer self-stretch sm:self-auto justify-center">
            <span class="material-symbols-outlined text-base">storefront</span>
            <span>Tambah Menu Lain</span>
        </a>
    </div>

    <!-- 1. Kondisi Keranjang Kosong (Empty State - Bug 1 QA) -->
    <div id="empty-cart-container"
        class="p-8 md:p-16 bg-white rounded-3xl shadow-xs border border-slate-200/80 flex flex-col items-center justify-center text-center gap-4 {{ empty($cartItems) || count($cartItems) === 0 ? 'flex' : 'hidden' }}">
        <div class="w-20 h-20 rounded-3xl bg-blue-50 text-blue-900 flex items-center justify-center shadow-inner">
            <span class="material-symbols-outlined text-4xl">shopping_cart</span>
        </div>
        <div class="max-w-sm">
            <h3 class="text-base md:text-lg font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Keranjang Belanja Kosong
            </h3>
            <p class="text-xs md:text-sm text-slate-500 mt-1">Anda belum menambahkan produk kopi ke dalam keranjang belanja.
            </p>
        </div>
        <a href="{{ url('/katalog') }}"
            class="mt-2 px-6 py-2.5 bg-blue-900 hover:bg-blue-800 text-white rounded-xl text-xs md:text-sm font-bold transition-all shadow-sm flex items-center gap-2">
            <span class="material-symbols-outlined text-base">storefront</span>
            <span>Mulai Belanja Sekarang</span>
        </a>
    </div>

    <!-- 2. Kondisi Keranjang Terisi (Desktop 2-Kolom & Mobile 1-Kolom Responsive Grid) -->
    <div id="cart-content-container" class="{{ empty($cartItems) || count($cartItems) === 0 ? 'hidden' : 'block' }}">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- Kolom Kiri: Daftar Produk, Upsell, & Catatan Barista (lg:col-span-7 xl:col-span-8) -->
            <div class="lg:col-span-7 xl:col-span-8 flex flex-col gap-5">

                <!-- Header Daftar Produk -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-900 text-xl">shopping_bag</span>
                        <h2 class="text-base md:text-lg font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Daftar Produk
                            Pesanan</h2>
                    </div>
                    <span class="text-xs text-slate-500 font-medium">
                        Total <span id="header-bottle-count" class="font-bold text-slate-800">{{ $totalJumlah ?? 0 }}</span>
                        Botol
                    </span>
                </div>

                <!-- List Items Container Dinamis (FR-4.1 & FR-4.2) -->
                <div id="cart-items-list" class="flex flex-col gap-3">
                    @if(!empty($cartItems))
                        @foreach($cartItems as $item)
                            @php
                                $prod = $item->product;
                                $fotoUrl = $prod ? $prod->foto_url : asset('images/logo.png');
                                $hargaSatuan = $prod ? $prod->harga : 0;
                                $stokTersedia = $prod ? $prod->stok : 0;
                                $subtotalItem = $item->subtotal ?? ($hargaSatuan * $item->jumlah);
                            @endphp
                            <div id="cart-item-{{ $item->id }}"
                                class="cart-item-row p-4 md:p-5 bg-white rounded-2xl shadow-xs border border-slate-200/90 hover:border-blue-900/30 transition-all flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 group"
                                data-id="{{ $item->id }}" data-price="{{ $hargaSatuan }}" data-stock="{{ $stokTersedia }}">
                                <!-- Info Kiri: Foto Botol & Info Produk -->
                                <div class="flex items-center gap-4 min-w-0 flex-1">
                                    <div
                                        class="w-16 h-20 md:w-20 md:h-24 bg-slate-50 rounded-xl flex items-center justify-center p-1.5 border border-slate-100 shrink-0 overflow-hidden">
                                        <img src="{{ $fotoUrl }}" alt="{{ $prod->nama ?? 'Produk' }}"
                                            class="w-full h-full object-contain group-hover:scale-105 transition-transform"
                                            onerror="this.src='https://placehold.co/150x200/1e3a8a/ffffff?text={{ urlencode($prod->nama ?? 'Kopi') }}'" />
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h3
                                            class="font-bold text-slate-900 text-sm md:text-base font-['Plus_Jakarta_Sans'] truncate">
                                            {{ $prod->nama ?? 'Produk Kopi' }}</h3>
                                        <p class="text-slate-500 text-xs mt-0.5 truncate">{{ $prod->rasa ?? '250ml' }}</p>
                                        <div class="flex items-center gap-2 mt-1 flex-wrap">
                                            <span class="text-xs font-semibold text-slate-700">Rp
                                                {{ number_format($hargaSatuan, 0, ',', '.') }} / btl</span>
                                            @if($stokTersedia <= 5)
                                                <span
                                                    class="text-[10px] text-amber-800 bg-amber-50 border border-amber-200 px-1.5 py-0.2 rounded-md font-semibold">Sisa
                                                    {{ $stokTersedia }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Aksi Kanan: Subtotal, Qty Counter & Tombol Hapus -->
                                <div
                                    class="flex sm:flex-col items-center sm:items-end justify-between w-full sm:w-auto gap-3 pt-3 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                                    <div class="flex items-center gap-3">
                                        <span id="subtotal-{{ $item->id }}"
                                            class="text-blue-900 font-extrabold text-base md:text-lg font-['Plus_Jakarta_Sans']">
                                            Rp {{ number_format($subtotalItem, 0, ',', '.') }}
                                        </span>
                                        <button type="button" onclick="deleteCartItem({{ $item->id }})"
                                            class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                            title="Hapus produk dari keranjang">
                                            <span class="material-symbols-outlined text-lg">delete</span>
                                        </button>
                                    </div>

                                    <!-- Counter Buttons (+ dan -) -->
                                    <div
                                        class="flex items-center gap-2.5 bg-slate-50 px-2.5 py-1 rounded-full border border-slate-200 shadow-2xs">
                                        <button type="button" onclick="changeQty({{ $item->id }}, -1)"
                                            class="w-6 h-6 bg-white hover:bg-slate-100 rounded-full border border-slate-300 text-slate-700 flex items-center justify-center font-bold text-xs shadow-2xs transition-colors cursor-pointer active:scale-95"
                                            title="Kurangi 1 botol">
                                            <span class="material-symbols-outlined text-xs font-bold">remove</span>
                                        </button>
                                        <span id="qty-{{ $item->id }}"
                                            class="text-xs md:text-sm font-bold text-slate-900 w-5 text-center">{{ $item->jumlah }}</span>
                                        <button type="button" onclick="changeQty({{ $item->id }}, 1)"
                                            class="w-6 h-6 bg-blue-900 hover:bg-blue-800 rounded-full text-white flex items-center justify-center font-bold text-xs shadow-2xs transition-colors cursor-pointer active:scale-95"
                                            title="Tambah 1 botol">
                                            <span class="material-symbols-outlined text-xs font-bold">add</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <!-- Section Rekomendasi Menu Favorit Lainnya (Upsell) -->
                @if(isset($upsellProducts) && $upsellProducts->isNotEmpty())
                    <div class="p-4 md:p-5 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex flex-col gap-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-blue-900 text-lg">local_cafe</span>
                                <h3 class="text-sm font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Tambah Varian Favorit
                                    Lainnya</h3>
                            </div>
                            <span
                                class="text-[11px] font-semibold text-slate-500 bg-slate-100 px-2.5 py-0.5 rounded-full">Pilihan
                                Barista</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                            @foreach($upsellProducts as $upsell)
                                <div
                                    class="p-3 bg-slate-50 hover:bg-blue-50/50 rounded-xl border border-slate-200/70 transition-all flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div
                                            class="w-10 h-10 rounded-lg bg-blue-100 text-blue-900 flex items-center justify-center shrink-0">
                                            <span class="material-symbols-outlined text-xl">coffee</span>
                                        </div>
                                        <div class="truncate">
                                            <div class="text-xs font-bold text-slate-900 truncate">{{ $upsell->nama }}</div>
                                            <div class="text-[11px] font-extrabold text-blue-900">Rp
                                                {{ number_format($upsell->harga, 0, ',', '.') }}</div>
                                        </div>
                                    </div>
                                    <button type="button" onclick="addUpsellProduct({{ $upsell->id }})"
                                        class="px-2.5 py-1 bg-white hover:bg-blue-900 text-blue-900 hover:text-white rounded-lg border border-slate-200 hover:border-blue-900 text-xs font-bold flex items-center gap-1 transition-colors cursor-pointer shadow-2xs">
                                        <span class="material-symbols-outlined text-sm font-bold">add</span>
                                        <span>Tambah</span>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif


            </div>

            <!-- Kolom Kanan: Ringkasan Biaya & Checkout Sticky Panel (lg:col-span-5 xl:col-span-4) -->
            <div class="lg:col-span-5 xl:col-span-4 lg:sticky lg:top-24 flex flex-col gap-4">

                <div class="p-5 md:p-6 bg-white rounded-2xl shadow-xs border border-slate-200/90 flex flex-col gap-4">
                    <!-- Header Ringkasan Biaya -->
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h3 class="text-base font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Ringkasan Biaya</h3>
                        <span
                            class="px-2 py-0.5 bg-blue-100 text-blue-900 text-[10px] font-extrabold rounded tracking-wider">KOPI
                            TIGA TITIK</span>
                    </div>

                    <!-- Breakdown Detail Biaya -->
                    <div class="flex flex-col gap-2.5 text-xs text-slate-600">
                        <div class="flex justify-between items-center">
                            <span>Subtotal Produk (<span id="summary-bottle-count"
                                    class="font-bold text-slate-800">{{ $totalJumlah ?? 0 }}</span> Botol)</span>
                            <span id="summary-subtotal" class="font-bold text-slate-900 text-sm">Rp
                                {{ number_format($totalPesanan ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="flex items-center gap-1.5">
                                <span>Estimasi Kemasan & Es Pendingin</span>
                                <span>❄️</span>
                            </span>
                            <span class="font-bold text-emerald-600">Gratis</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="flex items-center gap-1.5">
                                <span>Voucher Diskon Ongkir</span>
                                <span>🎟️</span>
                            </span>
                            <span class="font-medium text-slate-400">- Rp 0</span>
                        </div>
                    </div>

                    <div class="border-t border-dashed border-slate-200 my-1"></div>

                    <!-- Total Sementara -->
                    <div class="flex justify-between items-baseline">
                        <span class="text-xs md:text-sm font-bold text-slate-900">Total Sementara</span>
                        <span id="summary-total"
                            class="text-xl md:text-2xl font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">
                            Rp {{ number_format($totalPesanan ?? 0, 0, ',', '.') }}
                        </span>
                    </div>

                    <!-- Banner Total Pesanan Botol Segar -->
                    <div class="p-3 bg-blue-50/80 border border-blue-100 rounded-xl flex items-center justify-between">
                        <span class="text-[10px] font-bold text-blue-900 uppercase tracking-wider">TOTAL PESANAN</span>
                        <div
                            class="flex items-center gap-1.5 text-xs font-bold text-emerald-700 bg-white px-2.5 py-1 rounded-full shadow-2xs border border-emerald-200/60">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span><span id="bar-bottle-count">{{ $totalJumlah ?? 0 }}</span> Botol Segar</span>
                        </div>
                    </div>

                    <!-- Tombol Lanjut Pembayaran -->
                    <a href="{{ url('/checkout') }}"
                        class="w-full py-3.5 bg-blue-900 hover:bg-blue-800 text-white rounded-xl font-bold text-xs md:text-sm flex items-center justify-center gap-2 shadow-sm transition-all active:scale-98 cursor-pointer">
                        <span>Lanjut Pembayaran</span>
                        <span class="material-symbols-outlined text-base font-bold">arrow_forward</span>
                    </a>

                    <!-- Hygiene Badge -->
                    <div class="flex items-center justify-center gap-1.5 pt-1 text-slate-400 text-xs">
                        <span class="material-symbols-outlined text-emerald-600 text-sm">verified</span>
                        <span class="text-[11px]">Kopi kemasan botol & diseduh tiap hari</span>
                    </div>
                </div>

            </div>

        </div>
    </div>

@endsection

@push('scripts')
    <script>
        const csrfToken = '{{ csrf_token() }}';

        function showToast(message, isError = false) {
            const toast = document.getElementById('cart-toast');
            const msgEl = document.getElementById('toast-message');
            const iconEl = document.getElementById('toast-icon');

            if (!toast || !msgEl) return;

            msgEl.textContent = message;
            if (isError) {
                toast.className = 'fixed top-20 right-4 md:right-8 z-50 px-4 py-3 rounded-2xl shadow-xl text-xs font-semibold flex items-center gap-2.5 transition-all duration-300 opacity-100 pointer-events-auto bg-rose-600 text-white transform translate-y-0 max-w-sm';
                iconEl.textContent = 'error';
            } else {
                toast.className = 'fixed top-20 right-4 md:right-8 z-50 px-4 py-3 rounded-2xl shadow-xl text-xs font-semibold flex items-center gap-2.5 transition-all duration-300 opacity-100 pointer-events-auto bg-slate-900 text-white transform translate-y-0 max-w-sm';
                iconEl.textContent = 'check_circle';
            }

            setTimeout(() => {
                toast.classList.add('opacity-0', 'pointer-events-none', '-translate-y-2');
                toast.classList.remove('opacity-100', 'pointer-events-auto', 'translate-y-0');
            }, 3000);
        }

        function updateSidebarBadge(count) {
            const badge = document.getElementById('sidebar-cart-badge');
            if (badge) {
                badge.textContent = count;
                if (count > 0) {
                    badge.classList.remove('hidden');
                    badge.classList.add('flex');
                } else {
                    badge.classList.add('hidden');
                    badge.classList.remove('flex');
                }
            }
        }

        function updateSummaryUI(data) {
            if (!data) return;

            if (data.total_pesanan_formatted) {
                const summarySubtotal = document.getElementById('summary-subtotal');
                const summaryTotal = document.getElementById('summary-total');
                if (summarySubtotal) summarySubtotal.textContent = data.total_pesanan_formatted;
                if (summaryTotal) summaryTotal.textContent = data.total_pesanan_formatted;
            }

            if (data.total_jumlah !== undefined) {
                const summaryCount = document.getElementById('summary-bottle-count');
                const barCount = document.getElementById('bar-bottle-count');
                const headerCount = document.getElementById('header-bottle-count');

                if (summaryCount) summaryCount.textContent = data.total_jumlah;
                if (barCount) barCount.textContent = data.total_jumlah;
                if (headerCount) headerCount.textContent = data.total_jumlah;
            }

            if (data.cart_count !== undefined) {
                updateSidebarBadge(data.cart_count);
            }

            if (data.is_empty || data.cart_count === 0) {
                document.getElementById('cart-content-container')?.classList.add('hidden');
                document.getElementById('empty-cart-container')?.classList.remove('hidden');
                document.getElementById('empty-cart-container')?.classList.add('flex');
            }
        }

        // Ubah kuantitas item (+ dan -) dengan validasi stok
        function changeQty(cartItemId, delta) {
            const qtyEl = document.getElementById(`qty-${cartItemId}`);
            const cardEl = document.getElementById(`cart-item-${cartItemId}`);
            if (!qtyEl || !cardEl) return;

            const currentQty = parseInt(qtyEl.textContent) || 1;
            const maxStock = parseInt(cardEl.getAttribute('data-stock')) || 999;
            const newQty = currentQty + delta;

            if (newQty < 1) {
                if (confirm('Hapus produk ini dari keranjang belanja?')) {
                    deleteCartItem(cartItemId);
                }
                return;
            }

            if (newQty > maxStock) {
                showToast(`Jumlah melebihi stok yang tersedia (tersisa ${maxStock} botol).`, true);
                return;
            }

            qtyEl.textContent = newQty;

            fetch(`{{ url('/cart/items') }}/${cartItemId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ jumlah: newQty })
            })
                .then(async (res) => {
                    const data = await res.json();
                    if (!res.ok) {
                        qtyEl.textContent = currentQty;
                        throw new Error(data.message || 'Gagal mengubah jumlah produk');
                    }
                    return data;
                })
                .then((resData) => {
                    if (resData.data) {
                        const subtotalEl = document.getElementById(`subtotal-${cartItemId}`);
                        if (subtotalEl && resData.data.subtotal_formatted) {
                            subtotalEl.textContent = resData.data.subtotal_formatted;
                        }
                        updateSummaryUI(resData.data);
                    }
                })
                .catch((err) => {
                    showToast(err.message, true);
                });
        }

        // Hapus item dari keranjang
        function deleteCartItem(cartItemId) {
            const cardEl = document.getElementById(`cart-item-${cartItemId}`);
            if (cardEl) {
                cardEl.style.opacity = '0.4';
                cardEl.style.pointerEvents = 'none';
            }

            fetch(`{{ url('/cart/items') }}/${cartItemId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
                .then(async (res) => {
                    const data = await res.json();
                    if (!res.ok) {
                        if (cardEl) {
                            cardEl.style.opacity = '1';
                            cardEl.style.pointerEvents = 'auto';
                        }
                        throw new Error(data.message || 'Gagal menghapus produk');
                    }
                    return data;
                })
                .then((resData) => {
                    if (cardEl) {
                        cardEl.remove();
                    }
                    showToast('Produk berhasil dihapus dari keranjang.');
                    updateSummaryUI(resData.data);
                })
                .catch((err) => {
                    showToast(err.message, true);
                });
        }

        // Tambah varian rekomendasi (upsell)
        function addUpsellProduct(productId) {
            fetch(`{{ route('cart.items.store') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ product_id: productId, jumlah: 1 })
            })
                .then(async (res) => {
                    const data = await res.json();
                    if (!res.ok) {
                        throw new Error(data.message || 'Gagal menambahkan produk');
                    }
                    return data;
                })
                .then(() => {
                    showToast('Produk ditambahkan ke keranjang!');
                    setTimeout(() => window.location.reload(), 350);
                })
                .catch((err) => {
                    showToast(err.message, true);
                });
        }
    </script>
@endpush