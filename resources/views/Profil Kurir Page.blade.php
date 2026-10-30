@extends('layouts.kurir')

@section('title', 'Profil Kurir')
@section('active_menu', 'profil')
@section('page_title', 'Profil Kurir')
@section('role_badge', 'KURIR')
@section('page_subtitle', 'Kelola informasi data diri dan kontak koordinasi pengantaran')

@section('content')

    @if (session('success'))
        <div class="p-3.5 bg-emerald-50 text-emerald-800 text-xs font-semibold rounded-2xl border border-emerald-200 flex items-center gap-2">
            <span class="material-symbols-outlined text-base text-emerald-600">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 md:gap-6 items-start">

        <!-- Kolom Kiri: Profil & Statistik Pengantaran -->
        <div class="lg:col-span-2 flex flex-col gap-4">

            <!-- Card Identitas Kurir -->
            <div class="p-5 bg-white rounded-2xl border border-slate-200/90 shadow-xs flex flex-col gap-4">
                <div class="flex items-center gap-4">
                    <div class="relative shrink-0">
                        <div class="w-16 h-16 bg-blue-900 rounded-full flex items-center justify-center text-white text-2xl font-bold font-['Plus_Jakarta_Sans'] shadow-md">
                            I
                        </div>
                        <div class="absolute -bottom-0.5 -right-0.5 w-6 h-6 bg-emerald-500 rounded-full flex items-center justify-center border-2 border-white shadow-xs">
                            <span class="material-symbols-outlined text-white text-xs">moped</span>
                        </div>
                    </div>
                    <div class="min-w-0 flex flex-col">
                        <h2 class="text-base font-bold text-slate-900 font-['Plus_Jakarta_Sans'] leading-tight truncate">Ilham Ramadhan</h2>
                        <div class="pt-0.5 flex items-center gap-1.5 text-slate-500 text-xs">
                            <span class="material-symbols-outlined text-slate-400 text-sm">mail</span>
                            <span class="truncate">kurir1ilham@gmail.com</span>
                        </div>
                        <div class="pt-2">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-blue-50 text-blue-900 rounded-full text-[11px] font-bold border border-blue-100">
                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                                Aktif Shift Pagi
                            </span>
                        </div>
                    </div>
                </div>

                <div class="h-px bg-slate-100"></div>

                <div class="p-3 bg-blue-50/70 rounded-xl border border-blue-100/80 flex items-center justify-between">
                    <div class="flex items-center gap-1.5 text-xs font-semibold text-blue-900">
                        <span>WhatsApp:</span>
                        <span>+62 812-3456-7890</span>
                    </div>
                    <a href="https://wa.me/6281234567890" target="_blank" rel="noopener" aria-label="Buka WhatsApp"
                       class="p-1 rounded-lg text-blue-900 hover:bg-blue-100 transition-colors" title="Hubungi via WhatsApp">
                        <span class="material-symbols-outlined text-base">chat</span>
                    </a>
                </div>
            </div>

            <!-- Card Statistik Pengantaran -->
            <div class="p-5 bg-white rounded-2xl border border-slate-200/90 shadow-xs flex flex-col gap-3.5">
                <div class="flex items-center justify-between pb-1 border-b border-slate-100">
                    <h3 class="text-xs md:text-sm font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Pengantaran Hari Ini</h3>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Mojokerto Hub</span>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-1 gap-2.5">
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80 flex flex-col gap-1">
                        <div class="flex items-center justify-between text-slate-500">
                            <span class="text-[10px] font-bold uppercase tracking-wide">Total Selesai</span>
                            <span class="material-symbols-outlined text-blue-900 text-lg">inventory_2</span>
                        </div>
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-2xl font-extrabold text-blue-900 font-['Plus_Jakarta_Sans']">142</span>
                            <span class="text-xs font-semibold text-slate-600">Pesanan</span>
                        </div>
                        <div class="flex items-center gap-1 text-[10px] text-emerald-600 font-medium">
                            <span class="material-symbols-outlined text-xs text-emerald-600">check_circle</span>
                            <span>Semua terkirim tepat waktu</span>
                        </div>
                    </div>

                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80 flex items-center gap-3">
                        <div class="w-9 h-9 bg-blue-100 text-blue-900 rounded-lg flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-xl">two_wheeler</span>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Kendaraan</span>
                            <span class="text-xs font-bold text-slate-800 truncate">Honda Beat Hitam</span>
                            <span class="text-[11px] font-extrabold text-blue-900">Plat S 4821 NA</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Kolom Kanan: Form Kelola Profil -->
        <div class="lg:col-span-3 p-5 md:p-6 bg-white rounded-2xl border border-slate-200/90 shadow-xs flex flex-col gap-4">
            <div class="pb-3 border-b border-slate-100 flex items-center gap-2.5">
                <div class="w-9 h-9 bg-blue-50 text-blue-900 rounded-xl flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-lg">edit_note</span>
                </div>
                <div>
                    <h3 class="text-sm md:text-base font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Kelola Data Diri Kurir</h3>
                    <p class="text-[11px] text-slate-500">Perbarui profil dan kontak koordinasi pengantaran pesanan</p>
                </div>
            </div>

            <form action="{{ url('/kurir/profil') }}" method="POST" class="flex flex-col gap-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                    <div class="flex flex-col gap-1.5">
                        <label for="name" class="text-xs font-bold text-slate-700">Nama Kurir</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base">person</span>
                            <input id="name" name="name" type="text" value="{{ old('name', 'Ilham Ramadhan') }}" required
                                   class="w-full py-2.5 pl-10 pr-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-blue-900 focus:bg-white transition-colors" />
                        </div>
                        @error('name') <p class="text-[11px] text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="email" class="text-xs font-bold text-slate-700">Alamat Email</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base">mail</span>
                            <input id="email" name="email" type="email" value="{{ old('email', 'kurir1ilham@gmail.com') }}" required
                                   class="w-full py-2.5 pl-10 pr-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-blue-900 focus:bg-white transition-colors" />
                        </div>
                        @error('email') <p class="text-[11px] text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                    <div class="flex flex-col gap-1.5">
                        <label for="phone" class="text-xs font-bold text-slate-700">Nomor WhatsApp Koordinasi</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base">call</span>
                            <input id="phone" name="phone" type="tel" value="{{ old('phone', '+62 812-3456-7890') }}" required
                                   class="w-full py-2.5 pl-10 pr-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-blue-900 focus:bg-white transition-colors" />
                        </div>
                        @error('phone') <p class="text-[11px] text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="password" class="text-xs font-bold text-slate-700">Kata Sandi Baru</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base">lock</span>
                            <input id="password" name="password" type="password" minlength="8"
                                   placeholder="Kosongkan jika tidak diubah"
                                   class="w-full py-2.5 pl-10 pr-10 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder:text-slate-400 focus:outline-none focus:border-blue-900 focus:bg-white transition-colors" />
                            <button type="button" id="togglePassword" aria-label="Tampilkan kata sandi"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-900 transition-colors cursor-pointer">
                                <span class="material-symbols-outlined text-base">visibility</span>
                            </button>
                        </div>
                        @error('password') <p class="text-[11px] text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Info Box -->
                <div class="p-3 bg-blue-50/70 rounded-xl border border-blue-100 flex items-start gap-2.5">
                    <span class="material-symbols-outlined text-blue-900 text-base mt-0.5">info</span>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Pastikan nomor WhatsApp aktif untuk kemudahan update lokasi dan konfirmasi pengantaran pesanan ke pelanggan Kopi Tiga Titik.
                    </p>
                </div>

                <!-- Footer Action -->
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
    const toggleBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    if (toggleBtn && passwordInput) {
        toggleBtn.addEventListener('click', () => {
            const isHidden = passwordInput.type === 'password';
            passwordInput.type = isHidden ? 'text' : 'password';
            toggleBtn.querySelector('span').textContent = isHidden ? 'visibility_off' : 'visibility';
        });
    }
</script>
@endpush
