<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kopi Tiga Titik - Mojokerto Street Coffee</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Tailwind CSS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-800 antialiased font-sans flex flex-col justify-start items-center min-h-screen">

<!-- Main Wrapper (Max 1280px Sesuai Desain Figma) -->
<div class="w-full max-w-[1280px] bg-slate-50 flex flex-col justify-start items-start shadow-xl border-x border-slate-200/80">

  <!-- 1. Header Navigation Bar -->
  <header class="w-full bg-white shadow-xs border-b border-slate-200 sticky top-0 z-50">
    <div class="w-full h-20 max-w-[1280px] px-4 md:px-8 mx-auto flex justify-between items-center gap-4">
      
      <!-- Brand Logo & Name -->
      <a href="{{ url('/') }}" class="flex items-center gap-3 group">
        <div class="w-10 h-10 bg-blue-900 rounded-xl shadow-md flex items-center justify-center overflow-hidden shrink-0 group-hover:scale-105 transition-transform">
          <img class="w-7 h-7 object-contain" src="{{ asset('images/logo.png') }}" alt="Logo Kopi Tiga Titik" onerror="this.src='https://placehold.co/40x40/1e3a8a/ffffff?text=KTT'" />
        </div>
        <div class="flex flex-col">
          <span class="text-sky-900 text-xl font-bold font-['Plus_Jakarta_Sans'] leading-tight group-hover:text-blue-700 transition-colors">Kopi Tiga Titik</span>
          <span class="text-gray-500 text-[10px] font-bold font-['Inter'] leading-tight tracking-wide">Mojokerto Street Coffee</span>
        </div>
      </a>

      <!-- Desktop Navigation Menu Links -->
      <nav class="hidden md:flex items-center gap-8">
        <a href="{{ url('/') }}" class="pb-1 border-b-2 border-sky-900 text-sky-900 text-sm font-bold font-['Inter'] leading-5 tracking-tight transition-colors">
          Beranda
        </a>
        <a href="{{ url('/katalog') }}" class="text-gray-700 hover:text-blue-900 text-sm font-medium font-['Inter'] leading-6 transition-colors">
          Menu & Katalog
        </a>
        <a href="#lokasi-lapak" class="text-gray-700 hover:text-blue-900 text-sm font-medium font-['Inter'] leading-6 transition-colors">
          Lokasi Lapak
        </a>
        @auth
          <a href="{{ url('/order-tracking') }}" class="text-gray-700 hover:text-blue-900 text-sm font-medium font-['Inter'] leading-6 transition-colors">
            Lacak Pesanan
          </a>
        @else
          <a href="{{ route('login') }}" title="Silakan masuk untuk melacak pesanan" class="text-gray-700 hover:text-blue-900 text-sm font-medium font-['Inter'] leading-6 transition-colors">
            Lacak Pesanan
          </a>
        @endauth
      </nav>

      <!-- Action Button (Masuk / Profil Akun) -->
      <div class="flex items-center gap-3">
        <a href="{{ url('/katalog') }}" class="md:hidden px-3.5 py-2 bg-blue-100 text-blue-900 rounded-xl text-xs font-bold hover:bg-blue-200 transition-colors">
          Menu
        </a>
        @guest
          <a href="{{ route('login') }}" class="px-5 py-2.5 bg-blue-900 hover:bg-blue-800 text-white rounded-xl shadow-md text-sm font-semibold font-['Inter'] flex items-center gap-1.5 transition-all active:scale-95">
            <span class="material-symbols-outlined text-base">login</span>
            <span>Masuk</span>
          </a>
        @else
          <a href="{{ url('/profil-pelanggan') }}" class="px-3.5 py-2 bg-blue-50 hover:bg-blue-100 border border-blue-200/80 text-blue-900 rounded-xl text-xs md:text-sm font-semibold font-['Inter'] flex items-center gap-2 transition-all shadow-xs">
            <div class="w-6 h-6 rounded-full bg-blue-900 text-white text-[11px] font-bold flex items-center justify-center shrink-0">
              {{ strtoupper(substr(Auth::user()->name ?? 'P', 0, 1)) }}
            </div>
            <span class="truncate max-w-[120px]">{{ Auth::user()->name ?? 'Akun Saya' }}</span>
          </a>
        @endguest
      </div>
    </div>
  </header>

  <!-- 2. Hero Section -->
  <section class="w-full pt-8 md:pt-14 pb-12 bg-slate-50 flex flex-col justify-center items-center">
    <div class="w-full flex flex-col justify-start items-start">
      <div class="w-full px-6 md:px-12 py-10 md:py-16 relative flex flex-col justify-start items-start overflow-hidden">
        
        <!-- Background Blur Spheres -->
        <div class="w-96 h-96 -left-32 -top-32 absolute bg-sky-200/40 rounded-full blur-3xl pointer-events-none"></div>
        <div class="w-96 h-96 -right-20 top-60 absolute bg-blue-200/30 rounded-full blur-3xl pointer-events-none"></div>

        <div class="w-full flex flex-col lg:flex-row justify-between items-center gap-10 relative z-10">
          
          <!-- Hero Left Content -->
          <div class="w-full lg:w-1/2 flex flex-col justify-start items-start gap-5">
            
            <!-- Tagline Pill -->
            <div class="px-4 py-1.5 bg-sky-100 border border-sky-200/80 rounded-full shadow-xs inline-flex items-center gap-2">
              <span class="w-2 h-2 bg-blue-900 rounded-full animate-ping"></span>
              <span class="text-sky-900 text-[11px] font-bold font-['Inter'] uppercase tracking-wider">KOPI BOTOL SETIAP HARI • MOJOKERTO</span>
            </div>

            <!-- Headline -->
            <div>
              <h1 class="text-zinc-900 text-3xl sm:text-4xl md:text-5xl font-extrabold font-['Plus_Jakarta_Sans'] leading-tight md:leading-[56px]">
                Kopi Botol Jalanan<br/>
                Khas <span class="text-blue-700">Mojokerto</span>
              </h1>
            </div>

            <!-- Description -->
            <p class="max-w-[540px] text-gray-700 text-sm md:text-base font-normal font-['Plus_Jakarta_Sans'] leading-relaxed">
              Kemasan botol 250ml siap minum racikan artisan lokal. Diseduh segar dari biji pilihan, pesan praktis secara daring atau mampir ke lapak sore kami.
            </p>

            <!-- CTA Buttons -->
            <div class="pt-2 flex items-center gap-4 flex-wrap">
              <a href="{{ url('/katalog') }}" class="px-6 py-3.5 bg-blue-700 hover:bg-blue-800 text-white rounded-xl shadow-lg hover:shadow-xl font-semibold font-['Inter'] text-sm flex items-center justify-center gap-2 transition-all active:scale-98">
                <span>Pesan Sekarang</span>
                <span class="material-symbols-outlined text-base">arrow_forward</span>
              </a>
              <a href="#lokasi-lapak" class="px-6 py-3.5 bg-gray-200/90 hover:bg-gray-300 text-zinc-900 rounded-xl font-semibold font-['Inter'] text-sm flex items-center justify-center gap-2 transition-all">
                <span class="material-symbols-outlined text-base text-blue-900">storefront</span>
                <span>Lokasi Lapak</span>
              </a>
            </div>

            <!-- Badges Info -->
            <div class="w-full pt-4 flex items-center gap-4">
              <div class="flex-1 p-3 bg-white rounded-xl shadow-xs border border-slate-200/70 flex flex-col justify-start items-start">
                <span class="text-blue-900 text-base md:text-lg font-bold font-['Plus_Jakarta_Sans']">4 Varian</span>
                <span class="text-gray-600 text-xs font-medium font-['Plus_Jakarta_Sans']">Racikan Khas</span>
              </div>
              <div class="flex-1 p-3 bg-white rounded-xl shadow-xs border border-slate-200/70 flex flex-col justify-start items-start">
                <span class="text-blue-900 text-base md:text-lg font-bold font-['Plus_Jakarta_Sans']">250 ml</span>
                <span class="text-gray-600 text-xs font-medium font-['Plus_Jakarta_Sans']">Kemasan Botol</span>
              </div>
            </div>
          </div>

          <!-- Hero Right Banner Card -->
          <div class="w-full lg:w-1/2 p-4 md:p-6 bg-white rounded-3xl shadow-xl border border-slate-200/80 flex flex-col justify-start items-start gap-4 overflow-hidden relative group">
            
            <!-- Banner Image Container -->
            <div class="w-full h-64 sm:h-80 md:h-96 relative bg-slate-100 rounded-2xl flex justify-center items-center overflow-hidden border border-slate-200/60">
              <img class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500" src="{{ asset('products/All Produk.png') }}" alt="Semua Varian Kopi Tiga Titik" onerror="this.src='https://placehold.co/800x600/1e3a8a/ffffff?text=Kopi+Tiga+Titik'" />
              
              <!-- Floating Pill 1: 250ml -->
              <div class="px-3.5 py-1 left-4 top-4 absolute bg-white/95 rounded-full backdrop-blur-md shadow-md flex items-center gap-1.5 border border-slate-100">
                <span class="w-2 h-2 bg-blue-900 rounded-full"></span>
                <span class="text-zinc-900 text-[11px] font-bold font-['Inter']">250ml</span>
              </div>

              <!-- Floating Pill 2: Racikan Khas Mojokerto -->
              <div class="px-3.5 py-1 right-4 top-4 absolute bg-blue-700/95 text-white rounded-full shadow-md flex items-center gap-1.5 backdrop-blur-md">
                <span class="material-symbols-outlined text-xs">verified</span>
                <span class="text-[11px] font-semibold font-['Inter']">Racikan Khas Mojokerto</span>
              </div>
            </div>

            <!-- Banner Card Footer Info -->
            <div class="w-full p-3 bg-slate-100/90 rounded-2xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 border border-slate-200/60">
              <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 bg-blue-900 rounded-full shadow-xs flex justify-center items-center overflow-hidden shrink-0">
                  <img class="w-5 h-5 object-contain" src="{{ asset('images/logo.png') }}" alt="Logo" onerror="this.src='https://placehold.co/32x32/1e3a8a/ffffff?text=KTT'" />
                </div>
                <div class="flex flex-col">
                  <span class="text-zinc-900 text-xs font-bold font-['Inter']">Street Bottled Coffee</span>
                  <span class="text-gray-600 text-[11px] font-normal font-['Plus_Jakarta_Sans']">Diproduksi di Dusun Daleman, Desa Japan, Sooko</span>
                </div>
              </div>
              <span class="px-2.5 py-1 bg-white rounded-full shadow-xs text-sky-900 text-[10px] font-bold font-['Inter'] border border-slate-200/80">
                Mojokerto Area
              </span>
            </div>
          </div>

        </div>
      </div>

      <!-- 3. Lapak Aktif Hari Ini Card -->
      <div id="lokasi-lapak" class="w-full px-6 md:px-12 py-4 flex flex-col justify-start items-start">
        <div class="w-full p-5 md:p-6 bg-white rounded-2xl shadow-md border border-slate-200/90 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
          
          <div class="flex items-start gap-4">
            <div class="w-12 h-12 bg-sky-100 text-blue-900 rounded-2xl flex justify-center items-center shrink-0 border border-sky-200/60 shadow-xs">
              <span class="material-symbols-outlined text-2xl text-blue-900">storefront</span>
            </div>
            
            <div class="flex flex-col gap-1">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="px-2.5 py-0.5 bg-blue-50 text-blue-900 border border-blue-200/70 rounded-full text-[10px] font-bold font-['Inter'] flex items-center gap-1">
                  <span class="w-1.5 h-1.5 bg-blue-900 rounded-full animate-ping"></span>
                  <span>Lapak Aktif Hari Ini</span>
                </span>
                <span class="px-2.5 py-0.5 bg-slate-100 text-slate-700 rounded-full text-[10px] font-semibold font-['Inter'] border border-slate-200/70">
                  ⏰ Buka Sore 15.00 - 18.00 WIB
                </span>
              </div>
              <h3 class="text-zinc-900 text-base md:text-lg font-bold font-['Plus_Jakarta_Sans'] mt-0.5">
                Jl. Benteng Pancasila (Benpas)
              </h3>
              <p class="text-gray-600 text-xs md:text-sm font-normal font-['Plus_Jakarta_Sans']">
                Melayani dine-in di trotoar atau takeaway. Kec. Magersari, Kota Mojokerto.
              </p>
            </div>
          </div>

          <!-- Maps Action Button -->
          <div class="flex items-center gap-2 self-stretch md:self-auto justify-end">
            <a href="https://maps.google.com/?q=Benteng+Pancasila+Mojokerto" target="_blank" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-zinc-900 rounded-xl flex items-center gap-1.5 text-xs font-semibold font-['Inter'] transition-colors border border-slate-200/80 cursor-pointer shadow-xs">
              <span>Buka di Google Maps</span>
              <span class="material-symbols-outlined text-sm">open_in_new</span>
            </a>
          </div>
        </div>
      </div>

      <!-- 4. Produk yang Ditawarkan (4 Varian Kopi Favorit) -->
      <section class="w-full py-16 md:py-20 flex flex-col justify-start items-start">
        <div class="w-full px-6 md:px-12 flex flex-col justify-start items-start gap-8">
          
          <!-- Section Title Header -->
          <div class="w-full flex justify-between items-end flex-wrap gap-2">
            <div class="flex flex-col gap-1">
              <span class="text-sky-800 text-xs font-bold font-['Inter'] uppercase tracking-wider">Produk yang ditawarkan</span>
              <h2 class="text-zinc-900 text-2xl md:text-3xl font-extrabold font-['Plus_Jakarta_Sans'] leading-tight">4 Varian Kopi Favorit</h2>
              <p class="text-gray-600 text-xs md:text-sm font-normal font-['Plus_Jakarta_Sans']">Kemasan botol 250 ml siap minum racikan artisan lokal</p>
            </div>
            <a href="{{ url('/katalog') }}" class="text-blue-900 hover:text-blue-700 text-xs md:text-sm font-bold flex items-center gap-1 transition-colors">
              <span>Lihat Semua Menu</span>
              <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </a>
          </div>

          <!-- 4 Cards Grid -->
          <div class="w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

            <!-- Card 1: Titik Temu -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/90 flex flex-col justify-between overflow-hidden hover:shadow-lg hover:border-blue-700/50 transition-all group">
              <div>
                <div class="relative bg-slate-100 h-64 flex justify-center items-center overflow-hidden p-4">
                  <img class="h-56 object-contain group-hover:scale-105 transition-transform duration-300" src="{{ asset('products/Titik Temu.png') }}" alt="Titik Temu" onerror="this.src='https://placehold.co/278x278/1e3a8a/ffffff?text=Titik+Temu'" />
                  <div class="px-2.5 py-0.5 left-3 top-3 absolute bg-blue-700 text-white rounded-full shadow-xs text-[10px] font-bold font-['Inter']">
                    Best Seller
                  </div>
                  <span class="absolute bottom-2 right-2 px-1.5 py-0.5 bg-white/90 text-slate-700 text-[9px] font-bold rounded shadow-xs border border-slate-200">250 ml</span>
                </div>
                <div class="p-5 flex flex-col gap-2">
                  <span class="text-sky-800 text-[10px] font-bold font-['Inter'] uppercase tracking-wider">KOPI SUSU AREN</span>
                  <h3 class="text-zinc-900 text-lg font-bold font-['Plus_Jakarta_Sans'] group-hover:text-blue-900 transition-colors">Titik Temu</h3>
                  <p class="text-gray-600 text-xs font-normal font-['Plus_Jakarta_Sans'] line-clamp-2">Paduan espresso, susu murni, dan gula aren asli.</p>
                </div>
              </div>
              <div class="p-5 pt-0 flex justify-between items-center border-t border-slate-100 mt-2">
                <div class="flex flex-col">
                  <span class="text-gray-500 text-[10px] font-bold font-['Inter']">Harga</span>
                  <span class="text-blue-900 text-lg font-extrabold font-['Plus_Jakarta_Sans']">Rp 10.000</span>
                </div>
                <a href="{{ url('/katalog') }}" class="w-10 h-10 bg-blue-700 hover:bg-blue-800 text-white rounded-xl shadow-md flex justify-center items-center transition-transform active:scale-90" title="Pesan Titik Temu">
                  <span class="material-symbols-outlined text-lg">shopping_cart</span>
                </a>
              </div>
            </div>

            <!-- Card 2: Titik Hitam -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/90 flex flex-col justify-between overflow-hidden hover:shadow-lg hover:border-blue-700/50 transition-all group">
              <div>
                <div class="relative bg-slate-100 h-64 flex justify-center items-center overflow-hidden p-4">
                  <img class="h-56 object-contain group-hover:scale-105 transition-transform duration-300" src="{{ asset('products/Titik Hitam.png') }}" alt="Titik Hitam" onerror="this.src='https://placehold.co/278x278/1e3a8a/ffffff?text=Titik+Hitam'" />
                  <div class="px-2.5 py-0.5 left-3 top-3 absolute bg-zinc-900 text-white rounded-full shadow-xs text-[10px] font-bold font-['Inter']">
                    Kopi Murni
                  </div>
                  <span class="absolute bottom-2 right-2 px-1.5 py-0.5 bg-white/90 text-slate-700 text-[9px] font-bold rounded shadow-xs border border-slate-200">250 ml</span>
                </div>
                <div class="p-5 flex flex-col gap-2">
                  <span class="text-sky-800 text-[10px] font-bold font-['Inter'] uppercase tracking-wider">AMERICANO</span>
                  <h3 class="text-zinc-900 text-lg font-bold font-['Plus_Jakarta_Sans'] group-hover:text-blue-900 transition-colors">Titik Hitam</h3>
                  <p class="text-gray-600 text-xs font-normal font-['Plus_Jakarta_Sans'] line-clamp-2">Kopi hitam tanpa ampas dengan aroma bunga aromatik.</p>
                </div>
              </div>
              <div class="p-5 pt-0 flex justify-between items-center border-t border-slate-100 mt-2">
                <div class="flex flex-col">
                  <span class="text-gray-500 text-[10px] font-bold font-['Inter']">Harga</span>
                  <span class="text-blue-900 text-lg font-extrabold font-['Plus_Jakarta_Sans']">Rp 8.000</span>
                </div>
                <a href="{{ url('/katalog') }}" class="w-10 h-10 bg-blue-700 hover:bg-blue-800 text-white rounded-xl shadow-md flex justify-center items-center transition-transform active:scale-90" title="Pesan Titik Hitam">
                  <span class="material-symbols-outlined text-lg">shopping_cart</span>
                </a>
              </div>
            </div>

            <!-- Card 3: Titik Manja -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/90 flex flex-col justify-between overflow-hidden hover:shadow-lg hover:border-blue-700/50 transition-all group">
              <div>
                <div class="relative bg-slate-100 h-64 flex justify-center items-center overflow-hidden p-4">
                  <img class="h-56 object-contain group-hover:scale-105 transition-transform duration-300" src="{{ asset('products/Titik Manja.png') }}" alt="Titik Manja" onerror="this.src='https://placehold.co/278x278/1e3a8a/ffffff?text=Titik+Manja'" />
                  <div class="px-2.5 py-0.5 left-3 top-3 absolute bg-amber-800 text-white rounded-full shadow-xs text-[10px] font-bold font-['Inter']">
                    Kopi Karamel
                  </div>
                  <span class="absolute bottom-2 right-2 px-1.5 py-0.5 bg-white/90 text-slate-700 text-[9px] font-bold rounded shadow-xs border border-slate-200">250 ml</span>
                </div>
                <div class="p-5 flex flex-col gap-2">
                  <span class="text-sky-800 text-[10px] font-bold font-['Inter'] uppercase tracking-wider">CARAMEL MACCHIATO</span>
                  <h3 class="text-zinc-900 text-lg font-bold font-['Plus_Jakarta_Sans'] group-hover:text-blue-900 transition-colors">Titik Manja</h3>
                  <p class="text-gray-600 text-xs font-normal font-['Plus_Jakarta_Sans'] line-clamp-2">Perpaduan antara saus karamel mentega, susu segar, dan espresso.</p>
                </div>
              </div>
              <div class="p-5 pt-0 flex justify-between items-center border-t border-slate-100 mt-2">
                <div class="flex flex-col">
                  <span class="text-gray-500 text-[10px] font-bold font-['Inter']">Harga</span>
                  <span class="text-blue-900 text-lg font-extrabold font-['Plus_Jakarta_Sans']">Rp 10.000</span>
                </div>
                <a href="{{ url('/katalog') }}" class="w-10 h-10 bg-blue-700 hover:bg-blue-800 text-white rounded-xl shadow-md flex justify-center items-center transition-transform active:scale-90" title="Pesan Titik Manja">
                  <span class="material-symbols-outlined text-lg">shopping_cart</span>
                </a>
              </div>
            </div>

            <!-- Card 4: Titik Jenuh -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/90 flex flex-col justify-between overflow-hidden hover:shadow-lg hover:border-blue-700/50 transition-all group">
              <div>
                <div class="relative bg-slate-100 h-64 flex justify-center items-center overflow-hidden p-4">
                  <img class="h-56 object-contain group-hover:scale-105 transition-transform duration-300" src="{{ asset('products/Titik Jenuh.png') }}" alt="Titik Jenuh" onerror="this.src='https://placehold.co/278x278/1e3a8a/ffffff?text=Titik+Jenuh'" />
                  <div class="px-2.5 py-0.5 left-3 top-3 absolute bg-stone-700 text-white rounded-full shadow-xs text-[10px] font-bold font-['Inter']">
                    Non-Kopi
                  </div>
                  <span class="absolute bottom-2 right-2 px-1.5 py-0.5 bg-white/90 text-slate-700 text-[9px] font-bold rounded shadow-xs border border-slate-200">250 ml</span>
                </div>
                <div class="p-5 flex flex-col gap-2">
                  <span class="text-sky-800 text-[10px] font-bold font-['Inter'] uppercase tracking-wider">DARK CHOCO MILK</span>
                  <h3 class="text-zinc-900 text-lg font-bold font-['Plus_Jakarta_Sans'] group-hover:text-blue-900 transition-colors">Titik Jenuh</h3>
                  <p class="text-gray-600 text-xs font-normal font-['Plus_Jakarta_Sans'] line-clamp-2">Perpaduan cokelat pekat premium dan susu sapi segar gurih.</p>
                </div>
              </div>
              <div class="p-5 pt-0 flex justify-between items-center border-t border-slate-100 mt-2">
                <div class="flex flex-col">
                  <span class="text-gray-500 text-[10px] font-bold font-['Inter']">Harga</span>
                  <span class="text-blue-900 text-lg font-extrabold font-['Plus_Jakarta_Sans']">Rp 12.000</span>
                </div>
                <a href="{{ url('/katalog') }}" class="w-10 h-10 bg-blue-700 hover:bg-blue-800 text-white rounded-xl shadow-md flex justify-center items-center transition-transform active:scale-90" title="Pesan Titik Jenuh">
                  <span class="material-symbols-outlined text-lg">shopping_cart</span>
                </a>
              </div>
            </div>

          </div>
        </div>
      </section>

      <!-- 5. Komitmen Kualitas (Kenapa Kopi Tiga Titik?) -->
      <section class="w-full py-16 bg-slate-100/80 border-y border-slate-200/80 flex flex-col justify-start items-start">
        <div class="w-full px-6 md:px-12 flex flex-col justify-start items-center gap-10">
          
          <div class="max-w-[576px] flex flex-col justify-start items-center text-center gap-2">
            <span class="text-sky-800 text-xs font-bold font-['Inter'] uppercase tracking-wider">KOMITMEN KUALITAS</span>
            <h2 class="text-zinc-900 text-2xl md:text-3xl font-bold font-['Plus_Jakarta_Sans'] leading-tight">Kenapa Kopi Tiga Titik?</h2>
            <p class="text-gray-600 text-sm font-normal font-['Plus_Jakarta_Sans'] leading-relaxed">
              Dedikasi kami menyajikan street coffee bermutu tinggi tanpa kompromi rasa.
            </p>
          </div>

          <div class="w-full grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Keunggulan 1: Real Coffee -->
            <div class="p-8 bg-white rounded-2xl shadow-xs border border-slate-200/70 flex flex-col items-center text-center gap-3 hover:shadow-md transition-shadow">
              <div class="w-14 h-14 bg-sky-100 rounded-2xl flex justify-center items-center border border-sky-200/80 shadow-xs">
                <span class="material-symbols-outlined text-3xl text-blue-900">coffee</span>
              </div>
              <h3 class="text-zinc-900 text-lg font-bold font-['Plus_Jakarta_Sans']">Real Coffee</h3>
              <p class="text-gray-600 text-xs md:text-sm font-normal font-['Plus_Jakarta_Sans'] leading-relaxed">
                Fresh espresso shot diekstrak dari biji kopi lokal pilihan tanpa perisa tiruan sintetis.
              </p>
            </div>

            <!-- Keunggulan 2: Fast Delivery -->
            <div class="p-8 bg-white rounded-2xl shadow-xs border border-slate-200/70 flex flex-col items-center text-center gap-3 hover:shadow-md transition-shadow">
              <div class="w-14 h-14 bg-sky-100 rounded-2xl flex justify-center items-center border border-sky-200/80 shadow-xs">
                <span class="material-symbols-outlined text-3xl text-blue-900">moped</span>
              </div>
              <h3 class="text-zinc-900 text-lg font-bold font-['Plus_Jakarta_Sans']">Fast Delivery</h3>
              <p class="text-gray-600 text-xs md:text-sm font-normal font-['Plus_Jakarta_Sans'] leading-relaxed">
                Kurir internal siap mengantar ke seluruh kawasan Mojokerto Kota & Sekitarnya dengan kemasan botol aman.
              </p>
            </div>

            <!-- Keunggulan 3: Budget Friendly -->
            <div class="p-8 bg-white rounded-2xl shadow-xs border border-slate-200/70 flex flex-col items-center text-center gap-3 hover:shadow-md transition-shadow">
              <div class="w-14 h-14 bg-sky-100 rounded-2xl flex justify-center items-center border border-sky-200/80 shadow-xs">
                <span class="material-symbols-outlined text-3xl text-blue-900">savings</span>
              </div>
              <h3 class="text-zinc-900 text-lg font-bold font-['Plus_Jakarta_Sans']">Budget Friendly</h3>
              <p class="text-gray-600 text-xs md:text-sm font-normal font-['Plus_Jakarta_Sans'] leading-relaxed">
                Ramah kantong harian, nikmati kopi botol berkualitas mulai dari Rp 8.000 saja per kemasan botol.
              </p>
            </div>

          </div>
        </div>
      </section>

      <!-- 6. Footer Section -->
      <footer class="w-full px-6 md:px-12 py-16 bg-slate-900 text-slate-300 flex flex-col justify-start items-start">
        <div class="w-full grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
          
          <!-- Col 1: Brand & Bio -->
          <div class="flex flex-col gap-4">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 bg-blue-700 rounded-lg flex items-center justify-center overflow-hidden">
                <img class="w-5 h-5 object-contain" src="{{ asset('images/logo.png') }}" alt="Logo" onerror="this.src='https://placehold.co/32x32/1e3a8a/ffffff?text=KTT'" />
              </div>
              <span class="text-white text-xl font-bold font-['Plus_Jakarta_Sans']">Kopi Tiga Titik</span>
            </div>
            <p class="text-slate-400 text-xs md:text-sm font-normal font-['Inter'] leading-relaxed">
              Street bottled coffee crafted in Mojokerto. Menghadirkan kenikmatan kopi botol setiap hari.
            </p>
            <div class="pt-2 text-slate-500 text-[10px] font-bold font-['Inter'] leading-tight">
              © {{ date('Y') }} Kopi Tiga Titik Mojokerto.<br/>
              Street Bottled Coffee
            </div>
          </div>

          <!-- Col 2: Jam Operasional -->
          <div class="flex flex-col gap-3">
            <h4 class="text-white text-base font-bold font-['Plus_Jakarta_Sans']">Jam Operasional</h4>
            <div class="flex flex-col gap-2.5 text-xs md:text-sm font-['Inter']">
              <div>
                <strong class="text-slate-200 block">Dapur Seduh Sooko:</strong>
                <span class="text-slate-400">08.00 - 15.00 WIB</span>
              </div>
              <div>
                <strong class="text-slate-200 block">Lapak Jl. Benteng Pancasila:</strong>
                <span class="text-slate-400">15.00 - 18.00 WIB</span>
              </div>
            </div>
          </div>

          <!-- Col 3: Navigasi & Wilayah -->
          <div class="flex flex-col gap-3">
            <h4 class="text-white text-base font-bold font-['Plus_Jakarta_Sans']">Lokasi & Navigasi</h4>
            <ul class="flex flex-col gap-2 text-xs md:text-sm font-['Inter'] text-slate-400">
              <li><a href="{{ url('/katalog') }}" class="hover:text-white transition-colors">Menu & Katalog</a></li>
              <li><a href="#lokasi-lapak" class="hover:text-white transition-colors">Lokasi Lapak Mojokerto</a></li>
              @auth
                <li><a href="{{ url('/order-tracking') }}" class="hover:text-white transition-colors">Lacak Status Pesanan</a></li>
              @else
                <li><a href="{{ route('login') }}" class="hover:text-white transition-colors" title="Silakan masuk untuk melacak pesanan">Lacak Status Pesanan</a></li>
              @endauth
            </ul>
            <div class="pt-1 text-slate-500 text-[11px] font-['Inter']">
              Area Antar: Kec. Sooko, Kec. Magersari, Kec. Prajurit Kulon, & Kec. Puri.
            </div>
          </div>

          <!-- Col 4: Kontak -->
          <div class="flex flex-col gap-3">
            <h4 class="text-white text-base font-bold font-['Plus_Jakarta_Sans']">Kontak</h4>
            <div class="flex flex-col gap-2 text-xs md:text-sm font-['Inter']">
              <a href="https://wa.me/6281238499210" target="_blank" class="flex items-center gap-2 text-slate-400 hover:text-emerald-400 transition-colors">
                <span class="material-symbols-outlined text-sm text-emerald-500">chat</span>
                <span>WhatsApp (+62 812-3849-9210)</span>
              </a>
              <a href="https://instagram.com/kopitigatitik_" target="_blank" class="flex items-center gap-2 text-slate-400 hover:text-pink-400 transition-colors">
                <span class="material-symbols-outlined text-sm text-pink-500">photo_camera</span>
                <span>Instagram @kopitigatitik_</span>
              </a>
            </div>
          </div>

        </div>
      </footer>

    </div>
  </section>

</div>

</body>
</html>
