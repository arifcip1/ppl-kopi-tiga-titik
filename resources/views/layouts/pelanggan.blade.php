<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kopi Tiga Titik') - Akun Pelanggan</title>

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

    <!-- Sidebar Navigasi Kiri (Pelanggan) -->
    <aside id="mainSidebar" class="fixed top-0 bottom-0 left-0 z-50 w-[75vw] max-w-[75vw] md:w-64 md:max-w-64 h-dvh max-h-screen overflow-hidden select-none bg-white border-r border-slate-200/90 shadow-2xl md:shadow-xs flex flex-col justify-between -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
        
        <!-- Bagian Atas: Brand & Identitas Pelanggan -->
        <div class="flex flex-col shrink-0">
            <!-- Header Sidebar -->
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

            @auth
                @php
                    $userRole = Auth::user()->role ?? 'pembeli';
                    $roleLabel = match($userRole) {
                        'admin', 'owner' => 'OWNER',
                        'kurir' => 'KURIR',
                        default => 'PELANGGAN'
                    };
                    $badgeClass = in_array($userRole, ['admin', 'owner']) 
                        ? 'bg-amber-100 text-amber-900 border border-amber-300' 
                        : 'bg-blue-200/70 text-blue-900';
                @endphp
                <!-- Identitas Akun Pelanggan / Pengguna -->
                <div class="mx-3 my-2 p-2 bg-blue-50/70 rounded-xl border border-blue-100/80 flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-blue-900 text-white font-extrabold text-xs flex items-center justify-center shadow-xs shrink-0">
                        {{ strtoupper(substr(Auth::user()->name ?? 'P', 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1">
                            <span class="text-xs font-bold text-slate-900 truncate font-['Plus_Jakarta_Sans']">{{ Auth::user()->name ?? 'Pelanggan' }}</span>
                            <span class="px-1 py-0.2 {{ $badgeClass }} text-[8px] font-extrabold rounded uppercase">{{ $roleLabel }}</span>
                        </div>
                        <p class="text-[9px] text-slate-500 truncate leading-tight">{{ Auth::user()->telepon ?? Auth::user()->email ?? '+62 812-3849-9210' }}</p>
                    </div>
                </div>

                @if(in_array($userRole, ['admin', 'owner']))
                    <!-- Shortcut Khusus Owner saat berada di Halaman Pelanggan -->
                    <div class="mx-3 mb-2 p-2 bg-amber-50 rounded-xl border border-amber-200 flex items-center justify-between">
                        <div class="flex items-center gap-1.5 text-amber-900">
                            <span class="material-symbols-outlined text-sm">admin_panel_settings</span>
                            <span class="text-[10px] font-bold">Akun Owner</span>
                        </div>
                        <a href="{{ url('/owner/dashboard') }}" class="px-2 py-0.5 bg-amber-600 hover:bg-amber-700 text-white text-[9px] font-extrabold rounded-md shadow-xs flex items-center gap-1 transition-colors">
                            <span class="material-symbols-outlined text-[11px]">dashboard</span>
                            <span>Dashboard</span>
                        </a>
                    </div>
                @endif
            @else
                <!-- Banner Mode Tamu (Guest) -->
                <div class="mx-3 my-2 p-2.5 bg-blue-50/80 rounded-xl border border-blue-200/70 flex flex-col gap-1">
                    <div class="flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-900"></span>
                        <span class="text-[11px] font-bold text-blue-900">Mode Tamu (Guest)</span>
                    </div>
                    <p class="text-[9.5px] text-slate-500 leading-tight">Silakan masuk untuk menyimpan keranjang dan pesanan.</p>
                </div>
            @endauth
        </div>

        <!-- Bagian Tengah: Menu Navigasi Pelanggan -->
        <div class="flex-1 overflow-y-auto px-2.5 py-1 flex flex-col gap-0.5">
            <div class="px-2.5 py-1 text-[9px] font-bold text-slate-400 uppercase tracking-wider">MENU UTAMA</div>

            @php
                $activeMenu = trim($__env->yieldContent('active_menu', 'profil'));
                $cartBadgeCount = $cartCount ?? 0;
            @endphp

            <!-- 1. Katalog / Beranda (Publik Tanpa Login FR-3.1) -->
            <a href="{{ url('/katalog') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-medium transition-colors {{ $activeMenu === 'katalog' ? 'font-bold text-white bg-blue-900 shadow-xs' : 'text-slate-600 hover:text-blue-900 hover:bg-slate-100' }}">
                <span class="material-symbols-outlined text-lg {{ $activeMenu === 'katalog' ? 'text-white' : 'text-slate-500' }}">storefront</span>
                <span>Katalog Menu</span>
            </a>

            <!-- 2. Keranjang Belanja -->
            <a href="{{ auth()->check() ? url('/cart') : route('login') }}" class="flex items-center justify-between px-3 py-1.5 rounded-xl text-xs font-medium transition-colors {{ $activeMenu === 'keranjang' ? 'font-bold text-white bg-blue-900 shadow-xs' : 'text-slate-600 hover:text-blue-900 hover:bg-slate-100' }}">
                <div class="flex items-center gap-2.5">
                    <div class="relative flex items-center justify-center">
                        <span class="material-symbols-outlined text-lg {{ $activeMenu === 'keranjang' ? 'text-white' : 'text-slate-500' }}">shopping_cart</span>
                        <span id="sidebar-cart-badge" class="{{ $cartBadgeCount > 0 ? 'flex' : 'hidden' }} absolute -top-1.5 -right-2 px-1 min-w-[16px] h-4 bg-rose-600 text-white text-[9px] font-bold rounded-full items-center justify-center shadow-xs border-2 border-white">
                            {{ $cartBadgeCount }}
                        </span>
                    </div>
                    <span>Keranjang Belanja</span>
                </div>
            </a>

            <!-- 3. Pesanan Saya -->
            <a href="{{ auth()->check() ? url('/riwayat-pesanan') : route('login') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-medium transition-colors {{ $activeMenu === 'pesanan-saya' ? 'font-bold text-white bg-blue-900 shadow-xs' : 'text-slate-600 hover:text-blue-900 hover:bg-slate-100' }}">
                <span class="material-symbols-outlined text-lg {{ $activeMenu === 'pesanan-saya' ? 'text-white' : 'text-slate-500' }}">receipt_long</span>
                <span>Pesanan Saya</span>
            </a>
        </div>

        <!-- Bagian Bawah: Profil & Logout -->
        <div class="p-2.5 pb-6 md:pb-3 border-t border-slate-100 flex flex-col gap-0.5 shrink-0 bg-white">
            @auth
                <!-- Profil -->
                <a href="{{ url('/profil-pelanggan') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-semibold transition-colors {{ $activeMenu === 'profil' ? 'font-bold text-white bg-blue-900 shadow-xs' : 'text-slate-600 hover:text-blue-900 hover:bg-slate-100' }}">
                    <span class="material-symbols-outlined text-lg {{ $activeMenu === 'profil' ? 'text-white' : 'text-slate-500' }}">person</span>
                    <span>Profil Saya</span>
                </a>

                <!-- Keluar Akun -->
                <button type="button" onclick="openLogoutModal()" class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50 transition-colors w-full text-left cursor-pointer">
                    <span class="material-symbols-outlined text-lg">logout</span>
                    <span>Keluar Akun</span>
                </button>
            @else
                <!-- Tombol Masuk Akun untuk Guest -->
                <a href="{{ route('login') }}" class="flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-xs font-bold text-white bg-blue-900 hover:bg-blue-800 transition-colors shadow-xs">
                    <span class="material-symbols-outlined text-base">login</span>
                    <span>Masuk Akun</span>
                </a>
            @endauth
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
                            <span class="font-extrabold text-blue-900 font-['Plus_Jakarta_Sans'] text-sm md:text-base tracking-tight">@yield('page_title', 'My Account')</span>
                            <span class="px-1.5 py-0.2 {{ Auth::check() && in_array(Auth::user()->role, ['admin', 'owner']) ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-blue-100 text-blue-900' }} font-bold text-[9px] rounded uppercase tracking-wider">
                                @yield('role_badge', strtoupper(Auth::check() && in_array(Auth::user()->role, ['admin', 'owner']) ? 'OWNER' : (Auth::user()->role ?? 'PELANGGAN')))
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 hidden md:block">@yield('page_subtitle', 'Kelola profil, alamat pengiriman, dan riwayat pesananmu')</p>
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
            <!-- Flash Message & Validasi Session (PPL-24) -->
            <x-flash-message />

            @yield('content')
        </main>
    </div>

    <!-- Container Toast Dinamis AJAX (Mengikuti Desain components/alert.blade.php PPL-24) -->
    <div id="global-toast-container" class="fixed top-20 right-4 md:right-8 z-50 flex flex-col gap-2.5 max-w-sm w-full pointer-events-none"></div>

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
            <h3 class="font-bold text-slate-900 text-base font-['Plus_Jakarta_Sans']">Keluar dari Akun?</h3>
            <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                Apakah kamu yakin ingin keluar dari akun <strong class="text-slate-900">Kopi Tiga Titik</strong>?
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

        /**
         * Global Toast Notification (Mengikuti styling komponen alert PPL-24)
         */
        function showAppToast(message, type = 'error', title = null) {
            const container = document.getElementById('global-toast-container');
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

            const style = typeStyles[type] || typeStyles.info;
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
                <button type="button" class="p-1 -mr-1 -mt-1 rounded-lg ${style.closeColor} hover:bg-black/5 transition-colors cursor-pointer" title="Tutup Notifikasi">
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
            }, 4000);
        }
    </script>

    @stack('scripts')

</body>
</html>

