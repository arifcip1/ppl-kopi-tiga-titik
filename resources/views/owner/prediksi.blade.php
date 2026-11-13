@extends('layouts.owner')

@section('title', 'Prediksi Stok (Moving Average)')
@section('page_title', 'Prediksi Kebutuhan Stok')
@section('page_subtitle', 'Estimasi kebutuhan stok botol menggunakan metode Moving Average 3 Minggu (21 Hari Kalender)')
@section('active_menu', 'prediksi-stok')

@section('header_actions')
    <a href="{{ url('/owner/prediksi') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl inline-flex items-center gap-1.5 transition-colors">
        <span class="material-symbols-outlined text-base">refresh</span>
        <span>Refresh</span>
    </a>
@endsection

@section('content')
<div class="flex flex-col gap-5">

    <!-- Notifikasi Flash Message & Validasi Global -->
    <x-flash-message />

    <!-- Card Panduan & Formula Singkat -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-start gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-blue-50 border border-blue-100 text-blue-900 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-2xl">calculate</span>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-sm md:text-base font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Metode: Simple Moving Average (SMA 3 Minggu)</h2>
                    <span class="px-2 py-0.5 bg-blue-100 text-blue-900 font-extrabold text-[10px] rounded-full uppercase">Aturan BR-17</span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">
                    Perhitungan didasarkan pada total penjualan riil (Online terverifikasi & POS kasir) selama 21 hari kalender ke belakang yang dibagi menjadi 3 periode 7-harian mundur.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <div class="px-3.5 py-2 bg-slate-50 rounded-xl border border-slate-200 text-center">
                <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Formula SMA</span>
                <span class="text-xs font-bold text-blue-900 font-mono">(P₁ + P₂ + P₃) ÷ 3</span>
            </div>
            <div class="px-3.5 py-2 bg-slate-50 rounded-xl border border-slate-200 text-center">
                <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Standar Harian</span>
                <span class="text-xs font-bold text-slate-800">10 Botol/Varian</span>
            </div>
        </div>
    </div>

    <!-- Kondisi 1: Data Transaksi Riil Belum Cukup (< 21 Hari) menggunakan Komponen Standar x-alert -->
    @if (!$isDataSufficient)
        <x-alert 
            type="warning" 
            title="Data Transaksi Riil Belum Mencukupi" 
            message="Data transaksi belum cukup untuk kalkulasi Moving Average 3 minggu (memerlukan minimal 21 hari kalender transaksi riil)."
            :dismissible="false"
            class="p-5 sm:p-6"
        >
            <div class="mt-3 pt-3 border-t border-amber-200/80 text-[11px] text-amber-900 flex items-center gap-2 font-medium">
                <span class="material-symbols-outlined text-sm text-amber-700 shrink-0">info</span>
                <span>Sesuai aturan bisnis PRD (BR-21), data seeder berlabel simulasi tidak digunakan dalam perhitungan prediksi produksi riil.</span>
            </div>
        </x-alert>

    <!-- Kondisi 2: Data Transaksi Riil Cukup (Tampilkan Tabel Moving Average) -->
    @else
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden flex flex-col">
            <!-- Header Card Tabel -->
            <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Rincian Rekomendasi Kebutuhan Stok per Varian</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Evaluasi pergerakan penjualan dan kebutuhan restok mingguan</p>
                </div>
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>
                    <span class="font-semibold text-slate-700">21 Hari Kalender Lengkap</span>
                </div>
            </div>

            <!-- Table Responsive Wrapper -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/80 text-slate-600 font-bold uppercase tracking-wider text-[10px] border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Nama Varian</th>
                            <th class="py-3 px-4 text-center">Stok Saat Ini</th>
                            <th class="py-3 px-4 text-center bg-slate-100/50">Minggu 1<br><span class="font-normal text-[9px] text-slate-400 normal-case">(H-21 s.d. H-15)</span></th>
                            <th class="py-3 px-4 text-center bg-slate-100/50">Minggu 2<br><span class="font-normal text-[9px] text-slate-400 normal-case">(H-14 s.d. H-8)</span></th>
                            <th class="py-3 px-4 text-center bg-slate-100/50">Minggu 3<br><span class="font-normal text-[9px] text-slate-400 normal-case">(H-7 s.d. H-1)</span></th>
                            <th class="py-3 px-4 text-center bg-blue-50/50 text-blue-900 font-extrabold">Estimasi Penjualan<br><span class="font-normal text-[9px] text-blue-700 normal-case">(Minggu Depan)</span></th>
                            <th class="py-3 px-4 text-center bg-emerald-50/60 text-emerald-900 font-extrabold">Saran Produksi<br><span class="font-normal text-[9px] text-emerald-700 normal-case">(Dibulatkan)</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($predictions as $item)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <!-- Nama Varian -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-slate-100 border border-slate-200/80 flex items-center justify-center shrink-0 p-1">
                                            <span class="material-symbols-outlined text-blue-900 text-lg">local_cafe</span>
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 text-xs font-['Plus_Jakarta_Sans']">{{ $item['nama'] }}</div>
                                            <div class="text-[10px] text-slate-500">{{ $item['rasa'] }} • 250ml</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Stok Saat Ini -->
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold {{ $item['stok'] <= 5 ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'bg-slate-100 text-slate-800' }}">
                                        @if ($item['stok'] <= 5)
                                            <span class="material-symbols-outlined text-xs">warning</span>
                                        @endif
                                        <span>{{ $item['stok'] }} Botol</span>
                                    </span>
                                </td>

                                <!-- Minggu 1 -->
                                <td class="py-3.5 px-4 text-center font-medium text-slate-700 bg-slate-50/30">
                                    {{ $item['minggu_1'] }} <span class="text-[10px] text-slate-400">btl</span>
                                </td>

                                <!-- Minggu 2 -->
                                <td class="py-3.5 px-4 text-center font-medium text-slate-700 bg-slate-50/30">
                                    {{ $item['minggu_2'] }} <span class="text-[10px] text-slate-400">btl</span>
                                </td>

                                <!-- Minggu 3 -->
                                <td class="py-3.5 px-4 text-center font-medium text-slate-700 bg-slate-50/30">
                                    {{ $item['minggu_3'] }} <span class="text-[10px] text-slate-400">btl</span>
                                </td>

                                <!-- Estimasi Penjualan (1 Desimal) -->
                                <td class="py-3.5 px-4 text-center bg-blue-50/30">
                                    <span class="text-xs font-extrabold text-blue-900">
                                        {{ number_format($item['estimasi'], 1) }}
                                    </span>
                                    <span class="text-[10px] text-blue-700 font-semibold block">Botol/Minggu</span>
                                </td>

                                <!-- Saran Produksi (Dibulatkan ke atas / integer) -->
                                <td class="py-3.5 px-4 text-center bg-emerald-50/40">
                                    @if ($item['saran_produksi'] > 0)
                                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-600 text-white font-extrabold text-xs rounded-xl shadow-xs">
                                            <span class="material-symbols-outlined text-xs">add_circle</span>
                                            <span>+{{ $item['saran_produksi'] }} Botol</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 text-slate-500 font-bold text-[11px] rounded-xl">
                                            <span class="material-symbols-outlined text-xs">check_circle</span>
                                            <span>Stok Memadai</span>
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Disclaimer / Catatan Kebijakan Bisnis menggunakan Komponen Standar x-alert -->
        <x-alert 
            type="info" 
            title="Catatan Penggunaan & Kebijakan Bisnis" 
            :dismissible="false"
        >
            <p class="mt-0.5 text-sky-950 font-normal leading-relaxed">
                Nilai estimasi penjualan dan saran produksi di atas murni merupakan rekomendasi analitik bantu bagi Owner dalam menyusun rencana pembuatan kopi. Sistem <strong class="font-bold text-sky-900">tidak melakukan pemotongan, pemesanan, atau penambahan stok secara otomatis</strong>. Keputusan final operasional racik kopi harian tetap berada di tangan Owner.
            </p>
        </x-alert>
    @endif

</div>
@endsection
