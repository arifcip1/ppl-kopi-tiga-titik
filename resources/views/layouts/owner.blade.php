<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Kopi Tiga Titik') - Owner Dashboard</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Tailwind CSS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 antialiased font-sans flex flex-col md:flex-row overflow-x-hidden">

    <!-- ============================================================== -->
    <!-- BACKDROP GELAP MOBILE (HANYA MUNCUL DI MOBILE SAAT SIDEBAR TERBUKA) -->
    <!-- ============================================================== -->
    <div id="mobileBackdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-40 hidden md:hidden transition-opacity duration-300 opacity-0"></div>

    <!-- ============================================================== -->
    <!-- SIDEBAR NAVIGASI KIRI RESMI (ADMIN / OWNER - 8 MENU UTAMA)      -->
    <!-- DESKTOP COLLAPSIBLE (PUSH) & MOBILE 75% DRAWER OVERLAY         -->
    <!-- ============================================================== -->
    <aside id="mainSidebar" class="fixed top-0 bottom-0 left-0 z-50 w-[75vw] max-w-[75vw] md:w-64 md:max-w-64 h-dvh max-h-screen overflow-hidden select-none bg-white border-r border-slate-200/90 shadow-2xl md:shadow-xs flex flex-col justify-between -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
        
        <!-- Bagian Atas: Brand & Identitas Pengguna -->
        <div class="flex flex-col shrink-0">
            <!-- Header Sidebar (Brand & Close Button) -->
            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <img class="w-7 h-7 rounded-lg object-contain shadow-xs" src="{{ asset('images/logo.png') }}" alt="Logo" onerror="this.src='https://placehold.co/100x100/1e3a8a/ffffff?text=KTT'" />
                    <div>
                        <span class="font-extrabold text-blue-900 font-['Plus_Jakarta_Sans'] text-xs tracking-tight block leading-tight">Kopi Tiga Titik</span>
                        <span class="text-[9px] text-slate-400 font-semibold">Street Coffee</span>
                    </div>
                </div>
                <!-- Tombol Tutup Sidebar (Mobile) -->
                <button onclick="toggleSidebar()" class="w-7 h-7 rounded-xl hover:bg-slate-100 text-slate-500 hover:text-slate-800 flex items-center justify-center transition-colors cursor-pointer" title="Tutup Menu">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>

            <!-- Identitas Akun Login di Sidebar -->
            <div class="mx-3 my-2 p-2 bg-blue-50/70 rounded-xl border border-blue-100/80 flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-blue-900 text-white font-extrabold text-xs flex items-center justify-center shadow-xs shrink-0">
                    F
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1">
                        <span class="text-xs font-bold text-slate-900 truncate font-['Plus_Jakarta_Sans']">Farrel</span>
                        <span class="px-1 py-0.2 bg-blue-200/70 text-blue-900 text-[8px] font-extrabold rounded">OWNER</span>
                    </div>
                    <p class="text-[9px] text-slate-500 truncate leading-tight">owner1@gmail.com</p>
                </div>
            </div>
        </div>

        <!-- Bagian Tengah: List 8 Menu Navigasi Utama Resmi (Scrollable aman jika layar pendek) -->
        <div class="flex-1 overflow-y-auto px-2.5 py-1 flex flex-col gap-0.5">
            <div class="px-2.5 py-1 text-[9px] font-bold text-slate-400 uppercase tracking-wider">MENU UTAMA</div>

            @php
                $activeMenu = trim($__env->yieldContent('active_menu', 'kelola-produk'));
            @endphp

            <!-- 1. Dashboard -->
            <a href="{{ url('/owner/dashboard') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-medium transition-colors {{ $activeMenu === 'dashboard' ? 'font-bold text-white bg-blue-900 shadow-xs' : 'text-slate-600 hover:text-blue-900 hover:bg-slate-100' }}">
                <span class="material-symbols-outlined text-lg {{ $activeMenu === 'dashboard' ? 'text-white' : 'text-slate-500' }}">dashboard</span>
                <span>Dashboard</span>
            </a>

            <!-- 2. POS Kasir -->
            <a href="#" class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-medium transition-colors {{ $activeMenu === 'pos' ? 'font-bold text-white bg-blue-900 shadow-xs' : 'text-slate-600 hover:text-blue-900 hover:bg-slate-100' }}">
                <span class="material-symbols-outlined text-lg {{ $activeMenu === 'pos' ? 'text-white' : 'text-slate-500' }}">point_of_sale</span>
                <span>POS Kasir</span>
            </a>

            <!-- 3. Kelola Produk -->
            <a href="{{ url('/owner/produk') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-medium transition-colors {{ $activeMenu === 'kelola-produk' ? 'font-bold text-white bg-blue-900 shadow-xs' : 'text-slate-600 hover:text-blue-900 hover:bg-slate-100' }}">
                <span class="material-symbols-outlined text-lg {{ $activeMenu === 'kelola-produk' ? 'text-white' : 'text-slate-500' }}">inventory_2</span>
                <span>Kelola Produk</span>
            </a>

            <!-- 4. Pesanan -->
            <a href="#" class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-medium transition-colors {{ $activeMenu === 'pesanan' ? 'font-bold text-white bg-blue-900 shadow-xs' : 'text-slate-600 hover:text-blue-900 hover:bg-slate-100' }}">
                <span class="material-symbols-outlined text-lg {{ $activeMenu === 'pesanan' ? 'text-white' : 'text-slate-500' }}">shopping_bag</span>
                <span>Pesanan</span>
            </a>

            <!-- 5. Riwayat Penjualan -->
            <a href="#" class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-medium transition-colors {{ $activeMenu === 'riwayat-penjualan' ? 'font-bold text-white bg-blue-900 shadow-xs' : 'text-slate-600 hover:text-blue-900 hover:bg-slate-100' }}">
                <span class="material-symbols-outlined text-lg {{ $activeMenu === 'riwayat-penjualan' ? 'text-white' : 'text-slate-500' }}">receipt_long</span>
                <span>Riwayat Penjualan</span>
            </a>

            <!-- 6. Prediksi Stok -->
            <a href="{{ url('/owner/prediksi') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-medium transition-colors {{ $activeMenu === 'prediksi-stok' ? 'font-bold text-white bg-blue-900 shadow-xs' : 'text-slate-600 hover:text-blue-900 hover:bg-slate-100' }}">
                <span class="material-symbols-outlined text-lg {{ $activeMenu === 'prediksi-stok' ? 'text-white' : 'text-slate-500' }}">query_stats</span>
                <span>Prediksi Stok</span>
            </a>

            <!-- 7. Pengiriman -->
            <a href="#" class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-medium transition-colors {{ $activeMenu === 'pengiriman' ? 'font-bold text-white bg-blue-900 shadow-xs' : 'text-slate-600 hover:text-blue-900 hover:bg-slate-100' }}">
                <span class="material-symbols-outlined text-lg {{ $activeMenu === 'pengiriman' ? 'text-white' : 'text-slate-500' }}">local_shipping</span>
                <span>Pengiriman</span>
            </a>

            <!-- 8. Pengaturan -->
            <a href="#" class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-medium transition-colors {{ $activeMenu === 'pengaturan' ? 'font-bold text-white bg-blue-900 shadow-xs' : 'text-slate-600 hover:text-blue-900 hover:bg-slate-100' }}">
                <span class="material-symbols-outlined text-lg {{ $activeMenu === 'pengaturan' ? 'text-white' : 'text-slate-500' }}">settings</span>
                <span>Pengaturan</span>
            </a>
        </div>

        <!-- Bagian Bawah: Profil & Logout (Selalu terkunci di dasar, tidak terpotong) -->
        <div class="p-2.5 pb-6 md:pb-3 border-t border-slate-100 flex flex-col gap-0.5 shrink-0 bg-white">
            <!-- Profil -->
            <a href="{{ url('/owner/profil') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-600 hover:text-blue-900 hover:bg-slate-100 transition-colors">
                <span class="material-symbols-outlined text-lg text-slate-500">person</span>
                <span>Profil Pengelola</span>
            </a>

            <!-- Keluar Akun -->
            <a href="{{ url('/login-owner') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50 transition-colors">
                <span class="material-symbols-outlined text-lg">logout</span>
                <span>Keluar Akun</span>
            </a>
        </div>
    </aside>

    <!-- ============================================================== -->
    <!-- AREA KONTEN UTAMA: AUTO RESIZE / PUSHED SAAT SIDEBAR DIBUKA/TUTUP -->
    <!-- ============================================================== -->
    <div id="contentWrapper" class="flex-1 w-full min-h-screen flex flex-col md:pl-64 transition-all duration-300 ease-in-out">

        <!-- Top Sticky Header -->
        <header class="bg-white px-4 md:px-8 py-3.5 border-b border-slate-200/90 shadow-xs flex items-center justify-between sticky top-0 z-30">
            <div class="flex items-center gap-3">
                <!-- Tombol Garis Tiga (Hamburger Menu) Buka/Tutup Sidebar -->
                <button onclick="toggleSidebar()" class="w-9 h-9 rounded-xl bg-slate-50 hover:bg-blue-50 border border-slate-200 text-slate-700 hover:text-blue-900 flex items-center justify-center transition-all cursor-pointer shadow-xs active:scale-95" title="Buka/Tutup Menu Sidebar">
                    <span class="material-symbols-outlined text-2xl font-bold">menu</span>
                </button>

                <div class="flex items-center gap-2">
                    <img class="w-8 h-8 rounded-lg object-contain shadow-xs" src="{{ asset('images/logo.png') }}" alt="Logo" onerror="this.src='https://placehold.co/100x100/1e3a8a/ffffff?text=KTT'" />
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="font-extrabold text-blue-900 font-['Plus_Jakarta_Sans'] text-sm md:text-base tracking-tight">@yield('page_title', 'Kelola Produk')</span>
                            <span class="px-1.5 py-0.2 bg-blue-100 text-blue-900 font-bold text-[9px] rounded uppercase tracking-wider">@yield('role_badge', 'OWNER')</span>
                        </div>
                        <p class="text-[11px] text-slate-500 hidden md:block">@yield('page_subtitle', 'Atur katalog menu, harga jual, stok harian, dan informasi keawetan')</p>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi Kanan Header (Slot Khusus Per Halaman) -->
            <div class="flex items-center gap-2.5">
                @yield('header_actions')
            </div>
        </header>

        <!-- Main Body Content Slot -->
        <main class="px-4 md:px-8 py-5 flex flex-col gap-4 max-w-7xl w-full mx-auto pb-12">
            @yield('content')
        </main>
    </div>

    <!-- JavaScript Navigasi Bersama -->
    <script>
        // SIDEBAR TOGGLE LOGIC (DESKTOP PUSH & MOBILE 75% OVERLAY)
        let isDesktopSidebarOpen = true;

        function toggleSidebar() {
            const sidebar = document.getElementById('mainSidebar');
            const content = document.getElementById('contentWrapper');
            const backdrop = document.getElementById('mobileBackdrop');
            const isMobile = window.innerWidth < 768;

            if (isMobile) {
                // Di Mobile: Buka/Tutup Drawer 75% dengan Backdrop Gelap
                const isClosed = !sidebar.classList.contains('mobile-drawer-open');
                if (isClosed) {
                    sidebar.style.transform = 'translateX(0)';
                    sidebar.classList.add('mobile-drawer-open');
                    backdrop.classList.remove('hidden');
                    setTimeout(() => backdrop.classList.remove('opacity-0'), 10);
                } else {
                    sidebar.style.transform = '';
                    sidebar.classList.remove('mobile-drawer-open');
                    backdrop.classList.add('opacity-0');
                    setTimeout(() => backdrop.classList.add('hidden'), 300);
                }
            } else {
                // Di Desktop: Toggle Collapsible Push / Fullscreen
                if (isDesktopSidebarOpen) {
                    sidebar.style.transform = 'translateX(-100%)';
                    content.classList.remove('md:pl-64');
                    content.classList.add('md:pl-0');
                    isDesktopSidebarOpen = false;
                } else {
                    sidebar.style.transform = 'translateX(0)';
                    content.classList.remove('md:pl-0');
                    content.classList.add('md:pl-64');
                    isDesktopSidebarOpen = true;
                }
            }
        }
    </script>

    @stack('scripts')

</body>
</html>

