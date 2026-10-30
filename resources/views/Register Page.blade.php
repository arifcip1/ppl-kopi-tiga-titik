<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Akun - Kopi Tiga Titik</title>

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
            <a href="{{ url('/login') }}" class="w-9 h-9 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-700 hover:text-blue-900 transition-colors shadow-xs" title="Kembali ke Halaman Masuk">
                <span class="material-symbols-outlined text-xl">arrow_back</span>
            </a>
            <div class="flex items-center gap-2">
                <img class="w-8 h-8 rounded-lg object-contain shadow-xs" src="{{ asset('images/logo.png') }}" alt="Logo" onerror="this.src='https://placehold.co/100x100/1e3a8a/ffffff?text=KTT'" />
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="font-extrabold text-blue-900 font-['Plus_Jakarta_Sans'] text-sm md:text-base tracking-tight">Daftar Akun</span>
                        <span class="px-1.5 py-0.2 bg-blue-100 text-blue-900 font-bold text-[9px] rounded uppercase tracking-wider">PEMBELI</span>
                    </div>
                    <p class="text-[11px] text-slate-500 hidden md:block">Buat akun baru untuk mulai memesan kopi</p>
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
            <div class="hidden md:flex flex-col justify-between w-full md:max-w-sm bg-gradient-to-br from-blue-900 via-blue-800 to-blue-950 rounded-2xl p-8 text-white shadow-lg min-h-[530px]">
                <div>
                    <img class="w-12 h-12 rounded-xl object-contain shadow-md mb-4 bg-white/10 p-1.5" src="{{ asset('images/logo.png') }}" alt="Logo" onerror="this.src='https://placehold.co/100x100/ffffff/1e3a8a?text=KTT'" />
                    <h2 class="font-extrabold text-2xl font-['Plus_Jakarta_Sans'] leading-tight">Gabung Bersama<br>Kopi Tiga Titik</h2>
                    <p class="text-blue-200 text-xs mt-2 leading-relaxed">Daftarkan akunmu sekarang untuk menikmati kemudahan checkout otomatis, simpan alamat favorit, dan pantau pengantaran pesanan secara real-time.</p>
                </div>

                <ul class="flex flex-col gap-3 my-6">
                    <li class="flex items-center gap-3 text-xs text-blue-100">
                        <span class="w-7 h-7 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-sm text-emerald-400">check_circle</span>
                        </span>
                        <span>Proses daftar cepat dan mudah</span>
                    </li>
                    <li class="flex items-center gap-3 text-xs text-blue-100">
                        <span class="w-7 h-7 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-sm text-emerald-400">check_circle</span>
                        </span>
                        <span>Riwayat pesanan kopi tersimpan rapi</span>
                    </li>
                    <li class="flex items-center gap-3 text-xs text-blue-100">
                        <span class="w-7 h-7 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-sm text-emerald-400">check_circle</span>
                        </span>
                        <span>Keamanan data pelanggan terjamin</span>
                    </li>
                </ul>

                <div class="pt-4 border-t border-white/10 flex items-center gap-2 text-[11px] text-blue-300">
                    <span class="material-symbols-outlined text-sm">verified_user</span>
                    <span>Street Coffee Mojokerto • Kopi Asli 100%</span>
                </div>
            </div>

            <!-- Form Register (Kanan / Utama) -->
            <div class="flex-1 w-full bg-white p-6 md:p-8 rounded-2xl border border-slate-200/90 shadow-xs">

                <div class="mb-5 pb-3 border-b border-slate-100">
                    <h1 class="text-lg md:text-xl font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Formulir Pendaftaran</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Lengkapi data diri berikut untuk membuat akun pembeli baru</p>
                </div>

                @if ($errors->any())
                    <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs flex flex-col gap-1">
                        <div class="font-bold flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-base">error</span>
                            <span>Mohon periksa data yang dimasukkan:</span>
                        </div>
                        <ul class="list-disc list-inside pl-1 text-[11px]">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ url('/register') }}" method="POST" class="flex flex-col gap-4">
                    @csrf

                    <!-- Nama Lengkap -->
                    <div class="flex flex-col gap-1.5">
                        <label for="name" class="text-xs font-bold text-slate-700">Nama Lengkap *</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base">person</span>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                placeholder="Contoh: Nabil Pratama"
                                required
                                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-blue-900 focus:bg-white transition-colors"
                            >
                        </div>
                        @error('name') <p class="text-[11px] text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Nomor WhatsApp -->
                    <div class="flex flex-col gap-1.5">
                        <label for="phone" class="text-xs font-bold text-slate-700">Nomor WhatsApp Aktif *</label>
                        <div class="relative flex items-center">
                            <span class="absolute left-3.5 text-xs font-bold text-slate-500 select-none">+62</span>
                            <input
                                type="tel"
                                id="phone"
                                name="phone"
                                value="{{ old('phone') }}"
                                placeholder="812-3456-7890"
                                required
                                class="w-full pl-12 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-blue-900 focus:bg-white transition-colors"
                            >
                        </div>
                        <p class="text-[10px] text-slate-400">Digunakan kurir untuk konfirmasi titik pengantaran pesanan kopi.</p>
                        @error('phone') <p class="text-[11px] text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Email -->
                    <div class="flex flex-col gap-1.5">
                        <label for="email" class="text-xs font-bold text-slate-700">Alamat Email *</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base">mail</span>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="nama@email.com"
                                required
                                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-blue-900 focus:bg-white transition-colors"
                            >
                        </div>
                        @error('email') <p class="text-[11px] text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Kata Sandi -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                        <div class="flex flex-col gap-1.5">
                            <label for="password" class="text-xs font-bold text-slate-700">Kata Sandi *</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base">lock</span>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    placeholder="Minimal 8 karakter"
                                    required
                                    minlength="8"
                                    class="w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder:text-slate-400 focus:outline-none focus:border-blue-900 focus:bg-white transition-colors"
                                >
                                <button type="button" onclick="togglePasswordVisibility('password', 'togglePasswordIcon')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-900 transition-colors cursor-pointer">
                                    <span class="material-symbols-outlined text-base" id="togglePasswordIcon">visibility</span>
                                </button>
                            </div>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="password_confirmation" class="text-xs font-bold text-slate-700">Ulangi Kata Sandi *</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base">lock_reset</span>
                                <input
                                    type="password"
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    placeholder="Ulangi kata sandi"
                                    required
                                    minlength="8"
                                    class="w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder:text-slate-400 focus:outline-none focus:border-blue-900 focus:bg-white transition-colors"
                                >
                                <button type="button" onclick="togglePasswordVisibility('password_confirmation', 'togglePasswordConfirmIcon')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-900 transition-colors cursor-pointer">
                                    <span class="material-symbols-outlined text-base" id="togglePasswordConfirmIcon">visibility</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Persetujuan Syarat & Ketentuan -->
                    <div class="pt-1">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" name="terms" required class="mt-0.5 w-4 h-4 rounded border-slate-300 text-blue-900 focus:ring-blue-900 cursor-pointer">
                            <span class="text-xs text-slate-600 leading-snug">
                                Saya menyetujui ketentuan layanan dan kebijakan privasi pemesanan Kopi Tiga Titik.
                            </span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full py-3 bg-blue-900 hover:bg-blue-800 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-2 shadow-xs hover:shadow-md transition-all active:scale-98 cursor-pointer mt-1">
                        <span class="material-symbols-outlined text-base">person_add</span>
                        <span>Daftar Akun Sekarang</span>
                    </button>

                    <!-- Area Pindah ke Halaman Masuk (Dipindahkan ke Halaman yang Sedang Ditampilkan, Bukan di Header) -->
                    <div class="mt-2 pt-4 border-t border-slate-100 flex flex-col items-center gap-2 text-center">
                        <p class="text-xs text-slate-500">Sudah memiliki akun terdaftar?</p>
                        <a href="{{ url('/login') }}" class="w-full py-2.5 bg-slate-50 hover:bg-blue-50 border border-slate-200 hover:border-blue-200 text-blue-900 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-all">
                            <span class="material-symbols-outlined text-base">login</span>
                            <span>Masuk ke Akun Sekarang</span>
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
        <a href="{{ url('/login') }}" class="flex flex-col items-center text-slate-400 hover:text-blue-900 transition-colors">
            <span class="material-symbols-outlined text-2xl">login</span>
            <span class="text-[10px] font-medium font-['Inter']">Masuk</span>
        </a>
        <a href="{{ url('/register') }}" class="flex flex-col items-center text-blue-900">
            <span class="material-symbols-outlined text-2xl font-bold">person_add</span>
            <span class="text-[10px] font-bold font-['Inter']">Daftar</span>
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
