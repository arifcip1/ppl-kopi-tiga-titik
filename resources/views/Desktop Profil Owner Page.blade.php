@extends('layouts.owner')

@section('title', 'Profil Owner')
@section('active_menu', 'profil')
@section('page_title', 'Profil Pengelola')
@section('role_badge', 'OWNER')
@section('page_subtitle', 'Kelola informasi profil akun dan data operasional lapak')

@section('content')

    @if (session('success'))
        <div class="p-3.5 bg-emerald-50 text-emerald-800 text-xs font-semibold rounded-2xl border border-emerald-200 flex items-center gap-2">
            <span class="material-symbols-outlined text-base text-emerald-600">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 md:gap-6 items-start">

        <!-- Kolom Kiri: Kartu Profil & Info Operasional Lapak -->
        <div class="lg:col-span-2 flex flex-col gap-4">

            <!-- Profil Singkat Card -->
            <div class="relative p-6 bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden flex flex-col items-center">
                <div class="w-20 h-20 bg-blue-900 rounded-full flex items-center justify-center text-white text-3xl font-extrabold ring-4 ring-blue-50 shadow-md mb-3">
                    F
                </div>
                <h2 class="text-base md:text-lg font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Farrel</h2>

                <div class="mt-2 px-3 py-1 bg-slate-50 rounded-full border border-slate-200 flex items-center gap-2">
                    <span class="material-symbols-outlined text-slate-400 text-sm">mail</span>
                    <span id="ownerEmail" class="text-xs font-medium text-slate-700">owner1@gmail.com</span>
                    <button type="button" id="copyEmail" aria-label="Salin email" class="p-0.5 rounded text-slate-400 hover:text-blue-900 transition-colors cursor-pointer" title="Salin Email">
                        <span class="material-symbols-outlined text-sm">content_copy</span>
                    </button>
                </div>

                <div class="w-full my-4 border-t border-slate-100"></div>

                <div class="flex items-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                    <span class="material-symbols-outlined text-emerald-600 text-base">verified</span>
                    <span>Pemilik Usaha Kopi Tiga Titik</span>
                </div>
            </div>

            <!-- Pusat Operasional Lapak & Produksi Card -->
            <div class="p-5 bg-white rounded-2xl border border-slate-200/90 shadow-xs flex flex-col gap-3.5">
                <div class="pb-2 border-b border-slate-100 flex items-center gap-2.5">
                    <div class="w-8 h-8 bg-blue-50 text-blue-900 rounded-lg flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-lg">store</span>
                    </div>
                    <div>
                        <h3 class="text-xs md:text-sm font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Pusat Operasional &amp; Produksi</h3>
                        <p class="text-[10px] text-slate-500">Daftar lokasi aktif cabang Mojokerto</p>
                    </div>
                </div>

                <div class="px-3.5 py-2 bg-emerald-50 rounded-xl border border-emerald-200 flex items-center gap-2">
                    <span class="relative flex w-2 h-2">
                        <span class="absolute inline-flex w-full h-full rounded-full bg-emerald-400 opacity-75 animate-ping"></span>
                        <span class="relative inline-flex w-2 h-2 rounded-full bg-emerald-600"></span>
                    </span>
                    <span class="text-xs font-bold text-emerald-800">Lapak Buka (15.00 - 18.00 WIB)</span>
                </div>

                <div class="flex flex-col gap-2.5">
                    <!-- Lokasi 1: Dapur / Produksi -->
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 flex items-start gap-2.5">
                        <div class="w-8 h-8 bg-blue-100 text-blue-900 rounded-lg flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-base">coffee_maker</span>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="text-xs font-bold text-slate-800 font-['Plus_Jakarta_Sans']">Pusat Produksi &amp; Dapur</span>
                            <span class="text-[11px] text-slate-600 leading-snug">Dsn. Daleman, Ds. Japan, Kec. Sooko, Mojokerto</span>
                            <span class="text-[10px] text-blue-800 font-semibold mt-0.5">Pusat seduh &amp; batching delivery</span>
                        </div>
                    </div>

                    <!-- Lokasi 2: Lapak Benteng Pancasila -->
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 flex items-start gap-2.5">
                        <div class="w-8 h-8 bg-amber-100 text-amber-800 rounded-lg flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-base">storefront</span>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="text-xs font-bold text-slate-800 font-['Plus_Jakarta_Sans']">Lapak Mangkal Street Coffee</span>
                            <span class="text-[11px] text-slate-600 leading-snug">Jl. Benteng Pancasila (Depan Taman Sentanan)</span>
                            <span class="text-[10px] text-amber-800 font-semibold mt-0.5">Titik temu pelanggan offline &amp; take-away</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Kolom Kanan: Form Akun Owner -->
        <div class="lg:col-span-3 p-5 md:p-6 bg-white rounded-2xl border border-slate-200/90 shadow-xs flex flex-col gap-4">
            <div class="pb-3 border-b border-slate-100 flex items-center gap-2.5">
                <div class="w-9 h-9 bg-blue-50 text-blue-900 rounded-xl flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-lg">manage_accounts</span>
                </div>
                <div>
                    <h3 class="text-sm md:text-base font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Informasi Akun Owner</h3>
                    <p class="text-[11px] text-slate-500">Perbarui data nama dan kredensial login pemilik usaha</p>
                </div>
            </div>

            <form action="{{ url('/owner/profil') }}" method="POST" class="flex flex-col gap-4">
                @csrf
                @method('PUT')

                <!-- Nama Lengkap -->
                <div class="flex flex-col gap-1.5">
                    <label for="name" class="text-xs font-bold text-slate-700">Nama Lengkap Owner</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base">person</span>
                        <input id="name" name="name" type="text" value="{{ old('name', 'Farrel') }}" required
                               class="w-full py-2.5 pl-10 pr-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-blue-900 focus:bg-white transition-colors" />
                    </div>
                    @error('name') <p class="text-[11px] text-rose-600">{{ $message }}</p> @enderror
                </div>

                <!-- Email (Readonly) -->
                <div class="flex flex-col gap-1.5">
                    <label for="email" class="text-xs font-bold text-slate-700">Alamat Email Utama</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base">mail</span>
                        <input id="email" type="email" value="owner1@gmail.com" readonly
                               class="w-full py-2.5 pl-10 pr-3.5 bg-slate-100 border border-slate-200 rounded-xl text-xs text-slate-600 cursor-not-allowed outline-none" />
                    </div>
                    <p class="text-[10px] text-slate-400">Alamat email terdaftar dikonfigurasi melalui sistem keamanan database.</p>
                </div>

                <!-- Kata Sandi Baru -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                    <div class="flex flex-col gap-1.5">
                        <label for="password" class="text-xs font-bold text-slate-700">Kata Sandi Baru</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base">lock</span>
                            <input id="password" name="password" type="password" minlength="8"
                                   placeholder="Kosongkan jika tidak diubah"
                                   class="w-full py-2.5 pl-10 pr-10 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder:text-slate-400 focus:outline-none focus:border-blue-900 focus:bg-white transition-colors" />
                            <button type="button" data-toggle="password" aria-label="Tampilkan kata sandi"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-900 transition-colors cursor-pointer">
                                <span class="material-symbols-outlined text-base">visibility</span>
                            </button>
                        </div>
                        @error('password') <p class="text-[11px] text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="password_confirmation" class="text-xs font-bold text-slate-700">Ulangi Kata Sandi Baru</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base">lock_reset</span>
                            <input id="password_confirmation" name="password_confirmation" type="password"
                                   placeholder="Ulangi kata sandi baru"
                                   class="w-full py-2.5 pl-10 pr-10 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder:text-slate-400 focus:outline-none focus:border-blue-900 focus:bg-white transition-colors" />
                            <button type="button" data-toggle="password_confirmation" aria-label="Tampilkan kata sandi"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-900 transition-colors cursor-pointer">
                                <span class="material-symbols-outlined text-base">visibility</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="p-3 bg-blue-50/70 rounded-xl border border-blue-100 flex items-start gap-2.5">
                    <span class="material-symbols-outlined text-blue-900 text-base mt-0.5">info</span>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Pastikan kata sandi baru mengandung minimal 8 karakter dengan kombinasi huruf dan angka untuk keamanan data usaha Kopi Tiga Titik.
                    </p>
                </div>

                <!-- Tombol Submit & Aksi -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="submit"
                            class="w-full md:w-auto px-6 py-2.5 bg-blue-900 hover:bg-blue-800 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-2 shadow-xs hover:shadow-md transition-all cursor-pointer">
                        <span class="material-symbols-outlined text-base">save</span>
                        <span>Simpan Perubahan Data</span>
                    </button>
                </div>
            </form>
        </div>

    </div>

@endsection

@push('scripts')
<script>
    // Toggle Password Visibility
    document.querySelectorAll('[data-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.dataset.toggle);
            if (!input) return;
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            btn.querySelector('span').textContent = isHidden ? 'visibility_off' : 'visibility';
        });
    });

    // Copy Email
    const copyBtn = document.getElementById('copyEmail');
    if (copyBtn) {
        copyBtn.addEventListener('click', async () => {
            const email = document.getElementById('ownerEmail').textContent.trim();
            const icon = copyBtn.querySelector('span');
            try {
                await navigator.clipboard.writeText(email);
                icon.textContent = 'check';
                setTimeout(() => (icon.textContent = 'content_copy'), 1500);
            } catch (err) {
                console.error('Gagal menyalin email', err);
            }
        });
    }
</script>
@endpush
