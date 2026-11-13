@extends('layouts.owner')

@section('title', 'Riwayat Penjualan & Ekspor Excel')
@section('page_title', 'Riwayat Penjualan')
@section('page_subtitle', 'Pantau rekapitulasi penjualan produk online & POS lapak, grafik performa, dan ekspor data ke Excel')
@section('active_menu', 'riwayat-penjualan')

@section('header_actions')
    <!-- Tombol Ekspor Excel Hijau Membuka Dialog Informasi Bertema Komponen Alert -->
    <button type="button" onclick="openExportModal()" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl inline-flex items-center gap-1.5 shadow-xs transition-colors cursor-pointer">
        <span class="material-symbols-outlined text-base">table_view</span>
        <span>Ekspor ke Excel</span>
    </button>
@endsection

@section('content')
<div class="flex flex-col gap-5">

    <!-- Notifikasi Flash Message & Validasi Global -->
    <x-flash-message />

    <!-- Filter Bar & Informasi Periode -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-slate-400 text-lg">filter_alt</span>
            <span class="text-xs font-bold text-slate-700">Filter Periode Laporan:</span>
        </div>

        <form method="GET" action="{{ url('/owner/riwayat-penjualan') }}" class="flex items-center gap-2">
            <select name="periode" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-900/20 cursor-pointer">
                <option value="harian" {{ $periode === 'harian' ? 'selected' : '' }}>Harian (Hari Ini)</option>
                <option value="mingguan" {{ $periode === 'mingguan' ? 'selected' : '' }}>Mingguan (7 Hari Terakhir)</option>
                <option value="bulanan" {{ $periode === 'bulanan' ? 'selected' : '' }}>Bulanan (Bulan Ini)</option>
            </select>

            <noscript>
                <button type="submit" class="px-2.5 py-1.5 bg-blue-900 text-white text-xs rounded-xl font-bold">Terapkan</button>
            </noscript>
        </form>
    </div>

    <!-- 3 Kotak Kartu Ringkasan (Summary Cards) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- 1. Total Botol Terjual -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Botol Terjual</span>
                <div class="text-2xl font-extrabold text-slate-900 font-['Plus_Jakarta_Sans'] mt-1">
                    {{ number_format($summary['total_botol'] ?? 0) }} <span class="text-xs font-semibold text-slate-500">Botol</span>
                </div>
                <span class="text-[10px] text-slate-500 mt-0.5 block">Akumulasi Online Midtrans + POS Lapak</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-100 text-blue-900 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-2xl">local_cafe</span>
            </div>
        </div>

        <!-- 2. Total Omzet Bersih (Tanpa Ongkir) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Omzet Penjualan</span>
                <div class="text-2xl font-extrabold text-emerald-700 font-['Plus_Jakarta_Sans'] mt-1">
                    Rp {{ number_format($summary['total_omzet'] ?? 0, 0, ',', '.') }}
                </div>
                <span class="text-[10px] text-emerald-600 font-medium mt-0.5 block">✓ Bersih, ongkir tidak dihitung omzet</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-2xl">payments</span>
            </div>
        </div>

        <!-- 3. Varian Terlaris -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Varian Paling Diminati</span>
                <div class="text-xl font-extrabold text-slate-900 font-['Plus_Jakarta_Sans'] mt-1">
                    {{ $summary['varian_terlaris'] ?? '-' }}
                </div>
                <span class="text-[10px] text-amber-600 font-semibold mt-0.5 block">🔥 Kontributor penjualan tertinggi</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-2xl">local_fire_department</span>
            </div>
        </div>
    </div>

    <!-- Grafik Visualisasi Tren Penjualan (Chart.js) -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs flex flex-col gap-3">
        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
            <div>
                <h3 class="text-sm font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Grafik Tren Penjualan Botol Kopi</h3>
                <p class="text-xs text-slate-500 mt-0.5">Pantau fluktuasi permintaan produk harian</p>
            </div>
            <span class="px-2.5 py-1 bg-slate-100 text-slate-600 text-[10px] font-bold rounded-lg uppercase">
                Mode Periode: {{ ucfirst($periode) }}
            </span>
        </div>

        <div class="relative w-full h-64 md:h-72">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <!-- Tabel Rincian Data Transaksi & Rekap -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden flex flex-col">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900 font-['Plus_Jakarta_Sans']">Daftar Rekapitulasi Transaksi</h3>
                <p class="text-xs text-slate-500 mt-0.5">Rincian penjualan per tanggal dan kanal transaksi</p>
            </div>
            <span class="text-xs text-slate-400 font-medium">Menampilkan transaksi terbaru</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 text-slate-600 font-bold uppercase tracking-wider text-[10px] border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">Tanggal / Waktu</th>
                        <th class="py-3 px-4">Varian Produk</th>
                        <th class="py-3 px-4 text-center">Sumber Penjualan</th>
                        <th class="py-3 px-4 text-center">Jumlah Botol</th>
                        <th class="py-3 px-4 text-right">Total Omzet Bersih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($sales as $row)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-4 font-mono text-slate-700 text-[11px]">
                                {{ $row['tanggal'] }}
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-900 font-['Plus_Jakarta_Sans']">
                                {{ $row['varian'] }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if (strtolower($row['sumber']) === 'online')
                                    <span class="px-2 py-0.5 bg-blue-100 text-blue-900 font-extrabold text-[10px] rounded-full uppercase">
                                        Online QRIS
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-900 font-extrabold text-[10px] rounded-full uppercase">
                                        POS Lapak
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center font-extrabold text-slate-800">
                                {{ $row['jumlah'] }} <span class="text-[10px] text-slate-400 font-normal">btl</span>
                            </td>
                            <td class="py-3 px-4 text-right font-extrabold text-slate-900">
                                Rp {{ number_format($row['total_omzet'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">
                                <span class="material-symbols-outlined text-3xl text-slate-300 block mb-1">receipt_long</span>
                                <span>Belum ada data riwayat transaksi penjualan untuk periode ini.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal Dialog Ekspor Excel Menggunakan Komponen Standar x-alert -->
<div id="modal-export-info" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden opacity-0 transition-opacity duration-200">
    <div class="w-full max-w-md">
        <x-alert 
            type="info" 
            title="Informasi Ekspor Excel (Fitur PPL-30)" 
            message="Fitur download file spreadsheet Excel (.xlsx) untuk riwayat transaksi penjualan akan aktif otomatis setelah modul Backend PPL-30 diimplementasikan."
            :dismissible="false"
            class="shadow-2xl"
        >
            <div class="mt-3 pt-3 border-t border-sky-200/80 flex items-center justify-between">
                <span class="text-[11px] text-sky-800 font-medium">
                    Filter Laporan: <strong>{{ ucfirst($periode) }}</strong>
                </span>
                <button type="button" onclick="closeExportModal()" class="px-3.5 py-1.5 bg-sky-900 hover:bg-sky-800 text-white rounded-xl text-xs font-bold transition-colors cursor-pointer shadow-xs">
                    Saya Mengerti
                </button>
            </div>
        </x-alert>
    </div>
</div>
@endsection

@push('scripts')
    <!-- CDN Chart.js Resmi Sesuai Opsi 1 -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Dialog Modal Ekspor Excel
        function openExportModal() {
            const modal = document.getElementById('modal-export-info');
            if (modal) {
                modal.classList.remove('hidden');
                setTimeout(() => modal.classList.remove('opacity-0'), 10);
            }
        }

        function closeExportModal() {
            const modal = document.getElementById('modal-export-info');
            if (modal) {
                modal.classList.add('opacity-0');
                setTimeout(() => modal.classList.add('hidden'), 200);
            }
        }

        // Inisialisasi Chart.js
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('salesChart');
            if (!ctx) return;

            const labels = @json($chartLabels ?? []);
            const dataPoints = @json($chartData ?? []);

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Botol Terjual',
                        data: dataPoints,
                        backgroundColor: 'rgba(30, 58, 138, 0.85)', // Blue-900 brand
                        hoverBackgroundColor: 'rgba(30, 58, 138, 1)',
                        borderRadius: 8,
                        borderSkipped: false,
                        maxBarThickness: 38
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleFont: { size: 12, weight: 'bold' },
                            bodyFont: { size: 12 },
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + ' Botol Terjual';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11 }, color: '#64748b' }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9' },
                            ticks: {
                                stepSize: 10,
                                font: { size: 11 },
                                color: '#64748b'
                            }
                        }
                    }
                }
            });
        });
    </script>
@endpush
