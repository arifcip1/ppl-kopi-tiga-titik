<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CatalogController extends Controller
{
    /**
     * Tampilkan halaman katalog produk untuk pelanggan (FR-3.1 s/d FR-3.5).
     * - FR-3.1: Publik dapat melihat katalog tanpa login; hanya produk aktif tampil.
     * - FR-3.4: Stok dibaca langsung dari database setiap kali tampil, tidak di-cache.
     * - Pemisahan Role: Owner/Admin dan Kurir diarahkan ke dashboard perannya masing-masing.
     */
    public function index(Request $request): View|JsonResponse|RedirectResponse
    {
        if (! $request->wantsJson() && Auth::check()) {
            $role = Auth::user()->role;
            if (in_array($role, ['admin', 'owner'])) {
                return redirect()->route('owner.dashboard');
            }
            if ($role === 'kurir') {
                return redirect()->route('kurir.tugas-baru');
            }
        }

        // FR-3.1 & FR-3.4: Stok dibaca langsung dari database pada setiap request, tidak di-cache.
        $products = Product::where('aktif', true)
            ->orderBy('id', 'asc')
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Berhasil',
                'data' => $products,
            ], 200);
        }

        return view('Desktop Catalog Product Page', compact('products'));
    }

    /**
     * API Publik: Daftar katalog produk aktif dalam format JSON (Kontrak Dokumen PPL 01 Bagian 7).
     * Endpoint: GET /api/products
     */
    public function apiIndex(): JsonResponse
    {
        $products = Product::where('aktif', true)
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'message' => 'Berhasil',
            'data' => $products,
        ], 200);
    }

    /**
     * API Publik: Detail varian produk aktif dalam format JSON (Kontrak Dokumen PPL 01 Bagian 7).
     * Endpoint: GET /api/products/{product}
     */
    public function apiShow(Product $product): JsonResponse
    {
        // FR-3.1 & BR-03: Produk dengan status nonaktif tidak dapat diakses di katalog publik
        if (!$product->aktif) {
            return response()->json([
                'message' => 'Produk tidak ditemukan atau tidak aktif.',
            ], 404);
        }

        return response()->json([
            'message' => 'Berhasil',
            'data' => $product,
        ], 200);
    }
}
