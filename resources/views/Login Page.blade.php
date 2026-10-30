<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - Kopi Tiga Titik</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Tailwind CSS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-screen flex justify-center items-start md:py-6 antialiased font-sans text-slate-800">

<!-- Responsive Main Container -->
<div class="w-full max-w-md md:max-w-6xl min-h-screen md:min-h-0 bg-slate-50 md:bg-white md:rounded-3xl shadow-2xl md:shadow-lg border-x md:border border-slate-200/80 relative flex flex-col justify-start pb-28 md:pb-10 transition-all duration-300">

    <!-- Top Sticky Header (Sesuai Acuan: Tanpa Tombol Daftar/Login di Header) -->
    <div class="bg-white px-4 md:px-8 py-3.5 border-b border-slate-100 md:border-slate-200 shadow-xs flex items-center justify-between sticky top-0 z-30 md:rounded-t-3xl">
        <div class="flex items-center gap-3">
            <a href="{{ url('/') }}" class="w-9 h-9 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-700 hover:text-blue-900 transition-colors shadow-xs" title="Kembali ke Beranda">
                <span class="material-symbols-outlined text-xl">arrow_back</span>
            </a>
            <div class="flex items-center gap-2">
                <img class="w-8 h-8 rounded-lg object-contain shadow-xs" src="{{ asset('images/logo.png') }}" alt="Logo" onerror="this.src='https://placehold.co/100x100/1e3a8a/ffffff?text=KTT'" />
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="font-extrabold text-blue-900 font-['Plus_Jakarta_Sans'] text-sm md:text-base tracking-tight">Masuk ke Akun</span>
                        <span class="px-1.5 py-0.2 bg-blue-100 text-blue-900 font-bold text-[9px] rounded uppercase tracking-wider">PEMBELI/OWNER/KURIR</span>
                    </div>
                    <p class="text-[11px] text-slate-500 hidden md:block">Silakan masuk sesuai peran akun Anda</p>
                </div>
            </div>
        </div>

        <!-- Sisi Kanan Header Bersih (Sesuai Arahan PO: Tidak Ada Tombol Daftar/Login di Header) -->
        <div class="w-9"></div>
    </div>

    <!-- Main Content Area -->
    <div class="px-4 md:px-8 py-5 flex flex-col gap-4">

        <!-- Desktop: Hero Panel + Form Side by Side | Mobile: Form Only -->
        <div class="flex flex-col md:flex-row gap-6 md:gap-8 md:items-start">

            <!-- Hero Panel (Desktop Only di Sisi Kiri) -->
            <div class="hidden md:flex flex-col justify-between w-full md:max-w-sm bg-gradient-to-br from-blue-900 via-blue-800 to-blue-950 rounded-2xl p-8 text-white shadow-lg min-h-[490px]">
                <div>
                    <img class="w-12 h-12 rounded-xl object-contain shadow-md mb-4 bg-white/10 p-1.5" src="{{ asset('images/logo.png') }}" alt="Logo" onerror="this.src='https://placehold.co/100x100/ffffff/1e3a8a?text=KTT'" />
                    <h2 class="font-extrabold text-2xl font-['Plus_Jakarta_Sans'] leading-tight">Selamat Datang di<br>Kopi Tiga Titik</h2>
                    <p class="text-blue-200 text-xs mt-2 leading-relaxed">Nikmati kemudahan pesan kopi susu botolan segar khas Mojokerto dengan sistem pengantaran langsung ke lokasimu.</p>
                </div>

                <ul class="flex flex-col gap-3 my-6">
                    <li class="flex items-center gap-3 text-xs text-blue-100">
                        <span class="w-7 h-7 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-sm text-emerald-400">check_circle</span>
                        </span>
                        <span>4 varian kopi botol segar setiap hari</span>
                    </li>
                    <li class="flex items-center gap-3 text-xs text-blue-100">
                        <span class="w-7 h-7 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-sm text-emerald-400">check_circle</span>
                        </span>
                        <span>Gratis ongkir hingga radius 5,5 km</span>
                    </li>
                    <li class="flex items-center gap-3 text-xs text-blue-100">
                        <span class="w-7 h-7 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-sm text-emerald-400">check_circle</span>
                        </span>
                        <span>Pembayaran cepat &amp; aman via QRIS</span>
                    </li>
                </ul>

                <div class="pt-4 border-t border-white/10 flex items-center gap-2 text-[11px] text-blue-300">
                    <span class="material-symbols-outlined text-sm">location_on</span>
                    <span>Mojokerto • Sooko &amp; Benteng Pancasila</span>
                </div>
            </div>

            <!-- Form Login (Kanan / Utama) -->
            <div class="flex-1 w-full bg-white p-6 md:p-8 rounded-2xl border border-slate-200/90 shadow-xs">

                <div class="mb-5 pb-3 border-b border-slate-100">
                    <h1 class="text-lg md:text-xl font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Formulir Masuk</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Pilih peran akun Anda lalu masukkan kredensial yang terdaftar</p>
                </div>

                @if (session('error'))
                    <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">error</span>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if (session('success'))
                    <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">check_circle</span>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                <form action="{{ url('/login') }}" method="POST" class="flex flex-col gap-4">
                    @csrf

                    <!-- Role Selector -->
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-2">Masuk Sebagai:</label>
                        <div class="grid grid-cols-3 gap-2.5">
                            <label class="cursor-pointer">
                                <input type="radio" name="role" value="pembeli" class="sr-only peer" {{ old('role', 'pembeli') === 'pembeli' ? 'checked' : '' }}>
                                <div class="flex flex-col items-center gap-1 p-2.5 bg-slate-50 border-2 border-slate-200 rounded-xl text-slate-600 peer-checked:border-blue-900 peer-checked:bg-blue-50/70 peer-checked:text-blue-900 transition-all">
                                    <span class="material-symbols-outlined text-xl">person</span>
                                    <span class="text-xs font-bold">Pembeli</span>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="role" value="owner" class="sr-only peer" {{ old('role') === 'owner' ? 'checked' : '' }}>
                                <div class="flex flex-col items-center gap-1 p-2.5 bg-slate-50 border-2 border-slate-200 rounded-xl text-slate-600 peer-checked:border-blue-900 peer-checked:bg-blue-50/70 peer-checked:text-blue-900 transition-all">
                                    <span class="material-symbols-outlined text-xl">store</span>
                                    <span class="text-xs font-bold">Owner</span>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="role" value="kurir" class="sr-only peer" {{ old('role') === 'kurir' ? 'checked' : '' }}>
                                <div class="flex flex-col items-center gap-1 p-2.5 bg-slate-50 border-2 border-slate-200 rounded-xl text-slate-600 peer-checked:border-blue-900 peer-checked:bg-blue-50/70 peer-checked:text-blue-900 transition-all">
                                    <span class="material-symbols-outlined text-xl">delivery_dining</span>
                                    <span class="text-xs font-bold">Kurir</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Email / WA Input -->
                    <div class="flex flex-col gap-1.5">
                        <label for="email" class="text-xs font-bold text-slate-700">Email atau No. WhatsApp *</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base">mail</span>
                            <input
                                type="text"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="email@contoh.com atau 08xxxxxxxxxx"
                                required
                                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-blue-900 focus:bg-white transition-colors"
                            >
                        </div>
                        @error('email') <p class="text-[11px] text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Password Input -->
                    <div class="flex flex-col gap-1.5">
                        <label for="password" class="text-xs font-bold text-slate-700">Kata Sandi *</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base">lock</span>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Masukkan kata sandi akun"
                                required
                                class="w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder:text-slate-400 focus:outline-none focus:border-blue-900 focus:bg-white transition-colors"
                            >
                            <button type="button" onclick="togglePasswordVisibility('password', 'togglePasswordIcon')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-900 transition-colors cursor-pointer">
                                <span class="material-symbols-outlined text-base" id="togglePasswordIcon">visibility</span>
                            </button>
                        </div>
                        @error('password') <p class="text-[11px] text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Remember Me + Forgot Password -->
                    <div class="flex items-center justify-between pt-0.5">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" id="remember" class="w-4 h-4 rounded border-slate-300 text-blue-900 focus:ring-blue-900 cursor-pointer" {{ old('remember') ? 'checked' : '' }}>
                            <span class="text-xs text-slate-600 font-medium">Ingat saya</span>
                        </label>
                        <span class="text-[11px] text-slate-400">Lupa kata sandi? Hubungi Admin</span>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full py-3 bg-blue-900 hover:bg-blue-800 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-2 shadow-xs hover:shadow-md transition-all active:scale-98 cursor-pointer">
                        <span class="material-symbols-outlined text-base">login</span>
                        <span>Masuk ke Akun</span>
                    </button>

                    <!-- Area Pindah ke Halaman Daftar (Dipindahkan ke Halaman yang Sedang Ditampilkan, Bukan di Header) -->
                    <div class="mt-2 pt-4 border-t border-slate-100 flex flex-col items-center gap-2 text-center">
                        <p class="text-xs text-slate-500">Belum memiliki akun di Kopi Tiga Titik?</p>
                        <a href="{{ url('/register') }}" class="w-full py-2.5 bg-slate-50 hover:bg-blue-50 border border-slate-200 hover:border-blue-200 text-blue-900 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-all">
                            <span class="material-symbols-outlined text-base">person_add</span>
                            <span>Daftar Akun Baru Sekarang</span>
                        </a>
                    </div>
                </form>

            </div>
        </div>

    </div>

    <!-- Bottom Navigation Mobile -->
    <div class="fixed md:hidden bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md bg-white border-t border-slate-200 py-1.5 px-8 flex justify-between items-center z-50 shadow-[0_-2px_10px_rgba(0,0,0,0.05)]">
        <a href="{{ url('/') }}" class="flex flex-col items-center text-slate-400 hover:text-blue-900 transition-colors">
            <span class="material-symbols-outlined text-2xl">home</span>
            <span class="text-[10px] font-medium font-['Inter']">Beranda</span>
        </a>
        <a href="{{ url('/katalog') }}" class="flex flex-col items-center text-slate-400 hover:text-blue-900 transition-colors">
            <span class="material-symbols-outlined text-2xl">storefront</span>
            <span class="text-[10px] font-medium font-['Inter']">Katalog</span>
        </a>
        <a href="{{ url('/login') }}" class="flex flex-col items-center text-blue-900">
            <span class="material-symbols-outlined text-2xl font-bold">login</span>
            <span class="text-[10px] font-bold font-['Inter']">Masuk</span>
        </a>
        <a href="{{ url('/register') }}" class="flex flex-col items-center text-slate-400 hover:text-blue-900 transition-colors">
            <span class="material-symbols-outlined text-2xl">person_add</span>
            <span class="text-[10px] font-medium font-['Inter']">Daftar</span>
        </a>
    </div>

</div>

<script>
    function togglePasswordVisibility(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.textContent = 'visibility_off';
        } else {
            input.type = 'password';
            icon.textContent = 'visibility';
        }
    }
</script>

</body>
</html>

