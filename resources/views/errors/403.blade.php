<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Akses Ditolak | Kopi Tiga Titik</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Tailwind CSS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-50 text-slate-800 antialiased font-sans flex flex-col justify-between selection:bg-rose-900 selection:text-white">

    <!-- Top Navigation Simple Header -->
    <header class="w-full bg-white/90 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-30">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <a href="{{ Auth::check() ? (in_array(Auth::user()->role ?? '', ['admin', 'owner']) ? url('/owner/dashboard') : (Auth::user()->role === 'kurir' ? url('/kurir/tugas-baru') : url('/katalog'))) : url('/') }}" class="flex items-center gap-2.5 group">
                <div class="w-9 h-9 bg-blue-900 rounded-xl shadow-xs flex items-center justify-center overflow-hidden shrink-0 group-hover:scale-105 transition-transform">
                    <img class="w-6 h-6 object-contain" src="{{ asset('images/logo.png') }}" alt="Logo Kopi Tiga Titik" onerror="this.src='https://placehold.co/40x40/1e3a8a/ffffff?text=KTT'" />
                </div>
                <div class="flex flex-col">
                    <span class="text-blue-900 text-base font-extrabold font-['Plus_Jakarta_Sans'] leading-tight">Kopi Tiga Titik</span>
                    <span class="text-slate-400 text-[10px] font-semibold leading-tight">Mojokerto Street Coffee</span>
                </div>
            </a>
            @auth
                <div class="flex items-center gap-2">
                    <span class="text-[11px] font-medium text-slate-500 hidden sm:inline">Masuk sebagai: <strong>{{ auth()->user()->name ?? 'Pengguna' }}</strong></span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-xs font-bold text-rose-600 hover:text-rose-700 bg-rose-50 px-3 py-1.5 rounded-xl border border-rose-200 transition-colors cursor-pointer">
                            Ganti Akun
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex items-center justify-center p-4 sm:p-6 my-auto">
        <div class="max-w-md w-full text-center flex flex-col items-center">
            
            <!-- Graphic / Icon Badge -->
            <div class="relative mb-6">
                <div class="w-28 h-28 sm:w-32 sm:h-32 rounded-3xl bg-rose-50 border-2 border-rose-100 flex items-center justify-center shadow-lg shadow-rose-900/5 -rotate-3 hover:rotate-0 transition-transform duration-300">
                    <span class="material-symbols-outlined text-rose-600 text-6xl sm:text-7xl select-none">lock_person</span>
                </div>
                <span class="absolute -bottom-2 -right-2 px-3 py-1 bg-rose-600 text-white font-extrabold text-xs sm:text-sm rounded-full shadow-md font-['Plus_Jakarta_Sans']">
                    403
                </span>
            </div>

            <!-- Title & Subtitle -->
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-['Plus_Jakarta_Sans'] tracking-tight mb-2">
                Area Khusus / Akses Terbatas
            </h1>
            <p class="text-slate-500 text-xs sm:text-sm leading-relaxed mb-8 max-w-sm">
                {{ $exception->getMessage() ?: 'Maaf, akun Anda tidak memiliki hak otorisasi untuk mengakses halaman manajemen ini.' }}
            </p>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                @auth
                    @if(in_array(Auth::user()->role ?? '', ['admin', 'owner']))
                        <a href="{{ url('/owner/dashboard') }}" class="w-full sm:w-auto px-5 py-3 bg-blue-900 hover:bg-blue-800 text-white text-xs sm:text-sm font-bold rounded-2xl shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 active:scale-98">
                            <span class="material-symbols-outlined text-lg">dashboard</span>
                            <span>Kembali ke Dashboard Owner</span>
                        </a>
                        <a href="{{ url('/owner/produk') }}" class="w-full sm:w-auto px-5 py-3 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 text-xs sm:text-sm font-semibold rounded-2xl shadow-xs transition-all flex items-center justify-center gap-2 active:scale-98">
                            <span class="material-symbols-outlined text-lg">inventory_2</span>
                            <span>Kelola Produk</span>
                        </a>
                    @elseif(Auth::user()->role === 'kurir')
                        <a href="{{ url('/kurir/tugas-baru') }}" class="w-full sm:w-auto px-5 py-3 bg-blue-900 hover:bg-blue-800 text-white text-xs sm:text-sm font-bold rounded-2xl shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 active:scale-98">
                            <span class="material-symbols-outlined text-lg">local_shipping</span>
                            <span>Kembali ke Dashboard Kurir</span>
                        </a>
                    @else
                        <a href="{{ url('/katalog') }}" class="w-full sm:w-auto px-5 py-3 bg-blue-900 hover:bg-blue-800 text-white text-xs sm:text-sm font-bold rounded-2xl shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 active:scale-98">
                            <span class="material-symbols-outlined text-lg">storefront</span>
                            <span>Kembali ke Katalog Menu</span>
                        </a>
                        <a href="{{ url('/cart') }}" class="w-full sm:w-auto px-5 py-3 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 text-xs sm:text-sm font-semibold rounded-2xl shadow-xs transition-all flex items-center justify-center gap-2 active:scale-98">
                            <span class="material-symbols-outlined text-lg">shopping_cart</span>
                            <span>Keranjang Belanja</span>
                        </a>
                    @endif
                @else
                    <a href="{{ url('/login') }}" class="w-full sm:w-auto px-5 py-3 bg-blue-900 hover:bg-blue-800 text-white text-xs sm:text-sm font-bold rounded-2xl shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 active:scale-98">
                        <span class="material-symbols-outlined text-lg">login</span>
                        <span>Login Akun</span>
                    </a>
                    <a href="{{ url('/katalog') }}" class="w-full sm:w-auto px-5 py-3 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 text-xs sm:text-sm font-semibold rounded-2xl shadow-xs transition-all flex items-center justify-center gap-2 active:scale-98">
                        <span class="material-symbols-outlined text-lg">storefront</span>
                        <span>Lihat Menu</span>
                    </a>
                @endauth
            </div>

            <!-- Explanation Box -->
            <div class="mt-8 p-3.5 bg-rose-50/70 border border-rose-200/80 rounded-2xl text-[11px] text-rose-800 text-left max-w-sm flex items-start gap-2.5">
                <span class="material-symbols-outlined text-rose-600 text-lg shrink-0 mt-0.5">security</span>
                <p class="leading-relaxed">
                    Halaman ini dikhususkan bagi <strong>Owner & Barista</strong> Kopi Tiga Titik. Jika Anda merasa berhak atas akses ini, silakan hubungi tim administrator.
                </p>
            </div>

        </div>
    </main>

    <!-- Footer Simple -->
    <footer class="w-full py-4 text-center text-xs text-slate-400 border-t border-slate-200/60 bg-white/60">
        <p>&copy; {{ date('Y') }} Kopi Tiga Titik Mojokerto. Sistem Keamanan & Otorisasi Terpadu.</p>
    </footer>

</body>
</html>

