<?php

namespace App\Http\Controllers;

use App\Exports\SalesHistoryExport;
use App\Services\SalesReportService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    public function exportSales(Request $request, SalesReportService $service): BinaryFileResponse
    {
        $periode = $service->normalizePeriode($request->query('periode'));

        return Excel::download(
            new SalesHistoryExport($periode),
            'riwayat-penjualan-'.$periode.'-'.today()->format('Ymd').'.xlsx'
        );
    }
}
