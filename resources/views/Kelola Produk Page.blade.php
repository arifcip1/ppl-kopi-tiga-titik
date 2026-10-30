@extends('layouts.owner')

@section('title', 'Kelola Produk')
@section('active_menu', 'kelola-produk')
@section('page_title', 'Kelola Produk')
@section('role_badge', 'OWNER')
@section('page_subtitle', 'Atur katalog menu, harga jual, stok harian, dan informasi keawetan')

@section('content')

    <!-- ============================================================== -->
    <!-- TOMBOL KHUSUS MOBILE: + TAMBAH PRODUK BARU (SANGAT JELAS DILIHAT) -->
    <!-- ============================================================== -->
    <div class="md:hidden">
        <button onclick="openModal('modal-tambah')" class="w-full py-3 bg-blue-900 hover:bg-blue-800 text-white rounded-2xl font-bold text-xs flex items-center justify-center gap-2 shadow-md transition-all active:scale-98 cursor-pointer">
            <span class="material-symbols-outlined text-lg">add_circle</span>
            <span>+ Tambah Produk Kopi Baru</span>
        </button>
    </div>

    <!-- Ringkasan Statistik Inventaris (Sesuai F2 & F10) -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2.5 md:gap-4">
        <!-- Total Produk -->
        <div class="p-3.5 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-900 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-xl">inventory_2</span>
            </div>
            <div>
                <div class="text-[11px] text-slate-500 font-medium">Total Menu</div>
                <div class="text-base md:text-lg font-extrabold text-slate-900 font-['Plus_Jakarta_Sans']">4 Varian</div>
            </div>
        </div>

        <!-- Total Stok Fisik -->
        <div class="p-3.5 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-xl">kitchen</span>
            </div>
            <div>
                <div class="text-[11px] text-slate-500 font-medium">Stok Cooler Box</div>
                <div class="text-base md:text-lg font-extrabold text-slate-900 font-['Plus_Jakarta_Sans']">25 Botol</div>
            </div>
        </div>

        <!-- Peringatan Restok (Stok < 5 sesuai BR-19 & F10) -->
        <div class="p-3.5 bg-white rounded-2xl shadow-xs border border-rose-200 bg-rose-50/40 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-xl">warning</span>
            </div>
            <div>
                <div class="text-[11px] text-rose-700 font-semibold">Perlu Restok (&lt;5)</div>
                <div class="text-base md:text-lg font-extrabold text-rose-800 font-['Plus_Jakarta_Sans']">1 Menu</div>
            </div>
        </div>

        <!-- Status Katalog -->
        <div class="p-3.5 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-900 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-xl">storefront</span>
            </div>
            <div>
                <div class="text-[11px] text-slate-500 font-medium">Katalog Online</div>
                <div class="text-xs md:text-sm font-bold text-emerald-600 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>4 Menu Aktif</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar with Add Product Button (Desktop) -->
    <div class="p-3 md:p-4 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
        <!-- Search Input -->
        <div class="relative flex-1 max-w-md">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
            <input type="text" id="searchInput" onkeyup="filterProducts()" placeholder="Cari nama menu / varian rasa..." class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder:text-slate-400 focus:outline-none focus:border-blue-900 transition-colors" />
        </div>

        <div class="flex items-center justify-between lg:justify-end gap-2.5 flex-wrap">
            <!-- Kategori Chips Filter -->
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-0.5">
                <button onclick="setCategoryFilter('all')" class="category-btn active px-3.5 py-1.5 bg-blue-900 text-white rounded-xl text-xs font-semibold shadow-xs whitespace-nowrap cursor-pointer transition-all">Semua (4)</button>
                <button onclick="setCategoryFilter('kopi-susu')" class="category-btn px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-medium whitespace-nowrap cursor-pointer transition-all">Kopi Susu</button>
                <button onclick="setCategoryFilter('kopi-murni')" class="category-btn px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-medium whitespace-nowrap cursor-pointer transition-all">Kopi Murni</button>
                <button onclick="setCategoryFilter('non-kopi')" class="category-btn px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-medium whitespace-nowrap cursor-pointer transition-all">Non-Kopi</button>
            </div>

            <!-- Tombol Tambah Produk (Khusus Desktop di Halaman Kelola Produk) -->
            <button onclick="openModal('modal-tambah')" class="hidden md:flex px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white rounded-xl text-xs font-bold items-center gap-1.5 shadow-xs transition-all hover:shadow-md cursor-pointer shrink-0">
                <span class="material-symbols-outlined text-base">add_circle</span>
                <span>Tambah Produk Baru</span>
            </button>
        </div>
    </div>

    <!-- DAFTAR PRODUK (Mobile: Card Stack | Desktop: Grid Responsif 2-4 Kolom) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3.5" id="productGrid">

        <!-- PRODUK 1: Titik Temu -->
        <div class="product-card p-3.5 bg-white rounded-2xl shadow-xs border border-slate-200/90 hover:border-blue-900/40 hover:shadow-md transition-all flex flex-col justify-between" data-category="kopi-susu" data-name="titik temu gula aren">
            <div>
                <!-- Header Card -->
                <div class="flex justify-between items-center mb-2">
                    <span class="px-2 py-0.5 bg-blue-100 text-blue-900 font-bold text-[10px] rounded-full">Kopi Susu</span>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span class="text-[10px] font-semibold text-emerald-700">Aktif di Katalog</span>
                    </div>
                </div>

                <!-- Visual Produk & Info Pokok -->
                <div class="flex md:flex-col items-center gap-3">
                    <div class="w-20 h-24 md:w-full md:h-36 bg-slate-50 rounded-xl flex items-center justify-center p-2 border border-slate-100 relative shrink-0">
                        <img src="{{ asset('products/Titik Temu.png') }}" alt="Titik Temu" class="w-full h-full object-contain" onerror="this.src='https://placehold.co/150x200/1e3a8a/ffffff?text=Titik+Temu'" />
                        <span class="absolute top-1.5 right-1.5 px-1.5 py-0.2 bg-blue-900 text-white text-[9px] font-bold rounded">250 ml</span>
                    </div>
                    <div class="flex-1 w-full">
                        <h3 class="font-bold text-slate-900 text-sm md:text-base font-['Plus_Jakarta_Sans']">Titik Temu</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Kopi Susu Gula Aren</p>
                        <p class="text-[10px] text-slate-400 mt-0.5 line-clamp-1">Espresso blend, susu segar, gula aren murni</p>

                        <!-- Harga & Stok Pill -->
                        <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <div class="text-[9px] text-slate-400 uppercase font-bold">Harga Jual</div>
                                <div class="text-sm font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">Rp 10.000</div>
                            </div>
                            <div class="text-right">
                                <div class="text-[9px] text-slate-400 uppercase font-bold">Stok Tersedia</div>
                                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-[11px] font-bold inline-block">8 botol</span>
                            </div>
                        </div>

                        <!-- Info Keawetan (Sesuai BR-01) -->
                        <div class="mt-2 p-1.5 bg-slate-50 rounded-lg flex items-center gap-1.5 text-[10px] text-slate-500">
                            <span class="material-symbols-outlined text-xs text-blue-900">ac_unit</span>
                            <span>Keawetan: <strong>5–7 hari</strong> di lemari pendingin</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="mt-3.5 pt-2.5 border-t border-slate-100 grid grid-cols-2 gap-2">
                <button onclick="openEditModal('Titik Temu', '10000', '8', 'Kopi Susu Gula Aren', 'Espresso blend, susu segar, gula aren murni', 'kopi-susu')" class="py-2 px-3 bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-900 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-sm">edit</span>
                    <span>Ubah</span>
                </button>
                <button onclick="openDeleteModal('Titik Temu')" class="py-2 px-3 bg-white hover:bg-rose-50 border border-slate-200 hover:border-rose-300 text-slate-600 hover:text-rose-700 rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-sm text-rose-600">block</span>
                    <span>Nonaktifkan</span>
                </button>
            </div>
        </div>

        <!-- PRODUK 2: Titik Hitam -->
        <div class="product-card p-3.5 bg-white rounded-2xl shadow-xs border border-slate-200/90 hover:border-blue-900/40 hover:shadow-md transition-all flex flex-col justify-between" data-category="kopi-murni" data-name="titik hitam americano">
            <div>
                <!-- Header Card -->
                <div class="flex justify-between items-center mb-2">
                    <span class="px-2 py-0.5 bg-neutral-100 text-neutral-800 font-bold text-[10px] rounded-full">Kopi Murni</span>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span class="text-[10px] font-semibold text-emerald-700">Aktif di Katalog</span>
                    </div>
                </div>

                <!-- Visual & Info -->
                <div class="flex md:flex-col items-center gap-3">
                    <div class="w-20 h-24 md:w-full md:h-36 bg-slate-50 rounded-xl flex items-center justify-center p-2 border border-slate-100 relative shrink-0">
                        <img src="{{ asset('products/Titik Hitam.png') }}" alt="Titik Hitam" class="w-full h-full object-contain" onerror="this.src='https://placehold.co/150x200/1e3a8a/ffffff?text=Titik+Hitam'" />
                        <span class="absolute top-1.5 right-1.5 px-1.5 py-0.2 bg-blue-900 text-white text-[9px] font-bold rounded">250 ml</span>
                    </div>
                    <div class="flex-1 w-full">
                        <h3 class="font-bold text-slate-900 text-sm md:text-base font-['Plus_Jakarta_Sans']">Titik Hitam</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Americano Dingin Pekat</p>
                        <p class="text-[10px] text-slate-400 mt-0.5 line-clamp-1">Espresso double shot, air mineral dingin</p>

                        <!-- Harga & Stok -->
                        <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <div class="text-[9px] text-slate-400 uppercase font-bold">Harga Jual</div>
                                <div class="text-sm font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">Rp 8.000</div>
                            </div>
                            <div class="text-right">
                                <div class="text-[9px] text-slate-400 uppercase font-bold">Stok Tersedia</div>
                                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-[11px] font-bold inline-block">10 botol</span>
                            </div>
                        </div>

                        <div class="mt-2 p-1.5 bg-slate-50 rounded-lg flex items-center gap-1.5 text-[10px] text-slate-500">
                            <span class="material-symbols-outlined text-xs text-blue-900">ac_unit</span>
                            <span>Keawetan: <strong>5–7 hari</strong> di lemari pendingin</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aksi -->
            <div class="mt-3.5 pt-2.5 border-t border-slate-100 grid grid-cols-2 gap-2">
                <button onclick="openEditModal('Titik Hitam', '8000', '10', 'Americano Dingin Pekat', 'Espresso double shot, air mineral dingin', 'kopi-murni')" class="py-2 px-3 bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-900 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-sm">edit</span>
                    <span>Ubah</span>
                </button>
                <button onclick="openDeleteModal('Titik Hitam')" class="py-2 px-3 bg-white hover:bg-rose-50 border border-slate-200 hover:border-rose-300 text-slate-600 hover:text-rose-700 rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-sm text-rose-600">block</span>
                    <span>Nonaktifkan</span>
                </button>
            </div>
        </div>

        <!-- PRODUK 3: Titik Manja (SKENARIO KRITIS: Stok < 5 Sesuai BR-19) -->
        <div class="product-card p-3.5 bg-white rounded-2xl shadow-xs border-2 border-rose-300 hover:shadow-md transition-all flex flex-col justify-between relative" data-category="kopi-susu" data-name="titik manja karamel">
            <div>
                <!-- Header Card -->
                <div class="flex justify-between items-center mb-2">
                    <span class="px-2 py-0.5 bg-amber-100 text-amber-800 font-bold text-[10px] rounded-full">Kopi Karamel</span>
                    <span class="px-2 py-0.5 bg-rose-100 text-rose-700 font-bold text-[9px] rounded-full animate-pulse">⚠️ RESTOK!</span>
                </div>

                <!-- Visual & Info -->
                <div class="flex md:flex-col items-center gap-3">
                    <div class="w-20 h-24 md:w-full md:h-36 bg-rose-50/50 rounded-xl flex items-center justify-center p-2 border border-rose-100 relative shrink-0">
                        <img src="{{ asset('products/Titik Manja.png') }}" alt="Titik Manja" class="w-full h-full object-contain" onerror="this.src='https://placehold.co/150x200/1e3a8a/ffffff?text=Titik+Manja'" />
                        <span class="absolute top-1.5 right-1.5 px-1.5 py-0.2 bg-blue-900 text-white text-[9px] font-bold rounded">250 ml</span>
                    </div>
                    <div class="flex-1 w-full">
                        <h3 class="font-bold text-slate-900 text-sm md:text-base font-['Plus_Jakarta_Sans']">Titik Manja</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Kopi Susu Karamel Lembut</p>
                        <p class="text-[10px] text-slate-400 mt-0.5 line-clamp-1">Espresso, susu segar, sirup karamel gurih</p>

                        <!-- Harga & Stok Kritis -->
                        <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <div class="text-[9px] text-slate-400 uppercase font-bold">Harga Jual</div>
                                <div class="text-sm font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">Rp 10.000</div>
                            </div>
                            <div class="text-right">
                                <div class="text-[9px] text-rose-600 uppercase font-bold">Stok Kritis</div>
                                <span class="px-2 py-0.5 bg-rose-100 text-rose-700 border border-rose-200 rounded-full text-[11px] font-extrabold inline-block">Sisa 2 botol</span>
                            </div>
                        </div>

                        <div class="mt-2 p-1.5 bg-rose-50 rounded-lg flex items-center gap-1.5 text-[10px] text-rose-700 font-medium">
                            <span class="material-symbols-outlined text-xs">production_quantity_limits</span>
                            <span>Saran: Buat <strong>8 botol</strong> hari ini</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aksi -->
            <div class="mt-3.5 pt-2.5 border-t border-slate-100 grid grid-cols-2 gap-2">
                <button onclick="openEditModal('Titik Manja', '10000', '2', 'Kopi Susu Karamel Lembut', 'Espresso, susu segar, sirup karamel gurih', 'kopi-susu')" class="py-2 px-3 bg-blue-900 hover:bg-blue-800 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-colors cursor-pointer shadow-xs">
                    <span class="material-symbols-outlined text-sm">edit</span>
                    <span>Update Stok</span>
                </button>
                <button onclick="openDeleteModal('Titik Manja')" class="py-2 px-3 bg-white hover:bg-rose-50 border border-slate-200 hover:border-rose-300 text-slate-600 hover:text-rose-700 rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-sm text-rose-600">block</span>
                    <span>Nonaktifkan</span>
                </button>
            </div>
        </div>

        <!-- PRODUK 4: Titik Jenuh -->
        <div class="product-card p-3.5 bg-white rounded-2xl shadow-xs border border-slate-200/90 hover:border-blue-900/40 hover:shadow-md transition-all flex flex-col justify-between" data-category="non-kopi" data-name="titik jenuh cokelat coklat">
            <div>
                <!-- Header Card -->
                <div class="flex justify-between items-center mb-2">
                    <span class="px-2 py-0.5 bg-stone-100 text-stone-800 font-bold text-[10px] rounded-full">Non-Kopi</span>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span class="text-[10px] font-semibold text-emerald-700">Aktif di Katalog</span>
                    </div>
                </div>

                <!-- Visual & Info -->
                <div class="flex md:flex-col items-center gap-3">
                    <div class="w-20 h-24 md:w-full md:h-36 bg-slate-50 rounded-xl flex items-center justify-center p-2 border border-slate-100 relative shrink-0">
                        <img src="{{ asset('products/Titik Jenuh.png') }}" alt="Titik Jenuh" class="w-full h-full object-contain" onerror="this.src='https://placehold.co/150x200/1e3a8a/ffffff?text=Titik+Jenuh'" />
                        <span class="absolute top-1.5 right-1.5 px-1.5 py-0.2 bg-blue-900 text-white text-[9px] font-bold rounded">250 ml</span>
                    </div>
                    <div class="flex-1 w-full">
                        <h3 class="font-bold text-slate-900 text-sm md:text-base font-['Plus_Jakarta_Sans']">Titik Jenuh</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Cokelat Susu Velvety</p>
                        <p class="text-[10px] text-slate-400 mt-0.5 line-clamp-1">Kakao murni, susu segar, pemanis alami</p>

                        <!-- Harga & Stok -->
                        <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <div class="text-[9px] text-slate-400 uppercase font-bold">Harga Jual</div>
                                <div class="text-sm font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">Rp 12.000</div>
                            </div>
                            <div class="text-right">
                                <div class="text-[9px] text-slate-400 uppercase font-bold">Stok Tersedia</div>
                                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-[11px] font-bold inline-block">5 botol</span>
                            </div>
                        </div>

                        <div class="mt-2 p-1.5 bg-slate-50 rounded-lg flex items-center gap-1.5 text-[10px] text-slate-500">
                            <span class="material-symbols-outlined text-xs text-blue-900">ac_unit</span>
                            <span>Keawetan: <strong>5–7 hari</strong> di lemari pendingin</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aksi -->
            <div class="mt-3.5 pt-2.5 border-t border-slate-100 grid grid-cols-2 gap-2">
                <button onclick="openEditModal('Titik Jenuh', '12000', '5', 'Cokelat Susu Velvety', 'Kakao murni, susu segar, pemanis alami', 'non-kopi')" class="py-2 px-3 bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-900 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-sm">edit</span>
                    <span>Ubah</span>
                </button>
                <button onclick="openDeleteModal('Titik Jenuh')" class="py-2 px-3 bg-white hover:bg-rose-50 border border-slate-200 hover:border-rose-300 text-slate-600 hover:text-rose-700 rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-sm text-rose-600">block</span>
                    <span>Nonaktifkan</span>
                </button>
            </div>
        </div>

    </div>

    <!-- ============================================================== -->
    <!-- MODAL 1: TAMBAH PRODUK BARU (FR-2.2 & FR-2.3) -->
    <!-- ============================================================== -->
    <div id="modal-tambah" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden opacity-0 transition-opacity duration-200">
        <div class="bg-white rounded-3xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-100 flex flex-col">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-900 flex items-center justify-center">
                        <span class="material-symbols-outlined text-lg">add_box</span>
                    </div>
                    <h3 class="font-bold text-slate-900 text-base font-['Plus_Jakarta_Sans']">Tambah Produk Kopi Baru</h3>
                </div>
                <button onclick="closeModal('modal-tambah')" class="w-8 h-8 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-700 transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>

            <!-- Form Body -->
            <form onsubmit="handleFormSubmit(event, 'Produk baru berhasil ditambahkan!')" class="p-6 flex flex-col gap-4">
                <!-- Upload Foto Produk (FR-2.3: max 2MB, jpg/png/webp) -->
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1.5">Foto Botol Produk (Maks. 2 MB) *</label>
                    <div class="border-2 border-dashed border-slate-200 hover:border-blue-900 rounded-2xl p-4 flex flex-col items-center justify-center bg-slate-50 hover:bg-blue-50/30 transition-colors cursor-pointer text-center relative group" onclick="document.getElementById('file-upload-tambah').click()">
                        <input type="file" id="file-upload-tambah" accept="image/png, image/jpeg, image/webp" class="hidden" onchange="previewImage(this, 'preview-tambah')" />
                        <div id="preview-tambah-container" class="flex flex-col items-center">
                            <span class="material-symbols-outlined text-3xl text-slate-400 group-hover:text-blue-900 transition-colors">add_photo_alternate</span>
                            <p class="text-xs font-semibold text-slate-700 mt-1">Pilih Foto atau Seret ke Sini</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Format: JPG, PNG, atau WebP (Standar botol 250 ml)</p>
                        </div>
                        <img id="preview-tambah" class="hidden max-h-32 object-contain rounded-lg" alt="Preview Foto" />
                    </div>
                </div>

                <!-- Nama & Kategori -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Nama Varian Produk *</label>
                        <input type="text" placeholder="Contoh: Titik Rindu" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-blue-900" />
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Kategori Menu *</label>
                        <select required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-blue-900">
                            <option value="kopi-susu">Kopi Susu</option>
                            <option value="kopi-murni">Kopi Murni (Black)</option>
                            <option value="non-kopi">Non-Kopi / Cokelat</option>
                        </select>
                    </div>
                </div>

                <!-- Deskripsi Rasa -->
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Deskripsi Singkat / Profil Rasa *</label>
                    <input type="text" placeholder="Contoh: Kopi susu rasa vanila lembut manis seimbang" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-blue-900" />
                </div>

                <!-- Harga & Stok Awal -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Harga Jual (Rp) *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" min="1000" step="500" placeholder="10000" required class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 font-bold focus:outline-none focus:border-blue-900" />
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Stok Awal Harian (Botol) *</label>
                        <input type="number" min="0" placeholder="10" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 font-bold focus:outline-none focus:border-blue-900" />
                    </div>
                </div>

                <!-- Komposisi Bahan & Informasi Keawetan (Wajib BR-01) -->
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Daftar Bahan / Komposisi *</label>
                    <textarea rows="2" placeholder="Contoh: Espresso house blend, susu pasteurisasi, sirup vanila (tanpa takaran)" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-blue-900"></textarea>
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Masa Keawetan & Penyimpanan *</label>
                    <input type="text" value="5–7 hari bila disimpan di lemari pendingin" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-blue-900" />
                </div>

                <!-- Toggle Status Aktif di Katalog -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-slate-900">Tampilkan di Katalog Online</div>
                        <div class="text-[10px] text-slate-500">Pelanggan langsung bisa melihat dan memesan menu ini</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" checked class="sr-only peer" />
                        <div class="w-10 h-5.5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4.5 after:w-4.5 after:transition-all peer-checked:bg-blue-900"></div>
                    </label>
                </div>

                <!-- Action Buttons -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" onclick="closeModal('modal-tambah')" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition-colors cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-blue-900 hover:bg-blue-800 text-white rounded-xl text-xs font-bold shadow-sm transition-all hover:shadow-md cursor-pointer flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm">save</span>
                        <span>Simpan Menu Baru</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 2: UBAH PRODUK (EDIT HARGA, STOK, FOTO) -->
    <!-- ============================================================== -->
    <div id="modal-edit" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden opacity-0 transition-opacity duration-200">
        <div class="bg-white rounded-3xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-100 flex flex-col">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-900 flex items-center justify-center">
                        <span class="material-symbols-outlined text-lg">edit_note</span>
                    </div>
                    <h3 class="font-bold text-slate-900 text-base font-['Plus_Jakarta_Sans']">Ubah Informasi Produk</h3>
                </div>
                <button onclick="closeModal('modal-edit')" class="w-8 h-8 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-700 transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>

            <!-- Form Body -->
            <form onsubmit="handleFormSubmit(event, 'Perubahan data produk berhasil disimpan!')" class="p-6 flex flex-col gap-4">
                <!-- Ganti Foto -->
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1.5">Ganti Foto Produk (Opsional)</label>
                    <div class="flex items-center gap-3 p-3 bg-slate-50 border border-slate-200 rounded-2xl">
                        <div class="w-14 h-16 bg-white rounded-xl border border-slate-200 flex items-center justify-center p-1 shrink-0">
                            <img id="edit-preview-img" src="{{ asset('products/Titik Temu.png') }}" alt="Preview" class="w-full h-full object-contain" />
                        </div>
                        <div class="flex-1">
                            <button type="button" onclick="document.getElementById('file-upload-edit').click()" class="px-3 py-1.5 bg-white hover:bg-slate-100 border border-slate-300 rounded-xl text-xs font-bold text-slate-700 flex items-center gap-1.5 cursor-pointer">
                                <span class="material-symbols-outlined text-sm">upload</span>
                                <span>Pilih Foto Baru</span>
                            </button>
                            <p class="text-[10px] text-slate-400 mt-1">Maks. 2 MB • JPG, PNG, atau WebP</p>
                            <input type="file" id="file-upload-edit" accept="image/png, image/jpeg, image/webp" class="hidden" onchange="previewImage(this, 'edit-preview-img')" />
                        </div>
                    </div>
                </div>

                <!-- Nama & Kategori -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Nama Produk *</label>
                        <input type="text" id="edit-nama" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 font-bold focus:outline-none focus:border-blue-900" />
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Kategori *</label>
                        <select id="edit-kategori" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-blue-900">
                            <option value="kopi-susu">Kopi Susu</option>
                            <option value="kopi-murni">Kopi Murni (Black)</option>
                            <option value="non-kopi">Non-Kopi / Cokelat</option>
                        </select>
                    </div>
                </div>

                <!-- Harga & Stok Baru (FR-2.4) -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Harga Jual (Rp) *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" id="edit-harga" min="1000" step="500" required class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 font-bold focus:outline-none focus:border-blue-900" />
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Stok Harian (Botol) *</label>
                        <input type="number" id="edit-stok" min="0" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 font-bold focus:outline-none focus:border-blue-900" />
                    </div>
                </div>

                <!-- Deskripsi & Komposisi -->
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Deskripsi Singkat *</label>
                    <input type="text" id="edit-deskripsi" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-blue-900" />
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Komposisi Bahan *</label>
                    <textarea id="edit-komposisi" rows="2" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-blue-900"></textarea>
                </div>

                <!-- Toggle Aktif/Nonaktif -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-slate-900">Status Produk</div>
                        <div class="text-[10px] text-slate-500">Nonaktifkan jika sementara tidak ingin dijual di katalog</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" checked class="sr-only peer" />
                        <div class="w-10 h-5.5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4.5 after:w-4.5 after:transition-all peer-checked:bg-blue-900"></div>
                    </label>
                </div>

                <!-- Action Buttons -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" onclick="closeModal('modal-edit')" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition-colors cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-blue-900 hover:bg-blue-800 text-white rounded-xl text-xs font-bold shadow-sm transition-all hover:shadow-md cursor-pointer flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm">save</span>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 3: KONFIRMASI NONAKTIFKAN (SESUAI BR-27) -->
    <!-- ============================================================== -->
    <div id="modal-delete" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden opacity-0 transition-opacity duration-200">
        <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-slate-100 text-center flex flex-col items-center">
            <div class="w-14 h-14 rounded-2xl bg-rose-100 text-rose-700 flex items-center justify-center mb-3">
                <span class="material-symbols-outlined text-3xl">block</span>
            </div>
            <h3 class="font-bold text-slate-900 text-base font-['Plus_Jakarta_Sans']">Nonaktifkan Produk Ini?</h3>
            <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                Apakah Anda yakin ingin menonaktifkan menu <strong id="delete-nama" class="text-slate-900">Titik Temu</strong> dari katalog online?
            </p>

            <!-- Penjelasan Regulasi PRD BR-27 -->
            <div class="mt-3 p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-[10px] text-slate-500 text-left flex items-start gap-2">
                <span class="material-symbols-outlined text-sm text-blue-900 shrink-0">info</span>
                <span>Sesuai aturan sistem (BR-27), menu tidak dihapus permanen agar tidak merusak data riwayat pesanan pelanggan masa lalu. Menu dapat diaktifkan kembali kapan saja.</span>
            </div>

            <div class="mt-5 w-full grid grid-cols-2 gap-2.5">
                <button onclick="closeModal('modal-delete')" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition-colors cursor-pointer">Batal</button>
                <button onclick="confirmDeactivate()" class="w-full py-2.5 bg-rose-700 hover:bg-rose-800 text-white rounded-xl text-xs font-bold transition-colors shadow-xs cursor-pointer">Ya, Nonaktifkan</button>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed top-5 left-1/2 -translate-x-1/2 bg-blue-900 text-white px-5 py-3 rounded-2xl shadow-xl flex items-center gap-2 text-xs font-bold z-50 transition-all duration-300 opacity-0 -translate-y-5 pointer-events-none">
        <span class="material-symbols-outlined text-emerald-400 text-base">check_circle</span>
        <span id="toast-msg">Aksi berhasil dilakukan</span>
    </div>

