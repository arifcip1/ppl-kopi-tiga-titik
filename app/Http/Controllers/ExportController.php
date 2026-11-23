<?php

namespace App\Http\Controllers;

use App\Exports\SalesHistoryExport;
use App\Services\SalesReportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExportController extends Controller
{
    public function exportSales(Request $request, SalesReportService $service): Response
    {
        $periode = $service->normalizePeriode($request->query('periode'));

        // Jika library Maatwebsite Excel dan ekstensi PHP zip terpasang, ekspor dalam format .xlsx
        if (class_exists(\Maatwebsite\Excel\Facades\Excel::class) && extension_loaded('zip')) {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new SalesHistoryExport($periode),
                'riwayat-penjualan-'.$periode.'-'.today()->format('Ymd').'.xlsx'
            );
        }

        // Fallback mandiri format CSV standar: tanpa dependensi eksternal, kompatibel dibuka di Microsoft Excel
        $filename = 'riwayat-penjualan-'.$periode.'-'.today()->format('Ymd').'.csv';
        $rows = $service->getRows($periode);

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            // Tambahkan UTF-8 BOM agar karakter terbaca rapi langsung di Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['Tanggal', 'Varian Produk', 'Sumber Penjualan', 'Jumlah Botol', 'Total Omzet Bersih (Rp)']);
            foreach ($rows as $r) {
                fputcsv($handle, [
                    $r['tanggal'],
                    $r['varian'],
                    $r['sumber'],
                    $r['jumlah'],
                    $r['total_omzet'],
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
