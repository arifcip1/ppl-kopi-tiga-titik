@extends('layouts.owner')

@section('title', 'Kelola Produk')
@section('active_menu', 'kelola-produk')
@section('page_title', 'Kelola Produk')
@section('role_badge', 'OWNER')
@section('page_subtitle', 'Atur katalog menu, harga jual, stok harian, dan informasi keawetan')

@section('content')

    <!-- Notifikasi Feedback Sukses / Error Validasi -->
    @if(session('success'))
        <div class="p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-2.5 text-xs font-bold shadow-xs">
            <span class="material-symbols-outlined text-emerald-600 text-lg">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-3.5 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-start gap-2.5 text-xs font-semibold shadow-xs">
            <span class="material-symbols-outlined text-rose-600 text-lg shrink-0">error</span>
            <div class="flex-1">
                <p class="font-bold mb-1">Gagal menyimpan data:</p>
                <ul class="list-disc pl-4 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- ============================================================== -->
    <!-- TOMBOL KHUSUS MOBILE: + TAMBAH PRODUK BARU (SANGAT JELAS DILIHAT) -->
    <!-- ============================================================== -->
    <div class="md:hidden">
        <button onclick="openModal('modal-tambah')" class="w-full py-3 bg-blue-900 hover:bg-blue-800 text-white rounded-2xl font-bold text-xs flex items-center justify-center gap-2 shadow-md transition-all active:scale-98 cursor-pointer">
            <span class="material-symbols-outlined text-lg">add_circle</span>
            <span>+ Tambah Produk Kopi Baru</span>
        </button>
    </div>

    <!-- Ringkasan Statistik Inventaris Real-Time (Sesuai F2 & F10) -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2.5 md:gap-4">
        <!-- Total Produk -->
        <div class="p-3.5 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-900 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-xl">inventory_2</span>
            </div>
            <div>
                <div class="text-[11px] text-slate-500 font-medium">Total Menu</div>
                <div class="text-base md:text-lg font-extrabold text-slate-900 font-['Plus_Jakarta_Sans']">{{ $totalMenu }} Varian</div>
            </div>
        </div>

        <!-- Total Stok Fisik -->
        <div class="p-3.5 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-xl">kitchen</span>
            </div>
            <div>
                <div class="text-[11px] text-slate-500 font-medium">Stok Cooler Box</div>
                <div class="text-base md:text-lg font-extrabold text-slate-900 font-['Plus_Jakarta_Sans']">{{ $totalStok }} Botol</div>
            </div>
        </div>

        <!-- Peringatan Restok (Stok <= 5 sesuai BR-10 & BR-19) -->
        <div class="p-3.5 bg-white rounded-2xl shadow-xs border border-rose-200 bg-rose-50/40 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-xl">warning</span>
            </div>
            <div>
                <div class="text-[11px] text-rose-700 font-semibold">Perlu Restok (&le;5)</div>
                <div class="text-base md:text-lg font-extrabold text-rose-800 font-['Plus_Jakarta_Sans']">{{ $perluRestok }} Menu</div>
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
                    <span>{{ $katalogAktif }} Menu Aktif</span>
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
                <button onclick="setCategoryFilter('all')" class="category-btn active px-3.5 py-1.5 bg-blue-900 text-white rounded-xl text-xs font-semibold shadow-xs whitespace-nowrap cursor-pointer transition-all">Semua ({{ $totalMenu }})</button>
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

        @forelse($products as $product)
            @php
                // Aturan Restok (BR-10 & BR-19: stok <= 5)
                $isRestok = $product->stok <= 5;
                // Saran Produksi (BR-20: max(0, 10 - stok))
                $saranProduksi = max(0, 10 - $product->stok);

                // Kategori otomatis untuk filter
                $namaLower = strtolower($product->nama);
                $rasaLower = strtolower($product->rasa ?? '');
                $categorySlug = 'kopi-susu';
                if (str_contains($namaLower, 'hitam') || str_contains($rasaLower, 'americano') || str_contains($rasaLower, 'black')) {
                    $categorySlug = 'kopi-murni';
                } elseif (str_contains($namaLower, 'jenuh') || str_contains($rasaLower, 'coklat') || str_contains($rasaLower, 'cokelat') || str_contains($rasaLower, 'tea') || str_contains($rasaLower, 'non')) {
                    $categorySlug = 'non-kopi';
                }

                // Resolusi foto (prioritas foto upload storage, lalu asset lokal, lalu placeholder)
                $imgSrc = $product->foto_path ? asset('storage/' . $product->foto_path) : (
                    file_exists(public_path('products/' . $product->nama . '.png')) ? asset('products/' . $product->nama . '.png') : 'https://placehold.co/150x200/1e3a8a/ffffff?text=' . urlencode($product->nama)
                );
            @endphp

            <div class="product-card p-3.5 bg-white rounded-2xl shadow-xs {{ $isRestok ? 'border-2 border-rose-300' : 'border border-slate-200/90' }} hover:border-blue-900/40 hover:shadow-md transition-all flex flex-col justify-between relative" data-category="{{ $categorySlug }}" data-name="{{ strtolower($product->nama . ' ' . $product->rasa . ' ' . $product->deskripsi) }}">
                <div>
                    <!-- Header Card -->
                    <div class="flex justify-between items-center mb-2">
                        <span class="px-2 py-0.5 {{ $categorySlug === 'kopi-murni' ? 'bg-neutral-100 text-neutral-800' : ($categorySlug === 'non-kopi' ? 'bg-stone-100 text-stone-800' : 'bg-blue-100 text-blue-900') }} font-bold text-[10px] rounded-full">
                            {{ $product->rasa ?? 'Varian KTT' }}
                        </span>
                        <div class="flex items-center gap-1.5">
                            @if($isRestok)
                                <span class="px-2 py-0.5 bg-rose-100 text-rose-700 font-bold text-[9px] rounded-full animate-pulse">⚠️ RESTOK!</span>
                            @elseif($product->aktif)
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span class="text-[10px] font-semibold text-emerald-700">Aktif di Katalog</span>
                            @else
                                <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                <span class="text-[10px] font-semibold text-slate-500">Nonaktif</span>
                            @endif
                        </div>
                    </div>

                    <!-- Visual Produk & Info Pokok -->
                    <div class="flex md:flex-col items-center gap-3">
                        <div class="w-20 h-24 md:w-full md:h-36 {{ $isRestok ? 'bg-rose-50/50 border border-rose-100' : 'bg-slate-50 border border-slate-100' }} rounded-xl flex items-center justify-center p-2 relative shrink-0">
                            <img src="{{ $imgSrc }}" alt="{{ $product->nama }}" class="w-full h-full object-contain" onerror="this.src='https://placehold.co/150x200/1e3a8a/ffffff?text={{ urlencode($product->nama) }}'" />
                            <span class="absolute top-1.5 right-1.5 px-1.5 py-0.2 bg-blue-900 text-white text-[9px] font-bold rounded">250 ml</span>
                        </div>
                        <div class="flex-1 w-full">
                            <h3 class="font-bold text-slate-900 text-sm md:text-base font-['Plus_Jakarta_Sans']">{{ $product->nama }}</h3>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $product->deskripsi }}</p>
                            <p class="text-[10px] text-slate-400 mt-0.5 line-clamp-1">{{ $product->komposisi }}</p>

                            <!-- Harga & Stok Pill -->
                            <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between">
                                <div>
                                    <div class="text-[9px] text-slate-400 uppercase font-bold">Harga Jual</div>
                                    <div class="text-sm font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">Rp {{ number_format($product->harga, 0, ',', '.') }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-[9px] {{ $isRestok ? 'text-rose-600' : 'text-slate-400' }} uppercase font-bold">
                                        {{ $isRestok ? 'Stok Kritis' : 'Stok Tersedia' }}
                                    </div>
                                    <span class="px-2 py-0.5 {{ $isRestok ? 'bg-rose-100 text-rose-700 border-rose-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200' }} border rounded-full text-[11px] font-bold inline-block">
                                        {{ $isRestok ? 'Sisa ' . $product->stok . ' botol' : $product->stok . ' botol' }}
                                    </span>
                                </div>
                            </div>

                            @if($isRestok)
                                <!-- Peringatan & Saran Produksi (BR-19 & BR-20) -->
                                <div class="mt-2 p-1.5 bg-rose-50 rounded-lg flex items-center gap-1.5 text-[10px] text-rose-700 font-medium">
                                    <span class="material-symbols-outlined text-xs">production_quantity_limits</span>
                                    <span>Saran: Buat <strong>{{ $saranProduksi }} botol</strong> hari ini</span>
                                </div>
                            @else
                                <!-- Info Keawetan (BR-01) -->
                                <div class="mt-2 p-1.5 bg-slate-50 rounded-lg flex items-center gap-1.5 text-[10px] text-slate-500">
                                    <span class="material-symbols-outlined text-xs text-blue-900">ac_unit</span>
                                    <span>Keawetan: <strong>{{ $product->keawetan }}</strong></span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="mt-3.5 pt-2.5 border-t border-slate-100 grid grid-cols-2 gap-2">
                    <button onclick="openEditModal({{ json_encode($product) }}, '{{ $imgSrc }}')" class="py-2 px-3 {{ $isRestok ? 'bg-blue-900 hover:bg-blue-800 text-white' : 'bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-900' }} rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-colors cursor-pointer shadow-xs">
                        <span class="material-symbols-outlined text-sm">edit</span>
                        <span>{{ $isRestok ? 'Update Stok' : 'Ubah' }}</span>
                    </button>
                    @if($product->aktif)
                        <button onclick="openDeleteModal('{{ $product->id }}', '{{ $product->nama }}')" class="py-2 px-3 bg-white hover:bg-rose-50 border border-slate-200 hover:border-rose-300 text-slate-600 hover:text-rose-700 rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                            <span class="material-symbols-outlined text-sm text-rose-600">block</span>
                            <span>Nonaktifkan</span>
                        </button>
                    @else
                        <form action="{{ route('owner.produk.toggle-status', $product->id) }}" method="POST" class="w-full">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full py-2 px-3 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-700 rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                                <span class="material-symbols-outlined text-sm text-emerald-600">check_circle</span>
                                <span>Aktifkan</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <!-- Tampilan Kosong Jika Database Belum Di-seed -->
            <div class="col-span-full p-12 bg-white rounded-3xl border border-dashed border-slate-300 text-center flex flex-col items-center">
                <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-900 mb-3">
                    <span class="material-symbols-outlined text-3xl">inventory_2</span>
                </div>
                <h4 class="font-bold text-slate-900 text-base font-['Plus_Jakarta_Sans']">Belum Ada Menu Produk</h4>
                <p class="text-xs text-slate-500 mt-1 max-w-sm">Klik tombol "Tambah Produk Baru" untuk menambahkan varian kopi botol ke dalam katalog sistem.</p>
                <button onclick="openModal('modal-tambah')" class="mt-4 px-5 py-2.5 bg-blue-900 hover:bg-blue-800 text-white rounded-xl text-xs font-bold flex items-center gap-2 shadow-xs cursor-pointer">
                    <span class="material-symbols-outlined text-sm">add</span>
                    <span>Tambah Produk Sekarang</span>
                </button>
            </div>
        @endforelse

    </div>

    <!-- ============================================================== -->
    <!-- MODAL 1: TAMBAH PRODUK BARU (TERHUBUNG KE CONTROLLER STORE)     -->
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

            <!-- Form Body (POST ke /owner/produk) -->
            <form action="{{ route('owner.produk.store') }}" method="POST" enctype="multipart/form-data" class="p-6 flex flex-col gap-4">
                @csrf

                <!-- Upload Foto Produk (FR-2.3: max 2MB, jpg/png/webp) -->
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1.5">Foto Botol Produk (Maks. 2 MB)</label>
                    <div class="border-2 border-dashed border-slate-200 hover:border-blue-900 rounded-2xl p-4 flex flex-col items-center justify-center bg-slate-50 hover:bg-blue-50/30 transition-colors cursor-pointer text-center relative group" onclick="document.getElementById('file-upload-tambah').click()">
                        <input type="file" id="file-upload-tambah" name="foto" accept="image/png, image/jpeg, image/webp" class="hidden" onchange="previewImage(this, 'preview-tambah')" />
                        <div id="preview-tambah-container" class="flex flex-col items-center">
                            <span class="material-symbols-outlined text-3xl text-slate-400 group-hover:text-blue-900 transition-colors">add_photo_alternate</span>
                            <p class="text-xs font-semibold text-slate-700 mt-1">Pilih Foto atau Seret ke Sini</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Format: JPG, PNG, atau WebP (Standar botol 250 ml)</p>
                        </div>
                        <img id="preview-tambah" class="hidden max-h-32 object-contain rounded-lg" alt="Preview Foto" />
                    </div>
                </div>

                <!-- Nama & Rasa -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Nama Varian Produk *</label>
                        <input type="text" name="nama" placeholder="Contoh: Titik Rindu" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-blue-900" />
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Varian Rasa / Kategori *</label>
                        <input type="text" name="rasa" placeholder="Contoh: Kopi Susu Vanila" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-blue-900" />
                    </div>
                </div>

                <!-- Deskripsi Rasa -->
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Deskripsi Singkat / Profil Rasa *</label>
                    <input type="text" name="deskripsi" placeholder="Contoh: Kopi susu rasa vanila lembut manis seimbang" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-blue-900" />
                </div>

                <!-- Harga & Stok Awal -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Harga Jual (Rp) *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" name="harga" min="1000" step="500" placeholder="10000" required class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 font-bold focus:outline-none focus:border-blue-900" />
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Stok Harian (Botol) *</label>
                        <input type="number" name="stok" min="0" placeholder="10" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 font-bold focus:outline-none focus:border-blue-900" />
                    </div>
                </div>

                <!-- Komposisi Bahan & Informasi Keawetan (Wajib BR-01) -->
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Daftar Bahan / Komposisi *</label>
                    <textarea name="komposisi" rows="2" placeholder="Contoh: Espresso house blend, susu pasteurisasi, sirup vanila (tanpa takaran)" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-blue-900"></textarea>
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Masa Keawetan & Penyimpanan *</label>
                    <input type="text" name="keawetan" value="5–7 hari bila disimpan di lemari pendingin" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-blue-900" />
                </div>

                <!-- Toggle Status Aktif di Katalog -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-slate-900">Tampilkan di Katalog Online</div>
                        <div class="text-[10px] text-slate-500">Pelanggan langsung bisa melihat dan memesan menu ini</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="aktif" value="1" checked class="sr-only peer" />
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
    <!-- MODAL 2: UBAH PRODUK (PUT /owner/produk/{id})                  -->
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

            <!-- Form Body (PUT dinamis via JS action) -->
            <form id="form-edit-produk" method="POST" enctype="multipart/form-data" class="p-6 flex flex-col gap-4">
                @csrf
                @method('PUT')

                <!-- Ganti Foto -->
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1.5">Ganti Foto Produk (Opsional)</label>
                    <div class="flex items-center gap-3 p-3 bg-slate-50 border border-slate-200 rounded-2xl">
                        <div class="w-14 h-16 bg-white rounded-xl border border-slate-200 flex items-center justify-center p-1 shrink-0">
                            <img id="edit-preview-img" src="" alt="Preview" class="w-full h-full object-contain" />
                        </div>
                        <div class="flex-1">
                            <button type="button" onclick="document.getElementById('file-upload-edit').click()" class="px-3 py-1.5 bg-white hover:bg-slate-100 border border-slate-300 rounded-xl text-xs font-bold text-slate-700 flex items-center gap-1.5 cursor-pointer">
                                <span class="material-symbols-outlined text-sm">upload</span>
                                <span>Pilih Foto Baru</span>
                            </button>
                            <p class="text-[10px] text-slate-400 mt-1">Maks. 2 MB • JPG, PNG, atau WebP</p>
                            <input type="file" id="file-upload-edit" name="foto" accept="image/png, image/jpeg, image/webp" class="hidden" onchange="previewImage(this, 'edit-preview-img')" />
                        </div>
                    </div>
                </div>

                <!-- Nama & Rasa -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Nama Produk *</label>
                        <input type="text" id="edit-nama" name="nama" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 font-bold focus:outline-none focus:border-blue-900" />
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Varian Rasa *</label>
                        <input type="text" id="edit-rasa" name="rasa" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-blue-900" />
                    </div>
                </div>

                <!-- Harga & Stok Baru (FR-2.4) -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Harga Jual (Rp) *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" id="edit-harga" name="harga" min="1000" step="500" required class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 font-bold focus:outline-none focus:border-blue-900" />
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Stok Harian (Botol) *</label>
                        <input type="number" id="edit-stok" name="stok" min="0" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 font-bold focus:outline-none focus:border-blue-900" />
                    </div>
                </div>

                <!-- Deskripsi & Komposisi -->
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Deskripsi Singkat *</label>
                    <input type="text" id="edit-deskripsi" name="deskripsi" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-blue-900" />
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Komposisi Bahan *</label>
                    <textarea id="edit-komposisi" name="komposisi" rows="2" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-blue-900"></textarea>
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Masa Keawetan *</label>
                    <input type="text" id="edit-keawetan" name="keawetan" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-blue-900" />
                </div>

                <!-- Toggle Aktif/Nonaktif -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-slate-900">Status Produk</div>
                        <div class="text-[10px] text-slate-500">Nonaktifkan jika sementara tidak ingin dijual di katalog</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="edit-aktif" name="aktif" value="1" class="sr-only peer" />
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
    <!-- MODAL 3: KONFIRMASI NONAKTIFKAN (DELETE /owner/produk/{id})    -->
    <!-- ============================================================== -->
    <div id="modal-delete" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden opacity-0 transition-opacity duration-200">
        <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-slate-100 text-center flex flex-col items-center">
            <div class="w-14 h-14 rounded-2xl bg-rose-100 text-rose-700 flex items-center justify-center mb-3">
                <span class="material-symbols-outlined text-3xl">block</span>
            </div>
            <h3 class="font-bold text-slate-900 text-base font-['Plus_Jakarta_Sans']">Nonaktifkan Produk Ini?</h3>
            <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                Apakah Anda yakin ingin menonaktifkan menu <strong id="delete-nama" class="text-slate-900">...</strong> dari katalog online?
            </p>

            <!-- Penjelasan Regulasi PRD BR-27 -->
            <div class="mt-3 p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-[10px] text-slate-500 text-left flex items-start gap-2">
                <span class="material-symbols-outlined text-sm text-blue-900 shrink-0">info</span>
                <span>Sesuai aturan sistem (BR-27), menu tidak dihapus permanen agar tidak merusak data riwayat pesanan pelanggan masa lalu. Menu dapat diaktifkan kembali kapan saja.</span>
            </div>

            <!-- Form Deactivate -->
            <form id="form-delete-produk" method="POST" class="mt-5 w-full grid grid-cols-2 gap-2.5">
                @csrf
                @method('DELETE')
                <button type="button" onclick="closeModal('modal-delete')" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition-colors cursor-pointer">Batal</button>
                <button type="submit" class="w-full py-2.5 bg-rose-700 hover:bg-rose-800 text-white rounded-xl text-xs font-bold transition-colors shadow-xs cursor-pointer">Ya, Nonaktifkan</button>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <!-- ============================================================== -->
    <!-- INTERACTIVE JAVASCRIPT LOGIC                                   -->
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

        // Modal Edit Pre-population dari Data Objek Produk Nyata
        function openEditModal(product, imgSrc) {
            const form = document.getElementById('form-edit-produk');
            form.action = "{{ url('/owner/produk') }}/" + product.id;

            document.getElementById('edit-nama').value = product.nama || '';
            document.getElementById('edit-rasa').value = product.rasa || '';
            document.getElementById('edit-harga').value = product.harga || '';
            document.getElementById('edit-stok').value = product.stok !== undefined ? product.stok : '';
            document.getElementById('edit-deskripsi').value = product.deskripsi || '';
            document.getElementById('edit-komposisi').value = product.komposisi || '';
            document.getElementById('edit-keawetan').value = product.keawetan || '5–7 hari bila disimpan di lemari pendingin';
            document.getElementById('edit-aktif').checked = Boolean(product.aktif);

            document.getElementById('edit-preview-img').src = imgSrc;

            openModal('modal-edit');
        }

        // Modal Deactivate Pre-population
        function openDeleteModal(productId, productName) {
            const form = document.getElementById('form-delete-produk');
            form.action = "{{ url('/owner/produk') }}/" + productId;

            document.getElementById('delete-nama').innerText = productName;
            openModal('modal-delete');
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
                    if (placeholder && previewId === 'preview-tambah') {
                        placeholder.classList.add('hidden');
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Live Search & Category Filtering
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