@endsection

@push('scripts')
    <!-- ============================================================== -->
    <!-- INTERACTIVE JAVASCRIPT LOGIC (CRUD MODALS, PREVIEW, FILTERS)    -->
    <!-- ============================================================== -->
    <script>
        // Modal Open & Close Helper
        function openModal(id) {
            const modal = document.getElementById(id);
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
            }, 10);
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            modal.classList.add('opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 200);
        }

        // Modal Edit Pre-population
        function openEditModal(nama, harga, stok, deskripsi, komposisi, kategori) {
            document.getElementById('edit-nama').value = nama;
            document.getElementById('edit-harga').value = harga;
            document.getElementById('edit-stok').value = stok;
            document.getElementById('edit-deskripsi').value = deskripsi;
            document.getElementById('edit-komposisi').value = komposisi;
            document.getElementById('edit-kategori').value = kategori;

            const imgMap = {
                'Titik Temu': "{{ asset('products/Titik Temu.png') }}",
                'Titik Hitam': "{{ asset('products/Titik Hitam.png') }}",
                'Titik Manja': "{{ asset('products/Titik Manja.png') }}",
                'Titik Jenuh': "{{ asset('products/Titik Jenuh.png') }}"
            };
            document.getElementById('edit-preview-img').src = imgMap[nama] || "https://placehold.co/150x200/1e3a8a/ffffff?text=KTT";

            openModal('modal-edit');
        }

        // Modal Delete / Deactivate
        let selectedProductForDeactivate = '';
        function openDeleteModal(nama) {
            selectedProductForDeactivate = nama;
            document.getElementById('delete-nama').innerText = nama;
            openModal('modal-delete');
        }

        function confirmDeactivate() {
            closeModal('modal-delete');
            showToast('Menu ' + selectedProductForDeactivate + ' berhasil dinonaktifkan dari katalog!');
        }

        // Image Preview Helper (FR-2.3)
        function previewImage(input, previewId) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.getElementById(previewId);
                    img.src = e.target.result;
                    img.classList.remove('hidden');
                    const placeholder = document.getElementById('preview-tambah-container');
                    if (placeholder) placeholder.classList.add('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Form Submit Handler
        function handleFormSubmit(event, message) {
            event.preventDefault();
            closeModal('modal-tambah');
            closeModal('modal-edit');
            showToast(message);
        }

        // Toast Notification
        function showToast(message) {
            const toast = document.getElementById('toast');
            const msg = document.getElementById('toast-msg');
            msg.innerText = message;
            toast.classList.remove('opacity-0', '-translate-y-5', 'pointer-events-none');
            setTimeout(() => {
                toast.classList.add('opacity-0', '-translate-y-5', 'pointer-events-none');
            }, 3000);
        }

        // Search and Category Filter
        let activeCategory = 'all';

        function setCategoryFilter(category) {
            activeCategory = category;
            document.querySelectorAll('.category-btn').forEach(btn => {
                btn.classList.remove('bg-blue-900', 'text-white', 'shadow-xs');
                btn.classList.add('bg-slate-100', 'text-slate-700');
            });
            event.currentTarget.classList.remove('bg-slate-100', 'text-slate-700');
            event.currentTarget.classList.add('bg-blue-900', 'text-white', 'shadow-xs');
            filterProducts();
        }

        function filterProducts() {
            const query = document.getElementById('searchInput').value.toLowerCase();
            const cards = document.querySelectorAll('.product-card');

            cards.forEach(card => {
                const name = card.getAttribute('data-name');
                const category = card.getAttribute('data-category');
                const matchesCategory = (activeCategory === 'all' || category === activeCategory);
                const matchesQuery = name.includes(query);

                if (matchesCategory && matchesQuery) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        }
    </script>
@endpush
