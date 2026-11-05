@extends('layouts.pelanggan')

@section('title', 'Katalog Menu - Kopi Tiga Titik')
@section('active_menu', 'katalog')
@section('page_title', 'Katalog Menu')
@section('role_badge', strtoupper(Auth::check() && in_array(Auth::user()->role, ['admin', 'owner']) ? 'OWNER' : (Auth::user()->role ?? 'PELANGGAN')))
@section('page_subtitle', 'Pilih varian kopi botol 250 ml favoritmu langsung dari lapak resmi Kopi Tiga Titik')

@section('content')

    <!-- 1. Lapak Status & Service Selector (Delivery vs Self Pickup) -->
    <div class="p-3.5 md:p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div class="flex items-center gap-3 flex-wrap">
            <span class="relative flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
            </span>
            <div class="flex items-center gap-1.5">
                <span class="material-symbols-outlined text-rose-500 text-lg">location_on</span>
                <span class="text-xs md:text-sm font-bold text-slate-800">Lapak Aktif: Jl. Kuwung, Mergelo, Kota Mojokerto</span>
            </div>
            <span class="px-2.5 py-0.5 bg-slate-100 rounded-full border border-slate-200/60 text-[11px] font-medium text-slate-600">
                ⏰ 15.00 - 18.00 WIB
            </span>
        </div>

        <!-- Delivery / Pickup Switcher (Interaktif) -->
        <div class="p-1 bg-slate-100 rounded-xl border border-slate-200/60 flex items-center gap-1 self-stretch sm:self-auto justify-center">
            <button id="btn-delivery" onclick="setServiceType('delivery')" class="px-3.5 py-1.5 bg-blue-900 text-white rounded-lg shadow-xs text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer">
                <span class="material-symbols-outlined text-sm">moped</span>
                <span>Delivery</span>
            </button>
            <button id="btn-pickup" onclick="setServiceType('pickup')" class="px-3.5 py-1.5 text-slate-600 hover:text-slate-900 rounded-lg text-xs font-medium flex items-center gap-1.5 transition-colors cursor-pointer">
                <span class="material-symbols-outlined text-sm">storefront</span>
                <span>Self Pickup</span>
            </button>
        </div>
    </div>

    <!-- 2. Search Toolbar & Filter Kategori Chips (Selaras dengan Kelola Produk) -->
    <div class="p-3 md:p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
        <!-- Search Input -->
        <div class="relative flex-1 max-w-md">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
            <input type="text" id="searchInput" onkeyup="filterProducts()" placeholder="Cari nama kopi / varian rasa..." class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder:text-slate-400 focus:outline-none focus:border-blue-900 transition-colors" />
        </div>

        <!-- Kategori Chips Filter -->
        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-0.5" id="categoryChips">
            <button onclick="filterCategory(this, 'all')" class="cat-chip active px-3.5 py-1.5 bg-blue-900 text-white rounded-xl text-xs font-semibold shadow-xs whitespace-nowrap cursor-pointer transition-all">Semua Menu ({{ $products->count() }})</button>
            <button onclick="filterCategory(this, 'kopi-susu')" class="cat-chip px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-medium whitespace-nowrap cursor-pointer transition-all">Kopi Susu</button>
            <button onclick="filterCategory(this, 'kopi-murni')" class="cat-chip px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-medium whitespace-nowrap cursor-pointer transition-all">Kopi Murni</button>
            <button onclick="filterCategory(this, 'non-kopi')" class="cat-chip px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-medium whitespace-nowrap cursor-pointer transition-all">Non-Kopi</button>
        </div>
    </div>

    <!-- 3. DAFTAR PRODUK (Full Width: Mobile 1 Kolom | Tablet 2 Kolom | Desktop 4 Kolom Sesuai Kelola Produk) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" id="productGrid">

        @forelse ($products as $product)
            @php
                // Klasifikasi kategori untuk filter chips
                $category = 'kopi-susu';
                $rasaLower = strtolower($product->rasa . ' ' . $product->nama . ' ' . $product->deskripsi);
                if (str_contains($rasaLower, 'americano') || str_contains($rasaLower, 'hitam') || str_contains($rasaLower, 'murni')) {
                    $category = 'kopi-murni';
                    $categoryLabel = 'Kopi Murni';
                    $badgeClass = 'bg-neutral-900 text-white';
                } elseif (str_contains($rasaLower, 'coklat') || str_contains($rasaLower, 'cokelat') || str_contains($rasaLower, 'non-kopi') || str_contains($rasaLower, 'tea') || str_contains($rasaLower, 'teh')) {
                    $category = 'non-kopi';
                    $categoryLabel = 'Non-Kopi';
                    $badgeClass = 'bg-stone-700 text-white';
                } elseif (str_contains($rasaLower, 'caramel') || str_contains($rasaLower, 'karamel')) {
                    $category = 'kopi-susu';
                    $categoryLabel = 'Kopi Karamel';
                    $badgeClass = 'bg-amber-800 text-white';
                } else {
                    $category = 'kopi-susu';
                    $categoryLabel = 'Kopi Susu';
                    $badgeClass = 'bg-blue-900 text-white';
                }

                // Resolusi foto produk (FR-2.3 & QE Fallback Placeholder)
                $fotoUrl = $product->foto_path 
                    ? asset('storage/' . $product->foto_path) 
                    : (file_exists(public_path('products/' . $product->nama . '.png')) 
                        ? asset('products/' . $product->nama . '.png') 
                        : 'https://placehold.co/150x200/1e3a8a/ffffff?text=' . urlencode($product->nama));

                $hargaFormatted = 'Rp ' . number_format($product->harga, 0, ',', '.');
                $isOutOfStock = $product->stok <= 0;
                $isLowStock = !$isOutOfStock && $product->stok <= 5;
            @endphp

            <div class="product-card p-4 bg-white rounded-2xl shadow-xs border {{ $isLowStock ? 'border-2 border-amber-300' : 'border-slate-200/90' }} hover:border-blue-900/40 hover:shadow-md transition-all flex flex-col justify-between group"
                data-id="{{ $product->id }}"
                data-nama="{{ $product->nama }}"
                data-rasa="{{ $product->rasa ?: $product->nama }}"
                data-harga-formatted="{{ $hargaFormatted }}"
                data-harga="{{ $product->harga }}"
                data-foto="{{ $fotoUrl }}"
                data-stok="{{ $product->stok }}"
                data-deskripsi="{{ $product->deskripsi }}"
                data-komposisi="{{ $product->komposisi }}"
                data-keawetan="{{ $product->keawetan ?: '5–7 hari bila disimpan di dalam lemari pendingin' }}"
                data-category="{{ $category }}"
                data-name="{{ strtolower($product->nama . ' ' . $product->rasa . ' ' . $product->komposisi) }}">
                <div>
                    <!-- Header Card: Badge Kategori & Status Stok (FR-3.2 & BR-19) -->
                    <div class="flex justify-between items-center mb-2.5">
                        <span class="px-2.5 py-0.5 {{ $badgeClass }} font-bold text-[10px] rounded-full shadow-xs">
                            {{ $categoryLabel }}
                        </span>
                        
                        @if ($isOutOfStock)
                            <span class="px-2 py-0.5 bg-rose-100 text-rose-700 border border-rose-300 rounded-full text-[10px] font-bold flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                <span>Stok Habis</span>
                            </span>
                        @elseif ($isLowStock)
                            <span class="px-2 py-0.5 bg-rose-50 text-rose-700 border border-rose-200 rounded-full text-[10px] font-bold flex items-center gap-1 animate-pulse">
                                <span>⏰ Sisa {{ $product->stok }} botol!</span>
                            </span>
                        @else
                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-full text-[10px] font-bold flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span>Stok: {{ $product->stok }} botol</span>
                            </span>
                        @endif
                    </div>

                    <!-- Gambar Produk & Click Detail Trigger (FR-3.2 & FR-3.3) -->
                    <div class="h-44 sm:h-48 bg-slate-50 rounded-xl flex items-center justify-center p-2.5 border border-slate-100 relative overflow-hidden group-hover:bg-blue-50/20 transition-colors cursor-pointer" onclick="openProductDetailFromCard(this)">
                        <img src="{{ $fotoUrl }}" alt="{{ $product->nama }}" class="h-full object-contain group-hover:scale-105 transition-transform duration-300" onerror="this.src='https://placehold.co/150x200/1e3a8a/ffffff?text={{ urlencode($product->nama) }}'" />
                        <span class="absolute bottom-2 right-2 px-1.5 py-0.5 bg-white/90 text-slate-700 text-[9px] font-bold rounded shadow-xs border border-slate-200">250 ml</span>
                    </div>

                    <!-- Detail Singkat -->
                    <div class="mt-3">
                        <h3 class="font-bold text-slate-900 text-sm md:text-base font-['Plus_Jakarta_Sans'] group-hover:text-blue-900 transition-colors cursor-pointer" onclick="openProductDetailFromCard(this)">
                            {{ $product->nama }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $product->rasa ?: $product->nama }} (250 ml)</p>
                        <p class="text-[11px] text-slate-400 mt-0.5 line-clamp-1">{{ $product->komposisi ?: $product->deskripsi }}</p>

                        <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <div class="text-[9px] text-slate-400 uppercase font-bold">Harga Jual</div>
                                <div class="text-base font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">{{ $hargaFormatted }}</div>
                            </div>
                            <div class="flex items-center gap-1 text-[10px] text-slate-500">
                                <span class="material-symbols-outlined text-xs text-blue-900">ac_unit</span>
                                <span>{{ $product->keawetan ? Str::limit($product->keawetan, 15) : '5-7 hr kulkas' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Button (FR-3.4: Jika stok 0 tombol pesan nonaktif) -->
                <div class="mt-3.5 pt-2.5 border-t border-slate-100 flex flex-col items-center">
                    @if ($isOutOfStock)
                        <button disabled class="w-full py-2.5 bg-slate-200 text-slate-400 rounded-xl shadow-xs text-xs font-bold flex items-center justify-center gap-1.5 cursor-not-allowed">
                            <span class="material-symbols-outlined text-base">remove_shopping_cart</span>
                            <span>Stok Habis</span>
                        </button>
                    @else
                        <button onclick="addToCart(this, '{{ addslashes($product->nama) }}', {{ $product->harga }}, {{ $product->id }})" class="w-full py-2.5 bg-blue-900 hover:bg-blue-800 text-white rounded-xl shadow-xs text-xs font-bold flex items-center justify-center gap-1.5 transition-all active:scale-98 cursor-pointer">
                            <span class="material-symbols-outlined text-base">shopping_cart</span>
                            <span>+ Keranjang</span>
                        </button>
                        <span class="cart-msg hidden text-[11px] font-semibold text-emerald-600 mt-1.5 flex items-center gap-1 transition-all">
                            <span class="material-symbols-outlined text-xs">check_circle</span>
                            <span>Ditambahkan ke keranjang</span>
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full p-12 bg-white rounded-2xl border border-slate-200 text-center flex flex-col items-center justify-center">
                <span class="material-symbols-outlined text-5xl text-slate-300 mb-3">coffee</span>
                <h3 class="text-base font-bold text-slate-700">Belum Ada Menu Aktif</h3>
                <p class="text-xs text-slate-400 mt-1">Saat ini produk belum tersedia di katalog. Silakan cek kembali nanti.</p>
            </div>
        @endforelse

    </div>

    <!-- Empty Search State -->
    <div id="emptySearch" class="hidden p-8 bg-white rounded-2xl border border-slate-200 text-center flex-col items-center justify-center">
        <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">search_off</span>
        <p class="text-sm font-bold text-slate-700">Menu tidak ditemukan</p>
        <p class="text-xs text-slate-400 mt-1">Coba gunakan kata kunci pencarian yang lain.</p>
    </div>

    <!-- Modal Detail Produk (PRD FR-3.3 & FR-3.4) -->
    <div id="product-detail-modal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-4">
        <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl border border-slate-100 overflow-hidden flex flex-col animate-in fade-in zoom-in-95 duration-200">
            <!-- Modal Header -->
            <div class="p-4 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Detail Varian Kopi</span>
                <button onclick="closeProductDetail()" class="w-8 h-8 rounded-full hover:bg-slate-200 flex items-center justify-center text-slate-500 transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-5 md:p-6 flex flex-col gap-4 max-h-[80vh] overflow-y-auto">
                <div class="h-44 bg-slate-50 rounded-2xl border border-slate-100 flex justify-center items-center p-2 relative">
                    <img id="modal-img" src="" alt="Foto Produk" class="h-36 object-contain" />
                    <span class="absolute bottom-2.5 right-2.5 px-2 py-0.5 bg-white text-slate-700 text-xs font-semibold rounded-lg shadow-xs border border-slate-200">
                        Isi Bersih 250 ml
                    </span>
                </div>

                <div>
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 id="modal-title" class="text-lg md:text-xl font-bold text-slate-900 font-['Plus_Jakarta_Sans']"></h2>
                            <p id="modal-subtitle" class="text-xs text-slate-500 font-medium"></p>
                        </div>
                        <div class="text-right">
                            <span id="modal-price" class="text-lg md:text-xl font-extrabold text-blue-900 font-['Plus_Jakarta_Sans'] block"></span>
                            <div id="modal-stock-indicator" class="mt-1 flex justify-end"></div>
                        </div>
                    </div>
                </div>

                <!-- Info Cards: Rasa, Bahan, Keawetan (Sesuai PRD F3) -->
                <div class="flex flex-col gap-2.5 text-xs">
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/70">
                        <div class="font-bold text-slate-800 flex items-center gap-1.5 mb-1">
                            <span class="material-symbols-outlined text-sm text-blue-900">coffee</span>
                            <span>Deskripsi Rasa:</span>
                        </div>
                        <p id="modal-desc" class="text-slate-600 leading-relaxed"></p>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/70">
                        <div class="font-bold text-slate-800 flex items-center gap-1.5 mb-1">
                            <span class="material-symbols-outlined text-sm text-blue-900">receipt</span>
                            <span>Daftar Bahan / Komposisi:</span>
                        </div>
                        <p id="modal-ingredients" class="text-slate-600 leading-relaxed"></p>
                    </div>

                    <div class="p-3 bg-blue-50/60 rounded-xl border border-blue-100 flex items-center gap-2 text-blue-900">
                        <span class="material-symbols-outlined text-base">ac_unit</span>
                        <div>
                            <span class="font-bold">Masa Keawetan:</span>
                            <span id="modal-keawetan">5 - 7 hari bila disimpan di dalam lemari pendingin (kulkas).</span>
                        </div>
                    </div>
                </div>

                <!-- Stepper & Action Modal (FR-3.4) -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
                    <div id="modal-stepper-container" class="flex items-center gap-2 border border-slate-200 rounded-xl p-1 bg-slate-50">
                        <button onclick="decrementModalQty()" class="w-8 h-8 rounded-lg bg-white shadow-xs font-bold text-slate-700 hover:bg-slate-100 flex items-center justify-center transition-colors cursor-pointer">
                            −
                        </button>
                        <span id="modal-qty" class="w-7 text-center font-bold text-slate-800 text-sm">1</span>
                        <button onclick="incrementModalQty()" class="w-8 h-8 rounded-lg bg-blue-900 text-white font-bold hover:bg-blue-800 flex items-center justify-center transition-colors cursor-pointer">
                            +
                        </button>
                    </div>

                    <button id="modal-submit-btn" onclick="confirmAddToCart()" class="flex-1 py-3 bg-blue-900 hover:bg-blue-800 text-white rounded-xl shadow-md text-xs md:text-sm font-bold flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                        <span class="material-symbols-outlined text-base">shopping_cart</span>
                        <span id="modal-submit-text">Tambah ke Keranjang</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    let currentVariantPrice = 10000;
    let currentModalQty = 1;
    let currentVariantName = '';
    let currentProductStock = 0;
    let currentProductId = 0;
    let totalCartQty = {{ $cartCount ?? 0 }};

    function openProductDetailFromCard(triggerEl) {
        const card = triggerEl.closest('.product-card');
        if (!card) return;

        const id = card.getAttribute('data-id') || '0';
        const title = card.getAttribute('data-nama') || '';
        const subtitle = card.getAttribute('data-rasa') || title;
        const priceFormatted = card.getAttribute('data-harga-formatted') || '';
        const priceNum = parseInt(card.getAttribute('data-harga') || '0');
        const imgSrc = card.getAttribute('data-foto') || '';
        const stock = parseInt(card.getAttribute('data-stok') || '0');
        const desc = card.getAttribute('data-deskripsi') || '';
        const ingredients = card.getAttribute('data-komposisi') || '';
        const keawetan = card.getAttribute('data-keawetan') || '5–7 hari bila disimpan di dalam lemari pendingin (kulkas)';

        openProductDetail(title, subtitle, priceFormatted, priceNum, imgSrc, stock, desc, ingredients, keawetan, id);
    }

    function openProductDetail(title, subtitle, priceFormatted, priceNum, imgSrc, stock, desc, ingredients, keawetan, id = 0) {
        currentProductId = parseInt(id) || 0;
        document.getElementById('modal-title').textContent = title;
        document.getElementById('modal-subtitle').textContent = subtitle + ' (250 ml)';
        document.getElementById('modal-price').textContent = priceFormatted;
        document.getElementById('modal-img').src = imgSrc;
        document.getElementById('modal-desc').textContent = desc;
        document.getElementById('modal-ingredients').textContent = ingredients;
        
        const keawetanEl = document.getElementById('modal-keawetan');
        if (keawetanEl && keawetan) {
            keawetanEl.textContent = keawetan;
        }

        currentVariantName = title;
        currentVariantPrice = parseInt(priceNum) || 0;
        currentProductStock = parseInt(stock) || 0;

        // Update Stock Indicator & Button State in Modal (FR-3.4)
        const stockIndicator = document.getElementById('modal-stock-indicator');
        const submitBtn = document.getElementById('modal-submit-btn');
        const submitText = document.getElementById('modal-submit-text');

        if (currentProductStock <= 0) {
            currentModalQty = 0;
            document.getElementById('modal-qty').textContent = '0';
            if (stockIndicator) {
                stockIndicator.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold flex items-center gap-1 bg-rose-100 text-rose-700 border border-rose-300';
                stockIndicator.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span><span>Stok Habis</span>';
            }
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.className = 'flex-1 py-3 bg-slate-200 text-slate-400 rounded-xl shadow-xs text-xs md:text-sm font-bold flex items-center justify-center gap-1.5 cursor-not-allowed';
            }
            if (submitText) {
                submitText.textContent = 'Stok Habis';
            }
        } else {
            currentModalQty = 1;
            document.getElementById('modal-qty').textContent = '1';
            if (stockIndicator) {
                if (currentProductStock <= 5) {
                    stockIndicator.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold flex items-center gap-1 bg-rose-50 text-rose-700 border border-rose-200 animate-pulse';
                    stockIndicator.innerHTML = '<span>⏰ Sisa ' + currentProductStock + ' botol!</span>';
                } else {
                    stockIndicator.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold flex items-center gap-1 bg-emerald-50 text-emerald-800 border border-emerald-200';
                    stockIndicator.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span><span>Stok: ' + currentProductStock + ' botol</span>';
                }
            }
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.className = 'flex-1 py-3 bg-blue-900 hover:bg-blue-800 text-white rounded-xl shadow-md text-xs md:text-sm font-bold flex items-center justify-center gap-1.5 transition-colors cursor-pointer';
            }
            if (submitText) {
                submitText.textContent = 'Tambah ke Keranjang';
            }
        }

        const modal = document.getElementById('product-detail-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeProductDetail() {
        const modal = document.getElementById('product-detail-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function incrementModalQty() {
        if (currentProductStock <= 0) return;
        if (currentModalQty < currentProductStock) {
            currentModalQty++;
            document.getElementById('modal-qty').textContent = currentModalQty;
        }
    }

    function decrementModalQty() {
        if (currentModalQty > 1) {
            currentModalQty--;
            document.getElementById('modal-qty').textContent = currentModalQty;
        }
    }

    function updateCartUI() {
        // Update Sidebar Cart Badge
        const sidebarBadge = document.getElementById('sidebar-cart-badge');
        if (sidebarBadge) {
            sidebarBadge.textContent = totalCartQty;
            if (totalCartQty > 0) {
                sidebarBadge.classList.remove('hidden');
                sidebarBadge.classList.add('flex');
            } else {
                sidebarBadge.classList.add('hidden');
                sidebarBadge.classList.remove('flex');
            }
        }
    }

    // Sinkronisasi badge saat halaman dimuat
    updateCartUI();

    function addToCart(btn, variantName, price, productId = 0) {
        @guest
            window.location.href = "{{ route('login') }}";
            return;
        @endguest

        totalCartQty++;
        updateCartUI();

        // Simpan ke database jika ada productId
        if (productId) {
            fetch("{{ route('cart.items.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ product_id: productId, jumlah: 1 })
            }).then(async res => {
                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.message || 'Gagal menambahkan produk');
                }
                return data;
            }).then(data => {
                if (data && data.cart_count !== undefined) {
                    totalCartQty = data.cart_count;
                    updateCartUI();
                }
            }).catch(err => {
                alert(err.message);
                updateCartUI();
            });
        }

        // Show feedback message under the button without browser alert
        const parent = btn.parentElement;
        const msg = parent.querySelector('.cart-msg');
        if (msg) {
            msg.classList.remove('hidden');
            const originalContent = btn.innerHTML;
            btn.classList.remove('bg-blue-900', 'hover:bg-blue-800');
            btn.classList.add('bg-emerald-600', 'hover:bg-emerald-700');
            btn.innerHTML = '<span class="material-symbols-outlined text-base">check</span> <span>Tersimpan!</span>';

            setTimeout(() => {
                msg.classList.add('hidden');
                btn.classList.remove('bg-emerald-600', 'hover:bg-emerald-700');
                btn.classList.add('bg-blue-900', 'hover:bg-blue-800');
                btn.innerHTML = originalContent;
            }, 2000);
        }
    }

    function confirmAddToCart() {
        if (currentProductStock <= 0) return;
        @guest
            window.location.href = "{{ route('login') }}";
            return;
        @endguest

        const addedQty = currentModalQty;
        const pid = currentProductId;

        totalCartQty += addedQty;
        updateCartUI();
        closeProductDetail();

        if (pid) {
            fetch("{{ route('cart.items.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ product_id: pid, jumlah: addedQty })
            }).then(async res => {
                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.message || 'Gagal menambahkan produk');
                }
                return data;
            }).then(data => {
                if (data && data.cart_count !== undefined) {
                    totalCartQty = data.cart_count;
                    updateCartUI();
                }
            }).catch(err => {
                alert(err.message);
                updateCartUI();
            });
        }
    }

    // Toggle Service Type (Delivery / Pickup color switcher)
    function setServiceType(type) {
        const btnDelivery = document.getElementById('btn-delivery');
        const btnPickup = document.getElementById('btn-pickup');
        
        if (type === 'delivery') {
            btnDelivery.className = 'px-3.5 py-1.5 bg-blue-900 text-white rounded-lg shadow-xs text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer';
            btnPickup.className = 'px-3.5 py-1.5 text-slate-600 hover:text-slate-900 rounded-lg text-xs font-medium flex items-center gap-1.5 transition-colors cursor-pointer';
        } else {
            btnPickup.className = 'px-3.5 py-1.5 bg-blue-900 text-white rounded-lg shadow-xs text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer';
            btnDelivery.className = 'px-3.5 py-1.5 text-slate-600 hover:text-slate-900 rounded-lg text-xs font-medium flex items-center gap-1.5 transition-colors cursor-pointer';
        }
    }

    // Filter Category Chips
    function filterCategory(btn, category) {
        // Reset all category chips styling
        document.querySelectorAll('.cat-chip').forEach(el => {
            el.className = 'cat-chip px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-medium whitespace-nowrap cursor-pointer transition-all';
        });

        // Set active chip styling
        btn.className = 'cat-chip active px-3.5 py-1.5 bg-blue-900 text-white rounded-xl text-xs font-semibold shadow-xs whitespace-nowrap cursor-pointer transition-all';

        // Filter product cards in grid
        const cards = document.querySelectorAll('.product-card');
        let visibleCount = 0;
        cards.forEach(card => {
            const cardCat = card.getAttribute('data-category');
            if (category === 'all' || cardCat === category) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const empty = document.getElementById('emptySearch');
        if (visibleCount === 0) {
            empty.classList.remove('hidden');
            empty.classList.add('flex');
        } else {
            empty.classList.add('hidden');
            empty.classList.remove('flex');
        }
    }

    // Search Filter by Name or Description
    function filterProducts() {
        const query = document.getElementById('searchInput').value.toLowerCase().trim();
        const cards = document.querySelectorAll('.product-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const name = card.getAttribute('data-name') || '';
            if (name.includes(query)) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const empty = document.getElementById('emptySearch');
        if (visibleCount === 0) {
            empty.classList.remove('hidden');
            empty.classList.add('flex');
        } else {
            empty.classList.add('hidden');
            empty.classList.remove('flex');
        }
    }

    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeProductDetail();
        }
    });
</script>
@endpush