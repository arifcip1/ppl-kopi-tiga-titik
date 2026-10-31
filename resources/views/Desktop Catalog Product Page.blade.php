@extends('layouts.pelanggan')

@section('title', 'Katalog Menu - Kopi Tiga Titik')
@section('active_menu', 'katalog')
@section('page_title', 'Katalog Menu')
@section('role_badge', 'PELANGGAN')
@section('page_subtitle', 'Pilih varian kopi botol 250 ml favoritmu langsung dari lapak resmi Kopi Tiga Titik')

@section('header_actions')
    <!-- Tombol Keranjang Belanja di Top Bar Header -->
    <a href="{{ url('/cart') }}" class="relative p-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-colors flex items-center justify-center shadow-xs group" title="Lihat Keranjang Belanja">
        <span class="material-symbols-outlined text-xl group-hover:text-blue-900 transition-colors">shopping_cart</span>
        <span id="header-cart-badge" class="absolute -top-1 -right-1 w-4 h-4 bg-rose-600 text-white text-[10px] font-bold rounded-full flex items-center justify-center shadow-xs border-2 border-white">1</span>
    </a>
@endsection

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
            <button onclick="filterCategory(this, 'all')" class="cat-chip active px-3.5 py-1.5 bg-blue-900 text-white rounded-xl text-xs font-semibold shadow-xs whitespace-nowrap cursor-pointer transition-all">Semua Menu (4)</button>
            <button onclick="filterCategory(this, 'kopi-susu')" class="cat-chip px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-medium whitespace-nowrap cursor-pointer transition-all">Kopi Susu</button>
            <button onclick="filterCategory(this, 'kopi-murni')" class="cat-chip px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-medium whitespace-nowrap cursor-pointer transition-all">Kopi Murni</button>
            <button onclick="filterCategory(this, 'non-kopi')" class="cat-chip px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-medium whitespace-nowrap cursor-pointer transition-all">Non-Kopi</button>
        </div>
    </div>

    <!-- 3. DAFTAR PRODUK (Full Width: Mobile 1 Kolom | Tablet 2 Kolom | Desktop 4 Kolom Sesuai Kelola Produk) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" id="productGrid">

                <!-- PRODUK 1: Titik Temu -->
                <div class="product-card p-4 bg-white rounded-2xl shadow-xs border border-slate-200/90 hover:border-blue-900/40 hover:shadow-md transition-all flex flex-col justify-between group" data-category="kopi-susu" data-name="titik temu gula aren kopi susu">
                    <div>
                        <!-- Header Card: Badge Kategori & Sisa Stok -->
                        <div class="flex justify-between items-center mb-2.5">
                            <span class="px-2.5 py-0.5 bg-blue-900 text-white font-bold text-[10px] rounded-full shadow-xs">
                                Favorit
                            </span>
                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-full text-[10px] font-bold flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span>Stok: 8 botol</span>
                            </span>
                        </div>

                        <!-- Gambar Produk & Click Detail Trigger -->
                        <div class="h-44 sm:h-48 bg-slate-50 rounded-xl flex items-center justify-center p-2.5 border border-slate-100 relative overflow-hidden group-hover:bg-blue-50/20 transition-colors cursor-pointer" onclick="openProductDetail('Titik Temu', 'Kopi Susu Gula Aren', 'Rp 10.000', '10000', '{{ asset('products/Titik Temu.png') }}', '8', 'Perpaduan seimbang espresso house blend dengan gula aren murni organik dan susu segar gurih.', 'Espresso Blend, Susu Segar Pasteurisasi, Gula Aren Organik Cair.')">
                            <img src="{{ asset('products/Titik Temu.png') }}" alt="Titik Temu" class="h-full object-contain group-hover:scale-105 transition-transform duration-300" onerror="this.src='https://placehold.co/150x200/1e3a8a/ffffff?text=Titik+Temu'" />
                            <span class="absolute bottom-2 right-2 px-1.5 py-0.5 bg-white/90 text-slate-700 text-[9px] font-bold rounded shadow-xs border border-slate-200">250 ml</span>
                        </div>

                        <!-- Detail Singkat -->
                        <div class="mt-3">
                            <h3 class="font-bold text-slate-900 text-sm md:text-base font-['Plus_Jakarta_Sans'] group-hover:text-blue-900 transition-colors cursor-pointer" onclick="openProductDetail('Titik Temu', 'Kopi Susu Gula Aren', 'Rp 10.000', '10000', '{{ asset('products/Titik Temu.png') }}', '8', 'Perpaduan seimbang espresso house blend dengan gula aren murni organik dan susu segar gurih.', 'Espresso Blend, Susu Segar Pasteurisasi, Gula Aren Organik Cair.')">
                                Titik Temu
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Kopi Susu Gula Aren (250 ml)</p>
                            <p class="text-[11px] text-slate-400 mt-0.5 line-clamp-1">Espresso blend, susu segar, gula aren murni</p>

                            <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between">
                                <div>
                                    <div class="text-[9px] text-slate-400 uppercase font-bold">Harga Jual</div>
                                    <div class="text-base font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">Rp 10.000</div>
                                </div>
                                <div class="flex items-center gap-1 text-[10px] text-slate-500">
                                    <span class="material-symbols-outlined text-xs text-blue-900">ac_unit</span>
                                    <span>5-7 hr kulkas</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="mt-3.5 pt-2.5 border-t border-slate-100 flex flex-col items-center">
                        <button onclick="addToCart(this, 'Titik Temu', 10000)" class="w-full py-2.5 bg-blue-900 hover:bg-blue-800 text-white rounded-xl shadow-xs text-xs font-bold flex items-center justify-center gap-1.5 transition-all active:scale-98 cursor-pointer">
                            <span class="material-symbols-outlined text-base">shopping_cart</span>
                            <span>+ Keranjang</span>
                        </button>
                        <span class="cart-msg hidden text-[11px] font-semibold text-emerald-600 mt-1.5 flex items-center gap-1 transition-all">
                            <span class="material-symbols-outlined text-xs">check_circle</span>
                            <span>Ditambahkan ke keranjang</span>
                        </span>
                    </div>
                </div>

                <!-- PRODUK 2: Titik Hitam -->
                <div class="product-card p-4 bg-white rounded-2xl shadow-xs border border-slate-200/90 hover:border-blue-900/40 hover:shadow-md transition-all flex flex-col justify-between group" data-category="kopi-murni" data-name="titik hitam americano kopi murni">
                    <div>
                        <!-- Header Card -->
                        <div class="flex justify-between items-center mb-2.5">
                            <span class="px-2.5 py-0.5 bg-neutral-900 text-white font-bold text-[10px] rounded-full shadow-xs">
                                Kopi Murni
                            </span>
                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-full text-[10px] font-bold flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span>Stok: 10 botol</span>
                            </span>
                        </div>

                        <!-- Gambar Produk & Click Detail Trigger -->
                        <div class="h-44 sm:h-48 bg-slate-50 rounded-xl flex items-center justify-center p-2.5 border border-slate-100 relative overflow-hidden group-hover:bg-blue-50/20 transition-colors cursor-pointer" onclick="openProductDetail('Titik Hitam', 'Americano Dingin Pekat', 'Rp 8.000', '8000', '{{ asset('products/Titik Hitam.png') }}', '10', 'Double shot espresso murni dengan air mineral dingin berkualitas, menghadirkan rasa pahit aromatik yang bersih dan menyegarkan.', 'Double Shot Espresso Robusta-Arabika, Air Mineral pH 7+ Dingin.')">
                            <img src="{{ asset('products/Titik Hitam.png') }}" alt="Titik Hitam" class="h-full object-contain group-hover:scale-105 transition-transform duration-300" onerror="this.src='https://placehold.co/150x200/1e3a8a/ffffff?text=Titik+Hitam'" />
                            <span class="absolute bottom-2 right-2 px-1.5 py-0.5 bg-white/90 text-slate-700 text-[9px] font-bold rounded shadow-xs border border-slate-200">250 ml</span>
                        </div>

                        <!-- Detail Singkat -->
                        <div class="mt-3">
                            <h3 class="font-bold text-slate-900 text-sm md:text-base font-['Plus_Jakarta_Sans'] group-hover:text-blue-900 transition-colors cursor-pointer" onclick="openProductDetail('Titik Hitam', 'Americano Dingin Pekat', 'Rp 8.000', '8000', '{{ asset('products/Titik Hitam.png') }}', '10', 'Double shot espresso murni dengan air mineral dingin berkualitas, menghadirkan rasa pahit aromatik yang bersih dan menyegarkan.', 'Double Shot Espresso Robusta-Arabika, Air Mineral pH 7+ Dingin.')">
                                Titik Hitam
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Americano Dingin Pekat (250 ml)</p>
                            <p class="text-[11px] text-slate-400 mt-0.5 line-clamp-1">Espresso double shot, air mineral dingin</p>

                            <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between">
                                <div>
                                    <div class="text-[9px] text-slate-400 uppercase font-bold">Harga Jual</div>
                                    <div class="text-base font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">Rp 8.000</div>
                                </div>
                                <div class="flex items-center gap-1 text-[10px] text-slate-500">
                                    <span class="material-symbols-outlined text-xs text-blue-900">ac_unit</span>
                                    <span>5-7 hr kulkas</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="mt-3.5 pt-2.5 border-t border-slate-100 flex flex-col items-center">
                        <button onclick="addToCart(this, 'Titik Hitam', 8000)" class="w-full py-2.5 bg-blue-900 hover:bg-blue-800 text-white rounded-xl shadow-xs text-xs font-bold flex items-center justify-center gap-1.5 transition-all active:scale-98 cursor-pointer">
                            <span class="material-symbols-outlined text-base">shopping_cart</span>
                            <span>+ Keranjang</span>
                        </button>
                        <span class="cart-msg hidden text-[11px] font-semibold text-emerald-600 mt-1.5 flex items-center gap-1 transition-all">
                            <span class="material-symbols-outlined text-xs">check_circle</span>
                            <span>Ditambahkan ke keranjang</span>
                        </span>
                    </div>
                </div>

                <!-- PRODUK 3: Titik Manja -->
                <div class="product-card p-4 bg-white rounded-2xl shadow-xs border-2 border-amber-300 hover:shadow-md transition-all flex flex-col justify-between group" data-category="kopi-susu" data-name="titik manja karamel kopi susu">
                    <div>
                        <!-- Header Card: Peringatan Stok Menipis -->
                        <div class="flex justify-between items-center mb-2.5">
                            <span class="px-2.5 py-0.5 bg-amber-800 text-white font-bold text-[10px] rounded-full shadow-xs">
                                Kopi Karamel
                            </span>
                            <span class="px-2 py-0.5 bg-rose-50 text-rose-700 border border-rose-200 rounded-full text-[10px] font-bold flex items-center gap-1 animate-pulse">
                                <span>⏰ Sisa 2 botol!</span>
                            </span>
                        </div>

                        <!-- Gambar Produk & Click Detail Trigger -->
                        <div class="h-44 sm:h-48 bg-slate-50 rounded-xl flex items-center justify-center p-2.5 border border-slate-100 relative overflow-hidden group-hover:bg-blue-50/20 transition-colors cursor-pointer" onclick="openProductDetail('Titik Manja', 'Kopi Susu Karamel Lembut', 'Rp 10.000', '10000', '{{ asset('products/Titik Manja.png') }}', '2', 'Espresso creamy dengan saus karamel mentega gurih manis yang lumer di lidah, rasa manis pas dan menyenangkan.', 'Espresso Blend, Susu Segar, Sirup & Saus Karamel Karamelisasi Suhu Rendah.')">
                            <img src="{{ asset('products/Titik Manja.png') }}" alt="Titik Manja" class="h-full object-contain group-hover:scale-105 transition-transform duration-300" onerror="this.src='https://placehold.co/150x200/1e3a8a/ffffff?text=Titik+Manja'" />
                            <span class="absolute bottom-2 right-2 px-1.5 py-0.5 bg-white/90 text-slate-700 text-[9px] font-bold rounded shadow-xs border border-slate-200">250 ml</span>
                        </div>

                        <!-- Detail Singkat -->
                        <div class="mt-3">
                            <h3 class="font-bold text-slate-900 text-sm md:text-base font-['Plus_Jakarta_Sans'] group-hover:text-blue-900 transition-colors cursor-pointer" onclick="openProductDetail('Titik Manja', 'Kopi Susu Karamel Lembut', 'Rp 10.000', '10000', '{{ asset('products/Titik Manja.png') }}', '2', 'Espresso creamy dengan saus karamel mentega gurih manis yang lumer di lidah, rasa manis pas dan menyenangkan.', 'Espresso Blend, Susu Segar, Sirup & Saus Karamel Karamelisasi Suhu Rendah.')">
                                Titik Manja
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Kopi Susu Karamel Lembut (250 ml)</p>
                            <p class="text-[11px] text-slate-400 mt-0.5 line-clamp-1">Espresso, susu segar, sirup karamel gurih</p>

                            <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between">
                                <div>
                                    <div class="text-[9px] text-slate-400 uppercase font-bold">Harga Jual</div>
                                    <div class="text-base font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">Rp 10.000</div>
                                </div>
                                <div class="flex items-center gap-1 text-[10px] text-slate-500">
                                    <span class="material-symbols-outlined text-xs text-blue-900">ac_unit</span>
                                    <span>5-7 hr kulkas</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="mt-3.5 pt-2.5 border-t border-slate-100 flex flex-col items-center">
                        <button onclick="addToCart(this, 'Titik Manja', 10000)" class="w-full py-2.5 bg-blue-900 hover:bg-blue-800 text-white rounded-xl shadow-xs text-xs font-bold flex items-center justify-center gap-1.5 transition-all active:scale-98 cursor-pointer">
                            <span class="material-symbols-outlined text-base">shopping_cart</span>
                            <span>+ Keranjang</span>
                        </button>
                        <span class="cart-msg hidden text-[11px] font-semibold text-emerald-600 mt-1.5 flex items-center gap-1 transition-all">
                            <span class="material-symbols-outlined text-xs">check_circle</span>
                            <span>Ditambahkan ke keranjang</span>
                        </span>
                    </div>
                </div>

                <!-- PRODUK 4: Titik Jenuh -->
                <div class="product-card p-4 bg-white rounded-2xl shadow-xs border border-slate-200/90 hover:border-blue-900/40 hover:shadow-md transition-all flex flex-col justify-between group" data-category="non-kopi" data-name="titik jenuh cokelat velvety non-kopi">
                    <div>
                        <!-- Header Card -->
                        <div class="flex justify-between items-center mb-2.5">
                            <span class="px-2.5 py-0.5 bg-stone-700 text-white font-bold text-[10px] rounded-full shadow-xs">
                                Non-Kopi
                            </span>
                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-full text-[10px] font-bold flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span>Stok: 5 botol</span>
                            </span>
                        </div>

                        <!-- Gambar Produk & Click Detail Trigger -->
                        <div class="h-44 sm:h-48 bg-slate-50 rounded-xl flex items-center justify-center p-2.5 border border-slate-100 relative overflow-hidden group-hover:bg-blue-50/20 transition-colors cursor-pointer" onclick="openProductDetail('Titik Jenuh', 'Cokelat Susu Velvety', 'Rp 12.000', '12000', '{{ asset('products/Titik Jenuh.png') }}', '5', 'Varian non-kopi spesial menggunakan cokelat murni pekat (dark cocoa) dengan susu sapi murni hangat dingin yang kental dan tidak kemanisan.', 'Pure Dark Cocoa Powder 70%, Susu Segar, Gula Tebu Alami.')">
                            <img src="{{ asset('products/Titik Jenuh.png') }}" alt="Titik Jenuh" class="h-full object-contain group-hover:scale-105 transition-transform duration-300" onerror="this.src='https://placehold.co/150x200/1e3a8a/ffffff?text=Titik+Jenuh'" />
                            <span class="absolute bottom-2 right-2 px-1.5 py-0.5 bg-white/90 text-slate-700 text-[9px] font-bold rounded shadow-xs border border-slate-200">250 ml</span>
                        </div>

                        <!-- Detail Singkat -->
                        <div class="mt-3">
                            <h3 class="font-bold text-slate-900 text-sm md:text-base font-['Plus_Jakarta_Sans'] group-hover:text-blue-900 transition-colors cursor-pointer" onclick="openProductDetail('Titik Jenuh', 'Cokelat Susu Velvety', 'Rp 12.000', '12000', '{{ asset('products/Titik Jenuh.png') }}', '5', 'Varian non-kopi spesial menggunakan cokelat murni pekat (dark cocoa) dengan susu sapi murni hangat dingin yang kental dan tidak kemanisan.', 'Pure Dark Cocoa Powder 70%, Susu Segar, Gula Tebu Alami.')">
                                Titik Jenuh
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Cokelat Susu Velvety (250 ml)</p>
                            <p class="text-[11px] text-slate-400 mt-0.5 line-clamp-1">Kakao murni, susu segar, pemanis alami</p>

                            <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between">
                                <div>
                                    <div class="text-[9px] text-slate-400 uppercase font-bold">Harga Jual</div>
                                    <div class="text-base font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">Rp 12.000</div>
                                </div>
                                <div class="flex items-center gap-1 text-[10px] text-slate-500">
                                    <span class="material-symbols-outlined text-xs text-blue-900">ac_unit</span>
                                    <span>5-7 hr kulkas</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="mt-3.5 pt-2.5 border-t border-slate-100 flex flex-col items-center">
                        <button onclick="addToCart(this, 'Titik Jenuh', 12000)" class="w-full py-2.5 bg-blue-900 hover:bg-blue-800 text-white rounded-xl shadow-xs text-xs font-bold flex items-center justify-center gap-1.5 transition-all active:scale-98 cursor-pointer">
                            <span class="material-symbols-outlined text-base">shopping_cart</span>
                            <span>+ Keranjang</span>
                        </button>
                        <span class="cart-msg hidden text-[11px] font-semibold text-emerald-600 mt-1.5 flex items-center gap-1 transition-all">
                            <span class="material-symbols-outlined text-xs">check_circle</span>
                            <span>Ditambahkan ke keranjang</span>
                        </span>
                    </div>
                </div>

    </div>

    <!-- Empty Search State -->
    <div id="emptySearch" class="hidden p-8 bg-white rounded-2xl border border-slate-200 text-center flex-col items-center justify-center">
        <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">search_off</span>
        <p class="text-sm font-bold text-slate-700">Menu tidak ditemukan</p>
        <p class="text-xs text-slate-400 mt-1">Coba gunakan kata kunci pencarian yang lain.</p>
    </div>

    <!-- Modal Detail Produk (PRD FR-3.3) -->
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
                        <span id="modal-price" class="text-lg md:text-xl font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']"></span>
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
                            <span>5 - 7 hari bila disimpan di dalam lemari pendingin (kulkas).</span>
                        </div>
                    </div>
                </div>

                <!-- Stepper & Action Modal -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2 border border-slate-200 rounded-xl p-1 bg-slate-50">
                        <button onclick="decrementModalQty()" class="w-8 h-8 rounded-lg bg-white shadow-xs font-bold text-slate-700 hover:bg-slate-100 flex items-center justify-center transition-colors cursor-pointer">
                            −
                        </button>
                        <span id="modal-qty" class="w-7 text-center font-bold text-slate-800 text-sm">1</span>
                        <button onclick="incrementModalQty()" class="w-8 h-8 rounded-lg bg-blue-900 text-white font-bold hover:bg-blue-800 flex items-center justify-center transition-colors cursor-pointer">
                            +
                        </button>
                    </div>

                    <button onclick="confirmAddToCart()" class="flex-1 py-3 bg-blue-900 hover:bg-blue-800 text-white rounded-xl shadow-md text-xs md:text-sm font-bold flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                        <span class="material-symbols-outlined text-base">shopping_cart</span>
                        <span>Tambah ke Keranjang</span>
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
    let totalCartQty = 1;
    let unitPrice = 10000;
    let currentItemName = 'Titik Temu';

    function openProductDetail(title, subtitle, priceFormatted, priceNum, imgSrc, stock, desc, ingredients) {
        document.getElementById('modal-title').textContent = title;
        document.getElementById('modal-subtitle').textContent = subtitle + ' (250 ml)';
        document.getElementById('modal-price').textContent = priceFormatted;
        document.getElementById('modal-img').src = imgSrc;
        document.getElementById('modal-desc').textContent = desc;
        document.getElementById('modal-ingredients').textContent = ingredients;
        
        currentVariantName = title;
        currentVariantPrice = parseInt(priceNum);
        currentModalQty = 1;
        document.getElementById('modal-qty').textContent = '1';

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
        currentModalQty++;
        document.getElementById('modal-qty').textContent = currentModalQty;
    }

    function decrementModalQty() {
        if (currentModalQty > 1) {
            currentModalQty--;
            document.getElementById('modal-qty').textContent = currentModalQty;
        }
    }

    function updateCartUI() {
        // Update Header Cart Badge
        const headerBadge = document.getElementById('header-cart-badge');
        if (headerBadge) {
            headerBadge.textContent = totalCartQty;
        }
    }

    function addToCart(btn, variantName, price) {
        currentItemName = variantName;
        unitPrice = price;
        totalCartQty++;
        updateCartUI();

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
        totalCartQty += currentModalQty;
        currentItemName = currentVariantName;
        unitPrice = currentVariantPrice;
        updateCartUI();
        closeProductDetail();
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