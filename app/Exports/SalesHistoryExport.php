<?php

namespace App\Exports;

use App\Services\SalesReportService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalesHistoryExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(private string $periode)
    {
    }

    public function headings(): array
    {
        return ['Tanggal', 'Varian Produk', 'Sumber Penjualan', 'Jumlah Botol', 'Total Omzet Bersih (Rp)'];
    }

    public function collection(): \Illuminate\Support\Enumerable
    {
        return app(SalesReportService::class)
            ->getRows($this->periode)
            ->map(fn ($r) => [
                $r['tanggal'],
                $r['varian'],
                $r['sumber'],
                $r['jumlah'],
                $r['total_omzet'],
            ]);
    }
}
