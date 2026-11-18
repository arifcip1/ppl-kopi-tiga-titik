<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\PosException;
use App\Services\PosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PosController extends Controller
{
    /**
     * Menampilkan antarmuka POS Kasir Lapak Offline (PPL-31 / F12).
     * Endpoint: GET /owner/pos
     */
    public function index(): View
    {
        $products = Product::where('aktif', true)
            ->orderBy('nama')
            ->get();

        return view('owner.pos', [
            'products' => $products,
        ]);
    }

    /**
     * Memproses transaksi POS Kasir Offline (PPL-32 / F12 / BR-13, BR-14, BR-15).
     * Endpoint: POST /owner/pos/transactions & POST /api/admin/pos/transactions
     */
    public function transaction(Request $request, PosService $posService): JsonResponse
    {
        $validated = $request->validate([
            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.jumlah'     => ['required', 'integer', 'min:1'],
            'metode_pembayaran'  => ['required', 'string', 'in:tunai,qris'],
            'nama_pelanggan'     => ['nullable', 'string', 'max:100'],
            'uang_diterima'      => ['nullable', 'numeric', 'min:0'],
        ], [
            'items.required'            => 'Keranjang pesanan kasir tidak boleh kosong.',
            'items.array'               => 'Format data item tidak valid.',
            'items.min'                 => 'Pilih minimal 1 item produk untuk transaksi.',
            'items.*.product_id.required' => 'ID produk wajib diisi.',
            'items.*.product_id.exists' => 'Varian produk yang dipilih tidak terdaftar di sistem.',
            'items.*.jumlah.required'   => 'Kuantitas botol wajib diisi.',
            'items.*.jumlah.min'        => 'Kuantitas setiap item minimal 1 botol.',
            'metode_pembayaran.required'=> 'Pilih metode pembayaran (Tunai atau QRIS Langsung).',
            'metode_pembayaran.in'      => 'Metode pembayaran hanya boleh tunai atau qris.',
            'nama_pelanggan.max'        => 'Nama pelanggan maksimal 100 karakter.',
        ]);

        try {
            $result = $posService->createTransaction($validated, $request->user());

            return response()->json([
                'success' => true,
                'message' => 'Transaksi kasir lapak offline berhasil diselesaikan.',
                'data'    => $result,
            ], 201);
        } catch (PosException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => $e->errors(),
            ], 422);
        }
    }
}
