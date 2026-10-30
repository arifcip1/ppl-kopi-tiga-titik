<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Kurir Hub') - Kopi Tiga Titik</title>

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

    <!-- Backdrop Gelap Mobile (Saat Sidebar Terbuka) -->
    <div id="mobileBackdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-40 hidden md:hidden transition-opacity duration-300 opacity-0"></div>

    <!-- Sidebar Navigasi Kiri (Kurir Hub) -->
    <aside id="mainSidebar" class="fixed top-0 bottom-0 left-0 z-50 w-[75vw] max-w-[75vw] md:w-64 md:max-w-64 h-dvh max-h-screen overflow-hidden select-none bg-white border-r border-slate-200/90 shadow-2xl md:shadow-xs flex flex-col justify-between -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
        
        <!-- Bagian Atas: Brand & Identitas Kurir -->
        <div class="flex flex-col shrink-0">
            <!-- Header Sidebar -->
            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <img class="w-7 h-7 rounded-lg object-contain shadow-xs" src="{{ asset('images/logo.png') }}" alt="Logo" onerror="this.src='https://placehold.co/100x100/1e3a8a/ffffff?text=KTT'" />
                    <div>
                        <span class="font-extrabold text-blue-900 font-['Plus_Jakarta_Sans'] text-xs tracking-tight block leading-tight">Kopi Tiga Titik</span>
                        <span class="text-[9px] text-slate-400 font-semibold">Kurir Pengantaran</span>
                    </div>
                </div>
                <!-- Tombol Tutup Sidebar (Mobile) -->
                <button onclick="toggleSidebar()" class="w-7 h-7 rounded-xl hover:bg-slate-100 text-slate-500 hover:text-slate-800 flex items-center justify-center transition-colors cursor-pointer" title="Tutup Menu">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>

            <!-- Identitas Akun Kurir -->
            <div class="mx-3 my-2 p-2 bg-blue-50/70 rounded-xl border border-blue-100/80 flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-blue-900 text-white font-extrabold text-xs flex items-center justify-center shadow-xs shrink-0">
                    I
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1">
                        <span class="text-xs font-bold text-slate-900 truncate font-['Plus_Jakarta_Sans']">Ilham</span>
                        <span class="px-1 py-0.2 bg-emerald-100 text-emerald-800 text-[8px] font-extrabold rounded">KURIR</span>
                    </div>
                    <p class="text-[9px] text-slate-500 truncate leading-tight">kurir1ilham@gmail.com</p>
                </div>
            </div>
        </div>

        <!-- Bagian Tengah: Menu Kurir -->
        <div class="flex-1 overflow-y-auto px-2.5 py-1 flex flex-col gap-0.5">
            <div class="px-2.5 py-1 text-[9px] font-bold text-slate-400 uppercase tracking-wider">NAVIGASI KURIR</div>

            @php
                $activeMenu = trim($__env->yieldContent('active_menu', 'profil'));
            @endphp

            <!-- 1. Tugas Pengantaran -->
            <a href="{{ url('/kurir/tugas-baru') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-medium transition-colors {{ $activeMenu === 'dashboard' ? 'font-bold text-white bg-blue-900 shadow-xs' : 'text-slate-600 hover:text-blue-900 hover:bg-slate-100' }}">
                <span class="material-symbols-outlined text-lg {{ $activeMenu === 'dashboard' ? 'text-white' : 'text-slate-500' }}">grid_view</span>
                <span>Tugas Baru</span>
            </a>

            <!-- 2. Riwayat Pengantaran / Selesai -->
            <a href="{{ url('/kurir/selesai') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-medium transition-colors {{ $activeMenu === 'pengantaran' ? 'font-bold text-white bg-blue-900 shadow-xs' : 'text-slate-600 hover:text-blue-900 hover:bg-slate-100' }}">
                <span class="material-symbols-outlined text-lg {{ $activeMenu === 'pengantaran' ? 'text-white' : 'text-slate-500' }}">local_shipping</span>
                <span>Riwayat Selesai</span>
            </a>
        </div>

        <!-- Bagian Bawah: Profil & Logout -->
        <div class="p-2.5 pb-6 md:pb-3 border-t border-slate-100 flex flex-col gap-0.5 shrink-0 bg-white">
            <!-- Profil Kurir -->
            <a href="{{ url('/kurir/profil') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-semibold transition-colors {{ $activeMenu === 'profil' ? 'font-bold text-white bg-blue-900 shadow-xs' : 'text-slate-600 hover:text-blue-900 hover:bg-slate-100' }}">
                <span class="material-symbols-outlined text-lg {{ $activeMenu === 'profil' ? 'text-white' : 'text-slate-500' }}">person</span>
                <span>Profil Kurir</span>
            </a>

            <!-- Keluar Akun -->
            <button type="button" onclick="openLogoutModal()" class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50 transition-colors w-full text-left cursor-pointer">
                <span class="material-symbols-outlined text-lg">logout</span>
                <span>Keluar Akun</span>
            </button>
        </div>
    </aside>

    <!-- Konten Utama: Auto resize saat sidebar collapse -->
    <div id="contentWrapper" class="flex-1 w-full min-h-screen flex flex-col md:pl-64 transition-all duration-300 ease-in-out">

        <!-- Top Sticky Header -->
        <header class="bg-white px-4 md:px-8 py-3.5 border-b border-slate-200/90 shadow-xs flex items-center justify-between sticky top-0 z-30">
            <div class="flex items-center gap-3">
                <button onclick="toggleSidebar()" class="w-9 h-9 rounded-xl bg-slate-50 hover:bg-blue-50 border border-slate-200 text-slate-700 hover:text-blue-900 flex items-center justify-center transition-all cursor-pointer shadow-xs active:scale-95" title="Buka/Tutup Menu">
                    <span class="material-symbols-outlined text-2xl font-bold">menu</span>
                </button>

                <div class="flex items-center gap-2">
                    <img class="w-8 h-8 rounded-lg object-contain shadow-xs" src="{{ asset('images/logo.png') }}" alt="Logo" onerror="this.src='https://placehold.co/100x100/1e3a8a/ffffff?text=KTT'" />
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="font-extrabold text-blue-900 font-['Plus_Jakarta_Sans'] text-sm md:text-base tracking-tight">@yield('page_title', 'Profil Kurir')</span>
                            <span class="px-1.5 py-0.2 bg-emerald-100 text-emerald-800 font-bold text-[9px] rounded uppercase tracking-wider">@yield('role_badge', 'KURIR')</span>
                        </div>
                        <p class="text-[11px] text-slate-500 hidden md:block">@yield('page_subtitle', 'Kelola informasi profil akun dan data operasional kurir')</p>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi Kanan Header -->
            <div class="flex items-center gap-2.5">
                @yield('header_actions')
            </div>
        </header>

        <!-- Main Body Content Slot -->
        <main class="px-4 md:px-8 py-5 flex flex-col gap-4 max-w-7xl w-full mx-auto pb-12">
            @yield('content')
        </main>
    </div>

    <!-- Form Logout Tersembunyi -->
    <form id="globalLogoutForm" action="{{ url('/logout') }}" method="POST" class="hidden">
        @csrf
    </form>

    <!-- Modal Konfirmasi Logout -->
    <div id="modal-logout-confirm" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden opacity-0 transition-opacity duration-200">
        <div class="bg-white w-full max-w-sm rounded-3xl p-6 shadow-2xl border border-slate-100 text-center flex flex-col items-center">
            <div class="w-14 h-14 rounded-2xl bg-rose-100 text-rose-700 flex items-center justify-center mb-3">
                <span class="material-symbols-outlined text-3xl">logout</span>
            </div>
            <h3 class="font-bold text-slate-900 text-base font-['Plus_Jakarta_Sans']">Keluar dari Akun Kurir?</h3>
            <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                Apakah kamu yakin ingin keluar dari sesi kurir <strong class="text-slate-900">Kopi Tiga Titik</strong>? Pastikan pengantaran hari ini sudah selesai tercatat.
            </p>

            <div class="mt-5 w-full grid grid-cols-2 gap-2.5">
                <button type="button" onclick="closeLogoutModal()" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="button" onclick="document.getElementById('globalLogoutForm').submit()" class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition-colors shadow-xs cursor-pointer">
                    Ya, Keluar
                </button>
            </div>
        </div>
    </div>

    <script>
        function openLogoutModal() {
            const modal = document.getElementById('modal-logout-confirm');
            modal.classList.remove('hidden');
            setTimeout(() => modal.classList.remove('opacity-0'), 10);
        }

        function closeLogoutModal() {
            const modal = document.getElementById('modal-logout-confirm');
            modal.classList.add('opacity-0');
            setTimeout(() => modal.classList.add('hidden'), 200);
        }

        let isDesktopSidebarOpen = true;

        function toggleSidebar() {
            const sidebar = document.getElementById('mainSidebar');
            const content = document.getElementById('contentWrapper');
            const backdrop = document.getElementById('mobileBackdrop');
            const isMobile = window.innerWidth < 768;

            if (isMobile) {
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

